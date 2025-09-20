<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Mailer\Mailer;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

/**
 * SendEmailJob Class
 *
 * This class is responsible for processing and sending emails as a background job.
 * It fetches email templates from the database, replaces placeholders with provided data,
 * and sends the email using CakePHP's Mailer class.
 */
class SendEmailJob extends AbstractJob
{
    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'email sending';
    }

    /**
     * Executes the email sending process using the provided message data.
     *
     * @param \Cake\Queue\Job\Message $message The message object containing email details
     * @return string|null Returns Processor::ACK if successful, Processor::REJECT if failed
     */
    public function execute(Message $message): ?string
    {
        $templateIdentifier = $message->getArgument('template_identifier');
        $from = $message->getArgument('from');
        $to = $message->getArgument('to');
        $viewVars = $message->getArgument('viewVars', []);

        if (!$this->validateArguments($message, ['template_identifier', 'from', 'to'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling(
            $templateIdentifier,
            function () use ($templateIdentifier, $from, $to, $viewVars) {
            // Fetch the email template from the database
                $emailTemplatesTable = $this->getTable('EmailTemplates');
                $emailTemplate = $emailTemplatesTable->find()
                ->where(['template_identifier' => $templateIdentifier])
                ->first();

                if (!$emailTemplate) {
                    throw new Exception(__('Email template not found: {0}', $templateIdentifier));
                }

            // Replace placeholders in the email body
                foreach ($viewVars as $key => $value) {
                    $emailTemplate->body_html = str_replace('{' . $key . '}', $value, $emailTemplate->body_html);
                    $emailTemplate->body_plain = str_replace('{' . $key . '}', $value, $emailTemplate->body_plain);
                }

            // Configure and send email
                $mailer = new Mailer('default');
                $mailer->setTo($to)
                ->setFrom($from)
                ->setSubject($emailTemplate->subject)
                ->setEmailFormat('both')
                ->setViewVars([
                    'bodyHtml' => $emailTemplate->body_html,
                    'bodyPlain' => $emailTemplate->body_plain,
                ])
                ->viewBuilder()
                    ->setTemplate('default')
                    ->setLayout('default')
                    ->setPlugin('AdminTheme');

                return $mailer->deliver();
            },
            "{$from} → {$to}",
        );
    }
}
