class WillowPresentation {
    constructor() {
        this.currentSlide = 0;
        this.totalSlides = 42;
        this.slidesWrapper = document.getElementById('slidesWrapper');
        this.slides = document.querySelectorAll('.slide');
        this.prevBtn = document.getElementById('prevBtn');
        this.nextBtn = document.getElementById('nextBtn');
        this.fullscreenBtn = document.getElementById('fullscreenBtn');
        this.currentSlideSpan = document.getElementById('currentSlide');
        this.totalSlidesSpan = document.getElementById('totalSlides');
        this.progressFill = document.getElementById('progressFill');
        this.presentationContainer = document.querySelector('.presentation-container');
        
        // Chart instances
        this.sustainabilityChart = null;
        this.savingsChart = null;
        
        this.init();
    }

    init() {
        this.updateSlideCounter();
        this.updateProgressBar();
        this.attachEventListeners();
        this.updateNavigationButtons();
        
        // Show first slide
        this.showSlide(0);
        
        // Initialize charts when needed
        this.initializeChartsOnSlideChange();
    }

    attachEventListeners() {
        // Navigation buttons - bind context properly
        this.prevBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.previousSlide();
        });
        
        this.nextBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.nextSlide();
        });
        
        this.fullscreenBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleFullscreen();
        });

        // Keyboard navigation - bind context properly
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
        if (slideIndex >= 0 && slideIndex < this.totalSlides && slideIndex !== this.currentSlide) {
            // Remove active class from current slide
            if (this.slides[this.currentSlide]) {
                this.slides[this.currentSlide].classList.remove('active');
            }
            
            // Update current slide
            const previousSlide = this.currentSlide;
            this.currentSlide = slideIndex;
            
            // Add active class to new slide
            if (this.slides[this.currentSlide]) {
                this.slides[this.currentSlide].classList.add('active');
            }
            
            // Move slides wrapper
            this.slidesWrapper.style.transform = `translateX(-${this.currentSlide * 100}%)`;
            
            // Update UI
            this.updateSlideCounter();
            this.updateProgressBar();
            this.updateNavigationButtons();
            
            // Trigger slide change animations
            setTimeout(() => this.animateSlideContent(), 100);
            
            // Initialize charts if needed
            setTimeout(() => this.initializeChartsOnSlideChange(), 200);
            
            console.log(`Navigated from slide ${previousSlide + 1} to slide ${this.currentSlide + 1}`);
        }
    }

    showSlide(slideIndex) {
        this.slides.forEach((slide, index) => {
            slide.classList.toggle('active', index === slideIndex);
        });
    }

    animateSlideContent() {
        const currentSlideElement = this.slides[this.currentSlide];
        if (!currentSlideElement) return;
        
        const animatableElements = currentSlideElement.querySelectorAll(
            '.feature-card, .skill-card, .stat-item, .service-item, .tool-item, .platform-card, .milestone, .developer-card'
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

    initializeChartsOnSlideChange() {
        // Slide 40: Sustainability Chart
        if (this.currentSlide === 39) { // 0-indexed, so slide 40 is index 39
            this.initializeSustainabilityChart();
        }
        
        // Slide 41: Savings Chart
        if (this.currentSlide === 40) { // 0-indexed, so slide 41 is index 40
            this.initializeSavingsChart();
        }
    }

    initializeSustainabilityChart() {
        const ctx = document.getElementById('sustainabilityChart');
        if (!ctx) return;
        
        // Destroy existing chart if it exists
        if (this.sustainabilityChart) {
            this.sustainabilityChart.destroy();
            this.sustainabilityChart = null;
        }

        try {
            this.sustainabilityChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['6 months', '1 year', '3 years', '5 years', '10 years', '20 years'],
                    datasets: [{
                        label: 'Funding Goals ($)',
                        data: [2400, 5000, 15000, 35000, 75000, 150000],
                        borderColor: '#1FB8CD',
                        backgroundColor: 'rgba(31, 184, 205, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#1FB8CD',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: '20-Year Sustainability Timeline',
                            color: '#134252',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#626c71',
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            },
                            grid: {
                                color: 'rgba(94, 82, 64, 0.2)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#626c71'
                            },
                            grid: {
                                color: 'rgba(94, 82, 64, 0.2)'
                            }
                        }
                    }
                }
            });
            console.log('Sustainability chart initialized');
        } catch (error) {
            console.error('Error initializing sustainability chart:', error);
        }
    }

    initializeSavingsChart() {
        const ctx = document.getElementById('savingsChart');
        if (!ctx) return;
        
        // Destroy existing chart if it exists
        if (this.savingsChart) {
            this.savingsChart.destroy();
            this.savingsChart = null;
        }

        try {
            this.savingsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['WordPress Plugins', 'Third-party Integrations', 'Hosting Premiums', 'Total Savings'],
                    datasets: [{
                        data: [200, 180, 120, 500],
                        backgroundColor: [
                            '#FFC185',
                            '#B4413C', 
                            '#5D878F',
                            '#1FB8CD'
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: '#134252',
                                padding: 15,
                                usePointStyle: true
                            }
                        },
                        title: {
                            display: true,
                            text: 'Annual Cost Savings Breakdown',
                            color: '#134252',
                            font: {
                                size: 16,
                                weight: 'bold'
                            }
                        }
                    }
                }
            });
            console.log('Savings chart initialized');
        } catch (error) {
            console.error('Error initializing savings chart:', error);
        }
    }

    updateSlideCounter() {
        if (this.currentSlideSpan && this.totalSlidesSpan) {
            this.currentSlideSpan.textContent = this.currentSlide + 1;
            this.totalSlidesSpan.textContent = this.totalSlides;
        }
    }

    updateProgressBar() {
        if (this.progressFill) {
            const progress = ((this.currentSlide + 1) / this.totalSlides) * 100;
            this.progressFill.style.width = `${progress}%`;
        }
    }

    updateNavigationButtons() {
        if (this.prevBtn && this.nextBtn) {
            this.prevBtn.disabled = this.currentSlide === 0;
            this.nextBtn.disabled = this.currentSlide === this.totalSlides - 1;
        }
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
        if (this.fullscreenBtn) {
            const icon = this.fullscreenBtn.querySelector('i');
            if (icon) {
                if (this.isFullscreen()) {
                    icon.className = 'fas fa-compress';
                } else {
                    icon.className = 'fas fa-expand';
                }
            }
        }
    }

    handleResize() {
        // Recalculate slide positions if needed
        this.slidesWrapper.style.transform = `translateX(-${this.currentSlide * 100}%)`;
        
        // Resize charts if they exist
        if (this.sustainabilityChart) {
            this.sustainabilityChart.resize();
        }
        if (this.savingsChart) {
            this.savingsChart.resize();
        }
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
            // Introduction & Skills Development (1-4)
            'title': 0,
            'skills': 1,
            'challenge': 2,
            'intro': 3,
            
            // Willow CMS Foundation (5-8)
            'baseline': 4,
            'ai-section': 5,
            'ai-features': 6,
            'ai-technical': 7,
            'ai-impact': 8,
            
            // Developer Experience (9-13)
            'development': 9,
            'dev-environment': 10,
            'testing': 11,
            'cicd': 12,
            
            // Content Management (14-17)
            'content': 13,
            'multilang': 14,
            'content-features': 15,
            'architecture': 16,
            
            // Performance & Quality (18-21)
            'performance': 17,
            'monitoring': 18,
            'refactoring': 19,
            'creator': 20,
            'conclusion': 21,
            
            // Deployment Architecture (23-28)
            'deployment': 22,
            'platforms': 23,
            'orchestration': 24,
            'portainer': 25,
            'strategy': 26,
            'future-arch': 27,
            
            // Strategic Roadmap (29-34)
            'roadmap': 28,
            'placeholders': 29,
            'phase-planning': 30,
            'tech-evolution': 31,
            'scrum': 32,
            'continuous-deployment': 33,
            
            // CI/CD & Production Strategy (35-36)
            'cicd-implementation': 34,
            'team-intro': 35,
            
            // Team & Collaboration (37-39)
            'developers': 36,
            'social': 37,
            'october': 38,
            
            // Funding & Sustainability (40-42)
            'funding': 39,
            'cost-analysis': 40,
            'table-contents': 41
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

    // Navigation shortcuts for specific slide ranges
    goToIntroduction() { this.goToSlide(0); }
    goToSkills() { this.goToSlide(1); }
    goToAISection() { this.goToSlide(5); }
    goToDevelopment() { this.goToSlide(9); }
    goToContent() { this.goToSlide(13); }
    goToPerformance() { this.goToSlide(17); }
    goToDeployment() { this.goToSlide(22); }
    goToRoadmap() { this.goToSlide(28); }
    goToCICD() { this.goToSlide(34); }
    goToTeam() { this.goToSlide(35); }
    goToFunding() { this.goToSlide(39); }
    goToConclusion() { this.goToSlide(41); }

    // Advanced navigation with section overview
    showSectionOverview() {
        const sections = [
            { name: 'Introduction & Skills', range: '1-4', start: 0 },
            { name: 'Willow CMS Foundation', range: '5-8', start: 4 },
            { name: 'AI Integration Revolution', range: '9-13', start: 8 },
            { name: 'Developer Experience', range: '14-17', start: 9 },
            { name: 'Content Management', range: '18-21', start: 13 },
            { name: 'Performance & Quality', range: '22-25', start: 17 },
            { name: 'Deployment Architecture', range: '26-31', start: 22 },
            { name: 'Strategic Roadmap', range: '32-37', start: 28 },
            { name: 'CI/CD & Production', range: '38-39', start: 34 },
            { name: 'Team & Collaboration', range: '40-42', start: 36 },
            { name: 'Funding & Sustainability', range: '43-45', start: 39 }
        ];
        
        console.table(sections);
        return sections;
    }

    // Export presentation data
    exportPresentationData() {
        return {
            title: 'Willow CMS Evolution: From v1.4.0 to Production-Ready Platform',
            totalSlides: this.totalSlides,
            currentSlide: this.currentSlide + 1,
            sections: this.showSectionOverview(),
            timestamp: new Date().toISOString(),
            version: '2.0.0'
        };
    }

    // Cleanup method
    destroy() {
        this.stopAutoPlay();
        
        if (this.sustainabilityChart) {
            this.sustainabilityChart.destroy();
        }
        if (this.savingsChart) {
            this.savingsChart.destroy();
        }
        
        // Remove event listeners would need to be handled more carefully
        // in a real application to avoid memory leaks
    }
}

// Initialize presentation when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.presentation = new WillowPresentation();
    
    // Add some helpful console methods for development
    console.log('Willow Presentation v2.0 loaded successfully!');
    console.log('Total slides: 42');
    console.log('Use presentation.goToSection("sectionName") to jump to specific sections');
    console.log('Available sections: title, skills, ai-section, development, content, performance, deployment, roadmap, team-intro, funding, table-contents');
    console.log('Press "f" key or click fullscreen button to toggle fullscreen mode');
    console.log('Use presentation.showSectionOverview() to see all sections');
    
    // Expose useful methods globally for debugging
    window.goToSlide = (index) => window.presentation.goToSlide(index);
    window.goToSection = (section) => window.presentation.goToSection(section);
    window.showSections = () => window.presentation.showSectionOverview();
    window.exportData = () => window.presentation.exportPresentationData();

    // Quick navigation shortcuts
    window.nav = {
        intro: () => window.presentation.goToIntroduction(),
        skills: () => window.presentation.goToSkills(),
        ai: () => window.presentation.goToAISection(),
        dev: () => window.presentation.goToDevelopment(),
        content: () => window.presentation.goToContent(),
        performance: () => window.presentation.goToPerformance(),
        deployment: () => window.presentation.goToDeployment(),
        roadmap: () => window.presentation.goToRoadmap(),
        cicd: () => window.presentation.goToCICD(),
        team: () => window.presentation.goToTeam(),
        funding: () => window.presentation.goToFunding(),
        end: () => window.presentation.goToConclusion()
    };
    
    console.log('Quick navigation available: window.nav.intro(), window.nav.ai(), window.nav.team(), etc.');
});

// Handle visibility change to pause/resume auto-play
document.addEventListener('visibilitychange', () => {
    if (window.presentation) {
        if (document.hidden) {
            window.presentation.stopAutoPlay();
        }
    }
});

// Add error handling for presentation
window.addEventListener('error', (e) => {
    console.error('Presentation error:', e.error);
});

// Handle unload to cleanup
window.addEventListener('beforeunload', () => {
    if (window.presentation) {
        window.presentation.destroy();
    }
});

// Export for potential module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WillowPresentation;
}