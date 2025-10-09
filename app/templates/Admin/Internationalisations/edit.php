<?php
$this->assign('title', __('Edit internationalisation'));
?>
<div class="internationalisation form content">
    <?= $this->Form->create($internationalisation ?? null) ?>
    <fieldset>
        <legend><?= __('Edit internationalisation') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
