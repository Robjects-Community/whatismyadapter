<?php
declare(strict_types=1);

namespace App\Job;

use App\Model\Entity\Comment;
use App\Service\Api\Anthropic\AnthropicApiService;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * CommentAnalysisJob Class
 *
 * This job is responsible for analyzing comments using the Anthropic API service.
 * It processes comments from the queue, performs analysis, and updates the comment status
 * based on the analysis results.
 */
class CommentAnalysisJob extends AbstractJob
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
        return 'comment analysis';
    }

    /**
     * Executes the comment analysis job.
     *
     * This method performs comment analysis using the Anthropic API service,
     * updating the comment status based on the analysis results.
     *
     * @param \Cake\Queue\Job\Message $message The message containing job data.
     * @return string|null The processor status (ACK or REJECT).
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['comment_id', 'content', 'user_id'])) {
            return Processor::REJECT;
        }

        $commentId = $message->getArgument('comment_id');
        $content = $message->getArgument('content');
        $userId = $message->getArgument('user_id');

        $commentsTable = $this->getTable('Comments');
        $comment = $commentsTable->get($commentId);

        if ($comment->is_analyzed) {
            $this->log(
                sprintf('Comment already analyzed. Skipping. Comment ID: %s', $commentId),
                'info',
                ['group_name' => static::class],
            );

            return Processor::ACK;
        }

        return $this->executeWithErrorHandling($commentId, function () use ($comment, $content) {
            $analysisResult = $this->anthropicService->analyzeComment($content);

            if ($analysisResult) {
                $this->updateCommentStatus($comment, $analysisResult);

                return true;
            }

            return false;
        }, "User ID: {$userId}");
    }

    /**
     * Updates the comment status based on the analysis result.
     *
     * This method extracts the inappropriateness status and reason from the analysis result
     * and updates the comment entity with the analysis results.
     *
     * @param \App\Model\Entity\Comment $comment The comment entity to update.
     * @param array $analysisResult The result of the comment analysis.
     * @return void
     */
    private function updateCommentStatus(Comment $comment, array $analysisResult): void
    {
        $isInappropriate = $analysisResult['is_inappropriate'] ?? false;
        $reason = $analysisResult['reason'] ?? [];

        $comment->setAccess('is_inappropriate', true);
        $comment->is_analyzed = true;
        $comment->display = !$isInappropriate;
        $comment->is_inappropriate = $isInappropriate;
        $comment->inappropriate_reason = $isInappropriate ? json_encode($reason) : null;

        $commentsTable = $this->getTable('Comments');
        $commentsTable->save($comment);
    }
}
