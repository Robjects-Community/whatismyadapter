<?php
$this->assign('title', __('Edit slug'));
?>
<div class="slug form content">
    <?= $this->Form->create($slug ?? null) ?>
    <fieldset>
        <legend><?= __('Edit slug') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
