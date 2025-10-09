<?php
/**
 * Admin Products Index Template
 */
$this->assign('title', __('Products'));
?>

<div class="products-index">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?= __('Products') ?></h1>
            <div class="actions">
                <?= $this->Html->link(__('Add Product'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Dashboard'), ['action' => 'dashboard'], ['class' => 'btn btn-info']) ?>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <?= $this->Form->create(null, ['type' => 'get', 'valueSources' => 'query']) ?>
                <div class="row">
                    <div class="col-md-4">
                        <?= $this->Form->control('search', [
                            'label' => __('Search'),
                            'placeholder' => __('Title, manufacturer, model...'),
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->Form->control('status', [
                            'label' => __('Status'),
                            'options' => [
                                '' => __('All'),
                                'published' => __('Published'),
                                'unpublished' => __('Unpublished'),
                                'pending' => __('Pending'),
                                'approved' => __('Approved')
                            ],
                            'empty' => __('All Status'),
                            'class' => 'form-control'
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <?= $this->Form->control('featured', [
                            'label' => __('Featured'),
                            'type' => 'checkbox',
                            'class' => 'form-check-input'
                        ]) ?>
                    </div>
                    <div class="col-md-2">
                        <?= $this->Form->button(__('Search'), ['class' => 'btn btn-primary']) ?>
                        <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                    </div>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-body">
                <?php if (!empty($products)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><?= __('ID') ?></th>
                                <th><?= __('Title') ?></th>
                                <th><?= __('Manufacturer') ?></th>
                                <th><?= __('Model') ?></th>
                                <th><?= __('Status') ?></th>
                                <th><?= __('Featured') ?></th>
                                <th><?= __('Created') ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?= h($product->id) ?></td>
                                <td><?= $this->Html->link(h($product->title), ['action' => 'view', $product->id]) ?></td>
                                <td><?= h($product->manufacturer) ?></td>
                                <td><?= h($product->model_number) ?></td>
                                <td>
                                    <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                        <?= $product->is_published ? __('Published') : __('Unpublished') ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($product->featured): ?>
                                        <i class="fas fa-star text-warning"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($product->created->format('M j, Y')) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__('View'), ['action' => 'view', $product->id], ['class' => 'btn btn-sm btn-info']) ?>
                                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id], ['class' => 'btn btn-sm btn-primary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $product->id], [
                                        'confirm' => __('Are you sure you want to delete # {0}?', $product->id),
                                        'class' => 'btn btn-sm btn-danger'
                                    ]) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="paginator">
                    <ul class="pagination">
                        <?= $this->Paginator->first('<< ' . __('first')) ?>
                        <?= $this->Paginator->prev('< ' . __('previous')) ?>
                        <?= $this->Paginator->numbers() ?>
                        <?= $this->Paginator->next(__('next') . ' >') ?>
                        <?= $this->Paginator->last(__('last') . ' >>') ?>
                    </ul>
                    <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                </div>
                <?php else: ?>
                <div class="alert alert-info">
                    <?= __('No products found.') ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
