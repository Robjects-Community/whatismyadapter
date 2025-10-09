<?php
$this->assign('title', __('Edit queueConfiguration'));
?>
<div class="queueConfiguration form content">
    <?= $this->Form->create($queueConfiguration ?? null) ?>
    <fieldset>
        <legend><?= __('Edit queueConfiguration') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
