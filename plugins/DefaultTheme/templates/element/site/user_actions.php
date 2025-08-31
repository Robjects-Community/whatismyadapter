<?php use App\Utility\SettingsManager; ?>

<?php if ($this->Identity->isLoggedIn()): ?>
<div class="flex-shrink-0 dropdown ms-auto">
    <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <?php if (isset($profilePic)) : ?>
            <?= $this->Html->image($profilePic, 
            [
                'pathPrefix' => '', 
                'class' => 'rounded-circle',
                'width' => '32',
                'height' => '32',

            ])?>
        <?php else: ?>
            <img src="/img/willow-icon.png" width="32" height="32" class="rounded-circle">
        <?php endif; ?>
    </a>
    <ul class="dropdown-menu dropdown-menu-end text-small shadow">
        <li>
            <?php if ($this->Identity->get('is_admin')): ?>
                <?= $this->Html->link(__('Admin'), ['prefix' => 'Admin', 'controller' => 'Articles', 'action' => 'index'], ['class' => 'dropdown-item']) ?>
            <?php endif; ?>
        </li>
        <li>
            <?= $this->Html->link(__('My Account'), ['controller' => 'Users', 'action' => 'edit', $this->Identity->get('id')], ['class' => 'dropdown-item']) ?>
        </li>
        <li>
            <?= $this->Html->link(__('Cookies'), ['_name' => 'cookie-consent'], ['class' => 'dropdown-item']) ?>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <?= $this->Html->link(__('Logout'), ['controller' => 'Users', 'action' => 'logout'], ['class' => 'dropdown-item']) ?>
        </li>
    </ul>
</div>

<?php else: ?>

<ul class="navbar-nav me-3">
    <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false"><?= __('Account') ?></a>
    <ul class="dropdown-menu">
        <li>
            <?= $this->Html->link(__('Log In'), ['controller' => 'Users', 'action' => 'login'], ['class' => 'dropdown-item']) ?>
        </li>
        <li>
            <?= $this->Html->link(__('Cookies'), ['_name' => 'cookie-consent'], ['class' => 'dropdown-item']) ?>
        </li>
        <li>
            <?php if (SettingsManager::read('Users.registrationEnabled', false)) :?>
            <?= $this->Html->link(__('Register'), ['controller' => 'Users', 'action' => 'register'], ['class' => 'dropdown-item']) ?>
            <?php endif; ?>
        </li>
    </ul>
    </li>
</ul>

<?php endif; ?>