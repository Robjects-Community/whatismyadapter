<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Ai Metric',
    'controllerName' => 'Ai Metrics',
    'entity' => $aiMetric,
    'entityDisplayName' => $aiMetric->task_type
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($aiMetric->task_type) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
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
                            <th><?= __('Execution Time Ms') ?></th>
                            <td><?= $aiMetric->execution_time_ms === null ? '' : $this->Number->format($aiMetric->execution_time_ms) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Tokens Used') ?></th>
                            <td><?= $aiMetric->tokens_used === null ? '' : $this->Number->format($aiMetric->tokens_used) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Cost Usd') ?></th>
                            <td><?= $aiMetric->cost_usd === null ? '' : $this->Number->format($aiMetric->cost_usd) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($aiMetric->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($aiMetric->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Success') ?></th>
                            <td><?= $aiMetric->success ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Error Message') ?></h5>
                            <p class="card-text"><?= html_entity_decode($aiMetric->error_message); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>