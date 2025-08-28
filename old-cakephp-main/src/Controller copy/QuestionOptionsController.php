<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * QuestionOptions Controller
 *
 * @property \App\Model\Table\QuestionOptionsTable $QuestionOptions
 */
class QuestionOptionsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $query = $this->QuestionOptions->find()
            ->contain(['Questions']);
        $questionOptions = $this->paginate($query);

        $this->set(compact('questionOptions'));
    }

    /**
     * View method
     *
     * @param string|null $id Question Option id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $questionOption = $this->QuestionOptions->get($id, contain: ['Questions']);
        $this->set(compact('questionOption'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $questionOption = $this->QuestionOptions->newEmptyEntity();
        if ($this->request->is('post')) {
            $questionOption = $this->QuestionOptions->patchEntity($questionOption, $this->request->getData());
            if ($this->QuestionOptions->save($questionOption)) {
                $this->Flash->success(__('The question option has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The question option could not be saved. Please, try again.'));
        }
        $questions = $this->QuestionOptions->Questions->find('list', limit: 200)->all();
        $this->set(compact('questionOption', 'questions'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Question Option id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $questionOption = $this->QuestionOptions->get($id, contain: []);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $questionOption = $this->QuestionOptions->patchEntity($questionOption, $this->request->getData());
            if ($this->QuestionOptions->save($questionOption)) {
                $this->Flash->success(__('The question option has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The question option could not be saved. Please, try again.'));
        }
        $questions = $this->QuestionOptions->Questions->find('list', limit: 200)->all();
        $this->set(compact('questionOption', 'questions'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Question Option id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $questionOption = $this->QuestionOptions->get($id);
        if ($this->QuestionOptions->delete($questionOption)) {
            $this->Flash->success(__('The question option has been deleted.'));
        } else {
            $this->Flash->error(__('The question option could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
