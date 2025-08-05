<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Product> $products
 */

// Load search utility scripts
$this->Html->script('AdminTheme.utils/search-handler', ['block' => true]);
$this->Html->script('AdminTheme.utils/popover-manager', ['block' => true]); 
?>

<header class="py-3 mb-3 border-bottom">
    <div class="container-fluid d-flex align-items-center">
        <div class="d-flex align-items-center me-auto">
            <!-- Status Filter -->
            <?= $this->element('status_filter', [
                'filters' => [
                    'all' => ['label' => __('All'), 'params' => []],
                    'filter1' => ['label' => __('Filter 1'), 'params' => ['status' => '0']],
                    'filter2' => ['label' => __('Filter 2'), 'params' => ['status' => '1']],
                ]
            ]) ?>
            
            <!-- Search Form -->
            <?= $this->element('search_form', [
                'id' => 'product-search-form',
                'inputId' => 'productSearch',
                'placeholder' => __('Search Products...'),
                'class' => 'd-flex me-3 flex-grow-1'
            ]) ?>
        </div>
        
        <div class="flex-shrink-0">
            <?= $this->Html->link(
                '<i class="fas fa-plus"></i> ' . __('New Product'),
                ['action' => 'add'],
                ['class' => 'btn btn-success', 'escape' => false]
            ) ?>
        </div>
    </div>
</header>
<div id="ajax-target">
  <table class="table table-striped">
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
</div>

<?php $this->Html->scriptStart(['block' => true]); ?>
// Initialize search functionality using AdminTheme utility
AdminTheme.SearchHandler.init({
    searchInputId: 'productSearch',
    resultsContainerId: '#ajax-target',
    baseUrl: '<?= $this->Url->build(['action' => 'index']) ?>',
    debounceDelay: 300
});
<?php $this->Html->scriptEnd(); ?>

