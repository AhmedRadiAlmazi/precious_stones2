@extends('layouts.app')

@section('title', 'المزادات الجارية | جوهرة')

@section('content')
    <!-- شريط التصفية العلوي -->
    <div class="bg-tertiary border-b border-color py-3">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4 rtl:space-x-reverse overflow-x-auto pb-2" id="auctions-filter-bar">
                    <button data-filter="all" class="auction-filter-btn whitespace-nowrap gold-gradient text-white px-4 py-2 rounded-full text-sm font-bold">
                        جميع المزادات
                    </button>
                    <button data-filter="ending_soon" class="auction-filter-btn whitespace-nowrap bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        تنتهي قريباً
                    </button>
                    <button data-filter="new" class="auction-filter-btn whitespace-nowrap bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        جديدة
                    </button>
                    <button data-filter="highest_price" class="auction-filter-btn whitespace-nowrap bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        الأعلى سعراً
                    </button>
                    <button data-filter="most_bids" class="auction-filter-btn whitespace-nowrap bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        الأكثر مزايدة
                    </button>
                </div>
                
                <div class="hidden md:flex items-center space-x-4 rtl:space-x-reverse">
                    <span class="text-sm text-secondary" id="auctions-count">0 مزاد</span>
                    <button id="filter-toggle" class="flex items-center space-x-2 rtl:space-x-reverse bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        <i class="fas fa-sliders-h"></i>
                        <span>تصفية</span>
                    </button>
                </div>
                
                <!-- زر الفلتر للجوال -->
                <button id="mobile-filter-toggle" class="md:hidden flex items-center space-x-2 rtl:space-x-reverse bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                    <i class="fas fa-filter"></i>
                    <span>فلتر</span>
                </button>
            </div>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <main class="container mx-auto px-4 py-6">
        <div class="flex flex-col md:flex-row gap-6">
            <!-- الشريط الجانبي للتصفية -->
            <div id="sidebar" class="sidebar-mobile md:relative md:w-1/4 lg:w-1/5 bg-secondary rounded-xl p-4 border border-color">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold gold-text">تصفية النتائج</h2>
                    <button id="close-sidebar" class="md:hidden text-secondary hover:text-primary">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <div class="space-y-6">
                    <!-- تصنيف السعر -->
                    <div>
                        <h3 class="font-semibold text-sm mb-2 text-primary">سعر البدء (ر.س)</h3>
                        <div class="flex gap-2">
                            <input type="number" id="price-min" placeholder="أدنى" class="w-1/2 bg-tertiary border border-color rounded-lg p-2 text-xs text-primary">
                            <input type="number" id="price-max" placeholder="أقصى" class="w-1/2 bg-tertiary border border-color rounded-lg p-2 text-xs text-primary">
                        </div>
                    </div>
                    
                    <!-- تصنيف فئات الأحجار -->
                    <div>
                        <h3 class="font-semibold text-sm mb-2 text-primary">نوع الحجر</h3>
                        <div class="space-y-2 text-xs text-secondary" id="stone-type-filters">
                            <label class="flex items-center"><input type="checkbox" data-category-id="2" class="ml-2"> ياقوت</label>
                            <label class="flex items-center"><input type="checkbox" data-category-id="3" class="ml-2"> زمرد</label>
                            <label class="flex items-center"><input type="checkbox" data-category-id="5" class="ml-2"> عقيق</label>
                            <label class="flex items-center"><input type="checkbox" data-category-id="1" class="ml-2"> ألماس</label>
                        </div>
                    </div>
                    
                    <button onclick="applyFilters()" class="w-full gold-gradient text-white font-bold py-2 rounded-lg text-sm transition">
                        تطبيق التصفية
                    </button>
                </div>
            </div>
            
            <!-- شبكة المزادات -->
            <div class="flex-1">
                <div id="auctions-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="col-span-full text-center py-20 text-secondary">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>جاري تحميل المزادات النشطة...</p>
                    </div>
                </div>
                
                <!-- ترقيم الصفحات -->
                <div id="pagination-container" class="flex justify-center mt-12 hidden">
                    <nav class="flex items-center space-x-2 rtl:space-x-reverse">
                        <button class="bg-secondary text-primary px-3 py-2 rounded-lg border border-color hover:bg-tertiary transition text-sm">السابق</button>
                        <button class="gold-gradient text-white px-3 py-2 rounded-lg font-bold text-sm">1</button>
                        <button class="bg-secondary text-primary px-3 py-2 rounded-lg border border-color hover:bg-tertiary transition text-sm">التالي</button>
                    </nav>
                </div>
            </div>
        </div>
    </main>
    <div id="sidebar-overlay" class="sidebar-overlay"></div>
@endsection

@section('scripts')
    <script>
        // التحكم في القائمة والفلتر في المحمول
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');
        const mobileFilterToggle = document.getElementById('mobile-filter-toggle');
        const closeSidebar = document.getElementById('close-sidebar');
        
        function openSidebar() {
            sidebar.classList.add('open');
            sidebarOverlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        
        function closeSidebarFunc() {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('open');
            document.body.style.overflow = 'auto';
        }
        
        if(mobileFilterToggle) mobileFilterToggle.addEventListener('click', openSidebar);
        if(closeSidebar) closeSidebar.addEventListener('click', closeSidebarFunc);
        if(sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebarFunc);

        // تأثير النقر الصوتي
        const rippleButtons = document.querySelectorAll('.ripple');
        rippleButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-select-click-1109.mp3');
                audio.volume = 0.3;
                audio.play().catch(() => {});
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let countdownInterval;
            let currentFilterParams = {};

            function parseDateUTC(dateString) {
                if (!dateString) return 0;
                const parts = dateString.split(/[- :]/);
                if (parts.length >= 6) {
                    return Date.UTC(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
                }
                return new Date(dateString).getTime();
            }

            function formatTimeRemaining(endTimeStr) {
                const end = parseDateUTC(endTimeStr);
                const now = new Date().getTime();
                const total = end - now;

                if (total <= 0) return 'منتهي';
                
                const seconds = Math.floor((total / 1000) % 60);
                const minutes = Math.floor((total / 1000 / 60) % 60);
                const hours = Math.floor((total / (1000 * 60 * 60)) % 24);
                const days = Math.floor(total / (1000 * 60 * 60 * 24));
                
                const s = seconds < 10 ? '0' + seconds : seconds;
                const m = minutes < 10 ? '0' + minutes : minutes;
                const h = hours < 10 ? '0' + hours : hours;

                if (days > 0) return `${days} يوم ${h}:${m}:${s}`;
                return `${h}:${m}:${s}`;
            }

            function startCountdownTimers() {
                if (countdownInterval) clearInterval(countdownInterval);
                const timers = document.querySelectorAll('.countdown-timer-text');
                updateTimers(timers);
                countdownInterval = setInterval(() => {
                    updateTimers(timers);
                }, 1000);
            }

            function updateTimers(timers) {
                timers.forEach(timer => {
                    const endTime = timer.dataset.endTime;
                    if (endTime) {
                        const text = formatTimeRemaining(endTime);
                        timer.innerText = text;
                        if (text === 'منتهي') {
                            timer.classList.remove('text-primary');
                            timer.classList.add('text-red-500', 'font-bold');
                            const card = timer.closest('.card-hover');
                            if(card) {
                                const btn = card.querySelector('button');
                                if(btn && !btn.disabled) {
                                    btn.disabled = true;
                                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                                    btn.innerText = 'انتهى المزاد';
                                }
                            }
                        }
                    }
                });
            }

            function getImageUrl(img) {
                if (!img) return '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                if (img.startsWith('http')) return img;
                const baseUrl = API_BASE_URL.replace('/api/v1', '');
                if (img.startsWith('/imges') || img.startsWith('imges')) {
                    return `${baseUrl}/${img.replace(/^\//, '')}`;
                }
                return `${baseUrl}/storage/${img.replace(/^\/|storage\//g, '')}`;
            }

            async function loadAuctions() {
                const auctionsContainer = document.getElementById('auctions-container');
                try {
                    const params = { limit: 12, ...currentFilterParams };
                    
                    const minPriceInput = document.getElementById('price-min');
                    const maxPriceInput = document.getElementById('price-max');
                    if (minPriceInput && minPriceInput.value) {
                        params.min_price = minPriceInput.value;
                    }
                    if (maxPriceInput && maxPriceInput.value) {
                        params.max_price = maxPriceInput.value;
                    }
                    
                    const checkedCategories = [];
                    const checkboxes = document.querySelectorAll('#stone-type-filters input[type="checkbox"]');
                    checkboxes.forEach(cb => {
                        if (cb.checked) {
                            const catId = cb.getAttribute('data-category-id');
                            if (catId) checkedCategories.push(catId);
                        }
                    });
                    if (checkedCategories.length > 0) {
                        params.category_id = checkedCategories[0];
                    }
                    
                    const auctionsResponse = await api.getAuctions(params);
                    let auctions = auctionsResponse.data?.data || auctionsResponse.data || [];
                    
                    if (auctions.length === 0) {
                        auctionsContainer.innerHTML = '<div class="col-span-full text-center py-20 text-secondary">لا توجد مزادات نشطة حالياً.</div>';
                        document.getElementById('auctions-count').textContent = '0 مزاد';
                        return;
                    }

                    document.getElementById('auctions-count').textContent = `${auctions.length} مزاد`;

                    const now = new Date().getTime();
                    const processedAuctions = auctions.map(auction => {
                        const endTime = parseDateUTC(auction.end_time);
                        return {
                            ...auction,
                            _endTimeParsed: endTime,
                            _isExpired: (endTime || 0) <= now
                        };
                    });

                    processedAuctions.sort((a, b) => {
                        if (a._isExpired === b._isExpired) {
                            return a._isExpired ? b._endTimeParsed - a._endTimeParsed : a._endTimeParsed - b._endTimeParsed;
                        }
                        return a._isExpired ? 1 : -1;
                    });

                    auctionsContainer.innerHTML = processedAuctions.map(auction => {
                        const isExpired = auction._isExpired;
                        const statusColor = isExpired ? 'bg-gray-500' : 'bg-green-500';
                        const statusText = isExpired ? 'منتهي' : 'نشط';
                        const btnState = isExpired ? 'disabled class="bg-gray-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed text-sm"' : 'class="gold-gradient text-white font-bold py-2 px-4 rounded-lg ripple text-sm" onclick="window.location.href=\'{{ url("/auction-details") }}?id=' + auction.id + '\'"';
                        const btnText = isExpired ? 'انتهى المزاد' : 'زايد الآن';
                        const countdownDisplay = isExpired ? 
                            '<span class="text-red-500 font-bold">منتهي</span>' : 
                            `<span class="countdown-timer-text font-bold dir-ltr" data-end-time="${auction.end_time}">--:--:--</span>`;

                        const progressWidth = isExpired ? 100 : 50; 
                        const progressColor = isExpired ? 'bg-gray-500' : 'bg-green-500';
                        const imageUrl = getImageUrl(auction.product && auction.product.images && auction.product.images[0]);

                        return `
                        <div class="bg-secondary rounded-xl overflow-hidden card-hover border border-color shadow-lg transition-all duration-300">
                            <div class="relative">
                                <div class="h-48 overflow-hidden">
                                    <img src="${imageUrl}" alt="${auction.product?.name || 'مزاد'}" class="w-full h-full object-cover">
                                </div>
                                <div class="absolute top-4 left-4 ${statusColor} text-white text-xs font-bold py-1 px-2 rounded">
                                    ${statusText}
                                </div>
                                <div class="certification-seal">
                                    <i class="fas fa-award text-white text-sm"></i>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold mb-2 text-primary truncate">${auction.product?.name || 'مزاد'}</h3>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-yellow-500 font-bold">${parseFloat(auction.current_price || auction.starting_price).toLocaleString()} ر.س</span>
                                    <span class="text-secondary text-sm">${auction.product?.country || 'غير محدد'}</span>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span class="text-secondary">الوقت المتبقي</span>
                                        ${countdownDisplay}
                                    </div>
                                    <div class="w-full bg-tertiary rounded-full h-2">
                                        <div class="${progressColor} h-2 rounded-full" style="width: ${progressWidth}%"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-secondary">${auction.total_bids || 0} مزايد</span>
                                    <button ${btnState}>
                                        ${btnText}
                                    </button>
                                </div>
                            </div>
                        </div>
                        `;
                    }).join('');

                    startCountdownTimers();
                    document.getElementById('pagination-container').classList.remove('hidden');

                } catch (error) {
                    console.error('Error loading auctions:', error);
                    auctionsContainer.innerHTML = `
                        <div class="col-span-full text-center py-10">
                            <p class="text-red-500">حدث خطأ أثناء تحميل المزادات.</p>
                            <button onclick="loadAuctions()" class="mt-4 text-yellow-500 hover:underline">إعادة المحاولة</button>
                        </div>
                    `;
                }
            }

            // Setup auction filter bar buttons
            const filterButtons = document.querySelectorAll('.auction-filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => {
                        btn.className = 'auction-filter-btn whitespace-nowrap bg-secondary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color';
                    });
                    button.className = 'auction-filter-btn whitespace-nowrap gold-gradient text-white px-4 py-2 rounded-full text-sm font-bold';
                    
                    const filterType = button.getAttribute('data-filter');
                    currentFilterParams = {};
                    if (filterType === 'ending_soon') {
                        currentFilterParams.ending_soon = 1;
                    } else if (filterType === 'new') {
                        currentFilterParams.sort_by = 'created_at';
                        currentFilterParams.sort_order = 'desc';
                    } else if (filterType === 'highest_price') {
                        currentFilterParams.sort_by = 'current_price';
                        currentFilterParams.sort_order = 'desc';
                    } else if (filterType === 'most_bids') {
                        currentFilterParams.sort_by = 'total_bids';
                        currentFilterParams.sort_order = 'desc';
                    }
                    loadAuctions();
                });
            });

            window.applyFilters = function() {
                loadAuctions();
                // Close sidebar on mobile if open
                if (typeof closeSidebarFunc === 'function') {
                    closeSidebarFunc();
                }
            };

            loadAuctions();
        });
    </script>
@endsection
