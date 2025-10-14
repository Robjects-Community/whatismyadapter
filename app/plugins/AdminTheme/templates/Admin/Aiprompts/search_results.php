<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Aiprompt> $aiprompts
 */
?>
<table class="table table-striped">
    <thead>
        <tr>
            <th scope="col"><?= $this->Paginator->sort('task_type') ?></th>
            <th scope="col"><?= $this->Paginator->sort('model') ?></th>
            <th scope="col"><?= $this->Paginator->sort('max_tokens') ?></th>
            <th scope="col"><?= $this->Paginator->sort('temperature') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($aiprompts as $aiprompt): ?>
        <tr>
            <td><?= h($aiprompt->task_type) ?></td>
            <td><?= h($aiprompt->model) ?></td>
            <td><?= $this->Number->format($aiprompt->max_tokens) ?></td>
            <td><?= $this->Number->format($aiprompt->temperature) ?></td>
            <td><?= h($aiprompt->created) ?></td>
            <td><?= h($aiprompt->modified) ?></td>
            <td>
                <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                    <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= __('Actions') ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(__('View'), ['action' => 'view', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $aiprompt->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $aiprompt->id], ['confirm' => __('Are you sure you want to delete {0}?', $aiprompt->task_type), 'class' => 'dropdown-item text-danger']) ?>
                        </li>
                    </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($aiprompts), 'search' => $search ?? '']) ?>