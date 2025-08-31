<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\EmailTemplate $emailTemplate
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Email Template',
        'controllerName' => 'EmailTemplates',
        'debugOnlyOptions' => ['add']
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Send Email') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create(null, ['url' => ['action' => 'sendEmail']]) ?>
                    <fieldset>

                    <div class="mb-3">
                    <?= $this->Form->control('email_template_id', [
                        'options' => $emailTemplates,
                        'empty' => 'Select an email template',
                        'class' => 'form-control',
                        'label' => ['class' => 'mb-2', 'text' => 'Email Template']
                    ]) ?>
                    </div>

                    <div class="mb-3">
                    <?= $this->Form->control('user_id', [
                        'options' => $users,
                        'empty' => 'Select a user',
                        'class' => 'form-control',
                        'label' => ['class' => 'mb-2', 'text' => 'Recipient']
                    ]) ?>
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