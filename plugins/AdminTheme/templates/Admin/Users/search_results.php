<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
<table class="table table-striped">
    <thead>
      <tr>
            <th><?= __('Image') ?></th>
            <th scope="col"><?= $this->Paginator->sort('email') ?></th>
            <th scope="col"><?= $this->Paginator->sort('is_admin', __('Admin')) ?></th>
            <th scope="col"><?= $this->Paginator->sort('active', __('Active')) ?></th>
            <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $user): ?>
      <tr>
          <td>
            <?php if (!empty($user->image)) : ?>
            <div class="position-relative">
                <?= $this->element('image/icon', ['model' => $user, 'icon' => $user->tennyImageUrl, 'preview' => $user->largeImageUrl]); ?>
            </div>
            <?php endif; ?>
          </td>
          <td><?= $this->Html->link(h($user->email), 'mailto:' . h($user->email)) ?></td>
          <td>
            <?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?>
          </td>
          <td>
            <?= $user->active ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?>
          </td>
          <td>
              <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                  <div class="dropdown">
                  <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <?= __('Actions') ?>
                  </button>
                  <ul class="dropdown-menu">
                      <li>
                          <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li>
                          <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete {0}?', $user->email), 'class' => 'dropdown-item text-danger']) ?>
                      </li>
                  </ul>
                  </div>
              </div>
          </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?= $this->element('pagination', ['recordCount' => count($users), 'search' => $search ?? '']) ?>