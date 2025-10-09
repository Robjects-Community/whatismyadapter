<?php
$this->assign('title', __('Add homepageFeed'));
?>
<div class="homepageFeed form content">
    <?= $this->Form->create($homepageFeed ?? null) ?>
    <fieldset>
        <legend><?= __('Add homepageFeed') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
