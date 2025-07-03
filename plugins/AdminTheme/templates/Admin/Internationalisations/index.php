<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Internationalisation> $internationalisations
 */
?>
<?php use App\Utility\I18nManager; ?>
<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center internationalisations">
      <div class="d-flex align-items-center me-auto">
        <ul class="navbar-nav me-3">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Filter') ?></a>
            <ul class="dropdown-menu">
              <?php $activeFilter = $this->request->getQuery('locale');  ?>
              <li>
                <?= $this->Html->link(
                    __('All'), 
                    ['action' => 'index'], 
                    [
                      'class' => 'dropdown-item' . (null === $activeFilter ? ' active' : '')
                    ]
                ) ?>
              </li>
              <?php foreach ($locales as $locale) : ?>
                <li>
                  <?= $this->Html->link(
                      $locale, 
                      ['action' => 'index', '?' => ['locale' => $locale]],
                      [
                        'class' => 'dropdown-item' . ($locale === $activeFilter ? ' active' : '')
                      ]
                  ) ?>
                </li>
              <?php endforeach; ?>
            </ul>
          </li>
        </ul>
        <form class="d-flex-grow-1 me-3" role="search">
          <input id="internationalisationSearch" type="search" class="form-control" placeholder="<?= __('Search Internationalisations...') ?>" aria-label="Search" value="<?= $this->request->getQuery('search') ?>">
        </form>
      </div>
      <div class="flex-shrink-0">
        <?= $this->Html->link(__('New Internationalisation'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
      </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
    <thead>
      <tr>
            <th scope="col"><?= $this->Paginator->sort('locale') ?></th>
            <th scope="col"><?= $this->Paginator->sort('message_id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('message_str') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($internationalisations as $internationalisation): ?>
      <tr>
                      <td><?= h($internationalisation->locale) ?></td>
                      <td><?= h($internationalisation->message_id) ?></td>
                      <td><?= h($internationalisation->message_str) ?></td>
                  <td>
              <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                  <div class="dropdown">
                  <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <?= __('Actions') ?>
                  </button>
                  <ul class="dropdown-menu">
                      <li>
                          <?= $this->Html->link(__('View'), ['action' => 'view', $internationalisation->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li>
                          <?= $this->Html->link(__('Edit'), ['action' => 'edit', $internationalisation->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $internationalisation->id], ['confirm' => __('Are you sure you want to delete {0}?', $internationalisation->message_id), 'class' => 'dropdown-item text-danger']) ?>
                      </li>
                  </ul>
                  </div>
              </div>
          </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <?= $this->element('pagination', ['recordCount' => count($internationalisations), 'search' => $search ?? '']) ?>
</div>
<?php $this->Html->scriptStart(['block' => true]); ?>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('internationalisationSearch');
    const resultsContainer = document.querySelector('#ajax-target');

    let debounceTimer;

    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const searchTerm = this.value.trim();
            
            let url = `<?= $this->Url->build(['action' => 'index']) ?>`;

            <?php if (null !== $activeFilter): ?>
            url += `?locale=<?= urlencode($activeFilter) ?>`;
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