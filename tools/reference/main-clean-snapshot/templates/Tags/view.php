<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Tag'), ['action' => 'edit', $tag->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Tag'), ['action' => 'delete', $tag->id], ['confirm' => __('Are you sure you want to delete # {0}?', $tag->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Tags'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Tag'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="tags view content">
            <h3><?= h($tag->title) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($tag->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Title') ?></th>
                    <td><?= h($tag->title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($tag->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= h($tag->image) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dir') ?></th>
                    <td><?= h($tag->dir) ?></td>
                </tr>
                <tr>
                    <th><?= __('Alt Text') ?></th>
                    <td><?= h($tag->alt_text) ?></td>
                </tr>
                <tr>
                    <th><?= __('Keywords') ?></th>
                    <td><?= h($tag->keywords) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mime') ?></th>
                    <td><?= h($tag->mime) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($tag->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Meta Title') ?></th>
                    <td><?= h($tag->meta_title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Parent Tag') ?></th>
                    <td><?= $tag->hasValue('parent_tag') ? $this->Html->link($tag->parent_tag->title, ['controller' => 'Tags', 'action' => 'view', $tag->parent_tag->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Size') ?></th>
                    <td><?= $tag->size === null ? '' : $this->Number->format($tag->size) ?></td>
                </tr>
                <tr>
                    <th><?= __('Lft') ?></th>
                    <td><?= $this->Number->format($tag->lft) ?></td>
                </tr>
                <tr>
                    <th><?= __('Rght') ?></th>
                    <td><?= $this->Number->format($tag->rght) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($tag->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($tag->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Main Menu') ?></th>
                    <td><?= $tag->main_menu ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->meta_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Keywords') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->meta_keywords)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Facebook Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->facebook_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Linkedin Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->linkedin_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Instagram Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->instagram_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Twitter Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($tag->twitter_description)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Articles') ?></h4>
                <?php if (!empty($tag->articles)) : ?>
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
                        <?php foreach ($tag->articles as $article) : ?>
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
            <div class="related">
                <h4><?= __('Related Slugs') ?></h4>
                <?php if (!empty($tag->slugs)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Model') ?></th>
                            <th><?= __('Foreign Key') ?></th>
                            <th><?= __('Slug') ?></th>
                            <th><?= __('Created') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($tag->slugs as $slug) : ?>
                        <tr>
                            <td><?= h($slug->id) ?></td>
                            <td><?= h($slug->model) ?></td>
                            <td><?= h($slug->foreign_key) ?></td>
                            <td><?= h($slug->slug) ?></td>
                            <td><?= h($slug->created) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Slugs', 'action' => 'view', $slug->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Slugs', 'action' => 'edit', $slug->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Slugs', 'action' => 'delete', $slug->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $slug->id),
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
                <h4><?= __('Related Tags Translations') ?></h4>
                <?php if (!empty($tag->_i18n)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Locale') ?></th>
                            <th><?= __('Title') ?></th>
                            <th><?= __('Description') ?></th>
                            <th><?= __('Meta Title') ?></th>
                            <th><?= __('Meta Description') ?></th>
                            <th><?= __('Meta Keywords') ?></th>
                            <th><?= __('Facebook Description') ?></th>
                            <th><?= __('Linkedin Description') ?></th>
                            <th><?= __('Instagram Description') ?></th>
                            <th><?= __('Twitter Description') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($tag->_i18n as $tagsTranslation) : ?>
                        <tr>
                            <td><?= h($tagsTranslation->id) ?></td>
                            <td><?= h($tagsTranslation->locale) ?></td>
                            <td><?= h($tagsTranslation->title) ?></td>
                            <td><?= h($tagsTranslation->description) ?></td>
                            <td><?= h($tagsTranslation->meta_title) ?></td>
                            <td><?= h($tagsTranslation->meta_description) ?></td>
                            <td><?= h($tagsTranslation->meta_keywords) ?></td>
                            <td><?= h($tagsTranslation->facebook_description) ?></td>
                            <td><?= h($tagsTranslation->linkedin_description) ?></td>
                            <td><?= h($tagsTranslation->instagram_description) ?></td>
                            <td><?= h($tagsTranslation->twitter_description) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'TagsTranslations', 'action' => 'view', $tagsTranslation->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'TagsTranslations', 'action' => 'edit', $tagsTranslation->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'TagsTranslations', 'action' => 'delete', $tagsTranslation->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $tagsTranslation->id),
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