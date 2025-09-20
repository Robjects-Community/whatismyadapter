<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Image> $images
 */
?>
<?php use App\Utility\SettingsManager; ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center images">
        <div class="d-flex align-items-center me-auto">
            <div class="btn-group me-3">
                <?= $this->Html->link('
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"></path>
                    </svg>
                    <span class="visually-hidden">' . __('List View') . '</span>
                ', ['action' => 'index', '?' => ['view' => 'list']], [
                    'class' => 'btn btn-secondary',
                    'escape' => false,
                ]) ?>
                <?= $this->Html->link('
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-grid-3x2" viewBox="0 0 16 16">
                        <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v8a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 11.5zM1.5 3a.5.5 0 0 0-.5.5V7h4V3zM5 8H1v3.5a.5.5 0 0 0 .5.5H5zm1 0v4h4V8zm4-1V3H6v4zm1 1v4h3.5a.5.5 0 0 0 .5-.5V8zm0-1h4V3.5a.5.5 0 0 0-.5-.5H11z"></path>
                    </svg>
                    <span class="visually-hidden">' . __('Grid View') . '</span>
                ', ['action' => 'index', '?' => ['view' => 'grid']], [
                    'class' => 'btn btn-outline-secondary',
                    'escape' => false,
                ]) ?>
            </div>
            <form class="d-flex-grow-1 me-3" role="search">
                <input id="imageSearch" type="search" class="form-control" placeholder="<?= __('Search...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
            </form>
        </div>
        <div class="flex-shrink-0">
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
            <?= $this->Html->link(__('Bulk Upload'), ['action' => 'bulkUpload'], ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
</header>
<div id="ajax-target">
<table class="table table-striped">
  <thead>
    <tr>
          <th scope="col"><?= __('Image') ?></th>
          <th scope="col"><?= $this->Paginator->sort('name') ?></th>
          <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
          <th scope="col"><?= $this->Paginator->sort('created') ?></th>
          <th scope="col"><?= __('Actions') ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($images as $image): ?>
    <tr>
            <td>
                <div class="position-relative">
                    <?= $this->element('image/icon', ['model' => $image, 'icon' => $image->teenyImageUrl, 'preview' => $image->extraLargeImageUrl]); ?>
                </div>
            </td>
            <td><?= h($image->name) ?></td>
            <td><?= h($image->created) ?></td>
            <td><?= h($image->modified) ?></td>
            <td>
            <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <?= __('Actions') ?>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $image->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $image->id], ['class' => 'dropdown-item']) ?>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete {0}?', $image->name), 'class' => 'dropdown-item text-danger']) ?>
                    </li>
                </ul>
                </div>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($images), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('imageSearch');
    const resultsContainer = document.querySelector('#ajax-target');

    let debounceTimer;

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
                // Re-initialize popovers after updating the content
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
                popoverTriggerList.map(function (popoverTriggerEl) {
                    return new bootstrap.Popover(popoverTriggerEl);
                });
            })
            .catch(error => console.error('Error:', error));

        }, 300); // Debounce for 300ms
    });

    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>