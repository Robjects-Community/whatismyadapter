<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 */
?>
<?php $activeFilter = $this->request->getQuery('status'); ?>
<table class="table table-striped">
    <thead>
      <tr>
        <th scope="col"><?= __('Picture') ?></th>
        <th scope="col"><?= $this->Paginator->sort('user_id', 'Author') ?></th>
        <th scope="col"><?= $this->Paginator->sort('title') ?></th>

        <?php if (null === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('is_published', 'Status') ?></th>
        <?php elseif ('1' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('published') ?></th>
        <?php elseif ('0' === $activeFilter) :?>
        <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
        <?php endif; ?>

        <th scope="col"><?= __('Actions') ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($articles as $article): ?>
      <tr>
        <td>
          <?php if (!empty($article->image)) : ?>
          <div class="position-relative">
            <?= $this->element('image/icon',  ['model' => $article, 'icon' => $article->teenyImageUrl, 'preview' => $article->largeImageUrl ]); ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <?php if (isset($article->_matchingData['Users']) && $article->_matchingData['Users']->username): ?>
              <?= $this->Html->link(
                  h($article->_matchingData['Users']->username),
                  ['controller' => 'Users', 'action' => 'view', $article->_matchingData['Users']->id]
              ) ?>
          <?php else: ?>
              <?= h(__('Unknown Author')) ?>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($article->is_published == true): ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
                  [
                      'controller' => 'Articles',
                      'action' => 'view-by-slug',
                      'slug' => $article->slug,
                      '_name' => 'article-by-slug'
                  ],
                  ['escape' => false]
              );
              ?>
          <?php else: ?>
              <?= $this->Html->link(
                  html_entity_decode($article->title),
                  [
                      'prefix' => 'Admin',
                      'controller' => 'Articles',
                      'action' => 'view',
                      $article->id
                  ],
                  ['escape' => false]
              ) ?>
          <?php endif; ?>
        </td>
        <?php if (null === $activeFilter) :?>
        <td><?= $article->is_published ? '<span class="badge bg-success">' . __('Published') . '</span>' : '<span class="badge bg-warning">' . __('Un-Published') . '</span>'; ?></td>
        <?php elseif ('1' === $activeFilter) :?>
        <td><?= h($article->published) ?></td>
        <?php elseif ('0' === $activeFilter) :?>
        <td><?= h($article->modified) ?></td>
        <?php endif; ?>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $article, 'display' => 'title']); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?= $this->element('pagination', ['recordCount' => count($articles), 'search' => $search ?? '']) ?>