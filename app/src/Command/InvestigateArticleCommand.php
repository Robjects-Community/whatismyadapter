<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * InvestigateArticle command to debug translation and SEO issues.
 *
 * This command provides comprehensive debugging information for articles that may have
 * translation or SEO generation issues. It checks multiple data sources to help identify
 * why AI jobs might have failed or not been triggered.
 *
 * ## Usage Examples:
 *
 * ```bash
 * # Basic usage - investigate a specific article
 * bin/cake investigate_article my-article-slug
 *
 * # Using Docker (recommended for development)
 * docker compose exec willowcms bin/cake investigate_article my-article-slug
 * ```
 *
 * ## What This Command Checks:
 *
 * 1. **Article Basic Info**: Verifies the article exists and shows core metadata
 * 2. **Translation Status**: Checks if translations exist in articles_translations table
 * 3. **Translation Logs**: Searches system_logs for translation job activities and errors
 * 4. **SEO Logs**: Searches system_logs for SEO generation job activities and errors
 * 5. **Queue Jobs**: Checks for pending or failed queue jobs related to the article
 *
 * ## Common Issues This Helps Diagnose:
 *
 * - Articles not appearing in other languages (translation missing)
 * - Empty SEO fields (meta_title, meta_description, etc.)
 * - AI jobs that were queued but never completed
 * - Failed API calls to Anthropic or Google Translate
 * - Queue worker not running when article was published
 *
 * ## Prerequisites:
 *
 * - Queue worker should be running: `bin/cake queue worker --verbose`
 * - AI settings must be enabled in admin area
 * - Valid API keys for Anthropic and/or Google Translate
 *
 * @since 1.0.0
 */
class InvestigateArticleCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * Configures the command to accept a required 'slug' argument which identifies
     * the article to investigate. The slug is the URL-friendly identifier used
     * in article URLs (e.g., 'my-article-title' from '/en/articles/my-article-title').
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Investigate article translation and SEO generation issues')
            ->addArgument('slug', [
                'help' => 'The slug of the article to investigate',
                'required' => true,
            ]);

        return $parser;
    }

    /**
     * Execute the investigation command.
     *
     * Performs a comprehensive analysis of an article's AI processing status by:
     * 1. Locating the article by slug
     * 2. Checking for existing translations in all configured locales
     * 3. Reviewing system logs for translation job activity and errors
     * 4. Reviewing system logs for SEO generation job activity and errors
     * 5. Checking queue_jobs table for pending/failed jobs
     *
     * ## Output Sections:
     * - **ARTICLE FOUND**: Basic article metadata and verification
     * - **EXISTING TRANSLATIONS**: Shows translations in articles_translations table
     * - **SYSTEM LOGS (Translation related)**: Recent logs containing translation keywords
     * - **SYSTEM LOGS (SEO related)**: Recent logs containing SEO generation keywords
     * - **PENDING QUEUE JOBS**: Active/failed jobs in the queue system
     *
     * ## Return Codes:
     * - `Command::CODE_SUCCESS` (0): Investigation completed successfully
     * - `Command::CODE_ERROR` (1): Article not found or exception occurred
     *
     * @param \Cake\Console\Arguments $args The command arguments containing the article slug
     * @param \Cake\Console\ConsoleIo $io Console I/O for output formatting and display
     * @return int The exit code indicating success or failure
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $slug = $args->getArgument('slug');

        $io->out("Investigating article with slug: $slug");
        $io->hr();

        try {
            // 1. Find the article by slug
            $articles = TableRegistry::getTableLocator()->get('Articles');
            $article = $articles->find()->where(['slug' => $slug])->first();

            if (!$article) {
                $io->error("Article with slug '$slug' not found");

                return static::CODE_ERROR;
            }

            $io->out('=== ARTICLE FOUND ===');
            $io->out('ID: ' . $article->id);
            $io->out('Title: ' . $article->title);
            $io->out('Slug: ' . $article->slug);
            $io->out('Locale: ' . $article->locale);
            $io->out('Created: ' . $article->created);
            $io->out('Modified: ' . $article->modified);
            $io->out('');

            // 2. Check for translations in articles_translations table
            // Note: Uses TranslateBehavior structure where translations are stored separately
            $translations = TableRegistry::getTableLocator()->get('ArticlesTranslations');
            $existingTranslations = $translations->find()
                ->where(['id' => $article->id])
                ->toArray();

            $io->out('=== EXISTING TRANSLATIONS ===');
            if (empty($existingTranslations)) {
                $io->out('No translations found');
            } else {
                foreach ($existingTranslations as $translation) {
                    $io->out('Locale: ' . $translation->locale);
                    $io->out('Field: ' . $translation->field);
                    $io->out('Content: ' . substr($translation->content, 0, 100) . '...');
                    $io->out('---');
                }
            }
            $io->out('');

            // 3. Search system logs for translation-related activities and errors
            // This includes job queuing, processing, completion, and failure logs
            $systemLogs = TableRegistry::getTableLocator()->get('SystemLogs');
            $translationErrors = $systemLogs->find()
                ->where([
                    'OR' => [
                        'message LIKE' => '%TranslateArticleJob%',
                        'message LIKE' => '%translation%',
                        'message LIKE' => '%' . $article->id . '%',
                    ],
                ])
                ->orderByDesc('created')
                ->limit(10)
                ->toArray();

            $io->out('=== SYSTEM LOGS (Translation related) ===');
            if (empty($translationErrors)) {
                $io->out('No translation-related log entries found');
            } else {
                foreach ($translationErrors as $log) {
                    $io->out('Time: ' . $log->created);
                    $io->out('Level: ' . $log->level);
                    $io->out('Message: ' . $log->message);
                    $io->out('---');
                }
            }
            $io->out('');

            // 4. Search system logs for SEO generation activities and errors
            // Includes ArticleSeoUpdateJob processing and AI-powered SEO content generation
            $seoErrors = $systemLogs->find()
                ->where([
                    'OR' => [
                        'message LIKE' => '%ArticleSeoUpdateJob%',
                        'message LIKE' => '%SEO%',
                        'message LIKE' => '%seo%',
                    ],
                ])
                ->orderByDesc('created')
                ->limit(10)
                ->toArray();

            $io->out('=== SYSTEM LOGS (SEO related) ===');
            if (empty($seoErrors)) {
                $io->out('No SEO-related log entries found');
            } else {
                foreach ($seoErrors as $log) {
                    $io->out('Time: ' . $log->created);
                    $io->out('Level: ' . $log->level);
                    $io->out('Message: ' . $log->message);
                    $io->out('---');
                }
            }
            $io->out('');

            // 5. Check queue_jobs table for pending, processing, or failed jobs
            // This helps identify if jobs are stuck in the queue or failed to process
            $connection = $articles->getConnection();
            $queueJobs = $connection->execute(
                'SELECT * FROM queue_jobs WHERE payload LIKE ? OR payload LIKE ? ORDER BY created DESC LIMIT 10',
                ['%' . $article->id . '%', '%TranslateArticleJob%'],
            )->fetchAll();

            $io->out('=== PENDING QUEUE JOBS ===');
            if (empty($queueJobs)) {
                $io->out('No pending queue jobs found for this article');
            } else {
                foreach ($queueJobs as $job) {
                    $io->out('ID: ' . $job['id']);
                    $io->out('Status: ' . $job['status']);
                    $io->out('Queue: ' . $job['queue']);
                    $io->out('Job Type: ' . $job['job_type']);
                    $io->out('Created: ' . $job['created']);
                    $io->out('Payload excerpt: ' . substr($job['payload'], 0, 200) . '...');
                    $io->out('---');
                }
            }

            return static::CODE_SUCCESS;
        } catch (Exception $e) {
            $io->error('Error: ' . $e->getMessage());
            $io->error('Trace: ' . $e->getTraceAsString());

            return static::CODE_ERROR;
        }
    }
}
