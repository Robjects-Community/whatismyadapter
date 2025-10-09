<?php
$this->assign('title', __('Edit video'));
?>
<div class="video form content">
    <?= $this->Form->create($video ?? null) ?>
    <fieldset>
        <legend><?= __('Edit video') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
