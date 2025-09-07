<?php
declare(strict_types=1);

namespace App\Controller;

use App\Controller\AppController;

/**
 * Login Test Controller - Simple authentication test
 */
class LoginTestController extends AppController
{
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
            $message = "✅ LOGGED IN as: " . $identity->email . " (Admin: " . ($identity->is_admin ? 'Yes' : 'No') . ")";
            if ($identity->is_admin) {
                $adminUrl = $this->getRequest()->getAttribute('webroot') . 'admin/products/forms';
                $message .= '<br><br><a href="' . $adminUrl . '">Go to Admin Panel</a>';
            }
        } else {
            $message = "❌ NOT LOGGED IN";
            $loginUrl = $this->getRequest()->getAttribute('webroot') . 'admin-test/login';
            $message .= '<br><br><a href="' . $loginUrl . '">Login Here</a>';
        }
        
        $this->set('message', $message);
        $this->viewBuilder()->setLayout(null); // No layout for simple output
    }

    /**
     * Simple login form
     */
    public function login()
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
