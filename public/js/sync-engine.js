/**
 * =============================================================
 * PureSync v2.1 — Production Sync Engine for Pure POS
 * =============================================================
 *
 * CRITICAL IMPROVEMENTS:
 *   - Exponential backoff retry with max limit
 *   - Deduplication before push (prevent double-send)
 *   - Per-item error handling (one failure doesn't block others)
 *   - Multi-device conflict detection and status tracking
 *   - Heartbeat with configurable interval
 *   - Incremental pull with cursor-based synchronization
 *   - Sync status state machine: ONLINE, OFFLINE, SYNCING, SYNCED, FAILED, CONFLICT, AUTH_EXPIRED
 *   - Stock movement sync support
 *   - Manual sync trigger with retry reset
 *   - Authentication expiry handling (HTTP 401/419) without losing queue
 *   - Credentials inclusion for same-origin sessions
 *   - Disaster recovery from localStorage backup
 *
 * Author: Puretec | Version: 2.1.0
 * =============================================================
 */

'use strict';

const PureSync = (function () {
    let _config = {
        apiBase: '/api/v1',
        pushEndpoint: '/sync/push',
        pullEndpoint: '/sync/pull',
        healthEndpoint: '/health',
        batchSize: 10,
        retryBaseDelay: 5000,     // 5 seconds base
        retryMaxDelay: 300000,    // 5 minutes max
        pullInterval: 300000,     // 5 minutes
        heartbeatInterval: 15000, // 15 seconds
        maxRetryAttempts: 10,
        deviceId: null,
        token: null,
        csrfToken: null,
    };

    let _state = {
        // Connection
        isOnline: navigator.onLine,
        connectionState: navigator.onLine ? 'ONLINE' : 'OFFLINE',
        // Sync State
        syncState: 'IDLE', // IDLE, SYNCING, SYNCED, FAILED, CONFLICT, AUTH_EXPIRED
        isSyncing: false,
        lastPush: null,
        lastPull: null,
        lastSyncAttempt: null,
        // Counts
        pendingCount: 0,
        failedCount: 0,
        errorCount: 0,
        // Errors
        syncErrors: [],
        lastError: null,
        // Progress
        syncProgress: { current: 0, total: 0 },
        // Retry
        retryCount: 0,
        // Listeners
        listeners: [],
    };

    let _intervals = {
        heartbeat: null,
        autoSync: null,
        autoPull: null,
        retryTimer: null,
    };

    // ── Initialize ───────────────────────────────────────────────

    function init(config = {}) {
        Object.assign(_config, config);

        // Auto-extract CSRF token from page meta if available
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta && !_config.csrfToken) _config.csrfToken = meta.content;

        // Auto-detect Sanctum token from local storage if not explicitly provided
        if (!_config.token) {
            _config.token = localStorage.getItem('purepos_auth_token') || localStorage.getItem('auth_token') || null;
        }

        _config.deviceId = _getDeviceId();

        window.addEventListener('online', _handleOnline);
        window.addEventListener('offline', _handleOffline);

        // Handle page visibility changes (resume sync when tab becomes active)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && _state.isOnline) {
                _updatePendingCount();
                // If there are pending items, trigger a push
                if (_state.pendingCount > 0 && !_state.isSyncing) {
                    console.log('[PureSync] Tab active with pending items — triggering push');
                    pushAll();
                }
            }
        });

        // Handle beforeunload — warn cashier if pending syncs exist
        window.addEventListener('beforeunload', (e) => {
            if (_state.pendingCount > 0) {
                e.preventDefault();
                e.returnValue = 'You have unsynced transactions. Are you sure you want to leave?';
            }
        });

        _state.connectionState = navigator.onLine ? 'ONLINE' : 'OFFLINE';
        _startHeartbeat();
        _startAutoPull();
        _updatePendingCount();

        // Listen for Service Worker background sync trigger
        if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
            navigator.serviceWorker.addEventListener('message', (event) => {
                if (event.data && event.data.type === 'SYNC_TRIGGER') {
                    console.log('[PureSync] Background sync triggered by Service Worker');
                    if (_state.isOnline && !_state.isSyncing) {
                        pushAll();
                    }
                }
            });
        }

        // Disaster recovery check: restore any backed-up items missing from IndexedDB
        if (typeof PureDB !== 'undefined' && PureDB.recoverFromBackup) {
            PureDB.recoverFromBackup();
        }

        console.log('[PureSync] Initialized v2.1. Online:', _state.isOnline, 'Device:', _config.deviceId);
    }

    // ── Network Detection ────────────────────────────────────────

    function _handleOnline() {
        console.log('[PureSync] Network connection detected: Online');
        _state.isOnline = true;
        _state.connectionState = 'ONLINE';
        _state.retryCount = 0;
        _notify('online');
        _notify('state_change', getFullStatus());

        // Trigger push + pull after brief stabilization delay
        setTimeout(async () => {
            await pushAll();
            // Incremental pull to refresh products/customers/categories
            try {
                await pull();
            } catch (e) {
                console.warn('[PureSync] Pull after reconnect failed:', e.message);
            }
        }, 2500);
    }

    function _handleOffline() {
        console.log('[PureSync] Network connection lost: Offline');
        _state.isOnline = false;
        _state.connectionState = 'OFFLINE';
        _state.syncState = 'IDLE';
        _notify('offline');
        _notify('state_change', getFullStatus());
    }

    function _startHeartbeat() {
        if (_intervals.heartbeat) clearInterval(_intervals.heartbeat);

        _intervals.heartbeat = setInterval(async () => {
            try {
                const controller = new AbortController();
                const timeoutId = setTimeout(() => controller.abort(), 5000);

                const response = await fetch(_config.apiBase + _config.healthEndpoint, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                    signal: controller.signal,
                });

                clearTimeout(timeoutId);

                if (response.ok) {
                    if (!_state.isOnline) {
                        _state.isOnline = true;
                        _state.connectionState = 'ONLINE';
                        _state.retryCount = 0;
                        _notify('online');
                        _notify('state_change', getFullStatus());
                        pushAll();
                    }
                } else if (response.status === 401 || response.status === 419) {
                    _notify('auth_expired', { status: response.status });
                }
            } catch (e) {
                if (_state.isOnline) {
                    _state.isOnline = false;
                    _state.connectionState = 'OFFLINE';
                    _notify('offline');
                    _notify('state_change', getFullStatus());
                }
            }
        }, _config.heartbeatInterval);
    }

    function _startAutoPull() {
        if (_intervals.autoPull) clearInterval(_intervals.autoPull);

        _intervals.autoPull = setInterval(async () => {
            if (_state.isOnline && !_state.isSyncing) {
                await pull();
            }
        }, _config.pullInterval);
    }

    // ── Push Synchronization (Robust Batching & Idempotency) ─────

    async function pushAll() {
        if (_state.isSyncing || !_state.isOnline) return;

        _state.isSyncing = true;
        _state.syncState = 'SYNCING';
        _state.lastSyncAttempt = new Date().toISOString();
        _notify('sync_start');
        _notify('state_change', getFullStatus());

        try {
            const pendingItems = await PureDB.getPendingSyncItems();

            if (!pendingItems || pendingItems.length === 0) {
                _state.isSyncing = false;
                _state.syncState = 'SYNCED';
                _state.retryCount = 0;
                await _updatePendingCount();
                _notify('sync_complete', { pushed: 0, failed: 0 });
                _notify('state_change', getFullStatus());
                return;
            }

            console.log(`[PureSync] Processing ${pendingItems.length} pending items...`);
            _state.syncProgress = { current: 0, total: pendingItems.length };

            let totalPushed = 0;
            let totalFailed = 0;
            let totalConflicts = 0;

            // Process sequentially in manageable chunks
            for (let i = 0; i < pendingItems.length; i += _config.batchSize) {
                const batch = pendingItems.slice(i, i + _config.batchSize);

                try {
                    const result = await _pushBatch(batch);
                    totalPushed += result.pushed;
                    totalFailed += result.failed;
                    if (result.conflicts) totalConflicts += result.conflicts;
                    _state.syncProgress.current = Math.min(i + _config.batchSize, pendingItems.length);
                    _notify('sync_progress', _state.syncProgress);
                } catch (batchError) {
                    console.error('[PureSync] Batch push failed:', batchError);
                    totalFailed += batch.length;
                    // Check for auth failure
                    if (batchError.message && batchError.message.includes('Authentication expired')) {
                        break;
                    }
                }
            }

            _state.lastPush = new Date().toISOString();
            _state.isSyncing = false;

            if (totalConflicts > 0) {
                _state.syncState = 'CONFLICT';
            } else if (totalFailed > 0) {
                _state.syncState = 'FAILED';
                _scheduleRetry();
            } else {
                _state.syncState = 'SYNCED';
                _state.retryCount = 0;
            }

            await _updatePendingCount();
            _notify('sync_complete', { pushed: totalPushed, failed: totalFailed, conflicts: totalConflicts });
            _notify('state_change', getFullStatus());

        } catch (error) {
            console.error('[PureSync] Push failed:', error);
            _state.isSyncing = false;
            _state.syncState = error.isAuth ? 'AUTH_EXPIRED' : 'FAILED';
            _state.lastError = error.message;
            _state.syncErrors.push({ time: new Date().toISOString(), error: error.message });
            if (_state.syncErrors.length > 20) _state.syncErrors.shift();
            _state.errorCount++;

            if (!error.isAuth) {
                _scheduleRetry();
            }
            _notify('sync_error', { error: error.message });
            _notify('state_change', getFullStatus());
        }
    }

    async function _pushBatch(items) {
        // Deduplicate within the batch by UUID
        const seenUUIDs = new Set();
        const uniqueItems = items.filter(item => {
            if (seenUUIDs.has(item.uuid)) return false;
            seenUUIDs.add(item.uuid);
            return true;
        });

        const transactions = uniqueItems.map(item => ({
            uuid: item.uuid,
            entity_type: item.entity_type,
            operation: item.operation,
            payload: item.payload,
            created_at: item.created_at,
        }));

        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Device-ID': _config.deviceId,
        };

        if (_config.token) {
            headers['Authorization'] = 'Bearer ' + _config.token;
        }
        if (_config.csrfToken) {
            headers['X-CSRF-TOKEN'] = _config.csrfToken;
        }

        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 35000);

        let response;
        try {
            response = await fetch(_config.apiBase + _config.pushEndpoint, {
                method: 'POST',
                headers: headers,
                credentials: 'same-origin',
                body: JSON.stringify({
                    device_id: _config.deviceId,
                    transactions: transactions,
                }),
                signal: controller.signal,
            });
        } finally {
            clearTimeout(timeoutId);
        }

        // Handle authentication expiry without losing local transactions
        if (response.status === 401 || response.status === 419) {
            _notify('auth_expired', { status: response.status });
            const authErr = new Error('Authentication expired. Please re-authenticate.');
            authErr.isAuth = true;
            throw authErr;
        }

        if (!response.ok) {
            throw new Error(`Push rejected by server with HTTP status ${response.status}`);
        }

        const data = await response.json();
        let pushed = 0;
        let failed = 0;
        let conflicts = 0;

        if (data.success && data.data && data.data.results) {
            for (const result of data.data.results) {
                const item = uniqueItems.find(i => i.uuid === result.uuid);
                if (!item) continue;

                if (result.status === 'success' || result.status === 'duplicate') {
                    // Confirmed by server: mark as synced and remove disaster backup
                    await PureDB.markSynced(item.id);
                    if (item.entity_type === 'order') {
                        await PureDB.markOrderSynced(item.uuid);
                    }
                    if (item.entity_type === 'customer' && result.entity_id && PureDB.updateCustomerServerId) {
                        await PureDB.updateCustomerServerId(item.uuid, result.entity_id);
                    }
                    pushed++;
                } else if (result.status === 'conflict') {
                    // Multi-device conflict detected: mark item as conflict, retain for review
                    const queueItem = await PureDB.get('sync_queue', item.id);
                    if (queueItem) {
                        queueItem.status = 'conflict';
                        queueItem.error_message = result.message || 'Data conflict recorded on server';
                        await PureDB.put('sync_queue', queueItem);
                    }
                    if (item.entity_type === 'order' && PureDB.markOrderConflict) {
                        await PureDB.markOrderConflict(item.uuid, result.message);
                    }
                    conflicts++;
                } else {
                    // Processing error: keep item in queue and increment attempt counter
                    await PureDB.markSyncFailed(item.id, result.message || 'Sync processing failed');
                    failed++;
                }
            }
        }

        return { pushed, failed, conflicts };
    }

    /**
     * Exponential backoff retry handler.
     */
    function _scheduleRetry() {
        _state.retryCount++;
        if (_state.retryCount > _config.maxRetryAttempts) {
            console.warn(`[PureSync] Max retry attempts (${_config.maxRetryAttempts}) reached. Awaiting next network event or manual trigger.`);
            return;
        }

        const delay = Math.min(
            _config.retryBaseDelay * Math.pow(2, _state.retryCount - 1),
            _config.retryMaxDelay
        );

        console.log(`[PureSync] Scheduling retry ${_state.retryCount} in ${delay / 1000}s`);

        if (_intervals.retryTimer) clearTimeout(_intervals.retryTimer);
        _intervals.retryTimer = setTimeout(() => {
            if (_state.isOnline && !_state.isSyncing) {
                pushAll();
            }
        }, delay);
    }

    // ── Incremental Pull Synchronization ─────────────────────────

    async function pull(types = 'products,customers,categories') {
        if (!_state.isOnline) return null;

        try {
            const since = _state.lastPull || await PureDB.getMeta('last_pull');
            const syncCursor = await PureDB.getMeta('sync_cursor');

            const headers = {
                'Accept': 'application/json',
                'X-Device-ID': _config.deviceId,
            };
            if (_config.token) {
                headers['Authorization'] = 'Bearer ' + _config.token;
            }
            if (_config.csrfToken) {
                headers['X-CSRF-TOKEN'] = _config.csrfToken;
            }

            let url = _config.apiBase + _config.pullEndpoint + '?types=' + encodeURIComponent(types);
            if (syncCursor && parseInt(syncCursor, 10) > 0) {
                url += '&cursor=' + encodeURIComponent(syncCursor);
            } else if (since) {
                url += '&since=' + encodeURIComponent(since);
            }

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            let response;
            try {
                response = await fetch(url, {
                    method: 'GET',
                    headers: headers,
                    credentials: 'same-origin',
                    signal: controller.signal,
                });
            } finally {
                clearTimeout(timeoutId);
            }

            if (response.status === 401 || response.status === 419) {
                _notify('auth_expired', { status: response.status });
                throw new Error('Authentication expired during pull');
            }

            if (!response.ok) {
                throw new Error(`Pull failed with HTTP status ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.data) {
                const stats = await PureDB.hydrate(data.data);
                _state.lastPull = data.synced_at || new Date().toISOString();
                await PureDB.setMeta('last_pull', _state.lastPull);

                if (data.sync_cursor && parseInt(data.sync_cursor, 10) > 0) {
                    await PureDB.setMeta('sync_cursor', data.sync_cursor);
                }

                _notify('pull_complete', stats);
                _notify('state_change', getFullStatus());
                console.log('[PureSync] Pull complete:', stats, 'Cursor:', data.sync_cursor);
                return stats;
            }

            return null;
        } catch (error) {
            console.error('[PureSync] Pull failed:', error);
            _notify('pull_error', { error: error.message });
            return null;
        }
    }

    async function fullRefresh() {
        _state.lastPull = null;
        await PureDB.setMeta('last_pull', null);
        await PureDB.setMeta('sync_cursor', null);
        return await pull();
    }

    // ── Event Subscription System ────────────────────────────────

    function on(event, callback) {
        _state.listeners.push({ event, callback });
    }

    function off(event, callback) {
        _state.listeners = _state.listeners.filter(l =>
            !(l.event === event && l.callback === callback)
        );
    }

    function _notify(event, data = {}) {
        _state.listeners
            .filter(l => l.event === event)
            .forEach(l => {
                try { l.callback(data); }
                catch (e) { console.error('[PureSync] Listener error for ' + event + ':', e); }
            });
    }

    // ── Internal Helpers ─────────────────────────────────────────

    function _getDeviceId() {
        let deviceId = localStorage.getItem('purepos_device_id');
        if (!deviceId) {
            deviceId = PureDB.generateUUID ? PureDB.generateUUID() : 'dev_' + Math.random().toString(36).substring(2, 12);
            localStorage.setItem('purepos_device_id', deviceId);
        }
        return deviceId;
    }

    async function _updatePendingCount() {
        try {
            _state.pendingCount = await PureDB.getPendingSyncCount();
            _state.failedCount = await PureDB.getFailedSyncCount();
            _notify('pending_update', { count: _state.pendingCount, failed: _state.failedCount });

            // Register for background sync if supported and there are pending items
            if (_state.pendingCount > 0 && 'serviceWorker' in navigator && navigator.serviceWorker.ready) {
                try {
                    const reg = await navigator.serviceWorker.ready;
                    if (reg.sync) {
                        await reg.sync.register('purepos-sync');
                    }
                } catch (bgErr) {
                    // Background sync not supported or permission denied — non-critical
                }
            }
        } catch (e) {
            // Non-critical if DB initializing
        }
    }

    // ── Status Inspection ────────────────────────────────────────

    function getFullStatus() {
        return {
            connectionState: _state.connectionState,
            syncState: _state.syncState,
            isOnline: _state.isOnline,
            isSyncing: _state.isSyncing,
            pendingCount: _state.pendingCount,
            failedCount: _state.failedCount,
            errorCount: _state.errorCount,
            lastPush: _state.lastPush,
            lastPull: _state.lastPull,
            lastSyncAttempt: _state.lastSyncAttempt,
            lastError: _state.lastError,
            syncProgress: _state.syncProgress,
            deviceId: _config.deviceId,
            recentErrors: _state.syncErrors.slice(-5),
        };
    }

    function getStatus() {
        return getFullStatus();
    }

    function isOnline() {
        return _state.isOnline;
    }

    // ── Manual Sync Trigger (Reset Retry Timer & Force Sync) ─────

    async function syncNow() {
        if (!_state.isOnline) {
            _notify('sync_error', { error: 'Cannot synchronize while offline.' });
            return;
        }

        _state.retryCount = 0;
        if (_intervals.retryTimer) {
            clearTimeout(_intervals.retryTimer);
            _intervals.retryTimer = null;
        }

        // Reset any failed attempt counts so exhausted items are retried
        if (typeof PureDB !== 'undefined' && PureDB.resetFailedAttempts) {
            await PureDB.resetFailedAttempts();
        }

        await pushAll();
        await pull();
    }

    /**
     * Get failed sync items for display to the cashier.
     */
    async function getFailedItems() {
        try {
            if (typeof PureDB !== 'undefined' && PureDB.getPendingSyncItems) {
                const all = await PureDB.getPendingSyncItems();
                return (all || []).filter(item =>
                    item.status === 'failed' || item.status === 'conflict' || (item.attempts && item.attempts >= _config.maxRetryAttempts)
                );
            }
            return [];
        } catch (e) {
            return [];
        }
    }

    // ── Cleanup ──────────────────────────────────────────────────

    function destroy() {
        window.removeEventListener('online', _handleOnline);
        window.removeEventListener('offline', _handleOffline);
        if (_intervals.heartbeat) clearInterval(_intervals.heartbeat);
        if (_intervals.autoSync) clearInterval(_intervals.autoSync);
        if (_intervals.autoPull) clearInterval(_intervals.autoPull);
        if (_intervals.retryTimer) clearTimeout(_intervals.retryTimer);
    }

    // ── Public API ───────────────────────────────────────────────
    return {
        init,
        pushAll,
        pull,
        fullRefresh,
        syncNow,
        getFailedItems,
        on,
        off,
        getStatus,
        getFullStatus,
        isOnline,
        destroy,
    };
})();
