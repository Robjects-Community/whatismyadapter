<?php
/**
 * Quiz Results Template
 * @var \App\View\AppView $this
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
</script>