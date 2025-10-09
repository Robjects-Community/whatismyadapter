<?php
/**
 * About the Author
 * Theme: DefaultTheme
 * CakePHP 5.x
 */
use Cake\I18n\I18n;

$this->assign('title', __('About the Author'));

$this->start('meta');
// Meta description/keywords + canonical
echo $this->Html->meta('description', __('Willow CMS author Matthew Deaves builds a modern CakePHP 5.x + AI-powered, multi-language CMS.'));
echo $this->Html->meta('keywords', __('Willow CMS, CakePHP 5, AI CMS, multilingual, Docker, Matthew Deaves, content management'));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('About the Author')); ?>">
<meta property="og:description" content="<?= h(__('Meet the author of Willow CMS, a modern CakePHP 5.x + AI platform.')); ?>">
<meta property="og:type" content="article">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-9">
      <h1 class="mb-3"><?= __('About the Author'); ?></h1>
      <p class="lead">
        <?= __('Hi, I\'m {0}, the author of Willow CMS.', 'matthewdeaves'); ?>
      </p>
      <p>
        <?= __('Willow CMS is a modern CMS built with CakePHP 5.x and AI integration. It offers AI-powered content management, multi-language support, and a Docker-based development environment.'); ?>
      </p>
      <ul class="list-unstyled mb-4">
        <li>✅ <?= __('AI-generated SEO content, tags, summaries, and translations'); ?></li>
        <li>✅ <?= __('Multi-language first with locale-aware routing'); ?></li>
        <li>✅ <?= __('Queue workers for image processing and translations'); ?></li>
        <li>✅ <?= __('Redis caching and scalable architecture'); ?></li>
      </ul>

      <div class="d-flex flex-wrap gap-2">
        <?php
          $localeParam = $this->getRequest()->getParam('_locale') ? ['_locale' => $this->getRequest()->getParam('_locale')] : [];
          echo $this->Html->link(__('Hire Me'), ['_name' => 'hireMe'] + $localeParam, ['class' => 'btn btn-primary']);
          echo $this->Html->link(__('Follow Me'), ['_name' => 'followMe'] + $localeParam, ['class' => 'btn btn-outline-secondary']);
          echo $this->Html->link(__('GitHub Repo'), ['_name' => 'githubRepo'] + $localeParam, ['class' => 'btn btn-dark']);
        ?>
      </div>
    </div>
  </div>
</div>
