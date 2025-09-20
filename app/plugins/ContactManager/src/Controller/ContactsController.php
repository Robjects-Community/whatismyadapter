<?php
declare(strict_types=1);

namespace ContactManager\Controller;

class ContactsController extends ContactManagerAppController
{
    public $uses = ['ContactManager.Contacts'];

    /**
     * Index method
     *
     * @return void
     */
    public function index(): void
    {

        // Fetching all contacts from the Contacts model and paginating them

        $query = $this->Contacts->find('all', [
            'contain' => [],
        ]);

        $contacts = $this->paginate($query);
        // This method is used to list all contacts in the Contacts model.
        $this->paginate = [
            'contain' => [],
        ];
        // Fetching all contacts from the Contacts model and paginating them
        // The paginate method will automatically handle the pagination logic
        $this->set(compact('contacts'));
        // Setting the 'contacts' variable to be used in the view
    }

    /**
     * View method
     *
     * @param string|null $id Contact id.
     * @return void
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function view(?string $id = null): void
    {
        $query = $this->Contacts->find('all', [
            'contain' => [],
        ]);

                    // Fetching a single contact by its ID and setting it to the 'contact' variable
        $this->set(compact('contact'));
        // Setting the 'contact' variable to be used in the view
        $this->set('_serialize', ['contact']);
    }

    /**
     * Add method
     *
     * @return void Redirects on successful add, renders view otherwise.
     */
    public function add(): void
    {
        /*$contact = $this->Contacts->newEntity();
        if ($this->request->is('post')) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->data);
            pr($contact); exit;
            if ($this->Contacts->save($contact)) {
                $this->Flash->success('The contact has been saved.');
                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The contact could not be saved. Please, try again.');
            }
        }
        $this->set(compact('contact'));
        $this->set('_serialize', ['contact']);*/

        $contact = new ContactForm();
        $contactObj = $this->Contacts->newEntity();

        if ($this->request->is('post')) {
            if ($contact->execute($this->request->data)) {
                $contactObj = $this->Contacts->patchEntity($contactObj, $this->request->data);
                if ($this->Contacts->save($contactObj)) {
                    $this->Flash->success('We will get back to you soon.');
                } else {
                    //$isValid = $contact->validate($this->request->data); pr($isValid);
                   // pr($contact->_$errors);
                    $this->Flash->error('User with given email registered previously.');
                }
            } else {
                $this->Flash->error('There was a problem submitting your form.');
            }
        }
        $this->set('contact', $contact);
    }

    /**
     * Edit method
     *
     * @param string|null $id Contact id.
     * @return void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit(?string $id = null)
    {
        $contact = $this->Contacts->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $contact = $this->Contacts->patchEntity($contact, $this->request->data);
            if ($this->Contacts->save($contact)) {
                $this->Flash->success('The contact has been saved.');

                return $this->redirect(['action' => 'index']);
            } else {
                $this->Flash->error('The contact could not be saved. Please, try again.');
            }
        }
        $this->set(compact('contact'));
        $this->set('_serialize', ['contact']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Contact id.
     * @return void Redirects to index.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $contact = $this->Contacts->get($id);
        if ($this->Contacts->delete($contact)) {
            $this->Flash->success('The contact has been deleted.');
        } else {
            $this->Flash->error('The contact could not be deleted. Please, try again.');
        }

        return $this->redirect(['action' => 'index']);
    }
}
