<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Http\Response;

/**
 * CableCapabilities Controller - Admin interface for cable capabilities logical view
 *
 * Provides admin interface for managing cable capability data extracted from
 * the products table prototype schema.
 */
class CableCapabilitiesController extends AppController
{
    /**
     * Initialize method
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->loadModel('CableCapabilities');
        $this->loadModel('Products'); // For return navigation
    }

    /**
     * Index method - List cable capabilities
     */
    public function index(): ?Response
    {
        $this->paginate = [
            'limit' => 25,
            'order' => ['CableCapabilities.numeric_rating' => 'desc'],
            'conditions' => ['CableCapabilities.capability_name IS NOT' => null],
        ];

        $cableCapabilities = $this->paginate($this->CableCapabilities);
        $stats = $this->CableCapabilities->getCapabilityStats();
        $categories = $this->CableCapabilities->getCapabilityCategories();

        // Store search context in session for return navigation
        $this->getRequest()->getSession()->write('Admin.CableCapabilities.returnContext', [
            'url' => $this->getRequest()->getRequestTarget(),
            'search' => $this->getRequest()->getQueryParams(),
        ]);

        $this->set(compact('cableCapabilities', 'stats', 'categories'));
        $this->set('_serialize', ['cableCapabilities']);

        return null;
    }

    /**
     * View method - Show detailed capability information
     */
    public function view(string $id): ?Response
    {
        $capability = $this->CableCapabilities->get($id);

        // Get return context for navigation
        $returnContext = $this->getRequest()->getSession()->read('Admin.CableCapabilities.returnContext');

        $this->set(compact('capability', 'returnContext'));
        $this->set('_serialize', ['capability']);

        return null;
    }

    /**
     * Category view - Show capabilities by category
     */
    public function category(string $category): ?Response
    {
        $this->paginate = [
            'limit' => 25,
            'order' => ['CableCapabilities.numeric_rating' => 'desc'],
        ];

        $capabilities = $this->paginate($this->CableCapabilities->getCapabilitiesByCategory($category));
        $categoryStats = [
            'total' => $capabilities->count(),
            'certified' => $this->CableCapabilities->find()
                ->where(['capability_category' => $category, 'is_certified' => true])
                ->count(),
        ];

        // Store navigation context
        $this->getRequest()->getSession()->write('Admin.CableCapabilities.returnContext', [
            'url' => "/admin/cable-capabilities/category/{$category}",
            'search' => ['category' => $category],
        ]);

        $this->set(compact('capabilities', 'category', 'categoryStats'));

        return null;
    }

    /**
     * Certified capabilities view
     */
    public function certified(): ?Response
    {
        $this->paginate = [
            'limit' => 25,
            'order' => ['CableCapabilities.certification_date' => 'desc'],
        ];

        $certifiedCapabilities = $this->paginate($this->CableCapabilities->getCertifiedCapabilities());
        $certificationStats = $this->CableCapabilities->getCapabilityStats();

        $this->set(compact('certifiedCapabilities', 'certificationStats'));

        return null;
    }

    /**
     * Search capabilities by technical specifications
     */
    public function search(): ?Response
    {
        $searchTerm = $this->getRequest()->getQuery('q');

        if ($searchTerm) {
            $this->paginate = [
                'limit' => 25,
                'order' => ['CableCapabilities.numeric_rating' => 'desc'],
            ];

            $searchResults = $this->paginate($this->CableCapabilities->searchByTechnicalSpecs($searchTerm));

            // Store search context
            $this->getRequest()->getSession()->write('Admin.CableCapabilities.returnContext', [
                'url' => "/admin/cable-capabilities/search?q={$searchTerm}",
                'search' => ['q' => $searchTerm],
            ]);

            $this->set(compact('searchResults', 'searchTerm'));
        } else {
            $searchResults = null;
            $this->set(compact('searchResults'));
        }

        return null;
    }

    /**
     * Analytics dashboard for capabilities
     */
    public function analytics(): ?Response
    {
        $stats = $this->CableCapabilities->getCapabilityStats();
        $categories = $this->CableCapabilities->getCapabilityCategories();

        // Get capability trends by category
        $categoryBreakdown = [];
        foreach ($categories as $category) {
            $categoryBreakdown[$category] = $this->CableCapabilities->find()
                ->where(['capability_category' => $category])
                ->select([
                    'total' => 'COUNT(*)',
                    'certified' => 'SUM(CASE WHEN is_certified = 1 THEN 1 ELSE 0 END)',
                    'avg_rating' => 'AVG(numeric_rating)',
                ])
                ->first();
        }

        $this->set(compact('stats', 'categories', 'categoryBreakdown'));

        return null;
    }

    /**
     * Export capabilities data
     */
    public function export(): Response
    {
        $this->getRequest()->allowMethod(['get']);

        $capabilities = $this->CableCapabilities->find()
            ->select([
                'id', 'title', 'capability_name', 'capability_category',
                'capability_value', 'numeric_rating', 'is_certified',
                'certification_date', 'testing_standard', 'certifying_organization',
            ])
            ->where(['capability_name IS NOT' => null])
            ->orderBy(['capability_category' => 'ASC', 'numeric_rating' => 'DESC'])
            ->toArray();

        $csvData = "ID,Product Title,Capability Name,Category,Value,Rating,Certified,Certification Date,Testing Standard,Certifying Organization\n";

        foreach ($capabilities as $cap) {
            $csvData .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $cap->id,
                str_replace('"', '""', $cap->title ?? ''),
                str_replace('"', '""', $cap->capability_name ?? ''),
                str_replace('"', '""', $cap->capability_category ?? ''),
                str_replace('"', '""', $cap->capability_value ?? ''),
                $cap->numeric_rating ?? '',
                $cap->is_certified ? 'Yes' : 'No',
                $cap->certification_date ? $cap->certification_date->format('Y-m-d') : '',
                str_replace('"', '""', $cap->testing_standard ?? ''),
                str_replace('"', '""', $cap->certifying_organization ?? ''),
            );
        }

        $response = $this->getResponse()
            ->withType('csv')
            ->withHeader('Content-Disposition', 'attachment; filename="cable_capabilities_' . date('Y-m-d') . '.csv"')
            ->withStringBody($csvData);

        return $response;
    }

    /**
     * Return to products with preserved context
     */
    public function returnToProducts(): Response
    {
        $returnContext = $this->getRequest()->getSession()->read('Admin.CableCapabilities.returnContext');

        if ($returnContext && !empty($returnContext['search'])) {
            $queryString = http_build_query($returnContext['search']);

            return $this->redirect("/admin/products?{$queryString}");
        }

        return $this->redirect(['controller' => 'Products', 'action' => 'index']);
    }
}
