<?php
$this->assign('title', __('Internationalisations'));
?>
<div class="internationalisations index content">
    <h3><?= __('Internationalisations') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Id') ?></th>
                    <th><?= __('Title/Name') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($internationalisations)): ?>
                    <?php foreach ($internationalisations as $internationalisation): ?>
                    <tr>
                        <td><?= h($internationalisation->id ?? '') ?></td>
                        <td><?= h($internationalisation->title ?? $internationalisation->name ?? '') ?></td>
                        <td><?= $this->Html->link(__('View'), ['action' => 'view', $internationalisation->id ?? '']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
