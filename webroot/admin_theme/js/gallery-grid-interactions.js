/**
 * Gallery Grid Interactions
 * Handles preview hover effects, slideshow initialization, and PhotoSwipe integration
 */
(function() {
    'use strict';

    let config = {};

    /**
     * Initialize gallery grid interactions
     */
    function init(options = {}) {
        config = Object.assign({
            previewSelector: '.gallery-preview-overlay',
            playButtonSelector: '.gallery-play-button',
            galleryCardSelector: '.card',
            photoGallerySelector: '.photo-gallery',
            galleryItemSelector: '.gallery-item',
            popoverSelector: '[data-bs-toggle="popover"]',
            enableHoverEffects: true,
            enablePhotoSwipe: true,
            enableLazyLoading: true,
            lazyLoadThreshold: '50px',
            enablePreloading: true
        }, options);

        initializePreviewInteractions(false);
        
        if (config.enableLazyLoading) {
            initializeLazyLoading();
        }
        
        if (config.enablePreloading) {
            initializeImagePreloading();
        }
    }

    /**
     * Initialize preview interactions and effects
     */
    function initializePreviewInteractions(isAjaxLoad = false) {
        if (config.enableHoverEffects) {
            initializeHoverEffects();
        }

        if (config.enablePhotoSwipe && isAjaxLoad) {
            reinitializePhotoSwipe();
        }

        // Initialize Bootstrap popovers for AJAX content
        if (isAjaxLoad) {
            initializePopovers();
        }
    }

    /**
     * Initialize hover effects for gallery previews
     */
    function initializeHoverEffects() {
        const previews = document.querySelectorAll(config.previewSelector);
        
        previews.forEach(container => {
            const playButton = container.querySelector(config.playButtonSelector);
            
            if (!playButton) return;

            // Check if already initialized to prevent duplicate handlers
            if (container.hasAttribute('data-gallery-initialized')) {
                return;
            }
            container.setAttribute('data-gallery-initialized', 'true');

            // Add hover event listeners
            container.addEventListener('mouseenter', function() {
                playButton.style.display = 'block';
            });
            
            container.addEventListener('mouseleave', function() {
                playButton.style.display = 'none';
            });

            // Add click handler for slideshow
            container.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const galleryId = extractGalleryId(container);
                if (galleryId) {
                    startGallerySlideshow(galleryId);
                }
            });
        });
    }

    /**
     * Extract gallery ID from container onclick attribute or data
     */
    function extractGalleryId(container) {
        // Try to get from onclick attribute first (legacy support)
        const onclick = container.getAttribute('onclick');
        if (onclick) {
            const match = onclick.match(/startGallerySlideshow\(['"]([^'"]+)['"]\)/);
            if (match) {
                return match[1];
            }
        }

        // Try to get from data attribute
        return container.dataset.galleryId;
    }

    /**
     * Start gallery slideshow by finding and clicking first image
     */
    function startGallerySlideshow(galleryId) {
        let galleryContainer = null;
        
        // Look for the gallery container that contains this galleryId
        const galleryCards = document.querySelectorAll(config.galleryCardSelector);
        
        for (let card of galleryCards) {
            // Look for preview overlay with matching data-gallery-id
            const previewOverlay = card.querySelector(`[data-gallery-id="${galleryId}"]`);
            if (previewOverlay) {
                // Find the hidden photo gallery in the same card
                galleryContainer = card.querySelector(config.photoGallerySelector);
                break;
            }
        }
        
        // If we found the gallery, try to start PhotoSwipe manually
        if (galleryContainer) {
            const firstImageLink = galleryContainer.querySelector(`${config.galleryItemSelector} a[href]`);
            if (firstImageLink) {
                console.log('Starting gallery slideshow for:', galleryId);
                
                // Check if PhotoSwipe is available
                if (window.PhotoSwipe) {
                    // Initialize PhotoSwipe manually for this hidden gallery
                    setTimeout(() => {
                        initializeHiddenGallery(galleryContainer, 0);
                    }, 50);
                    return;
                } else {
                    console.warn('PhotoSwipe not available, trying click fallback');
                    // Fallback: try clicking the link
                    setTimeout(() => {
                        firstImageLink.click();
                    }, 50);
                    return;
                }
            } else {
                console.warn('No image links found in gallery:', galleryId);
            }
        } else {
            console.warn('Gallery container not found for:', galleryId);
        }

        // Fallback: redirect to view page
        const realGalleryId = galleryId.replace('gallery-', '');
        const viewUrl = `/admin/image-galleries/view/${realGalleryId}`;
        console.log('Falling back to view page:', viewUrl);
        window.location.href = viewUrl;
    }

    /**
     * Reinitialize PhotoSwipe galleries for AJAX-loaded content
     */
    function reinitializePhotoSwipe() {
        setTimeout(() => {
            if (window.PhotoSwipeGallery) {
                console.log('Re-initializing PhotoSwipe galleries after AJAX load...');
                try {
                    const ajaxPhotoSwipeInstance = new PhotoSwipeGallery({
                        gallerySelector: config.photoGallerySelector,
                        itemSelector: config.galleryItemSelector
                    });
                } catch (error) {
                    console.error('Error reinitializing PhotoSwipe:', error);
                }
            } else {
                console.warn('PhotoSwipeGallery not available for AJAX content');
            }
        }, 100);
    }

    /**
     * Initialize Bootstrap popovers
     */
    function initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll(config.popoverSelector));
        popoverTriggerList.map(function (popoverTriggerEl) {
            // Dispose existing popover to avoid duplicates
            const existingPopover = bootstrap.Popover.getInstance(popoverTriggerEl);
            if (existingPopover) {
                existingPopover.dispose();
            }
            
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    /**
     * Initialize lazy loading for gallery images
     */
    function initializeLazyLoading() {
        // Only proceed if IntersectionObserver is supported
        if (!('IntersectionObserver' in window)) {
            console.warn('IntersectionObserver not supported, skipping lazy loading');
            return;
        }

        const lazyImages = document.querySelectorAll('.gallery-preview-image[data-src], .gallery-image[data-src]');
        
        if (lazyImages.length === 0) {
            // If no data-src attributes, look for images that could benefit from lazy loading
            initializeLazyLoadingForExistingImages();
            return;
        }

        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    const src = img.dataset.src;
                    
                    if (src) {
                        // Create a new image to preload
                        const newImg = new Image();
                        newImg.onload = () => {
                            img.src = src;
                            img.classList.remove('lazy-loading');
                            img.classList.add('lazy-loaded');
                        };
                        newImg.onerror = () => {
                            img.classList.remove('lazy-loading');
                            img.classList.add('lazy-error');
                            console.warn('Failed to load lazy image:', src);
                        };
                        newImg.src = src;
                        
                        // Add loading class
                        img.classList.add('lazy-loading');
                        
                        // Remove data-src to prevent reloading
                        delete img.dataset.src;
                    }
                    
                    observer.unobserve(img);
                }
            });
        }, {
            root: null,
            rootMargin: config.lazyLoadThreshold,
            threshold: 0.1
        });

        lazyImages.forEach(img => {
            imageObserver.observe(img);
        });
        
        console.log(`Lazy loading initialized for ${lazyImages.length} images`);
    }

    /**
     * Initialize lazy loading for existing images (convert to lazy loading)
     */
    function initializeLazyLoadingForExistingImages() {
        const galleryImages = document.querySelectorAll('.gallery-preview-image, .gallery-image');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Only process if not already processed
                    if (!img.classList.contains('lazy-processed')) {
                        img.classList.add('lazy-processed');
                        
                        // Add fade-in effect
                        img.style.opacity = '0';
                        img.style.transition = 'opacity 0.3s ease';
                        
                        // Trigger reflow and fade in
                        setTimeout(() => {
                            img.style.opacity = '1';
                        }, 50);
                    }
                    
                    observer.unobserve(img);
                }
            });
        }, {
            root: null,
            rootMargin: config.lazyLoadThreshold,
            threshold: 0.1
        });

        galleryImages.forEach(img => {
            imageObserver.observe(img);
        });
    }

    /**
     * Initialize image preloading for better performance
     */
    function initializeImagePreloading() {
        // Preload images that are likely to be viewed next
        const galleryItems = document.querySelectorAll(config.galleryItemSelector);
        
        galleryItems.forEach((item, index) => {
            const link = item.querySelector('a[href]');
            if (link && index < 6) { // Preload first 6 images
                const img = new Image();
                img.src = link.href;
                // Store preloaded images to prevent garbage collection
                if (!window.preloadedGalleryImages) {
                    window.preloadedGalleryImages = [];
                }
                window.preloadedGalleryImages.push(img);
            }
        });
    }

    /**
     * Initialize PhotoSwipe for a hidden gallery
     */
    function initializeHiddenGallery(galleryContainer, startIndex = 0) {
        if (!window.PhotoSwipe) {
            console.error('PhotoSwipe not available');
            return;
        }

        // Parse gallery items like PhotoSwipeGallery does
        const items = [];
        const itemElements = galleryContainer.querySelectorAll(config.galleryItemSelector);
        
        itemElements.forEach((itemEl) => {
            const link = itemEl.querySelector('a');
            const img = itemEl.querySelector('img');
            
            if (link && img) {
                const item = {
                    src: link.href,
                    width: parseInt(link.dataset.pswpWidth || 0) || 0,
                    height: parseInt(link.dataset.pswpHeight || 0) || 0,
                    alt: img.alt || '',
                    title: link.dataset.title || img.alt || '',
                    caption: link.dataset.caption || ''
                };
                items.push(item);
            }
        });

        if (items.length === 0) {
            console.warn('No gallery items found');
            return;
        }

        console.log(`Opening PhotoSwipe with ${items.length} items, starting at index ${startIndex}`);

        // Create PhotoSwipe instance
        const photoswipe = new PhotoSwipe({
            dataSource: items,
            index: startIndex,
            bgOpacity: 0.8,
            showHideOpacity: true,
            showAnimationDuration: 333,
            hideAnimationDuration: 333,
            spacing: 0.1,
            padding: { top: 60, bottom: 60, left: 40, right: 40 }
        });

        // Initialize and open
        photoswipe.init();
    }

    /**
     * Preload adjacent images for better PhotoSwipe performance
     */
    function preloadAdjacentImages(currentIndex, items) {
        const preloadIndexes = [currentIndex - 1, currentIndex + 1];
        
        preloadIndexes.forEach(index => {
            if (index >= 0 && index < items.length) {
                const img = new Image();
                img.src = items[index].src;
            }
        });
    }

    /**
     * Clean up event listeners (useful for AJAX reloads)
     */
    function cleanup() {
        // Remove existing event listeners by cloning and replacing elements
        const previews = document.querySelectorAll(config.previewSelector);
        previews.forEach(container => {
            const newContainer = container.cloneNode(true);
            container.parentNode.replaceChild(newContainer, container);
        });
    }

    /**
     * Refresh interactions after content change (AJAX callback)
     */
    function refresh() {
        cleanup();
        initializePreviewInteractions(true);
    }

    // Auto-initialize on DOM ready
    document.addEventListener('DOMContentLoaded', function() {
        // Check if we're on a gallery grid page
        if (document.querySelector('.gallery-preview-overlay')) {
            init();
        }
    });

    // Export for manual initialization and control
    window.GalleryGridInteractions = {
        init: init,
        refresh: refresh,
        startGallerySlideshow: startGallerySlideshow,
        initializePreviewInteractions: initializePreviewInteractions,
        initializeLazyLoading: initializeLazyLoading,
        preloadAdjacentImages: preloadAdjacentImages
    };

    // Export global function for backward compatibility
    window.startGallerySlideshow = startGallerySlideshow;

})();