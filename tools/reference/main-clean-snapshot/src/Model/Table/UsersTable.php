<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Behavior\ImageValidationTrait;
use App\Model\Enum\Role;
use App\Model\Table\QueueableJobsTrait;
use Cake\Log\LogTrait;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
    use ImageValidationTrait;
    use LogTrait;
    use QueueableJobsTrait;

    /**
     * Initialize method
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('username');
        $this->setPrimaryKey('id');

        $this->addBehavior('QueueableImage', [
            'folder_path' => 'files/Users/image/',
            'field' => 'image',
        ]);

        $this->addBehavior('Timestamp');

        $this->hasMany('Articles', [
            'foreignKey' => 'user_id',
        ]);

        $this->hasMany('Comments', [
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
            ->scalar('username')
            ->maxLength('username', 50)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->minLength('password', 8, __('Password must be at least 8 characters long'))
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->allowEmptyString('password', 'update')
            ->notEmptyString('password', null, 'create');

        $validator
            ->scalar('confirm_password')
            ->maxLength('confirm_password', 255)
            ->requirePresence('confirm_password', 'create')
            ->allowEmptyString('confirm_password', 'update')
            ->notEmptyString('confirm_password', null, 'create')
            ->sameAs('confirm_password', 'password', 'Passwords do not match');

        $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('role')
            ->maxLength('role', 32)
            ->allowEmptyString('role')
            ->inList('role', array_column(Role::cases(), 'value'), __('Invalid role'));

        $this->addOptionalImageValidation($validator, 'image', [
            'messages' => [
                'mimeType' => 'Please upload only images (jpeg, png, gif).',
                'fileSize' => 'Image must be less than 10MB.',
            ],
        ]);

        return $validator;
    }

    /**
     * Validation rules for user registration.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationRegister(Validator $validator): Validator
    {
        $validator
            ->scalar('username')
            ->maxLength('username', 50)
            ->requirePresence('username', 'create')
            ->notEmptyString('username');

        $validator
            ->scalar('password')
            ->minLength('password', 8, __('Password must be at least 8 characters long'))
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password', null, 'create');

        $validator
            ->scalar('confirm_password')
            ->maxLength('confirm_password', 255)
            ->requirePresence('confirm_password', 'create')
            ->notEmptyString('confirm_password', null, 'create')
            ->sameAs('confirm_password', 'password', 'Passwords do not match');

        $validator
            ->email('email')
            ->notEmptyString('email');

        $this->addOptionalImageValidation($validator, 'image', [
            'messages' => [
                'mimeType' => 'Please upload only images (jpeg, png, gif).',
                'fileSize' => 'Image must be less than 10MB.',
            ],
        ]);

        return $validator;
    }

    /**
     * Validation method for resetting passwords.
     *
     * This method defines validation rules for the password reset process.
     * It ensures that the password meets the minimum length requirement
     * and that the password confirmation matches the password.
     *
     * @param \Cake\Validation\Validator $validator The validator instance to which rules will be added.
     * @return \Cake\Validation\Validator The modified validator instance with the added rules.
     */
    public function validationResetPassword(Validator $validator): Validator
    {
        $validator
            ->scalar('password')
            ->minLength('password', 8, __('Password must be at least 8 characters long'))
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->allowEmptyString('password', 'update')
            ->notEmptyString('password', null, 'create');

        $validator
            ->scalar('confirm_password')
            ->maxLength('confirm_password', 255)
            ->requirePresence('confirm_password', 'create')
            ->allowEmptyString('confirm_password', 'update')
            ->notEmptyString('confirm_password', null, 'create')
            ->sameAs('confirm_password', 'password', 'Passwords do not match');

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
        $rules->add($rules->isUnique(['username']), ['errorField' => 'username']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }

    /**
     * Custom finder method to retrieve only enabled records.
     *
     * This method modifies the query to filter out any records where the 'active'
     * field is set to 1, effectively returning only those records that are enabled.
     *
     * @param \Cake\ORM\Query $query The query object to modify.
     * @param array $options An array of options that can be used to customize the query.
     * @return \Cake\ORM\Query The modified query object with the 'active' condition applied.
     */
    public function findAuth(Query $query, array $options): Query
    {
        return $query->where(['active' => 1]);
    }
}
