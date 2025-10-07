<?php
$this->assign('title', __('Add comment'));
?>
<div class="comment form content">
    <?= $this->Form->create($comment ?? null) ?>
    <fieldset>
        <legend><?= __('Add comment') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
