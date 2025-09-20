<?php use App\Utility\SettingsManager; ?>
<div class="col-md-6 container mt-4 mb-3">
    <div class="row">
        <?= $this->Flash->render() ?>
        <?= $this->Form->create(null, ['url' => ['_name' => 'reset-password', $confirmationCode], 'class' => 'needs-validation', 'novalidate' => true]) ?>
        <h1 class="h3 mb-3 fw-normal text-center"><?= __('Reset Your Password') ?></h1>

        <fieldset>

            <div class="mb-3">
                <?php echo $this->Form->control('password',
                    [
                        'value' => '',
                        'class' => 'form-control' . ($this->Form->isFieldError('password') ? ' is-invalid' : '')
                    ]); ?>
                <?php if ($this->Form->isFieldError('password')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('password') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('confirm_password',
                    [
                        'value' => '',
                        'type' => 'password',
                        'class' => 'form-control' . ($this->Form->isFieldError('confirm_password') ? ' is-invalid' : '')
                    ]); ?>
                <?php if ($this->Form->isFieldError('confirm_password')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('confirm_password') ?>
                    </div>
                <?php endif; ?>
            </div>

        </fieldset>
        <div class="form-group">
            <?= $this->Form->button(__('Reset Password'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>