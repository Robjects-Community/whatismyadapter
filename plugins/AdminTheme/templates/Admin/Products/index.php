<?php

$this->assign('title', __('Products'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('Products') ?></h3>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-plus"></i> ' . __('New Product'),
                    ['action' => 'add'],
                    ['class' => 'btn btn-success', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-chart-line"></i> ' . __('Dashboard'),
                    ['action' => 'dashboard'],
                    ['class' => 'btn btn-info', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>

<!-- Filter and Search Bar -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline']) ?>
                
                <!-- Status Filter -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('status', [
                        'type' => 'select',
                        'options' => [
                            '' => __('All Status'),
                            'published' => __('Published'),
                            'unpublished' => __('Unpublished'),
                            'pending' => __('Pending Verification'),
                            'approved' => __('Approved'),
                            'rejected' => __('Rejected')
                        ],
                        'value' => $this->request->getQuery('status'),
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>

                <!-- Featured Filter -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('featured', [
                        'type' => 'checkbox',
                        'label' => __('Featured Only'),
                        'checked' => $this->request->getQuery('featured')
                    ]) ?>
                </div>

                <!-- Search -->
                <div class="form-group mr-3">
                    <?= $this->Form->control('search', [
                        'type' => 'text',
                        'placeholder' => __('Search products...'),
                        'value' => $this->request->getQuery('search'),
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>

                <!-- Submit -->
                <div class="form-group">
                    <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-primary']) ?>
                    <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-secondary ml-2']) ?>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<!-- Products Table -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <?php if (!empty($products)): ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= $this->Paginator->sort('title', __('Title')) ?></th>
                                    <th><?= $this->Paginator->sort('manufacturer', __('Manufacturer')) ?></th>
                                    <th><?= $this->Paginator->sort('price', __('Price')) ?></th>
                                    <th><?= __('Status') ?></th>
                                    <th><?= $this->Paginator->sort('reliability_score', __('Score')) ?></th>
                                    <th><?= $this->Paginator->sort('view_count', __('Views')) ?></th>
                                    <th><?= $this->Paginator->sort('created', __('Created')) ?></th>
                                    <th class="actions"><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if ($product->image): ?>
                                                <img src="<?= h($product->image) ?>" alt="<?= h($product->alt_text) ?>" 
                                                     class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php endif; ?>
                                            <div>
                                                <strong><?= h($product->title) ?></strong>
                                                <?php if ($product->featured): ?>
                                                    <span class="badge badge-warning ml-1"><?= __('Featured') ?></span>
                                                <?php endif; ?>
                                                <br>
                                                <small class="text-muted"><?= h($product->model_number) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?= h($product->manufacturer) ?></td>
                                    <td>
                                        <?php if ($product->price): ?>
                                            <?= number_format($product->price, 2) ?> <?= h($product->currency) ?>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'approved' => 'success', 
                                            'rejected' => 'danger'
                                        ][$product->verification_status] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $statusClass ?>">
                                            <?= __(ucfirst($product->verification_status)) ?>
                                        </span>
                                        <?php if ($product->is_published): ?>
                                            <span class="badge badge-success ml-1"><?= __('Published') ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($product->reliability_score > 0): ?>
                                            <span class="badge badge-info">
                                                <?= number_format($product->reliability_score, 1) ?>/5.0
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= number_format($product->view_count) ?></td>
                                    <td>
                                        <?= $product->created->format('M j, Y') ?><br>
                                        <small class="text-muted">by <?= h($product->user->username) ?></small>
                                    </td>
                                    <td class="actions">
                                        <div class="btn-group" role="group">
                                            <?= $this->Html->link(
                                                '<i class="fas fa-eye"></i>',
                                                ['action' => 'view', $product->id],
                                                ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'title' => __('View')]
                                            ) ?>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-edit"></i>',
                                                ['action' => 'edit', $product->id],
                                                ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false, 'title' => __('Edit')]
                                            ) ?>
                                            
                                            <!-- Toggle Featured -->
                                            <?= $this->Form->postLink(
                                                $product->featured ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star"></i>',
                                                ['action' => 'toggleFeatured', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-warning',
                                                    'escape' => false,
                                                    'title' => $product->featured ? __('Remove from Featured') : __('Make Featured'),
                                                    'confirm' => __('Are you sure?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Toggle Published -->
                                            <?= $this->Form->postLink(
                                                $product->is_published ? '<i class="fas fa-toggle-on text-success"></i>' : '<i class="fas fa-toggle-off text-secondary"></i>',
                                                ['action' => 'togglePublished', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-info',
                                                    'escape' => false,
                                                    'title' => $product->is_published ? __('Unpublish') : __('Publish'),
                                                    'confirm' => __('Are you sure?')
                                                ]
                                            ) ?>
                                            
                                            <!-- Delete -->
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-trash"></i>',
                                                ['action' => 'delete', $product->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-danger',
                                                    'escape' => false,
                                                    'title' => __('Delete'),
                                                    'confirm' => __('Are you sure you want to delete {0}?', $product->title)
                                                ]
                                            ) ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav>
                        <ul class="pagination justify-content-center">
                            <?= $this->Paginator->first('<< ' . __('first')) ?>
                            <?= $this->Paginator->prev('< ' . __('previous')) ?>
                            <?= $this->Paginator->numbers() ?>
                            <?= $this->Paginator->next(__('next') . ' >') ?>
                            <?= $this->Paginator->last(__('last') . ' >>') ?>
                        </ul>
                    </nav>

                    <p class="text-muted">
                        <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
                    </p>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p><?= __('No products found.') ?></p>
                        <?= $this->Html->link(
                            __('Add the first product'),
                            ['action' => 'add'],
                            ['class' => 'btn btn-primary']
                        ) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

