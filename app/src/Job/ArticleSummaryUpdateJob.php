<?php
declare(strict_types=1);

namespace App\Job;

use App\Service\Api\Anthropic\AnthropicApiService;
use App\Service\Api\Anthropic\TextSummaryGenerator;
use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleSummaryUpdateJob
 *
 * This job is responsible for generating and updating the summary of an article using the TextSummaryGenerator.
 * It processes messages from the queue to update the summary field of an article.
 */
class ArticleSummaryUpdateJob extends AbstractJob
{
    /**
     * Instance of the TextSummaryGenerator service.
     *
     * @var \App\Service\Api\Anthropic\TextSummaryGenerator
     */
    private TextSummaryGenerator $summaryGenerator;

    /**
     * Constructor to allow dependency injection for testing
     *
     * @param \App\Service\Api\Anthropic\TextSummaryGenerator|null $summaryGenerator
     */
    public function __construct(?TextSummaryGenerator $summaryGenerator = null)
    {
        if ($summaryGenerator) {
            $this->summaryGenerator = $summaryGenerator;
        }
    }

    /**
     * Get the TextSummaryGenerator instance, creating it if needed
     *
     * @return \App\Service\Api\Anthropic\TextSummaryGenerator
     */
    private function getSummaryGenerator(): TextSummaryGenerator
    {
        if (!isset($this->summaryGenerator)) {
            $this->summaryGenerator = new TextSummaryGenerator(
                new AnthropicApiService(),
                $this->getTable('Aiprompts'),
            );
        }

        return $this->summaryGenerator;
    }

    /**
     * Get the human-readable job type name for logging
     *
     * @return string The job type description
     */
    protected static function getJobType(): string
    {
        return 'article summary update';
    }

    /**
     * Executes the job to generate and update article summary.
     *
     * This method processes the message, retrieves the article, generates a summary
     * using the TextSummaryGenerator, and updates the article with the new summary.
     *
     * @param \Cake\Queue\Job\Message $message The message containing article data.
     * @return string|null Returns Processor::ACK on success, Processor::REJECT on failure.
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        $articlesTable = $this->getTable('Articles');
        $article = $articlesTable->get($id);

        return $this->executeWithErrorHandling($id, function () use ($article, $articlesTable) {
            $summaryResult = $this->getSummaryGenerator()->generateTextSummary(
                'article',
                (string)strip_tags($article->body),
            );

            if (isset($summaryResult['summary'])) {
                if (empty($article->summary)) {
                    $article->summary = $summaryResult['summary'];
                }

                if (empty($article->lede)) {
                    $article->lede = $summaryResult['lede'];
                }

                return $articlesTable->save($article, ['noMessage' => true]);
            }

            return false;
        }, $title);
    }
}
