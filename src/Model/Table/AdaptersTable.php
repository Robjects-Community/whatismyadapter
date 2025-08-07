
<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

class AdaptersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('adapters');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        // Add behaviors if needed (e.g., Timestamp for created/modified)
        $this->addBehavior('Timestamp');
        // Add associations (e.g., Tags, Products if relevant)
        $this->addAssociations([
            'belongsToMany' => ['Tags'],
            'belongsTo' => ['Products'],
        ]);
    }

    public function findPublished(SelectQuery $query, array $options): SelectQuery
    {
        return $query->where(['Adapters.is_published' => true]);  // Adjust based on your table's published field
    }
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
        ->scalar('connector_type_a')
        ->maxLength('connector_type_a', 255)
        ->requirePresence('connector_type_a', 'create')
        ->notEmptyString('connector_type_a');

    $validator
        ->scalar('connector_type_b')
        ->maxLength('connector_type_b', 255)
        ->requirePresence('connector_type_b', 'create')
        ->notEmptyString('connector_type_b');

    $validator
        ->scalar('max_power_delivery')
        ->maxLength('max_power_delivery', 255)
        ->allowEmptyString('max_power_delivery');

    // Add validation for other fields from your CSV as needed

    return $validator;
}

    // Adapter-specific methods

/**
 * Custom finder to retrieve adapters by connector types.
 *
 * @param \Cake\ORM\Query\SelectQuery $query The query object.
 * @param string $connectorA The first connector type.
 * @param string $connectorB The second connector type.
 * @return \Cake\ORM\Query\SelectQuery
 */
public function findByConnectorType(SelectQuery $query, string $connectorA, string $connectorB): SelectQuery
{
    return $query
        ->where(['connector_type_a' => $connectorA])
        ->where(['connector_type_b' => $connectorB]);
}

/**
 * Custom finder to retrieve adapters by minimum power delivery.
 *
 * @param \Cake\ORM\Query\SelectQuery $query The query object.
 * @param int $minWatts The minimum power in watts.
 * @return \Cake\ORM\Query\SelectQuery
 */
public function findByPowerDelivery(SelectQuery $query, int $minWatts): SelectQuery
{
    return $query
        ->where(['max_power_delivery >=' => $minWatts . 'W']);
}
}
