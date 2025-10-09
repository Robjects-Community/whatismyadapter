<?php
/**
 * Password Reset Email Template (Text)
 *
 * @var \Cake\View\View $this
 * @var string $resetUrl
 * @var string $username
 */
?>
Password Reset Request

Hello <?= $username ?>,

You recently requested to reset your password for your account. 

To reset your password, please visit the following link:
<?= $resetUrl ?>

This link will expire in 24 hours for security reasons.

If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.

Best regards,
The <?= Configure::read('App.name', 'WillowCMS') ?> Team