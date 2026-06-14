/**
 * Shop Page Logic
 * Fetches products from API and handles "Request Product" (Order) functionality.
 */

let currentCategoryId = '';

function initShop() {
    fetchProducts();
    setupCategoryFilters();
    setupSidebarFilters();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShop);
} else {
    initShop();
}

// Use a local variable to reference the global API_BASE_URL or fallback
const apiBase = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : '/api/v1';

async function fetchProducts() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return; // Guard clause

    // Show loading state
    productsGrid.innerHTML = '<div class="col-span-full text-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-yellow-500"></i></div>';

    // Parse URL params for specific product ID
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (productId) {
        try {
            const response = await fetch(`${apiBase}/products/${productId}`);
            if (!response.ok) throw new Error('Failed to fetch product');

            const data = await response.json();
            const product = data.data;

            if (product) {
                renderSingleProduct(product);
            } else {
                throw new Error('Product not found');
            }
        } catch (error) {
            console.error('Error fetching single product:', error);
            productsGrid.innerHTML = `
                <div class="col-span-full text-center text-red-500 py-10">
                    <p class="mb-4">فشل تحميل المنتج المطلوب. ربما يكون قد تم حذفه أو لم يعد متوفراً.</p>
                    <button onclick="clearProductFilter()" class="gold-gradient text-white font-bold py-2.5 px-6 rounded-xl transition">
                        عرض جميع المنتجات
                    </button>
                </div>
            `;
        }
    } else {
        try {
            let url = `${apiBase}/products?active=1`;
            if (currentCategoryId) {
                url += `&category_id=${currentCategoryId}`;
            }
            const minPriceInput = document.getElementById('price-min');
            const maxPriceInput = document.getElementById('price-max');
            if (minPriceInput && minPriceInput.value) {
                url += `&min_price=${minPriceInput.value}`;
            }
            if (maxPriceInput && maxPriceInput.value) {
                url += `&max_price=${maxPriceInput.value}`;
            }
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch products');

            const data = await response.json();
            let products = [];
            
            // multiple checks for various response structures
            if (Array.isArray(data)) {
                // Case 1: Direct array [P1, P2]
                products = data;
            } else if (data.data && Array.isArray(data.data)) {
                 // Case 2: { data: [P1, P2] } (Simple wrapper or just accessing the paginated array directly if logic allows)
                products = data.data;
            } else if (data.data && data.data.data && Array.isArray(data.data.data)) {
                // Case 3: { success: true, data: { data: [P1, P2], current_page: 1... } } (Full Pagination in Wrapper)
                products = data.data.data;
            } else if (data.current_page && Array.isArray(data.data)) {
                 // Case 4: { data: [P1, P2], current_page: 1... } (Direct Pagination)
                products = data.data;
            } else {
                 console.warn('Unexpected product data structure:', data);
            }

            renderProducts(products);
        } catch (error) {
            console.error('Error fetching products:', error);
            productsGrid.innerHTML = '<div class="col-span-full text-center text-red-500 py-10">فشل تحميل المنتجات. يرجى المحاولة لاحقاً.</div>';
        }
    }
}

function renderProducts(products) {
    const productsGrid = document.getElementById('products-grid');
    productsGrid.innerHTML = '';

    if (products.length === 0) {
        productsGrid.innerHTML = '<div class="col-span-full text-center text-secondary py-10">لا توجد منتجات متاحة حالياً.</div>';
        return;
    }

    const noImagePlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%23e5e7eb' width='400' height='300'/%3E%3Ctext fill='%239ca3af' font-family='sans-serif' font-size='30' dy='10.5' font-weight='bold' x='50%25' y='50%25' text-anchor='middle'%3ENo Image%3C/text%3E%3C/svg%3E";

    products.forEach(product => {
        // Fix image path: Use Server URL instead of local path
        let imageUrl = noImagePlaceholder;
        
        if (product.images && product.images.length > 0) {
            let imgPath = product.images[0];
            if (imgPath.startsWith('http')) {
                imageUrl = imgPath;
            } else if (imgPath.startsWith('/imges') || imgPath.startsWith('imges')) {
                const serverBase = apiBase.replace('/api/v1', '');
                imageUrl = `${serverBase}/${imgPath.replace(/^\//, '')}`;
            } else {
                // Get server base URL (remove /api/v1 from API_BASE_URL)
                // API_BASE_URL is likely http://127.0.0.1:8800/api/v1
                const serverBase = apiBase.replace('/api/v1', '');
                
                // Remove leading slash or 'storage/' to normalize
                imgPath = imgPath.replace(/^\/|storage\//g, ''); 
                
                // Construct absolute URL
                imageUrl = `${serverBase}/storage/${imgPath}`;
            }
        }

        const card = document.createElement('div');
        card.className = 'bg-secondary rounded-xl overflow-hidden card-hover product-card border border-color';
        
        card.innerHTML = `
            <div class="relative overflow-hidden">
                <div class="h-48 overflow-hidden">
                    <img src="${imageUrl}" alt="${product.name}" class="w-full h-full object-cover product-image">
                </div>
                ${product.is_featured ? `
                <div class="absolute top-4 left-4 bg-yellow-500 text-black text-xs font-bold py-1 px-2 rounded">
                    مميز
                </div>` : ''}
                <div class="absolute top-4 right-4">
                    <button class="bg-black bg-opacity-50 text-white p-2 rounded-full hover:bg-opacity-70 transition">
                        <i class="far fa-heart"></i>
                    </button>
                </div>
                ${product.certification ? `
                <div class="certification-seal cursor-pointer hover:scale-105 transition" onclick="showCertModal('${product.name.replace(/'/g, "\\'")}', '#GIA-${product.id}748${product.id}', '${product.weight || '3.20'} قيراط', 'VVS1 - نقي جداً', 'قطع وسادة ممتاز', '${product.origin_country || 'كولومبيا'}')" title="عرض شهادة الحجر">
                    <i class="fas fa-award text-white text-sm"></i>
                </div>` : ''}
            </div>
            <div class="p-4">
                <h3 class="text-lg font-bold mb-2 text-primary">${product.name}</h3>
                <p class="text-secondary text-sm mb-4 line-clamp-2">${product.description || ''}</p>
                <div class="flex justify-between items-center mb-4">
                    <span class="text-yellow-500 font-bold text-xl">${parseFloat(product.price).toLocaleString()} ر.س</span>
                     <div class="text-xs text-secondary">
                        البائع: ${product.seller ? product.seller.name : 'غير محدد'}
                    </div>
                </div>
                <div class="flex space-x-2 rtl:space-x-reverse">
                    <button onclick="requestProduct(${product.id})" class="flex-1 gold-gradient text-white font-bold py-2 px-4 rounded-lg ripple text-sm transition hover:shadow-lg transform hover:-translate-y-1">
                        <i class="fas fa-shopping-cart ml-1"></i>
                        طلب المنتج
                    </button>
                    <button class="bg-tertiary hover:bg-opacity-80 text-primary p-2 rounded-lg transition border border-color">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
        `;
        productsGrid.appendChild(card);
    });
}

async function requestProduct(productId) {
    // Check for auth token using the consistent key
    const token = localStorage.getItem('jawharah_auth_token');
    
    if (!token) {
        // Using alert for simplicity, ideally use a custom modal or toast
        await ui.alert('يجب عليك تسجيل الدخول أولاً لطلب هذا المنتج.', 'تنبيه تسجيل الدخول');
        window.location.href = '/login'; 
        return;
    }

    const isConfirmed = await ui.confirm('هل أنت متأكد من رغبتك في إرسال طلب شراء لهذا المنتج؟ سيتم إرسال الطلب للبائع.', 'تأكيد الشراء المباشر');
    if (!isConfirmed) {
        return;
    }

    try {
        const response = await fetch(`${apiBase}/orders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                product_id: productId,
                notes: 'طلب شراء فوري من المتجر',
                payment_method: 'bank_transfer' // Default payment method to satisfy DB constraint
            })
        });

        const data = await response.json();

        if (response.ok) {
            await ui.alert('تم إرسال الطلب بنجاح! يمكن للبائع التواصل معك الآن.', 'نجاح العملية');
        } else {
            await ui.alert(data.message || 'حدث خطأ أثناء إرسال الطلب.', 'خطأ');
        }

    } catch (error) {
        console.error('Error placing order:', error);
        await ui.alert('حدث خطأ في الاتصال بالخادم.', 'خطأ الاتصال');
    }
}

// Make requestProduct globally available for onclick
window.requestProduct = requestProduct;

function renderSingleProduct(product) {
    const productsGrid = document.getElementById('products-grid');
    productsGrid.innerHTML = '';

    const noImagePlaceholder = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%23e5e7eb' width='400' height='300'/%3E%3Ctext fill='%239ca3af' font-family='sans-serif' font-size='30' dy='10.5' font-weight='bold' x='50%25' y='50%25' text-anchor='middle'%3ENo Image%3C/text%3E%3C/svg%3E";

    let imageUrl = noImagePlaceholder;
    if (product.images && product.images.length > 0) {
        let imgPath = product.images[0];
        if (imgPath.startsWith('http')) {
            imageUrl = imgPath;
        } else if (imgPath.startsWith('/imges') || imgPath.startsWith('imges')) {
            const serverBase = apiBase.replace('/api/v1', '');
            imageUrl = `${serverBase}/${imgPath.replace(/^\//, '')}`;
        } else {
            const serverBase = apiBase.replace('/api/v1', '');
            imgPath = imgPath.replace(/^\/|storage\//g, ''); 
            imageUrl = `${serverBase}/storage/${imgPath}`;
        }
    }

    const card = document.createElement('div');
    card.className = 'col-span-full max-w-2xl mx-auto bg-secondary border border-color rounded-2xl overflow-hidden shadow-2xl p-6';

    card.innerHTML = `
        <div class="bg-yellow-500 bg-opacity-10 border border-yellow-500 text-yellow-500 rounded-xl p-4 mb-6 text-center text-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="font-medium"><i class="fas fa-info-circle ml-2"></i>أنت تستعرض تفاصيل منتج واحد فقط حالياً.</span>
            <button onclick="clearProductFilter()" class="bg-yellow-500 hover:bg-yellow-600 text-black text-xs font-bold py-2 px-4 rounded-lg transition duration-200">
                عرض جميع المنتجات
            </button>
        </div>

        <div class="flex flex-col md:flex-row gap-6">
            <div class="w-full md:w-1/2 h-64 overflow-hidden rounded-xl bg-black relative flex items-center justify-center">
                <img src="${imageUrl}" alt="${product.name}" class="w-full h-full object-cover">
                ${product.certification ? `
                <div class="certification-seal cursor-pointer hover:scale-105 transition" onclick="showCertModal('${product.name.replace(/'/g, "\\'")}', '#GIA-${product.id}748${product.id}', '${product.weight || '3.20'} قيراط', 'VVS1 - نقي جداً', 'قطع وسادة ممتاز', '${product.origin_country || 'كولومبيا'}')" title="عرض شهادة الحجر">
                    <i class="fas fa-award text-white text-sm"></i>
                </div>` : ''}
            </div>
            
            <div class="w-full md:w-1/2 flex flex-col justify-between">
                <div>
                    <h3 class="text-2xl font-bold mb-3 text-primary">${product.name}</h3>
                    <p class="text-secondary text-sm mb-4 leading-relaxed">${product.description || 'لا يوجد وصف متاح.'}</p>
                    
                    <div class="flex flex-wrap gap-2 text-xs mb-4">
                        <span class="bg-tertiary text-secondary py-1.5 px-3 rounded-lg border border-color">المنشأ: ${product.country || 'غير محدد'}</span>
                        <span class="bg-tertiary text-gold py-1.5 px-3 rounded-lg border border-color font-semibold">البائع: ${product.seller ? product.seller.first_name + ' ' + product.seller.last_name : 'غير محدد'}</span>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-6">
                        <span class="text-yellow-500 font-bold text-2xl">${parseFloat(product.price).toLocaleString()} ر.س</span>
                        <span class="text-xs text-secondary">المخزون: ${product.stock || 1}</span>
                    </div>

                    <div class="flex gap-2">
                        <button onclick="requestProduct(${product.id})" class="flex-1 gold-gradient text-white font-bold py-3 px-6 rounded-xl ripple text-sm transition duration-200 transform hover:-translate-y-0.5">
                            <i class="fas fa-shopping-cart ml-2"></i>طلب المنتج
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    productsGrid.appendChild(card);
}

function clearProductFilter() {
    const url = new URL(window.location);
    url.searchParams.delete('id');
    window.history.pushState({}, '', url);
    // Reset active category button styling
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(btn => {
        const catId = btn.getAttribute('data-category-id');
        if (catId === '') {
            btn.className = 'category-btn whitespace-nowrap gold-gradient text-white px-4 py-2 rounded-full text-sm font-bold';
        } else {
            btn.className = 'category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color';
        }
    });
    currentCategoryId = '';
    fetchProducts();
}

function setupCategoryFilters() {
    const categoryButtons = document.querySelectorAll('.category-btn');
    categoryButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Set active class
            categoryButtons.forEach(btn => {
                btn.className = 'category-btn whitespace-nowrap bg-tertiary hover:bg-opacity-80 text-primary px-4 py-2 rounded-full text-sm transition border border-color';
            });
            button.className = 'category-btn whitespace-nowrap gold-gradient text-white px-4 py-2 rounded-full text-sm font-bold';
            
            // Set current category ID and fetch products
            currentCategoryId = button.getAttribute('data-category-id');
            fetchProducts();
        });
    });
}

function setupSidebarFilters() {
    const applyBtn = document.getElementById('apply-filters-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            fetchProducts();
        });
    }
}

window.clearProductFilter = clearProductFilter;
window.renderSingleProduct = renderSingleProduct;
