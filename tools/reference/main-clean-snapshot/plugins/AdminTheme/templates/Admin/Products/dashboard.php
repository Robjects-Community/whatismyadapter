<?php
$this->assign('title', __('Products Dashboard'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<?= $this->element('nav/products_tabs') ?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Products Dashboard') ?></h3>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title"><?= number_format($totalProducts) ?></h5>
                <p class="card-text"><?= __('Total Products') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?= number_format($publishedProducts) ?></h5>
                <p class="card-text"><?= __('Published') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?= number_format($pendingProducts) ?></h5>
                <p class="card-text"><?= __('Pending') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-info"><?= number_format($approvedProducts) ?></h5>
                <p class="card-text"><?= __('Approved') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-sm-4 col-6">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?= number_format($featuredProducts) ?></h5>
                <p class="card-text"><?= __('Featured') ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Recent Products') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($recentProducts)): ?>
                    <div class="list-group">
                        <?php foreach ($recentProducts as $product): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><?= h($product->title) ?></strong>
                                    <small class="text-muted d-block"><?= h($product->manufacturer) ?></small>
                                </div>
                                <div>
                                    <small class="text-muted"><?= $product->created->format('M j, Y') ?></small>
                                    <?php if ($product->is_published): ?>
                                        <span class="badge badge-success ml-2"><?= __('Published') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-secondary ml-2"><?= __('Draft') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p><?= __('No products found.') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Manufacturers & Popular Tags -->
<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Top Manufacturers') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($topManufacturers)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($topManufacturers as $manufacturer): ?>
                            <li class="d-flex justify-content-between">
                                <span><?= h($manufacturer->manufacturer) ?></span>
                                <span class="badge badge-primary"><?= number_format($manufacturer->count) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?= __('No manufacturer data available.') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Popular Tags') ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($popularTags)): ?>
                    <ul class="list-unstyled">
                        <?php foreach ($popularTags as $tag): ?>
                            <li class="d-flex justify-content-between">
                                <span><?= h($tag->title) ?></span>
                                <span class="badge badge-info"><?= number_format($tag->count) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p><?= __('No tag data available.') ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
