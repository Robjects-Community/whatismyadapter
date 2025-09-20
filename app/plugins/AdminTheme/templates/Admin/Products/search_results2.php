<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */
?>
<?php if (empty($products)): ?>
    <?= $this->element('empty_state', [
        'type' => 'search',
        'title' => __('No Products found'),
        'message' => __('Try adjusting your search terms or filters.')
    ]) ?>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                            <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('manufacturer') ?></th>
                            
                            <!-- Capability & Standards -->
                            <th scope="col"><?= $this->Paginator->sort('capability_name') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('testing_standard') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('is_certified') ?></th>
                            
                            <!-- Port/Connector -->
                            <th scope="col"><?= $this->Paginator->sort('port_family') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('form_factor') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('connector_gender') ?></th>
                            
                            <!-- Device Compatibility -->
                            <th scope="col"><?= $this->Paginator->sort('device_category') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('compatibility_level') ?></th>
                            
                            <!-- Ratings -->
                            <th scope="col"><?= $this->Paginator->sort('numeric_rating') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('performance_rating') ?></th>
                            
                            <!-- Physical Specs -->
                            <th scope="col"><?= $this->Paginator->sort('max_voltage') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('max_current') ?></th>
                            
                            <!-- Basic Info -->
                            <th scope="col"><?= $this->Paginator->sort('price') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('reliability_score') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('is_published') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('verification_status') ?></th>
                            <th scope="col"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
      <tr>
            <!-- ID -->
            <td><?= h($product->id) ?></td>
            
            <!-- Title -->
            <td><?= h($product->title) ?></td>
            
            <!-- Manufacturer -->
            <td><?= h($product->manufacturer) ?></td>
            
            <!-- Capability Name -->
            <td><span class="badge badge-secondary"><?= h($product->capability_name) ?: '-' ?></span></td>
            
            <!-- Testing Standard -->
            <td><?= h($product->testing_standard) ?: '-' ?></td>
            
            <!-- Is Certified -->
            <td><?= $product->is_certified ? '<i class="fas fa-certificate text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
            
            <!-- Port Family -->
            <td><span class="badge badge-primary"><?= h($product->port_family) ?: '-' ?></span></td>
            
            <!-- Form Factor -->
            <td><?= h($product->form_factor) ?: '-' ?></td>
            
            <!-- Connector Gender -->
            <td>
                <?php if ($product->connector_gender): ?>
                    <span class="badge badge-<?= $product->connector_gender == 'Male' ? 'info' : 'warning' ?>"><?= h($product->connector_gender) ?></span>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            
            <!-- Device Category -->
            <td><?= h($product->device_category) ?: '-' ?></td>
            
            <!-- Compatibility Level -->
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
                    -
                <?php endif; ?>
            </td>
            
            <!-- Numeric Rating -->
            <td>
                <?php if ($product->numeric_rating): ?>
                    <span class="badge badge-info"><?= number_format($product->numeric_rating, 1) ?></span>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
            
            <!-- Performance Rating -->
            <td><?= $product->performance_rating ? number_format($product->performance_rating, 2) : '-' ?></td>
            
            <!-- Max Voltage -->
            <td><?= $product->max_voltage ? number_format($product->max_voltage, 2) . 'V' : '-' ?></td>
            
            <!-- Max Current -->
            <td><?= $product->max_current ? number_format($product->max_current, 2) . 'A' : '-' ?></td>
            
            <!-- Price -->
            <td><?= $product->price === null ? '-' : number_format($product->price, 2) ?></td>
            
            <!-- Reliability Score -->
            <td>
                <?php if ($product->reliability_score !== null): ?>
                    <?php 
                    $scoreColor = match(true) {
                        $product->reliability_score >= 0.9 => 'success',
                        $product->reliability_score >= 0.7 => 'warning', 
                        default => 'danger'
                    };
                    ?>
                    <?= $this->Html->link(
                        '<span class="badge bg-' . $scoreColor . '">' . $this->Number->toPercentage($product->reliability_score * 100, 1) . '</span>',
                        ['controller' => 'Reliability', 'action' => 'view', 'model' => 'Products', 'id' => $product->id],
                        ['escape' => false, 'title' => __('View reliability details')]
                    ) ?>
                <?php else: ?>
                    <span class="text-muted">-</span>
                <?php endif; ?>
            </td>
            
            <!-- Is Published -->
            <td><?= $product->is_published ? '<i class="fas fa-eye text-success"></i>' : '<i class="fas fa-eye-slash text-muted"></i>' ?></td>
            
            <!-- Verification Status -->
            <td>
                <?php 
                $statusClass = [
                    'pending' => 'warning',
                    'approved' => 'success', 
                    'rejected' => 'danger'
                ][$product->verification_status] ?? 'secondary';
                ?>
                <span class="badge badge-<?= $statusClass ?>"><?= h(ucfirst($product->verification_status)) ?></span>
            </td>
            
            <!-- Actions -->
            <td>
                <?= $this->element('evd_dropdown', ['model' => $product, 'display' => 'title']); ?>
            </td>
      </tr>
      <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
<?php endif; ?>

