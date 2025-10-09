<?php
/**
 * GitHub Repository
 */
$this->assign('title', __('GitHub Repository'));

$this->start('meta');
echo $this->Html->meta('description', __('Explore the Willow CMS GitHub repository: CakePHP 5.x + AI-powered, multi-language CMS.'));
echo $this->Html->meta('keywords', __('GitHub, Willow CMS, CakePHP 5, AI CMS, repository'));
?>
<link rel="canonical" href="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta property="og:title" content="<?= h(__('GitHub Repository')); ?>">
<meta property="og:description" content="<?= h(__('Code, issues, discussions, and releases for Willow CMS.')); ?>">
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($this->Url->build(null, ['fullBase' => true])); ?>">
<meta name="twitter:card" content="summary">
<?php $this->end(); ?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-10">
      <h1 class="mb-3"><?= __('GitHub Repository'); ?></h1>
      <p class="lead"><?= __('Willow CMS is open source. Browse the codebase, open issues, and contribute.'); ?></p>

      <div class="card mb-4">
        <div class="card-body">
          <h2 class="h5 mb-3"><?= __('Quick links'); ?></h2>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-dark" href="https://github.com/matthewdeaves/willow" target="_blank" rel="noopener"><?= __('Repository'); ?></a>
            <a class="btn btn-outline-dark" href="https://github.com/matthewdeaves/willow/issues" target="_blank" rel="noopener"><?= __('Issues'); ?></a>
            <a class="btn btn-outline-dark" href="https://github.com/matthewdeaves/willow/discussions" target="_blank" rel="noopener"><?= __('Discussions'); ?></a>
            <a class="btn btn-outline-dark" href="https://github.com/matthewdeaves/willow/pulls" target="_blank" rel="noopener"><?= __('Pull Requests'); ?></a>
            <a class="btn btn-outline-dark" href="https://github.com/matthewdeaves/willow/releases" target="_blank" rel="noopener"><?= __('Releases'); ?></a>
          </div>
        </div>
      </div>

      <div class="card mb-4">
        <div class="card-body">
          <h2 class="h5 mb-3"><?= __('Clone'); ?></h2>
          <pre class="mb-0"><code>git clone https://github.com/matthewdeaves/willow.git
cd willow
./setup_dev_env.sh
</code></pre>
        </div>
      </div>

      <div class="card">
        <div class="card-body">
          <h2 class="h5 mb-3"><?= __('Highlights'); ?></h2>
          <ul class="mb-0">
            <li><?= __('CakePHP 5.x MVC with plugin-based theming'); ?></li>
            <li><?= __('AI-powered SEO, tagging, summaries, and translations'); ?></li>
            <li><?= __('Multi-language first with locale-aware routing'); ?></li>
            <li><?= __('Queue workers for async jobs and image processing'); ?></li>
            <li><?= __('Docker-based development and management tooling'); ?></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>
