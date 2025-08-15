<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\CookieConsent $cookieConsent
 */
?>
<?php if (!$this->request->is('ajax')) :?>
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title"><?= __('Cookie Preferences') ?></h5>
            </div>
            <div class="card-body">
<?php endif; ?>

                <?= $this->Form->create($cookieConsent, [
                    'class' => 'needs-validation',
                    'novalidate' => true,
                ]) ?>
                <fieldset>
                    <p class="mb-4"><?= __('Please select your cookie preferences below. Essential cookies are required for the website to function and cannot be disabled.') ?></p>
                    <?php if (!empty($sitePrivacyPolicy)) : ?>
                    <p>
                        <?= __(
                            'To understand how we handle your personal information, including the use of cookies and other tracking technologies, please review our {0}.',
                            $this->Html->link(
                                __('Privacy Policy'),
                                [
                                    '_name' => 'page-by-slug',
                                    'slug' => $sitePrivacyPolicy['slug'],
                                ],
                            ),
                        ); ?>
                    </p>
                    <?php endif; ?>
                    <div class="mb-3">
                        <div class="form-check">
                            <?= $this->Form->checkbox('essential_consent', [
                                'class' => 'form-check-input' . ($this->Form->isFieldError('essential_consent') ? ' is-invalid' : ''),
                                'disabled' => true,
                                'checked' => true,
                            ]) ?>
                            <label class="form-check-label" for="essential-consent">
                                <?= __('Essential Cookies') ?>
                            </label>
                            <div class="form-text">
                                <?= __('Required for the website to function properly. These cannot be disabled.') ?>
                            </div>
                            <?php if ($this->Form->isFieldError('essential_consent')) : ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('essential_consent') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <?= $this->Form->checkbox('functional_consent', [
                                'class' => 'form-check-input' . ($this->Form->isFieldError('functional_consent') ? ' is-invalid' : ''),
                            ]) ?>
                            <label class="form-check-label" for="functional-consent">
                                <?= __('Functional Cookies') ?>
                            </label>
                            <div class="form-text">
                                <?= __('Enable enhanced functionality and personalization.') ?>
                            </div>
                            <?php if ($this->Form->isFieldError('functional_consent')) : ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('functional_consent') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <?= $this->Form->checkbox('analytics_consent', [
                                'class' => 'form-check-input' . ($this->Form->isFieldError('analytics_consent') ? ' is-invalid' : ''),
                            ]) ?>
                            <label class="form-check-label" for="analytics-consent">
                                <?= __('Analytics Cookies') ?>
                            </label>
                            <div class="form-text">
                                <?= __('Help us understand how visitors interact with our website.') ?>
                            </div>
                            <?php if ($this->Form->isFieldError('analytics_consent')) : ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('analytics_consent') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <?= $this->Form->checkbox('marketing_consent', [
                                'class' => 'form-check-input' . ($this->Form->isFieldError('marketing_consent') ? ' is-invalid' : ''),
                            ]) ?>
                            <label class="form-check-label" for="marketing-consent">
                                <?= __('Marketing Cookies') ?>
                            </label>
                            <div class="form-text">
                                <?= __('Used to deliver personalized advertisements and enable the facebook share button.') ?>
                            </div>
                            <?php if ($this->Form->isFieldError('marketing_consent')) : ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('marketing_consent') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <?= $this->Form->button(__('Essential'), [
                        'class' => 'btn btn-secondary my-1 me-1',
                        'name' => 'consent_type',
                        'value' => 'essential',
                        'data-consent-type' => 'essential',
                        ]) ?>
                    <?= $this->Form->button(__('Selected'), [
                        'class' => 'btn btn-secondary my-1 me-1',
                        'name' => 'consent_type',
                        'value' => 'selected',
                        'data-consent-type' => 'selected',
                        ]) ?>
                    <?= $this->Form->button(__('All'), [
                        'class' => 'btn btn-primary my-1 me-1',
                        'name' => 'consent_type',
                        'value' => 'all',
                        'data-consent-type' => 'all',
                        ]) ?>
                </div>
                <?= $this->Form->end() ?>
                
<?php if (!$this->request->is('ajax')) :?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>