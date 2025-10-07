<?php
/**
 * Admin AiMetrics Edit Template
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
$this->assign('title', __('Edit AI Metric'));
?>
<div class="ai-metrics form content">
    <?= $this->Form->create($aiMetric ?? null) ?>
    <fieldset>
        <legend><?= __('Edit AI Metric') ?></legend>
        <?php
            echo $this->Form->control('task_type');
            echo $this->Form->control('execution_time_ms');
            echo $this->Form->control('tokens_used');
            echo $this->Form->control('cost_usd');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
