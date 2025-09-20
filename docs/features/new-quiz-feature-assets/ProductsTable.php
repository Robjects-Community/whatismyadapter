<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Products Model
 *
 * @method \App\Model\Entity\Product newEmptyEntity()
 * @method \App\Model\Entity\Product newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Product> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Product get(mixed $primaryKey, array|string $finder = 'all', null|\Psr\SimpleCache\CacheInterface|string $cache = null, null|\Closure|string $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Product findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Product patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Product> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Product|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Product saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Product> saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Product> saveManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Product[]|\Cake\Datasource\ResultSetInterface deleteManyOrFail(iterable $entities, array $options = [])
 * @method \App\Model\Entity\Product[]|\Cake\Datasource\ResultSetInterface saveManyOrFail(iterable $entities, array $options = [])
 */
class ProductsTable extends Table
{
    /**
     * Initialize method
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->nonEmptyString('title')
            ->maxLength('title', 255);

        $validator
            ->scalar('manufacturer')
            ->maxLength('manufacturer', 100)
            ->allowEmptyString('manufacturer');

        $validator
            ->scalar('port_type')
            ->maxLength('port_type', 50)
            ->allowEmptyString('port_type');

        $validator
            ->decimal('price')
            ->greaterThan('price', 0)
            ->allowEmptyString('price');

        $validator
            ->decimal('rating')
            ->range('rating', [0, 5])
            ->allowEmptyString('rating');

        $validator
            ->boolean('certified');

        $validator
            ->inList('status', ['pending', 'approved', 'rejected'])
            ->notEmptyString('status');

        return $validator;
    }

    /**
     * Find approved products only
     */
    public function findApproved(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where(['status' => 'approved']);
    }

    /**
     * Find products by manufacturer
     */
    public function findByManufacturer(SelectQuery $query, array $options): SelectQuery
    {
        $manufacturer = $options['manufacturer'] ?? null;
        if ($manufacturer) {
            return $query->where(['manufacturer' => $manufacturer]);
        }
        return $query;
    }

    /**
     * Find products matching quiz criteria
     */
    public function findByQuizCriteria(SelectQuery $query, array $criteria): SelectQuery
    {
        $query = $query->find('approved');

        // Filter by manufacturer if specified
        if (!empty($criteria['manufacturer'])) {
            $manufacturers = (array)$criteria['manufacturer'];
            $query->where(['manufacturer IN' => $manufacturers]);
        }

        // Filter by port type if specified
        if (!empty($criteria['port_type'])) {
            $portTypes = (array)$criteria['port_type'];
            $query->where(['port_type IN' => $portTypes]);
        }

        // Filter by device category if specified
        if (!empty($criteria['device_cat'])) {
            $categories = (array)$criteria['device_cat'];
            $query->where(['device_cat IN' => $categories]);
        }

        // Filter by price range if specified
        if (!empty($criteria['price_range'])) {
            $minPrice = $criteria['price_range'][0] ?? 0;
            $maxPrice = $criteria['price_range'][1] ?? 999999;
            $query->where(['price >=' => $minPrice, 'price <=' => $maxPrice]);
        }

        // Filter by certification if required
        if (!empty($criteria['certified'])) {
            $query->where(['certified' => true]);
        }

        // Order by relevance score and rating
        $query->orderBy(['rel_score' => 'DESC', 'rating' => 'DESC']);

        return $query;
    }

    /**
     * AI-powered product matching
     */
    public function findMatchingProducts(array $quizData): array
    {
        $criteria = $this->extractCriteriaFromQuizData($quizData);
        $products = $this->find('byQuizCriteria', ['criteria' => $criteria])->toArray();

        $scoredProducts = [];
        foreach ($products as $product) {
            $matchScore = $product->matchesFilters($criteria);
            if ($matchScore > 0.3) { // Minimum threshold
                $scoredProducts[] = [
                    'product' => $product,
                    'confidence_score' => $matchScore,
                    'explanation' => $this->generateExplanation($product, $criteria)
                ];
            }
        }

        // Sort by confidence score
        usort($scoredProducts, function($a, $b) {
            return $b['confidence_score'] <=> $a['confidence_score'];
        });

        return array_slice($scoredProducts, 0, 5); // Return top 5 matches
    }

    /**
     * Extract matching criteria from quiz responses
     */
    private function extractCriteriaFromQuizData(array $quizData): array
    {
        $criteria = [];

        // Map quiz responses to search criteria
        foreach ($quizData as $questionId => $answer) {
            switch ($questionId) {
                case 'device_type':
                case 'q1':
                    if ($answer === 'laptop' || $answer === 'no') {
                        $criteria['device_cat'] = ['laptop', 'macbook'];
                    } elseif ($answer === 'phone' || $answer === 'yes') {
                        $criteria['device_cat'] = ['phone', 'smartphone'];
                    }
                    break;

                case 'manufacturer':
                case 'q3_computer':
                    if ($answer === 'apple' || $answer === 'yes') {
                        $criteria['manufacturer'] = ['apple'];
                    }
                    break;

                case 'port_type':
                case 'q4_modern_phone':
                    if ($answer === 'usbc' || $answer === 'yes') {
                        $criteria['port_type'] = ['usb-c'];
                    } elseif ($answer === 'lightning' || $answer === 'no') {
                        $criteria['port_type'] = ['lightning'];
                    }
                    break;

                case 'budget':
                    if (is_array($answer) && count($answer) === 2) {
                        $criteria['price_range'] = $answer;
                    }
                    break;

                case 'features':
                    if (is_array($answer) && in_array('certified', $answer)) {
                        $criteria['certified'] = true;
                    }
                    break;
            }
        }

        return $criteria;
    }

    /**
     * Generate AI explanation for product recommendation
     */
    private function generateExplanation($product, $criteria): string
    {
        $reasons = [];

        if (!empty($criteria['manufacturer']) && in_array($product->manufacturer, $criteria['manufacturer'])) {
            $reasons[] = "matches your preferred {$product->manufacturer} brand";
        }

        if (!empty($criteria['port_type']) && in_array($product->port_type, $criteria['port_type'])) {
            $reasons[] = "has the {$product->port_type} port you need";
        }

        if (!empty($criteria['device_cat']) && in_array($product->device_cat, $criteria['device_cat'])) {
            $reasons[] = "is designed for {$product->device_cat} devices";
        }

        if ($product->certified) {
            $reasons[] = "is officially certified for compatibility";
        }

        if ($product->rating >= 4.5) {
            $reasons[] = "has excellent customer reviews ({$product->rating}/5 stars)";
        }

        $explanation = "This {$product->title} is recommended because it " . implode(', ', $reasons);

        if ($product->price) {
            $explanation .= " and is reasonably priced at {$product->getFormattedPrice()}";
        }

        return $explanation . ".";
    }
}
?>