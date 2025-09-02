<?php use App\Utility\SettingsManager; ?>
<?php $kind = $this->request->getQuery('kind', 'article'); ?>
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


<?php if(SettingsManager::read('Editing.editor') == 'markdownit') : ?>
    <?= $this->element('form/article_body_markdownit'); ?>
<?php elseif(SettingsManager::read('Editing.editor') == 'trumbowyg') : ?>
    <div class="mb-3">
            <?php echo $this->Form->control('body',
                [
                    'id' => 'article-body',
                    'rows' => '30',
                    'class' => 'form-control' . ($this->Form->isFieldError('body') ? ' is-invalid' : '')
                ]); ?>
                <?php if ($this->Form->isFieldError('body')): ?>
                <div class="invalid-feedback">
                    <?= $this->Form->error('body') ?>
                </div>
            <?php endif; ?>
        </div>
<?php else: ?>
    <!-- Default fallback editor -->
    <div class="mb-3">
            <?php echo $this->Form->control('body',
                [
                    'id' => 'article-body',
                    'rows' => '30',
                    'class' => 'form-control' . ($this->Form->isFieldError('body') ? ' is-invalid' : '')
                ]); ?>
                <?php if ($this->Form->isFieldError('body')): ?>
                <div class="invalid-feedback">
                    <?= $this->Form->error('body') ?>
                </div>
            <?php endif; ?>
        </div>
<?php endif; ?>

<div class="mb-3">
    <?php echo $this->Form->control('lede', ['class' => 'form-control' . ($this->Form->isFieldError('lede') ? ' is-invalid' : '')]); ?>
        <?php if ($this->Form->isFieldError('lede')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('lede') ?>
        </div>
    <?php endif; ?>
</div>
<div class="mb-3">
    <?php echo $this->Form->control('summary',
        [
            'class' => 'form-control' . ($this->Form->isFieldError('summary') ? ' is-invalid' : '')
        ]); ?>
    <?php if ($this->Form->isFieldError('summary')): ?>
    <div class="invalid-feedback">
        <?= $this->Form->error('summary') ?>
    </div>
    <?php endif; ?>
</div>
<?php if ($kind == 'article') : ?>
<div class="mb-3">
    <div class="me-3">
        <?php echo $this->Form->label('tags._ids', __('Select Tags'), ['class' => 'form-label']); ?>
        <?php echo $this->Form->select('tags._ids', $tags, [
            'multiple' => true,
            'data-live-search' => 'true',
            'data-actions-box' => 'true',
            'id' => 'tags-select',
            'class' => 'form-select' . ($this->Form->isFieldError('tags._ids') ? ' is-invalid' : '')
        ]); ?>
        <?php if ($this->Form->isFieldError('tags._ids')): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error('tags._ids') ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
<div class="mb-3">
    <?php if ($article->tags && SettingsManager::read('AI.enabled') && SettingsManager::read('AI.articleTags')): ?>
        <?php if ($kind == 'article') : ?>
        <div class="form-check d-flex align-items-center">
            <?= $this->Form->checkbox("regenerateTags", [
                'checked' => false,
                'class' => 'form-check-input' . ($this->Form->isFieldError('regenerateTags') ? ' is-invalid' : '')
            ]) ?>
            <label class="form-check-label ms-2" for="regenerate-tags">
                <?= __('Auto Tag') ?>
            </label>
            <?php if ($this->Form->isFieldError('regenerateTags')): ?>
                <div class="invalid-feedback">
                    <?= $this->Form->error('regenerateTags') ?>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    
    <div class="form-check">
        <?php echo $this->Form->checkbox('is_published', [
            'class' => 'form-check-input' . ($this->Form->isFieldError('is_published') ? ' is-invalid' : '')
        ]); ?>
        <label class="form-check-label" for="is-published">
            <?= __('Published') ?>
        </label>
        <?php if ($this->Form->isFieldError('is_published')): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error('is_published') ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($kind == 'article') : ?>
    <div class="form-check">
        <?php echo $this->Form->checkbox('featured', [
            'class' => 'form-check-input' . ($this->Form->isFieldError('featured') ? ' is-invalid' : '')
        ]); ?>
        <label class="form-check-label" for="featured">
            <?= __('Featured') ?>
        </label>
        <?php if ($this->Form->isFieldError('featured')): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error('featured') ?>
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($kind == 'page' && SettingsManager::read('SitePages.mainMenuShow') == 'selected') : ?>
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

</div>
<div class="mb-3">
    <?php $parentId = $this->request->getQuery('parent_id'); ?>
    <?php if ($kind == 'page' || $parentId) : ?>
        <?php echo $this->Form->control('parent_id',
            [
                'empty' => __('None'),
                'options' => $parentArticles,
                'default' => $parentId,
                'class' => 'form-control' . ($this->Form->isFieldError('parent_id') ? ' is-invalid' : '')
            ]); ?>
            <?php if ($this->Form->isFieldError('parent_id')): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error('parent_id') ?>
            </div>
        <?php endif; ?>
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

<?php if (!empty($article->image)): ?>
    <div class="mb-3">
        <?= $this->element('image/icon', ['model' => $article, 'icon' => $article->teenyImageUrl, 'preview' => $article->extraLargeImageUrl]); ?>
    </div>
<?php endif; ?>

<?php if (SettingsManager::read('PagesAndArticles.additionalImages')) : ?>
    <div class="mb-3">
        <label class="form-label" for="customFileMultiple"><?= __('Image Uploads') ?></label>
        <?= $this->Form->file('image_uploads[]', [
            'multiple' => true,
            'class' => 'form-control' . ($this->Form->isFieldError('image_uploads') ? ' is-invalid' : ''),
            'id' => 'customFileMultiple'
        ]) ?>
        <?php if ($this->Form->isFieldError('image_uploads')): ?>
            <div class="invalid-feedback">
                <?= $this->Form->error('image_uploads') ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($article->images)) : ?>
        <div class="mb-3">
        <?= $this->element('image_carousel', [
            'images' => $article->images,
            'carouselId' => $carouselId ?? 'imageCarouselID'
        ]) ?>
        </div>
    <?php endif; ?>
            
<?php endif; ?>

<div class="mb-3">
    <?php echo $this->Form->control('user_id', [
        'default' => $this->Identity->get('id'),
        'options' => $users,
        'class' => 'form-select' . ($this->Form->isFieldError('user_id') ? ' is-invalid' : '')
    ]); ?>
    <?php if ($this->Form->isFieldError('user_id')): ?>
        <div class="invalid-feedback">
            <?= $this->Form->error('user_id') ?>
        </div>
    <?php endif; ?>
</div>