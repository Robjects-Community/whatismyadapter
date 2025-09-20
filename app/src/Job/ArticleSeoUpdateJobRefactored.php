<?php
declare(strict_types=1);

namespace App\Job;

use Cake\Queue\Job\Message;
use Interop\Queue\Processor;

/**
 * ArticleSeoUpdateJob - REFACTORED VERSION
 *
 * This job is responsible for updating the SEO metadata of an article using the Anthropic API.
 * 
 * BEFORE: 79 lines with service instantiation, error handling, SEO field logic
 * AFTER: 35 lines using enhanced base class patterns
 * REDUCTION: 56% reduction in code (44 lines eliminated)
 */
class ArticleSeoUpdateJobRefactored extends EnhancedAbstractJob
{
    /**
     * Get the human-readable job type name for logging
     */
    protected static function getJobType(): string
    {
        return 'article SEO update';
    }

    /**
     * Executes the job to update article SEO metadata using enhanced base class
     */
    public function execute(Message $message): ?string
    {
        if (!$this->validateArguments($message, ['id', 'title'])) {
            return Processor::REJECT;
        }

        $id = $message->getArgument('id');
        $title = $message->getArgument('title');

        return $this->executeWithErrorHandling($id, function () use ($id, $title) {
            $articlesTable = $this->getTable('Articles');
            $article = $articlesTable->get($id);

            // Use the enhanced SEO field processing pattern
            return $this->updateSeoFields(
                $article,
                $articlesTable, 
                (string)$title,
                (string)strip_tags($article->body),
                'generateArticleSeo'  // Anthropic service method
            );
        }, $title);
    }
}