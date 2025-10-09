<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Aiprompt $aiprompt
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Aiprompt',
        'controllerName' => 'Aiprompts',
        'entity' => $aiprompt,
        'entityDisplayName' => $aiprompt->task_type
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($aiprompt->task_type) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Task Type') ?></th>
                            <td><?= h($aiprompt->task_type) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Model') ?></th>
                            <td><?= h($aiprompt->model) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Max Tokens') ?></th>
                            <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Temperature') ?></th>
                            <td><?= $this->Number->format($aiprompt->temperature) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created At') ?></th>
                            <td><?= h($aiprompt->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified At') ?></th>
                            <td><?= h($aiprompt->modified) ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('System Prompt') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($aiprompt->system_prompt)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>