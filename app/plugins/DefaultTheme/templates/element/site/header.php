<?php use App\Utility\SettingsManager; ?>
<header class="border-bottom lh-1 py-3">
  <div class="row flex-nowrap justify-content-between align-items-center">
    <div class="col-6 col-md-4 pt-1">
      <?= $this->element('site/site_language', ['languages' => $siteLanguages, 'selectedSiteLanguage' => $selectedSiteLanguage]) ?>
    </div>
    <div class="d-none d-md-flex col-4 text-center">
      <a class="blog-header-logo text-body-emphasis text-decoration-none mx-auto" href="<?= $this->Url->build(['_name' => 'home']) ?>"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
    </div>
    <div class="col-6 col-md-4 d-flex justify-content-end align-items-center">
        <?= $this->element('site/user_actions') ?>
    </div>
  </div>
  
  <!-- Mobile site name -->
  <div class="d-md-none text-center mt-2">
    <a class="blog-header-logo text-body-emphasis text-decoration-none" href="<?= $this->Url->build(['_name' => 'home']) ?>"><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?></a>
  </div>
</header>