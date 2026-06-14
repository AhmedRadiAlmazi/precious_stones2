@extends('layouts.app')

@section('title', 'المتجر | جوهرة')

@section('content')
    <!-- قسم البطل -->
    <section class="relative bg-tertiary py-12 md:py-20">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row items-center">
                <div class="md:w-1/2 mb-8 md:mb-0">
                    <h1 class="text-4xl md:text-5xl font-bold mb-4 gold-text">اكتشف كنوزنا النادرة</h1>
                    <p class="text-xl mb-8 text-secondary">مجموعة فريدة من الأحجار الكريمة متاحة للشراء المباشر بأسعار تنافسية</p>
                    <div class="flex space-x-4 rtl:space-x-reverse">
                        <button class="gold-gradient text-white font-bold py-3 px-6 rounded-full ripple">
                            تسوق الآن
                        </button>
                        <button class="bg-transparent border-2 border-yellow-500 text-yellow-500 font-bold py-3 px-6 rounded-full hover:bg-yellow-500 hover:text-black transition">
                            عرض العروض
                        </button>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-4">
                            <div class="bg-secondary rounded-xl overflow-hidden h-32 md:h-40 border border-color">
                                <img src="{{ asset('imges/زمرد كولومبي نقي.jpg') }}" alt="زمرد" class="w-full h-full object-cover">
                            </div>
                            <div class="bg-secondary rounded-xl overflow-hidden h-24 md:h-32 border border-color">
                                <img src="{{ asset('imges/زمرد كولومبي نقي.jpg') }}" alt="ياقوت" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="space-y-4 mt-8">
                            <div class="bg-secondary rounded-xl overflow-hidden h-24 md:h-32 border border-color">
                                <img src="{{ asset('imges/ألماس وردي نادر.jpeg') }}" alt="ألماس" class="w-full h-full object-cover">
                            </div>
                            <div class="bg-secondary rounded-xl overflow-hidden h-32 md:h-40 border border-color">
                                <img src="{{ asset('imges/ياقوت أزرق نادر.jpeg') }}" alt="ياقوت وردي" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- شريط التصنيفات -->
    <div class="bg-secondary border-b border-color py-4 sticky top-16 md:top-20 z-40">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center overflow-x-auto">
                <div class="flex space-x-4 rtl:space-x-reverse pb-2">
                    <button data-category-id="" class="category-btn whitespace-nowrap gold-gradient text-white px-4 py-2 rounded-full text-sm font-bold">
                        جميع المنتجات
                    </button>
                    <button data-category-id="1" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        ألماس
                    </button>
                    <button data-category-id="2" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        ياقوت
                    </button>
                    <button data-category-id="3" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        زمرد
                    </button>
                    <button data-category-id="4" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        ياقوت أزرق
                    </button>
                    <button data-category-id="5" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        عقيق
                    </button>
                    <button data-category-id="6" class="category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color">
                        توباز
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- المحتوى الرئيسي للمتجر -->
    <main class="container mx-auto px-4 py-8">
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- الشريط الجانبي للتصفية -->
            <aside class="w-full lg:w-1/4 bg-secondary rounded-2xl p-6 border border-color h-fit">
                <h2 class="text-xl font-bold mb-6 gold-text pb-2 border-b border-color">تصفية النتائج</h2>
                
                <!-- تصنيف السعر -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">السعر (ر.س)</h3>
                    <div class="flex items-center space-x-2 rtl:space-x-reverse">
                        <input type="number" id="price-min" placeholder="من" class="w-1/2 bg-tertiary border border-color rounded-lg p-2 text-sm text-primary">
                        <span class="text-secondary">-</span>
                        <input type="number" id="price-max" placeholder="إلى" class="w-1/2 bg-tertiary border border-color rounded-lg p-2 text-sm text-primary">
                    </div>
                </div>

                <!-- تصنيف المصدر -->
                <div class="mb-6">
                    <h3 class="font-bold mb-3">بلد المنشأ</h3>
                    <div class="space-y-2 text-sm text-secondary">
                        <label class="flex items-center"><input type="checkbox" class="ml-2"> كولومبيا</label>
                        <label class="flex items-center"><input type="checkbox" class="ml-2"> سريلانكا</label>
                        <label class="flex items-center"><input type="checkbox" class="ml-2"> اليمن</label>
                        <label class="flex items-center"><input type="checkbox" class="ml-2"> مدغشقر</label>
                    </div>
                </div>

                <button id="apply-filters-btn" class="w-full gold-gradient text-white font-bold py-2.5 rounded-xl transition">تطبيق التصفية</button>
            </aside>

            <!-- شبكة المنتجات -->
            <div class="flex-1">
                <div class="flex justify-between items-center mb-6">
                    <div class="text-sm text-secondary">
                        ترتيب حسب: 
                        <select class="bg-secondary border border-color rounded-lg py-1 px-3 focus:outline-none focus:ring-1 focus:ring-yellow-500 text-sm text-primary mr-1">
                            <option>الأحدث</option>
                            <option>الأقل سعراً</option>
                            <option>الأعلى سعراً</option>
                        </select>
                    </div>
                </div>

                <div class="mb-10">
                    <div id="products-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- سيتم شحن المنتجات ديناميكياً هنا -->
                        <div class="col-span-full text-center py-20 text-secondary">
                            <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                            <p>جاري تحميل المعروضات...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection

@section('scripts')
    <script src="{{ asset('js/shop.js') }}"></script>
    <script>
        // تفعيل قائمة الجوال و ripple للمتجر
        document.addEventListener('DOMContentLoaded', () => {
            const rippleButtons = document.querySelectorAll('.ripple');
            rippleButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const audio = new Audio('https://assets.mixkit.co/sfx/preview/mixkit-select-click-1109.mp3');
                    audio.volume = 0.3;
                    audio.play().catch(() => {});
                });
            });
        });
    </script>
@endsection
