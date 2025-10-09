<?php use App\Utility\SettingsManager; ?>
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Product $product
 */
?>

<product class="blog-post">
    <header class="product-header mb-4">
        <h1 class="display-5 link-body-emphasis mb-3"><?= htmlspecialchars_decode($product->title) ?></h1>
        <div class="blog-post-meta">
            <span class="date"><?= $product->published->format('F j, Y') ?></span>
            <span class="author">by <?= h($product->user->username) ?></span>
        </div>
    </header>
    
    <?php if (!empty($product->image)): ?>
    <div class="product-featured-image mb-4">
        <?= $this->element('image/icon', [
            'model' => $product, 
            'icon' => $product->largeImageUrl, 
            'preview' => false,
            'class' => 'img-fluid rounded shadow-sm w-100'
        ]); ?>
    </div>
    <?php endif; ?>
    
    <?= $this->Html->css('https://cdn.jsdelivr.net/npm/trumbowyg@2.28.0/dist/ui/trumbowyg.min.css'); ?>
    
    <div class="product-content-wrapper">
        <div id="product-body-content" class="product-body trumbowyg-editor-visible trumbowyg-semantic"><?php
            $content = $product->body;
            // Process videos first
            $content = $this->Video->processVideoPlaceholders($content);
            // Process galleries second
            $content = $this->Gallery->processGalleryPlaceholders($content);
            // Enhance content formatting (alignment, responsive images)
            $content = $this->Content->enhanceContent($content, ['processResponsiveImages' => true]);
            echo htmlspecialchars_decode($content);
        ?></div>
    </div>
    
    <?= $this->element('site/facebook/share_button') ?>
    <div class="mb-3">
        <?= $this->element('image_carousel', [
            'images' => $product->images,
            'carouselId' => 'productImagesCarousel'
        ]) ?>
    </div>

    <div>
        <?= $this->element('product/tags', ['product' => $product]) ?>
    </div>
</product>

<?php if(
        (SettingsManager::read('Comments.productsEnabled') && $product->kind == 'product')
        || (SettingsManager::read('Comments.pagesEnabled') && $product->kind == 'page')
    ):
?>
    <section class="comments mb-8">
        <h3 class="mb-3"><?= __('Comments') ?></h3>
        <?php if (!empty($product->comments)): ?>
            <?php foreach ($product->comments as $comment): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="card-text"><?= htmlspecialchars_decode($comment->content) ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <?= __('Posted on {date} by {author}', [
                                    'date' => $comment->created->format('F j, Y, g:i a'),
                                    'author' => h($comment->user->username ?? 'Unknown')
                                ]) ?>
                            </small>
                        </p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted"><?= __('No comments yet.') ?></p>
        <?php endif; ?>           
    </section>
    <?php if ($this->Identity->isLoggedIn()): ?>
        <section class="add-comment mb-4">
            <h3 class="mb-3"><?= __('Add a Comment') ?></h3>
            <?= $this->Form->create(null, ['url' => ['controller' => 'Products', 'action' => 'addComment', $product->id]]) ?>
                <?= $this->Form->control('content', ['label' => __('Comment'), 'type' => 'textarea', 'rows' => 3, 'class' => 'form-control mb-3']) ?>
                <?= $this->Form->button(__('Submit Comment'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </section>
    <?php else: ?>
        <section class="login-prompt mb-4">
            <h3 class="mb-3"><?= __('Please log in to add a comment') ?></h3>
            <?= $this->Html->link(__('Login'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-primary']) ?>
        </section>
    <?php endif; ?>
<?php endif; ?>