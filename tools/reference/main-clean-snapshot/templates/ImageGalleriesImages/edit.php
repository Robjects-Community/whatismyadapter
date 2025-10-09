<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGalleriesImage $imageGalleriesImage
 * @var string[]|\Cake\Collection\CollectionInterface $imageGalleries
 * @var string[]|\Cake\Collection\CollectionInterface $images
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $imageGalleriesImage->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $imageGalleriesImage->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Image Galleries Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="imageGalleriesImages form content">
            <?= $this->Form->create($imageGalleriesImage) ?>
            <fieldset>
                <legend><?= __('Edit Image Galleries Image') ?></legend>
                <?php
                    echo $this->Form->control('image_gallery_id', ['options' => $imageGalleries]);
                    echo $this->Form->control('image_id', ['options' => $images]);
                    echo $this->Form->control('position');
                    echo $this->Form->control('caption');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
