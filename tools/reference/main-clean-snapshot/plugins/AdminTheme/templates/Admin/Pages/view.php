<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $page
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0">
                <i class="bi bi-eye me-2"></i>
                <?= __('View Page: {0}', h($page->title)) ?>
            </h1>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-arrow-left me-1"></i>' . __('Back to Pages'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary me-2', 'escape' => false]
            ) ?>
            
            <?= $this->Html->link(
                '<i class="bi bi-pencil me-1"></i>' . __('Edit'),
                ['action' => 'edit', $page->id],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>

<div class="row">
    <div class="col-lg-8">
        <!-- Page Content -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-file-text me-2"></i>
                        <?= __('Page Content') ?>
                    </h5>
                    <div>
                        <?php if ($page->is_published): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                <?= __('Published') ?>
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock me-1"></i>
                                <?= __('Draft') ?>
                            </span>
                        <?php endif; ?>
                        
                        <?= $this->Html->link(
                            '<i class="bi bi-eye me-1"></i>' . __('View Live'),
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $page->slug, 'prefix' => false],
                            [
                                'class' => 'btn btn-outline-primary btn-sm ms-2',
                                'escape' => false,
                                'target' => '_blank'
                            ]
                        ) ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <h1 class="mb-4"><?= h($page->title) ?></h1>
                
                <div class="page-content">
                    <?= $page->body ?>
                </div>
            </div>
        </div>

        <!-- SEO Information -->
        <?php if (!empty($page->meta_title) || !empty($page->meta_description) || !empty($page->meta_keywords)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-search me-2"></i>
                    <?= __('SEO Information') ?>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($page->meta_title)): ?>
                <div class="mb-3">
                    <label class="form-label text-muted"><?= __('Meta Title') ?></label>
                    <div class="border rounded p-2 bg-light">
                        <?= h($page->meta_title) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($page->meta_description)): ?>
                <div class="mb-3">
                    <label class="form-label text-muted"><?= __('Meta Description') ?></label>
                    <div class="border rounded p-2 bg-light">
                        <?= h($page->meta_description) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($page->meta_keywords)): ?>
                <div class="mb-0">
                    <label class="form-label text-muted"><?= __('Meta Keywords') ?></label>
                    <div class="border rounded p-2 bg-light">
                        <?php
                        $keywords = explode(',', $page->meta_keywords);
                        foreach ($keywords as $keyword):
                            $keyword = trim($keyword);
                            if (!empty($keyword)):
                        ?>
                            <span class="badge bg-secondary me-1"><?= h($keyword) ?></span>
                        <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <!-- Page Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    <?= __('Page Details') ?>
                </h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless table-sm">
                    <tr>
                        <td class="text-muted"><?= __('Slug') ?>:</td>
                        <td><code><?= h($page->slug) ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= __('Author') ?>:</td>
                        <td><?= h($page->user->username ?? __('Unknown')) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= __('Created') ?>:</td>
                        <td><?= $page->created->format('M d, Y H:i') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= __('Last Modified') ?>:</td>
                        <td><?= $page->modified->format('M d, Y H:i') ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><?= __('Status') ?>:</td>
                        <td>
                            <?php if ($page->is_published): ?>
                                <span class="badge bg-success"><?= __('Published') ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning"><?= __('Draft') ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Menu Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-menu-button-wide me-2"></i>
                    <?= __('Menu Settings') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" <?= $page->main_menu ? 'checked' : '' ?> disabled>
                        <label class="form-check-label text-muted">
                            <?= __('Show in header menu') ?>
                        </label>
                    </div>
                </div>
                
                <div class="mb-0">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" <?= $page->footer_menu ? 'checked' : '' ?> disabled>
                        <label class="form-check-label text-muted">
                            <?= __('Show in footer menu') ?>
                        </label>
                    </div>
                </div>

                <?php if (!$page->main_menu && !$page->footer_menu): ?>
                    <div class="mt-3">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <?= __('This page is not displayed in any menu.') ?>
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bi bi-lightning me-2"></i>
                    <?= __('Quick Actions') ?>
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?= $this->Html->link(
                        '<i class="bi bi-pencil me-2"></i>' . __('Edit Page'),
                        ['action' => 'edit', $page->id],
                        ['class' => 'btn btn-primary', 'escape' => false]
                    ) ?>
                    
                    <?= $this->Html->link(
                        '<i class="bi bi-eye me-2"></i>' . __('View Live'),
                        ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $page->slug, 'prefix' => false],
                        [
                            'class' => 'btn btn-outline-success',
                            'escape' => false,
                            'target' => '_blank'
                        ]
                    ) ?>
                    
                    <?= $this->Html->link(
                        '<i class="bi bi-files me-2"></i>' . __('Duplicate Page'),
                        ['action' => 'add', '?' => ['duplicate' => $page->id]],
                        ['class' => 'btn btn-outline-secondary', 'escape' => false]
                    ) ?>
                    
                    <hr class="my-2">
                    
                    <?= $this->Form->postLink(
                        '<i class="bi bi-trash me-2"></i>' . __('Delete Page'),
                        ['action' => 'delete', $page->id],
                        [
                            'class' => 'btn btn-outline-danger',
                            'escape' => false,
                            'confirm' => __('Are you sure you want to delete "{0}"? This action cannot be undone.', $page->title)
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-content {
    line-height: 1.6;
}

.page-content h1, 
.page-content h2, 
.page-content h3, 
.page-content h4, 
.page-content h5, 
.page-content h6 {
    margin-top: 1.5rem;
    margin-bottom: 1rem;
}

.page-content p {
    margin-bottom: 1rem;
}

.page-content ul, 
.page-content ol {
    margin-bottom: 1rem;
    padding-left: 2rem;
}

.page-content blockquote {
    border-left: 4px solid #dee2e6;
    padding-left: 1rem;
    margin: 1.5rem 0;
    font-style: italic;
}

.page-content code {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    padding: 2px 4px;
}

.page-content pre {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 1rem;
    overflow-x: auto;
}
</style>