<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\TableRegistry;

/**
 * HomeController
 * 
 * Manages the enhanced front page with multiple content feeds
 */
class HomeController extends AppController
{
    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->Authentication->allowUnauthenticated(['index']);
    }

    /**
     * Index method - Main front page with multiple feeds
     *
     * @return \Cake\Http\Response|null|void
     */
    public function index()
    {
        try {
            // Load settings table to check feed configurations
            $settingsTable = null;
            try {
                $settingsTable = TableRegistry::getTableLocator()->get('Settings');
            } catch (\Exception $e) {
                // Settings table doesn't exist, continue with defaults
            }
            
            // Load required tables
            $articlesTable = TableRegistry::getTableLocator()->get('Articles');
            $productsTable = null;
            $tagsTable = null;
            
            // Try to get products table (may not exist)
            try {
                $productsTable = TableRegistry::getTableLocator()->get('Products');
            } catch (\Exception $e) {
                // Products table doesn't exist, continue without it
            }
            
            // Try to get tags table (may not exist)
            try {
                $tagsTable = TableRegistry::getTableLocator()->get('Tags');
            } catch (\Exception $e) {
                // Tags table doesn't exist, continue without it
            }
            
            // Featured Articles Feed - Check if enabled
            $featuredArticles = [];
            $featuredEnabled = $this->_getSettingValue($settingsTable, 'homepage_featured_articles_enabled', true);
            $featuredLimit = $this->_getSettingValue($settingsTable, 'homepage_featured_articles_limit', 3);
            
            if ($featuredEnabled) {
                try {
                    $featuredQuery = $articlesTable->find('all')
                        ->where([
                            'Articles.is_published' => true,
                            'Articles.featured' => true
                        ])
                        ->contain(['Users'])
                        ->order(['Articles.published' => 'DESC'])
                        ->limit($featuredLimit);
                    $featuredArticles = $featuredQuery->toArray();
                } catch (\Exception $e) {
                    // Featured column doesn't exist, get recent articles instead
                    try {
                        $featuredQuery = $articlesTable->find('all')
                            ->where(['Articles.is_published' => true])
                            ->contain(['Users'])
                            ->order(['Articles.published' => 'DESC'])
                            ->limit($featuredLimit);
                        $featuredArticles = $featuredQuery->toArray();
                    } catch (\Exception $e2) {
                        $featuredArticles = [];
                    }
                }
            }
            
            // Latest Articles Feed - Check if enabled
            $latestArticles = [];
            $latestEnabled = $this->_getSettingValue($settingsTable, 'homepage_latest_articles_enabled', true);
            $latestLimit = $this->_getSettingValue($settingsTable, 'homepage_latest_articles_limit', 6);
            
            if ($latestEnabled) {
                try {
                    $latestQuery = $articlesTable->find('all')
                        ->where(['Articles.is_published' => true])
                        ->contain(['Users'])
                        ->order(['Articles.published' => 'DESC'])
                        ->limit($latestLimit);
                    $latestArticles = $latestQuery->toArray();
                } catch (\Exception $e) {
                    $latestArticles = [];
                }
            }
            
            // Products Feed - Check if enabled
            $latestProducts = [];
            $productsEnabled = $this->_getSettingValue($settingsTable, 'homepage_latest_products_enabled', true);
            $productsLimit = $this->_getSettingValue($settingsTable, 'homepage_latest_products_limit', 4);
            
            if ($productsEnabled && $productsTable) {
                try {
                    $productsQuery = $productsTable->find('all')
                        ->order(['Products.created' => 'DESC'])
                        ->limit($productsLimit);
                    $latestProducts = $productsQuery->toArray();
                } catch (\Exception $e) {
                    // Products table has different structure, continue without it
                    $latestProducts = [];
                }
            }
            
            // Popular Tags Feed - Check if enabled
            $popularTags = [];
            $tagsEnabled = $this->_getSettingValue($settingsTable, 'homepage_popular_tags_enabled', true);
            $tagsLimit = $this->_getSettingValue($settingsTable, 'homepage_popular_tags_limit', 15);
            
            if ($tagsEnabled && $tagsTable) {
                try {
                    $tagsQuery = $tagsTable->find('all')
                        ->order(['Tags.id' => 'DESC'])
                        ->limit($tagsLimit);
                    $popularTags = $tagsQuery->toArray();
                } catch (\Exception $e) {
                    // Tags table has different structure, continue without it
                    $popularTags = [];
                }
            }
            
        } catch (\Exception $e) {
            // Fallback for complete database failure
            $featuredArticles = [];
            $latestArticles = [];
            $latestProducts = [];
            $popularTags = [];
        }
        
        // Development/Server Info Feed - Check if enabled
        $developmentInfo = [];
        $devInfoEnabled = $this->_getSettingValue($settingsTable, 'homepage_development_info_enabled', true);
        
        if ($devInfoEnabled) {
            $developmentInfo = [
                'server_cost' => $this->_getServerCostInfo(),
                'tech_stack' => $this->_getTechStackInfo(),
                'deployment_info' => $this->_getDeploymentInfo()
            ];
        }
        
        // Social Links - Check if enabled (always show for now since they use direct routes)
        $socialLinksEnabled = $this->_getSettingValue($settingsTable, 'homepage_social_links_enabled', true);
        $socialLinks = []; // Not needed since template uses direct Html->link calls
        
        $this->set(compact(
            'featuredArticles',
            'latestArticles',
            'latestProducts',
            'popularTags',
            'developmentInfo',
            'socialLinks'
        ));
    }
    
    /**
     * Get server cost information
     *
     * @return array
     */
    private function _getServerCostInfo(): array
    {
        return [
            'hosting' => 'Self-hosted / Docker',
            'monthly_cost' => '$0 (excluding API costs)',
            'api_services' => [
                'OpenAI' => 'Pay per use',
                'GitHub Actions' => 'Free tier',
                'Docker Hub' => 'Free tier'
            ]
        ];
    }
    
    /**
     * Get technology stack information
     *
     * @return array
     */
    private function _getTechStackInfo(): array
    {
        return [
            'backend' => 'CakePHP 5.x',
            'frontend' => 'Bootstrap 5',
            'database' => 'MySQL 8.x',
            'caching' => 'Redis',
            'queue' => 'CakePHP Queue Plugin',
            'containerization' => 'Docker & Docker Compose',
            'ci_cd' => 'GitHub Actions',
            'monitoring' => 'Custom metrics dashboard'
        ];
    }
    
    /**
     * Get deployment information
     *
     * @return array
     */
    private function _getDeploymentInfo(): array
    {
        return [
            'environments' => ['Development', 'Staging', 'Production'],
            'deployment_method' => 'Docker Compose',
            'alternatives_explored' => [
                'Portainer' => 'Too complex for single app',
                'DigitalOcean K8s' => 'Overkill for current scale',
                'Jenkins' => 'Using GitHub Actions instead',
                'Kubernetes' => 'Future consideration',
                'AWS ECS' => 'Cost considerations'
            ]
        ];
    }
    
    /**
     * Safely get a setting value with fallback to default
     *
     * @param mixed $settingsTable The settings table instance
     * @param string $key The setting key
     * @param mixed $default Default value if setting not found
     * @return mixed Setting value or default
     */
    private function _getSettingValue($settingsTable, string $key, $default)
    {
        if (!$settingsTable) {
            return $default;
        }
        
        try {
            // For this SettingsTable structure, we use category='homepage' and key_name=$key
            if (method_exists($settingsTable, 'getSettingValue')) {
                $result = $settingsTable->getSettingValue('homepage', $key);
                return $result !== null ? $result : $default;
            }
            
            // Fallback: try to find the setting manually
            $setting = $settingsTable->find()
                ->where([
                    'category' => 'homepage',
                    'key_name' => $key
                ])
                ->first();
                
            if ($setting) {
                // Use the castValue method logic if available, otherwise return raw value
                $value = $setting->value ?? null;
                if ($value !== null) {
                    // Cast based on value_type
                    switch ($setting->value_type ?? 'text') {
                        case 'bool':
                            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        case 'numeric':
                            return (int)$value;
                        default:
                            return (string)$value;
                    }
                }
            }
            
            return $default;
        } catch (\Exception $e) {
            return $default;
        }
    }
}
