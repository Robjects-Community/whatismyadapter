<?php
$this->assign('title', __('Edit image'));
?>
<div class="image form content">
    <?= $this->Form->create($image ?? null) ?>
    <fieldset>
        <legend><?= __('Edit image') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
