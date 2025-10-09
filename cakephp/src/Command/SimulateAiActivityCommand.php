<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\Api\AiMetricsService;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;

/**
 * SimulateAiActivity command.
 *
 * Simulates continuous AI activity to demonstrate the real-time dashboard
 */
class SimulateAiActivityCommand extends Command
{
    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/4/en/console-commands/commands.html#defining-arguments-and-options
     * @param \Cake\Console\ConsoleOptionParser $parser The parser to be defined
     * @return \Cake\Console\ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->setDescription('Simulate continuous AI activity for real-time dashboard demonstration');
        $parser->addOption('duration', [
            'short' => 'd',
            'help' => 'Duration to run simulation in seconds (default: 300)',
            'default' => 300,
        ]);
        $parser->addOption('interval', [
            'short' => 'i',
            'help' => 'Interval between activities in seconds (default: 5)',
            'default' => 5,
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
        $duration = (int)$args->getOption('duration');
        $interval = (int)$args->getOption('interval');

        $io->out('<info>Starting AI Activity Simulation</info>');
        $io->out("Duration: {$duration} seconds, Interval: {$interval} seconds");

        $service = new AiMetricsService();
        $startTime = time();
        $endTime = $startTime + $duration;
        $counter = 1;

        // Define realistic task scenarios
        $taskScenarios = [
            ['google_translate_batch', [200, 350], [0.004, 0.012], 0.95, null],
            ['anthropic_seo_generation', [300, 600], [0.008, 0.025], 0.92, ['claude-3-sonnet', 'claude-3-haiku']],
            ['anthropic_content_analysis', [150, 400], [0.005, 0.018], 0.88, ['claude-3-sonnet', 'claude-3-haiku']],
            ['google_translate_single', [50, 150], [0.001, 0.005], 0.98, null],
            ['anthropic_image_analysis', [400, 800], [0.015, 0.035], 0.85, ['claude-3-sonnet']],
        ];

        while (time() < $endTime) {
            // Select a random task scenario
            $scenario = $taskScenarios[array_rand($taskScenarios)];
            [$taskType, $timeRange, $costRange, $successRate, $models] = $scenario;

            // Generate random parameters within scenario bounds
            $executionTime = rand($timeRange[0], $timeRange[1]);
            $cost = mt_rand((int)($costRange[0] * 10000), (int)($costRange[1] * 10000)) / 10000;
            $success = mt_rand() / mt_getrandmax() < $successRate;
            $tokens = $executionTime * rand(1, 3); // Rough correlation
            $model = $models ? $models[array_rand($models)] : null;
            $errorMessage = null;

            if (!$success) {
                $errors = [
                    'Rate limit exceeded',
                    'Service temporarily unavailable',
                    'Invalid API key',
                    'Request timeout',
                    'Content policy violation',
                ];
                $errorMessage = $errors[array_rand($errors)];
                $cost = 0; // No cost for failed requests
                $tokens = 0;
            }

            // Record the metric
            $result = $service->recordMetrics(
                $taskType,
                $executionTime,
                $success,
                $errorMessage,
                $tokens,
                $cost,
                $model,
            );

            $status = $success ? '<success>SUCCESS</success>' : '<error>FAILED</error>';
            $io->out("#{$counter}: {$taskType} - {$status} - \${$cost}");

            if (!$result) {
                $io->warning('Failed to record metric in database');
            }

            $counter++;

            // Wait for the specified interval
            sleep($interval);

            // Show progress
            $elapsed = time() - $startTime;
            $remaining = $endTime - time();

            if ($counter % 5 == 0) {
                $io->out("<info>Progress: {$elapsed}s elapsed, {$remaining}s remaining</info>");
                $dailyCost = $service->getDailyCost();
                $io->out("<info>Current daily cost: \${$dailyCost}</info>");
            }
        }

        $io->out('<success>AI Activity Simulation completed!</success>');
        $finalCost = $service->getDailyCost();
        $io->out('Total activities generated: ' . ($counter - 1));
        $io->out("Final daily cost: \${$finalCost}");

        return static::CODE_SUCCESS;
    }
}
