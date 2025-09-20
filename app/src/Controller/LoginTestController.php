<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;

/**
 * Login Test Controller - Simple authentication test
 */
class LoginTestController extends AppController
{
    /**
     * Initialize controller
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        // Allow unauthenticated access to test methods
        $this->Authentication->allowUnauthenticated(['index', 'login']);
    }

    /**
     * Test authentication status
     */
    public function index(): void
    {
        $identity = $this->Authentication->getIdentity();

        if ($identity) {
            $adminStatus = $identity->is_admin ? 'Yes' : 'No';
            $message = '✅ LOGGED IN as: ' . $identity->email . ' (Admin: ' . $adminStatus . ')';
            if ($identity->is_admin) {
                $adminUrl = $this->getRequest()->getAttribute('webroot') . 'admin/products/forms';
                $message .= '<br><br><a href="' . $adminUrl . '">Go to Admin Panel</a>';
            }
        } else {
            $message = '❌ NOT LOGGED IN';
            $loginUrl = $this->getRequest()->getAttribute('webroot') . 'admin-test/login';
            $message .= '<br><br><a href="' . $loginUrl . '">Login Here</a>';
        }

        $this->set('message', $message);
        $this->viewBuilder()->setLayout(null); // No layout for simple output
    }

    /**
     * Simple login form
     *
     * @return \Cake\Http\Response|null
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();
        if ($result->isValid()) {
            // Redirect back to test page after login
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $this->Flash->error('Invalid credentials. Try: admin@test.com / password123');
        }

        $this->viewBuilder()->setLayout(null); // No layout for simple output
    }
}
