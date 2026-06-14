@extends('layouts.dashboard')

@section('title', 'الإحصائيات | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-full bg-blue-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-box text-blue-500 text-xl"></i>
            </div>
        </div>
        <p class="text-secondary text-sm mb-1">إجمالي المنتجات</p>
        <p class="text-3xl font-bold text-primary" id="total-products">0</p>
    </div>
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-full bg-purple-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-gavel text-purple-500 text-xl"></i>
            </div>
        </div>
        <p class="text-secondary text-sm mb-1">إجمالي المزادات</p>
        <p class="text-3xl font-bold text-primary" id="total-auctions">0</p>
    </div>
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-shopping-cart text-green-500 text-xl"></i>
            </div>
        </div>
        <p class="text-secondary text-sm mb-1">إجمالي الطلبات</p>
        <p class="text-3xl font-bold text-primary" id="total-orders">0</p>
    </div>
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 rounded-full bg-yellow-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
            </div>
        </div>
        <p class="text-secondary text-sm mb-1">إجمالي الإيرادات</p>
        <p class="text-3xl font-bold text-gold" id="total-revenue">0 ر.س</p>
    </div>
</div>

<div class="bg-secondary border border-color rounded-xl p-6 mb-6">
    <h3 class="text-xl font-bold text-primary mb-4">أفضل المنتجات مبيعاً</h3>
    <div id="top-products" class="space-y-3">
        <div class="text-center py-6 text-secondary">جاري تحميل البيانات...</div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadStatistics() {
        try {
            const response = await api.getSellerStatistics();
            const stats = response.data || response;
            
            document.getElementById('total-products').textContent = stats.total_products || 0;
            document.getElementById('total-auctions').textContent = stats.total_auctions || 0;
            document.getElementById('total-orders').textContent = stats.total_orders || 0;
            document.getElementById('total-revenue').textContent = `${stats.total_revenue || 0} ر.س`;

            const topProducts = stats.top_products || [];
            const container = document.getElementById('top-products');
            if (topProducts.length === 0) {
                container.innerHTML = '<p class="text-secondary text-center py-4">لا توجد بيانات</p>';
            } else {
                container.innerHTML = topProducts.map((p, i) => `
                    <div class="flex items-center justify-between p-3 bg-tertiary rounded-lg">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-full bg-gold bg-opacity-20 flex items-center justify-center text-gold font-bold">${i + 1}</span>
                            <div>
                                <p class="text-primary font-semibold">${p.name}</p>
                                <p class="text-secondary text-sm">${p.orders_count || 0} طلب</p>
                            </div>
                        </div>
                        <p class="text-green-500 font-semibold">${p.price} ر.س</p>
                    </div>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading statistics:', error);
            ui.showError('فشل تحميل الإحصائيات');
        }
    }

    loadStatistics();
</script>
@endsection
