<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGalleriesImage $imageGalleriesImage
 * @var \Cake\Collection\CollectionInterface|string[] $imageGalleries
 * @var \Cake\Collection\CollectionInterface|string[] $images
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Image Galleries Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="imageGalleriesImages form content">
            <?= $this->Form->create($imageGalleriesImage) ?>
            <fieldset>
                <legend><?= __('Add Image Galleries Image') ?></legend>
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
