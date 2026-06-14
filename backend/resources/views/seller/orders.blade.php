@extends('layouts.dashboard')

@section('title', 'الطلبات الواردة | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<div class="flex justify-between items-center mb-6">
    <select id="filter-status" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
        <option value="">جميع الحالات</option>
        <option value="pending">قيد الانتظار</option>
        <option value="processing">قيد المعالجة</option>
        <option value="shipped">تم الشحن</option>
        <option value="delivered">تم التوصيل</option>
        <option value="cancelled">ملغي</option>
    </select>
</div>

<div class="bg-secondary border border-color rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-tertiary">
                <tr>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">رقم الطلب</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">المنتج</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">المشتري</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">المبلغ</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">الحالة</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">التاريخ</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">إجراءات</th>
                </tr>
            </thead>
            <tbody id="orders-table">
                <tr>
                    <td colspan="7" class="text-center py-12 text-secondary">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>جاري تحميل الطلبات...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadOrders() {
        try {
            const response = await api.getSellerOrders();
            const orders = response.data?.data || response.data || [];
            window.allOrders = orders;
            displayOrders(orders);
            return orders;
        } catch (error) {
            console.error('Error loading orders:', error);
            ui.showError('فشل تحميل الطلبات');
        }
    }

    function displayOrders(orders) {
        const tbody = document.getElementById('orders-table');
        if (orders.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-secondary"><i class="fas fa-inbox text-6xl mb-4"></i><p class="text-xl">لا توجد طلبات</p></td></tr>';
            return;
        }

        const statusValue = document.getElementById('filter-status').value;
        let filtered = orders.filter(o => !statusValue || o.status === statusValue);

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-12 text-secondary"><i class="fas fa-search text-6xl mb-4"></i><p class="text-xl">لا توجد نتائج</p></td></tr>';
            return;
        }

        const statusColors = {
            'pending': 'bg-yellow-500 bg-opacity-20 text-yellow-500',
            'processing': 'bg-blue-500 bg-opacity-20 text-blue-500',
            'shipped': 'bg-purple-500 bg-opacity-20 text-purple-500',
            'delivered': 'bg-green-500 bg-opacity-20 text-green-500',
            'cancelled': 'bg-red-500 bg-opacity-20 text-red-500'
        };

        const statusText = {
            'pending': 'قيد الانتظار',
            'processing': 'قيد المعالجة',
            'shipped': 'تم الشحن',
            'delivered': 'تم التوصيل',
            'cancelled': 'ملغي'
        };

        tbody.innerHTML = filtered.map(order => `
            <tr class="border-t border-color hover:bg-tertiary transition">
                <td class="py-4 px-6 text-primary whitespace-nowrap">#${order.id}</td>
                <td class="py-4 px-6 text-primary whitespace-nowrap">${order.product?.name || 'غير متوفر'}</td>
                <td class="py-4 px-6 text-primary whitespace-nowrap">${order.buyer?.first_name || ''} ${order.buyer?.last_name || ''}</td>
                <td class="py-4 px-6 text-green-500 font-semibold whitespace-nowrap">${order.total_amount} ر.س</td>
                <td class="py-4 px-6 whitespace-nowrap">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColors[order.status]}">
                        ${statusText[order.status]}
                    </span>
                </td>
                <td class="py-4 px-6 text-secondary text-sm whitespace-nowrap">${new Date(order.created_at).toLocaleDateString('ar-SA')}</td>
                <td class="py-4 px-6 whitespace-nowrap">
                    <select onchange="updateStatus(${order.id}, this.value)" class="bg-tertiary border border-color rounded px-2 py-1 text-sm text-primary">
                        <option value="">تغيير الحالة</option>
                        <option value="processing">قيد المعالجة</option>
                        <option value="shipped">تم الشحن</option>
                        <option value="delivered">تم التوصيل</option>
                        <option value="cancelled">إلغاء</option>
                    </select>
                </td>
            </tr>
        `).join('');
    }

    async function updateStatus(orderId, status) {
        if (!status) return;
        try {
            await api.updateOrderStatus(orderId, status);
            ui.showSuccess('تم تحديث حالة الطلب!');
            loadOrders();
        } catch (error) {
            console.error('Error updating status:', error);
            ui.showError(error.message || 'فشل تحديث الحالة');
        }
    }

    document.getElementById('filter-status').addEventListener('change', () => {
        if (window.allOrders) displayOrders(window.allOrders);
    });

    loadOrders().then(orders => {
        if (orders) {
            window.allOrders = orders;
        }
    });
</script>
@endsection
