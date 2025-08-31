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
        <a class="nav-item nav-link link-body-emphasis fw-medium px-3" 
           href="https://www.github.com/matthewdeaves/willow">
           GitHub
        </a>
    </nav>
</div>

