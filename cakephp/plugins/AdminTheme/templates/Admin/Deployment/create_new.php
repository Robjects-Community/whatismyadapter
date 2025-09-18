<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $page
 * @var array $existingSlugs
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-plus-circle me-2"></i>
                <?= __('Create New Deployment') ?>
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

<?= $this->Form->create($page, [
    'class' => 'needs-validation',
    'novalidate' => true,
    'type' => 'file',
    'enctype' => 'multipart/form-data'
]) ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Introduction -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="icon-circle create-new me-3">
                        <i class="bi bi-plus-circle"></i>
                    </div>
                    <div>
                        <h2 class="h5 mb-1"><?= __('Build Fresh Deployment') ?></h2>
                        <p class="text-muted mb-0"><?= __('Create a new page with custom styling and functionality') ?></p>
                    </div>
                </div>
                <p class="mb-0">
                    <?= __('Design your page from the ground up with custom HTML, CSS, and JavaScript. Upload your own files or write code directly in the editor while maintaining the outer Willow CMS theme.') ?>
                </p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-file-text me-2"></i>
                    <?= __('Page Content') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('title', [
                            'label' => ['text' => __('Page Title'), 'class' => 'form-label fw-semibold'],
                            'class' => 'form-control form-control-lg',
                            'placeholder' => __('Enter your page title'),
                            'required' => true,
                            'data-slug-source' => true
                        ]) ?>
                        <div class="form-text">
                            <?= __('This will be displayed as the main heading on your page.') ?>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('slug', [
                            'label' => ['text' => __('Page URL Slug'), 'class' => 'form-label fw-semibold'],
                            'class' => 'form-control',
                            'placeholder' => __('page-url-slug'),
                            'required' => true,
                            'data-slug-target' => true,
                            'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$'
                        ]) ?>
                        <div class="form-text">
                            <?= __('URL-friendly version of the title. Only lowercase letters, numbers, and hyphens allowed.') ?>
                            <br>
                            <strong><?= __('Full URL:') ?></strong> <code id="full-url-preview"><?= $this->Url->build('/', ['fullBase' => true]) ?>pages/</code>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => ['text' => __('Base HTML Content'), 'class' => 'form-label fw-semibold'],
                        'class' => 'form-control',
                        'rows' => 15,
                        'placeholder' => __('Write your main HTML content here. Additional custom HTML from uploads will be appended.')
                    ]) ?>
                    <div class="form-text">
                        <?= __('HTML is allowed. This will be the foundation of your page content.') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Files Section -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h3 class="card-title mb-0">
                    <i class="bi bi-file-code me-2"></i>
                    <?= __('Custom Files & Styling') ?>
                </h3>
            </div>
            <div class="card-body">
                <!-- File Upload Tabs -->
                <ul class="nav nav-tabs mb-4" id="customFileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="css-tab" data-bs-toggle="tab" data-bs-target="#css-panel" type="button" role="tab">
                            <i class="bi bi-filetype-css me-2"></i><?= __('CSS Styling') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="js-tab" data-bs-toggle="tab" data-bs-target="#js-panel" type="button" role="tab">
                            <i class="bi bi-filetype-js me-2"></i><?= __('JavaScript') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="html-tab" data-bs-toggle="tab" data-bs-target="#html-panel" type="button" role="tab">
                            <i class="bi bi-filetype-html me-2"></i><?= __('HTML Components') ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="files-tab" data-bs-toggle="tab" data-bs-target="#files-panel" type="button" role="tab">
                            <i class="bi bi-cloud-upload me-2"></i><?= __('File Upload') ?>
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    <!-- CSS Panel -->
                    <div class="tab-pane fade show active" id="css-panel" role="tabpanel">
                        <?= $this->Form->control('custom_css', [
                            'type' => 'textarea',
                            'label' => ['text' => __('Custom CSS'), 'class' => 'form-label fw-semibold'],
                            'class' => 'form-control font-monospace',
                            'rows' => 12,
                            'placeholder' => __('/* Write your custom CSS here */
.my-custom-class {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}'),
                            'data-language' => 'css'
                        ]) ?>
                        <div class="form-text">
                            <?= __('Custom CSS styles will be applied to your page while maintaining the outer Willow CMS theme.') ?>
                        </div>
                    </div>

                    <!-- JavaScript Panel -->
                    <div class="tab-pane fade" id="js-panel" role="tabpanel">
                        <?= $this->Form->control('custom_js', [
                            'type' => 'textarea',
                            'label' => ['text' => __('Custom JavaScript'), 'class' => 'form-label fw-semibold'],
                            'class' => 'form-control font-monospace',
                            'rows' => 12,
                            'placeholder' => __('// Write your custom JavaScript here
document.addEventListener("DOMContentLoaded", function() {
    console.log("Custom page loaded");
    
    // Your custom functionality
});'),
                            'data-language' => 'javascript'
                        ]) ?>
                        <div class="form-text">
                            <?= __('JavaScript code will be executed when your page loads. Ensure code is safe and tested.') ?>
                        </div>
                    </div>

                    <!-- HTML Panel -->
                    <div class="tab-pane fade" id="html-panel" role="tabpanel">
                        <?= $this->Form->control('custom_html', [
                            'type' => 'textarea',
                            'label' => ['text' => __('Additional HTML Components'), 'class' => 'form-label fw-semibold'],
                            'class' => 'form-control font-monospace',
                            'rows' => 12,
                            'placeholder' => __('<!-- Additional HTML components -->
<div class="custom-component">
    <h3>Custom Section</h3>
    <p>This HTML will be appended to your main content.</p>
</div>'),
                            'data-language' => 'html'
                        ]) ?>
                        <div class="form-text">
                            <?= __('This HTML content will be added to your main page content. Use for additional components or sections.') ?>
                        </div>
                    </div>

                    <!-- File Upload Panel -->
                    <div class="tab-pane fade" id="files-panel" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold"><?= __('CSS File') ?></label>
                                <?= $this->Form->file('css_file', [
                                    'class' => 'form-control',
                                    'accept' => '.css',
                                    'data-file-type' => 'css'
                                ]) ?>
                                <div class="form-text"><?= __('Upload a .css file to include in your page styling') ?></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold"><?= __('JavaScript File') ?></label>
                                <?= $this->Form->file('js_file', [
                                    'class' => 'form-control',
                                    'accept' => '.js',
                                    'data-file-type' => 'js'
                                ]) ?>
                                <div class="form-text"><?= __('Upload a .js file to include in your page functionality') ?></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold"><?= __('HTML File') ?></label>
                                <?= $this->Form->file('html_file', [
                                    'class' => 'form-control',
                                    'accept' => '.html,.htm',
                                    'data-file-type' => 'html'
                                ]) ?>
                                <div class="form-text"><?= __('Upload an .html file to append to your page content') ?></div>
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            <?= __('Uploaded files will be combined with your inline code. File content takes precedence over textarea content if both are provided.') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h3 class="card-title mb-0">
                    <i class="bi bi-search me-2"></i>
                    <?= __('SEO Settings') ?>
                </h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?= $this->Form->control('meta_title', [
                        'label' => ['text' => __('Meta Title'), 'class' => 'form-label fw-semibold'],
                        'class' => 'form-control',
                        'placeholder' => __('Page Title - Site Name'),
                        'maxlength' => 60
                    ]) ?>
                    <div class="form-text">
                        <?= __('Recommended: 50-60 characters. Leave empty to use page title.') ?>
                        <span class="float-end"><span id="meta-title-count">0</span>/60</span>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $this->Form->control('meta_description', [
                        'type' => 'textarea',
                        'label' => ['text' => __('Meta Description'), 'class' => 'form-label fw-semibold'],
                        'class' => 'form-control',
                        'rows' => 3,
                        'placeholder' => __('Brief description of the page content'),
                        'maxlength' => 160
                    ]) ?>
                    <div class="form-text">
                        <?= __('Recommended: 150-160 characters. This appears in search results.') ?>
                        <span class="float-end"><span id="meta-description-count">0</span>/160</span>
                    </div>
                </div>

                <div class="mb-0">
                    <?= $this->Form->control('meta_keywords', [
                        'label' => ['text' => __('Meta Keywords'), 'class' => 'form-label fw-semibold'],
                        'class' => 'form-control',
                        'placeholder' => __('keyword1, keyword2, keyword3')
                    ]) ?>
                    <div class="form-text">
                        <?= __('Comma-separated keywords related to your page content.') ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Publish Settings -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-gear me-2"></i>
                    <?= __('Publish Settings') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <?= $this->Form->checkbox('is_published', [
                            'class' => 'form-check-input',
                            'role' => 'switch',
                            'checked' => true
                        ]) ?>
                        <label class="form-check-label" for="is-published">
                            <?= __('Publish immediately') ?>
                        </label>
                    </div>
                    <div class="form-text">
                        <?= __('Uncheck to save as draft') ?>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <?= $this->Form->checkbox('main_menu', [
                            'class' => 'form-check-input'
                        ]) ?>
                        <label class="form-check-label" for="main-menu">
                            <?= __('Show in header menu') ?>
                        </label>
                    </div>
                </div>

                <div class="mb-0">
                    <div class="form-check">
                        <?= $this->Form->checkbox('footer_menu', [
                            'class' => 'form-check-input'
                        ]) ?>
                        <label class="form-check-label" for="footer-menu">
                            <?= __('Show in footer menu') ?>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-eye me-2"></i>
                    <?= __('Live Preview') ?>
                </h5>
            </div>
            <div class="card-body">
                <div id="page-preview" class="border rounded p-3" style="min-height: 200px; background-color: #f8f9fa;">
                    <div class="text-muted text-center py-4">
                        <i class="bi bi-eye-slash display-4"></i>
                        <p class="mb-0"><?= __('Preview will appear here as you type') ?></p>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <small class="text-muted"><?= __('Live preview of your content with custom styling') ?></small>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?= $this->Form->submit(__('Create New Deployment'), [
                        'class' => 'btn btn-success btn-lg'
                    ]) ?>
                    
                    <?= $this->Html->link(
                        __('Cancel'),
                        ['action' => 'choosePath'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Form->end() ?>

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

.icon-circle.create-new {
    background: linear-gradient(135deg, #4caf50 0%, #2e7d32 100%);
}

.font-monospace {
    font-family: 'Courier New', Courier, monospace;
    font-size: 0.9rem;
    line-height: 1.4;
}

.nav-tabs .nav-link {
    border-bottom-color: transparent;
}

.nav-tabs .nav-link.active {
    background-color: #f8f9fa;
    border-color: #dee2e6 #dee2e6 #f8f9fa;
}

/* Code editor styling */
textarea[data-language] {
    background-color: #2d3748;
    color: #e2e8f0;
    border: 1px solid #4a5568;
}

textarea[data-language]:focus {
    background-color: #2d3748;
    color: #e2e8f0;
    border-color: #4299e1;
    box-shadow: 0 0 0 0.2rem rgba(66, 153, 225, 0.25);
}

/* File upload styling */
input[type="file"] {
    padding: 0.375rem 0.75rem;
}

/* Preview styling */
#page-preview {
    max-height: 400px;
    overflow-y: auto;
}

/* Custom preview content styling */
#page-preview.has-content {
    background-color: white !important;
    border: 1px solid #dee2e6;
}

#page-preview .preview-content {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    line-height: 1.6;
}

#page-preview .preview-content h1,
#page-preview .preview-content h2,
#page-preview .preview-content h3 {
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    color: #333;
}

#page-preview .preview-content p {
    margin-bottom: 1rem;
    color: #555;
}

/* Dark theme adjustments */
@media (prefers-color-scheme: dark) {
    #page-preview {
        background-color: var(--bs-gray-800) !important;
        color: var(--bs-light);
    }
}
</style>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');
    const fullUrlPreview = document.getElementById('full-url-preview');
    const baseUrl = '<?= $this->Url->build('/', ['fullBase' => true]) ?>pages/';
    
    function generateSlug(text) {
        return text
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
    
    function updateUrlPreview() {
        const slug = slugInput.value || 'page-slug';
        fullUrlPreview.textContent = baseUrl + slug;
    }
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.manuallyEdited) {
                const slug = generateSlug(this.value);
                slugInput.value = slug;
                updateUrlPreview();
                validateSlug();
            }
            updatePreview();
        });
        
        slugInput.addEventListener('input', function() {
            this.dataset.manuallyEdited = 'true';
            updateUrlPreview();
            validateSlug();
        });
        
        slugInput.addEventListener('blur', function() {
            this.value = generateSlug(this.value);
            updateUrlPreview();
            validateSlug();
        });
    }
    
    // Slug validation
    function validateSlug() {
        const slug = slugInput.value;
        const existingSlugs = <?= json_encode(array_column($existingSlugs, 'slug')) ?>;
        
        slugInput.setCustomValidity('');
        
        if (slug && existingSlugs.includes(slug)) {
            slugInput.setCustomValidity('<?= __('This slug is already in use. Please choose a different one.') ?>');
        }
    }
    
    // Character counters
    const metaTitle = document.querySelector('input[name="meta_title"]');
    const metaDescription = document.querySelector('textarea[name="meta_description"]');
    
    if (metaTitle) {
        const counter = document.getElementById('meta-title-count');
        metaTitle.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.className = this.value.length > 60 ? 'text-danger' : 'text-muted';
        });
        
        // Initial count
        counter.textContent = metaTitle.value.length;
    }
    
    if (metaDescription) {
        const counter = document.getElementById('meta-description-count');
        metaDescription.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.className = this.value.length > 160 ? 'text-danger' : 'text-muted';
        });
        
        // Initial count
        counter.textContent = metaDescription.value.length;
    }
    
    // Live preview functionality
    function updatePreview() {
        const title = titleInput.value;
        const body = document.querySelector('textarea[name="body"]').value;
        const customHtml = document.querySelector('textarea[name="custom_html"]').value;
        const customCss = document.querySelector('textarea[name="custom_css"]').value;
        const preview = document.getElementById('page-preview');
        
        if (title || body || customHtml) {
            preview.classList.add('has-content');
            
            let previewContent = '<div class="preview-content">';
            
            if (title) {
                previewContent += `<h1>${title}</h1>`;
            }
            
            if (body) {
                previewContent += body;
            }
            
            if (customHtml) {
                previewContent += customHtml;
            }
            
            previewContent += '</div>';
            
            // Apply custom CSS if available
            if (customCss) {
                previewContent += `<style scoped>${customCss}</style>`;
            }
            
            preview.innerHTML = previewContent;
        } else {
            preview.classList.remove('has-content');
            preview.innerHTML = `
                <div class="text-muted text-center py-4">
                    <i class="bi bi-eye-slash display-4"></i>
                    <p class="mb-0"><?= __('Preview will appear here as you type') ?></p>
                </div>
            `;
        }
    }
    
    // Add event listeners for real-time preview
    const previewFields = [
        'textarea[name="body"]',
        'textarea[name="custom_html"]',
        'textarea[name="custom_css"]'
    ];
    
    previewFields.forEach(selector => {
        const field = document.querySelector(selector);
        if (field) {
            field.addEventListener('input', updatePreview);
        }
    });
    
    // File upload feedback
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const fileType = this.dataset.fileType;
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const content = e.target.result;
                    
                    // Preview file content in corresponding textarea
                    const textarea = document.querySelector(`textarea[name="custom_${fileType}"]`);
                    if (textarea && content.trim()) {
                        textarea.value += '\n\n/* From uploaded file: ' + file.name + ' */\n' + content;
                        updatePreview();
                    }
                };
                
                reader.readAsText(file);
            }
        });
    });
    
    // Initialize preview
    updatePreview();
    updateUrlPreview();
});
<?php $this->Html->scriptEnd(); ?>