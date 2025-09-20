<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\ProductAnalyzer;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class ProductSeoUpdateJob extends AbstractJob
{
    /**
     * Maximum number of attempts to process the job
     */
    public static int $maxAttempts = 3;

    /**
     * Whether there should be only one instance of a job on the queue at a time
     */
    public static bool $shouldBeUnique = true;

    /**
     * Get the human-readable job type name for logging
     */
    protected static function getJobType(): string
    {
        return 'Product SEO Update';
    }

    /**
     * Execute the job with the given message
     */
    public function execute(Message $message): ?string
    {
        // Validate required arguments
        if (!$this->validateArguments($message, ['product_id'])) {
            return Processor::REJECT;
        }

        $productId = $message->getArgument('product_id');

        return $this->executeWithEntitySave($productId, function () use ($productId) {

            // Get product data
            $productsTable = $this->getTable('Products');
            $product = $productsTable->get($productId, [
                'contain' => ['Tags'],
            ]);

            // Generate SEO content using AI
            $productAnalyzer = new ProductAnalyzer();
            $seoResult = $productAnalyzer->generateProductSEO([
                'title' => $product->title,
                'manufacturer' => $product->manufacturer,
                'model_number' => $product->model_number,
                'description' => $product->description,
                'tags' => array_map(fn($tag) => $tag->title, $product->tags ?? []),
            ]);

            if (!$seoResult['success']) {
                throw new Exception($seoResult['error'] ?? 'SEO generation failed');
            }

            // Update product with SEO data
            $seoData = $seoResult['data'];
            $product->meta_title = $seoData['meta_title'] ?: $product->title;
            $product->meta_description = $seoData['meta_description'];
            $product->meta_keywords = $seoData['meta_keywords'];

            return $product; // Will be saved by executeWithEntitySave
        }, $product->title ?? 'Product');
    }
}
