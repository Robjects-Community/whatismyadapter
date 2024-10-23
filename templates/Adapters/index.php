<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Adapter> $adapters
 */
?>
<div class="adapters index content">
    <?= $this->Html->link(__('New Adapter'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Adapters') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('type') ?></th>
                    <th><?= $this->Paginator->sort('gender') ?></th>
                    <th><?= $this->Paginator->sort('additional_params') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($adapters as $adapter): ?>
                <tr>
                    <td><?= $this->Number->format($adapter->id) ?></td>
                    <td><?= h($adapter->name) ?></td>
                    <td><?= h($adapter->type) ?></td>
                    <td><?= h($adapter->gender) ?></td>
                    <td><?= h($adapter->additional_params) ?></td>
                    <td><?= h($adapter->created) ?></td>
                    <td><?= h($adapter->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $adapter->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $adapter->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $adapter->id], ['confirm' => __('Are you sure you want to delete # {0}?', $adapter->id)]) ?>
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