<?php
/**
 * @var \App\View\AppView $this
 * @var array $errors
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-link-45deg me-2"></i>
                <?= __('Link Existing URL/Domain') ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Path Selection'),
                ['action' => 'choosePath'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Introduction -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle link-existing me-3">
                        <i class="bi bi-link-45deg"></i>
                    </div>
                    <div>
                        <h2 class="h5 mb-1"><?= __('Connect to Existing Infrastructure') ?></h2>
                        <p class="text-muted mb-0"><?= __('Validate and analyze your current domain setup') ?></p>
                    </div>
                </div>
                <p class="mb-0">
                    <?= __('This process will help you connect Willow CMS to your existing website or domain. We\'ll verify connectivity, analyze your current infrastructure costs, and provide recommendations for optimization.') ?>
                </p>
            </div>
        </div>

        <!-- Link Form -->
        <?= $this->Form->create(null, [
            'class' => 'needs-validation',
            'novalidate' => true,
            'id' => 'link-existing-form'
        ]) ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-globe me-2"></i>
                    <?= __('Domain Information') ?>
                </h3>
            </div>
            <div class="card-body">
                <!-- Domain URL -->
                <div class="mb-4">
                    <?= $this->Form->control('domain_url', [
                        'type' => 'text',
                        'label' => [
                            'text' => __('Domain URL'),
                            'class' => 'form-label fw-semibold'
                        ],
                        'class' => 'form-control form-control-lg' . (isset($errors['domain_url']) ? ' is-invalid' : ''),
                        'placeholder' => __('https://example.com or example.com'),
                        'required' => true,
                        'data-validation-url' => true
                    ]) ?>
                    <?php if (isset($errors['domain_url'])): ?>
                        <div class="invalid-feedback">
                            <?= implode('<br>', $errors['domain_url']) ?>
                        </div>
                    <?php endif; ?>
                    <div class="form-text">
                        <?= __('Enter the full URL or domain name of your existing website or application.') ?>
                    </div>
                </div>

                <!-- Verification Method -->
                <div class="mb-4">
                    <label class="form-label fw-semibold"><?= __('Verification Method') ?></label>
                    <?php if (isset($errors['verification_method'])): ?>
                        <div class="text-danger small mb-2">
                            <?= implode('<br>', $errors['verification_method']) ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="verification-methods">
                        <!-- File Verification -->
                        <div class="verification-method-option">
                            <div class="form-check">
                                <?= $this->Form->radio('verification_method', [
                                    ['value' => 'file', 'text' => '', 'class' => 'form-check-input']
                                ], [
                                    'default' => 'file'
                                ]) ?>
                                <label class="form-check-label" for="verification-method-file">
                                    <div class="method-header">
                                        <i class="bi bi-file-text text-primary me-2"></i>
                                        <strong><?= __('File Upload Verification') ?></strong>
                                        <span class="badge bg-success ms-2"><?= __('Recommended') ?></span>
                                    </div>
                                    <p class="method-description">
                                        <?= __('Upload a verification file to your domain root directory. This is the most reliable method.') ?>
                                    </p>
                                </label>
                            </div>
                        </div>

                        <!-- Meta Tag Verification -->
                        <div class="verification-method-option">
                            <div class="form-check">
                                <?= $this->Form->radio('verification_method', [
                                    ['value' => 'meta', 'text' => '', 'class' => 'form-check-input']
                                ]) ?>
                                <label class="form-check-label" for="verification-method-meta">
                                    <div class="method-header">
                                        <i class="bi bi-code-slash text-info me-2"></i>
                                        <strong><?= __('Meta Tag Verification') ?></strong>
                                    </div>
                                    <p class="method-description">
                                        <?= __('Add a meta tag to your website\'s HTML head section. Easy if you have access to the HTML.') ?>
                                    </p>
                                </label>
                            </div>
                        </div>

                        <!-- DNS Verification -->
                        <div class="verification-method-option">
                            <div class="form-check">
                                <?= $this->Form->radio('verification_method', [
                                    ['value' => 'dns', 'text' => '', 'class' => 'form-check-input']
                                ]) ?>
                                <label class="form-check-label" for="verification-method-dns">
                                    <div class="method-header">
                                        <i class="bi bi-hdd-network text-warning me-2"></i>
                                        <strong><?= __('DNS Record Verification') ?></strong>
                                        <span class="badge bg-secondary ms-2"><?= __('Coming Soon') ?></span>
                                    </div>
                                    <p class="method-description">
                                        <?= __('Add a TXT record to your domain\'s DNS settings. Requires DNS management access.') ?>
                                    </p>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="mb-4">
                    <label class="form-label fw-semibold"><?= __('Additional Notes (Optional)') ?></label>
                    <?= $this->Form->textarea('notes', [
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => __('Any additional information about your current setup, hosting provider, or specific requirements...')
                    ]) ?>
                </div>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        <small>
                            <i class="bi bi-shield-check me-1"></i>
                            <?= __('Your domain information is processed securely and not stored permanently.') ?>
                        </small>
                    </div>
                    <div>
                        <?= $this->Form->submit(__('Connect & Verify Domain'), [
                            'class' => 'btn btn-primary btn-lg px-4',
                            'id' => 'submit-btn'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <?= $this->Form->end() ?>

        <!-- Information Panel -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 bg-light">
                    <div class="card-body">
                        <h4 class="card-title">
                            <i class="bi bi-info-circle me-2 text-primary"></i>
                            <?= __('What Happens Next?') ?>
                        </h4>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="step-item">
                                    <div class="step-number">1</div>
                                    <h6><?= __('Connectivity Check') ?></h6>
                                    <p class="small text-muted"><?= __('We verify that your domain is accessible and responding.') ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="step-item">
                                    <div class="step-number">2</div>
                                    <h6><?= __('Verification Process') ?></h6>
                                    <p class="small text-muted"><?= __('Follow the verification steps based on your selected method.') ?></p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="step-item">
                                    <div class="step-number">3</div>
                                    <h6><?= __('Analysis & Recommendations') ?></h6>
                                    <p class="small text-muted"><?= __('Receive cost analysis and optimization recommendations.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 1.25rem;
    color: white;
    flex-shrink: 0;
}

.icon-circle.link-existing {
    background: linear-gradient(135deg, #4f84ff 0%, #2196f3 100%);
}

.verification-methods {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    overflow: hidden;
}

.verification-method-option {
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
    transition: background-color 0.2s ease;
}

.verification-method-option:last-child {
    border-bottom: none;
}

.verification-method-option:hover {
    background-color: #f8f9fa;
}

.method-header {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.method-description {
    margin-bottom: 0;
    font-size: 0.9rem;
    color: #6c757d;
    margin-left: 1.5rem;
}

.form-check-input:checked ~ .form-check-label .verification-method-option {
    background-color: #e3f2fd;
    border-color: #2196f3;
}

.step-item {
    text-align: center;
    padding: 1rem;
}

.step-number {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background-color: #2196f3;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    margin: 0 auto 0.5rem;
}

/* Loading state */
.btn.loading {
    pointer-events: none;
    position: relative;
}

.btn.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: button-loading-spinner 1s ease infinite;
}

@keyframes button-loading-spinner {
    from {
        transform: rotate(0turn);
    }
    to {
        transform: rotate(1turn);
    }
}
</style>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('link-existing-form');
    const submitBtn = document.getElementById('submit-btn');
    const domainInput = document.querySelector('input[name="domain_url"]');
    
    // Form validation
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        } else {
            // Add loading state
            submitBtn.classList.add('loading');
            submitBtn.textContent = '<?= __('Connecting...') ?>';
        }
        
        form.classList.add('was-validated');
    });
    
    // Real-time URL validation
    domainInput.addEventListener('input', function() {
        const value = this.value.trim();
        if (value) {
            // Simple URL validation
            const urlPattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
            if (!urlPattern.test(value)) {
                this.setCustomValidity('<?= __('Please enter a valid URL or domain name') ?>');
            } else {
                this.setCustomValidity('');
            }
        }
    });
    
    // Handle verification method selection
    const verificationMethods = document.querySelectorAll('input[name="verification_method"]');
    verificationMethods.forEach(method => {
        method.addEventListener('change', function() {
            // Remove previous selections
            document.querySelectorAll('.verification-method-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Highlight selected method
            this.closest('.verification-method-option').classList.add('selected');
        });
    });
    
    // Set initial selection
    const defaultMethod = document.querySelector('input[name="verification_method"]:checked');
    if (defaultMethod) {
        defaultMethod.closest('.verification-method-option').classList.add('selected');
    }
});
<?php $this->Html->scriptEnd(); ?>