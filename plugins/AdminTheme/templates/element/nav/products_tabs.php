<?php
/**
 * Products Navigation Tabs Element
 *
 * Outputs a Bootstrap 5 navigation bar with three links (Dashboard, Products, Pending Review)
 * for the Products controller pages with responsive design and proper accessibility features.
 *
 * Features:
 * - Bootstrap 5 nav-pills styling with custom product-tabs class
 * - Responsive design: stacks vertically on very small screens, horizontal from small screens up
 * - Active state detection based on controller and action
 * - Proper accessibility with aria-current="page" for active links
 *
 * Usage: <?= $this->element('AdminTheme.nav/products_tabs') ?>
 */

// Get current controller and action for active state detection
$currentController = $this->request->getParam('controller');
$currentAction = $this->request->getParam('action');

// Define the navigation links with their respective actions and display text
$navLinks = [
    'dashboard' => [
        'url' => ['controller' => 'Products', 'action' => 'dashboard', 'prefix' => 'Admin'],
        'text' => __('Dashboard'),
        'icon' => 'fas fa-tachometer-alt',
    ],
    'index' => [
        'url' => ['controller' => 'Products', 'action' => 'index', 'prefix' => 'Admin'],
        'text' => __('Products'),
        'icon' => 'fas fa-boxes',
    ],
    'pendingReview' => [
        'url' => ['controller' => 'Products', 'action' => 'pendingReview', 'prefix' => 'Admin'],
        'text' => __('Pending Review'),
        'icon' => 'fas fa-clock',
    ],
    'forms' => [
        'url' => ['controller' => 'Products', 'action' => 'forms', 'prefix' => 'Admin'],
        'text' => __('Forms Configuration'),
        'icon' => 'fas fa-cogs',
    ],
];

// Check if we're in the Products controller for tab activation
$isProductsController = ($currentController === 'Products');
?>

<div class="product-tabs-wrapper mb-4">
    <ul class="nav nav-pills product-tabs flex-column flex-sm-row">
        <?php foreach ($navLinks as $action => $link) : ?>
            <?php
            // Determine if this link should be active
            $isActive = $isProductsController && ($currentAction === $action);

            // Build CSS classes
            $linkClasses = ['nav-link'];
            if ($isActive) {
                $linkClasses[] = 'active';
            }

            // Build link attributes
            $linkAttributes = [
                'class' => implode(' ', $linkClasses),
                'title' => $link['text'],
            ];

            // Add aria-current for accessibility on active link
            if ($isActive) {
                $linkAttributes['aria-current'] = 'page';
            }
            ?>
            <li class="nav-item">
                <?= $this->Html->link(
                    sprintf(
                        '<i class="%s me-2"></i>%s',
                        $link['icon'],
                        h($link['text']),
                    ),
                    $link['url'],
                    array_merge($linkAttributes, ['escape' => false]),
                ) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>

<?php
// Add custom CSS for better visual appearance
$this->append('css');
?>
<style>
/* Custom styling for product tabs */
.product-tabs-wrapper {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.product-tabs {
    --bs-nav-pills-border-radius: 0.375rem;
    --bs-nav-pills-link-active-color: #fff;
    --bs-nav-pills-link-active-bg: #0d6efd;
    gap: 0.25rem;
}

.product-tabs .nav-link {
    color: #6c757d;
    background-color: transparent;
    border: 1px solid transparent;
    transition: all 0.15s ease-in-out;
    font-weight: 500;
    padding: 0.75rem 1rem;
}

.product-tabs .nav-link:hover {
    color: #0d6efd;
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.product-tabs .nav-link.active {
    color: var(--bs-nav-pills-link-active-color);
    background-color: var(--bs-nav-pills-link-active-bg);
    border-color: var(--bs-nav-pills-link-active-bg);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.product-tabs .nav-link i {
    font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 575.98px) {
    .product-tabs .nav-link {
        justify-content: center;
        text-align: center;
        margin-bottom: 0.25rem;
    }
    
    .product-tabs-wrapper {
        margin-bottom: 1.5rem !important;
    }
}

@media (min-width: 576px) {
    .product-tabs .nav-link {
        white-space: nowrap;
    }
}
</style>
<?php $this->end(); ?>
