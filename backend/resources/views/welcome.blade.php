@extends('layouts.app')

@section('title', 'جوهرة | منصة المزادات الفاخرة')

@section('styles')
<style>
    :root {
        --gold: #D4AF37;
        --dark-gold: #B8860B;
        --gold-light: #F5D76E;
        --bg-dark: #080810;
        --border-gold: rgba(212,175,55,0.22);
    }

    /* ===== SPLASH SCREEN ===== */
    #welcome-splash {
        position: fixed; inset: 0; z-index: 9998;
        background: radial-gradient(ellipse at center, #0d0d1a 0%, #040408 100%);
        display: flex; align-items: center; justify-content: center; flex-direction: column;
        transition: opacity 0.9s ease, visibility 0.9s ease;
    }
    #welcome-splash.fade-out { opacity: 0; visibility: hidden; }

    .splash-ring {
        width: 170px; height: 170px; border-radius: 50%;
        background: conic-gradient(from 0deg, #D4AF37, #F5D76E, #B8860B, #D4AF37);
        animation: spinRing 2s linear infinite;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .splash-ring::before {
        content: ''; position: absolute; inset: 7px; border-radius: 50%;
        background: radial-gradient(ellipse at center, #0d0d1a 60%, #1a1a3a 100%);
    }
    .splash-icon {
        position: relative; z-index: 1; font-size: 52px;
        background: linear-gradient(135deg, #D4AF37, #F5D76E, #B8860B);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        filter: drop-shadow(0 0 18px rgba(212,175,55,0.8));
    }
    .splash-name {
        font-size: 2.8rem; font-weight: 900; margin-top: 1.8rem;
        background: linear-gradient(135deg, #D4AF37, #F5D76E, #B8860B);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        opacity: 0; animation: fadeUp 0.6s 0.5s ease forwards;
    }
    .splash-sub {
        color: rgba(212,175,55,0.55); font-size: 0.9rem; margin-top: 0.4rem;
        opacity: 0; animation: fadeUp 0.6s 0.9s ease forwards;
        letter-spacing: 0.12em;
    }
    .splash-bar {
        width: 190px; height: 2px;
        background: rgba(212,175,55,0.15); border-radius: 1px;
        margin-top: 2rem; overflow: hidden;
        opacity: 0; animation: fadeUp 0.6s 1.1s ease forwards;
    }
    .splash-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #D4AF37, #F5D76E);
        animation: fillBar 1.8s 1.3s ease forwards; width: 0;
    }

    /* ===== HERO SLIDER OVERRIDES ===== */
    .parallax {
        background: radial-gradient(ellipse at center, rgba(212, 175, 55, 0.08) 0%, transparent 70%),
                    var(--bg-tertiary) !important;
        border-bottom: 1px solid var(--border-gold);
        transition: background-color 0.3s;
    }

    /* ===== LUXURY CARDS ===== */
    .card-hover {
        background: var(--bg-secondary) !important;
        border: 1px solid var(--border-color) !important;
        transition: all 0.4s cubic-bezier(.175,.885,.32,1.275);
    }
    .card-hover:hover {
        transform: translateY(-8px) scale(1.015);
        border-color: rgba(212,175,55,0.55) !important;
        box-shadow: 0 25px 50px rgba(0,0,0,0.15), 0 0 30px rgba(212,175,55,0.1) !important;
    }
    .dark .card-hover:hover {
        box-shadow: 0 25px 50px rgba(0,0,0,0.6), 0 0 30px rgba(212,175,55,0.15) !important;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes spinRing { to { transform:rotate(360deg); } }
    @keyframes fadeUp { from { opacity:0;transform:translateY(18px); } to { opacity:1;transform:translateY(0); } }
    @keyframes fillBar { from { width:0; } to { width:100%; } }
    @keyframes glowPulse { 0%,100%{box-shadow:0 0 8px rgba(212,175,55,.4);} 50%{box-shadow:0 0 24px rgba(212,175,55,.8);} }

    /* For suppliers / trusted members card */
    .supplier-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    .supplier-card:hover {
        border-color: var(--gold);
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        transform: translateY(-4px);
    }

    /* ===== TICKER MARQUEE AD ANIMATIONS ===== */
    @keyframes slide-ltr {
        0% { transform: translate3d(-50%, 0, 0); }
        100% { transform: translate3d(0, 0, 0); }
    }
    @keyframes slide-rtl {
        0% { transform: translate3d(0, 0, 0); }
        100% { transform: translate3d(-50%, 0, 0); }
    }
    .animate-slide-ltr {
        display: flex;
        width: max-content;
        animation: slide-ltr 35s linear infinite;
    }
    .animate-slide-rtl {
        display: flex;
        width: max-content;
        animation: slide-rtl 35s linear infinite;
    }
    .ad-marquee-container {
        position: relative;
    }
    .ad-marquee-container::before, .ad-marquee-container::after {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        width: 150px;
        z-index: 10;
        pointer-events: none;
        transition: background 0.3s;
    }
    .ad-marquee-container::before {
        left: 0;
        background: linear-gradient(to right, var(--bg-tertiary) 0%, transparent 100%);
    }
    .ad-marquee-container::after {
        right: 0;
        background: linear-gradient(to left, var(--bg-tertiary) 0%, transparent 100%);
    }
    .ticker-wrap {
        overflow: hidden;
        width: 100%;
        display: flex;
        direction: ltr !important;
    }
</style>
@endsection

@section('content')
{{-- ===== SPLASH SCREEN ===== --}}
<div id="welcome-splash">
    <canvas id="splash-canvas" style="position:absolute;inset:0;pointer-events:none;"></canvas>
    <div style="position:relative;z-index:1;text-align:center;">
        <div class="splash-ring"><i class="fas fa-gem splash-icon"></i></div>
        <div class="splash-name">جوهرة</div>
        <div class="splash-sub">JAWHARA · PRECIOUS STONES</div>
        <div class="splash-bar"><div class="splash-bar-fill"></div></div>
    </div>
</div>
    <!-- السلايدر الرئيسي -->
    <section class="relative py-20 md:py-32 overflow-hidden bg-tertiary flex items-center min-h-[500px]">
        <!-- الخلفيات المتحركة (Slideshow) -->
        <div class="absolute inset-0 z-0">
            <div class="hero-slide absolute inset-0 opacity-100 transition-opacity duration-1000 ease-in-out bg-cover bg-center" 
                 data-badge="💎 مزادات موثقة وشهادات GIA"
                 data-title="سوق الألماس الفاخر والمضمون" 
                 data-subtitle="مزادات حية لأندر قطع الألماس الوردي والملون بشهادات توثيق دولية معتمدة."
                 style="background-image: linear-gradient(to left, rgba(8,8,16,0.85), rgba(8,8,16,0.4)), url('{{ asset('imges/pink_diamond_hero.png') }}');"></div>
            
            <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out bg-cover bg-center" 
                 data-badge="🟢 ضمان وأمان كامل للمشتري"
                 data-title="أندر الزمرد الكولومبي النقي" 
                 data-subtitle="قطع حصرية من قلب كولومبيا بنقاء استثنائي وضمان مالي محمي عبر الحساب الوسيط."
                 style="background-image: linear-gradient(to left, rgba(8,8,16,0.85), rgba(8,8,16,0.4)), url('{{ asset('imges/colombian_emerald_hero.png') }}');"></div>
            
            <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out bg-cover bg-center" 
                 data-badge="💙 شحن مؤمن وتوصيل سريع"
                 data-title="ياقوت أزرق سريلانكي ساحر" 
                 data-subtitle="اقتنِ الفخامة بقطع الياقوت الملكي الأزرق مع خدمة شحن مؤمن بالكامل وتوصيل لباب منزلك."
                 style="background-image: linear-gradient(to left, rgba(8,8,16,0.85), rgba(8,8,16,0.4)), url('{{ asset('imges/blue_sapphire_hero.png') }}');"></div>
            
            <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out bg-cover bg-center" 
                 data-badge="🔴 فحص وتدقيق من خبراء الجيولوجيا"
                 data-title="ياقوت بورمي بلون دم الحمام" 
                 data-subtitle="قطع استثنائية تم فحصها بدقة وتوثيقها لتضمن لك أعلى درجات الأصالة والاستثمار الآمن."
                 style="background-image: linear-gradient(to left, rgba(8,8,16,0.85), rgba(8,8,16,0.4)), url('{{ asset('imges/burmese_ruby_hero.png') }}');"></div>
            
            <div class="hero-slide absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out bg-cover bg-center" 
                 data-badge="✨ قطع فريدة لا تتكرر"
                 data-title="العقيق والـأوبال الناري النادر" 
                 data-subtitle="استكشف سحر الطبيعة بألوان نارية ساحرة تعزز مجموعتك الفريدة من الأحجار الكريمة الحصرية."
                 style="background-image: linear-gradient(to left, rgba(8,8,16,0.85), rgba(8,8,16,0.4)), url('{{ asset('imges/black_opal_hero.png') }}');"></div>
        </div>

        <div class="particles-container" id="particles-container"></div>

        <div class="absolute inset-0 bg-black bg-opacity-30 z-10"></div>

        <div class="relative z-20 container mx-auto px-4">
            <div class="max-w-2xl text-right">
                <div id="hero-badge" class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold mb-6 transition-all duration-500 transform translate-y-0 opacity-100"
                    style="background:rgba(212,175,55,0.14);border:1px solid rgba(212,175,55,0.3);color:#D4AF37;">
                    💎 مزادات موثقة وشهادات GIA
                </div>
                <h1 id="hero-title" class="text-3xl md:text-5xl font-bold mb-4 gold-text leading-tight transition-all duration-500 transform translate-y-0 opacity-100">سوق الألماس الفاخر والمضمون</h1>
                <p id="hero-subtitle" class="text-lg md:text-xl mb-8 text-white opacity-95 transition-all duration-500 transform translate-y-0 opacity-100">مزادات حية لأندر قطع الألماس الوردي والملون بشهادات توثيق دولية معتمدة.</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-start">
                    <a href="{{ url('/shop') }}" class="gold-gradient text-black text-center font-bold py-3 px-8 rounded-full ripple shine-effect transition transform hover:scale-105">
                        استكشف الآن
                    </a>
                    <a href="{{ url('/auctions') }}" class="bg-transparent text-center border-2 border-yellow-500 text-yellow-500 font-bold py-3 px-8 rounded-full hover:bg-yellow-500 hover:text-black transition transform hover:scale-105">
                        تصفح المزادات
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- إعلانات متحركة من اليسار إلى اليمين -->
    <section class="py-10 overflow-hidden bg-tertiary border-b border-color ad-marquee-container">
        <div class="ticker-wrap select-none">
            <div class="animate-slide-ltr gap-6" id="ads-ltr-container">
                <!-- جاري التحميل... -->
                <div class="inline-block relative rounded-2xl overflow-hidden border border-color min-w-[320px] md:min-w-[450px] h-[180px] bg-secondary flex-shrink-0 mx-3 shadow-lg animate-pulse"></div>
            </div>
        </div>
    </section>

    <!-- حاوية مقاييس الضمان والتميز -->
    <div class="container mx-auto px-4 my-10 py-6 border-b border-color">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="stat-sep text-center py-4 px-6 border-l border-color last:border-l-0">
                <div class="text-xl font-black" style="color:#D4AF37;"> GIA معتمد</div>
                <div class="text-xs mt-1 text-secondary">جميع الأحجار موثقة دولياً</div>
            </div>
            <div class="stat-sep text-center py-4 px-6 border-l border-color last:border-l-0">
                <div class="text-xl font-black" style="color:#D4AF37;">🔒 Escrow</div>
                <div class="text-xs mt-1 text-secondary">ضمان مالي محمي</div>
            </div>
            <div class="stat-sep text-center py-4 px-6 border-l border-color last:border-l-0">
                <div class="text-xl font-black" style="color:#D4AF37;">🚀 شحن عالمي</div>
                <div class="text-xs mt-1 text-secondary">توصيل مؤمّن لـ 48 دولة</div>
            </div>
            <div class="stat-sep text-center py-4 px-6">
                <div class="text-xl font-black" style="color:#D4AF37;">↩️ إرجاع مضمون</div>
                <div class="text-xs mt-1 text-secondary">48 ساعة للفحص والإرجاع</div>
            </div>
        </div>
    </div>

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

    <!-- إعلانات متحركة من اليسار إلى اليمين (الصف الثاني) -->
    <section class="py-10 overflow-hidden bg-tertiary border-b border-color ad-marquee-container">
        <div class="ticker-wrap select-none">
            <div class="animate-slide-ltr gap-6" id="ads-rtl-container">
                <!-- جاري التحميل... -->
                <div class="inline-block relative rounded-2xl overflow-hidden border border-color min-w-[320px] md:min-w-[450px] h-[180px] bg-secondary flex-shrink-0 mx-3 shadow-lg animate-pulse"></div>
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

    <!-- عرض ترويجي خاص (مجموعة العقيق) -->
    <section class="container mx-auto px-4 py-8 reveal">
        <div class="relative overflow-hidden cursor-pointer" style="border-radius:22px;border:1px solid rgba(212,175,55,0.2);min-height:240px;background:linear-gradient(135deg,#0d0a20,#1a0d3a,#0a0d1a);transition:transform .3s ease;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
            <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,8,18,0.95) 42%,rgba(8,8,18,0.35) 100%);z-index:1;"></div>
            <img src="{{ asset('imges/عقيق ناري استرالي.jpg') }}" alt="عقيق ناري" style="position:absolute;right:0;top:0;width:55%;height:100%;object-fit:cover;">
            <div style="position:relative;z-index:2;" class="p-10">
                <div class="text-xs font-bold mb-3" style="color:#D4AF37;letter-spacing:.2em;">✦ عرض الأسبوع ✦</div>
                <h2 class="text-3xl md:text-4xl font-black text-white mb-3">مجموعة العقيق النادرة<br><span style="color:#D4AF37;">من اليمن وأستراليا</span></h2>
                <p class="mb-6 max-w-md" style="color:#94a3b8;">أحجار عقيق حصرية بألوان نادرة لا تتكرر. كل حجر قصة وتاريخ وأصالة.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/shop') }}" class="px-8 py-3 rounded-full font-bold text-black text-center inline-block" style="background: linear-gradient(135deg, rgb(212, 175, 55), rgb(184, 134, 11)); transition: transform 0.3s; transform: scale(1);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        اكتشف المجموعة <i class="fas fa-arrow-left mr-1"></i>
                    </a>
                    <div>
                        <div class="text-2xl font-black" style="color:#D4AF37;">خصم 15%</div>
                        <div class="text-xs" style="color:#64748b;">لفترة محدودة</div>
                    </div>
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
        // ============================================================
        // SPLASH SCREEN
        // ============================================================
        (function() {
            const splash = document.getElementById('welcome-splash');
            const isSplashShown = sessionStorage.getItem('jawhara_splash_shown');

            if (isSplashShown) {
                if (splash) {
                    splash.style.display = 'none';
                }
                document.body.style.overflow = '';
                return;
            }

            document.body.style.overflow = 'hidden';
            const canvas = document.getElementById('splash-canvas');
            if (!canvas) return;
            const ctx = canvas.getContext('2d');
            canvas.width = window.innerWidth; canvas.height = window.innerHeight;
            const pts = Array.from({length:80},()=>({
                x:Math.random()*canvas.width,
                y:Math.random()*canvas.height,
                r:Math.random()*2+.4,
                vx:(Math.random()-.5)*.4,
                vy:-.3-Math.random()*.5,
                a:.3+Math.random()*.5
            }));
            function drawSplash() {
                ctx.clearRect(0,0,canvas.width,canvas.height);
                pts.forEach(p=>{
                    p.x+=p.vx;
                    p.y+=p.vy;
                    if(p.y<-10){
                        p.y=canvas.height+10;
                        p.x=Math.random()*canvas.width;
                    }
                    ctx.beginPath();
                    ctx.arc(p.x,p.y,p.r,0,Math.PI*2);
                    ctx.fillStyle=`rgba(212,175,55,${p.a})`;
                    ctx.fill();
                });
                requestAnimationFrame(drawSplash);
            }
            drawSplash();

            setTimeout(()=>{
                sessionStorage.setItem('jawhara_splash_shown', 'true');
                if (splash) {
                    splash.classList.add('fade-out');
                    setTimeout(()=>{
                        splash.style.display='none';
                        document.body.style.overflow='';
                    }, 850);
                }
            }, 3200);
        })();

        // ============================================================
        // HERO SLIDESHOW
        // ============================================================
        (function() {
            let currentSlide = 0;
            const slides = document.querySelectorAll('.hero-slide');
            const heroTitle = document.getElementById('hero-title');
            const heroSubtitle = document.getElementById('hero-subtitle');
            const heroBadge = document.getElementById('hero-badge');

            if (slides.length === 0) return;

            function updateHeroText(slide) {
                const title = slide.getAttribute('data-title');
                const subtitle = slide.getAttribute('data-subtitle');
                const badge = slide.getAttribute('data-badge');

                // Fade out text
                if (heroTitle) {
                    heroTitle.classList.remove('opacity-100', 'translate-y-0');
                    heroTitle.classList.add('opacity-0', 'translate-y-2');
                }
                if (heroSubtitle) {
                    heroSubtitle.classList.remove('opacity-100', 'translate-y-0');
                    heroSubtitle.classList.add('opacity-0', 'translate-y-2');
                }
                if (heroBadge) {
                    heroBadge.classList.remove('opacity-100', 'translate-y-0');
                    heroBadge.classList.add('opacity-0', 'translate-y-2');
                }

                // Wait for fade out to complete, then update and fade in
                setTimeout(() => {
                    if (heroTitle && title) heroTitle.textContent = title;
                    if (heroSubtitle && subtitle) heroSubtitle.textContent = subtitle;
                    if (heroBadge && badge) {
                        heroBadge.innerHTML = badge;
                        heroBadge.style.display = 'inline-flex';
                    } else if (heroBadge) {
                        heroBadge.style.display = 'none';
                    }

                    // Fade in text
                    if (heroTitle) {
                        heroTitle.classList.remove('opacity-0', 'translate-y-2');
                        heroTitle.classList.add('opacity-100', 'translate-y-0');
                    }
                    if (heroSubtitle) {
                        heroSubtitle.classList.remove('opacity-0', 'translate-y-2');
                        heroSubtitle.classList.add('opacity-100', 'translate-y-0');
                    }
                    if (heroBadge && badge) {
                        heroBadge.classList.remove('opacity-0', 'translate-y-2');
                        heroBadge.classList.add('opacity-100', 'translate-y-0');
                    }
                }, 400);
            }

            // Set initial slide text
            updateHeroText(slides[currentSlide]);

            setInterval(() => {
                slides[currentSlide].classList.remove('opacity-100');
                slides[currentSlide].classList.add('opacity-0');
                currentSlide = (currentSlide + 1) % slides.length;
                slides[currentSlide].classList.remove('opacity-0');
                slides[currentSlide].classList.add('opacity-100');

                updateHeroText(slides[currentSlide]);
            }, 6000); // Change image every 6 seconds
        })();

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

            // 0. جلب منتجات الإعلانات المتحركة
            try {
                const adsResponse = await api.getProducts({ is_featured: 1, limit: 12 });
                const allAds = adsResponse.data?.data || adsResponse.data || [];
                const activeAds = allAds.filter(p => p.is_active);

                const ltrContainer = document.getElementById('ads-ltr-container');
                const rtlContainer = document.getElementById('ads-rtl-container');

                if (activeAds.length > 0) {
                    let displayAds = [...activeAds];
                    // Ensure we have enough items for continuous carousel scrolling
                    while (displayAds.length < 12) {
                        displayAds = [...displayAds, ...activeAds];
                    }

                    // LTR ads
                    ltrContainer.innerHTML = displayAds.map(product => {
                        const img = product.images && product.images.length > 0 ? getImageUrl(product.images[0]) : '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                        return `
                        <a href="{{ url('/shop') }}?id=${product.id}" class="inline-block relative rounded-2xl overflow-hidden border border-color min-w-[300px] md:min-w-[420px] h-[180px] bg-[#0d0d15] flex-shrink-0 mx-3 shadow-lg hover:border-gold transition-colors duration-300">
                            <img src="${img}" class="absolute inset-0 w-full h-full object-cover opacity-50">
                            <div class="absolute inset-0 bg-gradient-to-l from-black/95 via-black/80 to-black/35 p-6 flex flex-col justify-center text-right">
                                <span class="text-xs text-yellow-500 font-bold">✦ عرض مميز ✦</span>
                                <h3 class="text-lg font-black text-white mt-1 truncate">${product.name}</h3>
                                <p class="text-xs text-gray-300 mt-1 truncate">${product.origin_country || 'أحجار طبيعية معتمدة'}</p>
                                <span class="text-sm text-yellow-500 font-black mt-2">${parseFloat(product.price).toLocaleString()} ر.س</span>
                            </div>
                        </a>
                        `;
                    }).join('');

                    // RTL ads (reversed for variety)
                    const displayAdsRtl = [...displayAds].reverse();
                    rtlContainer.innerHTML = displayAdsRtl.map(product => {
                        const img = product.images && product.images.length > 0 ? getImageUrl(product.images[0]) : '{{ asset("imges/ياقوت أزرق نادر.jpeg") }}';
                        return `
                        <a href="{{ url('/shop') }}?id=${product.id}" class="inline-block relative rounded-2xl overflow-hidden border border-color min-w-[300px] md:min-w-[420px] h-[180px] bg-[#0d0d15] flex-shrink-0 mx-3 shadow-lg hover:border-gold transition-colors duration-300">
                            <img src="${img}" class="absolute inset-0 w-full h-full object-cover opacity-50">
                            <div class="absolute inset-0 bg-gradient-to-l from-black/95 via-black/80 to-black/35 p-6 flex flex-col justify-center text-right">
                                <span class="text-xs text-yellow-500 font-bold">✦ عرض خاص ✦</span>
                                <h3 class="text-lg font-black text-white mt-1 truncate">${product.name}</h3>
                                <p class="text-xs text-gray-300 mt-1 truncate">${product.origin_country || 'أحجار طبيعية معتمدة'}</p>
                                <span class="text-sm text-yellow-500 font-black mt-2">${parseFloat(product.price).toLocaleString()} ر.س</span>
                            </div>
                        </a>
                        `;
                    }).join('');
                } else {
                    const fallbackHtml = `
                    <div class="inline-block relative rounded-2xl overflow-hidden border border-color min-w-[300px] md:min-w-[420px] h-[180px] bg-[#0d0d15] flex-shrink-0 mx-3 shadow-lg flex items-center justify-center text-center">
                        <span class="text-secondary text-sm">شاهد الأحجار المميزة بالمتجر</span>
                    </div>`;
                    ltrContainer.innerHTML = fallbackHtml;
                    rtlContainer.innerHTML = fallbackHtml;
                }
            } catch (adError) {
                console.error("Error loading ads:", adError);
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
