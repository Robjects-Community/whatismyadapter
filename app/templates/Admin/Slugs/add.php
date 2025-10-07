<?php
$this->assign('title', __('Add slug'));
?>
<div class="slug form content">
    <?= $this->Form->create($slug ?? null) ?>
    <fieldset>
        <legend><?= __('Add slug') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
