<?php
/**
 * Admin AiMetrics View Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
$this->assign('title', __('AI Metric Details'));
?>
<div class="ai-metrics view content">
    <h3><?= __('AI Metric') ?></h3>
    <table class="table">
        <tr>
            <th><?= __('Id') ?></th>
            <td><?= h($aiMetric->id ?? '') ?></td>
        </tr>
        <tr>
            <th><?= __('Task Type') ?></th>
            <td><?= h($aiMetric->task_type ?? '') ?></td>
        </tr>
        <tr>
            <th><?= __('Created') ?></th>
            <td><?= h($aiMetric->created ?? '') ?></td>
        </tr>
    </table>
</div>
