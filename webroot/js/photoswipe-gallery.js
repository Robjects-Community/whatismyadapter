/**
 * Shared PhotoSwipe Gallery Component
 * Unified implementation for both AdminTheme and DefaultTheme
 * Provides beautiful lightbox galleries with slideshow functionality
 */

class PhotoSwipeGallery {
    constructor(options = {}) {
        // Detect current theme
        const theme = this.detectTheme();
        
        this.options = {
            gallerySelector: '.photo-gallery',
            itemSelector: '.gallery-item',
            // PhotoSwipe options
            bgOpacity: 0.8,
            showHideOpacity: true,
            showAnimationDuration: 333,
            hideAnimationDuration: 333,
            // Slideshow options
            slideshowInterval: 4000, // 4 seconds
            enableSlideshow: true,
            // Theme-specific defaults
            ...this.getThemeDefaults(theme),
            // User overrides
            ...options
        };
        
        this.theme = theme;
        this.galleries = [];
        this.slideshowTimer = null;
        this.isPlaying = false;
        this.init();
    }

    /**
     * Detect current theme from page context
     */
    detectTheme() {
        // Check for admin theme indicators
        if (document.querySelector('.admin-gallery') || 
            document.querySelector('[class*="admin"]') ||
            window.location.pathname.includes('/admin/')) {
            return 'admin';
        }
        
        // Check for default theme indicators
        if (document.querySelector('.default-gallery')) {
            return 'default';
        }
        
        // Fallback detection
        return document.body.classList.contains('admin') ? 'admin' : 'default';
    }

    /**
     * Get theme-specific default options
     */
    getThemeDefaults(theme) {
        const defaults = {
            admin: {
                // Admin theme: More conservative padding for better management UX
                padding: { top: 60, bottom: 60, left: 40, right: 40 },
                spacing: 0.1,
                initialZoomLevel: 'fit',
                secondaryZoomLevel: 'fit',
                enableBulkActions: true,
                showImageCount: true
            },
            default: {
                // Default theme: Generous padding for optimal viewing experience
                padding: { top: 80, bottom: 80, left: 60, right: 60 },
                spacing: 0.1,
                initialZoomLevel: 'fit',
                secondaryZoomLevel: 'fit',
                enableBulkActions: false,
                showImageCount: false
            }
        };

        return defaults[theme] || defaults.default;
    }

    async init() {
        try {
            // Load PhotoSwipe dynamically from CDN
            await this.loadPhotoSwipe();
            this.initializeGalleries();
        } catch (error) {
            console.error('Failed to initialize PhotoSwipe Gallery:', error);
            // Fall back to basic click handlers that open images in new tabs
            this.initializeFallbackGalleries();
        }
    }
    
    initializeFallbackGalleries() {
        console.log('PhotoSwipe Gallery: Initializing fallback mode (images will open in new tabs)');
        const galleryElements = document.querySelectorAll(this.options.gallerySelector);
        
        galleryElements.forEach((galleryEl) => {
            const itemElements = galleryEl.querySelectorAll(this.options.itemSelector);
            
            itemElements.forEach((itemEl) => {
                const link = itemEl.querySelector('a');
                if (link) {
                    link.addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        // Open image in new tab as fallback
                        window.open(link.href, '_blank');
                    });
                }
            });
        });
    }

    async loadPhotoSwipe() {
        // Load CSS
        if (!document.querySelector('link[href*="photoswipe"]')) {
            const cssLink = document.createElement('link');
            cssLink.rel = 'stylesheet';
            cssLink.href = 'https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/photoswipe.min.css';
            cssLink.onerror = () => {
                console.warn('PhotoSwipe Gallery: Failed to load PhotoSwipe CSS from CDN');
            };
            document.head.appendChild(cssLink);
        }

        // Load JS
        if (!window.PhotoSwipe) {
            console.log('PhotoSwipe Gallery: Loading PhotoSwipe library...');
            
            // Use reliable CDNJS source
            const src = 'https://cdnjs.cloudflare.com/ajax/libs/photoswipe/5.4.2/umd/photoswipe.umd.min.js';
            
            try {
                await this.loadScript(src);
                console.log('PhotoSwipe Gallery: PhotoSwipe library loaded successfully');
            } catch (error) {
                console.error('PhotoSwipe Gallery: Failed to load PhotoSwipe library:', error);
                throw new Error('Failed to load PhotoSwipe from CDN');
            }
        } else {
            console.log('PhotoSwipe Gallery: PhotoSwipe library already loaded');
        }
    }
    
    async loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = () => {
                // Double check PhotoSwipe is actually available
                if (window.PhotoSwipe) {
                    resolve();
                } else {
                    reject(new Error('PhotoSwipe not available after script load'));
                }
            };
            script.onerror = () => reject(new Error(`Failed to load script: ${src}`));
            
            document.head.appendChild(script);
        });
    }

    initializeGalleries() {
        const galleryElements = document.querySelectorAll(this.options.gallerySelector);
        console.log(`PhotoSwipe Gallery: Found ${galleryElements.length} galleries (${this.theme} theme)`);
        
        galleryElements.forEach((galleryEl, index) => {
            const items = this.parseGalleryItems(galleryEl);
            console.log(`PhotoSwipe Gallery: Gallery ${index} has ${items.length} items`);
            if (items.length > 0) {
                this.setupGalleryClickHandlers(galleryEl, items, index);
                console.log(`PhotoSwipe Gallery: Set up click handlers for gallery ${index}`);
            }
        });
    }

    parseGalleryItems(galleryEl) {
        const items = [];
        const itemElements = galleryEl.querySelectorAll(this.options.itemSelector);
        
        itemElements.forEach((itemEl) => {
            const link = itemEl.querySelector('a');
            const img = itemEl.querySelector('img');
            
            if (link && img) {
                // Get dimensions from data attributes or use auto-detection
                let width = parseInt(link.dataset.pswpWidth || link.dataset.width || 0);
                let height = parseInt(link.dataset.pswpHeight || link.dataset.height || 0);
                
                // If no dimensions provided, use auto-detection (PhotoSwipe 5 feature)
                if (!width || !height) {
                    width = 0;  // PhotoSwipe will auto-detect
                    height = 0; // PhotoSwipe will auto-detect
                }
                
                const item = {
                    src: link.href,
                    width: width,
                    height: height,
                    alt: img.alt || '',
                    title: link.dataset.title || img.alt || '',
                    caption: link.dataset.caption || '',
                    element: itemEl
                };
                
                console.log(`Image ${img.alt || 'untitled'}: ${item.width || 'auto'}x${item.height || 'auto'} (${this.theme} theme)`);
                
                items.push(item);
            }
        });
        
        return items;
    }

    setupGalleryClickHandlers(galleryEl, items, galleryIndex) {
        const itemElements = galleryEl.querySelectorAll(this.options.itemSelector);
        
        itemElements.forEach((itemEl, itemIndex) => {
            const link = itemEl.querySelector('a');
            if (link) {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    // Ensure PhotoSwipe is loaded before opening
                    if (window.PhotoSwipe) {
                        this.openGallery(items, itemIndex);
                    } else {
                        console.error('PhotoSwipe not loaded, falling back to simple image view');
                        // Fallback: open image in new tab
                        window.open(link.href, '_blank');
                    }
                });
            }
        });
    }

    openGallery(items, startIndex = 0) {
        const options = {
            dataSource: items,
            index: startIndex,
            bgOpacity: this.options.bgOpacity,
            showHideOpacity: this.options.showHideOpacity,
            showAnimationDuration: this.options.showAnimationDuration,
            hideAnimationDuration: this.options.hideAnimationDuration,
            
            // Enable zoom
            zoom: true,
            
            // Theme-specific options
            spacing: this.options.spacing,
            padding: this.options.padding,
            initialZoomLevel: this.options.initialZoomLevel,
            secondaryZoomLevel: this.options.secondaryZoomLevel,
            maxZoomLevel: 2,
            
            // Allow pan
            allowPanToNext: true,
            
            // Close on vertical drag
            closeOnVerticalDrag: true,
            
            // Preload images
            preload: [1, 3],
            
            // User interaction settings
            imageClickAction: 'zoom-or-close',
            tapAction: 'toggle-controls',
            doubleTapAction: 'zoom',
            
            // Disable built-in counter to prevent duplicates
            counter: false,
        };

        const gallery = new PhotoSwipe(options);
        this.currentGallery = gallery;
        
        // Add custom event handlers
        this.addCustomEventHandlers(gallery);
        
        gallery.init();
        
        // Auto-start slideshow after gallery opens if enabled
        if (this.options.enableSlideshow) {
            // Use openingAnimationEnd instead of afterInit for better timing
            gallery.on('openingAnimationEnd', () => {
                console.log('PhotoSwipe Gallery: Auto-starting slideshow...');
                
                // Show auto-start notification
                this.showSlideshowNotification(gallery, 'Slideshow starting automatically...');
                
                setTimeout(() => {
                    this.startSlideshow(gallery);
                    // Update UI to show slideshow is playing
                    const playBtn = gallery.element.querySelector('.pswp__button--play');
                    const pauseBtn = gallery.element.querySelector('.pswp__button--pause');
                    if (playBtn && pauseBtn) {
                        playBtn.style.display = 'none';
                        pauseBtn.style.display = 'block';
                    }
                    
                    // Show playing notification briefly
                    this.showSlideshowNotification(gallery, 'Slideshow playing - Press spacebar to pause');
                    setTimeout(() => this.hideSlideshowNotification(gallery), 3000);
                }, 500);
            });
        }
    }

    addCustomEventHandlers(gallery) {
        // Show image title/caption in UI
        gallery.on('uiRegister', () => {
            // Custom caption
            gallery.ui.registerElement({
                name: 'custom-caption',
                className: 'pswp__custom-caption',
                appendTo: 'root',
                onInit: (el, pswp) => {
                    gallery.on('change', () => {
                        this.updateCaption(el, gallery);
                    });
                    // Set initial caption
                    this.updateCaption(el, gallery);
                }
            });

            // Slideshow controls
            if (this.options.enableSlideshow) {
                gallery.ui.registerElement({
                    name: 'slideshow-controls',
                    className: 'pswp__slideshow-controls',
                    appendTo: 'top-bar',
                    onInit: (el, pswp) => {
                        el.innerHTML = `
                            <button class="pswp__button pswp__button--play" title="Play slideshow">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                            <button class="pswp__button pswp__button--pause" title="Pause slideshow" style="display: none;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                </svg>
                            </button>
                        `;

                        const playBtn = el.querySelector('.pswp__button--play');
                        const pauseBtn = el.querySelector('.pswp__button--pause');

                        playBtn.addEventListener('click', () => {
                            this.startSlideshow(gallery);
                            playBtn.style.display = 'none';
                            pauseBtn.style.display = 'block';
                            this.showSlideshowNotification(gallery, 'Slideshow playing');
                            setTimeout(() => this.hideSlideshowNotification(gallery), 2000);
                        });

                        pauseBtn.addEventListener('click', () => {
                            this.stopSlideshow();
                            pauseBtn.style.display = 'none';
                            playBtn.style.display = 'block';
                            this.showSlideshowNotification(gallery, 'Slideshow paused');
                            setTimeout(() => this.hideSlideshowNotification(gallery), 2000);
                        });
                    }
                });

                // Progress indicator
                gallery.ui.registerElement({
                    name: 'slideshow-progress',
                    className: 'pswp__slideshow-progress',
                    appendTo: 'root',
                    onInit: (el, pswp) => {
                        el.innerHTML = '<div class="pswp__progress-bar"></div>';
                        this.progressElement = el.querySelector('.pswp__progress-bar');
                    }
                });
            }

            // Image counter (theme-aware)
            if (this.options.showImageCount) {
                gallery.ui.registerElement({
                    name: 'image-counter',
                    className: 'pswp__image-counter',
                    appendTo: 'top-bar',
                    onInit: (el, pswp) => {
                        gallery.on('change', () => {
                            el.textContent = `${gallery.currIndex + 1} / ${gallery.getNumItems()}`;
                        });
                        // Set initial counter
                        el.textContent = `${gallery.currIndex + 1} / ${gallery.getNumItems()}`;
                    }
                });
            }
        });

        // Handle slideshow cleanup
        gallery.on('destroy', () => {
            this.stopSlideshow();
        });

        // Handle manual navigation during slideshow
        gallery.on('change', () => {
            if (this.isPlaying) {
                this.resetSlideshowTimer(gallery);
            }
        });

        // Add keyboard shortcuts
        gallery.on('keydown', (e) => {
            const event = e.originalEvent;
            if (!event) return;
            
            switch (event.keyCode) {
                case 37: // Left arrow
                    gallery.prev();
                    break;
                case 39: // Right arrow
                    gallery.next();
                    break;
                case 27: // Escape
                    gallery.close();
                    break;
                case 32: // Spacebar - toggle slideshow
                    e.preventDefault();
                    this.toggleSlideshow(gallery);
                    break;
            }
        });
    }

    updateCaption(el, gallery) {
        const currSlideElement = gallery.currSlide?.data;
        if (currSlideElement) {
            let captionHTML = '';
            if (currSlideElement.title) {
                captionHTML += `<div class="pswp__caption-title">${currSlideElement.title}</div>`;
            }
            // Only show caption in admin theme or if explicitly enabled
            if ((this.theme === 'admin' || this.options.showCaptions) && currSlideElement.caption) {
                captionHTML += `<div class="pswp__caption-description">${currSlideElement.caption}</div>`;
            }
            el.innerHTML = captionHTML;
        }
    }

    startSlideshow(gallery) {
        console.log('PhotoSwipe Gallery: Starting slideshow...');
        this.isPlaying = true;
        this.currentGallery = gallery;
        this.resetSlideshowTimer(gallery);
        this.startProgressAnimation();
    }

    stopSlideshow() {
        this.isPlaying = false;
        if (this.slideshowTimer) {
            clearTimeout(this.slideshowTimer);
            this.slideshowTimer = null;
        }
        this.stopProgressAnimation();
    }

    toggleSlideshow(gallery) {
        if (this.isPlaying) {
            this.stopSlideshow();
            // Update UI buttons
            const playBtn = gallery.element.querySelector('.pswp__button--play');
            const pauseBtn = gallery.element.querySelector('.pswp__button--pause');
            if (playBtn && pauseBtn) {
                pauseBtn.style.display = 'none';
                playBtn.style.display = 'block';
            }
            this.showSlideshowNotification(gallery, 'Slideshow paused - Press spacebar to resume');
            setTimeout(() => this.hideSlideshowNotification(gallery), 2500);
        } else {
            this.startSlideshow(gallery);
            // Update UI buttons
            const playBtn = gallery.element.querySelector('.pswp__button--play');
            const pauseBtn = gallery.element.querySelector('.pswp__button--pause');
            if (playBtn && pauseBtn) {
                playBtn.style.display = 'none';
                pauseBtn.style.display = 'block';
            }
            this.showSlideshowNotification(gallery, 'Slideshow playing - Press spacebar to pause');
            setTimeout(() => this.hideSlideshowNotification(gallery), 2500);
        }
    }

    resetSlideshowTimer(gallery) {
        if (this.slideshowTimer) {
            clearTimeout(this.slideshowTimer);
        }
        
        this.slideshowTimer = setTimeout(() => {
            if (this.isPlaying && gallery) {
                // Move to next slide, or loop to first if at end
                if (gallery.currIndex < gallery.getNumItems() - 1) {
                    gallery.next();
                } else {
                    gallery.goTo(0);
                }
            }
        }, this.options.slideshowInterval);
    }

    startProgressAnimation() {
        if (this.progressElement) {
            this.progressElement.style.animation = `slideshow-progress ${this.options.slideshowInterval}ms linear`;
        }
    }

    stopProgressAnimation() {
        if (this.progressElement) {
            this.progressElement.style.animation = 'none';
        }
    }
    
    showSlideshowNotification(gallery, message) {
        // Remove existing notification
        this.hideSlideshowNotification(gallery);
        
        // Create notification element
        const notification = document.createElement('div');
        notification.className = 'pswp__slideshow-notification';
        notification.textContent = message;
        notification.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 500;
            z-index: 10001;
            backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        `;
        
        gallery.element.appendChild(notification);
        
        // Fade in
        requestAnimationFrame(() => {
            notification.style.opacity = '1';
        });
        
        this.currentNotification = notification;
    }
    
    hideSlideshowNotification(gallery) {
        if (this.currentNotification) {
            this.currentNotification.style.opacity = '0';
            setTimeout(() => {
                if (this.currentNotification && this.currentNotification.parentNode) {
                    this.currentNotification.parentNode.removeChild(this.currentNotification);
                }
                this.currentNotification = null;
            }, 300);
        }
    }

    // Static method to initialize galleries
    static init(options = {}) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                new PhotoSwipeGallery(options);
            });
        } else {
            new PhotoSwipeGallery(options);
        }
    }

    // Method to refresh galleries (useful for dynamic content)
    refresh() {
        this.initializeGalleries();
    }

    // Get current theme
    getTheme() {
        return this.theme;
    }

    // Get theme-specific configuration
    getThemeConfig() {
        return this.getThemeDefaults(this.theme);
    }
}

// Auto-initialize if galleries are present
document.addEventListener('DOMContentLoaded', () => {
    const galleries = document.querySelectorAll('.photo-gallery');
    if (galleries.length > 0) {
        console.log(`PhotoSwipe Gallery: Found ${galleries.length} galleries, initializing...`);
        PhotoSwipeGallery.init();
    } else {
        console.log('PhotoSwipe Gallery: No galleries found on page');
    }
});

// Also initialize immediately if DOM is already loaded
if (document.readyState !== 'loading') {
    const galleries = document.querySelectorAll('.photo-gallery');
    if (galleries.length > 0) {
        console.log(`PhotoSwipe Gallery: Found ${galleries.length} galleries, initializing immediately...`);
        PhotoSwipeGallery.init();
    }
}

// Export for manual initialization
window.PhotoSwipeGallery = PhotoSwipeGallery;