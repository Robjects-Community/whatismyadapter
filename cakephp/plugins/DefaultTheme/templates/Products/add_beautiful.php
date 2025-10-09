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
.frontend-product-form {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 2rem 0;
}

.form-container {
    max-width: 800px;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.98);
    backdrop-filter: blur(10px);
    border-radius: 25px;
    box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    overflow: hidden;
}

.form-header {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    padding: 3rem 2rem;
    text-align: center;
    color: white;
    position: relative;
}

.form-header::before {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    right: 0;
    height: 20px;
    background: url("data:image/svg+xml,%3csvg width='100%25' height='100%25' xmlns='http://www.w3.org/2000/svg'%3e%3cdefs%3e%3cpattern id='wave' x='0' y='0' width='40' height='20' patternUnits='userSpaceOnUse'%3e%3cpath d='M0 20C10 10 30 10 40 20V0H0V20Z' fill='%23ffffff'/%3e%3c/pattern%3e%3c/defs%3e%3crect width='100%25' height='100%25' fill='url(%23wave)'/%3e%3c/svg%3e") repeat-x;
}

.form-header h1 {
    font-size: 2.8rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    text-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.form-header p {
    margin: 0;
    opacity: 0.95;
    font-size: 1.2rem;
    font-weight: 300;
}

.form-body {
    padding: 3rem;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f3f4;
}

.section-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.section-title {
    font-size: 1.6rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.form-field {
    margin-bottom: 2.5rem;
    position: relative;
}

.form-field label {
    display: block;
    font-weight: 600;
    color: #34495e;
    margin-bottom: 0.8rem;
    font-size: 1.1rem;
}

.field-badge {
    font-size: 0.75rem;
    padding: 0.3rem 0.8rem;
    border-radius: 15px;
    font-weight: 600;
    margin-left: 0.8rem;
    display: inline-block;
}

.badge-required {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
}

.badge-optional {
    background: linear-gradient(135deg, #4ecdc4, #44a08d);
    color: white;
}

.form-field input,
.form-field textarea,
.form-field select {
    width: 100%;
    padding: 1.2rem;
    border: 2px solid #e9ecef;
    border-radius: 15px;
    font-size: 1rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
}

.form-field input:focus,
.form-field textarea:focus,
.form-field select:focus {
    border-color: #4facfe;
    outline: none;
    box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1), 0 4px 8px rgba(0, 0, 0, 0.05);
    transform: translateY(-1px);
}

.form-field textarea {
    min-height: 120px;
    resize: vertical;
    font-family: inherit;
}

.input-group {
    display: flex;
    gap: 0;
}

.input-group select {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
    border-right: none;
    flex: 0 0 120px;
}

.input-group input {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    flex: 1;
}

.checkbox-field {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    border-radius: 15px;
    margin-bottom: 1rem;
}

.checkbox-field input[type="checkbox"] {
    width: 24px;
    height: 24px;
    accent-color: #4facfe;
}

.checkbox-field label {
    margin: 0;
    font-size: 1rem;
    color: #495057;
    cursor: pointer;
}

.image-drop-zone {
    border: 3px dashed #4facfe;
    border-radius: 20px;
    padding: 3rem;
    text-align: center;
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.05), rgba(0, 242, 254, 0.05));
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.image-drop-zone:hover {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.1), rgba(0, 242, 254, 0.1));
    border-color: #667eea;
    transform: translateY(-2px);
}

.image-drop-zone.dragover {
    background: linear-gradient(135deg, rgba(79, 172, 254, 0.15), rgba(0, 242, 254, 0.15));
    border-color: #667eea;
    border-style: solid;
}

.drop-icon {
    font-size: 4rem;
    color: #4facfe;
    margin-bottom: 1rem;
    display: block;
}

.ai-insights {
    background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
    border-radius: 20px;
    padding: 2rem;
    margin: 2rem 0;
    text-align: center;
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}

.ai-insights.show {
    opacity: 1;
    transform: translateY(0);
}

.ai-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.score-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea, #764ba2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    font-weight: 700;
    margin: 0 auto 1.5rem;
    position: relative;
}

.score-circle::after {
    content: '';
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    padding: 5px;
    background: linear-gradient(135deg, #4facfe, #00f2fe);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask-composite: exclude;
}

.ai-feedback {
    background: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    padding: 1.5rem;
    text-align: left;
    border-left: 5px solid #4facfe;
}

.feedback-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feedback-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}

.feedback-list li:last-child {
    border-bottom: none;
}

.form-actions {
    padding: 3rem;
    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    text-align: center;
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 1rem 2.5rem;
    border: none;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 180px;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    color: white;
}

.btn-secondary {
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: white;
    box-shadow: 0 4px 15px rgba(116, 185, 255, 0.4);
}

.btn-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(116, 185, 255, 0.6);
    color: white;
}

.help-tooltip {
    position: fixed;
    bottom: 2rem;
    right: 2rem;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fd79a8, #e84393);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 6px 20px rgba(253, 121, 168, 0.4);
    transition: all 0.3s ease;
    z-index: 1000;
}

.help-tooltip:hover {
    transform: scale(1.1);
    box-shadow: 0 8px 25px rgba(253, 121, 168, 0.6);
}

@media (max-width: 768px) {
    .frontend-product-form {
        padding: 1rem 0;
    }
    
    .form-container {
        margin: 0 1rem;
        border-radius: 20px;
    }
    
    .form-header {
        padding: 2rem 1.5rem;
    }
    
    .form-header h1 {
        font-size: 2.2rem;
    }
    
    .form-body {
        padding: 2rem 1.5rem;
    }
    
    .form-actions {
        padding: 2rem 1.5rem;
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<div class="frontend-product-form">
    <div class="form-container">
        <!-- Header -->
        <div class="form-header">
            <h1>🚀 Share Your Amazing Product</h1>
            <p>Create a stunning product listing with AI-powered recommendations</p>
        </div>

        <!-- Form Body -->
        <div class="form-body">
            <?= $this->Form->create($product, [
                'type' => 'file',
                'enctype' => 'multipart/form-data',
                'id' => 'product-form'
            ]) ?>

            <!-- Basic Information Section -->
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div>
                    <h2 class="section-title">Tell us about your product</h2>
                </div>
            </div>

            <div class="form-field">
                <label for="title">
                    Product Name
                    <span class="field-badge badge-required">Required</span>
                </label>
                <?= $this->Form->control('title', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'What\'s your product called?',
                    'data-ai-field' => 'title'
                ]) ?>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div class="form-field">
                    <label for="description">
                        Description
                        <span class="field-badge badge-optional">Optional</span>
                    </label>
                    <?= $this->Form->control('description', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'label' => false,
                        'placeholder' => 'What makes your product special? What problems does it solve?',
                        'data-ai-field' => 'description'
                    ]) ?>
                </div>

                <div class="form-field">
                    <label for="manufacturer">
                        Brand
                        <span class="field-badge badge-optional">Optional</span>
                    </label>
                    <?= $this->Form->control('manufacturer', [
                        'class' => 'form-control',
                        'label' => false,
                        'placeholder' => 'Brand name',
                        'data-ai-field' => 'manufacturer'
                    ]) ?>
                </div>
            </div>

            <!-- Pricing Section -->
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div>
                    <h2 class="section-title">Pricing & Details</h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                <div class="form-field">
                    <label for="price">
                        Price
                        <span class="field-badge badge-optional">Optional</span>
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
                            'class' => 'form-control',
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

                <div class="form-field">
                    <label for="model_number">
                        Model/SKU
                        <span class="field-badge badge-optional">Optional</span>
                    </label>
                    <?= $this->Form->control('model_number', [
                        'class' => 'form-control',
                        'label' => false,
                        'placeholder' => 'Model number or SKU',
                        'data-ai-field' => 'model_number'
                    ]) ?>
                </div>
            </div>

            <div class="form-field">
                <label for="tags">
                    Categories
                    <span class="field-badge badge-optional">Optional</span>
                </label>
                <?= $this->Form->control('tags._ids', [
                    'type' => 'select',
                    'options' => $tags,
                    'multiple' => true,
                    'class' => 'form-control',
                    'label' => false,
                    'data-ai-field' => 'tags'
                ]) ?>
            </div>

            <!-- Media Section -->
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <div>
                    <h2 class="section-title">Show off your product</h2>
                </div>
            </div>

            <div class="form-field">
                <label for="image">
                    Product Image
                    <span class="field-badge badge-optional">Optional</span>
                </label>
                <div class="image-drop-zone" id="drop-zone">
                    <i class="fas fa-cloud-upload-alt drop-icon"></i>
                    <h3>Drop your image here</h3>
                    <p>or click to browse</p>
                    <small>JPG, PNG, WebP up to 5MB</small>
                    <?= $this->Form->file('image', [
                        'class' => 'form-control d-none',
                        'accept' => 'image/*',
                        'id' => 'image-input'
                    ]) ?>
                </div>
            </div>

            <div class="form-field">
                <label for="alt_text">
                    Image Description
                    <span class="field-badge badge-optional">Optional</span>
                </label>
                <?= $this->Form->control('alt_text', [
                    'class' => 'form-control',
                    'label' => false,
                    'placeholder' => 'Describe your image for accessibility',
                    'data-ai-field' => 'alt_text'
                ]) ?>
            </div>

            <!-- Settings -->
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div>
                    <h2 class="section-title">Visibility Settings</h2>
                </div>
            </div>

            <div class="checkbox-field">
                <?= $this->Form->checkbox('is_published', ['checked' => true]) ?>
                <label for="is-published">
                    <strong>Make this product visible to everyone</strong><br>
                    <small>Others will be able to find and view your product</small>
                </label>
            </div>

            <div class="checkbox-field">
                <?= $this->Form->checkbox('featured') ?>
                <label for="featured">
                    <strong>Feature this product</strong><br>
                    <small>Highlight this product on the homepage</small>
                </label>
            </div>

            <?= $this->Form->end() ?>

            <!-- AI Insights -->
            <div class="ai-insights" id="ai-insights">
                <div class="ai-title">
                    <i class="fas fa-robot"></i>
                    AI Product Score
                </div>
                <div class="score-circle" id="score-circle">--</div>
                <div class="ai-feedback" id="ai-feedback">
                    <p>Fill out the form above to get personalized recommendations for improving your product listing!</p>
                </div>
                <button type="button" class="btn btn-secondary" id="refresh-score" style="display: none;">
                    <i class="fas fa-sync-alt"></i>
                    Refresh Analysis
                </button>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <a href="javascript:history.back()" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Go Back
            </a>
            <button type="submit" form="product-form" class="btn btn-primary" id="submit-btn">
                <i class="fas fa-rocket"></i>
                Publish Product
            </button>
        </div>
    </div>

    <!-- Help Tooltip -->
    <div class="help-tooltip" title="Need help?">
        <i class="fas fa-question"></i>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const imageInput = document.getElementById('image-input');
    const aiInsights = document.getElementById('ai-insights');
    const scoreCircle = document.getElementById('score-circle');
    const aiFeedback = document.getElementById('ai-feedback');
    const refreshBtn = document.getElementById('refresh-score');
    const helpTooltip = document.querySelector('.help-tooltip');
    
    let scoreTimeout;

    // Image upload handling
    dropZone.addEventListener('click', () => imageInput.click());
    
    dropZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        dropZone.classList.add('dragover');
    });
    
    dropZone.addEventListener('dragleave', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
    });
    
    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        dropZone.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0 && files[0].type.startsWith('image/')) {
            imageInput.files = files;
            showImagePreview(files[0]);
        }
    });
    
    imageInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            showImagePreview(e.target.files[0]);
        }
    });
    
    function showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            dropZone.innerHTML = `
                <img src="${e.target.result}" style="max-width: 100%; max-height: 250px; border-radius: 15px; margin-bottom: 1rem;">
                <h3>${file.name}</h3>
                <p>Click to change image</p>
            `;
        };
        reader.readAsDataURL(file);
    }

    // AI scoring system
    function gatherFormData() {
        const aiFields = document.querySelectorAll('[data-ai-field]');
        const data = {};
        
        aiFields.forEach(field => {
            const name = field.getAttribute('data-ai-field');
            if (field.type === 'checkbox') {
                data[name] = field.checked;
            } else if (field.tagName === 'SELECT' && field.multiple) {
                data[name] = Array.from(field.selectedOptions).map(opt => opt.text);
            } else {
                data[name] = field.value;
            }
        });
        
        return data;
    }

    async function calculateScore() {
        const data = gatherFormData();
        
        // Only score if there's meaningful data
        if (!data.title?.trim()) {
            return;
        }

        try {
            // Show loading
            scoreCircle.textContent = '...';
            aiFeedback.innerHTML = '<p><i class="fas fa-spinner fa-spin"></i> Analyzing your product...</p>';
            
            // Simulate AI processing
            await new Promise(resolve => setTimeout(resolve, 1200));
            
            let score = 0;
            let feedback = [];

            // Analyze title (30 points)
            if (data.title?.trim()) {
                const titleLen = data.title.trim().length;
                if (titleLen >= 10 && titleLen <= 60) {
                    score += 30;
                    feedback.push('✅ Perfect title length');
                } else if (titleLen > 0) {
                    score += 18;
                    if (titleLen < 10) feedback.push('⚠️ Title could be more descriptive');
                    if (titleLen > 60) feedback.push('⚠️ Title might be too long');
                }
            }

            // Analyze description (25 points)
            if (data.description?.trim()) {
                const descLen = data.description.trim().length;
                if (descLen >= 50) {
                    score += 25;
                    feedback.push('✅ Great product description');
                } else {
                    score += 12;
                    feedback.push('⚠️ Description could be more detailed');
                }
            } else {
                feedback.push('❌ Missing product description');
            }

            // Brand info (15 points)
            if (data.manufacturer?.trim()) {
                score += 15;
                feedback.push('✅ Brand information included');
            } else {
                feedback.push('💡 Consider adding brand information');
            }

            // Pricing (15 points)
            if (data.price && parseFloat(data.price) > 0) {
                score += 15;
                feedback.push('✅ Pricing information provided');
            } else {
                feedback.push('💡 Price helps buyers make decisions');
            }

            // Categories (10 points)
            if (data.tags?.length > 0) {
                score += 10;
                feedback.push('✅ Product properly categorized');
            } else {
                feedback.push('💡 Tags help people find your product');
            }

            // Image (5 points)
            if (imageInput.files.length > 0) {
                score += 5;
                feedback.push('✅ Product image uploaded');
            } else {
                feedback.push('📷 Images make your listing more appealing');
            }

            // Display results
            scoreCircle.textContent = score;
            
            let motivation = '';
            if (score >= 80) motivation = '🎉 Outstanding! Your listing looks professional and complete.';
            else if (score >= 60) motivation = '👍 Great work! Just a few tweaks could make it even better.';
            else if (score >= 40) motivation = '🚀 Good start! Adding more details will boost visibility.';
            else motivation = '💪 Keep going! Every detail you add helps buyers find you.';
            
            aiFeedback.innerHTML = `
                <ul class="feedback-list">
                    ${feedback.map(item => `<li>${item}</li>`).join('')}
                </ul>
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(0,0,0,0.1); font-style: italic; text-align: center;">
                    ${motivation}
                </div>
            `;

            aiInsights.classList.add('show');
            refreshBtn.style.display = 'inline-flex';

        } catch (error) {
            scoreCircle.textContent = '?';
            aiFeedback.innerHTML = '<p>Unable to analyze right now. Please try again!</p>';
        }
    }

    // Event listeners
    const aiFields = document.querySelectorAll('[data-ai-field]');
    aiFields.forEach(field => {
        field.addEventListener('input', () => {
            clearTimeout(scoreTimeout);
            scoreTimeout = setTimeout(calculateScore, 800);
        });
        
        field.addEventListener('change', calculateScore);
    });

    imageInput.addEventListener('change', calculateScore);
    refreshBtn.addEventListener('click', calculateScore);

    // Help tooltip
    helpTooltip.addEventListener('click', () => {
        alert(`🤝 Need help getting started?\n\n✨ Tips for success:\n• Write a clear, descriptive product name\n• Explain what makes your product special\n• Add high-quality images\n• Include pricing information\n• Use relevant categories\n\n🤖 The AI score helps you optimize your listing for maximum visibility!`);
    });

    // Initial check for existing data
    setTimeout(() => {
        const data = gatherFormData();
        if (data.title?.trim()) {
            calculateScore();
        }
    }, 300);
});
</script>
