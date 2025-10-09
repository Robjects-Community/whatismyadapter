<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\CableCapability> $cableCapabilities
 * @var array $stats
 * @var array $categories
 */
$this->assign('title', __('Cable Capabilities Overview'));
$this->Html->css('willow-admin', ['block' => true]);
?>

<!-- Breadcrumb Navigation with Visual Return Button -->
<div class="row mb-4">
    <div class="col-md-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <?= $this->Html->link('<i class="fas fa-tachometer-alt"></i> Admin Dashboard', 
                        '/admin', 
                        ['escape' => false, 'class' => 'text-decoration-none']) ?>
                </li>
                <li class="breadcrumb-item">
                    <?= $this->Html->link('<i class="fas fa-box-open"></i> Products', 
                        '/admin/products', 
                        ['escape' => false, 'class' => 'text-decoration-none']) ?>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <i class="fas fa-cogs"></i> Cable Capabilities
                </li>
            </ol>
        </nav>
    </div>
    <div class="col-md-4 text-end">
        <!-- Visual Return Button -->
        <?= $this->Html->link(
            '<i class="fas fa-arrow-left me-2"></i>' . __('Return to Products'),
            ['controller' => 'CableCapabilities', 'action' => 'returnToProducts'],
            [
                'class' => 'btn btn-outline-primary btn-sm',
                'escape' => false,
                'title' => __('Return to products with preserved search filters'),
                'data-bs-toggle' => 'tooltip'
            ]
        ) ?>
    </div>
</div>

<!-- Statistics Dashboard -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Total Capabilities') ?></h5>
                <h2><?= number_format($stats['total_capabilities']) ?></h2>
                <small><i class="fas fa-cogs"></i> Active capabilities</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Certified') ?></h5>
                <h2><?= number_format($stats['certified_count']) ?></h2>
                <small><i class="fas fa-certificate"></i> 
                    <?= $stats['total_capabilities'] > 0 ? 
                        number_format(($stats['certified_count'] / $stats['total_capabilities']) * 100, 1) . '%' : 
                        '0%' ?> certified
                </small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Avg Rating') ?></h5>
                <h2><?= number_format($stats['average_rating'], 1) ?></h2>
                <small><i class="fas fa-star"></i> Average performance</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5 class="card-title"><?= __('Categories') ?></h5>
                <h2><?= $stats['categories_count'] ?></h2>
                <small><i class="fas fa-tags"></i> Unique categories</small>
            </div>
        </div>
    </div>
</div>

<!-- Action Buttons and Search -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="btn-group" role="group">
            <?= $this->Html->link(
                '<i class="fas fa-certificate"></i> ' . __('Certified Only'),
                ['action' => 'certified'],
                ['class' => 'btn btn-success btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-chart-bar"></i> ' . __('Analytics'),
                ['action' => 'analytics'],
                ['class' => 'btn btn-info btn-sm', 'escape' => false]
            ) ?>
            <?= $this->Html->link(
                '<i class="fas fa-download"></i> ' . __('Export CSV'),
                ['action' => 'export'],
                ['class' => 'btn btn-outline-secondary btn-sm', 'escape' => false]
            ) ?>
        </div>
    </div>
    <div class="col-md-6">
        <!-- Search Form -->
        <?= $this->Form->create(null, ['type' => 'get', 'url' => ['action' => 'search'], 'class' => 'd-flex']) ?>
        <div class="input-group">
            <?= $this->Form->control('q', [
                'type' => 'text',
                'placeholder' => __('Search technical specifications...'),
                'label' => false,
                'class' => 'form-control',
                'value' => $this->getRequest()->getQuery('q')
            ]) ?>
            <button class="btn btn-outline-secondary" type="submit">
                <i class="fas fa-search"></i>
            </button>
        </div>
        <?= $this->Form->end() ?>
    </div>
</div>

<!-- Category Filter Pills -->
<?php if (!empty($categories)): ?>
<div class="row mb-4">
    <div class="col-12">
        <h6 class="mb-2"><?= __('Filter by Category:') ?></h6>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($categories as $category): ?>
                <?= $this->Html->link(
                    $category,
                    ['action' => 'category', $category],
                    [
                        'class' => 'badge bg-primary text-decoration-none px-3 py-2',
                        'title' => __('View all capabilities in {0} category', $category),
                        'data-bs-toggle' => 'tooltip'
                    ]
                ) ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Capabilities Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fas fa-list"></i> <?= __('Cable Capabilities') ?>
            <small class="text-muted">(<?= $this->Paginator->counter() ?>)</small>
        </h5>
    </div>
    <div class="card-body">
        <?php if (!$cableCapabilities->isEmpty()): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th><?= $this->Paginator->sort('title', __('Product')) ?></th>
                            <th><?= $this->Paginator->sort('capability_name', __('Capability')) ?></th>
                            <th><?= $this->Paginator->sort('capability_category', __('Category')) ?></th>
                            <th><?= $this->Paginator->sort('capability_value', __('Value')) ?></th>
                            <th><?= $this->Paginator->sort('numeric_rating', __('Rating')) ?></th>
                            <th><?= $this->Paginator->sort('is_certified', __('Certified')) ?></th>
                            <th><?= $this->Paginator->sort('certifying_organization', __('Certifier')) ?></th>
                            <th class="text-center"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cableCapabilities as $capability): ?>
                            <tr>
                                <td>
                                    <?= $this->Html->link(
                                        h($capability->title),
                                        ['controller' => 'Products', 'action' => 'view', $capability->id],
                                        [
                                            'class' => 'text-decoration-none fw-bold',
                                            'title' => __('View full product details'),
                                            'data-bs-toggle' => 'tooltip',
                                            'data-bs-placement' => 'top'
                                        ]
                                    ) ?>
                                    <?php if (!empty($capability->description)): ?>
                                        <br>
                                        <small class="text-muted capability-description" 
                                               title="<?= h(Text::truncate($capability->description, 200)) ?>"
                                               data-bs-toggle' => 'tooltip'>
                                            <?= h(Text::truncate($capability->description, 80)) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= h($capability->capability_name) ?></strong>
                                    <?php if (!empty($capability->testing_standard)): ?>
                                        <br>
                                        <small class="text-info">
                                            <i class="fas fa-flask" 
                                               title="<?= h($capability->testing_standard) ?>"
                                               data-bs-toggle="tooltip"></i>
                                            <?= h(Text::truncate($capability->testing_standard, 30)) ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?= h($capability->capability_category) ?>
                                    </span>
                                </td>
                                <td>
                                    <code><?= h($capability->capability_value) ?></code>
                                </td>
                                <td>
                                    <?php if ($capability->numeric_rating): ?>
                                        <div class="d-flex align-items-center">
                                            <div class="rating-stars me-2" 
                                                 title="<?= h($capability->numeric_rating) ?>/10"
                                                 data-bs-toggle="tooltip">
                                                <?php 
                                                $stars = round($capability->numeric_rating / 2);
                                                for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $stars ? 'text-warning' : 'text-muted' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted"><?= number_format($capability->numeric_rating, 1) ?></small>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($capability->is_certified): ?>
                                        <span class="badge bg-success" 
                                              title="Certified<?= $capability->certification_date ? ' on ' . $capability->certification_date->format('M j, Y') : '' ?>"
                                              data-bs-toggle="tooltip">
                                            <i class="fas fa-certificate"></i> <?= __('Yes') ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times"></i> <?= __('No') ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= h($capability->certifying_organization) ?>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?= $this->Html->link(
                                            '<i class="fas fa-eye"></i>',
                                            ['action' => 'view', $capability->id],
                                            [
                                                'class' => 'btn btn-outline-info btn-sm',
                                                'escape' => false,
                                                'title' => __('View capability details'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                        <?= $this->Html->link(
                                            '<i class="fas fa-box-open"></i>',
                                            ['controller' => 'Products', 'action' => 'edit', $capability->id],
                                            [
                                                'class' => 'btn btn-outline-primary btn-sm',
                                                'escape' => false,
                                                'title' => __('Edit product'),
                                                'data-bs-toggle' => 'tooltip'
                                            ]
                                        ) ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?>
                </div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?= $this->Paginator->first('<< ' . __('first')) ?>
                        <?= $this->Paginator->prev('< ' . __('previous')) ?>
                        <?= $this->Paginator->numbers() ?>
                        <?= $this->Paginator->next(__('next') . ' >') ?>
                        <?= $this->Paginator->last(__('last') . ' >>') ?>
                    </ul>
                </nav>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                <h5><?= __('No Cable Capabilities Found') ?></h5>
                <p class="text-muted"><?= __('There are currently no cable capabilities in the system.') ?></p>
                <?= $this->Html->link(
                    '<i class="fas fa-plus"></i> ' . __('Add Product'),
                    ['controller' => 'Products', 'action' => 'add'],
                    ['class' => 'btn btn-primary', 'escape' => false]
                ) ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Initialize Tooltips and Enhanced UX -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Enhanced capability description hover
    document.querySelectorAll('.capability-description').forEach(function(element) {
        element.addEventListener('mouseenter', function() {
            this.style.maxHeight = 'none';
            this.style.overflow = 'visible';
        });
        
        element.addEventListener('mouseleave', function() {
            this.style.maxHeight = '';
            this.style.overflow = 'hidden';
        });
    });
    
    // Auto-refresh capability stats every 30 seconds
    setInterval(function() {
        fetch('/admin/cable-capabilities/index', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(response => response.text())
        .then(html => {
            // Update stats cards only
            const parser = new DOMParser();
            const newDoc = parser.parseFromString(html, 'text/html');
            const newStats = newDoc.querySelectorAll('.card .card-body h2');
            const currentStats = document.querySelectorAll('.card .card-body h2');
            
            newStats.forEach((newStat, index) => {
                if (currentStats[index] && newStat.textContent !== currentStats[index].textContent) {
                    currentStats[index].textContent = newStat.textContent;
                    currentStats[index].parentElement.classList.add('bg-success');
                    setTimeout(() => {
                        currentStats[index].parentElement.classList.remove('bg-success');
                    }, 2000);
                }
            });
        }).catch(console.error);
    }, 30000);
});
</script>

<style>
.rating-stars {
    font-size: 0.8rem;
}

.capability-description {
    max-height: 2.4em;
    overflow: hidden;
    line-height: 1.2em;
    transition: max-height 0.3s ease;
}

.card .bg-success {
    transition: background-color 0.5s ease;
}

.badge {
    transition: all 0.2s ease;
}

.badge:hover {
    transform: scale(1.05);
}

.btn-group .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.breadcrumb-item a:hover {
    text-decoration: underline !important;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,123,255,0.05);
}
</style>
