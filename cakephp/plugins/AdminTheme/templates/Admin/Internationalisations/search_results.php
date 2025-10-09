<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Internationalisation> $internationalisations
 */
?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
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