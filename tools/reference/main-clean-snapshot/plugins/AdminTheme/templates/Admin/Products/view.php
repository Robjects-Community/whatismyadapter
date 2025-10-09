<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
    echo $this->element('actions_card', [
<<<<<<< HEAD
        'modelName' => ($product->kind == 'page') ? 'Page' : 'Post',
        'controllerName' => 'Products',
        'controllerIndexAction' => ($product->kind == 'page') ? 'tree-index' : 'index',
        'entity' => $product,
        'entityDisplayName' => $product->title,
        'urlParams' => ($product->kind == 'page') ? ['kind' => 'page'] : [],
=======
        'modelName' => ($product->kind == 'product') ? 'Product' : 'Product',
        'controllerName' => 'Products',
        'controllerIndexAction' => ($product->kind == 'product') ? 'tree-index' : 'index',
        'entity' => $product,
        'entityDisplayName' => $product->title,
        'urlParams' => ($product->kind == 'product') ? ['kind' => 'product'] : [],
>>>>>>> e7397e3034035101febf4710cb40815e58d61f8e
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($product->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('User') ?></th>
                            <td>
                                <?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Kind') ?></th>
                            <td><?= h($product->kind) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($product->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td>
<<<<<<< HEAD
                                <?php $ruleName = ($product->kind == 'product') ? 'product-by-slug' : 'page-by-slug';?>
=======
                                <?php $ruleName = ($product->kind == 'product') ? 'product-by-slug' : 'product-by-slug';?>
>>>>>>> e7397e3034035101febf4710cb40815e58d61f8e
                                <?php if ($product->is_published == true): ?>
                                    
                                    <?= $this->Html->link(
                                        $product->slug,
                                        [
                                            'controller' => 'Products',
                                            'action' => 'view-by-slug',
                                            'slug' => $product->slug,
                                            '_name' => $ruleName,
                                        ],
                                        ['escape' => false]
                                    );
                                    ?>
                                <?php else: ?>
                                    <?= $this->Html->link(
                                        $product->slug,
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
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($product->image)) : ?>
                                <div class="position-relative">
                                    <?= $this->element('image/icon', ['model' => $product, 'icon' => $product->smallImageUrl, 'preview' => $product->largeImageUrl]); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($product->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($product->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Published') ?></th>
                            <td><?= h($product->published) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td>
                                <?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                        <th><?= __('Page Views') ?></th>
                            <td>
                            <?= $this->Html->link(
                                '<span class="badge bg-info me-3">' . __('{0} Views', $product->view_count) . '</span>',
                                [
                                    'prefix' => 'Admin',
<<<<<<< HEAD
                                    'controller' => 'PageViews',
                                    'action' => 'pageViewStats',
=======
                                    'controller' => 'Products',
                                    'action' => 'index',
>>>>>>> e7397e3034035101febf4710cb40815e58d61f8e
                                    $product['id']
                                ],
                                [
                                    'escape' => false,
                                    'class' => 'ms-2'
                                ]
                            ) ?>
                            </td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->body); ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Lede') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->lede); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Summary') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->summary); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                    <?= $this->element('seo_display_fields', ['model' => $product]); ?>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($product->tags)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->tags as $tag) : ?>
                                        <tr>
                                            <td><?= html_entity_decode($tag->title) ?></td>
                                            <td><?= h($tag->slug) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Tags', 'model' => $tag, 'display' => 'title']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card mt-4">
                    <?php if (!empty($product->images)) : ?>
                        <div class="mb-3">
                        <?= $this->element('image_carousel', [
                            'images' => $product->images,
                            'carouselId' => $carouselId ?? 'imageCarouselID',
                            'hideRemove' => true,
                        ]) ?>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <?= $this->element('related/comments', ['comments' => $product->comments, 'model' => $product]) ?>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($product->slugs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
                                            <td><?= h($slug->modified) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Slugs', 'model' => $slug, 'display' => 'slug']); ?>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>