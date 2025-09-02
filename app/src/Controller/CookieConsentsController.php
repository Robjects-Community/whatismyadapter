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
     * Configures authentication for specific actions.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['edit']);
    }

    /**
     * Edit cookie consent preferences and manage GDPR compliance.
     *
     * Handles both GET and POST/PUT/PATCH requests for cookie consent management.
     * For GET requests, displays the current consent settings if they exist.
     * For POST/PUT/PATCH requests, creates a new consent record for GDPR audit trail
     * and updates the user's cookie preferences. Supports both regular and AJAX requests.
     *
     * The method handles two types of consent:
     * - Essential only: Basic cookies required for site functionality
     * - Essential and Analytics: Includes analytics tracking consent
     *
     * @return \Cake\Http\Response|null Returns Response object for redirects/AJAX or null for normal view render
     * @throws \RuntimeException When cookie creation fails
     */
    public function edit(): ?Response
    {
        $userId = $this->request->getAttribute('identity') ?
            $this->request->getAttribute('identity')->getIdentifier() : null;

        $cookieConsent = null;
        $consentCookie = $this->request->getCookie('consent_cookie');
        if ($consentCookie) {
            $consentData = json_decode($consentCookie, true);
            $cookieConsent = $this->CookieConsents->newEntity($consentData);
            $cookieConsent->session_id = $this->request->getSession()->id();
        }

        if ($this->request->is(['patch', 'post', 'put'])) {
            // Always create a new record for GDPR audit trail
            $newConsent = $this->CookieConsents->newEntity($this->request->getData());

            $newConsent->session_id = $this->request->getSession()->id();
            $newConsent->user_id = $userId;
            $newConsent->ip_address = $this->request->clientIp();
            $newConsent->user_agent = $this->request->getHeaderLine('User-Agent');

            $consentType = $this->request->getData('consent_type');
            if ($consentType === 'essential') {
                $newConsent->analytics_consent = 0;
                $newConsent->functional_consent = 0;
                $newConsent->marketing_consent = 0;
            }
            if ($consentType === 'all') {
                $newConsent->analytics_consent = 1;
                $newConsent->functional_consent = 1;
                $newConsent->marketing_consent = 1;
            }

            if ($this->CookieConsents->save($newConsent)) {
                $this->Flash->success(__('Your cookie preferences have been saved.'));

                // Save a cookie with the consent information
                $cookie = $this->CookieConsents->createConsentCookie($newConsent);
                $this->response = $this->response->withCookie($cookie);

                if ($this->request->is('ajax')) {
                    return $this->response->withType('application/json')
                        ->withStringBody(json_encode(['success' => true]));
                }

                return $this->redirect(['action' => 'edit']);
            }

            if ($this->request->is('ajax')) {
                return $this->response->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => false,
                        'errors' => $cookieConsent->getErrors(),
                    ]));
            }

            $this->Flash->error(__('Your cookie preferences could not be saved. Please, try again.'));
        }

        $this->set(compact('cookieConsent'));

        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('edit');
        }

        return null;
    }
}
