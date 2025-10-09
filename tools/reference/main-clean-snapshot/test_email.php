<?php
/**
 * Test script for password reset email functionality
 */

// Bootstrap CakePHP
require_once __DIR__ . '/vendor/autoload.php';

use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Cake\ORM\TableRegistry;
use Cake\Core\Exception\Exception;

// Load configuration
if (file_exists(__DIR__ . '/config/app_local.php')) {
    Configure::load('app_local', 'default');
} else {
    Configure::load('app', 'default');
}

// Initialize connections
ConnectionManager::setConfig(Configure::consume('Datasources'));

// Initialize the application
$application = new \App\Application(__DIR__ . '/config');
$application->bootstrap();

echo "Testing Password Reset Email Functionality\n";
echo "==========================================\n\n";

try {
    // Load Users table
    $users = TableRegistry::getTableLocator()->get('Users');
    
    // Find test user
    $user = $users->findByEmail('admin@test.com')->first();
    
    if (!$user) {
        echo "❌ Test user admin@test.com not found!\n";
        exit(1);
    }
    
    echo "✅ Found user: {$user->email}\n";
    
    // Generate a secure reset token
    $resetToken = bin2hex(Security::randomBytes(32));
    $expiryTime = new \DateTime('+24 hours');
    
    // Save the reset token to the user
    $user->reset_token = $resetToken;
    $user->reset_token_expires = $expiryTime;
    
    if ($users->save($user)) {
        echo "✅ Reset token saved to database: " . substr($resetToken, 0, 16) . "...\n";
    } else {
        echo "❌ Failed to save reset token!\n";
        exit(1);
    }
    
    // Generate reset URL
    Router::fullBaseUrl('http://localhost:8080');
    
    $resetUrl = Router::url([
        'controller' => 'Users',
        'action' => 'resetPassword',
        $resetToken,
        'lang' => 'en'
    ], true);
    
    echo "✅ Generated reset URL: $resetUrl\n";
    
    // Test email configuration
    echo "\nTesting email configuration...\n";
    
    $transport = Configure::read('EmailTransport.mailpit');
    if ($transport) {
        echo "✅ MailPit transport configuration found:\n";
        echo "   Host: {$transport['host']}\n";
        echo "   Port: {$transport['port']}\n";
    }
    
    // Send password reset email
    echo "\nSending password reset email...\n";
    
    $mailer = new Mailer('mailpit'); // Use mailpit transport
    $mailer
        ->setTo('admin@test.com')
        ->setSubject('Password Reset Request - ' . Configure::read('App.name', 'WillowCMS'))
        ->setTemplate('password_reset')
        ->setViewVars([
            'username' => $user->username ?: $user->email,
            'resetUrl' => $resetUrl
        ])
        ->deliver();
        
    echo "✅ Password reset email sent successfully!\n";
    
    echo "\nCheck MailPit at http://localhost:8025 to view the email.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}