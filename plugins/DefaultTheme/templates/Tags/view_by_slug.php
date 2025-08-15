<?php ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Tag $tag
 */
?>
<div class="tag-articles">
    <h2 class="mb-4 text-primary"><?= htmlspecialchars_decode($tag->title) ?></h2>
    <div class="card mb-4">
        <div class="card-body">
            <p class="card-text"><?= htmlspecialchars_decode($tag->description) ?></p>
        </div>
    </div>
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="card-title h5 mb-0"><?= __('Articles/Pages') ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($tag->articles)) : ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($tag->articles as $article) : ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                            <?php if (!empty($article->image)) : ?>
                            <div class="me-2">
                                <?= $this->element('image/icon', ['model' => $article, 'icon' => $article->smallImageUrl, 'preview' => $article->largeImageUrl]); ?>
                            </div>
                            <?php endif; ?>
                                <div class="d-flex flex-column">
                                    <h4 class="h6 mb-0">
                                        <?= $this->Html->link(
                                            htmlspecialchars_decode($article->title),
                                            [
                                                '_name' => 'article-by-slug',
                                                'slug' => $article->slug,
                                            ],
                                            ['class' => 'text-primary'],
                                        ); ?>
                                    </h4>
                                    <small class="text-muted"><?= __('By') ?> <?= h($article->user->username) ?></small>
                                </div>
                                <small class="text-muted ms-auto"><?= h($article->created->format('F j, Y, g:i a')) ?></small>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p class="card-text"><?= __('No articles found for this tag.') ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl, {
            container: 'body'
        })
    })
});
</script>