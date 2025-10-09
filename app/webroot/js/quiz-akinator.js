/**
 * Akinator Quiz JavaScript
 * Interactive quiz functionality with API integration
 */

class AkinatorQuiz {
    constructor(config) {
        this.config = config || window.QuizConfig || {};
        this.state = {
            sessionId: null,
            currentQuestion: null,
            answers: [],
            progress: 0,
            confidence: 0,
            loading: false,
            error: null
        };

        this.elements = {};
        this.loadingMessageIndex = 0;
        this.loadingInterval = null;
        
        this.init();
    }

    /**
     * Initialize the quiz
     */
    init() {
        this.bindElements();
        this.bindEvents();
        this.showScreen('welcome');
        this.log('Akinator Quiz initialized');
    }

    /**
     * Bind DOM elements
     */
    bindElements() {
        // Screens
        this.elements.welcomeScreen = document.getElementById('welcome-screen');
        this.elements.questionScreen = document.getElementById('question-screen');
        this.elements.loadingScreen = document.getElementById('loading-screen');
        this.elements.resultsScreen = document.getElementById('results-screen');
        this.elements.errorScreen = document.getElementById('error-screen');

        // Progress
        this.elements.progressBar = document.getElementById('quiz-progress');
        this.elements.progressFill = document.getElementById('progress-fill');
        this.elements.currentQuestion = document.getElementById('current-question');
        this.elements.totalQuestions = document.getElementById('total-questions');
        this.elements.confidenceDisplay = document.getElementById('confidence-display');
        this.elements.confidenceValue = document.getElementById('confidence-value');

        // Question elements
        this.elements.questionText = document.getElementById('question-text');
        this.elements.questionOptions = document.getElementById('question-options');
        this.elements.questionNumber = document.getElementById('question-number');
        this.elements.backBtn = document.getElementById('back-btn');

        // Loading
        this.elements.loadingMessage = document.getElementById('loading-message');

        // Results
        this.elements.resultsSummary = document.getElementById('results-summary');
        this.elements.resultsContent = document.getElementById('results-content');

        // Error
        this.elements.errorMessage = document.getElementById('error-message');

        // Buttons
        this.elements.startQuizBtn = document.getElementById('start-quiz-btn');
        this.elements.retakeQuizBtn = document.getElementById('retake-quiz-btn');
        this.elements.viewAllProductsBtn = document.getElementById('view-all-products-btn');
        this.elements.retryQuizBtn = document.getElementById('retry-quiz-btn');
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Start quiz
        if (this.elements.startQuizBtn) {
            this.elements.startQuizBtn.addEventListener('click', () => this.startQuiz());
        }

        // Retake quiz
        if (this.elements.retakeQuizBtn) {
            this.elements.retakeQuizBtn.addEventListener('click', () => this.restartQuiz());
        }

        // View all products
        if (this.elements.viewAllProductsBtn) {
            this.elements.viewAllProductsBtn.addEventListener('click', () => {
                window.location.href = this.config.urls?.products || '/products';
            });
        }

        // Retry quiz
        if (this.elements.retryQuizBtn) {
            this.elements.retryQuizBtn.addEventListener('click', () => this.restartQuiz());
        }

        // Back button
        if (this.elements.backBtn) {
            this.elements.backBtn.addEventListener('click', () => this.goBack());
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => this.handleKeyboard(e));
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyboard(event) {
        if (this.state.loading) return;

        const { key } = event;

        // Number keys for options
        if (key >= '1' && key <= '9') {
            const optionIndex = parseInt(key, 10) - 1;
            const options = document.querySelectorAll('.option-btn');
            if (options[optionIndex]) {
                event.preventDefault();
                this.selectOption(options[optionIndex]);
            }
        }

        // Enter to continue
        if (key === 'Enter') {
            const selectedOption = document.querySelector('.option-btn.selected');
            if (selectedOption) {
                event.preventDefault();
                this.submitAnswer();
            }
        }

        // Escape to go back
        if (key === 'Escape') {
            event.preventDefault();
            this.goBack();
        }
    }

    /**
     * Start the quiz
     */
    async startQuiz() {
        this.log('Starting quiz...');
        this.setState({ loading: true, error: null });
        this.showScreen('loading');
        this.startLoadingMessages();

        try {
            const response = await this.apiRequest('start', {
                method: 'POST',
                body: JSON.stringify({
                    context: this.getInitialContext()
                })
            });

            if (response.success) {
                this.setState({
                    sessionId: response.data.session_id,
                    loading: false
                });
                
                this.showQuestion(response.data);
                this.log('Quiz started successfully', response.data);
            } else {
                throw new Error(response.error?.message || 'Failed to start quiz');
            }
        } catch (error) {
            this.handleError(error, 'Failed to start quiz');
        } finally {
            this.stopLoadingMessages();
        }
    }

    /**
     * Show a question
     */
    showQuestion(questionData) {
        const { question, progress, confidence } = questionData;
        
        this.setState({
            currentQuestion: question,
            progress: progress?.percentage || 0,
            confidence: confidence || 0
        });

        // Update progress bar
        this.updateProgress();

        // Update question content
        if (this.elements.questionText) {
            this.elements.questionText.textContent = question.text;
        }

        if (this.elements.questionNumber) {
            this.elements.questionNumber.textContent = 
                `Question ${progress?.current || 1}`;
        }

        // Render options
        this.renderOptions(question.options || []);

        // Show/hide back button
        if (this.elements.backBtn) {
            this.elements.backBtn.style.display = 
                this.state.answers.length > 0 ? 'inline-flex' : 'none';
        }

        this.showScreen('question');
        this.log('Question shown', question);
    }

    /**
     * Render question options
     */
    renderOptions(options) {
        if (!this.elements.questionOptions) return;

        this.elements.questionOptions.innerHTML = '';

        options.forEach((option, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'option-btn fade-in-up-delay-' + Math.min(index + 1, 3);
            button.textContent = option.label || option.text;
            button.dataset.value = option.id || option.value;
            
            // Add keyboard hint for first 9 options
            if (index < 9) {
                const hint = document.createElement('span');
                hint.className = 'option-hint';
                hint.textContent = ` (${index + 1})`;
                hint.style.opacity = '0.5';
                hint.style.fontSize = '0.8em';
                button.appendChild(hint);
            }

            button.addEventListener('click', () => this.selectOption(button));
            
            this.elements.questionOptions.appendChild(button);
        });
    }

    /**
     * Select an option
     */
    selectOption(optionElement) {
        if (this.state.loading) return;

        // Remove previous selection
        document.querySelectorAll('.option-btn').forEach(btn => {
            btn.classList.remove('selected');
        });

        // Select current option
        optionElement.classList.add('selected');

        // Auto-submit after a short delay for better UX
        setTimeout(() => {
            if (optionElement.classList.contains('selected')) {
                this.submitAnswer();
            }
        }, 500);
    }

    /**
     * Submit the current answer
     */
    async submitAnswer() {
        const selectedOption = document.querySelector('.option-btn.selected');
        if (!selectedOption || this.state.loading) return;

        const answer = selectedOption.dataset.value;
        this.log('Submitting answer:', answer);

        this.setState({ loading: true });
        this.showScreen('loading');
        this.startLoadingMessages();

        // Add answer to history
        this.state.answers.push({
            questionId: this.state.currentQuestion?.id,
            answer: answer,
            questionText: this.state.currentQuestion?.text
        });

        try {
            const response = await this.apiRequest('next', {
                method: 'POST',
                body: JSON.stringify({
                    session_id: this.state.sessionId,
                    answer: answer,
                    state: {
                        session_id: this.state.sessionId,
                        answers: this.state.answers.reduce((acc, item) => {
                            acc[item.questionId] = item.answer;
                            return acc;
                        }, {}),
                        question_count: this.state.answers.length,
                        confidence: this.state.confidence
                    }
                })
            });

            if (response.success) {
                this.setState({ loading: false });

                if (response.data.completed) {
                    // Quiz completed, show results
                    this.showResults(response.data);
                } else {
                    // Show next question
                    this.showQuestion(response.data);
                }
            } else {
                throw new Error(response.error?.message || 'Failed to process answer');
            }
        } catch (error) {
            this.handleError(error, 'Failed to process your answer');
        } finally {
            this.stopLoadingMessages();
        }
    }

    /**
     * Show quiz results
     */
    async showResults(resultData) {
        this.log('Showing results', resultData);

        // If we have session_id but need to fetch results
        if (resultData.session_id && !resultData.recommendations) {
            try {
                const response = await this.apiRequest('result', {
                    method: 'GET',
                    params: { session_id: resultData.session_id }
                });

                if (response.success) {
                    resultData = response.data;
                } else {
                    throw new Error('Failed to fetch results');
                }
            } catch (error) {
                this.handleError(error, 'Failed to load quiz results');
                return;
            }
        }

        // Update summary
        if (this.elements.resultsSummary) {
            const confidence = Math.round((resultData.final_confidence || 0) * 100);
            const totalMatches = resultData.total_matches || 0;
            
            this.elements.resultsSummary.textContent = 
                `Found ${totalMatches} perfect matches with ${confidence}% confidence based on your answers.`;
        }

        // Render recommendations
        if (this.elements.resultsContent) {
            this.renderRecommendations(resultData.recommendations || []);
        }

        this.showScreen('results');
    }

    /**
     * Render product recommendations
     */
    renderRecommendations(recommendations) {
        if (!this.elements.resultsContent) return;

        if (!recommendations.length) {
            this.elements.resultsContent.innerHTML = `
                <div class="no-results">
                    <p>No products match your specific criteria, but you can browse our full catalog.</p>
                </div>
            `;
            return;
        }

        const html = recommendations.map((item, index) => {
            const product = item.product || item;
            const confidence = item.confidence_score || 0;
            
            return `
                <div class="product-recommendation fade-in-up-delay-${Math.min(index + 1, 3)}">
                    ${product.image_url ? 
                        `<img src="${this.escapeHtml(product.image_url)}" 
                             alt="${this.escapeHtml(product.title)}" 
                             class="product-image">` : 
                        `<div class="product-image"></div>`
                    }
                    <div class="product-details">
                        <h4 class="product-title">${this.escapeHtml(product.title)}</h4>
                        ${product.manufacturer ? 
                            `<p class="product-manufacturer">by ${this.escapeHtml(product.manufacturer)}</p>` : ''
                        }
                        ${product.formatted_price ? 
                            `<p class="product-price">${this.escapeHtml(product.formatted_price)}</p>` : ''
                        }
                        <p class="product-confidence">${Math.round(confidence * 100)}% match</p>
                        ${product.url ? 
                            `<a href="${this.escapeHtml(product.url)}" class="btn btn-primary btn-sm">View Details</a>` : ''
                        }
                    </div>
                </div>
            `;
        }).join('');

        this.elements.resultsContent.innerHTML = html;
    }

    /**
     * Go back to previous question
     */
    goBack() {
        if (this.state.answers.length > 0 && !this.state.loading) {
            this.state.answers.pop();
            this.log('Going back, answers remaining:', this.state.answers.length);
            
            // Would need to implement proper back navigation with the API
            // For now, just restart the quiz
            this.restartQuiz();
        }
    }

    /**
     * Restart the quiz
     */
    restartQuiz() {
        this.log('Restarting quiz');
        this.resetState();
        this.showScreen('welcome');
    }

    /**
     * Reset quiz state
     */
    resetState() {
        this.setState({
            sessionId: null,
            currentQuestion: null,
            answers: [],
            progress: 0,
            confidence: 0,
            loading: false,
            error: null
        });

        if (this.elements.progressBar) {
            this.elements.progressBar.style.display = 'none';
        }
    }

    /**
     * Update progress display
     */
    updateProgress() {
        if (!this.elements.progressBar) return;

        // Show progress bar
        this.elements.progressBar.style.display = 'block';

        // Update progress fill
        if (this.elements.progressFill) {
            this.elements.progressFill.style.width = `${this.state.progress}%`;
        }

        // Update question counter
        if (this.elements.currentQuestion) {
            this.elements.currentQuestion.textContent = this.state.answers.length + 1;
        }

        // Update confidence display
        if (this.state.confidence > 0 && this.elements.confidenceDisplay) {
            this.elements.confidenceDisplay.style.display = 'inline';
            this.elements.confidenceDisplay.classList.add('visible');
            
            if (this.elements.confidenceValue) {
                this.elements.confidenceValue.textContent = Math.round(this.state.confidence * 100);
            }
        }
    }

    /**
     * Show a specific screen
     */
    showScreen(screenName) {
        const screens = [
            'welcome-screen', 'question-screen', 'loading-screen', 
            'results-screen', 'error-screen'
        ];

        screens.forEach(screen => {
            const element = document.getElementById(screen);
            if (element) {
                if (screen === `${screenName}-screen`) {
                    element.style.display = 'flex';
                    setTimeout(() => element.classList.add('active'), 10);
                } else {
                    element.classList.remove('active');
                    element.classList.add('exiting');
                    setTimeout(() => {
                        element.style.display = 'none';
                        element.classList.remove('exiting');
                    }, 500);
                }
            }
        });

        this.log(`Showing screen: ${screenName}`);
    }

    /**
     * Start cycling loading messages
     */
    startLoadingMessages() {
        const messages = this.config.messages?.loadingMessages || [
            'Analyzing your preferences...',
            'Finding the best matches...',
            'Almost ready...'
        ];

        this.loadingMessageIndex = 0;
        
        if (this.elements.loadingMessage) {
            this.elements.loadingMessage.textContent = messages[0];
        }

        this.loadingInterval = setInterval(() => {
            this.loadingMessageIndex = (this.loadingMessageIndex + 1) % messages.length;
            if (this.elements.loadingMessage) {
                this.elements.loadingMessage.textContent = messages[this.loadingMessageIndex];
            }
        }, 2000);
    }

    /**
     * Stop cycling loading messages
     */
    stopLoadingMessages() {
        if (this.loadingInterval) {
            clearInterval(this.loadingInterval);
            this.loadingInterval = null;
        }
    }

    /**
     * Handle errors
     */
    handleError(error, fallbackMessage = 'An error occurred') {
        this.log('Error:', error);
        
        this.setState({ 
            loading: false, 
            error: error.message || fallbackMessage 
        });

        if (this.elements.errorMessage) {
            this.elements.errorMessage.textContent = this.state.error;
        }

        this.showScreen('error');
        this.stopLoadingMessages();
    }

    /**
     * Make API request
     */
    async apiRequest(endpoint, options = {}) {
        const url = this.config.apiEndpoints?.[endpoint];
        if (!url) {
            throw new Error(`API endpoint '${endpoint}' not configured`);
        }

        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.config.csrfToken || ''
            }
        };

        // Add query parameters for GET requests
        let finalUrl = url;
        if (options.params && options.method !== 'POST') {
            const params = new URLSearchParams(options.params);
            finalUrl += (url.includes('?') ? '&' : '?') + params.toString();
        }

        const fetchOptions = {
            ...defaultOptions,
            ...options
        };
        
        delete fetchOptions.params; // Remove params from fetch options

        this.log(`API Request: ${options.method || 'GET'} ${finalUrl}`);

        try {
            const response = await fetch(finalUrl, fetchOptions);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.log(`API Response:`, data);
            
            return data;
        } catch (error) {
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                throw new Error(this.config.messages?.errors?.network || 'Network error');
            }
            throw error;
        }
    }

    /**
     * Get initial context for quiz
     */
    getInitialContext() {
        return {
            userAgent: navigator.userAgent,
            timestamp: Date.now(),
            referrer: document.referrer,
            screenResolution: `${screen.width}x${screen.height}`,
            language: navigator.language
        };
    }

    /**
     * Update internal state
     */
    setState(newState) {
        Object.assign(this.state, newState);
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Log messages (only in debug mode)
     */
    log(...args) {
        if (window.console && (this.config.debug || window.location.hostname === 'localhost')) {
            console.log('[AkinatorQuiz]', ...args);
        }
    }
}

// Initialize quiz when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we have the quiz container
    if (document.getElementById('quiz-container')) {
        window.akinatorQuiz = new AkinatorQuiz(window.QuizConfig);
    }
});

// Expose class globally for potential external use
window.AkinatorQuiz = AkinatorQuiz;