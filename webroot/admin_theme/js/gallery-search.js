/**
 * Gallery Search Handler
 * Provides debounced AJAX search functionality for gallery list and grid views
 */
(function() {
    'use strict';

    let searchTimer = null;
    let config = {};

    /**
     * Initialize gallery search functionality
     */
    function init(options = {}) {
        config = Object.assign({
            searchInputId: 'gallery-search',
            searchFormId: 'gallery-search-form',
            ajaxTargetId: 'ajax-target',
            debounceDelay: 300,
            onSearchComplete: null
        }, options);

        const searchInput = document.getElementById(config.searchInputId);
        const searchForm = document.getElementById(config.searchFormId);

        if (!searchInput || !searchForm) {
            console.warn('Gallery search elements not found');
            return;
        }

        bindSearchEvents(searchInput, searchForm);
    }

    /**
     * Bind search input and form events
     */
    function bindSearchEvents(searchInput, searchForm) {
        // Debounced input search
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                performSearch();
            }, config.debounceDelay);
        });

        // Form submission
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(searchTimer);
            performSearch();
        });
    }

    /**
     * Perform AJAX search request
     */
    function performSearch() {
        const searchInput = document.getElementById(config.searchInputId);
        const ajaxTarget = document.getElementById(config.ajaxTargetId);
        
        if (!searchInput || !ajaxTarget) {
            console.error('Required search elements not found');
            return;
        }

        const searchTerm = searchInput.value.trim();
        
        // Only search if 3+ characters or empty (to show all results)
        if (searchTerm.length > 0 && searchTerm.length < 3) {
            return;
        }
        
        const url = new URL(window.location.href);

        // Update URL parameters
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }

        // Show loading state
        addLoadingState(ajaxTarget);

        fetch(url.toString(), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            ajaxTarget.innerHTML = html;
            removeLoadingState(ajaxTarget);
            
            // Trigger callback for additional processing
            if (config.onSearchComplete && typeof config.onSearchComplete === 'function') {
                config.onSearchComplete();
            }

            // Update browser history without page reload
            window.history.replaceState({}, '', url.toString());
        })
        .catch(error => {
            console.error('Gallery search error:', error);
            removeLoadingState(ajaxTarget);
            showSearchError();
        });
    }

    /**
     * Add loading state visual feedback
     */
    function addLoadingState(target) {
        target.style.opacity = '0.6';
        target.style.pointerEvents = 'none';
        
        // Add loading spinner if not present
        if (!target.querySelector('.search-loading')) {
            const loadingDiv = document.createElement('div');
            loadingDiv.className = 'search-loading position-absolute top-50 start-50 translate-middle';
            loadingDiv.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
            target.style.position = 'relative';
            target.appendChild(loadingDiv);
        }
    }

    /**
     * Remove loading state
     */
    function removeLoadingState(target) {
        target.style.opacity = '';
        target.style.pointerEvents = '';
        
        const loading = target.querySelector('.search-loading');
        if (loading) {
            loading.remove();
        }
    }

    /**
     * Show search error message
     */
    function showSearchError() {
        const ajaxTarget = document.getElementById(config.ajaxTargetId);
        if (ajaxTarget) {
            ajaxTarget.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Search Error:</strong> Unable to perform search. Please try again.
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
        }
    }

    /**
     * Clear current search
     */
    function clearSearch() {
        const searchInput = document.getElementById(config.searchInputId);
        if (searchInput) {
            searchInput.value = '';
            performSearch();
        }
    }

    // Auto-initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on a gallery page
        if (document.getElementById('gallery-search')) {
            init();
        }
    });

    // Export for manual initialization and control
    window.GallerySearch = {
        init: init,
        clearSearch: clearSearch,
        performSearch: performSearch
    };

})();