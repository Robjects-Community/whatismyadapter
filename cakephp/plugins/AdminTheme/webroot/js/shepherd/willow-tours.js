/**
 * WillowCMS Admin Interface Guided Tours
 * 
 * This file implements comprehensive guided tours using Shepherd.js
 * for demonstrating all admin interface features to prospective clients.
 */

(function() {
    'use strict';
    
    // Tour configuration
    const TOUR_CONFIG = {
        defaultStepOptions: {
            scrollTo: { behavior: 'smooth', block: 'center' },
            cancelIcon: {
                enabled: true
            },
            classes: 'willow-tour-step'
        },
        useModalOverlay: true
    };

    // Tour storage
    let currentTour = null;
    let availableTours = {};

    /**
     * Initialize the tour system
     */
    function initTours() {
        // Check if Shepherd is loaded
        if (typeof Shepherd === 'undefined') {
            console.error('Shepherd.js is not loaded. Tours will not be available.');
            return;
        }

        // Initialize tour button
        createTourButton();
        
        // Register all tours
        registerTours();
        
        // Auto-start tour if requested
        checkAutoStart();
        
        // Listen for page changes (for SPAs or dynamic content)
        setupPageChangeListeners();
    }

    /**
     * Create the "Start Tour" button in the admin interface
     */
    function createTourButton() {
        // Create tour button container
        const tourButton = document.createElement('div');
        tourButton.innerHTML = `
            <div class="dropdown willow-tour-dropdown">
                <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" 
                        id="willowTourDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-route me-1"></i> Tour
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="willowTourDropdown" id="tourOptions">
                    <li><h6 class="dropdown-header">Available Tours</h6></li>
                </ul>
            </div>
        `;
        
        // Add to header actions area
        const headerActions = document.querySelector('.admin-header .d-flex.align-items-center.ms-auto');
        if (headerActions) {
            headerActions.insertBefore(tourButton, headerActions.firstChild);
        }
        
        // Add CSS for positioning
        addTourButtonStyles();
    }

    /**
     * Add CSS styles for tour elements
     */
    function addTourButtonStyles() {
        const style = document.createElement('style');
        style.textContent = `
            .willow-tour-dropdown {
                margin-right: 1rem;
            }
            
            .willow-tour-step {
                max-width: 400px;
            }
            
            .willow-tour-step .shepherd-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 8px 8px 0 0;
                padding: 12px 16px;
                font-weight: 600;
            }
            
            .willow-tour-step .shepherd-text {
                padding: 16px;
                line-height: 1.5;
            }
            
            .willow-tour-step .shepherd-footer {
                padding: 12px 16px;
                background: #f8f9fa;
                border-top: 1px solid #dee2e6;
            }
            
            .willow-tour-highlight {
                position: relative;
                z-index: 9999;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.3);
                border-radius: 4px;
                transition: box-shadow 0.3s ease;
            }
            
            .shepherd-modal-overlay-container {
                z-index: 9998;
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Register all available tours
     */
    function registerTours() {
        // Dashboard Tours
        availableTours.dashboard = new DashboardTour();
        availableTours.analytics = new AnalyticsTour();
        
        // Products Tours
        availableTours.products = new ProductsTour();
        availableTours.productsCreate = new ProductsCreateTour();
        availableTours.productsPending = new ProductsPendingTour();
        
        // Content Tours
        availableTours.posts = new PostsTour();
        availableTours.pages = new PagesTour();
        availableTours.tags = new TagsTour();
        availableTours.images = new ImagesTour();
        availableTours.galleries = new GalleriesTour();
        availableTours.comments = new CommentsTour();
        
        // User Management Tours
        availableTours.users = new UsersTour();
        
        // Administration Tours
        availableTours.queues = new QueuesTour();
        availableTours.costAnalysis = new CostAnalysisTour();
        availableTours.settings = new SettingsTour();
        availableTours.emailTemplates = new EmailTemplatesTour();
        availableTours.slugs = new SlugsTour();
        
        // System Tours
        availableTours.cache = new CacheTour();
        availableTours.blockedIps = new BlockedIpsTour();
        availableTours.systemLogs = new SystemLogsTour();
        
        // AI Features Tours (debug mode only)
        if (window.willowDebugMode) {
            availableTours.aiPrompts = new AiPromptsTour();
            availableTours.i18n = new InternationalisationTour();
        }
        
        // Overview Tours
        availableTours.quickOverview = new QuickOverviewTour();
        availableTours.completeWalkthrough = new CompleteWalkthroughTour();
        
        // Update tour dropdown
        updateTourDropdown();
    }

    /**
     * Update the tour dropdown with available tours
     */
    function updateTourDropdown() {
        const tourOptions = document.getElementById('tourOptions');
        if (!tourOptions) return;
        
        // Clear existing options (keep header)
        const header = tourOptions.querySelector('.dropdown-header');
        tourOptions.innerHTML = '';
        tourOptions.appendChild(header);
        
        // Get current page context
        const currentController = getCurrentController();
        const currentAction = getCurrentAction();
        
        // Add contextual tour first
        const contextualTour = getContextualTour(currentController, currentAction);
        if (contextualTour) {
            const li = document.createElement('li');
            li.innerHTML = `<a class="dropdown-item" href="#" onclick="window.WillowTours.startTour('${contextualTour}')">
                <i class="fas fa-play-circle me-2"></i>${getTourTitle(contextualTour)}
            </a>`;
            tourOptions.appendChild(li);
            
            // Add separator
            const separator = document.createElement('li');
            separator.innerHTML = '<hr class="dropdown-divider">';
            tourOptions.appendChild(separator);
        }
        
        // Add overview tours
        const overviewTours = [
            { key: 'quickOverview', title: 'Quick Overview (5 min)', icon: 'fas fa-clock' },
            { key: 'completeWalkthrough', title: 'Complete Tour (15 min)', icon: 'fas fa-route' }
        ];
        
        overviewTours.forEach(tour => {
            const li = document.createElement('li');
            li.innerHTML = `<a class="dropdown-item" href="#" onclick="window.WillowTours.startTour('${tour.key}')">
                <i class="${tour.icon} me-2"></i>${tour.title}
            </a>`;
            tourOptions.appendChild(li);
        });
        
        // Add category-specific tours
        const categories = {
            'Dashboard': ['dashboard', 'analytics'],
            'Products': ['products', 'productsCreate', 'productsPending'],
            'Content': ['posts', 'pages', 'tags', 'images', 'galleries', 'comments'],
            'Administration': ['users', 'queues', 'settings', 'emailTemplates', 'slugs'],
            'System': ['cache', 'blockedIps', 'systemLogs']
        };
        
        if (window.willowDebugMode) {
            categories['AI Features'] = ['aiPrompts', 'i18n'];
        }
        
        Object.entries(categories).forEach(([category, tours]) => {
            const separator = document.createElement('li');
            separator.innerHTML = '<hr class="dropdown-divider">';
            tourOptions.appendChild(separator);
            
            const categoryHeader = document.createElement('li');
            categoryHeader.innerHTML = `<h6 class="dropdown-header">${category}</h6>`;
            tourOptions.appendChild(categoryHeader);
            
            tours.forEach(tourKey => {
                if (availableTours[tourKey]) {
                    const li = document.createElement('li');
                    li.innerHTML = `<a class="dropdown-item" href="#" onclick="window.WillowTours.startTour('${tourKey}')">
                        <i class="fas fa-info-circle me-2"></i>${getTourTitle(tourKey)}
                    </a>`;
                    tourOptions.appendChild(li);
                }
            });
        });
    }

    /**
     * Get the appropriate tour for the current page context
     */
    function getContextualTour(controller, action) {
        const contextMap = {
            'PageViews': 'analytics',
            'AiMetrics': 'dashboard',
            'Products': action === 'pendingReview' ? 'productsPending' : 'products',
            'Articles': 'posts',
            'Pages': 'pages',
            'Tags': 'tags',
            'Images': 'images',
            'ImageGalleries': 'galleries',
            'Comments': 'comments',
            'Users': 'users',
            'QueueConfigurations': 'queues',
            'Settings': 'settings',
            'EmailTemplates': 'emailTemplates',
            'Slugs': 'slugs',
            'Cache': 'cache',
            'BlockedIps': 'blockedIps',
            'SystemLogs': 'systemLogs',
            'Aiprompts': 'aiPrompts',
            'Internationalisations': 'i18n'
        };
        
        return contextMap[controller] || null;
    }

    /**
     * Get human-readable title for a tour
     */
    function getTourTitle(tourKey) {
        const titles = {
            'quickOverview': 'Quick Overview',
            'completeWalkthrough': 'Complete Walkthrough',
            'dashboard': 'Dashboard Overview',
            'analytics': 'Analytics Deep Dive',
            'products': 'Products Management',
            'productsCreate': 'Creating Products',
            'productsPending': 'Pending Review Queue',
            'posts': 'Posts & Articles',
            'pages': 'Pages Management',
            'tags': 'Tags & Categories',
            'images': 'Image Library',
            'galleries': 'Image Galleries',
            'comments': 'Comments Moderation',
            'users': 'User Management',
            'queues': 'Queue Configuration',
            'costAnalysis': 'Cost Analysis',
            'settings': 'System Settings',
            'emailTemplates': 'Email Templates',
            'slugs': 'URL Management',
            'cache': 'Cache Management',
            'blockedIps': 'Security Settings',
            'systemLogs': 'System Logs',
            'aiPrompts': 'AI Prompts',
            'i18n': 'Internationalization'
        };
        
        return titles[tourKey] || tourKey;
    }

    /**
     * Get current controller from URL or data attributes
     */
    function getCurrentController() {
        // Check for data attribute first
        const body = document.body;
        if (body.dataset.controller) {
            return body.dataset.controller;
        }
        
        // Parse from URL
        const path = window.location.pathname;
        const segments = path.split('/').filter(s => s.length > 0);
        
        // Look for admin prefix
        if (segments.includes('admin') && segments.length > 1) {
            const adminIndex = segments.indexOf('admin');
            return segments[adminIndex + 1] || null;
        }
        
        return null;
    }

    /**
     * Get current action from URL or data attributes
     */
    function getCurrentAction() {
        const body = document.body;
        if (body.dataset.action) {
            return body.dataset.action;
        }
        
        const path = window.location.pathname;
        const segments = path.split('/').filter(s => s.length > 0);
        
        if (segments.includes('admin') && segments.length > 2) {
            const adminIndex = segments.indexOf('admin');
            return segments[adminIndex + 2] || 'index';
        }
        
        return 'index';
    }

    /**
     * Start a tour by key
     */
    function startTour(tourKey) {
        // Stop current tour if running
        if (currentTour) {
            currentTour.cancel();
        }
        
        const tour = availableTours[tourKey];
        if (!tour) {
            console.error(`Tour '${tourKey}' not found`);
            return;
        }
        
        currentTour = tour;
        tour.start();
        
        // Track tour start
        trackTourEvent('start', tourKey);
    }

    /**
     * Check if a tour should auto-start
     */
    function checkAutoStart() {
        const urlParams = new URLSearchParams(window.location.search);
        const autoTour = urlParams.get('tour');
        
        if (autoTour && availableTours[autoTour]) {
            // Delay to ensure page is fully loaded
            setTimeout(() => startTour(autoTour), 1000);
        }
    }

    /**
     * Setup listeners for dynamic page changes
     */
    function setupPageChangeListeners() {
        // Listen for hash changes
        window.addEventListener('hashchange', () => {
            updateTourDropdown();
        });
        
        // Listen for popstate (back/forward)
        window.addEventListener('popstate', () => {
            updateTourDropdown();
        });
        
        // Observer for dynamic content changes
        const observer = new MutationObserver(() => {
            updateTourDropdown();
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    /**
     * Track tour events for analytics
     */
    function trackTourEvent(event, tourKey, stepIndex = null) {
        // Send to analytics if available
        if (typeof gtag !== 'undefined') {
            gtag('event', 'tour_' + event, {
                'tour_name': tourKey,
                'step_index': stepIndex
            });
        }
        
        // Log for development
        console.log(`Tour Event: ${event} - ${tourKey}${stepIndex ? ` (step ${stepIndex})` : ''}`);
    }

    /**
     * Utility function to wait for an element to be visible
     */
    function waitForElement(selector, timeout = 5000) {
        return new Promise((resolve, reject) => {
            const element = document.querySelector(selector);
            if (element) {
                resolve(element);
                return;
            }
            
            const observer = new MutationObserver(() => {
                const element = document.querySelector(selector);
                if (element) {
                    observer.disconnect();
                    resolve(element);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
            
            setTimeout(() => {
                observer.disconnect();
                reject(new Error(`Element ${selector} not found within ${timeout}ms`));
            }, timeout);
        });
    }

    /**
     * Utility function to highlight an element
     */
    function highlightElement(element) {
        if (element) {
            element.classList.add('willow-tour-highlight');
        }
    }

    /**
     * Utility function to remove highlight from an element
     */
    function removeHighlight(element) {
        if (element) {
            element.classList.remove('willow-tour-highlight');
        }
    }

    // Export public API
    window.WillowTours = {
        init: initTours,
        startTour: startTour,
        getCurrentTour: () => currentTour,
        getAvailableTours: () => Object.keys(availableTours),
        highlightElement: highlightElement,
        removeHighlight: removeHighlight,
        waitForElement: waitForElement
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTours);
    } else {
        initTours();
    }

})();