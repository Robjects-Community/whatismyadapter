/**
 * AdminTheme Popover Manager Utility
 * Centralized Bootstrap popover management
 */

class PopoverManager {
    constructor() {
        this.popovers = new Map();
        this.init();
    }
    
    init() {
        this.initializeAll();
        this.bindEvents();
    }
    
    initializeAll() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach((popoverTriggerEl) => {
            const popover = new bootstrap.Popover(popoverTriggerEl);
            this.popovers.set(popoverTriggerEl, popover);
        });
    }
    
    bindEvents() {
        // Auto-initialize popovers when new content is added to the DOM
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeAll();
        });
    }
    
    refresh() {
        // Dispose existing popovers
        this.disposeAll();
        // Re-initialize all popovers
        this.initializeAll();
    }
    
    disposeAll() {
        this.popovers.forEach((popover) => {
            popover.dispose();
        });
        this.popovers.clear();
    }
    
    dispose(element) {
        const popover = this.popovers.get(element);
        if (popover) {
            popover.dispose();
            this.popovers.delete(element);
        }
    }
    
    // Static method for global access
    static getInstance() {
        if (!window.AdminTheme.popoverManagerInstance) {
            window.AdminTheme.popoverManagerInstance = new PopoverManager();
        }
        return window.AdminTheme.popoverManagerInstance;
    }
    
    // Utility method for components that dynamically add content
    static refreshPopovers() {
        const instance = PopoverManager.getInstance();
        instance.refresh();
    }
}

// Global namespace for AdminTheme utilities
window.AdminTheme = window.AdminTheme || {};
window.AdminTheme.PopoverManager = PopoverManager;

// Auto-initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    PopoverManager.getInstance();
});