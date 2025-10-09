<?php
/**
 * Admin AiMetrics Index Template
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AiMetric> $aiMetrics
 */
$this->assign('title', __('AI Metrics'));
?>
<div class="ai-metrics index content">
    <h3><?= __('AI Metrics') ?></h3>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= __('Id') ?></th>
                    <th><?= __('Task Type') ?></th>
                    <th><?= __('Created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (isset($aiMetrics)): ?>
                    <?php foreach ($aiMetrics as $aiMetric): ?>
                    <tr>
                        <td><?= h($aiMetric->id ?? '') ?></td>
                        <td><?= h($aiMetric->task_type ?? '') ?></td>
                        <td><?= h($aiMetric->created ?? '') ?></td>
                        <td class="actions">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $aiMetric->id ?? '']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiMetric->id ?? '']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
