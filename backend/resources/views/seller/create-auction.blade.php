@extends('layouts.dashboard')

@section('title', 'إنشاء/تعديل المزاد | جوهرة')

@section('content')
<script>
    if (typeof api !== 'undefined' && api.getUser) {
        const user = api.getUser();
        if (user && user.account_type !== 'seller') {
            window.location.href = '{{ url("/") }}';
        }
    }
</script>

<div class="bg-secondary rounded-xl border border-color shadow-lg p-8">
    <h2 class="text-2xl font-bold mb-6 gold-text" id="page-headline">إنشاء مزاد جديد</h2>

    <form id="create-auction-form" class="space-y-6">
        <!-- Select Product -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-gem ml-2 text-yellow-600"></i>
                اختر المنتج
            </label>
            <select name="product_id" id="product-select" required class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary">
                <option value="">جاري تحميل المنتجات...</option>
            </select>
            <p class="text-sm text-secondary mt-2" id="product-helper-text">
                <i class="fas fa-info-circle ml-1"></i>
                سيتم عرض المنتجات التي ليس لها مزادات فقط
            </p>
        </div>

        <!-- Starting Price -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-dollar-sign ml-2 text-yellow-600"></i>
                سعر البداية (ر.س)
            </label>
            <input type="number" name="starting_price" id="starting_price" required min="0" step="0.01"
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="15000">
        </div>

        <!-- Reserve Price -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-shield-alt ml-2 text-yellow-600"></i>
                السعر الاحتياطي (اختياري)
            </label>
            <input type="number" name="reserve_price" id="reserve_price" min="0" step="0.01"
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="20000">
            <p class="text-sm text-secondary mt-2">
                <i class="fas fa-info-circle ml-1"></i>
                الحد الأدنى للسعر الذي ترغب في قبوله
            </p>
        </div>

        <!-- Bid Increment -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-plus ml-2 text-yellow-600"></i>
                قيمة الزيادة في المزايدة (ر.س)
            </label>
            <input type="number" name="bid_increment" id="bid_increment" required min="1" step="0.01" value="100"
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="100">
        </div>

        <!-- Start Time -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-calendar-alt ml-2 text-yellow-600"></i>
                وقت بداية المزاد
            </label>
            <input type="datetime-local" name="start_time" id="start_time" required
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary">
        </div>

        <!-- End Time -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-calendar-check ml-2 text-yellow-600"></i>
                وقت نهاية المزاد
            </label>
            <input type="datetime-local" name="end_time" id="end_time" required
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary">
        </div>

        <!-- Info Box -->
        <div class="bg-blue-500 bg-opacity-10 border border-blue-500 border-opacity-20 rounded-lg p-4">
            <h3 class="font-bold text-blue-400 mb-2">
                <i class="fas fa-lightbulb ml-2"></i>
                نصائح لمزاد ناجح
            </h3>
            <ul class="text-sm text-secondary space-y-1">
                <li>• اختر سعر بداية معقول لجذب المزايدين</li>
                <li>• حدد فترة زمنية كافية للمزاد (3-7 أيام مثالي)</li>
                <li>• تأكد من جودة صور المنتج ووصفه</li>
                <li>• السعر الاحتياطي يحميك من البيع بسعر منخفض</li>
            </ul>
        </div>

        <!-- Submit Button -->
        <div class="flex flex-col md:flex-row gap-4">
            <button type="submit" class="flex-1 gold-gradient text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition" id="submit-btn">
                <i class="fas fa-gavel ml-2"></i>
                إنشاء المزاد
            </button>
            <a href="{{ url('/seller/auctions') }}" class="flex-1 bg-tertiary text-secondary font-bold py-3 px-6 rounded-lg hover:bg-opacity-80 transition text-center border border-color">
                <i class="fas fa-times ml-2"></i>
                إلغاء
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const auctionId = urlParams.get('id');

    // Load products
    async function loadProducts(selectedProductId = null) {
        try {
            const response = await api.getMyProducts();
            let products = response.data?.data || response.data || [];
            
            // Filter products without auctions (unless it is the selected one for editing)
            const availableProducts = products.filter(p => !p.auction || p.id == selectedProductId);
            const select = document.getElementById('product-select');
            
            if (availableProducts.length === 0) {
                select.innerHTML = '<option value="">لا توجد منتجات متاحة للمزاد</option>';
                select.disabled = true;
                document.getElementById('submit-btn').disabled = true;
            } else {
                select.innerHTML = '<option value="">اختر المنتج...</option>' +
                    availableProducts.map(p => 
                        `<option value="${p.id}" ${p.id == selectedProductId ? 'selected' : ''}>${p.name} - ${p.price} ر.س</option>`
                    ).join('');
                select.disabled = false;
                document.getElementById('submit-btn').disabled = false;
            }
        } catch (error) {
            console.error('Error loading products:', error);
            ui.showError('فشل تحميل المنتجات');
        }
    }

    // Load existing auction details for Edit Mode
    async function loadExistingAuction() {
        if (!auctionId) {
            await loadProducts();
            return;
        }

        document.getElementById('page-headline').textContent = 'تعديل المزاد';
        document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save ml-2"></i> حفظ التغييرات';
        document.getElementById('product-helper-text').style.display = 'none';

        try {
            const response = await api.getAuction(auctionId);
            const auction = response.data || response;

            // Load products and pre-select current product
            await loadProducts(auction.product_id);
            // Disable selecting another product during edit to avoid confusion
            document.getElementById('product-select').disabled = true;

            document.getElementById('starting_price').value = auction.starting_price;
            document.getElementById('reserve_price').value = auction.reserve_price || '';
            document.getElementById('bid_increment').value = auction.bid_increment || 100;

            // Format dates for input (remove timezone/seconds if needed, e.g. YYYY-MM-DDThh:mm)
            if (auction.start_time) {
                document.getElementById('start_time').value = new Date(auction.start_time).toISOString().slice(0, 16);
            }
            if (auction.end_time) {
                document.getElementById('end_time').value = new Date(auction.end_time).toISOString().slice(0, 16);
            }
        } catch (error) {
            console.error('Error loading auction details:', error);
            ui.showError('فشل تحميل تفاصيل المزاد');
        }
    }

    // Set minimum datetime to now
    const now = new Date();
    now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
    const minDateTime = now.toISOString().slice(0, 16);
    document.getElementById('start_time').min = minDateTime;
    document.getElementById('end_time').min = minDateTime;

    document.getElementById('start_time').addEventListener('change', function() {
        document.getElementById('end_time').min = this.value;
    });

    // Handle form submission
    document.getElementById('create-auction-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = document.getElementById('submit-btn');
        const originalBtnText = submitButton.innerHTML;
        const formData = new FormData(this);
        
        // Format datetime for Laravel format (YYYY-MM-DD HH:MM:SS)
        const startTimeInput = formData.get('start_time');
        const endTimeInput = formData.get('end_time');
        
        const startTime = startTimeInput ? new Date(startTimeInput).toISOString().slice(0, 19).replace('T', ' ') : null;
        const endTime = endTimeInput ? new Date(endTimeInput).toISOString().slice(0, 19).replace('T', ' ') : null;
        
        const auctionData = {
            product_id: parseInt(document.getElementById('product-select').value),
            starting_price: parseFloat(formData.get('starting_price')),
            reserve_price: formData.get('reserve_price') ? parseFloat(formData.get('reserve_price')) : null,
            bid_increment: parseFloat(formData.get('bid_increment')),
            start_time: startTime,
            end_time: endTime
        };

        try {
            ui.showLoading(submitButton);
            
            if (auctionId) {
                await api.updateAuction(auctionId, auctionData);
                ui.showSuccess('تم تحديث المزاد بنجاح! ✅');
            } else {
                await api.createAuction(auctionData);
                ui.showSuccess('تم إنشاء المزاد بنجاح! ✅');
            }
            
            setTimeout(() => {
                window.location.href = '{{ url("/seller/auctions") }}';
            }, 1000);
            
        } catch (error) {
            ui.hideLoading(submitButton);
            submitButton.innerHTML = originalBtnText;
            
            if (error.errors) {
                ui.showValidationErrors(error.errors, this);
            } else {
                ui.showError(error.message || 'فشل حفظ المزاد');
            }
        }
    });

    // Initialize
    loadExistingAuction();
</script>
@endsection
