@extends('layouts.app')

@section('title', 'المتجر | جوهرة — أحجار كريمة فاخرة')

@section('styles')
<style>
    :root {
        --gold: #D4AF37;
        --dark-gold: #B8860B;
        --gold-light: #F5D76E;
        --bg-dark: #080810;
        --bg-card: #0f0f1a;
        --bg-card2: #14141f;
        --border-gold: rgba(212,175,55,0.22);
    }

    body {
        background: var(--bg-dark) !important;
        color: #e2e8f0 !important;
    }

    /* ===== SPLASH SCREEN ===== */
    #shop-splash {
        position: fixed; inset: 0; z-index: 9998;
        background: radial-gradient(ellipse at center, #0d0d1a 0%, #040408 100%);
        display: flex; align-items: center; justify-content: center; flex-direction: column;
        transition: opacity 0.9s ease, visibility 0.9s ease;
    }
    #shop-splash.fade-out { opacity: 0; visibility: hidden; }

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

    /* ===== TICKER ===== */
    .ticker-wrap { overflow: hidden; white-space: nowrap; }
    .ticker-track { display: inline-block; animation: ticker 38s linear infinite; }
    .ticker-track:hover { animation-play-state: paused; }

    /* ===== HERO ===== */
    .shop-hero {
        position: relative; min-height: 90vh;
        background: radial-gradient(ellipse at 18% 55%, rgba(139,105,20,0.1) 0%, transparent 55%),
                    radial-gradient(ellipse at 82% 45%, rgba(9,20,80,0.18) 0%, transparent 55%),
                    linear-gradient(180deg, #060610 0%, #0b0b18 60%, #080810 100%);
        display: flex; align-items: center; overflow: hidden;
    }
    #hero-canvas { position: absolute; inset: 0; pointer-events: none; }
    .hero-title {
        font-size: clamp(2rem, 5.5vw, 4.6rem);
        font-weight: 900; line-height: 1.1;
        background: linear-gradient(135deg, #fff 0%, #D4AF37 40%, #F5D76E 65%, #B8860B 100%);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    }

    /* ===== GEM FLOATS ===== */
    .gf { position: absolute; border-radius: 50%; pointer-events: none; }
    .gf1 { top:10%; right:4%;  width:78px; height:78px; opacity:.12; animation: floatGem 7s ease-in-out infinite; }
    .gf2 { bottom:18%; right:14%; width:58px; height:58px; opacity:.1;  animation: floatGem 9s 1s ease-in-out infinite; }
    .gf3 { top:28%; left:6%;  width:48px; height:48px; opacity:.10; animation: floatGem 6s 2s ease-in-out infinite; }
    .gf4 { bottom:28%; left:10%; width:66px; height:66px; opacity:.08; animation: floatGem 11s .5s ease-in-out infinite; }

    /* ===== STATS STRIP ===== */
    .stats-strip {
        background: linear-gradient(90deg,#0c0c18,#14142a,#0c0c18);
        border-top: 1px solid rgba(212,175,55,0.12);
        border-bottom: 1px solid rgba(212,175,55,0.12);
    }
    @media (min-width: 768px) {
        .stat-sep {
            border-left: 1px solid rgba(212,175,55,0.1) !important;
        }
        .stat-sep:last-child {
            border-left: none !important;
        }
    }

    /* ===== PROMO BANNERS ===== */
    .promo-card {
        border-radius: 20px; overflow: hidden; position: relative;
        cursor: pointer; transition: transform 0.35s ease, box-shadow 0.35s ease;
    }
    .promo-card:hover { transform: scale(1.018); box-shadow: 0 24px 50px rgba(0,0,0,0.5); }

    /* ===== CATEGORY PILLS ===== */
    .cat-pill {
        padding: 8px 20px; border-radius: 50px; font-weight: 600; font-size: 0.84rem;
        border: 1px solid rgba(212,175,55,0.2); cursor: pointer;
        transition: all 0.28s ease; color: #94a3b8; white-space: nowrap;
        background: rgba(212,175,55,0.04);
    }
    .cat-pill:hover, .cat-pill.active {
        background: linear-gradient(135deg,#D4AF37,#B8860B);
        color: #000; border-color: transparent;
        box-shadow: 0 4px 14px rgba(212,175,55,0.3);
    }

    /* ===== FILTER SIDEBAR ===== */
    .filter-sidebar {
        background: linear-gradient(180deg, #0f0f1a, #0b0b15);
        border: 1px solid var(--border-gold); border-radius: 20px;
    }
    @media (min-width: 768px) {
        #filter-sidebar {
            position: sticky;
            top: 120px;
        }
    }

    /* ===== PRODUCT CARDS ===== */
    .gem-card {
        background: linear-gradient(135deg, #0f0f1a 0%, #14141f 100%);
        border: 1px solid var(--border-gold); border-radius: 20px;
        overflow: hidden; position: relative;
        transition: all 0.4s cubic-bezier(.175,.885,.32,1.275);
        opacity: 0; transform: translateY(35px);
    }
    .gem-card.visible { opacity: 1; transform: translateY(0); }
    .gem-card:hover {
        transform: translateY(-11px) scale(1.018);
        border-color: rgba(212,175,55,0.55);
        box-shadow: 0 28px 56px rgba(0,0,0,0.5), 0 0 38px rgba(212,175,55,0.13);
    }
    .gem-img-box {
        position: relative; overflow: hidden; height: 215px;
        background: linear-gradient(135deg,#0a0a15,#121225);
    }
    .gem-img-box img {
        width: 100%; height: 100%; object-fit: cover;
        transition: transform 0.6s cubic-bezier(.25,.46,.45,.94);
    }
    .gem-card:hover .gem-img-box img { transform: scale(1.11); }
    .gem-shine {
        position: absolute; inset: 0;
        background: linear-gradient(135deg, transparent 30%, rgba(255,255,255,0.07) 50%, transparent 70%);
        transform: translateX(-100%); transition: transform 0.55s ease;
    }
    .gem-card:hover .gem-shine { transform: translateX(100%); }

    .gem-badge {
        position: absolute; top: 11px; right: 11px;
        padding: 3px 9px; border-radius: 20px; font-size: .68rem; font-weight: 700;
        backdrop-filter: blur(8px);
    }
    .cert-dot {
        position: absolute; top: 8px; left: 8px;
        width: 40px; height: 40px;
        background: linear-gradient(135deg,#D4AF37,#B8860B); border-radius: 50%;
        display: flex; align-items: center; justify-content: center; font-size: 15px; color: #000;
        box-shadow: 0 0 14px rgba(212,175,55,0.6);
        animation: glowPulse 2s ease-in-out infinite;
        cursor: pointer;
    }

    .add-btn {
        background: linear-gradient(135deg,#D4AF37 0%,#B8860B 100%);
        color: #000; font-weight: 700; border: none; border-radius: 12px;
        padding: 10px 14px; width: 100%; cursor: pointer;
        position: relative; overflow: hidden;
        transition: all 0.3s ease; font-family: 'Cairo', sans-serif; font-size: .87rem;
    }
    .add-btn::before {
        content: ''; position: absolute; top: 0; left: -100%;
        width: 100%; height: 100%;
        background: linear-gradient(90deg,transparent,rgba(255,255,255,0.3),transparent);
        transition: left 0.4s ease;
    }
    .add-btn:hover { transform: scale(1.03); box-shadow: 0 8px 24px rgba(212,175,55,0.4); }
    .add-btn:hover::before { left: 100%; }
    .add-btn:active { transform: scale(0.97); }

    /* ===== SECTION TITLE ===== */
    .sec-title {
        font-size: 1.75rem; font-weight: 900;
        background: linear-gradient(135deg,#D4AF37,#F5D76E);
        -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        display: inline-block;
    }
    .sec-line { height: 3px; width: 55px; background: linear-gradient(90deg,#D4AF37,transparent); border-radius: 2px; margin-top: 5px; }

    /* ===== SCROLL REVEAL ===== */
    .reveal { opacity: 0; transform: translateY(35px); transition: opacity .65s ease, transform .65s ease; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .rv1 { transition-delay: .08s; }
    .rv2 { transition-delay: .17s; }
    .rv3 { transition-delay: .26s; }

    /* ===== TOAST ===== */
    .gem-toast {
        background: linear-gradient(135deg,#0f0f1a,#1a1a2e);
        border: 1px solid rgba(212,175,55,0.4); border-radius: 14px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.6), 0 0 18px rgba(212,175,55,0.1);
    }

    /* ===== CART SIDEBAR ===== */
    #cart-sidebar {
        background: linear-gradient(180deg,#0c0c1a,#08080f);
        border-right: 1px solid rgba(212,175,55,0.18);
    }

    /* ===== SCROLLBAR ===== */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #080810; }
    ::-webkit-scrollbar-thumb { background: #D4AF37; border-radius: 3px; }
    ::-webkit-scrollbar-thumb:hover { background: #B8860B; }

    /* ===== SIDEBAR MOBILE ===== */
    @media (max-width: 768px) {
        #filter-sidebar {
            position: fixed; top: 0; right: -100%; width: 85%; height: 100%;
            transition: right 0.3s ease; z-index: 60; overflow-y: auto;
        }
        #filter-sidebar.open { right: 0; }
        .sidebar-overlay { position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:55;display:none; }
        .sidebar-overlay.open { display:block; }
    }

    /* ===== ANIMATIONS ===== */
    @keyframes spinRing { to { transform:rotate(360deg); } }
    @keyframes fadeUp { from { opacity:0;transform:translateY(18px); } to { opacity:1;transform:translateY(0); } }
    @keyframes fillBar { from { width:0; } to { width:100%; } }
    @keyframes ticker { from { transform:translateX(0); } to { transform:translateX(-50%); } }
    @keyframes floatGem { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-18px);} }
    @keyframes glowPulse { 0%,100%{box-shadow:0 0 8px rgba(212,175,55,.4);} 50%{box-shadow:0 0 24px rgba(212,175,55,.8);} }
    @keyframes spin { to { transform:rotate(360deg); } }

    /* ===== OVERRIDE LAYOUT BG ===== */
    footer { display: none !important; } /* hide the layout footer, we add our own */

    /* ===== RESPONSIVE ADJUSTMENTS ===== */
    @media (max-width: 1024px) {
        /* Hide floating gems to prevent overlap with content on smaller screens */
        .gf {
            display: none !important;
        }
        /* Adjust hero minimum height and padding */
        .shop-hero {
            min-height: auto;
            padding-top: 40px;
            padding-bottom: 40px;
        }
        /* Align and scale down hero elements */
        #hero-gallery {
            margin-top: 20px;
            width: 100%;
        }
    }

    @media (max-width: 768px) {
        /* Stats strip borders responsive */
        .stat-sep {
            border-right: none !important;
            border-left: none !important;
            border-bottom: 1px solid rgba(212,175,55,0.1);
        }
        .stat-sep:nth-child(odd) {
            border-left: 1px solid rgba(212,175,55,0.1) !important;
        }
        .stat-sep:nth-last-child(-n+2) {
            border-bottom: none;
        }

        /* Promo card adjustments */
        .promo-card {
            height: auto !important;
            min-height: 200px;
        }
        .promo-card div[style*="padding:30px"] {
            padding: 20px !important;
        }
        .promo-card h3 {
            font-size: 1.25rem !important;
        }
    }

    @media (max-width: 480px) {
        /* Mobile stats strip text scaling */
        .stats-strip {
            padding: 10px 0 !important;
        }
        .stat-sep {
            padding: 8px 4px !important;
        }
        .stat-sep .text-xl {
            font-size: 1rem !important;
        }
        .stat-sep .text-xs {
            font-size: 0.65rem !important;
        }

        /* Hide scrollbar for category pills scroll */
        #cat-pills-bar::-webkit-scrollbar {
            display: none !important;
        }
        #cat-pills-bar {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
    }
</style>
@endsection

@section('content')

{{-- ===== SPLASH SCREEN ===== --}}
<div id="shop-splash">
    <canvas id="splash-canvas" style="position:absolute;inset:0;pointer-events:none;"></canvas>
    <div style="position:relative;z-index:1;text-align:center;">
        <div class="splash-ring"><i class="fas fa-gem splash-icon"></i></div>
        <div class="splash-name">جوهرة</div>
        <div class="splash-sub">JAWHARA · PRECIOUS STONES</div>
        <div class="splash-bar"><div class="splash-bar-fill"></div></div>
    </div>
</div>


{{-- ===== HERO ===== --}}
<section class="shop-hero">
    <canvas id="hero-canvas"></canvas>

    {{-- Floating gem decoration --}}
    <div class="gf gf1"><img src="{{ asset('imges/ألماس وردي نادر.jpeg') }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>
    <div class="gf gf2"><img src="{{ asset('imges/زمرد كولومبي نقي.jpg') }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>
    <div class="gf gf3"><img src="{{ asset('imges/ياقوت أزرق نادر.jpeg') }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>
    <div class="gf gf4"><img src="{{ asset('imges/توباز أزرق برازيلي.png') }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:50%;"></div>

    <div class="container mx-auto px-4 py-16" style="position:relative;z-index:1;">
        <div class="flex flex-col lg:flex-row items-center gap-12">

            {{-- Hero Text --}}
            <div class="lg:w-1/2 text-center lg:text-right" id="hero-text" style="opacity:0;transform:translateX(40px);">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold mb-6"
                    style="background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.3);color:#D4AF37;">
                    <span class="w-2 h-2 bg-green-400 rounded-full" style="animation:glowPulse 1.5s infinite;display:inline-block;box-shadow:0 0 6px #4ade80;"></span>
                    متجر الأحجار الكريمة رقم #1 في المملكة
                </div>
                <h2 class="hero-title mb-6">اكتشف كنوز<br><span style="background:linear-gradient(135deg,#D4AF37,#F5D76E,#B8860B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">الأرض النادرة</span></h2>
                <p class="text-lg mb-8 leading-relaxed" style="color:#94a3b8;">
                    مجموعة استثنائية من أندر الأحجار الكريمة حول العالم.<br>
                    كل حجر معتمد ومضمون الأصالة بشهادات عالمية.
                </p>
                <div class="flex flex-wrap gap-4 justify-center lg:justify-start">
                    <button onclick="document.querySelector('#products-section').scrollIntoView({behavior:'smooth'})"
                        class="flex items-center gap-2 px-8 py-4 rounded-full font-bold text-black"
                        style="background:linear-gradient(135deg,#D4AF37,#B8860B);box-shadow:0 8px 24px rgba(212,175,55,0.4);transition:all .3s ease;"
                        onmouseover="this.style.opacity='.88';this.style.transform='scale(1.04)'" onmouseout="this.style.opacity='1';this.style.transform='scale(1)'">
                        <i class="fas fa-gem"></i> تسوق الآن
                    </button>
                    <a href="{{ url('/auctions') }}"
                        class="flex items-center gap-2 px-8 py-4 rounded-full font-bold transition"
                        style="border:2px solid rgba(212,175,55,0.45);color:#D4AF37;"
                        onmouseover="this.style.background='rgba(212,175,55,0.08)'" onmouseout="this.style.background=''">
                        <i class="fas fa-gavel"></i> المزادات الحية
                    </a>
                </div>
                <div class="flex gap-8 mt-10 justify-center lg:justify-start">
                    <div class="text-center">
                        <div class="text-2xl font-black counter" data-target="1200" style="color:#D4AF37;text-shadow:0 0 28px rgba(212,175,55,.5);">0</div>
                        <div class="text-xs mt-1" style="color:#64748b;">حجر في المتجر</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black counter" data-target="850" style="color:#D4AF37;text-shadow:0 0 28px rgba(212,175,55,.5);">0</div>
                        <div class="text-xs mt-1" style="color:#64748b;">عميل راضٍ</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-black counter" data-target="48" style="color:#D4AF37;text-shadow:0 0 28px rgba(212,175,55,.5);">0</div>
                        <div class="text-xs mt-1" style="color:#64748b;">دولة حول العالم</div>
                    </div>
                </div>
            </div>

            {{-- Hero Gallery --}}
            <div class="lg:w-1/2 relative" id="hero-gallery" style="opacity:0;transform:translateX(-40px);">
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-4">
                        <div class="rounded-2xl overflow-hidden" style="height:200px;border:1px solid rgba(212,175,55,0.3);box-shadow:0 20px 40px rgba(0,0,0,0.5);">
                            <img src="{{ asset('imges/ياقوت وردي نقي من ميانمار2.jpeg') }}" alt="ياقوت وردي" class="w-full h-full object-cover" style="transition:transform .7s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="rounded-2xl overflow-hidden" style="height:140px;border:1px solid rgba(212,175,55,0.2);">
                            <img src="{{ asset('imges/توباز أزرق برازيلي.png') }}" alt="توباز" class="w-full h-full object-cover" style="transition:transform .7s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                    </div>
                    <div class="space-y-4 mt-10">
                        <div class="rounded-2xl overflow-hidden" style="height:140px;border:1px solid rgba(212,175,55,0.2);">
                            <img src="{{ asset('imges/ألماس وردي نادر.jpeg') }}" alt="ألماس" class="w-full h-full object-cover" style="transition:transform .7s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                        <div class="rounded-2xl overflow-hidden" style="height:200px;border:1px solid rgba(212,175,55,0.3);box-shadow:0 20px 40px rgba(0,0,0,0.5);">
                            <img src="{{ asset('imges/زمرد كولومبي نقي.jpg') }}" alt="زمرد" class="w-full h-full object-cover" style="transition:transform .7s ease;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                        </div>
                    </div>
                </div>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:280px;height:280px;background:radial-gradient(ellipse,rgba(212,175,55,0.07),transparent);border-radius:50%;pointer-events:none;z-index:-1;"></div>
            </div>
        </div>
    </div>
</section>

{{-- ===== STATS STRIP ===== --}}
<div class="stats-strip py-6 reveal">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4">
            <div class="stat-sep text-center py-4 px-6">
                <div class="text-xl font-black" style="color:#D4AF37;"> GIA معتمد</div>
                <div class="text-xs mt-1" style="color:#64748b;">جميع الأحجار موثقة دولياً</div>
            </div>
            <div class="stat-sep text-center py-4 px-6">
                <div class="text-xl font-black" style="color:#D4AF37;">🔒 Escrow</div>
                <div class="text-xs mt-1" style="color:#64748b;">ضمان مالي محمي</div>
            </div>
            <div class="stat-sep text-center py-4 px-6">
                <div class="text-xl font-black" style="color:#D4AF37;">🚀 شحن عالمي</div>
                <div class="text-xs mt-1" style="color:#64748b;">توصيل مؤمّن لـ 48 دولة</div>
            </div>
            <div class="stat-sep text-center py-4 px-6">
                <div class="text-xl font-black" style="color:#D4AF37;">↩️ إرجاع مضمون</div>
                <div class="text-xs mt-1" style="color:#64748b;">48 ساعة للفحص والإرجاع</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== PROMO BANNERS ===== --}}
<section class="container mx-auto px-4 py-10 reveal">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Big Banner --}}
        <div class="md:col-span-2 promo-card" style="height:260px;">
            <div style="width:100%;height:100%;background:linear-gradient(135deg,#0d0620,#1a0940,#0a0d2a);position:relative;overflow:hidden;border:1px solid rgba(212,175,55,0.25);border-radius:20px;">
                <img src="{{ asset('imges/ألماس وردي نادر من جنوب أفريقيا.jpeg') }}" alt="" style="position:absolute;left:0;top:0;width:45%;height:100%;object-fit:cover;opacity:0.48;-webkit-mask-image:linear-gradient(to right,black 50%,transparent);mask-image:linear-gradient(to right,black 50%,transparent);">
                <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,0.15),rgba(13,6,32,0.92));"></div>
                <div style="position:absolute;inset:0;padding:30px;" class="flex flex-col justify-between">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold"
                        style="background:rgba(212,175,55,0.14);border:1px solid rgba(212,175,55,0.3);color:#D4AF37;width:fit-content;">
                        ⚡ عرض محدود المدة
                    </div>
                    <div>
                        <div class="text-xs mb-1" style="color:#94a3b8;">ألماس وردي فاخر · جنوب أفريقيا</div>
                        <h3 class="text-2xl font-black text-white mb-1">نادر للغاية — 2.8 قيراط</h3>
                        <div class="flex items-center gap-3 mt-2">
                            <span class="text-2xl font-black" style="color:#D4AF37;">85,000 ر.س</span>
                            <span class="text-sm line-through" style="color:#475569;">95,000 ر.س</span>
                            <span class="px-2 py-0.5 rounded text-xs font-bold bg-red-500 text-white">-11%</span>
                        </div>
                        <button class="mt-4 px-6 py-2.5 rounded-full text-sm font-bold text-black"
                            style="background:linear-gradient(135deg,#D4AF37,#B8860B);transition:transform .3s;"
                            onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                            اشترِ الآن <i class="fas fa-arrow-left mr-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Small Stack --}}
        <div class="flex flex-col gap-5">
            <div class="promo-card flex-1" style="min-height:118px;">
                <div style="width:100%;height:100%;min-height:118px;background:linear-gradient(135deg,#001a0a,#003d1a);position:relative;overflow:hidden;border:1px solid rgba(0,180,80,0.22);border-radius:20px;">
                    <img src="{{ asset('imges/زمرد كولومبي نقي عالي الجودة.jpeg') }}" alt="" style="position:absolute;right:0;top:0;width:50%;height:100%;object-fit:cover;opacity:0.48;-webkit-mask-image:linear-gradient(to left,black 40%,transparent);mask-image:linear-gradient(to left,black 40%,transparent);">
                    <div style="position:absolute;inset:0;padding:18px;">
                        <div class="text-xs font-semibold mb-1" style="color:#4ade80;"> زمرد كولومبي</div>
                        <div class="text-white font-bold text-lg">نقاء استثنائي</div>
                        <div class="font-black" style="color:#4ade80;">35,500 ر.س</div>
                    </div>
                </div>
            </div>
            <div class="promo-card flex-1" style="min-height:118px;">
                <div style="width:100%;height:100%;min-height:118px;background:linear-gradient(135deg,#001230,#002566);position:relative;overflow:hidden;border:1px solid rgba(30,100,220,0.28);border-radius:20px;">
                    <img src="{{ asset('imges/ياقوت أزرق نادر.jpeg') }}" alt="" style="position:absolute;right:0;top:0;width:50%;height:100%;object-fit:cover;opacity:0.48;-webkit-mask-image:linear-gradient(to left,black 40%,transparent);mask-image:linear-gradient(to left,black 40%,transparent);">
                    <div style="position:absolute;inset:0;padding:18px;">
                        <div class="text-xs font-semibold mb-1" style="color:#60a5fa;"> ياقوت أزرق</div>
                        <div class="text-white font-bold text-lg">سريلانكا · 3.5qt</div>
                        <div class="font-black" style="color:#60a5fa;">18,800 ر.س</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== CATEGORY PILLS ===== --}}
<div id="cat-bar" style="position:sticky;top:56px;z-index:40;background:rgba(8,8,16,0.9);backdrop-filter:blur(14px);border-bottom:1px solid rgba(212,175,55,0.1);">
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center gap-3 overflow-x-auto pb-1" id="cat-pills-bar">
            <button class="cat-pill active" data-category-id=""> الكل</button>
            <button class="cat-pill category-btn" data-category-id="1"> ألماس</button>
            <button class="cat-pill category-btn" data-category-id="2"> ياقوت</button>
            <button class="cat-pill category-btn" data-category-id="3"> زمرد</button>
            <button class="cat-pill category-btn" data-category-id="4"> ياقوت أزرق</button>
            <button class="cat-pill category-btn" data-category-id="5">عقيق</button>
            <button class="cat-pill category-btn" data-category-id="6"> توباز</button>
        </div>
    </div>
</div>

{{-- ===== MAIN PRODUCTS SECTION ===== --}}
<main class="container mx-auto px-4 py-10" id="products-section">
    <div class="flex flex-col md:flex-row gap-8">

        {{-- Filter Sidebar --}}
        <aside id="filter-sidebar" class="filter-sidebar md:w-60 lg:w-64 p-5 self-start">
            <div class="flex justify-between items-center mb-5">
                <h2 class="font-black text-lg" style="color:#D4AF37;">تصفية المنتجات</h2>
                <button id="close-sidebar-btn" class="md:hidden" style="color:#64748b;"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-6">
                <div>
                    <h3 class="font-bold text-sm mb-3" style="color:#cbd5e1;">نوع الحجر</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;"> ألماس</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;"> ياقوت</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;"> زمرد</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;"> ياقوت أزرق</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;">عقيق</span></label>
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-sm mb-3" style="color:#cbd5e1;">السعر (ر.س)</h3>
                    <div class="flex items-center gap-2">
                        <input type="number" id="price-min" placeholder="من" class="w-1/2 rounded-xl py-2 px-3 text-sm outline-none" style="background:rgba(255,255,255,0.05);border:1px solid rgba(212,175,55,0.15);color:#e2e8f0;">
                        <span style="color:#475569;">—</span>
                        <input type="number" id="price-max" placeholder="إلى" class="w-1/2 rounded-xl py-2 px-3 text-sm outline-none" style="background:rgba(255,255,255,0.05);border:1px solid rgba(212,175,55,0.15);color:#e2e8f0;">
                    </div>
                </div>
                <div>
                    <h3 class="font-bold text-sm mb-3" style="color:#cbd5e1;">بلد المنشأ</h3>
                    <div class="space-y-2">
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;">🇲🇲 ميانمار</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;">🇨🇴 كولومبيا</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;">🇿🇦 جنوب أفريقيا</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="accent-yellow-500 w-4 h-4"><span class="text-sm" style="color:#94a3b8;">🇾🇪 اليمن</span></label>
                    </div>
                </div>
                <div class="flex gap-2 pt-2">
                    <button id="apply-filters-btn" class="flex-1 py-2.5 rounded-xl text-sm font-bold text-black" style="background:linear-gradient(135deg,#D4AF37,#B8860B);">تطبيق</button>
                    <button class="flex-1 py-2.5 rounded-xl text-sm font-semibold" style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#64748b;">إعادة تعيين</button>
                </div>
            </div>
        </aside>
        <div class="sidebar-overlay" id="sidebar-overlay"></div>

        {{-- Products Grid --}}
        <div class="flex-1">
            <div class="flex justify-between items-center mb-6 reveal">
                <div class="text-sm" style="color:#64748b;">
                    جاري عرض المنتجات المتاحة
                </div>
                <div class="flex items-center gap-3">
                    <button id="mobile-filter-toggle" class="md:hidden flex items-center gap-2 px-4 py-2 rounded-full text-sm"
                        style="background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.2);color:#D4AF37;">
                        <i class="fas fa-filter"></i> فلتر
                    </button>
                    <select class="rounded-xl py-2 px-4 text-sm outline-none"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(212,175,55,0.15);color:#94a3b8;">
                        <option>الأحدث</option>
                        <option>الأعلى سعراً</option>
                        <option>الأقل سعراً</option>
                        <option>الأكثر مبيعاً</option>
                    </select>
                </div>
            </div>

            <div class="mb-4 reveal">
                <div class="sec-title">المنتجات المميزة</div>
                <div class="sec-line"></div>
            </div>

            {{-- Products injected by shop.js --}}
            <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="col-span-full text-center py-20" style="color:#64748b;">
                    <i class="fas fa-spinner fa-spin text-4xl mb-4" style="color:#D4AF37;"></i>
                    <p>جاري تحميل المعروضات...</p>
                </div>
            </div>
        </div>

    </div>
</main>

{{-- ===== FULL-WIDTH AD ===== --}}
<section class="container mx-auto px-4 py-6 reveal">
    <div class="relative overflow-hidden cursor-pointer" style="border-radius:22px;border:1px solid rgba(212,175,55,0.2);min-height:240px;background:linear-gradient(135deg,#0d0a20,#1a0d3a,#0a0d1a);transition:transform .3s ease;" onmouseover="this.style.transform='scale(1.01)'" onmouseout="this.style.transform='scale(1)'">
        <div style="position:absolute;inset:0;background:linear-gradient(90deg,rgba(8,8,18,0.95) 42%,rgba(8,8,18,0.35) 100%);z-index:1;"></div>
        <img src="{{ asset('imges/عقيق ناري استرالي.jpg') }}" alt="" style="position:absolute;right:0;top:0;width:55%;height:100%;object-fit:cover;">
        <div style="position:relative;z-index:2;" class="p-10">
            <div class="text-xs font-bold mb-3" style="color:#D4AF37;letter-spacing:.2em;">✦ عرض الأسبوع ✦</div>
            <h2 class="text-3xl md:text-4xl font-black text-white mb-3">مجموعة العقيق النادرة<br><span style="color:#D4AF37;">من اليمن وأستراليا</span></h2>
            <p class="mb-6 max-w-md" style="color:#94a3b8;">أحجار عقيق حصرية بألوان نادرة لا تتكرر. كل حجر قصة وتاريخ وأصالة.</p>
            <div class="flex items-center gap-4">
                <button class="px-8 py-3 rounded-full font-bold text-black" style="background:linear-gradient(135deg,#D4AF37,#B8860B);transition:transform .3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    اكتشف المجموعة <i class="fas fa-arrow-left mr-1"></i>
                </button>
                <div>
                    <div class="text-2xl font-black" style="color:#D4AF37;">خصم 15%</div>
                    <div class="text-xs" style="color:#64748b;">لفترة محدودة</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ===== CUSTOM FOOTER ===== --}}
<footer class="bg-secondary py-14 border-t border-color">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:linear-gradient(135deg,#D4AF37,#B8860B);">
                        <i class="fas fa-gem text-black"></i>
                    </div>
                    <span class="text-xl font-black gold-text">جوهرة</span>
                </div>
                <p class="text-sm leading-relaxed text-secondary">المنصة الرائدة لتداول الأحجار الكريمة الفاخرة بضمان الأصالة والجودة العالمية.</p>
            </div>
            <div>
                <h4 class="font-bold gold-text mb-4">المتجر</h4>
                <ul class="space-y-2 text-sm text-secondary">
                    <li><a href="{{ url('/shop') }}" style="transition:color .3s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color=''">جميع المنتجات</a></li>
                    <li><a href="{{ url('/auctions') }}" style="transition:color .3s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color=''">المزادات النشطة</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold gold-text mb-4">الدعم</h4>
                <ul class="space-y-2 text-sm text-secondary">
                    <li><a href="#" style="transition:color .3s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color=''">تواصل معنا</a></li>
                    <li><a href="#" style="transition:color .3s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color=''">الأسئلة الشائعة</a></li>
                    <li><a href="#" style="transition:color .3s;" onmouseover="this.style.color='#D4AF37'" onmouseout="this.style.color=''">سياسة الإرجاع</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-bold gold-text mb-4">النشرة البريدية</h4>
                <p class="text-sm mb-3 text-secondary">اشترك لتلقي أحدث العروض والأحجار النادرة.</p>
                <div class="flex gap-2">
                    <input type="email" placeholder="بريدك الإلكتروني" class="flex-1 rounded-xl py-2.5 px-4 text-sm outline-none"
                        style="background:rgba(255,255,255,0.05);border:1px solid rgba(212,175,55,0.2);color:var(--text-primary);">
                    <button class="px-4 py-2.5 rounded-xl font-bold text-black text-sm" style="background:linear-gradient(135deg,#D4AF37,#B8860B);">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="pt-6 text-center text-sm" style="border-top:1px solid rgba(255,255,255,0.05);color:#334155;">
            © 2026 جوهرة · Jawhara Precious Stones · جميع الحقوق محفوظة.
        </div>
    </div>
</footer>

{{-- ===== CART SIDEBAR ===== --}}
<div id="cart-overlay" class="fixed inset-0 bg-black/70 z-50 hidden" style="backdrop-filter:blur(4px);"></div>
<div id="cart-sidebar" class="fixed top-0 left-0 h-full w-full sm:w-96 z-50 flex flex-col" style="transform:translateX(-100%);transition:transform .3s ease;">
    <div class="p-5 border-b flex justify-between items-center" style="border-color:rgba(212,175,55,0.15);">
        <h2 class="font-black text-lg flex items-center gap-2" style="color:#D4AF37;">
            <i class="fas fa-shopping-cart"></i> سلة المشتريات
        </h2>
        <button id="close-cart" class="p-2 rounded-xl transition" style="color:#64748b;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#64748b'">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div id="cart-items" class="flex-1 overflow-y-auto p-5 space-y-3">
        <div class="text-center py-16" style="color:#475569;">
            <i class="fas fa-gem text-4xl mb-3 block" style="opacity:.25;color:#D4AF37;"></i>
            السلة فارغة حالياً.
        </div>
    </div>
    <div class="p-5 border-t" style="border-color:rgba(212,175,55,0.15);background:rgba(0,0,0,0.3);">
        <div class="flex justify-between items-center mb-4">
            <span style="color:#64748b;">الإجمالي:</span>
            <span id="cart-total" class="text-2xl font-black" style="color:#D4AF37;">0 ر.س</span>
        </div>
        <button id="checkout-btn" class="w-full py-3.5 rounded-2xl font-bold text-black flex items-center justify-center gap-2"
            style="background:linear-gradient(135deg,#D4AF37,#B8860B);box-shadow:0 8px 24px rgba(212,175,55,0.3);transition:all .3s;"
            onmouseover="this.style.opacity='.88';this.style.transform='scale(1.02)'" onmouseout="this.style.opacity='1';this.style.transform='scale(1)'">
            <i class="fas fa-lock"></i> إتمام عملية الشراء بأمان
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/shop.js') }}"></script>
<script>
// ============================================================
// SPLASH SCREEN
// ============================================================
(function() {
    const splash = document.getElementById('shop-splash');
    const isSplashShown = sessionStorage.getItem('jawhara_splash_shown');
    
    if (isSplashShown) {
        if (splash) {
            splash.style.display = 'none';
        }
        document.body.style.overflow = '';
        initReveal();
        initCounters();
        // Animate hero immediately
        setTimeout(() => {
            const ht = document.getElementById('hero-text');
            const hg = document.getElementById('hero-gallery');
            if(ht){ht.style.transition='all 1s ease';ht.style.opacity='1';ht.style.transform='translateX(0)';}
            if(hg){hg.style.transition='all 1s .3s ease';hg.style.opacity='1';hg.style.transform='translateX(0)';}
        }, 100);
        return;
    }

    document.body.style.overflow = 'hidden';
    const canvas = document.getElementById('splash-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth; canvas.height = window.innerHeight;
    const pts = Array.from({length:80},()=>({x:Math.random()*canvas.width,y:Math.random()*canvas.height,r:Math.random()*2+.4,vx:(Math.random()-.5)*.4,vy:-.3-Math.random()*.5,a:.3+Math.random()*.5}));
    function drawSplash() {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        pts.forEach(p=>{p.x+=p.vx;p.y+=p.vy;if(p.y<-10){p.y=canvas.height+10;p.x=Math.random()*canvas.width;}ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`rgba(212,175,55,${p.a})`;ctx.fill();});
        requestAnimationFrame(drawSplash);
    }
    drawSplash();
    setTimeout(()=>{
        sessionStorage.setItem('jawhara_splash_shown', 'true');
        document.getElementById('shop-splash').classList.add('fade-out');
        setTimeout(()=>{
            document.getElementById('shop-splash').style.display='none';
            document.body.style.overflow='';
            initReveal(); initCounters();
            // Animate hero
            const ht=document.getElementById('hero-text'); const hg=document.getElementById('hero-gallery');
            if(ht){ht.style.transition='all 1s ease';ht.style.opacity='1';ht.style.transform='translateX(0)';}
            if(hg){hg.style.transition='all 1s .3s ease';hg.style.opacity='1';hg.style.transform='translateX(0)';}
        },850);
    },3200);
})();

// ============================================================
// HERO PARTICLES
// ============================================================
(function() {
    const canvas = document.getElementById('hero-canvas');
    if(!canvas) return;
    const ctx = canvas.getContext('2d');
    function resize(){canvas.width=canvas.offsetWidth;canvas.height=canvas.offsetHeight;}
    window.addEventListener('resize',resize); resize();
    const pts = Array.from({length:90},()=>({x:Math.random()*canvas.width,y:Math.random()*canvas.height,r:Math.random()*1.6+.3,vx:(Math.random()-.5)*.18,vy:-.06-Math.random()*.12,a:.08+Math.random()*.35}));
    function draw(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        pts.forEach(p=>{p.x+=p.vx;p.y+=p.vy;if(p.y<-10){p.y=canvas.height+10;p.x=Math.random()*canvas.width;}if(p.x<-10)p.x=canvas.width+10;if(p.x>canvas.width+10)p.x=-10;ctx.beginPath();ctx.arc(p.x,p.y,p.r,0,Math.PI*2);ctx.fillStyle=`rgba(212,175,55,${p.a})`;ctx.fill();});
        requestAnimationFrame(draw);
    }
    draw();
})();

// ============================================================
// SCROLL REVEAL
// ============================================================
function initReveal() {
    const obs = new IntersectionObserver(entries=>{entries.forEach(e=>{if(e.isIntersecting)e.target.classList.add('visible');});},{threshold:.07});
    document.querySelectorAll('.reveal').forEach(el=>obs.observe(el));
}

// ============================================================
// COUNTERS
// ============================================================
function initCounters(){
    document.querySelectorAll('.counter').forEach(el=>{
        const target=parseInt(el.dataset.target); let cur=0;
        const step=target/60;
        const t=setInterval(()=>{cur=Math.min(cur+step,target);el.textContent=Math.floor(cur).toLocaleString('ar-SA');if(cur>=target)clearInterval(t);},25);
    });
}

// ============================================================
// CATEGORY PILLS (sync with shop.js setupCategoryFilters)
// ============================================================
document.querySelectorAll('#cat-pills-bar .cat-pill').forEach(btn => {
    btn.addEventListener('click', function(){
        document.querySelectorAll('#cat-pills-bar .cat-pill').forEach(b=>b.classList.remove('active'));
        this.classList.add('active');
    });
});

// ============================================================
// MOBILE FILTER SIDEBAR
// ============================================================
const fSidebar = document.getElementById('filter-sidebar');
const fOverlay = document.getElementById('sidebar-overlay');
document.getElementById('mobile-filter-toggle')?.addEventListener('click', ()=>{ fSidebar.classList.add('open'); fOverlay.classList.add('open'); });
document.getElementById('close-sidebar-btn')?.addEventListener('click', ()=>{ fSidebar.classList.remove('open'); fOverlay.classList.remove('open'); });
fOverlay?.addEventListener('click', ()=>{ fSidebar.classList.remove('open'); fOverlay.classList.remove('open'); });

// ============================================================
// CART SYSTEM
// ============================================================
const cartSidebar = document.getElementById('cart-sidebar');
const cartOverlay = document.getElementById('cart-overlay');
const cartBadge = document.getElementById('cart-badge');
let cart = JSON.parse(localStorage.getItem('jawhara_cart') || '[]');

function saveCart(){ localStorage.setItem('jawhara_cart', JSON.stringify(cart)); updateCartUI(); }

function updateCartUI(){
    const count = cart.reduce((t,i)=>t+i.quantity,0);
    if(cartBadge) cartBadge.textContent = count;
    const ci = document.getElementById('cart-items');
    const ct = document.getElementById('cart-total');
    if(!ci) return;
    if(!cart.length){
        ci.innerHTML='<div class="text-center py-16" style="color:#475569;"><i class="fas fa-gem text-4xl mb-3 block" style="opacity:.25;color:#D4AF37;"></i>السلة فارغة حالياً.</div>';
        if(ct) ct.textContent='0 ر.س'; return;
    }
    ci.innerHTML=''; let total=0;
    cart.forEach((item,i)=>{
        total+=item.price*item.quantity;
        const el=document.createElement('div');
        el.style.cssText='background:rgba(255,255,255,0.04);border:1px solid rgba(212,175,55,0.1);border-radius:16px;padding:12px;display:flex;gap:12px;';
        el.innerHTML=`<img src="${item.image}" style="width:60px;height:60px;object-fit:cover;border-radius:10px;border:1px solid rgba(212,175,55,.2);">
        <div style="flex:1;min-width:0;">
            <h4 style="font-size:.84rem;font-weight:700;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${item.name}</h4>
            <p style="color:#D4AF37;font-size:.84rem;font-weight:900;margin:.25rem 0;">${item.price.toLocaleString()} ر.س</p>
            <div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
                <button onclick="chQ(${i},-1)" style="width:22px;height:22px;border-radius:50%;background:rgba(212,175,55,.2);border:1px solid rgba(212,175,55,.3);color:white;font-weight:bold;cursor:pointer;font-size:.75rem;">−</button>
                <span style="color:white;font-weight:700;font-size:.84rem;">${item.quantity}</span>
                <button onclick="chQ(${i},1)" style="width:22px;height:22px;border-radius:50%;background:rgba(212,175,55,.2);border:1px solid rgba(212,175,55,.3);color:white;font-weight:bold;cursor:pointer;font-size:.75rem;">+</button>
            </div>
        </div>
        <button onclick="rmCart(${i})" style="color:rgba(239,68,68,.6);padding:4px;transition:color .2s;" onmouseover="this.style.color='rgba(239,68,68,1)'" onmouseout="this.style.color='rgba(239,68,68,.6)'"><i class="fas fa-trash-alt text-sm"></i></button>`;
        ci.appendChild(el);
    });
    if(ct) ct.textContent = total.toLocaleString()+' ر.س';
}
window.chQ=(i,d)=>{cart[i].quantity+=d;if(cart[i].quantity<=0)cart.splice(i,1);saveCart();};
window.rmCart=(i)=>{cart.splice(i,1);saveCart();};

function openCart(){ cartSidebar.style.transform='translateX(0)'; cartOverlay.classList.remove('hidden'); document.body.style.overflow='hidden'; }
function closeCart(){ cartSidebar.style.transform='translateX(-100%)'; cartOverlay.classList.add('hidden'); document.body.style.overflow=''; }

const headerCartBtn = document.getElementById('cart-toggle-btn');
if(headerCartBtn) headerCartBtn.addEventListener('click', openCart);
document.getElementById('close-cart')?.addEventListener('click', closeCart);
cartOverlay?.addEventListener('click', closeCart);

document.getElementById('checkout-btn')?.addEventListener('click',()=>{
    if(!cart.length) return;
    showGemToast('🎉 تهانينا! سيتم التواصل معك لإتمام عملية الشراء.');
    setTimeout(()=>{ cart=[]; saveCart(); closeCart(); },2200);
});

// Global addToCart used by shop.js
window.addToCartUI = function(name, price, image) {
    const existing = cart.find(x=>x.name===name);
    if(existing) existing.quantity++;
    else cart.push({name, price, image, quantity:1});
    saveCart();
    showGemToast(` تمت إضافة "${name}" إلى سلتك!`);
};

// ============================================================
// TOAST
// ============================================================
function showGemToast(msg){
    const t=document.createElement('div');
    t.className='gem-toast fixed flex items-center gap-3 px-5 py-3.5 text-sm font-semibold text-white';
    t.style.cssText='bottom:24px;left:24px;max-width:340px;z-index:9999;opacity:0;transform:translateY(10px);transition:all .3s;font-family:Cairo,sans-serif;';
    t.innerHTML=`<span>${msg}</span>`;
    document.body.appendChild(t);
    setTimeout(()=>{t.style.opacity='1';t.style.transform='translateY(0)';},50);
    setTimeout(()=>{t.style.opacity='0';t.style.transform='translateY(10px)';setTimeout(()=>t.remove(),350);},3300);
}
window.showGemToast = showGemToast;

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded',()=>{ updateCartUI(); });
</script>
@endsection
