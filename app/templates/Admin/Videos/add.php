<?php
$this->assign('title', __('Add video'));
?>
<div class="video form content">
    <?= $this->Form->create($video ?? null) ?>
    <fieldset>
        <legend><?= __('Add video') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
