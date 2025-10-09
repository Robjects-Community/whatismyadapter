<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\QueueConfiguration> $queueConfigurations
 * @var string $search
 */
?>
<div class="row">
    <?php if (empty($queueConfigurations)): ?>
        <div class="col-12">
            <div class="alert alert-warning text-center" role="alert">
                <i class="fas fa-search fa-2x text-muted mb-2"></i>
                <h5><?= __('No Results Found') ?></h5>
                <p class="mb-0">
                    <?= __('No queue configurations match your search for "{0}". Try different keywords or create a new configuration.', h($search)) ?>
                </p>
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
                <?= $this->element('pagination', ['recordCount' => count($queueConfigurations), 'search' => $search]) ?>
            </div>
        </div>
    <?php endif; ?>
</div>