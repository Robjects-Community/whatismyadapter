<?php
/**
 * Product View Template
 * 
 * Displays detailed information about a specific product
 */

$this->assign('title', h($product->title));
$this->Html->meta('description', h($product->excerpt ?: $this->Text->truncate(strip_tags($product->body), 160)), ['block' => 'meta']);
?>

<div class="product-view">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="product-main">
                    <!-- Product Header -->
                    <div class="product-header">
                        <div class="breadcrumbs mb-3">
                            <?= $this->Html->link(__('Products'), ['action' => 'index'], ['class' => 'text-muted']) ?> 
                            <span class="text-muted"> / </span>
                            <?php if (!empty($product->product_category)): ?>
                                <?= h($product->product_category->name) ?>
                                <span class="text-muted"> / </span>
                            <?php endif; ?>
                            <span><?= h($product->title) ?></span>
                        </div>

                        <h1 class="product-title"><?= h($product->title) ?></h1>
                        
                        <div class="product-badges mb-3">
                            <?php if ($product->featured): ?>
                                <span class="badge bg-warning me-2">
                                    <i class="fas fa-star"></i> <?= __('Featured') ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if ($product->verified): ?>
                                <span class="badge bg-success me-2">
                                    <i class="fas fa-check-circle"></i> <?= __('Verified') ?>
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($product->product_category)): ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-tag"></i> <?= h($product->product_category->name) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($product->excerpt)): ?>
                            <p class="product-excerpt lead"><?= h($product->excerpt) ?></p>
                        <?php endif; ?>
                    </div>

                    <!-- Product Image -->
                    <?php if (!empty($product->featured_image)): ?>
                        <div class="product-image mb-4">
                            <div class="image-container">
                                <?= $this->Html->image($product->featured_image, [
                                    'alt' => h($product->title),
                                    'class' => 'product-main-image img-fluid'
                                ]) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Product Content -->
                    <div class="product-content">
                        <?php if (!empty($product->body)): ?>
                            <div class="product-description">
                                <h3><?= __('Description') ?></h3>
                                <div class="content">
                                    <?= $product->body ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Product Actions -->
                    <div class="product-actions mt-4">
                        <div class="row g-3">
                            <?php if (!empty($product->website_url)): ?>
                                <div class="col-md-6">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-external-link-alt"></i> ' . __('Visit Website'),
                                        $product->website_url,
                                        [
                                            'class' => 'btn btn-primary btn-lg w-100',
                                            'target' => '_blank',
                                            'rel' => 'noopener noreferrer',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($product->contact_email)): ?>
                                <div class="col-md-6">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-envelope"></i> ' . __('Contact'),
                                        'mailto:' . $product->contact_email,
                                        [
                                            'class' => 'btn btn-outline-primary btn-lg w-100',
                                            'escape' => false
                                        ]
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Social Share -->
                        <div class="social-share mt-4">
                            <h6><?= __('Share this product:') ?></h6>
                            <div class="share-buttons">
                                <?= $this->Html->link(
                                    '<i class="fab fa-twitter"></i>',
                                    'https://twitter.com/intent/tweet?text=' . urlencode($product->title) . '&url=' . urlencode($this->Url->build(null, ['fullBase' => true])),
                                    [
                                        'class' => 'btn btn-sm btn-outline-info me-2',
                                        'target' => '_blank',
                                        'title' => __('Share on Twitter'),
                                        'escape' => false
                                    ]
                                ) ?>
                                
                                <?= $this->Html->link(
                                    '<i class="fab fa-linkedin"></i>',
                                    'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($this->Url->build(null, ['fullBase' => true])),
                                    [
                                        'class' => 'btn btn-sm btn-outline-primary me-2',
                                        'target' => '_blank',
                                        'title' => __('Share on LinkedIn'),
                                        'escape' => false
                                    ]
                                ) ?>
                                
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard()" title="<?= __('Copy link') ?>">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="product-sidebar">
                    <!-- Product Info -->
                    <div class="info-card">
                        <h5><?= __('Product Information') ?></h5>
                        
                        <div class="info-item">
                            <strong><?= __('Added:') ?></strong>
                            <span><?= $product->created->format('M d, Y') ?></span>
                        </div>
                        
                        <?php if ($product->modified > $product->created): ?>
                            <div class="info-item">
                                <strong><?= __('Updated:') ?></strong>
                                <span><?= $product->modified->format('M d, Y') ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product->price) && $product->price > 0): ?>
                            <div class="info-item">
                                <strong><?= __('Price:') ?></strong>
                                <span class="price"><?= '$' . number_format($product->price, 2) ?></span>
                            </div>
                        <?php elseif (isset($product->price)): ?>
                            <div class="info-item">
                                <strong><?= __('Price:') ?></strong>
                                <span class="price text-success"><?= __('Free') ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product->product_category)): ?>
                            <div class="info-item">
                                <strong><?= __('Category:') ?></strong>
                                <span><?= h($product->product_category->name) ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Related Products -->
                    <?php if (!empty($relatedProducts)): ?>
                        <div class="related-products">
                            <h5><?= __('Related Products') ?></h5>
                            <div class="related-list">
                                <?php foreach ($relatedProducts as $related): ?>
                                    <div class="related-item">
                                        <div class="row g-2">
                                            <?php if (!empty($related->featured_image)): ?>
                                                <div class="col-4">
                                                    <?= $this->Html->image($related->featured_image, [
                                                        'alt' => h($related->title),
                                                        'class' => 'related-image img-fluid'
                                                    ]) ?>
                                                </div>
                                                <div class="col-8">
                                            <?php else: ?>
                                                <div class="col-12">
                                            <?php endif; ?>
                                                <h6 class="related-title">
                                                    <?= $this->Html->link(
                                                        h($related->title),
                                                        ['action' => 'view', $related->id],
                                                        ['class' => 'text-decoration-none']
                                                    ) ?>
                                                </h6>
                                                <?php if (!empty($related->excerpt)): ?>
                                                    <p class="related-excerpt">
                                                        <?= $this->Text->truncate(h($related->excerpt), 60) ?>
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Quiz CTA -->
                    <div class="quiz-cta">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6><i class="fas fa-lightbulb"></i> <?= __('Need Help Finding Products?') ?></h6>
                                <p class="card-text small">
                                    <?= __('Take our interactive quiz to get personalized recommendations.') ?>
                                </p>
                                <?= $this->Html->link(
                                    __('Take Quiz'),
                                    ['action' => 'quiz'],
                                    ['class' => 'btn btn-light btn-sm']
                                ) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Product CTA -->
                    <?php if ($this->Identity->isLoggedIn()): ?>
                        <div class="submit-cta mt-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6><i class="fas fa-plus-circle"></i> <?= __('Have a Product?') ?></h6>
                                    <p class="card-text small">
                                        <?= __('Submit your own product for review.') ?>
                                    </p>
                                    <?= $this->Html->link(
                                        __('Submit Product'),
                                        ['action' => 'add'],
                                        ['class' => 'btn btn-primary btn-sm']
                                    ) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Back to Products -->
        <div class="back-link mt-5">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left"></i> ' . __('Back to Products'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</div>

<style>
.product-title {
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 1rem;
}

.product-excerpt {
    color: #6c757d;
    font-size: 1.125rem;
}

.product-badges .badge {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.product-main-image {
    max-height: 400px;
    width: 100%;
    object-fit: cover;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.image-container {
    text-align: center;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
}

.product-description h3 {
    color: #2c3e50;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 0.5rem;
    margin-bottom: 1.5rem;
}

.product-description .content {
    line-height: 1.7;
    font-size: 1rem;
}

.product-actions .btn-lg {
    padding: 12px 24px;
    font-weight: 600;
}

.social-share h6 {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.share-buttons .btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.product-sidebar .info-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.info-card h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.price {
    font-weight: 600;
    font-size: 1.1rem;
}

.related-products {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.related-products h5 {
    color: #2c3e50;
    font-weight: 600;
    margin-bottom: 1rem;
}

.related-item {
    padding: 1rem 0;
    border-bottom: 1px solid #f8f9fa;
}

.related-item:last-child {
    border-bottom: none;
}

.related-image {
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}

.related-title {
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.related-excerpt {
    font-size: 0.8rem;
    color: #6c757d;
    margin-bottom: 0;
}

.quiz-cta .card {
    border: none;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.breadcrumbs {
    font-size: 0.9rem;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .product-main-image {
        max-height: 250px;
    }
    
    .product-actions .row > div {
        margin-bottom: 0.75rem;
    }
    
    .share-buttons .btn {
        width: 35px;
        height: 35px;
        margin-right: 0.5rem;
    }
}
</style>

<script>
function copyToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        // Show success feedback
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-check"></i>';
        button.classList.replace('btn-outline-secondary', 'btn-success');
        
        setTimeout(() => {
            button.innerHTML = originalContent;
            button.classList.replace('btn-success', 'btn-outline-secondary');
        }, 2000);
    }).catch(function() {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = url;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        alert('<?= __("Link copied to clipboard!") ?>');
    });
}
</script>
