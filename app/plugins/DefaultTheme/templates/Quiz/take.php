<?php
/**
 * Quiz Take Template
 * 
 * Interactive quiz form with multiple choice questions
 */

$this->assign('title', $quizInfo['title'] ?? __('Adapter Finder Quiz'));
$this->Html->meta('description', $quizInfo['description'] ?? __('Find the perfect adapter for your needs with our interactive quiz'), ['block' => 'meta']);
?>

<div class="quiz-take">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="quiz-header text-center mb-5">
                    <h1 class="quiz-title"><?= h($quizInfo['title']) ?></h1>
                    <p class="quiz-description lead">
                        <?= h($quizInfo['description']) ?>
                    </p>
                    <?php if (!empty($quizInfo['estimated_time'])): ?>
                        <p class="text-muted">
                            <i class="fas fa-clock"></i> <?= __('Estimated time: {0}', h($quizInfo['estimated_time'])) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (!empty($questions)): ?>
                    <!-- Quiz Form -->
                    <div class="quiz-container">
                        <?php if ($display['show_progress'] ?? true): ?>
                            <div class="quiz-progress-container mb-4">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                        <span class="progress-text">0%</span>
                                    </div>
                                </div>
                                <small class="text-muted mt-2 d-block text-center">
                                    <span id="current-question">1</span> of <span id="total-questions"><?= count($questions) ?></span> questions
                                </small>
                            </div>
                        <?php endif; ?>

                        <?= $this->Form->create(null, [
                            'id' => 'quiz-form',
                            'url' => ['action' => 'take'],
                            'class' => 'quiz-form'
                        ]) ?>

                        <div class="quiz-questions">
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="quiz-question <?= $index === 0 ? 'active' : '' ?>" data-question="<?= $index + 1 ?>">
                                    <div class="question-card">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="question-title mb-4">
                                                    <?= h($question['text']) ?>
                                                    <?php if ($question['required'] ?? false): ?>
                                                        <span class="text-danger">*</span>
                                                    <?php endif; ?>
                                                </h5>
                                                
                                                <?php if (!empty($question['help_text'])): ?>
                                                    <p class="text-muted small mb-3">
                                                        <i class="fas fa-info-circle"></i> <?= h($question['help_text']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="question-answers">
                                                    <?php 
                                                    $fieldName = 'answers.' . $question['id'];
                                                    $isMultiple = $question['multiple'] ?? false;
                                                    $inputType = $isMultiple ? 'checkbox' : 'radio';
                                                    ?>
                                                    
                                                    <?php if ($isMultiple): ?>
                                                        <!-- Multiple Selection (Checkboxes) -->
                                                        <?php foreach ($question['options'] as $option): ?>
                                                            <div class="form-check mb-3">
                                                                <?= $this->Form->checkbox(
                                                                    $fieldName . '.' . $option['key'], 
                                                                    [
                                                                        'value' => $option['key'],
                                                                        'class' => 'form-check-input',
                                                                        'id' => $fieldName . '-' . $option['key']
                                                                    ]
                                                                ) ?>
                                                                <label class="form-check-label" for="<?= $fieldName . '-' . $option['key'] ?>">
                                                                    <?= h($option['label']) ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <!-- Single Selection (Radio Buttons) -->
                                                        <?php 
                                                        $options = [];
                                                        foreach ($question['options'] as $option) {
                                                            $options[$option['key']] = $option['label'];
                                                        }
                                                        ?>
                                                        
                                                        <?= $this->Form->control($fieldName, [
                                                            'type' => 'radio',
                                                            'options' => $options,
                                                            'label' => false,
                                                            'required' => $question['required'] ?? false,
                                                            'class' => 'quiz-radio-group',
                                                            'templates' => [
                                                                'radioWrapper' => '<div class="form-check mb-3">{{label}}</div>',
                                                                'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}} class="form-check-input">',
                                                                'label' => '<label class="form-check-label"{{attrs}}>{{text}}</label>'
                                                            ]
                                                        ]) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Quiz Navigation -->
                        <div class="quiz-navigation mt-4">
                            <div class="row">
                                <div class="col-6">
                                    <?php if ($display['allow_back'] ?? true): ?>
                                        <button type="button" id="prev-question" class="btn btn-outline-secondary" style="display: none;">
                                            <i class="fas fa-arrow-left"></i> <?= __('Previous') ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="col-6 text-end">
                                    <button type="button" id="next-question" class="btn btn-primary">
                                        <?= __('Next') ?> <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button type="submit" id="submit-quiz" class="btn btn-success" style="display: none;">
                                        <i class="fas fa-check"></i> <?= __('Get My Recommendations') ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Quiz Results (Hidden Initially) -->
                    <div id="quiz-results" class="quiz-results" style="display: none;">
                        <div class="results-header text-center mb-4">
                            <h3><?= __('Your Personalized Recommendations') ?></h3>
                            <p class="text-muted"><?= __('Based on your answers, here are the products that best match your needs:') ?></p>
                        </div>
                        <div id="results-content">
                            <!-- Results will be loaded here via AJAX -->
                        </div>
                        <div class="text-center mt-4">
                            <button type="button" id="retake-quiz" class="btn btn-outline-primary">
                                <i class="fas fa-redo"></i> <?= __('Take Quiz Again') ?>
                            </button>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Quiz Disabled State -->
                    <div class="quiz-disabled text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-cog fa-3x text-muted"></i>
                        </div>
                        <h3><?= __('Quiz Temporarily Unavailable') ?></h3>
                        <p class="text-muted"><?= __('The quiz is currently being updated. Please check back later.') ?></p>
                        <div class="mt-4">
                            <?= $this->Html->link(
                                __('Browse All Products'),
                                ['controller' => 'Products', 'action' => 'index'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Help Section -->
                <div class="quiz-help mt-5">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-question-circle"></i> <?= __('Need Help?') ?>
                            </h6>
                            <p class="card-text">
                                <?= __('This quiz is designed to help you find the most suitable adapters based on your specific requirements. Take your time answering each question for the best recommendations.') ?>
                            </p>
                            <div class="mt-3">
                                <?= $this->Html->link(
                                    __('Browse All Products'),
                                    ['controller' => 'Products', 'action' => 'index'],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                                <?= $this->Html->link(
                                    __('Preview Quiz'),
                                    ['action' => 'preview'],
                                    ['class' => 'btn btn-outline-secondary btn-sm']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quizForm = document.getElementById('quiz-form');
    const questions = document.querySelectorAll('.quiz-question');
    const totalQuestions = questions.length;
    let currentQuestionIndex = 0;

    const prevButton = document.getElementById('prev-question');
    const nextButton = document.getElementById('next-question');
    const submitButton = document.getElementById('submit-quiz');
    const retakeButton = document.getElementById('retake-quiz');
    const progressBar = document.querySelector('.progress-bar');
    const currentQuestionSpan = document.getElementById('current-question');

    function updateProgress() {
        if (progressBar && currentQuestionSpan) {
            const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressBar.querySelector('.progress-text').textContent = Math.round(progress) + '%';
            currentQuestionSpan.textContent = currentQuestionIndex + 1;
        }
    }

    function showQuestion(index) {
        questions.forEach((question, i) => {
            question.classList.toggle('active', i === index);
        });

        if (prevButton) {
            prevButton.style.display = index > 0 ? 'block' : 'none';
        }
        if (nextButton) {
            nextButton.style.display = index < totalQuestions - 1 ? 'block' : 'none';
        }
        if (submitButton) {
            submitButton.style.display = index === totalQuestions - 1 ? 'block' : 'none';
        }

        updateProgress();
    }

    function isCurrentQuestionAnswered() {
        const currentQuestion = questions[currentQuestionIndex];
        const inputs = currentQuestion.querySelectorAll('input[type="radio"], input[type="checkbox"]');
        
        for (let input of inputs) {
            if (input.checked) return true;
        }
        return false;
    }

    if (nextButton) {
        nextButton.addEventListener('click', function() {
            // Check if current question is required and answered
            const currentQuestion = questions[currentQuestionIndex];
            const isRequired = currentQuestion.querySelector('.text-danger'); // has required asterisk
            
            if (isRequired && !isCurrentQuestionAnswered()) {
                alert('<?= __("Please answer this question before proceeding.") ?>');
                return;
            }

            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                showQuestion(currentQuestionIndex);
            }
        });
    }

    if (prevButton) {
        prevButton.addEventListener('click', function() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                showQuestion(currentQuestionIndex);
            }
        });
    }

    if (quizForm) {
        quizForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if final question is answered if required
            const currentQuestion = questions[currentQuestionIndex];
            const isRequired = currentQuestion.querySelector('.text-danger');
            
            if (isRequired && !isCurrentQuestionAnswered()) {
                alert('<?= __("Please answer this question before submitting.") ?>');
                return;
            }

            // Show loading state
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __("Finding Matches...") ?>';
                submitButton.disabled = true;
            }

            // Submit quiz via AJAX
            const formData = new FormData(quizForm);
            
            fetch(quizForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Hide quiz form and show results
                    document.querySelector('.quiz-container').style.display = 'none';
                    document.getElementById('quiz-results').style.display = 'block';
                    document.getElementById('results-content').innerHTML = data.resultsHtml;
                    
                    // Scroll to results
                    document.getElementById('quiz-results').scrollIntoView({ 
                        behavior: 'smooth' 
                    });
                } else {
                    throw new Error(data.message || 'Quiz submission failed');
                }
            })
            .catch(error => {
                console.error('Quiz Error:', error);
                alert('<?= __("Sorry, there was an error processing your quiz. Please try again.") ?>');
            })
            .finally(() => {
                if (submitButton) {
                    submitButton.innerHTML = '<i class="fas fa-check"></i> <?= __("Get My Recommendations") ?>';
                    submitButton.disabled = false;
                }
            });
        });
    }

    // Retake quiz functionality
    if (retakeButton) {
        retakeButton.addEventListener('click', function() {
            // Reset quiz state
            currentQuestionIndex = 0;
            
            // Clear all form inputs
            const inputs = quizForm.querySelectorAll('input[type="radio"], input[type="checkbox"]');
            inputs.forEach(input => input.checked = false);
            
            // Show quiz form, hide results
            document.querySelector('.quiz-container').style.display = 'block';
            document.getElementById('quiz-results').style.display = 'none';
            
            // Reset to first question
            showQuestion(0);
            
            // Scroll to top
            document.querySelector('.quiz-header').scrollIntoView({ 
                behavior: 'smooth' 
            });
        });
    }

    // Initialize first question
    if (totalQuestions > 0) {
        showQuestion(0);
    }
});
</script>

<style>
.quiz-container {
    max-width: 800px;
    margin: 0 auto;
}

.question-card .card {
    border: 2px solid #e9ecef;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    min-height: 300px;
}

.question-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.quiz-question {
    display: none;
    opacity: 0;
    transform: translateX(20px);
    transition: all 0.3s ease;
}

.quiz-question.active {
    display: block;
    opacity: 1;
    transform: translateX(0);
}

.form-check {
    padding: 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
}

.form-check:hover {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.form-check input:checked + label {
    font-weight: 500;
    color: #0d6efd;
}

.form-check input:checked {
    border-color: #0d6efd;
}

.quiz-navigation button {
    min-width: 120px;
}

.quiz-progress-container {
    position: sticky;
    top: 20px;
    z-index: 100;
    background: white;
    padding: 15px 0;
    margin-bottom: 30px;
}

.progress {
    height: 8px;
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
    font-size: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: width 0.3s ease;
}

.quiz-help .card {
    border-left: 4px solid #17a2b8;
}

.quiz-results {
    animation: slideInUp 0.5s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .question-card .card {
        min-height: 250px;
    }
    
    .quiz-navigation .col-6:first-child {
        text-align: left;
    }
    
    .quiz-navigation button {
        width: 100%;
        margin-bottom: 10px;
    }
}
</style>
