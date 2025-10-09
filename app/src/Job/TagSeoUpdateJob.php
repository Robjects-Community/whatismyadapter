<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * TagSeoUpdateJob
 *
 * This job is responsible for updating SEO-related information for tags using the Anthropic API.
 * It processes queued messages, retrieves tag information, generates SEO content, and updates the tag record.
 */
class TagSeoUpdateJob extends AbstractJob
{
    /**
     * The Anthropic API service used for generating SEO content.
     *
     * @var \App\Service\Api\AnthropicApiService
     */
    private AnthropicApiService $anthropicService;

    /**
     * Constructor to allow dependency injection for testing.
     *
     * @param \App\Service\Api\Anthropic\AnthropicApiService|null $anthropicService The Anthropic API service instance.
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
        return 'tag SEO update';
    }

    /**
     * Executes the job to update tag SEO information.
     *
     * This method processes the queued message, retrieves the tag, generates SEO content using the Anthropic API,
     * and updates the tag record with the new SEO information.
     *
     * @param \Cake\Queue\Job\Message $message The message containing the job data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $tagsTable = $this->getTable('Tags');
        $tag = $tagsTable->get($id);

        return $this->executeWithErrorHandling($id, function () use ($tag, $tagsTable, $title) {
            $seoResult = $this->anthropicService->generateTagSeo(
                (string)$title,
                (string)$tag->description,
            );

            if ($seoResult) {
                $emptyFields = $tagsTable->emptySeoFields($tag);
                array_map(fn($field) => $tag->{$field} = $seoResult[$field], $emptyFields);

                return $tagsTable->save($tag, ['noMessage' => true]);
            }

            return false;
        }, $title);
    }
}
