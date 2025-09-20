<?php
/**
 * Quiz Index Template
 * @var \App\View\AppView $this
 * @var int $totalProducts
 * @var int $totalManufacturers
 */
?>

<div class="quiz-index">
    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1><?= h($title) ?></h1>
                <p class="lead">
                    Find the perfect charger or adapter for your device using our intelligent AI-powered quiz system. 
                    Choose from two different quiz styles to get personalized recommendations.
                </p>

                <div class="stats-row">
                    <div class="stat-item">
                        <span class="stat-number"><?= number_format($totalProducts) ?></span>
                        <span class="stat-label">Products Available</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?= $totalManufacturers ?></span>
                        <span class="stat-label">Manufacturers</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">95%</span>
                        <span class="stat-label">Accuracy Rate</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="quiz-options-section">
        <div class="container">
            <h2>Choose Your Quiz Style</h2>

            <div class="quiz-options-grid">
                <!-- Akinator Style Quiz -->
                <div class="quiz-option akinator-style">
                    <div class="quiz-option-icon">
                        🧞‍♂️
                    </div>
                    <h3>Akinator Style Quiz</h3>
                    <p class="quiz-description">
                        Let our AI genie guess your device through a series of clever yes/no questions. 
                        Just like the famous Akinator game!
                    </p>

                    <ul class="quiz-features">
                        <li>✨ Mystical AI genie experience</li>
                        <li>🎯 Smart binary questions</li>
                        <li>⚡ Quick results (5-8 questions)</li>
                        <li>🧠 Advanced decision tree logic</li>
                    </ul>

                    <div class="quiz-option-footer">
                        <?= $this->Html->link(
                            'Start Mystical Quiz',
                            ['action' => 'akinator'],
                            ['class' => 'btn btn-primary btn-large']
                        ) ?>
                        <span class="estimated-time">⏱️ ~2 minutes</span>
                    </div>
                </div>

                <!-- Comprehensive Quiz -->
                <div class="quiz-option comprehensive-style">
                    <div class="quiz-option-icon">
                        📋
                    </div>
                    <h3>Comprehensive Quiz</h3>
                    <p class="quiz-description">
                        Answer detailed questions about your device, preferences, and requirements 
                        for the most accurate recommendations.
                    </p>

                    <ul class="quiz-features">
                        <li>🎯 Precise matching algorithm</li>
                        <li>💰 Budget and feature preferences</li>
                        <li>🔧 Technical specifications</li>
                        <li>📊 Detailed explanations</li>
                    </ul>

                    <div class="quiz-option-footer">
                        <?= $this->Html->link(
                            'Start Detailed Quiz',
                            ['action' => 'comprehensive'],
                            ['class' => 'btn btn-secondary btn-large']
                        ) ?>
                        <span class="estimated-time">⏱️ ~3 minutes</span>
                    </div>
                </div>
            </div>

            <!-- Quick Browse Option -->
            <div class="alternative-options">
                <div class="divider">
                    <span>Or</span>
                </div>

                <div class="browse-products">
                    <h4>Browse Products Directly</h4>
                    <p>Already know what you're looking for? Browse our product catalog.</p>

                    <div class="browse-buttons">
                        <?= $this->Html->link('Browse All Products', '/adapters', ['class' => 'btn btn-outline']) ?>
                        <?= $this->Html->link('Search Products', '/api/products/search', ['class' => 'btn btn-outline']) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section">
        <div class="container">
            <h2>Why Use Our AI Quiz?</h2>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">🤖</div>
                    <h4>AI-Powered Matching</h4>
                    <p>Our advanced algorithm analyzes your answers to find the perfect match from thousands of products.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">⚡</div>
                    <h4>Fast & Accurate</h4>
                    <p>Get precise recommendations in under 3 minutes with 95% accuracy rate.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">🛡️</div>
                    <h4>Certified Products</h4>
                    <p>All recommended products are tested and certified for compatibility and safety.</p>
                </div>

                <div class="feature-item">
                    <div class="feature-icon">💡</div>
                    <h4>Smart Explanations</h4>
                    <p>Understand why each product was recommended with AI-generated explanations.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Html->css('quiz/quiz-index') ?>
<script>
// Track quiz option clicks for analytics
document.addEventListener('DOMContentLoaded', function() {
    const quizOptions = document.querySelectorAll('.quiz-option');

    quizOptions.forEach(option => {
        option.addEventListener('click', function(e) {
            if (!e.target.closest('.btn')) {
                const quizType = this.classList.contains('akinator-style') ? 'akinator' : 'comprehensive';

                // Track analytics
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'quiz_option_viewed', {
                        'quiz_type': quizType,
                        'event_category': 'quiz_interaction'
                    });
                }
            }
        });
    });
});
</script>