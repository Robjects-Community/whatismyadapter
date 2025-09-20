<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\ORM\TableRegistry;
use App\Service\QueueManager;
use App\Job\ProductImageGenerationJob;

/**
 * BatchProductImageGenerationCommand
 *
 * CLI command to batch process products that need AI-generated images.
 * This command provides various options for filtering and controlling
 * the batch processing of product image generation jobs.
 *
 * Usage examples:
 * bin/cake batch_product_image_generation
 * bin/cake batch_product_image_generation --limit 100 --dry-run
 * bin/cake batch_product_image_generation --category electronics --manufacturer Dell
 * bin/cake batch_product_image_generation --force-regenerate --batch-size 25
 */
class BatchProductImageGenerationCommand extends Command
{
    /**
     * Default batch size for processing products
     */
    private const DEFAULT_BATCH_SIZE = 50;

    /**
     * Maximum batch size to prevent overwhelming the queue
     */
    private const MAX_BATCH_SIZE = 200;

    /**
     * Default delay between batches (in seconds)
     */
    private const DEFAULT_BATCH_DELAY = 5;

    /**
     * @var \App\Service\QueueManager Queue manager for job processing
     */
    private QueueManager $queueManager;

    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser
            ->setDescription('Generate AI images for products in batches.')
            ->addOption('limit', [
                'short' => 'l',
                'help' => 'Maximum number of products to process (0 = no limit)',
                'default' => 0
            ])
            ->addOption('batch-size', [
                'short' => 'b',
                'help' => 'Number of products to process in each batch',
                'default' => self::DEFAULT_BATCH_SIZE
            ])
            ->addOption('delay', [
                'short' => 'd',
                'help' => 'Delay between batches in seconds',
                'default' => self::DEFAULT_BATCH_DELAY
            ])
            ->addOption('dry-run', [
                'help' => 'Show what would be processed without actually doing it',
                'boolean' => true
            ])
            ->addOption('category', [
                'short' => 'c',
                'help' => 'Only process products in specific category'
            ])
            ->addOption('manufacturer', [
                'short' => 'm',
                'help' => 'Only process products from specific manufacturer'
            ])
            ->addOption('force-regenerate', [
                'short' => 'f',
                'help' => 'Regenerate images even for products that already have images',
                'boolean' => true
            ])
            ->addOption('queue-priority', [
                'short' => 'p',
                'help' => 'Priority for queued jobs (high, normal, low)',
                'default' => 'normal',
                'choices' => ['high', 'normal', 'low']
            ])
            ->addOption('verbose', [
                'short' => 'v',
                'help' => 'Enable verbose output',
                'boolean' => true
            ])
            ->addOption('stats-only', [
                'short' => 's',
                'help' => 'Show statistics only, do not process',
                'boolean' => true
            ]);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->queueManager = new QueueManager();

        $io->out('<info>Product Image Generation Batch Processor</info>');
        $io->hr();

        // Validate and parse options
        $options = $this->parseOptions($args, $io);
        if ($options === false) {
            return static::CODE_ERROR;
        }

        // Get products table with image detection trait
        $productsTable = TableRegistry::getTableLocator()->get('Products');

        // Check if image generation is enabled
        if (!method_exists($productsTable, 'isImageGenerationEnabled') || 
            !$productsTable->isImageGenerationEnabled()) {
            $io->error('Image generation is not enabled in configuration.');
            return static::CODE_ERROR;
        }

        // Build the query for products needing images
        $query = $this->buildProductQuery($productsTable, $options, $io);
        
        // Show statistics
        $this->displayStatistics($query, $productsTable, $options, $io);

        // Exit early if only showing stats
        if ($options['stats_only']) {
            return static::CODE_SUCCESS;
        }

        // Process products in batches
        return $this->processProductsBatch($query, $options, $io);
    }

    /**
     * Parse and validate command options
     *
     * @param \Cake\Console\Arguments $args Command arguments
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return array|false Parsed options or false on error
     */
    private function parseOptions(Arguments $args, ConsoleIo $io): array|false
    {
        $options = [
            'limit' => max(0, (int)$args->getOption('limit')),
            'batch_size' => max(1, min(self::MAX_BATCH_SIZE, (int)$args->getOption('batch-size'))),
            'delay' => max(0, (int)$args->getOption('delay')),
            'dry_run' => $args->getOption('dry-run'),
            'category' => $args->getOption('category'),
            'manufacturer' => $args->getOption('manufacturer'),
            'force_regenerate' => $args->getOption('force-regenerate'),
            'queue_priority' => $args->getOption('queue-priority'),
            'verbose' => $args->getOption('verbose'),
            'stats_only' => $args->getOption('stats-only')
        ];

        // Validate batch size
        if ($options['batch_size'] !== (int)$args->getOption('batch-size')) {
            $io->warning("Batch size adjusted to maximum allowed: {$options['batch_size']}");
        }

        return $options;
    }

    /**
     * Build the query for products that need image generation
     *
     * @param \Cake\ORM\Table $productsTable Products table
     * @param array $options Command options
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return \Cake\ORM\Query Product query
     */
    private function buildProductQuery($productsTable, array $options, ConsoleIo $io): \Cake\ORM\Query
    {
        if ($options['force_regenerate']) {
            // For forced regeneration, get all products regardless of existing images
            $query = $productsTable->find()
                ->select(['id', 'title', 'description', 'manufacturer', 'category', 'type']);
        } else {
            // Use the trait method to find products needing images
            if (method_exists($productsTable, 'findProductsNeedingImages')) {
                $query = $productsTable->findProductsNeedingImages();
            } else {
                // Fallback query
                $query = $productsTable->find()
                    ->select(['id', 'title', 'description', 'manufacturer', 'category', 'type'])
                    ->where([
                        'OR' => [
                            ['image IS' => null],
                            ['image' => '']
                        ]
                    ]);
            }
        }

        // Apply category filter
        if (!empty($options['category'])) {
            $query = $query->where(['category' => $options['category']]);
            $io->verbose("Filtering by category: {$options['category']}");
        }

        // Apply manufacturer filter
        if (!empty($options['manufacturer'])) {
            $query = $query->where(['manufacturer LIKE' => '%' . $options['manufacturer'] . '%']);
            $io->verbose("Filtering by manufacturer: {$options['manufacturer']}");
        }

        // Apply limit if specified
        if ($options['limit'] > 0) {
            $query = $query->limit($options['limit']);
        }

        return $query->order(['id' => 'ASC']); // Consistent ordering
    }

    /**
     * Display statistics about products to be processed
     *
     * @param \Cake\ORM\Query $query Product query
     * @param \Cake\ORM\Table $productsTable Products table
     * @param array $options Command options
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return void
     */
    private function displayStatistics($query, $productsTable, array $options, ConsoleIo $io): void
    {
        $totalCount = $query->count();
        
        $io->out('<info>Processing Statistics:</info>');
        $io->out("Products to process: <warning>{$totalCount}</warning>");
        
        if ($options['limit'] > 0 && $totalCount > $options['limit']) {
            $io->out("Limited to: <warning>{$options['limit']}</warning>");
            $totalCount = $options['limit'];
        }

        if ($totalCount > 0) {
            $batches = (int)ceil($totalCount / $options['batch_size']);
            $io->out("Batch size: {$options['batch_size']}");
            $io->out("Number of batches: {$batches}");
            
            if ($options['delay'] > 0 && $batches > 1) {
                $totalTime = ($batches - 1) * $options['delay'];
                $io->out("Estimated minimum time (delays only): {$totalTime} seconds");
            }
        }

        // Show additional statistics if available
        if (method_exists($productsTable, 'countProductsNeedingImages') && !$options['force_regenerate']) {
            $globalCount = $productsTable->countProductsNeedingImages();
            if ($globalCount !== $totalCount) {
                $io->out("Total products needing images: <info>{$globalCount}</info>");
            }
        }

        $io->out("Force regenerate: " . ($options['force_regenerate'] ? '<error>YES</error>' : '<info>NO</info>'));
        $io->out("Queue priority: <info>{$options['queue_priority']}</info>");
        $io->out("Dry run: " . ($options['dry_run'] ? '<warning>YES</warning>' : '<info>NO</info>'));
        $io->hr();
    }

    /**
     * Process products in batches
     *
     * @param \Cake\ORM\Query $query Product query
     * @param array $options Command options  
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return int Exit code
     */
    private function processProductsBatch($query, array $options, ConsoleIo $io): int
    {
        $processed = 0;
        $queued = 0;
        $skipped = 0;
        $errors = 0;
        $batchNumber = 1;

        $offset = 0;

        do {
            $io->verbose("Processing batch {$batchNumber}...");
            
            // Get products for this batch
            $products = $query
                ->limit($options['batch_size'])
                ->offset($offset)
                ->toArray();

            if (empty($products)) {
                break; // No more products
            }

            $batchResults = $this->processBatch($products, $options, $io);
            
            $processed += $batchResults['processed'];
            $queued += $batchResults['queued'];
            $skipped += $batchResults['skipped'];
            $errors += $batchResults['errors'];

            $io->out("Batch {$batchNumber}: " . 
                "Processed: {$batchResults['processed']}, " .
                "Queued: {$batchResults['queued']}, " .
                "Skipped: {$batchResults['skipped']}, " .
                "Errors: {$batchResults['errors']}");

            $batchNumber++;
            $offset += $options['batch_size'];

            // Check if we've hit the limit
            if ($options['limit'] > 0 && $processed >= $options['limit']) {
                break;
            }

            // Delay between batches if specified
            if ($options['delay'] > 0 && !empty($products) && count($products) === $options['batch_size']) {
                $io->verbose("Waiting {$options['delay']} seconds...");
                sleep($options['delay']);
            }

        } while (!empty($products) && count($products) === $options['batch_size']);

        // Final summary
        $io->hr();
        $io->out('<success>Batch Processing Complete</success>');
        $io->out("Total processed: <info>{$processed}</info>");
        $io->out("Total queued for generation: <info>{$queued}</info>");
        $io->out("Total skipped: <info>{$skipped}</info>");
        
        if ($errors > 0) {
            $io->out("Total errors: <error>{$errors}</error>");
            return static::CODE_ERROR;
        }

        return static::CODE_SUCCESS;
    }

    /**
     * Process a single batch of products
     *
     * @param array $products Products to process
     * @param array $options Command options
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return array Processing results
     */
    private function processBatch(array $products, array $options, ConsoleIo $io): array
    {
        $results = [
            'processed' => 0,
            'queued' => 0,
            'skipped' => 0,
            'errors' => 0
        ];

        foreach ($products as $product) {
            try {
                $results['processed']++;

                if ($options['verbose']) {
                    $io->out("Processing: {$product->title} (ID: {$product->id})");
                }

                if ($options['dry_run']) {
                    $io->verbose("DRY RUN: Would queue image generation for product {$product->id}");
                    $results['queued']++;
                    continue;
                }

                // Queue the image generation job
                $jobData = [
                    'id' => $product->id,
                    'title' => $product->title,
                    'regenerate' => $options['force_regenerate']
                ];

                $priority = $this->getQueuePriority($options['queue_priority']);
                
                if ($this->queueManager->enqueue(ProductImageGenerationJob::class, $jobData, $priority)) {
                    $results['queued']++;
                    $io->verbose("Queued image generation for product {$product->id}");
                } else {
                    $results['errors']++;
                    $io->error("Failed to queue job for product {$product->id}");
                }

            } catch (\Exception $e) {
                $results['errors']++;
                $io->error("Error processing product {$product->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Convert priority string to queue priority constant
     *
     * @param string $priority Priority string
     * @return int Queue priority constant
     */
    private function getQueuePriority(string $priority): int
    {
        return match (strtolower($priority)) {
            'high' => QueueManager::PRIORITY_HIGH,
            'low' => QueueManager::PRIORITY_LOW,
            default => QueueManager::PRIORITY_NORMAL,
        };
    }

    /**
     * Get the command name for help and error messages
     *
     * @return string Command name
     */
    public static function getCommandName(): string
    {
        return 'batch_product_image_generation';
    }

    /**
     * Display additional help information
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Option parser
     * @param \Cake\Console\Arguments $args Command arguments
     * @param \Cake\Console\ConsoleIo $io Console IO
     * @return void
     */
    public function displayHelp(ConsoleOptionParser $parser, Arguments $args, ConsoleIo $io): void
    {
        parent::displayHelp($parser, $args, $io);
        
        $io->hr();
        $io->out('<info>Examples:</info>');
        $io->out('  bin/cake batch_product_image_generation --stats-only');
        $io->out('  bin/cake batch_product_image_generation --limit 100 --dry-run');
        $io->out('  bin/cake batch_product_image_generation --category electronics --verbose');
        $io->out('  bin/cake batch_product_image_generation --force-regenerate --batch-size 25');
        $io->out('  bin/cake batch_product_image_generation --manufacturer Dell --queue-priority high');
        $io->hr();
        
        $io->out('<info>Notes:</info>');
        $io->out('  - Use --dry-run to see what would be processed without making changes');
        $io->out('  - Use --stats-only to see counts without processing');
        $io->out('  - Batch processing includes delays to avoid overwhelming the system');
        $io->out('  - Image generation requires proper AI provider configuration');
    }
}