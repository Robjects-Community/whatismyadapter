namespace App\Controller\Admin;

use App\Controller\Admin\AppController;

class AdaptersController extends AppController {
    public function index() {  // Or 'featured' if it's a custom action
        $adapters = $this->Adapters->find('all')->where(['featured' => true]);  // Example query
        $this->set(compact('adapters'));
    }
}
