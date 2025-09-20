<div class="nav-scroller py-1 mb-3 border-bottom">
    <nav class="nav nav-underline justify-content-between">
    <?= $this->Html->link(
    __('All'),
    [
        '_name' => 'home',
    ],
    [
        'class' => 'nav-item nav-link link-body-emphasis' . (!$selectedTagId ? ' active' : '')
    ]) ?>
    <?php foreach ($rootTags as $rootTag) : ?>
        <?= $this->Html->link(
            htmlspecialchars_decode($rootTag->title),
            [
                '_name' => 'home',
                '?' => ['tag' => $rootTag->id]
            ],
            ['class' => 'nav-item nav-link link-body-emphasis' . ($selectedTagId == $rootTag->id ? ' active' : '')]
        ) ?>
    <?php endforeach; ?>
    </nav>
</div>