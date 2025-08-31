<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Queue\QueueManager;

/**
 * QueueableJobsTrait
 *
 * Provides job queuing functionality for Table classes.
 * This trait consolidates duplicate job queuing logic that was
 * previously scattered across multiple table classes.
 */
trait QueueableJobsTrait
{
    /**
     * Queues a job with the provided job class and data
     *
     * @param string $job The fully qualified job class name
     * @param array<string, mixed> $data The data to be passed to the job
     * @param array<string, mixed> $options Additional options for the job (delay, queue name, etc.)
     * @return void
     */
    public function queueJob(string $job, array $data, array $options = []): void
    {
        // Set default queue config
        $defaultOptions = ['config' => 'default'];
        $options = array_merge($defaultOptions, $options);

        QueueManager::push($job, $data, $options);

        $this->log(
            sprintf(
                'Queued a %s with data: %s%s',
                $job,
                json_encode($data),
                !empty($options['delay']) ? sprintf(' (delayed by %d seconds)', $options['delay']) : '',
            ),
            'info',
            ['group_name' => $job],
        );
    }

    /**
     * Queue multiple jobs at once
     *
     * @param array<array{job: string, data: array, options?: array}> $jobs Array of job configurations
     * @return void
     */
    public function queueJobs(array $jobs): void
    {
        foreach ($jobs as $jobConfig) {
            $options = $jobConfig['options'] ?? [];
            $this->queueJob($jobConfig['job'], $jobConfig['data'], $options);
        }
    }

    /**
     * Queue a job with a delay
     *
     * @param string $job The fully qualified job class name
     * @param array<string, mixed> $data The data to be passed to the job
     * @param int $delaySeconds Delay in seconds
     * @return void
     */
    public function queueDelayedJob(string $job, array $data, int $delaySeconds): void
    {
        $this->queueJob($job, $data, ['delay' => $delaySeconds]);
    }
}
