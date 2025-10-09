<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 * @var string[]|\Cake\Collection\CollectionInterface $imageGalleries
 * @var string[]|\Cake\Collection\CollectionInterface $articles
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $image->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $image->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="images form content">
            <?= $this->Form->create($image) ?>
            <fieldset>
                <legend><?= __('Edit Image') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('alt_text');
                    echo $this->Form->control('keywords');
                    echo $this->Form->control('image');
                    echo $this->Form->control('dir');
                    echo $this->Form->control('size');
                    echo $this->Form->control('mime');
                    echo $this->Form->control('image_galleries._ids', ['options' => $imageGalleries]);
                    echo $this->Form->control('articles._ids', ['options' => $articles]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
