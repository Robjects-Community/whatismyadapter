<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Internationalisation $internationalisation
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Internationalisation',
        'controllerName' => 'Internationalisations',
        'entity' => $internationalisation,
        'entityDisplayName' => $internationalisation->message_id
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($internationalisation->message_id) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Locale') ?></th>
                            <td><?= h($internationalisation->locale) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created At') ?></th>
                            <td><?= h($internationalisation->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Updated At') ?></th>
                            <td><?= h($internationalisation->modified) ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Message Id') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($internationalisation->message_id)); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Message Str') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($internationalisation->message_str)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>