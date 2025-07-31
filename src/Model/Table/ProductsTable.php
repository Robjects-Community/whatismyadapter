<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use App\Utility\SettingsManager;
use ArrayObject;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Log\LogTrait;
use Cake\ORM\Behavior\Translate\TranslateTrait;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use DateTime;

/**
 * Products Table
 *
 * Manages product content with features including:
 * - Multi-language support
 * - Technical specifications
 * - Verification workflow
 * - Affiliate link management
 * - User reviews and ratings
 *
 * @property \Cake\ORM\Association\BelongsTo $Users
 * @property \Cake\ORM\Association\BelongsToMany $Tags
 * @property \Cake\ORM\Association\BelongsToMany $Categories
 * @property \Cake\ORM\Association\HasMany $ProductViews
 * @property \Cake\ORM\Association\HasMany $ProductReviews
 * @property \Cake\ORM\Association\HasMany $ProductSpecifications
 * @property \Cake\ORM\Association\HasMany $ProductConnectors
 * @property \Cake\ORM\Association\HasMany $ProductAffiliateLinks
 * @property \Cake\ORM\Association\HasMany $ProductVerifications
 */
class ProductsTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use QueueableJobsTrait;
    use SeoFieldsTrait;
    use TranslateTrait;

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('products');
        $this->setDisplayField('title');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
        $this->addBehavior('Commentable');
        $this->addBehavior('Orderable', [
            'displayField' => 'title',
        ]);
        $this->addBehavior('Slug');
        $this->addBehavior('ImageAssociable');
        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Products/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Translate', [
            'fields' => [
                'title', 'lede', 'body', 'summary',
                'meta_title', 'meta_description', 'meta_keywords',
                'facebook_description', 'linkedin_description',
                'instagram_description', 'twitter_description',
            ],
            'defaultLocale' => 'en_GB',
            'allowEmptyTranslations' => false,
        ]);

        // Associations
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT',
        ]);

        $this->belongsToMany('Tags', [
            'foreignKey' => 'product_id',
            'targetForeignKey' => 'tag_id',
            'joinTable' => 'products_tags',
        ]);

        $this->belongsToMany('Categories', [
            'className' => 'ProductCategories',
            'foreignKey' => 'product_id',
            'targetForeignKey' => 'category_id',
            'joinTable' => 'products_categories',
        ]);

        $this->hasMany('ProductViews', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);

        $this->hasMany('ProductReviews', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);

        $this->hasMany('ProductSpecifications', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);

        $this->hasMany('ProductConnectors', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);

        $this->hasMany('ProductAffiliateLinks', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);

        $this->hasMany('ProductVerifications', [
            'foreignKey' => 'product_id',
            'dependent' => true,
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('user_id')
            ->notEmptyString('user_id');

        $validator
            ->scalar('title')
            ->maxLength('title', 255)
            ->requirePresence('title', 'create')
            ->notEmptyString('title');

        $validator
            ->scalar('manufacturer')
            ->maxLength('manufacturer', 255)
            ->allowEmptyString('manufacturer');

        $validator
            ->decimal('price')
            ->allowEmptyString('price');

        $validator
            ->decimal('reliability_score')
            ->range('reliability_score', [0, 5])
            ->allowEmptyString('reliability_score');

        $this->addOptionalImageValidation($validator, 'image');

        // manual addition of ratings
        $validator
        ->integer('rating')
        ->range('rating', [1, 5])
        ->requirePresence('rating', 'create')
        ->notEmptyString('rating');
        
        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);
        return $rules;
    }

    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        // Set publication date when product is published
        if ($entity->isDirty('is_published') && $entity->is_published) {
            $entity->published = new DateTime('now');
        }

        // Calculate word count if body is set or modified
        if ($entity->isDirty('body') || ($entity->isNew() && !empty($entity->body))) {
            $strippedBody = strip_tags((string)$entity->body);
            $wordCount = str_word_count($strippedBody);
            $entity->word_count = $wordCount;
        }
    }

    public function afterSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        $noMessage = $options['noMessage'] ?? false;

        // Queue AI-powered verification and enhancement jobs
        if (SettingsManager::read('Products.aiVerificationEnabled') && !$noMessage) {
            $data = [
                'id' => $entity->id,
                'title' => $entity->title,
            ];

            // Queue product verification job
            $this->queueJob('App\Job\ProductVerificationJob', $data);

            // Queue SEO generation if published
            if ($entity->is_published && SettingsManager::read('AI.enabled')) {
                $this->queueJob('App\Job\ProductSeoUpdateJob', $data);
            }
        }
    }

    /**
     * Get published products with optional filters
     */
    public function getPublishedProducts(array $conditions = []): \Cake\ORM\Query
    {
        $baseConditions = [
            'Products.is_published' => 1,
            'Products.verification_status' => 'approved',
        ];

        if (SettingsManager::read('Products.minVerificationScore')) {
            $baseConditions['Products.reliability_score >='] = (float)SettingsManager::read('Products.minVerificationScore');
        }

        $conditions = array_merge($baseConditions, $conditions);

        return $this->find()
            ->where($conditions)
            ->contain(['Users', 'Categories', 'ProductConnectors.ConnectorTypes'])
            ->orderBy(['Products.reliability_score' => 'DESC', 'Products.created' => 'DESC']);
    }
}
