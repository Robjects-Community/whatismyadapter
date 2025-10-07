<?php
/**
 * AI Metric View Template
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
$this->assign('title', __('AI Metric'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('AI Metric') ?></h3>
            <div class="actions">
                <?= $this->Html->link(__('Back'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiMetric->id], ['class' => 'btn btn-warning']) ?>
                <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiMetric->id], ['confirm' => __('Are you sure?'), 'class' => 'btn btn-danger']) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5><?= __('Metric Details') ?></h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th><?= __('ID') ?></th>
                        <td><?= h($aiMetric->id) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Task Type') ?></th>
                        <td><?= h($aiMetric->task_type) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Model Used') ?></th>
                        <td><?= h($aiMetric->model_used) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Execution Time') ?></th>
                        <td><?= h($aiMetric->execution_time_ms) ?> ms</td>
                    </tr>
                    <tr>
                        <th><?= __('Tokens Used') ?></th>
                        <td><?= $this->Number->format($aiMetric->tokens_used) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Cost (USD)') ?></th>
                        <td>$<?= number_format($aiMetric->cost_usd, 6) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Success') ?></th>
                        <td>
                            <?php if ($aiMetric->success): ?>
                                <span class="badge badge-success"><?= __('Success') ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger"><?= __('Failed') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><?= __('Created') ?></th>
                        <td><?= h($aiMetric->created) ?></td>
                    </tr>
                    <tr>
                        <th><?= __('Modified') ?></th>
                        <td><?= h($aiMetric->modified) ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <?php if (!empty($aiMetric->error_message)): ?>
        <div class="card mt-3">
            <div class="card-header bg-danger text-white">
                <h5><?= __('Error Message') ?></h5>
            </div>
            <div class="card-body">
                <pre><?= h($aiMetric->error_message) ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
