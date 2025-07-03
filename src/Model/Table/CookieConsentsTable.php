<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\CookieConsent;
use Cake\Http\Cookie\Cookie;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use DateTime;

/**
 * CookieConsents Model
 *
 * @property \App\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @method \App\Model\Entity\CookieConsent newEmptyEntity()
 * @method \App\Model\Entity\CookieConsent newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\CookieConsent> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\CookieConsent get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\CookieConsent findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\CookieConsent patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\CookieConsent> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\CookieConsent|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\CookieConsent saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\CookieConsent>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\CookieConsent> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class CookieConsentsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('cookie_consents');
        $this->setDisplayField('ip_address');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->uuid('user_id')
            ->allowEmptyString('user_id');

        $validator
            ->scalar('session_id')
            ->maxLength('session_id', 255)
            ->allowEmptyString('session_id');

        $validator
            ->boolean('analytics_consent')
            ->notEmptyString('analytics_consent');

        $validator
            ->boolean('functional_consent')
            ->notEmptyString('functional_consent');

        $validator
            ->boolean('marketing_consent')
            ->notEmptyString('marketing_consent');

        $validator
            ->boolean('essential_consent')
            ->notEmptyString('essential_consent');

        $validator
            ->scalar('ip_address')
            ->maxLength('ip_address', 45)
            ->requirePresence('ip_address', 'create')
            ->notEmptyString('ip_address');

        $validator
            ->scalar('user_agent')
            ->maxLength('user_agent', 255)
            ->requirePresence('user_agent', 'create')
            ->notEmptyString('user_agent');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->existsIn(['user_id'], 'Users'), ['errorField' => 'user_id']);

        return $rules;
    }

    /**
     * Creates a consent cookie from a consent entity
     * getLatestConsent($sessionId, $user->getIdentifier())
     *
     * @param \App\Model\Entity\CookieConsent $consent The consent entity to create cookie from
     * @return \Cake\Http\Cookie\Cookie The configured cookie object
     */
    public function createConsentCookie(CookieConsent $consent): Cookie
    {
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';

        $cookie = (new Cookie('consent_cookie'))
            ->withValue(json_encode([
                'user_id' => $consent->user_id,
                'analytics_consent' => $consent->analytics_consent,
                'functional_consent' => $consent->functional_consent,
                'marketing_consent' => $consent->marketing_consent,
                'essential_consent' => true,
                'created' => time(),
            ]))
            ->withExpiry(new DateTime('+1 year'))
            ->withPath('/')
            ->withHttpOnly(true)
            ->withSameSite('Lax');

        // Only set Secure flag if we're actually on HTTPS
        if ($isHttps) {
            $cookie = $cookie->withSecure(true);
        }

        return $cookie;
    }

    /**
     * Gets the latest consent record prioritizing user_id over session_id
     *
     * @param string|null $sessionId The session ID to search for
     * @param string|null $userId The user ID to search for
     * @return array|null Array of consent data or null if no match found
     */
    public function getLatestConsent(?string $sessionId = null, ?string $userId = null): ?array
    {
        $fields = [
            'user_id',
            'session_id',
            'analytics_consent',
            'functional_consent',
            'marketing_consent',
            'essential_consent',
            'ip_address',
            'user_agent',
        ];

        // If both parameters are null, return early
        if ($sessionId === null && $userId === null) {
            return null;
        }

        // First try to find by user_id if provided
        if ($userId !== null) {
            $result = $this->find()
                ->select($fields)
                ->where(['user_id' => $userId])
                ->orderBy(['created' => 'DESC'])
                ->first();

            if ($result !== null) {
                return $result->toArray();
            }
        }

        // If no result found by user_id and session_id is provided, try finding by session_id
        if ($sessionId !== null) {
            $result = $this->find()
                ->select($fields)
                ->where(['session_id' => $sessionId])
                ->orderBy(['created' => 'DESC'])
                ->first();

            if ($result !== null) {
                return $result->toArray();
            }
        }

        // No matching record found
        return null;
    }
}
