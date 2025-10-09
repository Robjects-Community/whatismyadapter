<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\AiMetric $aiMetric
 */
?>
<?php
// Only show actions if we have an entity (edit mode)
if (!$aiMetric->isNew()) {
    echo $this->element('actions_card', [
        'modelName' => 'Ai Metric',
        'controllerName' => 'Ai Metrics',
        'entity' => $aiMetric,
        'entityDisplayName' => $aiMetric->task_type
    ]);
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Ai Metric') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($aiMetric,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('task_type', ['class' => 'form-control' . ($this->Form->isFieldError('task_type') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('task_type')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('task_type') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('execution_time_ms', ['class' => 'form-control' . ($this->Form->isFieldError('execution_time_ms') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('execution_time_ms')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('execution_time_ms') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('tokens_used', ['class' => 'form-control' . ($this->Form->isFieldError('tokens_used') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('tokens_used')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('tokens_used') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('cost_usd', ['class' => 'form-control' . ($this->Form->isFieldError('cost_usd') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('cost_usd')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('cost_usd') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('success', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('success') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="success">
                                    <?= __('Success') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('success')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('success') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('error_message', ['class' => 'form-control' . ($this->Form->isFieldError('error_message') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('error_message')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('error_message') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('model_used', ['class' => 'form-control' . ($this->Form->isFieldError('model_used') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('model_used')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('model_used') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('created')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('created') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('modified')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('modified') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                                                                                    
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>