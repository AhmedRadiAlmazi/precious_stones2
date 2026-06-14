@extends('layouts.dashboard')

@section('title', 'مزاداتي | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<!-- Header Actions -->
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div class="flex flex-col md:flex-row gap-4 items-center w-full md:w-auto">
        <input type="text" id="search-auctions" placeholder="البحث في المزادات..." 
            class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-64">
        <select id="filter-status" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الحالات</option>
            <option value="pending">في الانتظار</option>
            <option value="active">نشط</option>
            <option value="ended">منتهي</option>
            <option value="cancelled">ملغي</option>
        </select>
    </div>
    <a href="{{ url('/seller/create-auction') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition w-full md:w-auto text-center">
        <i class="fas fa-plus ml-2"></i>
        إنشاء مزاد جديد
    </a>
</div>

<!-- Auctions Grid -->
<div id="auctions-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="text-center py-12 text-secondary col-span-full">
        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
        <p>جاري تحميل المزادات...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Load seller's auctions
    async function loadAuctions() {
        try {
            const response = await api.getMyAuctions();
            const auctions = response.data?.data || response.data || [];
            window.allAuctions = auctions;
            displayAuctions(auctions);
        } catch (error) {
            console.error('Error loading auctions:', error);
            ui.showError('فشل تحميل المزادات');
        }
    }

    function displayAuctions(auctions) {
        const container = document.getElementById('auctions-grid');
        
        if (auctions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary col-span-full">
                    <i class="fas fa-gavel text-6xl mb-4"></i>
                    <p class="text-xl mb-4">لا توجد مزادات</p>
                    <a href="{{ url('/seller/create-auction') }}" class="gold-gradient text-white px-6 py-3 rounded-lg inline-block hover:shadow-lg transition">
                        <i class="fas fa-plus ml-2"></i>
                        إنشاء مزاد جديد
                    </a>
                </div>
            `;
            return;
        }

        // Filter auctions
        const searchValue = document.getElementById('search-auctions').value.toLowerCase();
        const statusValue = document.getElementById('filter-status').value;

        let filteredAuctions = auctions.filter(auction => {
            const matchSearch = !searchValue || (auction.product && auction.product.name.toLowerCase().includes(searchValue));
            const matchStatus = !statusValue || auction.status === statusValue;
            return matchSearch && matchStatus;
        });

        if (filteredAuctions.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary col-span-full">
                    <i class="fas fa-search text-6xl mb-4"></i>
                    <p class="text-xl">لا توجد نتائج</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filteredAuctions.map(auction => {
            const endTime = new Date(auction.end_time);
            const now = new Date();
            const timeRemaining = endTime - now;
            
            // Determine effective status
            let status = auction.status;
            if (status === 'active' && timeRemaining <= 0) {
                status = 'ended';
            }

            const statusColors = {
                'pending': 'bg-yellow-500 bg-opacity-20 text-yellow-500',
                'active': 'bg-green-500 bg-opacity-20 text-green-500',
                'ended': 'bg-gray-500 bg-opacity-20 text-gray-500',
                'cancelled': 'bg-red-500 bg-opacity-20 text-red-500'
            };

            const statusText = {
                'pending': 'في الانتظار',
                'active': 'نشط',
                'ended': 'منتهي',
                'cancelled': 'ملغي'
            };
            const hoursRemaining = Math.max(0, Math.floor(timeRemaining / (1000 * 60 * 60)));
            const minutesRemaining = Math.max(0, Math.floor((timeRemaining % (1000 * 60 * 60)) / (1000 * 60)));

            return `
                <div class="bg-secondary border border-color rounded-xl p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-primary mb-1">${auction.product ? auction.product.name : 'منتج محذوف'}</h3>
                            <p class="text-sm text-secondary">المزاد #${auction.id}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[status]}">
                            ${statusText[status]}
                        </span>
                    </div>

                    ${status === 'active' && timeRemaining > 0 ? `
                        <div class="bg-tertiary rounded-lg p-3 mb-4">
                            <p class="text-xs text-secondary mb-1">الوقت المتبقي</p>
                            <p class="text-lg font-bold text-gold">
                                <i class="fas fa-clock ml-1"></i>
                                ${hoursRemaining} ساعة و ${minutesRemaining} دقيقة
                            </p>
                        </div>
                    ` : ''}

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-tertiary rounded-lg p-3">
                            <p class="text-xs text-secondary mb-1">السعر الحالي</p>
                            <p class="text-lg font-bold text-green-500">${auction.current_price} ر.س</p>
                        </div>
                        <div class="bg-tertiary rounded-lg p-3">
                            <p class="text-xs text-secondary mb-1">عدد المزايدات</p>
                            <p class="text-lg font-bold text-primary">${auction.bids_count || 0}</p>
                        </div>
                    </div>

                    <div class="space-y-2 mb-4 text-sm">
                        <div class="flex justify-between">
                            <span class="text-secondary">البداية:</span>
                            <span class="text-primary">${new Date(auction.start_time).toLocaleString('ar-SA')}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-secondary">النهاية:</span>
                            <span class="text-primary">${new Date(auction.end_time).toLocaleString('ar-SA')}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-color">
                        ${status === 'pending' ? `
                            <a href="{{ url('/seller/create-auction') }}?id=${auction.id}" 
                                class="flex-1 text-sm px-3 py-2 rounded bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-blue-500 transition text-center"
                                title="تعديل">
                                <i class="fas fa-edit ml-1"></i>تعديل
                            </a>
                        ` : ''}
                        ${status !== 'cancelled' && status !== 'ended' ? `
                            <button onclick="cancelAuction(${auction.id})" 
                                class="flex-1 text-sm px-3 py-2 rounded bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-red-500 transition"
                                title="إلغاء">
                                <i class="fas fa-ban ml-1"></i>إلغاء المزاد
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    async function cancelAuction(id) {
        if (!confirm('هل أنت متأكد من إلغاء هذا المزاد؟')) return;
        try {
            await api.cancelAuction(id);
            ui.showSuccess('تم إلغاء المزاد بنجاح');
            loadAuctions();
        } catch (error) {
            ui.showError(error.message || 'فشل إلغاء المزاد');
        }
    }

    document.getElementById('search-auctions').addEventListener('input', () => {
        if (window.allAuctions) displayAuctions(window.allAuctions);
    });
    document.getElementById('filter-status').addEventListener('change', () => {
        if (window.allAuctions) displayAuctions(window.allAuctions);
    });

    loadAuctions();
</script>
@endsection
