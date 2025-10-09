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
    
    // Optional: Ensure aria-current attributes are consistent for navigation tabs
    // While server-side detection handles this for full page loads, this provides
    // additional consistency for any client-side navigation scenarios
    function ensureAriaCurrentConsistency() {
        const navTabs = document.querySelectorAll('.product-tabs .nav-link');
        navTabs.forEach(link => {
            if (link.classList.contains('active')) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    }
    
    // Run aria-current consistency check on load
    ensureAriaCurrentConsistency();
    
    // Optional: Listen for any dynamic changes to navigation state
    // (This is primarily for future-proofing if client-side navigation is added later)
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.type === 'attributes' && 
                mutation.attributeName === 'class' &&
                mutation.target.classList.contains('nav-link')) {
                ensureAriaCurrentConsistency();
            }
        });
    });
    
    // Observe navigation links for class changes
    const productTabs = document.querySelector('.product-tabs');
    if (productTabs) {
        observer.observe(productTabs, {
            attributes: true,
            subtree: true,
            attributeFilter: ['class']
        });
    }
});
