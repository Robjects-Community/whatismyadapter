<?php
$this->assign('title', __('Add emailTemplate'));
?>
<div class="emailTemplate form content">
    <?= $this->Form->create($emailTemplate ?? null) ?>
    <fieldset>
        <legend><?= __('Add emailTemplate') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
