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
    <div class="row">
        <?php if (empty($emailTemplates)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center" role="alert">
                    <h4 class="alert-heading"><?= __('No Email Templates Found') ?></h4>
                    <p><?= __('Get started by creating your first email template.') ?></p>
                    <?php if (Configure::read('debug')): ?>
                        <?= $this->Html->link(__('Create First Template'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($emailTemplates as $emailTemplate): ?>
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1"><?= h($emailTemplate->name) ?></h5>
                            <?php if (!empty($emailTemplate->template_identifier)): ?>
                                <span class="badge bg-secondary"><?= h($emailTemplate->template_identifier) ?></span>
                            <?php else: ?>
                                <span class="badge bg-light text-dark"><?= __('No Identifier') ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                                </li>
                                <li>
                                    <?= $this->Html->link(__('Send Email'), ['action' => 'sendEmail', '?' => ['template' => $emailTemplate->id]], ['class' => 'dropdown-item']) ?>
                                </li>
                                <?php if (Configure::read('debug')): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete {0}?', $emailTemplate->name), 'class' => 'dropdown-item text-danger']) ?>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="card-text text-muted mb-2"><strong><?= __('Subject:') ?></strong> <?= h($emailTemplate->subject) ?></p>
                        
                        <?php if (!empty($emailTemplate->body_html)): ?>
                            <div class="preview-content mb-3">
                                <h6 class="text-muted"><?= __('Preview:') ?></h6>
                                <div class="border rounded p-2 bg-light" style="max-height: 100px; overflow: hidden; font-size: 0.8em;">
                                    <?= $this->Text->truncate(strip_tags($emailTemplate->body_html), 150, ['html' => false]) ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="template-meta">
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-plus me-1"></i><?= __('Created:') ?> <?= $emailTemplate->created->format('M j, Y') ?>
                            </small>
                            <small class="text-muted d-block">
                                <i class="bi bi-calendar-check me-1"></i><?= __('Modified:') ?> <?= $emailTemplate->modified->format('M j, Y') ?>
                            </small>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="btn-group w-100" role="group">
                            <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
                            <?= $this->Html->link(__('Send'), ['action' => 'sendEmail', '?' => ['template' => $emailTemplate->id]], ['class' => 'btn btn-outline-success btn-sm']) ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Pagination -->
            <div class="col-12 mt-4">
                <?= $this->element('pagination', ['recordCount' => count($emailTemplates), 'search' => $search ?? '']) ?>
            </div>
        <?php endif; ?>
    </div>
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