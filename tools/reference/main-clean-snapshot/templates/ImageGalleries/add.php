<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 * @var \Cake\Collection\CollectionInterface|string[] $images
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Image Galleries'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="imageGalleries form content">
            <?= $this->Form->create($imageGallery) ?>
            <fieldset>
                <legend><?= __('Add Image Gallery') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('description');
                    echo $this->Form->control('preview_image');
                    echo $this->Form->control('is_published');
                    echo $this->Form->control('created_by');
                    echo $this->Form->control('modified_by');
                    echo $this->Form->control('meta_title');
                    echo $this->Form->control('meta_description');
                    echo $this->Form->control('meta_keywords');
                    echo $this->Form->control('facebook_description');
                    echo $this->Form->control('linkedin_description');
                    echo $this->Form->control('instagram_description');
                    echo $this->Form->control('twitter_description');
                    echo $this->Form->control('images._ids', ['options' => $images]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
