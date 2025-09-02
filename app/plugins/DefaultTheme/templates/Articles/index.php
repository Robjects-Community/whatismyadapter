<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Article> $articles
 * @var array $tags
 * @var string|null $selectedTag
 */
?>
<?php foreach ($articles as $article): ?>
<article class="article-list-item mb-4">
    <a class="text-decoration-none" href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
        <h2 class="article-title h4 link-body-emphasis mb-2"><?= htmlspecialchars_decode($article->title) ?></h2>
    </a>
    
    <div class="article-meta mb-3">
        <span class="date"><?= $article->published->format('F j, Y') ?></span> • 
        <span class="author"><?= h($article->user->username) ?></span>
    </div>
    
    <?php $displayMode = SettingsManager::read('Blog.articleDisplayMode', 'summary') ?>
    <div class="article-wrap-container">
        <?php if (!empty($article->image)): ?>
        <div class="article-image-container">
            <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>">
                <?= $this->element('image/icon', [
                    'model' => $article, 
                    'icon' => $article->extraLargeImageUrl, 
                    'preview' => false,
                    'class' => 'article-wrap-image'
                ]); ?>
            </a>
        </div>
        <?php endif; ?>
        <div class="article-text-wrap">
            <?php if ($displayMode == 'lede') : ?>
                <p><?= htmlspecialchars_decode($article->lede) ?></p>
            <?php elseif ($displayMode == 'summary') : ?>
                <p><?= htmlspecialchars_decode($article->summary) ?></p>
            <?php elseif ($displayMode == 'body') : ?>
                <div><?php
                    $content = $article->body;
                    $content = $this->Video->processVideoPlaceholders($content);
                    $content = $this->Gallery->processGalleryPlaceholders($content);
                    echo htmlspecialchars_decode($content);
                ?></div>
            <?php endif; ?>
            <div class="read-more-container">
                <a href="<?= $this->Url->build(['_name' => $article->kind . '-by-slug', 'slug' => $article->slug]) ?>" class="read-more-link">
                    <?= __('Read more') ?>
                </a>
            </div>
        </div>
    </div>
    
    <hr class="article-separator" />
</article>
<?php endforeach; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
});
</script>