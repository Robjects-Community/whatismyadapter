<?php use App\Utility\SettingsManager; ?>
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
            <?= $this->Html->link(__('Front Site'), '/', ['class' => 'dropdown-item']) ?>
        </li>
        <li>
            <?= $this->Html->link(__('My Account'), ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'view', $this->Identity->get('id')], ['class' => 'dropdown-item']) ?>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <?= $this->Html->link(__('Logout'), ['prefix' => 'Admin', 'controller' => 'Users', 'action' => 'logout'], ['class' => 'dropdown-item']) ?>
        </li>
    </ul>
</div>