<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QueueConfiguration $queueConfiguration
 */
?>
<?php $this->assign('title', __('Queue Configuration Details')); ?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h3 mb-0"><?= __('Queue Configuration Details') ?></h1>
            <span class="badge bg-info ms-2"><?= h($queueConfiguration->name) ?></span>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $queueConfiguration->id], ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $queueConfiguration->id],
                [
                    'confirm' => __('Are you sure you want to delete the queue configuration "{0}"?', $queueConfiguration->name),
                    'class' => 'btn btn-danger'
                ]
            ) ?>
            <?= $this->Html->link(__('Back to List'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <!-- Status & Overview -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i><?= __('Status & Overview') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong><?= __('Status:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <?php if ($queueConfiguration->enabled): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-check-circle me-1"></i><?= __('Enabled') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger">
                                    <i class="fas fa-times-circle me-1"></i><?= __('Disabled') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong><?= __('Queue Type:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            $typeClass = $queueConfiguration->queue_type === 'redis' ? 'bg-info' : 'bg-warning';
                            $typeIcon = $queueConfiguration->queue_type === 'redis' ? 'fas fa-database' : 'fas fa-rabbit';
                            ?>
                            <span class="badge <?= $typeClass ?> text-dark">
                                <i class="<?= $typeIcon ?> me-1"></i><?= ucfirst($queueConfiguration->queue_type) ?>
                            </span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong><?= __('Priority:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <?php
                            $priorityClass = $queueConfiguration->priority >= 8 ? 'bg-danger' : 
                                           ($queueConfiguration->priority >= 5 ? 'bg-warning text-dark' : 'bg-secondary');
                            ?>
                            <span class="badge <?= $priorityClass ?>"><?= $queueConfiguration->priority ?>/10</span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong><?= __('Max Workers:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <span class="badge bg-secondary"><?= $queueConfiguration->max_workers ?></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-6">
                            <strong><?= __('Created:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <?= $queueConfiguration->created->format('M j, Y g:i A') ?>
                            </small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <strong><?= __('Modified:') ?></strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted">
                                <?= $queueConfiguration->modified->format('M j, Y g:i A') ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Basic Configuration -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i><?= __('Basic Configuration') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong><?= __('Display Name:') ?></strong>
                        </div>
                        <div class="col-sm-9">
                            <?= h($queueConfiguration->name) ?>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong><?= __('Config Key:') ?></strong>
                        </div>
                        <div class="col-sm-9">
                            <code class="bg-light px-2 py-1 rounded"><?= h($queueConfiguration->config_key) ?></code>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <strong><?= __('Queue Name:') ?></strong>
                        </div>
                        <div class="col-sm-9">
                            <code><?= h($queueConfiguration->queue_name) ?></code>
                        </div>
                    </div>

                    <?php if (!empty($queueConfiguration->description)): ?>
                        <div class="row">
                            <div class="col-sm-3">
                                <strong><?= __('Description:') ?></strong>
                            </div>
                            <div class="col-sm-9">
                                <?= nl2br(h($queueConfiguration->description)) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Connection Settings -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-network-wired me-2"></i><?= __('Connection Settings') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong><?= __('Host:') ?></strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="font-monospace"><?= h($queueConfiguration->host) ?></span>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong><?= __('Port:') ?></strong>
                        </div>
                        <div class="col-sm-8">
                            <span class="font-monospace"><?= $queueConfiguration->port ?></span>
                        </div>
                    </div>

                    <?php if (!empty($queueConfiguration->username)): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong><?= __('Username:') ?></strong>
                            </div>
                            <div class="col-sm-8">
                                <span class="font-monospace"><?= h($queueConfiguration->username) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong><?= __('Password:') ?></strong>
                        </div>
                        <div class="col-sm-8">
                            <?php if (!empty($queueConfiguration->password)): ?>
                                <span class="badge bg-success"><?= __('Set') ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= __('Not Set') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($queueConfiguration->queue_type === 'redis'): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong><?= __('Database:') ?></strong>
                            </div>
                            <div class="col-sm-8">
                                <span class="font-monospace"><?= $queueConfiguration->db_index ?? 0 ?></span>
                            </div>
                        </div>
                    <?php elseif ($queueConfiguration->queue_type === 'rabbitmq'): ?>
                        <div class="row mb-3">
                            <div class="col-sm-4">
                                <strong><?= __('Virtual Host:') ?></strong>
                            </div>
                            <div class="col-sm-8">
                                <span class="font-monospace"><?= h($queueConfiguration->vhost ?? '/') ?></span>
                            </div>
                        </div>

                        <?php if (!empty($queueConfiguration->exchange)): ?>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong><?= __('Exchange:') ?></strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="font-monospace"><?= h($queueConfiguration->exchange) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($queueConfiguration->routing_key)): ?>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <strong><?= __('Routing Key:') ?></strong>
                                </div>
                                <div class="col-sm-8">
                                    <span class="font-monospace"><?= h($queueConfiguration->routing_key) ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="row mb-3">
                        <div class="col-sm-4">
                            <strong><?= __('SSL Enabled:') ?></strong>
                        </div>
                        <div class="col-sm-8">
                            <?php if ($queueConfiguration->ssl_enabled): ?>
                                <span class="badge bg-success">
                                    <i class="fas fa-lock me-1"></i><?= __('Yes') ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="fas fa-unlock me-1"></i><?= __('No') ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-4">
                            <strong><?= __('Persistent:') ?></strong>
                        </div>
                        <div class="col-sm-8">
                            <?php if ($queueConfiguration->persistent): ?>
                                <span class="badge bg-success"><?= __('Yes') ?></span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><?= __('No') ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generated Configuration -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-code me-2"></i><?= __('Generated Configuration') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        <?= __('This is how this configuration appears in the queue.php file:') ?>
                    </p>
                    
                    <pre class="bg-light p-3 rounded"><code><?php
                    // Build sample config array for display
                    $sampleConfig = [
                        'queue' => $queueConfiguration->queue_name,
                        'host' => $queueConfiguration->host,
                        'port' => $queueConfiguration->port,
                        'persistent' => $queueConfiguration->persistent,
                    ];

                    if ($queueConfiguration->queue_type === 'rabbitmq') {
                        $protocol = $queueConfiguration->ssl_enabled ? 'amqps' : 'amqp';
                        $auth = '';
                        if ($queueConfiguration->username) {
                            $auth = $queueConfiguration->username;
                            if ($queueConfiguration->password) {
                                $auth .= ':***';
                            }
                            $auth .= '@';
                        }
                        $vhost = urlencode($queueConfiguration->vhost ?? '/');
                        $sampleConfig['url'] = sprintf('%s://%s%s:%d/%s', $protocol, $auth, $queueConfiguration->host, $queueConfiguration->port, $vhost);
                        $sampleConfig['username'] = $queueConfiguration->username;
                        $sampleConfig['password'] = '***';
                        $sampleConfig['exchange'] = $queueConfiguration->exchange;
                        $sampleConfig['routing_key'] = $queueConfiguration->routing_key;
                        $sampleConfig['vhost'] = $queueConfiguration->vhost ?? '/';
                        $sampleConfig['ssl'] = $queueConfiguration->ssl_enabled;
                    } else {
                        $auth = '';
                        if ($queueConfiguration->password) {
                            $auth = ':***@';
                        }
                        $sampleConfig['url'] = sprintf('redis://%s%s:%d/%d', $auth, $queueConfiguration->host, $queueConfiguration->port, $queueConfiguration->db_index ?? 0);
                        $sampleConfig['database'] = $queueConfiguration->db_index ?? 0;
                    }

                    // Clean up null values for display
                    $sampleConfig = array_filter($sampleConfig, function($value) {
                        return $value !== null && $value !== '';
                    });

                    echo "'{$queueConfiguration->config_key}' => [\n";
                    foreach ($sampleConfig as $key => $value) {
                        $valueDisplay = is_bool($value) ? ($value ? 'true' : 'false') : (is_numeric($value) ? $value : "'" . $value . "'");
                        echo "    '{$key}' => {$valueDisplay},\n";
                    }
                    echo "]";
                    ?></code></pre>

                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            <?= __('Password values are masked for security') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($queueConfiguration->config_data)): ?>
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-database me-2"></i><?= __('Additional Configuration Data') ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <pre class="bg-light p-3 rounded"><code><?= json_encode(json_decode($queueConfiguration->config_data), JSON_PRETTY_PRINT) ?></code></pre>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Action buttons -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-center gap-2">
                        <?= $this->Html->link(
                            '<i class="fas fa-edit me-1"></i>' . __('Edit Configuration'),
                            ['action' => 'edit', $queueConfiguration->id],
                            ['class' => 'btn btn-primary', 'escape' => false]
                        ) ?>
                        
                        <?= $this->Html->link(
                            '<i class="fas fa-sync me-1"></i>' . __('Sync All Configurations'),
                            ['action' => 'sync'],
                            [
                                'class' => 'btn btn-success',
                                'escape' => false,
                                'confirm' => __('This will update the queue.php config file with current database settings. Continue?')
                            ]
                        ) ?>
                        
                        <?= $this->Form->postLink(
                            '<i class="fas fa-trash me-1"></i>' . __('Delete Configuration'),
                            ['action' => 'delete', $queueConfiguration->id],
                            [
                                'confirm' => __('Are you sure you want to delete the queue configuration "{0}"? This action cannot be undone.', $queueConfiguration->name),
                                'class' => 'btn btn-danger',
                                'escape' => false
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>