<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\Query;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ProductsTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use TranslateTrait;

    /**
     * Initialize hook
     *
     * @param array $config The configuration settings provided to this table
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Slug', [
            'slug' => 'slug',
            'displayField' => 'title',
        ]);

        // Reliability scoring behavior - verification-focused v2.0
        $this->addBehavior('Reliability', [
            'fields' => [
                // Critical verification fields (high weight, require external validation)
                'technical_specifications' => 0.25,  // JSON specs are critical
                'testing_standard' => 0.20,          // Must have testing standard
                'certifying_organization' => 0.15,   // Must have certifier
                'numeric_rating' => 0.10,            // Must have performance rating
                
                // Basic product information (lower weight without verification)
                'title' => 0.08,
                'description' => 0.08,
                'manufacturer' => 0.05,
                'model_number' => 0.03,
                'price' => 0.03,
                'currency' => 0.01,
                'image' => 0.01,
                'alt_text' => 0.01,
            ],
            'scoring_version' => 'v2.0',  // Updated version with verification focus
            'verification_required' => true, // Products need verification to score well
        ]);

        // Core relationships
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER',
        ]);

        $this->belongsTo('Articles', [
            'foreignKey' => 'article_id',
            'joinType' => 'LEFT',
            'propertyName' => 'article',
        ]);

        // Many-to-many with Tags (unified tagging system)
        $this->belongsToMany('Tags', [
            'foreignKey' => 'product_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'products_tags',
        ]);

        // Reliability tracking association
        $this->hasOne('ProductsReliability', [
            'className' => 'ProductsReliability',
            'bindingKey' => 'id',
            'foreignKey' => 'foreign_key',
            'conditions' => ['ProductsReliability.model' => 'Products'],
            'dependent' => true
        ]);
    }

    /**
     * Default validation rules
     *
     * @param \Cake\Validation\Validator $validator Validator instance
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('id')
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        // $validator
        //     ->scalar('slug')
        //     ->maxLength('slug', 191)
        //     ->requirePresence('slug', 'create')
        //     ->notEmptyString('slug')
        $validator
            ->scalar('description')
            ->allowEmptyString('description');

        $validator
            ->scalar('manufacturer')
            ->maxLength('manufacturer', 255)
            ->allowEmptyString('manufacturer');

        $validator
            ->scalar('model_number')
            ->maxLength('model_number', 255)
            ->allowEmptyString('model_number');

        $validator
            ->decimal('price')
            ->allowEmptyString('price');

        $validator
            ->boolean('is_published')
            ->notEmptyString('is_published');

        $validator
            ->boolean('featured')
            ->notEmptyString('featured');

        return $validator;
    }

    /**
     * Get published products with optional filtering
     */
    public function getPublishedProducts(array $options = []): Query
    {
        $query = $this->find()
            ->where(['Products.is_published' => true])
            ->contain(['Users', 'Tags', 'Articles', 'ProductsReliability'])
            ->orderBy(['Products.created' => 'DESC']);

        // Apply filters
        if (!empty($options['tag'])) {
            $query->matching('Tags', function ($q) use ($options) {
                return $q->where(['Tags.slug' => $options['tag']]);
            });
        }

        if (!empty($options['manufacturer'])) {
            $query->where(['Products.manufacturer LIKE' => '%' . $options['manufacturer'] . '%']);
        }

        if (!empty($options['featured'])) {
            $query->where(['Products.featured' => true]);
        }

        return $query;
    }

    /**
     * Get products by verification status
     */
    public function getProductsByStatus(string $status): Query
    {
        return $this->find()
            ->where(['verification_status' => $status])
            ->contain(['Users', 'Tags', 'ProductsReliability'])
            ->orderBy(['created' => 'ASC']);
    }

    /**
     * Search products across title, description, manufacturer
     */
    public function searchProducts(string $term): Query
    {
        return $this->find()
            ->where([
                'OR' => [
                    'Products.title LIKE' => "%{$term}%",
                    'Products.description LIKE' => "%{$term}%",
                    'Products.manufacturer LIKE' => "%{$term}%",
                    'Products.model_number LIKE' => "%{$term}%",
                ],
            ])
            ->contain(['Tags', 'Users', 'ProductsReliability'])
            ->where(['Products.is_published' => true]);
    }

    /**
     * Get products with same tags (for related products)
     */
    public function getRelatedProducts(string $productId, int $limit = 5): array
    {
        $product = $this->get($productId, ['contain' => ['Tags']]);

        if (empty($product->tags)) {
            return [];
        }

        $tagIds = array_map(fn($tag) => $tag->id, $product->tags);

        return $this->find()
            ->matching('Tags', function ($q) use ($tagIds) {
                return $q->where(['Tags.id IN' => $tagIds]);
            })
            ->where([
                'Products.id !=' => $productId,
                'Products.is_published' => true,
            ])
            ->limit($limit)
            ->toArray();
    }

    /**
     * Increment view count
     */
    public function incrementViewCount(string $productId): bool
    {
        return $this->updateAll(
            ['view_count = view_count + 1'],
            ['id' => $productId],
        );
    }

    /**
     * Enhanced search across all prototype fields
     */
    public function advancedSearch(array $filters = []): Query
    {
        $query = $this->find()
            ->contain(['Users', 'Tags', 'ProductsReliability'])
            ->where(['Products.is_published' => true]);

        // Capability-based filters
        if (!empty($filters['capability_name'])) {
            $query->where(['Products.capability_name LIKE' => '%' . $filters['capability_name'] . '%']);
        }
        
        if (!empty($filters['capability_category'])) {
            $query->where(['Products.capability_category' => $filters['capability_category']]);
        }

        // Port/Connector filters
        if (!empty($filters['port_family'])) {
            $query->where(['Products.port_family' => $filters['port_family']]);
        }
        
        if (!empty($filters['form_factor'])) {
            $query->where(['Products.form_factor' => $filters['form_factor']]);
        }
        
        if (!empty($filters['connector_gender'])) {
            $query->where(['Products.connector_gender' => $filters['connector_gender']]);
        }

        // Device compatibility filters
        if (!empty($filters['device_category'])) {
            $query->where(['Products.device_category' => $filters['device_category']]);
        }
        
        if (!empty($filters['device_brand'])) {
            $query->where(['Products.device_brand LIKE' => '%' . $filters['device_brand'] . '%']);
        }
        
        if (!empty($filters['compatibility_level'])) {
            $query->where(['Products.compatibility_level' => $filters['compatibility_level']]);
        }

        // Certification filters
        if (isset($filters['is_certified'])) {
            $query->where(['Products.is_certified' => (bool)$filters['is_certified']]);
        }
        
        if (!empty($filters['certifying_organization'])) {
            $query->where(['Products.certifying_organization LIKE' => '%' . $filters['certifying_organization'] . '%']);
        }

        // Rating filters
        if (!empty($filters['min_performance_rating'])) {
            $query->where(['Products.performance_rating >=' => $filters['min_performance_rating']]);
        }
        
        if (!empty($filters['min_numeric_rating'])) {
            $query->where(['Products.numeric_rating >=' => $filters['min_numeric_rating']]);
        }

        // Technical specs filter
        if (!empty($filters['technical_search'])) {
            $query->where([
                'OR' => [
                    'JSON_UNQUOTE(JSON_EXTRACT(Products.technical_specifications, "$.description")) LIKE' => '%' . $filters['technical_search'] . '%',
                    'Products.spec_description LIKE' => '%' . $filters['technical_search'] . '%',
                    'Products.adapter_functionality LIKE' => '%' . $filters['technical_search'] . '%'
                ]
            ]);
        }

        return $query;
    }

    /**
     * Find products for quiz-based matching
     * 
     * @param array $answers Quiz answers from user
     * @param array $constraints Additional filtering constraints
     * @return Query
     */
    public function findForQuiz(array $answers = [], array $constraints = []): Query
    {
        $query = $this->find()
            ->where(['Products.is_published' => true])
            ->contain(['Users', 'Tags', 'ProductsReliability'])
            ->orderBy(['Products.reliability_score' => 'DESC', 'Products.numeric_rating' => 'DESC']);

        // Apply basic filters based on quiz answers
        if (!empty($answers['device_type'])) {
            $deviceType = strtolower($answers['device_type']);
            $query->where([
                'OR' => [
                    'Products.device_category LIKE' => "%{$deviceType}%",
                    'Products.title LIKE' => "%{$deviceType}%",
                    'Products.description LIKE' => "%{$deviceType}%"
                ]
            ]);
        }

        if (!empty($answers['manufacturer'])) {
            $query->where(['Products.manufacturer LIKE' => '%' . $answers['manufacturer'] . '%']);
        }

        if (!empty($answers['port_type'])) {
            $query->where(['Products.port_family LIKE' => '%' . $answers['port_type'] . '%']);
        }

        if (!empty($answers['budget_range'])) {
            if (is_array($answers['budget_range'])) {
                $min = $answers['budget_range']['min'] ?? null;
                $max = $answers['budget_range']['max'] ?? null;
                if ($min) $query->where(['Products.price >=' => $min]);
                if ($max) $query->where(['Products.price <=' => $max]);
            }
        }

        if (!empty($answers['certification_required']) && $answers['certification_required'] === 'yes') {
            $query->where(['Products.is_certified' => true]);
        }

        // Apply additional constraints
        if (!empty($constraints['limit'])) {
            $query->limit($constraints['limit']);
        }

        if (!empty($constraints['featured_only'])) {
            $query->where(['Products.featured' => true]);
        }

        return $query;
    }

    /**
     * Score products with AI-assisted matching
     * 
     * @param array $products List of products to score
     * @param array $answers User quiz answers
     * @return array Product IDs mapped to confidence scores
     */
    public function scoreWithAi(array $products, array $answers): array
    {
        $scores = [];
        
        foreach ($products as $product) {
            $score = $this->calculateProductScore($product, $answers);
            $scores[$product->id] = $score;
        }

        // Sort by score (highest first)
        arsort($scores);
        
        return $scores;
    }

    /**
     * Calculate individual product score based on quiz answers
     * 
     * @param \App\Model\Entity\Product $product
     * @param array $answers
     * @return float Score between 0.0 and 1.0
     */
    private function calculateProductScore($product, array $answers): float
    {
        $score = 0.0;
        $totalWeight = 0.0;

        // Device type matching (weight: 30%)
        if (!empty($answers['device_type'])) {
            $weight = 0.3;
            $totalWeight += $weight;
            
            $deviceType = strtolower($answers['device_type']);
            $productFields = [
                strtolower($product->device_category ?? ''),
                strtolower($product->title ?? ''),
                strtolower($product->description ?? '')
            ];
            
            foreach ($productFields as $field) {
                if (str_contains($field, $deviceType)) {
                    $score += $weight;
                    break;
                }
            }
        }

        // Manufacturer matching (weight: 20%)
        if (!empty($answers['manufacturer'])) {
            $weight = 0.2;
            $totalWeight += $weight;
            
            $manufacturer = strtolower($answers['manufacturer']);
            $productManufacturer = strtolower($product->manufacturer ?? '');
            
            if (str_contains($productManufacturer, $manufacturer)) {
                $score += $weight;
            }
        }

        // Port type matching (weight: 25%)
        if (!empty($answers['port_type'])) {
            $weight = 0.25;
            $totalWeight += $weight;
            
            $portType = strtolower($answers['port_type']);
            $productPort = strtolower($product->port_family ?? '');
            
            if (str_contains($productPort, $portType)) {
                $score += $weight;
            }
        }

        // Price matching (weight: 15%)
        if (!empty($answers['budget_range']) && $product->price) {
            $weight = 0.15;
            $totalWeight += $weight;
            
            if (is_array($answers['budget_range'])) {
                $min = $answers['budget_range']['min'] ?? 0;
                $max = $answers['budget_range']['max'] ?? 9999;
                
                if ($product->price >= $min && $product->price <= $max) {
                    $score += $weight;
                }
            }
        }

        // Certification bonus (weight: 10%)
        if (!empty($answers['certification_required'])) {
            $weight = 0.1;
            $totalWeight += $weight;
            
            if ($answers['certification_required'] === 'yes' && $product->is_certified) {
                $score += $weight;
            } elseif ($answers['certification_required'] === 'no') {
                // No penalty for not requiring certification
                $score += $weight * 0.5;
            }
        }

        // Normalize score
        return $totalWeight > 0 ? $score / $totalWeight : 0.0;
    }

    /**
     * Get products needing normalization (prototype cleanup)
     */
    public function getByPortCompatibility(string $portFamily, string $formFactor = null): Query
    {
        $query = $this->find()
            ->where(['Products.port_family' => $portFamily])
            ->contain(['Users', 'Tags'])
            ->orderBy(['Products.performance_rating' => 'DESC']);

        if ($formFactor) {
            $query->where(['Products.form_factor' => $formFactor]);
        }

        return $query;
    }

    /**
     * Get products by device compatibility
     */
    public function getByDeviceCompatibility(string $deviceCategory, string $deviceBrand = null): Query
    {
        $query = $this->find()
            ->where(['Products.device_category' => $deviceCategory])
            ->contain(['Users', 'Tags'])
            ->orderBy(['Products.compatibility_level' => 'ASC', 'Products.performance_rating' => 'DESC']);

        if ($deviceBrand) {
            $query->where(['Products.device_brand LIKE' => '%' . $deviceBrand . '%']);
        }

        return $query;
    }

    /**
     * Get certified products only
     */
    public function getCertifiedProducts(): Query
    {
        return $this->find()
            ->where([
                'Products.is_certified' => true,
                'Products.certifying_organization IS NOT' => null
            ])
            ->contain(['Users', 'Tags'])
            ->orderBy(['Products.certification_date' => 'DESC']);
    }

    /**
     * Get products needing normalization (prototype cleanup)
     */
    public function getProductsNeedingNormalization(): Query
    {
        return $this->find()
            ->where(['Products.needs_normalization' => true])
            ->contain(['Users'])
            ->orderBy(['Products.created' => 'ASC']);
    }

    /**
     * Insert sample test data to demonstrate prototype fields
     */
    public function insertSampleData(): bool
    {
        $sampleProducts = [
            [
                'id' => '550e8400-e29b-41d4-a716-446655440001',
                'user_id' => '550e8400-e29b-41d4-a716-446655440000', // Assuming admin user exists
                'title' => 'USB-C to HDMI 4K Adapter',
                'slug' => 'usb-c-to-hdmi-4k-adapter',
                'description' => 'High-quality USB-C to HDMI adapter supporting 4K@60Hz output',
                'manufacturer' => 'TechConnector',
                'model_number' => 'TC-UCH4K-001',
                'price' => 29.99,
                'currency' => 'USD',
                'image' => 'usb-c-hdmi-adapter.jpg',
                'alt_text' => 'USB-C to HDMI 4K adapter with cable',
                
                // Capability fields
                'capability_name' => 'Video Output Conversion',
                'capability_category' => 'Display',
                'technical_specifications' => json_encode([
                    'max_resolution' => '4096x2160@60Hz',
                    'color_depth' => '8-bit',
                    'hdr_support' => true,
                    'audio_passthrough' => true
                ]),
                'testing_standard' => 'HDMI 2.0 Compliance',
                'certifying_organization' => 'HDMI Licensing LLC',
                'capability_value' => '4K@60Hz',
                'numeric_rating' => 8.5,
                'is_certified' => true,
                'certification_date' => '2024-01-15',
                
                // Category fields
                'parent_category_name' => 'Display Adapters',
                'category_description' => 'Adapters for converting video signals between different connector types',
                'category_icon' => 'fas fa-tv',
                'display_order' => 1,
                
                // Port/Connector fields
                'port_type_name' => 'USB-C',
                'endpoint_position' => 'end_a',
                'is_detachable' => false,
                'adapter_functionality' => 'Converts USB-C DisplayPort Alt Mode to HDMI output',
                'port_family' => 'USB',
                'form_factor' => 'Type-C',
                'connector_gender' => 'Male',
                'pin_count' => 24,
                'max_voltage' => 5.0,
                'max_current' => 3.0,
                'data_pin_count' => 4,
                'power_pin_count' => 4,
                'ground_pin_count' => 4,
                'electrical_shielding' => 'Braided Shield',
                'durability_cycles' => 10000,
                
                // Device compatibility
                'device_category' => 'Laptop',
                'device_brand' => 'Apple',
                'device_model' => 'MacBook Pro',
                'compatibility_level' => 'Full',
                'compatibility_notes' => 'Works with all MacBook Pro models 2016 and later',
                'performance_rating' => 9.2,
                'verification_date' => '2024-02-01',
                'verified_by' => 'TechConnector QA Team',
                'user_reported_rating' => 8.8,
                
                // Physical specs
                'physical_spec_name' => 'Cable Length',
                'spec_value' => '6 inches',
                'numeric_value' => 6.0,
                'spec_type' => 'measurement',
                'measurement_unit' => 'inches',
                'spec_description' => 'Short 6-inch cable perfect for portable use',
                'introduced_date' => '2016-01-01',
                'physical_specs_summary' => '6" cable, aluminum housing',
                
                // Administrative
                'prototype_notes' => 'Sample data for testing prototype schema - USB-C adapter',
                'needs_normalization' => true,
                'is_published' => true,
                'featured' => true,
                'verification_status' => 'verified',
                'reliability_score' => 9.1,
                'view_count' => 0
            ],
            [
                'id' => '550e8400-e29b-41d4-a716-446655440002',
                'user_id' => '550e8400-e29b-41d4-a716-446655440000',
                'title' => 'Lightning to USB-A Cable',
                'slug' => 'lightning-to-usb-a-cable',
                'description' => 'MFi Certified Lightning to USB-A cable for iPhone and iPad',
                'manufacturer' => 'AppleConnect',
                'model_number' => 'AC-LTU-MFI-3FT',
                'price' => 19.99,
                'currency' => 'USD',
                'image' => 'lightning-usb-cable.jpg',
                'alt_text' => 'White Lightning to USB-A cable',
                
                // Capability fields
                'capability_name' => 'Data Transfer & Charging',
                'capability_category' => 'Power & Data',
                'technical_specifications' => json_encode([
                    'data_rate' => 'USB 2.0 - 480 Mbps',
                    'charging_power' => '12W',
                    'mfi_certified' => true
                ]),
                'testing_standard' => 'MFi Certification',
                'certifying_organization' => 'Apple Inc.',
                'capability_value' => '2.4A Charging',
                'numeric_rating' => 7.8,
                'is_certified' => true,
                'certification_date' => '2023-09-20',
                
                // Category fields
                'parent_category_name' => 'Charging Cables',
                'category_description' => 'Cables for charging and data transfer',
                'category_icon' => 'fas fa-bolt',
                'display_order' => 2,
                
                // Port/Connector fields
                'port_type_name' => 'Lightning',
                'endpoint_position' => 'end_b',
                'is_detachable' => false,
                'adapter_functionality' => 'Bidirectional data transfer and device charging',
                'port_family' => 'Lightning',
                'form_factor' => 'Standard',
                'connector_gender' => 'Male',
                'pin_count' => 8,
                'max_voltage' => 5.0,
                'max_current' => 2.4,
                'data_pin_count' => 2,
                'power_pin_count' => 2,
                'ground_pin_count' => 2,
                'electrical_shielding' => 'Aluminum Foil',
                'durability_cycles' => 5000,
                
                // Device compatibility
                'device_category' => 'Smartphone',
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 14',
                'compatibility_level' => 'Full',
                'compatibility_notes' => 'Compatible with iPhone 5 and later, iPad 4th gen and later',
                'performance_rating' => 8.5,
                'verification_date' => '2024-01-10',
                'verified_by' => 'AppleConnect Testing',
                'user_reported_rating' => 8.2,
                
                // Physical specs
                'physical_spec_name' => 'Cable Length',
                'spec_value' => '3 feet',
                'numeric_value' => 3.0,
                'spec_type' => 'measurement',
                'measurement_unit' => 'feet',
                'spec_description' => 'Standard 3-foot length for everyday use',
                'introduced_date' => '2012-09-21',
                'physical_specs_summary' => '3ft cable, TPE jacket',
                
                // Administrative
                'prototype_notes' => 'Sample data for testing prototype schema - Lightning cable',
                'needs_normalization' => true,
                'is_published' => true,
                'featured' => false,
                'verification_status' => 'verified',
                'reliability_score' => 8.3,
                'view_count' => 0
            ]
        ];

        try {
            foreach ($sampleProducts as $productData) {
                $productData['created'] = date('Y-m-d H:i:s');
                $productData['modified'] = date('Y-m-d H:i:s');
                
                $product = $this->newEntity($productData);
                if (!$this->save($product)) {
                    $this->log('Failed to insert sample product: ' . json_encode($product->getErrors()), 'error');
                    return false;
                }
            }
            
            $this->log('Successfully inserted ' . count($sampleProducts) . ' sample products', 'info');
            return true;
        } catch (\Exception $e) {
            $this->log('Error inserting sample data: ' . $e->getMessage(), 'error');
            return false;
        }
    }
}
