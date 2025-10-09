<?php
/**
 * Admin Products Dashboard Template
 * 
 * Displays product statistics, recent products, and quick actions
 */

$this->assign('title', __('Products Dashboard'));
?>

<div class="products-dashboard">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header d-flex justify-content-between align-items-center mb-4">
                    <h1 class="page-title"><?= __('Products Dashboard') ?></h1>
                    <div class="actions">
                        <?= $this->Html->link(
                            __('Add Product'),
                            ['action' => 'add'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= __('Total Products') ?></h5>
                        <h2 class="text-primary"><?= number_format($totalProducts) ?></h2>
                        <small class="text-muted"><?= __('All products') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= __('Published') ?></h5>
                        <h2 class="text-success"><?= number_format($publishedProducts) ?></h2>
                        <small class="text-muted"><?= __('Live products') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= __('Pending Review') ?></h5>
                        <h2 class="text-warning"><?= number_format($pendingProducts) ?></h2>
                        <small class="text-muted"><?= __('Awaiting verification') ?></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?= __('Featured') ?></h5>
                        <h2 class="text-info"><?= number_format($featuredProducts) ?></h2>
                        <small class="text-muted"><?= __('Featured products') ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Products and Top Manufacturers -->
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Recent Products') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($recentProducts)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentProducts as $product): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">
                                            <?= $this->Html->link(
                                                h($product->title),
                                                ['action' => 'view', $product->id]
                                            ) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= $product->created->format('M j, Y') ?> 
                                            <?php if (!empty($product->user)): ?>
                                                by <?= h($product->user->username) ?>
                                            <?php endif; ?>
                                            <?php if (!empty($product->manufacturer)): ?>
                                                • <?= h($product->manufacturer) ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if (!empty($product->tags)): ?>
                                            <div class="mt-1">
                                                <?php foreach ($product->tags as $tag): ?>
                                                    <span class="badge badge-secondary badge-sm"><?= h($tag->title) ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                            <?= $product->is_published ? __('Published') : __(ucfirst($product->verification_status)) ?>
                                        </span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?= __('No recent products') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Top Manufacturers') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($topManufacturers)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($topManufacturers as $manufacturer): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= h($manufacturer->manufacturer) ?></span>
                                    <span class="badge badge-primary badge-pill"><?= $manufacturer->count ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted"><?= __('No manufacturer data available') ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Popular Tags -->
        <?php if (!empty($popularTags)): ?>
        <div class="row">
            <div class="col-md-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Popular Tags') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($popularTags as $tag): ?>
                            <div class="col-md-3 mb-2">
                                <span class="badge badge-info p-2">
                                    <?= h($tag->title) ?> 
                                    <span class="badge badge-light ml-1"><?= $tag->count ?></span>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><?= __('Quick Actions') ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="btn-group" role="group">
                            <?= $this->Html->link(
                                __('Add Product'),
                                ['action' => 'add'],
                                ['class' => 'btn btn-success']
                            ) ?>
                            <?= $this->Html->link(
                                __('All Products'),
                                ['action' => 'index'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                            <?= $this->Html->link(
                                __('Pending Review'),
                                ['action' => 'index', '?' => ['status' => 'pending']],
                                ['class' => 'btn btn-warning']
                            ) ?>
                            <?= $this->Html->link(
                                __('Featured Products'),
                                ['action' => 'index', '?' => ['featured' => '1']],
                                ['class' => 'btn btn-info']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
