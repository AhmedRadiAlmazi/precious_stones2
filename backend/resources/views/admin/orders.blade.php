@extends('layouts.dashboard')

@section('title', 'جميع الطلبات | جوهرة')

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
    <h1 class="text-2xl font-bold gold-text">جميع الطلبات في النظام</h1>
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
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">البائع</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">المشتري</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">المبلغ</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">الحالة</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">التاريخ</th>
                    <th class="text-right py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="orders-table">
                <tr>
                    <td colspan="8" class="text-center py-12 text-secondary">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>جاري تحميل الطلبات...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Order Status Modal -->
<div id="order-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-secondary rounded-xl shadow-2xl max-w-sm w-full mx-4 border border-color">
        <div class="p-6 border-b border-color flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary" id="order-modal-title">تحديث حالة الطلب</h2>
            <button onclick="closeOrderModal()" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="order-form" class="p-6 space-y-4">
            <input type="hidden" id="order-id">
            
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">رقم الطلب</label>
                <div id="order-number-display" class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 text-secondary text-sm font-semibold">
                    #ORD-1234
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">حالة الطلب الجديدة *</label>
                <select id="order-status" required class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                    <option value="pending">قيد الانتظار (Pending)</option>
                    <option value="processing">قيد المعالجة (Processing)</option>
                    <option value="shipped">تم الشحن (Shipped)</option>
                    <option value="delivered">تم التوصيل (Delivered)</option>
                    <option value="cancelled">ملغي (Cancelled)</option>
                </select>
            </div>

            <div class="flex gap-3 pt-4 border-t border-color mt-6">
                <button type="submit" class="flex-1 bg-gold hover:bg-yellow-600 text-black py-2.5 rounded-lg font-bold transition">
                    <i class="fas fa-save ml-1"></i>حفظ التعديلات
                </button>
                <button type="button" onclick="closeOrderModal()" class="flex-1 bg-tertiary text-primary py-2.5 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    async function loadOrders() {
        try {
            const response = await api.request('/admin/orders');
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
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-12 text-secondary"><i class="fas fa-inbox text-6xl mb-4"></i><p class="text-xl">لا توجد طلبات</p></td></tr>';
            return;
        }

        const statusValue = document.getElementById('filter-status').value;
        let filtered = orders.filter(o => !statusValue || o.status === statusValue);

        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-12 text-secondary"><i class="fas fa-search text-6xl mb-4"></i><p class="text-xl">لا توجد نتائج</p></td></tr>';
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
                <td class="py-4 px-6 text-sm font-semibold text-primary whitespace-nowrap">
                    ${order.order_number}
                </td>
                <td class="py-4 px-6 text-sm text-primary">
                    <div class="flex items-center gap-2">
                        <span>${order.product ? order.product.name : 'منتج غير معروف'}</span>
                    </div>
                </td>
                <td class="py-4 px-6 text-sm text-secondary font-semibold">
                    ${order.seller ? `${order.seller.first_name} ${order.seller.last_name}` : 'غير معروف'}
                </td>
                <td class="py-4 px-6 text-sm text-secondary font-semibold">
                    ${order.buyer ? `${order.buyer.first_name} ${order.buyer.last_name}` : 'غير معروف'}
                </td>
                <td class="py-4 px-6 text-sm font-bold text-gold">
                    ${parseFloat(order.total_amount).toLocaleString()} ر.س
                </td>
                <td class="py-4 px-6 text-sm">
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold ${statusColors[order.status] || 'bg-gray-500 bg-opacity-20 text-gray-500'}">
                        ${statusText[order.status] || order.status}
                    </span>
                </td>
                <td class="py-4 px-6 text-sm text-secondary whitespace-nowrap">
                    ${new Date(order.created_at).toLocaleDateString('ar-SA')}
                </td>
                <td class="py-4 px-6 text-sm whitespace-nowrap">
                    <button onclick="editOrderStatus(${order.id})" 
                        class="text-xs px-3 py-1.5 rounded bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-blue-500 transition font-bold"
                        title="تحديث الحالة">
                        <i class="fas fa-edit ml-1"></i>تعديل الحالة
                    </button>
                </td>
            </tr>
        `).join('');
    }

    function editOrderStatus(id) {
        const order = window.allOrders.find(o => o.id === id);
        if (!order) return;

        document.getElementById('order-id').value = order.id;
        document.getElementById('order-number-display').textContent = '#' + order.order_number;
        document.getElementById('order-status').value = order.status;

        const modal = document.getElementById('order-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeOrderModal() {
        const modal = document.getElementById('order-modal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    document.getElementById('order-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('order-id').value;
        const newStatus = document.getElementById('order-status').value;

        try {
            const response = await api.updateAdminOrderStatus(id, newStatus);
            if (response.success) {
                ui.showSuccess('تم تحديث حالة الطلب بنجاح!');
                closeOrderModal();
                loadOrders();
            } else {
                ui.showError(response.message || 'فشل التحديث');
            }
        } catch (error) {
            console.error(error);
            ui.showError(error.message || 'حدث خطأ أثناء حفظ التعديلات');
        }
    });

    document.getElementById('filter-status').addEventListener('change', () => {
        if (window.allOrders) displayOrders(window.allOrders);
    });

    loadOrders();
</script>
@endsection
