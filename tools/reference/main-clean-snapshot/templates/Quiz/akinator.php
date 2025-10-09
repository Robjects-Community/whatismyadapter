<?php
/**
 * Akinator-style Quiz Template
 * 
 * Interactive product recommendation quiz with progressive disclosure
 */

$this->assign('title', $pageTitle ?? __('Product Quiz - Find Your Perfect Match'));
$this->assign('description', $pageDescription ?? __('Answer a few quick questions to get personalized product recommendations'));

// Load required CSS and JavaScript
echo $this->Html->css('quiz-akinator');
echo $this->Html->script('quiz-akinator');

// Get configuration with defaults
$maxQuestions = $quizConfig['max_questions'] ?? 10;
$apiEndpoints = [
    'start' => $this->Url->build(['prefix' => 'Api', 'controller' => 'Quiz', 'action' => 'akinatorStart', '_ext' => 'json'], ['fullBase' => true]),
    'next' => $this->Url->build(['prefix' => 'Api', 'controller' => 'Quiz', 'action' => 'akinatorNext', '_ext' => 'json'], ['fullBase' => true]),
    'result' => $this->Url->build(['prefix' => 'Api', 'controller' => 'Quiz', 'action' => 'akinatorResult', '_ext' => 'json'], ['fullBase' => true]),
];

// Check if Akinator is enabled and not temporarily disabled
$akinatorEnabled = $quizConfig['akinator_enabled'] ?? true;
$temporarilyDisabled = false; // This would come from a system check
?>

<div class="quiz-container" id="quiz-container">
    <div class="quiz-header">
        <h1 class="quiz-title"><?= h($pageTitle ?? __('AI Adapter Finder Quiz')) ?></h1>
        <p class="quiz-description"><?= h($pageDescription ?? __('Answer a few quick questions to get personalized adapter and charger recommendations tailored just for you')) ?></p>
        
        <!-- Progress Bar -->
        <div class="quiz-progress" id="quiz-progress" style="display: none;">
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill" style="width: 0%"></div>
            </div>
            <div class="progress-text">
                <span id="current-question">1</span> / <span id="total-questions"><?= h($maxQuestions) ?></span>
                <span class="confidence-score" id="confidence-display" style="display: none;">
                    Confidence: <span id="confidence-value">0</span>%
                </span>
            </div>
        </div>
    </div>

    <div class="quiz-content">
        <?php if (!$akinatorEnabled || $temporarilyDisabled): ?>
            <!-- Fallback Content When Quiz is Disabled -->
            <div class="quiz-screen fallback-screen">
                <div class="fallback-card">
                    <div class="warning-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 19.73h20L12 2z"/>
                            <path d="M12 9v4"/>
                            <path d="M12 17h.01"/>
                        </svg>
                    </div>
                    <h2><?= __('Quiz Temporarily Unavailable') ?></h2>
                    <p><?= __('The AI Adapter Quiz is currently unavailable. Please try our comprehensive quiz instead.') ?></p>
                    
                    <div class="fallback-actions">
                        <?= $this->Html->link(
                            __('Try Comprehensive Quiz'),
                            ['action' => 'comprehensive'],
                            ['class' => 'btn btn-primary btn-large']
                        ) ?>
                        <?= $this->Html->link(
                            __('Browse All Products'),
                            ['controller' => 'Products', 'action' => 'index'],
                            ['class' => 'btn btn-secondary btn-large']
                        ) ?>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Welcome Screen -->
            <div class="quiz-screen welcome-screen" id="welcome-screen">
                <div class="welcome-card">
                    <div class="quiz-icon">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M9 12l2 2 4-4"/>
                        </svg>
                    </div>
                    <h2><?= __('Ready to Find Your Perfect Product?') ?></h2>
                    <p><?= __('I\'ll ask you a few questions to understand exactly what you need. This usually takes less than 2 minutes.') ?></p>
                    
                    <div class="quiz-features">
                        <div class="feature">
                            <strong><?= __('Quick & Easy') ?></strong>
                            <span><?= __('Just a few simple questions') ?></span>
                        </div>
                        <div class="feature">
                            <strong><?= __('Personalized') ?></strong>
                            <span><?= __('Tailored recommendations just for you') ?></span>
                        </div>
                        <div class="feature">
                            <strong><?= __('Expert Powered') ?></strong>
                            <span><?= __('AI-driven product matching') ?></span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary btn-large" id="start-quiz-btn">
                        <?= __('Start Quiz') ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Question Screen -->
        <div class="quiz-screen question-screen" id="question-screen" style="display: none;">
            <div class="question-card">
                <div class="question-content">
                    <h2 class="question-text" id="question-text"></h2>
                    <div class="question-options" id="question-options">
                        <!-- Options will be populated dynamically -->
                    </div>
                </div>
                
                <div class="question-navigation">
                    <button type="button" class="btn btn-secondary" id="back-btn" style="display: none;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <?= __('Back') ?>
                    </button>
                    
                    <div class="question-meta">
                        <span class="question-number" id="question-number"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Screen -->
        <div class="quiz-screen loading-screen" id="loading-screen" style="display: none;">
            <div class="loading-card">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                </div>
                <h2><?= __('Analyzing Your Answers...') ?></h2>
                <p class="loading-message" id="loading-message">
                    <?= __('Finding the perfect products for your needs') ?>
                </p>
            </div>
        </div>

        <!-- Results Screen -->
        <div class="quiz-screen results-screen" id="results-screen" style="display: none;">
            <div class="results-card">
                <div class="results-header">
                    <div class="success-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22,4 12,14.01 9,11.01"/>
                        </svg>
                    </div>
                    <h2><?= __('Perfect Matches Found!') ?></h2>
                    <p class="results-summary" id="results-summary">
                        <!-- Dynamic summary -->
                    </p>
                </div>

                <div class="results-content" id="results-content">
                    <!-- Product recommendations will be populated here -->
                </div>

                <div class="results-actions">
                    <button type="button" class="btn btn-secondary" id="retake-quiz-btn">
                        <?= __('Take Quiz Again') ?>
                    </button>
                    <button type="button" class="btn btn-primary" id="view-all-products-btn">
                        <?= __('Browse All Products') ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Error Screen -->
        <div class="quiz-screen error-screen" id="error-screen" style="display: none;">
            <div class="error-card">
                <div class="error-icon">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <h2><?= __('Oops! Something went wrong') ?></h2>
                <p class="error-message" id="error-message">
                    <?= __('We encountered an issue while processing your quiz. Please try again.') ?>
                </p>
                <button type="button" class="btn btn-primary" id="retry-quiz-btn">
                    <?= __('Try Again') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- No-JavaScript fallback message (hidden by CSS when JS is available) -->
<noscript>
    <div class="no-js-message">
        <div class="alert alert-info">
            <h4><?= __('JavaScript Required') ?></h4>
            <p><?= __('For the best interactive experience, please enable JavaScript in your browser. Alternatively, you can use our comprehensive quiz below.') ?></p>
            <div class="fallback-actions">
                <?= $this->Html->link(
                    __('Try Comprehensive Quiz'),
                    ['action' => 'comprehensive'],
                    ['class' => 'btn btn-primary']
                ) ?>
                <?= $this->Html->link(
                    __('Browse Products'),
                    ['controller' => 'Products', 'action' => 'index'],
                    ['class' => 'btn btn-secondary']
                ) ?>
            </div>
        </div>
    </div>
</noscript>

<!-- Hidden form for CSRF token -->
<?= $this->Form->create(null, [
    'id' => 'quiz-form',
    'style' => 'display: none;'
]) ?>
<?= $this->Form->end() ?>

<script>
// Pass configuration to JavaScript
window.QuizConfig = {
    maxQuestions: <?= json_encode($maxQuestions) ?>,
    apiEndpoints: <?= json_encode($apiEndpoints) ?>,
    csrfToken: <?= json_encode($this->request->getAttribute('csrfToken')) ?>,
    messages: {
        loadingMessages: [
            <?= json_encode(__('Analyzing your preferences...')) ?>,
            <?= json_encode(__('Matching products to your needs...')) ?>,
            <?= json_encode(__('Finding the best options...')) ?>,
            <?= json_encode(__('Almost ready...')) ?>
        ],
        errors: {
            network: <?= json_encode(__('Network error. Please check your connection and try again.')) ?>,
            server: <?= json_encode(__('Server error. Please try again in a moment.')) ?>,
            validation: <?= json_encode(__('Please select an answer to continue.')) ?>,
            noResults: <?= json_encode(__('No products match your criteria. Please try adjusting your answers.')) ?>
        }
    },
    urls: {
        products: <?= json_encode($this->Url->build(['controller' => 'Products', 'action' => 'index'])) ?>,
        productView: <?= json_encode($this->Url->build(['controller' => 'Products', 'action' => 'view', 'ID_PLACEHOLDER'])) ?>
    }
};
</script>

<style>
/* Quick inline styles for immediate functionality - these should be moved to external CSS */
.quiz-container {
    max-width: 800px;
    margin: 2rem auto;
    padding: 1rem;
}

.quiz-header {
    text-align: center;
    margin-bottom: 2rem;
}

.quiz-progress {
    margin: 1rem 0;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #4CAF50, #45a049);
    transition: width 0.3s ease;
}

.progress-text {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
    font-size: 0.9rem;
    color: #666;
}

.quiz-screen {
    min-height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.welcome-card, .question-card, .loading-card, .results-card, .error-card {
    background: white;
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    width: 100%;
    max-width: 600px;
}

.quiz-icon, .success-icon, .error-icon {
    color: #4CAF50;
    margin-bottom: 1rem;
}

.error-icon {
    color: #f44336;
}

.quiz-features {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    text-align: left;
}

.feature {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.feature strong {
    font-weight: 600;
    color: #333;
}

.feature span {
    font-size: 0.9rem;
    color: #666;
}

.question-options {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    margin: 2rem 0;
}

.option-btn {
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    background: white;
    cursor: pointer;
    transition: all 0.2s ease;
    text-align: left;
    font-size: 1rem;
}

.option-btn:hover {
    border-color: #4CAF50;
    background: #f8f9fa;
}

.option-btn.selected {
    border-color: #4CAF50;
    background: #e8f5e8;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: #4CAF50;
    color: white;
}

.btn-primary:hover {
    background: #45a049;
}

.btn-secondary {
    background: #f0f0f0;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.loading-spinner .spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f0f0f0;
    border-top: 4px solid #4CAF50;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.results-content {
    margin: 2rem 0;
}

.product-recommendation {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 1rem;
    text-align: left;
}

.product-image {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 6px;
    background: #f0f0f0;
}

.product-details {
    flex: 1;
}

.product-title {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.product-confidence {
    font-size: 0.9rem;
    color: #4CAF50;
    font-weight: 500;
}

.confidence-score {
    font-weight: 500;
    color: #4CAF50;
}

@media (max-width: 768px) {
    .quiz-features {
        flex-direction: column;
        gap: 1rem;
    }
    
    .product-recommendation {
        flex-direction: column;
    }
    
    .product-image {
        width: 100%;
        height: 200px;
    }
}
</style>