<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Adapter $adapter
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $adapter->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $adapter->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Adapters'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="adapters form content">
            <?= $this->Form->create($adapter) ?>
            <fieldset>
                <legend><?= __('Edit Adapter') ?></legend>
                <?php
                    echo $this->Form->control('name');
                    echo $this->Form->control('type');
                    echo $this->Form->control('gender');
                    echo $this->Form->control('additional_params');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
