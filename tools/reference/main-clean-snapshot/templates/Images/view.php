<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Image $image
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Image'), ['action' => 'edit', $image->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Image'), ['action' => 'delete', $image->id], ['confirm' => __('Are you sure you want to delete # {0}?', $image->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Images'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="images view content">
            <h3><?= h($image->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($image->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($image->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Alt Text') ?></th>
                    <td><?= h($image->alt_text) ?></td>
                </tr>
                <tr>
                    <th><?= __('Keywords') ?></th>
                    <td><?= h($image->keywords) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= h($image->image) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dir') ?></th>
                    <td><?= h($image->dir) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mime') ?></th>
                    <td><?= h($image->mime) ?></td>
                </tr>
                <tr>
                    <th><?= __('Size') ?></th>
                    <td><?= $this->Number->format($image->size) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($image->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($image->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Image Galleries') ?></h4>
                <?php if (!empty($image->image_galleries)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Slug') ?></th>
                            <th><?= __('Description') ?></th>
                            <th><?= __('Preview Image') ?></th>
                            <th><?= __('Is Published') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Created By') ?></th>
                            <th><?= __('Modified By') ?></th>
                            <th><?= __('Meta Title') ?></th>
                            <th><?= __('Meta Description') ?></th>
                            <th><?= __('Meta Keywords') ?></th>
                            <th><?= __('Facebook Description') ?></th>
                            <th><?= __('Linkedin Description') ?></th>
                            <th><?= __('Instagram Description') ?></th>
                            <th><?= __('Twitter Description') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($image->image_galleries as $imageGallery) : ?>
                        <tr>
                            <td><?= h($imageGallery->id) ?></td>
                            <td><?= h($imageGallery->name) ?></td>
                            <td><?= h($imageGallery->slug) ?></td>
                            <td><?= h($imageGallery->description) ?></td>
                            <td><?= h($imageGallery->preview_image) ?></td>
                            <td><?= h($imageGallery->is_published) ?></td>
                            <td><?= h($imageGallery->created) ?></td>
                            <td><?= h($imageGallery->modified) ?></td>
                            <td><?= h($imageGallery->created_by) ?></td>
                            <td><?= h($imageGallery->modified_by) ?></td>
                            <td><?= h($imageGallery->meta_title) ?></td>
                            <td><?= h($imageGallery->meta_description) ?></td>
                            <td><?= h($imageGallery->meta_keywords) ?></td>
                            <td><?= h($imageGallery->facebook_description) ?></td>
                            <td><?= h($imageGallery->linkedin_description) ?></td>
                            <td><?= h($imageGallery->instagram_description) ?></td>
                            <td><?= h($imageGallery->twitter_description) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'ImageGalleries', 'action' => 'view', $imageGallery->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'ImageGalleries', 'action' => 'edit', $imageGallery->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'ImageGalleries', 'action' => 'delete', $imageGallery->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $imageGallery->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Articles') ?></h4>
                <?php if (!empty($image->articles)) : ?>
                <div class="table-responsive">
                    <table>
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
                        <?php foreach ($image->articles as $article) : ?>
                        <tr>
                            <td><?= h($article->id) ?></td>
                            <td><?= h($article->user_id) ?></td>
                            <td><?= h($article->kind) ?></td>
                            <td><?= h($article->featured) ?></td>
                            <td><?= h($article->title) ?></td>
                            <td><?= h($article->lede) ?></td>
                            <td><?= h($article->slug) ?></td>
                            <td><?= h($article->body) ?></td>
                            <td><?= h($article->markdown) ?></td>
                            <td><?= h($article->summary) ?></td>
                            <td><?= h($article->image) ?></td>
                            <td><?= h($article->alt_text) ?></td>
                            <td><?= h($article->keywords) ?></td>
                            <td><?= h($article->name) ?></td>
                            <td><?= h($article->dir) ?></td>
                            <td><?= h($article->size) ?></td>
                            <td><?= h($article->mime) ?></td>
                            <td><?= h($article->is_published) ?></td>
                            <td><?= h($article->created) ?></td>
                            <td><?= h($article->modified) ?></td>
                            <td><?= h($article->published) ?></td>
                            <td><?= h($article->meta_title) ?></td>
                            <td><?= h($article->meta_description) ?></td>
                            <td><?= h($article->meta_keywords) ?></td>
                            <td><?= h($article->facebook_description) ?></td>
                            <td><?= h($article->linkedin_description) ?></td>
                            <td><?= h($article->instagram_description) ?></td>
                            <td><?= h($article->twitter_description) ?></td>
                            <td><?= h($article->word_count) ?></td>
                            <td><?= h($article->parent_id) ?></td>
                            <td><?= h($article->lft) ?></td>
                            <td><?= h($article->rght) ?></td>
                            <td><?= h($article->main_menu) ?></td>
                            <td><?= h($article->view_count) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Articles', 'action' => 'view', $article->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Articles', 'action' => 'delete', $article->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $article->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>