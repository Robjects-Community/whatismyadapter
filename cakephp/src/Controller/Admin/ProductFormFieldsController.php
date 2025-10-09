<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use App\Service\ProductFormFieldService;
use Cake\Http\Response;
use Exception;

/**
 * ProductFormFields Controller
 *
 * Handles CRUD operations for managing dynamic product form fields
 * in the admin interface
 */
class ProductFormFieldsController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        // ProductFormFields model is automatically loaded based on controller name
    }

    /**
     * Index method - List all form fields
     *
     * @return \Cake\Http\Response|null Renders view
     */
    public function index(): ?Response
    {
        $productFormFields = $this->paginate($this->ProductFormFields->find('all')->orderBy(['display_order' => 'ASC']));

        $this->set(compact('productFormFields'));

        return null;
    }

    /**
     * View method - Display a single form field
     *
     * @param string|null $id ProductFormField id
     * @return \Cake\Http\Response|null Renders view
     * @throws \App\Controller\Admin\RecordNotFoundException When record not found
     */
    public function view(?string $id = null): ?Response
    {
        $productFormField = $this->ProductFormFields->get($id);

        $this->set(compact('productFormField'));

        return null;
    }

    /**
     * Add method - Create a new form field
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise
     */
    public function add(): ?Response
    {
        $productFormField = $this->ProductFormFields->newEmptyEntity();

        if ($this->request->is('post')) {
            $productFormField = $this->ProductFormFields->patchEntity($productFormField, $this->request->getData());

            // Set display_order if not provided
            if (empty($productFormField->display_order)) {
                $maxOrder = $this->ProductFormFields->find()
                    ->select(['max_order' => 'MAX(display_order)'])
                    ->first();
                $productFormField->display_order = ($maxOrder->max_order ?? 0) + 10;
            }

            if ($this->ProductFormFields->save($productFormField)) {
                $this->Flash->success(__('The product form field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product form field could not be saved. Please, try again.'));
        }

        $this->set(compact('productFormField'));

        return null;
    }

    /**
     * Edit method - Edit an existing form field
     *
     * @param string|null $id ProductFormField id
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise
     * @throws \App\Controller\Admin\RecordNotFoundException When record not found
     */
    public function edit(?string $id = null): ?Response
    {
        $productFormField = $this->ProductFormFields->get($id);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $productFormField = $this->ProductFormFields->patchEntity($productFormField, $this->request->getData());

            if ($this->ProductFormFields->save($productFormField)) {
                $this->Flash->success(__('The product form field has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The product form field could not be saved. Please, try again.'));
        }

        $this->set(compact('productFormField'));

        return null;
    }

    /**
     * Delete method - Delete a form field
     *
     * @param string|null $id ProductFormField id
     * @return \Cake\Http\Response|null Redirects to index
     * @throws \App\Controller\Admin\RecordNotFoundException When record not found
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $productFormField = $this->ProductFormFields->get($id);

        if ($this->ProductFormFields->delete($productFormField)) {
            $this->Flash->success(__('The product form field has been deleted.'));
        } else {
            $this->Flash->error(__('The product form field could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Reorder method - Update sort order of fields
     * Can handle both AJAX POST requests and URL parameter requests
     *
     * @param string|null $id Field ID (for single field reorder)
     * @param string|null $direction Direction ('up' or 'down')
     * @return \Cake\Http\Response|null JSON response or redirect
     */
    public function reorder(?string $id = null, ?string $direction = null): ?Response
    {
        $this->request->allowMethod(['post', 'get']);

        // Handle AJAX bulk reorder via POST data
        if ($this->request->is('post') && $this->request->getData('field_ids')) {
            $fieldIds = $this->request->getData('field_ids');

            if (!is_array($fieldIds)) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Invalid field IDs provided',
                    ]));
            }

            try {
                foreach ($fieldIds as $index => $fieldId) {
                    $field = $this->ProductFormFields->get($fieldId);
                    $field->display_order = ($index + 1) * 10;
                    $this->ProductFormFields->save($field);
                }

                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'message' => 'Field order updated successfully',
                    ]));
            } catch (Exception $e) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Error updating field order: ' . $e->getMessage(),
                    ]));
            }
        }

        // Handle single field up/down reorder via URL parameters
        if ($id && $direction && in_array($direction, ['up', 'down'])) {
            try {
                $field = $this->ProductFormFields->get($id);
                $currentOrder = $field->display_order;

                if ($direction === 'up') {
                    // Find field with next lower order
                    $swapField = $this->ProductFormFields->find()
                        ->where(['display_order <' => $currentOrder])
                        ->orderBy(['display_order' => 'DESC'])
                        ->first();
                } else {
                    // Find field with next higher order
                    $swapField = $this->ProductFormFields->find()
                        ->where(['display_order >' => $currentOrder])
                        ->orderBy(['display_order' => 'ASC'])
                        ->first();
                }

                if ($swapField) {
                    // Swap the order values
                    $swapOrder = $swapField->display_order;
                    $swapField->display_order = $currentOrder;
                    $field->display_order = $swapOrder;

                    $this->ProductFormFields->save($field);
                    $this->ProductFormFields->save($swapField);

                    $this->Flash->success(__('Field order updated successfully.'));
                } else {
                    $this->Flash->warning(__('Field is already at the {0}.', $direction === 'up' ? 'top' : 'bottom'));
                }
            } catch (Exception $e) {
                $this->Flash->error(__('Error updating field order: {0}', $e->getMessage()));
            }

            return $this->redirect(['action' => 'index']);
        }

        // If no valid parameters, redirect to index
        return $this->redirect(['action' => 'index']);
    }

    /**
     * Toggle AI enabled status for a field
     *
     * @param string|null $id ProductFormField id
     * @return \Cake\Http\Response JSON response
     */
    public function toggleAi(?string $id = null): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $productFormField = $this->ProductFormFields->get($id);
            $productFormField->ai_enabled = !$productFormField->ai_enabled;

            if ($this->ProductFormFields->save($productFormField)) {
                return $this->response
                    ->withType('application/json')
                    ->withStringBody(json_encode([
                        'success' => true,
                        'ai_enabled' => $productFormField->ai_enabled,
                        'message' => 'AI status updated successfully',
                    ]));
            } else {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(500)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'message' => 'Failed to update AI status',
                    ]));
            }
        } catch (Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => 'Error updating AI status: ' . $e->getMessage(),
                ]));
        }
    }

    /**
     * Test AI suggestion for a specific field
     *
     * @return \Cake\Http\Response JSON response with AI suggestion
     */
    public function testAi(): Response
    {
        $this->request->allowMethod(['post']);

        try {
            $fieldId = $this->request->getData('field_id');
            $formData = $this->request->getData('form_data', []);

            if (empty($fieldId)) {
                return $this->response
                    ->withType('application/json')
                    ->withStatus(400)
                    ->withStringBody(json_encode([
                        'success' => false,
                        'error' => 'Field ID is required',
                    ]));
            }

            $productFormFieldService = new ProductFormFieldService();
            $suggestion = $productFormFieldService->getAiSuggestion($fieldId, $formData);

            return $this->response
                ->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'suggestion' => $suggestion,
                    'message' => $suggestion ? 'AI suggestion generated' : 'No suggestion available',
                ]));
        } catch (Exception $e) {
            return $this->response
                ->withType('application/json')
                ->withStatus(500)
                ->withStringBody(json_encode([
                    'success' => false,
                    'error' => 'Error generating AI suggestion: ' . $e->getMessage(),
                ]));
        }
    }

    /**
     * Reset field order to default increments
     *
     * @return \Cake\Http\Response Redirects to index
     */
    public function resetOrder(): Response
    {
        try {
            $fields = $this->ProductFormFields->find('all')->orderBy(['display_order' => 'ASC'])->toArray();

            foreach ($fields as $index => $field) {
                $field->display_order = ($index + 1) * 10;
                $this->ProductFormFields->save($field);
            }

            $this->Flash->success(__('Field order has been reset successfully.'));
        } catch (Exception $e) {
            $this->Flash->error(__('Error resetting field order: {0}', $e->getMessage()));
        }

        return $this->redirect(['action' => 'index']);
    }
}
