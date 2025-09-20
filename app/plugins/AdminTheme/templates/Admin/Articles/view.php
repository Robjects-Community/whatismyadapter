<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 */
?>

<?php
    echo $this->element('actions_card', [
        'modelName' => ($article->kind == 'page') ? 'Page' : 'Post',
        'controllerName' => 'Articles',
        'controllerIndexAction' => ($article->kind == 'page') ? 'tree-index' : 'index',
        'entity' => $article,
        'entityDisplayName' => $article->title,
        'urlParams' => ($article->kind == 'page') ? ['kind' => 'page'] : [],
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($article->title) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('User') ?></th>
                            <td>
                                <?= $article->hasValue('user') ? $this->Html->link($article->user->username, ['controller' => 'Users', 'action' => 'view', $article->user->id], ['class' => 'btn btn-link']) : '' ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Kind') ?></th>
                            <td><?= h($article->kind) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Title') ?></th>
                            <td><?= h($article->title) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Slug') ?></th>
                            <td>
                                <?php $ruleName = ($article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>
                                <?php if ($article->is_published == true): ?>
                                    
                                    <?= $this->Html->link(
                                        $article->slug,
                                        [
                                            'controller' => 'Articles',
                                            'action' => 'view-by-slug',
                                            'slug' => $article->slug,
                                            '_name' => $ruleName,
                                        ],
                                        ['escape' => false]
                                    );
                                    ?>
                                <?php else: ?>
                                    <?= $this->Html->link(
                                        $article->slug,
                                        [
                                            'prefix' => 'Admin',
                                            'controller' => 'Articles',
                                            'action' => 'view',
                                            $article->id
                                        ],
                                        ['escape' => false]
                                    ) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($article->image)) : ?>
                                <div class="position-relative">
                                    <?= $this->element('image/icon', ['model' => $article, 'icon' => $article->smallImageUrl, 'preview' => $article->largeImageUrl]); ?>
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($article->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($article->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Published') ?></th>
                            <td><?= h($article->published) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Published') ?></th>
                            <td>
                                <?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                        <th><?= __('Page Views') ?></th>
                            <td>
                            <?= $this->Html->link(
                                '<span class="badge bg-info me-3">' . __('{0} Views', $article->view_count) . '</span>',
                                [
                                    'prefix' => 'Admin',
                                    'controller' => 'PageViews',
                                    'action' => 'pageViewStats',
                                    $article['id']
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
                            <p class="card-text"><?= html_entity_decode($article->body); ?>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Lede') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->lede); ?></p>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Summary') ?></h5>
                            <p class="card-text"><?= html_entity_decode($article->summary); ?></p>
                        </div>
                    </div>
                    <div class="mt-4">
                    <?= $this->element('seo_display_fields', ['model' => $article]); ?>
                    </div>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Tags') ?></h4>
                            <?php if (!empty($article->tags)) : ?>
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
                                        <?php foreach ($article->tags as $tag) : ?>
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
                    <?php if (!empty($article->images)) : ?>
                        <div class="mb-3">
                        <?= $this->element('image_carousel', [
                            'images' => $article->images,
                            'carouselId' => $carouselId ?? 'imageCarouselID',
                            'hideRemove' => true,
                        ]) ?>
                        </div>
                    <?php endif; ?>
                    </div>
                    
                    <?= $this->element('related/comments', ['comments' => $article->comments, 'model' => $article]) ?>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title"><?= __('Related Slugs') ?></h4>
                            <?php if (!empty($article->slugs)) : ?>
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
                                        <?php foreach ($article->slugs as $slug) : ?>
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