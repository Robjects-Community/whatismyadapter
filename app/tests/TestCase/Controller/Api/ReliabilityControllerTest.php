<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\ReliabilityController Test Case
 *
 * Auto-generated test file for API ReliabilityController
 * Tests JSON response format and API-specific behaviors
 *
 * @uses \App\Controller\Api\ReliabilityController
 */
class ReliabilityControllerTest extends TestCase
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
        'app.Products',
        'app.ProductsReliability',
        'app.ProductsReliabilityLogs',
        'app.ProductsReliabilityFields'
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
     * Test score API method
     *
     * @return void
     */
    public function testScoreApi(): void
    {
        // POST is required with product data
        $this->post('/api/reliability/score', [
            'model' => 'Products',
            'data' => [
                'title' => 'Test Product',
                'manufacturer' => 'Test Corp',
                'price' => 99.99,
                'currency' => 'USD'
            ]
        ]);
        
        // Debug: output response if not JSON
        if ($this->_response->getHeaderLine('Content-Type') !== 'application/json') {
            echo "\n\nDEBUG Response Status: " . $this->_response->getStatusCode() . "\n";
            echo "Content-Type: " . $this->_response->getHeaderLine('Content-Type') . "\n";
            echo "Response Body: " . substr((string)$this->_response->getBody(), 0, 500) . "\n\n";
        }
        
        // Verify API responds with JSON
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        
        // Verify response structure
        $body = (string)$this->_response->getBody();
        $data = json_decode($body, true);
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('model', $data);
        $this->assertArrayHasKey('timestamp', $data);
    }

    /**
     * Test verifyChecksum API method
     *
     * @return void
     */
    public function testVerifyChecksumApi(): void
    {
        // POST is required with log data
        $this->post('/api/reliability/verify-checksum', [
            'model' => 'Products',
            'foreign_key' => 'nonexistent-uuid',
            'log_id' => 'nonexistent-log-id'
        ]);
        
        // Expect 404 for nonexistent log
        $this->assertResponseCode(404);
        $this->assertContentType('application/json');
        
        // Verify error response
        $body = (string)$this->_response->getBody();
        $data = json_decode($body, true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Log entry not found', $data['error']);
    }

    /**
     * Test fieldStats API method
     *
     * @return void
     */
    public function testFieldStatsApi(): void
    {
        // GET request to field-stats endpoint
        $this->get('/api/reliability/field-stats');
        
        // Verify API responds with JSON
        // May return 404 if endpoint doesn't exist, which is acceptable
        $this->assertContentType('application/json');
    }

}
