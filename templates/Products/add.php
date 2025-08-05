<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $articles
 * @var \Cake\Collection\CollectionInterface|string[] $tags
 */
?>
<?php
// Only show actions if we have an entity (edit mode)
if (!$product->isNew()) {
    echo $this->element('actions_card', [
        'modelName' => 'Product',
        'controllerName' => 'Products',
        'entity' => $product,
        'entityDisplayName' => $product->title
    ]);
}
?>
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title"><?= __('Add Product') ?></h5>
                </div>
                <div class="card-body">
                    <?= $this->Form->create($product,
                    [
                        'type' => 'file',
                        'enctype' => 'multipart/form-data',
                        'class' => 'needs-validation', 'novalidate' => true
                    ]) ?>
                    <fieldset>
                    <div class="mb-3">
                            <?php echo $this->Form->control('user_id', ['options' => $users, 'class' => 'form-select' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('user_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('user_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('article_id', ['options' => $articles, 'empty' => true, 'class' => 'form-select' . ($this->Form->isFieldError('article_id') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('article_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('article_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('title', ['class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('title')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('title') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('slug', ['class' => 'form-control' . ($this->Form->isFieldError('slug') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('slug')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('slug') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('description', ['class' => 'form-control' . ($this->Form->isFieldError('description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('manufacturer', ['class' => 'form-control' . ($this->Form->isFieldError('manufacturer') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('manufacturer')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('manufacturer') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('model_number', ['class' => 'form-control' . ($this->Form->isFieldError('model_number') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('model_number')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('model_number') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('price', ['class' => 'form-control' . ($this->Form->isFieldError('price') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('price')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('price') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('currency', ['class' => 'form-control' . ($this->Form->isFieldError('currency') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('currency')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('currency') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('image', ['class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('image')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('image') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('alt_text', ['class' => 'form-control' . ($this->Form->isFieldError('alt_text') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('alt_text')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('alt_text') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('is_published', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('is_published') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="is-published">
                                    <?= __('Is Published') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('is_published')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('is_published') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('featured', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('featured') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="featured">
                                    <?= __('Featured') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('featured')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('featured') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('verification_status', ['class' => 'form-control' . ($this->Form->isFieldError('verification_status') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('verification_status')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('verification_status') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('reliability_score', ['class' => 'form-control' . ($this->Form->isFieldError('reliability_score') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('reliability_score')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('reliability_score') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('view_count', ['class' => 'form-control' . ($this->Form->isFieldError('view_count') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('view_count')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('view_count') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('created')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('created') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php if ($this->Form->isFieldError('modified')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('modified') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                                                                                        <div class="mb-3">
                            <?php echo $this->Form->control('tags._ids', ['options' => $tags, 'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('tags._ids')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('tags._ids') ?>
                                </div>
                            <?php endif; ?>
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