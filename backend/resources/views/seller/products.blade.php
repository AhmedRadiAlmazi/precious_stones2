@extends('layouts.dashboard')

@section('title', 'منتجاتي | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<!-- Header Actions -->
<div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
    <div class="flex flex-col md:flex-row gap-4 items-center w-full md:w-auto">
        <input type="text" id="search-products" placeholder="البحث في المنتجات..." 
            class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-64">
        <select id="filter-category" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الفئات</option>
        </select>
        <select id="filter-status" class="bg-tertiary border border-color rounded-lg py-2 px-4 focus:outline-none focus:border-gold text-primary w-full md:w-auto">
            <option value="">جميع الحالات</option>
            <option value="1">نشط</option>
            <option value="0">غير نشط</option>
        </select>
    </div>
    <a href="{{ url('/seller/add-product') }}" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-6 py-3 rounded-lg hover:shadow-lg transition w-full md:w-auto text-center">
        <i class="fas fa-plus ml-2"></i>
        إضافة منتج جديد
    </a>
</div>

<!-- Products Grid -->
<div id="products-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div class="text-center py-12 text-secondary col-span-full">
        <i class="fas fa-spinner fa-spin text-4xl mb-4"></i>
        <p>جاري تحميل المنتجات...</p>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let categories = [];

    // Load categories for filter
    async function loadCategories() {
        try {
            // Fetch categories - fallback to empty if endpoint fails
            const response = await api.request('/categories').catch(() => api.request('/admin/categories').catch(() => ({data: []})));
            categories = response.data || response || [];
            const select = document.getElementById('filter-category');
            
            categories.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.id;
                option.textContent = cat.name;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load seller's products
    async function loadProducts() {
        try {
            const response = await api.getMyProducts();
            const products = response.data?.data || response.data || [];
            window.allProducts = products;
            displayProducts(products);
        } catch (error) {
            console.error('Error loading products:', error);
            ui.showError('فشل تحميل المنتجات');
        }
    }

    function displayProducts(products) {
        const container = document.getElementById('products-grid');
        
        if (products.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary col-span-full">
                    <i class="fas fa-box-open text-6xl mb-4"></i>
                    <p class="text-xl mb-4">لا توجد منتجات</p>
                    <a href="{{ url('/seller/add-product') }}" class="gold-gradient text-white px-6 py-3 rounded-lg inline-block hover:shadow-lg transition">
                        <i class="fas fa-plus ml-2"></i>
                        إضافة منتج جديد
                    </a>
                </div>
            `;
            return;
        }

        // Filter products
        const searchValue = document.getElementById('search-products').value.toLowerCase();
        const categoryValue = document.getElementById('filter-category').value;
        const statusValue = document.getElementById('filter-status').value;

        let filteredProducts = products.filter(product => {
            const matchSearch = !searchValue || product.name.toLowerCase().includes(searchValue);
            const matchCategory = !categoryValue || product.category_id == categoryValue;
            const matchStatus = statusValue === '' || product.is_active == statusValue;
            return matchSearch && matchCategory && matchStatus;
        });

        if (filteredProducts.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-secondary col-span-full">
                    <i class="fas fa-search text-6xl mb-4"></i>
                    <p class="text-xl">لا توجد نتائج</p>
                </div>
            `;
            return;
        }

        container.innerHTML = filteredProducts.map(product => `
            <div class="bg-secondary border border-color rounded-xl overflow-hidden hover:shadow-lg transition">
                <div class="relative">
                    <img src="${product.images && product.images[0] || 'https://via.placeholder.com/400x300'}" 
                        alt="${product.name}" 
                        class="w-full h-48 object-cover">
                    <span class="absolute top-3 left-3 px-3 py-1 rounded-full text-xs font-semibold ${
                        product.is_active 
                            ? 'bg-green-500 bg-opacity-90 text-white' 
                            : 'bg-red-500 bg-opacity-90 text-white'
                    }">
                        ${product.is_active ? 'نشط' : 'غير نشط'}
                    </span>
                </div>
                <div class="p-4">
                    <h3 class="text-lg font-bold text-primary mb-2">${product.name}</h3>
                    <p class="text-secondary text-sm mb-3 line-clamp-2">${product.description || 'لا يوجد وصف'}</p>
                    
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs text-secondary">السعر</p>
                            <p class="text-xl font-bold text-green-500">${product.price} ر.س</p>
                        </div>
                        <div>
                            <p class="text-xs text-secondary">المخزون</p>
                            <p class="text-lg font-bold ${product.stock > 10 ? 'text-green-500' : product.stock > 0 ? 'text-yellow-500' : 'text-red-500'}">
                                ${product.stock}
                            </p>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-3 border-t border-color">
                        <button onclick="toggleProductStatus(${product.id})" 
                            class="flex-1 text-sm px-3 py-2 rounded ${product.is_active ? 'bg-red-500 text-red-500' : 'bg-green-500 text-green-500'} bg-opacity-20 hover:bg-opacity-30 transition"
                            title="${product.is_active ? 'إلغاء التفعيل' : 'تفعيل'}">
                            <i class="fas fa-${product.is_active ? 'eye-slash' : 'eye'}"></i>
                        </button>
                        <a href="{{ url('/seller/add-product') }}?id=${product.id}" 
                            class="flex-1 text-sm px-3 py-2 rounded bg-blue-500 bg-opacity-20 hover:bg-opacity-30 text-blue-500 transition text-center"
                            title="تعديل">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteProduct(${product.id})" 
                            class="flex-1 text-sm px-3 py-2 rounded bg-red-500 bg-opacity-20 hover:bg-opacity-30 text-red-500 transition"
                            title="حذف">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    async function toggleProductStatus(id) {
        try {
            await api.request(`/products/${id}/toggle-status`, { method: 'POST' });
            ui.showSuccess('تم تغيير حالة المنتج بنجاح');
            loadProducts();
        } catch (error) {
            ui.showError(error.message || 'فشل تغيير حالة المنتج');
        }
    }

    async function deleteProduct(id) {
        if (!confirm('هل أنت متأكد من حذف هذا المنتج؟')) return;
        try {
            await api.deleteProduct(id);
            ui.showSuccess('تم حذف المنتج بنجاح');
            loadProducts();
        } catch (error) {
            ui.showError(error.message || 'فشل حذف المنتج');
        }
    }

    // Set up search and filter listeners
    document.getElementById('search-products').addEventListener('input', () => {
        if (window.allProducts) displayProducts(window.allProducts);
    });
    document.getElementById('filter-category').addEventListener('change', () => {
        if (window.allProducts) displayProducts(window.allProducts);
    });
    document.getElementById('filter-status').addEventListener('change', () => {
        if (window.allProducts) displayProducts(window.allProducts);
    });

    // Initialize Page
    async function initPage() {
        await loadCategories();
        await loadProducts();
    }

    initPage();
</script>
@endsection
