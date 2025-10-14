/**
 * Universal Search Handler
 * Provides consistent search behavior across all admin interfaces
 * Automatically triggers search after 3+ characters with 300ms debounce
 */
(function() {
    'use strict';

    /**
     * Initialize universal search on any search input
     * @param {Object} options Configuration options
     * @param {string} options.inputSelector - CSS selector for search input
     * @param {string} options.formSelector - CSS selector for search form
     * @param {string} options.targetSelector - CSS selector for results container
     * @param {string} options.baseUrl - Base URL for search requests
     * @param {number} [options.minChars=3] - Minimum characters to trigger search
     * @param {number} [options.debounceDelay=300] - Debounce delay in milliseconds
     * @param {Function} [options.onComplete] - Callback after search completes
     */
    function initUniversalSearch(options) {
        const config = Object.assign({
            minChars: 3,
            debounceDelay: 300,
            onComplete: null
        }, options);

        const searchInput = document.querySelector(config.inputSelector);
        const searchForm = document.querySelector(config.formSelector);
        const resultsContainer = document.querySelector(config.targetSelector);

        if (!searchInput || !searchForm || !resultsContainer) {
            console.warn('Universal search: Required elements not found', {
                input: !!searchInput,
                form: !!searchForm,
                results: !!resultsContainer
            });
            return;
        }

        let debounceTimer = null;

        function performSearch(searchTerm) {
            // Only search if meets minimum character requirement or is empty (show all)
            if (searchTerm.length > 0 && searchTerm.length < config.minChars) {
                return;
            }

            // Build URL with search parameters
            const url = new URL(config.baseUrl, window.location.origin);
            if (searchTerm.length > 0) {
                url.searchParams.set('search', searchTerm);
            }

            // Preserve existing URL parameters
            const currentParams = new URLSearchParams(window.location.search);
            currentParams.forEach((value, key) => {
                if (key !== 'search') {
                    url.searchParams.set(key, value);
                }
            });

            // Add loading state
            resultsContainer.style.opacity = '0.6';
            resultsContainer.style.pointerEvents = 'none';

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
                resultsContainer.innerHTML = html;
                resultsContainer.style.opacity = '';
                resultsContainer.style.pointerEvents = '';
                
                // Update browser history
                window.history.replaceState({}, '', url.toString());
                
                // Call completion callback
                if (config.onComplete && typeof config.onComplete === 'function') {
                    config.onComplete();
                }
            })
            .catch(error => {
                console.error('Universal search error:', error);
                resultsContainer.style.opacity = '';
                resultsContainer.style.pointerEvents = '';
                
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Search failed. Please try again.
                    </div>
                `;
            });
        }

        // Bind input events
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                performSearch(this.value.trim());
            }, config.debounceDelay);
        });

        // Bind form submission
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            clearTimeout(debounceTimer);
            performSearch(searchInput.value.trim());
        });
    }

    /**
     * Auto-initialize universal search for common patterns
     */
    function autoInitialize() {
        // Standard admin search pattern
        const standardSearch = document.querySelector('#search-form');
        if (standardSearch) {
            const searchInput = standardSearch.querySelector('input[type="search"]');
            const resultsContainer = document.querySelector('#ajax-target');
            
            if (searchInput && resultsContainer) {
                initUniversalSearch({
                    inputSelector: `#${searchInput.id}`,
                    formSelector: '#search-form',
                    targetSelector: '#ajax-target',
                    baseUrl: window.location.pathname,
                    onComplete: function() {
                        // Re-initialize Bootstrap popovers
                        const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
                        popovers.forEach(el => {
                            const existingPopover = bootstrap.Popover.getInstance(el);
                            if (existingPopover) {
                                existingPopover.dispose();
                            }
                            new bootstrap.Popover(el);
                        });
                    }
                });
            }
        }
    }

    // Export for manual usage
    window.UniversalSearch = {
        init: initUniversalSearch,
        autoInit: autoInitialize
    };

    // Auto-initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', autoInitialize);
    } else {
        autoInitialize();
    }

})();