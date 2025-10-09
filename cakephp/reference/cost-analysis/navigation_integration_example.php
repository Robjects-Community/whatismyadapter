<?php
/**
 * Admin Navigation Integration Example for Cost Analysis
 * 
 * This file shows how to integrate the Cost Analysis page into your AdminTheme
 * navigation. Place these navigation items in your admin layout file.
 * 
 * File location for integration: 
 * cakephp/plugins/AdminTheme/templates/layout/default.php
 */

// Example navigation menu structure
$adminMenuItems = [
    [
        'title' => __('Dashboard'),
        'url' => ['controller' => 'Dashboard', 'action' => 'index'],
        'icon' => 'fas fa-tachometer-alt',
        'active' => $this->request->getParam('controller') === 'Dashboard'
    ],
    [
        'title' => __('Content Management'),
        'url' => '#',
        'icon' => 'fas fa-edit',
        'submenu' => [
            [
                'title' => __('Articles'),
                'url' => ['controller' => 'Articles', 'action' => 'index'],
                'icon' => 'fas fa-newspaper'
            ],
            [
                'title' => __('Pages'),
                'url' => ['controller' => 'Pages', 'action' => 'index'], 
                'icon' => 'fas fa-file-alt'
            ]
        ]
    ],
    [
        'title' => __('System Analysis'),  // NEW SECTION
        'url' => '#',
        'icon' => 'fas fa-chart-line',
        'submenu' => [
            [
                'title' => __('Cost Analysis'),
                'url' => ['controller' => 'Pages', 'action' => 'costAnalysis'],
                'icon' => 'fas fa-calculator',
                'description' => __('Server deployment cost comparison'),
                'badge' => 'NEW',
                'active' => ($this->request->getParam('controller') === 'Pages' && 
                           $this->request->getParam('action') === 'costAnalysis')
            ],
            [
                'title' => __('Performance Monitor'),
                'url' => ['controller' => 'Monitor', 'action' => 'performance'],
                'icon' => 'fas fa-tachometer-alt'
            ],
            [
                'title' => __('AI Metrics'),
                'url' => ['controller' => 'AiMetrics', 'action' => 'dashboard'],
                'icon' => 'fas fa-robot'
            ]
        ]
    ],
    [
        'title' => __('Settings'),
        'url' => ['controller' => 'Settings', 'action' => 'index'],
        'icon' => 'fas fa-cog'
    ]
];

/**
 * Quick Access Widget Example
 * Add this to your dashboard or main admin layout
 */
?>
<div class="quick-access-widget">
    <h3><i class="fas fa-rocket"></i> <?= __('Quick Access') ?></h3>
    <div class="widget-content">
        <?= $this->Html->link(
            '<i class="fas fa-calculator"></i>' .
            '<div class="widget-info">' .
                '<strong>' . __('Cost Analysis') . '</strong>' .
                '<span>' . __('Compare deployment platforms') . '</span>' .
            '</div>',
            ['controller' => 'Pages', 'action' => 'costAnalysis'],
            [
                'class' => 'widget-link primary',
                'escape' => false,
                'title' => __('Analyze server deployment costs over 10 years')
            ]
        ) ?>

        <?= $this->Html->link(
            '<i class="fas fa-robot"></i>' .
            '<div class="widget-info">' .
                '<strong>' . __('AI Metrics') . '</strong>' .
                '<span>' . __('Monitor API usage & costs') . '</span>' .
            '</div>',
            ['controller' => 'AiMetrics', 'action' => 'dashboard'],
            [
                'class' => 'widget-link info',
                'escape' => false,
                'title' => __('Monitor AI API usage and costs')
            ]
        ) ?>
    </div>
</div>

<?php
/**
 * Breadcrumb Example for Cost Analysis page
 * Add this to your AdminTheme layout to show proper breadcrumbs
 */
if ($this->request->getParam('controller') === 'Pages' && 
    $this->request->getParam('action') === 'costAnalysis'): 
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <?= $this->Html->link(__('Dashboard'), ['controller' => 'Dashboard', 'action' => 'index']) ?>
        </li>
        <li class="breadcrumb-item">
            <?= $this->Html->link(__('Pages'), ['controller' => 'Pages', 'action' => 'index']) ?>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= __('Cost Analysis') ?>
        </li>
    </ol>
</nav>
<?php endif; ?>

<?php
/**
 * CSS for Navigation Integration
 * Add this to your AdminTheme CSS files
 */
?>
<style>
/* Quick Access Widget Styles */
.quick-access-widget {
    background: var(--color-surface, #ffffff);
    border: 1px solid var(--color-card-border, #e9ecef);
    border-radius: var(--radius-lg, 8px);
    padding: var(--space-20, 1.25rem);
    margin: var(--space-20, 1.25rem);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.quick-access-widget h3 {
    display: flex;
    align-items: center;
    gap: var(--space-8, 0.5rem);
    margin-bottom: var(--space-16, 1rem);
    font-size: var(--font-size-lg, 1.125rem);
    color: var(--color-text, #212529);
}

.quick-access-widget h3 i {
    color: var(--color-primary, #007bff);
}

.widget-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-12, 0.75rem);
}

.widget-link {
    display: flex;
    align-items: center;
    gap: var(--space-12, 0.75rem);
    padding: var(--space-12, 0.75rem);
    border-radius: var(--radius-base, 6px);
    text-decoration: none;
    transition: all 0.2s ease;
    border: 1px solid var(--color-card-border, #e9ecef);
    background: var(--color-bg-1, #f8f9fa);
}

.widget-link:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    text-decoration: none;
}

.widget-link.primary {
    border-color: var(--color-primary, #007bff);
}

.widget-link.primary:hover {
    background: var(--color-primary, #007bff);
    color: white;
}

.widget-link.info {
    border-color: var(--color-info, #17a2b8);
}

.widget-link.info:hover {
    background: var(--color-info, #17a2b8);
    color: white;
}

.widget-link i {
    font-size: 1.5rem;
    color: var(--color-primary, #007bff);
    flex-shrink: 0;
}

.widget-info strong {
    display: block;
    color: var(--color-text, #212529);
    font-weight: 600;
    margin-bottom: var(--space-2, 0.125rem);
}

.widget-info span {
    font-size: var(--font-size-sm, 0.875rem);
    color: var(--color-text-secondary, #6c757d);
}

.widget-link:hover .widget-info strong,
.widget-link:hover .widget-info span,
.widget-link:hover i {
    color: inherit;
}

/* Badge Styles */
.badge {
    background: var(--color-success, #28a745);
    color: white;
    font-size: var(--font-size-xs, 0.75rem);
    padding: var(--space-2, 0.125rem) var(--space-6, 0.375rem);
    border-radius: var(--radius-full, 20px);
    font-weight: 600;
    margin-left: auto;
}

/* Navigation Active States */
.nav-item.active > .nav-link {
    background: var(--color-primary, #007bff);
    color: white;
    font-weight: 600;
}

.submenu-item.active .submenu-link {
    background: rgba(var(--color-primary-rgb, 0, 123, 255), 0.1);
    color: var(--color-primary, #007bff);
    font-weight: 600;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quick-access-widget {
        margin: var(--space-12, 0.75rem);
    }

    .widget-content {
        flex-direction: row;
        gap: var(--space-8, 0.5rem);
    }

    .widget-link {
        flex: 1;
        justify-content: center;
        text-align: center;
    }

    .widget-info {
        display: none;
    }
}
</style>