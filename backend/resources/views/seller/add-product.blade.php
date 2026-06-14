@extends('layouts.dashboard')

@section('title', 'إضافة/تعديل منتج | جوهرة')

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
    <h2 class="text-2xl font-bold mb-6 gold-text" id="page-headline">إضافة منتج جديد</h2>
    
    <form id="add-product-form" class="space-y-6">
        <!-- Category -->
        <div>
             <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-tag ml-2 text-yellow-600"></i>
                التصنيف
             </label>
             <select name="category_id" id="category_id" required class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary">
                <option value="">اختر التصنيف...</option>
             </select>
        </div>

        <!-- Product Name -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-gem ml-2 text-yellow-600"></i>
                اسم المنتج
            </label>
            <input type="text" name="name" id="name" required 
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="مثال: ياقوت أزرق نادر من سريلانكا">
        </div>

        <!-- Description -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-align-right ml-2 text-yellow-600"></i>
                الوصف
            </label>
            <textarea name="description" id="description" required rows="4"
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="وصف تفصيلي للمنتج..."></textarea>
        </div>

        <!-- Price and Stock -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold mb-2 text-primary">
                    <i class="fas fa-dollar-sign ml-2 text-yellow-600"></i>
                    السعر (ر.س)
                </label>
                <input type="number" name="price" id="price" required min="0" step="0.01"
                    class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                    placeholder="18500">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-primary">
                    <i class="fas fa-boxes ml-2 text-yellow-600"></i>
                    الكمية المتوفرة
                </label>
                <input type="number" name="stock" id="stock" required min="0"
                    class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                    placeholder="1">
            </div>
        </div>

        <!-- Weight and Origin -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-bold mb-2 text-primary">
                    <i class="fas fa-weight ml-2 text-yellow-600"></i>
                    الوزن (قيراط) - اختياري
                </label>
                <input type="number" name="weight" id="weight" min="0" step="0.01"
                    class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                    placeholder="5.2">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2 text-primary">
                    <i class="fas fa-globe ml-2 text-yellow-600"></i>
                    بلد المنشأ - اختياري
                </label>
                <input type="text" name="origin_country" id="origin_country"
                    class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                    placeholder="سريلانكا">
            </div>
        </div>

        <!-- Certification -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-certificate ml-2 text-yellow-600"></i>
                الشهادة - اختياري
            </label>
            <input type="text" name="certification" id="certification"
                class="w-full bg-tertiary border border-color rounded-lg py-3 px-4 focus:outline-none focus:border-yellow-500 text-primary"
                placeholder="GIA, IGI, etc.">
        </div>

        <!-- Images Upload -->
        <div>
            <label class="block text-sm font-bold mb-2 text-primary">
                <i class="fas fa-images ml-2 text-yellow-600"></i>
                صور المنتج
            </label>
            
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-color border-dashed rounded-lg bg-tertiary hover:bg-opacity-80 transition cursor-pointer" id="drop-zone">
                <div class="space-y-1 text-center">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                    <div class="flex text-sm text-secondary justify-center">
                        <label for="file-upload" class="relative cursor-pointer bg-secondary rounded-md font-medium text-yellow-600 hover:text-yellow-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-yellow-500">
                            <span class="px-2">اختر صوراً</span>
                            <input id="file-upload" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                        </label>
                        <p class="pl-1">أو اسحبها هنا</p>
                    </div>
                    <p class="text-xs text-secondary">
                        PNG, JPG, GIF حتى 5MB
                    </p>
                </div>
            </div>

            <!-- Image Preview Container -->
            <div id="image-preview-container" class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4 hidden">
                <!-- Previews will be added here -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex gap-4">
            <button type="submit" class="flex-1 gold-gradient text-white font-bold py-3 px-6 rounded-lg hover:shadow-lg transition" id="submit-btn">
                <i class="fas fa-plus ml-2"></i>
                إضافة المنتج
            </button>
            <a href="{{ url('/seller/products') }}" class="flex-1 bg-tertiary text-secondary font-bold py-3 px-6 rounded-lg hover:bg-opacity-80 transition text-center border border-color">
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
    const productId = urlParams.get('id');

    // File Upload Handling
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('file-upload');
    const previewContainer = document.getElementById('image-preview-container');
    let selectedFiles = [];

    // Handle Drag & Drop
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-yellow-500');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-yellow-500');
        }, false);
    });

    dropZone.addEventListener('drop', e => {
        const dt = e.dataTransfer;
        handleFiles(dt.files);
    }, false);

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            previewContainer.classList.remove('hidden');
        }
        
        ([...files]).forEach(file => {
            if (!file.type.match('image.*')) return;
            selectedFiles.push(file);
            previewFile(file);
        });
    }

    function previewFile(file) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onloadend = function() {
            const div = document.createElement('div');
            div.className = 'relative group h-24 rounded-lg overflow-hidden border border-color';
            
            div.innerHTML = `
                <img src="${reader.result}" class="w-full h-full object-cover">
                <button type="button" class="absolute top-1 left-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition w-6 h-6 flex items-center justify-center text-xs" onclick="removeFile(this, '${file.name}')">
                    <i class="fas fa-times"></i>
                </button>
                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate text-center">
                    ${file.name}
                </div>
            `;
            
            previewContainer.appendChild(div);
        }
    }

    window.removeFile = function(btn, fileName) {
        selectedFiles = selectedFiles.filter(f => f.name !== fileName);
        btn.closest('div').remove();
        if (selectedFiles.length === 0) {
            previewContainer.classList.add('hidden');
            fileInput.value = '';
        }
    }

    // Load categories
    async function loadCategories() {
        try {
            const response = await api.request('/categories').catch(() => api.request('/admin/categories').catch(() => ({data: []})));
            const categories = response.data || response || [];
            const select = document.getElementById('category_id');
            
            select.innerHTML = '<option value="">اختر التصنيف...</option>' + 
                categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('');
        } catch (error) {
            console.error('Error loading categories:', error);
        }
    }

    // Load existing product if in Edit Mode
    async function loadExistingProduct() {
        if (!productId) return;

        document.getElementById('page-headline').textContent = 'تعديل المنتج';
        const submitBtn = document.getElementById('submit-btn');
        submitBtn.innerHTML = '<i class="fas fa-save ml-2"></i> حفظ التغييرات';

        try {
            const response = await api.getProduct(productId);
            const product = response.data || response;

            document.getElementById('category_id').value = product.category_id;
            document.getElementById('name').value = product.name;
            document.getElementById('description').value = product.description;
            document.getElementById('price').value = product.price;
            document.getElementById('stock').value = product.stock;
            document.getElementById('weight').value = product.weight || '';
            document.getElementById('origin_country').value = product.origin_country || '';
            document.getElementById('certification').value = product.certification || '';

            // Show existing images
            if (product.images && product.images.length > 0) {
                previewContainer.classList.remove('hidden');
                product.images.forEach((imgUrl, index) => {
                    const div = document.createElement('div');
                    div.className = 'relative group h-24 rounded-lg overflow-hidden border border-color';
                    div.innerHTML = `
                        <img src="${imgUrl}" class="w-full h-full object-cover">
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 truncate text-center">
                            صورة مسجلة #${index + 1}
                        </div>
                    `;
                    previewContainer.appendChild(div);
                });
            }
        } catch (error) {
            console.error('Error loading product details:', error);
            ui.showError('فشل تحميل تفاصيل المنتج');
        }
    }

    // Form Submission
    document.getElementById('add-product-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const submitButton = document.getElementById('submit-btn');
        const originalBtnText = submitButton.innerHTML;
        
        const formData = new FormData(this);
        
        // Remove default images[] transfer
        formData.delete('images[]');
        
        // Append all files in selectedFiles
        selectedFiles.forEach(file => {
            formData.append('images[]', file);
        });

        try {
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الحفظ...';
            
            if (productId) {
                // If editing, use put/post update
                // Form data requires POST with spoofing or handle as multipart POST
                formData.append('_method', 'PUT');
                await api.updateProduct(productId, formData);
                ui.showSuccess('تم تحديث المنتج بنجاح! ✅');
            } else {
                await api.createProduct(formData);
                ui.showSuccess('تم إضافة المنتج بنجاح! ✅');
            }
            
            setTimeout(() => {
                window.location.href = '{{ url("/seller/products") }}';
            }, 1000);
            
        } catch (error) {
            console.error(error);
            submitButton.disabled = false;
            submitButton.innerHTML = originalBtnText;
            ui.showError(error.message || 'فشل حفظ المنتج');
        }
    });

    // Init page
    async function initPage() {
        await loadCategories();
        await loadExistingProduct();
    }

    initPage();
</script>
@endsection
