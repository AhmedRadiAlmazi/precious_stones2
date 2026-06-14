@extends('layouts.dashboard')

@section('title', 'لوحة تحكم الإدارة | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        const isAdmin = user && user.roles && user.roles.some(role => role.name === 'admin');
        if (!isAdmin) {
            alert('هذه الصفحة للمديرين فقط!');
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value" id="total-users">0</div>
                <div class="stat-card-label">إجمالي المستخدمين</div>
            </div>
            <div class="stat-card-icon blue">
                <i class="fas fa-users"></i>
            </div>
        </div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>12% من الشهر الماضي</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value" id="pending-sellers">0</div>
                <div class="stat-card-label">البائعون المعلقون</div>
            </div>
            <div class="stat-card-icon gold">
                <i class="fas fa-user-clock"></i>
            </div>
        </div>
        <div class="stat-card-change">
            <i class="fas fa-clock"></i>
            <span>يحتاج مراجعة</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value" id="total-products">0</div>
                <div class="stat-card-label">إجمالي المنتجات</div>
            </div>
            <div class="stat-card-icon green">
                <i class="fas fa-box"></i>
            </div>
        </div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>8% من الشهر الماضي</span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <div class="stat-card-value" id="active-auctions">0</div>
                <div class="stat-card-label">المزادات النشطة</div>
            </div>
            <div class="stat-card-icon red">
                <i class="fas fa-gavel"></i>
            </div>
        </div>
        <div class="stat-card-change positive">
            <i class="fas fa-arrow-up"></i>
            <span>15% من الشهر الماضي</span>
        </div>
    </div>
</div>

<!-- Pending Sellers Section -->
<div class="bg-secondary border border-color rounded-xl overflow-hidden mb-8">
    <div class="gold-gradient p-6">
        <h2 class="text-2xl font-bold text-white">
            <i class="fas fa-user-check ml-2"></i>
            البائعون في انتظار الموافقة
        </h2>
    </div>
    
    <div id="pending-sellers-list" class="p-6">
        <div class="text-center py-12 text-secondary">
            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
            <p>جاري تحميل البيانات...</p>
        </div>
    </div>
</div>

<!-- Pending Auctions Section -->
<div class="bg-secondary border border-color rounded-xl overflow-hidden mb-8">
    <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
        <h2 class="text-2xl font-bold text-white">
            <i class="fas fa-gavel ml-2"></i>
            مزادات في انتظار الموافقة
        </h2>
    </div>
    
    <div id="pending-auctions-list" class="p-6">
        <div class="text-center py-12 text-secondary">
            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
            <p>جاري تحميل المزادات...</p>
        </div>
    </div>
</div>

<!-- All Users Section -->
<div class="bg-secondary border border-color rounded-xl overflow-hidden">
    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
        <h2 class="text-2xl font-bold text-white">
            <i class="fas fa-users ml-2"></i>
            جميع المستخدمين
        </h2>
    </div>
    
    <div class="p-6">
        <div class="mb-4 flex gap-4">
            <input type="text" id="search-users" placeholder="البحث عن مستخدم..." 
                class="flex-1 bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
            <select id="filter-role" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                <option value="">جميع الأدوار</option>
                <option value="admin">مدير</option>
                <option value="seller">بائع</option>
                <option value="buyer">مشتري</option>
            </select>
        </div>
        
        <div id="users-list" class="space-y-4">
            <div class="text-center py-12 text-secondary">
                <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                <p>جاري تحميل المستخدمين...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadDashboard() {
        try {
            console.log('Loading dashboard data...');
            
            // Load dashboard stats
            const statsResponse = await api.request('/admin/stats');
            if (!statsResponse.success) {
                throw new Error(statsResponse.message || 'Failed to load stats');
            }
            
            const stats = statsResponse.data;
            
            // Update stats
            document.getElementById('total-users').textContent = stats.total_users || 0;
            document.getElementById('pending-sellers').textContent = stats.pending_sellers || 0;
            document.getElementById('total-products').textContent = stats.total_products || 0;
            document.getElementById('active-auctions').textContent = stats.active_auctions || 0;
            
            const pendingBadge = document.getElementById('pending-badge');
            if (pendingBadge) {
                pendingBadge.textContent = stats.pending_sellers;
            }

            // Load pending sellers
            const pendingResponse = await api.getPendingSellers();
            const pendingSellers = pendingResponse.data || [];
            displayPendingSellers(pendingSellers);

            // Load pending auctions
            const pendingAuctionsResponse = await api.getPendingAuctions();
            const pendingAuctions = pendingAuctionsResponse.data || [];
            displayPendingAuctions(pendingAuctions);

            // Load all users
            const usersResponse = await api.getAllUsers();
            window.allUsersList = usersResponse.data?.data || usersResponse.data || [];
            filterAndDisplayUsers();
            
        } catch (error) {
            console.error('Dashboard load error:', error);
            
            if (error.status === 401 || error.status === 403) {
                alert('انتهت جلستك. يرجى تسجيل الدخول مرة أخرى.');
                api.removeToken();
                window.location.href = '{{ url("/login") }}';
                return;
            }
            
            const errorMessage = error.message || 'فشل تحميل البيانات';
            
            // Show error in containers
            document.getElementById('pending-sellers-list').innerHTML = `
                <div class="text-center py-12 text-red-500">
                    <i class="fas fa-exclamation-triangle text-6xl mb-4"></i>
                    <p class="text-xl">فشل تحميل البيانات</p>
                    <p class="text-sm mt-2">${errorMessage}</p>
                    <button onclick="loadDashboard()" class="mt-4 gold-gradient text-white px-6 py-3 rounded-lg">
                        إعادة المحاولة
                    </button>
                </div>
            `;
        }
    }

    function displayPendingSellers(sellers) {
        const container = document.getElementById('pending-sellers-list');
        
        if (sellers.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary">
                    <i class="fas fa-check-circle text-6xl mb-4 text-green-500"></i>
                    <p class="text-xl">لا توجد طلبات معلقة</p>
                    <p class="text-sm mt-2">جميع البائعين تمت الموافقة عليهم</p>
                </div>
            `;
            return;
        }

        container.innerHTML = sellers.map(seller => `
            <div class="border-2 border-gold bg-tertiary rounded-lg p-6 hover:shadow-lg transition mb-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                ${seller.first_name.charAt(0)}${seller.last_name.charAt(0)}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-primary">
                                    ${seller.first_name} ${seller.last_name}
                                </h3>
                                <span class="inline-block px-3 py-1 bg-yellow-500 bg-opacity-20 text-yellow-500 text-xs rounded-full font-semibold">
                                    <i class="fas fa-clock ml-1"></i>
                                    في انتظار الموافقة
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4">
                            <div class="flex items-center gap-2 text-secondary">
                                <i class="fas fa-envelope text-blue-500 w-5"></i>
                                <span class="text-sm">${seller.email}</span>
                            </div>
                            <div class="flex items-center gap-2 text-secondary">
                                <i class="fas fa-phone text-green-500 w-5"></i>
                                <a href="tel:${seller.phone}" class="text-sm hover:text-gold">${seller.phone}</a>
                            </div>
                            <div class="flex items-center gap-2 text-secondary">
                                <i class="fas fa-calendar text-purple-500 w-5"></i>
                                <span class="text-sm">تاريخ التسجيل: ${new Date(seller.created_at).toLocaleDateString('ar-SA')}</span>
                            </div>
                            <div class="flex items-center gap-2 text-secondary">
                                <i class="fas fa-user-tag text-orange-500 w-5"></i>
                                <span class="text-sm">نوع الحساب: بائع</span>
                            </div>
                        </div>

                        <div class="bg-primary rounded-lg p-4 mb-4 border border-color">
                            <h4 class="font-bold text-primary mb-2">
                                <i class="fas fa-info-circle ml-1 text-blue-500"></i>
                                معلومات إضافية
                            </h4>
                            <div class="text-sm text-secondary space-y-1">
                                <p><strong>رقم المستخدم:</strong> #${seller.id}</p>
                                <p><strong>البريد الإلكتروني للتواصل:</strong> 
                                    <a href="mailto:${seller.email}" class="text-gold hover:underline">${seller.email}</a>
                                </p>
                                <p><strong>رقم الهاتف للتواصل:</strong> 
                                    <a href="https://wa.me/${seller.phone.replace(/[^0-9]/g, '')}" target="_blank" class="text-green-500 hover:underline">
                                        <i class="fab fa-whatsapp ml-1"></i>
                                        ${seller.phone}
                                    </a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex flex-row lg:flex-col gap-2 w-full lg:w-auto justify-end">
                        <button onclick="approveSeller(${seller.id})" 
                            class="bg-green-500 text-white px-6 py-3 rounded-lg hover:bg-green-600 transition shadow-md flex-1 lg:flex-initial">
                            <i class="fas fa-check ml-1"></i>موافقة
                        </button>
                        <button onclick="rejectSeller(${seller.id})" 
                            class="bg-red-500 text-white px-6 py-3 rounded-lg hover:bg-red-600 transition shadow-md flex-1 lg:flex-initial">
                            <i class="fas fa-times ml-1"></i>رفض
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function displayAllUsers(users) {
        const container = document.getElementById('users-list');
        
        if (users.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary">
                    <i class="fas fa-users-slash text-6xl mb-4"></i>
                    <p class="text-xl">لا يوجد مستخدمين</p>
                </div>
            `;
            return;
        }

        container.innerHTML = users.map(u => {
            const roleColors = {
                'admin': 'bg-red-500 bg-opacity-20 text-red-500',
                'seller': 'bg-blue-500 bg-opacity-20 text-blue-500',
                'buyer': 'bg-green-500 bg-opacity-20 text-green-500'
            };
            const roleColor = roleColors[u.account_type] || 'bg-gray-500 bg-opacity-20 text-gray-500';
            
            return `
                <div class="border border-color rounded-lg p-4 hover:shadow-md transition bg-tertiary">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-bold text-primary">
                                    ${u.first_name} ${u.last_name}
                                </h3>
                                <span class="px-3 py-1 rounded-full text-xs font-semibold ${roleColor}">
                                    ${u.account_type === 'admin' ? 'مدير' : u.account_type === 'seller' ? 'بائع' : 'مشتري'}
                                </span>
                                ${u.account_type === 'seller' && u.is_approved ? 
                                    '<span class="px-2 py-1 bg-green-500 bg-opacity-20 text-green-500 text-xs rounded-full"><i class="fas fa-check ml-1"></i>موافق عليه</span>' : 
                                    u.account_type === 'seller' && !u.is_approved ? 
                                    '<span class="px-2 py-1 bg-yellow-500 bg-opacity-20 text-yellow-500 text-xs rounded-full"><i class="fas fa-clock ml-1"></i>معلق</span>' : 
                                    ''}
                            </div>
                            <div class="flex flex-wrap gap-4 text-sm text-secondary">
                                <span><i class="fas fa-envelope ml-1 text-blue-500"></i>${u.email}</span>
                                <span><i class="fas fa-phone ml-1 text-green-500"></i>${u.phone}</span>
                                <span><i class="fas fa-calendar ml-1 text-purple-500"></i>${new Date(u.created_at).toLocaleDateString('ar-SA')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function filterAndDisplayUsers() {
        if (!window.allUsersList) return;
        const searchVal = document.getElementById('search-users').value.toLowerCase();
        const roleVal = document.getElementById('filter-role').value;

        const filtered = window.allUsersList.filter(u => {
            const matchSearch = !searchVal || 
                u.first_name.toLowerCase().includes(searchVal) || 
                u.last_name.toLowerCase().includes(searchVal) || 
                u.email.toLowerCase().includes(searchVal);
            const matchRole = !roleVal || u.account_type === roleVal;
            return matchSearch && matchRole;
        });

        displayAllUsers(filtered);
    }

    function displayPendingAuctions(auctions) {
        const container = document.getElementById('pending-auctions-list');
        
        if (auctions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary">
                    <i class="fas fa-check-circle text-6xl mb-4 text-green-500"></i>
                    <p class="text-xl">لا توجد مزادات معلقة</p>
                </div>
            `;
            return;
        }

        container.innerHTML = auctions.map(auction => `
            <div class="border border-color rounded-lg p-4 hover:shadow-md transition bg-tertiary mb-4">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-primary">
                                ${auction.product ? auction.product.name : 'منتج غير معروف'}
                            </h3>
                            <span class="px-3 py-1 bg-yellow-500 bg-opacity-20 text-yellow-500 text-xs rounded-full font-semibold">
                                <i class="fas fa-clock ml-1"></i>
                                في انتظار الموافقة
                            </span>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-sm text-secondary">
                            <div>
                                <span class="font-bold">البائع:</span> ${auction.seller ? `${auction.seller.first_name} ${auction.seller.last_name}` : 'غير معروف'}
                            </div>
                            <div>
                                <span class="font-bold">السعر المبدئي:</span> ${auction.starting_price} ريال
                            </div>
                            <div>
                                <span class="font-bold">تاريخ البدء:</span> ${new Date(auction.start_time).toLocaleString('ar-SA')}
                            </div>
                            <div>
                                <span class="font-bold">تاريخ الانتهاء:</span> ${new Date(auction.end_time).toLocaleString('ar-SA')}
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full md:w-auto justify-end">
                        <button onclick="approveAuction(${auction.id})" 
                            class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-sm flex-1 md:flex-none">
                            <i class="fas fa-check ml-1"></i>موافقة
                        </button>
                        <button onclick="rejectAuction(${auction.id})" 
                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm flex-1 md:flex-none">
                            <i class="fas fa-times ml-1"></i>رفض
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function approveAuction(auctionId) {
        if (!await ui.confirm('هل أنت متأكد من الموافقة على هذا المزاد؟', 'تأكيد المزاد')) return;
        try {
            await api.approveAuction(auctionId);
            ui.showSuccess('تمت الموافقة على المزاد بنجاح!');
            loadDashboard();
        } catch (error) {
            ui.showError(error.message || 'فشل الموافقة على المزاد');
        }
    }

    async function rejectAuction(auctionId) {
        if (!await ui.confirm('هل أنت متأكد من رفض هذا المزاد؟', 'رفض المزاد')) return;
        try {
            await api.rejectAuction(auctionId);
            ui.showSuccess('تم رفض المزاد');
            loadDashboard();
        } catch (error) {
            ui.showError(error.message || 'فشل رفض المزاد');
        }
    }

    async function approveSeller(userId) {
        if (!await ui.confirm('هل أنت متأكد من الموافقة على هذا البائع؟', 'موافقة بائع')) return;
        try {
            await api.approveSeller(userId);
            ui.showSuccess('تم الموافقة على البائع بنجاح!');
            loadDashboard();
        } catch (error) {
            ui.showError(error.message || 'فشل الموافقة على البائع');
        }
    }

    async function rejectSeller(userId) {
        if (!await ui.confirm('هل أنت متأكد من رفض هذا البائع؟', 'رفض بائع')) return;
        try {
            await api.rejectSeller(userId);
            ui.showSuccess('تم رفض البائع');
            loadDashboard();
        } catch (error) {
            ui.showError(error.message || 'فشل رفض البائع');
        }
    }

    document.getElementById('search-users').addEventListener('input', filterAndDisplayUsers);
    document.getElementById('filter-role').addEventListener('change', filterAndDisplayUsers);

    loadDashboard();
</script>
@endsection
