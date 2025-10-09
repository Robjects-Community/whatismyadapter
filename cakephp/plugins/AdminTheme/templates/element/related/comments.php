<?php $hideColumns = $hideColumns ?? []; ?>
<div class="card mt-4">
    <div class="card-body">
        <h4 class="card-title"><?= __('Related Comments') ?></h4>
        <?php if (!empty($comments)) : ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <th><?= __('User') ?></th>
                        <?php endif; ?>
                        <th><?= __('Content') ?></th>
                        <th><?= __('Display') ?></th>
                        <th><?= __('Inappropriate') ?></th>
                        <th><?= __('Reason') ?></th>
                        <th><?= __('Created') ?></th>
                        <th class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comments as $comment) : ?>
                    <tr>
                        <?php if (!in_array('User', $hideColumns)) : ?>
                        <td><?= $model->hasValue('user') ? $this->Html->link($model->user->username, ['controller' => 'Users', 'action' => 'view', $model->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        <?php endif; ?>
                        <td><?= h($comment->content) ?></td>
                        <td><?= $comment->display ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        <td><?= $comment->is_inappropriate ? '<span class="badge bg-danger">' . __('Yes') . '</span>' : '<span class="badge bg-success">' . __('No') . '</span>'; ?></td>
                        <td><?= h($comment->inappropriate_reason) ?></td>
                        <td><?= h($comment->created) ?></td>
                        <td class="actions">
                            <?= $this->element('evd_dropdown', ['controller' => 'Comments', 'model' => $comment, 'display' => 'id']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>