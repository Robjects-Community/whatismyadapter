<?php
/**
 * Admin Form Wrapper Element
 * 
 * Provides a standardized wrapper for admin forms to eliminate duplication
 * 
 * @var \App\View\AppView $this
 * @var string $title Form title
 * @var mixed $entity The entity being edited/created  
 * @var string $modelName Display name for the model
 * @var string $controllerName Controller name for actions
 * @var array $formOptions Additional form options
 * @var bool $showActions Whether to show the actions card
 * @var array $actionOptions Options for the actions card
 */

// Set defaults
$title = $title ?? __('Form');
$modelName = $modelName ?? 'Item';
$controllerName = $controllerName ?? $this->request->getParam('controller');
$formOptions = $formOptions ?? [];
$showActions = $showActions ?? !$entity->isNew();
$actionOptions = $actionOptions ?? [];

// Default form options
$defaultFormOptions = [
    'type' => 'file',
    'enctype' => 'multipart/form-data',
    'class' => 'needs-validation',
    'novalidate' => true
];
$formOptions = array_merge($defaultFormOptions, $formOptions);
?>

<?php if ($showActions): ?>
<?= $this->element('actions_card', array_merge([
    'modelName' => $modelName,
    'controllerName' => $controllerName,
    'entity' => $entity,
], $actionOptions)) ?>
<?php endif; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= h($title) ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($entity, $formOptions) ?>
                    <fieldset>
                        <?= $content ?? '' ?>
                    </fieldset>
                    
                    <div class="form-group mt-4">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary btn-lg']) ?>
                        <?= $this->Html->link(
                            __('Cancel'),
                            ['action' => 'index'],
                            ['class' => 'btn btn-outline-secondary ms-2']
                        ) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>