<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */
?>
<?php if (empty($products)): ?>
    <?= $this->element('empty_state', [
        'type' => 'search',
        'title' => __('No Products found'),
        'message' => __('Try adjusting your search terms or filters.')
    ]) ?>
<?php else: ?>
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                            <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('article_id') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('title') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('slug') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('description') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('manufacturer') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('model_number') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('price') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('currency') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('image') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('alt_text') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('is_published') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('featured') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('verification_status') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('reliability_score') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('view_count') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                            <th scope="col"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
      <tr>
                                                        <td><?= h($product->id) ?></td>
                                    <td><?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                                                            <td><?= $product->hasValue('article') ? $this->Html->link($product->article->title, ['controller' => 'Articles', 'action' => 'view', $product->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                                                                        <td><?= h($product->title) ?></td>
                                                                <td><?= h($product->slug) ?></td>
                                                                <td><?= h($product->description) ?></td>
                                                                <td><?= h($product->manufacturer) ?></td>
                                                                <td><?= h($product->model_number) ?></td>
                                                                <td><?= $product->price === null ? '' : $this->Number->format($product->price) ?></td>
                                                                <td><?= h($product->currency) ?></td>
                                                                <td><?= h($product->image) ?></td>
                                                                <td><?= h($product->alt_text) ?></td>
                                                                <td><?= h($product->is_published) ?></td>
                                                                <td><?= h($product->featured) ?></td>
                                                                <td><?= h($product->verification_status) ?></td>
                                                                <td><?= $product->reliability_score === null ? '' : $this->Number->format($product->reliability_score) ?></td>
                                                                <td><?= $this->Number->format($product->view_count) ?></td>
                                                                <td><?= h($product->created) ?></td>
                                                                <td><?= h($product->modified) ?></td>
                          <td>
            <?= $this->element('evd_dropdown', ['model' => $product, 'display' => 'title']); ?>
          </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?= $this->element('pagination') ?>
<?php endif; ?>

