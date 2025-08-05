<?php
declare(strict_types=1);
namespace App\Controller;

use App\Controller\AppController;
use Cake\Http\Response;
use Cake\Utility\Text;
class ProductsController extends AppController
{

public function importAdapters()
{
    $csvData = $this->request->getUploadedFiles()['csv_file'];
    
    foreach ($this->parseCsv($csvData) as $row) {
        $adapter = $this->Products->newEntity([
            'title' => "{$row['connector_type_a']} to {$row['connector_type_b']} Adapter",
            'connector_type_a' => $row['connector_type_a'],
            'connector_type_b' => $row['connector_type_b'],
            'supports_usb_pd' => (bool)$row['supports_usb_pd'],
            'max_power_delivery' => $row['max_power_delivery'],
            'price' => $row['price_usd'],
            'category_rating' => $row['category_rating'],
            'verification_status' => 'pending',
            'user_id' => $this->Authentication->getIdentity()->id
        ]);
        
        $this->Products->save($adapter);
        }
}
    public function index()
    {
        // Customer-facing adapter catalog
        $adapters = $this->Products->find()
            ->where(['is_published' => true, 'verification_status' => 'verified'])
            ->contain(['Tags'])
            ->order(['featured' => 'DESC', 'view_count' => 'DESC'])
            ->limit(50);
        
        $this->set(compact('adapters'));
    }
    public function indexSimple()
    {
        // Customer-facing adapter catalog
        $adapters = $this->Products->find()
            ->where(['is_published' => true, 'verification_status' => 'verified'])
            ->contain(['Tags'])
            ->order(['featured' => 'DESC', 'view_count' => 'DESC'])
            ->limit(50);
        
        $this->set(compact('adapters'));
    }

    public function view($slug = null)
    {
        // Individual adapter details with compatibility info
        $adapter = $this->Products->findBySlug($slug)
            ->where(['is_published' => true])
            ->contain(['Tags', 'Users'])
            ->firstOrFail();
            
        // Increment view count
        $this->Products->updateAll(['view_count' => $adapter->view_count + 1], ['id' => $adapter->id]);
        
        $this->set(compact('adapter'));
    }
    
    
    public function search()
    {
        // AJAX-powered adapter search by connector types
        $criteria = $this->request->getQuery();
        $adapters = $this->Products->searchAdapters($criteria);
        
        if ($this->request->is('ajax')) {
            $this->viewBuilder()->setLayout('ajax');
            return $this->render('search_results');
        }
        
        $this->set(compact('adapters', 'criteria'));
    }

}