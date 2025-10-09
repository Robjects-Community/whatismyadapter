/**
 * Funding Banner JavaScript for robjects-community
 * 
 * This file provides dynamic functionality for the funding banner component.
 * Features:
 * - Local storage caching with configurable TTL
 * - API integration for real-time updates
 * - Progress bar animations
 * - Error handling and fallbacks
 * 
 * Usage:
 * Include this script in your page and call initializeFundingBanner() 
 * with the banner element ID and configuration options.
 */

(function() {
    'use strict';

    // Configuration defaults
    const DEFAULTS = {
        CACHE_KEY: 'robjectsFundingData',
        CACHE_TTL: 12 * 60 * 60 * 1000, // 12 hours in milliseconds
        UPDATE_INTERVAL: 60 * 60 * 1000, // 1 hour for background updates
        ANIMATION_DURATION: 1000, // Progress bar animation duration in ms
        API_TIMEOUT: 5000, // API request timeout in ms
        RETRY_ATTEMPTS: 3,
        RETRY_DELAY: 1000 // Delay between retry attempts in ms
    };

    /**
     * Initialize a funding banner instance
     * @param {string} bannerId - The ID of the banner element
     * @param {Object} options - Configuration options
     * @param {string} options.apiEndpoint - API endpoint URL for fetching data
     * @param {number} options.currentAmount - Current funding amount (fallback)
     * @param {number} options.goalAmount - Goal amount (fallback)
     * @param {string} options.campaignName - Campaign name
     * @param {number} options.cacheTTL - Cache time-to-live in milliseconds
     * @param {boolean} options.enableAutoUpdate - Enable automatic updates
     */
    window.initializeFundingBanner = function(bannerId, options = {}) {
        const banner = document.getElementById(bannerId);
        if (!banner) {
            console.error(`Funding banner element not found: ${bannerId}`);
            return;
        }

        const config = {
            apiEndpoint: options.apiEndpoint || banner.dataset.apiEndpoint,
            currentAmount: options.currentAmount || 0,
            goalAmount: options.goalAmount || 12000,
            campaignName: options.campaignName || 'Monthly Goal',
            cacheTTL: options.cacheTTL || DEFAULTS.CACHE_TTL,
            enableAutoUpdate: options.enableAutoUpdate !== false,
            cacheKey: `${DEFAULTS.CACHE_KEY}_${bannerId}`
        };

        // Initialize the banner
        const bannerInstance = new FundingBanner(banner, config);
        bannerInstance.init();

        // Store instance for external access
        banner.fundingBannerInstance = bannerInstance;
    };

    /**
     * FundingBanner class
     */
    class FundingBanner {
        constructor(element, config) {
            this.element = element;
            this.config = config;
            this.updateTimer = null;
            this.retryCount = 0;
        }

        /**
         * Initialize the banner
         */
        async init() {
            try {
                // Check for cached data first
                const cachedData = this.getCachedData();
                
                if (cachedData) {
                    this.updateBannerDisplay(cachedData);
                }

                // If API endpoint is configured, fetch fresh data
                if (this.config.apiEndpoint) {
                    await this.fetchAndUpdateData();
                    
                    // Set up auto-update if enabled
                    if (this.config.enableAutoUpdate) {
                        this.startAutoUpdate();
                    }
                }

                // Animate progress bar on load
                this.animateProgressBar();

            } catch (error) {
                console.error('Failed to initialize funding banner:', error);
                this.handleError(error);
            }
        }

        /**
         * Get cached data from localStorage
         * @returns {Object|null} Cached data or null if not found/expired
         */
        getCachedData() {
            try {
                const cached = localStorage.getItem(this.config.cacheKey);
                if (!cached) return null;

                const { timestamp, data } = JSON.parse(cached);
                const age = Date.now() - timestamp;

                if (age < this.config.cacheTTL) {
                    return data;
                }

                // Cache expired, remove it
                localStorage.removeItem(this.config.cacheKey);
                return null;

            } catch (error) {
                console.error('Error reading cached data:', error);
                return null;
            }
        }

        /**
         * Save data to localStorage cache
         * @param {Object} data - Data to cache
         */
        setCachedData(data) {
            try {
                const cacheData = {
                    timestamp: Date.now(),
                    data: data
                };
                localStorage.setItem(this.config.cacheKey, JSON.stringify(cacheData));
            } catch (error) {
                console.error('Error caching data:', error);
            }
        }

        /**
         * Fetch data from API and update display
         */
        async fetchAndUpdateData() {
            if (!this.config.apiEndpoint) return;

            try {
                this.element.classList.add('loading');
                
                const data = await this.fetchWithRetry(this.config.apiEndpoint);
                
                // Process the data based on expected API response format
                const processedData = this.processApiResponse(data);
                
                // Update display
                this.updateBannerDisplay(processedData);
                
                // Cache the data
                this.setCachedData(processedData);
                
                this.element.classList.remove('loading');
                this.element.classList.remove('error');
                
            } catch (error) {
                console.error('Failed to fetch funding data:', error);
                this.handleError(error);
            }
        }

        /**
         * Fetch with retry logic
         * @param {string} url - API endpoint URL
         * @returns {Promise<Object>} API response data
         */
        async fetchWithRetry(url) {
            for (let attempt = 0; attempt < DEFAULTS.RETRY_ATTEMPTS; attempt++) {
                try {
                    const controller = new AbortController();
                    const timeoutId = setTimeout(() => controller.abort(), DEFAULTS.API_TIMEOUT);
                    
                    const response = await fetch(url, {
                        signal: controller.signal,
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    
                    clearTimeout(timeoutId);
                    
                    if (!response.ok) {
                        throw new Error(`API request failed: ${response.status}`);
                    }
                    
                    return await response.json();
                    
                } catch (error) {
                    if (attempt === DEFAULTS.RETRY_ATTEMPTS - 1) {
                        throw error;
                    }
                    
                    // Wait before retrying
                    await new Promise(resolve => 
                        setTimeout(resolve, DEFAULTS.RETRY_DELAY * (attempt + 1))
                    );
                }
            }
        }

        /**
         * Process API response data
         * @param {Object} data - Raw API response
         * @returns {Object} Processed data for display
         * 
         * This method should be customized based on your API response format.
         * Common API response formats:
         * 
         * GitHub Sponsors format:
         * {
         *   "total_monthly_average_income": 8226,
         *   "current_goal": { "target_amount": 12000 }
         * }
         * 
         * Custom format:
         * {
         *   "current": 8226,
         *   "goal": 12000,
         *   "campaign": "Monthly Goal"
         * }
         */
        processApiResponse(data) {
            // GitHub Sponsors format (like DDEV)
            if (data.total_monthly_average_income !== undefined) {
                return {
                    currentAmount: data.total_monthly_average_income || 0,
                    goalAmount: data.current_goal?.target_amount || this.config.goalAmount,
                    campaignName: data.current_goal?.title || this.config.campaignName
                };
            }
            
            // Custom format
            if (data.current !== undefined) {
                return {
                    currentAmount: data.current || 0,
                    goalAmount: data.goal || this.config.goalAmount,
                    campaignName: data.campaign || this.config.campaignName
                };
            }
            
            // Patreon format
            if (data.data?.attributes?.patron_count !== undefined) {
                return {
                    currentAmount: data.data.attributes.campaign_pledge_sum || 0,
                    goalAmount: data.data.attributes.campaign_goal || this.config.goalAmount,
                    campaignName: this.config.campaignName
                };
            }
            
            // Default: use the data as-is if it has the expected properties
            return {
                currentAmount: data.currentAmount || this.config.currentAmount,
                goalAmount: data.goalAmount || this.config.goalAmount,
                campaignName: data.campaignName || this.config.campaignName
            };
        }

        /**
         * Update banner display with new data
         * @param {Object} data - Funding data to display
         */
        updateBannerDisplay(data) {
            const percentage = Math.min((data.currentAmount / data.goalAmount) * 100, 100);
            const percentageDisplay = Math.round(percentage);
            
            // Format currency
            const formatter = new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            
            const currentDisplay = formatter.format(data.currentAmount);
            const goalDisplay = formatter.format(data.goalAmount);
            
            // Update text elements
            this.updateTextElement('[data-field="raised"]', `Raised: ${currentDisplay}`);
            this.updateTextElement('[data-field="raised-mobile"]', `Raised: ${currentDisplay}`);
            this.updateTextElement('[data-field="goal"]', `Goal: ${goalDisplay}`);
            this.updateTextElement('[data-field="goal-mobile"]', `Goal: ${goalDisplay}`);
            this.updateTextElement('[data-field="percent"]', `${percentageDisplay}% of ${data.campaignName}`);
            this.updateTextElement('[data-field="percent-mobile"]', `${percentageDisplay}% of ${data.campaignName}`);
            
            // Update progress bars
            this.updateProgressBar('[data-field="progress"]', percentage);
            this.updateProgressBar('[data-field="progress-mobile"]', percentage);
            
            // Update progress bar state based on percentage
            this.updateProgressBarState(percentage);
        }

        /**
         * Update text content of an element
         * @param {string} selector - Element selector
         * @param {string} text - Text to set
         */
        updateTextElement(selector, text) {
            const element = this.element.querySelector(selector);
            if (element) {
                element.textContent = text;
            }
        }

        /**
         * Update progress bar width
         * @param {string} selector - Progress bar selector
         * @param {number} percentage - Percentage to set
         */
        updateProgressBar(selector, percentage) {
            const progressBar = this.element.querySelector(selector);
            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
                progressBar.setAttribute('aria-valuenow', percentage);
            }
        }

        /**
         * Update progress bar state based on percentage
         * @param {number} percentage - Current percentage
         */
        updateProgressBarState(percentage) {
            const progressBars = this.element.querySelectorAll('.robjects-funding-banner__progress-fill');
            
            progressBars.forEach(bar => {
                // Remove all state classes
                bar.removeAttribute('data-state');
                
                // Add appropriate state
                if (percentage >= 100) {
                    bar.setAttribute('data-state', 'success');
                } else if (percentage >= 75) {
                    bar.setAttribute('data-state', 'warning');
                } else if (percentage < 25) {
                    bar.setAttribute('data-state', 'danger');
                }
            });
        }

        /**
         * Animate progress bar on load
         */
        animateProgressBar() {
            const progressBars = this.element.querySelectorAll('.robjects-funding-banner__progress-fill');
            
            progressBars.forEach(bar => {
                const currentWidth = bar.style.width;
                bar.style.width = '0%';
                
                // Trigger reflow
                bar.offsetHeight;
                
                // Animate to actual width
                setTimeout(() => {
                    bar.style.width = currentWidth;
                }, 100);
            });
        }

        /**
         * Start automatic updates
         */
        startAutoUpdate() {
            // Clear any existing timer
            this.stopAutoUpdate();
            
            // Set up new timer
            this.updateTimer = setInterval(() => {
                this.fetchAndUpdateData();
            }, DEFAULTS.UPDATE_INTERVAL);
        }

        /**
         * Stop automatic updates
         */
        stopAutoUpdate() {
            if (this.updateTimer) {
                clearInterval(this.updateTimer);
                this.updateTimer = null;
            }
        }

        /**
         * Handle errors
         * @param {Error} error - Error object
         */
        handleError(error) {
            this.element.classList.remove('loading');
            this.element.classList.add('error');
            
            // Log error for debugging
            console.error('Funding banner error:', error);
            
            // Use fallback data
            this.updateBannerDisplay({
                currentAmount: this.config.currentAmount,
                goalAmount: this.config.goalAmount,
                campaignName: this.config.campaignName
            });
        }

        /**
         * Destroy the banner instance
         */
        destroy() {
            this.stopAutoUpdate();
            this.element.fundingBannerInstance = null;
        }
    }

    // Auto-initialize banners on DOMContentLoaded
    document.addEventListener('DOMContentLoaded', function() {
        // Find all funding banners without API endpoints (static banners)
        const staticBanners = document.querySelectorAll('.robjects-funding-banner:not([data-api-endpoint])');
        
        staticBanners.forEach(banner => {
            if (!banner.fundingBannerInstance) {
                // Just animate the progress bar for static banners
                const instance = new FundingBanner(banner, {});
                instance.animateProgressBar();
            }
        });
    });

})();