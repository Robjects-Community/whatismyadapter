<?php
/**
 * @var \App\View\AppView $this
 * @var array $tags
 * @var string|null $selectedSiteLanguage
 */
?>
<?php
$currentParams = $this->request->getAttribute('params');
unset($currentParams['_matchedRoute']);
unset($currentParams['pass']);
?>
<?php if (!empty($languages)) : ?>
    <?php $currentParams['lang'] = 'en'; ?>
    <ul class="navbar-nav me-3">
        <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Language') ?></a>
        <ul class="dropdown-menu">
            <li>
                <?= $this->Html->link(
                    __('English'),
                    $currentParams,
                    [
                    'class' => 'dropdown-item' . ($selectedSiteLanguage === 'en' ? ' active' : ''),
                    ],
                ) ?>
            </li>
            <?php foreach ($languages as $code => $name) : ?>
                <?php $currentParams['lang'] = $code; ?>
                <li>
                <?= $this->Html->link(
                    h($name),
                    $currentParams,
                    ['class' => 'dropdown-item' . ($selectedSiteLanguage == $code ? ' active' : '')],
                ) ?>
                </li>
            <?php endforeach; ?>
        </ul>
        </li>
    </ul>
<?php endif; ?>