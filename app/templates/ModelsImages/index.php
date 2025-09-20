<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ModelsImage> $modelsImages
 */
?>
<div class="modelsImages index content">
    <?= $this->Html->link(__('New Models Image'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Models Images') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('model') ?></th>
                    <th><?= $this->Paginator->sort('foreign_key') ?></th>
                    <th><?= $this->Paginator->sort('image_id') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($modelsImages as $modelsImage): ?>
                <tr>
                    <td><?= h($modelsImage->id) ?></td>
                    <td><?= h($modelsImage->model) ?></td>
                    <td><?= $modelsImage->hasValue('article') ? $this->Html->link($modelsImage->article->title, ['controller' => 'Articles', 'action' => 'view', $modelsImage->article->id]) : '' ?></td>
                    <td><?= $modelsImage->hasValue('image') ? $this->Html->link($modelsImage->image->name, ['controller' => 'Images', 'action' => 'view', $modelsImage->image->id]) : '' ?></td>
                    <td><?= h($modelsImage->created) ?></td>
                    <td><?= h($modelsImage->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $modelsImage->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $modelsImage->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $modelsImage->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $modelsImage->id),
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