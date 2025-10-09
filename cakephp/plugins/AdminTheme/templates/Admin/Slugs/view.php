<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Slug $slug
 * @var array|null $relatedRecord
 * @var array|null $relatedSlugs
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Slug',
        'controllerName' => 'Slugs',
        'entity' => $slug,
        'entityDisplayName' => $slug->slug
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">
                        <?php
                        if ($relatedRecord) {
                            $routeName = match ($slug->model) {
                                'Articles' => $relatedRecord->kind === 'page' ? 'page-by-slug' : 'article-by-slug',
                                'Tags' => 'tag-by-slug',
                                default => null,
                            };

                            // Only create link if it's a Tag or a published Article
                            $showLink = $slug->model === 'Tags' || 
                                ($slug->model === 'Articles' && $relatedRecord->is_published);

                            if ($routeName && $showLink) {
                                echo $this->Html->link(
                                    h($slug->slug),
                                    [
                                        '_name' => $routeName,
                                        'slug' => $slug->slug,
                                    ],
                                    [
                                        'class' => 'text-decoration-none',
                                        'target' => '_blank'
                                    ]
                                );
                            } else {
                                echo h($slug->slug);
                            }
                        } else {
                            echo h($slug->slug);
                        }
                        ?>
                    </h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Model') ?></th>
                            <td>
                                <?php
                                if ($relatedRecord && $slug->model === 'Articles') {
                                    echo h(ucfirst($relatedRecord->kind));
                                } else {
                                    echo h(str_replace('s', '', $slug->model));
                                }
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Related Content') ?></th>
                            <td>
                                <?php if ($relatedRecord): ?>
                                    <?= $this->Html->link(
                                        h($relatedRecord->title),
                                        [
                                            'controller' => $slug->model,
                                            'action' => 'view',
                                            $relatedRecord->id
                                        ],
                                        [
                                            'class' => 'text-decoration-none',
                                            'escape' => false
                                        ]
                                    ) ?>
                                    <?php if ($slug->model === 'Articles' && !$relatedRecord->is_published): ?>
                                        <span class="badge bg-warning ms-2"><?= __('Not Published') ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= h($slug->foreign_key) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($slug->created) ?></td>
                        </tr>
                        <?php if ($relatedRecord): ?>
                        <tr>
                            <th><?= __('Preview') ?></th>
                            <td>
                                <?php
                                $routeName = match ($slug->model) {
                                    'Articles' => $relatedRecord->kind === 'page' ? 'page-by-slug' : 'article-by-slug',
                                    'Tags' => 'tag-by-slug',
                                    default => null,
                                };

                                // Only show preview button if it's a Tag or a published Article
                                $showPreview = $slug->model === 'Tags' || 
                                    ($slug->model === 'Articles' && $relatedRecord->is_published);

                                if ($routeName && $showPreview) {
                                    echo $this->Html->link(
                                        __('View on site'),
                                        [
                                            '_name' => $routeName,
                                            'slug' => $slug->slug,
                                        ],
                                        [
                                            'class' => 'btn btn-sm btn-outline-primary',
                                            'target' => '_blank'
                                        ]
                                    );
                                } elseif ($slug->model === 'Articles') {
                                    echo '<span class="text-muted">' . __('Not available until published') . '</span>';
                                }
                                ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($relatedSlugs)): ?>
                        <tr>
                            <th><?= __('Other Slugs') ?></th>
                            <td>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($relatedSlugs as $relatedSlug): ?>
                                        <li>
                                            <?= h($relatedSlug->slug) ?>
                                            <small class="text-muted">
                                                (<?= $relatedSlug->created->format('Y-m-d H:i:s') ?>)
                                            </small>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>