<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $pages
 * @var string|null $search
 * @var string|null $statusFilter
 * @var string|null $menuFilter
 * @var int $totalPages
 * @var int $publishedPages
 * @var int $unpublishedPages
 */
?>

<header class="py-3 mb-4 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <h1 class="h4 mb-0 me-4">
                <i class="bi bi-file-text me-2"></i>
                <?= __('Pages Management') ?>
            </h1>
            
            <!-- Filters -->
            <ul class="navbar-nav me-3">
                <!-- Status Filter -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-funnel me-1"></i>
                        <?= __('Status') ?>
                        <?php if ($statusFilter !== null): ?>
                            <span class="badge bg-primary ms-1">
                                <?= $statusFilter === '1' ? __('Published') : __('Unpublished') ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(
                                __('All Pages'),
                                ['action' => 'index'],
                                ['class' => 'dropdown-item' . (null === $statusFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('Published'),
                                ['action' => 'index', '?' => ['status' => 1]],
                                ['class' => 'dropdown-item' . ('1' === $statusFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('Unpublished'),
                                ['action' => 'index', '?' => ['status' => 0]],
                                ['class' => 'dropdown-item' . ('0' === $statusFilter ? ' active' : '')]
                            ) ?>
                        </li>
                    </ul>
                </li>
                
                <!-- Menu Filter -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-menu-button-wide me-1"></i>
                        <?= __('Menu') ?>
                        <?php if ($menuFilter !== null): ?>
                            <span class="badge bg-secondary ms-1"><?= h(ucfirst($menuFilter)) ?></span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(
                                __('All'),
                                ['action' => 'index'],
                                ['class' => 'dropdown-item' . (null === $menuFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('Header Menu'),
                                ['action' => 'index', '?' => ['menu' => 'header']],
                                ['class' => 'dropdown-item' . ('header' === $menuFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('Footer Menu'),
                                ['action' => 'index', '?' => ['menu' => 'footer']],
                                ['class' => 'dropdown-item' . ('footer' === $menuFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('Both Menus'),
                                ['action' => 'index', '?' => ['menu' => 'both']],
                                ['class' => 'dropdown-item' . ('both' === $menuFilter ? ' active' : '')]
                            ) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(
                                __('No Menu'),
                                ['action' => 'index', '?' => ['menu' => 'none']],
                                ['class' => 'dropdown-item' . ('none' === $menuFilter ? ' active' : '')]
                            ) ?>
                        </li>
                    </ul>
                </li>
            </ul>
            
            <!-- Search -->
            <form class="d-flex me-3" role="search">
                <input id="pageSearch" type="search" class="form-control" 
                       placeholder="<?= __('Search pages...') ?>" 
                       aria-label="Search" 
                       value="<?= h($search) ?>">
            </form>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="bi bi-plus-circle me-1"></i>' . __('New Page'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary', 'escape' => false]
            ) ?>
            
            <?= $this->Form->postLink(
                '<i class="bi bi-magic me-1"></i>' . __('Create Connect Pages'),
                ['action' => 'createConnectPages'],
                [
                    'class' => 'btn btn-success ms-2',
                    'escape' => false,
                    'confirm' => __('This will create standard connect pages (About, GitHub, Hire Me, Follow Me). Continue?')
                ]
            ) ?>
        </div>
    </div>
</header>

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-primary"><?= number_format($totalPages) ?></h5>
                <p class="card-text text-muted mb-0"><?= __('Total Pages') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-success"><?= number_format($publishedPages) ?></h5>
                <p class="card-text text-muted mb-0"><?= __('Published') ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h5 class="card-title text-warning"><?= number_format($unpublishedPages) ?></h5>
                <p class="card-text text-muted mb-0"><?= __('Unpublished') ?></p>
            </div>
        </div>
    </div>
</div>

<div id="ajax-target">
    <?php if (count($pages) > 0): ?>
        <!-- Bulk Actions Form -->
        <?= $this->Form->create(null, [
            'url' => ['action' => 'bulkActions'],
            'id' => 'bulk-actions-form'
        ]) ?>
        
        <div class="card">
            <div class="card-header bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="select-all">
                            <label class="form-check-label" for="select-all">
                                <?= __('Select All') ?>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="input-group" style="width: 250px; margin-left: auto;">
                            <select name="bulk_action" class="form-select" required>
                                <option value=""><?= __('Bulk Actions') ?></option>
                                <option value="publish"><?= __('Publish Selected') ?></option>
                                <option value="unpublish"><?= __('Unpublish Selected') ?></option>
                                <option value="delete"><?= __('Delete Selected') ?></option>
                            </select>
                            <button type="submit" class="btn btn-outline-secondary"><?= __('Apply') ?></button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50"></th>
                            <th><?= $this->Paginator->sort('title', __('Title')) ?></th>
                            <th><?= $this->Paginator->sort('slug', __('Slug')) ?></th>
                            <th><?= __('Menu') ?></th>
                            <th><?= $this->Paginator->sort('is_published', __('Status')) ?></th>
                            <th><?= $this->Paginator->sort('modified', __('Last Modified')) ?></th>
                            <th><?= __('Author') ?></th>
                            <th width="100"><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $page): ?>
                        <tr>
                            <td>
                                <div class="form-check">
                                    <input class="form-check-input page-checkbox" 
                                           type="checkbox" 
                                           name="selected_pages[]" 
                                           value="<?= $page->id ?>">
                                </div>
                            </td>
                            <td>
                                <div>
                                    <strong>
                                        <?= $this->Html->link(
                                            h($page->title),
                                            ['action' => 'edit', $page->id],
                                            ['class' => 'text-decoration-none']
                                        ) ?>
                                    </strong>
                                    <?php if (!empty($page->meta_title)): ?>
                                        <br>
                                        <small class="text-muted">SEO: <?= h($page->meta_title) ?></small>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <code><?= h($page->slug) ?></code>
                                <br>
                                <small>
                                    <?= $this->Html->link(
                                        '<i class="bi bi-eye me-1"></i>' . __('View'),
                                        ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $page->slug, 'prefix' => false],
                                        ['escape' => false, 'class' => 'text-muted', 'target' => '_blank']
                                    ) ?>
                                </small>
                            </td>
                            <td>
                                <?php
                                $menuBadges = [];
                                if ($page->main_menu) {
                                    $menuBadges[] = '<span class="badge bg-primary me-1">Header</span>';
                                }
                                if ($page->footer_menu) {
                                    $menuBadges[] = '<span class="badge bg-secondary me-1">Footer</span>';
                                }
                                if (empty($menuBadges)) {
                                    $menuBadges[] = '<span class="badge bg-light text-dark">None</span>';
                                }
                                echo implode('', $menuBadges);
                                ?>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                <?= $page->modified->format('M d, Y H:i') ?>
                            </td>
                            <td>
                                <?= h($page->user->username ?? __('Unknown')) ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <?= $this->Html->link(
                                        '<i class="bi bi-eye"></i>',
                                        ['action' => 'view', $page->id],
                                        [
                                            'class' => 'btn btn-outline-primary btn-sm',
                                            'escape' => false,
                                            'title' => __('View')
                                        ]
                                    ) ?>
                                    <?= $this->Html->link(
                                        '<i class="bi bi-pencil"></i>',
                                        ['action' => 'edit', $page->id],
                                        [
                                            'class' => 'btn btn-outline-secondary btn-sm',
                                            'escape' => false,
                                            'title' => __('Edit')
                                        ]
                                    ) ?>
                                    <?= $this->Form->postLink(
                                        '<i class="bi bi-trash"></i>',
                                        ['action' => 'delete', $page->id],
                                        [
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'escape' => false,
                                            'confirm' => __('Are you sure you want to delete "{0}"?', $page->title),
                                            'title' => __('Delete')
                                        ]
                                    ) ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?= $this->Form->end() ?>
        
        <!-- Pagination -->
        <div class="mt-4">
            <?= $this->element('pagination', ['recordCount' => count($pages), 'search' => $search ?? '']) ?>
        </div>
        
    <?php else: ?>
        <!-- Empty State -->
        <div class="text-center py-5">
            <i class="bi bi-file-text display-1 text-muted mb-3"></i>
            <h3 class="text-muted"><?= __('No pages found') ?></h3>
            <p class="text-muted mb-4">
                <?php if (!empty($search) || $statusFilter !== null || $menuFilter !== null): ?>
                    <?= __('No pages match your current filters. Try adjusting your search criteria.') ?>
                <?php else: ?>
                    <?= __('Get started by creating your first page.') ?>
                <?php endif; ?>
            </p>
            <?= $this->Html->link(
                '<i class="bi bi-plus-circle me-2"></i>' . __('Create First Page'),
                ['action' => 'add'],
                ['class' => 'btn btn-primary btn-lg', 'escape' => false]
            ) ?>
        </div>
    <?php endif; ?>
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('pageSearch');
    const resultsContainer = document.querySelector('#ajax-target');
    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = '<?= $this->Url->build(['action' => 'index']) ?>';
            const params = new URLSearchParams();
            
            <?php if ($statusFilter !== null): ?>
            params.append('status', '<?= $statusFilter ?>');
            <?php endif; ?>
            
            <?php if ($menuFilter !== null): ?>
            params.append('menu', '<?= $menuFilter ?>');
            <?php endif; ?>
            
            if (searchTerm.length > 0) {
                params.append('search', searchTerm);
            }
            
            if (params.toString()) {
                url += '?' + params.toString();
            }

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                initializeCheckboxes();
            })
            .catch(error => console.error('Error:', error));

        }, 300);
    });

    // Checkbox functionality
    function initializeCheckboxes() {
        const selectAll = document.getElementById('select-all');
        const pageCheckboxes = document.querySelectorAll('.page-checkbox');

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                pageCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
            });
        }

        pageCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (selectAll) {
                    selectAll.checked = [...pageCheckboxes].every(cb => cb.checked);
                }
            });
        });
    }

    // Bulk actions form submission
    const bulkForm = document.getElementById('bulk-actions-form');
    if (bulkForm) {
        bulkForm.addEventListener('submit', function(e) {
            const selectedPages = document.querySelectorAll('.page-checkbox:checked');
            const bulkAction = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedPages.length === 0) {
                e.preventDefault();
                alert('<?= __('Please select at least one page.') ?>');
                return;
            }
            
            if (bulkAction === 'delete') {
                const confirmed = confirm('<?= __('Are you sure you want to delete the selected pages? This action cannot be undone.') ?>');
                if (!confirmed) {
                    e.preventDefault();
                }
            }
        });
    }

    // Initialize checkboxes on page load
    initializeCheckboxes();
});
<?php $this->Html->scriptEnd(); ?>