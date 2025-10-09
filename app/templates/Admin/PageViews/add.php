<?php
$this->assign('title', __('Add pageView'));
?>
<div class="pageView form content">
    <?= $this->Form->create($pageView ?? null) ?>
    <fieldset>
        <legend><?= __('Add pageView') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
