<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * Internationalisations Controller
 *
 * @property \App\Model\Table\InternationalisationsTable $Internationalisations
 */
class InternationalisationsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index(): ?Response
    {
        $statusFilter = $this->request->getQuery('locale');

        $query = $this->Internationalisations->find()
            ->select([
                'Internationalisations.id',
                'Internationalisations.locale',
                'Internationalisations.message_id',
                'Internationalisations.message_str',
                'Internationalisations.created',
                'Internationalisations.modified',
            ]);

        if ($statusFilter !== null) {
            $query->where(['Internationalisations.locale' => $statusFilter]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Internationalisations.locale LIKE' => '%' . $search . '%',
                    'Internationalisations.message_id LIKE' => '%' . $search . '%',
                    'Internationalisations.message_str LIKE' => '%' . $search . '%',
                ],
            ]);
        }
        $internationalisations = $this->paginate($query);
        if ($this->request->is('ajax')) {
            $this->set(compact('internationalisations', 'search'));
            $this->viewBuilder()->setLayout('ajax');

            return $this->render('search_results');
        }

        // Get unique locales for the locale filter
        $localesQuery = $this->Internationalisations->find()
            ->select(['Internationalisations.locale'])
            ->distinct(['Internationalisations.locale'])
            ->orderBy(['Internationalisations.locale' => 'ASC']);

        $locales = $localesQuery->all()->extract('locale')->toList();
        $this->set(compact('internationalisations', 'locales'));

        return null;
    }

    /**
     * View method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $internationalisation = $this->Internationalisations->get($id, contain: []);
        $this->set(compact('internationalisation'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add(): ?Response
    {
        $internationalisation = $this->Internationalisations->newEmptyEntity();
        if ($this->request->is('post')) {
            $internationalisation = $this->Internationalisations->patchEntity(
                $internationalisation,
                $this->request->getData(),
            );
            if ($this->Internationalisations->save($internationalisation)) {
                $this->Flash->success(__('The internationalisation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The internationalisation could not be saved. Please, try again.'));
        }
        $this->set(compact('internationalisation'));

        return null;
    }

    /**
     * Edit method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit(?string $id = null): ?Response
    {
        $internationalisation = $this->Internationalisations->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $internationalisation = $this->Internationalisations->patchEntity(
                $internationalisation,
                $this->request->getData(),
            );
            if ($this->Internationalisations->save($internationalisation)) {
                $this->Flash->success(__('The internationalisation has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The internationalisation could not be saved. Please, try again.'));
        }
        $this->set(compact('internationalisation'));

        return null;
    }

    /**
     * Delete method
     *
     * @param string|null $id Internationalisation id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $internationalisation = $this->Internationalisations->get($id);
        if ($this->Internationalisations->delete($internationalisation)) {
            $this->Flash->success(__('The internationalisation has been deleted.'));
        } else {
            $this->Flash->error(__('The internationalisation could not be deleted. Please, try again.'));
        }

        return $this->redirect($this->referer());
    }
}
