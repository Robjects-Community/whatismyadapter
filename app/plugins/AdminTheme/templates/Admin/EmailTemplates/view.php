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
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($emailTemplate->name) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Template Identifier') ?></th>
                            <td><?= h($emailTemplate->template_identifier) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($emailTemplate->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Subject') ?></th>
                            <td><?= h($emailTemplate->subject) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($emailTemplate->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($emailTemplate->modified) ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body Html') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($emailTemplate->body_html)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body Plain') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($emailTemplate->body_plain)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>