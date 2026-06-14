/**
 * Jawhara Luxury Notification & Confirmation System
 * Replaces standard alert/confirm dialogs with gold-accented premium UI components.
 */

(function() {
    // 1. Toast notifications helpers
    function getToastContainer() {
        let container = document.querySelector('.custom-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'custom-toast-container';
            document.body.appendChild(container);
        }
        return container;
    }

    function showToast(message, type = 'info', duration = 5000) {
        const container = getToastContainer();
        const toast = document.createElement('div');
        toast.className = `custom-toast custom-toast-${type}`;
        
        let iconClass = 'fa-info-circle';
        if (type === 'success') iconClass = 'fa-check-circle';
        if (type === 'error') iconClass = 'fa-exclamation-circle';
        if (type === 'warning') iconClass = 'fa-exclamation-triangle';
        
        toast.innerHTML = `
            <div class="custom-toast-icon">
                <i class="fas ${iconClass}"></i>
            </div>
            <div class="custom-toast-content">${message}</div>
            <button class="custom-toast-close" aria-label="إغلاق">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        // Trigger show animation
        setTimeout(() => toast.classList.add('show'), 50);
        
        const closeToast = () => {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => toast.remove(), 400);
        };
        
        toast.querySelector('.custom-toast-close').addEventListener('click', closeToast);
        
        let autoClose = setTimeout(closeToast, duration);
        
        // Pause timeout on hover
        toast.addEventListener('mouseenter', () => clearTimeout(autoClose));
        toast.addEventListener('mouseleave', () => autoClose = setTimeout(closeToast, 3000));
    }

    // 2. Custom Confirmation Modal helper
    function showCustomConfirm(message, title = 'تأكيد العملية', confirmText = 'حسناً', cancelText = 'إلغاء') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'custom-confirm-overlay';
            
            // Clean up message newlines to <br> for HTML rendering
            const htmlMessage = message.replace(/\n/g, '<br>');
            
            overlay.innerHTML = `
                <div class="custom-confirm-modal">
                    <div class="custom-confirm-header">
                        <div class="custom-confirm-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="custom-confirm-title">${title}</div>
                    </div>
                    <div class="custom-confirm-body">${htmlMessage}</div>
                    <div class="custom-confirm-footer">
                        <button class="custom-confirm-btn custom-confirm-btn-confirm">${confirmText}</button>
                        <button class="custom-confirm-btn custom-confirm-btn-cancel">${cancelText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden'; // Lock scrolling
            
            setTimeout(() => overlay.classList.add('show'), 50);
            
            const cleanUp = () => {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
                setTimeout(() => overlay.remove(), 300);
            };
            
            overlay.querySelector('.custom-confirm-btn-confirm').addEventListener('click', () => {
                cleanUp();
                resolve(true);
            });
            
            overlay.querySelector('.custom-confirm-btn-cancel').addEventListener('click', () => {
                cleanUp();
                resolve(false);
            });
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    cleanUp();
                    resolve(false);
                }
            });
        });
    }

    // 3. Custom Alert Modal helper
    function showCustomAlert(message, title = 'تنبيه', buttonText = 'حسناً') {
        return new Promise((resolve) => {
            const overlay = document.createElement('div');
            overlay.className = 'custom-confirm-overlay';
            
            const htmlMessage = message.replace(/\n/g, '<br>');
            
            let iconClass = 'fa-exclamation-circle';
            let iconColorStyle = '';
            
            if (message.includes('نجاح') || message.includes('بنجاح') || message.includes('تم ')) {
                iconClass = 'fa-check-circle';
                iconColorStyle = 'color: #10b981; border-color: rgba(16, 185, 129, 0.4); background: rgba(16, 185, 129, 0.08);';
            } else if (message.includes('عذراً') || message.includes('خطأ') || message.includes('فشل') || message.includes('غير كاف')) {
                iconClass = 'fa-times-circle';
                iconColorStyle = 'color: #ef4444; border-color: rgba(239, 68, 68, 0.4); background: rgba(239, 68, 68, 0.08);';
            }
            
            overlay.innerHTML = `
                <div class="custom-confirm-modal">
                    <div class="custom-confirm-header">
                        <div class="custom-confirm-icon" style="${iconColorStyle}">
                            <i class="fas ${iconClass}"></i>
                        </div>
                        <div class="custom-confirm-title">${title}</div>
                    </div>
                    <div class="custom-confirm-body">${htmlMessage}</div>
                    <div class="custom-confirm-footer">
                        <button class="custom-confirm-btn custom-confirm-btn-confirm" style="width: 100%">${buttonText}</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
            
            setTimeout(() => overlay.classList.add('show'), 50);
            
            const cleanUp = () => {
                overlay.classList.remove('show');
                document.body.style.overflow = '';
                setTimeout(() => overlay.remove(), 300);
            };
            
            overlay.querySelector('.custom-confirm-btn-confirm').addEventListener('click', () => {
                cleanUp();
                resolve(true);
            });
            
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    cleanUp();
                    resolve(true);
                }
            });
        });
    }

    // 4. Overwrite ui helper functions if loaded
    if (typeof ui !== 'undefined') {
        ui.showSuccess = function(message) {
            showToast(message, 'success');
        };
        ui.showError = function(message) {
            showToast(message, 'error');
        };
        ui.showInfo = function(message) {
            showToast(message, 'info');
        };
        ui.showWarning = function(message) {
            showToast(message, 'warning');
        };
        
        ui.confirm = function(message, title = 'تأكيد العملية', confirmText = 'موافق', cancelText = 'إلغاء') {
            return showCustomConfirm(message, title, confirmText, cancelText);
        };
        ui.alert = function(message, title = 'تنبيه', buttonText = 'حسناً') {
            return showCustomAlert(message, title, buttonText);
        };
        
        window.ui = ui;
    } else {
        // Expose globally if ui is not yet defined
        window.ui = {
            showSuccess: (msg) => showToast(msg, 'success'),
            showError: (msg) => showToast(msg, 'error'),
            showInfo: (msg) => showToast(msg, 'info'),
            showWarning: (msg) => showToast(msg, 'warning'),
            confirm: (msg, title, conf, canc) => showCustomConfirm(msg, title, conf, canc),
            alert: (msg, title, btn) => showCustomAlert(msg, title, btn)
        };
    }

    // Expose standalone functions too
    window.customToast = showToast;
    window.customConfirm = showCustomConfirm;
    window.customAlert = showCustomAlert;

})();
