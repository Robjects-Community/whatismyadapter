<?php
/**
 * Products Index Template
 * 
 * Displays a listing of published products with filtering and pagination
 */

$this->assign('title', __('Products'));
$this->Html->meta('description', __('Browse our collection of adapters and products'), ['block' => 'meta']);
?>

<div class="products-index">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <h1 class="page-title"><?= __('Products') ?></h1>
                    <p class="page-description"><?= __('Browse our collection of adapters and products. Find the perfect solution for your needs.') ?></p>
                </div>

                <!-- Action Bar -->
                <div class="action-bar mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <?= $this->Html->link(
                                __('Take the Quiz'),
                                ['action' => 'quiz'],
                                ['class' => 'btn btn-primary btn-lg']
                            ) ?>
                            <?php if ($this->Identity->isLoggedIn()): ?>
                                <?= $this->Html->link(
                                    __('Submit a Product'),
                                    ['action' => 'add'],
                                    ['class' => 'btn btn-outline-secondary']
                                ) ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="products-count">
                                <small class="text-muted">
                                    <?= __('{0} products found', $this->Paginator->counter('{{count}}')) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filters-section mb-4">
                    <div class="card">
                        <div class="card-body">
                            <?= $this->Form->create(null, [
                                'type' => 'get',
                                'class' => 'filter-form'
                            ]) ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <?= $this->Form->control('search', [
                                        'label' => __('Search'),
                                        'placeholder' => __('Search products...'),
                                        'value' => $this->request->getQuery('search'),
                                        'class' => 'form-control'
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $this->Form->control('category_id', [
                                        'label' => __('Category'),
                                        'options' => $categories ?? [],
                                        'empty' => __('All Categories'),
                                        'value' => $this->request->getQuery('category_id'),
                                        'class' => 'form-select'
                                    ]) ?>
                                </div>
                                <div class="col-md-3">
                                    <?= $this->Form->control('sort', [
                                        'label' => __('Sort By'),
                                        'options' => [
                                            'title' => __('Name (A-Z)'),
                                            'title DESC' => __('Name (Z-A)'),
                                            'created DESC' => __('Newest First'),
                                            'created' => __('Oldest First'),
                                            'featured DESC' => __('Featured First')
                                        ],
                                        'value' => $this->request->getQuery('sort', 'featured DESC'),
                                        'class' => 'form-select'
                                    ]) ?>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <?= $this->Form->submit(__('Filter'), ['class' => 'btn btn-outline-primary']) ?>
                                        <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                                    </div>
                                </div>
                            </div>
                            <?= $this->Form->end() ?>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (!empty($products)): ?>
                    <div class="products-grid">
                        <div class="row g-4">
                            <?php foreach ($products as $product): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="product-card h-100">
                                        <div class="card h-100">
                                            <?php if (!empty($product->featured_image)): ?>
                                                <div class="product-image">
                                                    <?= $this->Html->image($product->featured_image, [
                                                        'alt' => h($product->title),
                                                        'class' => 'card-img-top product-thumbnail'
                                                    ]) ?>
                                                    <?php if ($product->featured): ?>
                                                        <div class="featured-badge">
                                                            <span class="badge bg-warning"><?= __('Featured') ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="card-body d-flex flex-column">
                                                <h5 class="card-title">
                                                    <?= $this->Html->link(
                                                        h($product->title),
                                                        ['action' => 'view', $product->id],
                                                        ['class' => 'text-decoration-none']
                                                    ) ?>
                                                </h5>
                                                
                                                <?php if (!empty($product->excerpt)): ?>
                                                    <p class="card-text text-muted flex-grow-1">
                                                        <?= $this->Text->truncate(h($product->excerpt), 120, ['ellipsis' => '...']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                
                                                <div class="product-meta mt-auto">
                                                    <?php if (!empty($product->product_category)): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-tag"></i>
                                                            <?= h($product->product_category->name) ?>
                                                        </small>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($product->verified): ?>
                                                        <span class="badge bg-success ms-2">
                                                            <i class="fas fa-check-circle"></i>
                                                            <?= __('Verified') ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="mt-3">
                                                    <?= $this->Html->link(
                                                        __('View Details'),
                                                        ['action' => 'view', $product->id],
                                                        ['class' => 'btn btn-primary btn-sm w-100']
                                                    ) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="pagination-wrapper mt-5">
                        <?= $this->element('pagination') ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State -->
                    <div class="empty-state text-center py-5">
                        <div class="empty-icon mb-3">
                            <i class="fas fa-search fa-3x text-muted"></i>
                        </div>
                        <h3><?= __('No products found') ?></h3>
                        <p class="text-muted"><?= __('We couldn\'t find any products matching your criteria.') ?></p>
                        <div class="mt-3">
                            <?= $this->Html->link(
                                __('Take the Quiz'),
                                ['action' => 'quiz'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                            <?= $this->Html->link(
                                __('Clear Filters'),
                                ['action' => 'index'],
                                ['class' => 'btn btn-outline-secondary']
                            ) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.product-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-image {
    position: relative;
    overflow: hidden;
}

.product-thumbnail {
    height: 200px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.product-card:hover .product-thumbnail {
    transform: scale(1.05);
}

.featured-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.product-meta {
    font-size: 0.875rem;
}

.filters-section .card {
    border: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.empty-state {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.action-bar {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 1rem;
}

@media (max-width: 768px) {
    .products-grid .col-md-6 {
        margin-bottom: 1rem;
    }
    
    .action-bar .col-md-6:first-child {
        text-align: center;
        margin-bottom: 1rem;
    }
}
</style>
