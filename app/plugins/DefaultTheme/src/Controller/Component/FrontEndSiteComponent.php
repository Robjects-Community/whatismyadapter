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
        $controller = $this->getController();
        $request = $controller->getRequest();
        
        // Skip processing for admin routes
        if ($request->getParam('prefix') === 'Admin') {
            return;
        }
        
        // Skip processing for certain user actions during tests
        if (Configure::read('debug') && $request->getParam('controller') === 'Users') {
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

        $cacheKey = $controller->cacheKey;
        $articlesTable = $controller->fetchTable('Articles');
        $tagsTable = $controller->fetchTable('Tags');

        $menuPages = [];
        switch(SettingsManager::read('SitePages.mainMenuShow', 'root')) {
            case "root":
                $menuPages = $articlesTable->getRootPages($cacheKey);
                break;
            case "selected":
                $menuPages = $articlesTable->getMainMenuPages($cacheKey);
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
        
        $controller->set(compact(
            'menuPages',
            'rootTags',
            'featuredArticles',
            'articleArchives',
        ));
        
        $controller->set('siteLanguages', I18nManager::getEnabledLanguages());
        $controller->set('selectedSiteLanguage', $request->getParam('lang', 'en'));
    }
}