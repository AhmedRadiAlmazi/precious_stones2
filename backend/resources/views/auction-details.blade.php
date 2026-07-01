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
        /* AI Valuation scanning animations */
        @keyframes gemRotateAD {
            from { transform: rotateY(0deg); }
            to   { transform: rotateY(360deg); }
        }
        @keyframes scanLineAD {
            0%   { top: 0; opacity: 0.8; }
            50%  { opacity: 1; }
            100% { top: 100%; opacity: 0.8; }
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
                        <div class="certification-seal glow cursor-pointer hover:scale-105 transition" id="cert-seal" title="عرض شهادة توثيق الحجر الكريم">
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
            <!-- تقرير التقييم الذكي للمزاد -->
            <div class="mt-8 bg-secondary border border-color rounded-2xl p-6 shadow-xl">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <h3 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-robot text-gold"></i>
                        <span>تقرير تقييم الذكاء الاصطناعي للحجر الكريم</span>
                    </h3>
                    <button id="ai-valuation-btn" onclick="loadAiValuation()"
                        class="gold-gradient text-black font-bold px-5 py-2.5 rounded-xl text-sm hover:shadow-lg transition flex items-center gap-2">
                        <i class="fas fa-search-dollar"></i>
                        <span>عرض التقييم الذكي</span>
                    </button>
                </div>

                <!-- Initial placeholder -->
                <div id="ai-initial-state" class="text-center py-8 text-secondary">
                    <i class="fas fa-gem text-4xl text-gold mb-3 block" style="opacity:0.4"></i>
                    <p class="text-sm">اضغط على "عرض التقييم الذكي" لتحليل قيمة هذا الحجر باستخدام خوارزمية الذكاء الاصطناعي المبنية على معايير 4 Cs العالمية.</p>
                </div>

                <!-- AI Loading -->
                <div id="ai-loading-state" class="hidden text-center py-8">
                    <div style="position:relative;width:80px;height:80px;margin:0 auto 12px;">
                        <span style="font-size:4rem;line-height:1;display:block;animation:gemRotateAD 2s linear infinite;filter:drop-shadow(0 0 10px #D4AF37);">💎</span>
                        <div style="position:absolute;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,#D4AF37,transparent);animation:scanLineAD 1s linear infinite;box-shadow:0 0 8px #D4AF37;"></div>
                    </div>
                    <p class="text-gold font-bold text-sm">جاري فحص خصائص الحجر بالذكاء الاصطناعي...</p>
                </div>

                <!-- AI Result -->
                <div id="ai-result-state" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Quality gauge -->
                        <div class="bg-tertiary border border-color rounded-xl p-4 flex flex-col items-center">
                            <div style="position:relative;width:100px;height:100px;">
                                <svg viewBox="0 0 120 120" width="100" height="100" style="transform:rotate(-90deg)">
                                    <defs>
                                        <linearGradient id="adGaugeGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                                            <stop offset="0%" style="stop-color:#B8860B"/>
                                            <stop offset="100%" style="stop-color:#F5D76E"/>
                                        </linearGradient>
                                    </defs>
                                    <circle cx="60" cy="60" r="50" fill="none" stroke="rgba(255,255,255,0.06)" stroke-width="12"/>
                                    <circle cx="60" cy="60" r="50" id="ad-gauge-fill" fill="none"
                                        stroke="url(#adGaugeGrad)" stroke-width="12" stroke-linecap="round"
                                        stroke-dasharray="314.16" stroke-dashoffset="314.16"
                                        style="transition:stroke-dashoffset 1.5s cubic-bezier(0.4,0,0.2,1)"/>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                                    <span class="text-xl font-black text-gold" id="ad-quality-score">0</span>
                                    <span class="text-xs text-secondary">/100</span>
                                </div>
                            </div>
                            <p class="text-xs text-secondary mt-2 text-center">مؤشر جودة الحجر</p>
                        </div>

                        <!-- Price range -->
                        <div class="md:col-span-2 bg-tertiary border border-color rounded-xl p-4">
                            <p class="text-xs text-secondary mb-3">التقدير السعري (ريال سعودي)</p>
                            <div class="flex items-center gap-4 mb-3">
                                <div class="flex-1 text-center bg-secondary rounded-lg p-2">
                                    <p class="text-xs text-secondary mb-1">الأدنى</p>
                                    <p class="font-bold text-primary text-sm" id="ad-price-low">--</p>
                                </div>
                                <i class="fas fa-arrows-alt-h text-gold"></i>
                                <div class="flex-1 text-center bg-secondary rounded-lg p-2">
                                    <p class="text-xs text-secondary mb-1">الأعلى</p>
                                    <p class="font-bold text-primary text-sm" id="ad-price-high">--</p>
                                </div>
                            </div>
                            <div class="text-center pt-3 border-t border-color">
                                <p class="text-xs text-secondary mb-1">التقدير الوسطي</p>
                                <p class="text-2xl font-black text-gold" id="ad-price-mid">--</p>
                                <p class="text-xs text-secondary">ر.س</p>
                            </div>
                            <!-- Comparison with auction -->
                            <div id="ad-comparison" class="mt-3 pt-3 border-t border-color text-xs text-center hidden">
                                <span id="ad-comparison-text" class="font-bold"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Factors -->
                    <div class="bg-tertiary border border-color rounded-xl p-4">
                        <p class="font-bold text-sm mb-3 flex items-center gap-2">
                            <i class="fas fa-chart-bar text-gold text-xs"></i>
                            عوامل التسعير المؤثرة
                        </p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3" id="ad-factors-grid">
                            <!-- Injected by JS -->
                        </div>
                    </div>

                    <p class="text-xs text-secondary text-center mt-3">
                        <i class="fas fa-info-circle text-gold ml-1"></i>
                        التقييم تقديري استناداً لمعايير 4 Cs العالمية وهو للإرشاد فقط.
                        <a href="{{ url('/ai-valuation') }}" class="text-gold hover:underline mr-2">جرّب أداة التقييم الكاملة</a>
                    </p>
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

                // Connect certification seal click
                const certSeal = document.getElementById('cert-seal');
                if (certSeal) {
                    certSeal.onclick = () => {
                        const name = auction.product?.name || 'حجر كريم فاخر';
                        const certNo = auction.product?.certification_number || `#GIA-${auction.id}748${auction.id}`;
                        const weight = `${auction.product?.weight || '3.50'} قيراط`;
                        const clarity = auction.product?.clarity || 'VVS1 - نقي جداً';
                        const cut = 'قطع وسادة كوشون ممتاز';
                        const origin = auction.product?.country || 'سريلانكا';
                        showCertModal(name, certNo, weight, clarity, cut, origin);
                    };
                }

                startCountdown(auction.end_time);
            }

            function playGavelSound() {
                const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-gavel-hammer-strike-3103.mp3');
                audio.volume = 0.4;
                audio.play().catch(err => console.log('Audio playback block:', err));
            }

            function triggerPriceGlow() {
                const priceEl = document.getElementById('current-price');
                if (priceEl) {
                    priceEl.classList.remove('pulsate-gold');
                    void priceEl.offsetWidth; // Trigger reflow to restart animation
                    priceEl.classList.add('pulsate-gold');
                }
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
                        
                        const rowClass = index === 0 ? 'bid-history-item new-bid-row' : 'bid-history-item';
                        
                        return `
                            <tr class="${rowClass}">
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
                        await ui.alert('عذراً، يجب عليك تسجيل الدخول أولاً لتتمكن من تقديم المزايدة.', 'تنبيه المصادقة');
                        window.location.href = `{{ url("/login") }}?redirect=${encodeURIComponent(window.location.href)}`;
                        return;
                    }

                    const bidInput = document.getElementById('bid-amount-input');
                    const bidAmount = parseFloat(bidInput.value);
                    const currentPriceVal = parseFloat(currentAuction.current_price || currentAuction.starting_price);
                    const incrementVal = parseFloat(currentAuction.bid_increment || 100);

                    if (bidAmount < (currentPriceVal + incrementVal)) {
                        await ui.alert(`قيمة المزايدة غير كافية. الحد الأدنى للمزايدة هو ${(currentPriceVal + incrementVal).toLocaleString('ar-SA')} ر.س`, 'خطأ في القيمة');
                        return;
                    }

                    const depositRequired = Math.round(bidAmount * 0.05);
                    const wallet = api.getUserWallet();
                    if (wallet.balance < depositRequired) {
                        await ui.alert(`رصيد المحفظة غير كافٍ لحجز مبلغ ضمان جدية المزايدة (المطلوب 5% أي: ${depositRequired.toLocaleString('ar-SA')} ر.س، والرصيد المتاح هو ${wallet.balance.toLocaleString('ar-SA')} ر.س).`, 'رصيد غير كافٍ');
                        return;
                    }

                    const isConfirmed = await ui.confirm(`هل أنت متأكد من تقديم عرض مزايدة بقيمة ${bidAmount.toLocaleString('ar-SA')} ر.س؟\nسيتم حجز مبلغ ضمان جدية بقيمة ${depositRequired.toLocaleString('ar-SA')} ر.س من محفظتك.`, 'تأكيد تقديم المزايدة');
                    if (!isConfirmed) {
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
                            // Lock deposit in wallet
                            api.lockBidDeposit(depositRequired);
                            // Update header balance display
                            const balanceEl = document.getElementById('header-wallet-balance');
                            if (balanceEl) {
                                const newWallet = api.getUserWallet();
                                balanceEl.textContent = parseFloat(newWallet.balance).toLocaleString();
                            }
                            
                            ui.showSuccess('تم تسجيل عرض المزايدة الخاص بك بنجاح وحجز مبلغ الضمان!');
                            
                            playGavelSound();
                            triggerPriceGlow();
                            
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

            // ===== AI VALUATION LOGIC =====
            async function loadAiValuation() {
                if (!currentAuction) {
                    alert('يرجى انتظار تحميل بيانات المزاد أولاً.');
                    return;
                }

                const btn = document.getElementById('ai-valuation-btn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>جاري التحليل...</span>';

                document.getElementById('ai-initial-state').classList.add('hidden');
                document.getElementById('ai-result-state').classList.add('hidden');
                document.getElementById('ai-loading-state').classList.remove('hidden');

                // Build inputs from auction product data
                const product  = currentAuction.product || {};
                const catName  = product.category?.name || 'أحجار كريمة';
                const carats   = parseFloat(product.weight) || 1.5;
                const desc     = (product.description || '').toLowerCase();
                const origin   = product.origin_country || product.country || 'أخرى';

                // Detect clarity from description
                let clarity = 'vvs';
                if (desc.includes('flawless') || desc.includes('fl') || desc.includes('if')) clarity = 'fl_if';
                else if (desc.includes('vs'))  clarity = 'vs';
                else if (desc.includes('si'))  clarity = 'si';

                // Detect cut
                let cut = 'excellent';
                if (desc.includes('very good') || desc.includes('جيد جدا')) cut = 'very_good';
                else if (desc.includes('good') || desc.includes('جيد'))     cut = 'good';
                else if (desc.includes('fair') || desc.includes('مقبول'))   cut = 'fair';

                // Color
                let color = 'd_colorless';
                if (desc.includes('g-color') || desc.includes('near colorless')) color = 'g_near_colorless';
                if (desc.includes('fancy') || desc.includes('ملون'))            color = 'fancy_vivid';

                try {
                    const response = await api.simulateValuation({
                        category: catName,
                        carats,
                        cut,
                        clarity,
                        color,
                        origin,
                    });

                    await new Promise(r => setTimeout(r, 1200)); // Show loader briefly

                    document.getElementById('ai-loading-state').classList.add('hidden');

                    if (!response || !response.success) throw new Error('فشل التقييم');

                    const d = response.data;

                    // Gauge
                    document.getElementById('ad-quality-score').textContent = d.quality_score;
                    setTimeout(() => {
                        const circ = 314.16;
                        document.getElementById('ad-gauge-fill').style.strokeDashoffset =
                            circ - (circ * d.quality_score / 100);
                    }, 100);

                    // Prices
                    document.getElementById('ad-price-low').textContent  = Number(d.low_estimate_sar).toLocaleString('ar-SA') + ' ر.س';
                    document.getElementById('ad-price-high').textContent = Number(d.high_estimate_sar).toLocaleString('ar-SA') + ' ر.س';
                    document.getElementById('ad-price-mid').textContent  = Number(d.mid_estimate_sar).toLocaleString('ar-SA');

                    // Compare with current auction price
                    const currentBid = parseFloat(currentAuction.current_price || currentAuction.starting_price);
                    const sarBid     = currentBid; // already in SAR
                    const compDiv    = document.getElementById('ad-comparison');
                    const compText   = document.getElementById('ad-comparison-text');
                    if (sarBid && d.mid_estimate_sar) {
                        const ratio = (sarBid / d.mid_estimate_sar) * 100;
                        compDiv.classList.remove('hidden');
                        if (ratio < 85) {
                            compText.className = 'font-bold text-green-500';
                            compText.innerHTML = `<i class="fas fa-thumbs-up ml-1"></i>سعر المزاد الحالي (${currentBid.toLocaleString('ar-SA')} ر.س) أقل من التقدير بنسبة ${(100-ratio).toFixed(0)}% — فرصة ممتازة!`;
                        } else if (ratio > 115) {
                            compText.className = 'font-bold text-red-400';
                            compText.innerHTML = `<i class="fas fa-exclamation-triangle ml-1"></i>سعر المزاد الحالي أعلى من التقدير بنسبة ${(ratio-100).toFixed(0)}%`;
                        } else {
                            compText.className = 'font-bold text-gold';
                            compText.innerHTML = `<i class="fas fa-balance-scale ml-1"></i>سعر المزاد الحالي ضمن النطاق التقديري — سعر عادل.`;
                        }
                    }

                    // Factors grid
                    const factorLabels = {
                        cut_multiplier:     { label: 'القطع',   icon: '<i class="fas fa-cut" style="color:#D4AF37;font-size:1.1rem;"></i>' },
                        clarity_multiplier: { label: 'النقاء',  icon: '<i class="fas fa-microscope" style="color:#818cf8;font-size:1.1rem;"></i>' },
                        color_multiplier:   { label: 'اللون',   icon: '<i class="fas fa-palette" style="color:#f472b6;font-size:1.1rem;"></i>' },
                        origin_multiplier:  { label: 'المنشأ',  icon: '<i class="fas fa-map-marker-alt" style="color:#34d399;font-size:1.1rem;"></i>' },
                    };
                    const grid = document.getElementById('ad-factors-grid');
                    grid.innerHTML = '';
                    Object.entries(d.factors).forEach(([key, val]) => {
                        const info = factorLabels[key];
                        if (!info) return;
                        const color2 = val >= 1.5 ? '#22c55e' : val >= 1.2 ? '#D4AF37' : val >= 1.0 ? '#94a3b8' : '#ef4444';
                        grid.innerHTML += `
                            <div class="bg-secondary border border-color rounded-lg p-3 text-center">
                                <div class="text-xl mb-1">${info.icon}</div>
                                <div class="text-xs text-secondary mb-1">${info.label}</div>
                                <div class="font-black text-sm" style="color:${color2}">×${val.toFixed(2)}</div>
                            </div>`;
                    });

                    document.getElementById('ai-result-state').classList.remove('hidden');

                } catch(err) {
                    console.error(err);
                    document.getElementById('ai-loading-state').classList.add('hidden');
                    document.getElementById('ai-initial-state').classList.remove('hidden');
                    alert('حدث خطأ أثناء جلب التقييم: ' + err.message);
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-sync-alt"></i> <span>إعادة التقييم</span>';
                }
            }

            // Expose globally
            window.loadAiValuation = loadAiValuation;
        });
    </script>
@endsection
