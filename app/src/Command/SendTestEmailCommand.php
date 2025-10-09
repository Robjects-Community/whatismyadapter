<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Mailer\Mailer;

/**
 * SendTestEmail command.
 */
class SendTestEmailCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'send_test_email';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'send_test_email';
    }

    /**
     * Get the command description.
     *
     * @return string
     */
    public static function getDescription(): string
    {
        return 'Send a test email to verify Gmail SMTP configuration.';
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/5/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        return parent::buildOptionParser($parser)
            ->setDescription(static::getDescription())
            ->addArgument('to', [
                'help' => 'Email address to send test email to',
                'required' => false,
                'default' => 'mike.mail.tester@gmail.com'
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
        $to = $args->getArgument('to') ?? 'mike.mail.tester@gmail.com';
        
        $io->out('Sending test email to: ' . $to);
        
        try {
            // Use mailpit transport for testing
            $mailer = new Mailer();
            $mailer->setTransport('mailpit')
                   ->setTo($to)
                   ->setFrom(['noreply@willowcms.app' => 'WillowCMS'])
                   ->setSubject('WillowCMS MailPit Test - ' . date('Y-m-d H:i:s'))
                   ->deliver('This is a test email sent via MailPit SMTP from WillowCMS. If you are reading this in MailPit, the email configuration is working correctly!');
            
            $io->success("Test email sent successfully to {$to} via MailPit!");
            $io->out('Please check MailPit at http://localhost:8025 to view the email.');
            
            return static::CODE_SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to send test email: ' . $e->getMessage());
            $io->out('Error details:');
            $io->out($e->getTraceAsString());
            
            return static::CODE_ERROR;
        }
    }
}
