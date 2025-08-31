<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 */
?>
<?php if (!$slug->isNew()): ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Slug',
        'controllerName' => 'Slugs',
        'entity' => $slug,
        'entityDisplayName' => $slug->slug
    ]);
?>
<?php endif; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Slug') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($slug, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('model', ['class' => 'form-control' . ($this->Form->isFieldError('model') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('model')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('model') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('foreign_key', ['class' => 'form-control' . ($this->Form->isFieldError('foreign_key') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('foreign_key')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('foreign_key') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('slug', ['class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('slug')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('slug') ?>
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