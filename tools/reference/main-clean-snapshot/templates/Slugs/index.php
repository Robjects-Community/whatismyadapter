<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<div class="slugs index content">
    <?= $this->Html->link(__('New Slug'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Slugs') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('foreign_key') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slugs as $slug): ?>
                <tr>
                    <td><?= h($slug->id) ?></td>
                    <td><?= h($slug->model) ?></td>
                    <td><?= $slug->hasValue('article') ? $this->Html->link($slug->article->title, ['controller' => 'Articles', 'action' => 'view', $slug->article->id]) : '' ?></td>
                    <td><?= $slug->hasValue('tag') ? $this->Html->link($slug->tag->title, ['controller' => 'Tags', 'action' => 'view', $slug->tag->id]) : '' ?></td>
                    <td><?= h($slug->slug) ?></td>
                    <td><?= h($slug->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $slug->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $slug->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $slug->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $slug->id),
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