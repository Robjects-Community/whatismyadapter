<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\Http\Response;
use DateTime;

/**
 * CacheController
 *
 * Handles cache clearing operations for the application.
 */
class CacheController extends AppController
{
    /**
     * Clears all cache configurations and updates the last cleared time.
     *
     * @return \Cake\Http\Response|null Redirects to the clearAll action or renders the view.
     */
    public function clearAll(): ?Response
    {
        $cacheInfo = $this->getCacheInfo();

        if ($this->request->is('post')) {
            $clearedCaches = [];
            $failedCaches = [];

            foreach ($cacheInfo as $config => $info) {
                if (Cache::clear($config)) {
                    $clearedCaches[] = $config;
                    $this->updateLastClearedTime($config);
                } else {
                    $failedCaches[] = $config;
                }
            }

            SettingsManager::clearCache();
            $clearedCaches[] = 'SettingsManager';

            if (empty($failedCaches)) {
                $this->Flash->success(__('All caches have been cleared successfully.'));
            } else {
                $this->Flash->warning(__(
                    'Some caches were cleared, but the following failed: {0}',
                    implode(', ', $failedCaches),
                ));
            }

            return $this->redirect(['action' => 'clearAll']);
        }

        $this->set('cacheInfo', $cacheInfo);

        return null;
    }

    /**
     * Clears a specific cache configuration and updates the last cleared time.
     *
     * @param string $cacheName The name of the cache configuration to clear.
     * @return \Cake\Http\Response Redirects to the clearAll action.
     */
    public function clear(string $cacheName): Response
    {
        $decodedCacheName = urldecode($cacheName);

        if ($this->request->is('post')) {
            if (Cache::getConfig($decodedCacheName)) {
                if (Cache::clear($decodedCacheName)) {
                    $this->updateLastClearedTime($decodedCacheName);
                    $this->Flash->success(__('{0} cache has been cleared successfully.', ucfirst($decodedCacheName)));
                } else {
                    $this->Flash->error(__('{0} cache could not be cleared.', ucfirst($decodedCacheName)));
                }
            } else {
                $this->Flash->error(__('{0} cache configuration does not exist.', ucfirst($decodedCacheName)));
            }
        }

        return $this->redirect(['action' => 'clearAll']);
    }

    /**
     * Retrieves information about all configured cache engines.
     *
     * @return array An array containing cache configuration details.
     */
    private function getCacheInfo(): array
    {
        $cacheInfo = [];
        $configuredCaches = Cache::configured();

        foreach ($configuredCaches as $config) {
            $engineConfig = Cache::getConfig($config);
            unset($engineConfig['password']);

            $cacheInfo[$config] = [
                'engine' => $engineConfig['className'],
                'settings' => $engineConfig,
                'last_cleared' => $this->getLastClearedTime($config),
            ];
        }

        return $cacheInfo;
    }

    /**
     * Gets the last cleared time for a specific cache configuration.
     *
     * @param string $config The cache configuration name.
     * @return \DateTime|null The last cleared time or null if not available.
     */
    private function getLastClearedTime(string $config): ?DateTime
    {
        $time = Cache::read('last_cleared_' . $config, 'default');

        return $time ? new DateTime($time) : null;
    }

    /**
     * Updates the last cleared time for a specific cache configuration.
     *
     * @param string $config The cache configuration name.
     * @return void
     */
    private function updateLastClearedTime(string $config): void
    {
        $time = new DateTime();
        Cache::write('last_cleared_' . $config, $time->format('Y-m-d H:i:s'), 'default');
    }
}
