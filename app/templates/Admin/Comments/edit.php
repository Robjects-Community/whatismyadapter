<?php
$this->assign('title', __('Edit comment'));
?>
<div class="comment form content">
    <?= $this->Form->create($comment ?? null) ?>
    <fieldset>
        <legend><?= __('Edit comment') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
