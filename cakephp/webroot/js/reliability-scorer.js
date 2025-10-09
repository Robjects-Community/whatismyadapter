/**
 * ReliabilityScorer - Frontend JavaScript module for live product reliability scoring
 * 
 * Handles real-time score calculations via API, UI updates, and user interactions.
 * Provides a modern, responsive scoring interface with visual feedback.
 */
class ReliabilityScorer {
    constructor(options = {}) {
        // Configuration
        this.config = {
            apiEndpoint: '/api/reliability/score',
            debounceDelay: 500,
            model: 'Products',
            containerSelector: '.reliability-scorer',
            formSelector: '#product-form',
            ...options
        };
        
        // State
        this.state = {
            currentScore: null,
            lastScoreData: null,
            isScoring: false,
            fields: {},
            debounceTimer: null
        };
        
        // UI elements
        this.elements = {
            container: null,
            scoreBar: null,
            scoreText: null,
            fieldList: null,
            suggestionsList: null,
            progressRing: null,
            form: null
        };
        
        // Initialize
        this.init();
    }
    
    /**
     * Initialize the scorer
     */
    init() {
        this.findElements();
        this.setupEventListeners();
        this.initializeUI();
        this.performInitialScore();
    }
    
    /**
     * Find DOM elements
     */
    findElements() {
        this.elements.container = document.querySelector(this.config.containerSelector);
        this.elements.form = document.querySelector(this.config.formSelector);
        
        if (!this.elements.container || !this.elements.form) {
            console.warn('ReliabilityScorer: Required elements not found');
            return;
        }
        
        this.elements.scoreBar = this.elements.container.querySelector('.score-bar');
        this.elements.scoreText = this.elements.container.querySelector('.score-text');
        this.elements.fieldList = this.elements.container.querySelector('.field-scores');
        this.elements.suggestionsList = this.elements.container.querySelector('.suggestions-list');
        this.elements.progressRing = this.elements.container.querySelector('.progress-ring');
    }
    
    /**
     * Setup event listeners
     */
    setupEventListeners() {
        if (!this.elements.form) return;
        
        // Listen to form field changes
        const formElements = this.elements.form.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            element.addEventListener('input', () => this.handleFieldChange());
            element.addEventListener('change', () => this.handleFieldChange());
        });
        
        // Listen to JSON editor changes if present
        document.addEventListener('jsonEditorChange', () => this.handleFieldChange());
        
        // Listen to suggestion clicks
        document.addEventListener('click', (e) => {
            if (e.target.matches('.suggestion-item [data-action="apply"]')) {
                this.applySuggestion(e.target.closest('.suggestion-item'));
            }
        });
    }
    
    /**
     * Initialize UI components
     */
    initializeUI() {
        if (!this.elements.container) return;
        
        // Add CSS classes for styling
        this.elements.container.classList.add('reliability-scorer-active');
        
        // Create progress ring if not exists
        if (!this.elements.progressRing) {
            this.createProgressRing();
        }
        
        // Show loading state
        this.updateLoadingState(true);
    }
    
    /**
     * Handle field change events
     */
    handleFieldChange() {
        // Debounce API calls
        clearTimeout(this.state.debounceTimer);
        this.state.debounceTimer = setTimeout(() => {
            this.performScoring();
        }, this.config.debounceDelay);
        
        // Immediate UI feedback
        this.updateLoadingState(true);
    }
    
    /**
     * Perform initial scoring
     */
    performInitialScore() {
        setTimeout(() => {
            this.performScoring();
        }, 100);
    }
    
    /**
     * Perform scoring via API
     */
    async performScoring() {
        if (this.state.isScoring) return;
        
        this.state.isScoring = true;
        this.updateLoadingState(true);
        
        try {
            // Collect form data
            const formData = this.collectFormData();
            
            // Prepare API payload
            const payload = {
                model: this.config.model,
                data: formData
            };
            
            // Make API request
            const response = await fetch(this.config.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const scoreData = await response.json();
            
            if (!scoreData.success) {
                throw new Error(scoreData.error || 'Scoring failed');
            }
            
            // Update state and UI
            this.state.lastScoreData = scoreData;
            this.state.currentScore = scoreData.total_score;
            
            this.updateUI(scoreData);
            
        } catch (error) {
            console.error('ReliabilityScorer: Scoring failed', error);
            this.updateErrorState(error.message);
        } finally {
            this.state.isScoring = false;
            this.updateLoadingState(false);
        }
    }
    
    /**
     * Collect form data
     */
    collectFormData() {
        const formData = {};
        const form = this.elements.form;
        
        if (!form) return formData;
        
        // Collect regular form fields
        const formElements = form.querySelectorAll('input, textarea, select');
        formElements.forEach(element => {
            const name = element.name;
            if (!name) return;
            
            let value = element.value;
            
            // Handle different input types
            if (element.type === 'checkbox') {
                value = element.checked;
            } else if (element.type === 'radio') {
                if (element.checked) {
                    formData[name] = value;
                }
                return;
            } else if (element.type === 'number') {
                value = value ? parseFloat(value) : null;
            }
            
            formData[name] = value;
        });
        
        // Handle JSON editor data if present
        const jsonEditor = document.querySelector('.json-editor');
        if (jsonEditor && typeof jsonEditor.getValue === 'function') {
            formData.technical_specifications = jsonEditor.getValue();
        }
        
        return formData;
    }
    
    /**
     * Update UI with scoring results
     */
    updateUI(scoreData) {
        if (!this.elements.container) return;
        
        // Update overall score
        this.updateOverallScore(scoreData.total_score, scoreData.completeness_percent, scoreData.ui.severity);
        
        // Update field scores
        this.updateFieldScores(scoreData.field_scores, scoreData.ui.field_importance);
        
        // Update suggestions
        this.updateSuggestions(scoreData.suggestions);
        
        // Update progress ring
        this.updateProgressRing(scoreData.completeness_percent);
        
        // Update container class for styling
        this.elements.container.className = this.elements.container.className.replace(/\\bseverity-\\w+/g, '');
        this.elements.container.classList.add(`severity-${scoreData.ui.severity}`);
    }
    
    /**
     * Update overall score display
     */
    updateOverallScore(totalScore, completeness, severity) {
        if (this.elements.scoreText) {
            const percentage = Math.round(totalScore * 100);
            this.elements.scoreText.textContent = `${percentage}%`;
        }
        
        if (this.elements.scoreBar) {
            const percentage = totalScore * 100;
            this.elements.scoreBar.style.width = `${percentage}%`;
            this.elements.scoreBar.className = `score-bar severity-${severity}`;
        }
        
        // Update completeness indicator if present
        const completenessEl = this.elements.container.querySelector('.completeness-text');
        if (completenessEl) {
            completenessEl.textContent = `${Math.round(completeness)}% Complete`;
        }
    }
    
    /**
     * Update field scores display
     */
    updateFieldScores(fieldScores, importantFields = []) {
        if (!this.elements.fieldList) return;
        
        // Clear existing items
        this.elements.fieldList.innerHTML = '';
        
        // Sort fields by importance (important fields first, then by contribution)
        const sortedFields = Object.entries(fieldScores).sort(([fieldA, dataA], [fieldB, dataB]) => {
            const aImportant = importantFields.includes(fieldA);
            const bImportant = importantFields.includes(fieldB);
            
            if (aImportant && !bImportant) return -1;
            if (!aImportant && bImportant) return 1;
            
            return dataB.contribution - dataA.contribution;
        });
        
        // Create field score items
        sortedFields.forEach(([field, data]) => {
            const isImportant = importantFields.includes(field);
            const item = this.createFieldScoreItem(field, data, isImportant);
            this.elements.fieldList.appendChild(item);
        });
    }
    
    /**
     * Create field score item element
     */
    createFieldScoreItem(field, data, isImportant = false) {
        const item = document.createElement('div');
        item.className = `field-score-item ${isImportant ? 'important' : ''}`;
        
        const label = this.formatFieldLabel(field);
        const percentage = Math.round(data.score * 100);
        const contribution = Math.round(data.contribution * 100);
        
        item.innerHTML = `
            <div class="field-info">
                <span class="field-label">${label}</span>
                <span class="field-score">${percentage}%</span>
            </div>
            <div class="field-progress">
                <div class="progress-bar" style="width: ${percentage}%"></div>
            </div>
            <div class="field-details">
                <span class="contribution">+${contribution}% contribution</span>
                <span class="notes">${data.notes}</span>
            </div>
        `;
        
        return item;
    }
    
    /**
     * Update suggestions display
     */
    updateSuggestions(suggestions) {
        if (!this.elements.suggestionsList) return;
        
        // Clear existing suggestions
        this.elements.suggestionsList.innerHTML = '';
        
        if (!suggestions || suggestions.length === 0) {
            this.elements.suggestionsList.innerHTML = '<div class="no-suggestions">No suggestions available</div>';
            return;
        }
        
        // Create suggestion items
        suggestions.forEach((suggestion, index) => {
            const item = this.createSuggestionItem(suggestion, index);
            this.elements.suggestionsList.appendChild(item);
        });
    }
    
    /**
     * Create suggestion item element
     */
    createSuggestionItem(suggestion, index) {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.dataset.index = index;
        item.dataset.field = suggestion.field || '';
        
        item.innerHTML = `
            <div class="suggestion-content">
                <div class="suggestion-message">${suggestion.message}</div>
                ${suggestion.action ? `
                    <button class="btn btn-sm btn-outline-primary" data-action="apply">
                        ${suggestion.action}
                    </button>
                ` : ''}
            </div>
        `;
        
        return item;
    }
    
    /**
     * Update progress ring
     */
    updateProgressRing(percentage) {
        if (!this.elements.progressRing) return;
        
        const circle = this.elements.progressRing.querySelector('.progress-circle');
        if (!circle) return;
        
        const radius = circle.r.baseVal.value;
        const circumference = radius * 2 * Math.PI;
        const offset = circumference - (percentage / 100) * circumference;
        
        circle.style.strokeDasharray = `${circumference} ${circumference}`;
        circle.style.strokeDashoffset = offset;
        
        // Update percentage text
        const text = this.elements.progressRing.querySelector('.progress-text');
        if (text) {
            text.textContent = `${Math.round(percentage)}%`;
        }
    }
    
    /**
     * Create progress ring element
     */
    createProgressRing() {
        const container = this.elements.container.querySelector('.progress-container');
        if (!container) return;
        
        const progressRing = document.createElement('div');
        progressRing.className = 'progress-ring';
        progressRing.innerHTML = `
            <svg class="progress-svg" width="100" height="100">
                <circle
                    class="progress-track"
                    cx="50" cy="50" r="40"
                    fill="transparent"
                    stroke="#e0e0e0"
                    stroke-width="6"
                />
                <circle
                    class="progress-circle"
                    cx="50" cy="50" r="40"
                    fill="transparent"
                    stroke="#007bff"
                    stroke-width="6"
                    stroke-linecap="round"
                    transform="rotate(-90 50 50)"
                />
            </svg>
            <div class="progress-text">0%</div>
        `;
        
        container.appendChild(progressRing);
        this.elements.progressRing = progressRing;
    }
    
    /**
     * Apply suggestion
     */
    applySuggestion(suggestionElement) {
        const index = suggestionElement.dataset.index;
        const field = suggestionElement.dataset.field;
        
        if (!field) return;
        
        const suggestion = this.state.lastScoreData?.suggestions?.[index];
        if (!suggestion) return;
        
        // Find the form field and focus it
        const fieldElement = this.elements.form.querySelector(`[name="${field}"]`);
        if (fieldElement) {
            fieldElement.focus();
            fieldElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Add visual highlight
            fieldElement.classList.add('suggestion-highlight');
            setTimeout(() => {
                fieldElement.classList.remove('suggestion-highlight');
            }, 2000);
        }
        
        // Mark suggestion as applied
        suggestionElement.classList.add('suggestion-applied');
    }
    
    /**
     * Update loading state
     */
    updateLoadingState(loading) {
        if (!this.elements.container) return;
        
        this.elements.container.classList.toggle('loading', loading);
        
        // Update loading indicator
        const loadingEl = this.elements.container.querySelector('.loading-indicator');
        if (loadingEl) {
            loadingEl.style.display = loading ? 'block' : 'none';
        }
    }
    
    /**
     * Update error state
     */
    updateErrorState(errorMessage) {
        if (!this.elements.container) return;
        
        this.elements.container.classList.add('error');
        
        // Show error message
        const errorEl = this.elements.container.querySelector('.error-message');
        if (errorEl) {
            errorEl.textContent = errorMessage;
            errorEl.style.display = 'block';
        }
        
        // Hide after 5 seconds
        setTimeout(() => {
            this.elements.container.classList.remove('error');
            if (errorEl) {
                errorEl.style.display = 'none';
            }
        }, 5000);
    }
    
    /**
     * Format field label for display
     */
    formatFieldLabel(field) {
        return field
            .replace(/_/g, ' ')
            .replace(/\b\w/g, l => l.toUpperCase())
            .replace(/Id$/, ' ID')
            .replace(/Json$/, ' JSON')
            .replace(/Url$/, ' URL');
    }
    
    /**
     * Get current score
     */
    getCurrentScore() {
        return this.state.currentScore;
    }
    
    /**
     * Get last score data
     */
    getLastScoreData() {
        return this.state.lastScoreData;
    }
    
    /**
     * Destroy the scorer
     */
    destroy() {
        // Clear timers
        if (this.state.debounceTimer) {
            clearTimeout(this.state.debounceTimer);
        }
        
        // Remove event listeners (would need to store references for proper cleanup)
        // For now, just remove classes
        if (this.elements.container) {
            this.elements.container.classList.remove('reliability-scorer-active');
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if reliability scorer container exists
    const container = document.querySelector('.reliability-scorer');
    if (container) {
        // Initialize scorer
        window.reliabilityScorer = new ReliabilityScorer();
    }
});

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ReliabilityScorer;
}
