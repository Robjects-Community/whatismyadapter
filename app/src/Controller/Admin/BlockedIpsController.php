<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Cache\Cache;
use Cake\Http\Response;

/**
 * BlockedIps Controller
 *
 * @property \App\Model\Table\BlockedIpsTable $BlockedIps
 */
class BlockedIpsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $query = $this->BlockedIps->find()
            ->select([
                'BlockedIps.id',
                'BlockedIps.ip_address',
                'BlockedIps.reason',
                'BlockedIps.blocked_at',
                'BlockedIps.expires_at',
                'BlockedIps.created',
                'BlockedIps.modified',
            ]);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'BlockedIps.ip_address LIKE' => '%' . $search . '%',
                    'BlockedIps.reason LIKE' => '%' . $search . '%',
                ],
            ]);
        }

        $blockedIps = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('blockedIps', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('blockedIps'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        $this->set(compact('blockedIp'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $blockedIp = $this->BlockedIps->newEmptyEntity();
        if ($this->request->is('post')) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                Cache::clear('ip_blocker');
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $blockedIp = $this->BlockedIps->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $blockedIp = $this->BlockedIps->patchEntity($blockedIp, $this->request->getData());
            if ($this->BlockedIps->save($blockedIp)) {
                Cache::clear('ip_blocker');
                $this->Flash->success(__('The blocked ip has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The blocked ip could not be saved. Please, try again.'));
        }
        $this->set(compact('blockedIp'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Blocked Ip id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $blockedIp = $this->BlockedIps->get($id);
        if ($this->BlockedIps->delete($blockedIp)) {
            $this->Flash->success(__('The blocked ip has been deleted.'));
            Cache::clear('ip_blocker');
        } else {
            $this->Flash->error(__('The blocked ip could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
