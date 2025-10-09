<?php
/**
 * Password Reset Email Template (HTML)
 *
 * @var \Cake\View\View $this
 * @var string $resetUrl
 * @var string $username
 */
?>
<h2>Password Reset Request</h2>

<p>Hello <?= h($username) ?>,</p>

<p>You recently requested to reset your password for your account. Click the button below to reset it:</p>

<p style="text-align: center; margin: 30px 0;">
    <a href="<?= $resetUrl ?>" style="background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 4px; display: inline-block;">
        Reset Your Password
    </a>
</p>

<p>If the button above doesn't work, you can also copy and paste the following link into your web browser:</p>
<p><?= $resetUrl ?></p>

<p><strong>This link will expire in 24 hours for security reasons.</strong></p>

<p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>

<p>Best regards,<br>
The <?= h(Configure::read('App.name', 'WillowCMS')) ?> Team</p>