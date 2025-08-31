<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Comment $comment
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $articles
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
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Edit Comment') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($comment, ['class' => 'needs-validation', 'novalidate' => true]) ?>
                    <fieldset>

                        <div class="mb-3">
                            <?php echo $this->Form->control('content', ['class' => 'form-control' . ($this->Form->isFieldError('content') ? ' is-invalid' : '')]); ?>
                                <?php if ($this->Form->isFieldError('content')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('content') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('display', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('display') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-published">
                                    <?= __('Display') ?>
                                </label>
                                <?php if ($this->Form->isFieldError('display')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('display') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('is_inappropriate', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('is_inappropriate') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-published">
                                    <?= __('Inappropriate') ?>
                                </label>
                                <?php if ($this->Form->isFieldError('is_inappropriate')): ?>
                                    <div class="invalid-feedback">
                                        <?= $this->Form->error('is_inappropriate') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                    </fieldset>
                    <div class="form-group">
                        <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?= $this->Form->end() ?>
                </div>
            </div>
        </div>
    </div>
</div>