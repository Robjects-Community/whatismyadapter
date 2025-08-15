<?php ?>
<div class="col-md-6 container mt-4 mb-3">
    <div class="row">
        <?= $this->Flash->render() ?>
        <?= $this->Form->create(null, ['url' => ['_name' => 'forgot-password'], 'class' => 'needs-validation', 'novalidate' => true]) ?>
        <h1 class="h3 mb-3 fw-normal text-center"><?= __('Reset your password') ?></h1>

        <fieldset>

            <div class="mb-3">
                <?php echo $this->Form->control('email', ['class' => 'form-control' . ($this->Form->isFieldError('email') ? ' is-invalid' : '')]); ?>
                <?php if ($this->Form->isFieldError('email')) : ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('email') ?>
                    </div>
                <?php endif; ?>
            </div>

        </fieldset>
        <div class="form-group">
            <?= $this->Form->button(__('Send Reset Link'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= $this->Form->end() ?>
        <div class="text-center">
            <?= $this->Html->link(__('Back to Login'), ['action' => 'login'], ['class' => 'text-muted']) ?>
        </div>
    </div>
</div>