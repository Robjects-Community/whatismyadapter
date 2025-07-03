<?php use App\Utility\SettingsManager; ?>
<?php use Cake\Routing\Router; ?>
<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
  <head>
  <?php if (!empty($consentData) && $consentData['analytics_consent']) :?>
    <?= SettingsManager::read('Google.tagManagerHead', '') ?>
    <?php endif; ?>
    <?= $this->Html->script('willow-modal') ?>
    <?= $this->Html->script('DefaultTheme.color-modes') ?>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= $this->element('site/meta_tags', ['model' => $article ?? $tag ?? null]) ?>
    <title><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>: <?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <?= $this->Html->css('DefaultTheme.willow') ?>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <link href="https://fonts.googleapis.com/css?family=Playfair&#43;Display:700,900&amp;display=swap" rel="stylesheet">
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
    <?= $this->Html->meta([
        'link' => Router::url([
            '_name' => 'rss'
        ], true),
        'type' => 'application/rss+xml',
        'title' => __('Latest Articles RSS Feed'),
        'rel' => 'alternate'
    ]); ?>
</head>
  <body>
      <?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
      <?= $this->element('site/facebook/sdk') ?>
      <?php endif; ?>

    <?= $this->element('site/bootstrap') ?>

    <div class="container">

      <?= $this->element('site/header'); ?>

      <?= $this->element('site/main_menu'); ?>

      <?= $this->element('site/tags'); ?>

    </div>

    <main class="container" id="main-content">
      <div class="row g-5">
        
        <div class="col-lg-8">
            <div role="main" aria-label="<?= __('Article list') ?>">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
                <?= $this->element('pagination', ['recordCount' => count($articles)]) ?>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="sidebar-content">
                <div class="d-none d-lg-block position-sticky" style="top: 2rem;">

            <div class="p-4 mb-3 bg-body-tertiary rounded">
              <h4 class="fst-italic"><?= __('About') ?></h4>
              <p class="mb-0"><?= __("Welcome to willowcms.app. This site uses Willow - a content management system I'm building in the open. Here you'll find development updates, feature highlights, and guides on using Willow for your own sites.") ?></p>
            </div>

                <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>

                <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>

                <?= $this->element('site/archives') ?>

                <?= $this->element('site/elsewhere') ?>

                <?= $this->element('site/feeds') ?>

                </div>
                
                <!-- Mobile sidebar (visible on smaller screens) -->
                <div class="d-lg-none mt-4">
                    <div class="p-4 mb-3 bg-body-tertiary rounded">
                        <h4 class="fst-italic"><?= __('About') ?></h4>
                        <p class="mb-0"><?= __("Welcome to willowcms.app. This site uses Willow - a content management system I'm building in the open. Here you'll find development updates, feature highlights, and guides on using Willow for your own sites.") ?></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>
                            <?= $this->element('site/archives') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __('Recent posts')]) ?>
                            <?= $this->element('site/elsewhere') ?>
                        </div>
                    </div>
                    <?= $this->element('site/feeds') ?>
                </div>
            </div>
        </div>
      </div>

    </main>

    <?= $this->element('site/footer'); ?>

    <?= $this->element('site/cookie_prefs'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <?= $this->Html->script('youtube-gdpr') ?>
    <?= $this->fetch('scriptBottom') ?>
  </body>
</html>