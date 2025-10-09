<?php
declare(strict_types=1);

// Bootstrap test environment
require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/tests/bootstrap.php';

use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class DebugTest extends TestCase
{
    use IntegrationTestTrait;
    use \App\Test\TestCase\Controller\AuthenticationTestTrait;

    protected array $fixtures = [
        'app.Users',
    ];

    public function testIndexAuthenticated(): void
    {
        // Mock authenticated user
        $this->mockAuthenticatedUser();
        
        // Make the request
        $this->get('/users');
        
        // Get response details
        $statusCode = $this->_response->getStatusCode();
        $body = (string)$this->_response->getBody();
        
        // Output for debugging
        echo "\n\n===== DEBUG INFO =====\n";
        echo "Status Code: $statusCode\n";
        echo "Response Body: $body\n";
        echo "======================\n\n";
        
        // Assertions
        $this->assertResponseOk();
    }
}

// Run the test
$test = new DebugTest('testIndexAuthenticated');
$test->run();
