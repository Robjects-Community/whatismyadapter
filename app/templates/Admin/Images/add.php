<?php
$this->assign('title', __('Add image'));
?>
<div class="image form content">
    <?= $this->Form->create($image ?? null) ?>
    <fieldset>
        <legend><?= __('Add image') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
