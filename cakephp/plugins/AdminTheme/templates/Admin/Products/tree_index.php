<?php
$activeFilter = $this->request->getQuery('status');
if ($activeFilter === null) {
    $this->Html->script('https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js', ['block' => true]);
    $this->Html->script('products_tree', ['block' => true]);
}
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center pages">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Status') ?></a>
            <ul class="dropdown-menu">
              <li>
                <?= $this->Html->link(
                    __('All'), 
                    ['action' => 'treeIndex', 'id' => ''], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === null ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Un-Published'), 
                    ['action' => 'treeIndex', 'id' => '', '?' => ['status' => 0]], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === '0' ? ' active' : '')
                    ]
                ) ?>
              </li>
              <li>
                <?= $this->Html->link(
                    __('Published'), 
                    ['action' => 'treeIndex', 'id' => '', '?' => ['status' => 1]], 
                    [
                      'class' => 'dropdown-item' . ($activeFilter === '1' ? ' active' : '')
                    ]
                ) ?>
              </li>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="pageSearch" type="search" class="form-control" placeholder="<?= __('Search Pages...') ?>" aria-label="Search">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Page'), ['action' => 'add', '?' => ['kind' => 'page']], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<span id="ajax-target">
<?php
    if (!empty($products)) {
        echo $this->element('tree/page_tree', ['products' => $products, 'level' => 0]);
    } else {
        echo $this->Html->tag('p', __('No pages found.'));
    }
?>
</span>

<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('pageSearch');
    const resultsContainer = document.querySelector('#ajax-target');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            let url = `<?= $this->Url->build(['action' => 'treeIndex']) ?>`;
            <?php if (null !== $activeFilter): ?>
            url += `?status=<?= urlencode($activeFilter) ?>`;
            <?php endif; ?>
            if (searchTerm.length > 0) {
                url += (url.includes('?') ? '&' : '?') + `search=${encodeURIComponent(searchTerm)}`;
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
            } else {
                // If search is empty, you might want to reload all results or clear the table
                location.reload();
            }
        }, 300); // Debounce for 300ms
    });

    // Initialize popovers on page load
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
<?php $this->Html->scriptEnd(); ?>