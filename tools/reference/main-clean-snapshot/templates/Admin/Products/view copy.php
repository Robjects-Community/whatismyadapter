<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>
<?php
echo $this->element('actions_card', [
    'modelName' => 'Product',
    'controllerName' => 'Products',
    'entity' => $product,
    'entityDisplayName' => $product->title
]);
?>
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($product->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Id') ?></th>
                            <td><?= h($product->id) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $product->hasValue('user') ? $this->Html->link($product->user->username, ['controller' => 'Users', 'action' => 'view', $product->user->id], ['class' => 'btn btn-link']) : '' ?></td>
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
                            <th><?= __('Lede') ?></th>
                            <td><?= h($product->lede) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($product->slug) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td><?= h($product->image) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Alt Text') ?></th>
                            <td><?= h($product->alt_text) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Keywords') ?></th>
                            <td><?= h($product->keywords) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Name') ?></th>
                            <td><?= h($product->name) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Dir') ?></th>
                            <td><?= h($product->dir) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Mime') ?></th>
                            <td><?= h($product->mime) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Meta Title') ?></th>
                            <td><?= h($product->meta_title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Parent Product') ?></th>
                            <td><?= $product->hasValue('parent_product') ? $this->Html->link($product->parent_product->title, ['controller' => 'Products', 'action' => 'view', $product->parent_product->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Size') ?></th>
                            <td><?= $product->size === null ? '' : $this->Number->format($product->size) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Word Count') ?></th>
                            <td><?= $product->word_count === null ? '' : $this->Number->format($product->word_count) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Lft') ?></th>
                            <td><?= $this->Number->format($product->lft) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Rght') ?></th>
                            <td><?= $this->Number->format($product->rght) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('View Count') ?></th>
                            <td><?= $this->Number->format($product->view_count) ?></td>
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
                            <th><?= __('Featured') ?></th>
                            <td><?= $product->featured ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td><?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Main Menu') ?></th>
                            <td><?= $product->main_menu ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Body') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->body); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Markdown') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->markdown); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Summary') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->summary); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->meta_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Meta Keywords') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->meta_keywords); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Facebook Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->facebook_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Linkedin Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->linkedin_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Instagram Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->instagram_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Twitter Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->twitter_description); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($product->tags)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Description') ?></th>
                                            <th><?= __('Image') ?></th>
                                            <th><?= __('Dir') ?></th>
                                            <th><?= __('Alt Text') ?></th>
                                            <th><?= __('Keywords') ?></th>
                                            <th><?= __('Size') ?></th>
                                            <th><?= __('Mime') ?></th>
                                            <th><?= __('Name') ?></th>
                                            <th><?= __('Meta Title') ?></th>
                                            <th><?= __('Meta Description') ?></th>
                                            <th><?= __('Meta Keywords') ?></th>
                                            <th><?= __('Facebook Description') ?></th>
                                            <th><?= __('Linkedin Description') ?></th>
                                            <th><?= __('Instagram Description') ?></th>
                                            <th><?= __('Twitter Description') ?></th>
                                            <th><?= __('Parent Id') ?></th>
                                            <th><?= __('Main Menu') ?></th>
                                            <th><?= __('Lft') ?></th>
                                            <th><?= __('Rght') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->tags as $tag) : ?>
                                        <tr>
                                            <td><?= h($tag->id) ?></td>
                                            <td><?= h($tag->title) ?></td>
                                            <td><?= h($tag->slug) ?></td>
                                            <td><?= h($tag->description) ?></td>
                                            <td><?= h($tag->image) ?></td>
                                            <td><?= h($tag->dir) ?></td>
                                            <td><?= h($tag->alt_text) ?></td>
                                            <td><?= h($tag->keywords) ?></td>
                                            <td><?= h($tag->size) ?></td>
                                            <td><?= h($tag->mime) ?></td>
                                            <td><?= h($tag->name) ?></td>
                                            <td><?= h($tag->meta_title) ?></td>
                                            <td><?= h($tag->meta_description) ?></td>
                                            <td><?= h($tag->meta_keywords) ?></td>
                                            <td><?= h($tag->facebook_description) ?></td>
                                            <td><?= h($tag->linkedin_description) ?></td>
                                            <td><?= h($tag->instagram_description) ?></td>
                                            <td><?= h($tag->twitter_description) ?></td>
                                            <td><?= h($tag->parent_id) ?></td>
                                            <td><?= h($tag->main_menu) ?></td>
                                            <td><?= h($tag->lft) ?></td>
                                            <td><?= h($tag->rght) ?></td>
                                            <td><?= h($tag->modified) ?></td>
                                            <td><?= h($tag->created) ?></td>
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
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Products') ?></h4>
                            <?php if (!empty($product->child_products)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('User Id') ?></th>
                                            <th><?= __('Kind') ?></th>
                                            <th><?= __('Featured') ?></th>
                                            <th><?= __('Title') ?></th>
                                            <th><?= __('Lede') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Body') ?></th>
                                            <th><?= __('Markdown') ?></th>
                                            <th><?= __('Summary') ?></th>
                                            <th><?= __('Image') ?></th>
                                            <th><?= __('Alt Text') ?></th>
                                            <th><?= __('Keywords') ?></th>
                                            <th><?= __('Name') ?></th>
                                            <th><?= __('Dir') ?></th>
                                            <th><?= __('Size') ?></th>
                                            <th><?= __('Mime') ?></th>
                                            <th><?= __('Is Published') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th><?= __('Modified') ?></th>
                                            <th><?= __('Published') ?></th>
                                            <th><?= __('Meta Title') ?></th>
                                            <th><?= __('Meta Description') ?></th>
                                            <th><?= __('Meta Keywords') ?></th>
                                            <th><?= __('Facebook Description') ?></th>
                                            <th><?= __('Linkedin Description') ?></th>
                                            <th><?= __('Instagram Description') ?></th>
                                            <th><?= __('Twitter Description') ?></th>
                                            <th><?= __('Word Count') ?></th>
                                            <th><?= __('Parent Id') ?></th>
                                            <th><?= __('Lft') ?></th>
                                            <th><?= __('Rght') ?></th>
                                            <th><?= __('Main Menu') ?></th>
                                            <th><?= __('View Count') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->child_products as $childProduct) : ?>
                                        <tr>
                                            <td><?= h($childProduct->id) ?></td>
                                            <td><?= h($childProduct->user_id) ?></td>
                                            <td><?= h($childProduct->kind) ?></td>
                                            <td><?= h($childProduct->featured) ?></td>
                                            <td><?= h($childProduct->title) ?></td>
                                            <td><?= h($childProduct->lede) ?></td>
                                            <td><?= h($childProduct->slug) ?></td>
                                            <td><?= h($childProduct->body) ?></td>
                                            <td><?= h($childProduct->markdown) ?></td>
                                            <td><?= h($childProduct->summary) ?></td>
                                            <td><?= h($childProduct->image) ?></td>
                                            <td><?= h($childProduct->alt_text) ?></td>
                                            <td><?= h($childProduct->keywords) ?></td>
                                            <td><?= h($childProduct->name) ?></td>
                                            <td><?= h($childProduct->dir) ?></td>
                                            <td><?= h($childProduct->size) ?></td>
                                            <td><?= h($childProduct->mime) ?></td>
                                            <td><?= h($childProduct->is_published) ?></td>
                                            <td><?= h($childProduct->created) ?></td>
                                            <td><?= h($childProduct->modified) ?></td>
                                            <td><?= h($childProduct->published) ?></td>
                                            <td><?= h($childProduct->meta_title) ?></td>
                                            <td><?= h($childProduct->meta_description) ?></td>
                                            <td><?= h($childProduct->meta_keywords) ?></td>
                                            <td><?= h($childProduct->facebook_description) ?></td>
                                            <td><?= h($childProduct->linkedin_description) ?></td>
                                            <td><?= h($childProduct->instagram_description) ?></td>
                                            <td><?= h($childProduct->twitter_description) ?></td>
                                            <td><?= h($childProduct->word_count) ?></td>
                                            <td><?= h($childProduct->parent_id) ?></td>
                                            <td><?= h($childProduct->lft) ?></td>
                                            <td><?= h($childProduct->rght) ?></td>
                                            <td><?= h($childProduct->main_menu) ?></td>
                                            <td><?= h($childProduct->view_count) ?></td>
                                            <td class="actions">
                                                <?= $this->element('evd_dropdown', ['controller' => 'Products', 'model' => $childProduct, 'display' => 'title']); ?>
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