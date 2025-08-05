<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\ProductAnalyzer;
use App\Utility\SettingsManager;
use Cake\Queue\Job\Message;
use Exception;
use Interop\Queue\Processor;

class ProductVerificationJob extends AbstractJob
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
        return 'Product Verification';
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
        $verifierUserId = $message->getArgument('verifier_user_id');
        $useAI = $message->getArgument('use_ai') ?? true;

        return $this->executeWithErrorHandling($productId, function () use ($productId, $verifierUserId, $useAI) {

            // Get product data
            $productsTable = $this->getTable('Products');
            $product = $productsTable->get($productId, [
                'contain' => ['Tags', 'Articles'],
            ]);

            $verificationScore = $this->calculateVerificationScore($product);

            // Use AI verification if enabled
            if ($useAI && SettingsManager::read('Products.aiVerificationEnabled', true)) {
                $aiScore = $this->runAIVerification($product);
                $verificationScore = ($verificationScore + $aiScore) / 2;
            }

            // Update product with verification score
            $product->reliability_score = $verificationScore;

            // Auto-publish if score is high enough
            $autoPublishThreshold = (float)SettingsManager::read('Products.autoPublishThreshold', 4.0);
            if ($verificationScore >= $autoPublishThreshold) {
                $product->is_published = true;
                $product->verification_status = 'approved';
            } else {
                $product->verification_status = 'pending';
            }

            // Save the updated product
            if (!$productsTable->save($product)) {
                throw new Exception('Failed to save product verification results');
            }

            return true; // Success
        }, $product->title ?? 'Product');
    }

    /**
     * Calculate basic verification score based on completeness
     */
    private function calculateVerificationScore($product): float
    {
        $score = 0;
        $maxScore = 0;

        // Title is required (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product->title)) {
            $score += 1.0;
        }

        // Description (weight: 1.5)
        $maxScore += 1.5;
        if (!empty($product->description) && strlen($product->description) >= 50) {
            $score += 1.5;
        } elseif (!empty($product->description)) {
            $score += 0.75;
        }

        // Manufacturer (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product->manufacturer)) {
            $score += 1.0;
        }

        // Model number (weight: 0.5)
        $maxScore += 0.5;
        if (!empty($product->model_number)) {
            $score += 0.5;
        }

        // Image (weight: 1.0)
        $maxScore += 1.0;
        if (!empty($product->image)) {
            $score += 1.0;
        }

        // Tags (weight: 0.5)
        $maxScore += 0.5;
        if (!empty($product->tags) && count($product->tags) > 0) {
            $score += 0.5;
        }

        // Convert to 5-point scale
        return $score / $maxScore * 5.0;
    }

    /**
     * Run AI verification if service is available
     */
    private function runAIVerification($product): float
    {
        try {
            $productAnalyzer = new ProductAnalyzer();
            $aiAnalysis = $productAnalyzer->analyzeProduct([
                'title' => $product->title,
                'manufacturer' => $product->manufacturer,
                'model_number' => $product->model_number,
                'description' => $product->description,
                'tags' => array_map(fn($tag) => $tag->title, $product->tags ?? []),
                'price' => $product->price,
            ]);

            if ($aiAnalysis['success'] && isset($aiAnalysis['data']['overall_score'])) {
                // Convert AI score (0-100) to our 5-point scale
                return $aiAnalysis['data']['overall_score'] / 100 * 5.0;
            }

            return 3.0; // Neutral score if AI analysis fails
        } catch (Exception $e) {
            return 3.0; // Fallback score
        }
    }
}
