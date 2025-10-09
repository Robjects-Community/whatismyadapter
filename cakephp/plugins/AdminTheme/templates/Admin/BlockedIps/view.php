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
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($blockedIp->ip_address) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Ip Address') ?></th>
                            <td><?= h($blockedIp->ip_address) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Blocked At') ?></th>
                            <td><?= h($blockedIp->blocked_at) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Expires At') ?></th>
                            <td><?= h($blockedIp->expires_at) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($blockedIp->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($blockedIp->modified) ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Reason') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($blockedIp->reason)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>