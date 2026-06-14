/**
 * Global Theme Management
 * Handles dark/light mode toggling and persistence.
 * Apply 'dark' class to the HTML element.
 */

(function() {
    // 1. Immediate Theme Application (Prevent FOUC)
    // Check local storage or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && systemDark)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    // 2. Helper to Toggle Theme
    window.toggleTheme = function() {
        const html = document.documentElement;
        const isDark = html.classList.toggle('dark');
        
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        
        // Update UI icons if they exist
        updateThemeIcons(isDark);
    };

    // 3. Update Icons (Call this on load and toggle)
    function updateThemeIcons(isDark) {
        const moonIcons = document.querySelectorAll('.fa-moon');
        const sunIcons = document.querySelectorAll('.fa-sun');
        
        // This is a generic robust toggle, assumes icons might be hidden/shown via classes
        // Or we might change the icon class on a single element.
        
        // Strategy: Look for a generic theme-toggle-icon and swap class
        const toggleIcons = document.querySelectorAll('.theme-toggle-icon');
        toggleIcons.forEach(icon => {
            if (isDark) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            } else {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            }
        });
    }

    // Initialize icons on DOM load
    document.addEventListener('DOMContentLoaded', () => {
        const isDark = document.documentElement.classList.contains('dark');
        updateThemeIcons(isDark);
        
        // Attach click listeners to any element with .theme-toggle class
        const toggles = document.querySelectorAll('.theme-toggle');
        toggles.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                window.toggleTheme();
            });
        });
    });

})();
