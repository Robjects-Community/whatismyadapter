<?php
$this->assign('title', __('Edit pageView'));
?>
<div class="pageView form content">
    <?= $this->Form->create($pageView ?? null) ?>
    <fieldset>
        <legend><?= __('Edit pageView') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
