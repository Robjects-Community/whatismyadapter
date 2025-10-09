<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 * @var array $products
 */

// Recursive function to build the nested menu
function buildMenu($products, $view) {
    echo '<ul class="nested-menu">';
    foreach ($products as $page) {
        echo '<li>';
        echo $view->Html->link(
            h($page->title),
            ['prefix' => false, 'controller' => 'Products', 'action' => 'view-by-slug', $page->slug],
            ['class' => 'menu-item']
        );
        if (!empty($page->children)) {
            buildMenu($page->children, $view);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="product-container">
    <?php if ($product) : ?>
    <product>
        <header>
            <h1 class="product-title"><?= htmlspecialchars_decode($product->title) ?></h1>
            <time class="product-date" datetime="<?= $product->modified->format('Y-m-d H:i:s') ?>">
                Last updated: <?= $product->modified->format('F j, Y, g:i a') ?>
            </time>
        </header>

        <div id="product-body-content"><?php
            $content = $product->body;
            $content = $this->Video->processVideoPlaceholders($content);
            $content = $this->Gallery->processGalleryPlaceholders($content);
            echo htmlspecialchars_decode($content);
        ?></div>

    </product>
    <?php endif; ?>
    <?php if ($products) : ?>
    <nav class="sidebar-menu">
        <h2>Pages Menu</h2>
        <?php buildMenu($products, $this); ?>
    </nav>
    <?php endif; ?>
</div>