<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CookieConsent> $cookieConsents
 */
?>
<div class="cookieConsents index content">
    <?= $this->Html->link(__('New Cookie Consent'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Cookie Consents') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('session_id') ?></th>
                    <th><?= $this->Paginator->sort('analytics_consent') ?></th>
                    <th><?= $this->Paginator->sort('functional_consent') ?></th>
                    <th><?= $this->Paginator->sort('marketing_consent') ?></th>
                    <th><?= $this->Paginator->sort('essential_consent') ?></th>
                    <th><?= $this->Paginator->sort('ip_address') ?></th>
                    <th><?= $this->Paginator->sort('user_agent') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cookieConsents as $cookieConsent): ?>
                <tr>
                    <td><?= h($cookieConsent->id) ?></td>
                    <td><?= $cookieConsent->hasValue('user') ? $this->Html->link($cookieConsent->user->username, ['controller' => 'Users', 'action' => 'view', $cookieConsent->user->id]) : '' ?></td>
                    <td><?= h($cookieConsent->session_id) ?></td>
                    <td><?= h($cookieConsent->analytics_consent) ?></td>
                    <td><?= h($cookieConsent->functional_consent) ?></td>
                    <td><?= h($cookieConsent->marketing_consent) ?></td>
                    <td><?= h($cookieConsent->essential_consent) ?></td>
                    <td><?= h($cookieConsent->ip_address) ?></td>
                    <td><?= h($cookieConsent->user_agent) ?></td>
                    <td><?= h($cookieConsent->created) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $cookieConsent->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $cookieConsent->id]) ?>
                        <?= $this->Form->postLink(
                            __('Delete'),
                            ['action' => 'delete', $cookieConsent->id],
                            [
                                'method' => 'delete',
                                'confirm' => __('Are you sure you want to delete # {0}?', $cookieConsent->id),
                            ]
                        ) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>