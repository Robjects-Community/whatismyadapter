<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $pages
 */
?>

<div class="pages index content">
    <?= $this->Html->link(__('New Page'), ['action' => 'add'], ['class' => 'btn btn-primary float-end']) ?>
    <h3><?= __('Pages') ?></h3>
    
    <div class="row mb-3">
        <div class="col-md-8">
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'form-inline']) ?>
            <div class="row g-3">
                <div class="col-auto">
                    <?= $this->Form->control('search', [
                        'type' => 'text',
                        'placeholder' => 'Search pages...',
                        'value' => $search ?? '',
                        'class' => 'form-control',
                        'label' => false
                    ]) ?>
                </div>
                <div class="col-auto">
                    <?= $this->Form->control('status', [
                        'type' => 'select',
                        'options' => ['' => 'All Status', '1' => 'Published', '0' => 'Unpublished'],
                        'value' => $statusFilter ?? '',
                        'class' => 'form-select',
                        'label' => false,
                        'empty' => false
                    ]) ?>
                </div>
                <div class="col-auto">
                    <?= $this->Form->control('menu', [
                        'type' => 'select',
                        'options' => [
                            '' => 'All Menu Types', 
                            'header' => 'Header Menu Only',
                            'footer' => 'Footer Menu Only',
                            'both' => 'Both Menus',
                            'none' => 'No Menus'
                        ],
                        'value' => $menuFilter ?? '',
                        'class' => 'form-select',
                        'label' => false,
                        'empty' => false
                    ]) ?>
                </div>
                <div class="col-auto">
                    <?= $this->Form->button(__('Filter'), ['class' => 'btn btn-outline-secondary']) ?>
                    <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </div>
            <?= $this->Form->end() ?>
        </div>
        <div class="col-md-4 text-end">
            <small class="text-muted">
                <strong><?= $totalPages ?? 0 ?></strong> total pages | 
                <span class="text-success"><?= $publishedPages ?? 0 ?></span> published | 
                <span class="text-warning"><?= $unpublishedPages ?? 0 ?></span> unpublished
            </small>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col"><?= $this->Paginator->sort('title', 'Title') ?></th>
                    <th scope="col"><?= $this->Paginator->sort('slug', 'Slug') ?></th>
                    <th scope="col">Menu</th>
                    <th scope="col"><?= $this->Paginator->sort('is_published', 'Status') ?></th>
                    <th scope="col"><?= $this->Paginator->sort('modified', 'Modified') ?></th>
                    <th scope="col">Author</th>
                    <th scope="col" class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pages as $page): ?>
                <tr>
                    <td>
                        <strong><?= h($page->title) ?></strong>
                        <?php if (!empty($page->meta_title)): ?>
                            <br><small class="text-muted"><?= h($page->meta_title) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <code><?= h($page->slug) ?></code>
                        <br>
                        <?= $this->Html->link(
                            '<i class="fas fa-external-link-alt"></i> View',
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $page->slug, 'prefix' => false],
                            ['class' => 'btn btn-sm btn-outline-primary', 'escape' => false, 'target' => '_blank']
                        ) ?>
                    </td>
                    <td>
                        <?php if ($page->main_menu && $page->footer_menu): ?>
                            <span class="badge bg-success">Both</span>
                        <?php elseif ($page->main_menu): ?>
                            <span class="badge bg-info">Header</span>
                        <?php elseif ($page->footer_menu): ?>
                            <span class="badge bg-secondary">Footer</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark">None</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($page->is_published): ?>
                            <span class="badge bg-success">Published</span>
                        <?php else: ?>
                            <span class="badge bg-warning">Draft</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= $page->modified ? $page->modified->format('M j, Y g:i A') : 'Never' ?>
                    </td>
                    <td>
                        <?= h($page->user->username ?? 'Unknown') ?>
                    </td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $page->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $page->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $page->id], ['confirm' => __('Are you sure you want to delete # {0}?', $page->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <?php if (empty($pages->toArray())): ?>
        <div class="alert alert-info">
            <h5>No pages found</h5>
            <p class="mb-0">
                <?php if (!empty($search) || isset($statusFilter) || isset($menuFilter)): ?>
                    No pages match your current filters. <?= $this->Html->link('Clear filters', ['action' => 'index']) ?> to see all pages.
                <?php else: ?>
                    You haven't created any pages yet. <?= $this->Html->link('Create your first page', ['action' => 'add']) ?>.
                <?php endif; ?>
            </p>
        </div>
    <?php endif; ?>

    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <?= $this->Html->link(
                    '<i class="fas fa-magic"></i> Generate Connect Pages', 
                    ['action' => 'generateConnectPages'], 
                    [
                        'class' => 'btn btn-success me-2', 
                        'escape' => false,
                        'confirm' => 'This will create standard connect pages (About Author, GitHub, Hire Me, Follow Me) if they don\'t exist. Continue?'
                    ]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-plus"></i> New Page', 
                    ['action' => 'add'], 
                    ['class' => 'btn btn-primary me-2', 'escape' => false]
                ) ?>
                <?= $this->Html->link(
                    '<i class="fas fa-list"></i> View All Articles', 
                    ['controller' => 'Articles', 'action' => 'index'], 
                    ['class' => 'btn btn-outline-secondary', 'escape' => false]
                ) ?>
            </div>
        </div>
    </div>
</div>