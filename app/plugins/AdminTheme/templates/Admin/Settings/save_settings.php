<?php
/**
 * Admin Settings Save Template
 * 
 * This template is shown when accessing the save-settings route directly
 * (though typically this would be a POST-only action that redirects)
 */

$this->assign('title', __('Save Settings'));
?>

<div class="settings-save">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-cog fa-3x mb-3 text-muted"></i>
                        <h2><?= __('Settings Management') ?></h2>
                        <p class="lead text-muted mb-4">
                            <?= __('To save settings, please use the settings form.') ?>
                        </p>
                        <div class="actions">
                            <?= $this->Html->link(
                                __('Go to Settings'),
                                ['action' => 'index'],
                                ['class' => 'btn btn-primary btn-lg']
                            ) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
