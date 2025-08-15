<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Article $article
 * @var array $articles
 */

// Recursive function to build the nested menu
function buildMenu($articles, $view): void
{
    echo '<ul class="nested-menu">';
    foreach ($articles as $page) {
        echo '<li>';
        echo $view->Html->link(
            h($page->title),
            ['prefix' => false, 'controller' => 'Articles', 'action' => 'view-by-slug', $page->slug],
            ['class' => 'menu-item'],
        );
        if (!empty($page->children)) {
            buildMenu($page->children, $view);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="article-container">
    <?php if ($article) : ?>
    <article>
        <header>
            <h1 class="article-title"><?= htmlspecialchars_decode($article->title) ?></h1>
            <time class="article-date" datetime="<?= $article->modified->format('Y-m-d H:i:s') ?>">
                Last updated: <?= $article->modified->format('F j, Y, g:i a') ?>
            </time>
        </header>

        <div id="article-body-content"><?php
            $content = $article->body;
            $content = $this->Video->processVideoPlaceholders($content);
            $content = $this->Gallery->processGalleryPlaceholders($content);
            echo htmlspecialchars_decode($content);
        ?></div>

    </article>
    <?php endif; ?>
    <?php if ($articles) : ?>
    <nav class="sidebar-menu">
        <h2>Pages Menu</h2>
        <?php buildMenu($articles, $this); ?>
    </nav>
    <?php endif; ?>
</div>