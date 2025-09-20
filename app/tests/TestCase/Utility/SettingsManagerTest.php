<?php
declare(strict_types=1);

namespace App\Test\TestCase\Utility;

use App\Utility\SettingsManager;
use Cake\Cache\Cache;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class SettingsManagerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = ['app.Settings'];

    public function setUp(): void
    {
        parent::setUp();
        Cache::clear(SettingsManager::getCacheConfig());
    }

    public function tearDown(): void
    {
        parent::tearDown();
        Cache::clear(SettingsManager::getCacheConfig());
    }

    public function testReadAIEnabled(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AIEnabledTest',
            'key_name' => 'enabled',
            'value' => 1,
            'value_type' => 'bool',
        ]));

        $result = SettingsManager::read('AIEnabledTest.enabled');
        $this->assertIsBool($result);
        $this->assertTrue($result);

        $settingsTable->updateAll(['value' => 'false'], ['category' => 'AIEnabledTest', 'key_name' => 'enabled']);
        Cache::clear(SettingsManager::getCacheConfig());
        $result = SettingsManager::read('AIEnabledTest.enabled');
        $this->assertFalse($result);
    }

    public function testReadAIAnthropicApiKey(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AIKeyTest',
            'key_name' => 'anthropicApiKey',
            'value' => 'test_api_key_here',
            'value_type' => 'text',
        ]));

        $result = SettingsManager::read('AIKeyTest.anthropicApiKey');
        $this->assertIsString($result);
        $this->assertEquals('test_api_key_here', $result);
    }

    public function testReadAICategory(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AITest',
            'key_name' => 'enabled',
            'value' => 1,
            'value_type' => 'bool',
        ]));
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'AITest',
            'key_name' => 'anthropicApiKey',
            'value' => 'test_api_key',
            'value_type' => 'text',
        ]));

        $result = SettingsManager::read('AITest');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('enabled', $result);
        $this->assertArrayHasKey('anthropicApiKey', $result);
        $this->assertTrue($result['enabled']);
        $this->assertEquals('test_api_key', $result['anthropicApiKey']);
    }

    public function testReadImageSizes(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $imageSizes = [
            'extra-large' => 500,
            'medium' => 300,
            'large' => 400,
        ];

        foreach ($imageSizes as $key => $value) {
            $settingsTable->saveOrFail($settingsTable->newEntity([
                'category' => 'ImageSizes',
                'key_name' => $key,
                'value' => (string)$value,
                'value_type' => 'numeric',
            ]));
        }

        $result = SettingsManager::read('ImageSizes');

        $this->assertIsArray($result);
        foreach ($imageSizes as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            $this->assertIsInt($result[$key]);
            $this->assertEquals($value, $result[$key]);
        }
    }

    public function testReadAIBadKey(): void
    {
        $result = SettingsManager::read('AI.bad_key');
        $this->assertNull($result);
    }

    public function testReadNonExistentSetting(): void
    {
        $result = SettingsManager::read('NonExistent.Setting', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testCaching(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settingsTable->saveOrFail($settingsTable->newEntity([
            'category' => 'Test',
            'key_name' => 'cached_value',
            'value' => 'initial',
            'value_type' => 'text',
        ]));

        $initialResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('initial', $initialResult);

        $settingsTable->updateAll(['value' => 'updated'], ['category' => 'Test', 'key_name' => 'cached_value']);

        $cachedResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('initial', $cachedResult, 'Should return cached value');

        SettingsManager::clearCache();
        $refreshedResult = SettingsManager::read('Test.cached_value');
        $this->assertEquals('updated', $refreshedResult, 'Should return updated value after cache clear');
    }

    public function testReadWithDifferentTypes(): void
    {
        $settingsTable = TableRegistry::getTableLocator()->get('Settings');
        $settings = [
            ['category' => 'Types', 'key_name' => 'string_value', 'value' => 'test', 'value_type' => 'text'],
            ['category' => 'Types', 'key_name' => 'int_value', 'value' => '42', 'value_type' => 'numeric'],
            ['category' => 'Types', 'key_name' => 'bool_value', 'value' => 1, 'value_type' => 'bool'],
        ];

        foreach ($settings as $setting) {
            $settingsTable->saveOrFail($settingsTable->newEntity($setting));
        }

        $this->assertIsString(SettingsManager::read('Types.string_value'));
        $this->assertEquals('test', SettingsManager::read('Types.string_value'));

        $this->assertIsInt(SettingsManager::read('Types.int_value'));
        $this->assertEquals(42, SettingsManager::read('Types.int_value'));

        $this->assertIsBool(SettingsManager::read('Types.bool_value'));
        $this->assertTrue(SettingsManager::read('Types.bool_value'));
    }
}
