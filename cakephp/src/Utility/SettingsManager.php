<?php
declare(strict_types=1);

namespace App\Utility;

use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use InvalidArgumentException;

/**
 * Class SettingsManager
 *
 * This utility class manages application settings with caching capabilities.
 * It uses CakePHP's Cache and ORM components to efficiently retrieve and store settings.
 */
class SettingsManager
{
    /**
     * @var string The cache configuration name used for storing settings.
     */
    private static string $cacheConfig = 'settings_cache';

    /**
     * Writes a setting value to the database and updates the cache.
     *
     * This method updates a setting value identified by the given path. It updates the value in the database
     * using the Settings table and then updates the cache to reflect the change.
     *
     * @param string $path The dot-separated path to the setting (e.g., 'category.key_name').
     * @param mixed $value The value to set for the specified setting.
     * @return bool True if the setting was successfully updated, false otherwise.
     * @throws \InvalidArgumentException If the path format is invalid.
     * @throws \RuntimeException If the Settings table cannot be accessed.
     */
    public static function write(string $path, mixed $value): bool
    {
        $parts = explode('.', $path);
        if (count($parts) !== 2) {
            throw new InvalidArgumentException(
                'Invalid path format. Must be in the format "category.key_name"',
            );
        }

        [$category, $keyName] = $parts;
        $cacheKey = 'setting_' . str_replace('.', '_', $path);

        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        // Find the existing setting
        $setting = $settingsTable->find()
            ->where([
                'category' => $category,
                'key_name' => $keyName,
            ])
            ->first();

        if (!$setting) {
            throw new InvalidArgumentException(
                sprintf('Setting not found: %s.%s', $category, $keyName),
            );
        }

        // Update the setting
        $setting->value = $value;

        if ($settingsTable->save($setting)) {
            // Update the cache
            Cache::write($cacheKey, $value, self::$cacheConfig);

            // Also clear the category-level cache if it exists
            Cache::delete('setting_' . $category, self::$cacheConfig);

            return true;
        }

        return false;
    }

    /**
     * Reads a setting value from the cache or database.
     *
     * This method attempts to read a setting value identified by the given path. It first checks the cache
     * for the value. If the value is not found in the cache, it retrieves the value from the database using
     * the Settings table. The result is then cached to optimize future requests.
     *
     * The method supports three types of reads:
     * 1. Reading all settings for a category (e.g., 'ImageSizes')
     * 2. Reading a specific setting within a category (e.g., 'ImageSizes.medium')
     * 3. Reading nested settings using a three-part path (e.g., 'AI.imageGeneration.enabled')
     *
     * @param string $path The dot-separated path to the setting (e.g., 'category' or 'category.key_name' or 'parent.category.key_name').
     * @param mixed $default The default value to return if the setting is not found.
     * @return mixed The setting value if found, otherwise the default value.
     *               For category-level requests, returns an array of all settings in that category.
     *               For specific setting requests, returns the value of that setting.
     * @throws \RuntimeException If the Settings table cannot be accessed.
     */
    public static function read(string $path, mixed $default = null): mixed
    {
        $cacheKey = 'setting_' . str_replace('.', '_', $path);

        $value = Cache::read($cacheKey, self::$cacheConfig);
        if ($value !== null) {
            return $value;
        }

        $parts = explode('.', $path);
        $category = $parts[0] ?? null;
        $keyName = $parts[1] ?? null;
        $nestedKey = $parts[2] ?? null;

        $settingsTable = TableRegistry::getTableLocator()->get('Settings');

        // Handle three-level nested paths like 'AI.imageGeneration.enabled'
        if ($nestedKey !== null) {
            // For nested paths like AI.imageGeneration.enabled, look for imageGeneration.enabled
            $nestedCategory = $keyName;
            $nestedKeyName = $nestedKey;
            $value = $settingsTable->getSettingValue($nestedCategory, $nestedKeyName);
        } elseif ($keyName === null) {
            // Fetch all settings for the category
            $value = $settingsTable->getSettingValue($category);
        } else {
            // Fetch a single setting
            $value = $settingsTable->getSettingValue($category, $keyName);
        }

        // Cache the result, even if it's null, to avoid repeated database queries
        Cache::write($cacheKey, $value, self::$cacheConfig);

        return $value ?? $default;
    }

    /**
     * Clears the settings cache.
     *
     * Removes all cached settings to ensure fresh data retrieval on subsequent reads.
     *
     * @return void
     */
    public static function clearCache(): void
    {
        Cache::clear(self::$cacheConfig);
    }

    /**
     * Get the cache configuration name. Useful for testcases.
     *
     * @return string
     */
    public static function getCacheConfig(): string
    {
        return self::$cacheConfig;
    }
}
