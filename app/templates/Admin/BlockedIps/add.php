<?php
/**
 * Admin BlockedIps Add Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $BlockedIp
 */
$this->assign('title', __('Add BlockedIp'));
?>
<div class="BlockedIp form content">
    <?= $this->Form->create($BlockedIp ?? null) ?>
    <fieldset>
        <legend><?= __('Add BlockedIp') ?></legend>
        <?php
            echo $this->Form->control('title');
            echo $this->Form->control('description', ['type' => 'textarea']);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
