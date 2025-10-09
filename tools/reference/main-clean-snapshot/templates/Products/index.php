<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 * @var array $manufacturers
 * @var array $tags
 * @var string|null $search
 * @var string|null $manufacturer
 * @var string|null $tag
 * @var bool $featured
 */
?>

<div class="container mt-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h1 class="h2 text-primary mb-0">
                        <i class="fas fa-cube me-2"></i>
                        Product Catalog
                    </h1>
                    <p class="text-muted mb-0">Discover adapters and connectivity solutions</p>
                </div>
                <div class="d-flex gap-2">
                    <?= $this->Html->link(
                        '<i class="fas fa-magic me-2"></i>Product Finder Quiz',
                        ['action' => 'quiz'],
                        [
                            'class' => 'btn btn-outline-primary',
                            'escape' => false
                        ]
                    ) ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-plus-circle me-2"></i>Submit Product',
                        ['action' => 'add'],
                        [
                            'class' => 'btn btn-success',
                            'escape' => false
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Product Submission Success Notice -->
    <?php if ($this->getRequest()->getSession()->check('Flash.success')): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                </div>
                <div class="flex-grow-1 ms-3">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-clipboard-check me-2"></i>
                        Product Successfully Submitted!
                    </h5>
                    <p class="mb-2">
                        <?= $this->getRequest()->getSession()->read('Flash.success')[0]['message'] ?>
                    </p>
                    <div class="d-flex align-items-center mt-3">
                        <div class="badge bg-primary me-3">
                            <i class="fas fa-hourglass-half me-1"></i>
                            Pending Review
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            You'll receive an email notification once reviewed
                        </small>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Search and Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?= $this->Form->create(null, [
                        'type' => 'get',
                        'class' => 'row g-3'
                    ]) ?>
                    
                    <div class="col-md-4">
                        <?= $this->Form->control('search', [
                            'label' => false,
                            'placeholder' => 'Search products...',
                            'value' => $search,
                            'class' => 'form-control',
                            'autocomplete' => 'off'
                        ]) ?>
                    </div>
                    
                    <div class="col-md-3">
                        <?= $this->Form->control('manufacturer', [
                            'label' => false,
                            'options' => [''] + array_combine(
                                array_column($manufacturers, 'manufacturer'),
                                array_column($manufacturers, 'manufacturer')
                            ),
                            'empty' => 'All Manufacturers',
                            'value' => $manufacturer,
                            'class' => 'form-select'
                        ]) ?>
                    </div>
                    
                    <div class="col-md-3">
                        <?= $this->Form->control('tag', [
                            'label' => false,
                            'options' => [''] + array_combine(
                                array_column($tags, 'slug'),
                                array_column($tags, 'title')
                            ),
                            'empty' => 'All Categories',
                            'value' => $tag,
                            'class' => 'form-select'
                        ]) ?>
                    </div>
                    
                    <div class="col-md-2">
                        <div class="d-grid">
                            <?= $this->Form->button(
                                '<i class="fas fa-search me-1"></i>Search',
                                [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary',
                                    'escape' => false
                                ]
                            ) ?>
                        </div>
                    </div>
                    
                    <?= $this->Form->end() ?>
                    
                    <!-- Filter Tags -->
                    <?php if ($search || $manufacturer || $tag || $featured): ?>
                        <div class="col-12">
                            <div class="d-flex align-items-center gap-2 mt-2 pt-2 border-top">
                                <span class="text-muted small">Active filters:</span>
                                <?php if ($search): ?>
                                    <span class="badge bg-primary">
                                        Search: "<?= h($search) ?>"
                                        <?= $this->Html->link(
                                            '×',
                                            ['action' => 'index', '?' => array_filter(compact('manufacturer', 'tag', 'featured'))],
                                            ['class' => 'text-white text-decoration-none ms-1']
                                        ) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($manufacturer): ?>
                                    <span class="badge bg-info">
                                        <?= h($manufacturer) ?>
                                        <?= $this->Html->link(
                                            '×',
                                            ['action' => 'index', '?' => array_filter(compact('search', 'tag', 'featured'))],
                                            ['class' => 'text-white text-decoration-none ms-1']
                                        ) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($tag): ?>
                                    <span class="badge bg-success">
                                        Category: <?= h($tag) ?>
                                        <?= $this->Html->link(
                                            '×',
                                            ['action' => 'index', '?' => array_filter(compact('search', 'manufacturer', 'featured'))],
                                            ['class' => 'text-white text-decoration-none ms-1']
                                        ) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($featured): ?>
                                    <span class="badge bg-warning">
                                        Featured Only
                                        <?= $this->Html->link(
                                            '×',
                                            ['action' => 'index', '?' => array_filter(compact('search', 'manufacturer', 'tag'))],
                                            ['class' => 'text-white text-decoration-none ms-1']
                                        ) ?>
                                    </span>
                                <?php endif; ?>
                                <?= $this->Html->link(
                                    '<i class="fas fa-times me-1"></i>Clear All',
                                    ['action' => 'index'],
                                    ['class' => 'btn btn-outline-secondary btn-sm ms-2', 'escape' => false]
                                ) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <?php if (count($products) > 0): ?>
        <div class="row" id="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 shadow-sm border-0 product-card">
                        <!-- Product Image -->
                        <?php if (!empty($product->image)): ?>
                            <div class="card-img-top-container position-relative" style="height: 200px; overflow: hidden;">
                                <img src="<?= h($product->image) ?>" 
                                     class="card-img-top" 
                                     alt="<?= h($product->alt_text ?: $product->title) ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                                <?php if ($product->featured): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <!-- Reliability Score Badge -->
                                <?php if (!empty($product->reliability_score)): ?>
                                    <div class="position-absolute top-0 start-0 m-2">
                                        <span class="badge bg-primary">
                                            <?= round($product->reliability_score * 100) ?>% reliable
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height: 200px;">
                                <i class="fas fa-cube fa-3x text-muted"></i>
                                <?php if ($product->featured): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star me-1"></i>Featured
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <h5 class="card-title mb-1">
                                    <?= $this->Html->link(
                                        h($product->title),
                                        ['action' => 'view', $product->id],
                                        ['class' => 'text-decoration-none']
                                    ) ?>
                                </h5>
                                <?php if (!empty($product->manufacturer)): ?>
                                    <small class="text-muted">
                                        <i class="fas fa-industry me-1"></i>
                                        <?= h($product->manufacturer) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            
                            <p class="card-text flex-grow-1">
                                <?= $this->Text->truncate(
                                    h($product->description),
                                    100,
                                    ['ellipsis' => '...', 'exact' => false]
                                ) ?>
                            </p>
                            
                            <div class="mt-auto">
                                <?php if (!empty($product->price)): ?>
                                    <div class="mb-2">
                                        <span class="h6 text-success mb-0">
                                            <?= $this->Number->currency($product->price, $product->currency ?: 'USD') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <?= $this->Html->link(
                                        '<i class="fas fa-eye me-1"></i>View Details',
                                        ['action' => 'view', $product->id],
                                        [
                                            'class' => 'btn btn-primary btn-sm',
                                            'escape' => false
                                        ]
                                    ) ?>
                                    
                                    <small class="text-muted">
                                        <i class="fas fa-eye me-1"></i>
                                        <?= $this->Number->format($product->view_count) ?> views
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="row">
            <div class="col-12">
                <nav aria-label="Product pagination">
                    <?= $this->Paginator->prev('« Previous', ['class' => 'btn btn-outline-secondary me-2']) ?>
                    <span class="mx-3">
                        Page <?= $this->Paginator->counter('{{page}} of {{pages}}') ?>
                    </span>
                    <?= $this->Paginator->next('Next »', ['class' => 'btn btn-outline-secondary ms-2']) ?>
                </nav>
            </div>
        </div>
    <?php else: ?>
        <!-- No Products Found -->
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Products Found</h4>
                    <p class="text-muted mb-4">
                        <?php if ($search || $manufacturer || $tag): ?>
                            No products match your current search criteria. Try adjusting your filters.
                        <?php else: ?>
                            No products are currently available. Be the first to submit one!
                        <?php endif; ?>
                    </p>
                    <div class="d-flex justify-content-center gap-2">
                        <?php if ($search || $manufacturer || $tag): ?>
                            <?= $this->Html->link(
                                '<i class="fas fa-times me-2"></i>Clear Filters',
                                ['action' => 'index'],
                                ['class' => 'btn btn-outline-primary', 'escape' => false]
                            ) ?>
                        <?php endif; ?>
                        <?= $this->Html->link(
                            '<i class="fas fa-plus-circle me-2"></i>Submit First Product',
                            ['action' => 'add'],
                            ['class' => 'btn btn-success', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
}

.badge {
    font-size: 0.75rem;
}

.card-img-top-container {
    border-radius: 0.375rem 0.375rem 0 0;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}
</style>

