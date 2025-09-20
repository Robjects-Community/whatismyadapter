<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGallery> $imageGalleries
 */
?>
<div class="imageGalleries index content">
    <?= $this->Html->link(__('New Image Gallery'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Image Galleries') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('name') ?></th>
                    <th><?= $this->Paginator->sort('slug') ?></th>
                    <th><?= $this->Paginator->sort('preview_image') ?></th>
                    <th><?= $this->Paginator->sort('is_published') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th><?= $this->Paginator->sort('created_by') ?></th>
                    <th><?= $this->Paginator->sort('modified_by') ?></th>
                    <th><?= $this->Paginator->sort('meta_title') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($imageGalleries as $imageGallery): ?>
                <tr>
                    <td><?= h($imageGallery->id) ?></td>
                    <td><?= h($imageGallery->name) ?></td>
                    <td><?= h($imageGallery->slug) ?></td>
                    <td><?= h($imageGallery->preview_image) ?></td>
                    <td><?= h($imageGallery->is_published) ?></td>
                    <td><?= h($imageGallery->created) ?></td>
                    <td><?= h($imageGallery->modified) ?></td>
                    <td><?= h($imageGallery->created_by) ?></td>
                    <td><?= h($imageGallery->modified_by) ?></td>
                    <td><?= h($imageGallery->meta_title) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $imageGallery->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $imageGallery->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $imageGallery->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $imageGallery->id),
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