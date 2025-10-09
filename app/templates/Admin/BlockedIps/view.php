<?php
/**
 * Admin BlockedIps View Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $BlockedIp
 */
$this->assign('title', __('BlockedIp Details'));
?>
<div class="BlockedIp view content">
    <h3><?= __('BlockedIp') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($BlockedIp->id ?? '') ?></td>
        </tr>
        <tr>
            <th><?= __('Title') ?></th>
            <td><?= h($BlockedIp->title ?? $BlockedIp->name ?? '') ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($BlockedIp->created ?? '') ?></td>
        </tr>
    </table>
</div>
