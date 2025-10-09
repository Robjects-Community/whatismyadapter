<?php
$this->assign('title', __('Edit homepageFeed'));
?>
<div class="homepageFeed form content">
    <?= $this->Form->create($homepageFeed ?? null) ?>
    <fieldset>
        <legend><?= __('Edit homepageFeed') ?></legend>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
