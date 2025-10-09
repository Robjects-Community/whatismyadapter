<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Mailer;
use Cake\Routing\Router;
use Cake\Utility\Security;

/**
 * TestPasswordReset command.
 */
class TestPasswordResetCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'test_password_reset';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'test_password_reset';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Test password reset email functionality.';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription())
            ->addArgument('email', [
                'help' => 'Email address to send password reset to',
                'required' => false,
                'default' => 'admin@test.com'
            ]);
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $email = $args->getArgument('email') ?? 'admin@test.com';
        
        $io->out('Testing password reset for: ' . $email);
        
        try {
            // Load Users table
            $users = TableRegistry::getTableLocator()->get('Users');
            
            // Find user by email
            $user = $users->findByEmail($email)->first();
            
            if (!$user) {
                $io->error("User with email {$email} not found!");
                return static::CODE_ERROR;
            }
            
            $io->out('✅ Found user: ' . $user->email);
            
            // Generate a secure reset token
            $resetToken = bin2hex(Security::randomBytes(32));
            $expiryTime = new \DateTime('+24 hours');
            
            // Save the reset token to the user
            $user->reset_token = $resetToken;
            $user->reset_token_expires = $expiryTime;
            
            if ($users->save($user)) {
                $io->out('✅ Reset token saved to database: ' . substr($resetToken, 0, 16) . '...');
            } else {
                $io->error('❌ Failed to save reset token!');
                return static::CODE_ERROR;
            }
            
            // Generate reset URL
            Router::fullBaseUrl('http://localhost:8080');
            
            $resetUrl = Router::url([
                '_name' => 'reset-password',
                'confirmationCode' => $resetToken
            ], true);
            
            $io->out('✅ Generated reset URL: ' . $resetUrl);
            
            // Send password reset email
            $io->out('Sending password reset email...');
            
            $transport = 'mailpit'; // Use mailpit for local testing
            $io->out('Using transport: ' . $transport);
            
            $mailer = new Mailer();
            
            $emailContent = "Hello " . ($user->username ?: $user->email) . ",\n\n" .
                           "You have requested a password reset for your account.\n\n" .
                           "Please click the following link to reset your password:\n" .
                           $resetUrl . "\n\n" .
                           "This link will expire in 24 hours.\n\n" .
                           "If you did not request this password reset, please ignore this email.\n\n" .
                           "Best regards,\n" .
                           env('APP_NAME', 'WillowCMS');
            
            $mailer
                ->setTransport($transport)
                ->setTo($email)
                ->setFrom([env('EMAIL_FROM_ADDRESS', 'noreply@willowcms.app') => env('EMAIL_FROM_NAME', 'WillowCMS')])
                ->setSubject('Password Reset Request - ' . env('APP_NAME', 'WillowCMS'))
                ->deliver($emailContent);
                
            $io->success('Password reset email sent successfully!');
            $io->out('Check MailPit at http://localhost:8025 to view the email.');
            
            return static::CODE_SUCCESS;
            
        } catch (\Exception $e) {
            $io->error('Failed to send password reset email: ' . $e->getMessage());
            $io->out('Error details:');
            $io->out($e->getTraceAsString());
            
            return static::CODE_ERROR;
        }
    }
}