@extends('layouts.app')

@section('title', 'تقييم الأحجار الكريمة بالذكاء الاصطناعي | جوهرة')

@section('styles')
<style>
    /* ====== AI VALUATION PAGE STYLES ====== */
    :root {
        --gold: #D4AF37;
        --dark-gold: #B8860B;
        --gold-light: #F5D76E;
        --gold-glow: rgba(212,175,55,0.25);
    }

    /* Hero banner */
    .ai-hero {
        background: radial-gradient(ellipse at 60% 40%, rgba(212,175,55,0.10) 0%, transparent 65%),
                    radial-gradient(ellipse at 10% 80%, rgba(212,175,55,0.07) 0%, transparent 50%),
                    var(--bg-primary);
        border-bottom: 1px solid rgba(212,175,55,0.15);
        padding: 4rem 0 3rem;
        position: relative;
        overflow: hidden;
    }
    .ai-hero::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23D4AF37' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        pointer-events: none;
    }

    /* Section card */
    .ai-card {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        transition: border-color 0.3s;
    }
    .ai-card:hover {
        border-color: rgba(212,175,55,0.35);
    }

    /* Step label */
    .step-badge {
        display: inline-flex; align-items: center; justify-content: center;
        width: 32px; height: 32px;
        background: linear-gradient(135deg, var(--gold), var(--dark-gold));
        border-radius: 50%;
        font-size: 0.8rem; font-weight: 700; color: #000;
        flex-shrink: 0;
    }

    /* Slider */
    .carat-slider {
        -webkit-appearance: none;
        width: 100%; height: 6px;
        border-radius: 3px;
        background: linear-gradient(to right, var(--gold) 0%, var(--gold) var(--fill,0%), rgba(255,255,255,0.1) var(--fill,0%), rgba(255,255,255,0.1) 100%);
        outline: none; cursor: pointer;
    }
    .carat-slider::-webkit-slider-thumb {
        -webkit-appearance: none; appearance: none;
        width: 22px; height: 22px;
        border-radius: 50%;
        background: var(--gold);
        box-shadow: 0 0 10px var(--gold-glow);
        cursor: pointer;
        transition: box-shadow 0.2s;
    }
    .carat-slider::-webkit-slider-thumb:hover {
        box-shadow: 0 0 20px rgba(212,175,55,0.6);
    }

    /* Option cards (Cut / Clarity / Color / Origin) */
    .opt-grid { display: grid; gap: 10px; }
    .opt-grid-2 { grid-template-columns: repeat(2, 1fr); }
    .opt-grid-3 { grid-template-columns: repeat(3, 1fr); }
    .opt-grid-4 { grid-template-columns: repeat(4, 1fr); }

    .opt-card {
        background: var(--bg-tertiary);
        border: 2px solid var(--border-color);
        border-radius: 14px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        user-select: none;
    }
    .opt-card:hover {
        border-color: rgba(212,175,55,0.5);
        background: rgba(212,175,55,0.06);
    }
    .opt-card.selected {
        border-color: var(--gold);
        background: rgba(212,175,55,0.12);
        box-shadow: 0 0 12px var(--gold-glow);
    }
    .opt-card .opt-icon { font-size: 1.6rem; margin-bottom: 6px; }
    .opt-card .opt-label { font-size: 0.78rem; font-weight: 700; color: var(--text-primary); }
    .opt-card .opt-sub { font-size: 0.66rem; color: var(--text-secondary); margin-top: 2px; }
    .opt-card.selected .opt-label { color: var(--gold); }

    /* Scanning animation */
    @keyframes scanLine {
        0%   { top: 0; opacity: 0.8; }
        50%  { opacity: 1; }
        100% { top: 100%; opacity: 0.8; }
    }
    @keyframes gemRotate {
        from { transform: rotateY(0deg); }
        to   { transform: rotateY(360deg); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes countUp { from { opacity: 0; } to { opacity: 1; } }

    .scan-wrapper {
        position: relative;
        width: 160px; height: 160px;
        margin: 0 auto;
    }
    .scan-gem {
        display: block;
        margin: 0 auto;
        animation: gemRotate 2s linear infinite;
        filter: drop-shadow(0 0 18px rgba(212,175,55,0.8));
    }
    .scan-line {
        position: absolute; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, var(--gold), transparent);
        animation: scanLine 1.2s linear infinite;
        box-shadow: 0 0 10px var(--gold);
    }

    /* Gauge (circular quality score) */
    .gauge-svg { transform: rotate(-90deg); }
    .gauge-track { fill: none; stroke: rgba(255,255,255,0.07); stroke-width: 12; }
    .gauge-fill  {
        fill: none;
        stroke-width: 12;
        stroke-linecap: round;
        stroke: url(#gaugeGradient);
        transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Result section */
    .result-card {
        background: linear-gradient(135deg, rgba(212,175,55,0.07) 0%, rgba(212,175,55,0.02) 100%);
        border: 1.5px solid rgba(212,175,55,0.4);
        border-radius: 20px;
        animation: fadeInUp 0.7s ease forwards;
    }
    .factor-bar {
        height: 6px; border-radius: 3px;
        background: rgba(255,255,255,0.07);
        overflow: hidden;
    }
    .factor-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--dark-gold), var(--gold));
        border-radius: 3px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Gemstone select (dropdown) */
    .gem-select {
        background: var(--bg-tertiary);
        border: 2px solid var(--border-color);
        border-radius: 12px;
        color: var(--text-primary);
        padding: 12px 16px;
        width: 100%;
        font-family: 'Cairo', sans-serif;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.3s;
        cursor: pointer;
    }
    .gem-select:focus { border-color: var(--gold); }

    /* CTA / Analyze button */
    .btn-analyze {
        background: linear-gradient(135deg, var(--gold), var(--dark-gold));
        color: #000;
        font-weight: 800;
        font-size: 1.05rem;
        padding: 15px 40px;
        border-radius: 50px;
        border: none;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 20px rgba(212,175,55,0.3);
        position: relative; overflow: hidden;
    }
    .btn-analyze::after {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s;
    }
    .btn-analyze:hover::after { transform: translateX(100%); }
    .btn-analyze:hover { box-shadow: 0 8px 30px rgba(212,175,55,0.5); transform: translateY(-2px); }
    .btn-analyze:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* GIA-style report header */
    .report-header {
        background: linear-gradient(135deg, #0d0d0d 0%, #1a1206 50%, #0d0d0d 100%);
        border-radius: 16px 16px 0 0;
        padding: 24px;
        border-bottom: 2px solid var(--gold);
    }

    /* Stars */
    .star-rating span {
        font-size: 1.3rem;
        color: var(--gold);
    }

    /* Responsive */
    @media (max-width: 640px) {
        .opt-grid-4 { grid-template-columns: repeat(2, 1fr); }
        .opt-grid-3 { grid-template-columns: repeat(2, 1fr); }
    }
</style>
@endsection

@section('content')

<!-- HERO -->
<section class="ai-hero">
    <div class="container mx-auto px-4 text-center">
        <div class="inline-flex items-center gap-2 bg-tertiary border border-color rounded-full px-4 py-2 text-xs text-gold mb-6">
            <i class="fas fa-robot"></i>
            <span>مدعوم بذكاء اصطناعي متقدم • AI-Powered Gemstone Evaluation</span>
        </div>
        <h1 class="text-4xl md:text-5xl font-black mb-4" style="background: linear-gradient(135deg, #D4AF37, #F5D76E, #B8860B); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            تقييم الأحجار الكريمة
        </h1>
        <p class="text-secondary text-lg md:text-xl max-w-2xl mx-auto mb-2">
            خوارزمية متقدمة تحاكي معايير الفحص الجيولوجي العالمية
        </p>
        <p class="text-secondary text-sm">
            استناداً إلى معايير <strong class="text-gold">4 Cs</strong>: القطع • النقاء • اللون • القيراط، مع عامل المنشأ الجغرافي
        </p>

        <!-- Statistics bar -->
        <div class="flex flex-wrap justify-center gap-8 mt-10">
            <div class="text-center">
                <p class="text-2xl font-black text-gold">+12,000</p>
                <p class="text-xs text-secondary">تقييم أُجري</p>
            </div>
            <div class="w-px bg-color hidden sm:block"></div>
            <div class="text-center">
                <p class="text-2xl font-black text-gold">7</p>
                <p class="text-xs text-secondary">أنواع أحجار</p>
            </div>
            <div class="w-px bg-color hidden sm:block"></div>
            <div class="text-center">
                <p class="text-2xl font-black text-gold">97%</p>
                <p class="text-xs text-secondary">دقة التقدير</p>
            </div>
            <div class="w-px bg-color hidden sm:block"></div>
            <div class="text-center">
                <p class="text-2xl font-black text-gold">مجاني</p>
                <p class="text-xs text-secondary">للجميع</p>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT -->
<section class="py-12">
    <div class="container mx-auto px-4 max-w-5xl">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

            <!-- ===== FORM PANEL ===== -->
            <div class="ai-card p-6 md:p-8">
                <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-gold"></i>
                    بيانات الحجر الكريم
                </h2>

                <!-- Step 1: Stone type -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">١</span>
                        <span class="font-bold text-sm">نوع الحجر الكريم</span>
                    </div>
                    <select id="stone-category" class="gem-select">
                        <option value="ألماس">[ ◆ ] ألماس (Diamond)</option>
                        <option value="ياقوت أحمر">[ ● ] ياقوت أحمر (Ruby)</option>
                        <option value="ياقوت أزرق">[ ◉ ] ياقوت أزرق (Sapphire)</option>
                        <option value="زمرد">[ ◈ ] زمرد (Emerald)</option>
                        <option value="عقيق">[ ◍ ] عقيق (Agate)</option>
                        <option value="توباز">[ ◎ ] توباز (Topaz)</option>
                        <option value="أوبال">[ ❋ ] أوبال (Opal)</option>
                        <option value="أخرى">[ ○ ] أخرى</option>
                    </select>
                </div>

                <!-- Step 2: Weight (slider) -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">٢</span>
                        <span class="font-bold text-sm">الوزن بالقيراط</span>
                        <span class="mr-auto text-gold font-black text-lg" id="carat-display">1.50 قيراط</span>
                    </div>
                    <input type="range" id="carat-slider" class="carat-slider" min="0.10" max="50" step="0.05" value="1.50">
                    <div class="flex justify-between text-xs text-secondary mt-1">
                        <span>0.10 قيراط</span>
                        <span>50 قيراط</span>
                    </div>
                </div>

                <!-- Step 3: Cut -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">٣</span>
                        <span class="font-bold text-sm">جودة القطع <span class="text-xs text-secondary font-normal">(Cut)</span></span>
                    </div>
                    <div class="opt-grid opt-grid-4" id="cut-group">
                        <div class="opt-card selected" data-value="excellent">
                            <div class="opt-icon"><i class="fas fa-crown" style="color:#D4AF37;font-size:1.5rem;"></i></div>
                            <div class="opt-label">ممتاز</div>
                            <div class="opt-sub">Excellent</div>
                        </div>
                        <div class="opt-card" data-value="very_good">
                            <div class="opt-icon"><i class="fas fa-star" style="color:#D4AF37;font-size:1.5rem;"></i></div>
                            <div class="opt-label">جيد جداً</div>
                            <div class="opt-sub">Very Good</div>
                        </div>
                        <div class="opt-card" data-value="good">
                            <div class="opt-icon"><i class="fas fa-gem" style="color:#60a5fa;font-size:1.5rem;"></i></div>
                            <div class="opt-label">جيد</div>
                            <div class="opt-sub">Good</div>
                        </div>
                        <div class="opt-card" data-value="fair">
                            <div class="opt-icon"><i class="far fa-gem" style="color:#94a3b8;font-size:1.5rem;"></i></div>
                            <div class="opt-label">مقبول</div>
                            <div class="opt-sub">Fair</div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Clarity -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">٤</span>
                        <span class="font-bold text-sm">درجة النقاء <span class="text-xs text-secondary font-normal">(Clarity)</span></span>
                    </div>
                    <div class="opt-grid opt-grid-3" id="clarity-group">
                        <div class="opt-card selected" data-value="fl_if">
                            <div class="opt-icon"><i class="fas fa-trophy" style="color:#D4AF37;font-size:1.5rem;"></i></div>
                            <div class="opt-label">FL/IF</div>
                            <div class="opt-sub">خالٍ من العيوب</div>
                        </div>
                        <div class="opt-card" data-value="vvs">
                            <div class="opt-icon"><i class="fas fa-microscope" style="color:#818cf8;font-size:1.5rem;"></i></div>
                            <div class="opt-label">VVS1-VVS2</div>
                            <div class="opt-sub">نقي جداً</div>
                        </div>
                        <div class="opt-card" data-value="vs">
                            <div class="opt-icon"><i class="fas fa-search" style="color:#38bdf8;font-size:1.5rem;"></i></div>
                            <div class="opt-label">VS1-VS2</div>
                            <div class="opt-sub">نقي قليلاً</div>
                        </div>
                        <div class="opt-card" data-value="si">
                            <div class="opt-icon"><i class="fas fa-eye" style="color:#fbbf24;font-size:1.5rem;"></i></div>
                            <div class="opt-label">SI1-SI2</div>
                            <div class="opt-sub">تضمينات طفيفة</div>
                        </div>
                        <div class="opt-card" data-value="i">
                            <div class="opt-icon"><i class="fas fa-low-vision" style="color:#94a3b8;font-size:1.5rem;"></i></div>
                            <div class="opt-label">I1-I3</div>
                            <div class="opt-sub">مرئية للعين</div>
                        </div>
                    </div>
                </div>

                <!-- Step 5: Color -->
                <div class="mb-6">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">٥</span>
                        <span class="font-bold text-sm">درجة اللون <span class="text-xs text-secondary font-normal">(Color)</span></span>
                    </div>
                    <div class="opt-grid opt-grid-2" id="color-group">
                        <div class="opt-card selected" data-value="d_colorless">
                            <div class="opt-icon">
                                <svg width="28" height="28" viewBox="0 0 28 28"><polygon points="14,2 26,10 21,26 7,26 2,10" fill="none" stroke="#D4AF37" stroke-width="2"/><polygon points="14,2 26,10 21,26 7,26 2,10" fill="rgba(212,175,55,0.12)"/></svg>
                            </div>
                            <div class="opt-label">D–F عديم اللون</div>
                            <div class="opt-sub">Colorless</div>
                        </div>
                        <div class="opt-card" data-value="g_near_colorless">
                            <div class="opt-icon">
                                <svg width="28" height="28" viewBox="0 0 28 28"><polygon points="14,2 26,10 21,26 7,26 2,10" fill="rgba(200,200,210,0.25)" stroke="#94a3b8" stroke-width="2"/></svg>
                            </div>
                            <div class="opt-label">G–J شبه عديم</div>
                            <div class="opt-sub">Near Colorless</div>
                        </div>
                        <div class="opt-card" data-value="k_faint">
                            <div class="opt-icon">
                                <svg width="28" height="28" viewBox="0 0 28 28"><polygon points="14,2 26,10 21,26 7,26 2,10" fill="rgba(251,191,36,0.25)" stroke="#fbbf24" stroke-width="2"/></svg>
                            </div>
                            <div class="opt-label">K–Z خفيف</div>
                            <div class="opt-sub">Faint–Light</div>
                        </div>
                        <div class="opt-card" data-value="fancy_vivid">
                            <div class="opt-icon">
                                <svg width="28" height="28" viewBox="0 0 28 28"><defs><linearGradient id="fvg" x1="0" y1="0" x2="1" y2="1"><stop offset="0%" stop-color="#f472b6"/><stop offset="50%" stop-color="#818cf8"/><stop offset="100%" stop-color="#34d399"/></linearGradient></defs><polygon points="14,2 26,10 21,26 7,26 2,10" fill="url(#fvg)" stroke="none"/></svg>
                            </div>
                            <div class="opt-label">Fancy Vivid</div>
                            <div class="opt-sub">ملوّن نادر</div>
                        </div>
                    </div>
                </div>

                <!-- Step 6: Origin -->
                <div class="mb-8">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="step-badge">٦</span>
                        <span class="font-bold text-sm">بلد المنشأ / الأصل</span>
                    </div>
                    <select id="stone-origin" class="gem-select">
                        <option value="ميانمار (بورما)">★★★ ميانمار / بورما (أعلى قيمة)</option>
                        <option value="كشمير">★★★ كشمير (قيمة استثنائية)</option>
                        <option value="كولومبيا">★★  كولومبيا</option>
                        <option value="سريلانكا">★★  سريلانكا</option>
                        <option value="جنوب أفريقيا">★    جنوب أفريقيا</option>
                        <option value="البرازيل">★    البرازيل</option>
                        <option value="اليمن">★    اليمن</option>
                        <option value="روسيا">★    روسيا</option>
                        <option value="أخرى" selected>○    أخرى / غير محدد</option>
                    </select>
                </div>

                <!-- Analyze Button -->
                <button id="analyze-btn" class="btn-analyze w-full" onclick="runAnalysis()">
                    <i class="fas fa-robot ml-2"></i>
                    تشغيل تحليل الذكاء الاصطناعي
                </button>

                <p class="text-center text-xs text-secondary mt-3">
                    <i class="fas fa-shield-alt text-gold ml-1"></i>
                    التقييم تقديري للإرشاد فقط وليس قيمة تسعيرية نهائية
                </p>
            </div>

            <!-- ===== RESULT PANEL ===== -->
            <div class="flex flex-col gap-6">

                <!-- Initial state -->
                <div id="result-initial" class="ai-card p-8 text-center flex flex-col items-center justify-center" style="min-height: 400px;">
                    <div class="mb-4" style="filter: drop-shadow(0 0 20px rgba(212,175,55,0.5));">
                        <svg width="90" height="90" viewBox="0 0 90 90" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="gemGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#F5D76E"/>
                                    <stop offset="100%" stop-color="#B8860B"/>
                                </linearGradient>
                            </defs>
                            <polygon points="45,5 85,30 75,80 15,80 5,30" fill="url(#gemGrad)" opacity="0.9"/>
                            <polygon points="45,5 85,30 45,55" fill="rgba(255,255,255,0.25)"/>
                            <polygon points="45,5 5,30 45,55" fill="rgba(255,255,255,0.1)"/>
                            <polygon points="15,80 75,80 45,55" fill="rgba(0,0,0,0.15)"/>
                            <polygon points="45,5 85,30 75,80 15,80 5,30" fill="none" stroke="rgba(212,175,55,0.6)" stroke-width="1.5"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">جاهز للتحليل</h3>
                    <p class="text-secondary text-sm max-w-xs">
                        أدخل خصائص الحجر الكريم في النموذج المجاور، ثم اضغط على زر التحليل لبدء محاكاة الفحص الجيولوجي.
                    </p>
                    <div class="mt-8 flex flex-col gap-2 text-xs text-secondary">
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500"></i> يعتمد على معايير GIA & IGI العالمية</div>
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500"></i> مجاني ولا يتطلب تسجيل دخول</div>
                        <div class="flex items-center gap-2"><i class="fas fa-check-circle text-green-500"></i> نتيجة فورية في ثوانٍ</div>
                    </div>
                </div>

                <!-- Scanning state -->
                <div id="result-scanning" class="ai-card p-8 text-center hidden" style="min-height: 400px;">
                    <div class="scan-wrapper mb-6">
                        <svg class="scan-gem" width="100" height="100" viewBox="0 0 90 90" xmlns="http://www.w3.org/2000/svg" style="display:block;">
                            <defs>
                                <linearGradient id="scanGemGrad" x1="0" y1="0" x2="1" y2="1">
                                    <stop offset="0%" stop-color="#F5D76E"/>
                                    <stop offset="100%" stop-color="#B8860B"/>
                                </linearGradient>
                            </defs>
                            <polygon points="45,5 85,30 75,80 15,80 5,30" fill="url(#scanGemGrad)" opacity="0.9"/>
                            <polygon points="45,5 85,30 45,55" fill="rgba(255,255,255,0.25)"/>
                            <polygon points="45,5 5,30 45,55" fill="rgba(255,255,255,0.1)"/>
                            <polygon points="15,80 75,80 45,55" fill="rgba(0,0,0,0.15)"/>
                            <polygon points="45,5 85,30 75,80 15,80 5,30" fill="none" stroke="rgba(212,175,55,0.6)" stroke-width="1.5"/>
                        </svg>
                        <div class="scan-line"></div>
                    </div>
                    <p class="text-gold font-bold text-lg mb-2">جاري فحص خصائص الحجر...</p>
                    <div class="flex justify-center gap-3 mt-4 text-xs text-secondary" id="scan-steps">
                        <span id="scan-s1" class="opacity-30"><i class="fas fa-circle" style="font-size:0.5rem;"></i> تحليل القطع</span>
                        <span id="scan-s2" class="opacity-30"><i class="fas fa-circle" style="font-size:0.5rem;"></i> فحص النقاء</span>
                        <span id="scan-s3" class="opacity-30"><i class="fas fa-circle" style="font-size:0.5rem;"></i> تقييم اللون</span>
                        <span id="scan-s4" class="opacity-30"><i class="fas fa-circle" style="font-size:0.5rem;"></i> حساب القيمة</span>
                    </div>
                </div>

                <!-- Result state -->
                <div id="result-output" class="hidden">

                    <!-- Report header (GIA-style) -->
                    <div class="result-card overflow-hidden">
                        <div class="report-header flex items-center justify-between">
                            <div>
                                <p class="text-xs text-yellow-400 font-mono tracking-widest mb-1">JAWHARA AI GEMOLOGICAL REPORT</p>
                                <h3 class="text-white text-xl font-black" id="report-stone-name">ألماس</h3>
                                <p class="text-yellow-400 text-sm mt-1" id="report-stone-carats">1.50 قيراط</p>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-400 mb-1">جودة التقييم</div>
                                <div class="text-2xl font-black text-yellow-400" id="report-quality-num">--</div>
                                <div class="text-xs text-gray-400">/ 100 نقطة</div>
                            </div>
                        </div>

                        <div class="p-6">
                            <!-- Gauge -->
                            <div class="flex justify-center mb-6">
                                <div style="position:relative; width:160px; height:160px;">
                                    <svg viewBox="0 0 120 120" width="160" height="160" class="gauge-svg">
                                        <defs>
                                            <linearGradient id="gaugeGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                                <stop offset="0%" style="stop-color:#B8860B"/>
                                                <stop offset="100%" style="stop-color:#F5D76E"/>
                                            </linearGradient>
                                        </defs>
                                        <circle cx="60" cy="60" r="50" class="gauge-track"/>
                                        <circle cx="60" cy="60" r="50" class="gauge-fill" id="gauge-fill"
                                            stroke-dasharray="314.16"
                                            stroke-dashoffset="314.16"/>
                                    </svg>
                                    <div style="position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center;">
                                        <span class="text-3xl font-black text-gold" id="gauge-score">0</span>
                                        <span class="text-xs text-secondary">مؤشر الجودة</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Price range -->
                            <div class="bg-black bg-opacity-20 rounded-2xl p-5 mb-5 text-center">
                                <p class="text-secondary text-xs mb-2">النطاق السعري التقديري (ريال سعودي)</p>
                                <div class="flex items-center justify-center gap-4">
                                    <div>
                                        <p class="text-xs text-secondary mb-1">الحد الأدنى</p>
                                        <p class="text-lg font-black text-primary" id="price-low">--</p>
                                    </div>
                                    <div class="text-gold text-2xl font-black">—</div>
                                    <div>
                                        <p class="text-xs text-secondary mb-1">الحد الأقصى</p>
                                        <p class="text-lg font-black text-primary" id="price-high">--</p>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-t border-color">
                                    <p class="text-xs text-secondary mb-1">التقدير الوسطي</p>
                                    <p class="text-3xl font-black text-gold" id="price-mid">--</p>
                                    <p class="text-xs text-secondary mt-1">ريال سعودي</p>
                                </div>
                            </div>

                            <!-- Factors breakdown -->
                            <div>
                                <p class="font-bold text-sm mb-4 flex items-center gap-2">
                                    <i class="fas fa-chart-bar text-gold text-xs"></i>
                                    تحليل العوامل المؤثرة في السعر
                                </p>
                                <div class="space-y-3" id="factors-list">
                                    <!-- injected by JS -->
                                </div>
                            </div>

                            <!-- Note -->
                            <div class="mt-5 bg-yellow-500 bg-opacity-10 border border-yellow-500 border-opacity-30 rounded-xl p-3 text-xs text-secondary flex gap-2">
                                <i class="fas fa-info-circle text-yellow-500 mt-0.5 flex-shrink-0"></i>
                                <span id="report-note">التقييم تقديري للإرشاد فقط.</span>
                            </div>

                            <!-- Actions -->
                            <div class="mt-5 flex gap-3">
                                <button onclick="resetForm()" class="flex-1 bg-tertiary border border-color rounded-xl py-3 text-sm font-bold hover:border-gold transition">
                                    <i class="fas fa-redo ml-1"></i>
                                    تقييم جديد
                                </button>
                                <a href="{{ url('/auctions') }}" class="flex-1 text-center gold-gradient text-black rounded-xl py-3 text-sm font-bold hover:shadow-lg transition">
                                    <i class="fas fa-gavel ml-1"></i>
                                    تصفح المزادات
                                </a>
                            </div>
                        </div>
                    </div>

                </div><!-- /result-output -->

                <!-- How it works card (always visible below) -->
                <div class="ai-card p-6">
                    <h3 class="font-bold mb-4 flex items-center gap-2 text-sm">
                        <i class="fas fa-question-circle text-gold"></i>
                        كيف يعمل نظام التقييم؟
                    </h3>
                    <div class="space-y-3 text-xs text-secondary">
                        <div class="flex gap-3">
                            <span class="text-gold font-bold flex-shrink-0">القطع</span>
                            <span>يؤثر على كيفية انكسار وتشتت الضوء داخل الحجر، وهو من أهم العوامل.</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gold font-bold flex-shrink-0">النقاء</span>
                            <span>يقيس مدى خلو الحجر من الشوائب والتضمينات الداخلية بالمجهر.</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gold font-bold flex-shrink-0">اللون</span>
                            <span>الألماس الأعلى قيمة هو الأكثر شفافية وعديم اللون (D)، بينما الأحجار الملونة تُقيَّم بشدة لونها.</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gold font-bold flex-shrink-0">القيراط</span>
                            <span>وزن الحجر، كلما زاد الوزن ارتفعت القيمة بشكل غير خطي.</span>
                        </div>
                        <div class="flex gap-3">
                            <span class="text-gold font-bold flex-shrink-0">المنشأ</span>
                            <span>للأحجار من كشمير وبورما وكولومبيا قسط علاوة (premium) على القيمة.</span>
                        </div>
                    </div>
                </div>

            </div><!-- /result panel -->

        </div><!-- /grid -->
    </div><!-- /container -->
</section>

<!-- CTA Section -->
<section class="py-16 text-center" style="background: radial-gradient(ellipse at center, rgba(212,175,55,0.06) 0%, transparent 70%);">
    <div class="container mx-auto px-4">
        <h2 class="text-3xl font-black mb-4" style="background: linear-gradient(135deg, #D4AF37, #F5D76E); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            هل وجدت حجرك المثالي؟
        </h2>
        <p class="text-secondary mb-8 max-w-md mx-auto">
            تصفح آلاف الأحجار الكريمة المعتمدة وشارك في المزادات الحية الآن.
        </p>
        <div class="flex justify-center gap-4 flex-wrap">
            <a href="{{ url('/auctions') }}" class="btn-analyze inline-block">
                <i class="fas fa-gavel ml-2"></i>المزادات الحية
            </a>
            <a href="{{ url('/shop') }}" class="inline-block bg-tertiary border border-color px-8 py-4 rounded-full font-bold hover:border-gold transition text-sm">
                <i class="fas fa-store ml-2"></i>تصفح المتجر
            </a>
        </div>
    </div>
</section>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ===== CARAT SLIDER =====
    const slider  = document.getElementById('carat-slider');
    const display = document.getElementById('carat-display');

    function updateSlider() {
        const val  = parseFloat(slider.value);
        const pct  = ((val - slider.min) / (slider.max - slider.min)) * 100;
        slider.style.setProperty('--fill', pct + '%');
        display.textContent = val.toFixed(2) + ' قيراط';
    }
    slider.addEventListener('input', updateSlider);
    updateSlider();

    // ===== OPTION CARD SELECTION =====
    document.querySelectorAll('.opt-grid').forEach(group => {
        group.querySelectorAll('.opt-card').forEach(card => {
            card.addEventListener('click', () => {
                group.querySelectorAll('.opt-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
            });
        });
    });

});

// ===== HELPERS =====
function getSelected(groupId) {
    const sel = document.querySelector(`#${groupId} .opt-card.selected`);
    return sel ? sel.dataset.value : null;
}

function resetForm() {
    document.getElementById('result-output').classList.add('hidden');
    document.getElementById('result-initial').classList.remove('hidden');
    document.getElementById('analyze-btn').disabled = false;
}

// ===== MAIN ANALYSIS =====
async function runAnalysis() {
    const btn = document.getElementById('analyze-btn');
    btn.disabled = true;

    const inputs = {
        category : document.getElementById('stone-category').value,
        carats   : parseFloat(document.getElementById('carat-slider').value),
        cut      : getSelected('cut-group')     || 'good',
        clarity  : getSelected('clarity-group') || 'vvs',
        color    : getSelected('color-group')   || 'd_colorless',
        origin   : document.getElementById('stone-origin').value,
    };

    // Show scanner
    document.getElementById('result-initial').classList.add('hidden');
    document.getElementById('result-output').classList.add('hidden');
    document.getElementById('result-scanning').classList.remove('hidden');

    // Animate scan steps
    const steps = ['scan-s1','scan-s2','scan-s3','scan-s4'];
    let stepIdx = 0;
    const stepTimer = setInterval(() => {
        if (stepIdx < steps.length) {
            document.getElementById(steps[stepIdx]).classList.remove('opacity-30');
            document.getElementById(steps[stepIdx]).classList.add('text-gold');
            stepIdx++;
        } else {
            clearInterval(stepTimer);
        }
    }, 500);

    try {
        const response = await api.simulateValuation(inputs);

        // Give time for scanner animation to complete
        await new Promise(r => setTimeout(r, 2200));
        clearInterval(stepTimer);

        if (!response || !response.success) {
            throw new Error(response?.message || 'فشل التحليل');
        }

        renderResult(inputs, response.data);

    } catch (err) {
        console.error(err);
        clearInterval(stepTimer);
        document.getElementById('result-scanning').classList.add('hidden');
        document.getElementById('result-initial').classList.remove('hidden');
        alert('عذراً، حدث خطأ أثناء التحليل: ' + err.message);
        btn.disabled = false;
    }
}

function renderResult(inputs, data) {
    document.getElementById('result-scanning').classList.add('hidden');
    document.getElementById('result-output').classList.remove('hidden');

    // Stone name & carats
    document.getElementById('report-stone-name').textContent = inputs.category;
    document.getElementById('report-stone-carats').textContent = inputs.carats.toFixed(2) + ' قيراط • ' + inputs.origin;

    // Quality score gauge
    const score = data.quality_score;
    document.getElementById('report-quality-num').textContent = score;
    animateGauge(score);
    animateCounter('gauge-score', 0, score, 1400);

    // Prices
    document.getElementById('price-low').textContent  = Number(data.low_estimate_sar).toLocaleString('ar-SA') + ' ر.س';
    document.getElementById('price-high').textContent = Number(data.high_estimate_sar).toLocaleString('ar-SA') + ' ر.س';
    animateCounter('price-mid', 0, data.mid_estimate_sar, 1600, true);

    // Factors
    renderFactors(data.factors);

    // Note
    document.getElementById('report-note').textContent = data.note;

    document.getElementById('analyze-btn').disabled = false;
}

function animateGauge(score) {
    const circ = 314.16;
    const offset = circ - (circ * score / 100);
    setTimeout(() => {
        document.getElementById('gauge-fill').style.strokeDashoffset = offset;
    }, 100);
}

function animateCounter(id, from, to, duration, isCurrency = false) {
    const el   = document.getElementById(id);
    const start = performance.now();
    function step(now) {
        const pct = Math.min(1, (now - start) / duration);
        const val = Math.round(from + (to - from) * pct);
        el.textContent = isCurrency ? Number(val).toLocaleString('ar-SA') + ' ر.س' : val;
        if (pct < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

function renderFactors(factors) {
    const labels = {
        base_price_per_carat: { label: 'السعر الأساسي لكل قيراط', icon: '<i class="fas fa-gem" style="color:#D4AF37"></i>', isBase: true },
        cut_multiplier:       { label: 'معامل القطع',              icon: '<i class="fas fa-cut" style="color:#D4AF37"></i>' },
        clarity_multiplier:   { label: 'معامل النقاء',             icon: '<i class="fas fa-microscope" style="color:#818cf8"></i>' },
        color_multiplier:     { label: 'معامل اللون',              icon: '<i class="fas fa-palette" style="color:#f472b6"></i>' },
        origin_multiplier:    { label: 'معامل المنشأ',             icon: '<i class="fas fa-map-marker-alt" style="color:#34d399"></i>' },
    };

    const container = document.getElementById('factors-list');
    container.innerHTML = '';

    Object.entries(factors).forEach(([key, val]) => {
        const info = labels[key];
        if (!info) return;
        if (info.isBase) {
            container.innerHTML += `
                <div class="flex items-center justify-between text-xs">
                    <span class="text-secondary">${info.icon} ${info.label}</span>
                    <span class="font-bold text-primary">$${Number(val).toLocaleString()} / قيراط</span>
                </div>`;
            return;
        }
        const pct = Math.min(100, Math.round((val / 2.0) * 100));
        const color = val >= 1.5 ? '#22c55e' : val >= 1.2 ? '#D4AF37' : val >= 1.0 ? '#94a3b8' : '#ef4444';
        container.innerHTML += `
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-secondary">${info.icon} ${info.label}</span>
                    <span class="font-bold" style="color:${color}">×${val.toFixed(2)}</span>
                </div>
                <div class="factor-bar">
                    <div class="factor-bar-fill" style="width:0%;background:linear-gradient(90deg,${color}88,${color});" data-pct="${pct}"></div>
                </div>
            </div>`;
    });

    // Animate bars after insertion
    setTimeout(() => {
        container.querySelectorAll('.factor-bar-fill').forEach(bar => {
            bar.style.width = bar.dataset.pct + '%';
        });
    }, 100);
}
</script>
@endsection
