<?php
/**
 * AdminTheme Default Layout
 * 
 * Main layout for admin area
 */
use App\Utility\SettingsManager;
?>
<!doctype html>
<html lang="<?= $this->request->getParam('lang', 'en') ?>" data-bs-theme="auto">
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?> - Admin: <?= $this->fetch('title') ?></title>
    <?= $this->Html->meta('icon') ?>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
    
    <?= $this->Html->scriptBlock(sprintf(
        'var csrfToken = %s;',
        json_encode($this->request->getAttribute('csrfToken'))
    )); ?>
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .admin-header {
            background: #343a40;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        .admin-sidebar {
            background: #fff;
            border-right: 1px solid #dee2e6;
            min-height: calc(100vh - 56px);
            padding: 1rem;
        }
        .admin-content {
            padding: 2rem;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <?= $this->Html->link(
                        SettingsManager::read('SEO.siteName', 'Willow CMS') . ' Admin',
                        ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin'],
                        ['class' => 'navbar-brand text-white text-decoration-none']
                    ) ?>
                </div>
                <nav>
                    <ul class="nav">
                        <li class="nav-item">
                            <?= $this->Html->link(
                                '<i class="fas fa-home"></i> Dashboard',
                                ['controller' => 'Dashboard', 'action' => 'index', 'prefix' => 'Admin'],
                                ['class' => 'nav-link text-white', 'escape' => false]
                            ) ?>
                        </li>
                        <li class="nav-item">
                            <?= $this->Html->link(
                                '<i class="fas fa-cog"></i> Settings',
                                ['controller' => 'Settings', 'action' => 'index', 'prefix' => 'Admin'],
                                ['class' => 'nav-link text-white', 'escape' => false]
                            ) ?>
                        </li>
                        <li class="nav-item">
                            <?= $this->Html->link(
                                '<i class="fas fa-eye"></i> View Site',
                                ['controller' => 'Home', 'action' => 'index', 'prefix' => false],
                                ['class' => 'nav-link text-white', 'escape' => false, 'target' => '_blank']
                            ) ?>
                        </li>
                        <?php if ($this->Identity->isLoggedIn()): ?>
                        <li class="nav-item">
                            <?= $this->Html->link(
                                '<i class="fas fa-sign-out-alt"></i> Logout',
                                ['controller' => 'Users', 'action' => 'logout', 'prefix' => false],
                                ['class' => 'nav-link text-white', 'escape' => false]
                            ) ?>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container-fluid">
        <div class="row">
            <!-- Content Area -->
            <main class="col-md-12 admin-content">
                <?= $this->Flash->render() ?>
                <?= $this->fetch('content') ?>
            </main>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-3 bg-light border-top">
        <div class="container-fluid text-center text-muted">
            <small>&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. All rights reserved.</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <?= $this->fetch('scriptBottom') ?>
</body>
</html>
