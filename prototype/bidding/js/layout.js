/**
 * Global Dashboard Layout Manager
 * Injects Sidebar and Header dynamically.
 * Handles active state and theme integration.
 */

(function() {
    'use strict';

    // Wait for DOM
    document.addEventListener('DOMContentLoaded', async () => {
        const dashboardContainer = document.querySelector('.dashboard-container');
        if (!dashboardContainer) {
            console.warn('Dashboard container not found. Layout script requires .dashboard-container');
            return;
        }

        // 1. Determine User Role (admin vs seller)
        // We rely on api.js being loaded.
        let role = 'seller'; // default
        if (typeof api !== 'undefined' && api.getUser) {
            const user = api.getUser();
            if (user && user.roles && user.roles.some(r => r.name === 'admin')) {
                role = 'admin';
            }
        }
        
        // 2. Define Navigation Data
        const navItems = {
            admin: [
                { section: 'القائمة الرئيسية', items: [
                    { label: 'لوحة التحكم', icon: 'fa-home', url: 'admin-dashboard.html' },
                    { label: 'المستخدمين', icon: 'fa-users', url: 'admin-users.html' },
                    { label: 'البائعون المعلقون', icon: 'fa-user-clock', url: '#', badgeId: 'pending-badge' }
                ]},
                { section: 'المحتوى', items: [
                    { label: 'المنتجات', icon: 'fa-box', url: 'admin-products.html' },
                    { label: 'المزادات', icon: 'fa-gavel', url: 'admin-auctions.html' },
                    { label: 'الطلبات', icon: 'fa-shopping-cart', url: '#' },
                    { label: 'الفئات', icon: 'fa-tags', url: 'admin-categories.html' }
                ]},
                { section: 'الإعدادات', items: [
                    { label: 'إعدادات النظام', icon: 'fa-cog', url: 'admin-settings.html' },
                    { label: 'العودة للموقع', icon: 'fa-arrow-right', url: '../index.html' }
                ]}
            ],
            seller: [
                { section: 'القائمة الرئيسية', items: [
                    { label: 'لوحة التحكم', icon: 'fa-home', url: 'seller-dashboard.html' }
                ]},
                { section: 'المنتجات والمزادات', items: [
                    { label: 'منتجاتي', icon: 'fa-box', url: 'seller-products.html' },
                    { label: 'مزاداتي', icon: 'fa-gavel', url: 'seller-auctions.html' }
                ]},
                { section: 'إضافة جديد', items: [
                    { label: 'إضافة منتج', icon: 'fa-plus-circle', url: 'add-product.html' },
                    { label: 'إنشاء مزاد', icon: 'fa-hammer', url: 'create-auction.html' }
                ]},
                { section: 'المبيعات', items: [
                    { label: 'الطلبات', icon: 'fa-shopping-cart', url: 'seller-orders.html' },
                    { label: 'الإحصائيات', icon: 'fa-chart-line', url: 'seller-stats.html' },
                    { label: 'الأرباح', icon: 'fa-dollar-sign', url: 'seller-revenue.html' }
                ]},
                { section: 'الإعدادات', items: [
                    { label: 'العودة للموقع', icon: 'fa-arrow-right', url: '../index.html' }
                ]}
            ]
        };

        const currentNav = navItems[role] || navItems.seller;
        const currentPath = window.location.pathname.split('/').pop();

        // 3. Build Sidebar HTML
        const sidebarHTML = `
        <aside class="dashboard-sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <div class="sidebar-logo-icon">
                        <i class="fas fa-gem text-white text-xl"></i>
                    </div>
                    <span class="sidebar-logo-text">جوهرة</span>
                </div>
                <p class="sidebar-role">${role === 'admin' ? 'لوحة تحكم الإدارة' : 'لوحة تحكم البائع'}</p>
            </div>

            <nav class="sidebar-nav">
                ${currentNav.map(section => `
                    <div class="sidebar-nav-section">
                        <h3 class="sidebar-nav-title">${section.section}</h3>
                        ${section.items.map(item => `
                            <a href="${item.url}" class="sidebar-nav-item ${item.url === currentPath ? 'active' : ''}">
                                <i class="sidebar-nav-icon fas ${item.icon}"></i>
                                <span class="sidebar-nav-text">${item.label}</span>
                                ${item.badgeId ? `<span class="sidebar-nav-badge" id="${item.badgeId}">0</span>` : ''}
                            </a>
                        `).join('')}
                    </div>
                `).join('')}
            </nav>
            
            <div class="sidebar-footer">
               <!-- Optional Footer Info -->
            </div>
        </aside>
        `;

        // 4. Build Header HTML (only inner content of .dashboard-header if possible, or inject whole header)
        // NOTE: The main content area usually contains the header. We need to be careful not to overwrite page-specific content.
        // We will assume the page has a <main class="dashboard-main"> and we prepend the header to it.
        
        // Find active nav item for title
        let pageTitle = role === 'admin' ? 'لوحة التحكم' : 'لوحة البائع';
        let breadcrumbSection = 'الرئيسية';
        
        for (const section of currentNav) {
            const activeItem = section.items.find(i => i.url === currentPath);
            if (activeItem) {
                pageTitle = activeItem.label;
                breadcrumbSection = section.section;
                break;
            }
        }

        const mainContent = document.querySelector('.dashboard-main');
        if (mainContent) {
            // Check if header already exists (static), if so remove it
            const existingHeader = mainContent.querySelector('.dashboard-header');
            if(existingHeader) existingHeader.remove();

            const headerHTML = `
            <header class="dashboard-header">
                <div class="dashboard-header-left">
                    <button class="sidebar-toggle" id="sidebar-toggle-btn">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="dashboard-title">${pageTitle}</h1>
                        <div class="dashboard-breadcrumb">
                             <i class="fas fa-home"></i>
                             <span>${breadcrumbSection}</span>
                             <i class="fas fa-chevron-left text-xs"></i>
                             <span>${pageTitle}</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-header-right">
                    <div class="dashboard-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="البحث...">
                    </div>

                    <div class="dashboard-actions">
                        <button class="dashboard-action-btn theme-toggle" id="theme-toggle">
                            <i class="fas fa-moon theme-toggle-icon" id="theme-icon"></i>
                        </button>
                        <button class="dashboard-action-btn" onclick="if(window.handleLogout) window.handleLogout()">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </div>
                </div>
            </header>
            `;
            
            mainContent.insertAdjacentHTML('afterbegin', headerHTML);
        }

        // 5. Inject Sidebar PREPENDING to dashboard-container
        // Check if sidebar exists and remove
        const existingSidebar = document.querySelector('.dashboard-sidebar');
        if(existingSidebar) existingSidebar.remove();
        
        dashboardContainer.insertAdjacentHTML('afterbegin', sidebarHTML);

        // 6. Re-Initialize Theme Buttons if they were injected
        // theme.js runs on DOMContentLoaded usually. Since we are also likely in DOMContentLoaded, 
        // we might run AFTER theme.js. So we need to manually trigger icon update or re-attach listeners.
        if (window.toggleTheme) {
             const btn = document.getElementById('theme-toggle');
             if(btn) {
                 btn.addEventListener('click', window.toggleTheme);
             }
             // Ensure correct icon state
             const isDark = document.documentElement.classList.contains('dark');
             const icon = document.getElementById('theme-icon');
             if(icon) {
                 if(isDark) {
                     icon.classList.remove('fa-moon');
                     icon.classList.add('fa-sun');
                 } else {
                     icon.classList.remove('fa-sun');
                     icon.classList.add('fa-moon');
                 }
             }
        }

        // 7. Add Sidebar Event Listeners (Event Delegation)
        document.addEventListener('click', (e) => {
            // Sidebar Toggle
            const toggleBtn = e.target.closest('#sidebar-toggle-btn');
            if (toggleBtn) {
                const sidebar = document.querySelector('.dashboard-sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                if (sidebar) sidebar.classList.toggle('open');
                if (overlay) overlay.classList.toggle('open');
            }

            // Overlay Click
            if (e.target.classList.contains('sidebar-overlay')) {
                const sidebar = document.querySelector('.dashboard-sidebar');
                const overlay = document.querySelector('.sidebar-overlay');
                if (sidebar) sidebar.classList.remove('open');
                if (overlay) overlay.classList.remove('open');
            }
        });
    });

})();
