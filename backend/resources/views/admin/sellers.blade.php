@extends('layouts.dashboard')

@section('title', 'البائعون المعلقون | جوهرة')

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

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold gold-text">البائعون في انتظار الموافقة</h1>
</div>

<div class="bg-secondary border border-color rounded-xl overflow-hidden p-6" id="pending-sellers-list">
    <div class="text-center py-12 text-secondary">
        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
        <p>جاري تحميل البائعين المعلقين...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadPendingSellers() {
        try {
            const response = await api.getPendingSellers();
            const sellers = response.data || [];
            displayPendingSellers(sellers);
        } catch (error) {
            console.error('Error loading pending sellers:', error);
            document.getElementById('pending-sellers-list').innerHTML = `
                <div class="text-center py-12 text-red-500">
                    <i class="fas fa-exclamation-triangle text-6xl mb-4"></i>
                    <p class="text-xl">فشل تحميل البيانات</p>
                    <button onclick="loadPendingSellers()" class="mt-4 gold-gradient text-white px-6 py-3 rounded-lg">
                        إعادة المحاولة
                    </button>
                </div>
            `;
        }
    }

    function displayPendingSellers(sellers) {
        const container = document.getElementById('pending-sellers-list');
        
        if (sellers.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary">
                    <i class="fas fa-check-circle text-6xl mb-4 text-green-500"></i>
                    <p class="text-xl">لا توجد طلبات معلقة حالياً</p>
                    <p class="text-sm mt-2">جميع طلبات البائعين قد تمت مراجعتها.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = sellers.map(seller => `
            <div class="border border-color bg-tertiary rounded-lg p-6 hover:shadow-lg transition mb-4">
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-16 h-16 gold-gradient rounded-full flex items-center justify-center text-white text-2xl font-bold">
                                ${seller.first_name.charAt(0)}${seller.last_name.charAt(0)}
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-primary">
                                    ${seller.first_name} ${seller.last_name}
                                </h3>
                                <span class="inline-block px-3 py-1 bg-yellow-500 bg-opacity-20 text-yellow-500 text-xs rounded-full font-semibold">
                                    <i class="fas fa-clock ml-1"></i>
                                    في انتظار المراجعة
                                </span>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-4 text-sm text-secondary">
                            <div><strong>البريد الإلكتروني:</strong> ${seller.email}</div>
                            <div><strong>رقم الهاتف:</strong> ${seller.phone}</div>
                            <div><strong>تاريخ التسجيل:</strong> ${new Date(seller.created_at).toLocaleDateString('ar-SA')}</div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2 w-full lg:w-auto justify-end">
                        <button onclick="approveSeller(${seller.id})" 
                            class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600 transition shadow-md">
                            <i class="fas fa-check ml-1"></i>موافقة
                        </button>
                        <button onclick="rejectSeller(${seller.id})" 
                            class="bg-red-500 text-white px-6 py-2 rounded-lg hover:bg-red-600 transition shadow-md">
                            <i class="fas fa-times ml-1"></i>رفض
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function approveSeller(userId) {
        if (!await ui.confirm('هل أنت متأكد من الموافقة على هذا البائع؟', 'موافقة بائع')) return;
        try {
            await api.approveSeller(userId);
            ui.showSuccess('تمت الموافقة على البائع بنجاح!');
            loadPendingSellers();
        } catch (error) {
            ui.showError(error.message || 'فشل الموافقة على البائع');
        }
    }

    async function rejectSeller(userId) {
        if (!await ui.confirm('هل أنت متأكد من رفض هذا البائع؟', 'رفض بائع')) return;
        try {
            await api.rejectSeller(userId);
            ui.showSuccess('تم رفض البائع بنجاح.');
            loadPendingSellers();
        } catch (error) {
            ui.showError(error.message || 'فشل رفض البائع');
        }
    }

    loadPendingSellers();
</script>
@endsection
