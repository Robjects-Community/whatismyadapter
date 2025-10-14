<?php 
use App\Utility\SettingsManager;
use Cake\Core\Configure;

$session = $this->request->getSession();
?>
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head>
    <script>
    // Check localStorage immediately to prevent sidebar jumping
    (function() {
        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.documentElement.classList.add('sidebar-preload-collapsed');
        }
    })();
    </script>
    <?= $this->Html->script('AdminTheme.color-modes') ?>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>: <?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    
    <?= $this->Html->css([
        'AdminTheme.base',
        'AdminTheme.theme',
        'AdminTheme.admin-layout',
        'AdminTheme.semantic-ui-dropdown',
        'AdminTheme.images-grid',
        'AdminTheme.' . (SettingsManager::read('Editing.editor') == 'trumbowyg' ? 'trumbowyg' : 'markdown'),
    ], ['block' => true]) ?>

    <?= $this->Html->script('AdminTheme.image-preview') ?>
    <?= $this->Html->script('AdminTheme.universal-search') ?>
    <?= $this->Html->script('willow-modal') ?>
    <?= $this->Html->script('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js'); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/components/dropdown.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/components/transition.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/components/label.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/components/icon.min.css">
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.5.0/dist/semantic.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/markdown-it@14.1.0/dist/markdown-it.min.js"></script>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
    <?= $this->element('libraries/highlightjs'); ?>
  </head>
  <body class="bg-body-tertiary admin-layout">

    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>

    <?php if (SettingsManager::read('Editing.editor') == 'trumbowyg') : ?>
    <div id="trumbowyg-icons">
        <?= $this->element('trumbowyg-icons') ?>
    </div>
    <?php endif; ?>

    <!-- Theme Toggle -->
    <div class="dropdown position-fixed bottom-0 end-0 mb-3 me-3 bd-mode-toggle" style="z-index: 1050;">
      <button class="btn btn-bd-primary py-2 dropdown-toggle d-flex align-items-center"
        id="bd-theme"
        type="button"
        aria-expanded="false"
        data-bs-toggle="dropdown"
        aria-label="Toggle theme (auto)">
        <svg class="bi my-1 theme-icon-active" width="1em" height="1em"><use href="#circle-half"></use></svg>
        <span class="visually-hidden" id="bd-theme-text">Toggle theme</span>
      </button>
      <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme-text">
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="light" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#sun-fill"></use></svg>
            Light
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center" data-bs-theme-value="dark" aria-pressed="false">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#moon-stars-fill"></use></svg>
            Dark
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
        <li>
          <button type="button" class="dropdown-item d-flex align-items-center active" data-bs-theme-value="auto" aria-pressed="true">
            <svg class="bi me-2 opacity-50" width="1em" height="1em"><use href="#circle-half"></use></svg>
            Auto
            <svg class="bi ms-auto d-none" width="1em" height="1em"><use href="#check2"></use></svg>
          </button>
        </li>
      </ul>
    </div>

    <!-- Top Header -->
    <header class="navbar navbar-expand-lg navbar-dark bg-dark admin-header">
      <div class="container-fluid">
        <!-- Brand and Toggle -->
        <div class="d-flex align-items-center">
          <button class="btn btn-outline-light me-3 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
            <i class="fas fa-bars"></i>
          </button>
          <button class="btn btn-outline-light me-3 d-none d-lg-block" type="button" id="sidebarToggle" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
          </button>
          <?= $this->Html->image('willow-icon.png', [
              'alt' => __('Willow Logo'),
              'class' => 'navbar-logo me-2',
              'url' => ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'],
              'width' => 30,
              'height' => 30
          ]) ?>
          <a class="navbar-brand" href="<?= $this->Url->build(['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index']) ?>">
            <?= SettingsManager::read('SEO.siteName', __('Willow CMS')) ?>
          </a>
        </div>

        <!-- Header Actions -->
        <div class="d-flex align-items-center ms-auto">

          <!-- User Actions -->
          <?= $this->element('user_actions') ?>
        </div>
      </div>
    </header>

    <!-- Main Layout Container -->
    <div class="admin-container">
      <!-- Sidebar Navigation (Desktop) -->
      <nav class="admin-sidebar bg-light border-end d-none d-lg-block" id="adminSidebarDesktop">
        <div class="sidebar-content">
          <div class="list-group list-group-flush">
            <!-- Dashboard -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted sidebar-text"><?= __('Dashboard') ?></h6>
            </div>
            
            <?= $this->Html->link(
                '<i class="fas fa-tachometer-alt sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Analytics') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'PageViews', 'action' => 'dashboard'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . (($activeCtl == 'PageViews') ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Analytics'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <!-- Content Management -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted sidebar-text"><?= __('Content') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-newspaper sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Posts') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . (($activeCtl == 'Articles' && $activeAct != 'treeIndex' && empty($this->request->getQuery('kind'))) ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Posts'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-alt sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Pages') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'treeIndex'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . (($activeCtl == 'Articles' && $activeAct == 'treeIndex') || (!empty($this->request->getQuery('kind'))) ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Pages'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-tags sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Tags') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => $session->read('Tags.indexAction', 'treeIndex')],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Tags' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Tags'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-images sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Images') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Images', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . (($this->request->getParam('controller') == 'Images') ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Images'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-layer-group sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Image Galleries') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'ImageGalleries', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . (($this->request->getParam('controller') == 'ImageGalleries') ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Image Galleries'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?php if(SettingsManager::read('Comments.pagesEnabled', false) || SettingsManager::read('Comments.articlesEnabled', false)) : ?>
            <?= $this->Html->link(
                '<i class="fas fa-comments sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Comments') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Comments', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Comments' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Comments'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>
            <?php endif; ?>

            <!-- User Management -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted sidebar-text"><?= __('Users') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-users sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Manage Users') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Users' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Manage Users'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <!-- Administration -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted sidebar-text"><?= __('Administration') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-cog sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Settings') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Settings' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Settings'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-envelope sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Email Templates') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'EmailTemplates', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'EmailTemplates' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Email Templates'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-link sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Slugs') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Slugs', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Slugs' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Slugs'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?php if (Configure::read('debug')) : ?>
            <?= $this->Html->link(
                '<i class="fas fa-robot sidebar-icon"></i><span class="sidebar-text ms-2">' . __('AI Prompts') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Aiprompts', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Aiprompts' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('AI Prompts'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-globe sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Internationalisation') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Internationalisations', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Internationalisations' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Internationalisation'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>
            <?php endif; ?>

            <!-- System -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted sidebar-text"><?= __('System') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-trash sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Clear Cache') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'Cache', 'action' => 'clearAll'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'Cache' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Clear Cache'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-ban sidebar-icon"></i><span class="sidebar-text ms-2">' . __('Blocked IPs') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'BlockedIps', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'BlockedIps' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('Blocked IPs'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-text sidebar-icon"></i><span class="sidebar-text ms-2">' . __('System Logs') . '</span>',
                ['prefix' => 'Admin', 'controller' => 'SystemLogs', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0 sidebar-link' . ($activeCtl == 'SystemLogs' ? ' active' : ''),
                    'escape' => false,
                    'title' => __('System Logs'),
                    'data-bs-toggle' => 'tooltip',
                    'data-bs-placement' => 'right'
                ]
            ) ?>
          </div>
        </div>
      </nav>

      <!-- Mobile Sidebar (Offcanvas) -->
      <div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header">
          <h5 class="offcanvas-title" id="adminSidebarLabel"><?= __('Navigation') ?></h5>
          <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
          <!-- Copy of sidebar content for mobile -->
          <div class="list-group list-group-flush">
            <!-- Dashboard -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted"><?= __('Dashboard') ?></h6>
            </div>
            
            <?= $this->Html->link(
                '<i class="fas fa-tachometer-alt me-2"></i>' . __('Analytics'),
                ['prefix' => 'Admin', 'controller' => 'PageViews', 'action' => 'dashboard'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . (($activeCtl == 'PageViews') ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <!-- Content Management -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted"><?= __('Content') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-newspaper me-2"></i>' . __('Posts'),
                ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . (($activeCtl == 'Articles' && $activeAct != 'treeIndex' && empty($this->request->getQuery('kind'))) ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-alt me-2"></i>' . __('Pages'),
                ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'treeIndex'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . (($activeCtl == 'Articles' && $activeAct == 'treeIndex') || (!empty($this->request->getQuery('kind'))) ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-tags me-2"></i>' . __('Tags'),
                ['prefix' => 'Admin', 'controller' => 'Tags', 'action' => $session->read('Tags.indexAction', 'treeIndex')],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Tags' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-images me-2"></i>' . __('Images'),
                ['prefix' => 'Admin', 'controller' => 'Images', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . (($this->request->getParam('controller') == 'Images') ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-layer-group me-2"></i>' . __('Image Galleries'),
                ['prefix' => 'Admin', 'controller' => 'ImageGalleries', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . (($this->request->getParam('controller') == 'ImageGalleries') ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?php if(SettingsManager::read('Comments.pagesEnabled', false) || SettingsManager::read('Comments.articlesEnabled', false)) : ?>
            <?= $this->Html->link(
                '<i class="fas fa-comments me-2"></i>' . __('Comments'),
                ['prefix' => 'Admin', 'controller' => 'Comments', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Comments' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>
            <?php endif; ?>

            <!-- User Management -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted"><?= __('Users') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-users me-2"></i>' . __('Manage Users'),
                ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Users' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <!-- Administration -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted"><?= __('Administration') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-cog me-2"></i>' . __('Settings'),
                ['prefix' => 'Admin', 'controller' => 'Settings', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Settings' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-envelope me-2"></i>' . __('Email Templates'),
                ['prefix' => 'Admin', 'controller' => 'EmailTemplates', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'EmailTemplates' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-link me-2"></i>' . __('Slugs'),
                ['prefix' => 'Admin', 'controller' => 'Slugs', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Slugs' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?php if (Configure::read('debug')) : ?>
            <?= $this->Html->link(
                '<i class="fas fa-robot me-2"></i>' . __('AI Prompts'),
                ['prefix' => 'Admin', 'controller' => 'Aiprompts', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Aiprompts' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-globe me-2"></i>' . __('Internationalisation'),
                ['prefix' => 'Admin', 'controller' => 'Internationalisations', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Internationalisations' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>
            <?php endif; ?>

            <!-- System -->
            <div class="list-group-item list-group-item-action border-0 sidebar-header">
              <h6 class="mb-1 text-muted"><?= __('System') ?></h6>
            </div>

            <?= $this->Html->link(
                '<i class="fas fa-trash me-2"></i>' . __('Clear Cache'),
                ['prefix' => 'Admin', 'controller' => 'Cache', 'action' => 'clearAll'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'Cache' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-ban me-2"></i>' . __('Blocked IPs'),
                ['prefix' => 'Admin', 'controller' => 'BlockedIps', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'BlockedIps' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>

            <?= $this->Html->link(
                '<i class="fas fa-file-text me-2"></i>' . __('System Logs'),
                ['prefix' => 'Admin', 'controller' => 'SystemLogs', 'action' => 'index'],
                [
                    'class' => 'list-group-item list-group-item-action border-0' . ($activeCtl == 'SystemLogs' ? ' active' : ''),
                    'escape' => false
                ]
            ) ?>
          </div>
        </div>
      </div>

      <!-- Main Content Area -->
      <main class="admin-main">
        <div class="admin-content p-3">
          <?= $this->Flash->render() ?>
          <?= $this->fetch('content') ?>
        </div>
      </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <?= $this->Html->script('AdminTheme.admin-layout') ?>
    <?= $this->fetch('scriptBottom') ?>

  </body>
</html>