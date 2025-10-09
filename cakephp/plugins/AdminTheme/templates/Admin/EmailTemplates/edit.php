<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Email Template',
        'controllerName' => 'Email Templates',
        'entity' => $emailTemplate,
        'entityDisplayName' => $emailTemplate->name,
        'debugOnlyOptions' => ['delete', 'add'],
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Email Template') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($emailTemplate, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>

                        <div class="mb-3">
                            <?php echo $this->Form->control('template_identifier', ['class' => 'form-control' . ($this->Form->isFieldError('template_identifier') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('template_identifier')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('template_identifier') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php echo $this->Form->control('name', ['class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php echo $this->Form->control('subject', ['class' => 'form-control' . ($this->Form->isFieldError('subject') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('subject')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('subject') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php echo $this->Form->control('body_html', ['class' => 'form-control' . ($this->Form->isFieldError('body_html') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('body_html')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('body_html') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <?php echo $this->Form->control('body_plain', ['class' => 'form-control' . ($this->Form->isFieldError('body_plain') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('body_plain')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('body_plain') ?>
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