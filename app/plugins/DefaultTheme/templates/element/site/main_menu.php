<?php $mbAmount = $mbAmount ?? 0; ?>
<?php $currentUrl = $this->request->getPath(); ?>

<div class="nav-scroller py-1 mb-<?= $mbAmount ?> border-bottom">
    <nav class="nav nav-underline justify-content-center" role="navigation" aria-label="<?= __('Main navigation') ?>">

        <?php $url = $this->Html->Url->build(['_name' => 'home']); ?>
        <?= $this->Html->link(__('Blog'), $url, [
            'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . (($currentUrl == $url) ? ' active' : ''),
            'aria-current' => ($currentUrl == $url) ? 'page' : false
        ]) ?>

        <?php foreach ($menuPages as $menuPage) : ?>
            <?php $url = $this->Html->Url->build(['_name' => 'page-by-slug', 'slug' => $menuPage['slug']]); ?>
            <?=
                $this->Html->link(
                    htmlspecialchars_decode($menuPage['title']),
                    $url,
                    [
                        'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . (($currentUrl == $url) ? ' active' : ''),
                        'escape' => false,
                        'aria-current' => ($currentUrl == $url) ? 'page' : false
                    ]
                );
            ?>
        <?php endforeach ?>
        
        <!-- Author Pages -->
        <?php 
        // Get GitHub URL from settings
        $githubUrl = \App\Utility\SettingsManager::read('Author.githubUrl', '');
        $authorName = \App\Utility\SettingsManager::read('Author.fullName', '');
        ?>
        
        <?php $url = $this->Html->Url->build(['_name' => 'aboutAuthor']); ?>
        <?= $this->Html->link(__('About'), $url, [
            'class' => 'nav-item nav-link link-body-emphasis fw-medium px-3' . (($currentUrl == $url) ? ' active' : ''),
            'aria-current' => ($currentUrl == $url) ? 'page' : false
        ]) ?>
        
        <?php if (!empty($githubUrl)): ?>
        <a class="nav-item nav-link link-body-emphasis fw-medium px-3" 
           href="<?= h($githubUrl); ?>" target="_blank" rel="noopener">
           <?= __('GitHub'); ?>
        </a>
        <?php endif; ?>
    </nav>
</div>

