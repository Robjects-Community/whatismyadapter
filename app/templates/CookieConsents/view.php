<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CookieConsent $cookieConsent
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Cookie Consent'), ['action' => 'edit', $cookieConsent->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Cookie Consent'), ['action' => 'delete', $cookieConsent->id], ['confirm' => __('Are you sure you want to delete # {0}?', $cookieConsent->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Cookie Consents'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Cookie Consent'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="cookieConsents view content">
            <h3><?= h($cookieConsent->ip_address) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($cookieConsent->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $cookieConsent->hasValue('user') ? $this->Html->link($cookieConsent->user->username, ['controller' => 'Users', 'action' => 'view', $cookieConsent->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Session Id') ?></th>
                    <td><?= h($cookieConsent->session_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ip Address') ?></th>
                    <td><?= h($cookieConsent->ip_address) ?></td>
                </tr>
                <tr>
                    <th><?= __('User Agent') ?></th>
                    <td><?= h($cookieConsent->user_agent) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($cookieConsent->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Analytics Consent') ?></th>
                    <td><?= $cookieConsent->analytics_consent ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Functional Consent') ?></th>
                    <td><?= $cookieConsent->functional_consent ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Marketing Consent') ?></th>
                    <td><?= $cookieConsent->marketing_consent ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Essential Consent') ?></th>
                    <td><?= $cookieConsent->essential_consent ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>