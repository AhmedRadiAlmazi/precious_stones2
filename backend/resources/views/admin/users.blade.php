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
                <option value="user">مستخدم عادي</option>
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
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">البريد الإلكتروني</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">نوع الحساب</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">تاريخ الانضمام</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الحالة</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-secondary">
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
@endsection

@section('scripts')
<script>
    let currentPage = 1;
    let totalPages = 1;

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
            displayUsers(response.data || response);
        } catch (error) {
            console.error('Error loading users:', error);
            if (error.status === 401) {
                window.location.href = '{{ url("/login") }}';
                return;
            }
            document.getElementById('users-tbody').innerHTML = '<tr><td colspan="6" class="text-center p-4 text-red-500">فشل تحميل البيانات: ' + error.message + '</td></tr>';
        }
    }

    function displayUsers(data) {
        const tbody = document.getElementById('users-tbody');
        const users = data.data || [];
        
        if (users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-secondary">
                        <i class="fas fa-users-slash text-6xl mb-4"></i>
                        <p class="text-xl">لا يوجد مستخدمين</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = users.map(u => `
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
                <td class="px-6 py-4 text-secondary">${u.email}</td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-500 bg-opacity-20 text-blue-500 rounded-full text-xs">
                        ${u.account_type === 'seller' ? 'بائع' : 'مشتري'}
                    </span>
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
                    <button onclick="toggleUserStatus(${u.id}, ${u.is_active})" 
                        class="text-sm px-4 py-2 rounded ${u.is_active ? 'bg-red-500 text-red-500' : 'bg-green-500 text-green-500'} bg-opacity-25 hover:bg-opacity-35 transition font-bold"
                        title="${u.is_active ? 'تعطيل الحساب' : 'تفعيل الحساب'}">
                        ${u.is_active ? 'تعطيل' : 'تفعيل'}
                    </button>
                </td>
            </tr>
        `).join('');

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

    async function toggleUserStatus(id, currentStatus) {
        const action = currentStatus ? 'تعطيل' : 'تفعيل';
        if (!confirm(`هل أنت متأكد من ${action} هذا المستخدم؟`)) return;

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
