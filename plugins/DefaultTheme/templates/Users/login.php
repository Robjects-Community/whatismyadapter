<?php use App\Utility\SettingsManager; ?>
<div class="form-signin w-100 m-auto">
    <?= $this->Flash->render() ?>
    <?= $this->Form->create(null, ['class' => 'needs-validation', 'novalidate' => true]) ?>
        <h1 class="h3 mb-3 fw-normal"><?= __('Log In') ?></h1>

        <div class="form-floating">
            <?= $this->Form->input('email', [
                'type' => 'email',
                'required' => true,
                'class' => 'form-control',
                'placeholder' => 'name@example.com',
                'id' => 'floatingInput',
                'label' => false,
                'templates' => [
                    'inputContainer' => '{{content}}',
                ],
            ]) ?>
            <label for="floatingInput">Email address</label>
        </div>

        <div class="form-floating">
            <?= $this->Form->input('password', [
                'type' => 'password',
                'required' => true,
                'class' => 'form-control',
                'placeholder' => 'Password',
                'id' => 'floatingPassword',
                'label' => false,
                'templates' => [
                    'inputContainer' => '{{content}}',
                ],
            ]) ?>
            <label for="floatingPassword">Password</label>
        </div>

        <?= $this->Form->submit(__('Log In'), ['class' => 'btn btn-primary w-100 py-2', 'type' => 'submit']) ?>

        <?php if (SettingsManager::read('Users.registrationEnabled', false)) :?>      
        <div class="text-center">
            <?= $this->Html->link(__('Forgot Password?'), ['controller' => 'Users', 'action' => 'forgot-password'], ['class' => 'text-decoration-none']) ?>
        </div>
        <?php endif; ?>

    <?= $this->Form->end() ?>
</div>