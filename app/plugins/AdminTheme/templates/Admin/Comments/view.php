<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 */
?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'Comment',
        'controllerName' => 'Comments',
        'entity' => $comment,
        'entityDisplayName' => $comment->model
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= __('Comment') ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Article') ?></th>
                            <td><?= $comment->hasValue('article') ? $this->Html->link($comment->article->title, ['controller' => 'Articles', 'action' => 'view', $comment->article->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('User') ?></th>
                            <td><?= $comment->hasValue('user') ? $this->Html->link($comment->user->username, ['controller' => 'Users', 'action' => 'view', $comment->user->id], ['class' => 'btn btn-link']) : '' ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Inappropriate Reason') ?></th>
                            <td><?= h($comment->inappropriate_reason) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($comment->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Display') ?></th>
                            <td><?= $comment->display ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Inappropriate') ?></th>
                            <td><?= $comment->is_inappropriate ? '<span class="badge bg-danger">' . __('Yes') . '</span>' : '<span class="badge bg-success">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>
                    <div class="card mt-4">
                        <div class="card-body">
                            <h5 class="card-title"><?= __('Content') ?></h5>
                            <p class="card-text"><?= $this->Text->autoParagraph(h($comment->content)); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>