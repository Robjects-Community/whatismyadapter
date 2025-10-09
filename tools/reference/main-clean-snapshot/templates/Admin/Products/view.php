<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Product',
    'controllerName' => 'Products',
    'entity' => $product,
    'entityDisplayName' => $product->title
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($product->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($product->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Article') ?></th>
                            <td><?= $product->hasValue('article') ? $this->Html->link($product->article->title, ['controller' => 'Articles', 'action' => 'view', $product->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($product->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($product->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Manufacturer') ?></th>
                            <td><?= h($product->manufacturer) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Model Number') ?></th>
                            <td><?= h($product->model_number) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Currency') ?></th>
                            <td><?= h($product->currency) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td><?= h($product->image) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Alt Text') ?></th>
                            <td><?= h($product->alt_text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Verification Status') ?></th>
                            <td><?= h($product->verification_status) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Price') ?></th>
                            <td><?= $product->price === null ? '' : $this->Number->format($product->price) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Reliability Score') ?></th>
                            <td><?= $product->reliability_score === null ? '' : $this->Number->format($product->reliability_score) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('View Count') ?></th>
                            <td><?= $this->Number->format($product->view_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($product->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($product->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Featured') ?></th>
                            <td><?= $product->featured ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        
                        <!-- Core Product Fields -->
                        <?php if ($product->capability_name): ?>
                        <tr>
                            <th><?= __('Capability Name') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->capability_name),
                                    ['controller' => 'CableCapabilities', 'action' => 'index', '?' => ['search' => $product->capability_name]],
                                    ['class' => 'badge bg-primary text-decoration-none', 'title' => __('View related capabilities')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->capability_category): ?>
                        <tr>
                            <th><?= __('Capability Category') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->capability_category),
                                    ['controller' => 'CableCapabilities', 'action' => 'category', $product->capability_category],
                                    ['class' => 'badge bg-info text-decoration-none', 'title' => __('View same category')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->port_family): ?>
                        <tr>
                            <th><?= __('Port Family') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->port_family),
                                    ['controller' => 'PortTypes', 'action' => 'family', $product->port_family],
                                    ['class' => 'badge bg-success text-decoration-none', 'title' => __('View port family')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->port_type_name): ?>
                        <tr>
                            <th><?= __('Port Type Name') ?></th>
                            <td><?= h($product->port_type_name) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->form_factor): ?>
                        <tr>
                            <th><?= __('Form Factor') ?></th>
                            <td><?= h($product->form_factor) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->connector_gender): ?>
                        <tr>
                            <th><?= __('Connector Gender') ?></th>
                            <td><?= h($product->connector_gender) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->device_category): ?>
                        <tr>
                            <th><?= __('Device Category') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->device_category),
                                    ['controller' => 'DeviceCompatibility', 'action' => 'category', $product->device_category],
                                    ['class' => 'badge bg-warning text-dark text-decoration-none', 'title' => __('View device category')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->device_brand): ?>
                        <tr>
                            <th><?= __('Device Brand') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->device_brand),
                                    ['controller' => 'DeviceCompatibility', 'action' => 'brand', $product->device_brand],
                                    ['class' => 'badge bg-secondary text-decoration-none', 'title' => __('View device brand')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->device_model): ?>
                        <tr>
                            <th><?= __('Device Model') ?></th>
                            <td><?= h($product->device_model) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->compatibility_level): ?>
                        <tr>
                            <th><?= __('Compatibility Level') ?></th>
                            <td>
                                <?php 
                                $compatClass = [
                                    'Full' => 'success',
                                    'Partial' => 'warning', 
                                    'Limited' => 'info',
                                    'Incompatible' => 'danger'
                                ][$product->compatibility_level] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $compatClass ?>"><?= h($product->compatibility_level) ?></span>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->physical_spec_name): ?>
                        <tr>
                            <th><?= __('Physical Spec Name') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->physical_spec_name),
                                    ['controller' => 'PhysicalSpecifications', 'action' => 'index', '?' => ['search' => $product->physical_spec_name]],
                                    ['class' => 'badge bg-dark text-decoration-none', 'title' => __('View physical specifications')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->spec_type): ?>
                        <tr>
                            <th><?= __('Spec Type') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->spec_type),
                                    ['controller' => 'PhysicalSpecifications', 'action' => 'type', $product->spec_type],
                                    ['class' => 'badge bg-light text-dark text-decoration-none', 'title' => __('View spec type')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->spec_value): ?>
                        <tr>
                            <th><?= __('Spec Value') ?></th>
                            <td><?= h($product->spec_value) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->numeric_value): ?>
                        <tr>
                            <th><?= __('Numeric Value') ?></th>
                            <td><?= number_format($product->numeric_value, 3) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->measurement_unit): ?>
                        <tr>
                            <th><?= __('Measurement Unit') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->measurement_unit),
                                    ['controller' => 'PhysicalSpecifications', 'action' => 'unit', $product->measurement_unit],
                                    ['class' => 'badge bg-outline-secondary text-decoration-none', 'title' => __('View unit specifications')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->max_voltage): ?>
                        <tr>
                            <th><?= __('Max Voltage') ?></th>
                            <td><?= number_format($product->max_voltage, 2) ?> V</td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->max_current): ?>
                        <tr>
                            <th><?= __('Max Current') ?></th>
                            <td><?= number_format($product->max_current, 2) ?> A</td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->pin_count): ?>
                        <tr>
                            <th><?= __('Pin Count') ?></th>
                            <td><?= number_format($product->pin_count) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->data_pin_count): ?>
                        <tr>
                            <th><?= __('Data Pin Count') ?></th>
                            <td><?= number_format($product->data_pin_count) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->power_pin_count): ?>
                        <tr>
                            <th><?= __('Power Pin Count') ?></th>
                            <td><?= number_format($product->power_pin_count) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->ground_pin_count): ?>
                        <tr>
                            <th><?= __('Ground Pin Count') ?></th>
                            <td><?= number_format($product->ground_pin_count) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->endpoint_position): ?>
                        <tr>
                            <th><?= __('Endpoint Position') ?></th>
                            <td><?= h($product->endpoint_position) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product->is_detachable)): ?>
                        <tr>
                            <th><?= __('Is Detachable') ?></th>
                            <td><?= $product->is_detachable ? '<i class="fas fa-check text-success"></i> Yes' : '<i class="fas fa-times text-muted"></i> No' ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->electrical_shielding): ?>
                        <tr>
                            <th><?= __('Electrical Shielding') ?></th>
                            <td><?= h($product->electrical_shielding) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->durability_cycles): ?>
                        <tr>
                            <th><?= __('Durability Cycles') ?></th>
                            <td><?= number_format($product->durability_cycles) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->performance_rating): ?>
                        <tr>
                            <th><?= __('Performance Rating') ?></th>
                            <td><?= number_format($product->performance_rating, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->user_reported_rating): ?>
                        <tr>
                            <th><?= __('User Reported Rating') ?></th>
                            <td><?= number_format($product->user_reported_rating, 2) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->verification_date): ?>
                        <tr>
                            <th><?= __('Verification Date') ?></th>
                            <td><?= $product->verification_date->format('M j, Y') ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->verified_by): ?>
                        <tr>
                            <th><?= __('Verified By') ?></th>
                            <td><?= h($product->verified_by) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->parent_category_name): ?>
                        <tr>
                            <th><?= __('Parent Category Name') ?></th>
                            <td>
                                <?= $this->Html->link(
                                    h($product->parent_category_name),
                                    ['controller' => 'Categories', 'action' => 'parent', $product->parent_category_name],
                                    ['class' => 'badge bg-secondary text-decoration-none', 'title' => __('View category')]
                                ) ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->category_icon): ?>
                        <tr>
                            <th><?= __('Category Icon') ?></th>
                            <td><i class="<?= h($product->category_icon) ?>"></i> <?= h($product->category_icon) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->display_order): ?>
                        <tr>
                            <th><?= __('Display Order') ?></th>
                            <td><?= number_format($product->display_order) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product->is_certified)): ?>
                        <tr>
                            <th><?= __('Is Certified') ?></th>
                            <td>
                                <?= $product->is_certified ? '<i class="fas fa-certificate text-success"></i> Certified' : '<i class="fas fa-times text-muted"></i> Not Certified' ?>
                                <?php if ($product->certification_date): ?>
                                    <br><small class="text-muted">Certified: <?= $product->certification_date->format('M j, Y') ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->certifying_organization): ?>
                        <tr>
                            <th><?= __('Certifying Organization') ?></th>
                            <td><?= h($product->certifying_organization) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->testing_standard): ?>
                        <tr>
                            <th><?= __('Testing Standard') ?></th>
                            <td><?= h($product->testing_standard) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($product->needs_normalization)): ?>
                        <tr>
                            <th><?= __('Needs Normalization') ?></th>
                            <td><?= $product->needs_normalization ? '<i class="fas fa-exclamation-triangle text-warning"></i> Yes' : '<i class="fas fa-check text-success"></i> No' ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->introduced_date): ?>
                        <tr>
                            <th><?= __('Introduced Date') ?></th>
                            <td><?= $product->introduced_date->format('M j, Y') ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->deprecated_date): ?>
                        <tr>
                            <th><?= __('Deprecated Date') ?></th>
                            <td><?= $product->deprecated_date->format('M j, Y') ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <!-- Additional Description Fields -->
                        <?php if ($product->capability_value): ?>
                        <tr>
                            <th><?= __('Capability Value') ?></th>
                            <td><?= h($product->capability_value) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->physical_specs_summary): ?>
                        <tr>
                            <th><?= __('Physical Specs Summary') ?></th>
                            <td><?= h($product->physical_specs_summary) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->adapter_functionality): ?>
                        <tr>
                            <th><?= __('Adapter Functionality') ?></th>
                            <td><?= nl2br(h($product->adapter_functionality)) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->compatibility_notes): ?>
                        <tr>
                            <th><?= __('Compatibility Notes') ?></th>
                            <td><?= nl2br(h($product->compatibility_notes)) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->spec_description): ?>
                        <tr>
                            <th><?= __('Spec Description') ?></th>
                            <td><?= nl2br(h($product->spec_description)) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->category_description): ?>
                        <tr>
                            <th><?= __('Category Description') ?></th>
                            <td><?= nl2br(h($product->category_description)) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->prototype_notes): ?>
                        <tr>
                            <th><?= __('Prototype Notes') ?></th>
                            <td><?= nl2br(h($product->prototype_notes)) ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($product->technical_specifications): ?>
                        <tr>
                            <th><?= __('Technical Specifications') ?></th>
                            <td>
                                <details class="mt-2">
                                    <summary class="btn btn-outline-secondary btn-sm">View JSON Specifications</summary>
                                    <pre class="mt-2 bg-light p-3"><?= h(json_encode(json_decode($product->technical_specifications), JSON_PRETTY_PRINT)) ?></pre>
                                </details>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <!-- Capability Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-cogs"></i> <?= __('Capability Information') ?></h5>
                            <div class="btn-group" role="group">
                                <?php if ($product->capability_name || $product->capability_category): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-table me-1"></i>' . __('View All Capabilities'),
                                        ['controller' => 'CableCapabilities', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-light btn-sm',
                                            'escape' => false,
                                            'title' => __('View comprehensive cable capabilities data'),
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php if ($product->capability_category): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-filter me-1"></i>' . __('Same Category'),
                                            ['controller' => 'CableCapabilities', 'action' => 'category', $product->capability_category],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all products in {0} category', $product->capability_category),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->is_certified): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-certificate me-1"></i>' . __('Certified Only'),
                                            ['controller' => 'CableCapabilities', 'action' => 'certified'],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View only certified capabilities'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Capability Name') ?></th>
                                            <td><?= h($product->capability_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Capability Category') ?></th>
                                            <td><?= h($product->capability_category) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Capability Value') ?></th>
                                            <td><?= h($product->capability_value) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Testing Standard') ?></th>
                                            <td><?= h($product->testing_standard) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Certifying Organization') ?></th>
                                            <td><?= h($product->certifying_organization) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Numeric Rating') ?></th>
                                            <td><?= $product->numeric_rating ? number_format($product->numeric_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->technical_specifications): ?>
                            <div class="mt-3">
                                <h6><?= __('Technical Specifications') ?></h6>
                                <div class="bg-light p-3">
                                    <pre><?= h(json_encode(json_decode($product->technical_specifications), JSON_PRETTY_PRINT)) ?></pre>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Port/Connector Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-plug"></i> <?= __('Port & Connector Information') ?></h5>
                            <div class="btn-group" role="group">
                                <?php if ($product->port_family || $product->form_factor): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-table me-1"></i>' . __('View All Port Types'),
                                        ['controller' => 'PortTypes', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-light btn-sm',
                                            'escape' => false,
                                            'title' => __('View comprehensive port types data'),
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php if ($product->port_family): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-filter me-1"></i>' . h($product->port_family) . ' ' . __('Family'),
                                            ['controller' => 'PortTypes', 'action' => 'family', $product->port_family],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} port types', $product->port_family),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->max_voltage && $product->max_current): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-bolt me-1"></i>' . __('Electrical Specs'),
                                            ['controller' => 'PortTypes', 'action' => 'electrical'],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('Compare electrical specifications'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Port Family') ?></th>
                                            <td><?= h($product->port_family) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Port Type Name') ?></th>
                                            <td><?= h($product->port_type_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Form Factor') ?></th>
                                            <td><?= h($product->form_factor) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Connector Gender') ?></th>
                                            <td><?= h($product->connector_gender) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Pin Count') ?></th>
                                            <td><?= $product->pin_count ? number_format($product->pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Data Pin Count') ?></th>
                                            <td><?= $product->data_pin_count ? number_format($product->data_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Power Pin Count') ?></th>
                                            <td><?= $product->power_pin_count ? number_format($product->power_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Ground Pin Count') ?></th>
                                            <td><?= $product->ground_pin_count ? number_format($product->ground_pin_count) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-4">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Endpoint Position') ?></th>
                                            <td><?= h($product->endpoint_position) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Is Detachable') ?></th>
                                            <td><?= $product->is_detachable ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Electrical Shielding') ?></th>
                                            <td><?= h($product->electrical_shielding) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Durability Cycles') ?></th>
                                            <td><?= $product->durability_cycles ? number_format($product->durability_cycles) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->adapter_functionality): ?>
                            <div class="mt-3">
                                <h6><?= __('Adapter Functionality') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->adapter_functionality)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Device Compatibility -->
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-laptop"></i> <?= __('Device Compatibility') ?></h5>
                            <div class="btn-group" role="group">
                                <?php if ($product->device_category || $product->device_brand): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-table me-1"></i>' . __('View All Devices'),
                                        ['controller' => 'DeviceCompatibility', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-light btn-sm',
                                            'escape' => false,
                                            'title' => __('View comprehensive device compatibility data'),
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php if ($product->device_category): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-filter me-1"></i>' . h($product->device_category),
                                            ['controller' => 'DeviceCompatibility', 'action' => 'category', $product->device_category],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} devices', $product->device_category),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->device_brand): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-tag me-1"></i>' . h($product->device_brand),
                                            ['controller' => 'DeviceCompatibility', 'action' => 'brand', $product->device_brand],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} devices', $product->device_brand),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->compatibility_level === 'Full'): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-check me-1"></i>' . __('Full Compatible'),
                                            ['controller' => 'DeviceCompatibility', 'action' => 'full_compatible'],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View only fully compatible devices'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Device Category') ?></th>
                                            <td><?= h($product->device_category) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Device Brand') ?></th>
                                            <td><?= h($product->device_brand) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Device Model') ?></th>
                                            <td><?= h($product->device_model) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Compatibility Level') ?></th>
                                            <td>
                                                <?php if ($product->compatibility_level): ?>
                                                    <?php 
                                                    $compatClass = [
                                                        'Full' => 'success',
                                                        'Partial' => 'warning', 
                                                        'Limited' => 'info',
                                                        'Incompatible' => 'danger'
                                                    ][$product->compatibility_level] ?? 'secondary';
                                                    ?>
                                                    <span class="badge badge-<?= $compatClass ?>"><?= h($product->compatibility_level) ?></span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Performance Rating') ?></th>
                                            <td><?= $product->performance_rating ? number_format($product->performance_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('User Reported Rating') ?></th>
                                            <td><?= $product->user_reported_rating ? number_format($product->user_reported_rating, 2) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Verification Date') ?></th>
                                            <td><?= $product->verification_date ? $product->verification_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Verified By') ?></th>
                                            <td><?= h($product->verified_by) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->compatibility_notes): ?>
                            <div class="mt-3">
                                <h6><?= __('Compatibility Notes') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->compatibility_notes)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Physical Specifications -->
                    <div class="card mt-4">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-ruler"></i> <?= __('Physical Specifications') ?></h5>
                            <div class="btn-group" role="group">
                                <?php if ($product->physical_spec_name || $product->spec_type): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-table me-1"></i>' . __('View All Specs'),
                                        ['controller' => 'PhysicalSpecifications', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-dark btn-sm',
                                            'escape' => false,
                                            'title' => __('View comprehensive physical specifications data'),
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php if ($product->spec_type): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-filter me-1"></i>' . h($product->spec_type),
                                            ['controller' => 'PhysicalSpecifications', 'action' => 'type', $product->spec_type],
                                            [
                                                'class' => 'btn btn-outline-dark btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} specifications', $product->spec_type),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->measurement_unit): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-balance-scale me-1"></i>' . h($product->measurement_unit),
                                            ['controller' => 'PhysicalSpecifications', 'action' => 'unit', $product->measurement_unit],
                                            [
                                                'class' => 'btn btn-outline-dark btn-sm',
                                                'escape' => false,
                                                'title' => __('View all specifications in {0}', $product->measurement_unit),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->max_voltage && $product->max_current): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-bolt me-1"></i>' . __('Electrical'),
                                            ['controller' => 'PhysicalSpecifications', 'action' => 'electrical'],
                                            [
                                                'class' => 'btn btn-outline-dark btn-sm',
                                                'escape' => false,
                                                'title' => __('View electrical specifications'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Physical Spec Name') ?></th>
                                            <td><?= h($product->physical_spec_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Spec Type') ?></th>
                                            <td><?= h($product->spec_type) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Spec Value') ?></th>
                                            <td><?= h($product->spec_value) ?: '<span class="text-muted">-</span>' ?></td>
                        </tr>
                                        <tr>
                                            <th><?= __('Numeric Value') ?></th>
                                            <td><?= $product->numeric_value ? number_format($product->numeric_value, 3) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Measurement Unit') ?></th>
                                            <td><?= h($product->measurement_unit) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Max Voltage') ?></th>
                                            <td><?= $product->max_voltage ? number_format($product->max_voltage, 2) . ' V' : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Max Current') ?></th>
                                            <td><?= $product->max_current ? number_format($product->max_current, 2) . ' A' : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Physical Specs Summary') ?></th>
                                            <td><?= h($product->physical_specs_summary) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Introduced Date') ?></th>
                                            <td><?= $product->introduced_date ? $product->introduced_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Deprecated Date') ?></th>
                                            <td><?= $product->deprecated_date ? $product->deprecated_date->format('M j, Y') : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->spec_description): ?>
                            <div class="mt-3">
                                <h6><?= __('Specification Description') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->spec_description)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Category Information -->
                    <div class="card mt-4">
                        <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-tags"></i> <?= __('Category Information') ?></h5>
                            <div class="btn-group" role="group">
                                <?php if ($product->parent_category_name || $product->manufacturer): ?>
                                    <?= $this->Html->link(
                                        '<i class="fas fa-table me-1"></i>' . __('View All Categories'),
                                        ['controller' => 'Categories', 'action' => 'index'],
                                        [
                                            'class' => 'btn btn-outline-light btn-sm',
                                            'escape' => false,
                                            'title' => __('View comprehensive category data'),
                                            'data-bs-toggle' => 'tooltip'
                                        ]
                                    ) ?>
                                    <?php if ($product->parent_category_name): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-sitemap me-1"></i>' . h($product->parent_category_name),
                                            ['controller' => 'Categories', 'action' => 'parent', $product->parent_category_name],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} subcategories', $product->parent_category_name),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->manufacturer): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-industry me-1"></i>' . h($product->manufacturer),
                                            ['controller' => 'Products', 'action' => 'manufacturer', $product->manufacturer],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View all {0} products', $product->manufacturer),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                    <?php if ($product->is_certified): ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-certificate me-1"></i>' . __('Certified'),
                                            ['controller' => 'Categories', 'action' => 'certified'],
                                            [
                                                'class' => 'btn btn-outline-light btn-sm',
                                                'escape' => false,
                                                'title' => __('View only certified products'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Parent Category Name') ?></th>
                                            <td><?= h($product->parent_category_name) ?: '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Category Icon') ?></th>
                                            <td>
                                                <?php if ($product->category_icon): ?>
                                                    <i class="<?= h($product->category_icon) ?>"></i> <?= h($product->category_icon) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Display Order') ?></th>
                                            <td><?= $product->display_order ? number_format($product->display_order) : '<span class="text-muted">-</span>' ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-sm">
                                        <tr>
                                            <th><?= __('Is Certified') ?></th>
                                            <td>
                                                <?= $product->is_certified ? '<i class="fas fa-certificate text-success"></i> Yes' : '<i class="fas fa-times text-muted"></i> No' ?>
                                                <?php if ($product->certification_date): ?>
                                                    <br><small class="text-muted"><?= $product->certification_date->format('M j, Y') ?></small>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?= __('Needs Normalization') ?></th>
                                            <td><?= $product->needs_normalization ? '<i class="fas fa-exclamation-triangle text-warning"></i> Yes' : '<i class="fas fa-check text-success"></i> No' ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <?php if ($product->category_description): ?>
                            <div class="mt-3">
                                <h6><?= __('Category Description') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->category_description)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if ($product->prototype_notes): ?>
                            <div class="mt-3">
                                <h6><?= __('Prototype Notes') ?></h6>
                                <div class="bg-light p-3">
                                    <?= nl2br(h($product->prototype_notes)) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($product->tags)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Description') ?></th>
                                            <th><?= __('Image') ?></th>
                                            <th><?= __('Dir') ?></th>
                                            <th><?= __('Alt Text') ?></th>
                                            <th><?= __('Keywords') ?></th>
                                            <th><?= __('Size') ?></th>
                                            <th><?= __('Mime') ?></th>
                                            <th><?= __('Name') ?></th>
                                            <th><?= __('Meta Title') ?></th>
                                            <th><?= __('Meta Description') ?></th>
                                            <th><?= __('Meta Keywords') ?></th>
                                            <th><?= __('Facebook Description') ?></th>
                                            <th><?= __('Linkedin Description') ?></th>
                                            <th><?= __('Instagram Description') ?></th>
                                            <th><?= __('Twitter Description') ?></th>
                                            <th><?= __('Parent Id') ?></th>
                                            <th><?= __('Main Menu') ?></th>
                                            <th><?= __('Lft') ?></th>
                                            <th><?= __('Rght') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->tags as $tag) : ?>
                                        <tr>
                                            <td><?= h($tag->id) ?></td>
                                            <td><?= h($tag->title) ?></td>
                                            <td><?= h($tag->slug) ?></td>
                                            <td><?= h($tag->description) ?></td>
                                            <td><?= h($tag->image) ?></td>
                                            <td><?= h($tag->dir) ?></td>
                                            <td><?= h($tag->alt_text) ?></td>
                                            <td><?= h($tag->keywords) ?></td>
                                            <td><?= h($tag->size) ?></td>
                                            <td><?= h($tag->mime) ?></td>
                                            <td><?= h($tag->name) ?></td>
                                            <td><?= h($tag->meta_title) ?></td>
                                            <td><?= h($tag->meta_description) ?></td>
                                            <td><?= h($tag->meta_keywords) ?></td>
                                            <td><?= h($tag->facebook_description) ?></td>
                                            <td><?= h($tag->linkedin_description) ?></td>
                                            <td><?= h($tag->instagram_description) ?></td>
                                            <td><?= h($tag->twitter_description) ?></td>
                                            <td><?= h($tag->parent_id) ?></td>
                                            <td><?= h($tag->main_menu) ?></td>
                                            <td><?= h($tag->lft) ?></td>
                                            <td><?= h($tag->rght) ?></td>
                                            <td><?= h($tag->modified) ?></td>
                                            <td><?= h($tag->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Tags', 'model' => $tag, 'display' => 'title']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($product->slugs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Model') ?></th>
                                            <th><?= __('Foreign Key') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->id) ?></td>
                                            <td><?= h($slug->model) ?></td>
                                            <td><?= h($slug->foreign_key) ?></td>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Slugs', 'model' => $slug, 'display' => 'slug']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>