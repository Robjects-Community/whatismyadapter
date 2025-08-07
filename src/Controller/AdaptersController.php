<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Adapters Controller
 */
class AdaptersController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('status');
        $query = $this->Adapters->find()
            ->select([
                'Adapters.id',
                'Adapters.product_id',
                'Adapters.connector_type_a',
                'Adapters.connector_type_b',
                'Adapters.supports_usb_pd',
                'Adapters.max_power_delivery',
                'Adapters.usb_version',
                'Adapters.supports_displayport',
                'Adapters.supports_hdmi',
                'Adapters.supports_alt_mode',
                'Adapters.supports_thunderbolt',
                'Adapters.supports_quick_charge',
                'Adapters.supports_audio',
                'Adapters.cable_length',
                'Adapters.wire_gauge',
                'Adapters.shielding_type',
                'Adapters.is_active_cable',
                'Adapters.category_rating',
                'Adapters.shopping_link',
                'Adapters.verification_date',
                'Adapters.technical_notes',
                'Adapters.created',
                'Adapters.modified',
            ]);

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Adapters.connector_type_a LIKE' => '%' . $search . '%',
                    'Adapters.connector_type_b LIKE' => '%' . $search . '%',
                    'Adapters.max_power_delivery LIKE' => '%' . $search . '%',
                    'Adapters.usb_version LIKE' => '%' . $search . '%',
                    'Adapters.cable_length LIKE' => '%' . $search . '%',
                    'Adapters.wire_gauge LIKE' => '%' . $search . '%',
                    'Adapters.shielding_type LIKE' => '%' . $search . '%',
                    'Adapters.category_rating LIKE' => '%' . $search . '%',
                    'Adapters.shopping_link LIKE' => '%' . $search . '%',
                    'Adapters.technical_notes LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $adapters = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('adapters', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }
        $this->set(compact('adapters'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null)
    {
        $adapter = $this->Adapters->get($id, contain: []);
        $this->set(compact('adapter'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $adapter = $this->Adapters->newEmptyEntity();
        if ($this->request->is('post')) {
            $adapter = $this->Adapters->patchEntity($adapter, $this->request->getData());
            if ($this->Adapters->save($adapter)) {
                $this->Flash->success(__('The adapter has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }
        $this->set(compact('adapter'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $adapter = $this->Adapters->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $adapter = $this->Adapters->patchEntity($adapter, $this->request->getData());
            if ($this->Adapters->save($adapter)) {
                $this->Flash->success(__('The adapter has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The adapter could not be saved. Please, try again.'));
        }
        $this->set(compact('adapter'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Adapter id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $adapter = $this->Adapters->get($id);
        if ($this->Adapters->delete($adapter)) {
            $this->Flash->success(__('The adapter has been deleted.'));
        } else {
            $this->Flash->error(__('The adapter could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
