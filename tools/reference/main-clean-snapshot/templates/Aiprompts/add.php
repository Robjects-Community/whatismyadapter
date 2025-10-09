<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Aiprompts'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="aiprompts form content">
            <?= $this->Form->create($aiprompt) ?>
            <fieldset>
                <legend><?= __('Add Aiprompt') ?></legend>
                <?php
                    echo $this->Form->control('task_type');
                    echo $this->Form->control('system_prompt');
                    echo $this->Form->control('model');
                    echo $this->Form->control('max_tokens');
                    echo $this->Form->control('temperature');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
