<?php
$this->assign('title', __('Bulk Upload Images'));
?>
<div class="images form content">
    <?= $this->Form->create(null, ['type' => 'file']) ?>
    <fieldset>
        <legend><?= __('Bulk Upload Images') ?></legend>
        <?php
            echo $this->Form->control('images[]', ['type' => 'file', 'multiple' => true]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Upload')) ?>
    <?= $this->Form->end() ?>
</div>
