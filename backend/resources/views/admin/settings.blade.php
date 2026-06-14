@extends('layouts.dashboard')

@section('title', 'إعدادات النظام | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        const isAdmin = user && user.roles && user.roles.some(role => role.name === 'admin');
        if (!isAdmin) {
            alert('هذه الصفحة للمديرين فقط!');
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<!-- Settings Tabs -->
<div class="flex gap-2 mb-6 overflow-x-auto pb-2">
    <button onclick="switchTab('general')" id="tab-general" class="tab-btn px-6 py-3 rounded-lg font-semibold transition border-b-2 border-gold text-gold whitespace-nowrap">
        <i class="fas fa-globe ml-2"></i>عام
    </button>
    <button onclick="switchTab('auctions')" id="tab-auctions" class="tab-btn px-6 py-3 rounded-lg font-semibold transition border-b-2 border-transparent text-secondary whitespace-nowrap">
        <i class="fas fa-gavel ml-2"></i>المزادات
    </button>
    <button onclick="switchTab('email')" id="tab-email" class="tab-btn px-6 py-3 rounded-lg font-semibold transition border-b-2 border-transparent text-secondary whitespace-nowrap">
        <i class="fas fa-envelope ml-2"></i>البريد الإلكتروني
    </button>
    <button onclick="switchTab('payment')" id="tab-payment" class="tab-btn px-6 py-3 rounded-lg font-semibold transition border-b-2 border-transparent text-secondary whitespace-nowrap">
        <i class="fas fa-credit-card ml-2"></i>الدفع
    </button>
    <button onclick="switchTab('security')" id="tab-security" class="tab-btn px-6 py-3 rounded-lg font-semibold transition border-b-2 border-transparent text-secondary whitespace-nowrap">
        <i class="fas fa-shield-alt ml-2"></i>الأمان
    </button>
</div>

<!-- General Settings -->
<div id="content-general" class="tab-content">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <h2 class="text-xl font-bold text-primary mb-6">الإعدادات العامة</h2>
        <form id="general-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">اسم الموقع</label>
                    <input type="text" id="site-name" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">البريد الإلكتروني للموقع</label>
                    <input type="email" id="site-email" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">رقم الهاتف</label>
                    <input type="tel" id="site-phone" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">العملة</label>
                    <select id="site-currency" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                        <option value="SAR">ريال سعودي (ر.س)</option>
                        <option value="USD">دولار أمريكي ($)</option>
                        <option value="EUR">يورو (€)</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-primary mb-2">وصف الموقع</label>
                <textarea id="site-description" rows="3" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary"></textarea>
            </div>
            <div class="flex items-center gap-4">
                <input type="checkbox" id="site-maintenance" class="w-4 h-4">
                <label for="site-maintenance" class="text-sm text-primary">وضع الصيانة (إيقاف الموقع مؤقتاً)</label>
            </div>
            <button type="submit" class="bg-tertiary text-primary px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                <i class="fas fa-save ml-2"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>

<!-- Auctions Settings -->
<div id="content-auctions" class="tab-content hidden">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <h2 class="text-xl font-bold text-primary mb-6">إعدادات المزادات</h2>
        <form id="auctions-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">المدة الافتراضية للمزاد (أيام)</label>
                    <input type="number" id="default-duration" min="1" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">الحد الأدنى للزيادة (%)</label>
                    <input type="number" id="min-increment" min="1" step="0.1" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">عمولة المنصة (%)</label>
                    <input type="number" id="platform-commission" min="0" max="100" step="0.1" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">الحد الأدنى لسعر البداية (ر.س)</label>
                    <input type="number" id="min-starting-price" min="0" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="auto-approve-auctions" class="w-4 h-4">
                    <label for="auto-approve-auctions" class="text-sm text-primary">الموافقة التلقائية على المزادات</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="allow-auction-extension" class="w-4 h-4">
                    <label for="allow-auction-extension" class="text-sm text-primary">السماح بتمديد المزاد تلقائياً عند المزايدة في آخر دقيقة</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="require-seller-approval" class="w-4 h-4">
                    <label for="require-seller-approval" class="text-sm text-primary">يتطلب موافقة البائع قبل النشر</label>
                </div>
            </div>
            <button type="submit" class="bg-tertiary text-primary px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                <i class="fas fa-save ml-2"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>

<!-- Email Settings -->
<div id="content-email" class="tab-content hidden">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <h2 class="text-xl font-bold text-primary mb-6">إعدادات البريد الإلكتروني</h2>
        <form id="email-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">خادم SMTP</label>
                    <input type="text" id="smtp-host" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">منفذ SMTP</label>
                    <input type="number" id="smtp-port" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">اسم المستخدم</label>
                    <input type="text" id="smtp-username" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">كلمة المرور</label>
                    <input type="password" id="smtp-password" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">التشفير</label>
                    <select id="smtp-encryption" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                        <option value="tls">TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="none">بدون تشفير</option>
                    </select>
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="send-welcome-email" class="w-4 h-4">
                    <label for="send-welcome-email" class="text-sm text-primary">إرسال بريد ترحيبي للمستخدمين الجدد</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="send-auction-notifications" class="w-4 h-4">
                    <label for="send-auction-notifications" class="text-sm text-primary">إرسال إشعارات المزادات</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="send-bid-notifications" class="w-4 h-4">
                    <label for="send-bid-notifications" class="text-sm text-primary">إرسال إشعارات المزايدات</label>
                </div>
            </div>
            <button type="submit" class="bg-tertiary text-primary px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                <i class="fas fa-save ml-2"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>

<!-- Payment Settings -->
<div id="content-payment" class="tab-content hidden">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <h2 class="text-xl font-bold text-primary mb-6">إعدادات الدفع</h2>
        <form id="payment-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">بوابة الدفع</label>
                    <select id="payment-gateway" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                        <option value="stripe">Stripe</option>
                        <option value="paypal">PayPal</option>
                        <option value="tap">Tap Payments</option>
                        <option value="moyasar">Moyasar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">وضع الاختبار</label>
                    <select id="payment-mode" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                        <option value="test">وضع الاختبار</option>
                        <option value="live">وضع الإنتاج</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">مفتاح API العام</label>
                    <input type="text" id="payment-public-key" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">مفتاح API السري</label>
                    <input type="password" id="payment-secret-key" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="accept-credit-cards" class="w-4 h-4">
                    <label for="accept-credit-cards" class="text-sm text-primary">قبول بطاقات الائتمان</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="accept-apple-pay" class="w-4 h-4">
                    <label for="accept-apple-pay" class="text-sm text-primary">قبول Apple Pay</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="accept-mada" class="w-4 h-4">
                    <label for="accept-mada" class="text-sm text-primary">قبول مدى</label>
                </div>
            </div>
            <button type="submit" class="bg-tertiary text-primary px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                <i class="fas fa-save ml-2"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>

<!-- Security Settings -->
<div id="content-security" class="tab-content hidden">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <h2 class="text-xl font-bold text-primary mb-6">إعدادات الأمان</h2>
        <form id="security-form" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">الحد الأقصى لمحاولات تسجيل الدخول</label>
                    <input type="number" id="max-login-attempts" min="1" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">مدة الحظر (دقائق)</label>
                    <input type="number" id="lockout-duration" min="1" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">الحد الأدنى لطول كلمة المرور</label>
                    <input type="number" id="min-password-length" min="6" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-primary mb-2">مدة صلاحية الجلسة (دقائق)</label>
                    <input type="number" id="session-lifetime" min="30" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                </div>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="require-email-verification" class="w-4 h-4">
                    <label for="require-email-verification" class="text-sm text-primary">يتطلب التحقق من البريد الإلكتروني</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="enable-two-factor" class="w-4 h-4">
                    <label for="enable-two-factor" class="text-sm text-primary">تفعيل المصادقة الثنائية</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="require-strong-password" class="w-4 h-4">
                    <label for="require-strong-password" class="text-sm text-primary">يتطلب كلمة مرور قوية (أحرف كبيرة، صغيرة، أرقام، رموز)</label>
                </div>
                <div class="flex items-center gap-4">
                    <input type="checkbox" id="enable-recaptcha" class="w-4 h-4">
                    <label for="enable-recaptcha" class="text-sm text-primary">تفعيل reCAPTCHA</label>
                </div>
            </div>
            <button type="submit" class="bg-tertiary text-primary px-8 py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                <i class="fas fa-save ml-2"></i>حفظ التغييرات
            </button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadSettings(group) {
        try {
            const response = await api.getSettings(group);
            const settings = response.data || response;
            
            Object.keys(settings).forEach(key => {
                const element = document.getElementById(key.replace(/_/g, '-'));
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = settings[key] == '1' || settings[key] === true;
                    } else {
                        element.value = settings[key];
                    }
                }
            });
        } catch (error) {
            console.error('Error loading settings:', error);
            ui.showError('فشل تحميل الإعدادات');
        }
    }

    function switchTab(tab) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('border-gold', 'text-gold');
            btn.classList.add('text-secondary', 'border-transparent');
        });
        document.getElementById(`tab-${tab}`).classList.add('border-gold', 'text-gold');
        document.getElementById(`tab-${tab}`).classList.remove('text-secondary', 'border-transparent');

        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        document.getElementById(`content-${tab}`).classList.remove('hidden');

        loadSettings(tab);
    }

    function collectFormData(formId, group) {
        const form = document.getElementById(formId);
        const inputs = form.querySelectorAll('input, select, textarea');
        const settings = {};

        inputs.forEach(input => {
            if (!input.id) return;
            const key = input.id.replace(/-/g, '_');
            let value, type;

            if (input.type === 'checkbox') {
                value = input.checked ? '1' : '0';
                type = 'boolean';
            } else if (input.type === 'number') {
                value = input.value;
                type = 'integer';
            } else {
                value = input.value;
                type = 'string';
            }

            settings[key] = { value, type };
        });

        return settings;
    }

    document.getElementById('general-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = collectFormData('general-form', 'general');
        try {
            await api.updateSettings(settings, 'general');
            ui.showSuccess('تم حفظ الإعدادات العامة بنجاح!');
        } catch (error) {
            console.error('Error saving settings:', error);
            ui.showError(error.message || 'فشل حفظ الإعدادات');
        }
    });

    document.getElementById('auctions-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = collectFormData('auctions-form', 'auctions');
        try {
            await api.updateSettings(settings, 'auctions');
            ui.showSuccess('تم حفظ إعدادات المزادات بنجاح!');
        } catch (error) {
            console.error('Error saving settings:', error);
            ui.showError(error.message || 'فشل حفظ الإعدادات');
        }
    });

    document.getElementById('email-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = collectFormData('email-form', 'email');
        try {
            await api.updateSettings(settings, 'email');
            ui.showSuccess('تم حفظ إعدادات البريد الإلكتروني بنجاح!');
        } catch (error) {
            console.error('Error saving settings:', error);
            ui.showError(error.message || 'فشل حفظ الإعدادات');
        }
    });

    document.getElementById('payment-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = collectFormData('payment-form', 'payment');
        try {
            await api.updateSettings(settings, 'payment');
            ui.showSuccess('تم حفظ إعدادات الدفع بنجاح!');
        } catch (error) {
            console.error('Error saving settings:', error);
            ui.showError(error.message || 'فشل حفظ الإعدادات');
        }
    });

    document.getElementById('security-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const settings = collectFormData('security-form', 'security');
        try {
            await api.updateSettings(settings, 'security');
            ui.showSuccess('تم حفظ إعدادات الأمان بنجاح!');
        } catch (error) {
            console.error('Error saving settings:', error);
            ui.showError(error.message || 'فشل حفظ الإعدادات');
        }
    });

    // Load initial settings
    loadSettings('general');
</script>
@endsection
