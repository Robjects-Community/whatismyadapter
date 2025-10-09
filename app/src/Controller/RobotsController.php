<?php
declare(strict_types=1);

namespace App\Controller;

use App\Utility\SettingsManager;
use Cake\Event\EventInterface;
use Cake\Http\Response;

class RobotsController extends AppController
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
        $this->Authentication->addUnauthenticatedActions(['index']);
    }

    /**
     * Display robots.txt content with language-specific paths
     *
     * @return \Cake\Http\Response
     */
    public function index(): Response
    {
        $robotsContent = SettingsManager::read('SEO.robots', '');

        // Get language from URL or default to 'en'
        $lang = $this->request->getParam('lang');

        // If no language parameter (root robots.txt request), default to 'en'
        if (empty($lang)) {
            $lang = 'en';
        }

        // Replace language placeholder with actual language code
        $robotsContent = str_replace('{LANG}', $lang, $robotsContent);

        return $this->response
            ->withType('text/plain')
            ->withStringBody($robotsContent)
            ->withCache('-1 minute', '+1 day'); // Add cache headers
    }
}
