<?php
$this->assign('title', __('Add user'));
?>
<div class="user form content">
    <?= $this->Form->create($user ?? null) ?>
    <fieldset>
        <legend><?= __('Add user') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
