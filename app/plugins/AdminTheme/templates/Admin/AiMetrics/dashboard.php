<?php
/**
 * AI Metrics Dashboard Template
 * 
 * @var \App\View\AppView $this
 * @var int $totalCalls
 * @var float $successRate
 * @var float $totalCost
 * @var array $taskMetrics
 * @var array $recentErrors
 * @var array $currentUsage
 */
$this->assign('title', __('AI Metrics Dashboard'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('AI Metrics Dashboard') ?></h3>
            <p class="text-muted"><?= __('Last 30 days overview') ?></p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total API Calls') ?></h5>
                <h2 class="text-primary" id="metric-total-calls"><?= number_format($totalCalls) ?></h2>
                <small class="text-muted"><?= __('Last 30 days') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Success Rate') ?></h5>
                <h2 class="<?= $successRate >= 95 ? 'text-success' : ($successRate >= 85 ? 'text-warning' : 'text-danger') ?>" id="metric-success-rate">
                    <?= number_format($successRate, 1) ?>%
                </h2>
                <small class="text-muted"><?= __('API Success Rate') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total Cost') ?></h5>
                <h2 class="text-info" id="metric-total-cost">$<?= number_format($totalCost, 2) ?></h2>
                <small class="text-muted"><?= __('Last 30 days') ?></small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Rate Limit') ?></h5>
                <h2 class="<?= $currentUsage['remaining'] > 10 ? 'text-success' : 'text-warning' ?>" id="metric-rate-limit">
                    <?= $currentUsage['current'] ?>/<?= $currentUsage['limit'] ?>
                </h2>
                <small class="text-muted"><?= __('This hour') ?></small>
            </div>
        </div>
    </div>
</div>

<!-- Task Metrics Table -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Metrics by Task Type') ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-striped" id="task-metrics-table">
                    <thead>
                        <tr>
                            <th><?= __('Task Type') ?></th>
                            <th><?= __('Count') ?></th>
                            <th><?= __('Avg Time (ms)') ?></th>
                            <th><?= __('Success Rate') ?></th>
                            <th><?= __('Total Cost') ?></th>
                            <th><?= __('Total Tokens') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($taskMetrics)): ?>
                            <?php foreach ($taskMetrics as $metric): ?>
                            <tr>
                                <td><?= h($metric->task_type) ?></td>
                                <td><?= number_format($metric->count) ?></td>
                                <td><?= number_format($metric->avg_time, 0) ?></td>
                                <td>
                                    <span class="badge <?= $metric->success_rate >= 95 ? 'badge-success' : ($metric->success_rate >= 85 ? 'badge-warning' : 'badge-danger') ?>">
                                        <?= number_format($metric->success_rate, 1) ?>%
                                    </span>
                                </td>
                                <td>$<?= number_format($metric->total_cost, 2) ?></td>
                                <td><?= number_format($metric->total_tokens) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted"><?= __('No metrics data available') ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Recent Errors -->
<?php if (!empty($recentErrors)): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Recent Errors') ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th><?= __('Date') ?></th>
                            <th><?= __('Task Type') ?></th>
                            <th><?= __('Error Message') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentErrors as $error): ?>
                        <tr>
                            <td><?= $error->created->format('M j, Y H:i') ?></td>
                            <td><?= h($error->task_type) ?></td>
                            <td><?= h($error->error_message) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
