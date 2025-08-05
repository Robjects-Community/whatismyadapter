<?php
namespace App\Controller\Admin;

use App\Test\TestCase\AppControllerTestCase;
use Cake\Http\Response;

class AdaptersControllerTest extends AppControllerTestCase
{
    public function index()
    {
        // Your existing detailed query, adapted for admin (with published finder)
        $statusFilter = $this->request->getQuery('status');  // Keep if needed for admin filtering
        
        $query = $this->fetchTable('Adapters')
            ->find('published')  // Custom finder for published adapters (add this to AdaptersTable if not present)
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
            ])
            ->contain(['Tags'])  // Include associated tags as per the snippet
            ->limit(10);  // Limit results as per the snippet

        // Keep your search logic
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
}