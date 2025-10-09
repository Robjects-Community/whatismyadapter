<?php
/**
 * AI Metrics Index Template
 * 
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AiMetric> $aiMetrics
 */
$this->assign('title', __('AI Metrics'));
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= __('AI Metrics') ?></h3>
            <div class="actions">
                <?= $this->Html->link(
                    __('Dashboard'),
                    ['action' => 'dashboard'],
                    ['class' => 'btn btn-primary']
                ) ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?= $this->Paginator->sort('task_type', __('Task Type')) ?></th>
                                <th><?= $this->Paginator->sort('execution_time_ms', __('Execution Time')) ?></th>
                                <th><?= $this->Paginator->sort('tokens_used', __('Tokens')) ?></th>
                                <th><?= $this->Paginator->sort('cost_usd', __('Cost')) ?></th>
                                <th><?= $this->Paginator->sort('success', __('Success')) ?></th>
                                <th><?= $this->Paginator->sort('model_used', __('Model')) ?></th>
                                <th><?= $this->Paginator->sort('created', __('Created')) ?></th>
                                <th class="actions"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aiMetrics as $aiMetric): ?>
                            <tr>
                                <td><?= h($aiMetric->task_type) ?></td>
                                <td><?= h($aiMetric->execution_time_ms) ?> ms</td>
                                <td><?= $this->Number->format($aiMetric->tokens_used) ?></td>
                                <td>$<?= number_format($aiMetric->cost_usd, 4) ?></td>
                                <td>
                                    <?php if ($aiMetric->success): ?>
                                        <span class="badge badge-success"><?= __('Success') ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-danger"><?= __('Failed') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($aiMetric->model_used) ?></td>
                                <td><?= h($aiMetric->created) ?></td>
                                <td class="actions">
                                    <?= $this->Html->link(__('View'), ['action' => 'view', $aiMetric->id], ['class' => 'btn btn-sm btn-info']) ?>
                                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiMetric->id], ['class' => 'btn btn-sm btn-warning']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiMetric->id], ['confirm' => __('Are you sure?'), 'class' => 'btn btn-sm btn-danger']) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="paginator">
                    <ul class="pagination">
                        <?= $this->Paginator->first('<< ' . __('first')) ?>
                        <?= $this->Paginator->prev('< ' . __('previous')) ?>
                        <?= $this->Paginator->numbers() ?>
                        <?= $this->Paginator->next(__('next') . ' >') ?>
                        <?= $this->Paginator->last(__('last') . ' >>') ?>
                    </ul>
                    <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
