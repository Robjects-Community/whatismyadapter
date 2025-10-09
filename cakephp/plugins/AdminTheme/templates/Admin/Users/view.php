<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<?php use App\Utility\SettingsManager; ?>
<?php
    echo $this->element('actions_card', [
        'modelName' => 'User',
        'controllerName' => 'Users',
        'entity' => $user,
        'entityDisplayName' => $user->username
    ]);
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title"><?= h($user->username) ?></h2>
                    <table class="table table-striped">
                        <tr>
                            <th><?= __('Email') ?></th>
                            <td><?= h($user->email) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Username') ?></th>
                            <td><?= h($user->username) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Image') ?></th>
                            <td>
                                <?php if (!empty($user->image)): ?>
                                    <div class="mb-3">
                                        <?= $this->element('image/icon', ['model' => $user, 'icon' => $user->teenyImageUrl, 'preview' => $user->extraLargeImageUrl]); ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th><?= __('Created') ?></th>
                            <td><?= h($user->created) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Modified') ?></th>
                            <td><?= h($user->modified) ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Is Admin') ?></th>
                            <td><?= $user->is_admin ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-warning">' . __('No') . '</span>'; ?></td>
                        </tr>
                        <tr>
                            <th><?= __('Active') ?></th>
                            <td><?= $user->active ? '<span class="badge bg-success">' . __('Yes') . '</span>' : '<span class="badge bg-danger">' . __('No') . '</span>'; ?></td>
                        </tr>
                    </table>

                    <?= $this->element('related/articles', ['articles' => $user->articles, 'hideColumns' => ['User']]) ?>

                    <?= $this->element('related/comments', ['comments' => $user->comments, 'model' => $user, 'hideColumns' => ['User']]) ?>

                </div>
            </div>
        </div>
    </div>
</div>