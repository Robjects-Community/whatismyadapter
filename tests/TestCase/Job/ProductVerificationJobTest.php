<?php
declare(strict_types=1);

namespace App\Test\TestCase\Job;

use App\Job\ProductVerificationJob;
use Cake\ORM\TableRegistry;
use Cake\Queue\Job\Message;
use Cake\TestSuite\TestCase;
use Queue\Queue\Processor;

class ProductVerificationJobTest extends TestCase
{
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

        // Create message
        $message = new Message(['product_id' => $savedProduct->id], []);

        // Execute job
        $job = new ProductVerificationJob();
        $result = $job->execute($message);

        $this->assertEquals(Processor::ACK, $result);

        // Verify product was updated
        $updatedProduct = $productsTable->get($savedProduct->id);
        $this->assertGreaterThan(0, $updatedProduct->reliability_score);
        $this->assertNotEquals('pending', $updatedProduct->verification_status);
    }

    public function testExecuteWithMissingArguments(): void
    {
        $message = new Message([], []); // No product_id

        $job = new ProductVerificationJob();
        $result = $job->execute($message);

        $this->assertEquals(Processor::REJECT, $result);
    }

    public function testGetJobType(): void
    {
        $this->assertEquals('Product Verification', ProductVerificationJob::getJobType());
    }
}
