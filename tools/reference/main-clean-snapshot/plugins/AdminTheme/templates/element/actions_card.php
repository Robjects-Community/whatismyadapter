<?php
use Cake\Core\Configure;
use Cake\Utility\Inflector;

$modelNamePlural = Inflector::pluralize($modelName);
$debugOnlyOptions = $debugOnlyOptions ?? [];
$controllerIndexAction = $controllerIndexAction ?? 'index';
$entityDisplayName = $entityDisplayName ?? '';
?>

<div class="page-actions-floating">
    <div class="btn-group" role="group" aria-label="<?= __('Page Actions') ?>">

            <?= $this->Html->link(
                '<i class="fas fa-list me-1"></i>' . __('List {0}', [$modelNamePlural]),
                [
                    'controller' => $controllerName,
                    'action' => $controllerIndexAction,
                    '?' => $urlParams ?? [],
                ],
                ['class' => 'btn btn-secondary', 'escape' => false],
            ) ?>

            <?php if (!in_array($this->request->getParam('action'), ['add', 'view', 'bulkUpload', 'sendEmail'])) : ?>
                <?php if (!isset($hideView)) : ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-eye me-1"></i>' . __('View'),
                        [
                            'controller' => $controllerName,
                            'action' => 'view',
                            $entity->id,
                            '?' => $urlParams ?? [],
                        ],
                        ['class' => 'btn btn-primary', 'escape' => false],
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!isset($hideNew)) : ?>
                <?php if (
                    (Configure::read('debug') && in_array('add', $debugOnlyOptions))
                    || !in_array('add', $debugOnlyOptions)
) : ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-plus me-1"></i>' . __('New'),
                        [
                            'controller' => $controllerName,
                            'action' => 'add',
                            '?' => $urlParams ?? [],
                        ],
                        ['class' => 'btn btn-success', 'escape' => false],
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!in_array($this->request->getParam('action'), ['add', 'edit', 'bulkUpload', 'sendEmail'])) : ?>
                <?php if (!isset($hideEdit)) : ?>
                    <?= $this->Html->link(
                        '<i class="fas fa-edit me-1"></i>' . __('Edit'),
                        [
                            'controller' => $controllerName,
                            'action' => 'edit',
                            $entity->id,
                            '?' => $urlParams ?? [],
                        ],
                        ['class' => 'btn btn-warning', 'escape' => false],
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>


            <?php if (!in_array($this->request->getParam('action'), ['add', 'bulkUpload', 'sendEmail'])) : ?>
                <?php if (!isset($hideDelete)) : ?>
                    <?php
                        $customConfirm = __('Are you sure you want to delete {0}?', $entityDisplayName);
                    if (isset($confirm)) {
                        $customConfirm = $confirm;
                    }
                    ?>

                    <?php if (empty($debugOnlyOptions) || in_array('delete', $debugOnlyOptions) && Configure::read('debug')) : ?>
                        <?= $this->Form->postLink(
                            '<i class="fas fa-trash me-1"></i>' . __('Delete'),
                            [
                                'controller' => $controllerName,
                                'action' => 'delete',
                                $entity->id,
                                '?' => $urlParams ?? [],
                            ],
                            [
                                'confirm' => $customConfirm,
                                'class' => 'btn btn-danger',
                                'escape' => false,
                            ],
                        ) ?>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
    </div>
</div>