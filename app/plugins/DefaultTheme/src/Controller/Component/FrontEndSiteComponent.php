<?php
namespace DefaultTheme\Controller\Component;

use Cake\Controller\Component;
use Cake\Event\EventInterface;
use App\Utility\I18nManager;
use App\Utility\SettingsManager;
use Cake\Core\Configure;

/**
 * FrontEndSiteComponent
 *
 * This component is responsible for preparing and setting up data
 * for the front-end of the site, specifically the article tree and tag list.
 * It automatically runs before rendering non-admin pages.
 */
class FrontEndSiteComponent extends Component
{
    /**
     * Default configuration.
     *
     * Defines the events this component listens to.
     *
     * @var array
     */
    protected array $_defaultConfig = [
        'implementedEvents' => [
            'Controller.beforeRender' => 'beforeRender'
        ]
    ];

    /**
     * Before render callback.
     *
     * This method is automatically called before the view is rendered
     * using the DefaultTheme. It prepares and sets data
     * for use in the views.
     *
     * @param \Cake\Event\EventInterface $event The beforeRender event that was fired.
     * @return void
     */
    public function beforeRender(EventInterface $event): void
    {
        $controller = $this->getController(); // Get the controller instance


// A request object from the controller encapsulates the details of the current HTTP request that the client sends to the server.
// It provides methods to access various aspects of the request, such as parameters, headers, and the request method.
// This allows the component to access the Articles and Tags models and to set the necessary data for the front-end site.
        $request = $controller->getRequest(); // Ensure the controller is set and has a request object



        // Skip processing for admin routes
        if ($request->getParam('prefix') === 'Admin') {
            return;
        }
        
        // Skip processing for certain user actions during tests
        if (Configure::read('debug') && $request->getParam('controller') === 'Users') { // Check if the controller is Users
            // Skip certain actions that do not require front-end data
            $skipActions = ['login', 'logout', 'register', 'edit', 'forgotPassword', 'resetPassword', 'confirmEmail'];
            if (in_array($request->getParam('action'), $skipActions)) {
                // Set minimal required variables
                $controller->set([
                    'menuPages' => [],
                    'rootTags' => [],
                    'featuredArticles' => [],
                    'articleArchives' => [],
                    'siteLanguages' => I18nManager::getEnabledLanguages(),
                    'selectedSiteLanguage' => $request->getParam('lang', 'en')
                ]);
                return;
            }
        }

        $cacheKey = $controller->cacheKey; // Use the cache key from the controller to ensure consistent caching
        $articlesTable = $controller->fetchTable('Articles'); // Fetch the Articles table from the controller
        $tagsTable = $controller->fetchTable('Tags'); // Fetch the Tags table from the controller
        $menuPages = []; // Initialize menu pages


        switch(SettingsManager::read('SitePages.mainMenuShow', 'root')) {
            case "root":
                $menuPages = $articlesTable->getRootPages($cacheKey);
                break;
            case "selected":
                $menuPages = $articlesTable->getMainMenuPagesWithChildren($cacheKey);
                break;
        }

        $rootTags = [];
        switch(SettingsManager::read('SitePages.mainTagMenuShow', 'root')) {
            case "root":
                $rootTags = $tagsTable->getRootTags($cacheKey);
                break;
            case "selected":
                $rootTags = $tagsTable->getMainMenuTags($cacheKey);
                break;
        }

        $featuredArticles = $articlesTable->getFeatured($cacheKey);
        $articleArchives = $articlesTable->getArchiveDates($cacheKey);

        // Set the site privacy policy if configured
        // This function looks for an article that has been configured previously as the privacy policy by a site admin
        // and sets it to the view variable 'sitePrivacyPolicy' if found.
        // If no privacy policy is set, this will not set the variable.
        $privacyPolicyId = SettingsManager::read('SitePages.privacyPolicy', null);
        if ($privacyPolicyId && $privacyPolicyId != 'None') {
            $privacyPolicy = $articlesTable->find()
                ->select(['id', 'title', 'slug'])
                ->where(['id' => $privacyPolicyId])
                ->cache($cacheKey . 'priv_page', 'content')
                ->first();
                
            if ($privacyPolicy) {
                $controller->set('sitePrivacyPolicy', $privacyPolicy->toArray());
            }
        }
        
        // Get footer menu pages based on settings
        $footerMenuPages = [];
        switch(SettingsManager::read('SitePages.footerMenuShow', 'selected')) {
            case "root":
                $footerMenuPages = $articlesTable->getRootPages($cacheKey);
                break;
            case "selected":
                $footerMenuPages = $articlesTable->getFooterMenuPagesWithChildren($cacheKey);
                break;
        }
        

        //////////////////////////////////// SET  functions for the templates, accessible in the views without the need to pass them explicitly to the controller ////////////////////
        // Set the view variables for the front-end site
        // These variables will be available in the DefaultTheme views
        // and can be used to render the menu, tags, featured articles, and archives.
        $controller->set(compact(
            'menuPages',
            'footerMenuPages',
            'rootTags',
            'featuredArticles',
            'articleArchives',
        ));
        // Set the site languages and selected language
        // This will be used to render the language switcher in the front-end.
        // The selected language is determined by the 'lang' parameter in the request,
        $controller->set('siteLanguages', I18nManager::getEnabledLanguages());
        $controller->set('selectedSiteLanguage', $request->getParam('lang', 'en'));
    }
}