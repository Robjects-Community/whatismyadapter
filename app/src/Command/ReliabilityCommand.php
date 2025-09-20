<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Log\Log;
use Exception;

/**
 * Reliability Recalc command
 *
 * Handles bulk recalculation of reliability scores for the polymorphic
 * reliability system.
 */
class ReliabilityCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        $parser->setDescription('Recalculate reliability scores for one or all records of a model.');

        $parser->addArgument('model', [
            'help' => 'The model to recalculate (e.g., Products)',
            'required' => true,
        ]);

        $parser->addOption('id', [
            'help' => 'Specific entity ID to recalculate',
            'short' => 'i',
        ]);

        $parser->addOption('all', [
            'help' => 'Recalculate all entities of this model',
            'short' => 'a',
            'boolean' => true,
        ]);

        $parser->addOption('limit', [
            'help' => 'Number of records to process in one batch',
            'short' => 'l',
            'default' => '100',
        ]);

        $parser->addOption('offset', [
            'help' => 'Offset for batch processing',
            'short' => 'o',
            'default' => '0',
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
    public function execute(Arguments $args, ConsoleIo $io)
    {
        return $this->recalculate($args, $io);
    }

    /**
     * Recalculate reliability scores
     *
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console IO
     * @return int
     */
    protected function recalculate(Arguments $args, ConsoleIo $io): int
    {
        $model = $args->getArgumentAt(0); // First argument is the model
        $entityId = $args->getOption('id');
        $all = $args->getOption('all');
        $limit = (int)$args->getOption('limit');
        $offset = (int)$args->getOption('offset');

        // Validate model
        if (!in_array($model, ['Products'], true)) {
            $io->error("Unsupported model: {$model}. Supported models: Products");

            return self::CODE_ERROR;
        }

        if (!$entityId && !$all) {
            $io->error('You must specify either --id or --all');

            return self::CODE_ERROR;
        }

        try {
            $table = $this->fetchTable($model);

            // Check if the table has the reliability behavior
            if (!$table->behaviors()->has('Reliability')) {
                $io->error("Model {$model} does not have ReliabilityBehavior attached");

                return self::CODE_ERROR;
            }

            $behavior = $table->behaviors()->get('Reliability');
            $processed = 0;
            $errors = 0;

            if ($entityId) {
                // Recalculate single entity
                $io->info("Recalculating reliability scores for {$model} ID: {$entityId}");

                try {
                    $entity = $table->get($entityId);
                    $context = [
                        'source' => 'system',
                        'actor_user_id' => null,
                        'actor_service' => 'cli:recalc-command',
                        'message' => 'Bulk recalculation via CLI command',
                    ];

                    $behavior->recalcFor($entity, $context);
                    $processed++;
                    $io->success("✓ Recalculated {$model} {$entityId}");
                } catch (RecordNotFoundException $e) {
                    $io->error("Entity not found: {$entityId}");

                    return self::CODE_ERROR;
                } catch (Exception $e) {
                    $io->error("Failed to recalculate {$entityId}: " . $e->getMessage());
                    $errors++;
                }
            } else {
                // Recalculate all entities
                $io->info("Recalculating reliability scores for all {$model} entities (limit: {$limit}, offset: {$offset})");

                $query = $table->find()
                    ->limit($limit)
                    ->offset($offset)
                    ->orderAsc('created');

                $total = $query->count();
                $io->info("Found {$total} entities to process");

                if ($total === 0) {
                    $io->warning('No entities found to process');

                    return self::CODE_SUCCESS;
                }

                $progressBar = $io->helper('Progress');
                $progressBar->init(['total' => $total]);
                $progressBar->draw();

                foreach ($query as $entity) {
                    try {
                        $context = [
                            'source' => 'system',
                            'actor_user_id' => null,
                            'actor_service' => 'cli:recalc-command',
                            'message' => 'Bulk recalculation via CLI command',
                        ];

                        $behavior->recalcFor($entity, $context);
                        $processed++;
                    } catch (Exception $e) {
                        $errors++;
                        Log::error('Reliability recalc command failed', [
                            'model' => $model,
                            'id' => $entity->id ?? 'unknown',
                            'error' => $e->getMessage(),
                        ]);

                        $io->verbose("Error processing {$entity->id}: " . $e->getMessage());
                    }

                    $progressBar->increment(1);
                    $progressBar->draw();
                }

                $io->out(''); // New line after progress bar
            }

            // Summary
            $io->success('Recalculation completed!');
            $io->out("Processed: {$processed}");
            if ($errors > 0) {
                $io->warning("Errors: {$errors} (check logs for details)");

                return self::CODE_ERROR;
            }

            return self::CODE_SUCCESS;
        } catch (Exception $e) {
            $io->error('Command failed: ' . $e->getMessage());
            Log::error('ReliabilityCommand recalc failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return self::CODE_ERROR;
        }
    }

    /**
     * Verify reliability log checksums
     *
     * @param \Cake\Console\Arguments $args The command arguments
     * @param \Cake\Console\ConsoleIo $io The console IO
     * @return int
     */
    protected function verifyLogs(Arguments $args, ConsoleIo $io): int
    {
        $model = $args->getArgumentAt(1);
        $entityId = $args->getOption('id');
        $all = $args->getOption('all');
        $limit = (int)$args->getOption('limit');

        // Validate model
        if (!in_array($model, ['Products'], true)) {
            $io->error("Unsupported model: {$model}. Supported models: Products");

            return self::CODE_ERROR;
        }

        if (!$entityId && !$all) {
            $io->error('You must specify either --id or --all');

            return self::CODE_ERROR;
        }

        try {
            $table = $this->fetchTable($model);

            // Check if the table has the reliability behavior
            if (!$table->behaviors()->has('Reliability')) {
                $io->error("Model {$model} does not have ReliabilityBehavior attached");

                return self::CODE_ERROR;
            }

            $behavior = $table->behaviors()->get('Reliability');
            $logsTable = $this->fetchTable('ProductsReliabilityLogs');

            $verified = 0;
            $mismatches = 0;
            $errors = 0;

            if ($entityId) {
                // Verify logs for single entity
                $io->info("Verifying reliability logs for {$model} ID: {$entityId}");

                // Verify entity exists
                try {
                    $table->get($entityId);
                } catch (RecordNotFoundException $e) {
                    $io->error("Entity not found: {$entityId}");

                    return self::CODE_ERROR;
                }

                $logs = $logsTable->find('logsFor', ['model' => $model, 'id' => $entityId])->all();
            } else {
                // Verify logs for all entities
                $io->info("Verifying reliability logs for all {$model} entities (limit: {$limit})");

                $logs = $logsTable->find()
                    ->where(['model' => $model])
                    ->limit($limit)
                    ->orderDesc('created')
                    ->all();
            }

            $total = $logs->count();

            if ($total === 0) {
                $io->warning('No logs found to verify');

                return self::CODE_SUCCESS;
            }

            $io->info("Found {$total} log entries to verify");

            $progressBar = $io->helper('Progress');
            $progressBar->init(['total' => $total]);
            $progressBar->draw();

            foreach ($logs as $log) {
                try {
                    // Rebuild payload for checksum verification
                    $payload = [
                        'model' => $log->model,
                        'foreign_key' => $log->foreign_key,
                        'from_total_score' => $log->from_total_score,
                        'to_total_score' => $log->to_total_score,
                        'from_field_scores_json' => $log->from_field_scores_json,
                        'to_field_scores_json' => $log->to_field_scores_json,
                        'source' => $log->source,
                        'actor_user_id' => $log->actor_user_id,
                        'actor_service' => $log->actor_service,
                        'created' => $log->created->format('c'), // ISO8601 format
                    ];

                    $expectedChecksum = $behavior->computeChecksum($payload);

                    if ($expectedChecksum === $log->checksum_sha256) {
                        $verified++;
                    } else {
                        $mismatches++;
                        $mismatchData = [
                            'log_id' => $log->id,
                            'model' => $log->model,
                            'foreign_key' => $log->foreign_key,
                            'expected' => $expectedChecksum,
                            'actual' => $log->checksum_sha256,
                            'created' => $log->created,
                        ];

                        Log::error('Reliability log checksum mismatch', $mismatchData);
                        $io->verbose("Checksum mismatch for log {$log->id}: expected {$expectedChecksum}, got {$log->checksum_sha256}");
                    }
                } catch (Exception $e) {
                    $errors++;
                    $io->verbose("Error verifying log {$log->id}: " . $e->getMessage());
                }

                $progressBar->increment(1);
                $progressBar->draw();
            }

            $io->out(''); // New line after progress bar

            // Summary
            $io->success('Checksum verification completed!');
            $io->out("Total logs verified: {$total}");
            $io->out("✓ Valid checksums: {$verified}");

            if ($mismatches > 0) {
                $io->warning("✗ Checksum mismatches: {$mismatches}");
                $io->warning('Details logged to application log files');
            }

            if ($errors > 0) {
                $io->error("Errors during verification: {$errors}");
            }

            return $mismatches > 0 || $errors > 0 ? self::CODE_ERROR : self::CODE_SUCCESS;
        } catch (Exception $e) {
            $io->error('Command failed: ' . $e->getMessage());
            Log::error('ReliabilityCommand verify-logs failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return self::CODE_ERROR;
        }
    }
}
