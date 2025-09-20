/**
 * Quiz Module - Progressive Enhancement for AI-Powered Quizzes
 * 
 * Provides client-side functionality for both Akinator-style and comprehensive quizzes
 * with graceful fallback to server-side functionality
 */

class QuizModule {
    constructor() {
        this.sessionId = null;
        this.currentState = null;
        this.questionHistory = [];
        this.apiEndpoints = {
            akinatorStart: '/api/quiz/akinator/start.json',
            akinatorNext: '/api/quiz/akinator/next.json', 
            akinatorResult: '/api/quiz/akinator/result.json',
            comprehensiveSubmit: '/api/quiz/comprehensive/submit.json',
            products: '/api/products.json'
        };
    }

    /**
     * Initialize Akinator quiz functionality
     */
    async initializeAkinator() {
        try {
            console.log('Initializing Akinator Quiz Module');
            
            // Bind events first
            this.bindAkinatorEvents();
            
            // Start the Akinator session
            const startResponse = await this.startAkinator();
            
            if (startResponse.success && startResponse.data) {
                this.sessionId = startResponse.data.session_id;
                this.currentState = startResponse.data;
                this.displayAkinatorQuestion(startResponse.data.question || startResponse.data.first_question);
                this.updateProgress(startResponse.data.confidence || 0, 1);
            } else {
                throw new Error(startResponse.error?.message || 'Failed to start Akinator');
            }
        } catch (error) {
            console.error('Akinator initialization failed:', error);
            this.showError('Failed to start the quiz. Please try again.');
        }
    }

    /**
     * Start Akinator quiz session
     */
    async startAkinator(context = {}) {
        this.showLoading();
        
        const response = await fetch(this.apiEndpoints.akinatorStart, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                context: {
                    quiz_type: 'akinator',
                    user_agent: navigator.userAgent,
                    started_at: new Date().toISOString(),
                    ...context
                }
            })
        });

        this.hideLoading();
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Submit answer and get next question or results
     */
    async nextAkinator(answer) {
        if (!this.sessionId) {
            throw new Error('No active Akinator session');
        }
        
        this.showLoading();

        try {
            const response = await fetch(this.apiEndpoints.akinatorNext, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    answer: answer,
                    state: {
                        session_id: this.sessionId,
                        question_count: this.questionHistory.length,
                        answers: this.questionHistory.reduce((acc, item) => {
                            if (item.questionId && item.answer) {
                                acc[item.questionId] = item.answer;
                            }
                            return acc;
                        }, {})
                    }
                })
            });

            this.hideLoading();
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                // Store current question in history for back functionality
                if (this.currentState && this.currentState.question) {
                    this.questionHistory.push({
                        questionId: this.currentState.question.id,
                        question: this.currentState.question,
                        answer: answer
                    });
                }

                if (data.data.completed || !data.data.question) {
                    this.showAkinatorResults(data.data.recommendations || [], data.data);
                } else {
                    this.currentState = {
                        session_id: this.sessionId,
                        question: data.data.question
                    };
                    this.displayAkinatorQuestion(data.data.question);
                    this.updateProgress(data.data.confidence || 0, this.questionHistory.length + 1);
                }
            }

            return data;
        } catch (error) {
            this.hideLoading();
            throw error;
        }
    }

    /**
     * Display Akinator question
     */
    displayAkinatorQuestion(question) {
        const questionEl = document.getElementById('akinator-question');
        const optionsEl = document.getElementById('answer-options');
        const questionDisplay = document.querySelector('.question-display');
        
        if (questionEl && question) {
            questionEl.textContent = question.text || question;
            
            // Update options dynamically
            if (question.options && optionsEl) {
                optionsEl.innerHTML = '';
                
                question.options.forEach((option, index) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = `btn btn-${this.getButtonColor(index)} btn-lg me-2 mb-2`;
                    button.setAttribute('data-answer', option.id);
                    button.innerHTML = `${this.getButtonIcon(index)} ${option.label}`;
                    optionsEl.appendChild(button);
                    
                    // Add click event
                    button.addEventListener('click', async (e) => {
                        const answer = e.target.getAttribute('data-answer');
                        await this.handleAkinatorAnswer(answer);
                    });
                });
            }
            
            // Show question display, hide others
            questionDisplay.style.display = 'block';
            questionDisplay.classList.add('active');
            document.getElementById('results-display').style.display = 'none';
            document.getElementById('loading-state').style.display = 'none';
        }
    }

    /**
     * Show Akinator results
     */
    showAkinatorResults(recommendations, resultData = {}) {
        const resultsDisplay = document.getElementById('results-display');
        const questionDisplay = document.querySelector('.question-display');
        const resultsContainer = document.getElementById('akinator-results');

        if (!resultsContainer) return;

        // Hide question, show results
        if (questionDisplay) {
            questionDisplay.style.display = 'none';
        }
        if (resultsDisplay) {
            resultsDisplay.style.display = 'block';
            resultsDisplay.classList.add('active');
        }

        // Clear previous results
        resultsContainer.innerHTML = '';

        if (!recommendations || recommendations.length === 0) {
            resultsContainer.innerHTML = `
                <div class="no-results text-center">
                    <i class="fas fa-search fa-2x text-muted mb-3"></i>
                    <h5>No Perfect Match Found</h5>
                    <p class="text-muted">Based on your answers, we couldn't find a specific product match. Try our comprehensive quiz for better results.</p>
                    <div class="mt-3">
                        <a href="/en/quiz/comprehensive" class="btn btn-primary">
                            <i class="fas fa-list"></i> Try Comprehensive Quiz
                        </a>
                    </div>
                </div>
            `;
            return;
        }

        // Display results
        let resultsHTML = '<div class="recommendations-grid">';
        
        recommendations.forEach((rec, index) => {
            if (rec.product) {
                const product = rec.product;
                const confidence = Math.round((rec.confidence_score || 0) * 100);
                const price = product.price ? `$${product.price}` : 'Price varies';
                
                resultsHTML += `
                    <div class="recommendation-card mb-3">
                        <div class="card ${index === 0 ? 'border-success' : ''}">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title">${this.escapeHtml(product.title)}</h5>
                                    <span class="badge bg-${confidence >= 90 ? 'success' : confidence >= 70 ? 'warning' : 'secondary'}">
                                        ${confidence}% match
                                    </span>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <p class="text-muted mb-2">
                                            <strong>${this.escapeHtml(product.manufacturer || 'Unknown')}</strong>
                                            ${product.port_family ? `• ${this.escapeHtml(product.port_family)}` : ''}
                                        </p>
                                        
                                        ${rec.explanation ? `
                                            <p class="recommendation-explanation">
                                                ${this.escapeHtml(rec.explanation)}
                                            </p>
                                        ` : ''}
                                        
                                        ${rec.key_benefits && rec.key_benefits.length > 0 ? `
                                            <div class="benefits mb-2">
                                                <small class="text-success">
                                                    <i class="fas fa-check"></i>
                                                    ${rec.key_benefits.map(benefit => this.escapeHtml(benefit)).join(' • ')}
                                                </small>
                                            </div>
                                        ` : ''}
                                    </div>
                                    
                                    <div class="col-md-4 text-end">
                                        <div class="price-badge">
                                            <span class="h5 text-primary">${price}</span>
                                        </div>
                                        ${product.rating ? `
                                            <div class="rating mt-1">
                                                <small class="text-muted">
                                                    <i class="fas fa-star text-warning"></i>
                                                    ${product.rating}/5
                                                </small>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                                
                                ${rec.compatibility_notes ? `
                                    <div class="compatibility-notes mt-2">
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i>
                                            ${this.escapeHtml(rec.compatibility_notes)}
                                        </small>
                                    </div>
                                ` : ''}
                                
                                <div class="mt-3">
                                    ${product.slug ? `
                                        <a href="/en/products/${product.slug}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    ` : ''}
                                    ${product.buy_url ? `
                                        <a href="${product.buy_url}" target="_blank" rel="noopener" class="btn btn-success btn-sm ms-2">
                                            <i class="fas fa-shopping-cart"></i> Buy Now
                                        </a>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
        });
        
        resultsHTML += '</div>';
        
        // Add quiz statistics
        if (resultData.questions_asked || resultData.final_confidence) {
            resultsHTML += `
                <div class="quiz-stats mt-3 text-center">
                    <small class="text-muted">
                        ${resultData.questions_asked ? `${resultData.questions_asked} questions asked` : ''}
                        ${resultData.final_confidence ? ` • ${Math.round(resultData.final_confidence * 100)}% final confidence` : ''}
                    </small>
                </div>
            `;
        }
        
        resultsContainer.innerHTML = resultsHTML;
    }

    /**
     * Bind Akinator event handlers
     */
    bindAkinatorEvents() {
        // Answer buttons
        document.querySelectorAll('#answer-options button[data-answer]').forEach(button => {
            button.addEventListener('click', async (e) => {
                const answer = e.target.getAttribute('data-answer');
                await this.handleAkinatorAnswer(answer);
            });
        });

        // Back button
        const backButton = document.getElementById('back-button');
        if (backButton) {
            backButton.addEventListener('click', () => {
                this.goBackAkinator();
            });
        }

        // Restart button
        const restartButton = document.getElementById('restart-button');
        if (restartButton) {
            restartButton.addEventListener('click', () => {
                this.restartAkinator();
            });
        }

        // Play again button
        const playAgainButton = document.getElementById('play-again-button');
        if (playAgainButton) {
            playAgainButton.addEventListener('click', () => {
                this.restartAkinator();
            });
        }
    }

    /**
     * Handle Akinator answer submission
     */
    async handleAkinatorAnswer(answer) {
        try {
            const response = await this.nextAkinator(answer);
            
            if (!response.success) {
                throw new Error(response.error?.message || 'Failed to process answer');
            }
            
        } catch (error) {
            console.error('Answer processing failed:', error);
            this.showError('Failed to process your answer. Please try again.');
        }
    }

    /**
     * Go back to previous question
     */
    goBackAkinator() {
        if (this.questionHistory.length > 0) {
            const previousState = this.questionHistory.pop();
            this.currentState = previousState.state;
            
            const questionEl = document.getElementById('akinator-question');
            if (questionEl) {
                questionEl.textContent = previousState.question;
            }
            
            this.updateProgress(0, this.questionHistory.length + 1);
        }
    }

    /**
     * Restart Akinator quiz
     */
    async restartAkinator() {
        this.sessionId = null;
        this.currentState = null;
        this.questionHistory = [];
        
        document.getElementById('results-display').style.display = 'none';
        this.updateProgress(0, 1);
        
        await this.initializeAkinator();
    }

    /**
     * Initialize comprehensive quiz functionality
     */
    initializeComprehensive() {
        this.bindComprehensiveEvents();
        this.initializeStepNavigation();
        this.enableFormEnhancements();
    }

    /**
     * Bind comprehensive quiz events
     */
    bindComprehensiveEvents() {
        const form = document.getElementById('comprehensive-quiz-form');
        if (!form) return;

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.submitComprehensiveQuiz();
        });

        // Add real-time validation
        this.addFormValidation(form);
        
        // Add preview functionality on final step
        this.addPreviewFunctionality();
    }

    /**
     * Initialize step navigation for comprehensive quiz
     */
    initializeStepNavigation() {
        const steps = document.querySelectorAll('.quiz-step');
        const nextBtn = document.getElementById('next-step');
        const prevBtn = document.getElementById('prev-step');
        
        if (!steps.length) return;

        let currentStep = 1;
        const totalSteps = steps.length;

        const updateStep = () => {
            steps.forEach((step, index) => {
                const stepNumber = index + 1;
                step.style.display = stepNumber === currentStep ? 'block' : 'none';
                step.classList.toggle('active', stepNumber === currentStep);
            });

            // Update progress
            const progress = (currentStep / totalSteps) * 100;
            this.updateStepProgress(currentStep, totalSteps, progress);

            // Update navigation buttons
            if (prevBtn) prevBtn.style.display = currentStep > 1 ? 'inline-block' : 'none';
            if (nextBtn) nextBtn.style.display = currentStep < totalSteps ? 'inline-block' : 'none';

            // Update summary on final step
            if (currentStep === totalSteps) {
                this.updateQuizSummary();
            }
        };

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                if (this.validateCurrentStep(currentStep) && currentStep < totalSteps) {
                    currentStep++;
                    updateStep();
                }
            });
        }

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                if (currentStep > 1) {
                    currentStep--;
                    updateStep();
                }
            });
        }

        updateStep(); // Initialize
    }

    /**
     * Validate current step
     */
    validateCurrentStep(stepNumber) {
        const currentStepEl = document.getElementById(`step-${stepNumber}`);
        if (!currentStepEl) return true;

        const requiredFields = currentStepEl.querySelectorAll('[required]');
        
        for (let field of requiredFields) {
            if (field.type === 'radio') {
                const radioGroup = currentStepEl.querySelectorAll(`input[name="${field.name}"]`);
                const isChecked = Array.from(radioGroup).some(radio => radio.checked);
                if (!isChecked) {
                    field.focus();
                    this.showValidationError('Please answer this question before proceeding.');
                    return false;
                }
            } else if (!field.value.trim()) {
                field.focus();
                this.showValidationError('Please fill in this required field.');
                return false;
            }
        }

        return true;
    }

    /**
     * Submit comprehensive quiz
     */
    async submitComprehensiveQuiz() {
        try {
            const form = document.getElementById('comprehensive-quiz-form');
            const formData = new FormData(form);
            const answers = this.formDataToObject(formData);

            this.showSubmitLoading();

            const response = await fetch(this.apiEndpoints.comprehensiveSubmit, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    session_id: this.sessionId,
                    answers: answers
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                // Redirect to results or update page
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else if (data.results_html) {
                    this.displayInlineResults(data.results_html);
                }
            } else {
                throw new Error(data.error?.message || 'Quiz submission failed');
            }

        } catch (error) {
            console.error('Comprehensive quiz submission failed:', error);
            this.showError('Failed to submit quiz. Please try again.');
        } finally {
            this.hideSubmitLoading();
        }
    }

    /**
     * Update step progress
     */
    updateStepProgress(currentStep, totalSteps, progress) {
        const currentStepSpan = document.getElementById('current-step');
        const totalStepsSpan = document.getElementById('total-steps');
        const completionSpan = document.getElementById('completion-percentage');
        const progressBar = document.getElementById('main-progress-bar');

        if (currentStepSpan) currentStepSpan.textContent = currentStep;
        if (totalStepsSpan) totalStepsSpan.textContent = totalSteps;
        if (completionSpan) completionSpan.textContent = Math.round(progress);
        if (progressBar) {
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
        }
    }

    /**
     * Update progress for Akinator
     */
    updateProgress(confidence, questionCount) {
        const confidenceSpan = document.getElementById('confidence-score');
        const questionSpan = document.getElementById('current-question-num');
        const progressBar = document.getElementById('confidence-bar');

        if (confidenceSpan) confidenceSpan.textContent = Math.round(confidence) + '%';
        if (questionSpan) questionSpan.textContent = questionCount;
        if (progressBar) {
            progressBar.style.width = confidence + '%';
            progressBar.setAttribute('aria-valuenow', confidence);
        }

        // Update back button visibility
        const backButton = document.getElementById('back-button');
        if (backButton) {
            backButton.style.display = questionCount > 1 ? 'inline-block' : 'none';
        }
    }

    /**
     * Update quiz summary
     */
    updateQuizSummary() {
        const summaryContainer = document.getElementById('quiz-summary');
        if (!summaryContainer) return;

        const form = document.getElementById('comprehensive-quiz-form');
        const formData = new FormData(form);
        const answers = this.formDataToObject(formData);

        let summaryHtml = '<div class="summary-sections">';

        // Device information
        if (answers.device_type) {
            summaryHtml += `
                <div class="summary-section mb-3">
                    <h6><i class="fas fa-laptop text-primary"></i> Device Information</h6>
                    <p class="mb-1"><strong>Type:</strong> ${this.escapeHtml(answers.device_type)}</p>
                    ${answers.device_brand ? `<p class="mb-1"><strong>Brand:</strong> ${this.escapeHtml(answers.device_brand)}</p>` : ''}
                    ${answers.device_model ? `<p class="mb-1"><strong>Model:</strong> ${this.escapeHtml(answers.device_model)}</p>` : ''}
                </div>
            `;
        }

        // Usage information
        if (answers.primary_use) {
            summaryHtml += `
                <div class="summary-section mb-3">
                    <h6><i class="fas fa-cogs text-success"></i> Usage</h6>
                    <p class="mb-1"><strong>Primary use:</strong> ${this.escapeHtml(answers.primary_use)}</p>
                    ${answers.performance_level ? `<p class="mb-1"><strong>Performance needs:</strong> ${this.escapeHtml(answers.performance_level)}</p>` : ''}
                </div>
            `;
        }

        // Budget information
        if (answers.budget_range) {
            summaryHtml += `
                <div class="summary-section mb-3">
                    <h6><i class="fas fa-dollar-sign text-info"></i> Budget</h6>
                    <p class="mb-1"><strong>Range:</strong> ${this.escapeHtml(answers.budget_range)}</p>
                </div>
            `;
        }

        summaryHtml += '</div>';
        summaryContainer.innerHTML = summaryHtml;
    }

    /**
     * Add form validation enhancements
     */
    addFormValidation(form) {
        // Add real-time validation for required fields
        const requiredFields = form.querySelectorAll('[required]');
        
        requiredFields.forEach(field => {
            field.addEventListener('blur', () => {
                this.validateField(field);
            });

            field.addEventListener('change', () => {
                this.validateField(field);
            });
        });
    }

    /**
     * Validate individual field
     */
    validateField(field) {
        const isValid = field.type === 'radio' ? 
            document.querySelector(`input[name="${field.name}"]:checked`) !== null :
            field.value.trim() !== '';

        field.classList.toggle('is-invalid', !isValid);
        field.classList.toggle('is-valid', isValid);

        return isValid;
    }

    /**
     * Add preview functionality
     */
    addPreviewFunctionality() {
        // Could add live preview of matching products as user fills form
        // For now, just update summary
    }

    /**
     * Utility function to convert FormData to object
     */
    formDataToObject(formData) {
        const obj = {};
        for (let [key, value] of formData.entries()) {
            if (obj[key]) {
                // Handle multiple values (checkboxes)
                if (Array.isArray(obj[key])) {
                    obj[key].push(value);
                } else {
                    obj[key] = [obj[key], value];
                }
            } else {
                obj[key] = value;
            }
        }
        return obj;
    }

    /**
     * Show loading state
     */
    showLoading() {
        document.getElementById('loading-state').style.display = 'block';
        document.querySelector('.question-display').style.display = 'none';
    }

    /**
     * Hide loading state
     */
    hideLoading() {
        document.getElementById('loading-state').style.display = 'none';
        document.querySelector('.question-display').style.display = 'block';
    }

    /**
     * Show submit loading
     */
    showSubmitLoading() {
        const submitBtn = document.getElementById('submit-quiz');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
        }
    }

    /**
     * Hide submit loading
     */
    hideSubmitLoading() {
        const submitBtn = document.getElementById('submit-quiz');
        if (submitBtn) {
            submitBtn.innerHTML = '<i class="fas fa-rocket"></i> Get My Recommendations';
            submitBtn.disabled = false;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const errorAlert = document.getElementById('error-alert');
        const errorMessage = document.getElementById('error-message');
        
        if (errorAlert && errorMessage) {
            errorMessage.textContent = message;
            errorAlert.style.display = 'block';
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 5000);
        }
    }

    /**
     * Show validation error
     */
    showValidationError(message) {
        // You could create a specific validation error display
        this.showError(message);
    }

    /**
     * Get button color for option index
     */
    getButtonColor(index) {
        const colors = ['primary', 'success', 'info', 'warning', 'secondary'];
        return colors[index % colors.length];
    }
    
    /**
     * Get button icon for option index
     */
    getButtonIcon(index) {
        const icons = [
            '<i class="fas fa-check"></i>',
            '<i class="fas fa-star"></i>',
            '<i class="fas fa-lightbulb"></i>',
            '<i class="fas fa-question"></i>',
            '<i class="fas fa-cog"></i>'
        ];
        return icons[index % icons.length];
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
     * Display inline results
     */
    displayInlineResults(html) {
        const container = document.querySelector('.quiz-comprehensive');
        if (container) {
            container.innerHTML = html;
        }
    }
}

// Initialize global QuizModule instance
window.QuizModule = new QuizModule();

// Expose initialization function globally for template use
window.initializeAkinator = function() {
    return window.QuizModule.initializeAkinator();
};

// Auto-initialize based on page content
document.addEventListener('DOMContentLoaded', function() {
    console.log('Quiz module loaded successfully');
    
    // Check which quiz type is present and initialize accordingly
    if (document.getElementById('akinator-container')) {
        console.log('Akinator container found, ready for initialization');
        // Akinator initialization will be called from template
    }
    
    if (document.getElementById('comprehensive-quiz-form')) {
        console.log('Initializing Comprehensive quiz...');
        window.QuizModule.initializeComprehensive();
    }
});

// Export for ES6 modules if needed
export default QuizModule;
