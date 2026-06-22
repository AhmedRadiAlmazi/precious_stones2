/**
 * Shop Page Logic — Luxury Redesign
 * Fetches products from API and renders luxury gem cards.
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

const apiBase = (typeof API_BASE_URL !== 'undefined') ? API_BASE_URL : '/api/v1';

// ================================================================
// GEM BADGE CONFIGS PER CATEGORY
// ================================================================
const gemBadges = {
    'ألماس':       { emoji: '💎', bg: 'rgba(80,0,100,0.85)',  border: 'rgba(200,100,255,0.3)', color: '#e9d5ff' },
    'ياقوت':      { emoji: '🔴', bg: 'rgba(155,27,48,0.85)', border: 'rgba(255,100,120,0.3)', color: '#ffb3bc' },
    'زمرد':       { emoji: '🟢', bg: 'rgba(0,78,48,0.85)',   border: 'rgba(0,200,80,0.3)',    color: '#86efac' },
    'ياقوت أزرق': { emoji: '🔵', bg: 'rgba(15,82,186,0.85)', border: 'rgba(100,150,255,0.3)', color: '#bfdbfe' },
    'عقيق':       { emoji: '🟠', bg: 'rgba(180,60,0,0.85)',  border: 'rgba(255,150,50,0.3)',  color: '#fed7aa' },
    'توباز':      { emoji: '💠', bg: 'rgba(0,90,140,0.85)',  border: 'rgba(56,189,248,0.3)',  color: '#bae6fd' },
};

function getGemBadge(name) {
    for (const [key, val] of Object.entries(gemBadges)) {
        if (name && name.includes(key)) return val;
    }
    return { emoji: '✨', bg: 'rgba(212,175,55,0.3)', border: 'rgba(212,175,55,0.4)', color: '#fef9c3' };
}

// ================================================================
// FETCH PRODUCTS
// ================================================================
async function fetchProducts() {
    const productsGrid = document.getElementById('products-grid');
    if (!productsGrid) return;

    productsGrid.innerHTML = `
        <div class="col-span-full text-center py-20" style="color:#64748b;">
            <div style="width:52px;height:52px;border:3px solid rgba(212,175,55,0.2);border-top-color:#D4AF37;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 16px;"></div>
            <p>جاري تحميل المعروضات...</p>
        </div>`;

    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');

    if (productId) {
        try {
            const response = await fetch(`${apiBase}/products/${productId}`);
            if (!response.ok) throw new Error('Failed to fetch product');
            const data = await response.json();
            const product = data.data;
            if (product) renderSingleProduct(product);
            else throw new Error('Product not found');
        } catch (error) {
            console.error('Error fetching single product:', error);
            productsGrid.innerHTML = `
                <div class="col-span-full text-center py-16" style="color:#64748b;">
                    <i class="fas fa-gem text-4xl mb-4 block" style="color:rgba(212,175,55,0.3);"></i>
                    <p class="mb-4">فشل تحميل المنتج المطلوب. ربما يكون قد تم حذفه.</p>
                    <button onclick="clearProductFilter()" style="background:linear-gradient(135deg,#D4AF37,#B8860B);color:#000;font-weight:700;padding:10px 24px;border-radius:12px;border:none;cursor:pointer;">
                        عرض جميع المنتجات
                    </button>
                </div>`;
        }
    } else {
        try {
            let url = `${apiBase}/products?active=1`;
            if (currentCategoryId) url += `&category_id=${currentCategoryId}`;

            const minPriceInput = document.getElementById('price-min');
            const maxPriceInput = document.getElementById('price-max');
            if (minPriceInput && minPriceInput.value) url += `&min_price=${minPriceInput.value}`;
            if (maxPriceInput && maxPriceInput.value) url += `&max_price=${maxPriceInput.value}`;

            const response = await fetch(url);
            if (!response.ok) throw new Error('Failed to fetch products');
            const data = await response.json();

            let products = [];
            if (Array.isArray(data)) products = data;
            else if (data.data && Array.isArray(data.data)) products = data.data;
            else if (data.data && data.data.data && Array.isArray(data.data.data)) products = data.data.data;
            else if (data.current_page && Array.isArray(data.data)) products = data.data;
            else console.warn('Unexpected product data structure:', data);

            renderProducts(products);
        } catch (error) {
            console.error('Error fetching products:', error);
            productsGrid.innerHTML = `
                <div class="col-span-full text-center py-16" style="color:#ef4444;">
                    <i class="fas fa-exclamation-triangle text-3xl mb-3 block"></i>
                    فشل تحميل المنتجات. يرجى المحاولة لاحقاً.
                </div>`;
        }
    }
}

// ================================================================
// RESOLVE IMAGE URL
// ================================================================
function resolveImageUrl(product) {
    const noImg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'%3E%3Crect fill='%230f0f1a' width='400' height='300'/%3E%3Ctext fill='%23D4AF37' font-family='sans-serif' font-size='28' dy='10.5' font-weight='bold' x='50%25' y='50%25' text-anchor='middle'%3E💎%3C/text%3E%3C/svg%3E";
    if (!product.images || !product.images.length) return noImg;

    let imgPath = product.images[0];
    if (imgPath.startsWith('http')) return imgPath;

    const serverBase = apiBase.replace('/api/v1', '');
    if (imgPath.startsWith('/imges') || imgPath.startsWith('imges')) {
        return `${serverBase}/${imgPath.replace(/^\//, '')}`;
    }
    imgPath = imgPath.replace(/^\/|storage\//g, '');
    return `${serverBase}/storage/${imgPath}`;
}

// ================================================================
// RENDER PRODUCTS — LUXURY CARDS
// ================================================================
function renderProducts(products) {
    const productsGrid = document.getElementById('products-grid');
    productsGrid.innerHTML = '';

    if (!products.length) {
        productsGrid.innerHTML = `
            <div class="col-span-full text-center py-20" style="color:#64748b;">
                <i class="fas fa-gem text-5xl mb-4 block" style="color:rgba(212,175,55,0.2);"></i>
                <p class="text-lg">لا توجد منتجات متاحة حالياً في هذه الفئة.</p>
                <button onclick="clearProductFilter()" style="margin-top:16px;background:linear-gradient(135deg,#D4AF37,#B8860B);color:#000;font-weight:700;padding:10px 24px;border-radius:12px;border:none;cursor:pointer;">
                    عرض الكل
                </button>
            </div>`;
        return;
    }

    products.forEach((product, index) => {
        const imageUrl = resolveImageUrl(product);
        const badge = getGemBadge(product.name);
        const price = parseFloat(product.price).toLocaleString('ar-SA');
        const hasCert = product.certification;
        const isFeatured = product.is_featured;
        const delay = (index % 3) * 100;

        const card = document.createElement('div');
        card.className = 'gem-card';
        card.style.transitionDelay = `${delay}ms`;

        // Safe strings for onclick attributes
        const safeName = (product.name || '').replace(/'/g, "\\'");
        const safeOrigin = (product.origin_country || product.country || 'غير محدد').replace(/'/g, "\\'");
        const certNo = `#GIA-${product.id}748${product.id}`;
        const weight = product.weight ? `${product.weight} قيراط` : '3.20 قيراط';

        card.innerHTML = `
            <div class="gem-img-box">
                <img src="${imageUrl}" alt="${product.name || 'حجر كريم'}" loading="lazy">
                <div class="gem-shine"></div>

                ${hasCert ? `
                <div class="cert-dot"
                    onclick="showCertModal('${safeName}', '${certNo}', '${weight}', 'VVS1 - نقي جداً', 'قطع وسادة ممتاز', '${safeOrigin}')"
                    title="عرض شهادة الأصالة">
                    <i class="fas fa-award"></i>
                </div>` : ''}

                <div class="gem-badge"
                    style="background:${badge.bg};border:1px solid ${badge.border};color:${badge.color};">
                    ${badge.emoji} ${getCategoryLabel(product)}
                </div>

                ${isFeatured ? `
                <div style="position:absolute;bottom:10px;left:10px;">
                    <span style="padding:3px 9px;border-radius:20px;font-size:.68rem;font-weight:700;background:linear-gradient(135deg,#D4AF37,#B8860B);color:#000;">
                        ⭐ مميز
                    </span>
                </div>` : ''}
            </div>

            <div style="padding:16px;">
                <h3 style="font-weight:900;font-size:.95rem;color:#fff;margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    ${product.name || 'حجر كريم'}
                </h3>
                <p style="font-size:.75rem;color:#64748b;margin-bottom:10px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    ${product.description || (product.seller ? 'البائع: ' + (product.seller.name || product.seller.first_name || '') : 'جودة عالية · معتمد')}
                </p>

                <div style="display:flex;align-items:center;gap:4px;margin-bottom:10px;">
                    ${renderStars(product.rating || 4.5)}
                    <span style="font-size:.7rem;color:#64748b;margin-right:4px;">(${product.reviews_count || Math.floor(Math.random()*50)+5})</span>
                </div>

                <div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:14px;">
                    <span style="font-size:1.2rem;font-weight:900;color:#D4AF37;">${price} ر.س</span>
                    ${product.old_price ? `<span style="font-size:.75rem;text-decoration:line-through;color:#334155;">${parseFloat(product.old_price).toLocaleString()}</span>` : ''}
                </div>

                <div style="display:flex;gap:8px;">
                    <button class="add-btn" onclick="handleAddToCart(this, ${product.id}, '${safeName}', ${parseFloat(product.price)}, '${imageUrl}')">
                        <i class="fas fa-shopping-cart" style="margin-left:4px;"></i> أضف للسلة
                    </button>
                    <button onclick="requestProduct(${product.id})"
                        style="padding:10px 12px;border-radius:12px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);color:#94a3b8;cursor:pointer;transition:all .25s;"
                        onmouseover="this.style.background='rgba(212,175,55,0.1)';this.style.color='#D4AF37';"
                        onmouseout="this.style.background='rgba(255,255,255,0.04)';this.style.color='#94a3b8';"
                        title="طلب المنتج من البائع">
                        <i class="fas fa-paper-plane text-sm"></i>
                    </button>
                </div>
            </div>`;

        productsGrid.appendChild(card);

        // Trigger reveal animation
        setTimeout(() => { card.classList.add('visible'); }, 80 + delay);
    });
}

// ================================================================
// HELPERS
// ================================================================
function getCategoryLabel(product) {
    if (product.category && product.category.name) return product.category.name;
    if (product.name) {
        if (product.name.includes('ألماس'))  return 'ألماس';
        if (product.name.includes('ياقوت أزرق')) return 'ياقوت أزرق';
        if (product.name.includes('ياقوت'))  return 'ياقوت';
        if (product.name.includes('زمرد'))   return 'زمرد';
        if (product.name.includes('عقيق'))   return 'عقيق';
        if (product.name.includes('توباز'))  return 'توباز';
    }
    return 'حجر كريم';
}

function renderStars(rating) {
    const full  = Math.floor(rating);
    const half  = rating % 1 >= 0.5 ? 1 : 0;
    const empty = 5 - full - half;
    let html = '';
    for (let i = 0; i < full;  i++) html += '<i class="fas fa-star" style="color:#facc15;font-size:.7rem;"></i>';
    if (half)                       html += '<i class="fas fa-star-half-alt" style="color:#facc15;font-size:.7rem;"></i>';
    for (let i = 0; i < empty; i++) html += '<i class="far fa-star" style="color:#facc15;font-size:.7rem;"></i>';
    return html;
}

// ================================================================
// ADD TO CART (connects to the cart UI in shop.blade)
// ================================================================
window.handleAddToCart = function(btn, id, name, price, image) {
    // Use the cart system defined in shop.blade.php
    if (typeof window.addToCartUI === 'function') {
        window.addToCartUI(name, price, image);
    }

    // Visual feedback on button
    const original = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check" style="margin-left:4px;"></i> تمت الإضافة!';
    btn.style.background = 'linear-gradient(135deg,#22c55e,#16a34a)';
    btn.disabled = true;
    setTimeout(() => {
        btn.innerHTML = original;
        btn.style.background = '';
        btn.disabled = false;
    }, 1600);
};

// ================================================================
// REQUEST PRODUCT (Order API)
// ================================================================
async function requestProduct(productId) {
    const token = localStorage.getItem('jawharah_auth_token');

    if (!token) {
        if (typeof window.showGemToast === 'function') {
            window.showGemToast('🔒 يجب تسجيل الدخول أولاً لطلب هذا المنتج.');
        }
        setTimeout(() => { window.location.href = '/login'; }, 1800);
        return;
    }

    const confirmed = await ui.confirm('هل أنت متأكد من رغبتك في إرسال طلب شراء لهذا المنتج؟', 'تأكيد الشراء');
    if (!confirmed) return;

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
                payment_method: 'bank_transfer'
            })
        });

        const data = await response.json();

        if (response.ok) {
            if (typeof window.showGemToast === 'function') {
                window.showGemToast('✅ تم إرسال الطلب بنجاح! سيتواصل معك البائع قريباً.');
            } else {
                await ui.alert('تم إرسال الطلب بنجاح!', 'نجاح');
            }
        } else {
            await ui.alert(data.message || 'حدث خطأ أثناء إرسال الطلب.', 'خطأ');
        }
    } catch (error) {
        console.error('Error placing order:', error);
        await ui.alert('حدث خطأ في الاتصال بالخادم.', 'خطأ الاتصال');
    }
}
window.requestProduct = requestProduct;

// ================================================================
// RENDER SINGLE PRODUCT
// ================================================================
function renderSingleProduct(product) {
    const productsGrid = document.getElementById('products-grid');
    productsGrid.innerHTML = '';

    const imageUrl = resolveImageUrl(product);
    const badge = getGemBadge(product.name);
    const price = parseFloat(product.price).toLocaleString('ar-SA');
    const safeName = (product.name || '').replace(/'/g, "\\'");
    const safeOrigin = (product.origin_country || product.country || 'غير محدد').replace(/'/g, "\\'");
    const certNo = `#GIA-${product.id}748${product.id}`;
    const weight = product.weight ? `${product.weight} قيراط` : '3.20 قيراط';

    const card = document.createElement('div');
    card.className = 'col-span-full';
    card.innerHTML = `
        <div style="background:rgba(212,175,55,0.06);border:1px solid rgba(212,175,55,0.25);border-radius:14px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <span style="color:#D4AF37;font-size:.875rem;"><i class="fas fa-info-circle ml-2"></i>أنت تستعرض تفاصيل منتج واحد فقط حالياً.</span>
            <button onclick="clearProductFilter()" style="background:linear-gradient(135deg,#D4AF37,#B8860B);color:#000;font-size:.8rem;font-weight:700;padding:8px 18px;border-radius:10px;border:none;cursor:pointer;">
                عرض جميع المنتجات
            </button>
        </div>

        <div style="background:linear-gradient(135deg,#0f0f1a,#14141f);border:1px solid rgba(212,175,55,0.2);border-radius:22px;overflow:hidden;max-width:700px;margin:0 auto;">
            <div style="position:relative;height:320px;overflow:hidden;">
                <img src="${imageUrl}" alt="${product.name}" style="width:100%;height:100%;object-fit:cover;">
                <div style="position:absolute;inset:0;background:linear-gradient(180deg,transparent 50%,rgba(8,8,16,0.95));"></div>
                ${product.certification ? `
                <div class="cert-dot" style="top:14px;left:14px;"
                    onclick="showCertModal('${safeName}','${certNo}','${weight}','VVS1 - نقي جداً','قطع وسادة ممتاز','${safeOrigin}')">
                    <i class="fas fa-award"></i>
                </div>` : ''}
                <div class="gem-badge" style="background:${badge.bg};border:1px solid ${badge.border};color:${badge.color};top:14px;right:14px;">
                    ${badge.emoji} ${getCategoryLabel(product)}
                </div>
            </div>
            <div style="padding:24px;">
                <h3 style="font-size:1.5rem;font-weight:900;color:#fff;margin-bottom:8px;">${product.name}</h3>
                <p style="color:#64748b;font-size:.9rem;line-height:1.7;margin-bottom:16px;">${product.description || 'لا يوجد وصف متاح.'}</p>
                <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;font-size:.75rem;">
                    <span style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);padding:5px 12px;border-radius:10px;color:#94a3b8;">
                        📍 المنشأ: ${product.origin_country || product.country || 'غير محدد'}
                    </span>
                    <span style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);padding:5px 12px;border-radius:10px;color:#94a3b8;">
                        ⚖️ الوزن: ${product.weight || '—'} قيراط
                    </span>
                    ${product.seller ? `<span style="background:rgba(212,175,55,0.08);border:1px solid rgba(212,175,55,0.2);padding:5px 12px;border-radius:10px;color:#D4AF37;">
                        👤 البائع: ${product.seller.name || (product.seller.first_name + ' ' + product.seller.last_name)}
                    </span>` : ''}
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
                    <span style="font-size:2rem;font-weight:900;color:#D4AF37;">${price} ر.س</span>
                    <span style="font-size:.8rem;color:#475569;">المخزون: ${product.stock || 1}</span>
                </div>
                <div style="display:flex;gap:10px;">
                    <button class="add-btn" style="flex:1;" onclick="handleAddToCart(this, ${product.id}, '${safeName}', ${parseFloat(product.price)}, '${imageUrl}')">
                        <i class="fas fa-shopping-cart" style="margin-left:6px;"></i> أضف للسلة
                    </button>
                    <button onclick="requestProduct(${product.id})"
                        style="padding:10px 20px;border-radius:12px;font-weight:700;font-size:.85rem;background:rgba(212,175,55,0.1);border:1px solid rgba(212,175,55,0.25);color:#D4AF37;cursor:pointer;transition:all .25s;font-family:Cairo,sans-serif;"
                        onmouseover="this.style.background='rgba(212,175,55,0.2)'" onmouseout="this.style.background='rgba(212,175,55,0.1)'">
                        <i class="fas fa-paper-plane ml-1"></i> طلب
                    </button>
                </div>
            </div>
        </div>`;

    productsGrid.appendChild(card);
}

// ================================================================
// CLEAR PRODUCT FILTER
// ================================================================
function clearProductFilter() {
    const url = new URL(window.location);
    url.searchParams.delete('id');
    window.history.pushState({}, '', url);
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.className = btn.className.replace('active', '').trim();
    });
    // Reset cat pill bar
    document.querySelectorAll('#cat-pills-bar .cat-pill').forEach(btn => {
        btn.classList.remove('active');
        if (!btn.getAttribute('data-category-id')) btn.classList.add('active');
    });
    currentCategoryId = '';
    fetchProducts();
}

// ================================================================
// CATEGORY FILTERS
// ================================================================
function setupCategoryFilters() {
    // Legacy .category-btn (in the cat bar)
    document.querySelectorAll('.category-btn').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('.category-btn').forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            currentCategoryId = button.getAttribute('data-category-id') || '';
            fetchProducts();
        });
    });

    // New cat-pill bar in shop.blade
    document.querySelectorAll('#cat-pills-bar .cat-pill').forEach(button => {
        button.addEventListener('click', () => {
            document.querySelectorAll('#cat-pills-bar .cat-pill').forEach(b => b.classList.remove('active'));
            button.classList.add('active');
            currentCategoryId = button.getAttribute('data-category-id') || '';
            fetchProducts();
        });
    });
}

// ================================================================
// SIDEBAR FILTERS
// ================================================================
function setupSidebarFilters() {
    const applyBtn = document.getElementById('apply-filters-btn');
    if (applyBtn) applyBtn.addEventListener('click', () => fetchProducts());
}

// ================================================================
// EXPORTS
// ================================================================
window.clearProductFilter  = clearProductFilter;
window.renderSingleProduct = renderSingleProduct;
window.fetchProducts       = fetchProducts;
