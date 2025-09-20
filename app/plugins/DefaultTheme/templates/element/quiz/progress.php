<?php
/**
 * Progress Element for Quiz
 * 
 * Displays progress indicators for quiz completion
 * 
 * Variables:
 * - $currentStep: Current step number
 * - $totalSteps: Total number of steps
 * - $progress: Progress percentage (0-100)
 * - $confidence: Confidence score for Akinator mode (optional)
 * - $questionCount: Number of questions answered (optional)
 * - $mode: 'steps' for comprehensive quiz, 'confidence' for Akinator
 */

$currentStep = $currentStep ?? 1;
$totalSteps = $totalSteps ?? 1;
$progress = $progress ?? 0;
$confidence = $confidence ?? 0;
$questionCount = $questionCount ?? 0;
$mode = $mode ?? 'steps';
?>

<div class="quiz-progress" data-mode="<?= $mode ?>">
    <?php if ($mode === 'steps'): ?>
        <!-- Step-based Progress (Comprehensive Quiz) -->
        <div class="progress-header">
            <div class="step-info">
                <span class="step-indicator">
                    <?= __('Step {0} of {1}', $currentStep, $totalSteps) ?>
                </span>
            </div>
            <div class="completion-info">
                <span class="completion-percentage">
                    <?= round($progress) ?>% <?= __('Complete') ?>
                </span>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar bg-success" 
                     role="progressbar" 
                     style="width: <?= $progress ?>%"
                     aria-valuenow="<?= $progress ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        </div>
        
        <!-- Step indicators -->
        <?php if ($totalSteps > 1): ?>
        <div class="step-indicators">
            <?php for ($i = 1; $i <= $totalSteps; $i++): ?>
                <div class="step-dot <?= $i === $currentStep ? 'active' : ($i < $currentStep ? 'completed' : '') ?>">
                    <?php if ($i < $currentStep): ?>
                        <i class="fas fa-check"></i>
                    <?php else: ?>
                        <?= $i ?>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        
    <?php else: ?>
        <!-- Confidence-based Progress (Akinator Quiz) -->
        <div class="progress-header">
            <div class="question-info">
                <span class="question-counter">
                    <?= __('Question {0}', $questionCount) ?>
                </span>
            </div>
            <div class="confidence-info">
                <span class="confidence-score">
                    <?= __('Confidence: {0}%', round($confidence)) ?>
                </span>
            </div>
        </div>
        
        <div class="progress-container">
            <div class="progress">
                <div class="progress-bar bg-primary" 
                     role="progressbar" 
                     style="width: <?= $confidence ?>%"
                     aria-valuenow="<?= $confidence ?>" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                </div>
            </div>
        </div>
        
        <!-- Confidence indicator -->
        <div class="confidence-indicator">
            <div class="confidence-scale">
                <div class="scale-marker low" style="left: 25%">
                    <span><?= __('Low') ?></span>
                </div>
                <div class="scale-marker medium" style="left: 70%">
                    <span><?= __('Good') ?></span>
                </div>
                <div class="scale-marker high" style="left: 90%">
                    <span><?= __('High') ?></span>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Additional Info -->
    <?php if (!empty($timeRemaining) || !empty($estimatedTime)): ?>
    <div class="time-info text-center mt-2">
        <small class="text-muted">
            <i class="fas fa-clock"></i>
            <?php if (!empty($timeRemaining)): ?>
                <?= __('Est. time remaining: {0}', $timeRemaining) ?>
            <?php elseif (!empty($estimatedTime)): ?>
                <?= __('Est. total time: {0}', $estimatedTime) ?>
            <?php endif; ?>
        </small>
    </div>
    <?php endif; ?>
</div>

<style>
.quiz-progress {
    max-width: 600px;
    margin: 0 auto 2rem;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.9rem;
    font-weight: 600;
}

.step-indicator,
.question-counter {
    color: #495057;
}

.completion-percentage,
.confidence-score {
    color: #28a745;
}

.progress-container {
    margin-bottom: 1rem;
}

.progress {
    height: 10px;
    border-radius: 10px;
    background-color: #e9ecef;
    overflow: hidden;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.5s ease;
}

/* Step Indicators */
.step-indicators {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    position: relative;
}

.step-indicators::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #dee2e6;
    z-index: 1;
}

.step-dot {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background-color: #e9ecef;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    font-weight: 600;
    color: #6c757d;
    position: relative;
    z-index: 2;
    transition: all 0.3s ease;
}

.step-dot.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    transform: scale(1.1);
}

.step-dot.completed {
    background-color: #28a745;
    border-color: #28a745;
    color: white;
}

/* Confidence Scale */
.confidence-indicator {
    margin-top: 0.5rem;
    position: relative;
}

.confidence-scale {
    position: relative;
    height: 20px;
}

.scale-marker {
    position: absolute;
    transform: translateX(-50%);
    text-align: center;
}

.scale-marker span {
    font-size: 0.7rem;
    font-weight: 500;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    border: 1px solid #dee2e6;
    white-space: nowrap;
}

.scale-marker.low span {
    color: #dc3545;
    border-color: #dc3545;
    background-color: #fff5f5;
}

.scale-marker.medium span {
    color: #ffc107;
    border-color: #ffc107;
    background-color: #fffbf0;
}

.scale-marker.high span {
    color: #28a745;
    border-color: #28a745;
    background-color: #f0fff4;
}

.time-info {
    border-top: 1px solid #dee2e6;
    padding-top: 0.75rem;
    margin-top: 0.75rem;
}

/* Akinator mode specific styling */
.quiz-progress[data-mode="confidence"] .progress-bar {
    background: linear-gradient(90deg, #dc3545 0%, #ffc107 50%, #28a745 100%);
}

.quiz-progress[data-mode="confidence"] .confidence-info {
    font-size: 1rem;
    font-weight: 700;
}

/* Animation for progress changes */
@keyframes progressPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); }
    100% { transform: scale(1); }
}

.quiz-progress.updating {
    animation: progressPulse 0.5s ease;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .quiz-progress {
        padding: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .progress-header {
        font-size: 0.8rem;
        margin-bottom: 0.5rem;
    }
    
    .progress {
        height: 8px;
    }
    
    .step-dot {
        width: 28px;
        height: 28px;
        font-size: 0.7rem;
    }
    
    .step-dot.active {
        transform: scale(1.05);
    }
    
    .scale-marker span {
        font-size: 0.6rem;
        padding: 0.15rem 0.3rem;
    }
    
    .time-info {
        font-size: 0.8rem;
    }
}

/* Print styles */
@media print {
    .quiz-progress {
        border: 1px solid #000;
        background-color: #fff;
    }
    
    .step-dot.active,
    .step-dot.completed {
        background-color: #000 !important;
        border-color: #000 !important;
    }
    
    .progress-bar {
        background-color: #000 !important;
    }
}
</style>

<script>
// Progress update functionality
window.QuizProgress = {
    update: function(data) {
        const progressEl = document.querySelector('.quiz-progress');
        if (!progressEl) return;
        
        progressEl.classList.add('updating');
        
        // Update step-based progress
        if (data.currentStep !== undefined) {
            const stepIndicator = progressEl.querySelector('.step-indicator');
            const completionPercentage = progressEl.querySelector('.completion-percentage');
            const progressBar = progressEl.querySelector('.progress-bar');
            
            if (stepIndicator) {
                stepIndicator.textContent = `Step ${data.currentStep} of ${data.totalSteps}`;
            }
            
            if (completionPercentage && data.progress !== undefined) {
                completionPercentage.textContent = Math.round(data.progress) + '% Complete';
            }
            
            if (progressBar && data.progress !== undefined) {
                progressBar.style.width = data.progress + '%';
                progressBar.setAttribute('aria-valuenow', data.progress);
            }
            
            // Update step dots
            const stepDots = progressEl.querySelectorAll('.step-dot');
            stepDots.forEach((dot, index) => {
                const stepNum = index + 1;
                dot.classList.remove('active', 'completed');
                
                if (stepNum === data.currentStep) {
                    dot.classList.add('active');
                } else if (stepNum < data.currentStep) {
                    dot.classList.add('completed');
                    dot.innerHTML = '<i class="fas fa-check"></i>';
                } else {
                    dot.textContent = stepNum;
                }
            });
        }
        
        // Update confidence-based progress
        if (data.confidence !== undefined) {
            const confidenceScore = progressEl.querySelector('.confidence-score');
            const progressBar = progressEl.querySelector('.progress-bar');
            
            if (confidenceScore) {
                confidenceScore.textContent = `Confidence: ${Math.round(data.confidence)}%`;
            }
            
            if (progressBar) {
                progressBar.style.width = data.confidence + '%';
                progressBar.setAttribute('aria-valuenow', data.confidence);
            }
        }
        
        if (data.questionCount !== undefined) {
            const questionCounter = progressEl.querySelector('.question-counter');
            if (questionCounter) {
                questionCounter.textContent = `Question ${data.questionCount}`;
            }
        }
        
        // Remove updating class after animation
        setTimeout(() => {
            progressEl.classList.remove('updating');
        }, 500);
    }
};
</script>
