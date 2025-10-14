<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<?php if (!$aiprompt->isNew()): ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Aiprompt',
        'controllerName' => 'Aiprompts',
        'entity' => $aiprompt,
        'entityDisplayName' => $aiprompt->task_type
    ]);
?>
<?php endif; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Aiprompt') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($aiprompt, ['class' => 'needs-validation', 'novalidate' => true]) ?>
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
                            <?php echo $this->Form->control('system_prompt', ['class' => 'form-control' . ($this->Form->isFieldError('system_prompt') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('system_prompt')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('system_prompt') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('model', ['class' => 'form-control' . ($this->Form->isFieldError('model') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('model')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('model') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('max_tokens', ['class' => 'form-control' . ($this->Form->isFieldError('max_tokens') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('max_tokens')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('max_tokens') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('temperature', ['class' => 'form-control' . ($this->Form->isFieldError('temperature') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('temperature')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('temperature') ?>
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