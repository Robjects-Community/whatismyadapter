<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ModelsImage $modelsImage
 * @var string[]|\Cake\Collection\CollectionInterface $images
 * @var string[]|\Cake\Collection\CollectionInterface $articles
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $modelsImage->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $modelsImage->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Models Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="modelsImages form content">
            <?= $this->Form->create($modelsImage) ?>
            <fieldset>
                <legend><?= __('Edit Models Image') ?></legend>
                <?php
                    echo $this->Form->control('model');
                    echo $this->Form->control('foreign_key', ['options' => $articles]);
                    echo $this->Form->control('image_id', ['options' => $images]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
