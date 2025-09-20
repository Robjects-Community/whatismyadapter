<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\QueueConfiguration $queueConfiguration
 */
?>
<?php $this->assign('title', __('Edit Queue Configuration')); ?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h3 mb-0"><?= __('Edit Queue Configuration') ?></h1>
            <span class="badge bg-info ms-2"><?= h($queueConfiguration->name) ?></span>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(__('View'), ['action' => 'view', $queueConfiguration->id], ['class' => 'btn btn-info']) ?>
            <?= $this->Html->link(__('Back to List'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
        </div>
    </div>
</header>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <?= $this->Form->create($queueConfiguration, ['class' => 'needs-validation', 'novalidate' => true, 'id' => 'queueConfigForm']) ?>
            
            <div class="row">
                <!-- Basic Configuration -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-cogs me-2"></i><?= __('Basic Configuration') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <?= $this->Form->control('name', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : ''),
                                    'label' => __('Display Name'),
                                    'placeholder' => __('e.g., High Priority Image Processing'),
                                    'required' => true
                                ]) ?>
                                <?php if ($this->Form->isFieldError('name')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('name') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <?= $this->Form->control('config_key', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('config_key') ? ' is-invalid' : ''),
                                    'label' => __('Configuration Key'),
                                    'placeholder' => __('e.g., image_analysis, fast_jobs, heavy_processing'),
                                    'required' => true
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('Unique identifier used in queue.php config file') ?>
                                </small>
                                <?php if ($this->Form->isFieldError('config_key')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('config_key') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <?= $this->Form->control('queue_type', [
                                    'type' => 'select',
                                    'options' => [
                                        'redis' => __('Redis'),
                                        'rabbitmq' => __('RabbitMQ')
                                    ],
                                    'class' => 'form-select' . ($this->Form->isFieldError('queue_type') ? ' is-invalid' : ''),
                                    'label' => __('Queue Type'),
                                    'id' => 'queueType'
                                ]) ?>
                                <?php if ($this->Form->isFieldError('queue_type')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('queue_type') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <?= $this->Form->control('queue_name', [
                                    'class' => 'form-control' . ($this->Form->isFieldError('queue_name') ? ' is-invalid' : ''),
                                    'label' => __('Queue Name'),
                                    'placeholder' => __('e.g., image_analysis, default, emails'),
                                    'required' => true
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('Name of the queue to process jobs from') ?>
                                </small>
                                <?php if ($this->Form->isFieldError('queue_name')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('queue_name') ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <?= $this->Form->control('description', [
                                    'type' => 'textarea',
                                    'class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : ''),
                                    'label' => __('Description'),
                                    'placeholder' => __('Description of what this queue handles'),
                                    'rows' => 3
                                ]) ?>
                                <?php if ($this->Form->isFieldError('description')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('description') ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Connection Settings -->
                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-network-wired me-2"></i><?= __('Connection Settings') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <?= $this->Form->control('host', [
                                            'class' => 'form-control' . ($this->Form->isFieldError('host') ? ' is-invalid' : ''),
                                            'label' => __('Host'),
                                            'placeholder' => __('localhost, redis, rabbitmq'),
                                            'required' => true
                                        ]) ?>
                                        <?php if ($this->Form->isFieldError('host')): ?>
                                            <div class="invalid-feedback"><?= $this->Form->error('host') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <?= $this->Form->control('port', [
                                            'type' => 'number',
                                            'class' => 'form-control' . ($this->Form->isFieldError('port') ? ' is-invalid' : ''),
                                            'label' => __('Port'),
                                            'placeholder' => __('6379 / 5672'),
                                            'id' => 'portField'
                                        ]) ?>
                                        <?php if ($this->Form->isFieldError('port')): ?>
                                            <div class="invalid-feedback"><?= $this->Form->error('port') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <div class="row" id="authFields">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <?= $this->Form->control('username', [
                                            'class' => 'form-control' . ($this->Form->isFieldError('username') ? ' is-invalid' : ''),
                                            'label' => __('Username'),
                                            'placeholder' => __('Optional username')
                                        ]) ?>
                                        <?php if ($this->Form->isFieldError('username')): ?>
                                            <div class="invalid-feedback"><?= $this->Form->error('username') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <?= $this->Form->control('password', [
                                            'type' => 'password',
                                            'class' => 'form-control' . ($this->Form->isFieldError('password') ? ' is-invalid' : ''),
                                            'label' => __('Password'),
                                            'placeholder' => __('Leave empty to keep current password'),
                                            'value' => '' // Don't show existing password
                                        ]) ?>
                                        <small class="form-text text-muted">
                                            <?= __('Leave empty to keep the current password') ?>
                                        </small>
                                        <?php if ($this->Form->isFieldError('password')): ?>
                                            <div class="invalid-feedback"><?= $this->Form->error('password') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Redis-specific fields -->
                            <div id="redisFields">
                                <div class="mb-3">
                                <?= $this->Form->control('db_index', [
                                    'type' => 'number',
                                    'class' => 'form-control' . ($this->Form->isFieldError('db_index') ? ' is-invalid' : ''),
                                    'label' => __('Database Number'),
                                    'placeholder' => __('0'),
                                    'min' => 0,
                                    'max' => 15
                                ]) ?>
                                <small class="form-text text-muted">
                                    <?= __('Redis database number (0-15)') ?>
                                </small>
                                <?php if ($this->Form->isFieldError('db_index')): ?>
                                    <div class="invalid-feedback"><?= $this->Form->error('db_index') ?></div>
                                <?php endif; ?>
                                </div>
                            </div>

                            <!-- RabbitMQ-specific fields -->
                            <div id="rabbitmqFields" style="display: none;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('vhost', [
                                                'class' => 'form-control' . ($this->Form->isFieldError('vhost') ? ' is-invalid' : ''),
                                                'label' => __('Virtual Host'),
                                                'placeholder' => __('/')
                                            ]) ?>
                                            <?php if ($this->Form->isFieldError('vhost')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('vhost') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <?= $this->Form->control('exchange', [
                                                'class' => 'form-control' . ($this->Form->isFieldError('exchange') ? ' is-invalid' : ''),
                                                'label' => __('Exchange'),
                                                'placeholder' => __('amq.direct')
                                            ]) ?>
                                            <?php if ($this->Form->isFieldError('exchange')): ?>
                                                <div class="invalid-feedback"><?= $this->Form->error('exchange') ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <?= $this->Form->control('routing_key', [
                                        'class' => 'form-control' . ($this->Form->isFieldError('routing_key') ? ' is-invalid' : ''),
                                        'label' => __('Routing Key'),
                                        'placeholder' => __('Optional routing key')
                                    ]) ?>
                                    <?php if ($this->Form->isFieldError('routing_key')): ?>
                                        <div class="invalid-feedback"><?= $this->Form->error('routing_key') ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <?= $this->Form->checkbox('ssl_enabled', [
                                        'class' => 'form-check-input',
                                        'id' => 'sslEnabled'
                                    ]) ?>
                                    <label class="form-check-label" for="sslEnabled">
                                        <?= __('Enable SSL Connection') ?>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <?= $this->Form->checkbox('persistent', [
                                        'class' => 'form-check-input',
                                        'id' => 'persistentConnection'
                                    ]) ?>
                                    <label class="form-check-label" for="persistentConnection">
                                        <?= __('Use Persistent Connection') ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Queue Settings -->
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sliders-h me-2"></i><?= __('Queue Settings') ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <?= $this->Form->control('priority', [
                                            'type' => 'range',
                                            'class' => 'form-range',
                                            'min' => 1,
                                            'max' => 10,
                                            'id' => 'priorityRange',
                                            'label' => __('Priority') . ' <span id="priorityValue">' . $queueConfiguration->priority . '</span>'
                                        ]) ?>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted"><?= __('Low (1)') ?></small>
                                            <small class="text-muted"><?= __('High (10)') ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <?= $this->Form->control('max_workers', [
                                            'type' => 'number',
                                            'class' => 'form-control' . ($this->Form->isFieldError('max_workers') ? ' is-invalid' : ''),
                                            'label' => __('Max Workers'),
                                            'min' => 1,
                                            'max' => 20,
                                            'placeholder' => __('1')
                                        ]) ?>
                                        <small class="form-text text-muted">
                                            <?= __('Maximum worker processes') ?>
                                        </small>
                                        <?php if ($this->Form->isFieldError('max_workers')): ?>
                                            <div class="invalid-feedback"><?= $this->Form->error('max_workers') ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <div class="form-check mt-4">
                                            <?= $this->Form->checkbox('enabled', [
                                                'class' => 'form-check-input',
                                                'id' => 'configEnabled'
                                            ]) ?>
                                            <label class="form-check-label" for="configEnabled">
                                                <?= __('Enable Configuration') ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <small class="text-muted">
                                        <?= __('Created: {0}', $queueConfiguration->created->format('M j, Y g:i A')) ?><br>
                                        <?= __('Modified: {0}', $queueConfiguration->modified->format('M j, Y g:i A')) ?>
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    <?= $this->Html->link(__('Cancel'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
                                    <?= $this->Form->button(__('Update Configuration'), ['class' => 'btn btn-primary']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const queueTypeSelect = document.getElementById('queueType');
    const portField = document.getElementById('portField');
    const redisFields = document.getElementById('redisFields');
    const rabbitmqFields = document.getElementById('rabbitmqFields');
    const priorityRange = document.getElementById('priorityRange');
    const priorityValue = document.getElementById('priorityValue');

    // Handle queue type change
    function handleQueueTypeChange() {
        const selectedType = queueTypeSelect.value;
        
        if (selectedType === 'redis') {
            redisFields.style.display = 'block';
            rabbitmqFields.style.display = 'none';
        } else if (selectedType === 'rabbitmq') {
            redisFields.style.display = 'none';
            rabbitmqFields.style.display = 'block';
        }
    }

    // Handle priority range change
    function handlePriorityChange() {
        priorityValue.textContent = priorityRange.value;
    }

    // Initialize
    handleQueueTypeChange();
    handlePriorityChange();

    // Event listeners
    queueTypeSelect.addEventListener('change', handleQueueTypeChange);
    priorityRange.addEventListener('input', handlePriorityChange);

    // Form validation
    const form = document.getElementById('queueConfigForm');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});
<?php $this->Html->scriptEnd(); ?>