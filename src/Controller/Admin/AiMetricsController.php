<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\Api\RateLimitService;
use Cake\Http\Response;

/**
 * AiMetrics Controller
 *
 * @property \App\Model\Table\AiMetricsTable $AiMetrics
 */
class AiMetricsController extends AppController
{
    /**
     * Dashboard method - AI metrics overview
     */
    public function dashboard(): void
    {
        $last30Days = date('Y-m-d', strtotime('-30 days'));
        $today = date('Y-m-d');

        // Summary statistics
        $totalCalls = $this->AiMetrics->find()
            ->where(['created >=' => $last30Days])
            ->count();

        $successfulCalls = $this->AiMetrics->find()
            ->where(['created >=' => $last30Days, 'success' => true])
            ->count();

        $successRate = $totalCalls > 0 ? $successfulCalls / $totalCalls * 100 : 0;

        $totalCost = $this->AiMetrics->getCostsByDateRange($last30Days, $today);

        // Task type breakdown
        $taskMetrics = $this->AiMetrics->getTaskTypeSummary($last30Days, $today);

        // Recent errors
        $recentErrors = $this->AiMetrics->getRecentErrors(5);

        // Rate limiting status
        $rateLimitService = new RateLimitService();
        $currentUsage = $rateLimitService->getCurrentUsage();

        $this->set(compact(
            'totalCalls',
            'successRate',
            'totalCost',
            'taskMetrics',
            'recentErrors',
            'currentUsage',
        ));
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $query = $this->AiMetrics->find()
            ->select([
                'AiMetrics.id',
                'AiMetrics.task_type',
                'AiMetrics.execution_time_ms',
                'AiMetrics.tokens_used',
                'AiMetrics.cost_usd',
                'AiMetrics.success',
                'AiMetrics.error_message',
                'AiMetrics.model_used',
                'AiMetrics.created',
                'AiMetrics.modified',
            ]);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'AiMetrics.task_type LIKE' => '%' . $search . '%',
                    'AiMetrics.error_message LIKE' => '%' . $search . '%',
                    'AiMetrics.model_used LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $aiMetrics = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('aiMetrics', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('aiMetrics'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Ai Metric id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): ?Response
    {
        $aiMetric = $this->AiMetrics->get($id, contain: []);
        $this->set(compact('aiMetric'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $aiMetric = $this->AiMetrics->newEmptyEntity();
        if ($this->request->is('post')) {
            $aiMetric = $this->AiMetrics->patchEntity($aiMetric, $this->request->getData());
            if ($this->AiMetrics->save($aiMetric)) {
                $this->Flash->success(__('The ai metric has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The ai metric could not be saved. Please, try again.'));
        }
        $this->set(compact('aiMetric'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Ai Metric id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $aiMetric = $this->AiMetrics->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $aiMetric = $this->AiMetrics->patchEntity($aiMetric, $this->request->getData());
            if ($this->AiMetrics->save($aiMetric)) {
                $this->Flash->success(__('The ai metric has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The ai metric could not be saved. Please, try again.'));
        }
        $this->set(compact('aiMetric'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Ai Metric id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $aiMetric = $this->AiMetrics->get($id);
        if ($this->AiMetrics->delete($aiMetric)) {
            $this->Flash->success(__('The ai metric has been deleted.'));
        } else {
            $this->Flash->error(__('The ai metric could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
