<?php
/**
 * Hire Me
 */
$this->assign('title', __('Hire me for the next feature'));

$this->start('meta');
echo $this->Html->meta('description', __('Work with the Willow CMS author on feature development, AI integration, i18n, performance, and DevOps.'));
echo $this->Html->meta('keywords', __('Hire CakePHP developer, Willow CMS, AI integration, i18n, Docker, performance'));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('Hire me for the next feature')); ?>">
<meta property="og:description" content="<?= h(__('Available for professional services: features, integrations, and optimizations for Willow CMS or your CakePHP apps.')); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <h1 class="mb-3"><?= __('Hire me for the next feature'); ?></h1>
      <p class="lead">
        <?= __('I can help you deliver high-quality features and integrations for Willow CMS or your CakePHP 5.x projects.'); ?>
      </p>

      <div class="row g-3">
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h5"><?= __('What I do'); ?></h2>
              <ul class="mb-0">
                <li><?= __('Feature development and roadmap planning'); ?></li>
                <li><?= __('AI-powered SEO, tagging, summarization, and translation workflows'); ?></li>
                <li><?= __('Internationalization (i18n) and localization'); ?></li>
                <li><?= __('Image pipeline, CDN, and performance tuning'); ?></li>
                <li><?= __('Docker-based devops and CI improvements'); ?></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card h-100">
            <div class="card-body">
              <h2 class="h5"><?= __('How to reach me'); ?></h2>
              <ul class="mb-0">
                <li>
                  <a class="link-primary" href="https://github.com/matthewdeaves/willow/issues/new/choose" rel="noopener" target="_blank">
                    <?= __('Open a GitHub issue to start the conversation'); ?>
                  </a>
                </li>
                <li><?= __('Prefer email or another channel? Add details here in production.'); ?></li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <a class="btn btn-dark me-2" href="https://github.com/matthewdeaves/willow" target="_blank" rel="noopener"><?= __('View GitHub Repository'); ?></a>
        <?php
          $localeParam = $this->getRequest()->getParam('_locale') ? ['_locale' => $this->getRequest()->getParam('_locale')] : [];
          echo $this->Html->link(__('About the Author'), ['_name' => 'aboutAuthor'] + $localeParam, ['class' => 'btn btn-outline-secondary']);
        ?>
      </div>
    </div>
  </div>
</div>
