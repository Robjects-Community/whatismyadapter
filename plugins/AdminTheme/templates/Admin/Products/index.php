<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */
?>

<div class="products index content">
    <?= $this->Html->link(__('New Product'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Products') ?></h3>
    
    <div class="search-container">
        <?= $this->Form->create(null, ['type' => 'get']) ?>
        <?= $this->Form->control('search', [
            'placeholder' => __('Search products...'),
            'value' => $this->request->getQuery('search'),
            'label' => false
        ]) ?>
        <?= $this->Form->button(__('Search'), ['type' => 'submit']) ?>
        <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'button']) ?>
        <?= $this->Form->end() ?>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('title') ?></th>
                    <th><?= $this->Paginator->sort('manufacturer') ?></th>
                    <th><?= $this->Paginator->sort('price') ?></th>
                    <th><?= $this->Paginator->sort('verification_status', 'Status') ?></th>
                    <th><?= $this->Paginator->sort('user_id', 'Created By') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <?= h($product->title) ?>
                        <?php if ($product->featured): ?>
                            <span class="badge badge-star">★ Featured</span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($product->manufacturer) ?></td>
                    <td>
                        <?php if ($product->price): ?>
                            <?= $this->Number->currency($product->price, $product->currency ?? 'USD') ?>
                        <?php else: ?>
                            <em>No price set</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge status-<?= h($product->verification_status) ?>">
                            <?= h($product->verification_status) ?>
                        </span>
                    </td>
                    <td>
                        <?= $product->user ? h($product->user->username) : 'N/A' ?>
                    </td>
                    <td><?= h($product->created->format('Y-m-d H:i')) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $product->id], ['class' => 'button small']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id], ['class' => 'button small']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $product->id], [
                            'confirm' => __('Are you sure you want to delete "{0}"?', $product->title),
                            'class' => 'button small danger'
                        ]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
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
</div>

<style>
.status-badge {
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}
.status-pending { background: #ffc107; color: #000; }
.status-verified { background: #28a745; color: #fff; }
.status-rejected { background: #dc3545; color: #fff; }
.badge-star { 
    background: #17a2b8; 
    color: white; 
    padding: 2px 6px; 
    border-radius: 3px; 
    font-size: 0.7em; 
}
.search-container { 
    margin-bottom: 20px; 
    display: flex; 
    gap: 10px; 
    align-items: end; 
}
</style>
