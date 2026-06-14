<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'جوهرة | منصة المزادات الفاخرة')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.css') }}">
    <script src="{{ asset('js/theme.js') }}"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gold: {
                            500: '#D4AF37',
                            600: '#B8860B',
                        },
                        charcoal: {
                            800: '#1a1a1a',
                            900: '#0d0d0d',
                        }
                    },
                    fontFamily: {
                        'cairo': ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    @yield('styles')
</head>
<body class="theme-transition bg-primary text-primary">
    <!-- الهيدر الذكي -->
    <header class="bg-secondary sticky top-0 z-50 shadow-lg border-b border-color">
        <!-- شريط الأسعار العالمية -->
        <div class="bg-tertiary py-2 overflow-hidden">
            <div class="container mx-auto px-4">
                <div class="flex space-x-8 rtl:space-x-reverse animate-pulse">
                    <span class="gold-text"><i class="fas fa-gem mr-2"></i>الذهب: 2,415 ر.س/أوقية</span>
                    <span class="gold-text"><i class="fas fa-gem mr-2"></i>البلاتين: 1,120 ر.س/أوقية</span>
                    <span class="gold-text"><i class="fas fa-gem mr-2"></i>الماس: 18,350 ر.س/قيراط</span>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center w-full">
                <!-- الشعار -->
                <div class="flex items-center flex-1 justify-start">
                    <a href="{{ url('/') }}" class="flex items-center" aria-label="العودة إلى الصفحة الرئيسية">
                        <div class="w-12 h-12 gold-gradient rounded-full flex items-center justify-center glow">
                            <i class="fas fa-gem text-white text-xl"></i>
                        </div>
                        <h1 class="text-2xl font-bold mr-3 gold-text">جوهرة</h1>
                    </a>
                </div>
                
                <!-- القائمة الرئيسية (تتوسط الهيدر) -->
                <nav class="hidden md:flex space-x-6 rtl:space-x-reverse justify-center flex-initial">
                    <a href="{{ url('/') }}" class="border-b-2 pb-1 transition-all {{ request()->is('/') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">الرئيسية</a>
                    <a href="{{ url('/auctions') }}" class="border-b-2 pb-1 transition-all {{ request()->is('auctions') || request()->is('auction-details') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">المزادات</a>
                    <a href="{{ url('/shop') }}" class="border-b-2 pb-1 transition-all {{ request()->is('shop') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">المتجر</a>
                    <a href="#" class="border-b-2 pb-1 transition-all border-transparent hover:text-yellow-500">البائعون</a>
                    <a href="#" class="border-b-2 pb-1 transition-all border-transparent hover:text-yellow-500">الدعم</a>
                </nav>

                <!-- زر القائمة المحمولة -->
                <button id="mobile-menu-toggle" class="md:hidden p-2 rounded-full hover:bg-tertiary transition">
                    <i class="fas fa-bars text-secondary"></i>
                </button>
                
                <!-- الأيقونات والملف الشخصي -->
                <div class="flex items-center space-x-4 rtl:space-x-reverse flex-1 justify-end">
                    <!-- زر تبديل الوضع -->
                    <button id="theme-toggle" class="theme-toggle p-2 rounded-full hover:bg-tertiary transition">
                        <i id="theme-icon" class="theme-toggle-icon fas fa-moon text-secondary"></i>
                    </button>
                    
                    <button class="relative p-2 rounded-full hover:bg-tertiary transition">
                        <i class="fas fa-bell text-secondary"></i>
                    </button>
                    <button class="relative p-2 rounded-full hover:bg-tertiary transition">
                        <i class="fas fa-heart text-secondary"></i>
                    </button>
                    <button class="relative p-2 rounded-full hover:bg-tertiary transition">
                        <i class="fas fa-shopping-cart text-secondary"></i>
                    </button>
                    
                    <div id="user-wallet-container" class="hidden items-center bg-tertiary border border-color rounded-full px-3 py-1.5 gap-2 text-xs font-bold text-yellow-500">
                        <i class="fas fa-wallet"></i>
                        <span id="header-wallet-balance">0</span><span class="mr-0.5">ر.س</span>
                    </div>

                    <div id="auth-buttons-container" class="flex items-center">
                        <a href="{{ url('/login') }}" class="text-secondary hover:text-yellow-500 transition text-sm font-semibold">تسجيل الدخول</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- القائمة المحمولة للجوال -->
        <div id="mobile-menu" class="md:hidden bg-secondary border-t border-color hidden">
            <div class="container mx-auto px-4 py-4">
                <nav class="flex flex-col space-y-4">
                    <a href="{{ url('/') }}" class="border-r-2 pr-2 transition-all {{ request()->is('/') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">الرئيسية</a>
                    <a href="{{ url('/auctions') }}" class="border-r-2 pr-2 transition-all {{ request()->is('auctions') || request()->is('auction-details') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">المزادات</a>
                    <a href="{{ url('/shop') }}" class="border-r-2 pr-2 transition-all {{ request()->is('shop') ? 'text-yellow-500 border-yellow-500 font-bold' : 'border-transparent hover:text-yellow-500' }}">المتجر</a>
                    <a href="#" class="border-r-2 pr-2 transition-all border-transparent hover:text-yellow-500">البائعون</a>
                    <a href="#" class="border-r-2 pr-2 transition-all border-transparent hover:text-yellow-500">الدعم</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- محتوى الصفحة الفرعية -->
    @yield('content')

    <!-- الفوتر -->
    <footer class="bg-secondary py-12 border-t border-color mt-12">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <div class="flex items-center mb-4">
                        <a href="{{ url('/') }}">
                            <div class="w-10 h-10 gold-gradient rounded-full flex items-center justify-center">
                                <i class="fas fa-gem text-white"></i>
                            </div>
                        </a>
                        <h2 class="text-xl font-bold mr-3 gold-text">جوهرة</h2>
                    </div>
                    <p class="text-secondary">منصة المزادات الفاخرة الأولى للاحجار الكريمة والنادرة في العالم العربي.</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 gold-text">المتجر</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ url('/shop') }}" class="text-secondary hover:text-yellow-500 transition">جميع المنتجات</a></li>
                        <li><a href="{{ url('/auctions') }}" class="text-secondary hover:text-yellow-500 transition">المزادات النشطة</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 gold-text">الدعم</h3>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-secondary hover:text-yellow-500 transition">اتصل بنا</a></li>
                        <li><a href="#" class="text-secondary hover:text-yellow-500 transition">مركز المساعدة</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4 gold-text">طرق الدفع</h3>
                    <div class="flex space-x-2 rtl:space-x-reverse mb-4">
                        <div class="bg-tertiary p-2 rounded"><i class="fab fa-cc-visa text-2xl text-blue-500"></i></div>
                        <div class="bg-tertiary p-2 rounded"><i class="fab fa-cc-mastercard text-2xl text-red-500"></i></div>
                    </div>
                </div>
            </div>
            <div class="border-t border-color mt-8 pt-8 text-center text-sm text-secondary">
                © 2026 جوهرة. جميع الحقوق محفوظة.
            </div>
        </div>
    </footer>

    <!-- نافذة الشهادة الفاخرة للتحقق من الأحجار -->
    <div id="cert-verification-modal" class="cert-modal-backdrop" onclick="closeCertModal()">
        <div class="cert-container text-right" onclick="event.stopPropagation()">
            <span class="cert-close" onclick="closeCertModal()">&times;</span>
            <div class="cert-header">
                <h2 class="cert-title">شهادة فحص وتوثيق حجر كريم</h2>
                <p class="cert-subtitle">Certificate of Authenticity & Gemological Report</p>
            </div>
            <div class="cert-body">
                <div class="cert-row">
                    <span class="cert-label">رقم الشهادة الموثق</span>
                    <span class="cert-value text-yellow-500 font-mono" id="cert-number">#GIA-9837482</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">نوع الحجر الكريم</span>
                    <span class="cert-value" id="cert-stone-name">ياقوت أحمر طبيعي (Ruby)</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">الوزن بالقيراط</span>
                    <span class="cert-value" id="cert-weight">4.25 قيراط (Carats)</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">درجة النقاء والوضوح</span>
                    <span class="cert-value" id="cert-clarity">VVS1 - نقي جداً</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">القطع والشكل</span>
                    <span class="cert-value" id="cert-cut">قطع وسادة بيضاوي (Oval Cushion)</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">المنشأ / المصدر</span>
                    <span class="cert-value text-yellow-500" id="cert-origin">سريلانكا (Ceylon)</span>
                </div>
                <div class="cert-row">
                    <span class="cert-label">حالة التوثيق</span>
                    <span class="cert-value text-green-500" id="cert-status"><i class="fas fa-check-circle ml-1"></i>نشط وموثق</span>
                </div>
            </div>
            <div class="cert-footer">
                <div class="cert-seal-gold">
                    جوهرة<br>ضمان 100%
                </div>
                <div class="cert-qr-container">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://jawharah.luxury/verify/GIA-9837482" alt="QR Verify" class="w-full h-full">
                </div>
            </div>
        </div>
    </div>

    <!-- تحميل ملف الـ API والتحقق من التوكن وتوجيه الأزرار -->
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/notifications.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // تفعيل قائمة الجوال
            const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            if (mobileMenuToggle && mobileMenu) {
                mobileMenuToggle.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // فحص المصادقة وعرض زر لوحة التحكم والمحفظة
            if (typeof api !== 'undefined') {
                const container = document.getElementById('auth-buttons-container');
                const walletContainer = document.getElementById('user-wallet-container');
                if (api.isAuthenticated()) {
                    const user = api.getUser();
                    let dashboardLink = '{{ url("/seller/dashboard") }}';
                    if (user && user.roles && user.roles.some(r => r.name === 'admin')) {
                        dashboardLink = '{{ url("/admin/dashboard") }}';
                    }
                    container.innerHTML = `
                        <a href="${dashboardLink}" class="bg-yellow-500 text-white hover:bg-yellow-600 font-bold py-1.5 px-4 rounded-full text-xs transition">
                            <i class="fas fa-tachometer-alt ml-1"></i>لوحة التحكم
                        </a>
                    `;

                    // Show wallet and load simulated balance
                    if (walletContainer) {
                        walletContainer.classList.remove('hidden');
                        walletContainer.classList.add('flex');
                        if (typeof api.getUserWallet === 'function') {
                            const wallet = api.getUserWallet();
                            document.getElementById('header-wallet-balance').textContent = parseFloat(wallet.balance).toLocaleString();
                        }
                    }

                    // Sync user profile and wallet with database
                    if (typeof api.getMe === 'function') {
                        api.getMe().then(response => {
                            if (response.success && response.data) {
                                const freshUser = response.data;
                                api.saveUser(freshUser);
                                
                                // Sync wallet balance from server
                                if (typeof freshUser.wallet_balance !== 'undefined' && freshUser.wallet_balance !== null) {
                                    const wallet = api.getUserWallet();
                                    wallet.balance = parseFloat(freshUser.wallet_balance);
                                    api.saveUserWallet(wallet);
                                }
                                
                                // Update dashboard link if roles changed
                                let newDashboardLink = '{{ url("/seller/dashboard") }}';
                                if (freshUser.roles && freshUser.roles.some(r => r.name === 'admin')) {
                                    newDashboardLink = '{{ url("/admin/dashboard") }}';
                                }
                                const linkEl = container.querySelector('a');
                                if (linkEl) {
                                    linkEl.href = newDashboardLink;
                                }
                            }
                        }).catch(err => console.error('Error syncing profile:', err));
                    }
                }
            }
        });

        function showCertModal(stoneName, certNo, weight, clarity, cut, origin) {
            document.getElementById('cert-stone-name').textContent = stoneName || 'ياقوت أزرق فاخر';
            document.getElementById('cert-number').textContent = certNo || '#GIA-9837482';
            document.getElementById('cert-weight').textContent = weight || '3.50 قيراط';
            document.getElementById('cert-clarity').textContent = clarity || 'VVS1 - نقي جداً';
            document.getElementById('cert-cut').textContent = cut || 'قطع كوشون ممتاز';
            document.getElementById('cert-origin').textContent = origin || 'سريلانكا';
            
            const qrImg = document.querySelector('.cert-qr-container img');
            if (qrImg) {
                qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=https://jawharah.luxury/verify/${encodeURIComponent(certNo || 'GIA-9837482')}`;
            }

            const modal = document.getElementById('cert-verification-modal');
            if (modal) {
                modal.classList.add('open');
            }
        }

        function closeCertModal() {
            const modal = document.getElementById('cert-verification-modal');
            if (modal) {
                modal.classList.remove('open');
            }
        }
        window.showCertModal = showCertModal;
        window.closeCertModal = closeCertModal;
    </script>
    @yield('scripts')
</body>
</html>
