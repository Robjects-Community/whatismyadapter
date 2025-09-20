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
                <i class="bi bi-pencil me-2"></i>
                <?= __('Edit Page: {0}', h($page->title)) ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Pages'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<?= $this->Form->create($page, ['class' => 'needs-validation', 'novalidate' => true]) ?>

<div class="row">
    <div class="col-lg-8">
        <!-- Main Content -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-text me-2"></i>
                    <?= __('Page Content') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('title', [
                            'label' => ['text' => __('Page Title'), 'class' => 'form-label'],
                            'class' => 'form-control form-control-lg',
                            'placeholder' => __('Enter page title'),
                            'required' => true,
                            'data-slug-source' => true
                        ]) ?>
                        <div class="form-text">
                            <?= __('This will be displayed as the main heading on your page.') ?>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <?= $this->Form->control('slug', [
                            'label' => ['text' => __('Page URL Slug'), 'class' => 'form-label'],
                            'class' => 'form-control',
                            'placeholder' => __('page-url-slug'),
                            'required' => true,
                            'data-slug-target' => true,
                            'pattern' => '^[a-z0-9]+(?:-[a-z0-9]+)*$'
                        ]) ?>
                        <div class="form-text">
                            <?= __('URL-friendly version of the title. Only lowercase letters, numbers, and hyphens allowed.') ?>
                            <br>
                            <strong><?= __('Full URL:') ?></strong> <code id="full-url-preview"><?= $this->Url->build('/', ['fullBase' => true]) ?>en/pages/</code>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <?= $this->Form->control('body', [
                        'type' => 'textarea',
                        'label' => ['text' => __('Page Content'), 'class' => 'form-label'],
                        'class' => 'form-control',
                        'rows' => 15,
                        'placeholder' => __('Write your page content here. You can use HTML tags.')
                    ]) ?>
                    <div class="form-text">
                        <?= __('HTML is allowed. Use the preview function to see how your content will look.') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEO Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-search me-2"></i>
                    <?= __('SEO Settings') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <?= $this->Form->control('meta_title', [
                        'label' => ['text' => __('Meta Title'), 'class' => 'form-label'],
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
                        'label' => ['text' => __('Meta Description'), 'class' => 'form-label'],
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
                        'label' => ['text' => __('Meta Keywords'), 'class' => 'form-label'],
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
        <div class="card mb-4">
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

        <!-- Page Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-eye me-2"></i>
                    <?= __('Preview') ?>
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
                    <small class="text-muted"><?= __('Live preview of your content') ?></small>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="card">
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?= $this->Form->submit(__('Update Page'), [
                        'class' => 'btn btn-primary btn-lg'
                    ]) ?>
                    
                    <?= $this->Html->link(
                        __('Cancel'),
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-secondary']
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->Form->end() ?>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from title
    const titleInput = document.querySelector('[data-slug-source]');
    const slugInput = document.querySelector('[data-slug-target]');
    const fullUrlPreview = document.getElementById('full-url-preview');
    const baseUrl = '<?= $this->Url->build('/', ['fullBase' => true]) ?>en/pages/';
    
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
    }
    
    if (metaDescription) {
        const counter = document.getElementById('meta-description-count');
        metaDescription.addEventListener('input', function() {
            counter.textContent = this.value.length;
            counter.className = this.value.length > 160 ? 'text-danger' : 'text-muted';
        });
    }
    
    // Live preview
    function updatePreview() {
        const title = document.querySelector('input[name="title"]').value;
        const content = document.querySelector('textarea[name="body"]').value;
        const preview = document.getElementById('page-preview');
        
        if (title || content) {
            let html = '';
            if (title) {
                html += '<h1>' + escapeHtml(title) + '</h1>';
            }
            if (content) {
                html += content; // Content can contain HTML
            }
            preview.innerHTML = html || '<div class="text-muted text-center py-4"><i class="bi bi-eye-slash display-4"></i><p class="mb-0"><?= __('Preview will appear here as you type') ?></p></div>';
        } else {
            preview.innerHTML = '<div class="text-muted text-center py-4"><i class="bi bi-eye-slash display-4"></i><p class="mb-0"><?= __('Preview will appear here as you type') ?></p></div>';
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Add preview update listeners
    document.querySelector('input[name="title"]').addEventListener('input', updatePreview);
    document.querySelector('textarea[name="body"]').addEventListener('input', updatePreview);
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Initialize
    updateUrlPreview();
    updatePreview();
});
<?php $this->Html->scriptEnd(); ?>