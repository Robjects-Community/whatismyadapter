<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'User',
        'controllerName' => 'Users',
        'entity' => $user,
        'entityDisplayName' => $user->username
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit User') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($user,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
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
                            <?php echo $this->Form->control('password', [
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
                            <?php echo $this->Form->control('confirm_password', [
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
                                'accept' => 'image/png,image/jpeg,image/gif',
                                'class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : ''),
                                'id' => 'customFile'
                            ]) ?>
                            <?php if ($this->Form->isFieldError('image')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('image') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('is_admin', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('is_admin') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-admin">
                                    <?= __('Admin') ?>
                                </label>
                                <?php if ($this->Form->isFieldError('is_admin')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('is_admin') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('active', [
                                    'checked' => true,
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('active') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="active">
                                    <?= __('Active') ?>
                                </label>
                                <?php if ($this->Form->isFieldError('active')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('active') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
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