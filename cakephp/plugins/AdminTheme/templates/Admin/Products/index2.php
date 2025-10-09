<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */

// Load search utility scripts
$this->Html->script('AdminTheme.utils/search-handler', ['block' => true]);
$this->Html->script('AdminTheme.utils/popover-manager', ['block' => true]); 
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- Status Filter -->
            <?= $this->element('status_filter', [
                'filters' => [
                    'all' => ['label' => __('All'), 'params' => []],
                    'filter1' => ['label' => __('Filter 1'), 'params' => ['status' => '0']],
                    'filter2' => ['label' => __('Filter 2'), 'params' => ['status' => '1']],
                ]
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'id' => 'product-search-form',
                'inputId' => 'productSearch',
                'placeholder' => __('Search Products...'),
                'class' => 'd-flex me-3 flex-grow-1'
            ]) ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Product'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
        <tr>
                  <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('manufacturer') ?></th>
                  
                  <!-- Capability Fields -->
                  <th scope="col"><?= $this->Paginator->sort('capability_name') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('capability_category') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('testing_standard') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('certifying_organization') ?></th>
                  
                  <!-- Port/Connector Fields -->
                  <th scope="col"><?= $this->Paginator->sort('port_family') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('form_factor') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('connector_gender') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('pin_count') ?></th>
                  
                  <!-- Device Compatibility -->
                  <th scope="col"><?= $this->Paginator->sort('device_category') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('device_brand') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('compatibility_level') ?></th>
                  
                  <!-- Ratings & Specs -->
                  <th scope="col"><?= $this->Paginator->sort('numeric_rating') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('performance_rating') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('is_certified') ?></th>
                  
                  <!-- Physical Specs -->
                  <th scope="col"><?= $this->Paginator->sort('max_voltage') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('max_current') ?></th>
                  
                  <!-- Basic Fields -->
                  <th scope="col"><?= $this->Paginator->sort('price') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('reliability_score') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('is_published') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('verification_status') ?></th>
                  <th scope="col"><?= $this->Paginator->sort('created') ?></th>
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
            <td><?= h($product->capability_name) ?: '-' ?></td>
            
            <!-- Capability Category -->
            <td><?= h($product->capability_category) ?: '-' ?></td>
            
            <!-- Testing Standard -->
            <td><?= h($product->testing_standard) ?: '-' ?></td>
            
            <!-- Certifying Organization -->
            <td><?= h($product->certifying_organization) ?: '-' ?></td>
            
            <!-- Port Family -->
            <td><?= h($product->port_family) ?: '-' ?></td>
            
            <!-- Form Factor -->
            <td><?= h($product->form_factor) ?: '-' ?></td>
            
            <!-- Connector Gender -->
            <td><?= h($product->connector_gender) ?: '-' ?></td>
            
            <!-- Pin Count -->
            <td><?= $product->pin_count ? number_format($product->pin_count) : '-' ?></td>
            
            <!-- Device Category -->
            <td><?= h($product->device_category) ?: '-' ?></td>
            
            <!-- Device Brand -->
            <td><?= h($product->device_brand) ?: '-' ?></td>
            
            <!-- Compatibility Level -->
            <td><?= h($product->compatibility_level) ?: '-' ?></td>
            
            <!-- Numeric Rating -->
            <td><?= $product->numeric_rating ? number_format($product->numeric_rating, 1) : '-' ?></td>
            
            <!-- Performance Rating -->
            <td><?= $product->performance_rating ? number_format($product->performance_rating, 2) : '-' ?></td>
            
            <!-- Is Certified -->
            <td><?= $product->is_certified ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' ?></td>
            
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
            <td><?= h(ucfirst($product->verification_status)) ?></td>
            
            <!-- Created -->
            <td><?= h($product->created) ?></td>
            
            <!-- Actions -->
            <td>
              <?= $this->element('evd_dropdown', ['model' => $product, 'display' => 'title']); ?>
            </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  
  <?= $this->element('pagination') ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
// Initialize search functionality using AdminTheme utility
AdminTheme.SearchHandler.init({
    searchInputId: 'productSearch',
    resultsContainerId: '#ajax-target',
    baseUrl: '<?= $this->Url->build(['action' => 'index']) ?>',
    debounceDelay: 300
});
<?php $this->Html->scriptEnd(); ?>

