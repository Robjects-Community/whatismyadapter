<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Slugs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="slugs form content">
            <?= $this->Form->create($slug) ?>
            <fieldset>
                <legend><?= __('Add Slug') ?></legend>
                <?php
                    echo $this->Form->control('model');
                    echo $this->Form->control('foreign_key', ['options' => $tags]);
                    echo $this->Form->control('slug');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
