@extends('layouts.app')

@section('title', 'جوهرة | منصة المزادات الفاخرة')

@section('content')
    <!-- السلايدر الرئيسي -->
    <section class="relative py-16 md:py-28 overflow-hidden bg-tertiary parallax flex items-center">
        <div class="particles-container" id="particles-container"></div>
        
        <div class="absolute inset-0 bg-black bg-opacity-20 z-10"></div>
        
        <div class="relative z-20 container mx-auto px-4">
            <div class="max-w-2xl">
                <h1 class="text-3xl md:text-5xl font-bold mb-4 gold-text">اكتشف عالم الأحجار النادرة</h1>
                <p class="text-lg md:text-xl mb-8 text-secondary">منصة المزادات الفاخرة الأولى للاحجار الكريمة والنادرة</p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 rtl:space-x-reverse">
                    <a href="{{ url('/shop') }}" class="gold-gradient text-white text-center font-bold py-3 px-6 rounded-full ripple shine-effect">
                        استكشف الآن
                    </a>
                    <a href="{{ url('/auctions') }}" class="bg-transparent text-center border-2 border-yellow-500 text-yellow-500 font-bold py-3 px-6 rounded-full hover:bg-yellow-500 hover:text-black transition">
                        تصفح المزادات
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- المنتجات المميزة -->
    <section class="py-16 fade-in">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold gold-text">منتجات مميزة</h2>
                <a href="{{ url('/shop') }}" class="text-yellow-500 hover:text-yellow-400 flex items-center">
                    مشاهدة الكل
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>
            
            <div id="featured-products-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- سيتم تحميل المنتجات هنا -->
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-yellow-500"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- المزادات الجارية -->
    <section class="py-16 bg-tertiary fade-in">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center mb-10">
                <h2 class="text-3xl font-bold gold-text">المزادات الجارية</h2>
                <a href="{{ url('/auctions') }}" class="text-yellow-500 hover:text-yellow-400 flex items-center">
                    مشاهدة الكل
                    <i class="fas fa-arrow-left mr-2"></i>
                </a>
            </div>
            
            <div id="live-auctions-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- سيتم تحميل المزادات هنا -->
                <div class="col-span-full text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-yellow-500"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- الموردين الموثوقين -->
    <section class="py-16 fade-in">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center gold-text mb-12">الموردين الموثوقين</h2>
            
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <!-- مورد 1 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1560250097-0b93528c311a?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">أحمد الجواهري</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
                
                <!-- مورد 2 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">محمد الأحمدي</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
                
                <!-- مورد 3 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">خالد البلوي</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
                
                <!-- مورد 4 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">سعيد القحطاني</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
                
                <!-- مورد 5 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">فهد الشمري</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
                
                <!-- مورد 6 -->
                <div class="flex flex-col items-center">
                    <div class="w-24 h-24 rounded-full overflow-hidden border-2 border-yellow-500 mb-3">
                        <img src="https://images.unsplash.com/photo-1580489944761-15a19d654956?ixlib=rb-4.0.3&auto=format&fit=crop&w=200&q=80" alt="مورد" class="w-full h-full object-cover">
                    </div>
                    <h3 class="font-bold">نورة السديري</h3>
                    <div class="flex text-yellow-500 text-sm">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star"></i>
                    </div>
                    <span class="text-xs bg-yellow-500 text-black px-2 py-1 rounded mt-1">موثق رسمي</span>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // --- تأثيرات الجسيمات والتأثيرات الحركية ---
        document.addEventListener('DOMContentLoaded', function() {
            createParticles();
            
            const rippleButtons = document.querySelectorAll('.ripple');
            rippleButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-select-click-1109.mp3');
                    audio.volume = 0.3;
                    audio.play().catch(() => {});
                });
            });
            
            // زر العودة لأعلى
            const backToTopButton = document.createElement('button');
            backToTopButton.innerHTML = '<i class="fas fa-arrow-up"></i>';
            backToTopButton.className = 'fixed bottom-4 right-4 bg-yellow-500 text-black p-3 rounded-full shadow-lg hover:bg-yellow-600 transition opacity-0 pointer-events-none z-50';
            backToTopButton.id = 'back-to-top';
            document.body.appendChild(backToTopButton);

            window.addEventListener('scroll', () => {
                if (window.pageYOffset > 300) {
                    backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
                    backToTopButton.classList.add('opacity-100', 'pointer-events-auto');
                } else {
                    backToTopButton.classList.remove('opacity-100', 'pointer-events-auto');
                    backToTopButton.classList.add('opacity-0', 'pointer-events-none');
                }
            });

            backToTopButton.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        });

        // تأثير الجسيمات الذهبية
        function createParticles() {
            const container = document.getElementById('particles-container');
            if(!container) return;
            const particleCount = 30;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                const size = Math.random() * 5 + 2;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                particle.style.left = `${Math.random() * 100}%`;
                particle.style.top = `${Math.random() * 100}%`;
                particle.style.opacity = Math.random() * 0.5 + 0.1;
                
                const duration = Math.random() * 20 + 10;
                particle.style.animation = `float ${duration}s linear infinite`;
                
                const keyframes = `
                @keyframes float {
                    0% { transform: translate(0, 0) rotate(0deg); }
                    25% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(90deg); }
                    50% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(180deg); }
                    75% { transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) rotate(270deg); }
                    100% { transform: translate(0, 0) rotate(360deg); }
                }`;
                
                const styleSheet = document.createElement('style');
                styleSheet.textContent = keyframes;
                document.head.appendChild(styleSheet);
                container.appendChild(particle);
            }
        }
    </script>
    <script>
        // --- ربط قاعدة البيانات وتحميل المحتويات ---
        document.addEventListener('DOMContentLoaded', async () => {
            let countdownInterval;

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
                const timers = document.querySelectorAll('.countdown-flash');
                updateTimers(timers);
                countdownInterval = setInterval(() => {
                    updateTimers(timers);
                }, 1000);
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

            function updateTimers(timers) {
                timers.forEach(timer => {
                    const endTime = timer.dataset.endTime;
                    if (endTime) {
                        const text = formatTimeRemaining(endTime);
                        timer.innerText = text;
                        
                        if (text === 'منتهي') {
                            timer.classList.remove('text-yellow-500', 'text-white');
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

            // 1. جلب المنتجات المميزة
            try {
                const productsResponse = await api.getProducts({ page: 1, limit: 4 }); 
                const products = productsResponse.data?.data || productsResponse.data || []; 
                const productsContainer = document.getElementById('featured-products-container');
                
                if (products.length > 0) {
                    productsContainer.innerHTML = products.map(product => {
                        const img = product.images && product.images.length > 0 ? getImageUrl(product.images[0]) : '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                        return `
                        <div class="bg-secondary rounded-xl overflow-hidden card-hover relative border border-color">
                            <div class="certification-seal">
                                <i class="fas fa-award text-white"></i>
                            </div>
                            <div class="relative h-48 overflow-hidden">
                                <img src="${img}" alt="${product.name}" class="w-full h-full object-cover">
                                <div class="absolute top-4 left-4 bg-yellow-500 text-black text-xs font-bold py-1 px-2 rounded">موثق</div>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold mb-2">${product.name}</h3>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-yellow-500 font-bold">${parseFloat(product.price).toLocaleString()} ر.س</span>
                                    <span class="text-secondary text-sm">${product.country || 'غير محدد'}</span>
                                </div>
                                <button onclick="window.location.href='{{ url("/shop") }}?id=${product.id}'" 
                                    class="w-full bg-tertiary hover:bg-opacity-80 text-primary py-2 rounded-lg transition border border-color">
                                    عرض التفاصيل
                                </button>
                            </div>
                        </div>
                    `}).join('');
                } else {
                    productsContainer.innerHTML = '<p class="text-center col-span-full">لا توجد منتجات مميزة حالياً</p>';
                }
            } catch (error) {
                console.error("Error fetching products:", error);
                document.getElementById('featured-products-container').innerHTML = `
                    <p class="text-center text-red-500 col-span-full">حدث خطأ أثناء تحميل المنتجات.</p>
                `;
            }

            // 2. جلب المزادات النشطة
            try {
                const auctionsResponse = await api.getAuctions({ limit: 10 });
                let auctions = auctionsResponse.data?.data || auctionsResponse.data || [];
                const auctionsContainer = document.getElementById('live-auctions-container');

                if (auctions.length > 0) {
                    const now = new Date().getTime();
                    const processedAuctions = auctions.map(auction => {
                        let endTimeStr = auction.end_time;
                        const endTime = parseDateUTC(endTimeStr);
                        return {
                            ...auction,
                            _endTimeParsed: endTime,
                            _isExpired: endTime <= now
                        };
                    });

                    processedAuctions.sort((a, b) => {
                        if (a._isExpired === b._isExpired) {
                            return a._isExpired ? b._endTimeParsed - a._endTimeParsed : a._endTimeParsed - b._endTimeParsed;
                        }
                        return a._isExpired ? 1 : -1;
                    });

                    const displayAuctions = processedAuctions.slice(0, 3);

                    auctionsContainer.innerHTML = displayAuctions.map(auction => {
                        const isExpired = auction._isExpired;
                        const statusColor = isExpired ? 'bg-gray-500' : 'bg-green-500';
                        const statusText = isExpired ? 'منتهي' : 'نشط';
                        const btnState = isExpired ? 'disabled class="bg-gray-400 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed"' : 'class="gold-gradient text-white font-bold py-2 px-4 rounded-lg ripple" onclick="window.location.href=\'{{ url("/auction-details") }}?id=' + auction.id + '\'"';
                        const btnText = isExpired ? 'انتهى المزاد' : 'زايد الآن';
                        const countdownDisplay = isExpired ? '<span class="text-red-500 font-bold">منتهي</span>' : `<span class="countdown-flash dir-ltr" data-end-time="${auction.end_time}">${formatTimeRemaining(auction.end_time)}</span>`;

                        const imgUrl = auction.product && auction.product.images && auction.product.images.length > 0 
                                     ? getImageUrl(auction.product.images[0]) 
                                     : '{{ asset("imges/عقيق نادر من اليمن.jpeg") }}';

                        return `
                         <div class="bg-secondary rounded-xl overflow-hidden card-hover relative border border-color shadow-lg transition-colors duration-300">
                            <div class="relative h-48 overflow-hidden">
                                <img src="${imgUrl}" alt="${auction.product?.name || 'مزاد'}" class="w-full h-full object-cover">
                                <div class="absolute top-4 left-4 ${statusColor} text-white text-xs font-bold py-1 px-2 rounded">
                                    ${statusText}
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="text-lg font-bold mb-2 text-primary truncate">${auction.product?.name || 'مزاد'}</h3>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-yellow-500 font-bold">${parseFloat(auction.current_price || auction.starting_price).toLocaleString()} ر.س</span>
                                    <span class="text-secondary text-sm">${auction.product?.country || 'غير محدد'}</span>
                                </div>
                                <div class="mb-4">
                                    <div class="flex justify-between text-sm mb-1 text-secondary">
                                        <span>الوقت المتبقي</span>
                                        ${countdownDisplay}
                                    </div>
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="${isExpired ? 'bg-gray-500' : 'bg-green-500'} h-2 rounded-full" style="width: ${isExpired ? '100%' : '50%'}"></div>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-sm text-secondary">${auction.total_bids || 0} مزايد</span>
                                    <button ${btnState}>
                                        ${btnText}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `}).join('');
                    
                    startCountdownTimers();
                } else {
                    auctionsContainer.innerHTML = '<p class="text-center col-span-full text-secondary">لا توجد مزادات نشطة حالياً</p>';
                }
            } catch (error) {
                console.error("Error fetching auctions:", error);
                document.getElementById('live-auctions-container').innerHTML = `
                    <p class="text-center text-red-500 col-span-full">حدث خطأ أثناء تحميل المزادات.</p>
                `;
            }
        });
    </script>
@endsection
