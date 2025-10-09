<?php
$this->assign('title', __('Add setting'));
?>
<div class="setting form content">
    <?= $this->Form->create($setting ?? null) ?>
    <fieldset>
        <legend><?= __('Add setting') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
