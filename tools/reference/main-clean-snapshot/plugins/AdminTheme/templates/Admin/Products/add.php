<?php
$this->assign('title', isset($product->id) ? __('Edit Product') : __('Add Product'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="actions-card">
            <h3><?= isset($product->id) ? __('Edit Product') : __('Add Product') ?></h3>
            <div class="actions">
                <?= $this->Html->link(
                    '<i class="fas fa-list"></i> ' . __('List Products'),
                    ['action' => 'index'],
                    ['class' => 'btn btn-secondary', 'escape' => false]
                ) ?>
                <?php if (isset($product->id)): ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-eye"></i> ' . __('View'),
                        ['action' => 'view', $product->id],
                        ['class' => 'btn btn-info', 'escape' => false]
                    ) ?>
                    <?= $this->Form->postLink(
                        '<i class="fas fa-trash"></i> ' . __('Delete'),
                        ['action' => 'delete', $product->id],
                        [
                            'confirm' => __('Are you sure you want to delete {0}?', $product->title),
                            'class' => 'btn btn-danger',
                            'escape' => false
                        ]
                    ) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <?= $this->Form->create($product, [
                    'type' => 'file',
                    'novalidate' => true
                ]) ?>
                
                <div class="form-group">
                    <?= $this->Form->control('title', [
                        'class' => 'form-control',
                        'required' => true
                    ]) ?>
                </div>

                <div class="form-group">
                    <?= $this->Form->control('slug', [
                        'class' => 'form-control',
                        'help' => __('URL-friendly version of the title. Leave blank to auto-generate.')
                    ]) ?>
                </div>

                <div class="form-group">
                    <?= $this->Form->control('description', [
                        'type' => 'textarea',
                        'class' => 'form-control',
                        'rows' => 6,
                        'help' => __('Brief description of the product')
                    ]) ?>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= $this->Form->control('manufacturer', [
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <?= $this->Form->control('model_number', [
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <?= $this->Form->control('price', [
                                'type' => 'number',
                                'step' => '0.01',
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <?= $this->Form->control('currency', [
                                'type' => 'select',
                                'options' => [
                                    'USD' => 'USD',
                                    'EUR' => 'EUR',
                                    'GBP' => 'GBP',
                                    'CAD' => 'CAD'
                                ],
                                'class' => 'form-control'
                            ]) ?>
                        </div>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="form-group">
                    <?= $this->Form->label('image_uploads', __('Product Images')) ?>
                    <?= $this->Form->file('image_uploads[]', [
                        'multiple' => true,
                        'accept' => 'image/*',
                        'class' => 'form-control-file'
                    ]) ?>
                    <small class="form-text text-muted"><?= __('Upload product images (JPG, PNG, GIF)') ?></small>
                </div>

                <?php if ($product->image): ?>
                    <div class="current-image mb-3">
                        <label><?= __('Current Image') ?></label>
                        <div>
                            <img src="<?= h($product->image) ?>" alt="<?= h($product->alt_text) ?>" 
                                 class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <?= $this->Form->control('alt_text', [
                        'class' => 'form-control',
                        'help' => __('Alternative text for images (accessibility)')
                    ]) ?>
                </div>

                <?= $this->Form->button(__('Save Product'), [
                    'class' => 'btn btn-success'
                ]) ?>
                
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Publication Settings -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><?= __('Publication') ?></h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <?= $this->Form->control('is_published', [
                        'type' => 'checkbox',
                        'label' => __('Published')
                    ]) ?>
                </div>
                
                <div class="form-group">
                    <?= $this->Form->control('featured', [
                        'type' => 'checkbox',
                        'label' => __('Featured Product')
                    ]) ?>
                </div>

                <div class="form-group">
                    <?= $this->Form->control('verification_status', [
                        'type' => 'select',
                        'options' => [
                            'pending' => __('Pending'),
                            'approved' => __('Approved'),
                            'rejected' => __('Rejected')
                        ],
                        'class' => 'form-control'
                    ]) ?>
                </div>
            </div>
        </div>

        <!-- Tags -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><?= __('Tags') ?></h5>
            </div>
            <div class="card-body">
                <?= $this->Form->control('tags._ids', [
                    'type' => 'select',
                    'multiple' => true,
                    'options' => $tags,
                    'class' => 'form-control select2',
                    'label' => false
                ]) ?>
                <small class="form-text text-muted"><?= __('Select tags for unified search across articles and products') ?></small>
            </div>
        </div>

        <!-- Article Association -->
        <div class="card mb-3">
            <div class="card-header">
                <h5><?= __('Detailed Article') ?></h5>
            </div>
            <div class="card-body">
                <?= $this->Form->control('article_id', [
                    'type' => 'select',
                    'options' => $articles,
                    'empty' => __('None - No detailed article'),
                    'class' => 'form-control',
                    'label' => false
                ]) ?>
                <small class="form-text text-muted"><?= __('Optional: Link to a detailed article about this product') ?></small>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Select2 for tags
    $('.select2').select2({
        placeholder: '<?= __("Select tags...") ?>',
        allowClear: true
    });
    
    // Auto-generate slug from title
    $('#title').on('input', function() {
        if ($('#slug').val() === '') {
            var slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $('#slug').val(slug);
        }
    });
});
</script>
