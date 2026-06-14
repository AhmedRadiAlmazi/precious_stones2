@extends('layouts.dashboard')

@section('title', 'إدارة المنتجات | جوهرة')

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
    <div class="flex flex-col md:flex-row gap-4">
        <input type="text" id="search-products" placeholder="البحث في المنتجات..." 
            class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto md:flex-1">
        
        <select id="filter-category" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الفئات</option>
        </select>

        <select id="filter-status" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الحالات</option>
            <option value="1">نشط</option>
            <option value="0">غير نشط</option>
        </select>

        <select id="sort-by" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="created_at">الأحدث</option>
            <option value="name">الاسم</option>
            <option value="price">السعر</option>
            <option value="stock">المخزون</option>
        </select>
    </div>
</div>

<!-- Products Table -->
<div class="bg-secondary border border-color rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-tertiary border-b border-color">
                <tr>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">المنتج</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الفئة</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">البائع</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">السعر</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">المخزون</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الحالة</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-primary whitespace-nowrap">الإجراءات</th>
                </tr>
            </thead>
            <tbody id="products-tbody">
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-secondary">
                        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
                        <p>جاري تحميل المنتجات...</p>
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

<!-- Edit Product Modal -->
<div id="product-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-secondary rounded-xl shadow-2xl max-w-lg w-full mx-4 border border-color overflow-hidden">
        <div class="p-6 border-b border-color flex justify-between items-center">
            <h2 class="text-xl font-bold text-primary" id="product-modal-title">تعديل بيانات المنتج</h2>
            <button onclick="closeProductModal()" class="text-secondary hover:text-primary">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form id="product-form" class="p-6 space-y-4 max-h-[80vh] overflow-y-auto">
            <input type="hidden" id="product-id">
            
            <div>
                <label class="block text-xs font-semibold text-primary mb-1">اسم المنتج *</label>
                <input type="text" id="product-name" required
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">السعر (ر.س) *</label>
                    <input type="number" id="product-price" required min="0" step="0.01"
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">المخزون *</label>
                    <input type="number" id="product-stock" required min="0"
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">الوزن بالقيراط *</label>
                    <input type="number" id="product-weight" required min="0" step="0.01"
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">بلد المنشأ</label>
                    <input type="text" id="product-origin"
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">الفئة *</label>
                    <select id="product-category" required class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                        <!-- Filled dynamically -->
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-primary mb-1">الشهادة / التوثيق</label>
                    <input type="text" id="product-certification"
                        class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-primary mb-1">الوصف</label>
                <textarea id="product-description" rows="3"
                    class="w-full bg-tertiary border border-color rounded-lg py-2 px-3 focus:outline-none focus:border-gold text-primary text-sm"></textarea>
            </div>

            <div class="flex gap-4 pt-2">
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="product-featured" class="w-4 h-4 rounded text-gold focus:ring-gold bg-tertiary border-color">
                    <label for="product-featured" class="text-sm text-primary">منتج مميز (Featured)</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="product-active" class="w-4 h-4 rounded text-gold focus:ring-gold bg-tertiary border-color">
                    <label for="product-active" class="text-sm text-primary">منتج نشط (Active)</label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-color mt-6">
                <button type="submit" class="flex-1 bg-gold hover:bg-yellow-600 text-black py-2.5 rounded-lg font-bold transition">
                    <i class="fas fa-save ml-1"></i>حفظ التعديلات
                </button>
                <button type="button" onclick="closeProductModal()" class="flex-1 bg-tertiary text-primary py-2.5 rounded-lg font-semibold hover:bg-opacity-80 transition border border-color">
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
    window.allProducts = [];
    window.allCategories = [];

    // Load categories for filter and form
    async function loadCategories() {
        try {
            const response = await api.request('/categories').catch(() => api.getAdminCategories({ paginate: 'false', is_active: 1 }));
            const categories = response.data || response || [];
            window.allCategories = categories;

            const selectFilter = document.getElementById('filter-category');
            const selectForm = document.getElementById('product-category');
            
            selectFilter.innerHTML = '<option value="">جميع الفئات</option>';
            selectForm.innerHTML = '';

            categories.forEach(cat => {
                const optFilter = document.createElement('option');
                optFilter.value = cat.id;
                optFilter.textContent = cat.name;
                selectFilter.appendChild(optFilter);

                const optForm = document.createElement('option');
                optForm.value = cat.id;
                optForm.textContent = cat.name;
                selectForm.appendChild(optForm);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load products
    async function loadProducts(page = 1) {
        try {
            const params = { page };
            
            const searchValue = document.getElementById('search-products').value;
            if (searchValue) params.search = searchValue;
            
            const categoryValue = document.getElementById('filter-category').value;
            if (categoryValue) params.category_id = categoryValue;
            
            const statusValue = document.getElementById('filter-status').value;
            if (statusValue !== '') params.is_active = statusValue;

            const sortValue = document.getElementById('sort-by').value;
            params.sort_by = sortValue;
            params.sort_order = 'desc';

            const response = await api.getAdminProducts(params);
            const data = response.data || response;
            window.allProducts = data.data || [];
            displayProducts(data);
        } catch (error) {
            console.error('Error loading products:', error);
            ui.showError('فشل تحميل المنتجات');
        }
    }

    function displayProducts(data) {
        const tbody = document.getElementById('products-tbody');
        const products = data.data || [];
        
        if (products.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-secondary">
                        <i class="fas fa-box-open text-6xl mb-4"></i>
                        <p class="text-xl">لا توجد منتجات</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = products.map(product => `
            <tr class="border-b border-color hover:bg-tertiary hover:bg-opacity-50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <img src="${product.images && product.images[0] || 'https://via.placeholder.com/60'}" 
                            alt="${product.name}" 
                            class="w-12 h-12 rounded-lg object-cover">
                        <div>
                            <div class="font-semibold text-primary">${product.name}</div>
                            <div class="text-xs text-secondary">#${product.id}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 bg-blue-500 bg-opacity-20 text-blue-500 rounded-full text-xs font-bold">
                        ${product.category ? product.category.name : 'غير محدد'}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-secondary font-semibold">
                    ${product.seller ? product.seller.first_name + ' ' + product.seller.last_name : 'غير محدد'}
                </td>
                <td class="px-6 py-4">
                    <span class="font-bold text-green-500 text-sm">${product.price} ر.س</span>
                </td>
                <td class="px-6 py-4 font-bold text-sm">
                    <span class="${product.stock > 10 ? 'text-green-500' : product.stock > 0 ? 'text-yellow-500' : 'text-red-500'}">
                        ${product.stock}
                    </span>
                </td>
                <td class="px-6 py-4">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                        product.is_active 
                            ? 'bg-green-500 bg-opacity-20 text-green-500' 
                            : 'bg-red-500 bg-opacity-20 text-red-500'
                    }">
                        ${product.is_active ? 'نشط' : 'غير نشط'}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex gap-2">
                        <button onclick="editProduct(${product.id})" 
                            class="text-sm px-3 py-1.5 rounded bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-blue-500 transition font-bold"
                            title="تعديل">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="toggleProductStatus(${product.id})" 
                            class="text-sm px-3 py-1.5 rounded ${product.is_active ? 'bg-red-500 text-red-500' : 'bg-green-500 text-green-500'} bg-opacity-20 hover:bg-opacity-30 transition font-bold"
                            title="${product.is_active ? 'إلغاء التفعيل' : 'تفعيل'}">
                            <i class="fas fa-${product.is_active ? 'eye-slash' : 'eye'}"></i>
                        </button>
                        <button onclick="deleteProduct(${product.id})" 
                            class="text-sm px-3 py-1.5 rounded bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-red-500 transition font-bold"
                            title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
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
            prevBtn.onclick = () => loadProducts(currentPage - 1);
            buttonsContainer.appendChild(prevBtn);
        }

        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `px-3 py-1 rounded transition ${
                    i === currentPage 
                        ? 'bg-gold text-black font-bold' 
                        : 'bg-tertiary hover:bg-gold hover:text-black text-primary'
                }`;
                pageBtn.textContent = i;
                pageBtn.onclick = () => loadProducts(i);
                buttonsContainer.appendChild(pageBtn);
            } else if (i === currentPage - 2 || i === currentPage + 2) {
                const dots = document.createElement('span');
                dots.className = 'px-2 text-secondary';
                dots.textContent = '...';
                buttonsContainer.appendChild(dots);
            }
        }

        if (currentPage < totalPages) {
            const nextBtn = document.createElement('button');
            nextBtn.className = 'px-3 py-1 bg-tertiary rounded hover:bg-gold hover:text-black transition text-primary';
            nextBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            nextBtn.onclick = () => loadProducts(currentPage + 1);
            buttonsContainer.appendChild(nextBtn);
        }
    }

    function editProduct(id) {
        const product = window.allProducts.find(p => p.id === id);
        if (!product) return;

        document.getElementById('product-id').value = product.id;
        document.getElementById('product-name').value = product.name || '';
        document.getElementById('product-price').value = product.price || 0;
        document.getElementById('product-stock').value = product.stock || 0;
        document.getElementById('product-weight').value = product.weight || 0;
        document.getElementById('product-origin').value = product.origin_country || '';
        document.getElementById('product-certification').value = product.certification || '';
        document.getElementById('product-category').value = product.category_id || '';
        document.getElementById('product-description').value = product.description || '';
        document.getElementById('product-featured').checked = !!product.is_featured;
        document.getElementById('product-active').checked = !!product.is_active;

        const modal = document.getElementById('product-modal');
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }

    function closeProductModal() {
        const modal = document.getElementById('product-modal');
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    document.getElementById('product-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('product-id').value;
        const data = {
            name: document.getElementById('product-name').value,
            price: parseFloat(document.getElementById('product-price').value),
            stock: parseInt(document.getElementById('product-stock').value),
            weight: parseFloat(document.getElementById('product-weight').value),
            origin_country: document.getElementById('product-origin').value,
            certification: document.getElementById('product-certification').value,
            category_id: parseInt(document.getElementById('product-category').value),
            description: document.getElementById('product-description').value,
            is_featured: document.getElementById('product-featured').checked ? 1 : 0,
            is_active: document.getElementById('product-active').checked ? 1 : 0
        };

        try {
            const response = await api.updateAdminProduct(id, data);
            if (response.success) {
                ui.showSuccess('تم تحديث بيانات المنتج بنجاح!');
                closeProductModal();
                loadProducts(currentPage);
            } else {
                ui.showError(response.message || 'فشل التحديث');
            }
        } catch (error) {
            console.error(error);
            ui.showError(error.message || 'حدث خطأ أثناء حفظ التعديلات');
        }
    });

    async function toggleProductStatus(id) {
        try {
            await api.toggleProductStatus(id);
            ui.showSuccess('تم تغيير حالة المنتج بنجاح!');
            loadProducts(currentPage);
        } catch (error) {
            console.error('Error toggling status:', error);
            ui.showError(error.message || 'فشل تغيير الحالة');
        }
    }

    async function deleteProduct(id) {
        const approved = await ui.confirm('هل أنت متأكد من حذف هذا المنتج؟\n\nملاحظة: لا يمكن حذف منتجات مرتبطة بمزادات نشطة أو معلقة.', 'تأكيد حذف المنتج');
        if (!approved) return;

        try {
            await api.deleteAdminProduct(id);
            ui.showSuccess('تم حذف المنتج بنجاح!');
            loadProducts(currentPage);
        } catch (error) {
            console.error('Error deleting product:', error);
            ui.showError(error.message || 'فشل حذف المنتج');
        }
    }

    function debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    document.getElementById('search-products').addEventListener('input', debounce(() => {
        loadProducts(1);
    }, 500));

    document.getElementById('filter-category').addEventListener('change', () => {
        loadProducts(1);
    });

    document.getElementById('filter-status').addEventListener('change', () => {
        loadProducts(1);
    });

    document.getElementById('sort-by').addEventListener('change', () => {
        loadProducts(1);
    });

    loadCategories();
    loadProducts();
</script>
@endsection
