<ul class="list-group sortable-list" data-level="<?= $level ?>">
    <?php foreach ($tags as $tag): ?>
        <li class="list-group-item list-group-item-action sortable-item py-2 px-3 border" data-id="<?= $tag['id'] ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="d-flex align-items-center">
                    <span class="handle me-2">☰</span>
                    <span class="me-3"><?= $tag->title ?></span>
                </div>
                <div class="btn-group" role="group">
                    <div class="btn-group">
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= __('Actions') ?>
                            </button>
                            <ul class="dropdown-menu">
                                <li><?= $this->Html->link(__('Add'), ['action' => 'add', '?' => ['parent_id' => $tag['id']]], ['class' => 'dropdown-item']) ?></li>
                                <li><?= $this->Html->link(__('Edit'), ['action' => 'edit', $tag['id'], '?' => ['parent_id' => $tag['parent_id']]], ['class' => 'dropdown-item']) ?></li>
                                <li><?= $this->Html->link(__('View'), ['action' => 'view', $tag['id']], ['class' => 'dropdown-item']) ?></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $tag['id']], ['confirm' => __('Are you sure you want to delete {0}?', $tag->title), 'class' => 'dropdown-item text-danger']) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="children-container">
                <?php if (!empty($tag['children'])): ?>
                    <?= $this->element('tree/tag_tree', ['tags' => $tag['children'], 'level' => $level + 1]) ?>
                <?php else: ?>
                    <ul class="list-group sortable-list"></ul>
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>