<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\BlockedIp $blockedIp
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Blocked Ip',
        'controllerName' => 'Blocked Ips',
        'entity' => $blockedIp,
        'entityDisplayName' => $blockedIp->ip_address
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Blocked Ip') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($blockedIp, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('ip_address', ['class' => 'form-control' . ($this->Form->isFieldError('ip_address') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('ip_address')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('ip_address') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('reason', ['class' => 'form-control' . ($this->Form->isFieldError('reason') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('reason')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('reason') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('blocked_at', ['class' => 'form-control' . ($this->Form->isFieldError('blocked_at') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('blocked_at')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('blocked_at') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('expires_at', ['class' => 'form-control' . ($this->Form->isFieldError('expires_at') ? ' is-invalid' : '')]); ?>
                                                                                        <?php if ($this->Form->isFieldError('expires_at')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('expires_at') ?>
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