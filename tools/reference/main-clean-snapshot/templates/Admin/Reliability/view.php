<?php
/**
 * @var \App\View\AppView $this
 * @var string $model
 * @var string $id
 * @var \Cake\Datasource\EntityInterface $entity
 * @var \App\Model\Entity\ProductsReliability|null $reliabilitySummary
 * @var \Cake\Collection\CollectionInterface $fieldsData
 * @var \Cake\ORM\Query $logs
 */

$entityDisplayName = match ($model) {
    'Products' => $entity->title ?? 'Product',
    default => ucfirst($model) . ' ' . $id
};

// Calculate color and badge style based on score
$scoreColor = 'secondary';
$scorePercentage = 0;
if ($reliabilitySummary) {
    $score = $reliabilitySummary->total_score ?? 0;
    $scorePercentage = round($score * 100, 1);
    $scoreColor = match (true) {
        $score >= 0.9 => 'success',
        $score >= 0.7 => 'warning',
        default => 'danger'
    };
}
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line me-2"></i>
                <?= __('Reliability Details: {name}', ['name' => h($entityDisplayName)]) ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-arrow-left"></i> ' . __('Back to {model}', ['model' => $model]),
                ['controller' => $model, 'action' => 'index'],
                ['class' => 'btn btn-secondary me-2', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Summary Card -->
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        <?= __('Reliability Summary') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($reliabilitySummary): ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="me-3">
                                        <span class="badge bg-<?= $scoreColor ?> fs-5 px-3 py-2">
                                            <?= $this->Number->toPercentage($scorePercentage, 1) ?>
                                        </span>
                                    </div>
                                    <div>
                                        <strong><?= __('Overall Reliability Score') ?></strong><br>
                                        <small class="text-muted">
                                            <?= $this->Number->precision($reliabilitySummary->total_score, 3) ?>/1.00 
                                            (<?= __('Version: {version}', ['version' => h($reliabilitySummary->scoring_version)]) ?>)
                                        </small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <strong><?= __('Completeness:') ?></strong>
                                    <span class="badge bg-info ms-2">
                                        <?= $this->Number->toPercentage($reliabilitySummary->completeness_percent, 1) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <strong><?= __('Last Calculated:') ?></strong>
                                    <?= $reliabilitySummary->last_calculated ? 
                                        $this->Time->timeAgoInWords($reliabilitySummary->last_calculated) : 
                                        '<em>' . __('Never') . '</em>' ?>
                                </div>
                                <div class="mb-2">
                                    <strong><?= __('Last Source:') ?></strong>
                                    <span class="badge bg-secondary"><?= h($reliabilitySummary->last_source) ?></span>
                                </div>
                                <?php if ($reliabilitySummary->updated_by_user_id): ?>
                                    <div class="mb-2">
                                        <strong><?= __('Updated By User:') ?></strong>
                                        <?= h($reliabilitySummary->updated_by_user_id) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($reliabilitySummary->updated_by_service): ?>
                                    <div class="mb-2">
                                        <strong><?= __('Updated By Service:') ?></strong>
                                        <code><?= h($reliabilitySummary->updated_by_service) ?></code>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="border-top pt-3 mt-3">
                            <?= $this->Form->postLink(
                                '<i class="fas fa-sync-alt"></i> ' . __('Recalculate Scores'),
                                ['action' => 'recalc', $model, $id],
                                [
                                    'class' => 'btn btn-primary me-2',
                                    'escape' => false,
                                    'confirm' => __('Are you sure you want to recalculate reliability scores for this {model}?', ['model' => strtolower($model)])
                                ]
                            ) ?>
                            <?= $this->Form->postLink(
                                '<i class="fas fa-shield-alt"></i> ' . __('Verify Checksums'),
                                ['action' => 'verifyChecksums', $model, $id],
                                [
                                    'class' => 'btn btn-outline-secondary',
                                    'escape' => false,
                                    'confirm' => __('This will verify the integrity of all reliability logs for this {model}. Continue?', ['model' => strtolower($model)])
                                ]
                            ) ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= __('No reliability data found for this {model}. Use the recalculate button to generate initial scores.', ['model' => strtolower($model)]) ?>
                        </div>
                        <?= $this->Form->postLink(
                            '<i class="fas fa-sync-alt"></i> ' . __('Generate Initial Scores'),
                            ['action' => 'recalc', $model, $id],
                            [
                                'class' => 'btn btn-primary',
                                'escape' => false,
                                'confirm' => __('This will generate initial reliability scores for this {model}. Continue?', ['model' => strtolower($model)])
                            ]
                        ) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Field-by-Field Breakdown -->
        <?php if ($reliabilitySummary && !$fieldsData->isEmpty()): ?>
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-list-ul me-2"></i>
                        <?= __('Field-Level Breakdown') ?>
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?= __('Field') ?></th>
                                    <th><?= __('Score') ?></th>
                                    <th><?= __('Weight') ?></th>
                                    <th><?= __('Contribution') ?></th>
                                    <th><?= __('Max Possible') ?></th>
                                    <th><?= __('Notes') ?></th>
                                    <th><?= __('Last Updated') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fieldsData as $field): ?>
                                <tr>
                                    <td><code><?= h($field->field) ?></code></td>
                                    <td>
                                        <span class="badge bg-<?= $field->score >= 0.8 ? 'success' : ($field->score >= 0.5 ? 'warning' : 'danger') ?>">
                                            <?= $this->Number->precision($field->score, 2) ?>
                                        </span>
                                    </td>
                                    <td><?= $this->Number->precision($field->weight, 3) ?></td>
                                    <td><?= $this->Number->precision($field->score * $field->weight, 3) ?></td>
                                    <td><?= $this->Number->precision($field->max_score, 2) ?></td>
                                    <td>
                                        <?php if (!empty($field->notes)): ?>
                                            <small class="text-muted"><?= h($field->notes) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= $this->Time->timeAgoInWords($field->modified) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- History/Logs -->
        <?php if ($reliabilitySummary): ?>
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        <?= __('Update History') ?>
                        <small class="text-muted">(<?= __('Recent 20 entries') ?>)</small>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (!$logs->isEmpty()): ?>
                        <div class="timeline">
                            <?php foreach ($logs as $log): ?>
                            <div class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-start">
                                            <div class="timeline-marker me-3 mt-1">
                                                <i class="fas fa-circle text-<?= 
                                                    match($log->source) {
                                                        'system' => 'info',
                                                        'admin' => 'warning',
                                                        'ai' => 'success',
                                                        'user' => 'primary',
                                                        default => 'secondary'
                                                    }
                                                ?>"></i>
                                            </div>
                                            <div class="timeline-content flex-grow-1">
                                                <div class="mb-2">
                                                    <strong><?= __('Score changed from {from} to {to}', [
                                                        'from' => $log->from_total_score !== null ? $this->Number->precision($log->from_total_score, 3) : 'N/A',
                                                        'to' => $this->Number->precision($log->to_total_score, 3)
                                                    ]) ?></strong>
                                                </div>
                                                <?php if (!empty($log->message)): ?>
                                                    <p class="text-muted mb-2"><?= h($log->message) ?></p>
                                                <?php endif; ?>
                                                <div class="small text-muted">
                                                    <span class="badge bg-<?= 
                                                        match($log->source) {
                                                            'system' => 'info',
                                                            'admin' => 'warning',
                                                            'ai' => 'success',
                                                            'user' => 'primary',
                                                            default => 'secondary'
                                                        }
                                                    ?>"><?= h($log->source) ?></span>
                                                    
                                                    <?php if ($log->actor_user_id): ?>
                                                        • <?= __('User: {id}', ['id' => h($log->actor_user_id)]) ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($log->actor_service): ?>
                                                        • <?= __('Service: {service}', ['service' => h($log->actor_service)]) ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <div class="small text-muted">
                                            <?= $this->Time->format($log->created, 'yyyy-MM-dd HH:mm:ss') ?><br>
                                            <code class="small"><?= substr($log->checksum_sha256, 0, 8) ?>...</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= __('No update history available yet.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.timeline-marker {
    width: 20px;
    text-align: center;
}

.timeline-item:last-child {
    border-bottom: none !important;
}

.badge {
    font-size: 0.75em;
}

.fs-5 {
    font-size: 1.25rem;
}
</style>
