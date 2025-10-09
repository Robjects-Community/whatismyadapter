<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Comment> $comments
 */
?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
  <table class="table table-striped">
    <thead>
      <tr>
            <th scope="col"><?= $this->Paginator->sort('model', __('On')) ?></th>
            <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
            <th scope="col"><?= $this->Paginator->sort('content') ?></th>
            <?php if (null === $activeFilter) :?>
            <th scope="col"><?= $this->Paginator->sort('display', __('Display')) ?></th>
            <?php endif; ?>
            <?php if ('0' === $activeFilter || '1' === $activeFilter) :?>
            <th scope="col"><?= $this->Paginator->sort('is_inappropriate', __('Flagged')) ?></th>
            <?php endif; ?>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($comments as $comment): ?>
      <tr>
          <td><?= $comment->hasValue('article') ? $this->Html->link($comment->article->title, ['controller' => 'Articles', 'action' => 'view', $comment->article->id]) : '' ?></td>
          <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id]) : '' ?></td>
          <td><?= substr(h($comment->content), 0, 30) . '...' ?></td>
          
          <?php if (null === $activeFilter) :?>
            <td>
              <?= $comment->display ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?>
            </td>
          <?php endif; ?>

          <?php if ('0' === $activeFilter || '1' === $activeFilter) :?>
            <td>
              <?= $comment->is_inappropriate ? '<span class="badge bg-warning">' . __('Yes') . '</span>' : '<span class="badge bg-success">' . __('No') . '</span>'; ?>
            </td>
          <?php endif; ?>

          <td><?= h($comment->created) ?></td>
          <td>
              <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                  <div class="dropdown">
                  <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <?= __('Actions') ?>
                  </button>
                  <ul class="dropdown-menu">
                      <li>
                          <?= $this->Html->link(__('View'), ['action' => 'view', $comment->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li>
                          <?= $this->Html->link(__('Edit'), ['action' => 'edit', $comment->id], ['class' => 'dropdown-item']) ?>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                          <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $comment->id], ['confirm' => __('Are you sure you want to delete # {0}?', $comment->id), 'class' => 'dropdown-item text-danger']) ?>
                      </li>
                  </ul>
                  </div>
              </div>
          </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?= $this->element('pagination', ['recordCount' => count($comments), 'search' => $search ?? '']) ?>