<?php
/**
 * Hire Me
 * Theme: DefaultTheme
 * CakePHP 5.x
 * 
 * Variables available from controller:
 * - $authorName: Author's full name
 * - $authorEmail: Author's email
 * - $hireMeContent: Content for the hire me page
 * - $githubUrl: GitHub profile URL
 * - $linkedinUrl: LinkedIn profile URL
 */
$this->assign('title', __('Hire Me'));

$this->start('meta');
$metaDescription = !empty($hireMeContent) ? h(strip_tags($hireMeContent)) : __('Work with the Willow CMS author on feature development, AI integration, i18n, performance, and DevOps.');
echo $this->Html->meta('description', $metaDescription);
echo $this->Html->meta('keywords', __('Hire CakePHP developer, Willow CMS, AI integration, i18n, Docker, performance'));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('Hire Me')); ?>">
<meta property="og:description" content="<?= h($metaDescription); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <h1 class="mb-3"><?= __('Hire Me'); ?></h1>
      
      <?php if (!empty($authorName)): ?>
        <p class="lead">
          <?= __('I can help you deliver high-quality features and integrations for Willow CMS or your CakePHP 5.x projects.'); ?>
        </p>
      <?php endif; ?>
      
      <?php if (!empty($hireMeContent)): ?>
        <div class="mb-4">
          <?= $hireMeContent; // Allow HTML in hire me content ?>
        </div>
      <?php else: ?>
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
                  <?php if (!empty($githubUrl)): ?>
                    <li>
                      <a class="link-primary" href="<?= h($githubUrl); ?>/issues/new/choose" rel="noopener" target="_blank">
                        <?= __('Open a GitHub issue to start the conversation'); ?>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (!empty($authorEmail)): ?>
                    <li>
                      <a class="link-primary" href="mailto:<?= h($authorEmail); ?>">
                        <?= __('Email me directly at {0}', h($authorEmail)); ?>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (!empty($linkedinUrl)): ?>
                    <li>
                      <a class="link-primary" href="<?= h($linkedinUrl); ?>" rel="noopener" target="_blank">
                        <?= __('Connect on LinkedIn'); ?>
                      </a>
                    </li>
                  <?php endif; ?>
                  <?php if (empty($authorEmail) && empty($linkedinUrl) && empty($githubUrl)): ?>
                    <li><?= __('Contact details can be configured in the admin settings.'); ?></li>
                  <?php endif; ?>
                </ul>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <div class="mt-4">
        <?php if (!empty($githubUrl)): ?>
          <a class="btn btn-dark me-2" href="<?= h($githubUrl); ?>" target="_blank" rel="noopener"><?= __('View GitHub Repository'); ?></a>
        <?php endif; ?>
        <?php
          $localeParam = $this->getRequest()->getParam('_locale') ? ['_locale' => $this->getRequest()->getParam('_locale')] : [];
          echo $this->Html->link(__('About the Author'), ['_name' => 'aboutAuthor'] + $localeParam, ['class' => 'btn btn-outline-secondary']);
        ?>
      </div>
    </div>
  </div>
</div>
