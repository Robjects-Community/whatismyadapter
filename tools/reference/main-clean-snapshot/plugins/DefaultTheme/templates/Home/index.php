<?php
/**
 * Enhanced Home Page with Multiple Feeds
 * 
 * @var \App\View\AppView $this
 * @var \Cake\ORM\Query $featuredArticles
 * @var \Cake\ORM\Query $latestArticles
 * @var \Cake\ORM\Query|null $latestProducts
 * @var \Cake\ORM\Query $popularTags
 * @var array $developmentInfo
 * @var array $socialLinks
 */
?>

<div class="home-page-container">
    <!-- Hero Section -->
    <section class="hero-section mb-5">
        <div class="jumbotron p-5 rounded-lg theme-hero-section">
            <h1 class="display-4">Welcome to WhatIsMyAdapter</h1>
            <p class="lead">Discover adapters, explore articles, and learn about technology</p>
            <div class="hero-actions mt-4">
                <?= $this->Html->link('Browse Articles', ['controller' => 'Articles', 'action' => 'index'], ['class' => 'btn btn-bd-primary btn-lg me-2']) ?>
                <?= $this->Html->link('View Products', ['controller' => 'Products', 'action' => 'index'], ['class' => 'btn btn-outline-secondary btn-lg']) ?>
            </div>
        </div>
    </section>

    <div class="row">
        <!-- Main Content Area -->
        <div class="col-lg-8">
            <!-- Featured Articles Feed -->
            <?php if ($featuredArticles && count($featuredArticles) > 0): ?>
            <section class="featured-articles-feed mb-5">
                <h2 class="feed-title mb-4">
                    <i class="bi bi-star-fill text-warning"></i> Featured Articles
                </h2>
                <div class="row">
                    <?php foreach ($featuredArticles as $article): ?>
                    <div class="col-md-12 mb-4">
                        <div class="card featured-article-card h-100 shadow-sm">
                            <div class="row g-0">
                                <?php if (!empty($article->image)): ?>
                                <div class="col-md-4">
                                    <a href="<?= $this->Url->build(['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $article->slug]) ?>">
                                        <?= $this->element('image/icon', [
                                            'model' => $article,
                                            'icon' => $article->extraLargeImageUrl,
                                            'preview' => false,
                                            'class' => 'img-fluid rounded-start h-100 object-fit-cover'
                                        ]) ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                                <div class="<?= !empty($article->image) ? 'col-md-8' : 'col-md-12' ?>">
                                    <div class="card-body">
                                        <h3 class="card-title h4">
                                            <a href="<?= $this->Url->build(['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $article->slug]) ?>" class="text-decoration-none">
                                                <?= h($article->title) ?>
                                            </a>
                                        </h3>
                                        <p class="card-text text-muted small">
                                            <span class="me-2"><i class="bi bi-calendar"></i> <?= $article->published ? $article->published->format('M d, Y') : $article->created->format('M d, Y') ?></span>
                                            <span class="me-2"><i class="bi bi-person"></i> <?= h($article->user->username) ?></span>
                                        </p>
                                        <p class="card-text"><?= h($article->summary) ?></p>
                                        <div class="article-tags">
                                            <?php foreach ($article->tags as $tag): ?>
                                            <span class="badge bg-secondary me-1"><?= h($tag->title) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Latest Articles Feed -->
            <?php if ($latestArticles && count($latestArticles) > 0): ?>
            <section class="latest-articles-feed mb-5">
                <h2 class="feed-title mb-4">
                    <i class="bi bi-newspaper"></i> Latest Articles
                </h2>
                <div class="row">
                    <?php foreach ($latestArticles as $article): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card article-card h-100">
                            <?php if (!empty($article->image)): ?>
                            <a href="<?= $this->Url->build(['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $article->slug]) ?>">
                                <?= $this->element('image/icon', [
                                    'model' => $article,
                                    'icon' => $article->largeImageUrl,
                                    'preview' => false,
                                    'class' => 'card-img-top'
                                ]) ?>
                            </a>
                            <?php endif; ?>
                            <div class="card-body">
                                <h4 class="card-title h5">
                                    <a href="<?= $this->Url->build(['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => $article->slug]) ?>" class="text-decoration-none">
                                        <?= h($article->title) ?>
                                    </a>
                                </h4>
                                <p class="card-text small text-muted">
                                    <?= $article->published ? $article->published->format('M d, Y') : $article->created->format('M d, Y') ?>
                                </p>
                                <p class="card-text"><?= $this->Text->truncate(h($article->summary), 150) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-3">
                    <?= $this->Html->link('View All Articles →', ['controller' => 'Articles', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Products Feed -->
            <?php if ($latestProducts && count($latestProducts) > 0): ?>
            <section class="products-feed mb-5">
                <h2 class="feed-title mb-4">
                    <i class="bi bi-box-seam"></i> Latest Products
                </h2>
                <div class="row">
                    <?php foreach ($latestProducts as $product): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="card product-card h-100">
                            <?php if (!empty($product->image)): ?>
                            <a href="<?= $this->Url->build(['controller' => 'Products', 'action' => 'view', $product->id]) ?>">
                                <?= $this->element('image/icon', [
                                    'model' => $product,
                                    'icon' => $product->mediumImageUrl,
                                    'preview' => false,
                                    'class' => 'card-img-top'
                                ]) ?>
                            </a>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= h($product->name) ?></h5>
                                <p class="card-text small"><?= $this->Text->truncate(h($product->description), 100) ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Social Links Section -->
            <section class="social-links-widget mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="h5 mb-0"><i class="bi bi-people"></i> Connect</h3>
                    </div>
                    <div class="list-group list-group-flush">
                        <?= $this->Html->link(
                            '<i class="bi bi-person-badge me-2"></i> About the Author',
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => 'about-author'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="bi bi-github me-2"></i> GitHub Repository',
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => 'github'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="bi bi-briefcase me-2"></i> Hire Me',
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => 'hire-me'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                        <?= $this->Html->link(
                            '<i class="bi bi-heart me-2"></i> Follow Me',
                            ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => 'follow-me'],
                            ['class' => 'list-group-item list-group-item-action', 'escape' => false]
                        ) ?>
                    </div>
                </div>
            </section>

            <!-- Popular Tags Feed -->
            <?php if ($popularTags && count($popularTags) > 0): ?>
            <section class="tags-widget mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0"><i class="bi bi-tags"></i> Popular Tags</h3>
                    </div>
                    <div class="card-body">
                        <div class="tag-cloud">
                            <?php foreach ($popularTags as $tag): ?>
                            <?= $this->Html->link(
                                h($tag->title),
                                ['controller' => 'Articles', 'action' => 'index', '?' => ['tag' => $tag->slug]],
                                ['class' => 'badge bg-secondary me-1 mb-1 text-decoration-none']
                            ) ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <!-- Development Info Feed -->
            <section class="development-info-widget mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0"><i class="bi bi-code-slash"></i> Development Stack</h3>
                    </div>
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-muted">Server Costs</h6>
                        <ul class="list-unstyled small">
                            <li><strong>Hosting:</strong> <?= h($developmentInfo['server_cost']['hosting']) ?></li>
                            <li><strong>Monthly:</strong> <?= h($developmentInfo['server_cost']['monthly_cost']) ?></li>
                        </ul>
                        
                        <h6 class="card-subtitle mb-2 text-muted mt-3">Tech Stack</h6>
                        <ul class="list-unstyled small">
                            <?php foreach ($developmentInfo['tech_stack'] as $key => $value): ?>
                            <li><strong><?= h(ucfirst(str_replace('_', ' ', $key))) ?>:</strong> <?= h($value) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="mt-3">
                            <?= $this->Html->link('Learn More →', ['controller' => 'Articles', 'action' => 'view-by-slug', 'slug' => 'github'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Search Widget (Future Implementation) -->
            <section class="search-widget mb-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="h5 mb-0"><i class="bi bi-search"></i> Search</h3>
                    </div>
                    <div class="card-body">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Search articles..." disabled>
                            <button class="btn btn-outline-secondary" type="button" disabled>
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <small class="text-muted mt-2 d-block">Coming soon: Enhanced search functionality</small>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Custom CSS for Home Page -->
<style>
.hero-section {
    margin-top: -1rem;
}

.theme-hero-section {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

[data-bs-theme="dark"] .theme-hero-section {
    background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
    border: 1px solid #4a5568;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.hero-actions .btn {
    transition: all 0.3s ease;
    font-weight: 600;
    border-radius: 0.5rem;
    padding: 0.75rem 2rem;
}

.hero-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.2);
}

.featured-article-card {
    transition: transform 0.2s;
}

.featured-article-card:hover {
    transform: translateY(-5px);
}

.article-card, .product-card {
    transition: box-shadow 0.2s;
}

.article-card:hover, .product-card:hover {
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}

.feed-title {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.5rem;
}

.tag-cloud .badge {
    font-size: 0.875rem;
    font-weight: normal;
}

.tag-cloud .badge:hover {
    background-color: #495057!important;
}

.object-fit-cover {
    object-fit: cover;
}
</style>