/**
 * AdminTheme Layout JavaScript
 * Core layout functionality for the Willow CMS admin interface
 */

document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('adminSidebarDesktop');
    
    // Initialize tooltips
    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Load saved sidebar state
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
        initializeTooltips();
    }
    
    // Remove preload class to enable transitions
    document.documentElement.classList.remove('sidebar-preload-collapsed');

    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        if (isCollapsed) {
            // Initialize tooltips when collapsed
            setTimeout(initializeTooltips, 300); // Wait for animation to complete
        } else {
            // Dispose tooltips when expanded
            const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
            tooltips.forEach(tooltip => {
                const tooltipInstance = bootstrap.Tooltip.getInstance(tooltip);
                if (tooltipInstance) {
                    tooltipInstance.dispose();
                }
            });
        }
    });

    // Initialize tooltips if sidebar is already collapsed
    if (sidebar.classList.contains('collapsed')) {
        initializeTooltips();
    }
});