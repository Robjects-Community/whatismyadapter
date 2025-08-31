<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\EmailTemplate> $emailTemplates
 */
?>
<?php use Cake\Core\Configure; ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center emailTemplates">
      <div class="d-flex align-items-center me-auto">
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="emailTemplateSearch" type="search" class="form-control" placeholder="<?= __('Search Email Templates...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?php if (Configure::read('debug')) : ?>
        <?= $this->Html->link(__('New Email Template'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
        <?php endif ?>
        <?= $this->Html->link(__('Send Email'), ['action' => 'sendEmail'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
    <table class="table table-striped">
    <thead>
        <tr>
            <th scope="col"><?= $this->Paginator->sort('name') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($emailTemplates as $emailTemplate): ?>
        <tr>
            <td><?= h($emailTemplate->name) ?></td>
            <td><?= h($emailTemplate->created) ?></td>
            <td><?= h($emailTemplate->modified) ?></td>
            <td>
                <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                    <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= __('Actions') ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                        </li>

                        <?php if (Configure::read('debug')) : ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete {0}?', $emailTemplate->name), 'class' => 'dropdown-item text-danger']) ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
    </table>
    <?= $this->element('pagination', ['recordCount' => count($emailTemplates), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('emailTemplateSearch');
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