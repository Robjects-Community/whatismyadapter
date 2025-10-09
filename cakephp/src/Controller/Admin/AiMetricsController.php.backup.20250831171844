<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\Core\Configure;

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
            
        $successRate = $totalCalls > 0 ? ($successfulCalls / $totalCalls) * 100 : 0;
        
        $totalCost = $this->AiMetrics->getCostsByDateRange($last30Days, $today);
        
        // Task type breakdown
        $taskMetrics = $this->AiMetrics->getTaskTypeSummary($last30Days, $today);
        
        // Recent errors
        $recentErrors = $this->AiMetrics->getRecentErrors(5);
        
        // Rate limiting status
        $rateLimitService = new \App\Service\Api\RateLimitService();
        $currentUsage = $rateLimitService->getCurrentUsage();
        
        $this->set(compact(
            'totalCalls', 
            'successRate', 
            'totalCost', 
            'taskMetrics',
            'recentErrors',
            'currentUsage'
        ));
    }

    /**
     * Real-time metrics API endpoint for AJAX updates
     * Returns JSON data for dashboard live updates
     */
    public function realtimeData(): ?Response
    {
        $this->request->allowMethod(['get']);
        
        // Always return JSON regardless of content-type header
        $this->response = $this->response->withType('application/json');
        
        try {
            // Get timeframe from query params (default: last 30 days)
            $timeframe = $this->request->getQuery('timeframe', '30d');
            
            switch ($timeframe) {
                case '1h':
                    $startDate = date('Y-m-d H:i:s', strtotime('-1 hour'));
                    break;
                case '24h':
                    $startDate = date('Y-m-d H:i:s', strtotime('-24 hours'));
                    break;
                case '7d':
                    $startDate = date('Y-m-d', strtotime('-7 days'));
                    break;
                case '30d':
                default:
                    $startDate = date('Y-m-d', strtotime('-30 days'));
                    break;
            }
            
            $endDate = date('Y-m-d H:i:s');
            
            // Get real-time statistics
            $totalCalls = $this->AiMetrics->find()
                ->where(['created >=' => $startDate])
                ->count();
                
            $successfulCalls = $this->AiMetrics->find()
                ->where(['created >=' => $startDate, 'success' => true])
                ->count();
                
            $successRate = $totalCalls > 0 ? ($successfulCalls / $totalCalls) * 100 : 0;
            
            $totalCost = $this->AiMetrics->getCostsByDateRange($startDate, $endDate);
            
            // Task type breakdown
            $taskMetrics = $this->AiMetrics->getTaskTypeSummary($startDate, $endDate);
            
            // Recent activity (last 10 minutes)
            $recentActivity = $this->AiMetrics->find()
                ->where(['created >=' => date('Y-m-d H:i:s', strtotime('-10 minutes'))])
                ->orderBy(['created' => 'DESC'])
                ->limit(10)
                ->toArray();
            
            // Recent errors
            $recentErrors = $this->AiMetrics->getRecentErrors(5);
            
            // Rate limiting status
            $rateLimitService = new \App\Service\Api\RateLimitService();
            $currentUsage = $rateLimitService->getCurrentUsage();
            
            // Get queue status (active jobs) - handle case where Queue plugin might not be loaded
            $activeJobs = 0;
            $pendingJobs = 0;
            try {
                $queueJobsTable = $this->getTableLocator()->get('Queue.QueuedJobs');
                $activeJobs = $queueJobsTable->find()
                    ->where(['status' => 'in_progress'])
                    ->count();
                    
                $pendingJobs = $queueJobsTable->find()
                    ->where(['status' => 'new'])
                    ->count();
            } catch (\Exception $e) {
                // Queue plugin not available or table doesn't exist
                $this->log('Queue status check failed: ' . $e->getMessage(), 'warning');
            }
            
            // Get recent API calls per minute for the last hour (for sparkline)
            $sparklineData = $this->AiMetrics->find()
                ->select([
                    'minute' => "DATE_FORMAT(created, '%Y-%m-%d %H:%i')",
                    'count' => 'COUNT(*)'
                ])
                ->where(['created >=' => date('Y-m-d H:i:s', strtotime('-1 hour'))])
                ->groupBy("DATE_FORMAT(created, '%Y-%m-%d %H:%i')")
                ->orderBy(['minute' => 'ASC'])
                ->toArray();
            
            $response = [
                'success' => true,
                'timestamp' => time(),
                'timeframe' => $timeframe,
                'data' => [
                    'totalCalls' => $totalCalls,
                    'successRate' => round($successRate, 1),
                    'totalCost' => round($totalCost, 4),
                    'taskMetrics' => $taskMetrics,
                    'recentErrors' => $recentErrors,
                    'currentUsage' => $currentUsage,
                    'recentActivity' => $recentActivity,
                    'queueStatus' => [
                        'active' => $activeJobs,
                        'pending' => $pendingJobs
                    ],
                    'sparkline' => array_column($sparklineData, 'count')
                ]
            ];
            
            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode($response));
                
        } catch (\Exception $e) {
            // Log the error and return error response
            $this->log('Error in realtimeData: ' . $e->getMessage(), 'error');
            
            $errorResponse = [
                'success' => false,
                'error' => 'Failed to fetch real-time data',
                'message' => Configure::read('debug') ? $e->getMessage() : 'Internal server error'
            ];
            
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode($errorResponse));
        }
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
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
    public function view($id = null)
    {
        $aiMetric = $this->AiMetrics->get($id, contain: []);
        $this->set(compact('aiMetric'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
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
    public function edit($id = null)
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
    public function delete($id = null): ?Response
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
