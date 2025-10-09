<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AiMetric> $aiMetrics
 */
?>
<?php if (empty($aiMetrics)): ?>
    <?= $this->element('empty_state', [
        'type' => 'search',
        'title' => __('No Ai Metrics found'),
        'message' => __('Try adjusting your search terms or filters.')
    ]) ?>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                            <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('task_type') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('execution_time_ms') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('tokens_used') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('cost_usd') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('success') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('error_message') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('model_used') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                            <th scope="col"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($aiMetrics as $aiMetric): ?>
      <tr>
                                <td><?= h($aiMetric->id) ?></td>
                                        <td><?= h($aiMetric->task_type) ?></td>
                                        <td><?= $aiMetric->execution_time_ms === null ? '' : $this->Number->format($aiMetric->execution_time_ms) ?></td>
                                        <td><?= $aiMetric->tokens_used === null ? '' : $this->Number->format($aiMetric->tokens_used) ?></td>
                                        <td><?= $aiMetric->cost_usd === null ? '' : $this->Number->format($aiMetric->cost_usd) ?></td>
                                        <td><?= h($aiMetric->success) ?></td>
                                        <td><?= h($aiMetric->error_message) ?></td>
                                        <td><?= h($aiMetric->model_used) ?></td>
                                        <td><?= h($aiMetric->created) ?></td>
                                        <td><?= h($aiMetric->modified) ?></td>
                          <td>
            <?= $this->element('evd_dropdown', ['model' => $aiMetric, 'display' => 'task_type']); ?>
          </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
<?php endif; ?>

