<?php
$this->assign('title', __('Add page'));
?>
<div class="page form content">
    <?= $this->Form->create($page ?? null) ?>
    <fieldset>
        <legend><?= __('Add page') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
