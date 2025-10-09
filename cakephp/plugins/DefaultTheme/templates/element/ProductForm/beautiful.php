<?php
/**
 * Beautiful Product Form Element
 * 
 * @var \App\View\AppView $this
 * @var int $pageId The current page ID (optional)
 * @var bool $aiScoring Enable AI scoring (default: true)
 * @var bool $requireApproval Require admin approval (default: true)
 * @var bool $showProgress Show progress steps (default: true)
 */

// Set defaults
$pageId = $pageId ?? 0;
$aiScoring = $aiScoring ?? true;
$requireApproval = $requireApproval ?? true;
$showProgress = $showProgress ?? true;

// Create a new empty product entity for the form
$product = $this->Form->getConfig('context')['entity'] ?? null;
if (!$product) {
    $productsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Products');
    $product = $productsTable->newEmptyEntity();
}

// Get form options (cached for performance)
$cacheKey = 'product_form_options';
$formOptions = \Cake\Cache\Cache::read($cacheKey);
if (!$formOptions) {
    $productsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Products');
    $formOptions = [
        'tags' => $productsTable->Tags->find('list', ['limit' => 200])->all()->toArray()
    ];
    \Cake\Cache\Cache::write($cacheKey, $formOptions, 'default');
}
?>

<div class="beautiful-product-form-element" data-page-id="<?= h($pageId) ?>">
    <?php if ($showProgress): ?>
    <!-- Progress Steps -->
    <div class="form-progress mb-4">
        <div class="progress-container">
            <div class="progress-step active" data-step="1">
                <div class="step-circle">1</div>
                <span class="step-label">Product Info</span>
            </div>
            <div class="progress-step" data-step="2">
                <div class="step-circle">2</div>
                <span class="step-label">Details</span>
            </div>
            <div class="progress-step" data-step="3">
                <div class="step-circle">3</div>
                <span class="step-label">Images</span>
            </div>
            <?php if ($aiScoring): ?>
            <div class="progress-step" data-step="4">
                <div class="step-circle">4</div>
                <span class="step-label">AI Score</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Product Form -->
    <?= $this->Form->create($product, [
        'url' => ['controller' => 'Products', 'action' => 'submit'],
        'type' => 'file',
        'class' => 'beautiful-product-form',
        'data-ai-scoring' => $aiScoring ? 'true' : 'false',
        'data-require-approval' => $requireApproval ? 'true' : 'false'
    ]) ?>

    <?php if ($pageId): ?>
        <?= $this->Form->hidden('source_page_id', ['value' => $pageId]) ?>
    <?php endif; ?>

    <div class="form-sections">
        <!-- Section 1: Basic Information -->
        <div class="form-section active" data-section="1">
            <h3 class="section-title">
                <i class="fas fa-info-circle"></i>
                Tell us about your product
            </h3>
            
            <div class="form-grid">
                <div class="form-group col-span-2">
                    <label for="product-title" class="form-label required">
                        Product Name
                    </label>
                    <?= $this->Form->control('title', [
                        'label' => false,
                        'class' => 'form-input',
                        'placeholder' => 'What\'s your product called?',
                        'required' => true,
                        'data-ai-field' => 'title'
                    ]) ?>
                </div>

                <div class="form-group">
                    <label for="manufacturer" class="form-label">
                        Brand <span class="optional">(Optional)</span>
                    </label>
                    <?= $this->Form->control('manufacturer', [
                        'label' => false,
                        'class' => 'form-input',
                        'placeholder' => 'e.g. Apple, Dell, etc.',
                        'data-ai-field' => 'manufacturer'
                    ]) ?>
                </div>

                <div class="form-group col-span-3">
                    <label for="description" class="form-label">
                        Description <span class="optional">(Optional)</span>
                    </label>
                    <?= $this->Form->control('description', [
                        'type' => 'textarea',
                        'label' => false,
                        'class' => 'form-input',
                        'placeholder' => 'What makes your product special? What problems does it solve?',
                        'rows' => 4,
                        'data-ai-field' => 'description'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Section 2: Details -->
        <div class="form-section" data-section="2">
            <h3 class="section-title">
                <i class="fas fa-cog"></i>
                Product Details
            </h3>
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="price" class="form-label">
                        Price <span class="optional">(Optional)</span>
                    </label>
                    <div class="input-group">
                        <?= $this->Form->control('currency', [
                            'type' => 'select',
                            'options' => [
                                'USD' => '$',
                                'EUR' => '€',
                                'GBP' => '£',
                                'JPY' => '¥'
                            ],
                            'default' => 'USD',
                            'class' => 'form-select',
                            'label' => false
                        ]) ?>
                        <?= $this->Form->control('price', [
                            'class' => 'form-input',
                            'label' => false,
                            'placeholder' => '0.00',
                            'type' => 'number',
                            'step' => '0.01',
                            'data-ai-field' => 'price'
                        ]) ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="model_number" class="form-label">
                        Model/SKU <span class="optional">(Optional)</span>
                    </label>
                    <?= $this->Form->control('model_number', [
                        'label' => false,
                        'class' => 'form-input',
                        'placeholder' => 'Model number',
                        'data-ai-field' => 'model_number'
                    ]) ?>
                </div>

                <div class="form-group col-span-2">
                    <label for="tags" class="form-label">
                        Categories <span class="optional">(Optional)</span>
                    </label>
                    <?= $this->Form->control('tags._ids', [
                        'type' => 'select',
                        'multiple' => true,
                        'options' => $formOptions['tags'],
                        'class' => 'form-select',
                        'label' => false,
                        'data-ai-field' => 'tags'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Section 3: Images -->
        <div class="form-section" data-section="3">
            <h3 class="section-title">
                <i class="fas fa-images"></i>
                Product Images
            </h3>
            
            <div class="form-grid">
                <div class="form-group col-span-3">
                    <label class="form-label">
                        Product Image <span class="optional">(Optional)</span>
                    </label>
                    <div class="image-upload-zone" id="image-upload">
                        <div class="upload-content">
                            <i class="fas fa-cloud-upload-alt upload-icon"></i>
                            <h4>Drop your image here</h4>
                            <p>or click to browse</p>
                            <small>Supports JPG, PNG, WebP up to 5MB</small>
                        </div>
                        <?= $this->Form->file('image', [
                            'class' => 'upload-input',
                            'accept' => 'image/*',
                            'id' => 'image-file'
                        ]) ?>
                    </div>
                </div>

                <div class="form-group col-span-3">
                    <label for="alt_text" class="form-label">
                        Image Description <span class="optional">(Optional)</span>
                    </label>
                    <?= $this->Form->control('alt_text', [
                        'label' => false,
                        'class' => 'form-input',
                        'placeholder' => 'Describe your image for accessibility',
                        'data-ai-field' => 'alt_text'
                    ]) ?>
                </div>
            </div>
        </div>

        <?php if ($aiScoring): ?>
        <!-- AI Scoring Section -->
        <div class="ai-scoring-section" id="ai-score-display" style="display: none;">
            <div class="score-card">
                <h3 class="score-title">
                    <i class="fas fa-robot"></i>
                    AI Product Analysis
                </h3>
                <div class="score-content">
                    <div class="score-number" id="score-value">--</div>
                    <div class="score-feedback" id="score-feedback">
                        Complete the form to get AI-powered recommendations!
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Form Navigation -->
    <div class="form-navigation">
        <button type="button" class="btn btn-secondary" id="prev-step" style="display: none;">
            <i class="fas fa-arrow-left"></i>
            Previous
        </button>
        
        <button type="button" class="btn btn-primary" id="next-step">
            Next
            <i class="fas fa-arrow-right"></i>
        </button>
        
        <button type="submit" class="btn btn-success" id="submit-form" style="display: none;">
            <i class="fas fa-paper-plane"></i>
            Submit Product
        </button>
    </div>

    <?= $this->Form->end() ?>
</div>

<style>
.beautiful-product-form-element {
    max-width: 800px;
    margin: 2rem auto;
    padding: 2rem;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}

/* Progress Steps */
.progress-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    position: relative;
}

.progress-container::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    z-index: 2;
    background: white;
    padding: 0 1rem;
}

.step-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e9ecef;
    color: #6c757d;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
}

.progress-step.active .step-circle {
    background: #007bff;
    color: white;
}

.progress-step.completed .step-circle {
    background: #28a745;
    color: white;
}

.step-label {
    font-size: 0.875rem;
    color: #6c757d;
    font-weight: 500;
}

/* Form Sections */
.form-section {
    display: none;
}

.form-section.active {
    display: block;
}

.section-title {
    font-size: 1.5rem;
    color: #212529;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #007bff;
}

/* Form Grid */
.form-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.col-span-2 {
    grid-column: span 2;
}

.form-group.col-span-3 {
    grid-column: span 3;
}

.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-label.required::after {
    content: '*';
    color: #dc3545;
}

.optional {
    font-weight: 400;
    color: #6c757d;
    font-size: 0.875rem;
}

.form-input,
.form-select {
    padding: 0.75rem 1rem;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.15s ease;
    background: white;
}

.form-input:focus,
.form-select:focus {
    outline: none;
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.input-group {
    display: flex;
    gap: 0;
}

.input-group .form-select {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
    flex: 0 0 100px;
}

.input-group .form-input {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    flex: 1;
}

/* Image Upload */
.image-upload-zone {
    border: 3px dashed #007bff;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    background: rgba(0, 123, 255, 0.05);
}

.image-upload-zone:hover {
    background: rgba(0, 123, 255, 0.1);
    border-color: #0056b3;
}

.upload-icon {
    font-size: 3rem;
    color: #007bff;
    margin-bottom: 1rem;
}

.upload-input {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    cursor: pointer;
}

/* AI Scoring */
.ai-scoring-section {
    margin: 2rem 0;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s ease;
}

.ai-scoring-section.show {
    opacity: 1;
    transform: translateY(0);
}

.score-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
}

.score-title {
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.score-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 1rem;
}

.score-feedback {
    background: rgba(255, 255, 255, 0.2);
    padding: 1rem;
    border-radius: 8px;
    font-size: 0.95rem;
}

/* Form Navigation */
.form-navigation {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 50px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: #007bff;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #545b62;
    transform: translateY(-2px);
}

.btn-success {
    background: #28a745;
    color: white;
}

.btn-success:hover {
    background: #1e7e34;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .beautiful-product-form-element {
        margin: 1rem;
        padding: 1.5rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-group.col-span-2,
    .form-group.col-span-3 {
        grid-column: span 1;
    }
    
    .progress-container {
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .step-label {
        display: none;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const formElement = document.querySelector('.beautiful-product-form-element');
    const form = formElement.querySelector('.beautiful-product-form');
    const sections = formElement.querySelectorAll('.form-section');
    const progressSteps = formElement.querySelectorAll('.progress-step');
    const prevBtn = formElement.querySelector('#prev-step');
    const nextBtn = formElement.querySelector('#next-step');
    const submitBtn = formElement.querySelector('#submit-form');
    const aiScoreSection = formElement.querySelector('#ai-score-display');
    
    let currentStep = 1;
    const totalSteps = sections.length;
    const aiScoring = form.dataset.aiScoring === 'true';
    
    // Form navigation
    function showStep(step) {
        // Hide all sections
        sections.forEach(section => section.classList.remove('active'));
        progressSteps.forEach(step => step.classList.remove('active'));
        
        // Show current section
        const currentSection = formElement.querySelector(`[data-section="${step}"]`);
        if (currentSection) {
            currentSection.classList.add('active');
        }
        
        // Update progress
        if (progressSteps[step - 1]) {
            progressSteps[step - 1].classList.add('active');
        }
        
        // Update navigation buttons
        prevBtn.style.display = step > 1 ? 'inline-flex' : 'none';
        nextBtn.style.display = step < totalSteps ? 'inline-flex' : 'none';
        submitBtn.style.display = step === totalSteps ? 'inline-flex' : 'none';
        
        // Show AI scoring if enabled and on last step
        if (aiScoring && step === totalSteps) {
            calculateAIScore();
        }
    }
    
    // Navigation event handlers
    nextBtn.addEventListener('click', () => {
        if (currentStep < totalSteps) {
            // Mark current step as completed
            if (progressSteps[currentStep - 1]) {
                progressSteps[currentStep - 1].classList.add('completed');
            }
            
            currentStep++;
            showStep(currentStep);
        }
    });
    
    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            showStep(currentStep);
        }
    });
    
    // Image upload handling
    const imageUpload = formElement.querySelector('#image-upload');
    const imageFile = formElement.querySelector('#image-file');
    
    if (imageUpload && imageFile) {
        imageUpload.addEventListener('click', () => imageFile.click());
        
        imageUpload.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUpload.style.background = 'rgba(0, 123, 255, 0.15)';
        });
        
        imageUpload.addEventListener('dragleave', () => {
            imageUpload.style.background = 'rgba(0, 123, 255, 0.05)';
        });
        
        imageUpload.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUpload.style.background = 'rgba(0, 123, 255, 0.05)';
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                imageFile.files = files;
                showImagePreview(files[0]);
            }
        });
        
        imageFile.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                showImagePreview(e.target.files[0]);
            }
        });
    }
    
    function showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUpload.innerHTML = `
                <img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 8px; margin-bottom: 1rem;">
                <h4>${file.name}</h4>
                <p>Click to change image</p>
            `;
        };
        reader.readAsDataURL(file);
    }
    
    // AI Scoring
    function calculateAIScore() {
        if (!aiScoring || !aiScoreSection) return;
        
        const formData = new FormData(form);
        const data = {};
        
        // Collect form data
        const aiFields = form.querySelectorAll('[data-ai-field]');
        aiFields.forEach(field => {
            const name = field.getAttribute('data-ai-field');
            data[name] = field.value;
        });
        
        // Show loading
        const scoreValue = formElement.querySelector('#score-value');
        const scoreFeedback = formElement.querySelector('#score-feedback');
        
        if (scoreValue && scoreFeedback) {
            scoreValue.textContent = '...';
            scoreFeedback.textContent = 'Analyzing your product...';
            aiScoreSection.style.display = 'block';
            aiScoreSection.classList.add('show');
            
            // Simulate AI scoring (replace with actual AJAX call)
            setTimeout(() => {
                const score = calculateBasicScore(data);
                scoreValue.textContent = `${score}/100`;
                scoreFeedback.innerHTML = generateFeedback(score, data);
            }, 1500);
        }
    }
    
    function calculateBasicScore(data) {
        let score = 0;
        
        // Title (30 points)
        if (data.title) {
            const titleLen = data.title.length;
            if (titleLen >= 10 && titleLen <= 60) score += 30;
            else if (titleLen > 0) score += 18;
        }
        
        // Description (25 points)
        if (data.description) {
            score += data.description.length >= 50 ? 25 : 12;
        }
        
        // Brand (15 points)
        if (data.manufacturer) score += 15;
        
        // Price (15 points)
        if (data.price && parseFloat(data.price) > 0) score += 15;
        
        // Tags (10 points)
        const tagsSelect = form.querySelector('[data-ai-field="tags"]');
        if (tagsSelect && tagsSelect.selectedOptions.length > 0) score += 10;
        
        // Image (5 points)
        if (imageFile && imageFile.files.length > 0) score += 5;
        
        return score;
    }
    
    function generateFeedback(score, data) {
        const feedback = [];
        
        if (data.title) {
            const titleLen = data.title.length;
            if (titleLen >= 10 && titleLen <= 60) feedback.push('✅ Perfect title length');
            else if (titleLen < 10) feedback.push('⚠️ Title could be more descriptive');
            else feedback.push('⚠️ Title might be too long');
        } else {
            feedback.push('❌ Missing product title');
        }
        
        if (data.description) {
            feedback.push(data.description.length >= 50 ? '✅ Great description' : '⚠️ Description could be more detailed');
        } else {
            feedback.push('❌ Missing product description');
        }
        
        if (data.manufacturer) feedback.push('✅ Brand information included');
        else feedback.push('💡 Consider adding brand information');
        
        let overall = '';
        if (score >= 80) overall = '🎉 Excellent! Your product looks amazing.';
        else if (score >= 60) overall = '👍 Great work! A few improvements could help.';
        else if (score >= 40) overall = '🚀 Good start! Adding more details will help.';
        else overall = '💪 Keep going! Every detail helps buyers find you.';
        
        return `
            <div style="text-align: left; margin-bottom: 1rem;">
                ${feedback.map(item => `<div style="margin-bottom: 0.5rem;">${item}</div>`).join('')}
            </div>
            <div style="font-style: italic; text-align: center; font-weight: 600;">
                ${overall}
            </div>
        `;
    }
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        const requiredFields = form.querySelectorAll('[required]');
        let valid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#dc3545';
                valid = false;
            } else {
                field.style.borderColor = '#e9ecef';
            }
        });
        
        if (!valid) {
            alert('Please fill in all required fields.');
            return;
        }
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        submitBtn.disabled = true;
        
        // Submit form (you'll need to implement the actual submission)
        // For now, show success message
        setTimeout(() => {
            alert('Thank you! Your product has been submitted for review.');
            form.reset();
            currentStep = 1;
            showStep(1);
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Product';
            submitBtn.disabled = false;
        }, 2000);
    });
    
    // Initialize
    showStep(1);
});
</script>
