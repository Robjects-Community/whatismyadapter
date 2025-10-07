<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\AiFormSuggestionsController Test Case
 *
 * Auto-generated test file for API AiFormSuggestionsController
 * Tests JSON response format and API-specific behaviors
 *
 * @uses \App\Controller\Api\AiFormSuggestionsController
 */
class AiFormSuggestionsControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticationTestTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.ProductFormFields'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->configRequest([
            'headers' => ['Accept' => 'application/json']
        ]);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }


    /**
     * Test index API method (unauthenticated - should fail)
     *
     * @return void
     */
    public function testIndexApiUnauthenticated(): void
    {
        // POST is required, GET should fail
        $this->post('/api/ai-form-suggestions', []);
        $this->assertResponseError();
    }

    /**
     * Test index API method without field_name (should fail with 400)
     *
     * @return void
     */
    public function testIndexApiMissingFieldName(): void
    {
        $this->mockAdminUser();
        $this->post('/api/ai-form-suggestions', []);
        $this->assertResponseCode(400);
        $responseData = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    /**
     * Test index API method with valid field_name
     *
     * @return void
     */
    public function testIndexApiWithValidFieldName(): void
    {
        $this->mockAdminUser();
        $data = [
            'field_name' => 'Lorem ipsum dolor sit amet',
            'existing_data' => [
                'title' => 'Test Product',
                'manufacturer' => 'Test Manufacturer',
            ],
        ];
        $this->post('/api/ai-form-suggestions', $data);
        $this->assertResponseOk();
        $this->assertJsonResponse();
        $responseData = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('success', $responseData);
    }

    /**
     * Test index API method with non-existent field
     *
     * @return void
     */
    public function testIndexApiWithNonExistentField(): void
    {
        $this->mockAdminUser();
        $data = [
            'field_name' => 'non_existent_field',
            'existing_data' => [],
        ];
        $this->post('/api/ai-form-suggestions', $data);
        $this->assertResponseOk();
        $this->assertJsonResponse();
        $responseData = json_decode((string)$this->_response->getBody(), true);
        $this->assertArrayHasKey('success', $responseData);
    }

}
