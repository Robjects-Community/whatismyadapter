<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Datasource\Exception\RecordNotFoundException;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use Cake\Log\Log;
use Exception;

/**
 * Reliability Controller
 *
 * Handles detailed reliability score viewing, manual recalculation,
 * and checksum verification for polymorphic reliability system.
 *
 * @property \App\Model\Table\ProductsReliabilityTable $ProductsReliability
 * @property \App\Model\Table\ProductsReliabilityFieldsTable $ProductsReliabilityFields
 * @property \App\Model\Table\ProductsReliabilityLogsTable $ProductsReliabilityLogs
 */
class ReliabilityController extends AppController
{
    /**
     * View reliability breakdown for a specific model/entity
     *
     * @param string $model The model name (e.g., 'Products')
     * @param string $id The entity ID (UUID)
     * @return void
     * @throws \Cake\Http\Exception\NotFoundException When model/entity not found
     */
    public function view(string $model, string $id): void
    {
        $this->set('title', 'Reliability Details');

        // Validate model
        if (!in_array($model, ['Products'], true)) {
            throw new NotFoundException(__('Invalid model specified.'));
        }

        // Load the primary entity to ensure it exists
        $entityTable = $this->fetchTable($model);
        try {
            $entity = $entityTable->get($id);
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException(__('Record not found.'));
        }

        // Load reliability data
        $reliabilityTable = $this->fetchTable('ProductsReliability');
        $fieldsTable = $this->fetchTable('ProductsReliabilityFields');
        $logsTable = $this->fetchTable('ProductsReliabilityLogs');

        // Get current reliability summary
        $reliabilitySummary = $reliabilityTable->findSummaryFor($model, $id);

        // Get field-level breakdown
        $fieldsData = $fieldsTable->findFieldsFor($model, $id);

        // Get paginated history logs (most recent first)
        $logs = $logsTable->findLogsFor($model, $id)
            ->limit(20);

        $this->set(compact('model', 'id', 'entity', 'reliabilitySummary', 'fieldsData', 'logs'));
    }

    /**
     * Manually recalculate reliability scores for a specific entity
     *
     * @param string $model The model name
     * @param string $id The entity ID
     * @return \Cake\Http\Response|null Redirects back to view
     * @throws \Cake\Http\Exception\NotFoundException When model/entity not found
     */
    public function recalc(string $model, string $id): ?Response
    {
        $this->request->allowMethod(['post']);

        // Validate model
        if (!in_array($model, ['Products'], true)) {
            throw new NotFoundException(__('Invalid model specified.'));
        }

        // Load entity
        $entityTable = $this->fetchTable($model);
        try {
            $entity = $entityTable->get($id);
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException(__('Record not found.'));
        }

        try {
            // Get the behavior attached to the table
            $behavior = $entityTable->behaviors()->get('Reliability');

            // Manual recalculation context
            $context = [
                'source' => 'admin',
                'actor_user_id' => $this->Authentication->getIdentity()?->getIdentifier(),
                'actor_service' => null,
                'message' => 'Manual recalculation triggered from admin interface',
            ];

            // Trigger recalculation
            $behavior->recalcFor($entity, $context);

            $this->Flash->success(__('Reliability scores have been recalculated successfully.'));
        } catch (Exception $e) {
            Log::error('Reliability recalculation failed', [
                'model' => $model,
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->Flash->error(__('Failed to recalculate reliability scores. Please try again.'));
        }

        return $this->redirect(['action' => 'view', $model, $id]);
    }

    /**
     * Verify checksums for reliability logs of a specific entity
     *
     * @param string $model The model name
     * @param string $id The entity ID
     * @return \Cake\Http\Response|null Redirects back to view
     * @throws \Cake\Http\Exception\NotFoundException When model/entity not found
     */
    public function verifyChecksums(string $model, string $id): ?Response
    {
        $this->request->allowMethod(['post']);

        // Validate model
        if (!in_array($model, ['Products'], true)) {
            throw new NotFoundException(__('Invalid model specified.'));
        }

        // Load entity to ensure it exists
        $entityTable = $this->fetchTable($model);
        try {
            $entityTable->get($id); // Verify entity exists
        } catch (RecordNotFoundException $e) {
            throw new NotFoundException(__('Record not found.'));
        }

        try {
            // Get behavior for checksum computation
            $behavior = $entityTable->behaviors()->get('Reliability');

            // Load all logs for this entity
            $logsTable = $this->fetchTable('ProductsReliabilityLogs');
            $logs = $logsTable->findLogsFor($model, $id)->all();

            $verified = 0;
            $mismatches = [];

            foreach ($logs as $log) {
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
                    $mismatches[] = [
                        'log_id' => $log->id,
                        'expected' => $expectedChecksum,
                        'actual' => $log->checksum_sha256,
                        'created' => $log->created,
                    ];
                }
            }

            if (empty($mismatches)) {
                $this->Flash->success(__(
                    'All {count} log checksums verified successfully.',
                    ['count' => $verified],
                ));
            } else {
                $this->Flash->warning(__(
                    'Checksum verification completed: {verified} passed, {failed} failed. '
                    . 'Check system logs for details.',
                    ['verified' => $verified, 'failed' => count($mismatches)],
                ));

                // Log mismatches for investigation
                Log::error('Reliability log checksum mismatches detected', [
                    'model' => $model,
                    'id' => $id,
                    'mismatches' => $mismatches,
                ]);
            }
        } catch (Exception $e) {
            Log::error('Reliability checksum verification failed', [
                'model' => $model,
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->Flash->error(__('Failed to verify checksums. Please try again.'));
        }

        return $this->redirect(['action' => 'view', $model, $id]);
    }
}
