@extends('layouts.dashboard')

@section('title', 'إدارة المستخدمين | جوهرة')

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

<!-- Filters -->
<div class="bg-secondary border border-color rounded-xl p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
        <div class="md:col-span-6">
            <input type="text" id="search-users" placeholder="البحث عن اسم أو بريد..." 
                class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
        </div>
        
        <div class="md:col-span-3">
            <select id="filter-account-type" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                <option value="">جميع أنواع الحسابات</option>
                <option value="seller">بائع</option>
                <option value="buyer">مشتري</option>
            </select>
        </div>

        <div class="md:col-span-3">
            <select id="filter-role" class="w-full bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary">
                <option value="">جميع الأدوار</option>
                <option value="admin">مدير</option>
                <option value="seller">بائع</option>
                <option value="buyer">مشتري</option>
            </select>
        </div>
    </div>
</div>

<!-- Users Table -->
<div class="bg-secondary border border-color rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-tertiary border-b border-color">
                <tr>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">المستخدم</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">البريد الإلكتروني / الهاتف</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">نوع الحساب</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">رصيد المحفظة</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">تاريخ الانضمام</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الحالة</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-secondary">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>جاري تحميل المستخدمين...</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div id="pagination" class="px-6 py-4 border-t border-color flex justify-between items-center">
        <div class="text-sm text-secondary" id="pagination-info">
            عرض 0 من 0
        </div>
        <div class="flex gap-2" id="pagination-buttons">
            <!-- Pagination buttons will be inserted here -->
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="user-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-secondary rounded-xl shadow-2xl max-w-md w-full mx-4 border border-color">
        <div class="p-6 border-b border-color flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary" id="modal-title">تعديل بيانات المستخدم</h2>
            <button onclick="closeUserModal()" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="user-form" class="p-6 space-y-4">
            <input type="hidden" id="user-id">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">الاسم الأول *</label>
                    <input type="text" id="user-first-name" required
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">الاسم الأخير *</label>
                    <input type="text" id="user-last-name" required
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">البريد الإلكتروني *</label>
                <input type="email" id="user-email" required
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">رقم الهاتف *</label>
                <input type="text" id="user-phone" required
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">الدور / الصلاحية *</label>
                <select id="user-role" required class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                    <option value="buyer">مشتري (Buyer)</option>
                    <option value="seller">بائع (Seller)</option>
                    <option value="admin">مدير النظام (Admin)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">رصيد المحفظة (ر.س) *</label>
                <input type="number" id="user-wallet-balance" required min="0" step="0.01"
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="user-active" class="w-4 h-4 rounded text-gold focus:ring-gold bg-tertiary border-color">
                <label for="user-active" class="text-sm text-primary">حساب نشط (غير معطل)</label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-color mt-6">
                <button type="submit" class="flex-1 bg-gold hover:bg-yellow-600 text-black py-2.5 rounded-lg font-bold transition">
                    <i class="fas fa-save ml-1"></i>حفظ التعديلات
                </button>
                <button type="button" onclick="closeUserModal()" class="flex-1 bg-tertiary text-primary py-2.5 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
                    إلغاء
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let currentPage = 1;
    let totalPages = 1;
    window.allUsers = [];

    async function loadUsers(page = 1) {
        try {
            const params = { page };
            
            const searchValue = document.getElementById('search-users').value;
            if (searchValue) params.search = searchValue;
            
            const typeValue = document.getElementById('filter-account-type').value;
            if (typeValue) params.account_type = typeValue;
            
            const roleValue = document.getElementById('filter-role').value;
            if (roleValue) params.role = roleValue;

            const response = await api.getAllUsers(params);
            const data = response.data || response;
            window.allUsers = data.data || [];
            displayUsers(data);
        } catch (error) {
            console.error('Error loading users:', error);
            if (error.status === 401) {
                window.location.href = '{{ url("/login") }}';
                return;
            }
            document.getElementById('users-tbody').innerHTML = '<tr><td colspan="7" class="text-center p-4 text-red-500">فشل تحميل البيانات: ' + error.message + '</td></tr>';
        }
    }

    function displayUsers(data) {
        const tbody = document.getElementById('users-tbody');
        const users = data.data || [];
        
        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-secondary">
                        <i class="fas fa-users-slash text-6xl mb-4"></i>
                        <p class="text-xl">لا يوجد مستخدمين</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = users.map(u => {
            const role = u.roles && u.roles.length ? u.roles[0].name : u.account_type;
            const roleLabel = role === 'admin' ? 'مدير' : (role === 'seller' ? 'بائع' : 'مشتري');
            const roleColor = role === 'admin' ? 'bg-red-500 text-red-500' : (role === 'seller' ? 'bg-yellow-500 text-yellow-500' : 'bg-blue-500 text-blue-500');

            return `
                <tr class="border-b border-color hover:bg-tertiary hover:bg-opacity-50 transition">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gold text-black flex items-center justify-center font-bold">
                                ${u.first_name ? u.first_name[0] : 'U'}
                            </div>
                            <div>
                                <div class="font-semibold text-primary">${u.first_name} ${u.last_name}</div>
                                <div class="text-xs text-secondary">ID: ${u.id}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-primary text-sm">${u.email}</div>
                        <div class="text-xs text-secondary">${u.phone || '-'}</div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 ${roleColor} bg-opacity-20 rounded-full text-xs font-bold">
                            ${roleLabel}
                        </span>
                    </td>
                    <td class="px-6 py-4 font-bold text-gold text-sm">
                        ${parseFloat(u.wallet_balance || 100000).toLocaleString()} ر.س
                    </td>
                    <td class="px-6 py-4 text-secondary text-sm">
                        ${new Date(u.created_at).toLocaleDateString('ar-EG')}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                            u.is_active 
                                ? 'bg-green-500 bg-opacity-20 text-green-500' 
                                : 'bg-red-500 bg-opacity-20 text-red-500'
                        }">
                            ${u.is_active ? 'نشط' : 'معطل'}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <button onclick="editUser(${u.id})" 
                                class="text-sm px-3 py-1.5 rounded bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-blue-500 transition font-bold"
                                title="تعديل">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="toggleUserStatus(${u.id}, ${u.is_active})" 
                                class="text-sm px-3 py-1.5 rounded ${u.is_active ? 'bg-red-500 text-red-500' : 'bg-green-500 text-green-500'} bg-opacity-20 hover:bg-opacity-30 transition font-bold"
                                title="${u.is_active ? 'تعطيل الحساب' : 'تفعيل الحساب'}">
                                <i class="fas fa-${u.is_active ? 'ban' : 'check'}"></i>
                            </button>
                            <button onclick="deleteUser(${u.id})" 
                                class="text-sm px-3 py-1.5 rounded bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-red-500 transition font-bold"
                                title="حذف">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        currentPage = data.current_page || 1;
        totalPages = data.last_page || 1;
        updatePagination(data);
    }
    
    function updatePagination(data) {
        document.getElementById('pagination-info').textContent = 
            `عرض ${data.from || 0} - ${data.to || 0} من ${data.total || 0}`;

        const buttonsContainer = document.getElementById('pagination-buttons');
        buttonsContainer.innerHTML = '';

        if (totalPages <= 1) return;

        if (currentPage > 1) {
            const prevBtn = document.createElement('button');
            prevBtn.className = 'px-3 py-1 bg-tertiary rounded hover:bg-gold hover:text-black transition text-primary';
            prevBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
            prevBtn.onclick = () => loadUsers(currentPage - 1);
            buttonsContainer.appendChild(prevBtn);
        }
        
        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.className = 'px-3 py-1 bg-tertiary rounded hover:bg-gold hover:text-black transition text-primary';
            nextBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            nextBtn.onclick = () => loadUsers(currentPage + 1);
            buttonsContainer.appendChild(nextBtn);
        }
    }

    function editUser(id) {
        const user = window.allUsers.find(u => u.id === id);
        if (!user) return;
        
        document.getElementById('user-id').value = user.id;
        document.getElementById('user-first-name').value = user.first_name || '';
        document.getElementById('user-last-name').value = user.last_name || '';
        document.getElementById('user-email').value = user.email || '';
        document.getElementById('user-phone').value = user.phone || '';
        
        const role = user.roles && user.roles.length ? user.roles[0].name : user.account_type;
        document.getElementById('user-role').value = role;
        document.getElementById('user-wallet-balance').value = user.wallet_balance || 100000;
        document.getElementById('user-active').checked = user.is_active;
        
        const modal = document.getElementById('user-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeUserModal() {
        const modal = document.getElementById('user-modal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    document.getElementById('user-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('user-id').value;
        const data = {
            first_name: document.getElementById('user-first-name').value,
            last_name: document.getElementById('user-last-name').value,
            email: document.getElementById('user-email').value,
            phone: document.getElementById('user-phone').value,
            role: document.getElementById('user-role').value,
            wallet_balance: parseFloat(document.getElementById('user-wallet-balance').value),
            is_active: document.getElementById('user-active').checked ? 1 : 0
        };
        
        try {
            const response = await api.updateAdminUser(id, data);
            if (response.success) {
                ui.showSuccess('تم تحديث بيانات المستخدم بنجاح!');
                closeUserModal();
                loadUsers(currentPage);
            } else {
                ui.showError(response.message || 'فشل التحديث');
            }
        } catch (error) {
            console.error(error);
            ui.showError(error.message || 'حدث خطأ أثناء حفظ التعديلات');
        }
    });

    async function deleteUser(id) {
        const approved = await ui.confirm('هل أنت متأكد من حذف هذا المستخدم نهائياً؟ لا يمكن التراجع عن هذا الإجراء.', 'تأكيد حذف المستخدم');
        if (!approved) return;
        
        try {
            const response = await api.deleteAdminUser(id);
            if (response.success) {
                ui.showSuccess(response.message || 'تم حذف المستخدم بنجاح!');
                loadUsers(currentPage);
            } else {
                ui.showError(response.message || 'فشل حذف المستخدم');
            }
        } catch (error) {
            console.error(error);
            ui.showError(error.message || 'حدث خطأ أثناء حذف المستخدم');
        }
    }

    async function toggleUserStatus(id, currentStatus) {
        const action = currentStatus ? 'تعطيل' : 'تفعيل';
        const approved = await ui.confirm(`هل أنت متأكد من ${action} هذا المستخدم؟`, 'تأكيد تغيير حالة المستخدم');
        if (!approved) return;

        try {
            const response = await api.request(`/admin/users/${id}/toggle-status`, {
                method: 'POST'
            });
            
            if(response.success) {
                 ui.showSuccess(response.message || 'تم تحديث الحالة بنجاح');
                 loadUsers(currentPage);
            } else {
                 ui.showError(response.message || 'حدث خطأ');
            }
        } catch (error) {
            console.error(error);
            ui.showError('فشل تعطيل/تفعيل المستخدم: ' + error.message);
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    document.getElementById('search-users').addEventListener('input', debounce(() => loadUsers(1), 500));
    document.getElementById('filter-account-type').addEventListener('change', () => loadUsers(1));
    document.getElementById('filter-role').addEventListener('change', () => loadUsers(1));

    loadUsers();
</script>
@endsection
