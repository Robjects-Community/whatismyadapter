<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\SettingsTable Test Case
 *
 * Comprehensive test suite for SettingsTable including:
 * - Initialization and configuration tests
 * - Validation rules (category, key_name, value_type, value)
 * - getSettingValue() method for retrieving settings
 * - Value type casting (bool, numeric, string)
 * - CRUD operations
 */
class SettingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    protected $Settings;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Settings',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Settings') ? [] : ['className' => SettingsTable::class];
        $this->Settings = $this->getTableLocator()->get('Settings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Settings);

        parent::tearDown();
    }

    // ============================================================
    // Initialization Tests
    // ============================================================

    /**
     * Test initialize method sets up table correctly
     *
     * @return void
     */
    public function testInitialize(): void
    {
        $this->assertEquals('settings', $this->Settings->getTable());
        $this->assertEquals('category', $this->Settings->getDisplayField());
        $this->assertEquals('id', $this->Settings->getPrimaryKey());
        
        // Test behaviors are attached
        $this->assertTrue($this->Settings->hasBehavior('Timestamp'));
    }

    /**
     * Test table configuration
     *
     * @return void
     */
    public function testTableConfiguration(): void
    {
        $this->assertEquals('settings', $this->Settings->getTable());
        $this->assertEquals('category', $this->Settings->getDisplayField());
        $this->assertEquals('id', $this->Settings->getPrimaryKey());
    }

    // ============================================================
    // Validation Tests - Default
    // ============================================================

    /**
     * Test validationDefault with valid data
     *
     * @return void
     */
    public function testValidationDefaultSuccess(): void
    {
        $data = [
            'category' => 'System',
            'key_name' => 'site_name',
            'value' => 'WillowCMS',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertEmpty($setting->getErrors(), 'Expected no validation errors');
    }

    /**
     * Test validationDefault requires category
     *
     * @return void
     */
    public function testValidationCategoryRequired(): void
    {
        $data = [
            'key_name' => 'test_key',
            'value' => 'test_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('category'));
        $this->assertArrayHasKey('_required', $setting->getError('category'));
    }

    /**
     * Test validationDefault category max length
     *
     * @return void
     */
    public function testValidationCategoryMaxLength(): void
    {
        $data = [
            'category' => str_repeat('a', 256), // Exceeds 255 char limit
            'key_name' => 'test_key',
            'value' => 'test_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('category'));
        $this->assertArrayHasKey('maxLength', $setting->getError('category'));
    }

    /**
     * Test validationDefault requires key_name
     *
     * @return void
     */
    public function testValidationKeyNameRequired(): void
    {
        $data = [
            'category' => 'System',
            'value' => 'test_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('key_name'));
        $this->assertArrayHasKey('_required', $setting->getError('key_name'));
    }

    /**
     * Test validationDefault key_name max length
     *
     * @return void
     */
    public function testValidationKeyNameMaxLength(): void
    {
        $data = [
            'category' => 'System',
            'key_name' => str_repeat('a', 256), // Exceeds 255 char limit
            'value' => 'test_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('key_name'));
        $this->assertArrayHasKey('maxLength', $setting->getError('key_name'));
    }

    /**
     * Test validationDefault requires value_type
     *
     * @return void
     */
    public function testValidationValueTypeRequired(): void
    {
        $data = [
            'category' => 'System',
            'key_name' => 'test_key',
            'value' => 'test_value',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value_type'));
        $this->assertArrayHasKey('_required', $setting->getError('value_type'));
    }

    /**
     * Test validationDefault value_type must be in allowed list
     *
     * @return void
     */
    public function testValidationValueTypeInList(): void
    {
        // Test valid types pass
        $validTypes = ['text', 'numeric', 'bool', 'textarea', 'select', 'select-page'];
        
        foreach ($validTypes as $type) {
            $data = [
                'category' => 'System',
                'key_name' => 'test_key_' . $type,
                'value' => ($type === 'bool') ? '1' : 'test_value',
                'value_type' => $type,
            ];
            
            $setting = $this->Settings->newEntity($data);
            $this->assertEmpty($setting->getError('value_type'), "Type {$type} should be valid");
        }
        
        // Test invalid type fails
        $data = [
            'category' => 'System',
            'key_name' => 'test_key',
            'value' => 'test_value',
            'value_type' => 'invalid_type',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value_type'));
        $this->assertArrayHasKey('inList', $setting->getError('value_type'));
    }

    /**
     * Test validationDefault requires value
     *
     * @return void
     */
    public function testValidationValueRequired(): void
    {
        $data = [
            'category' => 'System',
            'key_name' => 'test_key',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value'));
    }

    /**
     * Test validationDefault custom numeric validation
     *
     * @return void
     */
    public function testValidationValueCustomNumeric(): void
    {
        // Valid numeric value should pass
        $data = [
            'category' => 'System',
            'key_name' => 'max_items',
            'value' => '100',
            'value_type' => 'numeric',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertEmpty($setting->getError('value'), 'Valid numeric value should pass');
        
        // Non-numeric value should fail
        $data['value'] = 'not_a_number';
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value'));
    }

    /**
     * Test validationDefault custom bool validation
     *
     * @return void
     */
    public function testValidationValueCustomBool(): void
    {
        // Valid bool values (0 or 1) should pass
        $validValues = [0, 1];
        
        foreach ($validValues as $value) {
            $data = [
                'category' => 'System',
                'key_name' => 'is_enabled',
                'value' => $value,
                'value_type' => 'bool',
            ];
            
            $setting = $this->Settings->newEntity($data);
            $this->assertEmpty($setting->getError('value'), "Bool value {$value} should be valid");
        }
        
        // Invalid bool value should fail
        $data = [
            'category' => 'System',
            'key_name' => 'is_enabled',
            'value' => 2,
            'value_type' => 'bool',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value'));
    }

    /**
     * Test validationDefault custom text validation
     *
     * @return void
     */
    public function testValidationValueCustomText(): void
    {
        // Valid text value should pass
        $data = [
            'category' => 'System',
            'key_name' => 'site_title',
            'value' => 'My Site',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->assertEmpty($setting->getError('value'), 'Valid text value should pass');
        
        // Empty text value should fail
        $data['value'] = '';
        $setting = $this->Settings->newEntity($data);
        $this->assertNotEmpty($setting->getError('value'));
    }

    /**
     * Test validation for all value types
     *
     * @return void
     */
    public function testValidationAllValueTypes(): void
    {
        $testCases = [
            ['type' => 'text', 'value' => 'Sample text'],
            ['type' => 'numeric', 'value' => '42'],
            ['type' => 'bool', 'value' => 1],
            ['type' => 'textarea', 'value' => 'Long text content'],
            ['type' => 'select', 'value' => 'option1'],
            ['type' => 'select-page', 'value' => 'page-slug'],
        ];
        
        foreach ($testCases as $testCase) {
            $data = [
                'category' => 'System',
                'key_name' => 'test_' . $testCase['type'],
                'value' => $testCase['value'],
                'value_type' => $testCase['type'],
            ];
            
            $setting = $this->Settings->newEntity($data);
            $this->assertEmpty($setting->getErrors(), "Type {$testCase['type']} should be valid");
        }
    }

    // ============================================================
    // getSettingValue() Method Tests
    // ============================================================

    /**
     * Test getSettingValue retrieves single setting with type casting
     *
     * @return void
     */
    public function testGetSettingValueSingleSetting(): void
    {
        // Create test setting
        $data = [
            'category' => 'TestCategory',
            'key_name' => 'test_setting',
            'value' => '123',
            'value_type' => 'numeric',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->Settings->save($setting);
        
        // Retrieve setting
        $result = $this->Settings->getSettingValue('TestCategory', 'test_setting');
        
        $this->assertIsInt($result, 'Numeric value should be cast to int');
        $this->assertEquals(123, $result);
    }

    /**
     * Test getSettingValue retrieves all settings for a category
     *
     * @return void
     */
    public function testGetSettingValueAllCategorySettings(): void
    {
        // Create multiple test settings in same category
        $settings = [
            ['category' => 'AppSettings', 'key_name' => 'setting1', 'value' => 'value1', 'value_type' => 'text'],
            ['category' => 'AppSettings', 'key_name' => 'setting2', 'value' => '42', 'value_type' => 'numeric'],
            ['category' => 'AppSettings', 'key_name' => 'setting3', 'value' => 1, 'value_type' => 'bool'],
        ];
        
        foreach ($settings as $data) {
            $setting = $this->Settings->newEntity($data);
            $this->Settings->save($setting);
        }
        
        // Retrieve all settings for category
        $result = $this->Settings->getSettingValue('AppSettings');
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('setting1', $result);
        $this->assertArrayHasKey('setting2', $result);
        $this->assertArrayHasKey('setting3', $result);
        $this->assertEquals('value1', $result['setting1']);
        $this->assertEquals(42, $result['setting2']);
        $this->assertTrue($result['setting3']);
    }

    /**
     * Test getSettingValue returns null for non-existent setting
     *
     * @return void
     */
    public function testGetSettingValueNonExistent(): void
    {
        $result = $this->Settings->getSettingValue('NonExistentCategory', 'non_existent_key');
        
        $this->assertNull($result);
    }

    /**
     * Test getSettingValue returns empty array for non-existent category
     *
     * @return void
     */
    public function testGetSettingValueEmptyCategory(): void
    {
        $result = $this->Settings->getSettingValue('EmptyCategory');
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    // ============================================================
    // Value Casting Tests
    // ============================================================

    /**
     * Test boolean true value casting
     *
     * @return void
     */
    public function testCastValueBooleanTrue(): void
    {
        // Create setting with bool type
        $data = [
            'category' => 'BoolTest',
            'key_name' => 'enabled',
            'value' => 1,
            'value_type' => 'bool',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->Settings->save($setting);
        
        $result = $this->Settings->getSettingValue('BoolTest', 'enabled');
        
        $this->assertIsBool($result);
        $this->assertTrue($result);
    }

    /**
     * Test boolean false value casting
     *
     * @return void
     */
    public function testCastValueBooleanFalse(): void
    {
        // Create setting with bool type
        $data = [
            'category' => 'BoolTest',
            'key_name' => 'disabled',
            'value' => 0,
            'value_type' => 'bool',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->Settings->save($setting);
        
        $result = $this->Settings->getSettingValue('BoolTest', 'disabled');
        
        $this->assertIsBool($result);
        $this->assertFalse($result);
    }

    /**
     * Test numeric value casting
     *
     * @return void
     */
    public function testCastValueNumeric(): void
    {
        // Create setting with numeric type
        $data = [
            'category' => 'NumericTest',
            'key_name' => 'count',
            'value' => '42',
            'value_type' => 'numeric',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $this->Settings->save($setting);
        
        $result = $this->Settings->getSettingValue('NumericTest', 'count');
        
        $this->assertIsInt($result);
        $this->assertEquals(42, $result);
    }

    // ============================================================
    // CRUD Operation Tests
    // ============================================================

    /**
     * Test successful setting creation
     *
     * @return void
     */
    public function testCreateSettingSuccess(): void
    {
        $data = [
            'category' => 'NewCategory',
            'key_name' => 'new_setting',
            'value' => 'new_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $result = $this->Settings->save($setting);
        
        $this->assertNotFalse($result);
        $this->assertNotEmpty($result->id);
        $this->assertEquals('NewCategory', $result->category);
        $this->assertEquals('new_setting', $result->key_name);
        $this->assertEquals('new_value', $result->value);
    }

    /**
     * Test updating existing setting
     *
     * @return void
     */
    public function testUpdateSettingSuccess(): void
    {
        // Create initial setting
        $data = [
            'category' => 'UpdateTest',
            'key_name' => 'updateable',
            'value' => 'original_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $saved = $this->Settings->save($setting);
        
        // Update the setting
        $setting = $this->Settings->patchEntity($saved, [
            'value' => 'updated_value',
        ]);
        
        $result = $this->Settings->save($setting);
        
        $this->assertNotFalse($result);
        $this->assertEquals('updated_value', $result->value);
    }

    /**
     * Test deleting setting
     *
     * @return void
     */
    public function testDeleteSettingSuccess(): void
    {
        // Create setting
        $data = [
            'category' => 'DeleteTest',
            'key_name' => 'deleteable',
            'value' => 'temp_value',
            'value_type' => 'text',
        ];
        
        $setting = $this->Settings->newEntity($data);
        $saved = $this->Settings->save($setting);
        
        // Delete the setting
        $result = $this->Settings->delete($saved);
        
        $this->assertTrue($result);
        
        // Verify setting no longer exists
        $exists = $this->Settings->exists(['id' => $saved->id]);
        $this->assertFalse($exists);
    }
}
