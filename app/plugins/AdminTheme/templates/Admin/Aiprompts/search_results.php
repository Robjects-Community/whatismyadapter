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
            <th scope="col"><?= $this->Paginator->sort('status') ?></th>
            <th scope="col"><?= __('Description') ?></th>
            <th scope="col"><?= $this->Paginator->sort('category') ?></th>
            <th scope="col"><?= $this->Paginator->sort('is_active') ?></th>
            <th scope="col"><?= $this->Paginator->sort('last_used') ?></th>
            <th scope="col"><?= $this->Paginator->sort('usage_count') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($aiprompts as $aiprompt): ?>
        <tr>
            <td><?= h($aiprompt->task_type) ?></td>
            <td><?= h($aiprompt->model) ?></td>
            <td><?= h($aiprompt->status) ?></td>
            <td><?= $this->Text->truncate((string)$aiprompt->description, 80, ['exact' => false, 'ellipsis' => '…']) ?></td>
            <td><?= h($aiprompt->category) ?></td>
            <td>
                <?php if ($aiprompt->is_active): ?>
                    <span class="badge bg-success"><?= __('Active') ?></span>
                <?php else: ?>
                    <span class="badge bg-secondary"><?= __('Inactive') ?></span>
                <?php endif; ?>
            </td>
            <td><?= $aiprompt->last_used ? $this->Time->timeAgoInWords($aiprompt->last_used) : __('Never') ?></td>
            <td><?= $this->Number->format($aiprompt->usage_count) ?></td>
            <td><?= h($aiprompt->created) ?></td>
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