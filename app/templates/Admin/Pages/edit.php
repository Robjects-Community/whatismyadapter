<?php
$this->assign('title', __('Edit page'));
?>
<div class="page form content">
    <?= $this->Form->create($page ?? null) ?>
    <fieldset>
        <legend><?= __('Edit page') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
