<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 */
?>
<?php
// Only show actions if we have an entity (edit mode)
if (!$product->isNew()) {
    echo $this->element('actions_card', [
        'modelName' => 'Product',
        'controllerName' => 'Products',
        'entity' => $product,
        'entityDisplayName' => $product->title
    ]);
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Product') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($product,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('user_id', ['options' => $users, 'class' => 'form-select' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('user_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('user_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('article_id', ['options' => $articles, 'empty' => true, 'class' => 'form-select' . ($this->Form->isFieldError('article_id') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('article_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('article_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('title', ['class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('title')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('title') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('slug', ['class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('slug')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('slug') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('description', ['class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('manufacturer', ['class' => 'form-control' . ($this->Form->isFieldError('manufacturer') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('manufacturer')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('manufacturer') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('model_number', ['class' => 'form-control' . ($this->Form->isFieldError('model_number') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('model_number')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('model_number') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('price', ['class' => 'form-control' . ($this->Form->isFieldError('price') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('price')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('price') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('currency', ['class' => 'form-control' . ($this->Form->isFieldError('currency') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('currency')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('currency') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('image', ['class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('image')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('image') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('alt_text', ['class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('alt_text')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('alt_text') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('is_published', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('is_published') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-published">
                                    <?= __('Is Published') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('is_published')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('is_published') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('featured', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('featured') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="featured">
                                    <?= __('Featured') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('featured')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('featured') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('verification_status', ['class' => 'form-control' . ($this->Form->isFieldError('verification_status') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('verification_status')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('verification_status') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('reliability_score', ['class' => 'form-control' . ($this->Form->isFieldError('reliability_score') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('reliability_score')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('reliability_score') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('view_count', ['class' => 'form-control' . ($this->Form->isFieldError('view_count') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('view_count')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('view_count') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('created')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('created') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('modified')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('modified') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                                                                                        <div class="mb-3">
                            <?php echo $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('tags._ids')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('tags._ids') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Capability Fields -->
                        <div class="card mt-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-cogs"></i> <?= __('Capability Information') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('capability_name', ['class' => 'form-control' . ($this->Form->isFieldError('capability_name') ? ' is-invalid' : ''), 'label' => __('Capability Name')]) ?>
                                            <?php if ($this->Form->isFieldError('capability_name')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('capability_name') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('capability_category', ['class' => 'form-control' . ($this->Form->isFieldError('capability_category') ? ' is-invalid' : ''), 'label' => __('Capability Category')]) ?>
                                            <?php if ($this->Form->isFieldError('capability_category')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('capability_category') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('capability_value', ['class' => 'form-control' . ($this->Form->isFieldError('capability_value') ? ' is-invalid' : ''), 'label' => __('Capability Value')]) ?>
                                            <?php if ($this->Form->isFieldError('capability_value')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('capability_value') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('testing_standard', ['class' => 'form-control' . ($this->Form->isFieldError('testing_standard') ? ' is-invalid' : ''), 'label' => __('Testing Standard')]) ?>
                                            <?php if ($this->Form->isFieldError('testing_standard')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('testing_standard') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('certifying_organization', ['class' => 'form-control' . ($this->Form->isFieldError('certifying_organization') ? ' is-invalid' : ''), 'label' => __('Certifying Organization')]) ?>
                                            <?php if ($this->Form->isFieldError('certifying_organization')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('certifying_organization') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('numeric_rating', ['type' => 'number', 'step' => '0.001', 'class' => 'form-control' . ($this->Form->isFieldError('numeric_rating') ? ' is-invalid' : ''), 'label' => __('Numeric Rating')]) ?>
                                            <?php if ($this->Form->isFieldError('numeric_rating')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('numeric_rating') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('technical_specifications', ['type' => 'textarea', 'rows' => 4, 'class' => 'form-control' . ($this->Form->isFieldError('technical_specifications') ? ' is-invalid' : ''), 'label' => __('Technical Specifications (JSON)')]) ?>
                                    <?php if ($this->Form->isFieldError('technical_specifications')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('technical_specifications') ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted"><?= __('Enter as JSON format for structured technical data') ?></small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Port/Connector Fields -->
                        <div class="card mt-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-plug"></i> <?= __('Port & Connector Information') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <?= $this->Form->control('port_family', ['type' => 'select', 'empty' => __('Select Port Family'), 'options' => ['USB' => 'USB', 'HDMI' => 'HDMI', 'DisplayPort' => 'DisplayPort', 'Audio' => 'Audio', 'Video' => 'Video', 'Power' => 'Power', 'Data' => 'Data'], 'class' => 'form-select' . ($this->Form->isFieldError('port_family') ? ' is-invalid' : ''), 'label' => __('Port Family')]) ?>
                                            <?php if ($this->Form->isFieldError('port_family')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('port_family') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('port_type_name', ['class' => 'form-control' . ($this->Form->isFieldError('port_type_name') ? ' is-invalid' : ''), 'label' => __('Port Type Name')]) ?>
                                            <?php if ($this->Form->isFieldError('port_type_name')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('port_type_name') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('form_factor', ['type' => 'select', 'empty' => __('Select Form Factor'), 'options' => ['Standard' => 'Standard', 'Mini' => 'Mini', 'Micro' => 'Micro', 'Type-A' => 'Type-A', 'Type-B' => 'Type-B', 'Type-C' => 'Type-C'], 'class' => 'form-select' . ($this->Form->isFieldError('form_factor') ? ' is-invalid' : ''), 'label' => __('Form Factor')]) ?>
                                            <?php if ($this->Form->isFieldError('form_factor')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('form_factor') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('connector_gender', ['type' => 'select', 'empty' => __('Select Gender'), 'options' => ['Male' => 'Male', 'Female' => 'Female', 'Reversible' => 'Reversible'], 'class' => 'form-select' . ($this->Form->isFieldError('connector_gender') ? ' is-invalid' : ''), 'label' => __('Connector Gender')]) ?>
                                            <?php if ($this->Form->isFieldError('connector_gender')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('connector_gender') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <?= $this->Form->control('pin_count', ['type' => 'number', 'min' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('pin_count') ? ' is-invalid' : ''), 'label' => __('Pin Count')]) ?>
                                            <?php if ($this->Form->isFieldError('pin_count')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('pin_count') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('data_pin_count', ['type' => 'number', 'min' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('data_pin_count') ? ' is-invalid' : ''), 'label' => __('Data Pin Count')]) ?>
                                            <?php if ($this->Form->isFieldError('data_pin_count')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('data_pin_count') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('power_pin_count', ['type' => 'number', 'min' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('power_pin_count') ? ' is-invalid' : ''), 'label' => __('Power Pin Count')]) ?>
                                            <?php if ($this->Form->isFieldError('power_pin_count')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('power_pin_count') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('ground_pin_count', ['type' => 'number', 'min' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('ground_pin_count') ? ' is-invalid' : ''), 'label' => __('Ground Pin Count')]) ?>
                                            <?php if ($this->Form->isFieldError('ground_pin_count')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('ground_pin_count') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <?= $this->Form->control('endpoint_position', ['type' => 'select', 'empty' => __('Select Position'), 'options' => ['end_a' => 'End A', 'end_b' => 'End B'], 'class' => 'form-select' . ($this->Form->isFieldError('endpoint_position') ? ' is-invalid' : ''), 'label' => __('Endpoint Position')]) ?>
                                            <?php if ($this->Form->isFieldError('endpoint_position')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('endpoint_position') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <?= $this->Form->checkbox('is_detachable', ['class' => 'form-check-input' . ($this->Form->isFieldError('is_detachable') ? ' is-invalid' : '')]) ?>
                                                <label class="form-check-label" for="is-detachable"><?= __('Is Detachable') ?></label>
                                            </div>
                                            <?php if ($this->Form->isFieldError('is_detachable')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('is_detachable') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('electrical_shielding', ['class' => 'form-control' . ($this->Form->isFieldError('electrical_shielding') ? ' is-invalid' : ''), 'label' => __('Electrical Shielding')]) ?>
                                            <?php if ($this->Form->isFieldError('electrical_shielding')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('electrical_shielding') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('durability_cycles', ['type' => 'number', 'min' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('durability_cycles') ? ' is-invalid' : ''), 'label' => __('Durability Cycles')]) ?>
                                            <?php if ($this->Form->isFieldError('durability_cycles')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('durability_cycles') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('adapter_functionality', ['type' => 'textarea', 'rows' => 3, 'class' => 'form-control' . ($this->Form->isFieldError('adapter_functionality') ? ' is-invalid' : ''), 'label' => __('Adapter Functionality')]) ?>
                                    <?php if ($this->Form->isFieldError('adapter_functionality')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('adapter_functionality') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Device Compatibility Fields -->
                        <div class="card mt-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-laptop"></i> <?= __('Device Compatibility') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('device_category', ['type' => 'select', 'empty' => __('Select Device Category'), 'options' => ['Smartphone' => 'Smartphone', 'Tablet' => 'Tablet', 'Laptop' => 'Laptop', 'Desktop' => 'Desktop', 'TV' => 'TV', 'Monitor' => 'Monitor', 'Audio' => 'Audio', 'Camera' => 'Camera'], 'class' => 'form-select' . ($this->Form->isFieldError('device_category') ? ' is-invalid' : ''), 'label' => __('Device Category')]) ?>
                                            <?php if ($this->Form->isFieldError('device_category')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('device_category') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('device_brand', ['class' => 'form-control' . ($this->Form->isFieldError('device_brand') ? ' is-invalid' : ''), 'label' => __('Device Brand')]) ?>
                                            <?php if ($this->Form->isFieldError('device_brand')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('device_brand') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('device_model', ['class' => 'form-control' . ($this->Form->isFieldError('device_model') ? ' is-invalid' : ''), 'label' => __('Device Model')]) ?>
                                            <?php if ($this->Form->isFieldError('device_model')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('device_model') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('compatibility_level', ['type' => 'select', 'empty' => __('Select Compatibility'), 'options' => ['Full' => 'Full', 'Partial' => 'Partial', 'Limited' => 'Limited', 'Incompatible' => 'Incompatible'], 'class' => 'form-select' . ($this->Form->isFieldError('compatibility_level') ? ' is-invalid' : ''), 'label' => __('Compatibility Level')]) ?>
                                            <?php if ($this->Form->isFieldError('compatibility_level')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('compatibility_level') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('performance_rating', ['type' => 'number', 'step' => '0.01', 'min' => '0', 'max' => '9.99', 'class' => 'form-control' . ($this->Form->isFieldError('performance_rating') ? ' is-invalid' : ''), 'label' => __('Performance Rating')]) ?>
                                            <?php if ($this->Form->isFieldError('performance_rating')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('performance_rating') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('user_reported_rating', ['type' => 'number', 'step' => '0.01', 'min' => '0', 'max' => '9.99', 'class' => 'form-control' . ($this->Form->isFieldError('user_reported_rating') ? ' is-invalid' : ''), 'label' => __('User Reported Rating')]) ?>
                                            <?php if ($this->Form->isFieldError('user_reported_rating')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('user_reported_rating') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('verification_date', ['type' => 'date', 'class' => 'form-control' . ($this->Form->isFieldError('verification_date') ? ' is-invalid' : ''), 'label' => __('Verification Date')]) ?>
                                            <?php if ($this->Form->isFieldError('verification_date')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('verification_date') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('verified_by', ['class' => 'form-control' . ($this->Form->isFieldError('verified_by') ? ' is-invalid' : ''), 'label' => __('Verified By')]) ?>
                                            <?php if ($this->Form->isFieldError('verified_by')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('verified_by') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('compatibility_notes', ['type' => 'textarea', 'rows' => 3, 'class' => 'form-control' . ($this->Form->isFieldError('compatibility_notes') ? ' is-invalid' : ''), 'label' => __('Compatibility Notes')]) ?>
                                    <?php if ($this->Form->isFieldError('compatibility_notes')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('compatibility_notes') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Physical Specifications -->
                        <div class="card mt-4">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fas fa-ruler"></i> <?= __('Physical Specifications') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('physical_spec_name', ['class' => 'form-control' . ($this->Form->isFieldError('physical_spec_name') ? ' is-invalid' : ''), 'label' => __('Physical Spec Name')]) ?>
                                            <?php if ($this->Form->isFieldError('physical_spec_name')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('physical_spec_name') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('spec_type', ['type' => 'select', 'empty' => __('Select Type'), 'options' => ['measurement' => 'Measurement', 'material' => 'Material', 'rating' => 'Rating', 'boolean' => 'Boolean', 'text' => 'Text'], 'class' => 'form-select' . ($this->Form->isFieldError('spec_type') ? ' is-invalid' : ''), 'label' => __('Spec Type')]) ?>
                                            <?php if ($this->Form->isFieldError('spec_type')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('spec_type') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('spec_value', ['class' => 'form-control' . ($this->Form->isFieldError('spec_value') ? ' is-invalid' : ''), 'label' => __('Spec Value')]) ?>
                                            <?php if ($this->Form->isFieldError('spec_value')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('spec_value') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('numeric_value', ['type' => 'number', 'step' => '0.001', 'class' => 'form-control' . ($this->Form->isFieldError('numeric_value') ? ' is-invalid' : ''), 'label' => __('Numeric Value')]) ?>
                                            <?php if ($this->Form->isFieldError('numeric_value')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('numeric_value') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('measurement_unit', ['type' => 'select', 'empty' => __('Select Unit'), 'options' => ['mm' => 'mm', 'cm' => 'cm', 'm' => 'm', 'kg' => 'kg', 'g' => 'g', 'V' => 'V', 'A' => 'A', 'W' => 'W', 'Hz' => 'Hz'], 'class' => 'form-select' . ($this->Form->isFieldError('measurement_unit') ? ' is-invalid' : ''), 'label' => __('Measurement Unit')]) ?>
                                            <?php if ($this->Form->isFieldError('measurement_unit')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('measurement_unit') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('max_voltage', ['type' => 'number', 'step' => '0.01', 'min' => '0', 'class' => 'form-control' . ($this->Form->isFieldError('max_voltage') ? ' is-invalid' : ''), 'label' => __('Max Voltage (V)')]) ?>
                                            <?php if ($this->Form->isFieldError('max_voltage')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('max_voltage') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('max_current', ['type' => 'number', 'step' => '0.01', 'min' => '0', 'class' => 'form-control' . ($this->Form->isFieldError('max_current') ? ' is-invalid' : ''), 'label' => __('Max Current (A)')]) ?>
                                            <?php if ($this->Form->isFieldError('max_current')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('max_current') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('physical_specs_summary', ['class' => 'form-control' . ($this->Form->isFieldError('physical_specs_summary') ? ' is-invalid' : ''), 'label' => __('Physical Specs Summary')]) ?>
                                            <?php if ($this->Form->isFieldError('physical_specs_summary')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('physical_specs_summary') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('introduced_date', ['type' => 'date', 'class' => 'form-control' . ($this->Form->isFieldError('introduced_date') ? ' is-invalid' : ''), 'label' => __('Introduced Date')]) ?>
                                            <?php if ($this->Form->isFieldError('introduced_date')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('introduced_date') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('deprecated_date', ['type' => 'date', 'class' => 'form-control' . ($this->Form->isFieldError('deprecated_date') ? ' is-invalid' : ''), 'label' => __('Deprecated Date')]) ?>
                                            <?php if ($this->Form->isFieldError('deprecated_date')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('deprecated_date') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('spec_description', ['type' => 'textarea', 'rows' => 4, 'class' => 'form-control' . ($this->Form->isFieldError('spec_description') ? ' is-invalid' : ''), 'label' => __('Specification Description')]) ?>
                                    <?php if ($this->Form->isFieldError('spec_description')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('spec_description') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Category & Administrative Fields -->
                        <div class="card mt-4">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="mb-0"><i class="fas fa-tags"></i> <?= __('Category & Administrative') ?></h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('parent_category_name', ['class' => 'form-control' . ($this->Form->isFieldError('parent_category_name') ? ' is-invalid' : ''), 'label' => __('Parent Category Name')]) ?>
                                            <?php if ($this->Form->isFieldError('parent_category_name')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('parent_category_name') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('category_icon', ['class' => 'form-control' . ($this->Form->isFieldError('category_icon') ? ' is-invalid' : ''), 'label' => __('Category Icon (CSS class)')]) ?>
                                            <?php if ($this->Form->isFieldError('category_icon')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('category_icon') ?></div>
                                            <?php endif; ?>
                                            <small class="form-text text-muted"><?= __('Example: fas fa-plug, fas fa-usb') ?></small>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('display_order', ['type' => 'number', 'min' => 0, 'default' => 0, 'class' => 'form-control' . ($this->Form->isFieldError('display_order') ? ' is-invalid' : ''), 'label' => __('Display Order')]) ?>
                                            <?php if ($this->Form->isFieldError('display_order')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('display_order') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <?= $this->Form->checkbox('is_certified', ['class' => 'form-check-input' . ($this->Form->isFieldError('is_certified') ? ' is-invalid' : '')]) ?>
                                                <label class="form-check-label" for="is-certified"><?= __('Is Certified') ?></label>
                                            </div>
                                            <?php if ($this->Form->isFieldError('is_certified')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('is_certified') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <?= $this->Form->control('certification_date', ['type' => 'date', 'class' => 'form-control' . ($this->Form->isFieldError('certification_date') ? ' is-invalid' : ''), 'label' => __('Certification Date')]) ?>
                                            <?php if ($this->Form->isFieldError('certification_date')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('certification_date') ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <?= $this->Form->checkbox('needs_normalization', ['class' => 'form-check-input' . ($this->Form->isFieldError('needs_normalization') ? ' is-invalid' : ''), 'default' => true]) ?>
                                                <label class="form-check-label" for="needs-normalization"><?= __('Needs Normalization') ?></label>
                                            </div>
                                            <?php if ($this->Form->isFieldError('needs_normalization')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('needs_normalization') ?></div>
                                            <?php endif; ?>
                                            <small class="form-text text-muted"><?= __('Check if this product data needs to be normalized into separate tables') ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('category_description', ['type' => 'textarea', 'rows' => 3, 'class' => 'form-control' . ($this->Form->isFieldError('category_description') ? ' is-invalid' : ''), 'label' => __('Category Description')]) ?>
                                    <?php if ($this->Form->isFieldError('category_description')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('category_description') ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('prototype_notes', ['type' => 'textarea', 'rows' => 4, 'class' => 'form-control' . ($this->Form->isFieldError('prototype_notes') ? ' is-invalid' : ''), 'label' => __('Prototype Notes')]) ?>
                                    <?php if ($this->Form->isFieldError('prototype_notes')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('prototype_notes') ?></div>
                                    <?php endif; ?>
                                    <small class="form-text text-muted"><?= __('Development notes and observations about this product prototype') ?></small>
                                </div>
                            </div>
                        </div>
                                                                                
                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>