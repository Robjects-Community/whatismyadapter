<?php
/**
 * Follow Me
 */
$this->assign('title', __('Follow me on social media'));

$this->start('meta');
echo $this->Html->meta('description', __('Follow the Willow CMS author for updates on CakePHP 5.x, AI integrations, and new features.'));
echo $this->Html->meta('keywords', __('Follow, social media, Willow CMS, CakePHP, AI, updates'));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('Follow me on social media')); ?>">
<meta property="og:description" content="<?= h(__('Get the latest updates on Willow CMS and related projects.')); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <h1 class="mb-3"><?= __('Follow me on social media'); ?></h1>
      <p class="lead"><?= __('Connect for updates about Willow CMS, CakePHP 5.x, and AI integrations.'); ?></p>

      <div class="list-group mb-4">
        <?php if (!empty($githubUrl)): ?>
          <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
             href="<?= h($githubUrl); ?>" rel="noopener" target="_blank">
            <span>GitHub</span>
            <span class="badge bg-dark"><?= h(basename($githubUrl)); ?></span>
          </a>
        <?php endif; ?>

        <?php if (!empty($linkedinUrl)): ?>
          <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
             href="<?= h($linkedinUrl); ?>" rel="noopener" target="_blank">
            <span>LinkedIn</span>
            <span class="badge bg-primary"><?= __('Connect'); ?></span>
          </a>
        <?php endif; ?>
        
        <?php if (empty($githubUrl) && empty($linkedinUrl)): ?>
          <div class="list-group-item">
            <p class="mb-0 text-muted"><?= __('Social media links can be configured in the admin settings.'); ?></p>
          </div>
        <?php endif; ?>
      </div>

      <?php
        $localeParam = $this->getRequest()->getParam('_locale') ? ['_locale' => $this->getRequest()->getParam('_locale')] : [];
        echo $this->Html->link(__('About the Author'), ['_name' => 'aboutAuthor'] + $localeParam, ['class' => 'btn btn-outline-secondary me-2']);
        echo $this->Html->link(__('GitHub Repo'), ['_name' => 'githubRepo'] + $localeParam, ['class' => 'btn btn-dark']);
      ?>
    </div>
  </div>
</div>
