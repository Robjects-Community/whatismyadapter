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
                            <th><?= __('Article') ?></th>
                            <td><?= $product->hasValue('article') ? $this->Html->link($product->article->title, ['controller' => 'Articles', 'action' => 'view', $product->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($product->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td><?= h($product->slug) ?></td>
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
                            <th><?= __('Currency') ?></th>
                            <td><?= h($product->currency) ?></td>
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
                            <th><?= __('Verification Status') ?></th>
                            <td><?= h($product->verification_status) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Price') ?></th>
                            <td><?= $product->price === null ? '' : $this->Number->format($product->price) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Reliability Score') ?></th>
                            <td><?= $product->reliability_score === null ? '' : $this->Number->format($product->reliability_score) ?></td>
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
                            <th><?= __('Is Published') ?></th>
                            <td><?= $product->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Featured') ?></th>
                            <td><?= $product->featured ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Description') ?></h5>
                            <p class="card-text"><?= html_entity_decode($product->description); ?></p>
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
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($product->slugs)) : ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= __('Id') ?></th>
                                            <th><?= __('Model') ?></th>
                                            <th><?= __('Foreign Key') ?></th>
                                            <th><?= __('Slug') ?></th>
                                            <th><?= __('Created') ?></th>
                                            <th class="actions"><?= __('Actions') ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($product->slugs as $slug) : ?>
                                        <tr>
                                            <td><?= h($slug->id) ?></td>
                                            <td><?= h($slug->model) ?></td>
                                            <td><?= h($slug->foreign_key) ?></td>
                                            <td><?= h($slug->slug) ?></td>
                                            <td><?= h($slug->created) ?></td>
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