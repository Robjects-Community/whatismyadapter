<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ConsentService;
use App\Utility\I18nManager;
use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
// No explicit `use Cake\Http\Response;` needed just for redirecting in beforeFilter

class AppController extends Controller
{
    use LogTrait;

    /**
     * Store a cache key available for all controllers to use
     * Created in the initialize method
     *
     * @var string
     */
    public string $cacheKey;

    /**
     * ConsentService for handling cookie consent and session management
     *
     * @var \App\Service\ConsentService
     */
    protected ConsentService $consentService;

    /**
     * Checks if the current request is an admin request.
     *
     * This method determines whether the request is intended for the admin section
     * of the application by checking if the 'prefix' routing parameter is set to 'Admin'.
     *
     * @return bool Returns true if the request is for the admin section, false otherwise.
     */
    private function isAdminRequest(): bool
    {
        return $this->request->getParam('prefix') === 'Admin';
    }

    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->cacheKey = hash('xxh3', json_encode($this->request->getAttribute('params')));
        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication'); // Loads the component

        // Initialize ConsentService
        $this->consentService = new ConsentService();

        // Only load FrontEndSite component for non-admin routes
        if (!$this->request->getParam('prefix') || $this->request->getParam('prefix') !== 'Admin') {
            $this->loadComponent('DefaultTheme.FrontEndSite');
        }
    }

    /**
     * beforeFilter callback.
     *
     * Executed before each controller action. Checks for admin access rights
     * when accessing admin-prefixed routes.
     *
     * @param \Cake\Event\EventInterface $event The event instance.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event); // Call parent's beforeFilter

        I18nManager::setLocaleForLanguage($this->request->getParam('lang', 'en'));

        $identity = null;
        // The AuthenticationComponent (loaded in initialize) makes the identity available.
        // It relies on the AuthenticationMiddleware having run first to populate the request attribute.
        if ($this->components()->has('Authentication')) {
            $identity = $this->Authentication->getIdentity();
        }

        if ($identity) {
            $profilePic = $identity->image_url;

            // Only set profilePic if the user has an actual image file
            if ($profilePic && $identity->image) {
                $this->set(compact('profilePic'));
            }
        }

        if ($this->isAdminRequest()) {
            // If there is no identity, or the identifier part of the identity is null/empty
            if (!$identity || !$identity->getIdentifier() || $identity->get('is_admin') == false) {
                $this->Flash->error(__('Access denied. You must be logged in as an admin to view this page.'));
                $event->setResult($this->redirect(['_name' => 'home', 'prefix' => false]));

                return;
            }

            I18nManager::setLocalForAdminArea();
        }

        // Handle consent data processing
        $consentData = $this->consentService->getConsentData($this->request);
        $this->set($consentData);

        $this->set('activeCtl', $this->request->getParam('controller'));
        $this->set('activeAct', $this->request->getParam('action'));
    }

    /**
     * beforeRender method
     *
     * This method is called before the controller action is rendered. It
     * sets the theme for the view based on the prefix of the request.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        parent::beforeRender($event);

        $theme = $this->request->getParam('prefix') === 'Admin'
            ? Configure::read('Theme.admin_theme', 'AdminTheme')
            : Configure::read('Theme.default_theme', 'DefaultTheme');

        $this->viewBuilder()->setTheme($theme);
    }
}
