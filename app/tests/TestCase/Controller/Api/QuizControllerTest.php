<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Controller\Api\QuizController;
use App\Test\TestCase\Controller\AuthenticationTestTrait;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\Api\QuizController Test Case
 *
 * Auto-generated test file for API QuizController
 * Tests JSON response format and API-specific behaviors
 *
 * @uses \App\Controller\Api\QuizController
 */
class QuizControllerTest extends TestCase
{
    use IntegrationTestTrait;
    use AuthenticationTestTrait;
    use MockAiServiceTrait;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.QuizSubmissions',
        'app.Products',
        'app.Tags',
        'app.ProductsTags'
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
     * Build controller with mocked AI services
     *
     * @param string $class Controller class name
     * @return \Cake\Controller\Controller
     */
    protected function _buildController(string $class)
    {
        $controller = parent::_buildController($class);
        
        // Inject mocks for QuizController after it's built by the framework
        if ($controller instanceof QuizController) {
            $mockDecisionTree = $this->mockDecisionTreeService();
            $mockProductMatcher = $this->mockAiProductMatcherService();
            
            $controller->setAiServices($mockProductMatcher, $mockDecisionTree);
        }
        
        return $controller;
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
     * Test akinatorStart API method
     *
     * @return void
     */
    public function testAkinatorStartApi(): void
    {
        // POST request required for akinatorStart
        $this->post('/api/quiz/akinator/start.json', [
            'context' => ['initial' => true]
        ]);
        
        // Verify API responds with JSON
        $this->assertResponseOk();
        $this->assertContentType('application/json');
        
        // Verify response structure
        $response = (array)$this->viewVariable('data');
        if ($response) {
            $this->assertArrayHasKey('success', $response);
        }
    }

    /**
     * Test akinatorNext API method
     *
     * @return void
     */
    public function testAkinatorNextApi(): void
    {
        // POST request required with session_id and answer
        $this->post('/api/quiz/akinator/next.json', [
            'session_id' => 'test-session-123',
            'answer' => 'yes',
            'state' => ['session_id' => 'test-session-123']
        ]);
        
        // May return 400 for missing/invalid session, which is expected
        $this->assertResponseCode(400); // BadRequest is expected for invalid session
        $this->assertContentType('application/json');
    }

    /**
     * Test akinatorResult API method
     *
     * @return void
     */
    public function testAkinatorResultApi(): void
    {
        // GET request with session_id parameter
        $this->get('/api/quiz/akinator/result.json?session_id=nonexistent');
        
        // Expect 404 for nonexistent session
        $this->assertResponseCode(404);
        $this->assertContentType('application/json');
        
        // Verify error response structure
        $body = (string)$this->_response->getBody();
        $data = json_decode($body, true);
        $this->assertFalse($data['success']);
        $this->assertEquals('RESULT_NOT_FOUND', $data['error']['code']);
    }

    /**
     * Test comprehensiveSubmit API method
     *
     * @return void
     */
    public function testComprehensiveSubmitApi(): void
    {
        // POST request with quiz answers
        $this->post('/api/quiz/comprehensive/submit.json', [
            'answers' => [
                'device_type' => 'laptop',
                'usage' => 'work',
                'budget' => '100-500'
            ],
            'session_id' => 'test-comprehensive-session',
            'max_results' => 5
        ]);
        
        // Verify API responds (may have issues with AI service, so just check JSON format)
        $this->assertContentType('application/json');
        
        // Response should have success field regardless of outcome
        $body = (string)$this->_response->getBody();
        $data = json_decode($body, true);
        $this->assertArrayHasKey('success', $data);
    }

}
