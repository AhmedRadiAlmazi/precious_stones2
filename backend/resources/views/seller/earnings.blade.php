@extends('layouts.dashboard')

@section('title', 'الأرباح | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-green-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-dollar-sign text-green-500 text-xl"></i>
            </div>
            <div>
                <p class="text-secondary text-sm">إجمالي الأرباح</p>
                <p class="text-2xl font-bold text-green-500" id="total-earnings">0 ر.س</p>
            </div>
        </div>
    </div>
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-red-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-percent text-red-500 text-xl"></i>
            </div>
            <div>
                <p class="text-secondary text-sm">العمولة (<span id="commission-rate">0</span>%)</p>
                <p class="text-2xl font-bold text-red-500" id="commission">0 ر.س</p>
            </div>
        </div>
    </div>
    <div class="bg-secondary border border-color rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 rounded-full bg-yellow-500 bg-opacity-20 flex items-center justify-center">
                <i class="fas fa-wallet text-yellow-600 text-xl"></i>
            </div>
            <div>
                <p class="text-secondary text-sm">صافي الأرباح</p>
                <p class="text-2xl font-bold text-gold" id="net-earnings">0 ر.س</p>
            </div>
        </div>
    </div>
</div>

<div class="bg-secondary border border-color rounded-xl p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-bold text-primary">المدفوعات المعلقة</h3>
        <p class="text-2xl font-bold text-yellow-500" id="pending-payments">0 ر.س</p>
    </div>
</div>

<div class="bg-secondary border border-color rounded-xl p-6">
    <h3 class="text-xl font-bold text-primary mb-4">سجل المدفوعات</h3>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-tertiary">
                <tr>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-primary">المنتج</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-primary">المبلغ</th>
                    <th class="text-right py-3 px-4 text-sm font-semibold text-primary">التاريخ</th>
                </tr>
            </thead>
            <tbody id="payment-history">
                <tr>
                    <td colspan="3" class="text-center py-6 text-secondary">جاري تحميل البيانات...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadEarnings() {
        try {
            const response = await api.getSellerEarnings();
            const data = response.data || response;
            
            document.getElementById('total-earnings').textContent = `${data.total_earnings || 0} ر.س`;
            document.getElementById('commission').textContent = `${data.commission || 0} ر.س`;
            document.getElementById('commission-rate').textContent = data.commission_rate || 0;
            document.getElementById('net-earnings').textContent = `${data.net_earnings || 0} ر.س`;
            document.getElementById('pending-payments').textContent = `${data.pending_payments || 0} ر.س`;

            const history = data.payment_history || [];
            const tbody = document.getElementById('payment-history');
            if (history.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-8 text-secondary">لا توجد مدفوعات</td></tr>';
            } else {
                tbody.innerHTML = history.map(h => `
                    <tr class="border-t border-color hover:bg-tertiary transition">
                        <td class="py-3 px-4 text-primary">${h.product?.name || 'غير متوفر'}</td>
                        <td class="py-3 px-4 text-green-500 font-semibold">${h.total_amount} ر.س</td>
                        <td class="py-3 px-4 text-secondary text-sm">${new Date(h.created_at).toLocaleDateString('ar-SA')}</td>
                    </tr>
                `).join('');
            }
        } catch (error) {
            console.error('Error loading earnings:', error);
            ui.showError('فشل تحميل الأرباح');
        }
    }

    loadEarnings();
</script>
@endsection
