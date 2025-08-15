<?php
/**
 * @var \App\View\AppView $this
 * @var array $locales
 * @var string|null $selectedLocale
 */
?>
<div class="mb-3">
    <div class="btn-group flex-wrap" role="group" aria-label="Locale filters" style="display: flex; flex-wrap: wrap; width: 100%;">
        <?= $this->Html->link(__('All Locales'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary' . (!$selectedLocale ? ' active' : '')]) ?>
        <?php foreach ($locales as $locale) : ?>
            <?= $this->Html->link(
                h($locale),
                ['action' => 'index', '?' => ['locale' => $locale]],
                ['class' => 'btn btn-outline-secondary' . ($selectedLocale === $locale ? ' active' : '')],
            ) ?>
        <?php endforeach; ?>
    </div>
</div>