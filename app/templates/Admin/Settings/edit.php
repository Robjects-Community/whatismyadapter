<?php
$this->assign('title', __('Edit setting'));
?>
<div class="setting form content">
    <?= $this->Form->create($setting ?? null) ?>
    <fieldset>
        <legend><?= __('Edit setting') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
