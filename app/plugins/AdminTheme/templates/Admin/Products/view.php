<?php
/**
 * Admin Products View Template
 */
$this->assign('title', __('Product Details'));
?>

<div class="products-view">
    <div class="container-fluid">
        <div class="page-header d-flex justify-content-between align-items-center mb-4">
            <h1 class="page-title"><?= h($product->title) ?></h1>
            <div class="actions">
                <?= $this->Html->link(__('Edit'), ['action' => 'edit', $product->id], ['class' => 'btn btn-primary']) ?>
                <?= $this->Html->link(__('Back to List'), ['action' => 'index'], ['class' => 'btn btn-secondary']) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?= __('Product Information') ?></h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <tr>
                                <th><?= __('Title') ?></th>
                                <td><?= h($product->title) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Manufacturer') ?></th>
                                <td><?= h($product->manufacturer) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Model Number') ?></th>
                                <td><?= h($product->model_number) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Price') ?></th>
                                <td><?= $this->Number->currency($product->price, $product->currency) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Status') ?></th>
                                <td>
                                    <span class="badge badge-<?= $product->is_published ? 'success' : 'warning' ?>">
                                        <?= $product->is_published ? __('Published') : __('Unpublished') ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th><?= __('Featured') ?></th>
                                <td><?= $product->featured ? __('Yes') : __('No') ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Created') ?></th>
                                <td><?= h($product->created->format('M j, Y g:i A')) ?></td>
                            </tr>
                            <tr>
                                <th><?= __('Modified') ?></th>
                                <td><?= h($product->modified->format('M j, Y g:i A')) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5><?= __('Description') ?></h5>
                    </div>
                    <div class="card-body">
                        <?= $this->Text->autoParagraph(h($product->description)) ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php if (!empty($product->image)): ?>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5><?= __('Product Image') ?></h5>
                    </div>
                    <div class="card-body">
                        <?= $this->Html->image($product->image, ['class' => 'img-fluid', 'alt' => h($product->alt_text)]) ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($product->tags)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5><?= __('Tags') ?></h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($product->tags as $tag): ?>
                            <span class="badge badge-secondary"><?= h($tag->title) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
