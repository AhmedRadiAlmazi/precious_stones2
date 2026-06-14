@extends('layouts.dashboard')

@section('title', 'الإعدادات | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<div class="bg-secondary border border-color rounded-xl p-6">
    <h3 class="text-xl font-bold text-primary mb-6">الملف الشخصي</h3>
    <form id="profile-form" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-primary mb-2">الاسم الأول</label>
                <input type="text" id="first-name" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-primary mb-2">الاسم الأخير</label>
                <input type="text" id="last-name" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
            </div>
        </div>
        <div>
            <label class="block text-sm font-semibold text-primary mb-2">البريد الإلكتروني</label>
            <input type="email" id="email" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-primary mb-2">رقم الهاتف</label>
            <input type="tel" id="phone" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
        </div>
        <div>
            <label class="block text-sm font-semibold text-primary mb-2">العنوان</label>
            <textarea id="address" rows="3" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary"></textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-primary mb-2">المدينة</label>
                <input type="text" id="city" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
            </div>
            <div>
                <label class="block text-sm font-semibold text-primary mb-2">الدولة</label>
                <input type="text" id="country" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
            </div>
        </div>
        <button type="submit" class="w-full bg-tertiary text-primary py-3 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
            <i class="fas fa-save ml-2"></i>حفظ التغييرات
        </button>
    </form>
</div>
@endsection

@section('scripts')
<script>
    async function loadProfile() {
        try {
            const response = await api.getSellerProfile();
            const profile = response.data || response;
            
            document.getElementById('first-name').value = profile.first_name || '';
            document.getElementById('last-name').value = profile.last_name || '';
            document.getElementById('email').value = profile.email || '';
            document.getElementById('phone').value = profile.phone || '';
            document.getElementById('address').value = profile.address || '';
            document.getElementById('city').value = profile.city || '';
            document.getElementById('country').value = profile.country || '';
        } catch (error) {
            console.error('Error loading profile:', error);
            ui.showError('فشل تحميل الملف الشخصي');
        }
    }

    document.getElementById('profile-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const data = {
            first_name: document.getElementById('first-name').value,
            last_name: document.getElementById('last-name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            address: document.getElementById('address').value,
            city: document.getElementById('city').value,
            country: document.getElementById('country').value,
        };

        try {
            await api.updateSellerProfile(data);
            ui.showSuccess('تم تحديث الملف الشخصي بنجاح!');
        } catch (error) {
            console.error('Error updating profile:', error);
            ui.showError(error.message || 'فشل تحديث الملف الشخصي');
        }
    });

    loadProfile();
</script>
@endsection
