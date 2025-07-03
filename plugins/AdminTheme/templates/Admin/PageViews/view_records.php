<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var \Cake\Collection\CollectionInterface $viewRecords
 */

// Helper function to extract browser name from user agent
function extractBrowserName($userAgent) {
    $browsers = [
        'Chrome' => '/Chrome\/[\d.]+/',
        'Firefox' => '/Firefox\/[\d.]+/',
        'Safari' => '/Safari\/[\d.]+/',
        'Edge' => '/Edg\/[\d.]+/',
        'Opera' => '/OPR\/[\d.]+/',
        'Internet Explorer' => '/MSIE [\d.]+/',
    ];

    foreach ($browsers as $browser => $pattern) {
        if (preg_match($pattern, $userAgent)) {
            return $browser;
        }
    }

    return 'Other';
}
?>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><?= __('Page Views for {0}', h($article->title)) ?></h3>
                </div>
                <div class="card-body">
                    <div class="article-details mb-4">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <p class="mb-2"><strong><?= __('Article ID:') ?></strong> <span class="badge bg-secondary"><?= h($article->id) ?></span></p>
                            </div>
                            <div class="col-12 col-md-6">
                                <p class="mb-2"><strong><?= __('Slug:') ?></strong> <code><?= h($article->slug) ?></code></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0"><?= __('View Records') ?></h4>
                        <span class="badge bg-info"><?= number_format($viewRecords->count()) ?> <?= __('records') ?></span>
                    </div>
                    
                    <?php if (!$viewRecords->isEmpty()): ?>
                        <!-- Desktop Table -->
                        <div class="table-responsive d-none d-lg-block">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><?= __('Date & Time') ?></th>
                                        <th><?= __('IP Address') ?></th>
                                        <th><?= __('Browser') ?></th>
                                        <th><?= __('Referer') ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($viewRecords as $record): ?>
                                        <tr>
                                            <td>
                                                <div><?= $record->created->format('M j, Y') ?></div>
                                                <small class="text-muted"><?= $record->created->format('H:i:s') ?></small>
                                            </td>
                                            <td>
                                                <code><?= h($record->ip_address) ?></code>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?= h($record->user_agent) ?>">
                                                    <?= h(extractBrowserName($record->user_agent)) ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($record->referer): ?>
                                                    <div class="text-truncate" style="max-width: 200px;" title="<?= h($record->referer) ?>">
                                                        <?= h(parse_url($record->referer, PHP_URL_HOST) ?: $record->referer) ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted"><?= __('Direct') ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile/Tablet Cards -->
                        <div class="d-lg-none">
                            <?php foreach ($viewRecords as $record): ?>
                                <div class="card mb-3 view-record-card">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-12 col-sm-6">
                                                <h6 class="card-title mb-2">
                                                    <i class="fas fa-clock text-primary me-1"></i>
                                                    <?= $record->created->format('M j, Y H:i') ?>
                                                </h6>
                                                <p class="mb-2">
                                                    <strong><?= __('IP:') ?></strong> 
                                                    <code><?= h($record->ip_address) ?></code>
                                                </p>
                                            </div>
                                            <div class="col-12 col-sm-6">
                                                <p class="mb-2">
                                                    <strong><?= __('Browser:') ?></strong><br>
                                                    <span class="text-muted"><?= h(extractBrowserName($record->user_agent)) ?></span>
                                                </p>
                                                <p class="mb-0">
                                                    <strong><?= __('Source:') ?></strong><br>
                                                    <?php if ($record->referer): ?>
                                                        <span class="text-muted text-break"><?= h(parse_url($record->referer, PHP_URL_HOST) ?: $record->referer) ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= __('Direct') ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info d-flex align-items-center" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <?= __('No view records found for this article.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.view-record-card {
    border: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;
}

.view-record-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    font-size: 0.875rem;
}

@media (max-width: 992px) {
    .article-details .row {
        gap: 0.5rem;
    }
}
</style>