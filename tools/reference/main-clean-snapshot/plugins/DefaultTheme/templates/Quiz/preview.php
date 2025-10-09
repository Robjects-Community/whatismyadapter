<?php
/**
 * Quiz Preview Template
 * 
 * Read-only preview of quiz questions (no submission)
 */

$this->assign('title', $quizInfo['title'] ?? __('Quiz Preview'));
$this->Html->meta('description', $quizInfo['description'] ?? __('Preview of the adapter finder quiz questions'), ['block' => 'meta']);
?>

<div class="quiz-preview">
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
                    
                    <div class="alert alert-info">
                        <i class="fas fa-eye"></i> <?= __('This is a preview of the quiz questions. No answers will be submitted.') ?>
                    </div>
                </div>

                <?php if (!empty($questions)): ?>
                    <!-- Quiz Preview -->
                    <div class="quiz-preview-container">
                        <div class="quiz-questions">
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="quiz-question-preview mb-4">
                                    <div class="question-card">
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <h6 class="mb-0">
                                                    <?= __('Question {0} of {1}', $index + 1, count($questions)) ?>
                                                    <?php if ($question['required'] ?? false): ?>
                                                        <span class="badge bg-danger ms-2"><?= __('Required') ?></span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary ms-2"><?= __('Optional') ?></span>
                                                    <?php endif; ?>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="question-title mb-4">
                                                    <?= h($question['text']) ?>
                                                </h5>
                                                
                                                <?php if (!empty($question['help_text'])): ?>
                                                    <p class="text-muted small mb-3">
                                                        <i class="fas fa-info-circle"></i> <?= h($question['help_text']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="question-answers">
                                                    <?php 
                                                    $isMultiple = $question['multiple'] ?? false;
                                                    ?>
                                                    
                                                    <?php if ($isMultiple): ?>
                                                        <p class="text-info small">
                                                            <i class="fas fa-check-square"></i> <?= __('Multiple selections allowed') ?>
                                                        </p>
                                                        <?php foreach ($question['options'] as $option): ?>
                                                            <div class="form-check mb-2 preview-option">
                                                                <input class="form-check-input" type="checkbox" disabled>
                                                                <label class="form-check-label text-muted">
                                                                    <?= h($option['label']) ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <p class="text-info small">
                                                            <i class="fas fa-dot-circle"></i> <?= __('Single selection only') ?>
                                                        </p>
                                                        <?php foreach ($question['options'] as $option): ?>
                                                            <div class="form-check mb-2 preview-option">
                                                                <input class="form-check-input" type="radio" disabled>
                                                                <label class="form-check-label text-muted">
                                                                    <?= h($option['label']) ?>
                                                                </label>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Preview Actions -->
                        <div class="quiz-preview-actions text-center mt-5">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-play"></i> <?= __('Ready to Take the Quiz?') ?>
                                    </h5>
                                    <p class="card-text">
                                        <?= __('This quiz contains {0} questions and should take about {1} to complete.', 
                                            count($questions), 
                                            $quizInfo['estimated_time'] ?? '2-3 minutes'
                                        ) ?>
                                    </p>
                                    <div class="mt-3">
                                        <?= $this->Html->link(
                                            __('Start Quiz'),
                                            ['action' => 'take'],
                                            ['class' => 'btn btn-primary btn-lg']
                                        ) ?>
                                        <?= $this->Html->link(
                                            __('Browse Products Instead'),
                                            ['controller' => 'Products', 'action' => 'index'],
                                            ['class' => 'btn btn-outline-secondary btn-lg ms-2']
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- No Questions Available -->
                    <div class="quiz-disabled text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-exclamation-triangle fa-3x text-muted"></i>
                        </div>
                        <h3><?= __('Quiz Not Available') ?></h3>
                        <p class="text-muted"><?= __('No quiz questions are currently configured.') ?></p>
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
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="fas fa-question-circle"></i> <?= __('About This Quiz') ?>
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= __('How it works:') ?></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-check text-success"></i> Answer questions about your device</li>
                                        <li><i class="fas fa-check text-success"></i> Get personalized recommendations</li>
                                        <li><i class="fas fa-check text-success"></i> View matching products with confidence scores</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><?= __('Features:') ?></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-star text-warning"></i> Smart product matching</li>
                                        <li><i class="fas fa-star text-warning"></i> Budget-aware filtering</li>
                                        <li><i class="fas fa-star text-warning"></i> Compatibility verification</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?= $this->Html->link(
                                    __('Start the Quiz'),
                                    ['action' => 'take'],
                                    ['class' => 'btn btn-primary btn-sm']
                                ) ?>
                                <?= $this->Html->link(
                                    __('Browse Products'),
                                    ['controller' => 'Products', 'action' => 'index'],
                                    ['class' => 'btn btn-outline-primary btn-sm']
                                ) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quiz-preview-container {
    max-width: 800px;
    margin: 0 auto;
}

.question-card .card {
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.question-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2c3e50;
}

.preview-option {
    padding: 8px;
    border: 1px solid #f0f0f0;
    border-radius: 6px;
    background-color: #fafafa;
}

.preview-option input {
    opacity: 0.5;
}

.preview-option label {
    opacity: 0.7;
}

.quiz-help .card {
    border-left: 4px solid #6c757d;
}

.quiz-preview-actions .card {
    border: 2px solid #0d6efd;
}

.badge {
    font-size: 0.7em;
}

@media (max-width: 768px) {
    .btn-lg {
        display: block;
        margin-bottom: 10px;
    }
    
    .ms-2 {
        margin-left: 0 !important;
    }
}
</style>
