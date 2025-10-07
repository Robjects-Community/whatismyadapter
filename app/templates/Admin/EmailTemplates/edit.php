<?php
$this->assign('title', __('Edit emailTemplate'));
?>
<div class="emailTemplate form content">
    <?= $this->Form->create($emailTemplate ?? null) ?>
    <fieldset>
        <legend><?= __('Edit emailTemplate') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
