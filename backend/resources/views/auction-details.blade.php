@extends('layouts.app')

@section('title', 'تفاصيل المزاد | جوهرة')

@section('styles')
    <style>
        /* Extra visual enhancements */
        .glow-gold {
            box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
        }
        .countdown-box {
            background: linear-gradient(135deg, rgba(212, 175, 55, 0.1) 0%, rgba(212, 175, 55, 0.02) 100%);
            border: 1px solid rgba(212, 175, 55, 0.2);
        }
        .bid-history-item {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
        }
        .bid-history-item:hover {
            background-color: var(--bg-tertiary);
        }
        .bid-history-item:last-child {
            border-bottom: none;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255,255,255,0.02);
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: var(--gold);
            border-radius: 3px;
        }
    </style>
@endsection

@section('content')
    <!-- المحتوى الرئيسي -->
    <main class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- فتات الخبز (Breadcrumb) -->
        <div class="mb-6 flex items-center space-x-2 rtl:space-x-reverse text-sm text-secondary">
            <a href="{{ url('/') }}" class="hover:text-gold transition"><i class="fas fa-home ml-1"></i>الرئيسية</a>
            <i class="fas fa-chevron-left text-xs"></i>
            <a href="{{ url('/auctions') }}" class="hover:text-gold transition">المزادات</a>
            <i class="fas fa-chevron-left text-xs"></i>
            <span class="text-primary font-semibold" id="breadcrumb-title">تفاصيل المزاد</span>
        </div>

        <div id="loading-state" class="text-center py-20">
            <i class="fas fa-spinner fa-spin text-5xl text-gold-soft mb-4"></i>
            <p class="text-secondary text-lg">جاري تحميل تفاصيل المزاد الكريم...</p>
        </div>

        <div id="error-state" class="hidden text-center py-20">
            <div class="max-w-md mx-auto bg-secondary p-8 rounded-xl border border-red-500 bg-opacity-10">
                <i class="fas fa-exclamation-triangle text-5xl text-red-500 mb-4"></i>
                <h2 class="text-2xl font-bold mb-2">فشل تحميل المزاد</h2>
                <p class="text-secondary mb-6" id="error-message">ربما يكون المزاد غير موجود أو تم حذفه.</p>
                <a href="{{ url('/auctions') }}" class="inline-block gold-gradient text-white px-6 py-3 rounded-lg font-bold hover:shadow-lg transition">العودة لصفحة المزادات</a>
            </div>
        </div>

        <!-- لوحة تفاصيل المزاد -->
        <div id="auction-details-panel" class="hidden">
            <div class="flex flex-col lg:flex-row gap-8 items-stretch">
                <!-- الجزء الأيمن: معرض الصور والمؤثرات -->
                <div class="flex-1">
                    <div class="gallery h-[450px] relative rounded-2xl overflow-hidden border border-color shadow-2xl flex items-center justify-center bg-black">
                        <div class="certification-seal glow" id="cert-seal">
                            <i class="fas fa-award text-white text-xl"></i>
                        </div>
                        <div class="spotlight"></div>
                        <img src="" alt="حجر كريم" id="auction-image" class="max-w-[85%] max-h-[80%] object-contain z-10 duration-300">
                    </div>
                </div>

                <!-- الجزء الأيسر: لوحة المزايدة والتفاصيل -->
                <div class="w-full lg:w-[450px] bg-secondary border border-color rounded-2xl p-6 shadow-xl flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-2">
                            <h2 class="text-2xl font-bold text-primary" id="product-name">اسم الحجر الكريم</h2>
                            <button id="fav-btn" class="bg-tertiary border border-color hover:text-red-500 text-secondary p-2.5 rounded-full transition duration-300">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                        
                        <div class="flex flex-wrap gap-2 mb-4 text-xs">
                            <span class="bg-tertiary text-secondary px-3 py-1.5 rounded-lg border border-color" id="product-country">المصدر: --</span>
                            <span class="bg-tertiary text-secondary px-3 py-1.5 rounded-lg border border-color" id="product-category">الفئة: --</span>
                            <span class="bg-tertiary text-gold px-3 py-1.5 rounded-lg border border-color font-semibold" id="seller-name">البائع: --</span>
                        </div>

                        <p class="text-secondary text-sm leading-relaxed mb-6" id="product-description">
                            تفاصيل ووصف الحجر الكريم...
                        </p>

                        <!-- كتلة الأسعار والمزايدات -->
                        <div class="price-block mb-6">
                            <div class="price-row mb-3">
                                <div>
                                    <span class="current-price block mb-1">السعر الحالي</span>
                                    <span class="price block text-3xl font-bold" id="current-price">0 ر.س</span>
                                </div>
                                <span class="bg-green-500 bg-opacity-20 text-green-500 text-xs font-bold px-3 py-1.5 rounded-lg" id="bids-count">0 مزايدات</span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-color text-xs text-secondary">
                                <div>
                                    <span class="block">سعر البداية:</span>
                                    <span class="font-bold text-primary mt-0.5 block" id="starting-price">0 ر.س</span>
                                </div>
                                <div>
                                    <span class="block">الحد الأدنى للزيادة:</span>
                                    <span class="font-bold text-primary mt-0.5 block" id="bid-increment">0 ر.س</span>
                                </div>
                            </div>
                        </div>

                        <!-- عداد الوقت التنازلي -->
                        <div class="countdown-box rounded-xl p-4 mb-6 text-center">
                            <div class="text-sm text-secondary mb-2 flex items-center justify-center gap-1">
                                <i class="far fa-clock"></i>
                                <span>الوقت المتبقي لانتهاء المزاد</span>
                            </div>
                            <div class="text-2xl font-bold tracking-widest text-primary dir-ltr" id="countdown-display">
                                -- : -- : --
                            </div>
                        </div>

                        <!-- مراحل الضمان المالي والتوثيق -->
                        <div class="escrow-steps mb-6">
                            <div class="step active text-center p-2 rounded-lg text-xs" title="المزاد نشط وجاري تقديم العروض">
                                <i class="fas fa-hammer mb-1 block"></i>
                                <span>مزايدة حية</span>
                            </div>
                            <div class="step text-center p-2 rounded-lg text-xs" title="يتم حجز الأموال بأمان لدى المنصة">
                                <i class="fas fa-shield-alt mb-1 block"></i>
                                <span>ضمان مالي</span>
                            </div>
                            <div class="step text-center p-2 rounded-lg text-xs" title="يتم فحص الحجر في مختبر معتمد قبل تسليمه">
                                <i class="fas fa-search-location mb-1 block"></i>
                                <span>فحص مخبري</span>
                            </div>
                            <div class="step text-center p-2 rounded-lg text-xs" title="توصيل آمن للمشتري">
                                <i class="fas fa-truck mb-1 block"></i>
                                <span>استلام مؤمن</span>
                            </div>
                        </div>
                    </div>

                    <!-- نموذج المزايدة -->
                    <div id="bid-form-container">
                        <div class="bg-red-500 bg-opacity-10 border border-red-500 text-red-500 text-sm p-3 rounded-lg mb-3 hidden" id="bid-warning-message">
                            <!-- Warnings e.g., Own auction -->
                        </div>
                        <form id="place-bid-form" class="space-y-3">
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <input type="number" id="bid-amount-input" class="w-full bg-tertiary border border-color rounded-xl py-3 px-4 focus:outline-none focus:ring-2 focus:ring-yellow-500 text-primary font-bold" placeholder="أدخل مبلغ المزايدة" required>
                                    <span class="absolute left-4 top-1/2 transform -translate-y-1/2 text-secondary text-sm font-semibold">ر.س</span>
                                </div>
                                <button type="submit" id="submit-bid-btn" class="gold-gradient text-white font-bold px-6 py-3 rounded-xl hover:shadow-lg transition flex items-center justify-center gap-2">
                                    <span>زايد الآن</span>
                                    <i class="fas fa-gavel"></i>
                                </button>
                            </div>
                            <p class="text-xs text-secondary text-right" id="min-bid-instruction">يجب أن تكون المزايدة بقيمة -- أو أكثر</p>
                        </form>
                    </div>

                    <div id="ended-auction-container" class="hidden bg-tertiary border border-color p-4 rounded-xl text-center">
                        <i class="fas fa-lock text-3xl text-red-500 mb-2"></i>
                        <p class="font-bold text-lg text-primary">المزاد منتهٍ</p>
                        <p class="text-secondary text-sm mt-1" id="ended-winner-text">لا يمكن المزايدة على هذا المنتج حالياً.</p>
                    </div>
                </div>
            </div>

            <!-- سجل المزايدات الحية -->
            <div class="mt-8 bg-secondary border border-color rounded-2xl p-6 shadow-xl">
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <i class="fas fa-history text-gold"></i>
                    <span>سجل المزايدات الحية لهذا المزاد</span>
                </h3>
                <div class="overflow-hidden border border-color rounded-xl">
                    <table class="w-full text-right border-collapse">
                        <thead>
                            <tr class="bg-tertiary text-secondary text-sm border-b border-color">
                                <th class="py-3 px-4 font-semibold">المزايد</th>
                                <th class="py-3 px-4 font-semibold">قيمة المزايدة</th>
                                <th class="py-3 px-4 font-semibold">تاريخ ووقت المزايدة</th>
                                <th class="py-3 px-4 font-semibold text-center">الحالة</th>
                            </tr>
                        </thead>
                        <tbody id="bids-history-list" class="divide-y divide-color">
                            <tr>
                                <td colspan="4" class="py-8 text-center text-secondary">جاري تحميل سجل المزايدات...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const auctionId = urlParams.get('id');

            if (!auctionId) {
                window.location.href = '{{ url("/auctions") }}';
                return;
            }

            let currentAuction = null;
            let timerInterval = null;

            async function loadAuctionDetails() {
                try {
                    const response = await api.getAuction(auctionId);
                    
                    if (!response || !response.success || !response.data) {
                        throw new Error('فشل جلب تفاصيل المزاد');
                    }

                    currentAuction = response.data;
                    renderAuction(currentAuction);
                    loadBidHistory();
                    
                    document.getElementById('loading-state').classList.add('hidden');
                    document.getElementById('auction-details-panel').classList.remove('hidden');
                } catch (error) {
                    console.error('Error loading auction details:', error);
                    document.getElementById('loading-state').classList.add('hidden');
                    document.getElementById('error-state').classList.remove('hidden');
                    if (error.message) {
                        document.getElementById('error-message').textContent = error.message;
                    }
                }
            }

            function getImageUrl(path) {
                if (!path) return '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                if (path.startsWith('http')) return path;
                const serverBase = API_BASE_URL.replace('/api/v1', '');
                if (path.startsWith('/imges') || path.startsWith('imges')) {
                    return `${serverBase}/${path.replace(/^\//, '')}`;
                }
                return `${serverBase}/storage/${path.replace(/^\/|storage\//g, '')}`;
            }

            function parseDateUTC(dateStr) {
                if (!dateStr) return 0;
                const normalizedStr = dateStr.replace(' ', 'T');
                const dateVal = new Date(normalizedStr);
                return isNaN(dateVal.getTime()) ? 0 : dateVal.getTime();
            }

            function renderAuction(auction) {
                const imgElement = document.getElementById('auction-image');
                if (auction.product && auction.product.images && auction.product.images.length > 0) {
                    imgElement.src = getImageUrl(auction.product.images[0]);
                } else {
                    imgElement.src = '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                }
                imgElement.alt = auction.product?.name || 'حجر كريم';

                document.getElementById('product-name').textContent = auction.product?.name || 'مزاد بدون اسم';
                document.getElementById('breadcrumb-title').textContent = auction.product?.name || 'تفاصيل المزاد';
                document.getElementById('product-description').textContent = auction.product?.description || 'لا يوجد وصف متاح لهذا المنتج.';
                document.getElementById('product-country').textContent = `المنشأ: ${auction.product?.country || 'غير محدد'}`;
                document.getElementById('product-category').textContent = `الفئة: ${auction.product?.category?.name || 'أحجار كريمة'}`;
                
                const sellerName = auction.seller ? `${auction.seller.first_name || ''} ${auction.seller.last_name || ''}` : 'غير محدد';
                document.getElementById('seller-name').textContent = `البائع: ${sellerName}`;

                const currentBidAmount = parseFloat(auction.current_price || auction.starting_price);
                document.getElementById('current-price').textContent = `${currentBidAmount.toLocaleString('ar-SA')} ر.س`;
                document.getElementById('starting-price').textContent = `${parseFloat(auction.starting_price).toLocaleString('ar-SA')} ر.س`;
                
                const incrementAmount = parseFloat(auction.bid_increment || 100);
                document.getElementById('bid-increment').textContent = `${incrementAmount.toLocaleString('ar-SA')} ر.س`;
                document.getElementById('bids-count').textContent = `${auction.total_bids || 0} مزايدة`;

                const minBidRequired = currentBidAmount + incrementAmount;
                const bidInput = document.getElementById('bid-amount-input');
                bidInput.value = minBidRequired;
                bidInput.min = minBidRequired;
                document.getElementById('min-bid-instruction').textContent = `يجب أن تكون المزايدة بقيمة ${minBidRequired.toLocaleString('ar-SA')} ر.س أو أكثر`;

                if (api.isAuthenticated()) {
                    const currentUser = api.getUser();
                    if (currentUser && currentUser.id === auction.seller_id) {
                        document.getElementById('place-bid-form').style.display = 'none';
                        const warningBox = document.getElementById('bid-warning-message');
                        warningBox.textContent = 'لا يمكنك المزايدة على مزادك الخاص كبائع.';
                        warningBox.classList.remove('hidden');
                    }
                }

                startCountdown(auction.end_time);
            }

            function startCountdown(endTimeStr) {
                if (timerInterval) clearInterval(timerInterval);

                const endTime = parseDateUTC(endTimeStr);
                const display = document.getElementById('countdown-display');

                function updateTimer() {
                    const now = new Date().getTime();
                    const distance = endTime - now;

                    if (distance < 0) {
                        clearInterval(timerInterval);
                        display.innerHTML = '<span class="text-red-500 font-bold">منتهي</span>';
                        document.getElementById('bid-form-container').classList.add('hidden');
                        
                        const winnerText = currentAuction && currentAuction.winner 
                            ? `الفائز بالمزاد: ${currentAuction.winner.first_name} ${currentAuction.winner.last_name}` 
                            : 'انتهى وقت المزاد دون تقديم عروض كافية.';
                        
                        document.getElementById('ended-winner-text').textContent = winnerText;
                        document.getElementById('ended-auction-container').classList.remove('hidden');
                        return;
                    }

                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    let timeString = '';
                    if (days > 0) {
                        timeString += `${days} يوم و `;
                    }
                    timeString += `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    display.textContent = timeString;

                    if (distance < 30 * 60 * 1000) {
                        display.parentElement.classList.add('urgent');
                        display.classList.add('text-red-500');
                    }
                }

                updateTimer();
                timerInterval = setInterval(updateTimer, 1000);
            }

            async function loadBidHistory() {
                const tbody = document.getElementById('bids-history-list');
                try {
                    const response = await api.getAuctionBids(auctionId);
                    if (!response || !response.success) {
                        throw new Error('فشل تحميل سجل المزايدات');
                    }

                    const bids = response.data?.data || response.data || [];
                    
                    if (bids.length === 0) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="py-8 text-center text-secondary">لا توجد مزايدات سابقة على هذا الحجر. كن أول المزايدين!</td>
                            </tr>
                        `;
                        return;
                    }

                    tbody.innerHTML = bids.map((bid, index) => {
                        const bidderName = bid.user ? `${bid.user.first_name || ''} ${bid.user.last_name || ''}` : 'مزايد مجهول';
                        const dateFormatted = new Date(bid.created_at).toLocaleString('ar-SA');
                        const isWinning = bid.is_winning ? '<span class="bg-green-500 bg-opacity-20 text-green-500 text-xs px-2 py-1 rounded-full font-bold">العرض الأعلى الحالي</span>' : '<span class="text-secondary text-xs">تجاوزه مزايد آخر</span>';
                        
                        return `
                            <tr class="bid-history-item">
                                <td class="py-4 px-6 text-primary font-semibold flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-gold bg-opacity-10 text-gold flex items-center justify-center text-xs font-bold">
                                        ${index + 1}
                                    </div>
                                    <span>${bidderName}</span>
                                </td>
                                <td class="py-4 px-6 text-gold font-bold">${parseFloat(bid.amount).toLocaleString('ar-SA')} ر.س</td>
                                <td class="py-4 px-6 text-secondary text-sm">${dateFormatted}</td>
                                <td class="py-4 px-6 text-center">${isWinning}</td>
                            </tr>
                        `;
                    }).join('');
                } catch (error) {
                    console.error('Error loading bid history:', error);
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="4" class="py-8 text-center text-red-500">حدث خطأ أثناء تحميل سجل المزايدات.</td>
                        </tr>
                    `;
                }
            }

            const placeBidForm = document.getElementById('place-bid-form');
            if (placeBidForm) {
                placeBidForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    if (!api.isAuthenticated()) {
                        alert('عذراً، يجب عليك تسجيل الدخول أولاً لتتمكن من تقديم المزايدة.');
                        window.location.href = `{{ url("/login") }}?redirect=${encodeURIComponent(window.location.href)}`;
                        return;
                    }

                    const bidInput = document.getElementById('bid-amount-input');
                    const bidAmount = parseFloat(bidInput.value);
                    const currentPriceVal = parseFloat(currentAuction.current_price || currentAuction.starting_price);
                    const incrementVal = parseFloat(currentAuction.bid_increment || 100);

                    if (bidAmount < (currentPriceVal + incrementVal)) {
                        alert(`قيمة المزايدة غير كافية. الحد الأدنى للمزايدة هو ${(currentPriceVal + incrementVal).toLocaleString('ar-SA')} ر.س`);
                        return;
                    }

                    if (!confirm(`هل أنت متأكد من تقديم عرض مزايدة بقيمة ${bidAmount.toLocaleString('ar-SA')} ر.س؟`)) {
                        return;
                    }

                    const submitBtn = document.getElementById('submit-bid-btn');
                    ui.showLoading(submitBtn);

                    try {
                        const response = await api.placeBid({
                            auction_id: parseInt(auctionId),
                            amount: bidAmount
                        });

                        if (response && response.success) {
                            ui.showSuccess('تم تسجيل عرض المزايدة الخاص بك بنجاح!');
                            await loadAuctionDetails();
                        } else {
                            ui.showError(response.message || 'حدث خطأ أثناء تسجيل المزايدة.');
                        }
                    } catch (error) {
                        console.error('Error placing bid:', error);
                        ui.showError(error.message || 'فشل الاتصال بالخادم لتقديم المزايدة.');
                    } finally {
                        ui.hideLoading(submitBtn);
                    }
                });
            }

            loadAuctionDetails();
        });
    </script>
@endsection
