<div class="col-md-6 container mt-4 mb-3">
    <div class="row">
        <?= $this->Flash->render() ?>
        <?= $this->Form->create(
            $user,
            [
            'type' => 'file',
            'enctype' => 'multipart/form-data',
            'class' => 'needs-validation', 'novalidate' => true,
            ],
        ) ?>
        <h1 class="h3 mb-3 fw-normal text-center"><?= __('Create an Account') ?></h1>

        <fieldset>

            <div class="mb-3">
                <?php echo $this->Form->control('email', ['class' => 'form-control' . ($this->Form->isFieldError('email') ? ' is-invalid' : '')]); ?>
                <?php if ($this->Form->isFieldError('email')) : ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('email') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control(
                    'password',
                    [
                        'value' => '',
                        'class' => 'form-control' . ($this->Form->isFieldError('password') ? ' is-invalid' : ''),
                    ],
                ); ?>
                <?php if ($this->Form->isFieldError('password')) : ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('password') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control(
                    'confirm_password',
                    [
                        'value' => '',
                        'type' => 'password',
                        'class' => 'form-control' . ($this->Form->isFieldError('confirm_password') ? ' is-invalid' : ''),
                    ],
                ); ?>
                <?php if ($this->Form->isFieldError('confirm_password')) : ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('confirm_password') ?>
                    </div>
                <?php endif; ?>
            </div>

        </fieldset>
        <div class="form-group">
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?= $this->Form->end() ?>
        <p class="mt-3 text-center">
            <?= $this->Html->link('Already have an account? Login', ['action' => 'login'], ['class' => 'text-primary']) ?>
        </p>
    </div>
</div>