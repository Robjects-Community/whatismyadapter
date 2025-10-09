<?php
/**
 * Product Add Template
 * 
 * Allows logged-in users to submit products for review
 */

$this->assign('title', __('Submit a Product'));
$this->Html->meta('description', __('Submit your product for review and inclusion in our directory'), ['block' => 'meta']);
?>

<div class="products-add">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="page-header text-center mb-5">
                    <h1 class="page-title"><?= __('Submit a Product') ?></h1>
                    <p class="page-description">
                        <?= __('Share your adapter or product with our community. All submissions are reviewed before publication.') ?>
                    </p>
                </div>

                <?php if (!$this->Identity->isLoggedIn()): ?>
                    <!-- Login Required -->
                    <div class="alert alert-info text-center">
                        <h5><i class="fas fa-info-circle"></i> <?= __('Login Required') ?></h5>
                        <p><?= __('You must be logged in to submit products.') ?></p>
                        <div class="mt-3">
                            <?= $this->Html->link(
                                __('Login'),
                                ['controller' => 'Users', 'action' => 'login'],
                                ['class' => 'btn btn-primary']
                            ) ?>
                            <?= $this->Html->link(
                                __('Register'),
                                ['controller' => 'Users', 'action' => 'register'],
                                ['class' => 'btn btn-outline-primary']
                            ) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Submission Form -->
                    <div class="submission-form">
                        <?= $this->Form->create($product, [
                            'class' => 'product-form',
                            'type' => 'file'
                        ]) ?>

                        <div class="form-sections">
                            <!-- Basic Information -->
                            <div class="form-section">
                                <div class="section-header">
                                    <h4><i class="fas fa-info-circle"></i> <?= __('Basic Information') ?></h4>
                                    <p class="text-muted"><?= __('Essential details about your product') ?></p>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-12">
                                        <?= $this->Form->control('title', [
                                            'label' => [
                                                'text' => __('Product Name') . ($formSettings['title_required'] ? ' *' : ''),
                                                'class' => 'form-label'
                                            ],
                                            'class' => 'form-control form-control-lg',
                                            'placeholder' => __('Enter the product name'),
                                            'required' => $formSettings['title_required'] ?? true
                                        ]) ?>
                                    </div>

                                    <?php if ($formSettings['excerpt_enabled'] ?? true): ?>
                                    <div class="col-md-12">
                                        <?= $this->Form->control('excerpt', [
                                            'label' => [
                                                'text' => __('Short Description') . ($formSettings['excerpt_required'] ? ' *' : ''),
                                                'class' => 'form-label'
                                            ],
                                            'type' => 'textarea',
                                            'class' => 'form-control',
                                            'rows' => 3,
                                            'placeholder' => __('Brief description of your product (max 200 characters)'),
                                            'maxlength' => 200,
                                            'required' => $formSettings['excerpt_required'] ?? false
                                        ]) ?>
                                        <div class="form-text"><?= __('This will be displayed in product listings') ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Terms and Submission -->
                            <div class="form-section">
                                <div class="section-header">
                                    <h4><i class="fas fa-check-circle"></i> <?= __('Review and Submit') ?></h4>
                                </div>

                                <div class="submission-terms">
                                    <div class="alert alert-info">
                                        <h6><i class="fas fa-info-circle"></i> <?= __('Submission Guidelines:') ?></h6>
                                        <ul class="mb-0">
                                            <li><?= __('All submissions are manually reviewed before publication') ?></li>
                                            <li><?= __('Reviews typically take 1-3 business days') ?></li>
                                            <li><?= __('Products must be relevant to adapters or related technology') ?></li>
                                            <li><?= __('Spam or inappropriate content will be rejected') ?></li>
                                            <li><?= __('You will be notified via email when your submission is reviewed') ?></li>
                                        </ul>
                                    </div>

                                    <div class="form-check mt-3">
                                        <?= $this->Form->checkbox('agree_terms', [
                                            'class' => 'form-check-input',
                                            'required' => true
                                        ]) ?>
                                        <label class="form-check-label" for="agree-terms">
                                            <?= __('I agree to the submission guidelines and confirm that I have the right to submit this product') ?> *
                                        </label>
                                    </div>
                                </div>

                                <div class="form-actions mt-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?= $this->Html->link(
                                                __('Cancel'),
                                                ['action' => 'index'],
                                                ['class' => 'btn btn-outline-secondary btn-lg w-100']
                                            ) ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?= $this->Form->submit(__('Submit for Review'), [
                                                'class' => 'btn btn-primary btn-lg w-100'
                                            ]) ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?= $this->Form->end() ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.submission-form {
    max-width: 800px;
    margin: 0 auto;
}

.form-section {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

.section-header {
    border-bottom: 2px solid #f8f9fa;
    padding-bottom: 15px;
    margin-bottom: 25px;
}

.section-header h4 {
    color: #2c3e50;
    margin-bottom: 5px;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 12px 15px;
    transition: all 0.2s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.form-check {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.form-actions .btn {
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 8px;
}
</style>
