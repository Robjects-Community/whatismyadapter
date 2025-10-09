<?php use App\Utility\SettingsManager; ?>
<footer class="py-5 text-center text-body-secondary bg-body-tertiary">
    <div class="container">
        <!-- Footer Menu Pages -->
        <?php if (!empty($footerMenuPages)) : ?>
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <h6 class="text-uppercase fw-bold mb-3 text-primary"><?= __('Footer Pages') ?></h6>
                <div class="footer-menu-pages">
                    <nav class="nav justify-content-center flex-wrap">
                        <?php foreach ($footerMenuPages as $footerPage): ?>
                        <div class="nav-item mx-2 mb-2">
                            <?= $this->Html->link(
                                h($footerPage->title),
                                [
                                    '_name' => 'page-by-slug',
                                    'slug' => $footerPage->slug
                                ],
                                [
                                    'class' => 'nav-link text-body-secondary footer-page-link px-2 py-1 rounded',
                                    'style' => 'border: 1px solid rgba(var(--bs-secondary-rgb), 0.3); background: rgba(var(--bs-light-rgb), 0.1);'
                                ]
                            ); ?>
                        </div>
                        <?php endforeach; ?>
                    </nav>
                </div>
                <hr class="my-4 opacity-50">
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Copyright and Standard Footer Links -->
        <p class="mb-3">&copy; <?= date('Y') ?> <?= SettingsManager::read('SEO.siteName', 'Willow CMS') ?>. <?= __(singular: 'All rights reserved.') ?></p>
        
        <!-- Standard Footer Links -->
        <div class="footer-standard-links mb-3">
            <?php if (!empty($sitePrivacyPolicy)) : ?>
                <?= $this->Html->link(
                    __('Privacy Policy'),
                    [
                        '_name' => 'page-by-slug',
                        'slug' => $sitePrivacyPolicy['slug']
                    ],
                    [
                        'class' => 'text-body-secondary text-decoration-none me-3'
                    ]
                ); ?>
            <?php endif; ?>
            <a href="#" class="text-body-secondary text-decoration-none"><?= __('Back to top') ?></a>
        </div>
    </div>
</footer>

<style>
.footer-page-link {
    transition: all 0.2s ease;
    font-size: 0.9rem;
    font-weight: 500;
}

.footer-page-link:hover {
    background: rgba(var(--bs-primary-rgb), 0.1) !important;
    border-color: var(--bs-primary) !important;
    transform: translateY(-1px);
    color: var(--bs-primary) !important;
}

.footer-menu-pages {
    margin-bottom: 1rem;
}

.footer-menu-pages h6 {
    font-size: 0.8rem;
    letter-spacing: 0.1em;
}

@media (max-width: 768px) {
    .footer-page-link {
        font-size: 0.85rem;
        margin: 0.25rem;
    }
}
</style>
