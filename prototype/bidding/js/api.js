// API Configuration
const API_BASE_URL = 'http://localhost:8800/api/v1';
const TOKEN_KEY = 'jawharah_auth_token';
const USER_KEY = 'jawharah_user';

// API Helper Functions
const api = {
    // Get stored token
    getToken() {
        return localStorage.getItem(TOKEN_KEY);
    },

    // Save token
    saveToken(token) {
        localStorage.setItem(TOKEN_KEY, token);
    },

    // Remove token
    removeToken() {
        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
    },

    // Save user data
    saveUser(user) {
        localStorage.setItem(USER_KEY, JSON.stringify(user));
    },

    // Get user data
    getUser() {
        const user = localStorage.getItem(USER_KEY);
        return user ? JSON.parse(user) : null;
    },

    // Check if user is authenticated
    isAuthenticated() {
        return !!this.getToken();
    },

    // Make API request
    async request(endpoint, options = {}) {
        const url = `${API_BASE_URL}${endpoint}`;
        const token = this.getToken();

        const headers = {
            'Accept': 'application/json',
            ...options.headers,
        };

        // Only set Content-Type if body is NOT FormData
        // FormData requires the browser to set the Content-Type with boundary
        if (!(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
        }

        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }

        try {
            const response = await fetch(url, {
                ...options,
                headers,
            });

            let data;
            const responseText = await response.text();
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('Failed to parse JSON response:', responseText);
                throw new Error('Invalid JSON response from server: ' + responseText.substring(0, 100));
            }

            if (!response.ok) {
                if (response.status === 422) {
                    const error = new Error(data.message || 'Validation Error');
                    error.errors = data.errors;
                    throw error;
                }
                throw new Error(data.message || 'Something went wrong');
            }

            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },

    async register(userData) {
        const response = await this.request('/register', {
            method: 'POST',
            body: JSON.stringify(userData),
        });

        if (response.success && response.data.token) {
            this.saveToken(response.data.token);
            this.saveUser(response.data.user);
        }

        return response;
    },

    async login(credentials) {
        const response = await this.request('/login', {
            method: 'POST',
            body: JSON.stringify(credentials),
        });

        if (response.success && response.data.token) {
            this.saveToken(response.data.token);
            this.saveUser(response.data.user);
        }

        return response;
    },

    async logout() {
        try {
            await this.request('/logout', {
                method: 'POST',
            });
        } finally {
            this.removeToken();
        }
    },

    async getMe() {
        return await this.request('/me');
    },

    // Product APIs
    async getProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/products${queryString ? '?' + queryString : ''}`);
    },

    async getProduct(id) {
        return await this.request(`/products/${id}`);
    },

    async createProduct(productData) {
        return await this.request('/products', {
            method: 'POST',
            body: productData instanceof FormData ? productData : JSON.stringify(productData),
        });
    },

    async updateProduct(id, productData) {
        return await this.request(`/products/${id}`, {
            method: 'POST', // Changed from PUT to POST for FormData support (Laravel method spoofing if needed, but simple POST is often safer for files)
             // Actually, usually PUT doesn't handle files well in PHP. 
             // But if we are sending JSON, PUT is fine.
             // If sending FormData (files) for update, we typically use POST with _method: PUT.
             // For now let's just fix the stringify issue.
            method: productData instanceof FormData ? 'POST' : 'PUT',
            body: productData instanceof FormData ? productData : JSON.stringify(productData),
        });
    },

    async deleteProduct(id) {
        return await this.request(`/products/${id}`, {
            method: 'DELETE',
        });
    },

    async getMyProducts() {
        return await this.request('/my-products');
    },

    // Auction APIs
    async getAuctions(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/auctions${queryString ? '?' + queryString : ''}`);
    },

    async getAuction(id) {
        return await this.request(`/auctions/${id}`);
    },

    async createAuction(auctionData) {
        return await this.request('/auctions', {
            method: 'POST',
            body: auctionData instanceof FormData ? auctionData : JSON.stringify(auctionData),
        });
    },

    async updateAuction(id, auctionData) {
        return await this.request(`/auctions/${id}`, {
            method: auctionData instanceof FormData ? 'POST' : 'PUT',
            body: auctionData instanceof FormData ? auctionData : JSON.stringify(auctionData),
        });
    },

    async cancelAuction(id) {
        return await this.request(`/auctions/${id}`, {
            method: 'DELETE',
        });
    },

    async getMyAuctions() {
        return await this.request('/my-auctions');
    },

    // Bid APIs
    async placeBid(bidData) {
        return await this.request('/bids', {
            method: 'POST',
            body: JSON.stringify(bidData),
        });
    },

    async getMyBids() {
        return await this.request('/my-bids');
    },

    async getAuctionBids(auctionId) {
        return await this.request(`/auctions/${auctionId}/bids`);
    },

    // Admin APIs (require admin role)
    async getAllUsers(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/admin/users${queryString ? '?' + queryString : ''}`);
    },

    async getPendingSellers() {
        return await this.request('/admin/sellers/pending');
    },

    async approveSeller(userId) {
        return await this.request(`/admin/sellers/${userId}/approve`, {
            method: 'POST',
        });
    },

    async rejectSeller(userId) {
        return await this.request(`/admin/sellers/${userId}/reject`, {
            method: 'POST',
        });
    },

    async getPendingAuctions() {
        return await this.request('/admin/auctions/pending');
    },

    async approveAuction(auctionId) {
        return await this.request(`/admin/auctions/${auctionId}/approve`, {
            method: 'POST',
        });
    },

    async rejectAuction(auctionId) {
        return await this.request(`/admin/auctions/${auctionId}/reject`, {
            method: 'POST',
        });
    },

    // ============================================
    // Admin Content Management APIs
    // ============================================

    // Products Management
    async getAdminProducts(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/admin/products${queryString ? '?' + queryString : ''}`);
    },

    async updateAdminProduct(id, data) {
        return await this.request(`/admin/products/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    async deleteAdminProduct(id) {
        return await this.request(`/admin/products/${id}`, {
            method: 'DELETE',
        });
    },

    async toggleProductStatus(id) {
        return await this.request(`/admin/products/${id}/toggle-status`, {
            method: 'POST',
        });
    },

    // Auctions Management
    async getAdminAuctions(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/admin/auctions${queryString ? '?' + queryString : ''}`);
    },

    async updateAdminAuction(id, data) {
        return await this.request(`/admin/auctions/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    async deleteAdminAuction(id) {
        return await this.request(`/admin/auctions/${id}`, {
            method: 'DELETE',
        });
    },

    async endAuction(id) {
        return await this.request(`/admin/auctions/${id}/end`, {
            method: 'POST',
        });
    },

    // Categories Management
    async getAdminCategories(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return await this.request(`/admin/categories${queryString ? '?' + queryString : ''}`);
    },

    async createCategory(data) {
        return await this.request('/admin/categories', {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },

    async updateCategory(id, data) {
        return await this.request(`/admin/categories/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    async deleteCategory(id) {
        return await this.request(`/admin/categories/${id}`, {
            method: 'DELETE',
        });
    },

    async toggleCategoryStatus(id) {
        return await this.request(`/admin/categories/${id}/toggle-status`, {
            method: 'POST',
        });
    },

    // Settings Management
    async getSettings(group = null) {
        const params = group ? `?group=${group}` : '';
        return await this.request(`/admin/settings${params}`);
    },

    async getSetting(key) {
        return await this.request(`/admin/settings/${key}`);
    },

    async updateSettings(settings, group = null) {
        return await this.request('/admin/settings', {
            method: 'PUT',
            body: JSON.stringify({ settings, group }),
        });
    },

    async updateSetting(key, value, type = 'string', group = null) {
        return await this.request(`/admin/settings/${key}`, {
            method: 'PUT',
            body: JSON.stringify({ value, type, group }),
        });
    },

    // Seller Management
    async getSellerOrders() {
        return await this.request('/seller/orders');
    },

    async updateOrderStatus(orderId, status) {
        return await this.request(`/seller/orders/${orderId}/status`, {
            method: 'PUT',
            body: JSON.stringify({ status }),
        });
    },

    async getSellerStatistics() {
        return await this.request('/seller/statistics');
    },

    async getSellerEarnings() {
        return await this.request('/seller/earnings');
    },

    async getSellerProfile() {
        return await this.request('/seller/profile');
    },

    async updateSellerProfile(data) {
        return await this.request('/seller/profile', {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    async updateSellerSettings(data) {
        return await this.request('/seller/settings', {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },
};

// UI Helper Functions
const ui = {
    showLoading(button) {
        button.disabled = true;
        button.dataset.originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري التحميل...';
    },

    hideLoading(button) {
        button.disabled = false;
        if (button.dataset.originalText) {
            button.innerHTML = button.dataset.originalText;
        }
    },

    showError(message, container = null) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'bg-red-500 text-white p-4 rounded-lg mb-4 animate-fade-in';
        errorDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle ml-2"></i>
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        if (container) {
            container.insertBefore(errorDiv, container.firstChild);
        } else {
            document.body.insertBefore(errorDiv, document.body.firstChild);
        }

        setTimeout(() => errorDiv.remove(), 5000);
    },

    showSuccess(message, container = null) {
        const successDiv = document.createElement('div');
        successDiv.className = 'bg-green-500 text-white p-4 rounded-lg mb-4 animate-fade-in';
        successDiv.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-check-circle ml-2"></i>
                    <span>${message}</span>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        if (container) {
            container.insertBefore(successDiv, container.firstChild);
        } else {
            document.body.insertBefore(successDiv, document.body.firstChild);
        }

        setTimeout(() => successDiv.remove(), 5000);
    },

    showValidationErrors(errors, formElement) {
        // Clear previous errors
        formElement.querySelectorAll('.error-message').forEach(el => el.remove());
        formElement.querySelectorAll('.border-red-500').forEach(el => {
            el.classList.remove('border-red-500');
        });

        // Show new errors
        Object.keys(errors).forEach(field => {
            const input = formElement.querySelector(`[name="${field}"]`);
            if (input) {
                input.classList.add('border-red-500');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-message text-red-500 text-sm mt-1';
                errorDiv.textContent = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                input.parentElement.appendChild(errorDiv);
            }
        });
    },

    redirect(url, delay = 0) {
        setTimeout(() => {
            window.location.href = url;
        }, delay);
    },
};

// Check authentication on protected pages
function checkAuth() {
    if (!api.isAuthenticated()) {
        ui.redirect('../index.html');
        return false;
    }
    return true;
}

// Logout function
async function handleLogout() {
    try {
        await api.logout();
        ui.showSuccess('تم تسجيل الخروج بنجاح!');
        ui.redirect('./login.html', 1000);
    } catch (error) {
        console.error('Logout error:', error);
        api.removeToken();
        ui.redirect('./login.html');
    }
}
