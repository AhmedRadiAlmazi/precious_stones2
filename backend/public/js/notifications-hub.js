/**
 * Jawhara Smart Notifications Hub
 * ─────────────────────────────────────────────────────────────────────────────
 * Handles:
 *  • Polling-based unread count refresh (every 30s when logged in)
 *  • Browser Web Push permission request & display
 *  • Bell icon badge counter in header
 *  • Slide-in notification drawer (desktop + mobile)
 *  • Toast pop-ups for incoming alerts
 *  • Mark-as-read, mark-all-read, delete
 * ─────────────────────────────────────────────────────────────────────────────
 */

(function () {
    'use strict';

    /* ================================================================
       CONFIG
    ================================================================ */
    const POLL_INTERVAL_MS  = 30_000;   // 30 seconds
    const TOAST_DURATION_MS = 7_000;
    const MAX_DRAWER_ITEMS  = 30;

    /* ================================================================
       STATE
    ================================================================ */
    let pollingTimer      = null;
    let lastKnownIds      = new Set();  // IDs already seen (to detect new ones)
    let drawerOpen        = false;
    let drawerLoaded      = false;
    let pushPermission    = Notification.permission; // native browser API

    /* ================================================================
       DOM – inject hub elements once DOM is ready
    ================================================================ */
    function injectDOM() {
        // ── Bell button (replaces / wraps existing bell in header) ──────────
        const existingBell = document.getElementById('notif-bell-btn');
        if (existingBell) {
            existingBell.style.position = 'relative';
            if (!existingBell.querySelector('.notif-badge')) {
                const badge = document.createElement('span');
                badge.id        = 'notif-badge';
                badge.className = 'notif-badge';
                badge.style.cssText = `
                    display:none; position:absolute; top:-4px; right:-4px;
                    background:#ef4444; color:#fff; font-size:0.6rem; font-weight:800;
                    width:18px; height:18px; border-radius:50%; align-items:center;
                    justify-content:center; border:2px solid var(--bg-secondary,#1a1a1a);
                    z-index:10;`;
                existingBell.appendChild(badge);
            }
            existingBell.addEventListener('click', toggleDrawer);
        }

        // ── Notification Drawer ─────────────────────────────────────────────
        if (!document.getElementById('notif-drawer')) {
            document.body.insertAdjacentHTML('beforeend', `
            <div id="notif-drawer" aria-label="الإشعارات" role="dialog" aria-modal="true">
                <div id="notif-drawer-overlay"></div>
                <div id="notif-drawer-panel">
                    <!-- Header -->
                    <div class="notif-drawer-head">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-bell text-gold"></i>
                            <h2 class="notif-drawer-title">الإشعارات</h2>
                            <span id="notif-drawer-badge" class="notif-drawer-badge hidden">0</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="notif-read-all-btn" title="تحديد الكل كمقروء" class="notif-head-btn">
                                <i class="fas fa-check-double"></i>
                            </button>
                            <button id="notif-settings-btn" title="الإعدادات" class="notif-head-btn" onclick="window.location='/notifications'">
                                <i class="fas fa-cog"></i>
                            </button>
                            <button id="notif-close-btn" title="إغلاق" class="notif-head-btn notif-close-x">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Tabs -->
                    <div class="notif-tabs">
                        <button class="notif-tab active" data-filter="all">الكل</button>
                        <button class="notif-tab" data-filter="outbid">المزايدات</button>
                        <button class="notif-tab" data-filter="match_alert">التوصيات</button>
                    </div>

                    <!-- List -->
                    <div id="notif-list" class="notif-list">
                        <div class="notif-empty" id="notif-empty-state">
                            <i class="fas fa-bell-slash text-3xl mb-3" style="color:rgba(212,175,55,0.3)"></i>
                            <p>لا توجد إشعارات حالياً</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="notif-drawer-foot">
                        <a href="/notifications" class="notif-view-all">
                            عرض كل الإشعارات <i class="fas fa-arrow-left mr-1"></i>
                        </a>
                    </div>
                </div>
            </div>
            `);

            document.getElementById('notif-drawer-overlay').addEventListener('click', closeDrawer);
            document.getElementById('notif-close-btn').addEventListener('click', closeDrawer);
            document.getElementById('notif-read-all-btn').addEventListener('click', markAllRead);

            // Tab switching
            document.querySelectorAll('.notif-tab').forEach(tab => {
                tab.addEventListener('click', () => {
                    document.querySelectorAll('.notif-tab').forEach(t => t.classList.remove('active'));
                    tab.classList.add('active');
                    currentFilter = tab.dataset.filter;
                    renderDrawerItems();
                });
            });
        }

        // ── Toast Container ──────────────────────────────────────────────────
        if (!document.getElementById('notif-toast-container')) {
            const tc = document.createElement('div');
            tc.id = 'notif-toast-container';
            tc.style.cssText = `
                position:fixed; top:80px; left:20px; z-index:99999;
                display:flex; flex-direction:column; gap:10px;
                max-width:360px; width:calc(100vw - 40px);`;
            document.body.appendChild(tc);
        }

        injectStyles();
    }

    /* ================================================================
       STYLES (injected once)
    ================================================================ */
    function injectStyles() {
        if (document.getElementById('notif-hub-styles')) return;
        const s = document.createElement('style');
        s.id = 'notif-hub-styles';
        s.textContent = `
            /* Drawer */
            #notif-drawer { display:none; position:fixed; inset:0; z-index:9000; }
            #notif-drawer.open { display:block; }
            #notif-drawer-overlay {
                position:absolute; inset:0;
                background:rgba(0,0,0,0.55); backdrop-filter:blur(2px);
                animation:notifOverlayIn .25s ease;
            }
            @keyframes notifOverlayIn { from{opacity:0} to{opacity:1} }
            #notif-drawer-panel {
                position:absolute; top:0; left:0; bottom:0;
                width:min(420px,100vw);
                background:var(--bg-secondary,#111);
                border-right:1px solid rgba(212,175,55,0.25);
                display:flex; flex-direction:column;
                animation:notifPanelIn .3s cubic-bezier(0.4,0,0.2,1);
                box-shadow: 4px 0 40px rgba(0,0,0,0.5);
            }
            @keyframes notifPanelIn { from{transform:translateX(-100%)} to{transform:translateX(0)} }

            .notif-drawer-head {
                padding:18px 20px; display:flex; justify-content:space-between; align-items:center;
                border-bottom:1px solid rgba(212,175,55,0.15);
                background:linear-gradient(135deg,rgba(212,175,55,0.06),transparent);
                flex-shrink:0;
            }
            .notif-drawer-title { font-size:1.1rem; font-weight:800; color:var(--text-primary,#fff); }
            .notif-drawer-badge {
                background:#ef4444; color:#fff; font-size:0.65rem; font-weight:800;
                padding:2px 6px; border-radius:10px;
            }
            .notif-drawer-badge.hidden { display:none; }
            .notif-head-btn {
                width:32px; height:32px; border-radius:8px; border:none;
                background:rgba(255,255,255,0.06); color:var(--text-secondary,#aaa);
                cursor:pointer; display:flex; align-items:center; justify-content:center;
                font-size:0.85rem; transition:all .2s;
            }
            .notif-head-btn:hover { background:rgba(212,175,55,0.15); color:#D4AF37; }
            .notif-close-x:hover { background:rgba(239,68,68,0.15); color:#ef4444; }

            /* Tabs */
            .notif-tabs {
                display:flex; gap:4px; padding:10px 16px;
                border-bottom:1px solid rgba(255,255,255,0.05); flex-shrink:0;
            }
            .notif-tab {
                padding:5px 14px; border-radius:20px; border:none;
                background:transparent; color:var(--text-secondary,#aaa);
                font-size:0.78rem; cursor:pointer; font-weight:600;
                transition:all .2s; font-family:inherit;
            }
            .notif-tab.active { background:rgba(212,175,55,0.15); color:#D4AF37; }
            .notif-tab:hover:not(.active) { background:rgba(255,255,255,0.06); }

            /* List */
            .notif-list {
                flex:1; overflow-y:auto; padding:8px 0;
            }
            .notif-list::-webkit-scrollbar { width:4px; }
            .notif-list::-webkit-scrollbar-thumb { background:#D4AF37; border-radius:2px; }

            /* Item */
            .notif-item {
                display:flex; gap:12px; padding:14px 18px;
                border-bottom:1px solid rgba(255,255,255,0.04);
                cursor:pointer; transition:background .2s;
                position:relative; align-items:flex-start;
                animation:notifItemIn .3s ease;
            }
            @keyframes notifItemIn { from{opacity:0;transform:translateX(-12px)} to{opacity:1;transform:translateX(0)} }
            .notif-item:hover { background:rgba(255,255,255,0.04); }
            .notif-item.unread { background:rgba(212,175,55,0.04); }
            .notif-item.unread::before {
                content:''; position:absolute; right:0; top:0; bottom:0;
                width:3px; background:#D4AF37; border-radius:0 2px 2px 0;
            }

            .notif-icon-wrap {
                width:40px; height:40px; border-radius:12px; flex-shrink:0;
                display:flex; align-items:center; justify-content:center;
                font-size:1rem;
            }
            .notif-item-body { flex:1; min-width:0; }
            .notif-item-title { font-size:0.82rem; font-weight:700; color:var(--text-primary,#fff); margin-bottom:3px; }
            .notif-item-text  { font-size:0.75rem; color:var(--text-secondary,#aaa); line-height:1.5; }
            .notif-item-time  { font-size:0.68rem; color:rgba(255,255,255,0.3); margin-top:5px; }
            .notif-item-actions { display:flex; gap:6px; margin-top:8px; }
            .notif-item-btn {
                font-size:0.7rem; padding:4px 10px; border-radius:6px; border:none;
                cursor:pointer; font-weight:600; transition:all .2s;
                font-family:inherit;
            }
            .notif-item-btn-primary {
                background:linear-gradient(135deg,#D4AF37,#B8860B); color:#000;
            }
            .notif-item-btn-secondary {
                background:rgba(255,255,255,0.06); color:var(--text-secondary,#aaa);
            }
            .notif-item-btn:hover { opacity:.85; }

            .notif-delete-btn {
                position:absolute; top:8px; left:8px;
                width:22px; height:22px; border-radius:50%; border:none;
                background:rgba(255,255,255,0.05); color:rgba(255,255,255,0.3);
                font-size:0.55rem; cursor:pointer; display:none;
                align-items:center; justify-content:center;
                transition:all .2s;
            }
            .notif-item:hover .notif-delete-btn { display:flex; }
            .notif-delete-btn:hover { background:rgba(239,68,68,0.2); color:#ef4444; }

            .notif-empty {
                text-align:center; padding:60px 20px;
                color:var(--text-secondary,#aaa); font-size:0.85rem;
            }

            /* Footer */
            .notif-drawer-foot {
                padding:14px 20px; border-top:1px solid rgba(255,255,255,0.06);
                flex-shrink:0;
            }
            .notif-view-all {
                display:block; text-align:center; color:#D4AF37;
                font-size:0.8rem; font-weight:700; text-decoration:none;
            }
            .notif-view-all:hover { text-decoration:underline; }

            /* Toast */
            .notif-toast {
                background:var(--bg-secondary,#1a1a1a);
                border:1px solid rgba(212,175,55,0.25);
                border-radius:16px; padding:14px 16px;
                display:flex; gap:12px; align-items:flex-start;
                box-shadow:0 8px 30px rgba(0,0,0,0.5);
                animation:toastIn .4s cubic-bezier(0.4,0,0.2,1);
                cursor:pointer; position:relative;
                overflow:hidden;
            }
            .notif-toast::before {
                content:''; position:absolute; bottom:0; left:0;
                height:3px; background:linear-gradient(90deg,#D4AF37,#B8860B);
                animation:toastTimer linear forwards;
            }
            @keyframes toastIn { from{opacity:0;transform:translateX(-24px)} to{opacity:1;transform:translateX(0)} }
            @keyframes toastOut { to{opacity:0;transform:translateX(-24px)} }
            @keyframes toastTimer { from{width:100%} to{width:0%} }
            .notif-toast-icon {
                width:38px; height:38px; border-radius:10px; flex-shrink:0;
                display:flex; align-items:center; justify-content:center;
                font-size:1rem;
            }
            .notif-toast-title { font-size:0.82rem; font-weight:800; margin-bottom:3px; }
            .notif-toast-text  { font-size:0.74rem; color:var(--text-secondary,#aaa); line-height:1.5; }
            .notif-toast-close {
                position:absolute; top:8px; left:8px;
                background:none; border:none; color:rgba(255,255,255,0.3);
                font-size:0.75rem; cursor:pointer; transition:color .2s;
                width:20px; height:20px; display:flex; align-items:center; justify-content:center;
            }
            .notif-toast-close:hover { color:#ef4444; }

            /* Push permission banner */
            #push-permission-banner {
                display:none; position:fixed; bottom:20px; right:20px;
                background:var(--bg-secondary,#1a1a1a);
                border:1px solid rgba(212,175,55,0.3);
                border-radius:16px; padding:16px 20px;
                box-shadow:0 8px 30px rgba(0,0,0,0.5);
                z-index:8999; max-width:320px;
                animation:notifItemIn .4s ease;
            }
        `;
        document.head.appendChild(s);
    }

    /* ================================================================
       DATA
    ================================================================ */
    let allNotifications = [];
    let currentFilter    = 'all';

    async function fetchUnreadCount() {
        if (!api || !api.getToken()) return;
        try {
            const res = await api.request('/notifications/unread-count');
            if (!res?.success) return;

            updateBadge(res.unread_count);

            // Detect brand-new notifications
            const newOnes = (res.latest || []).filter(n => !lastKnownIds.has(n.id));
            newOnes.forEach(n => {
                showToast(n);
                lastKnownIds.add(n.id);
                // Show browser push if permission granted
                if (pushPermission === 'granted') {
                    showBrowserPush(n);
                }
            });
        } catch (_) { /* silent */ }
    }

    async function fetchAll() {
        if (!api || !api.getToken()) return;
        try {
            const res = await api.request('/notifications');
            if (!res?.success) return;
            allNotifications = res.data || [];
            allNotifications.forEach(n => lastKnownIds.add(n.id));
            updateBadge(res.unread_count);
            renderDrawerItems();
        } catch (_) { /* silent */ }
    }

    /* ================================================================
       BADGE
    ================================================================ */
    function updateBadge(count) {
        const badge    = document.getElementById('notif-badge');
        const dbBadge  = document.getElementById('notif-drawer-badge');
        const display  = count > 0 ? String(count > 99 ? '99+' : count) : '';

        if (badge) {
            badge.textContent = display;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
        if (dbBadge) {
            dbBadge.textContent = display;
            dbBadge.classList.toggle('hidden', count === 0);
        }
    }

    /* ================================================================
       DRAWER
    ================================================================ */
    function toggleDrawer() {
        drawerOpen ? closeDrawer() : openDrawer();
    }

    function openDrawer() {
        const drawer = document.getElementById('notif-drawer');
        if (!drawer) return;
        drawer.classList.add('open');
        drawerOpen = true;
        document.body.style.overflow = 'hidden';
        if (!drawerLoaded) {
            drawerLoaded = true;
            fetchAll();
        }
    }

    function closeDrawer() {
        const drawer = document.getElementById('notif-drawer');
        if (!drawer) return;
        drawer.classList.remove('open');
        drawerOpen = false;
        document.body.style.overflow = '';
    }

    function renderDrawerItems() {
        const list = document.getElementById('notif-list');
        const empty = document.getElementById('notif-empty-state');
        if (!list) return;

        const filtered = currentFilter === 'all'
            ? allNotifications
            : allNotifications.filter(n => n.type === currentFilter);

        if (filtered.length === 0) {
            empty.style.display = 'block';
            list.querySelectorAll('.notif-item').forEach(el => el.remove());
            return;
        }

        empty.style.display = 'none';
        list.innerHTML = '';

        filtered.slice(0, MAX_DRAWER_ITEMS).forEach(n => {
            list.insertAdjacentHTML('beforeend', buildItemHTML(n));
        });

        // Attach events
        list.querySelectorAll('.notif-item[data-id]').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target.closest('.notif-delete-btn, .notif-item-btn')) return;
                handleItemClick(parseInt(el.dataset.id));
            });
        });
        list.querySelectorAll('.notif-delete-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                deleteNotification(parseInt(btn.dataset.id));
            });
        });
        list.querySelectorAll('.notif-action-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const url = btn.dataset.url;
                if (url) window.location.href = url;
            });
        });
    }

    function buildItemHTML(n) {
        const bg    = n.color ? hexToRgba(n.color, 0.12) : 'rgba(212,175,55,0.12)';
        const isUnread = !n.is_read ? 'unread' : '';
        const actionBtn = n.action_url
            ? `<button class="notif-item-btn notif-item-btn-primary notif-action-btn" data-url="${n.action_url}">عرض التفاصيل</button>`
            : '';

        return `
        <div class="notif-item ${isUnread}" data-id="${n.id}">
            <div class="notif-icon-wrap" style="background:${bg}; color:${n.color || '#D4AF37'}">
                <i class="${n.icon || 'fas fa-bell'}"></i>
            </div>
            <div class="notif-item-body">
                <div class="notif-item-title">${n.title}</div>
                <div class="notif-item-text">${n.body}</div>
                <div class="notif-item-time"><i class="far fa-clock" style="font-size:0.6rem;"></i> ${n.created_at}</div>
                ${actionBtn ? `<div class="notif-item-actions">${actionBtn}</div>` : ''}
            </div>
            <button class="notif-delete-btn" data-id="${n.id}" title="حذف"><i class="fas fa-times"></i></button>
        </div>`;
    }

    async function handleItemClick(id) {
        const n = allNotifications.find(x => x.id === id);
        if (!n) return;
        if (!n.is_read) {
            await markRead(id);
        }
        if (n.action_url) {
            window.location.href = n.action_url;
        }
    }

    async function markRead(id) {
        try {
            await api.request(`/notifications/${id}/read`, { method: 'PUT' });
            const n = allNotifications.find(x => x.id === id);
            if (n) n.is_read = true;
            const el = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (el) el.classList.remove('unread');
            const unread = allNotifications.filter(x => !x.is_read).length;
            updateBadge(unread);
        } catch (_) {}
    }

    async function markAllRead() {
        try {
            await api.request('/notifications/read-all', { method: 'PUT' });
            allNotifications.forEach(n => n.is_read = true);
            document.querySelectorAll('.notif-item.unread').forEach(el => el.classList.remove('unread'));
            updateBadge(0);
        } catch (_) {}
    }

    async function deleteNotification(id) {
        try {
            await api.request(`/notifications/${id}`, { method: 'DELETE' });
            allNotifications = allNotifications.filter(x => x.id !== id);
            lastKnownIds.delete(id);
            const el = document.querySelector(`.notif-item[data-id="${id}"]`);
            if (el) {
                el.style.animation = 'toastOut .3s ease forwards';
                setTimeout(() => el.remove(), 300);
            }
            const unread = allNotifications.filter(x => !x.is_read).length;
            updateBadge(unread);
            if (allNotifications.length === 0) {
                document.getElementById('notif-empty-state').style.display = 'block';
            }
        } catch (_) {}
    }

    /* ================================================================
       TOAST
    ================================================================ */
    function showToast(n) {
        const container = document.getElementById('notif-toast-container');
        if (!container) return;

        const bg  = n.color ? hexToRgba(n.color, 0.12) : 'rgba(212,175,55,0.12)';
        const dur = TOAST_DURATION_MS;

        const toast = document.createElement('div');
        toast.className = 'notif-toast';
        toast.innerHTML = `
            <div class="notif-toast-icon" style="background:${bg}; color:${n.color || '#D4AF37'}">
                <i class="${n.icon || 'fas fa-bell'}"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="notif-toast-title" style="color:${n.color || '#D4AF37'}">${n.title}</div>
                <div class="notif-toast-text">${n.body}</div>
            </div>
            <button class="notif-toast-close"><i class="fas fa-times"></i></button>
        `;
        toast.style.setProperty('--toast-dur', dur + 'ms');
        toast.querySelector('::before') ; // just reference
        toast.querySelector('.notif-toast-close').addEventListener('click', () => dismissToast(toast));
        toast.addEventListener('click', (e) => {
            if (e.target.closest('.notif-toast-close')) return;
            dismissToast(toast);
            if (n.action_url) window.location.href = n.action_url;
        });

        // Progress bar
        const bar = document.createElement('div');
        bar.style.cssText = `
            position:absolute; bottom:0; left:0; height:3px;
            background:linear-gradient(90deg,${n.color || '#D4AF37'},${n.color || '#B8860B'});
            width:100%; transition:width ${dur}ms linear;`;
        toast.style.position = 'relative';
        toast.appendChild(bar);
        container.prepend(toast);
        requestAnimationFrame(() => { bar.style.width = '0%'; });

        const timer = setTimeout(() => dismissToast(toast), dur);
        toast.addEventListener('mouseenter', () => clearTimeout(timer));
    }

    function dismissToast(toast) {
        toast.style.animation = 'toastOut .35s ease forwards';
        setTimeout(() => toast.remove(), 380);
    }

    /* ================================================================
       BROWSER WEB PUSH
    ================================================================ */
    function showBrowserPush(n) {
        try {
            new window.Notification(n.title, {
                body: n.body,
                icon: '/favicon.ico',
                tag:  'jawhara-notif-' + n.id,
            });
        } catch (_) {}
    }

    function requestPushPermission() {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted') return;
        if (Notification.permission === 'denied') return;

        // Show a polite banner instead of immediately prompting
        showPushBanner();
    }

    function showPushBanner() {
        if (document.getElementById('push-permission-banner')) return;
        document.body.insertAdjacentHTML('beforeend', `
        <div id="push-permission-banner">
            <div style="display:flex;gap:10px;align-items:flex-start;margin-bottom:12px;">
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(212,175,55,0.12);
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#D4AF37;">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <p style="font-size:0.82rem;font-weight:700;color:var(--text-primary,#fff);margin-bottom:4px;">تفعيل الإشعارات الفورية</p>
                    <p style="font-size:0.73rem;color:var(--text-secondary,#aaa);">احصل على تنبيهات فورية عند تجاوز مزايدتك أو توفر حجر يناسب اهتماماتك.</p>
                </div>
            </div>
            <div style="display:flex;gap:8px;">
                <button id="push-allow-btn" style="flex:1;padding:8px;border-radius:8px;border:none;
                    background:linear-gradient(135deg,#D4AF37,#B8860B);color:#000;font-weight:700;
                    font-size:0.78rem;cursor:pointer;font-family:inherit;">
                    السماح بالإشعارات
                </button>
                <button id="push-deny-btn" style="padding:8px 12px;border-radius:8px;border:none;
                    background:rgba(255,255,255,0.06);color:var(--text-secondary,#aaa);
                    font-size:0.78rem;cursor:pointer;font-family:inherit;">
                    لاحقاً
                </button>
            </div>
        </div>`);

        document.getElementById('push-allow-btn').addEventListener('click', async () => {
            const permission = await Notification.requestPermission();
            pushPermission = permission;
            document.getElementById('push-permission-banner')?.remove();
        });
        document.getElementById('push-deny-btn').addEventListener('click', () => {
            document.getElementById('push-permission-banner')?.remove();
        });
    }

    /* ================================================================
       POLLING
    ================================================================ */
    function startPolling() {
        if (pollingTimer) return;
        fetchUnreadCount(); // immediate first call
        pollingTimer = setInterval(fetchUnreadCount, POLL_INTERVAL_MS);
    }

    function stopPolling() {
        clearInterval(pollingTimer);
        pollingTimer = null;
    }

    /* ================================================================
       HELPERS
    ================================================================ */
    function hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1,3), 16);
        const g = parseInt(hex.slice(3,5), 16);
        const b = parseInt(hex.slice(5,7), 16);
        return `rgba(${r},${g},${b},${alpha})`;
    }

    /* ================================================================
       INIT
    ================================================================ */
    function init() {
        injectDOM();

        // Only start polling if user is logged in
        if (typeof api !== 'undefined' && api.getToken()) {
            startPolling();
            // Politely ask for push permission after 5 seconds
            setTimeout(requestPushPermission, 5000);
        }
    }

    // Boot on DOMContentLoaded or immediately if already ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // Expose for external use
    window.jawharaNotifications = {
        open:         openDrawer,
        close:        closeDrawer,
        refresh:      fetchAll,
        showToast:    showToast,
        startPolling: startPolling,
        stopPolling:  stopPolling,
    };

})();
