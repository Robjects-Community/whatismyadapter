<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;

/**
 * HomepageFeeds Controller for Admin
 * 
 * Manages configuration of feeds displayed on the homepage
 */
class HomepageFeedsController extends AppController
{
    /**
     * Index method - Show current homepage feeds configuration
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $settingsTable = $this->fetchTable('Settings');
        
        // Define feed configurations with their settings keys
        $feedConfigs = $this->_getFeedConfigurations();
        
        // Load current settings for each feed
        $feedOptions = [];
        foreach ($feedConfigs as $key => $config) {
            $enabled = $settingsTable->getSettingValue('homepage', $config['setting_key']);
            $limit = isset($config['limit_key']) ? $settingsTable->getSettingValue('homepage', $config['limit_key']) : null;
            
            $feedOptions[$key] = [
                'label' => $config['label'],
                'description' => $config['description'],
                'enabled' => $enabled !== null ? $enabled : $config['default'],
                'limit' => $limit !== null ? $limit : ($config['limit_default'] ?? null)
            ];
        }
        
        // Get feed statistics for display
        $feedStats = $this->_getFeedStatistics();
        
        $this->set(compact('feedOptions', 'feedStats'));
    }
    
    /**
     * Configure method - Save homepage feeds configuration
     *
     * @return \Cake\Http\Response|null|void Redirects after save
     */
    public function configure()
    {
        if ($this->request->is(['patch', 'post', 'put'])) {
            $settingsTable = $this->fetchTable('Settings');
            $feedData = $this->request->getData();
            $feedConfigs = $this->_getFeedConfigurations();
            
            $savedCount = 0;
            
            try {
                // Save each feed setting individually
                foreach ($feedConfigs as $key => $config) {
                    $enabled = !empty($feedData[$key]);
                    $this->_saveSetting($settingsTable, 'homepage', $config['setting_key'], $enabled ? '1' : '0', 'bool');
                    $savedCount++;
                    
                    // Save limit settings if applicable
                    if (isset($config['limit_key']) && isset($feedData[$key . '_limit'])) {
                        $limit = (int)$feedData[$key . '_limit'];
                        if ($limit > 0 && $limit <= 50) { // Reasonable limits
                            $this->_saveSetting($settingsTable, 'homepage', $config['limit_key'], (string)$limit, 'numeric');
                        }
                    }
                }
                
                $this->Flash->success(__('Homepage feeds configuration has been saved successfully. {0} settings updated.', $savedCount));
                
                return $this->redirect(['action' => 'index']);
            } catch (\Exception $e) {
                $this->Flash->error(__('Unable to save homepage feeds configuration. Please try again. Error: {0}', $e->getMessage()));
            }
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Preview method - Preview homepage with current settings
     *
     * @return \Cake\Http\Response Redirects to homepage
     */
    public function preview()
    {
        // Clear any caches that might affect the homepage display
        \Cake\Cache\Cache::clear();
        
        $this->Flash->info(__('Preview mode: Homepage cache cleared. You can now view the updated feeds.'));
        
        return $this->redirect(['_name' => 'home']);
    }
    
    /**
     * Reset method - Reset homepage feeds to default configuration
     *
     * @return \Cake\Http\Response|null|void Redirects after reset
     */
    public function reset()
    {
        if ($this->request->is(['post', 'delete'])) {
            $settingsTable = $this->fetchTable('Settings');
            $feedConfigs = $this->_getFeedConfigurations();
            
            $resetCount = 0;
            
            try {
                // Reset each feed setting to its default
                foreach ($feedConfigs as $key => $config) {
                    $this->_saveSetting($settingsTable, 'homepage', $config['setting_key'], $config['default'] ? '1' : '0', 'bool');
                    $resetCount++;
                    
                    // Reset limit settings if applicable
                    if (isset($config['limit_key'])) {
                        $this->_saveSetting($settingsTable, 'homepage', $config['limit_key'], (string)$config['limit_default'], 'numeric');
                    }
                }
                
                $this->Flash->success(__('Homepage feeds configuration has been reset to defaults. {0} settings reset.', $resetCount));
            } catch (\Exception $e) {
                $this->Flash->error(__('Unable to reset homepage feeds configuration. Please try again. Error: {0}', $e->getMessage()));
            }
        }
        
        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Get statistics about each feed type
     *
     * @return array Feed statistics
     */
    private function _getFeedStatistics(): array
    {
        $stats = [];
        
        try {
            // Articles statistics
            $articlesTable = $this->fetchTable('Articles');
            $stats['articles'] = [
                'total' => $articlesTable->find()->where(['is_published' => true])->count(),
                'featured' => $articlesTable->find()->where([
                    'is_published' => true,
                    'OR' => [
                        'promoted' => true,
                        'rating >=' => 4.5
                    ]
                ])->count(),
                'recent' => $articlesTable->find()->where([
                    'is_published' => true,
                    'created >=' => date('Y-m-d', strtotime('-30 days'))
                ])->count()
            ];
        } catch (\Exception $e) {
            $stats['articles'] = ['total' => 0, 'featured' => 0, 'recent' => 0];
        }
        
        try {
            // Products statistics
            $productsTable = $this->fetchTable('Products');
            $stats['products'] = [
                'total' => $productsTable->find()->where(['status' => 'active'])->count(),
                'high_reliability' => $productsTable->find()->where([
                    'status' => 'active',
                    'rel_score >=' => 0.8
                ])->count(),
                'recent' => $productsTable->find()->where([
                    'status' => 'active',
                    'created >=' => date('Y-m-d', strtotime('-30 days'))
                ])->count()
            ];
        } catch (\Exception $e) {
            $stats['products'] = ['total' => 0, 'high_reliability' => 0, 'recent' => 0];
        }
        
        try {
            // Image Galleries statistics
            $galleriesTable = $this->fetchTable('ImageGalleries');
            $stats['galleries'] = [
                'total' => $galleriesTable->find()->where(['is_published' => true])->count(),
                'recent' => $galleriesTable->find()->where([
                    'is_published' => true,
                    'created >=' => date('Y-m-d', strtotime('-30 days'))
                ])->count()
            ];
        } catch (\Exception $e) {
            $stats['galleries'] = ['total' => 0, 'recent' => 0];
        }
        
        try {
            // Tags statistics
            $tagsTable = $this->fetchTable('Tags');
            $stats['tags'] = [
                'total' => $tagsTable->find()->count(),
                'popular' => $tagsTable->find()->where(['article_count >' => 0])->count()
            ];
        } catch (\Exception $e) {
            $stats['tags'] = ['total' => 0, 'popular' => 0];
        }
        
        return $stats;
    }
    
    /**
     * Get feed configurations with settings keys and defaults
     *
     * @return array Feed configurations
     */
    private function _getFeedConfigurations(): array
    {
        return [
            'featured_articles' => [
                'label' => 'Featured Articles',
                'description' => 'Show promoted and high-rated articles at the top',
                'setting_key' => 'homepage_featured_articles_enabled',
                'limit_key' => 'homepage_featured_articles_limit',
                'default' => true,
                'limit_default' => 3
            ],
            'latest_articles' => [
                'label' => 'Latest Articles',
                'description' => 'Display latest published articles',
                'setting_key' => 'homepage_latest_articles_enabled',
                'limit_key' => 'homepage_latest_articles_limit',
                'default' => true,
                'limit_default' => 6
            ],
            'products' => [
                'label' => 'Latest Products',
                'description' => 'Show latest products with reliability scores',
                'setting_key' => 'homepage_latest_products_enabled',
                'limit_key' => 'homepage_latest_products_limit',
                'default' => true,
                'limit_default' => 4
            ],
            'popular_tags' => [
                'label' => 'Popular Tags',
                'description' => 'Display popular content tags',
                'setting_key' => 'homepage_popular_tags_enabled',
                'limit_key' => 'homepage_popular_tags_limit',
                'default' => true,
                'limit_default' => 15
            ],
            'social_links' => [
                'label' => 'Social Links',
                'description' => 'Show social media and profile links',
                'setting_key' => 'homepage_social_links_enabled',
                'default' => true
            ],
            'development_info' => [
                'label' => 'Development Info',
                'description' => 'Display technology stack and deployment information',
                'setting_key' => 'homepage_development_info_enabled',
                'default' => true
            ]
        ];
    }
    
    /**
     * Save a setting to the database
     *
     * @param mixed $settingsTable The settings table instance
     * @param string $category The setting category
     * @param string $keyName The setting key name
     * @param string $value The setting value
     * @param string $valueType The value type (text, bool, numeric)
     * @return bool Success
     */
    private function _saveSetting($settingsTable, string $category, string $keyName, string $value, string $valueType = 'text'): bool
    {
        try {
            // Try to find existing setting
            $setting = $settingsTable->find()
                ->where([
                    'category' => $category,
                    'key_name' => $keyName
                ])
                ->first();
            
            if ($setting) {
                // Update existing setting
                $setting->value = $value;
                $setting->value_type = $valueType;
                return (bool)$settingsTable->save($setting);
            } else {
                // Create new setting
                $newSetting = $settingsTable->newEntity([
                    'category' => $category,
                    'key_name' => $keyName,
                    'value' => $value,
                    'value_type' => $valueType
                ]);
                return (bool)$settingsTable->save($newSetting);
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
