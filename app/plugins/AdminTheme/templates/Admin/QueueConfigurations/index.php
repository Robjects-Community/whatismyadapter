<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\QueueConfiguration> $queueConfigurations
 */
?>
<?php $this->assign('title', __('Queue Configurations')); ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <form class="d-flex-grow-1 me-3" role="search">
                <input id="queueConfigSearch" type="search" class="form-control" 
                       placeholder="<?= __('Search Queue Configurations...') ?>" 
                       aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
            </form>
        </div>
        <div class="flex-shrink-0">
            <button id="refreshHealthBtn" class="btn btn-outline-info me-2" title="<?= __('Refresh Health Status') ?>" data-bs-toggle="tooltip">
                <i class="fas fa-sync-alt"></i> <?= __('Health Check') ?>
            </button>
            <?= $this->Html->link(__('New Queue Configuration'), ['action' => 'add'], ['class' => 'btn btn-primary me-2']) ?>
            <?= $this->Html->link(__('Sync Configurations'), ['action' => 'sync'], [
                'class' => 'btn btn-success',
                'confirm' => __('This will update the queue.php config file with current database settings. Continue?')
            ]) ?>
        </div>
    </div>
</header>

<div id="ajax-target">
    <div class="row">
        <?php if (empty($queueConfigurations)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <i class="fas fa-tasks fa-3x text-muted mb-3"></i>
                    <h4 class="alert-heading"><?= __('No Queue Configurations Found') ?></h4>
                    <p><?= __('Get started by creating your first queue configuration. Queue configurations allow you to manage different job queues like Redis and RabbitMQ from the admin panel.') ?></p>
                    <?= $this->Html->link(__('Create First Configuration'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        <?php else: ?>
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th scope="col"><?= __('Name') ?></th>
                                <th scope="col"><?= __('Config Key') ?></th>
                                <th scope="col"><?= __('Type') ?></th>
                                <th scope="col"><?= __('Queue Name') ?></th>
                                <th scope="col"><?= __('Host:Port') ?></th>
                                <th scope="col" class="text-center"><?= __('Workers') ?></th>
                                <th scope="col" class="text-center"><?= __('Priority') ?></th>
                                <th scope="col" class="text-center"><?= __('Status') ?></th>
                                <th scope="col" class="text-center"><?= __('Health') ?></th>
                                <th scope="col" class="text-center"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($queueConfigurations as $config): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= h($config->name) ?></div>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar-plus me-1"></i>
                                            <?= $config->created->format('M j, Y') ?>
                                        </small>
                                    </td>
                                    <td>
                                        <code class="bg-light px-2 py-1 rounded"><?= h($config->config_key) ?></code>
                                    </td>
                                    <td>
                                        <?php
                                        $typeClass = $config->queue_type === 'redis' ? 'bg-info' : 'bg-warning';
                                        $typeIcon = $config->queue_type === 'redis' ? 'fas fa-database' : 'fas fa-rabbit';
                                        ?>
                                        <span class="badge <?= $typeClass ?> text-dark">
                                            <i class="<?= $typeIcon ?> me-1"></i><?= ucfirst($config->queue_type) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <code><?= h($config->queue_name) ?></code>
                                    </td>
                                    <td>
                                        <span class="font-monospace"><?= h($config->host) ?>:<?= $config->port ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary"><?= $config->max_workers ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        $priorityClass = $config->priority >= 8 ? 'bg-danger' : 
                                                       ($config->priority >= 5 ? 'bg-warning text-dark' : 'bg-secondary');
                                        ?>
                                        <span class="badge <?= $priorityClass ?>"><?= $config->priority ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($config->enabled): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i><?= __('Enabled') ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-times-circle me-1"></i><?= __('Disabled') ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="health-status" data-config-id="<?= $config->id ?>">
                                            <?php if ($config->enabled): ?>
                                                <span class="badge bg-secondary health-checking">
                                                    <i class="fas fa-spinner fa-spin me-1"></i><?= __('Checking...') ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-muted">
                                                    <i class="fas fa-ban me-1"></i><?= __('Disabled') ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group" aria-label="Actions">
                                            <?= $this->Html->link(
                                                '<i class="fas fa-eye"></i>',
                                                ['action' => 'view', $config->id],
                                                [
                                                    'class' => 'btn btn-outline-info btn-sm',
                                                    'title' => __('View'),
                                                    'escape' => false,
                                                    'data-bs-toggle' => 'tooltip'
                                                ]
                                            ) ?>
                                            <?= $this->Html->link(
                                                '<i class="fas fa-edit"></i>',
                                                ['action' => 'edit', $config->id],
                                                [
                                                    'class' => 'btn btn-outline-primary btn-sm',
                                                    'title' => __('Edit'),
                                                    'escape' => false,
                                                    'data-bs-toggle' => 'tooltip'
                                                ]
                                            ) ?>
                                            <?= $this->Form->postLink(
                                                '<i class="fas fa-trash"></i>',
                                                ['action' => 'delete', $config->id],
                                                [
                                                    'confirm' => __('Are you sure you want to delete the queue configuration "{0}"?', $config->name),
                                                    'class' => 'btn btn-outline-danger btn-sm',
                                                    'title' => __('Delete'),
                                                    'escape' => false,
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
                <div class="mt-4">
                    <?= $this->element('pagination', ['recordCount' => count($queueConfigurations), 'search' => $search ?? '']) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="mt-4">
    <div class="alert alert-info">
        <h5><i class="fas fa-info-circle me-2"></i><?= __('About Queue Configurations') ?></h5>
        <p class="mb-2"><?= __('Queue configurations allow you to manage different job processing queues:') ?></p>
        <ul class="mb-0">
            <li><strong><?= __('Redis Queues:') ?></strong> <?= __('Fast, lightweight queues ideal for high-frequency, low-latency jobs.') ?></li>
            <li><strong><?= __('RabbitMQ Queues:') ?></strong> <?= __('Robust, feature-rich queues suitable for complex workflows and heavy processing.') ?></li>
            <li><strong><?= __('Priority:') ?></strong> <?= __('Higher priority queues (8-10) are processed first, medium (4-7), low (1-3).') ?></li>
            <li><strong><?= __('Sync:') ?></strong> <?= __('Updates the queue.php configuration file with current settings.') ?></li>
        </ul>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('queueConfigSearch');
    const resultsContainer = document.querySelector('#ajax-target');
    const refreshHealthBtn = document.getElementById('refreshHealthBtn');

    let debounceTimer;

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            if (searchTerm.length > 0) {
                url += (url.includes('?') ? '&' : '?') + `search=${encodeURIComponent(searchTerm)}`;
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                initializeTooltips();
                // Auto-refresh health after search results load
                setTimeout(checkAllHealthStatus, 500);
            })
            .catch(error => console.error('Search error:', error));

        }, 300); // Debounce for 300ms
    });

    // Health check functionality
    function checkHealthStatus(configId) {
        const healthDiv = document.querySelector(`[data-config-id="${configId}"]`);
        if (!healthDiv) return;

        // Show checking state
        healthDiv.innerHTML = '<span class="badge bg-secondary"><i class="fas fa-spinner fa-spin me-1"></i><?= __('Checking...') ?></span>';

        fetch(`<?= $this->Url->build(['action' => 'healthCheck']) ?>/${configId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.health) {
                updateHealthDisplay(healthDiv, data.health);
            } else {
                healthDiv.innerHTML = '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i><?= __('Error') ?></span>';
            }
        })
        .catch(error => {
            console.error('Health check error for config', configId, error);
            healthDiv.innerHTML = '<span class="badge bg-warning"><i class="fas fa-question-circle me-1"></i><?= __('Unknown') ?></span>';
        });
    }

    function updateHealthDisplay(healthDiv, healthData) {
        let badgeClass, icon, text, title;
        
        if (healthData.healthy) {
            badgeClass = 'bg-success';
            icon = 'fas fa-check-circle';
            text = '<?= __('Healthy') ?>';
            title = `<?= __('Response time: ') ?>${healthData.response_time_ms}ms`;
        } else {
            badgeClass = 'bg-danger';
            icon = 'fas fa-exclamation-triangle';
            text = '<?= __('Unhealthy') ?>';
            title = healthData.message || '<?= __('Connection failed') ?>';
        }
        
        healthDiv.innerHTML = `<span class="badge ${badgeClass}" title="${title}" data-bs-toggle="tooltip">` +
                             `<i class="${icon} me-1"></i>${text}</span>`;
        
        // Re-initialize tooltip for the new element
        const tooltip = healthDiv.querySelector('[data-bs-toggle="tooltip"]');
        if (tooltip) {
            new bootstrap.Tooltip(tooltip);
        }
    }

    function checkAllHealthStatus() {
        const healthDivs = document.querySelectorAll('.health-status[data-config-id]');
        healthDivs.forEach(div => {
            const configId = div.getAttribute('data-config-id');
            // Only check enabled configs (skip disabled ones)
            if (div.querySelector('.health-checking, .badge:not(.bg-muted)')) {
                checkHealthStatus(configId);
            }
        });
    }

    // Refresh health button handler
    if (refreshHealthBtn) {
        refreshHealthBtn.addEventListener('click', function() {
            const icon = this.querySelector('i');
            icon.classList.add('fa-spin');
            
            checkAllHealthStatus();
            
            // Remove spin after a delay
            setTimeout(() => {
                icon.classList.remove('fa-spin');
            }, 2000);
        });
    }

    function initializeTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    // Initialize tooltips and health checks on page load
    initializeTooltips();
    
    // Auto-check health status after page loads
    setTimeout(checkAllHealthStatus, 1000);
});
<?php $this->Html->scriptEnd(); ?>
