<?php
/**
 * @var \App\View\AppView $this
 * @var bool $featureEnabled
 * @var array $statistics
 * @var array $articlesNeedingImages
 * @var array $rateLimitStatus
 * @var array $configStatus
 */

$this->assign('title', __('AI Image Generation Dashboard'));
?>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><?= __('AI Image Generation Dashboard') ?></h2>
                <div class="btn-group" role="group">
                    <?= $this->Html->link(__('Statistics'), ['action' => 'statistics'], ['class' => 'btn btn-outline-info']) ?>
                    <?= $this->Html->link(__('Batch Process'), ['action' => 'batch'], ['class' => 'btn btn-outline-primary']) ?>
                    <?= $this->Html->link(__('Configuration'), ['action' => 'config'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!$featureEnabled): ?>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert alert-warning" role="alert">
                <h5 class="alert-heading"><?= __('Feature Not Enabled') ?></h5>
                <p><?= __('AI image generation is currently disabled. Please enable it in the settings to use this feature.') ?></p>
                <hr>
                <p class="mb-0">
                    <?= $this->Html->link(__('Go to Settings'), ['controller' => 'Settings', 'action' => 'index'], ['class' => 'btn btn-warning']) ?>
                </p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= __('Total Generated') ?></h5>
                            <h2 class="mb-0"><?= number_format($statistics['total_generated']) ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-image fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= __('Success Rate') ?></h5>
                            <h2 class="mb-0"><?= number_format($statistics['success_rate'], 1) ?>%</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= __('Articles Needing Images') ?></h5>
                            <h2 class="mb-0"><?= number_format(count($articlesNeedingImages)) ?>+</h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card <?= $rateLimitStatus['exceeded'] ? 'bg-danger' : 'bg-secondary' ?> text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h5 class="card-title"><?= __('Rate Limits') ?></h5>
                            <h2 class="mb-0"><?= $rateLimitStatus['exceeded'] ? __('EXCEEDED') : __('OK') ?></h2>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-tachometer-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuration Status -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Configuration Status') ?></h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <?= __('AI Features Enabled') ?>
                            <span class="badge <?= $configStatus['ai_enabled'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $configStatus['ai_enabled'] ? __('Yes') : __('No') ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <?= __('Image Generation Enabled') ?>
                            <span class="badge <?= $configStatus['image_generation_enabled'] ? 'bg-success' : 'bg-danger' ?>">
                                <?= $configStatus['image_generation_enabled'] ? __('Yes') : __('No') ?>
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <?= __('Primary Provider') ?>
                            <span class="badge bg-info"><?= h($configStatus['primary_provider'] ?: __('Not Set')) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <?= __('Model') ?>
                            <span class="badge bg-secondary"><?= h($configStatus['model'] ?: __('Default')) ?></span>
                        </li>
                    </ul>
                    
                    <div class="mt-3">
                        <h6><?= __('API Keys Status') ?></h6>
                        <div class="row">
                            <?php foreach ($configStatus['api_keys'] as $provider => $hasKey): ?>
                            <div class="col-md-4 mb-2">
                                <span class="badge <?= $hasKey ? 'bg-success' : 'bg-secondary' ?> w-100">
                                    <?= h(ucfirst($provider)) ?>: <?= $hasKey ? __('Set') : __('Missing') ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Limits Detail -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?= __('Rate Limits') ?></h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= __('Per Minute') ?></span>
                            <span><?= $rateLimitStatus['minute_count'] ?> / <?= $rateLimitStatus['minute_limit'] ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $rateLimitStatus['minute_count'] >= $rateLimitStatus['minute_limit'] ? 'bg-danger' : 'bg-info' ?>" 
                                 style="width: <?= min(100, ($rateLimitStatus['minute_count'] / $rateLimitStatus['minute_limit']) * 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= __('Per Hour') ?></span>
                            <span><?= $rateLimitStatus['hour_count'] ?> / <?= $rateLimitStatus['hour_limit'] ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $rateLimitStatus['hour_count'] >= $rateLimitStatus['hour_limit'] ? 'bg-danger' : 'bg-warning' ?>" 
                                 style="width: <?= min(100, ($rateLimitStatus['hour_count'] / $rateLimitStatus['hour_limit']) * 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span><?= __('Per Day') ?></span>
                            <span><?= $rateLimitStatus['day_count'] ?> / <?= $rateLimitStatus['day_limit'] ?></span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar <?= $rateLimitStatus['day_count'] >= $rateLimitStatus['day_limit'] ? 'bg-danger' : 'bg-primary' ?>" 
                                 style="width: <?= min(100, ($rateLimitStatus['day_count'] / $rateLimitStatus['day_limit']) * 100) ?>%"></div>
                        </div>
                    </div>
                    
                    <?php if ($rateLimitStatus['exceeded']): ?>
                    <div class="alert alert-danger alert-sm mt-3 mb-0" role="alert">
                        <?= __('Rate limits have been exceeded. Image generation is currently paused.') ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($articlesNeedingImages)): ?>
    <!-- Recent Articles Needing Images -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0"><?= __('Recent Articles Needing Images') ?></h5>
                    <?= $this->Html->link(__('Process All'), ['action' => 'batch'], ['class' => 'btn btn-primary btn-sm']) ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th><?= __('Title') ?></th>
                                    <th><?= __('Published') ?></th>
                                    <th><?= __('Word Count') ?></th>
                                    <th><?= __('Actions') ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($articlesNeedingImages, 0, 10) as $article): ?>
                                <tr>
                                    <td>
                                        <?= $this->Html->link(
                                            h($article->title),
                                            ['controller' => 'Articles', 'action' => 'view', $article->id],
                                            ['class' => 'text-decoration-none']
                                        ) ?>
                                    </td>
                                    <td>
                                        <?= $article->published ? $article->published->format('M j, Y') : __('Draft') ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= number_format($article->word_count ?? 0) ?></span>
                                    </td>
                                    <td>
                                        <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <?php if (count($articlesNeedingImages) > 10): ?>
                    <div class="text-center mt-3">
                        <p class="text-muted">
                            <?= __('Showing 10 of {0} articles that need images.', number_format(count($articlesNeedingImages))) ?>
                            <?= $this->Html->link(__('View All'), ['action' => 'batch'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
// Auto-refresh rate limit information every 30 seconds
setInterval(function() {
    // Only refresh if not showing a modal or form
    if (!document.querySelector('.modal.show') && !document.querySelector('form:focus-within')) {
        fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Update only the rate limits section
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newRateLimits = doc.querySelector('.card:has(.card-title:contains("Rate Limits"))');
            const currentRateLimits = document.querySelector('.card:has(.card-title:contains("Rate Limits"))');
            
            if (newRateLimits && currentRateLimits) {
                currentRateLimits.innerHTML = newRateLimits.innerHTML;
            }
        })
        .catch(error => {
            console.log('Rate limit refresh failed:', error);
        });
    }
}, 30000);
<?php $this->Html->scriptEnd(); ?>