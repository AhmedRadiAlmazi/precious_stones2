/**
 * Shop Page Logic
 * Fetches products from API and handles "Request Product" (Order) functionality.
 */

document.addEventListener('DOMContentLoaded', () => {
    // If api.js is loaded, we can use it, otherwise we might need standalone logic.
    // Ideally we should include api.js in theshop.html.
    // For now, let's just make fetchProducts use a locally scoped or checked base url.
    fetchProducts();
});

// Check if API_BASE_URL is defined, if not define it.
// Use var or check window to avoid const redeclaration error if script is double loaded
if (typeof API_BASE_URL === 'undefined') {
    var API_BASE_URL = 'http://127.0.0.1:8800/api/v1';
}

async function fetchProducts() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return; // Guard clause

    // Show loading state (optional)
    productsGrid.innerHTML = '<div class="col-span-full text-center py-10"><i class="fas fa-spinner fa-spin text-3xl text-yellow-500"></i></div>';

    try {
        const response = await fetch(`${API_BASE_URL}/products?active=1`);
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
            } else {
                // Get server base URL (remove /api/v1 from API_BASE_URL)
                // API_BASE_URL is likely http://127.0.0.1:8800/api/v1
                const serverBase = API_BASE_URL.replace('/api/v1', '');
                
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
                <div class="certification-seal">
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
        alert('يجب عليك تسجيل الدخول أولاً لطلب هذا المنتج.');
        window.location.href = 'login.html'; 
        return;
    }

    if (!confirm('هل أنت متأكد من رغبتك في إرسال طلب شراء لهذا المنتج؟ سيتم إرسال الطلب للبائع.')) {
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/orders`, {
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
            alert('تم إرسال الطلب بنجاح! يمكن للبائع التواصل معك الآن.');
        } else {
            alert(data.message || 'حدث خطأ أثناء إرسال الطلب.');
        }

    } catch (error) {
        console.error('Error placing order:', error);
        alert('حدث خطأ في الاتصال بالخادم.');
    }
}

// Make requestProduct globally available for onclick
window.requestProduct = requestProduct;
