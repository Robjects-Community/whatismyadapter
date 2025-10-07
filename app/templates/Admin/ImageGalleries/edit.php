<?php
$this->assign('title', __('Edit imageGallery'));
?>
<div class="imageGallery form content">
    <?= $this->Form->create($imageGallery ?? null) ?>
    <fieldset>
        <legend><?= __('Edit imageGallery') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
