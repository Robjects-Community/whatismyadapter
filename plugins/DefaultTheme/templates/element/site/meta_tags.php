<?php use App\Utility\SettingsManager; ?>
<?php if (isset($model)) : ?>
    <title><?= h($model->meta_title ?: $model->title) ?></title>
    <meta name="description" content="<?= h($model->meta_description ?: substr(strip_tags($model->description ?? $model->body ?? ''), 0, 160)) ?>">
    <meta name="keywords" content="<?= h($model->meta_keywords) ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?= $this->Url->build('/' . $model->slug, ['fullBase' => true]) ?>">
    <meta property="og:title" content="<?= h($model->meta_title ?: $model->title) ?>">
    <meta property="og:description" content="<?= h($model->facebook_description ?: $model->meta_description) ?>">
    <meta property="og:image" content="<?= !empty($model->image_url) ? $this->Url->build($model->image_url, ['fullBase' => true]) : '' ?>">
    <meta property="article:published_time" content="<?= isset($model->published) ? $model->published->format('c') : $model->modified->format('c') ?>">
    <meta property="article:modified_time" content="<?= $model->modified->format('c') ?>">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?= $this->Url->build('/' . $model->slug, ['fullBase' => true]) ?>">
    <meta name="twitter:title" content="<?= h($model->meta_title ?: $model->title) ?>">
    <meta name="twitter:description" content="<?= h($model->twitter_description ?: $model->meta_description) ?>">
    
    <!-- LinkedIn -->
    <meta name="linkedin:title" content="<?= h($model->meta_title ?: $model->title) ?>">
    <meta name="linkedin:description" content="<?= h($model->linkedin_description ?: $model->meta_description) ?>">
    
    <!-- Instagram -->
    <meta name="instagram:title" content="<?= h($model->meta_title ?: $model->title) ?>">
    <meta name="instagram:description" content="<?= h($model->instagram_description ?: $model->meta_description) ?>">
<?php else : ?>
    <title><?= SettingsManager::read('SEO.siteStrapline') ?></title>
    <meta name="description" content="<?= SettingsManager::read('SEO.siteMetaDescription', 'Meta Description') ?>">
    <meta name="keywords" content="<?= SettingsManager::read('SEO.siteMetakeywords', 'Meta Keywords') ?>">
<?php endif; ?>