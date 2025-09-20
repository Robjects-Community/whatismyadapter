<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $parentProducts
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
                            <?php echo $this->Form->control('kind', ['class' => 'form-control' . ($this->Form->isFieldError('kind') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('kind')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('kind') ?>
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
                            <?php echo $this->Form->control('title', ['class' => 'form-control' . ($this->Form->isFieldError('title') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('title')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('title') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('lede', ['class' => 'form-control' . ($this->Form->isFieldError('lede') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('lede')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('lede') ?>
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
                            <?php echo $this->Form->control('body', ['class' => 'form-control' . ($this->Form->isFieldError('body') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('body')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('body') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('markdown', ['class' => 'form-control' . ($this->Form->isFieldError('markdown') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('markdown')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('markdown') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('summary', ['class' => 'form-control' . ($this->Form->isFieldError('summary') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('summary')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('summary') ?>
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
                            <?php echo $this->Form->control('keywords', ['class' => 'form-control' . ($this->Form->isFieldError('keywords') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('keywords')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('keywords') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('name', ['class' => 'form-control' . ($this->Form->isFieldError('name') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('name')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('name') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('dir', ['class' => 'form-control' . ($this->Form->isFieldError('dir') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('dir')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('dir') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('size', ['class' => 'form-control' . ($this->Form->isFieldError('size') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('size')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('size') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('mime', ['class' => 'form-control' . ($this->Form->isFieldError('mime') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('mime')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('mime') ?>
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
                            <?php echo $this->Form->control('published', ['empty' => true, 'class' => 'form-control' . ($this->Form->isFieldError('published') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('published')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('published') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('meta_title', ['class' => 'form-control' . ($this->Form->isFieldError('meta_title') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('meta_title')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('meta_title') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('meta_description', ['class' => 'form-control' . ($this->Form->isFieldError('meta_description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('meta_description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('meta_description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('meta_keywords', ['class' => 'form-control' . ($this->Form->isFieldError('meta_keywords') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('meta_keywords')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('meta_keywords') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('facebook_description', ['class' => 'form-control' . ($this->Form->isFieldError('facebook_description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('facebook_description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('facebook_description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('linkedin_description', ['class' => 'form-control' . ($this->Form->isFieldError('linkedin_description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('linkedin_description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('linkedin_description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('instagram_description', ['class' => 'form-control' . ($this->Form->isFieldError('instagram_description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('instagram_description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('instagram_description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('twitter_description', ['class' => 'form-control' . ($this->Form->isFieldError('twitter_description') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('twitter_description')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('twitter_description') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('word_count', ['class' => 'form-control' . ($this->Form->isFieldError('word_count') ? ' is-invalid' : '')]); ?>
                                                        <?php if ($this->Form->isFieldError('word_count')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('word_count') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <?php echo $this->Form->control('parent_id', ['options' => $parentProducts, 'empty' => true, 'class' => 'form-select' . ($this->Form->isFieldError('parent_id') ? ' is-invalid' : '')]); ?>
                            <?php if ($this->Form->isFieldError('parent_id')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('parent_id') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                                        <div class="mb-3">
                            <div class="form-check">
                                <?php echo $this->Form->checkbox('main_menu', [
                                    'class' => 'form-check-input' . ($this->Form->isFieldError('main_menu') ? ' is-invalid' : '')
                                ]); ?>
                                <label class="form-check-label" for="main-menu">
                                    <?= __('Main Menu') ?>
                                </label>
                            </div>
                                                        <?php if ($this->Form->isFieldError('main_menu')): ?>
                                <div class="invalid-feedback">
                                    <?= $this->Form->error('main_menu') ?>
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