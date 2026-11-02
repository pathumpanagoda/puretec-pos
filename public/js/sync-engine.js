/**
 * =============================================================
 * PureSync v2 — Production Sync Engine for Pure POS
 * =============================================================
 *
 * CRITICAL IMPROVEMENTS:
 *   - Exponential backoff retry
 *   - Deduplication before push (prevent double-send)
 *   - Per-item error handling (one failure doesn't block others)
 *   - Conflict detection and resolution
 *   - Heartbeat with configurable interval
 *   - Incremental pull with sync cursor
 *   - Sync status state machine
 *   - Stock movement sync support
 *   - Manual sync trigger
 *   - Recovery from localStorage backup
 *
 * States: ONLINE, OFFLINE, SYNCING, SYNCED, FAILED, CONFLICT
 *
 * Author: Puretec | Version: 2.0.0
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
        connectionState: 'UNKNOWN', // ONLINE, OFFLINE
        // Sync
        syncState: 'IDLE',         // IDLE, SYNCING, SYNCED, FAILED, CONFLICT
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

        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) _config.csrfToken = meta.content;

        _config.deviceId = _getDeviceId();

        window.addEventListener('online', _handleOnline);
        window.addEventListener('offline', _handleOffline);

        // Handle page visibility changes (resume sync when tab becomes active)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && _state.isOnline) {
                _updatePendingCount();
            }
        });

        // Handle beforeunload — warn if pending syncs
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

        // Recover any backed-up sync items
        PureDB.recoverFromBackup();

        console.log('[PureSync] Initialized v2. Online:', _state.isOnline, 'Device:', _config.deviceId);
    }

    // ── Network Detection ────────────────────────────────────────

    function _handleOnline() {
        console.log('[PureSync] Network: Online');
        _state.isOnline = true;
        _state.connectionState = 'ONLINE';
        _state.retryCount = 0;
        _notify('online');
        _notify('state_change', getFullStatus());

        // Auto-push when coming back online (with small delay for network stabilization)
        setTimeout(() => pushAll(), 3000);
    }

    function _handleOffline() {
        console.log('[PureSync] Network: Offline');
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

    // ── Push Sync ────────────────────────────────────────────────

    async function pushAll() {
        if (_state.isSyncing || !_state.isOnline) return;

        _state.isSyncing = true;
        _state.syncState = 'SYNCING';
        _state.lastSyncAttempt = new Date().toISOString();
        _notify('sync_start');
        _notify('state_change', getFullStatus());

        try {
            const pendingItems = await PureDB.getPendingSyncItems();

            if (pendingItems.length === 0) {
                _state.isSyncing = false;
                _state.syncState = 'SYNCED';
                _state.retryCount = 0;
                await _updatePendingCount();
                _notify('sync_complete', { pushed: 0 });
                _notify('state_change', getFullStatus());
                return;
            }

            console.log(`[PureSync] Pushing ${pendingItems.length} items...`);
            _state.syncProgress = { current: 0, total: pendingItems.length };

            let totalPushed = 0;
            let totalFailed = 0;

            // Process in batches
            for (let i = 0; i < pendingItems.length; i += _config.batchSize) {
                const batch = pendingItems.slice(i, i + _config.batchSize);

                try {
                    const result = await _pushBatch(batch);
                    totalPushed += result.pushed;
                    totalFailed += result.failed;
                    _state.syncProgress.current = Math.min(i + _config.batchSize, pendingItems.length);
                    _notify('sync_progress', _state.syncProgress);
                } catch (batchError) {
                    console.error('[PureSync] Batch push failed:', batchError);
                    totalFailed += batch.length;
                    // Don't break — try remaining batches
                }
            }

            _state.lastPush = new Date().toISOString();
            _state.isSyncing = false;
            _state.retryCount = 0;

            if (totalFailed > 0) {
                _state.syncState = 'FAILED';
                _scheduleRetry();
            } else {
                _state.syncState = 'SYNCED';
            }

            await _updatePendingCount();
            _notify('sync_complete', { pushed: totalPushed, failed: totalFailed });
            _notify('state_change', getFullStatus());

        } catch (error) {
            console.error('[PureSync] Push failed:', error);
            _state.isSyncing = false;
            _state.syncState = 'FAILED';
            _state.lastError = error.message;
            _state.syncErrors.push({ time: new Date().toISOString(), error: error.message });
            // Keep only last 20 errors
            if (_state.syncErrors.length > 20) _state.syncErrors.splice(0, _state.syncErrors.length - 20);
            _state.errorCount++;

            _scheduleRetry();
            _notify('sync_error', { error: error.message });
            _notify('state_change', getFullStatus());
        }
    }

    async function _pushBatch(items) {
        // Deduplicate: ensure no duplicate UUIDs in the batch
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
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        const response = await fetch(_config.apiBase + _config.pushEndpoint, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify({
                device_id: _config.deviceId,
                transactions: transactions,
            }),
            signal: controller.signal,
        });

        clearTimeout(timeoutId);

        if (!response.ok) {
            throw new Error(`Push failed with status ${response.status}`);
        }

        const data = await response.json();
        let pushed = 0;
        let failed = 0;

        if (data.success && data.data && data.data.results) {
            for (const result of data.data.results) {
                const item = uniqueItems.find(i => i.uuid === result.uuid);
                if (!item) continue;

                if (result.status === 'success' || result.status === 'duplicate') {
                    await PureDB.markSynced(item.id);
                    if (item.entity_type === 'order') {
                        await PureDB.markOrderSynced(item.uuid);
                    }
                    pushed++;
                } else if (result.status === 'conflict') {
                    // Mark as conflict for manual resolution
                    const queueItem = await PureDB.get('sync_queue', item.id);
                    if (queueItem) {
                        queueItem.status = 'conflict';
                        queueItem.error_message = result.message || 'Data conflict';
                        await PureDB.put('sync_queue', queueItem);
                    }
                    _state.syncState = 'CONFLICT';
                    failed++;
                } else {
                    await PureDB.markSyncFailed(item.id, result.message || 'Unknown error');
                    failed++;
                }
            }
        }

        return { pushed, failed };
    }

    /**
     * Schedule a retry with exponential backoff.
     */
    function _scheduleRetry() {
        _state.retryCount++;
        if (_state.retryCount > _config.maxRetryAttempts) {
            console.warn('[PureSync] Max retry attempts reached');
            return;
        }

        const delay = Math.min(
            _config.retryBaseDelay * Math.pow(2, _state.retryCount - 1),
            _config.retryMaxDelay
        );

        console.log(`[PureSync] Retry ${_state.retryCount} in ${delay / 1000}s`);

        if (_intervals.retryTimer) clearTimeout(_intervals.retryTimer);
        _intervals.retryTimer = setTimeout(() => {
            if (_state.isOnline && !_state.isSyncing) {
                pushAll();
            }
        }, delay);
    }

    // ── Pull Sync ────────────────────────────────────────────────

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

            let url = _config.apiBase + _config.pullEndpoint + '?types=' + types;
            if (since) url += '&since=' + encodeURIComponent(since);
            if (syncCursor) url += '&cursor=' + encodeURIComponent(syncCursor);

            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 30000);

            const response = await fetch(url, {
                method: 'GET',
                headers: headers,
                signal: controller.signal,
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                throw new Error(`Pull failed with status ${response.status}`);
            }

            const data = await response.json();

            if (data.success && data.data) {
                const stats = await PureDB.hydrate(data.data);
                _state.lastPull = data.synced_at || new Date().toISOString();
                await PureDB.setMeta('last_pull', _state.lastPull);
                if (data.sync_cursor) {
                    await PureDB.setMeta('sync_cursor', data.sync_cursor);
                }
                _notify('pull_complete', stats);
                _notify('state_change', getFullStatus());
                console.log('[PureSync] Pull complete:', stats);
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

    /**
     * Hydrate IndexedDB from the web POS page (existing endpoints).
     */
    async function hydrateFromPageData(productsUrl, categoriesUrl, customersUrl) {
        try {
            const headers = {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': _config.csrfToken,
            };

            const [productsRes, customersRes] = await Promise.all([
                fetch(productsUrl || '/pos/products/search?limit=9999', { headers }),
                fetch(customersUrl || '/customers?format=json&limit=9999', { headers }).catch(() => null),
            ]);

            const data = {};

            if (productsRes && productsRes.ok) {
                const prodData = await productsRes.json();
                data.products = prodData.products || prodData.data || prodData;
            }

            if (customersRes && customersRes.ok) {
                const custData = await customersRes.json();
                data.customers = custData.customers || custData.data || custData;
            }

            if (data.products || data.customers) {
                const stats = await PureDB.hydrate(data);
                console.log('[PureSync] Page data hydration:', stats);
                return stats;
            }
        } catch (error) {
            console.error('[PureSync] Page data hydration failed:', error);
        }
        return null;
    }

    // ── Event System ─────────────────────────────────────────────

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
                catch (e) { console.error('[PureSync] Listener error:', e); }
            });
    }

    // ── Internal Helpers ─────────────────────────────────────────

    function _getDeviceId() {
        let deviceId = localStorage.getItem('purepos_device_id');
        if (!deviceId) {
            deviceId = PureDB.generateUUID();
            localStorage.setItem('purepos_device_id', deviceId);
        }
        return deviceId;
    }

    async function _updatePendingCount() {
        try {
            _state.pendingCount = await PureDB.getPendingSyncCount();
            _state.failedCount = await PureDB.getFailedSyncCount();
            _notify('pending_update', { count: _state.pendingCount, failed: _state.failedCount });
        } catch (e) {
            // DB might not be ready yet
        }
    }

    // ── Status ───────────────────────────────────────────────────

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

    // ── Manual Trigger ──────────────────────────────────────────

    async function syncNow() {
        if (!_state.isOnline) {
            _notify('sync_error', { error: 'Cannot sync while offline' });
            return;
        }
        _state.retryCount = 0;
        await pushAll();
        await pull();
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
        hydrateFromPageData,
        syncNow,
        on,
        off,
        getStatus,
        getFullStatus,
        isOnline,
        destroy,
    };
})();
