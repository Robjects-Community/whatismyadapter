<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ImageAnalysisJob
 *
 * This job is responsible for analyzing images using the Anthropic API.
 * It processes messages from the queue to analyze images and update their metadata.
 */
class ImageAnalysisJob extends AbstractJob
{
    /**
     * Instance of the Anthropic API service.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\AnthropicApiService|null $anthropicService
     */
    public function __construct(?AnthropicApiService $anthropicService = null)
    {
        $this->anthropicService = $anthropicService ?? new AnthropicApiService();
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'image analysis';
    }

    /**
     * Executes the job to analyze an image and update its metadata.
     *
     * @param \Cake\Queue\Job\Message $message The message containing image data
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure
     */
    public function execute(Message $message): ?string
    {
        $folderPath = $message->getArgument('folder_path');
        $file = $message->getArgument('file');
        $id = $message->getArgument('id');
        $model = $message->getArgument('model');

        if (!$this->validateArguments($message, ['folder_path', 'file', 'id', 'model'])) {
            return Processor::REJECT;
        }

        return $this->executeWithErrorHandling($id, function () use ($folderPath, $file, $id, $model) {
            $modelTable = $this->getTable($model);
            $image = $modelTable->get($id);

            $analysisResult = $this->anthropicService->analyzeImage($folderPath . $file);

            if ($analysisResult) {
                $image->name = $analysisResult['name'];
                $image->alt_text = $analysisResult['alt_text'];
                $image->keywords = $analysisResult['keywords'];

                return $modelTable->save($image);
            }

            return false;
        }, "{$model}:{$file}");
    }
}
