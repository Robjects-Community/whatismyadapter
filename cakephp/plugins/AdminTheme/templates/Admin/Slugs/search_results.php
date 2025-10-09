<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Slug> $slugs
 */
?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
  <table class="table table-striped">
    <thead>
      <tr>
              <th scope="col"><?= $this->Paginator->sort('model') ?></th>
              <th scope="col"><?= $this->Paginator->sort('foreign_key', __('Title')) ?></th>
              <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
              <th scope="col"><?= $this->Paginator->sort('created') ?></th>
              <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($slugs as $slug): ?>
      <tr>
          <td><?= h($slug->model) ?></td>
          <td>
              <?php if (isset($relatedData[$slug->id])): ?>
                  <?= $this->Html->link(
                      h($relatedData[$slug->id]['title']),
                      [
                          'controller' => $relatedData[$slug->id]['controller'],
                          'action' => 'view',
                          $relatedData[$slug->id]['id']
                      ],
                      [
                          'class' => 'text-decoration-none',
                          'escape' => false
                      ]
                  ) ?>
              <?php else: ?>
                  <?= h($slug->foreign_key) ?>
              <?php endif; ?>
          </td>
          <td><?= h($slug->slug) ?></td>
          <td><?= h($slug->created) ?></td>
          <td>
              <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                  <div class="dropdown">
                  <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <?= __('Actions') ?>
                  </button>
                  <ul class="dropdown-menu">
                      <li>
                          <?= $this->Html->link(__('View'), ['action' => 'view', $slug->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li>
                          <?= $this->Html->link(__('Edit'), ['action' => 'edit', $slug->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $slug->id], ['confirm' => __('Are you sure you want to delete # {0}?', $slug->id), 'class' => 'dropdown-item text-danger']) ?>
                      </li>
                  </ul>
                  </div>
              </div>
          </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?= $this->element('pagination', ['recordCount' => count($slugs), 'search' => $search ?? '']) ?>