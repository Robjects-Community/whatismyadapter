<?php
/**
 * About the Author
 * Theme: DefaultTheme
 * CakePHP 5.x
 * 
 * Variables available from controller:
 * - $authorName: Author's full name
 * - $authorBio: Author's biography
 * - $authorEmail: Author's email
 * - $githubUrl: GitHub profile URL
 * - $linkedinUrl: LinkedIn profile URL
 * - $aboutPageContent: Additional content for the about page
 */
use Cake\I18n\I18n;

$this->assign('title', __('About the Author'));

$this->start('meta');
// Meta description/keywords + canonical
$metaDescription = !empty($authorBio) ? h(strip_tags($authorBio)) : __('Meet the author of Willow CMS, a modern CakePHP 5.x + AI platform.');
echo $this->Html->meta('description', $metaDescription);
echo $this->Html->meta('keywords', __('Willow CMS, CakePHP 5, AI CMS, multilingual, Docker, {0}, content management', $authorName));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('About the Author')); ?>">
<meta property="og:description" content="<?= h($metaDescription); ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <h1 class="mb-3"><?= __('About the Author'); ?></h1>
      
      <?php if (!empty($authorName)): ?>
        <p class="lead">
          <?= __('Hi, I\'m {0}, the author of Willow CMS.', h($authorName)); ?>
        </p>
      <?php endif; ?>
      
      <?php if (!empty($authorBio)): ?>
        <div class="mb-4">
          <?= $authorBio; // Allow HTML in bio ?>
        </div>
      <?php else: ?>
        <p>
          <?= __('Willow CMS is a modern CMS built with CakePHP 5.x and AI integration. It offers AI-powered content management, multi-language support, and a Docker-based development environment.'); ?>
        </p>
        <ul class="list-unstyled mb-4">
          <li>✅ <?= __('AI-generated SEO content, tags, summaries, and translations'); ?></li>
          <li>✅ <?= __('Multi-language first with locale-aware routing'); ?></li>
          <li>✅ <?= __('Queue workers for image processing and translations'); ?></li>
          <li>✅ <?= __('Redis caching and scalable architecture'); ?></li>
        </ul>
      <?php endif; ?>
      
      <?php if (!empty($aboutPageContent)): ?>
        <div class="mb-4">
          <?= $aboutPageContent; // Allow HTML in additional content ?>
        </div>
      <?php endif; ?>

      <div class="d-flex flex-wrap gap-2">
        <?php
          $localeParam = $this->getRequest()->getParam('_locale') ? ['_locale' => $this->getRequest()->getParam('_locale')] : [];
          echo $this->Html->link(__('Hire Me'), ['_name' => 'hireMe'] + $localeParam, ['class' => 'btn btn-primary']);
          echo $this->Html->link(__('Follow Me'), ['_name' => 'followMe'] + $localeParam, ['class' => 'btn btn-outline-secondary']);
          if (!empty($githubUrl)) {
            echo $this->Html->link(__('GitHub Repo'), ['_name' => 'githubRepo'] + $localeParam, ['class' => 'btn btn-dark']);
          }
        ?>
      </div>
      
      <?php if (!empty($authorEmail)): ?>
        <div class="mt-4">
          <p><strong><?= __('Contact:'); ?></strong> 
            <a href="mailto:<?= h($authorEmail); ?>"> <?= h($authorEmail); ?></a>
          </p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
