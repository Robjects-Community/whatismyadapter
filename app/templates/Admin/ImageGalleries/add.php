<?php
$this->assign('title', __('Add imageGallery'));
?>
<div class="imageGallery form content">
    <?= $this->Form->create($imageGallery ?? null) ?>
    <fieldset>
        <legend><?= __('Add imageGallery') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
