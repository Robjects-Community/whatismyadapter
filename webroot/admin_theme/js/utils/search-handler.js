/**
 * AdminTheme Search Handler Utility
 * Reusable AJAX search functionality for admin interfaces
 */

class SearchHandler {
    constructor(config) {
        this.searchInputId = config.searchInputId;
        this.resultsContainerId = config.resultsContainerId;
        this.baseUrl = config.baseUrl;
        this.currentFilter = config.currentFilter || null;
        this.debounceDelay = config.debounceDelay || 300;
        
        this.debounceTimer = null;
        this.searchInput = null;
        this.resultsContainer = null;
        
        this.init();
    }
    
    init() {
        this.searchInput = document.getElementById(this.searchInputId);
        this.resultsContainer = document.querySelector(this.resultsContainerId);
        
        if (!this.searchInput || !this.resultsContainer) {
            console.error('SearchHandler: Required elements not found');
            return;
        }
        
        this.bindEvents();
        this.initializePopovers();
    }
    
    bindEvents() {
        this.searchInput.addEventListener('input', (e) => {
            this.handleSearch(e.target.value);
        });
    }
    
    handleSearch(searchTerm) {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.performSearch(searchTerm.trim());
        }, this.debounceDelay);
    }
    
    performSearch(searchTerm) {
        // Only search if 3+ characters or empty (to show all results)
        if (searchTerm.length > 0 && searchTerm.length < 3) {
            return;
        }
        
        let url = this.baseUrl;
        
        // Add current filter if exists
        if (this.currentFilter !== null) {
            url += `?status=${encodeURIComponent(this.currentFilter)}`;
        }
        
        // Add search term if provided
        if (searchTerm.length > 0) {
            url += (url.includes('?') ? '&' : '?') + `search=${encodeURIComponent(searchTerm)}`;
        }
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            this.resultsContainer.innerHTML = html;
            this.initializePopovers();
        })
        .catch(error => {
            console.error('Search error:', error);
            this.showError('Search failed. Please try again.');
        });
    }
    
    initializePopovers() {
        // Re-initialize popovers after updating the content
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }
    
    showError(message) {
        // Create a simple error display
        const errorHtml = `
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                ${message}
            </div>
        `;
        this.resultsContainer.innerHTML = errorHtml;
    }
    
    // Static method to initialize search handlers from templates
    static init(config) {
        document.addEventListener('DOMContentLoaded', function() {
            new SearchHandler(config);
        });
    }
}

// Global namespace for AdminTheme utilities
window.AdminTheme = window.AdminTheme || {};
window.AdminTheme.SearchHandler = SearchHandler;