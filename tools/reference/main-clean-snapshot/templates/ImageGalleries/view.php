<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\ImageGallery $imageGallery
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Image Gallery'), ['action' => 'edit', $imageGallery->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Image Gallery'), ['action' => 'delete', $imageGallery->id], ['confirm' => __('Are you sure you want to delete # {0}?', $imageGallery->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Image Galleries'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Image Gallery'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="imageGalleries view content">
            <h3><?= h($imageGallery->name) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($imageGallery->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($imageGallery->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Slug') ?></th>
                    <td><?= h($imageGallery->slug) ?></td>
                </tr>
                <tr>
                    <th><?= __('Preview Image') ?></th>
                    <td><?= h($imageGallery->preview_image) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created By') ?></th>
                    <td><?= h($imageGallery->created_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified By') ?></th>
                    <td><?= h($imageGallery->modified_by) ?></td>
                </tr>
                <tr>
                    <th><?= __('Meta Title') ?></th>
                    <td><?= h($imageGallery->meta_title) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($imageGallery->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($imageGallery->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Published') ?></th>
                    <td><?= $imageGallery->is_published ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->meta_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Meta Keywords') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->meta_keywords)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Facebook Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->facebook_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Linkedin Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->linkedin_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Instagram Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->instagram_description)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Twitter Description') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($imageGallery->twitter_description)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Images') ?></h4>
                <?php if (!empty($imageGallery->images)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Alt Text') ?></th>
                            <th><?= __('Keywords') ?></th>
                            <th><?= __('Image') ?></th>
                            <th><?= __('Dir') ?></th>
                            <th><?= __('Size') ?></th>
                            <th><?= __('Mime') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($imageGallery->images as $image) : ?>
                        <tr>
                            <td><?= h($image->id) ?></td>
                            <td><?= h($image->name) ?></td>
                            <td><?= h($image->alt_text) ?></td>
                            <td><?= h($image->keywords) ?></td>
                            <td><?= h($image->image) ?></td>
                            <td><?= h($image->dir) ?></td>
                            <td><?= h($image->size) ?></td>
                            <td><?= h($image->mime) ?></td>
                            <td><?= h($image->created) ?></td>
                            <td><?= h($image->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Images', 'action' => 'view', $image->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Images', 'action' => 'edit', $image->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Images', 'action' => 'delete', $image->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $image->id),
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
                <?php if (!empty($imageGallery->slugs)) : ?>
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
                        <?php foreach ($imageGallery->slugs as $slug) : ?>
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
                <h4><?= __('Related Image Galleries Translations') ?></h4>
                <?php if (!empty($imageGallery->_i18n)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Locale') ?></th>
                            <th><?= __('Name') ?></th>
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
                        <?php foreach ($imageGallery->_i18n as $imageGalleriesTranslation) : ?>
                        <tr>
                            <td><?= h($imageGalleriesTranslation->id) ?></td>
                            <td><?= h($imageGalleriesTranslation->locale) ?></td>
                            <td><?= h($imageGalleriesTranslation->name) ?></td>
                            <td><?= h($imageGalleriesTranslation->description) ?></td>
                            <td><?= h($imageGalleriesTranslation->meta_title) ?></td>
                            <td><?= h($imageGalleriesTranslation->meta_description) ?></td>
                            <td><?= h($imageGalleriesTranslation->meta_keywords) ?></td>
                            <td><?= h($imageGalleriesTranslation->facebook_description) ?></td>
                            <td><?= h($imageGalleriesTranslation->linkedin_description) ?></td>
                            <td><?= h($imageGalleriesTranslation->instagram_description) ?></td>
                            <td><?= h($imageGalleriesTranslation->twitter_description) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'ImageGalleriesTranslations', 'action' => 'view', $imageGalleriesTranslation->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'ImageGalleriesTranslations', 'action' => 'edit', $imageGalleriesTranslation->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'ImageGalleriesTranslations', 'action' => 'delete', $imageGalleriesTranslation->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $imageGalleriesTranslation->id),
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