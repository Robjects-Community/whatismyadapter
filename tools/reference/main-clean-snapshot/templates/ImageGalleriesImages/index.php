<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\ImageGalleriesImage> $imageGalleriesImages
 */
?>
<div class="imageGalleriesImages index content">
    <?= $this->Html->link(__('New Image Galleries Image'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Image Galleries Images') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('image_gallery_id') ?></th>
                    <th><?= $this->Paginator->sort('image_id') ?></th>
                    <th><?= $this->Paginator->sort('position') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($imageGalleriesImages as $imageGalleriesImage): ?>
                <tr>
                    <td><?= h($imageGalleriesImage->id) ?></td>
                    <td><?= $imageGalleriesImage->hasValue('image_gallery') ? $this->Html->link($imageGalleriesImage->image_gallery->name, ['controller' => 'ImageGalleries', 'action' => 'view', $imageGalleriesImage->image_gallery->id]) : '' ?></td>
                    <td><?= $imageGalleriesImage->hasValue('image') ? $this->Html->link($imageGalleriesImage->image->name, ['controller' => 'Images', 'action' => 'view', $imageGalleriesImage->image->id]) : '' ?></td>
                    <td><?= $this->Number->format($imageGalleriesImage->position) ?></td>
                    <td><?= h($imageGalleriesImage->created) ?></td>
                    <td><?= h($imageGalleriesImage->modified) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $imageGalleriesImage->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $imageGalleriesImage->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $imageGalleriesImage->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $imageGalleriesImage->id),
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