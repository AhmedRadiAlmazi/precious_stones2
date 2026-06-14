/**
 * Dashboard JavaScript - Precious Stones Platform
 * Handles sidebar navigation, theme switching, and dashboard interactions
 */

(function() {
    'use strict';

    // ============================================
    // Sidebar Toggle Functionality
    // ============================================
    
    function initSidebar() {
        const sidebar = document.querySelector('.dashboard-sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebar || !sidebarToggle) return;
        
        // Toggle sidebar
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('open');
            }
        });
        
        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('open');
            });
        }
        
        // Close sidebar on mobile when clicking a link
        const navLinks = sidebar.querySelectorAll('.sidebar-nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebar.classList.remove('open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('open');
                    }
                }
            });
        });
    }

    // ============================================
    // Active Navigation State
    // ============================================
    
    function setActiveNavItem() {
        const currentPath = window.location.pathname;
        const navItems = document.querySelectorAll('.sidebar-nav-item');
        
        navItems.forEach(item => {
            const href = item.getAttribute('href');
            if (href && currentPath.includes(href)) {
                item.classList.add('active');
            } else {
                item.classList.remove('active');
            }
        });
    }

    // ============================================
    // Search Functionality
    // ============================================
    
    function initSearch() {
        const searchInput = document.querySelector('.dashboard-search input');
        
        if (!searchInput) return;
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const searchTerm = this.value.trim();
                if (searchTerm) {
                    console.log('Searching for:', searchTerm);
                    // Implement search functionality here
                }
            }
        });
    }

    // ============================================
    // Notifications
    // ============================================
    
    function initNotifications() {
        const notificationBtn = document.querySelector('[data-action="notifications"]');
        
        if (!notificationBtn) return;
        
        notificationBtn.addEventListener('click', function() {
            // Show notifications dropdown
            console.log('Show notifications');
            // Implement notifications dropdown here
        });
    }

    // ============================================
    // Stats Animation
    // ============================================
    
    function animateStats() {
        const statValues = document.querySelectorAll('.stat-card-value');
        
        statValues.forEach(stat => {
            const finalValue = parseInt(stat.textContent.replace(/[^0-9]/g, ''));
            if (isNaN(finalValue)) return;
            
            let currentValue = 0;
            const increment = finalValue / 50;
            const duration = 1000;
            const stepTime = duration / 50;
            
            const counter = setInterval(() => {
                currentValue += increment;
                if (currentValue >= finalValue) {
                    stat.textContent = formatNumber(finalValue);
                    clearInterval(counter);
                } else {
                    stat.textContent = formatNumber(Math.floor(currentValue));
                }
            }, stepTime);
        });
    }
    
    function formatNumber(num) {
        return num.toLocaleString('ar-SA');
    }

    // ============================================
    // Responsive Handling
    // ============================================
    
    function handleResize() {
        const sidebar = document.querySelector('.dashboard-sidebar');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        
        if (!sidebar) return;
        
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1024) {
                sidebar.classList.remove('open');
                if (sidebarOverlay) {
                    sidebarOverlay.classList.remove('open');
                }
            }
        });
    }

    // ============================================
    // User Dropdown
    // ============================================
    
    function initUserDropdown() {
        const userBtn = document.querySelector('.sidebar-user');
        
        if (!userBtn) return;
        
        userBtn.addEventListener('click', function() {
            // Show user dropdown menu
            console.log('Show user menu');
            // Implement user dropdown here
        });
    }

    // ============================================
    // Initialize Dashboard
    // ============================================
    
    function init() {
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAll);
        } else {
            initAll();
        }
    }
    
    function initAll() {
        initSidebar();
        setActiveNavItem();
        // initTheme(); // Handled by theme.js
        initSearch();
        initNotifications();
        initUserDropdown();
        handleResize();
        
        // Animate stats on page load
        setTimeout(animateStats, 300);
    }

    // Start initialization
    init();

    // ============================================
    // Export functions for external use
    // ============================================
    
    window.Dashboard = {
        setActiveNav: setActiveNavItem,
        animateStats: animateStats
    };

})();
