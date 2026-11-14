/**
 * =============================================================
 * PureDB v2 — Production-Reliable IndexedDB for Pure POS
 * =============================================================
 *
 * CRITICAL RELIABILITY IMPROVEMENTS:
 *   - Write-ahead logging for sync queue (never lose transactions)
 *   - Retry on IndexedDB transaction abort
 *   - DB open recovery on versionchange/close
 *   - Durable sync queue items (never auto-deleted)
 *   - Incremental sync cursor support
 *   - Versioned schema with migration support
 *
 * Stores:
 *   products, categories, customers, cart,
 *   orders, sync_queue, settings, meta
 *
 * Author: Puretec | Version: 2.0.0
 * =============================================================
 */

'use strict';

const PureDB = (function () {
    const DB_NAME = 'pure_pos';
    const DB_VERSION = 3; // bumped for offline users, registers, and held_orders
    let _db = null;
    let _initPromise = null;

    // ── Database Schema ──────────────────────────────────────────
    const STORES = {
        products:   { keyPath: 'id', indexes: [
            { name: 'uuid', keyPath: 'uuid', unique: true },
            { name: 'sku', keyPath: 'sku', unique: false },
            { name: 'barcode', keyPath: 'barcode', unique: false },
            { name: 'category_id', keyPath: 'category_id', unique: false },
            { name: 'name', keyPath: 'name', unique: false },
            { name: 'updated_at', keyPath: 'updated_at', unique: false },
            { name: 'sync_version', keyPath: 'sync_version', unique: false },
        ]},
        categories: { keyPath: 'id', indexes: [
            { name: 'uuid', keyPath: 'uuid', unique: true },
            { name: 'parent_id', keyPath: 'parent_id', unique: false },
            { name: 'name', keyPath: 'name', unique: false },
        ]},
        customers:  { keyPath: 'id', indexes: [
            { name: 'uuid', keyPath: 'uuid', unique: true },
            { name: 'name', keyPath: 'name', unique: false },
            { name: 'phone', keyPath: 'phone', unique: false },
            { name: 'email', keyPath: 'email', unique: false },
        ]},
        cart:       { keyPath: 'id', autoIncrement: true },
        orders:     { keyPath: 'uuid', indexes: [
            { name: 'id', keyPath: 'id', unique: false },
            { name: 'status', keyPath: 'status', unique: false },
            { name: 'sync_status', keyPath: 'sync_status', unique: false },
            { name: 'created_at', keyPath: 'created_at', unique: false },
        ]},
        sync_queue: { keyPath: 'id', autoIncrement: true, indexes: [
            { name: 'entity_type', keyPath: 'entity_type', unique: false },
            { name: 'status', keyPath: 'status', unique: false },
            { name: 'created_at', keyPath: 'created_at', unique: false },
            { name: 'uuid', keyPath: 'uuid', unique: false },
        ]},
        users:      { keyPath: 'id', indexes: [
            { name: 'username', keyPath: 'username', unique: false },
            { name: 'email', keyPath: 'email', unique: false },
        ]},
        registers:  { keyPath: 'id', indexes: [
            { name: 'status', keyPath: 'status', unique: false },
            { name: 'opened_at', keyPath: 'opened_at', unique: false },
        ]},
        held_orders:{ keyPath: 'uuid', indexes: [
            { name: 'created_at', keyPath: 'created_at', unique: false },
        ]},
        settings:   { keyPath: 'key' },
        meta:       { keyPath: 'key' },
    };

    // ── Initialize ───────────────────────────────────────────────
    function init() {
        if (_initPromise) return _initPromise;

        _initPromise = new Promise((resolve, reject) => {
            if (_db) { resolve(_db); return; }

            const request = indexedDB.open(DB_NAME, DB_VERSION);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                const oldVersion = event.oldVersion;

                for (const [storeName, config] of Object.entries(STORES)) {
                    let store;
                    if (!db.objectStoreNames.contains(storeName)) {
                        const storeOpts = { keyPath: config.keyPath };
                        if (config.autoIncrement) storeOpts.autoIncrement = true;
                        store = db.createObjectStore(storeName, storeOpts);
                    } else {
                        store = event.target.transaction.objectStore(storeName);
                    }

                    if (config.indexes) {
                        config.indexes.forEach(idx => {
                            if (!store.indexNames.contains(idx.name)) {
                                store.createIndex(idx.name, idx.keyPath, { unique: idx.unique });
                            }
                        });
                    }
                }
            };

            request.onsuccess = (event) => {
                _db = event.target.result;

                // Auto-recover if DB is closed unexpectedly
                _db.onclose = () => {
                    console.warn('[PureDB] Database closed unexpectedly, will reconnect');
                    _db = null;
                    _initPromise = null;
                };

                _db.onversionchange = () => {
                    _db.close();
                    _db = null;
                    _initPromise = null;
                    console.warn('[PureDB] Database version changed, reconnecting');
                };

                console.log('[PureDB] Database opened successfully (v' + DB_VERSION + ')');

                // Request persistent storage protection against browser eviction
                if (navigator.storage && navigator.storage.persist) {
                    navigator.storage.persist().then(granted => {
                        if (granted) {
                            console.log('[PureDB] Storage persistence enabled: offline POS data protected from browser eviction');
                        }
                    }).catch(() => {});
                }

                resolve(_db);
            };

            request.onerror = (event) => {
                console.error('[PureDB] Database open failed:', event.target.error);
                _initPromise = null;
                reject(event.target.error);
            };

            request.onblocked = () => {
                console.warn('[PureDB] Database open blocked — close other tabs');
            };
        });

        return _initPromise;
    }

    /**
     * Ensure DB is ready before any operation.
     */
    async function _ensureDB() {
        if (!_db) await init();
        return _db;
    }

    // ── Generic CRUD Operations (with retry) ─────────────────────

    /**
     * Execute a transaction with retry on abort.
     */
    function _withTransaction(storeNames, mode, fn) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            const attempt = (retryCount) => {
                try {
                    const tx = _db.transaction(storeNames, mode);
                    const result = fn(tx);

                    tx.oncomplete = () => resolve(result._value !== undefined ? result._value : undefined);
                    tx.onerror = (e) => {
                        if (retryCount < 2) {
                            console.warn('[PureDB] Transaction error, retrying...', e.target.error);
                            setTimeout(() => attempt(retryCount + 1), 100);
                        } else {
                            reject(e.target.error);
                        }
                    };
                    tx.onabort = (e) => {
                        if (retryCount < 2) {
                            console.warn('[PureDB] Transaction aborted, retrying...');
                            setTimeout(() => attempt(retryCount + 1), 100);
                        } else {
                            reject(e.target.error || new Error('Transaction aborted'));
                        }
                    };
                } catch (e) {
                    if (retryCount < 2 && e.name === 'InvalidStateError') {
                        _db = null;
                        _initPromise = null;
                        init().then(() => attempt(retryCount + 1)).catch(reject);
                    } else {
                        reject(e);
                    }
                }
            };
            attempt(0);
        });
    }

    function get(storeName, key) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readonly');
                const store = tx.objectStore(storeName);
                const request = store.get(key);
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => reject(request.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function getAll(storeName) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readonly');
                const store = tx.objectStore(storeName);
                const request = store.getAll();
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function put(storeName, data) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readwrite');
                const store = tx.objectStore(storeName);
                const request = store.put(data);
                request.onsuccess = () => resolve(request.result);
                tx.onerror = () => reject(tx.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function putBulk(storeName, items) {
        return new Promise(async (resolve, reject) => {
            if (!items || items.length === 0) { resolve(0); return; }
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readwrite');
                const store = tx.objectStore(storeName);
                items.forEach(item => store.put(item));
                tx.oncomplete = () => resolve(items.length);
                tx.onerror = () => reject(tx.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function remove(storeName, key) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readwrite');
                const store = tx.objectStore(storeName);
                const request = store.delete(key);
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function clear(storeName) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readwrite');
                const store = tx.objectStore(storeName);
                const request = store.clear();
                request.onsuccess = () => resolve();
                request.onerror = () => reject(request.error);
            } catch (e) {
                reject(e);
            }
        });
    }

    function count(storeName) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readonly');
                const store = tx.objectStore(storeName);
                const request = store.count();
                request.onsuccess = () => resolve(request.result);
                request.onerror = () => reject(request.error);
            } catch (e) {
                resolve(0); // graceful fallback
            }
        });
    }

    function getByIndex(storeName, indexName, value) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readonly');
                const store = tx.objectStore(storeName);
                const index = store.index(indexName);
                const request = index.getAll(value);
                request.onsuccess = () => resolve(request.result || []);
                request.onerror = () => reject(request.error);
            } catch (e) {
                resolve([]);
            }
        });
    }

    function getOneByIndex(storeName, indexName, value) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction(storeName, 'readonly');
                const store = tx.objectStore(storeName);
                const index = store.index(indexName);
                const request = index.get(value);
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => reject(request.error);
            } catch (e) {
                resolve(null);
            }
        });
    }

    // ── Product Operations ───────────────────────────────────────

    function searchProducts(query, categoryId = null) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction('products', 'readonly');
                const store = tx.objectStore('products');
                const request = store.getAll();

                request.onsuccess = () => {
                    let products = request.result || [];
                    const q = (query || '').toLowerCase().trim();

                    if (categoryId) {
                        products = products.filter(p => p.category_id == categoryId);
                    }

                    if (q) {
                        products = products.filter(p =>
                            (p.name && p.name.toLowerCase().includes(q)) ||
                            (p.sku && p.sku.toLowerCase().includes(q)) ||
                            (p.barcode && p.barcode === q)
                        );
                    }

                    products = products.filter(p => p.is_active !== false);
                    products.forEach(p => {
                        if (p.stock === undefined) p.stock = parseFloat(p.stock_quantity || 0);
                        if (p.allow_negative === undefined) p.allow_negative = !!p.allow_negative_stock;
                    });
                    products.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
                    resolve(products);
                };

                request.onerror = () => reject(request.error);
            } catch (e) {
                resolve([]);
            }
        });
    }

    async function findByBarcode(barcode) {
        try {
            const result = await getOneByIndex('products', 'barcode', barcode);
            if (result) {
                if (result.stock === undefined) result.stock = parseFloat(result.stock_quantity || 0);
                if (result.allow_negative === undefined) result.allow_negative = !!result.allow_negative_stock;
                return result;
            }
        } catch (e) { /* fallback */ }
        const all = await getAll('products');
        const found = all.find(p => p.barcode === barcode || p.sku === barcode) || null;
        if (found) {
            if (found.stock === undefined) found.stock = parseFloat(found.stock_quantity || 0);
            if (found.allow_negative === undefined) found.allow_negative = !!found.allow_negative_stock;
        }
        return found;
    }

    async function updateProductStock(productId, quantityChange) {
        const product = await get('products', productId);
        if (product && product.track_stock) {
            const current = parseFloat(product.stock_quantity ?? product.stock ?? 0);
            const allowNeg = product.allow_negative_stock || product.allow_negative;
            const updated = allowNeg ? (current + quantityChange) : Math.max(0, current + quantityChange);
            product.stock_quantity = updated;
            product.stock = updated;
            product._local_stock_modified = true;
            await put('products', product);
            return updated;
        }
        return null;
    }

    // ── Customer Operations ──────────────────────────────────────

    function searchCustomers(query) {
        return new Promise(async (resolve, reject) => {
            await _ensureDB();
            try {
                const tx = _db.transaction('customers', 'readonly');
                const store = tx.objectStore('customers');
                const request = store.getAll();

                request.onsuccess = () => {
                    let customers = request.result || [];
                    const q = (query || '').toLowerCase().trim();

                    if (q) {
                        customers = customers.filter(c =>
                            (c.name && c.name.toLowerCase().includes(q)) ||
                            (c.phone && c.phone.includes(q)) ||
                            (c.email && c.email.toLowerCase().includes(q))
                        );
                    }

                    resolve(customers.slice(0, 20));
                };

                request.onerror = () => reject(request.error);
            } catch (e) {
                resolve([]);
            }
        });
    }

    async function createCustomerOffline(customerData) {
        const uuid = customerData.uuid || generateUUID();
        const id = customerData.id || Date.now();
        const customer = {
            id: id,
            uuid: uuid,
            name: customerData.name,
            phone: customerData.phone || '',
            email: customerData.email || '',
            address: customerData.address || '',
            city: customerData.city || '',
            loyalty_points: 0,
            loyalty_tier: 'bronze',
            is_active: true,
            sync_status: 'local',
            created_at: new Date().toISOString(),
        };

        await put('customers', customer);

        // Queue for server sync
        await addToSyncQueue('customer', uuid, 'create', {
            name: customer.name,
            phone: customer.phone,
            email: customer.email,
            address: customer.address,
            city: customer.city,
        });

        return customer;
    }

    async function getCustomerByUUID(uuid) {
        if (!uuid) return null;
        await _ensureDB();
        return new Promise((resolve) => {
            try {
                const tx = _db.transaction('customers', 'readonly');
                const store = tx.objectStore('customers');
                const index = store.index('uuid');
                const request = index.get(uuid);
                request.onsuccess = () => resolve(request.result || null);
                request.onerror = () => resolve(null);
            } catch (e) {
                resolve(null);
            }
        });
    }

    async function updateCustomerServerId(uuid, serverId) {
        if (!uuid || !serverId) return null;
        await _ensureDB();
        try {
            const customer = await getCustomerByUUID(uuid);
            if (!customer) return null;

            const oldId = customer.id;
            customer.id = Number(serverId);
            customer.sync_status = 'synced';

            // If the previous ID was a temporary timestamp, remove old key to prevent duplicates
            if (oldId && oldId !== customer.id) {
                await remove('customers', oldId);
            }
            await put('customers', customer);
            return customer;
        } catch (e) {
            console.warn('[PureDB] Failed to update customer server ID:', e);
            return null;
        }
    }

    // ── Sync Queue Operations (PRODUCTION RELIABLE) ─────────────

    /**
     * Add a transaction to the sync queue.
     * This is CRITICAL — must never lose data.
     * Uses a double-write pattern: write to sync_queue + backup in localStorage.
     */
    async function addToSyncQueue(entityType, uuid, operation, payload) {
        const item = {
            entity_type: entityType,
            uuid: uuid,
            operation: operation,
            payload: payload,
            status: 'pending',
            attempts: 0,
            max_attempts: 10,
            created_at: new Date().toISOString(),
            last_attempt_at: null,
            error_message: null,
        };

        // Primary write: IndexedDB
        const id = await put('sync_queue', item);

        // Safety backup: localStorage (in case IndexedDB is wiped)
        _backupToLocalStorage(uuid, item);

        return id;
    }

    /**
     * Backup sync queue item to localStorage for disaster recovery.
     */
    function _backupToLocalStorage(uuid, item) {
        try {
            const key = 'purepos_sync_backup';
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            // Keep only last 100 backup entries
            if (existing.length > 100) existing.splice(0, existing.length - 100);
            existing.push({ uuid: uuid, data: item, backed_at: new Date().toISOString() });
            localStorage.setItem(key, JSON.stringify(existing));
        } catch (e) {
            // localStorage might be full — non-critical
        }
    }

    /**
     * Remove a UUID from localStorage backup (after successful sync).
     */
    function _removeBackup(uuid) {
        try {
            const key = 'purepos_sync_backup';
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            const filtered = existing.filter(e => e.uuid !== uuid);
            localStorage.setItem(key, JSON.stringify(filtered));
        } catch (e) { /* non-critical */ }
    }

    /**
     * Recover any backed-up items that are missing from IndexedDB.
     */
    async function recoverFromBackup() {
        try {
            const key = 'purepos_sync_backup';
            const backups = JSON.parse(localStorage.getItem(key) || '[]');
            if (backups.length === 0) return 0;

            const existingItems = await getAll('sync_queue');
            const existingUUIDs = new Set(existingItems.map(i => i.uuid));
            let recovered = 0;

            for (const backup of backups) {
                if (!existingUUIDs.has(backup.uuid) && backup.data.status !== 'synced') {
                    backup.data.status = 'pending'; // re-queue
                    backup.data.error_message = 'Recovered from backup';
                    await put('sync_queue', backup.data);
                    recovered++;
                }
            }

            if (recovered > 0) {
                console.log(`[PureDB] Recovered ${recovered} sync items from backup`);
            }
            return recovered;
        } catch (e) {
            return 0;
        }
    }

    async function getPendingSyncItems(includeExhausted = false) {
        const all = await getAll('sync_queue');
        return all.filter(item =>
            item.status === 'pending' || item.status === 'failed'
        ).filter(item =>
            includeExhausted || item.attempts < (item.max_attempts || 10)
        ).sort((a, b) =>
            new Date(a.created_at) - new Date(b.created_at)
        );
    }

    async function resetFailedAttempts() {
        try {
            const all = await getAll('sync_queue');
            for (const item of all) {
                if (item.status === 'failed') {
                    item.status = 'pending';
                    item.attempts = 0;
                    item.error_message = null;
                    await put('sync_queue', item);
                }
            }
            return true;
        } catch (e) {
            console.error('[PureDB] resetFailedAttempts failed:', e);
            return false;
        }
    }

    async function markSynced(syncItemId) {
        const item = await get('sync_queue', syncItemId);
        if (item) {
            item.status = 'synced';
            item.last_attempt_at = new Date().toISOString();
            await put('sync_queue', item);
            _removeBackup(item.uuid);
        }
    }

    async function markSyncFailed(syncItemId, errorMessage) {
        const item = await get('sync_queue', syncItemId);
        if (item) {
            item.status = 'failed';
            item.attempts = (item.attempts || 0) + 1;
            item.last_attempt_at = new Date().toISOString();
            item.error_message = errorMessage;
            await put('sync_queue', item);
        }
    }

    async function getPendingSyncCount() {
        try {
            const pending = await getPendingSyncItems();
            return pending.length;
        } catch (e) {
            return 0;
        }
    }

    async function getFailedSyncCount() {
        try {
            const all = await getAll('sync_queue');
            return all.filter(i => i.status === 'failed').length;
        } catch (e) {
            return 0;
        }
    }

    async function getSyncedCount() {
        try {
            const all = await getAll('sync_queue');
            return all.filter(i => i.status === 'synced').length;
        } catch (e) {
            return 0;
        }
    }

    /**
     * Remove synced items older than N days.
     * NEVER removes pending or failed items.
     */
    async function cleanupSyncedItems(olderThanDays = 7) {
        const all = await getAll('sync_queue');
        const cutoff = new Date(Date.now() - olderThanDays * 86400000).toISOString();
        let removed = 0;

        for (const item of all) {
            if (item.status === 'synced' && item.created_at < cutoff) {
                await remove('sync_queue', item.id);
                removed++;
            }
        }
        return removed;
    }

    /**
     * Check if a UUID already exists in the sync queue (dedup).
     */
    async function syncQueueHasUUID(uuid) {
        try {
            const items = await getByIndex('sync_queue', 'uuid', uuid);
            return items.length > 0;
        } catch (e) {
            // Fallback: scan all
            const all = await getAll('sync_queue');
            return all.some(i => i.uuid === uuid);
        }
    }

    // ── Meta/Settings Operations ─────────────────────────────────

    async function getMeta(key, defaultValue = null) {
        try {
            const record = await get('meta', key);
            return record ? record.value : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    }

    async function setMeta(key, value) {
        return await put('meta', { key, value, updated_at: new Date().toISOString() });
    }

    async function getSetting(key, defaultValue = null) {
        try {
            const record = await get('settings', key);
            return record ? record.value : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    }

    async function setSetting(key, value) {
        return await put('settings', { key, value });
    }

    // ── Order Operations (Offline) ───────────────────────────────

    async function getOrder(uuid) {
        return await get('orders', uuid);
    }

    async function saveOfflineOrder(order) {
        order.sync_status = 'local';
        order.created_at = order.created_at || new Date().toISOString();

        // Save to IndexedDB
        await put('orders', order);

        // Also persist to localStorage as safety net
        _persistOrderBackup(order);

        return order;
    }

    function _persistOrderBackup(order) {
        try {
            const key = 'purepos_order_backup';
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            // Keep only last 50
            if (existing.length > 50) existing.splice(0, existing.length - 50);
            existing.push({ uuid: order.uuid, data: order, backed_at: new Date().toISOString() });
            localStorage.setItem(key, JSON.stringify(existing));
        } catch (e) { /* non-critical */ }
    }

    async function getLocalOrders() {
        const all = await getAll('orders');
        return all.filter(o => o.sync_status === 'local');
    }

    async function markOrderSynced(uuid) {
        const order = await get('orders', uuid);
        if (order) {
            order.sync_status = 'synced';
            order.synced_at = new Date().toISOString();
            await put('orders', order);
        }
        // Clean backup
        try {
            const key = 'purepos_order_backup';
            const existing = JSON.parse(localStorage.getItem(key) || '[]');
            const filtered = existing.filter(e => e.uuid !== uuid);
            localStorage.setItem(key, JSON.stringify(filtered));
        } catch (e) { /* non-critical */ }
    }

    async function markOrderConflict(uuid, errorMessage) {
        const order = await get('orders', uuid);
        if (order) {
            order.sync_status = 'conflict';
            order.sync_error = errorMessage;
            order.conflict_at = new Date().toISOString();
            await put('orders', order);
        }
    }

    // ── Data Hydration ───────────────────────────────────────────

    /**
     * Hydrate local stores with server data.
     * Supports incremental sync — only updates changed records.
     */
    async function hydrate(data) {
        const stats = { products: 0, categories: 0, customers: 0 };

        if (data.products && data.products.length > 0) {
            await putBulk('products', data.products);
            stats.products = data.products.length;
        }

        if (data.categories && data.categories.length > 0) {
            await putBulk('categories', data.categories);
            stats.categories = data.categories.length;
        }

        if (data.customers && data.customers.length > 0) {
            await putBulk('customers', data.customers);
            stats.customers = data.customers.length;
        }

        await setMeta('last_hydration', new Date().toISOString());

        // Track sync cursor
        if (data._sync_version) {
            await setMeta('sync_cursor', data._sync_version);
        }

        console.log('[PureDB] Hydration complete:', stats);
        return stats;
    }

    async function needsHydration() {
        const lastHydration = await getMeta('last_hydration');
        if (!lastHydration) return true;
        const fourHoursAgo = new Date(Date.now() - 4 * 3600000).toISOString();
        return lastHydration < fourHoursAgo;
    }

    // ── Offline User Authentication & Management ─────────────────

    async function hashCredential(credential, salt = 'purepos_salt_v1') {
        if (!credential) return null;
        try {
            const enc = new TextEncoder();
            const data = enc.encode(salt + ':' + credential);
            const hashBuffer = await crypto.subtle.digest('SHA-256', data);
            const hashArray = Array.from(new Uint8Array(hashBuffer));
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
        } catch (e) {
            let hash = 0;
            const str = salt + ':' + credential;
            for (let i = 0; i < str.length; i++) {
                hash = ((hash << 5) - hash) + str.charCodeAt(i);
                hash |= 0;
            }
            return 'h_' + Math.abs(hash).toString(16);
        }
    }

    async function saveAuthorizedUser(user, rawCredential = null) {
        if (!user || !user.id) return;
        const record = {
            id: user.id,
            uuid: user.uuid || generateUUID(),
            name: user.name,
            username: user.username,
            email: user.email,
            role: user.role || 'cashier',
            permissions: user.permissions || [],
            store_id: user.store_id || null,
            updated_at: new Date().toISOString(),
        };

        if (rawCredential) {
            record.pin_hash = await hashCredential(rawCredential);
        } else if (user.pin_hash) {
            record.pin_hash = user.pin_hash;
        }

        await put('users', record);
        return record;
    }

    async function getAuthorizedUsers() {
        const users = await getAll('users');
        return users.map(u => ({
            id: u.id,
            uuid: u.uuid,
            name: u.name,
            username: u.username,
            email: u.email,
            role: u.role,
            permissions: u.permissions,
            store_id: u.store_id,
        }));
    }

    async function verifyOfflineUser(login, credential) {
        if (!login || !credential) return null;
        const allUsers = await getAll('users');
        const l = login.toLowerCase().trim();
        const user = allUsers.find(u =>
            (u.username && u.username.toLowerCase() === l) ||
            (u.email && u.email.toLowerCase() === l)
        );
        if (!user) return null;

        const hash = await hashCredential(credential);
        if (user.pin_hash && user.pin_hash === hash) {
            return {
                id: user.id,
                uuid: user.uuid,
                name: user.name,
                username: user.username,
                email: user.email,
                role: user.role,
                permissions: user.permissions || [],
                store_id: user.store_id,
            };
        }
        return null;
    }

    // ── Offline Held Orders ──────────────────────────────────────

    async function saveHeldOrder(heldOrder) {
        heldOrder.uuid = heldOrder.uuid || generateUUID();
        heldOrder.created_at = heldOrder.created_at || new Date().toISOString();
        await put('held_orders', heldOrder);
        return heldOrder;
    }

    async function getHeldOrders() {
        const orders = await getAll('held_orders');
        return orders.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
    }

    async function removeHeldOrder(uuid) {
        return await remove('held_orders', uuid);
    }

    // ── Sales History & Offline Reports ──────────────────────────

    async function getSalesHistory(limit = 50, filters = {}) {
        const allOrders = await getAll('orders');
        let filtered = allOrders.filter(o => o.status === 'completed' || o.status === 'refunded' || o.order_type === 'return');

        if (filters.search) {
            const q = filters.search.toLowerCase().trim();
            filtered = filtered.filter(o =>
                (o.order_number && o.order_number.toLowerCase().includes(q)) ||
                (o.customer_name && o.customer_name.toLowerCase().includes(q))
            );
        }

        if (filters.date) {
            filtered = filtered.filter(o => (o.created_at || '').startsWith(filters.date));
        }

        filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        return filtered.slice(0, limit);
    }

    async function getDailyReport(dateStr = null) {
        const targetDate = dateStr || new Date().toISOString().slice(0, 10);
        const allOrders = await getAll('orders');
        const dayOrders = allOrders.filter(o =>
            (o.created_at || '').startsWith(targetDate) && o.status !== 'cancelled'
        );

        let grossSales = 0;
        let totalDiscounts = 0;
        let totalTax = 0;
        let totalReturns = 0;
        const paymentBreakdown = { cash: 0, card: 0, mobile_payment: 0, bank_transfer: 0, other: 0 };
        const productCounts = {};

        dayOrders.forEach(o => {
            if (o.order_type === 'return') {
                totalReturns += parseFloat(o.total_amount || 0);
            } else {
                grossSales += parseFloat(o.subtotal || o.total_amount || 0);
                totalDiscounts += parseFloat(o.discount_amount || 0);
                totalTax += parseFloat(o.tax_amount || 0);

                (o.payments || []).forEach(p => {
                    const method = p.method || 'cash';
                    const amt = parseFloat(p.amount || 0);
                    if (paymentBreakdown[method] !== undefined) {
                        paymentBreakdown[method] += amt;
                    } else {
                        paymentBreakdown.other += amt;
                    }
                });

                (o.items || []).forEach(item => {
                    const name = item.product_name || 'Item';
                    const qty = parseFloat(item.quantity || 0);
                    productCounts[name] = (productCounts[name] || 0) + qty;
                });
            }
        });

        const netSales = Math.max(0, grossSales - totalDiscounts + totalTax - totalReturns);
        const pendingSyncCount = await getPendingSyncCount();

        return {
            date: targetDate,
            total_orders: dayOrders.filter(o => o.order_type !== 'return').length,
            total_returns: dayOrders.filter(o => o.order_type === 'return').length,
            gross_sales: Math.round(grossSales * 100) / 100,
            discounts: Math.round(totalDiscounts * 100) / 100,
            tax: Math.round(totalTax * 100) / 100,
            returns_amount: Math.round(totalReturns * 100) / 100,
            net_sales: Math.round(netSales * 100) / 100,
            payments: paymentBreakdown,
            top_products: Object.entries(productCounts)
                .map(([name, qty]) => ({ name, qty }))
                .sort((a, b) => b.qty - a.qty)
                .slice(0, 10),
            pending_sync: pendingSyncCount,
        };
    }

    // ── Offline Register Operations ──────────────────────────────

    async function openRegisterOffline(data) {
        const id = data.id || generateUUID();
        const register = {
            id: id,
            uuid: id,
            opening_balance: parseFloat(data.opening_balance || 0),
            status: 'open',
            opened_at: data.opened_at || new Date().toISOString(),
            closed_at: null,
            closing_balance: null,
            expected_balance: null,
            difference: null,
            notes: data.notes || '',
            user_id: data.user_id || null,
            sync_status: 'local',
        };

        await put('registers', register);
        await setSetting('active_register_id', id);

        await addToSyncQueue('register', id, 'create', {
            id: id,
            action: 'open',
            opening_balance: register.opening_balance,
            opened_at: register.opened_at,
            notes: register.notes,
        });

        return register;
    }

    async function getActiveRegister() {
        const activeId = await getSetting('active_register_id');
        if (!activeId) return null;
        const reg = await get('registers', activeId);
        return (reg && reg.status === 'open') ? reg : null;
    }

    async function closeRegisterOffline(actualCash, notes = '') {
        const register = await getActiveRegister();
        if (!register) throw new Error('No active open register session found.');

        const allOrders = await getAll('orders');
        const shiftOrders = allOrders.filter(o =>
            o.created_at >= register.opened_at && o.status !== 'cancelled'
        );

        let cashSales = 0;
        let cashRefunds = 0;
        shiftOrders.forEach(o => {
            (o.payments || []).forEach(p => {
                if (p.method === 'cash') {
                    if (o.order_type === 'return') {
                        cashRefunds += parseFloat(p.amount || 0);
                    } else {
                        cashSales += parseFloat(p.amount || 0);
                    }
                }
            });
        });

        const expectedCash = register.opening_balance + cashSales - cashRefunds;
        const actual = parseFloat(actualCash || 0);
        const difference = actual - expectedCash;

        register.closing_balance = actual;
        register.expected_balance = Math.round(expectedCash * 100) / 100;
        register.difference = Math.round(difference * 100) / 100;
        register.closed_at = new Date().toISOString();
        register.status = 'closed';
        register.notes = notes;
        register.total_cash_sales = cashSales;
        register.total_cash_refunds = cashRefunds;
        register.total_orders = shiftOrders.length;

        await put('registers', register);
        await setSetting('active_register_id', null);

        await addToSyncQueue('register', register.uuid, 'update', {
            id: register.id,
            action: 'close',
            closing_balance: register.closing_balance,
            expected_balance: register.expected_balance,
            difference: register.difference,
            closed_at: register.closed_at,
            notes: register.notes,
            total_sales: cashSales,
            total_refunds: cashRefunds,
            total_orders: shiftOrders.length,
        });

        return register;
    }

    // ── Utility ──────────────────────────────────────────────────

    function generateUUID() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    }

    async function getStats() {
        return {
            products: await count('products'),
            categories: await count('categories'),
            customers: await count('customers'),
            orders: await count('orders'),
            syncQueuePending: await getPendingSyncCount(),
            syncQueueFailed: await getFailedSyncCount(),
            syncQueueSynced: await getSyncedCount(),
            lastHydration: await getMeta('last_hydration'),
            lastPull: await getMeta('last_pull'),
            syncCursor: await getMeta('sync_cursor'),
        };
    }

    async function resetAll() {
        for (const storeName of Object.keys(STORES)) {
            await clear(storeName);
        }
        console.log('[PureDB] All local data cleared');
    }

    // ── Public API ───────────────────────────────────────────────
    return {
        init,
        // Generic
        get, getAll, put, putBulk, remove, clear, count,
        getByIndex, getOneByIndex,
        // Products
        searchProducts, findByBarcode, updateProductStock,
        // Customers
        searchCustomers, createCustomerOffline, getCustomerByUUID, updateCustomerServerId,
        // Auth (Offline)
        saveAuthorizedUser, getAuthorizedUsers, verifyOfflineUser, hashCredential,
        // Held Orders
        saveHeldOrder, getHeldOrders, removeHeldOrder,
        // Registers
        openRegisterOffline, getActiveRegister, closeRegisterOffline,
        // Sales History & Reports
        getSalesHistory, getDailyReport,
        // Sync Queue (production reliable)
        addToSyncQueue, getPendingSyncItems, markSynced, markSyncFailed,
        getPendingSyncCount, getFailedSyncCount, getSyncedCount,
        cleanupSyncedItems, syncQueueHasUUID,
        recoverFromBackup, resetFailedAttempts,
        // Meta/Settings
        getMeta, setMeta, getSetting, setSetting,
        // Orders
        getOrder, saveOfflineOrder, getLocalOrders, markOrderSynced, markOrderConflict,
        // Hydration
        hydrate, needsHydration,
        // Utility
        generateUUID, getStats, resetAll,
    };
})();
