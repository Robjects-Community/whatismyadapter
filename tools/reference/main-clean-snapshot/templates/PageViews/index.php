<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\PageView> $pageViews
 */
?>
<div class="pageViews index content">
    <?= $this->Html->link(__('New Page View'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Page Views') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('article_id') ?></th>
                    <th><?= $this->Paginator->sort('product_id') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pageViews as $pageView): ?>
                <tr>
                    <td><?= h($pageView->id) ?></td>
                    <td><?= $pageView->hasValue('article') ? $this->Html->link($pageView->article->title, ['controller' => 'Articles', 'action' => 'view', $pageView->article->id]) : '' ?></td>
                    <td><?= h($pageView->product_id) ?></td>
                    <td><?= h($pageView->ip_address) ?></td>
                    <td><?= h($pageView->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $pageView->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $pageView->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $pageView->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $pageView->id),
                            ]
                        ) ?>
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