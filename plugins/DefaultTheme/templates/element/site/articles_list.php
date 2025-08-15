<?php if (!empty($articles)) : ?>
<div class="sidebar-section mb-4">
    <h4 class="fst-italic border-bottom pb-2 mb-3"><?= $title ?></h4>
    <div class="sidebar-articles-list">
    <?php foreach ($articles as $article) : ?>
        <article class="sidebar-article-item mb-3">
            <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>" aria-label="<?= h($article->title) ?>">
                <h6 class="sidebar-article-title mb-2 text-body-emphasis"><?= htmlspecialchars_decode($article->title) ?></h6>
            </a>
            
            <div class="sidebar-wrap-container">
                <?php if (!empty($article->image)) : ?>
                <div class="sidebar-image-container">
                    <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
                        <?= $this->element('image/icon', [
                            'model' => $article,
                            'icon' => $article->tinyImageUrl,
                            'preview' => false,
                            'class' => 'sidebar-wrap-image',
                        ]); ?>
                    </a>
                </div>
                <?php endif; ?>
                
                <div class="sidebar-text-wrap">
                    <?php if (!empty($article->lede)) : ?>
                    <p class="sidebar-article-summary mb-1 text-body-secondary small"><?= $this->Text->truncate(strip_tags($article->lede), 80) ?></p>
                    <?php endif; ?>
                    
                    <small class="sidebar-article-meta text-body-tertiary d-block"><?= $article->published->format('M j, Y') ?></small>
                </div>
            </div>
            
            <?php if ($article !== end($articles)) : ?>
            <hr class="sidebar-article-separator my-2" />
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>