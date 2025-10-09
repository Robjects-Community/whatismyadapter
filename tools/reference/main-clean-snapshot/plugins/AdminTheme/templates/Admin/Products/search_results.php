<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
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
      <?php foreach ($products as $product): ?>
      <tr>
        <td>
          <?php if (!empty($product->image)) : ?>
          <div class="position-relative">
            <?= $this->element('image/icon',  ['model' => $product, 'icon' => $product->teenyImageUrl, 'preview' => $product->largeImageUrl ]); ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <?php if (isset($product->_matchingData['Users']) && $product->_matchingData['Users']->username): ?>
              <?= $this->Html->link(
                  h($product->_matchingData['Users']->username),
                  ['controller' => 'Users', 'action' => 'view', $product->_matchingData['Users']->id]
              ) ?>
          <?php else: ?>
              <?= h(__('Unknown Author')) ?>
          <?php endif; ?>
        </td>
        <td>
          <?php if ($product->is_published == true): ?>
              <?= $this->Html->link(
                  html_entity_decode($product->title),
                  [
                      'controller' => 'Products',
                      'action' => 'view-by-slug',
                      'slug' => $product->slug,
                      '_name' => 'product-by-slug'
                  ],
                  ['escape' => false]
              );
              ?>
          <?php else: ?>
              <?= $this->Html->link(
                  html_entity_decode($product->title),
                  [
                      'prefix' => 'Admin',
                      'controller' => 'Products',
                      'action' => 'view',
                      $product->id
                  ],
                  ['escape' => false]
              ) ?>
          <?php endif; ?>
        </td>
        <?php if (null === $activeFilter) :?>
        <td><?= $product->is_published ? '<span class="badge bg-success">' . __('Published') . '</span>' : '<span class="badge bg-warning">' . __('Un-Published') . '</span>'; ?></td>
        <?php elseif ('1' === $activeFilter) :?>
        <td><?= h($product->published) ?></td>
        <?php elseif ('0' === $activeFilter) :?>
        <td><?= h($product->modified) ?></td>
        <?php endif; ?>
        <td>
          <?= $this->element('evd_dropdown', ['model' => $product, 'display' => 'title']); ?>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?= $this->element('pagination', ['recordCount' => count($products), 'search' => $search ?? '']) ?>