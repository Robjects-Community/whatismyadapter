<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<div class="images index content">
    <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Images') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('alt_text') ?></th>
                    <th><?= $this->Paginator->sort('keywords') ?></th>
                    <th><?= $this->Paginator->sort('image') ?></th>
                    <th><?= $this->Paginator->sort('dir') ?></th>
                    <th><?= $this->Paginator->sort('size') ?></th>
                    <th><?= $this->Paginator->sort('mime') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($images as $image): ?>
                <tr>
                    <td><?= h($image->id) ?></td>
                    <td><?= h($image->name) ?></td>
                    <td><?= h($image->alt_text) ?></td>
                    <td><?= h($image->keywords) ?></td>
                    <td><?= h($image->image) ?></td>
                    <td><?= h($image->dir) ?></td>
                    <td><?= $this->Number->format($image->size) ?></td>
                    <td><?= h($image->mime) ?></td>
                    <td><?= h($image->created) ?></td>
                    <td><?= h($image->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $image->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $image->id),
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