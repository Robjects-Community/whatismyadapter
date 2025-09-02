<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Settings Model
 *
 * @method \App\Model\Entity\Setting newEmptyEntity()
 * @method \App\Model\Entity\Setting newEntity(array $data, array $options = [])
 * @method array<\App\Model\Entity\Setting> newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\Setting get(mixed $primaryKey, array|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @method \App\Model\Entity\Setting findOrCreate($search, ?callable $callback = null, array $options = [])
 * @method \App\Model\Entity\Setting patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method array<\App\Model\Entity\Setting> patchEntities(iterable $entities, array $data, array $options = [])
 * @method \App\Model\Entity\Setting|false save(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method \App\Model\Entity\Setting saveOrFail(\Cake\Datasource\EntityInterface $entity, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting>|false saveMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting> saveManyOrFail(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting>|false deleteMany(iterable $entities, array $options = [])
 * @method iterable<\App\Model\Entity\Setting>|\Cake\Datasource\ResultSetInterface<\App\Model\Entity\Setting> deleteManyOrFail(iterable $entities, array $options = [])
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class SettingsTable extends Table
{
    /**
     * Initialize method
     *
     * This method initializes the table configuration, including setting the table name,
     * display field, primary key, and adding behaviors.
     *
     * @param array<string, mixed> $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('settings');
        $this->setDisplayField('category');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');
    }

    /**
     * Default validation rules.
     *
     * This method sets up the validation rules for the Settings table fields.
     * It includes rules for category, key_name, value_type, and value fields.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('category')
            ->maxLength('category', 255)
            ->requirePresence('category', 'create')
            ->notEmptyString('category');

        $validator
            ->scalar('key_name')
            ->maxLength('key_name', 255)
            ->requirePresence('key_name', 'create')
            ->notEmptyString('key_name');

        $validator
            ->scalar('value_type')
            ->requirePresence('value_type', 'create')
            ->notEmptyString('value_type')
            ->inList('value_type', [
                'text',
                'numeric',
                'bool',
                'textarea',
                'select',
                'select-page',
            ], __('Invalid type'));

        $validator
            ->requirePresence('value', 'create')
            ->notEmptyString('value', __('A value is required'))
            ->add('value', 'custom', [
                'rule' => function ($value, $context) {
                    $value_type = $context['data']['value_type'] ?? null;
                    if ($value_type === 'numeric' && !is_numeric($value)) {
                        return __('The value must be a number.');
                    }
                    if ($value_type === 'bool' && !in_array($value, [0, 1], true)) {
                        return __('The value must be 0 or 1.');
                    }
                    if ($value_type === 'text' && empty($value)) {
                        return __('The value must not be empty.');
                    }

                    return true;
                },
                'message' => __('Invalid value for the specified type.'),
            ]);

        return $validator;
    }

    /**
     * Retrieves setting values based on the specified category and optional key name.
     *
     * This method can fetch either all settings for a given category or a specific setting
     * within a category, depending on whether a key name is provided.
     *
     * @param string $category The category of the setting(s) to retrieve.
     * @param string|null $keyName The specific key name of the setting. If null, all settings for the category are returned.
     * @return mixed Returns one of the following:
     *               - An associative array of all settings for the category if $keyName is null.
     *               - The value of the specific setting if $keyName is provided and the setting exists.
     *               - null if a specific setting is requested but not found.
     * @throws \Cake\Database\Exception\DatabaseException If there's an issue with the database query.
     */
    public function getSettingValue(string $category, ?string $keyName = null): mixed
    {
        if (empty($keyName)) {
            // Fetch all settings for the category
            $settings = $this->find()
                ->where(['category' => $category])
                ->all()
                ->combine('key_name', function ($setting) {
                    return $this->castValue($setting->value, $setting->value_type);
                })
                ->toArray();

            return $settings;
        }

        // Fetch a single setting
        $setting = $this->find()
            ->where(['category' => $category, 'key_name' => $keyName])
            ->first();

        return $setting ? $this->castValue($setting->value, $setting->value_type) : null;
    }

    /**
     * Casts the value to the appropriate type based on the value_type.
     *
     * This private method is used internally to ensure that the returned
     * setting values are of the correct data type.
     *
     * @param mixed $value The value to be cast.
     * @param string $valueType The type to cast the value to ('bool', 'numeric', or 'string').
     * @return mixed The cast value.
     */
    private function castValue(mixed $value, string $valueType): mixed
    {
        switch ($valueType) {
            case 'bool':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'numeric':
                return (int)$value;
            case 'string':
            default:
                return (string)$value;
        }
    }
}
