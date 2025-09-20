<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 * @var \Cake\Collection\CollectionInterface|string[] $parentTag
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Tags'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="tags form content">
            <?= $this->Form->create($tag) ?>
            <fieldset>
                <legend><?= __('Add Tag') ?></legend>
                <?php
                    echo $this->Form->control('title');
                    echo $this->Form->control('slug');
                    echo $this->Form->control('description');
                    echo $this->Form->control('image');
                    echo $this->Form->control('dir');
                    echo $this->Form->control('alt_text');
                    echo $this->Form->control('keywords');
                    echo $this->Form->control('size');
                    echo $this->Form->control('mime');
                    echo $this->Form->control('name');
                    echo $this->Form->control('meta_title');
                    echo $this->Form->control('meta_description');
                    echo $this->Form->control('meta_keywords');
                    echo $this->Form->control('facebook_description');
                    echo $this->Form->control('linkedin_description');
                    echo $this->Form->control('instagram_description');
                    echo $this->Form->control('twitter_description');
                    echo $this->Form->control('parent_id', ['options' => $parentTag, 'empty' => true]);
                    echo $this->Form->control('main_menu');
                    echo $this->Form->control('articles._ids', ['options' => $articles]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
