<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\EmailTemplate> $emailTemplates
 */
?>
<table class="table table-striped">
    <thead>
        <tr>
            <th scope="col"><?= $this->Paginator->sort('name') ?></th>
            <th scope="col"><?= $this->Paginator->sort('created') ?></th>
            <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
            <th scope="col"><?= __('Actions') ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($emailTemplates as $emailTemplate): ?>
        <tr>
            <td><?= h($emailTemplate->name) ?></td>
            <td><?= h($emailTemplate->created) ?></td>
            <td><?= h($emailTemplate->modified) ?></td>
            <td>
                <div class="btn-group w-100 align-items-center justify-content-between flex-wrap">
                    <div class="dropdown">
                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?= __('Actions') ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <?= $this->Html->link(__('View'), ['action' => 'view', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $emailTemplate->id], ['class' => 'dropdown-item']) ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $emailTemplate->id], ['confirm' => __('Are you sure you want to delete {0}?', $emailTemplate->name), 'class' => 'dropdown-item text-danger']) ?>
                        </li>
                    </ul>
                    </div>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?= $this->element('pagination', ['recordCount' => count($emailTemplates), 'search' => $search ?? '']) ?>