<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 * @var array $formSettings
 */
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Header Section -->
            <div class="text-center mb-4">
                <h1 class="display-6 text-primary">
                    <i class="fas fa-plus-circle me-2"></i>
                    Submit a New Product
                </h1>
                <p class="lead text-muted">
                    Share your product with our community and help others find the right adapters and connectivity solutions.
                </p>
            </div>

            <!-- Information Notice -->
            <div class="alert alert-info border-0 shadow-sm mb-4" role="alert">
                <h6 class="alert-heading">
                    <i class="fas fa-info-circle me-2"></i>
                    Before You Submit
                </h6>
                <p class="mb-2">Your product submission will be reviewed by our team before being published. This helps ensure quality and accuracy for all users.</p>
                <small class="text-muted">
                    <i class="fas fa-clock me-1"></i>
                    Review typically takes 1-2 business days.
                </small>
            </div>

            <!-- Main Form Card -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <?= $this->Form->create($product, [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation',
                        'novalidate' => true,
                        'id' => 'product-submission-form'
                    ]) ?>
                    
                    <!-- Basic Information Section -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-primary border-bottom pb-2 mb-4">
                            <i class="fas fa-edit me-2"></i>
                            Basic Information
                        </legend>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <?= $this->Form->control('title', [
                                    'label' => 'Product Name *',
                                    'placeholder' => 'e.g., USB-C to HDMI Adapter',
                                    'class' => 'form-control form-control-lg' . ($this->Form->isFieldError('title') ? ' is-invalid' : ''),
                                    'required' => true
                                ]) ?>
                                <div class="form-text">
                                    <i class="fas fa-lightbulb text-warning me-1"></i>
                                    Use a clear, descriptive name that includes the product type and key features.
                                </div>
                                <?php if ($this->Form->isFieldError('title')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('title') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <?= $this->Form->control('manufacturer', [
                                    'label' => 'Manufacturer',
                                    'placeholder' => 'e.g., Anker, Belkin',
                                    'class' => 'form-control' . ($this->Form->isFieldError('manufacturer') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('manufacturer')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('manufacturer') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('model_number', [
                                    'label' => 'Model Number',
                                    'placeholder' => 'e.g., A8306, PowerConnect 2000',
                                    'class' => 'form-control' . ($this->Form->isFieldError('model_number') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('model_number')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('model_number') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <?= $this->Form->control('price', [
                                    'label' => 'Price',
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'placeholder' => '29.99',
                                    'class' => 'form-control' . ($this->Form->isFieldError('price') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('price')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('price') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <?= $this->Form->control('currency', [
                                    'label' => 'Currency',
                                    'options' => [
                                        'USD' => 'USD ($)',
                                        'EUR' => 'EUR (€)',
                                        'GBP' => 'GBP (£)',
                                        'CAD' => 'CAD ($)',
                                        'AUD' => 'AUD ($)'
                                    ],
                                    'default' => 'USD',
                                    'class' => 'form-select' . ($this->Form->isFieldError('currency') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('currency')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('currency') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <?= $this->Form->control('description', [
                                'label' => 'Product Description *',
                                'type' => 'textarea',
                                'rows' => 4,
                                'placeholder' => 'Describe what this product does, its key features, compatibility, and any other important details...',
                                'class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : ''),
                                'required' => true
                            ]) ?>
                            <div class="form-text">
                                <i class="fas fa-info-circle text-info me-1"></i>
                                Include key features, compatibility information, and what makes this product useful.
                            </div>
                            <?php if ($this->Form->isFieldError('description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </fieldset>

                    <!-- Media Section -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-primary border-bottom pb-2 mb-4">
                            <i class="fas fa-image me-2"></i>
                            Product Images
                        </legend>
                        
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <?= $this->Form->control('image_uploads', [
                                    'type' => 'file',
                                    'label' => 'Product Photo',
                                    'accept' => 'image/*',
                                    'class' => 'form-control' . ($this->Form->isFieldError('image_uploads') ? ' is-invalid' : '')
                                ]) ?>
                                <div class="form-text">
                                    <i class="fas fa-camera text-primary me-1"></i>
                                    Upload a clear photo of your product. Supported formats: JPG, PNG, WebP (max 5MB)
                                </div>
                                <?php if ($this->Form->isFieldError('image_uploads')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('image_uploads') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <?= $this->Form->control('alt_text', [
                                    'label' => 'Image Description',
                                    'placeholder' => 'Brief description of the image',
                                    'class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : '')
                                ]) ?>
                                <div class="form-text">
                                    <i class="fas fa-universal-access text-success me-1"></i>
                                    Helps with accessibility
                                </div>
                                <?php if ($this->Form->isFieldError('alt_text')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('alt_text') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Categories Section -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-primary border-bottom pb-2 mb-4">
                            <i class="fas fa-tags me-2"></i>
                            Categories & Tags
                        </legend>
                        
                        <div class="mb-3">
                            <?= $this->Form->control('tags._ids', [
                                'label' => 'Product Categories',
                                'options' => $tags,
                                'multiple' => true,
                                'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : ''),
                                'size' => 6,
                                'data-placeholder' => 'Select relevant categories for your product...'
                            ]) ?>
                            <div class="form-text">
                                <i class="fas fa-search text-info me-1"></i>
                                Select categories that best describe your product to help users find it.
                            </div>
                            <?php if ($this->Form->isFieldError('tags._ids')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('tags._ids') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </fieldset>

                    <!-- Technical Details Section -->
                    <fieldset class="mb-4">
                        <legend class="h5 text-primary border-bottom pb-2 mb-4">
                            <i class="fas fa-cog me-2"></i>
                            Technical Details <small class="text-muted">(Optional)</small>
                        </legend>
                        
                        <div class="alert alert-light border-0" role="alert">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Adding technical specifications helps improve the reliability score and usefulness of your product listing.
                            </small>
                        </div>
                        
                        <div class="mb-3">
                            <?= $this->Form->control('technical_specifications', [
                                'label' => 'Technical Specifications',
                                'type' => 'textarea',
                                'rows' => 3,
                                'placeholder' => 'e.g., Connector types, data transfer rates, power requirements, compatibility standards...',
                                'class' => 'form-control' . ($this->Form->isFieldError('technical_specifications') ? ' is-invalid' : '')
                            ]) ?>
                            <?php if ($this->Form->isFieldError('technical_specifications')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('technical_specifications') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('testing_standard', [
                                    'label' => 'Testing Standard',
                                    'placeholder' => 'e.g., IEEE 802.3, USB-IF Certified',
                                    'class' => 'form-control' . ($this->Form->isFieldError('testing_standard') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('testing_standard')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('testing_standard') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('certifying_organization', [
                                    'label' => 'Certifying Organization',
                                    'placeholder' => 'e.g., FCC, CE, RoHS',
                                    'class' => 'form-control' . ($this->Form->isFieldError('certifying_organization') ? ' is-invalid' : '')
                                ]) ?>
                                <?php if ($this->Form->isFieldError('certifying_organization')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('certifying_organization') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </fieldset>

                    <!-- Reliability Notice -->
                    <div class="alert alert-primary border-0 mb-4" role="alert">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-shield-alt fa-2x text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h6 class="alert-heading mb-2">Reliability Scoring</h6>
                                <p class="mb-1">Your submission will automatically receive a reliability score based on the completeness and quality of the information provided.</p>
                                <small class="text-muted">More complete information results in higher reliability scores and better visibility in search results.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Section -->
                    <div class="d-grid gap-2 d-md-flex justify-content-md-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-lock me-1"></i>
                                Your submission will be reviewed before publication.
                            </small>
                        </div>
                        <div class="d-grid gap-2 d-md-flex">
                            <?= $this->Html->link(
                                '<i class="fas fa-arrow-left me-2"></i>Cancel',
                                ['action' => 'index'],
                                [
                                    'class' => 'btn btn-outline-secondary',
                                    'escape' => false
                                ]
                            ) ?>
                            <?= $this->Form->button(
                                '<i class="fas fa-paper-plane me-2"></i>Submit for Review',
                                [
                                    'type' => 'submit',
                                    'class' => 'btn btn-primary btn-lg px-4',
                                    'escape' => false,
                                    'id' => 'submit-button'
                                ]
                            ) ?>
                        </div>
                    </div>
                    
                    <?= $this->Form->end() ?>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="mt-4">
                <div class="card border-0 bg-light">
                    <div class="card-body text-center py-3">
                        <small class="text-muted">
                            <i class="fas fa-question-circle me-1"></i>
                            Need help? Contact us at 
                            <a href="mailto:support@adaptercms.com" class="text-decoration-none">support@adaptercms.com</a>
                            or check our 
                            <a href="#" class="text-decoration-none">submission guidelines</a>.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-control:focus, .form-select:focus {
    border-color: var(--bs-primary);
    box-shadow: 0 0 0 0.2rem rgba(var(--bs-primary-rgb), 0.25);
}

.card {
    transition: all 0.2s ease-in-out;
}

.alert {
    border-radius: 0.5rem;
}

legend {
    font-weight: 600;
}

.form-text {
    font-size: 0.875rem;
}

#submit-button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('product-submission-form');
    const submitButton = document.getElementById('submit-button');
    
    if (form && submitButton) {
        form.addEventListener('submit', function(e) {
            // Show loading state
            const originalContent = submitButton.innerHTML;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            submitButton.disabled = true;
            
            // Re-enable after 5 seconds as failsafe
            setTimeout(function() {
                submitButton.innerHTML = originalContent;
                submitButton.disabled = false;
            }, 5000);
        });
    }
    
    // Auto-generate alt text from title when image is selected
    const titleField = document.getElementById('title');
    const altTextField = document.getElementById('alt-text');
    const imageField = document.getElementById('image-uploads');
    
    if (titleField && altTextField && imageField) {
        imageField.addEventListener('change', function() {
            if (this.files.length > 0 && titleField.value && !altTextField.value) {
                altTextField.value = titleField.value + ' product image';
            }
        });
    }
});
</script>
