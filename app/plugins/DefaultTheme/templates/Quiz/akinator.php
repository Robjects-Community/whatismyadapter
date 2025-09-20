<?php
/**
 * Akinator-Style Quiz Template
 * 
 * Interactive yes/no questioning system with AI-powered decision tree
 * Supports both JavaScript enhanced and server-side fallback modes
 */

$this->assign('title', __('Akinator Product Quiz'));
$this->Html->meta('description', __('Answer yes/no questions as our AI gradually narrows down to your perfect adapter match. Interactive and fun!'), ['block' => 'meta']);

// Include the quiz JavaScript module
$this->Html->script('DefaultTheme.quiz', ['block' => 'script']);
?>

<div class="quiz-akinator">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="quiz-header text-center mb-4">
                    <h1 class="quiz-title"><?= __('Akinator Product Quiz') ?></h1>
                    <p class="quiz-description">
                        <?= __('Answer questions and watch as our AI narrows down to your perfect match!') ?>
                    </p>
                    
                    <!-- Progress indicators -->
                    <div class="akinator-progress">
                        <div class="progress-info">
                            <span class="question-counter">
                                <?= __('Question') ?> <span id="current-question-num">1</span>
                            </span>
                            <span class="confidence-display">
                                <?= __('Confidence') ?>: <span id="confidence-score">0%</span>
                            </span>
                        </div>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-primary" id="confidence-bar" role="progressbar" style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript-enhanced Akinator interface -->
                <div id="akinator-container" class="akinator-container" style="display: none;">
                    <!-- Question Display -->
                    <div class="question-display">
                        <div class="card question-card">
                            <div class="card-body text-center">
                                <div class="akinator-avatar mb-3">
                                    <i class="fas fa-magic fa-3x text-primary"></i>
                                </div>
                                <h4 class="question-text mb-4" id="akinator-question">
                                    <?= __('Loading question...') ?>
                                </h4>
                                
                                <!-- Answer Options - Dynamically populated -->
                                <div class="answer-options" id="answer-options">
                                    <!-- Options will be populated dynamically by JavaScript -->
                                </div>

                                <!-- Navigation -->
                                <div class="akinator-navigation mt-4">
                                    <button type="button" class="btn btn-outline-secondary" id="back-button" style="display: none;">
                                        <i class="fas fa-arrow-left"></i> <?= __('Back') ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-info ms-2" id="restart-button">
                                        <i class="fas fa-redo"></i> <?= __('Restart') ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Results Display -->
                    <div class="results-display" id="results-display" style="display: none;">
                        <div class="card results-card">
                            <div class="card-body text-center">
                                <div class="results-header mb-4">
                                    <div class="akinator-avatar mb-3">
                                        <i class="fas fa-lightbulb fa-3x text-success"></i>
                                    </div>
                                    <h4 class="results-title"><?= __('I think I found your match!') ?></h4>
                                    <p class="results-subtitle text-muted">
                                        <?= __('Based on your answers, here are my top recommendations:') ?>
                                    </p>
                                </div>
                                
                                <!-- Results will be populated here -->
                                <div id="akinator-results" class="akinator-results">
                                    <!-- Dynamic content -->
                                </div>

                                <!-- Actions -->
                                <div class="results-actions mt-4">
                                    <button type="button" class="btn btn-primary" id="play-again-button">
                                        <i class="fas fa-play"></i> <?= __('Play Again') ?>
                                    </button>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-list"></i> ' . __('Try Comprehensive Quiz'),
                                        ['action' => 'comprehensive'],
                                        [
                                            'class' => 'btn btn-outline-success ms-2',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Loading State -->
                    <div class="loading-state" id="loading-state" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-3" role="status">
                                <span class="visually-hidden"><?= __('Loading...') ?></span>
                            </div>
                            <p class="text-muted"><?= __('Processing your answer...') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Server-side fallback form (no JavaScript) -->
                <noscript>
                    <div class="no-js-fallback">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= __('For the best Akinator experience, please enable JavaScript. You can still use our comprehensive quiz below:') ?>
                        </div>
                        <div class="text-center">
                            <?= $this->Html->link(
                                __('Start Comprehensive Quiz'),
                                ['action' => 'comprehensive'],
                                ['class' => 'btn btn-primary btn-lg']
                            ) ?>
                        </div>
                    </div>
                </noscript>

                <!-- Help Section -->
                <div class="quiz-help mt-5">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-question-circle"></i> <?= __('How to Play') ?>
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success"></i> <?= __('Choose the best option for each question') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('Be as honest and specific as possible') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('Use "Back" to change previous answers') ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-star text-warning"></i> <?= __('AI learns from each answer') ?></li>
                                        <li><i class="fas fa-star text-warning"></i> <?= __('Usually takes 5-10 questions') ?></li>
                                        <li><i class="fas fa-star text-warning"></i> <?= __('Get personalized recommendations') ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error handling -->
                <div class="alert alert-danger" id="error-alert" style="display: none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="error-message"><?= __('Something went wrong. Please try again.') ?></span>
                    <button type="button" class="btn-close" aria-label="Close" onclick="this.parentElement.style.display='none'"></button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quiz-akinator {
    padding: 2rem 0;
    min-height: 70vh;
}

.quiz-title {
    font-size: 2.25rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.quiz-description {
    font-size: 1.1rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.akinator-progress {
    margin-bottom: 2rem;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.progress {
    height: 8px;
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.5s ease;
}

.question-card, .results-card {
    border: 2px solid #dee2e6;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-radius: 15px;
    min-height: 400px;
    display: flex;
    align-items: center;
}

.question-card .card-body, .results-card .card-body {
    padding: 2rem;
    width: 100%;
}

.akinator-avatar {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.question-text {
    font-size: 1.4rem;
    font-weight: 600;
    color: #2c3e50;
    line-height: 1.4;
    min-height: 3.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.answer-options {
    margin: 1.5rem 0;
}

.answer-options .btn {
    min-width: 120px;
    padding: 0.75rem 1.5rem;
    margin: 0.5rem;
    border-radius: 25px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.answer-options .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.akinator-navigation {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.results-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #198754;
    margin-bottom: 0.5rem;
}

.results-subtitle {
    font-size: 1rem;
    margin-bottom: 1.5rem;
}

.loading-state {
    padding: 3rem 0;
    text-align: center;
}

.no-js-fallback {
    margin: 2rem 0;
    padding: 2rem;
    border-radius: 10px;
    background-color: #f8f9fa;
}

.quiz-help .card {
    border-left-width: 4px;
}

/* Animation for state transitions */
.question-display, .results-display {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.question-display.active, .results-display.active {
    opacity: 1;
    transform: translateY(0);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .quiz-title {
        font-size: 1.75rem;
    }
    
    .question-text {
        font-size: 1.2rem;
        min-height: auto;
        margin-bottom: 1rem;
    }
    
    .answer-options .btn {
        display: block;
        width: 100%;
        margin: 0.5rem 0;
        min-width: auto;
    }
    
    .akinator-navigation .btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .progress-info {
        font-size: 0.8rem;
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    .question-card, .results-card {
        background-color: #2c3e50;
        border-color: #495057;
        color: #fff;
    }
    
    .question-text {
        color: #fff;
    }
    
    .akinator-progress {
        color: #fff;
    }
}
</style>

<script>
// Initialize Akinator when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, checking for quiz module...');
    
    // Wait a bit for the quiz module to load
    setTimeout(function() {
        if (typeof window.initializeAkinator === 'function') {
            console.log('Quiz module found, initializing Akinator...');
            
            // Show the enhanced interface
            document.getElementById('akinator-container').style.display = 'block';
            
            // Initialize the Akinator quiz
            window.initializeAkinator().catch(function(error) {
                console.error('Akinator initialization failed:', error);
                showFallbackMessage();
            });
        } else {
            console.warn('Quiz module not available, showing fallback');
            showFallbackMessage();
        }
    }, 500); // Wait 500ms for module to load
    
    function showFallbackMessage() {
        const container = document.getElementById('akinator-container');
        if (container) {
            container.innerHTML = `
                <div class="alert alert-warning text-center">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h5><?= __('Quiz Temporarily Unavailable') ?></h5>
                    <p><?= __('The Akinator quiz is currently unavailable. Please try our comprehensive quiz instead.') ?></p>
                    <a href="<?= $this->Url->build(['action' => 'comprehensive']) ?>" class="btn btn-primary">
                        <?= __('Start Comprehensive Quiz') ?>
                    </a>
                </div>
            `;
            container.style.display = 'block';
        }
    }
});
</script>
