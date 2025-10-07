<?php
/**
 * Admin Aiprompts Index Template
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
$this->assign('title', __('Aiprompts'));
?>
<div class="aiprompts index content">
    <h3><?= __('Aiprompts') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Id') ?></th>
                    <th><?= __('Title') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($aiprompts)): ?>
                    <?php foreach ($aiprompts as $Aiprompt): ?>
                    <tr>
                        <td><?= h($Aiprompt->id ?? '') ?></td>
                        <td><?= h($Aiprompt->title ?? $Aiprompt->name ?? '') ?></td>
                        <td><?= h($Aiprompt->created ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $Aiprompt->id ?? '']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $Aiprompt->id ?? '']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
