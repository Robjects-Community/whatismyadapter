class WillowPresentation {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 22;
        this.slidesWrapper = document.getElementById('slidesWrapper');
        this.slides = document.querySelectorAll('.slide');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.fullscreenBtn = document.getElementById('fullscreenBtn');
        this.currentSlideSpan = document.getElementById('currentSlide');
        this.totalSlidesSpan = document.getElementById('totalSlides');
        this.progressFill = document.getElementById('progressFill');
        this.presentationContainer = document.querySelector('.presentation-container');
        
        this.init();
    }

    init() {
        this.updateSlideCounter();
        this.updateProgressBar();
        this.attachEventListeners();
        this.updateNavigationButtons();
        
        // Show first slide
        this.showSlide(0);
    }

    attachEventListeners() {
        // Navigation buttons
        this.prevBtn.addEventListener('click', () => this.previousSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());
        this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());

        // Keyboard navigation
        document.addEventListener('keydown', (e) => this.handleKeydown(e));

        // Touch/swipe navigation for mobile
        let startX = 0;
        let startY = 0;
        
        this.presentationContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
        }, { passive: true });
        
        this.presentationContainer.addEventListener('touchend', (e) => {
            const endX = e.changedTouches[0].clientX;
            const endY = e.changedTouches[0].clientY;
            const deltaX = startX - endX;
            const deltaY = startY - endY;
            
            // Only handle horizontal swipes (ignore vertical scrolling)
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 50) {
                if (deltaX > 0) {
                    this.nextSlide();
                } else {
                    this.previousSlide();
                }
            }
        }, { passive: true });

        // Prevent context menu on long press
        this.presentationContainer.addEventListener('contextmenu', (e) => {
            e.preventDefault();
        });

        // Handle fullscreen change events with better cross-browser support
        const fullscreenEvents = [
            'fullscreenchange',
            'webkitfullscreenchange', 
            'mozfullscreenchange',
            'MSFullscreenChange'
        ];
        
        fullscreenEvents.forEach(event => {
            document.addEventListener(event, () => this.updateFullscreenButton());
        });

        // Handle window resize for responsive adjustments
        window.addEventListener('resize', () => this.handleResize());
    }

    handleKeydown(e) {
        switch(e.key) {
            case 'ArrowLeft':
            case 'ArrowUp':
            case 'PageUp':
                e.preventDefault();
                this.previousSlide();
                break;
            case 'ArrowRight':
            case 'ArrowDown':
            case 'PageDown':
            case ' ': // Spacebar
                e.preventDefault();
                this.nextSlide();
                break;
            case 'Home':
                e.preventDefault();
                this.goToSlide(0);
                break;
            case 'End':
                e.preventDefault();
                this.goToSlide(this.totalSlides - 1);
                break;
            case 'f':
            case 'F':
                if (!e.ctrlKey && !e.altKey && !e.metaKey) {
                    e.preventDefault();
                    this.toggleFullscreen();
                }
                break;
            case 'F11':
                // Let F11 work naturally, but also update our button
                setTimeout(() => this.updateFullscreenButton(), 100);
                break;
            case 'Escape':
                if (this.isFullscreen()) {
                    this.exitFullscreen();
                }
                break;
        }
    }

    nextSlide() {
        if (this.currentSlide < this.totalSlides - 1) {
            this.goToSlide(this.currentSlide + 1);
        }
    }

    previousSlide() {
        if (this.currentSlide > 0) {
            this.goToSlide(this.currentSlide - 1);
        }
    }

    goToSlide(slideIndex) {
        if (slideIndex >= 0 && slideIndex < this.totalSlides) {
            // Remove active class from current slide
            this.slides[this.currentSlide].classList.remove('active');
            
            // Update current slide
            this.currentSlide = slideIndex;
            
            // Add active class to new slide
            this.slides[this.currentSlide].classList.add('active');
            
            // Move slides wrapper
            this.slidesWrapper.style.transform = `translateX(-${this.currentSlide * 100}%)`;
            
            // Update UI
            this.updateSlideCounter();
            this.updateProgressBar();
            this.updateNavigationButtons();
            
            // Trigger slide change animations
            this.animateSlideContent();
        }
    }

    showSlide(slideIndex) {
        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === slideIndex);
        });
    }

    animateSlideContent() {
        const currentSlideElement = this.slides[this.currentSlide];
        const animatableElements = currentSlideElement.querySelectorAll(
            '.feature-card, .skill-card, .stat-item, .service-item, .tool-item'
        );
        
        // Reset animations
        animatableElements.forEach((el, index) => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            
            // Stagger animations
            setTimeout(() => {
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    updateSlideCounter() {
        this.currentSlideSpan.textContent = this.currentSlide + 1;
        this.totalSlidesSpan.textContent = this.totalSlides;
    }

    updateProgressBar() {
        const progress = ((this.currentSlide + 1) / this.totalSlides) * 100;
        this.progressFill.style.width = `${progress}%`;
    }

    updateNavigationButtons() {
        this.prevBtn.disabled = this.currentSlide === 0;
        this.nextBtn.disabled = this.currentSlide === this.totalSlides - 1;
    }

    toggleFullscreen() {
        if (this.isFullscreen()) {
            this.exitFullscreen();
        } else {
            this.enterFullscreen();
        }
    }

    enterFullscreen() {
        const element = document.documentElement; // Use documentElement for better compatibility
        
        try {
            if (element.requestFullscreen) {
                element.requestFullscreen().catch(err => {
                    console.warn('Could not enter fullscreen mode:', err);
                    this.simulateFullscreen();
                });
            } else if (element.webkitRequestFullscreen) {
                element.webkitRequestFullscreen();
            } else if (element.mozRequestFullScreen) {
                element.mozRequestFullScreen();
            } else if (element.msRequestFullscreen) {
                element.msRequestFullscreen();
            } else {
                // Fallback: simulate fullscreen
                this.simulateFullscreen();
            }
        } catch (err) {
            console.warn('Fullscreen API not available, using fallback:', err);
            this.simulateFullscreen();
        }
    }

    exitFullscreen() {
        try {
            if (document.exitFullscreen) {
                document.exitFullscreen().catch(err => {
                    console.warn('Could not exit fullscreen mode:', err);
                    this.exitSimulatedFullscreen();
                });
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            } else {
                this.exitSimulatedFullscreen();
            }
        } catch (err) {
            console.warn('Could not exit fullscreen, using fallback:', err);
            this.exitSimulatedFullscreen();
        }
    }

    simulateFullscreen() {
        this.presentationContainer.classList.add('fullscreen');
        document.body.style.overflow = 'hidden';
        this.updateFullscreenButton();
    }

    exitSimulatedFullscreen() {
        this.presentationContainer.classList.remove('fullscreen');
        document.body.style.overflow = '';
        this.updateFullscreenButton();
    }

    isFullscreen() {
        return !!(document.fullscreenElement || 
                 document.webkitFullscreenElement || 
                 document.mozFullScreenElement || 
                 document.msFullscreenElement ||
                 this.presentationContainer.classList.contains('fullscreen'));
    }

    updateFullscreenButton() {
        const icon = this.fullscreenBtn.querySelector('i');
        if (this.isFullscreen()) {
            icon.className = 'fas fa-compress';
        } else {
            icon.className = 'fas fa-expand';
        }
    }

    handleResize() {
        // Recalculate slide positions if needed
        this.slidesWrapper.style.transform = `translateX(-${this.currentSlide * 100}%)`;
    }

    // Public methods for external control
    getCurrentSlide() {
        return this.currentSlide;
    }

    getTotalSlides() {
        return this.totalSlides;
    }

    // Method to jump to specific section
    goToSection(sectionName) {
        const sectionMap = {
            'title': 0,
            'skills': 1,
            'challenge': 2,
            'intro': 3,
            'baseline': 4,
            'ai': 5,
            'ai-features': 6,
            'ai-technical': 7,
            'ai-impact': 8,
            'development': 9,
            'dev-environment': 10,
            'testing': 11,
            'cicd': 12,
            'content': 13,
            'multilang': 14,
            'content-features': 15,
            'architecture': 16,
            'performance': 17,
            'monitoring': 18,
            'refactoring': 19,
            'creator': 20,
            'conclusion': 21
        };

        if (sectionMap.hasOwnProperty(sectionName)) {
            this.goToSlide(sectionMap[sectionName]);
        }
    }

    // Auto-play functionality (optional)
    startAutoPlay(interval = 10000) {
        this.autoPlayInterval = setInterval(() => {
            if (this.currentSlide < this.totalSlides - 1) {
                this.nextSlide();
            } else {
                this.stopAutoPlay();
            }
        }, interval);
    }

    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    }

    // Add slide indicators
    createSlideIndicators() {
        const indicatorsContainer = document.createElement('div');
        indicatorsContainer.className = 'slide-indicators';
        indicatorsContainer.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 8px;
            z-index: 1000;
        `;

        for (let i = 0; i < this.totalSlides; i++) {
            const indicator = document.createElement('button');
            indicator.className = 'slide-indicator';
            indicator.style.cssText = `
                width: 8px;
                height: 8px;
                border-radius: 50%;
                border: none;
                background: rgba(var(--color-text-secondary-rgb, 119, 124, 124), 0.5);
                cursor: pointer;
                transition: background 0.3s ease;
            `;
            
            if (i === 0) {
                indicator.style.background = 'var(--color-primary)';
            }

            indicator.addEventListener('click', () => {
                this.goToSlide(i);
                this.updateIndicators();
            });

            indicatorsContainer.appendChild(indicator);
        }

        this.presentationContainer.appendChild(indicatorsContainer);
        this.indicators = indicatorsContainer.querySelectorAll('.slide-indicator');
    }

    updateIndicators() {
        if (this.indicators) {
            this.indicators.forEach((indicator, index) => {
                if (index === this.currentSlide) {
                    indicator.style.background = 'var(--color-primary)';
                } else {
                    indicator.style.background = 'rgba(var(--color-text-secondary-rgb, 119, 124, 124), 0.5)';
                }
            });
        }
    }

    // Add presentation timer
    startPresentationTimer() {
        this.startTime = Date.now();
        this.timerDisplay = document.createElement('div');
        this.timerDisplay.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: rgba(var(--color-slate-900-rgb), 0.1);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 12px;
            color: var(--color-text-secondary);
            z-index: 1000;
            font-family: var(--font-family-mono);
        `;
        
        this.presentationContainer.appendChild(this.timerDisplay);
        
        this.timerInterval = setInterval(() => {
            const elapsed = Math.floor((Date.now() - this.startTime) / 1000);
            const minutes = Math.floor(elapsed / 60);
            const seconds = elapsed % 60;
            this.timerDisplay.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }, 1000);
    }

    stopPresentationTimer() {
        if (this.timerInterval) {
            clearInterval(this.timerInterval);
        }
        if (this.timerDisplay) {
            this.timerDisplay.remove();
        }
    }
}

// Initialize presentation when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.presentation = new WillowPresentation();
    
    // Optional: Start presentation timer
    // window.presentation.startPresentationTimer();
    
    // Optional: Initialize slide indicators
    // window.presentation.initializeIndicators();
    
    // Add some helpful console methods for development
    console.log('Willow Presentation loaded successfully!');
    console.log('Use presentation.goToSection("sectionName") to jump to specific sections');
    console.log('Available sections: title, skills, challenge, intro, baseline, ai, development, content, performance, creator, conclusion');
    console.log('Press "f" key or click fullscreen button to toggle fullscreen mode');
    
    // Expose useful methods globally for debugging
    window.goToSlide = (index) => window.presentation.goToSlide(index);
    window.goToSection = (section) => window.presentation.goToSection(section);
});

// Handle visibility change to pause/resume auto-play
document.addEventListener('visibilitychange', () => {
    if (window.presentation) {
        if (document.hidden) {
            window.presentation.stopAutoPlay();
        }
        // Auto-play can be manually restarted if needed
    }
});

// Add error handling for presentation
window.addEventListener('error', (e) => {
    console.error('Presentation error:', e.error);
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WillowPresentation;
}