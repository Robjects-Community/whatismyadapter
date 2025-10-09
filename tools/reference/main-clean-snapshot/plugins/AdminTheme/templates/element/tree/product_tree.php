<ul class="list-group sortable-list" data-level="<?= $level ?>">
    <?php foreach ($articles as $article) : ?>
        <li class="list-group-item list-group-item-action sortable-item py-2 px-3 border" data-id="<?= $article['id'] ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <span class="handle me-2">☰</span>
                    <?php
                    if ($article->is_published) {
                        $title = $this->Html->link(
                            html_entity_decode($article['title']),
                            [
                                'controller' => 'Articles',
                                'action' => 'view-by-slug',
                                'slug' => $article->slug,
                                '_name' => 'page-by-slug',
                            ],
                        );
                    } else {
                        $title = html_entity_decode($article['title']);
                    }
                    ?>
                    <span class="me-3"><?= $title ?></span>
                    <span class="badge me-3 <?= $article->is_published ? 'bg-success' : 'bg-secondary' ?>">
                        <?= $article->is_published ? 'Published' : 'Unpublished' ?>
                    </span>
                    <?= $this->Html->link(
                        '<span class="badge bg-info me-3">' . __('{0} Views', $article->view_count) . '</span>',
                        [
                            'prefix' => 'Admin',
                            'controller' => 'PageViews',
                            'action' => 'pageViewStats',
                            $article['id'],
                        ],
                        [
                            'escape' => false,
                            'class' => 'ms-2',
                        ],
                    ) ?>
                </div>
                <div class="btn-group" role="group">
                    <div class="btn-group">
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= __('Actions') ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?= $this->Html->link(__('Add'), ['action' => 'add', '?' => ['kind' => 'page', 'parent_id' => $article['id']]], ['class' => 'dropdown-item']) ?></li>
                                <li><?= $this->Html->link(__('Edit'), ['action' => 'edit', $article['id'], '?' => ['kind' => 'page', 'parent_id' => $article['parent_id']]], ['class' => 'dropdown-item']) ?></li>
                                <li><?= $this->Html->link(__('View'), ['action' => 'view', $article['id'], '?' => ['kind' => 'page']], ['class' => 'dropdown-item']) ?></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $article['id'], '?' => ['kind' => 'page']], ['confirm' => __('Are you sure you want to delete {0}?', $article->title), 'class' => 'dropdown-item text-danger']) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="children-container">
                <?php if (!empty($article['children'])) : ?>
                    <?= $this->element('tree/page_tree', ['articles' => $article['children'], 'level' => $level + 1]) ?>
                <?php else : ?>
                    <ul class="list-group sortable-list"></ul>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>