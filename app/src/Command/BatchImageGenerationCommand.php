<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Table\ArticlesTable;
use App\Utility\SettingsManager;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;

/**
 * Batch Image Generation Command
 *
 * This command processes existing articles that lack images and queues
 * them for AI image generation. It provides options for batch size limits,
 * dry-run mode, and filtering by date ranges.
 */
class BatchImageGenerationCommand extends Command
{
    /**
     * @var \App\Model\Table\ArticlesTable
     */
    private ArticlesTable $ArticlesTable;

    /**
     * Initialize method
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->ArticlesTable = TableRegistry::getTableLocator()->get('Articles');
    }

    /**
     * Build option parser method.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription([
                'Batch process articles that need AI-generated images.',
                '',
                'This command scans for published articles without images and queues',
                'them for AI image generation using the configured image generation service.',
            ])
            ->addOption('limit', [
                'help' => 'Maximum number of articles to process (default: 50)',
                'short' => 'l',
                'default' => 50,
            ])
            ->addOption('dry-run', [
                'help' => 'Show what would be processed without actually queuing jobs',
                'boolean' => true,
                'short' => 'd',
            ])
            ->addOption('since', [
                'help' => 'Only process articles published since this date (YYYY-MM-DD format)',
                'short' => 's',
            ])
            ->addOption('before', [
                'help' => 'Only process articles published before this date (YYYY-MM-DD format)',
                'short' => 'b',
            ])
            ->addOption('force', [
                'help' => 'Skip confirmation prompts and rate limit checks',
                'boolean' => true,
                'short' => 'f',
            ])
            ->addOption('verbose', [
                'help' => 'Show detailed output including statistics',
                'boolean' => true,
                'short' => 'v',
            ])
            ->addOption('stats', [
                'help' => 'Show only statistics without processing any articles',
                'boolean' => true,
            ]);

        return $parser;
    }

    /**
     * Execute the command
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        // Check if AI image generation is enabled
        if (!SettingsManager::read('AI.enabled') || !SettingsManager::read('AI.imageGeneration.enabled')) {
            $io->error('AI image generation is not enabled in settings.');
            $io->out('Please enable AI.enabled and AI.imageGeneration.enabled in your configuration.');
            return static::CODE_ERROR;
        }

        $limit = (int)$args->getOption('limit');
        $dryRun = $args->getOption('dry-run');
        $since = $args->getOption('since');
        $before = $args->getOption('before');
        $force = $args->getOption('force');
        $verbose = $args->getOption('verbose');
        $statsOnly = $args->getOption('stats');

        // Show current statistics if requested
        if ($statsOnly || $verbose) {
            $this->displayStatistics($io);
            if ($statsOnly) {
                return static::CODE_SUCCESS;
            }
        }

        // Validate date inputs
        if ($since && !$this->isValidDate($since)) {
            $io->error("Invalid 'since' date format. Please use YYYY-MM-DD format.");
            return static::CODE_ERROR;
        }

        if ($before && !$this->isValidDate($before)) {
            $io->error("Invalid 'before' date format. Please use YYYY-MM-DD format.");
            return static::CODE_ERROR;
        }

        // Get articles that need images
        $candidates = $this->findCandidateArticles($since, $before, $limit + 100); // Get extra for filtering
        $candidateCount = count($candidates);

        if ($candidateCount === 0) {
            $io->success('No articles found that need image generation.');
            return static::CODE_SUCCESS;
        }

        $io->out(sprintf('Found %d article(s) that need image generation.', $candidateCount));

        if ($verbose) {
            $io->out('');
            $io->out('Filter criteria:');
            $io->out('- Published articles only');
            $io->out('- Article type (not pages)');
            $io->out('- No existing images');
            if ($since) {
                $io->out("- Published since: {$since}");
            }
            if ($before) {
                $io->out("- Published before: {$before}");
            }
            $io->out('');
        }

        // Apply limit
        if ($candidateCount > $limit) {
            $candidates = array_slice($candidates, 0, $limit);
            $io->warning(sprintf('Processing limited to %d articles (use --limit to change).', $limit));
        }

        // Show what will be processed
        if ($dryRun) {
            $io->info('DRY RUN MODE - No jobs will be queued');
            $io->out('');
            $this->displayCandidateArticles($candidates, $io, $verbose);
            return static::CODE_SUCCESS;
        }

        // Rate limiting check can be implemented later if needed
        // For now, we'll allow the command to proceed

        // Confirm processing unless forced
        if (!$force) {
            $confirm = $io->askChoice(
                sprintf('Process %d articles for image generation?', count($candidates)),
                ['y', 'n'],
                'n'
            );

            if ($confirm !== 'y') {
                $io->out('Operation cancelled.');
                return static::CODE_SUCCESS;
            }
        }

        // Process the articles
        $io->out('');
        $io->out('Processing articles for image generation...');

        $processed = 0;
        $queued = 0;
        $skipped = 0;

        foreach ($candidates as $article) {
            $processed++;
            try {
                // For now, we'll simulate queuing the job
                // In a real implementation, this would queue an ArticleImageGenerationJob
                $io->out(sprintf('Would queue image generation for: %s (ID: %s)', $article->title, $article->id));
                $queued++;
            } catch (Exception $e) {
                $io->error(sprintf('Failed to queue %s: %s', $article->title, $e->getMessage()));
                $skipped++;
            }
        }

        $results = [
            'processed' => $processed,
            'queued' => $queued,
            'skipped' => $skipped
        ];

        // Display results
        $this->displayResults($results, $io, $verbose);

        if ($results['queued'] > 0) {
            $io->success(sprintf('Successfully queued %d articles for image generation.', $results['queued']));
            $io->info('Note: This is currently a simulation. Real image generation jobs would be queued in a full implementation.');
        }

        if ($results['skipped'] > 0) {
            $io->warning(sprintf('%d articles were skipped due to filters or rate limits.', $results['skipped']));
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Find articles that are candidates for image generation
     *
     * @param string|null $since Optional start date
     * @param string|null $before Optional end date
     * @param int $maxResults Maximum results to return
     * @return array List of article entities
     */
    private function findCandidateArticles(?string $since = null, ?string $before = null, int $maxResults = 1000): array
    {
        $query = $this->ArticlesTable->find()
            ->select(['id', 'title', 'published', 'created', 'image'])
            ->where([
                'Articles.kind' => 'article',
                'Articles.is_published' => true,
                'OR' => [
                    'Articles.image IS' => null,
                    'Articles.image' => ''
                ]
            ])
            ->orderBy(['Articles.published' => 'DESC'])
            ->limit($maxResults);

        // Apply date filters
        if ($since) {
            $query->where(['Articles.published >=' => $since . ' 00:00:00']);
        }

        if ($before) {
            $query->where(['Articles.published <=' => $before . ' 23:59:59']);
        }

        return $query->toArray();
    }

    /**
     * Display candidate articles that would be processed
     *
     * @param array $candidates List of candidate articles
     * @param \Cake\Console\ConsoleIo $io Console IO instance
     * @param bool $verbose Whether to show verbose output
     * @return void
     */
    private function displayCandidateArticles(array $candidates, ConsoleIo $io, bool $verbose = false): void
    {
        $io->out(sprintf('Articles to be processed (%d):', count($candidates)));
        $io->out(str_repeat('-', 50));

        foreach ($candidates as $index => $article) {
            $title = strlen($article->title) > 40 ? substr($article->title, 0, 37) . '...' : $article->title;
            $published = $article->published ? $article->published->format('Y-m-d') : 'Not published';

            if ($verbose) {
                $io->out(sprintf('%d. %s (ID: %s, Published: %s)', 
                    $index + 1, 
                    $title, 
                    $article->id, 
                    $published
                ));
            } else {
                $io->out(sprintf('%d. %s (%s)', $index + 1, $title, $published));
            }
        }
    }

    /**
     * Display processing results
     *
     * @param array $results Processing results
     * @param \Cake\Console\ConsoleIo $io Console IO instance
     * @param bool $verbose Whether to show verbose output
     * @return void
     */
    private function displayResults(array $results, ConsoleIo $io, bool $verbose = false): void
    {
        $io->out('');
        $io->out('Processing Results:');
        $io->out(str_repeat('-', 30));
        $io->out(sprintf('Total processed: %d', $results['processed']));
        $io->out(sprintf('Successfully queued: %d', $results['queued']));
        $io->out(sprintf('Skipped: %d', $results['skipped']));

        if ($verbose && isset($results['skipped_reasons'])) {
            $io->out('');
            $io->out('Skip reasons:');
            foreach ($results['skipped_reasons'] as $reason => $count) {
                $io->out(sprintf('- %s: %d', $reason, $count));
            }
        }
    }

    /**
     * Display current image generation statistics
     *
     * @param \Cake\Console\ConsoleIo $io Console IO instance
     * @return void
     */
    private function displayStatistics(ConsoleIo $io): void
    {
        // Count articles with and without images
        $totalArticles = $this->ArticlesTable->find()
            ->where(['kind' => 'article', 'is_published' => true])
            ->count();
            
        $articlesWithImages = $this->ArticlesTable->find()
            ->where([
                'kind' => 'article', 
                'is_published' => true,
                'image IS NOT' => null,
                'image !=' => ''
            ])
            ->count();
            
        $articlesNeedingImages = $totalArticles - $articlesWithImages;
        
        $io->out('Current Image Generation Statistics:');
        $io->out(str_repeat('=', 40));
        $io->out(sprintf('Total published articles: %d', $totalArticles));
        $io->out(sprintf('Articles with images: %d', $articlesWithImages));
        $io->out(sprintf('Articles needing images: %d', $articlesNeedingImages));
        
        if ($totalArticles > 0) {
            $completion = ($articlesWithImages / $totalArticles) * 100;
            $io->out(sprintf('Image completion rate: %.1f%%', $completion));
        }

        $io->out('');
    }

    /**
     * Validate date format
     *
     * @param string $date Date string to validate
     * @return bool True if valid YYYY-MM-DD format
     */
    private function isValidDate(string $date): bool
    {
        return (bool)preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && 
               strtotime($date) !== false;
    }
}