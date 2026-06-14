@extends('layouts.dashboard')

@section('title', 'إدارة المزادات | جوهرة')

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

<!-- Filters -->
<div class="bg-secondary border border-color rounded-xl p-4 mb-6">
    <div class="flex flex-col md:flex-row gap-4">
        <input type="text" id="search-auctions" placeholder="البحث في المزادات..." 
            class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto md:flex-1">
        
        <select id="filter-status" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الحالات</option>
            <option value="pending">في الانتظار</option>
            <option value="active">نشط</option>
            <option value="ended">منتهي</option>
            <option value="cancelled">ملغي</option>
        </select>

        <button onclick="loadAuctions(1)" class="bg-gold text-black px-6 py-2 rounded-lg hover:shadow-lg transition w-full md:w-auto font-bold">
            <i class="fas fa-search ml-2"></i>
            بحث
        </button>
    </div>
</div>

<!-- Auctions Grid -->
<div id="auctions-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="text-center py-12 text-secondary col-span-full">
        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
        <p>جاري تحميل المزادات...</p>
    </div>
</div>

<!-- Pagination -->
<div id="pagination" class="flex justify-between items-center border-t border-color pt-6">
    <div class="text-sm text-secondary" id="pagination-info">
        عرض 0 من 0
    </div>
    <div class="flex gap-2" id="pagination-buttons">
        <!-- Pagination buttons will be inserted here -->
    </div>
</div>

<!-- Edit Auction Modal -->
<div id="auction-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-secondary rounded-xl shadow-2xl max-w-md w-full mx-4 border border-color">
        <div class="p-6 border-b border-color flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary" id="auction-modal-title">تعديل بيانات المزاد</h2>
            <button onclick="closeAuctionModal()" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="auction-form" class="p-6 space-y-4">
            <input type="hidden" id="auction-id">
            
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">السعر الابتدائي (ر.س) *</label>
                <input type="number" id="auction-starting-price" required min="0" step="0.01"
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">الحد الأدنى للزيادة (ر.س) *</label>
                <input type="number" id="auction-increment" required min="0" step="0.01"
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">وقت البدء *</label>
                    <input type="datetime-local" id="auction-start-time" required
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">وقت الانتهاء *</label>
                    <input type="datetime-local" id="auction-end-time" required
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">حالة المزاد *</label>
                <select id="auction-status" required class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                    <option value="pending">في الانتظار (Pending)</option>
                    <option value="active">نشط (Active)</option>
                    <option value="ended">منتهي (Ended)</option>
                    <option value="cancelled">ملغي (Cancelled)</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4 border-t border-color mt-6">
                <button type="submit" class="flex-1 bg-gold hover:bg-yellow-600 text-black py-2.5 rounded-lg font-bold transition">
                    <i class="fas fa-save ml-1"></i>حفظ التعديلات
                </button>
                <button type="button" onclick="closeAuctionModal()" class="flex-1 bg-tertiary text-primary py-2.5 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentPage = 1;
    let totalPages = 1;
    window.allAuctions = [];

    // Load auctions
    async function loadAuctions(page = 1) {
        try {
            const params = { page };
            
            const searchValue = document.getElementById('search-auctions').value;
            if (searchValue) params.search = searchValue;
            
            const statusValue = document.getElementById('filter-status').value;
            if (statusValue) params.status = statusValue;

            const response = await api.getAdminAuctions(params);
            const data = response.data || response;
            window.allAuctions = data.data || [];
            displayAuctions(data);
        } catch (error) {
            console.error('Error loading auctions:', error);
            ui.showError('فشل تحميل المزادات');
        }
    }

    function displayAuctions(data) {
        const grid = document.getElementById('auctions-grid');
        const auctions = data.data || [];
        
        if (auctions.length === 0) {
            grid.innerHTML = `
                <div class="text-center py-12 text-secondary col-span-full">
                    <i class="fas fa-gavel text-6xl mb-4"></i>
                    <p class="text-xl">لا توجد مزادات</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = auctions.map(auction => {
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

            return `
                <div class="bg-secondary border border-color rounded-xl p-6 hover:shadow-lg transition">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-primary mb-1">${auction.product ? auction.product.name : 'منتج محذوف'}</h3>
                            <p class="text-sm text-secondary">البائع: ${auction.seller ? auction.seller.first_name + ' ' + auction.seller.last_name : 'غير محدد'}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[auction.status]}">
                            ${statusText[auction.status]}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="bg-tertiary rounded-lg p-3">
                            <p class="text-xs text-secondary mb-1">السعر الابتدائي / الحالي</p>
                            <p class="text-lg font-bold text-green-500">${auction.starting_price} / ${auction.current_price} ر.س</p>
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
                        ${auction.winner ? `
                            <div class="flex justify-between">
                                <span class="text-secondary">الفائز:</span>
                                <span class="text-gold font-semibold">${auction.winner.first_name} ${auction.winner.last_name}</span>
                            </div>
                        ` : ''}
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-color">
                        <button onclick="editAuction(${auction.id})" 
                            class="px-4 py-2 rounded-lg bg-blue-500 bg-opacity-25 hover:bg-opacity-35 text-blue-500 transition text-sm font-semibold"
                            title="تعديل">
                            <i class="fas fa-edit"></i> تعديل
                        </button>

                        ${auction.status === 'pending' ? `
                            <button onclick="approveAuction(${auction.id})" 
                                class="flex-1 bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition text-sm font-semibold">
                                <i class="fas fa-check ml-1"></i>موافقة
                            </button>
                            <button onclick="rejectAuction(${auction.id})" 
                                class="flex-1 bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition text-sm font-semibold">
                                <i class="fas fa-times ml-1"></i>رفض
                            </button>
                        ` : auction.status === 'active' ? `
                            <button onclick="endAuction(${auction.id})" 
                                class="flex-1 bg-orange-500 text-white px-4 py-2 rounded-lg hover:bg-orange-600 transition text-sm font-semibold">
                                <i class="fas fa-stop ml-1"></i>إنهاء المزاد
                            </button>
                        ` : ''}
                        
                        ${auction.status === 'pending' || auction.status === 'cancelled' ? `
                            <button onclick="deleteAuction(${auction.id})" 
                                class="px-4 py-2 rounded-lg bg-red-500 bg-opacity-25 hover:bg-opacity-35 text-red-500 transition text-sm font-semibold"
                                title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }).join('');

        currentPage = data.current_page || 1;
        totalPages = data.last_page || 1;
        updatePagination(data);
    }

    function updatePagination(data) {
        document.getElementById('pagination-info').textContent = 
            `عرض ${data.from || 0} - ${data.to || 0} من ${data.total || 0}`;

        const buttonsContainer = document.getElementById('pagination-buttons');
        buttonsContainer.innerHTML = '';

        if (totalPages <= 1) return;

        if (currentPage > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.className = 'px-3 py-1 bg-tertiary rounded hover:bg-gold hover:text-black transition text-primary';
            prevBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            prevBtn.onclick = () => loadAuctions(currentPage - 1);
            buttonsContainer.appendChild(prevBtn);
        }

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `px-3 py-1 rounded transition ${
                    i === currentPage 
                        ? 'bg-gold text-black font-bold' 
                        : 'bg-tertiary hover:bg-gold hover:text-black text-primary'
                }`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => loadAuctions(i);
                buttonsContainer.appendChild(pageBtn);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.className = 'px-2 text-secondary';
                dots.textContent = '...';
                buttonsContainer.appendChild(dots);
            }
        }

        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.className = 'px-3 py-1 bg-tertiary rounded hover:bg-gold hover:text-black transition text-primary';
            nextBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            nextBtn.onclick = () => loadAuctions(currentPage + 1);
            buttonsContainer.appendChild(nextBtn);
        }
    }

    function formatDateTimeLocal(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        const tzOffset = date.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(date.getTime() - tzOffset)).toISOString().slice(0, 16);
        return localISOTime;
    }

    function editAuction(id) {
        const auction = window.allAuctions.find(a => a.id === id);
        if (!auction) return;

        document.getElementById('auction-id').value = auction.id;
        document.getElementById('auction-starting-price').value = auction.starting_price || 0;
        document.getElementById('auction-increment').value = auction.min_bid_increment || 0;
        document.getElementById('auction-start-time').value = formatDateTimeLocal(auction.start_time);
        document.getElementById('auction-end-time').value = formatDateTimeLocal(auction.end_time);
        document.getElementById('auction-status').value = auction.status;

        const modal = document.getElementById('auction-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeAuctionModal() {
        const modal = document.getElementById('auction-modal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    document.getElementById('auction-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('auction-id').value;
        const data = {
            starting_price: parseFloat(document.getElementById('auction-starting-price').value),
            min_bid_increment: parseFloat(document.getElementById('auction-increment').value),
            start_time: document.getElementById('auction-start-time').value.replace('T', ' ') + ':00',
            end_time: document.getElementById('auction-end-time').value.replace('T', ' ') + ':00',
            status: document.getElementById('auction-status').value
        };

        try {
            const response = await api.updateAdminAuction(id, data);
            if (response.success) {
                ui.showSuccess('تم تحديث بيانات المزاد بنجاح!');
                closeAuctionModal();
                loadAuctions(currentPage);
            } else {
                ui.showError(response.message || 'فشل التحديث');
            }
        } catch (error) {
            console.error(error);
            ui.showError(error.message || 'حدث خطأ أثناء حفظ التعديلات');
        }
    });

    async function approveAuction(id) {
        const approved = await ui.confirm('هل أنت متأكد من الموافقة على هذا المزاد؟', 'موافقة على المزاد');
        if (!approved) return;
        try {
            await api.approveAuction(id);
            ui.showSuccess('تمت الموافقة على المزاد بنجاح!');
            loadAuctions(currentPage);
        } catch (error) {
            console.error('Error approving auction:', error);
            ui.showError(error.message || 'فشل الموافقة على المزاد');
        }
    }

    async function rejectAuction(id) {
        const approved = await ui.confirm('هل أنت متأكد من رفض هذا المزاد؟', 'رفض المزاد');
        if (!approved) return;
        try {
            await api.rejectAuction(id);
            ui.showSuccess('تم رفض المزاد');
            loadAuctions(currentPage);
        } catch (error) {
            console.error('Error rejecting auction:', error);
            ui.showError(error.message || 'فشل رفض المزاد');
        }
    }

    async function endAuction(id) {
        const approved = await ui.confirm('هل أنت متأكد من إنهاء هذا المزاد؟\n\nسيتم إنهاء المزاد فوراً وتحديد الفائز.', 'إنهاء المزاد فوراً');
        if (!approved) return;
        try {
            await api.endAuction(id);
            ui.showSuccess('تم إنهاء المزاد بنجاح!');
            loadAuctions(currentPage);
        } catch (error) {
            console.error('Error ending auction:', error);
            ui.showError(error.message || 'فشل إنهاء المزاد');
        }
    }

    async function deleteAuction(id) {
        const approved = await ui.confirm('هل أنت متأكد من حذف هذا المزاد؟', 'تأكيد حذف المزاد');
        if (!approved) return;
        try {
            await api.deleteAdminAuction(id);
            ui.showSuccess('تم حذف المزاد بنجاح!');
            loadAuctions(currentPage);
        } catch (error) {
            console.error('Error deleting auction:', error);
            ui.showError(error.message || 'فشل حذف المزاد');
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    document.getElementById('search-auctions').addEventListener('input', debounce(() => {
        loadAuctions(1);
    }, 500));

    document.getElementById('filter-status').addEventListener('change', () => {
        loadAuctions(1);
    });

    loadAuctions();
</script>
@endsection
