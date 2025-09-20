<?php
/**
 * Product Card Element for Quiz Results
 * 
 * Displays a product with confidence score and rationale
 * 
 * Variables:
 * - $product: Product data
 * - $confidence: Confidence score (0-100)
 * - $rationale: AI-generated explanation
 * - $rank: Ranking position (optional)
 * - $isTopMatch: Whether this is the #1 match (optional)
 * - $isAlternative: Whether this is an alternative option (optional)
 */

$confidence = $confidence ?? 0;
$rank = $rank ?? null;
$isTopMatch = $isTopMatch ?? false;
$isAlternative = $isAlternative ?? false;
$product = $product ?? [];

// Determine confidence level for styling
$confidenceLevel = 'medium';
if ($confidence >= 90) {
    $confidenceLevel = 'high';
} elseif ($confidence < 70) {
    $confidenceLevel = 'low';
}

$confidenceColor = [
    'high' => 'success',
    'medium' => 'warning', 
    'low' => 'danger'
][$confidenceLevel];

$borderColor = $isTopMatch ? 'border-warning' : ($isAlternative ? 'border-secondary' : 'border-primary');
?>

<div class="product-card <?= $isTopMatch ? 'top-match' : '' ?> <?= $isAlternative ? 'alternative' : '' ?>">
    <div class="card h-100 <?= $borderColor ?>">
        <?php if ($isTopMatch): ?>
            <div class="top-match-badge">
                <span class="badge bg-warning text-dark">
                    <i class="fas fa-crown"></i> <?= __('Top Match') ?>
                </span>
            </div>
        <?php elseif ($rank): ?>
            <div class="rank-badge">
                <span class="badge bg-primary">
                    <?= __('#{0}', $rank) ?>
                </span>
            </div>
        <?php endif; ?>

        <div class="card-body">
            <div class="product-header mb-3">
                <!-- Product Image -->
                <div class="product-image-container">
                    <?php if (!empty($product['featured_image'])): ?>
                        <?= $this->Html->image($product['featured_image'], [
                            'alt' => h($product['title'] ?? ''),
                            'class' => 'product-image img-fluid',
                            'loading' => 'lazy'
                        ]) ?>
                    <?php else: ?>
                        <div class="product-image-placeholder">
                            <i class="fas fa-cube fa-2x text-muted"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Product Title -->
                <h5 class="product-title">
                    <?php if (!empty($product['slug'])): ?>
                        <?= $this->Html->link(
                            h($product['title'] ?? __('Product')),
                            ['controller' => 'Products', 'action' => 'view', $product['slug']],
                            ['class' => 'text-decoration-none']
                        ) ?>
                    <?php else: ?>
                        <?= h($product['title'] ?? __('Product')) ?>
                    <?php endif; ?>
                </h5>

                <!-- Confidence Score -->
                <div class="confidence-score mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="confidence-label"><?= __('Match Confidence') ?></span>
                        <span class="confidence-value badge bg-<?= $confidenceColor ?> fs-6">
                            <?= round($confidence) ?>%
                        </span>
                    </div>
                    <div class="progress mt-1">
                        <div class="progress-bar bg-<?= $confidenceColor ?>" 
                             role="progressbar" 
                             style="width: <?= $confidence ?>%"
                             aria-valuenow="<?= $confidence ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="product-details">
                <!-- Price -->
                <?php if (!empty($product['price'])): ?>
                    <div class="product-price mb-2">
                        <span class="price-label text-muted"><?= __('Price:') ?></span>
                        <span class="price-value text-success fw-bold">
                            <?= $this->Number->currency($product['price']) ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Short Description -->
                <?php if (!empty($product['short_description'])): ?>
                    <p class="product-description text-muted">
                        <?= h($product['short_description']) ?>
                    </p>
                <?php endif; ?>

                <!-- Key Features -->
                <?php if (!empty($product['features'])): ?>
                    <div class="product-features">
                        <h6 class="features-title"><?= __('Key Features:') ?></h6>
                        <ul class="features-list">
                            <?php 
                            $features = is_array($product['features']) ? $product['features'] : explode("\n", $product['features']);
                            foreach (array_slice($features, 0, 3) as $feature): ?>
                                <li><i class="fas fa-check text-success"></i> <?= h(trim($feature)) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Rationale -->
                <?php if (!empty($rationale)): ?>
                    <div class="match-rationale">
                        <h6 class="rationale-title">
                            <i class="fas fa-lightbulb text-info"></i> <?= __('Why this matches:') ?>
                        </h6>
                        <p class="rationale-text"><?= h($rationale) ?></p>
                    </div>
                <?php endif; ?>

                <!-- Product Tags -->
                <?php if (!empty($product['tags'])): ?>
                    <div class="product-tags mt-3">
                        <?php 
                        $tags = is_array($product['tags']) ? $product['tags'] : explode(',', $product['tags']);
                        foreach (array_slice($tags, 0, 4) as $tag): ?>
                            <span class="badge bg-light text-dark me-1 mb-1">
                                <?= h(trim($tag)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-transparent">
            <div class="product-actions">
                <div class="row g-2">
                    <div class="col">
                        <?php if (!empty($product['slug'])): ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-eye"></i> ' . __('View Details'),
                                ['controller' => 'Products', 'action' => 'view', $product['slug']],
                                [
                                    'class' => 'btn btn-outline-primary btn-sm w-100',
                                    'escape' => false
                                ]
                            ) ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($product['external_url']) || !empty($product['buy_url'])): ?>
                    <div class="col">
                        <?= $this->Html->link(
                            '<i class="fas fa-shopping-cart"></i> ' . __('Buy Now'),
                            $product['buy_url'] ?? $product['external_url'],
                            [
                                'class' => 'btn btn-success btn-sm w-100',
                                'escape' => false,
                                'target' => '_blank',
                                'rel' => 'noopener'
                            ]
                        ) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    position: relative;
    transition: transform 0.2s, box-shadow 0.2s;
}

.product-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-card.top-match {
    position: relative;
    overflow: hidden;
}

.product-card.top-match::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ffc107, #ff8c00);
    z-index: 1;
}

.top-match-badge,
.rank-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 2;
}

.top-match-badge .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
}

.rank-badge .badge {
    font-size: 0.8rem;
    padding: 0.5rem 0.75rem;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image-container {
    text-align: center;
    margin-bottom: 1rem;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-image {
    max-height: 100px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 8px;
}

.product-image-placeholder {
    width: 80px;
    height: 80px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
    line-height: 1.3;
}

.product-title a {
    color: inherit;
}

.product-title a:hover {
    color: #0d6efd;
}

.confidence-score {
    background-color: #f8f9fa;
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.confidence-label {
    font-size: 0.85rem;
    font-weight: 500;
    color: #6c757d;
}

.confidence-value {
    font-weight: 600;
}

.progress {
    height: 6px;
    border-radius: 3px;
}

.product-price {
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 6px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.price-label {
    font-size: 0.9rem;
}

.price-value {
    font-size: 1.1rem;
}

.product-description {
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 1rem;
}

.features-title,
.rationale-title {
    font-size: 0.9rem;
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
}

.features-list {
    list-style: none;
    padding: 0;
    margin-bottom: 1rem;
}

.features-list li {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    color: #6c757d;
}

.features-list i {
    margin-right: 0.5rem;
    width: 12px;
}

.match-rationale {
    background-color: #f0f8ff;
    padding: 0.75rem;
    border-radius: 8px;
    border-left: 3px solid #17a2b8;
    margin-bottom: 1rem;
}

.rationale-text {
    font-size: 0.85rem;
    color: #495057;
    margin-bottom: 0;
    line-height: 1.4;
}

.product-tags {
    border-top: 1px solid #dee2e6;
    padding-top: 0.75rem;
}

.product-tags .badge {
    font-size: 0.7rem;
    border: 1px solid #dee2e6;
}

.product-actions .btn {
    font-size: 0.85rem;
    font-weight: 500;
}

.card-footer {
    border-top: 1px solid #dee2e6;
    padding: 1rem;
}

/* Alternative product styling */
.product-card.alternative {
    opacity: 0.9;
}

.product-card.alternative .card {
    background-color: #fafafa;
}

.product-card.alternative .confidence-score {
    background-color: #e9ecef;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .product-title {
        font-size: 1rem;
    }
    
    .confidence-label,
    .confidence-value {
        font-size: 0.8rem;
    }
    
    .product-price {
        padding: 0.4rem;
    }
    
    .price-value {
        font-size: 1rem;
    }
    
    .features-list li,
    .rationale-text {
        font-size: 0.8rem;
    }
    
    .product-actions .btn {
        font-size: 0.8rem;
        padding: 0.4rem 0.6rem;
    }
    
    .top-match-badge,
    .rank-badge {
        top: 5px;
        right: 5px;
    }
    
    .rank-badge .badge {
        width: 30px;
        height: 30px;
        font-size: 0.7rem;
    }
}
</style>
