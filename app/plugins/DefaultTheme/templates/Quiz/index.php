<?php
/**
 * AI-Powered Quiz Index Template
 * 
 * Entry point for different quiz types - Akinator-style and comprehensive quiz
 */

$this->assign('title', __('Product Finder Quiz'));
$this->Html->meta('description', __('Find the perfect adapter using our AI-powered quiz system. Choose between our Akinator-style interactive questionnaire or comprehensive product matcher.'), ['block' => 'meta']);
?>

<div class="quiz-index">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="quiz-header text-center mb-5">
                    <h1 class="quiz-title"><?= __('Find Your Perfect Adapter') ?></h1>
                    <p class="quiz-description lead">
                        <?= __('Our AI-powered quiz system will help you discover the ideal adapter for your specific needs. Choose your preferred quiz style below:') ?>
                    </p>
                </div>

                <!-- Quiz Options -->
                <div class="row g-4 mb-5">
                    <!-- Akinator-Style Quiz -->
                    <div class="col-md-6">
                        <div class="quiz-option-card h-100">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white text-center">
                                    <i class="fas fa-magic fa-2x mb-2"></i>
                                    <h4 class="mb-0"><?= __('Akinator Quiz') ?></h4>
                                    <small class="opacity-75"><?= __('Interactive & Fun') ?></small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <?= __('Answer yes/no questions as our AI gradually narrows down to your perfect match. Just like the famous guessing game!') ?>
                                    </p>
                                    
                                    <div class="feature-list mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-brain text-primary"></i>
                                            <span><?= __('AI-powered questioning') ?></span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-clock text-primary"></i>
                                            <span><?= __('2-3 minutes') ?></span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-gamepad text-primary"></i>
                                            <span><?= __('Interactive experience') ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <?= $this->Html->link(
                                            __('Start Akinator Quiz') . ' <i class="fas fa-play ms-2"></i>',
                                            ['action' => 'akinator'],
                                            [
                                                'class' => 'btn btn-primary btn-lg',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Comprehensive Quiz -->
                    <div class="col-md-6">
                        <div class="quiz-option-card h-100">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white text-center">
                                    <i class="fas fa-list-check fa-2x mb-2"></i>
                                    <h4 class="mb-0"><?= __('Comprehensive Quiz') ?></h4>
                                    <small class="opacity-75"><?= __('Detailed & Thorough') ?></small>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">
                                        <?= __('Answer detailed questions about your device, usage, and preferences for comprehensive product recommendations.') ?>
                                    </p>
                                    
                                    <div class="feature-list mb-3">
                                        <div class="feature-item">
                                            <i class="fas fa-search text-success"></i>
                                            <span><?= __('Detailed analysis') ?></span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-clock text-success"></i>
                                            <span><?= __('5-7 minutes') ?></span>
                                        </div>
                                        <div class="feature-item">
                                            <i class="fas fa-chart-bar text-success"></i>
                                            <span><?= __('Multiple recommendations') ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="d-grid">
                                        <?= $this->Html->link(
                                            __('Start Comprehensive Quiz') . ' <i class="fas fa-arrow-right ms-2"></i>',
                                            ['action' => 'comprehensive'],
                                            [
                                                'class' => 'btn btn-success btn-lg',
                                                'escape' => false
                                            ]
                                        ) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <?php if (!empty($stats)): ?>
                <div class="quiz-stats text-center mb-5">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number text-primary"><?= number_format($stats['total_products'] ?? 0) ?></div>
                                <div class="stat-label"><?= __('Products Available') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number text-success"><?= number_format($stats['quiz_submissions'] ?? 0) ?></div>
                                <div class="stat-label"><?= __('Successful Matches') ?></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?= isset($stats['avg_confidence']) ? round($stats['avg_confidence']) . '%' : '95%' ?></div>
                                <div class="stat-label"><?= __('Average Confidence') ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Alternative Options -->
                <div class="alternative-options">
                    <div class="card bg-light">
                        <div class="card-body text-center">
                            <h5 class="card-title">
                                <i class="fas fa-compass"></i> <?= __('Other Ways to Find Products') ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?= __('Prefer to browse on your own? No problem!') ?>
                            </p>
                            
                            <div class="row g-2 justify-content-center">
                                <div class="col-auto">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-th-large"></i> ' . __('Browse All Products'),
                                        ['controller' => 'Products', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-primary',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                                <div class="col-auto">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-search"></i> ' . __('Advanced Search'),
                                        ['controller' => 'Products', 'action' => 'index', '?' => ['advanced' => 1]],
                                        [
                                            'class' => 'btn btn-outline-secondary',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                                <?php // Preview action temporarily disabled ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Help Section -->
                <div class="quiz-help mt-5">
                    <div class="card border-info">
                        <div class="card-body">
                            <h6 class="card-title text-info">
                                <i class="fas fa-question-circle"></i> <?= __('How Our Quiz Works') ?>
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><?= __('Akinator Quiz:') ?></h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success"></i> <?= __('Answer yes/no questions') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('AI learns from your responses') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('Get highly targeted results') ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><?= __('Comprehensive Quiz:') ?></h6>
                                    <ul class="list-unstyled small">
                                        <li><i class="fas fa-check text-success"></i> <?= __('Detailed technical questions') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('Budget and preference settings') ?></li>
                                        <li><i class="fas fa-check text-success"></i> <?= __('Multiple product suggestions') ?></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt"></i> <?= __('Your quiz responses are anonymous and used only to improve recommendations.') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.quiz-index {
    padding: 2rem 0;
}

.quiz-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.quiz-description {
    font-size: 1.2rem;
    color: #6c757d;
    margin-bottom: 2rem;
}

.quiz-option-card .card {
    transition: transform 0.2s, box-shadow 0.2s;
    border-width: 2px;
}

.quiz-option-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.quiz-option-card .card-header {
    padding: 1.5rem;
}

.quiz-option-card .card-body {
    padding: 1.5rem;
}

.feature-list {
    list-style: none;
    padding: 0;
}

.feature-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.feature-item i {
    width: 20px;
    margin-right: 0.75rem;
}

.quiz-stats {
    border-top: 1px solid #dee2e6;
    border-bottom: 1px solid #dee2e6;
    padding: 2rem 0;
    margin: 2rem 0;
}

.stat-item {
    margin-bottom: 1rem;
}

.stat-number {
    font-size: 2rem;
    font-weight: bold;
    display: block;
}

.stat-label {
    font-size: 0.9rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.alternative-options {
    margin-top: 2rem;
}

.quiz-help {
    margin-top: 2rem;
}

.quiz-help .card {
    border-left-width: 4px;
}

@media (max-width: 768px) {
    .quiz-title {
        font-size: 2rem;
    }
    
    .quiz-description {
        font-size: 1.1rem;
    }
    
    .quiz-option-card .card-header {
        padding: 1rem;
    }
    
    .quiz-option-card .card-body {
        padding: 1rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .btn-lg {
        padding: 0.75rem 1rem;
        font-size: 1rem;
    }
    
    .row.g-2 .col-auto {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    
    .row.g-2 .btn {
        width: 100%;
    }
}
</style>
