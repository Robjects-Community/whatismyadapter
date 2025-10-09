<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 */
?>

<style>
.beautiful-product-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.form-container {
    max-width: 900px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    padding: 2rem;
    text-align: center;
    color: white;
}

.form-header h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-header p {
    margin: 0.5rem 0 0 0;
    opacity: 0.9;
    font-size: 1.1rem;
}

.form-section {
    padding: 2rem;
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-title i {
    color: #4facfe;
}

.form-group-enhanced {
    position: relative;
    margin-bottom: 2rem;
}

.form-group-enhanced label {
    font-weight: 600;
    color: #34495e;
    margin-bottom: 0.5rem;
    display: block;
}

.form-group-enhanced .form-control,
.form-group-enhanced .form-select {
    border: 2px solid #e8ecef;
    border-radius: 12px;
    padding: 1rem;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.form-group-enhanced .form-control:focus,
.form-group-enhanced .form-select:focus {
    border-color: #4facfe;
    box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
    outline: none;
}

.form-group-enhanced textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

.optional-badge {
    background: linear-gradient(135deg, #ffeaa7, #fab1a0);
    color: #2d3436;
    font-size: 0.8rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
    margin-left: 0.5rem;
}

.required-badge {
    background: linear-gradient(135deg, #fd79a8, #e84393);
    color: white;
    font-size: 0.8rem;
    padding: 0.25rem 0.6rem;
    border-radius: 20px;
    font-weight: 500;
    margin-left: 0.5rem;
}

.ai-score-container {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    border-radius: 15px;
    padding: 1.5rem;
    margin: 2rem 0;
    text-align: center;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.5s ease;
}

.ai-score-container.visible {
    opacity: 1;
    transform: translateY(0);
}

.ai-score-title {
    font-size: 1.3rem;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 1rem;
}

.score-display {
    font-size: 3rem;
    font-weight: 700;
    margin: 1rem 0;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.score-reasoning {
    background: rgba(255, 255, 255, 0.8);
    border-radius: 10px;
    padding: 1rem;
    margin-top: 1rem;
    text-align: left;
    border-left: 4px solid #4facfe;
}

.form-actions {
    padding: 2rem;
    background: #f8f9fb;
    text-align: center;
}

.btn-beautiful {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    padding: 1rem 2rem;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    min-width: 200px;
}

.btn-beautiful:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-secondary-beautiful {
    background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
    margin-right: 1rem;
}

.floating-helper {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    background: linear-gradient(135deg, #fd79a8, #e84393);
    color: white;
    border-radius: 50%;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(253, 121, 168, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.floating-helper:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(253, 121, 168, 0.6);
}

.progress-indicator {
    display: flex;
    justify-content: space-between;
    margin: 2rem 0;
    padding: 0 2rem;
}

.progress-step {
    flex: 1;
    text-align: center;
    position: relative;
}

.progress-step::after {
    content: '';
    position: absolute;
    top: 20px;
    left: 50%;
    width: 100%;
    height: 2px;
    background: #e8ecef;
    z-index: 1;
}

.progress-step:last-child::after {
    display: none;
}

.progress-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e8ecef;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    position: relative;
    z-index: 2;
    margin: 0 auto 0.5rem;
    transition: all 0.3s ease;
}

.progress-step.active .progress-circle {
    background: linear-gradient(135deg, #4facfe, #00f2fe);
}

.progress-step.completed .progress-circle {
    background: linear-gradient(135deg, #00b894, #00cec9);
}

.form-tips {
    background: linear-gradient(135deg, #ffeaa7 0%, #fab1a0 100%);
    border-radius: 10px;
    padding: 1rem;
    margin: 1rem 0;
}

.form-tips h4 {
    margin: 0 0 0.5rem 0;
    color: #2d3436;
}

.form-tips ul {
    margin: 0;
    padding-left: 1.2rem;
    color: #2d3436;
}

.image-upload-area {
    border: 3px dashed #4facfe;
    border-radius: 15px;
    padding: 2rem;
    text-align: center;
    background: rgba(79, 172, 254, 0.05);
    cursor: pointer;
    transition: all 0.3s ease;
}

.image-upload-area:hover {
    background: rgba(79, 172, 254, 0.1);
    border-color: #667eea;
}

.image-upload-area i {
    font-size: 3rem;
    color: #4facfe;
    margin-bottom: 1rem;
}

@media (max-width: 768px) {
    .form-container {
        margin: 1rem;
        border-radius: 15px;
    }
    
    .form-header h1 {
        font-size: 2rem;
    }
    
    .form-section {
        padding: 1.5rem;
    }
    
    .floating-helper {
        bottom: 1rem;
        right: 1rem;
    }
}
</style>

<div class="beautiful-product-form">
    <div class="form-container">
        <!-- Form Header -->
        <div class="form-header">
            <h1><i class="fas fa-sparkles"></i> Add Your Amazing Product</h1>
            <p>Create a beautiful product listing with AI-powered insights</p>
        </div>

        <!-- Progress Indicator -->
        <div class="progress-indicator">
            <div class="progress-step active" data-step="1">
                <div class="progress-circle">1</div>
                <span>Basic Info</span>
            </div>
            <div class="progress-step" data-step="2">
                <div class="progress-circle">2</div>
                <span>Details</span>
            </div>
            <div class="progress-step" data-step="3">
                <div class="progress-circle">3</div>
                <span>Media</span>
            </div>
            <div class="progress-step" data-step="4">
                <div class="progress-circle">4</div>
                <span>AI Score</span>
            </div>
        </div>

        <?= $this->Form->create($product, [
            'type' => 'file',
            'enctype' => 'multipart/form-data',
            'id' => 'beautiful-product-form',
            'data-ai-score-url' => $this->Url->build(['action' => 'aiScore'])
        ]) ?>

        <!-- Section 1: Essential Information -->
        <div class="form-section" data-section="1">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i>
                Essential Information
            </h2>
            
            <div class="form-tips">
                <h4><i class="fas fa-lightbulb"></i> Tips for Success</h4>
                <ul>
                    <li>Choose a clear, descriptive product title</li>
                    <li>Keep your description engaging but concise</li>
                    <li>Don't worry about perfect details - our AI will help you improve!</li>
                </ul>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group-enhanced">
                        <label for="title">
                            Product Title
                            <span class="required-badge">Required</span>
                        </label>
                        <?= $this->Form->control('title', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Enter your product name...',
                            'data-ai-field' => 'title'
                        ]) ?>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group-enhanced">
                        <label for="manufacturer">
                            Brand/Manufacturer
                            <span class="optional-badge">Optional</span>
                        </label>
                        <?= $this->Form->control('manufacturer', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'e.g. Apple, Samsung, etc.',
                            'data-ai-field' => 'manufacturer'
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="form-group-enhanced">
                <label for="description">
                    Product Description
                    <span class="optional-badge">Optional</span>
                </label>
                <?= $this->Form->control('description', [
                    'type' => 'textarea',
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'Tell us about your product. What makes it special? What problems does it solve?',
                    'data-ai-field' => 'description'
                ]) ?>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group-enhanced">
                        <label for="model-number">
                            Model Number
                            <span class="optional-badge">Optional</span>
                        </label>
                        <?= $this->Form->control('model_number', [
                            'class' => 'form-control',
                            'label' => false,
                            'placeholder' => 'Model or SKU number',
                            'data-ai-field' => 'model_number'
                        ]) ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group-enhanced">
                        <label for="tags">
                            Categories/Tags
                            <span class="optional-badge">Optional</span>
                        </label>
                        <?= $this->Form->control('tags._ids', [
                            'type' => 'select',
                            'options' => $tags,
                            'multiple' => true,
                            'class' => 'form-select',
                            'label' => false,
                            'data-ai-field' => 'tags'
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Pricing & Details -->
        <div class="form-section" data-section="2">
            <h2 class="section-title">
                <i class="fas fa-dollar-sign"></i>
                Pricing & Details
            </h2>

            <div class="row">
                <div class="col-lg-6">
                    <div class="form-group-enhanced">
                        <label for="price">
                            Price
                            <span class="optional-badge">Optional</span>
                        </label>
                        <div class="input-group">
                            <?= $this->Form->control('currency', [
                                'type' => 'select',
                                'options' => [
                                    'USD' => '$',
                                    'EUR' => '€',
                                    'GBP' => '£',
                                    'JPY' => '¥',
                                    'CAD' => 'C$',
                                    'AUD' => 'A$'
                                ],
                                'default' => 'USD',
                                'class' => 'form-select',
                                'style' => 'flex: 0 0 auto; width: auto;',
                                'label' => false
                            ]) ?>
                            <?= $this->Form->control('price', [
                                'class' => 'form-control',
                                'label' => false,
                                'placeholder' => '0.00',
                                'type' => 'number',
                                'step' => '0.01',
                                'data-ai-field' => 'price'
                            ]) ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="form-group-enhanced">
                        <label for="article-id">
                            Link to Detailed Article
                            <span class="optional-badge">Optional</span>
                        </label>
                        <?= $this->Form->control('article_id', [
                            'type' => 'select',
                            'options' => $articles,
                            'empty' => 'Select an article (optional)',
                            'class' => 'form-select',
                            'label' => false
                        ]) ?>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="form-group-enhanced">
                        <label>
                            <i class="fas fa-eye"></i>
                            Publish Status
                        </label>
                        <div class="form-check form-switch" style="padding-left: 0;">
                            <?= $this->Form->checkbox('is_published', [
                                'class' => 'form-check-input',
                                'style' => 'transform: scale(1.5); margin-right: 0.5rem;',
                                'checked' => true
                            ]) ?>
                            <label class="form-check-label" for="is-published" style="margin-left: 0.5rem;">
                                Make this product visible to everyone
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="form-group-enhanced">
                        <label>
                            <i class="fas fa-star"></i>
                            Featured Product
                        </label>
                        <div class="form-check form-switch" style="padding-left: 0;">
                            <?= $this->Form->checkbox('featured', [
                                'class' => 'form-check-input',
                                'style' => 'transform: scale(1.5); margin-right: 0.5rem;'
                            ]) ?>
                            <label class="form-check-label" for="featured" style="margin-left: 0.5rem;">
                                Highlight this product on the homepage
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Media Upload -->
        <div class="form-section" data-section="3">
            <h2 class="section-title">
                <i class="fas fa-images"></i>
                Product Images
            </h2>

            <div class="form-group-enhanced">
                <label for="image">
                    Primary Product Image
                    <span class="optional-badge">Optional</span>
                </label>
                <div class="image-upload-area" id="image-upload-area">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <h4>Drag & Drop Your Image Here</h4>
                    <p>Or click to browse files</p>
                    <p><small>Supported: JPG, PNG, WebP (Max 5MB)</small></p>
                    <?= $this->Form->file('image', [
                        'class' => 'form-control d-none',
                        'accept' => 'image/*',
                        'id' => 'image-file-input'
                    ]) ?>
                </div>
            </div>

            <div class="form-group-enhanced">
                <label for="alt-text">
                    Image Description (Alt Text)
                    <span class="optional-badge">Optional</span>
                </label>
                <?= $this->Form->control('alt_text', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'Describe your image for accessibility',
                    'data-ai-field' => 'alt_text'
                ]) ?>
            </div>
        </div>

        <!-- AI Score Section -->
        <div class="ai-score-container" id="ai-score-container">
            <div class="ai-score-title">
                <i class="fas fa-robot"></i> AI Product Score
            </div>
            <div class="score-display" id="score-display">--</div>
            <div class="score-reasoning" id="score-reasoning">
                Complete the form above to get your AI-powered product score and recommendations!
            </div>
            <button type="button" class="btn btn-secondary-beautiful" id="refresh-score" style="display: none;">
                <i class="fas fa-sync-alt"></i> Refresh Score
            </button>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="button" class="btn btn-secondary-beautiful" onclick="history.back()">
                <i class="fas fa-arrow-left"></i> Go Back
            </button>
            <button type="submit" class="btn btn-beautiful" id="submit-btn">
                <i class="fas fa-save"></i> Save Product
            </button>
        </div>

        <?= $this->Form->end() ?>
    </div>

    <!-- Floating Helper -->
    <div class="floating-helper" title="Need Help?">
        <i class="fas fa-question"></i>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('beautiful-product-form');
    const aiScoreContainer = document.getElementById('ai-score-container');
    const scoreDisplay = document.getElementById('score-display');
    const scoreReasoning = document.getElementById('score-reasoning');
    const refreshScoreBtn = document.getElementById('refresh-score');
    const progressSteps = document.querySelectorAll('.progress-step');
    
    let formData = {};
    let scoreTimeout;

    // Image upload handling
    const imageUploadArea = document.getElementById('image-upload-area');
    const imageFileInput = document.getElementById('image-file-input');

    imageUploadArea.addEventListener('click', () => {
        imageFileInput.click();
    });

    imageUploadArea.addEventListener('dragover', (e) => {
        e.preventDefault();
        imageUploadArea.style.background = 'rgba(79, 172, 254, 0.15)';
    });

    imageUploadArea.addEventListener('dragleave', (e) => {
        e.preventDefault();
        imageUploadArea.style.background = 'rgba(79, 172, 254, 0.05)';
    });

    imageUploadArea.addEventListener('drop', (e) => {
        e.preventDefault();
        imageUploadArea.style.background = 'rgba(79, 172, 254, 0.05)';
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            imageFileInput.files = files;
            updateImagePreview(files[0]);
        }
    });

    imageFileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            updateImagePreview(e.target.files[0]);
        }
    });

    function updateImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUploadArea.innerHTML = `
                <img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 10px; margin-bottom: 1rem;">
                <p><strong>${file.name}</strong></p>
                <p><small>Click to change image</small></p>
            `;
        };
        reader.readAsDataURL(file);
    }

    // AI Scoring functionality
    function collectFormData() {
        const aiFields = document.querySelectorAll('[data-ai-field]');
        formData = {};
        
        aiFields.forEach(field => {
            const fieldName = field.getAttribute('data-ai-field');
            if (field.type === 'checkbox') {
                formData[fieldName] = field.checked;
            } else if (field.tagName === 'SELECT' && field.multiple) {
                formData[fieldName] = Array.from(field.selectedOptions).map(option => option.text);
            } else {
                formData[fieldName] = field.value;
            }
        });
        
        return formData;
    }

    function updateProgressSteps() {
        const data = collectFormData();
        let completedSections = 0;

        // Section 1: Basic Info (title is required)
        if (data.title && data.title.trim()) {
            progressSteps[0].classList.add('completed');
            completedSections++;
        }

        // Section 2: Any detail filled
        if (data.price || data.manufacturer || data.model_number) {
            progressSteps[1].classList.add('completed');
            completedSections++;
        }

        // Section 3: Image uploaded
        if (imageFileInput.files.length > 0 || data.alt_text) {
            progressSteps[2].classList.add('completed');
            completedSections++;
        }

        // Update active step
        progressSteps.forEach(step => step.classList.remove('active'));
        if (completedSections < 3) {
            progressSteps[completedSections].classList.add('active');
        } else {
            progressSteps[3].classList.add('active');
        }
    }

    async function calculateAIScore() {
        const data = collectFormData();
        
        // Don't calculate if no meaningful data
        if (!data.title || !data.title.trim()) {
            return;
        }

        try {
            // Show loading state
            scoreDisplay.textContent = '...';
            scoreReasoning.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Analyzing your product...';
            
            // Simulate AI scoring (replace with actual API call)
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            // Calculate basic score based on completeness
            let score = 0;
            let reasons = [];

            // Title analysis (max 25 points)
            if (data.title && data.title.trim()) {
                const titleLength = data.title.trim().length;
                if (titleLength >= 10 && titleLength <= 60) {
                    score += 25;
                    reasons.push('✓ Excellent title length');
                } else if (titleLength > 0) {
                    score += 15;
                    if (titleLength < 10) reasons.push('⚠ Title could be more descriptive');
                    if (titleLength > 60) reasons.push('⚠ Title might be too long');
                }
            }

            // Description analysis (max 25 points)
            if (data.description && data.description.trim()) {
                const descLength = data.description.trim().length;
                if (descLength >= 50) {
                    score += 25;
                    reasons.push('✓ Comprehensive description provided');
                } else {
                    score += 10;
                    reasons.push('⚠ Description could be more detailed');
                }
            } else {
                reasons.push('✗ Missing product description');
            }

            // Brand/manufacturer (max 15 points)
            if (data.manufacturer && data.manufacturer.trim()) {
                score += 15;
                reasons.push('✓ Brand information provided');
            } else {
                reasons.push('⚠ Consider adding brand/manufacturer');
            }

            // Price information (max 15 points)
            if (data.price && parseFloat(data.price) > 0) {
                score += 15;
                reasons.push('✓ Pricing information included');
            } else {
                reasons.push('⚠ Price not specified');
            }

            // Categories/Tags (max 10 points)
            if (data.tags && data.tags.length > 0) {
                score += 10;
                reasons.push('✓ Product categorized with tags');
            } else {
                reasons.push('⚠ No categories selected');
            }

            // Image (max 10 points)
            if (imageFileInput.files.length > 0) {
                score += 10;
                reasons.push('✓ Product image uploaded');
            } else {
                reasons.push('✗ Missing product image');
            }

            // Display results
            scoreDisplay.textContent = score + '/100';
            scoreReasoning.innerHTML = `
                <h5 style="margin-bottom: 1rem;">AI Analysis Results:</h5>
                <ul style="text-align: left; margin: 0; padding-left: 1.2rem;">
                    ${reasons.map(reason => `<li>${reason}</li>`).join('')}
                </ul>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(0,0,0,0.1); font-style: italic;">
                    ${score >= 80 ? '🎉 Excellent! Your product listing looks professional and complete.' :
                      score >= 60 ? '👍 Good job! A few improvements could make this even better.' :
                      score >= 40 ? '⚡ Not bad! Consider adding more details to improve visibility.' :
                      '🚀 Great start! Fill in more information to create an amazing listing.'}
                </div>
            `;

            // Show the score container
            aiScoreContainer.classList.add('visible');
            refreshScoreBtn.style.display = 'inline-block';

        } catch (error) {
            scoreDisplay.textContent = 'Error';
            scoreReasoning.innerHTML = 'Unable to calculate score. Please try again.';
        }
    }

    // Add event listeners to form fields
    const aiFields = document.querySelectorAll('[data-ai-field]');
    aiFields.forEach(field => {
        field.addEventListener('input', () => {
            updateProgressSteps();
            
            // Debounce AI score calculation
            clearTimeout(scoreTimeout);
            scoreTimeout = setTimeout(calculateAIScore, 1000);
        });

        field.addEventListener('change', () => {
            updateProgressSteps();
            calculateAIScore();
        });
    });

    // Image input change
    imageFileInput.addEventListener('change', () => {
        updateProgressSteps();
        calculateAIScore();
    });

    // Refresh score button
    refreshScoreBtn.addEventListener('click', calculateAIScore);

    // Floating helper
    document.querySelector('.floating-helper').addEventListener('click', () => {
        alert('Need help? Here are some tips:\n\n• Start with a clear product title\n• Add a detailed description\n• Include pricing information\n• Upload a high-quality image\n• Use relevant tags for better discoverability\n\nThe AI score will help you optimize your listing!');
    });

    // Initial setup
    updateProgressSteps();
    
    // Calculate initial score if there's data
    setTimeout(() => {
        const data = collectFormData();
        if (data.title && data.title.trim()) {
            calculateAIScore();
        }
    }, 500);
});
</script>
