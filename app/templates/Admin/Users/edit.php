<?php
$this->assign('title', __('Edit user'));
?>
<div class="user form content">
    <?= $this->Form->create($user ?? null) ?>
    <fieldset>
        <legend><?= __('Edit user') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
