<?php
declare(strict_types=1);

/**
 * ContactManager Plugin
 *
 * This file is part of the ContactManager plugin for CakePHP.
 * It defines the ContactsTable class which interacts with the 'contacts' table.
 *
 * @package ContactManager\Model\Table
 */

// plugins/ContactManager/src/Model/Table/ContactsTable.php
namespace ContactManager\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ContactsTable extends Table
{
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        // Set the table name for this model
        $this->setTable('contacts');
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        // $validator
        //     ->add('id', 'valid', ['rule' => 'numeric'])
        //     ->allowEmpty('id', 'create')
        //     ->requirePresence('first_name', 'create')
        //     ->notEmpty('first_name')
        //     ->requirePresence('last_name', 'create')
        //     ->notEmpty('last_name')
        //     ->add('email', 'valid', ['rule' => 'email'])
        //     ->requirePresence('email', 'create')
        //     ->notEmpty('email')
        //     ->add('contact_num', 'valid', ['rule' => 'numeric'])
        //     ->requirePresence('contact_num', 'create')
        //     ->notEmpty('contact_num')
        //     ->requirePresence('address', 'create')
        //     ->notEmpty('address');
            /*->requirePresence('city', 'create')
            ->notEmpty('city')
            ->requirePresence('state', 'create')
            ->notEmpty('state')
            ->requirePresence('country', 'create')
            ->notEmpty('country')*/
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
        $rules->add($rules->isUnique(['email']));

        return $rules;
    }

    public function tryme()
    {
        return 'hey this is call form ContactManager Plugin';
    }
}
