<?php
/**
 * Quiz Result Template
 * 
 * Displays matched products with confidence scores and rationale
 */

$this->assign('title', __('Your Quiz Results'));
$this->Html->meta('description', __('Discover your personalized product recommendations based on your quiz answers.'), ['block' => 'meta']);
?>

<div class="quiz-result">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <!-- Results Header -->
                <div class="results-header text-center mb-5">
                    <div class="results-icon mb-3">
                        <i class="fas fa-trophy fa-3x text-warning"></i>
                    </div>
                    <h1 class="results-title"><?= __('Your Perfect Matches!') ?></h1>
                    <p class="results-description">
                        <?= __('Based on your answers, we found {0} products that match your needs with an average confidence of {1}%.', 
                            count($matches ?? []), 
                            isset($averageConfidence) ? round($averageConfidence) : '95'
                        ) ?>
                    </p>
                    
                    <?php if (!empty($quizType)): ?>
                    <div class="quiz-type-badge">
                        <span class="badge bg-<?= $quizType === 'akinator' ? 'primary' : 'success' ?> fs-6">
                            <i class="fas fa-<?= $quizType === 'akinator' ? 'magic' : 'list-check' ?>"></i>
                            <?= $quizType === 'akinator' ? __('Akinator Quiz') : __('Comprehensive Quiz') ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($matches)): ?>
                    <!-- Product Matches -->
                    <div class="product-matches mb-5">
                        <?php foreach ($matches as $index => $match): ?>
                            <div class="product-match-card mb-4" data-match-index="<?= $index ?>">
                                <?= $this->element('quiz/product_card', [
                                    'product' => $match['product'] ?? [],
                                    'confidence' => $match['confidence'] ?? 0,
                                    'rationale' => $match['rationale'] ?? '',
                                    'rank' => $index + 1,
                                    'isTopMatch' => $index === 0
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Result Summary -->
                    <?php if (!empty($resultSummary)): ?>
                    <div class="result-summary mb-5">
                        <div class="card border-info">
                            <div class="card-header bg-info text-white">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-lightbulb"></i> <?= __('Why These Recommendations?') ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <p class="card-text"><?= h($resultSummary) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Additional Recommendations -->
                    <?php if (!empty($alternativeMatches)): ?>
                    <div class="alternative-matches mb-5">
                        <h4 class="section-title">
                            <i class="fas fa-list"></i> <?= __('Alternative Options') ?>
                        </h4>
                        <p class="section-description text-muted">
                            <?= __('These products also matched your requirements but with lower confidence scores.') ?>
                        </p>
                        
                        <div class="row">
                            <?php foreach ($alternativeMatches as $altMatch): ?>
                                <div class="col-md-6 mb-3">
                                    <?= $this->element('quiz/product_card', [
                                        'product' => $altMatch['product'] ?? [],
                                        'confidence' => $altMatch['confidence'] ?? 0,
                                        'rationale' => $altMatch['rationale'] ?? '',
                                        'isAlternative' => true
                                    ]) ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php else: ?>
                    <!-- No Matches Found -->
                    <div class="no-matches text-center py-5">
                        <div class="no-matches-icon mb-3">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h3><?= __('No Perfect Matches Found') ?></h3>
                        <p class="text-muted mb-4">
                            <?= __('We couldn\'t find products that exactly match your requirements, but here are some suggestions:') ?>
                        </p>
                        
                        <div class="suggestions">
                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?= __('What you can do:') ?></h5>
                                            <ul class="list-unstyled">
                                                <li class="mb-2">
                                                    <i class="fas fa-redo text-primary"></i>
                                                    <?= $this->Html->link(
                                                        __('Retake the quiz with different answers'),
                                                        ['action' => $quizType ?? 'index'],
                                                        ['class' => 'ms-2']
                                                    ) ?>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-search text-success"></i>
                                                    <?= $this->Html->link(
                                                        __('Browse all products manually'),
                                                        ['controller' => 'Products', 'action' => 'index'],
                                                        ['class' => 'ms-2']
                                                    ) ?>
                                                </li>
                                                <li class="mb-2">
                                                    <i class="fas fa-envelope text-info"></i>
                                                    <span class="ms-2"><?= __('Contact our support team for personalized help') ?></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="result-actions text-center mb-5">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('What\'s Next?') ?></h5>
                            <div class="action-buttons">
                                <div class="row g-2 justify-content-center">
                                    <div class="col-auto">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-redo"></i> ' . __('Take Quiz Again'),
                                            ['action' => 'index'],
                                            [
                                                'class' => 'btn btn-primary',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                    <div class="col-auto">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-exchange-alt"></i> ' . __('Try Other Quiz Type'),
                                            ['action' => $quizType === 'akinator' ? 'comprehensive' : 'akinator'],
                                            [
                                                'class' => 'btn btn-outline-secondary',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                    <div class="col-auto">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-th-large"></i> ' . __('Browse All Products'),
                                            ['controller' => 'Products', 'action' => 'index'],
                                            [
                                                'class' => 'btn btn-outline-info',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quiz Info -->
                <?php if (!empty($submission)): ?>
                <div class="quiz-info">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <h6 class="card-title text-secondary">
                                <i class="fas fa-info-circle"></i> <?= __('Quiz Information') ?>
                            </h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong><?= __('Quiz Type:') ?></strong> 
                                        <?= ucfirst($submission['quiz_type'] ?? $quizType ?? 'Unknown') ?>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong><?= __('Completed:') ?></strong> 
                                        <?= isset($submission['created']) ? $this->Time->nice($submission['created']) : __('Just now') ?>
                                    </small>
                                </div>
                            </div>
                            <?php if (!empty($submission['session_id'])): ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <strong><?= __('Session ID:') ?></strong> 
                                    <code><?= h(substr($submission['session_id'], -8)) ?></code>
                                    <span class="ms-2"><?= __('(for support reference)') ?></span>
                                </small>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.quiz-result {
    padding: 2rem 0;
    min-height: 80vh;
}

.results-header {
    margin-bottom: 3rem;
}

.results-icon {
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.results-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.results-description {
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 1.5rem;
}

.quiz-type-badge {
    margin-top: 1rem;
}

.quiz-type-badge .badge {
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
}

.product-match-card {
    position: relative;
    animation: fadeInUp 0.6s ease;
    animation-fill-mode: both;
}

.product-match-card[data-match-index="0"] {
    animation-delay: 0.1s;
}

.product-match-card[data-match-index="1"] {
    animation-delay: 0.2s;
}

.product-match-card[data-match-index="2"] {
    animation-delay: 0.3s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.section-title {
    font-size: 1.75rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.section-description {
    font-size: 1rem;
    margin-bottom: 1.5rem;
}

.no-matches {
    background-color: #f8f9fa;
    border-radius: 15px;
    padding: 3rem 2rem;
}

.no-matches-icon {
    opacity: 0.6;
}

.result-actions .card {
    border: 2px dashed #dee2e6;
}

.action-buttons .btn {
    margin: 0.25rem;
    min-width: 150px;
}

.quiz-info {
    margin-top: 3rem;
}

.quiz-info .card {
    border-style: dashed;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .results-title {
        font-size: 2rem;
    }
    
    .results-description {
        font-size: 1.1rem;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .action-buttons .row.g-2 .col-auto {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .action-buttons .btn {
        width: 100%;
        min-width: auto;
    }
    
    .no-matches {
        padding: 2rem 1rem;
    }
}

/* Print styles */
@media print {
    .result-actions,
    .action-buttons {
        display: none;
    }
    
    .quiz-result {
        padding: 1rem 0;
    }
    
    .results-icon {
        animation: none;
    }
    
    .product-match-card {
        animation: none;
        page-break-inside: avoid;
        margin-bottom: 1rem;
    }
}
</style>
