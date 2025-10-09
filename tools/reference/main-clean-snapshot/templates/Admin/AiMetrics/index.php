<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\AiMetric> $aiMetrics
 */

// Load search utility scripts
$this->Html->script('AdminTheme.utils/search-handler', ['block' => true]);
$this->Html->script('AdminTheme.utils/popover-manager', ['block' => true]); 
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- Status Filter -->
            <?= $this->element('status_filter', [
                'filters' => [
                    'all' => ['label' => __('All'), 'params' => []],
                    'filter1' => ['label' => __('Filter 1'), 'params' => ['status' => '0']],
                    'filter2' => ['label' => __('Filter 2'), 'params' => ['status' => '1']],
                ]
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'id' => 'aiMetric-search-form',
                'inputId' => 'aiMetricSearch',
                'placeholder' => __('Search Ai Metrics...'),
                'class' => 'd-flex me-3 flex-grow-1'
            ]) ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Ai Metric'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
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
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
// Initialize search functionality using AdminTheme utility
AdminTheme.SearchHandler.init({
    searchInputId: 'aiMetricSearch',
    resultsContainerId: '#ajax-target',
    baseUrl: '<?= $this->Url->build(['action' => 'index']) ?>',
    debounceDelay: 300
});
<?php $this->Html->scriptEnd(); ?>

