<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Internationalisation $internationalisation
 */
?>
<?php if (!$internationalisation->isNew()): ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Internationalisation',
        'controllerName' => 'Internationalisations',
        'entity' => $internationalisation,
        'entityDisplayName' => $internationalisation->message_id
    ]);
?>
<?php endif; ?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Internationalisation') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($internationalisation, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                        <div class="mb-3">
                            <?php echo $this->Form->control('locale', ['class' => 'form-control' . ($this->Form->isFieldError('locale') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('locale')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('locale') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <?php echo $this->Form->control('message_id', ['class' => 'form-control' . ($this->Form->isFieldError('message_id') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('message_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('message_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <?php echo $this->Form->control('message_str', ['class' => 'form-control' . ($this->Form->isFieldError('message_str') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('message_str')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('message_str') ?>
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