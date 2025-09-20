<?php use App\Utility\SettingsManager; ?>
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
  </head>
  <body>
      <?php if (!empty($consentData) && $consentData['marketing_consent']) :?>
        <?= $this->element('site/facebook/sdk') ?>
      <?php endif; ?>

    <?= $this->element('site/bootstrap') ?>

    <div class="container">

    <?= $this->element('site/header'); ?>

    <?= $this->element('site/main_menu', ['mbAmount' => 3]); ?>

    </div>
    <main class="container">
      <div class="row g-5">
        
        <div class="col-md-8">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>

        <div class="col-md-4">
            <div class="position-sticky" style="top: 2rem;">

              <?= $this->element('site/articles_list', ['articles' => $childPages, 'title' => __('Related pages')]) ?>

              <?= $this->element('site/articles_list', ['articles' => $featuredArticles, 'title' => __('Featured posts')]) ?>

              <?= $this->element('site/articles_list', ['articles' => $recentArticles, 'title' => __(singular: 'Recent posts')]) ?>

              <?= $this->element('site/elsewhere') ?>

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