<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\ProductVerificationJob;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Interop\Queue\Context as InteropContext;
use Interop\Queue\Message as InteropMessage;
use Interop\Queue\Processor;
use ReflectionClass;

class ProductVerificationJobTest extends TestCase
{
    /**
     * Create a mock Interop message
     */
    private function createMockMessage(array $args): Message
    {
        // Create the message body in the expected format for CakePHP Queue
        $messageBody = [
            'class' => ['App\Job\ProductVerificationJob', 'execute'],
            'data' => $args,
        ];

        $interopMessage = $this->createMock(InteropMessage::class);
        $interopMessage->method('getBody')
            ->willReturn(json_encode($messageBody));
        $interopMessage->method('getProperties')
            ->willReturn([]);

        $interopContext = $this->createMock(InteropContext::class);

        return new Message($interopMessage, $interopContext);
    }

    public function testExecuteSuccess(): void
    {
        $productsTable = TableRegistry::getTableLocator()->get('Products');

        // Create a test product
        $product = $productsTable->newEntity([
            'title' => 'Test Product',
            'slug' => 'test-product',
            'description' => 'A comprehensive test product description that meets requirements',
            'manufacturer' => 'Test Manufacturer',
            'model_number' => 'TM-001',
            'user_id' => '1',
            'verification_status' => 'pending',
        ]);
        $savedProduct = $productsTable->save($product);

        // Create message with proper mock
        $message = $this->createMockMessage(['product_id' => $savedProduct->id]);

        // Execute job
        $job = new ProductVerificationJob();
        $result = $job->execute($message);

        $this->assertEquals(Processor::ACK, $result);

        // Verify product was updated
        $updatedProduct = $productsTable->get($savedProduct->id);
        $this->assertGreaterThan(0, $updatedProduct->reliability_score);
        // The status should be either 'approved' or 'pending' depending on the score
        $this->assertContains($updatedProduct->verification_status, ['approved', 'pending']);
    }

    public function testExecuteWithMissingArguments(): void
    {
        // Create message with no product_id
        $message = $this->createMockMessage([]);

        $job = new ProductVerificationJob();
        $result = $job->execute($message);

        $this->assertEquals(Processor::REJECT, $result);
    }

    public function testGetJobType(): void
    {
        // Use reflection to access the protected method
        $reflection = new ReflectionClass(ProductVerificationJob::class);
        $method = $reflection->getMethod('getJobType');
        $method->setAccessible(true);

        $job = new ProductVerificationJob();
        $result = $method->invoke($job);

        $this->assertEquals('Product Verification', $result);
    }
}
