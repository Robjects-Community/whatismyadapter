<?php
$this->assign('title', __('Add queueConfiguration'));
?>
<div class="queueConfiguration form content">
    <?= $this->Form->create($queueConfiguration ?? null) ?>
    <fieldset>
        <legend><?= __('Add queueConfiguration') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
