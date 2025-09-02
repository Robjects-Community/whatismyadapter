<?php $hideColumns = $hideColumns ?? []; ?>
<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title"><?= __('Related Articles/Pages') ?></h4>
        <?php if (!empty($articles)) : ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <th><?= __('User') ?></th>
                        <?php endif; ?>
                        <th><?= __('Kind') ?></th>
                        <th><?= __('Title') ?></th>
                        <th><?= __('Published') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article) : ?>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <td>
                            <?= $article->hasValue('user') ? $this->Html->link($article->user->username, ['controller' => 'Users', 'action' => 'view', $article->user->id], ['class' => 'btn btn-link']) : '' ?>
                        </td>
                        <?php endif; ?>
                        <td><?= h($article->kind) ?></td>
                        <td>
                            <?php $ruleName = ($article->kind == 'article') ? 'article-by-slug' : 'page-by-slug';?>
                            <?php if ($article->is_published == true): ?>
                                <?= $this->Html->link(
                                    $article->title,
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
                                    $article->title,
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
                        <td>
                            <?= $article->is_published ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?>
                        </td>
                        <td class="actions">
                            <?= $this->element('evd_dropdown', ['controller' => 'Articles', 'model' => $article, 'display' => 'title']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>