<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\Core\Configure;
use Cake\Filesystem\File;

/**
 * QueueConfigurations Controller
 *
 * Manages queue configurations and synchronizes them with the queue.php config file
 *
 * @property \App\Model\Table\QueueConfigurationsTable $QueueConfigurations
 */
class QueueConfigurationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index(): ?Response
    {
        $query = $this->QueueConfigurations->find()
            ->select([
                'QueueConfigurations.id',
                'QueueConfigurations.name',
                'QueueConfigurations.config_key',
                'QueueConfigurations.queue_type',
                'QueueConfigurations.queue_name',
                'QueueConfigurations.host',
                'QueueConfigurations.port',
                'QueueConfigurations.enabled',
                'QueueConfigurations.priority',
                'QueueConfigurations.max_workers',
                'QueueConfigurations.created',
                'QueueConfigurations.modified',
            ])
            ->orderBy(['priority' => 'DESC', 'created' => 'ASC']);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'QueueConfigurations.name LIKE' => '%' . $search . '%',
                    'QueueConfigurations.config_key LIKE' => '%' . $search . '%',
                    'QueueConfigurations.queue_type LIKE' => '%' . $search . '%',
                    'QueueConfigurations.queue_name LIKE' => '%' . $search . '%',
                    'QueueConfigurations.description LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $queueConfigurations = $this->paginate($query);

        if ($this->request->is('ajax')) {
            $this->set(compact('queueConfigurations', 'search'));
            $this->viewBuilder()->setLayout('ajax');
            return $this->render('search_results');
        }

        $this->set(compact('queueConfigurations'));
        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Queue Configuration id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $queueConfiguration = $this->QueueConfigurations->get($id);
        $this->set(compact('queueConfiguration'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $queueConfiguration = $this->QueueConfigurations->newEmptyEntity();
        
        if ($this->request->is('post')) {
            $data = $this->request->getData();
            
            // Set default port based on queue type
            if (empty($data['port'])) {
                $data['port'] = $data['queue_type'] === 'rabbitmq' ? 5672 : 6379;
            }

            $queueConfiguration = $this->QueueConfigurations->patchEntity($queueConfiguration, $data);
            
            if ($this->QueueConfigurations->save($queueConfiguration)) {
                $this->updateQueueConfigFile();
                $this->Flash->success(__('The queue configuration has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue configuration could not be saved. Please, try again.'));
        }

        $this->set(compact('queueConfiguration'));
        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Queue Configuration id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $queueConfiguration = $this->QueueConfigurations->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            
            // Set default port based on queue type if not provided
            if (empty($data['port'])) {
                $data['port'] = $data['queue_type'] === 'rabbitmq' ? 5672 : 6379;
            }

            $queueConfiguration = $this->QueueConfigurations->patchEntity($queueConfiguration, $data);
            
            if ($this->QueueConfigurations->save($queueConfiguration)) {
                $this->updateQueueConfigFile();
                $this->Flash->success(__('The queue configuration has been saved.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The queue configuration could not be saved. Please, try again.'));
        }

        $this->set(compact('queueConfiguration'));
        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Queue Configuration id.
     * @return \Cake\Http\Response
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $queueConfiguration = $this->QueueConfigurations->get($id);
        
        if ($this->QueueConfigurations->delete($queueConfiguration)) {
            $this->updateQueueConfigFile();
            $this->Flash->success(__('The queue configuration has been deleted.'));
        } else {
            $this->Flash->error(__('The queue configuration could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Sync queue configurations from database to queue.php file
     *
     * @return \Cake\Http\Response
     */
    public function sync(): Response
    {
        try {
            $this->updateQueueConfigFile();
            $this->Flash->success(__('Queue configurations have been synchronized with the config file.'));
        } catch (\Exception $e) {
            $this->Flash->error(__('Failed to synchronize queue configurations: {0}', $e->getMessage()));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Check health status of a queue configuration
     *
     * @param string|null $id Queue Configuration id.
     * @return \Cake\Http\Response
     */
    public function healthCheck(?string $id = null): Response
    {
        $this->viewBuilder()->setLayout('ajax');
        
        try {
            $queueConfiguration = $this->QueueConfigurations->get($id);
            $healthStatus = $this->checkQueueHealth($queueConfiguration);
            
            $this->set([
                'success' => true,
                'health' => $healthStatus,
                '_serialize' => ['success', 'health']
            ]);
        } catch (Exception $e) {
            $this->set([
                'success' => false,
                'error' => $e->getMessage(),
                '_serialize' => ['success', 'error']
            ]);
        }

        return $this->render();
    }

    /**
     * Check health status of all queue configurations
     *
     * @return \Cake\Http\Response
     */
    public function healthCheckAll(): Response
    {
        $this->viewBuilder()->setLayout('ajax');
        
        $queueConfigurations = $this->QueueConfigurations->find()
            ->where(['enabled' => true])
            ->toArray();
        
        $healthStatuses = [];
        
        foreach ($queueConfigurations as $config) {
            $healthStatuses[$config->id] = $this->checkQueueHealth($config);
        }
        
        $this->set([
            'success' => true,
            'health_statuses' => $healthStatuses,
            '_serialize' => ['success', 'health_statuses']
        ]);

        return $this->render();
    }

    /**
     * Check the health of a queue configuration
     *
     * @param \App\Model\Entity\QueueConfiguration $config
     * @return array
     */
    private function checkQueueHealth(\App\Model\Entity\QueueConfiguration $config): array
    {
        $startTime = microtime(true);
        $healthy = false;
        $message = '';
        $details = [];
        
        try {
            // Check basic connectivity with timeout
            $connection = @fsockopen($config->host, $config->port, $errno, $errstr, 5);
            
            if ($connection) {
                $healthy = true;
                $message = 'Connection successful';
                
                // Add queue-specific health checks
                if ($config->queue_type === 'rabbitmq') {
                    $details = $this->checkRabbitMQHealth($config, $connection);
                } elseif ($config->queue_type === 'redis') {
                    $details = $this->checkRedisHealth($config, $connection);
                }
                
                fclose($connection);
            } else {
                $healthy = false;
                $message = "Connection failed: {$errstr} (Error {$errno})";
            }
        } catch (Exception $e) {
            $healthy = false;
            $message = 'Health check error: ' . $e->getMessage();
        }
        
        $responseTime = round((microtime(true) - $startTime) * 1000, 2);
        
        return [
            'healthy' => $healthy,
            'message' => $message,
            'response_time_ms' => $responseTime,
            'details' => $details,
            'checked_at' => date('Y-m-d H:i:s'),
            'config_id' => $config->id,
            'config_name' => $config->name,
            'host' => $config->host,
            'port' => $config->port,
            'queue_type' => $config->queue_type
        ];
    }
    
    /**
     * Check RabbitMQ-specific health
     *
     * @param \App\Model\Entity\QueueConfiguration $config
     * @param resource $connection
     * @return array
     */
    private function checkRabbitMQHealth(\App\Model\Entity\QueueConfiguration $config, $connection): array
    {
        return [
            'queue_type' => 'RabbitMQ',
            'vhost' => $config->vhost ?? '/',
            'username' => $config->username ?? 'guest',
            'ssl_enabled' => $config->ssl_enabled,
            'exchange' => $config->exchange,
            'routing_key' => $config->routing_key,
            'status' => 'Port accessible - full AMQP health check would require additional libraries'
        ];
    }
    
    /**
     * Check Redis-specific health  
     *
     * @param \App\Model\Entity\QueueConfiguration $config
     * @param resource $connection
     * @return array
     */
    private function checkRedisHealth(\App\Model\Entity\QueueConfiguration $config, $connection): array
    {
        $details = [
            'queue_type' => 'Redis',
            'database' => $config->db_index ?? 0,
            'status' => 'Port accessible'
        ];
        
        // Try to send a simple PING command if it's Redis
        try {
            fwrite($connection, "*1\r\n$4\r\nPING\r\n");
            $response = fread($connection, 1024);
            
            if (strpos($response, '+PONG') === 0) {
                $details['status'] = 'Redis PING successful';
                $details['redis_response'] = 'PONG';
            } else {
                $details['status'] = 'Port accessible but Redis PING failed';
                $details['redis_response'] = trim($response);
            }
        } catch (Exception $e) {
            $details['status'] = 'Port accessible but Redis communication failed';
            $details['error'] = $e->getMessage();
        }
        
        return $details;
    }

    /**
     * Updates the queue.php configuration file with current database settings
     *
     * @return void
     * @throws \Exception When config file cannot be updated
     */
    private function updateQueueConfigFile(): void
    {
        $configPath = ROOT . '/config/queue.php';
        
        // Get all enabled queue configurations from database
        $queueConfigs = $this->QueueConfigurations->find()
            ->where(['enabled' => true])
            ->orderBy(['priority' => 'DESC'])
            ->toArray();

        // Build the configuration array
        $configArray = [
            'Queue' => []
        ];

        foreach ($queueConfigs as $config) {
            $queueConfig = [
                'queue' => $config->queue_name,
                'url' => $this->buildQueueUrl($config),
                'host' => $config->host,
                'port' => $config->port,
                'persistent' => $config->persistent,
            ];

            // Add queue-specific settings
            if ($config->queue_type === 'rabbitmq') {
                $queueConfig = array_merge($queueConfig, [
                    'username' => $config->username,
                    'password' => $config->password,
                    'exchange' => $config->exchange,
                    'routing_key' => $config->routing_key,
                    'vhost' => $config->vhost,
                    'ssl' => $config->ssl_enabled,
                ]);
            } elseif ($config->queue_type === 'redis') {
                $queueConfig['database'] = $config->db_index ?? 0;
            }

            // Add any additional config data
            if (!empty($config->config_data)) {
                $additionalConfig = json_decode($config->config_data, true);
                if (is_array($additionalConfig)) {
                    $queueConfig = array_merge($queueConfig, $additionalConfig);
                }
            }

            $configArray['Queue'][$config->config_key] = $queueConfig;
        }

        // Generate the PHP file content
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Queue Configuration\n";
        $configContent .= " * \n";
        $configContent .= " * This file is automatically generated by the Queue Configuration admin panel.\n";
        $configContent .= " * Manual changes to this file may be overwritten.\n";
        $configContent .= " * Last updated: " . date('Y-m-d H:i:s') . "\n";
        $configContent .= " */\n\n";
        $configContent .= "return " . var_export($configArray, true) . ";\n";

        // Write to file
        $file = new File($configPath, true, 0644);
        if (!$file->write($configContent)) {
            throw new \Exception('Could not write to queue configuration file');
        }
        $file->close();
    }

    /**
     * Builds the queue URL based on configuration
     *
     * @param \App\Model\Entity\QueueConfiguration $config
     * @return string
     */
    private function buildQueueUrl(\App\Model\Entity\QueueConfiguration $config): string
    {
        if ($config->queue_type === 'rabbitmq') {
            $protocol = $config->ssl_enabled ? 'amqps' : 'amqp';
            $auth = '';
            
            if ($config->username) {
                $auth = $config->username;
                if ($config->password) {
                    $auth .= ':' . $config->password;
                }
                $auth .= '@';
            }

            $vhost = urlencode($config->vhost ?? '/');
            
            return sprintf('%s://%s%s:%d/%s',
                $protocol,
                $auth,
                $config->host,
                $config->port,
                $vhost
            );
        } else {
            // Redis URL
            $auth = '';
            if ($config->password) {
                $auth = ':' . $config->password . '@';
            }

            return sprintf('redis://%s%s:%d/%d',
                $auth,
                $config->host,
                $config->port,
                $config->db_index ?? 0
            );
        }
    }
}