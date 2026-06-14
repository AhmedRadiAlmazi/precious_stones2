<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة التحكم | جوهرة')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dashboard-styles.css') }}">
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
    <!-- ملف الـ API والتحقق المسبق من الصلاحيات -->
    <script src="{{ asset('js/api.js') }}"></script>
    <script>
        if (!api.isAuthenticated()) {
            window.location.href = '{{ url("/login") }}';
        }
    </script>

    <!-- حاوية لوحة التحكم -->
    <div class="dashboard-container">
        <!-- غطاء الشاشة الجانبية للجوال -->
        <div class="sidebar-overlay"></div>

        <!-- المحتوى الرئيسي -->
        <main class="dashboard-main">
            <!-- سيقوم ملف layout.js بحقن الهيدر والشريط الجانبي هنا ديناميكياً -->

            <!-- محتوى اللوحة الفرعية -->
            <div class="dashboard-content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- مخرجات الجافاسكريبت المشتركة -->
    <script src="{{ asset('js/layout.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
    @yield('scripts')
</body>
</html>
