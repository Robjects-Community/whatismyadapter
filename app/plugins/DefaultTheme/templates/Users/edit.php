<?php use App\Utility\SettingsManager; ?>
<div class="col-md-6 container mt-4 mb-3">
    <div class="row">
        <?= $this->Flash->render() ?>
        <?= $this->Form->create($user,
        [
            'type' => 'file',
            'enctype' => 'multipart/form-data',
            'class' => 'needs-validation', 'novalidate' => true
        ]) ?>
        <h1 class="h3 mb-3 fw-normal text-center"><?= __('Edit your Account') ?></h1>

        <fieldset>

            <div class="mb-3">
                <?php echo $this->Form->control('email', ['class' => 'form-control' . ($this->Form->isFieldError('email') ? ' is-invalid' : '')]); ?>
                <?php if ($this->Form->isFieldError('email')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('email') ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <?php echo $this->Form->control('username', ['class' => 'form-control' . ($this->Form->isFieldError('username') ? ' is-invalid' : '')]); ?>
                <?php if ($this->Form->isFieldError('username')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('username') ?>
                    </div>
                <?php endif; ?>
            </div>

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

            <?php if (!empty($user->image)): ?>
                <div class="mb-3">
                    <?= $this->element('image/icon', ['model' => $user, 'icon' => $user->teenyImageUrl, 'preview' => $user->extraLargeImageUrl]); ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <?= $this->Form->control('image', [
                    'type' => 'file',
                    'label' => [
                        'text' => __('Image'),
                        'class' => 'form-label'
                    ],
                    'class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : ''),
                    'id' => 'customFile'
                ]) ?>
                <?php if ($this->Form->isFieldError('image')): ?>
                    <div class="invalid-feedback">
                        <?= $this->Form->error('image') ?>
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
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>