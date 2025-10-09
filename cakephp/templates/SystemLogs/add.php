<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\SystemLog $systemLog
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List System Logs'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="systemLogs form content">
            <?= $this->Form->create($systemLog) ?>
            <fieldset>
                <legend><?= __('Add System Log') ?></legend>
                <?php
                    echo $this->Form->control('level');
                    echo $this->Form->control('message');
                    echo $this->Form->control('context');
                    echo $this->Form->control('group_name');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
