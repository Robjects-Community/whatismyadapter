<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Response;

/**
 * CookieConsents Controller
 *
 * @property \App\Model\Table\CookieConsentsTable $CookieConsents
 */
class CookieConsentsController extends AppController
{
    /**
     * beforeFilter callback.
     *
     * Allow unauthenticated access to cookie consent management.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        
        // Allow unauthenticated access to all cookie consent actions
        $this->Authentication->allowUnauthenticated(['index', 'view', 'add', 'edit', 'delete']);
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->CookieConsents->find()
            ->contain(['Users']);
        $cookieConsents = $this->paginate($query);

        $this->set(compact('cookieConsents'));
    }

    /**
     * View method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $cookieConsent = $this->CookieConsents->get($id, contain: ['Users']);
        $this->set(compact('cookieConsent'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $cookieConsent = $this->CookieConsents->newEmptyEntity();
        if ($this->request->is('post')) {
            $cookieConsent = $this->CookieConsents->patchEntity($cookieConsent, $this->request->getData());
            if ($this->CookieConsents->save($cookieConsent)) {
                $this->Flash->success(__('The cookie consent has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The cookie consent could not be saved. Please, try again.'));
        }
        $users = $this->CookieConsents->Users->find('list', limit: 200)->all();
        $this->set(compact('cookieConsent', 'users'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        if (!$session->started()) {
            $session->start();
        }
        $sessionId = $session->id();

        $identity = $this->Authentication->getIdentity();
        $userId = $identity ? $identity->getIdentifier() : null;

        if ($id !== null) {
            // Backward compatibility: load specific record by ID
            $cookieConsent = $this->CookieConsents->get($id, contain: []);
        } else {
            // Find latest consent to pre-fill the form
            $latest = $this->CookieConsents->getLatestConsent($sessionId, $userId);
            if ($latest !== null) {
                $cookieConsent = $this->CookieConsents->newEmptyEntity();
                $cookieConsent = $this->CookieConsents->patchEntity($cookieConsent, $latest, ['validate' => false]);
            } else {
                // Create new entity with defaults
                $cookieConsent = $this->CookieConsents->newEmptyEntity();
                $cookieConsent->set('essential_consent', true);
                $cookieConsent->set('analytics_consent', false);
                $cookieConsent->set('functional_consent', false);
                $cookieConsent->set('marketing_consent', false);
            }
        }

        if ($request->is(['patch', 'post', 'put'])) {
            $data = (array)$request->getData();
            $type = $data['consent_type'] ?? 'selected';

            // Prepare normalized consent data
            $normalized = [
                'essential_consent' => true, // Always true
                'analytics_consent' => false,
                'functional_consent' => false,
                'marketing_consent' => false,
            ];

            // Handle different consent types
            if ($type === 'all') {
                $normalized['analytics_consent'] = true;
                $normalized['functional_consent'] = true;
                $normalized['marketing_consent'] = true;
            } elseif ($type === 'selected') {
                $normalized['analytics_consent'] = !empty($data['analytics_consent']);
                $normalized['functional_consent'] = !empty($data['functional_consent']);
                $normalized['marketing_consent'] = !empty($data['marketing_consent']);
            }
            // For 'essential' type, all non-essential consents remain false

            // Add server-side metadata
            $normalized['ip_address'] = $request->clientIp() ?: '127.0.0.1';
            $normalized['user_agent'] = $request->getHeaderLine('User-Agent') ?: 'Unknown';
            $normalized['session_id'] = $sessionId;
            if ($userId !== null) {
                $normalized['user_id'] = $userId;
            }

            // Always save a fresh record to maintain audit trail
            $toSave = $this->CookieConsents->newEmptyEntity();
            $toSave = $this->CookieConsents->patchEntity($toSave, $normalized);

            if ($this->CookieConsents->save($toSave)) {
                // Create and set the consent cookie
                $cookie = $this->CookieConsents->createConsentCookie($toSave);
                $this->setResponse($this->getResponse()->withCookie($cookie));
                $this->Flash->success(__('Your cookie preferences have been saved.'));

                return $this->redirect(['action' => 'edit']);
            }

            $this->Flash->error(__('The cookie preferences could not be saved. Please, try again.'));
        }

        // For backward compatibility with admin interface, still provide users list
        if ($id !== null) {
            $users = $this->CookieConsents->Users->find('list', limit: 200)->all();
            $this->set(compact('cookieConsent', 'users'));
        } else {
            $this->set(compact('cookieConsent'));
        }
    }

    /**
     * Delete method
     *
     * @param string|null $id Cookie Consent id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $cookieConsent = $this->CookieConsents->get($id);
        if ($this->CookieConsents->delete($cookieConsent)) {
            $this->Flash->success(__('The cookie consent has been deleted.'));
        } else {
            $this->Flash->error(__('The cookie consent could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
