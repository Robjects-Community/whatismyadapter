<?php use App\Utility\SettingsManager; ?>
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
    <?= $this->Form->control('image', [
        'type' => 'file',
        'label' => [
            'text' => __('Main Image'),
            'class' => 'form-label'
        ],
        'class' => 'form-control' . ($this->Form->isFieldError('image') ? ' is-invalid' : ''),
        'id' => 'customFile'
    ]) ?>
    <?php if ($this->Form->isFieldError('image')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('image') ?>
        </div>
    <?php endif; ?>
</div>

<?php if (!empty($tag->image)): ?>
    <div class="mb-3">
        <?= $this->element('image/icon', ['model' => $tag, 'icon' => $tag->smallImageUrl, 'preview' => $tag->extraLargeImageUrl]); ?>
    </div>
<?php endif; ?>

<div class="mb-3">
    <?php $parentId = $this->request->getQuery('parent_id'); ?>
        <?php echo $this->Form->control('parent_id',
            [
                'empty' => __('None'),
                'options' => $parentTags,
                'default' => $parentId,
                'class' => 'form-control' . ($this->Form->isFieldError('parent_id') ? ' is-invalid' : '')
            ]); ?>
        <?php if ($this->Form->isFieldError('parent_id')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('parent_id') ?>
        </div>
        <?php endif; ?>
</div>

<?php if (SettingsManager::read('SitePages.mainTagMenuShow') == 'selected') : ?>
<div class="form-check">
    <?php echo $this->Form->checkbox('main_menu', [
        'class' => 'form-check-input' . ($this->Form->isFieldError('main_menu') ? ' is-invalid' : '')
    ]); ?>
    <label class="form-check-label" for="main_menu">
        <?= __('Main Menu') ?>
    </label>
    <?php if ($this->Form->isFieldError('main_menu')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('main_menu') ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="mb-3">
    <?php echo $this->Form->label('articles._ids', __('Tag Articles/Pages'), ['class' => 'form-label']); ?>
    <?php echo $this->Form->select('articles._ids', $articles, [
        'multiple' => true,
        'data-live-search' => 'true',
        'data-actions-box' => 'true',
        'id' => 'articles-select',
        'class' => 'w-100 form-select' . ($this->Form->isFieldError('articles._ids') ? ' is-invalid' : '')
    ]); ?>
    <?php if ($this->Form->isFieldError('articles._ids')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('articles._ids') ?>
        </div>
    <?php endif; ?>
</div>