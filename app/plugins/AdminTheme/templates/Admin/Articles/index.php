<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center articles">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Status') ?></a>
            <ul class="dropdown-menu">
              <?php $activeFilter = $this->request->getQuery('status'); ?>
              <li>
                <?= $this->Html->link(
                    __('All'), 
                    ['action' => 'index'],
                    [
                      'class' => 'dropdown-item' . (null === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Un-Published'), 
                    ['action' => 'index', '?' => ['status' => 0]],
                    [
                      'class' => 'dropdown-item' . ('0' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Published'), 
                    ['action' => 'index', '?' => ['status' => 1]],
                    [
                      'class' => 'dropdown-item' . ('1' === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="articleSearch" type="search" class="form-control" placeholder="<?= __('Search Posts...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Post'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>

<!-- Bulk Actions Bar -->
<div id="bulk-actions-bar" class="alert alert-info d-none mb-3" role="alert">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span id="selected-count">0</span> <?= __('items selected') ?>
        </div>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-success btn-sm" id="bulk-publish">
                <i class="fas fa-eye"></i> <?= __('Publish') ?>
            </button>
            <button type="button" class="btn btn-warning btn-sm" id="bulk-unpublish">
                <i class="fas fa-eye-slash"></i> <?= __('Unpublish') ?>
            </button>
            <button type="button" class="btn btn-danger btn-sm" id="bulk-delete" data-bs-toggle="modal" data-bs-target="#confirmBulkDelete">
                <i class="fas fa-trash"></i> <?= __('Delete') ?>
            </button>
            <button type="button" class="btn btn-secondary btn-sm" id="clear-selection">
                <i class="fas fa-times"></i> <?= __('Clear') ?>
            </button>
        </div>
    </div>
</div>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
      <tr>
        <th scope="col" width="40">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="select-all">
            <label class="form-check-label" for="select-all"></label>
          </div>
        </th>
        <th scope="col"><?= __('Picture') ?></th>
        <th scope="col"><?= $this->Paginator->sort('user_id', 'Author') ?></th>
        <th scope="col"><?= $this->Paginator->sort('title') ?></th>

        <?php if (null === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('is_published', 'Status') ?></th>
        <?php elseif ('1' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('published') ?></th>
        <?php elseif ('0' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
        <?php endif; ?>

        <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $article): ?>
      <tr>
        <td>
          <div class="form-check">
            <input class="form-check-input article-checkbox" type="checkbox" value="<?= h($article->id) ?>" id="article-<?= h($article->id) ?>">
            <label class="form-check-label" for="article-<?= h($article->id) ?>"></label>
          </div>
        </td>
        <td>
          <?php if (!empty($article->image)) : ?>
          <div class="position-relative">
            <?= $this->element('image/icon',  ['model' => $article, 'icon' => $article->teenyImageUrl, 'preview' => $article->largeImageUrl ]); ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <?php if (isset($article->_matchingData['Users']) && $article->_matchingData['Users']->username): ?>
              <?= $this->Html->link(
                  h($article->_matchingData['Users']->username),
                  ['controller' => 'Users', 'action' => 'view', $article->_matchingData['Users']->id]
              ) ?>
          <?php else: ?>
              <?= h(__('Unknown Author')) ?>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($article->is_published == true): ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
                  [
                      'controller' => 'Articles',
                      'action' => 'view-by-slug',
                      'slug' => $article->slug,
                      '_name' => 'article-by-slug'
                  ],
                  ['escape' => false]
              );
              ?>
          <?php else: ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
                  [
                      'prefix' => 'Admin',
                      'controller' => 'Articles',
                      'action' => 'view',
                      $article->id
                  ],
                  ['escape' => false]
              ) ?>
          <?php endif; ?>
        </td>
        <?php if (null === $activeFilter) :?>
        <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Published') . '</span>' : '<span class="badge bg-warning">' . __('Un-Published') . '</span>'; ?></td>
        <?php elseif ('1' === $activeFilter) :?>
        <td><?= h($article->published) ?></td>
        <?php elseif ('0' === $activeFilter) :?>
        <td><?= h($article->modified) ?></td>
        <?php endif; ?>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $article, 'display' => 'title']); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?= $this->element('pagination', ['recordCount' => count($articles), 'search' => $search ?? '']) ?>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div class="modal fade" id="confirmBulkDelete" tabindex="-1" aria-labelledby="confirmBulkDeleteLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmBulkDeleteLabel"><?= __('Confirm Bulk Delete') ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p><?= __('Are you sure you want to delete the selected articles? This action cannot be undone.') ?></p>
        <p class="text-danger"><strong><?= __('Warning: This will permanently delete all selected articles and their associated data.') ?></strong></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Cancel') ?></button>
        <button type="button" class="btn btn-danger" id="confirm-bulk-delete"><?= __('Delete Articles') ?></button>
      </div>
    </div>
  </div>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('articleSearch');
    const resultsContainer = document.querySelector('#ajax-target');
    const bulkActionsBar = document.getElementById('bulk-actions-bar');
    const selectedCount = document.getElementById('selected-count');
    const selectAllCheckbox = document.getElementById('select-all');

    let debounceTimer;

    // Search functionality
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            <?php if (null !== $activeFilter): ?>
            url += `?status=<?= urlencode($activeFilter) ?>`;
            <?php endif; ?>

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
                // Re-initialize popovers and bulk selection after updating the content
                initializePopovers();
                initializeBulkSelection();
            })
            .catch(error => console.error('Error:', error));

        }, 300); // Debounce for 300ms
    });

    // Bulk selection functionality
    function initializeBulkSelection() {
        const articleCheckboxes = document.querySelectorAll('.article-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        
        // Select all checkbox handler
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                articleCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActionsBar();
            });
        }
        
        // Individual checkbox handlers
        articleCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkActionsBar();
            });
        });
    }

    function updateSelectAllState() {
        const articleCheckboxes = document.querySelectorAll('.article-checkbox');
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        const selectAllCheckbox = document.getElementById('select-all');
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = checkedBoxes.length === articleCheckboxes.length && articleCheckboxes.length > 0;
            selectAllCheckbox.indeterminate = checkedBoxes.length > 0 && checkedBoxes.length < articleCheckboxes.length;
        }
    }

    function updateBulkActionsBar() {
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (count > 0) {
            bulkActionsBar.classList.remove('d-none');
            selectedCount.textContent = count;
        } else {
            bulkActionsBar.classList.add('d-none');
        }
    }

    function getSelectedIds() {
        const checkedBoxes = document.querySelectorAll('.article-checkbox:checked');
        return Array.from(checkedBoxes).map(checkbox => checkbox.value);
    }

    function performBulkAction(action) {
        const selectedIds = getSelectedIds();
        if (selectedIds.length === 0) {
            alert('<?= __('Please select at least one article') ?>');
            return;
        }

        const formData = new FormData();
        formData.append('bulk_action', action);
        formData.append('_csrfToken', '<?= $this->request->getAttribute('csrfToken') ?>');
        selectedIds.forEach(id => {
            formData.append('selected_ids[]', id);
        });

        fetch('<?= $this->Url->build(['action' => 'bulk-action']) ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to reflect changes
                location.reload();
            } else {
                alert(data.message || '<?= __('An error occurred') ?>');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('<?= __('An error occurred while performing the bulk action') ?>');
        });
    }

    // Bulk action button handlers
    document.getElementById('bulk-publish')?.addEventListener('click', function() {
        performBulkAction('publish');
    });

    document.getElementById('bulk-unpublish')?.addEventListener('click', function() {
        performBulkAction('unpublish');
    });

    document.getElementById('confirm-bulk-delete')?.addEventListener('click', function() {
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmBulkDelete'));
        modal.hide();
        performBulkAction('delete');
    });

    document.getElementById('clear-selection')?.addEventListener('click', function() {
        const articleCheckboxes = document.querySelectorAll('.article-checkbox');
        const selectAllCheckbox = document.getElementById('select-all');
        
        articleCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
        });
        
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }
        
        updateBulkActionsBar();
    });

    // Initialize popovers
    function initializePopovers() {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    }

    // Initialize on page load
    initializePopovers();
    initializeBulkSelection();
});
<?php $this->Html->scriptEnd(); ?>
