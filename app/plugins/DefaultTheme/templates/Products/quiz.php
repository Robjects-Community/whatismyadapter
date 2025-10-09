<?php
/**
 * Products Quiz Template
 * 
 * Interactive quiz to help users find suitable adapters
 */

$this->assign('title', __('Adapter Finder Quiz'));
$this->Html->meta('description', __('Find the perfect adapter for your needs with our interactive quiz'), ['block' => 'meta']);
?>

<div class="products-quiz">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="quiz-header text-center mb-5">
                    <h1 class="quiz-title"><?= __('Find Your Perfect Adapter') ?></h1>
                    <p class="quiz-description lead">
                        <?= __('Answer a few questions and we\'ll recommend the best adapters for your specific needs.') ?>
                    </p>
                </div>

                <?php if (!empty($quizQuestions)): ?>
                    <!-- Quiz Form -->
                    <div class="quiz-container">
                        <div class="quiz-progress-container mb-4">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                    <span class="progress-text">0%</span>
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block text-center">
                                <span id="current-question">1</span> of <span id="total-questions"><?= count($quizQuestions) ?></span> questions
                            </small>
                        </div>

                        <?= $this->Form->create(null, [
                            'id' => 'quiz-form',
                            'url' => ['action' => 'quiz'],
                            'class' => 'quiz-form'
                        ]) ?>

                        <div class="quiz-questions">
                            <?php foreach ($quizQuestions as $index => $question): ?>
                                <div class="quiz-question <?= $index === 0 ? 'active' : '' ?>" data-question="<?= $index + 1 ?>">
                                    <div class="question-card">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="question-title mb-4"><?= h($question['question']) ?></h5>
                                                
                                                <div class="question-answers">
                                                    <?php 
                                                    $fieldName = 'answers.' . $index;
                                                    $inputType = $question['type'] ?? 'radio';
                                                    ?>
                                                    
                                                    <?php if ($inputType === 'radio'): ?>
                                                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                                            <div class="form-check mb-3">
                                                                <?= $this->Form->radio($fieldName, [
                                                                    $optionIndex => $option
                                                                ], [
                                                                    'class' => 'form-check-input',
                                                                    'hiddenField' => false,
                                                                    'legend' => false,
                                                                    'templates' => [
                                                                        'radioContainer' => '<div class="form-check">{{input}}{{label}}</div>',
                                                                        'radioWrapper' => '{{label}}',
                                                                        'radio' => '<input type="radio" name="{{name}}" value="{{value}}"{{attrs}}>',
                                                                        'label' => '<label class="form-check-label"{{attrs}}>{{text}}</label>'
                                                                    ]
                                                                ]) ?>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php elseif ($inputType === 'checkbox'): ?>
                                                        <?php foreach ($question['options'] as $optionIndex => $option): ?>
                                                            <div class="form-check mb-3">
                                                                <?= $this->Form->checkbox($fieldName . '.' . $optionIndex, [
                                                                    'value' => $optionIndex,
                                                                    'class' => 'form-check-input'
                                                                ]) ?>
                                                                <label class="form-check-label" for="<?= $fieldName . '-' . $optionIndex ?>">
                                                                    <?= h($option) ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php elseif ($inputType === 'select'): ?>
                                                        <?= $this->Form->control($fieldName, [
                                                            'type' => 'select',
                                                            'options' => $question['options'],
                                                            'empty' => __('Select an option'),
                                                            'class' => 'form-select',
                                                            'label' => false
                                                        ]) ?>
                                                    <?php else: ?>
                                                        <?= $this->Form->control($fieldName, [
                                                            'type' => 'text',
                                                            'class' => 'form-control',
                                                            'placeholder' => __('Your answer...'),
                                                            'label' => false
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
                                    <button type="button" id="prev-question" class="btn btn-outline-secondary" style="display: none;">
                                        <i class="fas fa-arrow-left"></i> <?= __('Previous') ?>
                                    </button>
                                </div>
                                <div class="col-6 text-end">
                                    <button type="button" id="next-question" class="btn btn-primary">
                                        <?= __('Next') ?> <i class="fas fa-arrow-right"></i>
                                    </button>
                                    <button type="submit" id="submit-quiz" class="btn btn-success" style="display: none;">
                                        <i class="fas fa-check"></i> <?= __('Get Results') ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>

                    <!-- Quiz Results (Hidden Initially) -->
                    <div id="quiz-results" class="quiz-results" style="display: none;">
                        <div class="results-header text-center mb-4">
                            <h3><?= __('Your Recommended Products') ?></h3>
                            <p class="text-muted"><?= __('Based on your answers, here are the products that best match your needs:') ?></p>
                        </div>
                        <div id="results-content">
                            <!-- Results will be loaded here via AJAX -->
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
                                ['action' => 'index'],
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
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                                <?= $this->Html->link(
                                    __('Contact Support'),
                                    ['controller' => 'Articles', 'action' => 'index'],
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
    const progressBar = document.querySelector('.progress-bar');
    const currentQuestionSpan = document.getElementById('current-question');

    function updateProgress() {
        const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
        progressBar.style.width = progress + '%';
        progressBar.setAttribute('aria-valuenow', progress);
        progressBar.querySelector('.progress-text').textContent = Math.round(progress) + '%';
        currentQuestionSpan.textContent = currentQuestionIndex + 1;
    }

    function showQuestion(index) {
        questions.forEach((question, i) => {
            question.classList.toggle('active', i === index);
        });

        prevButton.style.display = index > 0 ? 'block' : 'none';
        nextButton.style.display = index < totalQuestions - 1 ? 'block' : 'none';
        submitButton.style.display = index === totalQuestions - 1 ? 'block' : 'none';

        updateProgress();
    }

    function isCurrentQuestionAnswered() {
        const currentQuestion = questions[currentQuestionIndex];
        const inputs = currentQuestion.querySelectorAll('input, select, textarea');
        
        for (let input of inputs) {
            if (input.type === 'radio' || input.type === 'checkbox') {
                if (input.checked) return true;
            } else if (input.value.trim() !== '') {
                return true;
            }
        }
        return false;
    }

    nextButton.addEventListener('click', function() {
        if (!isCurrentQuestionAnswered()) {
            alert('<?= __("Please answer the current question before proceeding.") ?>');
            return;
        }

        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
        }
    });

    prevButton.addEventListener('click', function() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
        }
    });

    quizForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (!isCurrentQuestionAnswered()) {
            alert('<?= __("Please answer all questions before submitting.") ?>');
            return;
        }

        // Show loading state
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <?= __("Calculating...") ?>';
        submitButton.disabled = true;

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
            submitButton.innerHTML = '<i class="fas fa-check"></i> <?= __("Get Results") ?>';
            submitButton.disabled = false;
        });
    });

    // Initialize first question
    showQuestion(0);

    // Add smooth transitions
    const style = document.createElement('style');
    style.textContent = `
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
        .progress-bar {
            transition: width 0.3s ease;
        }
    `;
    document.head.appendChild(style);
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
