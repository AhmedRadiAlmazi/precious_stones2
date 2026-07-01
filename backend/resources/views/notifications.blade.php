@extends('layouts.app')

@section('title', 'مركز الإشعارات | جوهرة')

@section('styles')
<style>
    .notif-page-hero {
        background: radial-gradient(ellipse at 60% 30%, rgba(212,175,55,0.08) 0%, transparent 60%),
                    var(--bg-primary);
        border-bottom: 1px solid rgba(212,175,55,0.12);
        padding: 3rem 0 2rem;
    }

    /* Filter chips */
    .filter-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 18px; border-radius: 50px;
        border: 1.5px solid var(--border-color);
        background: var(--bg-secondary);
        font-size: 0.8rem; font-weight: 600;
        cursor: pointer; transition: all .25s;
        color: var(--text-secondary);
    }
    .filter-chip:hover, .filter-chip.active {
        border-color: #D4AF37;
        background: rgba(212,175,55,0.1);
        color: #D4AF37;
    }
    .filter-chip .chip-count {
        background: rgba(212,175,55,0.2);
        color: #D4AF37;
        font-size: 0.65rem; font-weight: 800;
        padding: 1px 6px; border-radius: 10px;
    }

    /* Notification card */
    .ncard {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 18px 20px;
        display: flex; gap: 14px; align-items: flex-start;
        cursor: pointer;
        transition: all .25s;
        position: relative; overflow: hidden;
        animation: fadeInCard .4s ease both;
    }
    @keyframes fadeInCard {
        from { opacity:0; transform:translateY(10px); }
        to   { opacity:1; transform:translateY(0); }
    }
    .ncard:hover { border-color: rgba(212,175,55,0.3); transform: translateY(-2px); }
    .ncard.unread {
        background: linear-gradient(135deg, rgba(212,175,55,0.05), var(--bg-secondary));
        border-color: rgba(212,175,55,0.2);
    }
    .ncard.unread::before {
        content: ''; position:absolute; right:0; top:0; bottom:0;
        width: 4px; background: linear-gradient(to bottom, #D4AF37, #B8860B);
        border-radius: 0 16px 16px 0;
    }

    .ncard-icon {
        width: 48px; height: 48px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .ncard-body { flex: 1; min-width: 0; }
    .ncard-title { font-size: 0.9rem; font-weight: 700; margin-bottom: 5px; }
    .ncard-text  { font-size: 0.8rem; color: var(--text-secondary); line-height: 1.6; }
    .ncard-meta  { display:flex; align-items:center; gap:12px; margin-top: 8px; }
    .ncard-time  { font-size: 0.7rem; color: rgba(255,255,255,0.3); }
    .ncard-type-badge {
        font-size: 0.6rem; font-weight: 800;
        padding: 2px 8px; border-radius: 8px;
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .ncard-actions { margin-top: 10px; display:flex; gap:8px; }
    .ncard-btn-go {
        font-size: 0.75rem; padding: 6px 16px;
        border-radius: 8px; border: none;
        background: linear-gradient(135deg, #D4AF37, #B8860B);
        color: #000; font-weight: 700; cursor: pointer;
        font-family: inherit; transition: opacity .2s;
    }
    .ncard-btn-go:hover { opacity: .85; }
    .ncard-btn-del {
        font-size: 0.75rem; padding: 6px 12px;
        border-radius: 8px; border: 1px solid var(--border-color);
        background: transparent; color: var(--text-secondary);
        cursor: pointer; font-family: inherit; transition: all .2s;
    }
    .ncard-btn-del:hover { border-color: #ef4444; color: #ef4444; }

    /* Settings panel */
    .settings-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px; padding: 24px;
        height: fit-content;
    }
    .toggle-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .toggle-row:last-child { border-bottom: none; padding-bottom: 0; }
    .toggle-label { font-size: 0.85rem; font-weight: 600; }
    .toggle-sub   { font-size: 0.72rem; color: var(--text-secondary); margin-top: 2px; }

    /* Toggle switch */
    .toggle-switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0; border-radius: 12px;
        background: rgba(255,255,255,0.1); cursor: pointer;
        transition: background .3s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 18px; height: 18px; border-radius: 50%;
        background: white; top: 3px; left: 3px;
        transition: transform .3s; box-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }
    .toggle-switch input:checked + .toggle-slider { background: #D4AF37; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    /* Empty state */
    .empty-state {
        text-align: center; padding: 80px 20px;
    }

    /* Stats row */
    .notif-stat {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 14px; padding: 16px 20px;
        text-align: center;
    }
</style>
@endsection

@section('content')

<!-- HERO -->
<section class="notif-page-hero">
    <div class="container mx-auto px-4">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="inline-flex items-center gap-2 text-xs text-gold mb-3">
                    <i class="fas fa-bell"></i>
                    <span>مركز الإشعارات الذكي</span>
                </div>
                <h1 class="text-3xl font-black mb-1">إشعاراتك</h1>
                <p class="text-secondary text-sm">تتبّع مزايداتك وتوصياتك المخصصة في مكان واحد</p>
            </div>
            <div class="flex gap-3">
                <button id="mark-all-read-btn" class="bg-tertiary border border-color text-sm font-bold px-4 py-2.5 rounded-xl hover:border-gold transition flex items-center gap-2">
                    <i class="fas fa-check-double text-gold"></i>
                    تحديد الكل كمقروء
                </button>
                <button id="delete-all-btn" class="bg-tertiary border border-color text-sm font-bold px-4 py-2.5 rounded-xl hover:border-red-500 transition flex items-center gap-2">
                    <i class="fas fa-trash text-red-400"></i>
                    حذف الكل
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-8">
            <div class="notif-stat">
                <p class="text-2xl font-black text-gold" id="stat-total">--</p>
                <p class="text-xs text-secondary mt-1">إجمالي الإشعارات</p>
            </div>
            <div class="notif-stat">
                <p class="text-2xl font-black" style="color:#ef4444" id="stat-unread">--</p>
                <p class="text-xs text-secondary mt-1">غير مقروءة</p>
            </div>
            <div class="notif-stat">
                <p class="text-2xl font-black" style="color:#fbbf24" id="stat-outbid">--</p>
                <p class="text-xs text-secondary mt-1">تنبيهات مزايدة</p>
            </div>
            <div class="notif-stat">
                <p class="text-2xl font-black" style="color:#D4AF37" id="stat-match">--</p>
                <p class="text-xs text-secondary mt-1">توصيات مخصصة</p>
            </div>
        </div>
    </div>
</section>

<!-- MAIN -->
<section class="py-10">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- NOTIFICATIONS LIST (2/3) -->
            <div class="lg:col-span-2">

                <!-- Filters -->
                <div class="flex flex-wrap gap-2 mb-6">
                    <button class="filter-chip active" data-filter="all">
                        <i class="fas fa-bell"></i> الكل
                        <span class="chip-count" id="chip-all">0</span>
                    </button>
                    <button class="filter-chip" data-filter="outbid">
                        <i class="fas fa-gavel"></i> تجاوز المزايدة
                        <span class="chip-count" id="chip-outbid">0</span>
                    </button>
                    <button class="filter-chip" data-filter="match_alert">
                        <i class="fas fa-gem"></i> التوصيات
                        <span class="chip-count" id="chip-match">0</span>
                    </button>
                    <button class="filter-chip" data-filter="unread">
                        <i class="fas fa-circle" style="font-size:0.6rem"></i> غير مقروءة
                        <span class="chip-count" id="chip-unread">0</span>
                    </button>
                </div>

                <!-- Skeleton loader -->
                <div id="notif-skeleton" class="space-y-4">
                    @for($i = 0; $i < 3; $i++)
                    <div class="ncard animate-pulse" style="pointer-events:none;">
                        <div class="ncard-icon bg-tertiary"></div>
                        <div class="flex-1 space-y-2 pt-1">
                            <div class="h-4 bg-tertiary rounded w-3/4"></div>
                            <div class="h-3 bg-tertiary rounded w-full"></div>
                            <div class="h-3 bg-tertiary rounded w-2/3"></div>
                        </div>
                    </div>
                    @endfor
                </div>

                <!-- List -->
                <div id="page-notif-list" class="space-y-4 hidden"></div>

                <!-- Empty state -->
                <div id="page-notif-empty" class="empty-state hidden">
                    <i class="fas fa-bell-slash text-5xl mb-4 block" style="color:rgba(212,175,55,0.2)"></i>
                    <h3 class="text-lg font-bold mb-2">لا توجد إشعارات</h3>
                    <p class="text-secondary text-sm">ستظهر هنا إشعارات المزادات والتوصيات المخصصة لك.</p>
                    <a href="{{ url('/auctions') }}" class="inline-flex items-center gap-2 mt-6 bg-tertiary border border-color px-5 py-3 rounded-xl text-sm font-bold hover:border-gold transition">
                        <i class="fas fa-gavel text-gold"></i>
                        استكشف المزادات
                    </a>
                </div>

                <!-- Load more -->
                <div class="text-center mt-6 hidden" id="load-more-wrap">
                    <button id="load-more-btn" class="bg-tertiary border border-color px-6 py-3 rounded-xl text-sm font-bold hover:border-gold transition">
                        <i class="fas fa-chevron-down mr-2"></i>
                        تحميل المزيد
                    </button>
                </div>
            </div>

            <!-- SETTINGS (1/3) -->
            <div class="space-y-5">

                <!-- Push permissions -->
                <div class="settings-card">
                    <h3 class="font-bold mb-4 flex items-center gap-2 text-sm">
                        <i class="fas fa-bell text-gold"></i>
                        إعدادات الإشعارات
                    </h3>

                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">إشعارات المتصفح</div>
                            <div class="toggle-sub">تنبيهات فورية حتى خارج الموقع</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-browser" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">تجاوز المزايدة</div>
                            <div class="toggle-sub">عندما يتجاوزك أحدهم في المزاد</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-outbid" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">توصيات مخصصة</div>
                            <div class="toggle-sub">أحجار تناسب اهتماماتك</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-match" checked onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="toggle-row">
                        <div>
                            <div class="toggle-label">انتهاء المزاد</div>
                            <div class="toggle-sub">تذكير قبل انتهاء المزاد بـ 10 دقائق</div>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" id="pref-ending" onchange="savePref()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <div class="mt-4 pt-4 border-t border-color">
                        <button id="enable-push-btn" class="w-full bg-tertiary border border-color text-sm font-bold py-3 rounded-xl hover:border-gold transition flex items-center justify-center gap-2" onclick="enablePushNotifications()">
                            <i class="fas fa-bell text-gold"></i>
                            <span id="push-btn-text">تفعيل إشعارات المتصفح</span>
                        </button>
                        <p class="text-xs text-secondary text-center mt-2" id="push-status-text">
                            احصل على تنبيهات فورية حتى عندما لا تكون في الموقع
                        </p>
                    </div>
                </div>

                <!-- Info card -->
                <div class="settings-card">
                    <h3 class="font-bold mb-3 text-sm flex items-center gap-2">
                        <i class="fas fa-info-circle text-gold"></i>
                        كيف تعمل الإشعارات؟
                    </h3>
                    <div class="space-y-3 text-xs text-secondary">
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0" style="background:rgba(239,68,68,0.12)">
                                <i class="fas fa-gavel" style="color:#ef4444; font-size:0.7rem;"></i>
                            </div>
                            <span><strong class="text-primary">تجاوز المزايدة:</strong> يُرسَل فوراً عند تقديم شخص آخر عرضاً أعلى من عرضك الحالي.</span>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0" style="background:rgba(212,175,55,0.12)">
                                <i class="fas fa-gem" style="color:#D4AF37; font-size:0.7rem;"></i>
                            </div>
                            <span><strong class="text-primary">التوصيات المخصصة:</strong> يُحلَّل سجل مزايداتك لرصد الأنواع المفضلة وإرسال تنبيه عند إضافة حجر يناسبك.</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
let pageData        = [];
let currentFilter   = 'all';
let currentPage     = 1;
let lastPage        = 1;

document.addEventListener('DOMContentLoaded', async () => {
    await loadNotifications();
    setupFilters();

    document.getElementById('mark-all-read-btn').addEventListener('click', doMarkAllRead);
    document.getElementById('delete-all-btn').addEventListener('click', doDeleteAll);
    document.getElementById('load-more-btn').addEventListener('click', loadMore);

    // Restore preferences from localStorage
    const prefs = JSON.parse(localStorage.getItem('notif_prefs') || '{}');
    if (prefs.outbid !== undefined) document.getElementById('pref-outbid').checked = prefs.outbid;
    if (prefs.match  !== undefined) document.getElementById('pref-match').checked  = prefs.match;
    if (prefs.ending !== undefined) document.getElementById('pref-ending').checked = prefs.ending;
    if (prefs.browser!== undefined) document.getElementById('pref-browser').checked= prefs.browser;

    updatePushStatus();
});

async function loadNotifications(page = 1) {
    if (!api.getToken()) {
        showLoginMessage();
        return;
    }
    try {
        const res = await api.request(`/notifications?page=${page}`);
        if (!res?.success) return;

        if (page === 1) {
            pageData = res.data || [];
        } else {
            pageData = [...pageData, ...(res.data || [])];
        }

        lastPage    = res.pagination?.last_page || 1;
        currentPage = res.pagination?.current_page || 1;

        updateStats(res.unread_count);
        renderPage();

        document.getElementById('notif-skeleton').classList.add('hidden');
        document.getElementById('page-notif-list').classList.remove('hidden');

        // Load more button
        const wrapEl = document.getElementById('load-more-wrap');
        wrapEl.classList.toggle('hidden', currentPage >= lastPage);
    } catch(e) {
        console.error(e);
        document.getElementById('notif-skeleton').classList.add('hidden');
    }
}

function updateStats(unread) {
    const total   = pageData.length;
    const outbid  = pageData.filter(n => n.type === 'outbid').length;
    const match   = pageData.filter(n => n.type === 'match_alert').length;
    const unreadC = pageData.filter(n => !n.is_read).length;

    document.getElementById('stat-total').textContent  = total;
    document.getElementById('stat-unread').textContent  = unread ?? unreadC;
    document.getElementById('stat-outbid').textContent  = outbid;
    document.getElementById('stat-match').textContent   = match;

    document.getElementById('chip-all').textContent     = total;
    document.getElementById('chip-outbid').textContent  = outbid;
    document.getElementById('chip-match').textContent   = match;
    document.getElementById('chip-unread').textContent  = unreadC;
}

function renderPage() {
    const list  = document.getElementById('page-notif-list');
    const empty = document.getElementById('page-notif-empty');

    const filtered = filterData();

    list.innerHTML = '';

    if (filtered.length === 0) {
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');

    filtered.forEach((n, i) => {
        list.insertAdjacentHTML('beforeend', buildCard(n, i));
    });

    list.querySelectorAll('.ncard[data-id]').forEach(el => {
        el.addEventListener('click', (e) => {
            if (e.target.closest('.ncard-btn-del, .ncard-btn-go')) return;
            doMarkRead(parseInt(el.dataset.id));
        });
    });
    list.querySelectorAll('.ncard-btn-del').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            doDelete(parseInt(btn.dataset.id));
        });
    });
    list.querySelectorAll('.ncard-btn-go').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const url = btn.dataset.url;
            if (url) window.location.href = url;
        });
    });
}

function filterData() {
    if (currentFilter === 'all')    return pageData;
    if (currentFilter === 'unread') return pageData.filter(n => !n.is_read);
    return pageData.filter(n => n.type === currentFilter);
}

function buildCard(n, idx) {
    const bg    = n.color ? hexToRgba(n.color, 0.12) : 'rgba(212,175,55,0.12)';
    const unread = !n.is_read ? 'unread' : '';

    const typeLabel = {
        outbid:      { text: 'مزايدة',    bg:'rgba(239,68,68,0.15)',    color:'#ef4444' },
        match_alert: { text: 'توصية',     bg:'rgba(212,175,55,0.15)',   color:'#D4AF37' },
        system:      { text: 'نظام',      bg:'rgba(99,102,241,0.15)',   color:'#818cf8' },
    }[n.type] || { text: n.type, bg:'rgba(255,255,255,0.08)', color:'#aaa' };

    const actionBtn = n.action_url
        ? `<button class="ncard-btn-go" data-url="${n.action_url}"><i class="fas fa-arrow-left ml-1"></i>عرض التفاصيل</button>`
        : '';

    return `
    <div class="ncard ${unread}" data-id="${n.id}" style="animation-delay:${idx * 0.06}s">
        <div class="ncard-icon" style="background:${bg}; color:${n.color || '#D4AF37'}">
            <i class="${n.icon || 'fas fa-bell'}"></i>
        </div>
        <div class="ncard-body">
            <div class="ncard-title">${n.title}</div>
            <div class="ncard-text">${n.body}</div>
            <div class="ncard-meta">
                <span class="ncard-time"><i class="far fa-clock" style="font-size:0.6rem;"></i> ${n.created_at}</span>
                <span class="ncard-type-badge" style="background:${typeLabel.bg}; color:${typeLabel.color}">${typeLabel.text}</span>
                ${!n.is_read ? '<span style="font-size:0.65rem;color:#D4AF37;font-weight:700;">● جديد</span>' : ''}
            </div>
            ${actionBtn ? `<div class="ncard-actions">${actionBtn}<button class="ncard-btn-del" data-id="${n.id}"><i class="fas fa-trash"></i> حذف</button></div>` : `<div class="ncard-actions"><button class="ncard-btn-del" data-id="${n.id}"><i class="fas fa-trash"></i> حذف</button></div>`}
        </div>
    </div>`;
}

function setupFilters() {
    document.querySelectorAll('.filter-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.filter-chip').forEach(c => c.classList.remove('active'));
            chip.classList.add('active');
            currentFilter = chip.dataset.filter;
            renderPage();
        });
    });
}

async function doMarkRead(id) {
    try {
        await api.request(`/notifications/${id}/read`, { method: 'PUT' });
        const n = pageData.find(x => x.id === id);
        if (n) { n.is_read = true; }
        document.querySelector(`.ncard[data-id="${id}"]`)?.classList.remove('unread');
        updateStats(pageData.filter(x => !x.is_read).length);
    } catch(e) {}
}

async function doMarkAllRead() {
    try {
        await api.request('/notifications/read-all', { method: 'PUT' });
        pageData.forEach(n => n.is_read = true);
        document.querySelectorAll('.ncard.unread').forEach(el => el.classList.remove('unread'));
        updateStats(0);
        if (typeof jawharaNotifications !== 'undefined') jawharaNotifications.refresh();
        if (typeof ui !== 'undefined') ui.showSuccess('تم تحديد جميع الإشعارات كمقروءة');
    } catch(e) {}
}

async function doDelete(id) {
    try {
        await api.request(`/notifications/${id}`, { method: 'DELETE' });
        pageData = pageData.filter(x => x.id !== id);
        const el = document.querySelector(`.ncard[data-id="${id}"]`);
        if (el) { el.style.animation = 'fadeOut .3s ease forwards'; setTimeout(() => el.remove(), 320); }
        updateStats(pageData.filter(x => !x.is_read).length);
        if (filterData().length === 0) document.getElementById('page-notif-empty').classList.remove('hidden');
    } catch(e) {}
}

async function doDeleteAll() {
    const confirmed = await ui.confirm('هل أنت متأكد من حذف جميع الإشعارات؟', 'حذف الإشعارات', 'نعم، احذف الكل', 'إلغاء');
    if (!confirmed) return;
    try {
        await api.request('/notifications', { method: 'DELETE' });
        pageData = [];
        document.getElementById('page-notif-list').innerHTML = '';
        document.getElementById('page-notif-empty').classList.remove('hidden');
        updateStats(0);
        if (typeof jawharaNotifications !== 'undefined') jawharaNotifications.refresh();
        ui.showSuccess('تم حذف جميع الإشعارات');
    } catch(e) {}
}

async function loadMore() {
    await loadNotifications(currentPage + 1);
}

// Push notifications
function enablePushNotifications() {
    if (!('Notification' in window)) {
        ui.showError('متصفحك لا يدعم إشعارات المتصفح');
        return;
    }
    Notification.requestPermission().then(perm => {
        updatePushStatus(perm);
        if (perm === 'granted') {
            ui.showSuccess('تم تفعيل الإشعارات الفورية بنجاح!');
        } else {
            ui.showWarning('لم يتم السماح بالإشعارات. يمكنك تغيير ذلك من إعدادات المتصفح.');
        }
    });
}

function updatePushStatus(permission) {
    const perm = permission || (typeof Notification !== 'undefined' ? Notification.permission : 'default');
    const btn  = document.getElementById('enable-push-btn');
    const txt  = document.getElementById('push-status-text');
    if (!btn) return;

    if (perm === 'granted') {
        btn.innerHTML = '<i class="fas fa-check-circle text-green-500"></i> <span>الإشعارات الفورية مفعّلة</span>';
        btn.style.borderColor = 'rgba(34,197,94,0.4)';
        txt.textContent = 'ستصلك التنبيهات الفورية تلقائياً';
    } else if (perm === 'denied') {
        btn.innerHTML = '<i class="fas fa-ban text-red-400"></i> <span>الإشعارات محظورة</span>';
        btn.style.borderColor = 'rgba(239,68,68,0.4)';
        txt.textContent = 'غيّر الإعداد من متصفحك ← الخصوصية ← الإشعارات';
    }
}

function savePref() {
    const prefs = {
        browser: document.getElementById('pref-browser').checked,
        outbid:  document.getElementById('pref-outbid').checked,
        match:   document.getElementById('pref-match').checked,
        ending:  document.getElementById('pref-ending').checked,
    };
    localStorage.setItem('notif_prefs', JSON.stringify(prefs));
    if (api.getToken()) {
        api.request('/notifications/preferences', { method: 'PUT', body: JSON.stringify({ preferences: prefs }) }).catch(() => {});
    }
}

function showLoginMessage() {
    document.getElementById('notif-skeleton').classList.add('hidden');
    document.getElementById('page-notif-list').innerHTML = `
        <div class="text-center py-16">
            <i class="fas fa-lock text-4xl mb-4 block" style="color:rgba(212,175,55,0.3)"></i>
            <h3 class="text-lg font-bold mb-2">تسجيل الدخول مطلوب</h3>
            <p class="text-secondary text-sm mb-6">يجب تسجيل الدخول لعرض إشعاراتك.</p>
            <a href="/login" class="inline-flex items-center gap-2 gold-gradient text-black px-6 py-3 rounded-xl font-bold text-sm">
                <i class="fas fa-sign-in-alt"></i>تسجيل الدخول
            </a>
        </div>`;
    document.getElementById('page-notif-list').classList.remove('hidden');
}

function hexToRgba(hex, alpha) {
    if (!hex || hex.length < 4) return `rgba(212,175,55,${alpha})`;
    const r = parseInt(hex.slice(1,3), 16);
    const g = parseInt(hex.slice(3,5), 16);
    const b = parseInt(hex.slice(5,7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

window.enablePushNotifications = enablePushNotifications;
window.savePref = savePref;
</script>
@endsection
