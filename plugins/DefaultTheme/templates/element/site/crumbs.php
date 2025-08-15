<?php $currentUrl = $this->request->getPath(); ?>
<?php if (!empty($crumbs) && count($crumbs) > 1) : ?>
<div class="border-bottom mb-3">
    <nav class="breadcrumb">
        <ol class="breadcrumb breadcrumb-chevron">
        <?php foreach ($crumbs as $crumb) : ?>
            <?php $url = $this->Html->Url->build(['_name' => 'page-by-slug', 'slug' => $crumb->slug]); ?>
            <li class="breadcrumb-item">
                <?php
                echo $this->Html->link(
                    $crumb->title,
                    $url,
                    [
                        'class' => 'link-body-emphasis text-decoration-none' . ($currentUrl == $url ? ' fw-semibold' : ''),
                    ],
                );
                ?>
            </li>
        <?php endforeach; ?>
        </ol>
    </nav>
</div>
<?php endif; ?>