<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * SystemLogs Controller
 *
 * Manages system logs, providing functionalities to view, filter, search, and delete logs.
 *
 * @property \App\Model\Table\SystemLogsTable $SystemLogs
 */
class SystemLogsController extends AppController
{
    /**
     * Index method for SystemLogs.
     *
     * Retrieves and displays a list of system logs. Supports filtering by log level and group name,
     * and allows for searching within the logs when accessed via AJAX. The logs are ordered by creation date in
     * descending order. Also retrieves distinct log levels and group names for filtering options.
     *
     * @return \Cake\Http\Response|null Returns null to render the view, or a Response object for AJAX requests.
     * @throws \Cake\Http\Exception\NotFoundException If the page is not found.
     * @throws \Cake\Database\Exception\DatabaseException If there's an issue with the database query.
     */
    public function index(): ?Response
    {
        $query = $this->SystemLogs->find()
            ->select([
                'SystemLogs.id',
                'SystemLogs.level',
                'SystemLogs.message',
                'SystemLogs.context',
                'SystemLogs.group_name',
                'SystemLogs.created',
            ])
            ->orderBy(['SystemLogs.created' => 'DESC']);

        $levels = $this->SystemLogs->find()
            ->select(['level'])
            ->distinct(['level'])
            ->orderBy(['level' => 'ASC'])
            ->all()
            ->extract('level')
            ->toArray();

        $groupNames = $this->SystemLogs->find()
            ->select(['group_name'])
            ->distinct(['group_name'])
            ->orderBy(['group_name' => 'ASC'])
            ->all()
            ->extract('group_name')
            ->toArray();

        $selectedLevel = $this->request->getQuery('level');
        $selectedGroup = $this->request->getQuery('group');

        if ($selectedLevel) {
            $query->where(['SystemLogs.level' => $selectedLevel]);
        }

        if ($selectedGroup) {
            $query->where(['SystemLogs.group_name' => $selectedGroup]);
        }

        if ($this->request->is('ajax')) {
            $search = $this->request->getQuery('search');
            if (!empty($search)) {
                $query->where([
                    'OR' => [
                        'SystemLogs.level LIKE' => '%' . $search . '%',
                        'SystemLogs.message LIKE' => '%' . $search . '%',
                        'SystemLogs.context LIKE' => '%' . $search . '%',
                        'SystemLogs.group_name LIKE' => '%' . $search . '%',
                        'DATE(SystemLogs.created) LIKE' => '%' . $search . '%',
                    ],
                ]);
            }
            $systemLogs = $query->all();
            $this->set(compact('systemLogs'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        $systemLogs = $this->paginate($query);
        $this->set(compact('systemLogs', 'levels', 'groupNames', 'selectedLevel', 'selectedGroup'));

        return null;
    }

    /**
     * View method for displaying a specific system log.
     *
     * @param string|null $id System Log id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $systemLog = $this->SystemLogs->get($id, contain: []);
        $this->set(compact('systemLog'));
    }

    /**
     * Delete system logs based on specified criteria.
     *
     * Handles various deletion scenarios:
     * - Delete all logs
     * - Delete logs by level
     * - Delete logs by group
     * - Delete a single log by ID
     *
     * @param string|null $type The type of deletion ('all', 'level', 'group') or log ID for single deletion
     * @param string|null $value The value associated with the deletion type (level or group name)
     * @return \Cake\Http\Response|null Redirects to the index action after deletion attempt
     * @throws \Cake\Http\Exception\MethodNotAllowedException When the request method is not POST or DELETE
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When a single log for deletion is not found
     */
    public function delete(?string $type = null, ?string $value = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        if ($type === 'all') {
            // Delete all logs
            if ($this->SystemLogs->deleteAll([])) {
                $this->Flash->success(__('All logs have been deleted.'));
            } else {
                $this->Flash->error(__('Unable to delete all logs.'));
            }
        } elseif ($type === 'level' && $value) {
            // Delete logs by level
            if ($this->SystemLogs->deleteAll(['level' => $value])) {
                $this->Flash->success(__('All logs with level {0} have been deleted.', $value));
            } else {
                $this->Flash->error(__('Unable to delete logs with level {0}.', $value));
            }
        } elseif ($type === 'group' && $value) {
            // Delete logs by group
            if ($this->SystemLogs->deleteAll(['group_name' => $value])) {
                $this->Flash->success(__('All logs in group {0} have been deleted.', $value));
            } else {
                $this->Flash->error(__('Unable to delete logs in group {0}.', $value));
            }
        } else {
            // Delete a single log by ID
            $log = $this->SystemLogs->get($type);
            if ($this->SystemLogs->delete($log)) {
                $this->Flash->success(__('The log has been deleted.'));
            } else {
                $this->Flash->error(__('Unable to delete the log.'));
            }
        }

        return $this->redirect(['action' => 'index']);
    }
}
