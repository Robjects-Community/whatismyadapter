<div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
    <div class="dropdown">
        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <?= __('Actions') ?>
        </button>
        <ul class="dropdown-menu">
            <li>
                <?= $this->Html->link(
                    __('Edit'),
                    isset($controller) ? ['controller' => $controller, 'action' => 'edit', $model->id] : ['action' => 'edit', $model->id],
                    ['class' => 'dropdown-item'],
                ) ?>
            </li>
            <li>
                <?= $this->Html->link(
                    __('View'),
                    isset($controller) ? ['controller' => $controller, 'action' => 'view', $model->id] : ['action' => 'view', $model->id],
                    ['class' => 'dropdown-item'],
                ) ?>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <?= $this->Form->postLink(
                    __('Delete'),
                    isset($controller) ? ['controller' => $controller, 'action' => 'delete', $model->id] : ['action' => 'delete', $model->id],
                    ['confirm' => __('Are you sure you want to delete {0}?', $model->{$display}), 'class' => 'dropdown-item text-danger'],
                ) ?>
            </li>
        </ul>
    </div>
</div>