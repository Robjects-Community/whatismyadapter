# Create CakePHP View Templates for the quiz system

# 1. Quiz Index Template (Quiz Selection Page)
index_template = """<?php
/**
 * Quiz Index Template
 * @var \\App\\View\\AppView $this
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
</script>"""

# 2. Akinator Quiz Template
akinator_template = """<?php
/**
 * Akinator Quiz Template
 * @var \\App\\View\\AppView $this
 * @var string $title
 * @var string $quizType
 * @var array $questions
 */
?>

<div class="akinator-quiz-container">
    <!-- Quiz Header -->
    <div class="akinator-header">
        <div class="genie-lamp">
            <div class="lamp-glow"></div>
            🧞‍♂️
        </div>
        <h1><?= h($title) ?></h1>
        <p class="akinator-subtitle">Think of your device and I'll guess the adapter you need!</p>
    </div>

    <!-- Progress & Confidence Meters -->
    <div class="quiz-meters">
        <div class="progress-container">
            <div class="progress-label">Progress</div>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
            <span class="progress-text" id="progress-text">0%</span>
        </div>
        
        <div class="confidence-container">
            <div class="confidence-label">AI Confidence</div>
            <div class="confidence-bar">
                <div class="confidence-fill" id="confidence-fill"></div>
            </div>
            <span class="confidence-text" id="confidence-text">0%</span>
        </div>
    </div>

    <!-- Main Quiz Interface -->
    <div class="akinator-main" id="quiz-main">
        
        <!-- Question Display -->
        <div class="question-container" id="question-container">
            <div class="thinking-bubble" id="thinking-bubble" style="display: none;">
                <div class="thinking-animation">
                    <span>🤔</span>
                    <div class="thinking-dots">
                        <span>.</span><span>.</span><span>.</span>
                    </div>
                </div>
                <p id="thinking-text">I'm thinking...</p>
            </div>
            
            <div class="question-display" id="question-display">
                <div class="question-number" id="question-number">Question 1</div>
                <h2 class="question-text" id="question-text">
                    Is your device something you can hold in one hand?
                </h2>
                
                <!-- Binary Answer Options -->
                <div class="answer-options" id="answer-options">
                    <button class="answer-btn yes-btn" id="yes-btn" data-answer="yes">
                        <span class="answer-icon">✅</span>
                        <span class="answer-text">Yes</span>
                    </button>
                    
                    <button class="answer-btn no-btn" id="no-btn" data-answer="no">
                        <span class="answer-icon">❌</span>
                        <span class="answer-text">No</span>
                    </button>
                </div>
                
                <!-- Answer History -->
                <div class="answer-history" id="answer-history">
                    <div class="history-label">Your answers:</div>
                    <div class="history-items" id="history-items"></div>
                </div>
            </div>
        </div>
        
        <!-- Quiz Controls -->
        <div class="quiz-controls">
            <button class="control-btn back-btn" id="back-btn" style="display: none;">
                ⬅️ Go Back
            </button>
            
            <button class="control-btn restart-btn" id="restart-btn" style="display: none;">
                🔄 Start Over
            </button>
        </div>
    </div>

    <!-- Results Display -->
    <div class="akinator-results" id="quiz-results" style="display: none;">
        <div class="results-header">
            <div class="genie-success">🎉</div>
            <h2>I found your perfect match!</h2>
            <div class="final-confidence">
                <span>AI Confidence: </span>
                <strong id="final-confidence-score">0%</strong>
            </div>
        </div>
        
        <div class="results-content" id="results-content">
            <!-- Results will be populated via JavaScript -->
        </div>
        
        <div class="results-actions">
            <button class="btn btn-primary" id="try-again-btn">
                🔄 Try Another Device
            </button>
            <button class="btn btn-secondary" id="view-all-btn">
                📋 View All Results
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loading-overlay" style="display: none;">
        <div class="loading-content">
            <div class="genie-thinking">
                <div class="lamp-spinning">🧞‍♂️</div>
            </div>
            <h3>The genie is analyzing...</h3>
            <div class="loading-dots">
                <span>.</span><span>.</span><span>.</span>
            </div>
        </div>
    </div>
</div>

<?= $this->Html->css(['quiz/quiz-akinator', 'quiz/quiz-animations']) ?>
<?= $this->Html->script('quiz/akinator-quiz') ?>

<script>
// Initialize Akinator Quiz
document.addEventListener('DOMContentLoaded', function() {
    const quiz = new AkinatorQuiz({
        apiEndpoint: '<?= $this->Url->build(['controller' => 'Api/Quiz', 'action' => 'nextQuestion', '_ext' => 'json']) ?>',
        submitEndpoint: '<?= $this->Url->build(['controller' => 'Api/Quiz', 'action' => 'submit', '_ext' => 'json']) ?>',
        questions: <?= json_encode($questions) ?>,
        quizType: '<?= $quizType ?>',
        sessionId: '<?= $this->request->getSession()->id() ?>'
    });
    
    quiz.start();
});
</script>"""

# 3. Quiz Results Template  
results_template = """<?php
/**
 * Quiz Results Template
 * @var \\App\\View\\AppView $this
 * @var array $results
 * @var string $quizType
 * @var array $quizData
 */
?>

<div class="quiz-results-page">
    <div class="results-header">
        <div class="container">
            <?php if ($quizType === 'akinator'): ?>
                <div class="genie-celebration">🎉🧞‍♂️🎉</div>
                <h1>The Genie Has Spoken!</h1>
                <p class="results-subtitle">Here are your magically matched adapters</p>
            <?php else: ?>
                <div class="analysis-complete">🔍✨</div>
                <h1>Analysis Complete</h1>
                <p class="results-subtitle">Based on your detailed answers, here are your perfect matches</p>
            <?php endif; ?>
            
            <div class="results-metrics">
                <div class="metric">
                    <span class="metric-number"><?= $results['total_matches'] ?></span>
                    <span class="metric-label">Perfect Matches</span>
                </div>
                <div class="metric">
                    <span class="metric-number"><?= round($results['overall_confidence'] * 100) ?>%</span>
                    <span class="metric-label">AI Confidence</span>
                </div>
                <div class="metric">
                    <span class="metric-number"><?= date('g:i A', strtotime($results['recommendations_generated'])) ?></span>
                    <span class="metric-label">Generated</span>
                </div>
            </div>
        </div>
    </div>

    <div class="recommendations-section">
        <div class="container">
            <?php if (!empty($results['matches'])): ?>
                <div class="recommendations-grid">
                    <?php foreach ($results['matches'] as $index => $match): ?>
                        <?php $product = $match['product']; ?>
                        <div class="recommendation-card <?= $index === 0 ? 'top-recommendation' : '' ?>">
                            
                            <?php if ($index === 0): ?>
                                <div class="top-pick-badge">
                                    <span>🏆 TOP PICK</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-image">
                                <?php if ($product->image_url): ?>
                                    <?= $this->Html->image($product->image_url, [
                                        'alt' => $product->title,
                                        'class' => 'product-img'
                                    ]) ?>
                                <?php else: ?>
                                    <div class="product-placeholder">
                                        <span class="placeholder-icon">🔌</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="product-info">
                                <div class="product-header">
                                    <h3 class="product-title"><?= h($product->title) ?></h3>
                                    <div class="manufacturer-badge">
                                        <?= h($product->manufacturer) ?>
                                    </div>
                                </div>
                                
                                <div class="product-specs">
                                    <div class="spec-item">
                                        <span class="spec-label">Port:</span>
                                        <span class="spec-value"><?= h($product->port_type) ?></span>
                                    </div>
                                    <div class="spec-item">
                                        <span class="spec-label">Price:</span>
                                        <span class="spec-value price"><?= $product->getFormattedPrice() ?></span>
                                    </div>
                                    <?php if ($product->rating): ?>
                                    <div class="spec-item">
                                        <span class="spec-label">Rating:</span>
                                        <span class="spec-value rating">
                                            <?= $product->getStarRating() ?>
                                            <span class="rating-number">(<?= $product->rating ?>/5)</span>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="compatibility-info">
                                    <div class="compatibility-label">Compatible with:</div>
                                    <div class="compatibility-list">
                                        <?= h($product->device_compatibility) ?>
                                    </div>
                                </div>
                                
                                <div class="confidence-score">
                                    <div class="confidence-label">Match Confidence</div>
                                    <div class="confidence-bar">
                                        <div class="confidence-fill" style="width: <?= round($match['confidence_score'] * 100) ?>%"></div>
                                    </div>
                                    <span class="confidence-percentage"><?= round($match['confidence_score'] * 100) ?>%</span>
                                </div>
                                
                                <div class="ai-explanation">
                                    <div class="explanation-header">
                                        <span class="ai-icon">🤖</span>
                                        <span>Why this matches:</span>
                                    </div>
                                    <p class="explanation-text"><?= h($match['explanation']) ?></p>
                                </div>
                                
                                <?php if ($product->features): ?>
                                <div class="product-features">
                                    <div class="features-label">Key Features:</div>
                                    <div class="features-list">
                                        <?php foreach ($product->features as $feature): ?>
                                            <span class="feature-tag"><?= h($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($product->certified): ?>
                                <div class="certified-badge">
                                    ✅ Officially Certified
                                </div>
                                <?php endif; ?>
                                
                                <div class="product-actions">
                                    <?= $this->Html->link(
                                        'View Details',
                                        ['controller' => 'Products', 'action' => 'view', $product->id],
                                        ['class' => 'btn btn-outline']
                                    ) ?>
                                    <button class="btn btn-primary add-to-compare" data-product-id="<?= $product->id ?>">
                                        Add to Compare
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Comparison Section -->
                <div class="comparison-section" id="comparison-section" style="display: none;">
                    <h3>Product Comparison</h3>
                    <div class="comparison-table" id="comparison-table">
                        <!-- Comparison table will be populated via JavaScript -->
                    </div>
                </div>
                
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">😔</div>
                    <h3>No Perfect Matches Found</h3>
                    <p>We couldn't find products that match your specific requirements. Try adjusting your criteria or browse our full catalog.</p>
                    
                    <div class="no-results-actions">
                        <?= $this->Html->link(
                            'Try Quiz Again',
                            ['action' => 'index'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                        <?= $this->Html->link(
                            'Browse All Products',
                            ['controller' => 'Products', 'action' => 'index'],
                            ['class' => 'btn btn-secondary']
                        ) ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Actions Section -->
    <div class="actions-section">
        <div class="container">
            <div class="actions-grid">
                <div class="action-card">
                    <h4>Try Another Device</h4>
                    <p>Need adapters for a different device?</p>
                    <?= $this->Html->link('Start New Quiz', ['action' => 'index'], ['class' => 'btn btn-primary']) ?>
                </div>
                
                <div class="action-card">
                    <h4>Need Help?</h4>
                    <p>Have questions about these recommendations?</p>
                    <?= $this->Html->link('Contact Support', '/contact', ['class' => 'btn btn-outline']) ?>
                </div>
                
                <div class="action-card">
                    <h4>Share Results</h4>
                    <p>Share these recommendations with others</p>
                    <button class="btn btn-outline share-results">Share</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Html->css(['quiz/quiz-results', 'quiz/product-cards']) ?>
<?= $this->Html->script('quiz/results') ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize results page functionality
    const resultsPage = new QuizResults({
        quizType: '<?= $quizType ?>',
        results: <?= json_encode($results) ?>,
        quizData: <?= json_encode($quizData) ?>
    });
    
    // Track result views for analytics
    if (typeof gtag !== 'undefined') {
        gtag('event', 'quiz_completed', {
            'quiz_type': '<?= $quizType ?>',
            'total_matches': <?= $results['total_matches'] ?>,
            'confidence': <?= round($results['overall_confidence'], 3) ?>,
            'event_category': 'quiz_completion'
        });
    }
});
</script>"""

# Save template files
with open('quiz_index.php', 'w') as f:
    f.write(index_template)

with open('quiz_akinator.php', 'w') as f:
    f.write(akinator_template)

with open('quiz_results.php', 'w') as f:
    f.write(results_template)

print("✅ CakePHP View Templates created!")
print("📄 Files:")
print("   • quiz_index.php - Quiz selection homepage")
print("   • quiz_akinator.php - Akinator-style quiz interface") 
print("   • quiz_results.php - AI recommendations display")
print("🎨 Features:")
print("   • Responsive design with CSS classes")
print("   • JavaScript integration for interactivity")
print("   • Analytics tracking built-in")
print("   • Accessibility features")
print("   • SEO-optimized structure")
print("   • Progressive enhancement support")