<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\SettingsTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * App\Model\Table\SettingsTable Test Case
 */
class SettingsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\SettingsTable
     */
    protected $SettingsTable;

    /**
     * Fixtures
     *
     * @var array<string>
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
        $this->SettingsTable = $this->getTableLocator()->get('Settings', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->SettingsTable);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @uses \App\Model\Table\SettingsTable::validationDefault()
     */
    public function testValidationDefault(): void
    {
        $validator = new Validator();
        $validator = $this->SettingsTable->validationDefault($validator);

        // Test valid data
        $validData = [
            'category' => 'TestCategory',
            'key_name' => 'TestKey',
            'value_type' => 'numeric',
            'value' => '100',
        ];
        $errors = $validator->validate($validData);
        $this->assertEmpty($errors, 'Validation should pass for valid numeric data');

        $validStringData = [
            'category' => 'TestCategory',
            'key_name' => 'TestKey',
            'value_type' => 'text',
            'value' => 'Test Value',
        ];
        $errors = $validator->validate($validStringData);
        $this->assertEmpty($errors, 'Validation should pass for valid string data');

        $validBoolData = [
            'category' => 'TestCategory',
            'key_name' => 'TestKey',
            'value_type' => 'bool',
            'value' => 1,
        ];
        $errors = $validator->validate($validBoolData);
        $this->assertEmpty($errors, 'Validation should pass for valid boolean data');

        // Test missing required fields
        $missingCategory = $validData;
        unset($missingCategory['category']);
        $errors = $validator->validate($missingCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category is missing');

        $missingKeyName = $validData;
        unset($missingKeyName['key_name']);
        $errors = $validator->validate($missingKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name is missing');

        $missingValue = $validData;
        unset($missingValue['value']);
        $errors = $validator->validate($missingValue);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is missing');

        // Test empty values
        $emptyCategory = $validData;
        $emptyCategory['category'] = '';
        $errors = $validator->validate($emptyCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category is empty');

        $emptyKeyName = $validData;
        $emptyKeyName['key_name'] = '';
        $errors = $validator->validate($emptyKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name is empty');

        $emptyValueNumeric = $validData;
        $emptyValueNumeric['value'] = '';
        $errors = $validator->validate($emptyValueNumeric);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is empty for numeric setting');

        $emptyValueString = $validStringData;
        $emptyValueString['value'] = '';
        $errors = $validator->validate($emptyValueString);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is empty for string setting');

        // Test invalid type
        $invalidType = $validData;
        $invalidType['value_type'] = 'invalid_type';
        $errors = $validator->validate($invalidType);
        $this->assertArrayHasKey('value_type', $errors, 'Validation should fail when value_type is invalid');

        // Test max length for string fields
        $longCategory = $validData;
        $longCategory['category'] = str_repeat('a', 256);
        $errors = $validator->validate($longCategory);
        $this->assertArrayHasKey('category', $errors, 'Validation should fail when category exceeds max length');

        $longKeyName = $validData;
        $longKeyName['key_name'] = str_repeat('a', 256);
        $errors = $validator->validate($longKeyName);
        $this->assertArrayHasKey('key_name', $errors, 'Validation should fail when key_name exceeds max length');

        // Test invalid values for different types
        $invalidNumeric = $validData;
        $invalidNumeric['value'] = 'not_a_number';
        $errors = $validator->validate($invalidNumeric);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is not numeric for numeric type');

        $invalidBool = $validBoolData;
        $invalidBool['value'] = '2';
        $errors = $validator->validate($invalidBool);
        $this->assertArrayHasKey('value', $errors, 'Validation should fail when value is not 0 or 1 for bool type');
    }

    /**
     * Test getSettingValue method
     *
     * @return void
     * @uses \App\Model\Table\SettingsTable::getSettingValue()
     */
    public function testGetSettingValue(): void
    {
        // Test getting all settings for a category
        $imageSizes = $this->SettingsTable->getSettingValue('ImageSizes');
        $this->assertIsArray($imageSizes, 'Should return an array of image sizes');
        $this->assertArrayHasKey('massive', $imageSizes, 'Should contain "massive" key');
        $this->assertEquals('800', $imageSizes['massive'], 'Should have correct value for "massive"');

        // Test getting a specific setting
        $replyEmail = $this->SettingsTable->getSettingValue('Email', 'reply_email');
        $this->assertEquals('noreply@example.com', $replyEmail, 'Should return correct reply email');

        // Test getting a non-existent setting
        $nonExistent = $this->SettingsTable->getSettingValue('NonExistent', 'non_existent_key');
        $this->assertNull($nonExistent, 'Should return null for non-existent setting');

        // Test getting a non-existent category
        $nonExistentCategory = $this->SettingsTable->getSettingValue('NonExistentCategory');
        $this->assertEmpty($nonExistentCategory, 'Should return empty array for non-existent category');
    }
}
