<?php
$this->assign('title', __('Add internationalisation'));
?>
<div class="internationalisation form content">
    <?= $this->Form->create($internationalisation ?? null) ?>
    <fieldset>
        <legend><?= __('Add internationalisation') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
