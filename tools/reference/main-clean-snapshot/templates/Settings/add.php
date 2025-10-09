<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Setting $setting
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Settings'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="settings form content">
            <?= $this->Form->create($setting) ?>
            <fieldset>
                <legend><?= __('Add Setting') ?></legend>
                <?php
                    echo $this->Form->control('ordering');
                    echo $this->Form->control('category');
                    echo $this->Form->control('key_name');
                    echo $this->Form->control('value');
                    echo $this->Form->control('value_type');
                    echo $this->Form->control('value_obscure');
                    echo $this->Form->control('description');
                    echo $this->Form->control('data');
                    echo $this->Form->control('column_width');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
