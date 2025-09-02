<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center aiprompts">
      <div class="d-flex align-items-center me-auto">
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="aipromptSearch" type="search" class="form-control" placeholder="<?= __('Search Aiprompts...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Aiprompt'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
    <table class="table table-striped">
    <thead>
        <tr>
            <th scope="col"><?= $this->Paginator->sort('task_type') ?></th>
            <th scope="col"><?= $this->Paginator->sort('model') ?></th>
            <th scope="col"><?= $this->Paginator->sort('max_tokens') ?></th>
            <th scope="col"><?= $this->Paginator->sort('temperature') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($aiprompts as $aiprompt): ?>
        <tr>
            <td><?= h($aiprompt->task_type) ?></td>
            <td><?= h($aiprompt->model) ?></td>
            <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
            <td><?= $this->Number->format($aiprompt->temperature) ?></td>
            <td><?= h($aiprompt->created) ?></td>
            <td><?= h($aiprompt->modified) ?></td>
            <td>
                <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                    <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= __('Actions') ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiprompt->id], ['confirm' => __('Are you sure you want to delete {0}?', $aiprompt->task_type), 'class' => 'dropdown-item text-danger']) ?>
                        </li>
                    </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
    <?= $this->element('pagination', ['recordCount' => count($aiprompts), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('aipromptSearch');
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