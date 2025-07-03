<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Model\Entity\User;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\ORM\TableRegistry;
use Cake\Routing\Router;
use Cake\Utility\Text;
use Exception;

/**
 * EmailTemplates Controller
 *
 * Manages email templates and sending emails based on these templates.
 *
 * @property \App\Model\Table\EmailTemplatesTable $EmailTemplates
 */
class EmailTemplatesController extends AppController
{
    /**
     * Displays a paginated list of email templates.
     *
     * @return void
     */
    public function index(): ?Response
    {
        $query = $this->EmailTemplates->find()
            ->select([
                'EmailTemplates.id',
                'EmailTemplates.template_identifier',
                'EmailTemplates.name',
                'EmailTemplates.subject',
                'EmailTemplates.body_html',
                'EmailTemplates.body_plain',
                'EmailTemplates.created',
                'EmailTemplates.modified',
            ]);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'EmailTemplates.template_identifier LIKE' => '%' . $search . '%',
                    'EmailTemplates.name LIKE' => '%' . $search . '%',
                    'EmailTemplates.subject LIKE' => '%' . $search . '%',
                    'EmailTemplates.body_html LIKE' => '%' . $search . '%',
                    'EmailTemplates.body_plain LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $emailTemplates = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('emailTemplates', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('emailTemplates'));

        return null;
    }

    /**
     * Displays details of a specific email template.
     *
     * @param string|null $id Email Template id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $emailTemplate = $this->EmailTemplates->get($id, contain: []);
        $this->set(compact('emailTemplate'));
    }

    /**
     * Adds a new email template.
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $emailTemplate = $this->EmailTemplates->newEmptyEntity();
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $data['body_plain'] = $this->htmlToPlainText($data['body_html']);

            $emailTemplate = $this->EmailTemplates->patchEntity($emailTemplate, $data);
            if ($this->EmailTemplates->save($emailTemplate)) {
                $this->Flash->success(__('The email template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The email template could not be saved. Please, try again.'));
        }
        $this->set(compact('emailTemplate'));

        return null;
    }

    /**
     * Edits an existing email template.
     *
     * @param string|null $id Email Template id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $emailTemplate = $this->EmailTemplates->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $data['body_plain'] = $this->htmlToPlainText($data['body_html']);

            $emailTemplate = $this->EmailTemplates->patchEntity($emailTemplate, $data);
            if ($this->EmailTemplates->save($emailTemplate)) {
                $this->Flash->success(__('The email template has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The email template could not be saved. Please, try again.'));
        }
        $this->set(compact('emailTemplate'));

        return null;
    }

    /**
     * Converts HTML content to plain text.
     *
     * @param string $html The HTML content to be converted.
     * @return string The plain text representation of the HTML content.
     */
    private function htmlToPlainText(string $html): string
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text);

        return trim($text);
    }

    /**
     * Deletes an email template.
     *
     * @param string|null $id Email Template id.
     * @return \Cake\Http\Response Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $emailTemplate = $this->EmailTemplates->get($id);
        if ($this->EmailTemplates->delete($emailTemplate)) {
            $this->Flash->success(__('The email template has been deleted.'));
        } else {
            $this->Flash->error(__('The email template could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sends an email to a user based on a selected email template.
     *
     * @return \Cake\Http\Response|null Redirects after attempting to send the email, or renders view.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When email template or user not found.
     * @throws \Exception If there's an error during the email sending process.
     */
    public function sendEmail(): ?Response
    {
        $emailTemplates = $this->EmailTemplates->find(
            'list',
            keyField: 'id',
            valueField: 'name',
        );

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $users = $usersTable->find(
            'list',
            keyField: 'id',
            valueField: 'email',
        );

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $variables = $this->prepareEmailVariables($data['email_template_id'], $data['user_id']);
            $emailTemplate = $this->EmailTemplates->get($data['email_template_id']);
            $user = $usersTable->get($data['user_id']);

            $bodyHtml = $emailTemplate->body_html ?? '';
            $bodyPlain = $emailTemplate->body_plain ?? '';

            // Replace all placeholders
            foreach ($variables as $key => $value) {
                $bodyHtml = str_replace('{' . $key . '}', $value, $bodyHtml);
                $bodyPlain = str_replace('{' . $key . '}', $value, $bodyPlain);
            }

            $mailer = new Mailer('default');
            $mailer->setTo($user->email)
                ->setSubject($emailTemplate->subject)
                ->setEmailFormat('both')
                ->setViewVars([
                    'bodyHtml' => $bodyHtml,
                    'bodyPlain' => $bodyPlain,
                ])
                ->viewBuilder()
                    ->setTemplate('default')
                    ->setLayout('default')
                    ->setPlugin('AdminTheme');

            try {
                $result = $mailer->deliver();
                if ($result) {
                    $this->Flash->success(__('Email sent successfully.'));
                    Log::info(
                        __(
                            'Email sent successfully to: {0}. Template: {1}, Subject: {2}',
                            $user->email,
                            $emailTemplate->template_identifier,
                            $emailTemplate->subject,
                        ),
                        ['group_name' => 'email'],
                    );
                } else {
                    $this->Flash->error(__('Failed to send email. Please check your email configuration.'));
                    Log::error(
                        __('Failed to send email to: {0}', $user->email),
                        ['group_name' => 'email'],
                    );
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Error sending email: {0}', $e->getMessage()));
                Log::error(
                    __('Error sending email: {0}', $e->getMessage()),
                    ['group_name' => 'email', 'exception' => $e],
                );
            }

            return $this->redirect(['action' => 'index']);
        }

        $this->set(compact('emailTemplates', 'users'));

        return null;
    }

    /**
     * Prepares variables for email templates.
     *
     * This method generates a set of variables to be used in email templates.
     * It always includes basic user information and conditionally adds other
     * variables based on the content of the email template.
     *
     * @param string $templateId The UUID of the email template.
     * @param string $userId The ID of the user for whom the email is being prepared.
     * @return array An associative array of variables for use in the email template.
     */
    private function prepareEmailVariables(string $templateId, string $userId): array
    {
        $variables = [];
        $emailTemplate = $this->EmailTemplates->get($templateId, contain: []);
        $user = TableRegistry::getTableLocator()->get('Users')->get($userId, contain: []);

        $variables['username'] = $user->username;
        $variables['email'] = $user->email;

        if (
            strpos($emailTemplate->body_html, '{confirm_email_link}') !== false ||
            strpos($emailTemplate->body_plain, '{confirm_email_link}') !== false
        ) {
            $variables['confirm_email_link'] = $this->generateLink($user, 'confirm_email_link');
        }

        if (
            strpos($emailTemplate->body_html, '{reset_password_link}') !== false ||
            strpos($emailTemplate->body_plain, '{reset_password_link}') !== false
        ) {
            $variables['reset_password_link'] = $this->generateLink($user, 'reset_password_link');
        }

        return $variables;
    }

    /**
     * Generates a confirmation link for a user.
     *
     * This method retrieves the confirmation code for a given user from the
     * UserAccountConfirmations table. If a confirmation code does not exist,
     * it generates a new UUID as the confirmation code, saves it to the table,
     * and then generates a URL for the user to confirm their account.
     *
     * @param \App\Model\Entity\User $user The user entity for whom the confirmation link is generated.
     * @return string The generated confirmation link URL.
     */
    private function generateLink(User $user, string $emailTemplateId): string
    {
        $userAccountConfirmationsTable = TableRegistry::getTableLocator()->get('UserAccountConfirmations');
        $confirmation = $userAccountConfirmationsTable->find()
            ->where(['user_id' => $user->id])
            ->first();

        if ($confirmation) {
            $confirmationCode = $confirmation->confirmation_code;
        } else {
            $confirmationCode = Text::uuid();
            $newConfirmation = $userAccountConfirmationsTable->newEntity([
                'user_id' => $user->id,
                'confirmation_code' => $confirmationCode,
            ]);
            $userAccountConfirmationsTable->save($newConfirmation);
        }

        switch ($emailTemplateId) {
            case 'reset_password_link':
                return Router::url([
                    '_name' => 'reset-password',
                    $confirmationCode,
                ], true);

            case 'confirm_email_link':
                return Router::url([
                    '_name' => 'confirm-email',
                    $confirmationCode,
                ], true);
        }

        return false;
    }
}
