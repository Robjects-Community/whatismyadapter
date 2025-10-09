<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGalleriesImage $imageGalleriesImage
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Image Galleries Image'), ['action' => 'edit', $imageGalleriesImage->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Image Galleries Image'), ['action' => 'delete', $imageGalleriesImage->id], ['confirm' => __('Are you sure you want to delete # {0}?', $imageGalleriesImage->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Image Galleries Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image Galleries Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="imageGalleriesImages view content">
            <h3><?= h($imageGalleriesImage->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($imageGalleriesImage->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image Gallery') ?></th>
                    <td><?= $imageGalleriesImage->hasValue('image_gallery') ? $this->Html->link($imageGalleriesImage->image_gallery->name, ['controller' => 'ImageGalleries', 'action' => 'view', $imageGalleriesImage->image_gallery->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= $imageGalleriesImage->hasValue('image') ? $this->Html->link($imageGalleriesImage->image->name, ['controller' => 'Images', 'action' => 'view', $imageGalleriesImage->image->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Position') ?></th>
                    <td><?= $this->Number->format($imageGalleriesImage->position) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($imageGalleriesImage->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($imageGalleriesImage->modified) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Caption') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGalleriesImage->caption)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>