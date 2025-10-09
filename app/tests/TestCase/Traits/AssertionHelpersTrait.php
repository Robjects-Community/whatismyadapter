<?php
declare(strict_types=1);

namespace App\Test\TestCase\Traits;

use Cake\Datasource\EntityInterface;
use Cake\ORM\Association;
use Cake\ORM\Table;

/**
 * AssertionHelpersTrait
 *
 * Provides custom assertions for common testing scenarios including:
 * - Validation error assertions
 * - Association testing helpers
 * - Behavior verification
 * - Entity state assertions
 * - CakePHP-specific helpers
 *
 * Usage in test classes:
 * ```php
 * use App\Test\TestCase\Traits\AssertionHelpersTrait;
 *
 * class MyTest extends TestCase
 * {
 *     use AssertionHelpersTrait;
 *
 *     public function testValidation(): void
 *     {
 *         $entity = $this->Users->newEntity($data);
 *         $this->assertHasValidationError($entity, 'email', 'email');
 *     }
 * }
 * ```
 */
trait AssertionHelpersTrait
{
    // ============================================================
    // Validation Assertions
    // ============================================================

    /**
     * Assert that an entity has a validation error for a specific field
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param string $field Field name
     * @param string|null $rule Specific validation rule (optional)
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertHasValidationError(
        EntityInterface $entity,
        string $field,
        ?string $rule = null,
        string $message = ''
    ): void {
        $errors = $entity->getError($field);
        
        $this->assertNotEmpty(
            $errors,
            $message ?: "Expected validation error for field '{$field}' but found none"
        );
        
        if ($rule !== null) {
            $this->assertArrayHasKey(
                $rule,
                $errors,
                $message ?: "Expected validation rule '{$rule}' for field '{$field}' but it was not found"
            );
        }
    }

    /**
     * Assert that an entity has NO validation errors for a specific field
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param string $field Field name
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertHasNoValidationError(
        EntityInterface $entity,
        string $field,
        string $message = ''
    ): void {
        $errors = $entity->getError($field);
        
        $this->assertEmpty(
            $errors,
            $message ?: "Expected no validation errors for field '{$field}' but found: " . 
                json_encode($errors)
        );
    }

    /**
     * Assert that an entity has NO validation errors at all
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertHasNoValidationErrors(
        EntityInterface $entity,
        string $message = ''
    ): void {
        $errors = $entity->getErrors();
        
        $this->assertEmpty(
            $errors,
            $message ?: "Expected no validation errors but found: " . json_encode($errors)
        );
    }

    /**
     * Assert that an entity has specific validation errors
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param array $expectedErrors Expected errors [field => rule]
     * @return void
     */
    protected function assertHasValidationErrors(
        EntityInterface $entity,
        array $expectedErrors
    ): void {
        foreach ($expectedErrors as $field => $rule) {
            $this->assertHasValidationError($entity, $field, $rule);
        }
    }

    /**
     * Assert validation error message contains specific text
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param string $field Field name
     * @param string $expectedText Text to search for
     * @return void
     */
    protected function assertValidationErrorContains(
        EntityInterface $entity,
        string $field,
        string $expectedText
    ): void {
        $errors = $entity->getError($field);
        $this->assertNotEmpty($errors, "No validation errors found for field '{$field}'");
        
        $found = false;
        foreach ($errors as $error) {
            if (is_string($error) && str_contains($error, $expectedText)) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $found,
            "Expected validation error for '{$field}' to contain '{$expectedText}' but it was not found. " .
            "Actual errors: " . json_encode($errors)
        );
    }

    // ============================================================
    // Association Assertions
    // ============================================================

    /**
     * Assert that a table has a specific association
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $associationName Association name
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertHasAssociation(
        Table $table,
        string $associationName,
        string $message = ''
    ): void {
        $this->assertTrue(
            $table->hasAssociation($associationName),
            $message ?: "Table does not have association '{$associationName}'"
        );
    }

    /**
     * Assert that an association is of a specific type
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $associationName Association name
     * @param string $expectedType Expected type (BelongsTo, HasMany, BelongsToMany, HasOne)
     * @return void
     */
    protected function assertAssociationType(
        Table $table,
        string $associationName,
        string $expectedType
    ): void {
        $this->assertHasAssociation($table, $associationName);
        
        $association = $table->getAssociation($associationName);
        $actualType = (new \ReflectionClass($association))->getShortName();
        
        $this->assertEquals(
            $expectedType,
            $actualType,
            "Expected association '{$associationName}' to be of type '{$expectedType}' but got '{$actualType}'"
        );
    }

    /**
     * Assert that an association points to a specific target table
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $associationName Association name
     * @param string $expectedTable Expected target table alias
     * @return void
     */
    protected function assertAssociationTarget(
        Table $table,
        string $associationName,
        string $expectedTable
    ): void {
        $this->assertHasAssociation($table, $associationName);
        
        $association = $table->getAssociation($associationName);
        $actualTable = $association->getTarget()->getAlias();
        
        $this->assertEquals(
            $expectedTable,
            $actualTable,
            "Expected association '{$associationName}' to target '{$expectedTable}' but targets '{$actualTable}'"
        );
    }

    /**
     * Assert that a BelongsToMany association uses correct join table
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $associationName Association name
     * @param string $expectedJoinTable Expected join table name
     * @return void
     */
    protected function assertBelongsToManyJoinTable(
        Table $table,
        string $associationName,
        string $expectedJoinTable
    ): void {
        $this->assertHasAssociation($table, $associationName);
        $this->assertAssociationType($table, $associationName, 'BelongsToMany');
        
        $association = $table->getAssociation($associationName);
        $actualJoinTable = $association->junction()->getTable();
        
        $this->assertEquals(
            $expectedJoinTable,
            $actualJoinTable,
            "Expected join table '{$expectedJoinTable}' but got '{$actualJoinTable}'"
        );
    }

    /**
     * Assert that an entity's association is properly loaded
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param string $associationName Association name
     * @return void
     */
    protected function assertAssociationLoaded(
        EntityInterface $entity,
        string $associationName
    ): void {
        $property = \Cake\Utility\Inflector::underscore($associationName);
        
        $this->assertTrue(
            $entity->has($property),
            "Entity does not have association '{$associationName}' loaded"
        );
        
        $this->assertNotNull(
            $entity->get($property),
            "Association '{$associationName}' is loaded but is null"
        );
    }

    // ============================================================
    // Behavior Assertions
    // ============================================================

    /**
     * Assert that a table has a specific behavior attached
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $behaviorName Behavior name
     * @param string $message Custom assertion message
     * @return void
     */
    protected function assertHasBehavior(
        Table $table,
        string $behaviorName,
        string $message = ''
    ): void {
        $this->assertTrue(
            $table->hasBehavior($behaviorName),
            $message ?: "Table does not have behavior '{$behaviorName}'"
        );
    }

    /**
     * Assert that a table does NOT have a specific behavior
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param string $behaviorName Behavior name
     * @return void
     */
    protected function assertDoesNotHaveBehavior(
        Table $table,
        string $behaviorName
    ): void {
        $this->assertFalse(
            $table->hasBehavior($behaviorName),
            "Table should not have behavior '{$behaviorName}' but it does"
        );
    }

    /**
     * Assert that Timestamp behavior is working (created/modified set)
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @return void
     */
    protected function assertTimestampBehaviorWorking(EntityInterface $entity): void
    {
        if ($entity->isNew()) {
            $this->assertNotNull(
                $entity->get('created'),
                'Timestamp behavior should set created datetime on new entities'
            );
        }
        
        $this->assertNotNull(
            $entity->get('modified'),
            'Timestamp behavior should set modified datetime'
        );
    }

    // ============================================================
    // Entity State Assertions
    // ============================================================

    /**
     * Assert that an entity is new (not persisted)
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @return void
     */
    protected function assertEntityIsNew(EntityInterface $entity): void
    {
        $this->assertTrue(
            $entity->isNew(),
            'Expected entity to be new (not persisted) but it is not'
        );
    }

    /**
     * Assert that an entity is NOT new (has been persisted)
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @return void
     */
    protected function assertEntityIsPersisted(EntityInterface $entity): void
    {
        $this->assertFalse(
            $entity->isNew(),
            'Expected entity to be persisted but it is still new'
        );
    }

    /**
     * Assert that specific fields are dirty (modified)
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param array $fields Field names
     * @return void
     */
    protected function assertFieldsAreDirty(
        EntityInterface $entity,
        array $fields
    ): void {
        foreach ($fields as $field) {
            $this->assertTrue(
                $entity->isDirty($field),
                "Expected field '{$field}' to be dirty but it is not"
            );
        }
    }

    /**
     * Assert that specific fields are NOT dirty
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param array $fields Field names
     * @return void
     */
    protected function assertFieldsAreClean(
        EntityInterface $entity,
        array $fields
    ): void {
        foreach ($fields as $field) {
            $this->assertFalse(
                $entity->isDirty($field),
                "Expected field '{$field}' to be clean but it is dirty"
            );
        }
    }

    /**
     * Assert that an entity has specific accessible fields
     *
     * @param \Cake\Datasource\EntityInterface $entity Entity to check
     * @param array $fields Field names
     * @return void
     */
    protected function assertFieldsAreAccessible(
        EntityInterface $entity,
        array $fields
    ): void {
        foreach ($fields as $field) {
            $this->assertTrue(
                $entity->isAccessible($field),
                "Expected field '{$field}' to be accessible but it is not"
            );
        }
    }

    // ============================================================
    // Database Assertions
    // ============================================================

    /**
     * Assert that a record exists in database
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param array $conditions Conditions to find the record
     * @return void
     */
    protected function assertRecordExists(Table $table, array $conditions): void
    {
        $exists = $table->exists($conditions);
        
        $this->assertTrue(
            $exists,
            "Expected record to exist in {$table->getAlias()} with conditions: " .
            json_encode($conditions)
        );
    }

    /**
     * Assert that a record does NOT exist in database
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param array $conditions Conditions to find the record
     * @return void
     */
    protected function assertRecordNotExists(Table $table, array $conditions): void
    {
        $exists = $table->exists($conditions);
        
        $this->assertFalse(
            $exists,
            "Expected record NOT to exist in {$table->getAlias()} but it does with conditions: " .
            json_encode($conditions)
        );
    }

    /**
     * Assert record count matches expected value
     *
     * @param \Cake\ORM\Table $table Table to check
     * @param int $expected Expected count
     * @param array $conditions Optional conditions
     * @return void
     */
    protected function assertRecordCount(
        Table $table,
        int $expected,
        array $conditions = []
    ): void {
        $query = $table->find();
        
        if (!empty($conditions)) {
            $query->where($conditions);
        }
        
        $actual = $query->count();
        
        $this->assertEquals(
            $expected,
            $actual,
            "Expected {$expected} records in {$table->getAlias()} but found {$actual}"
        );
    }

    // ============================================================
    // Array/Collection Assertions
    // ============================================================

    /**
     * Assert that an array contains entity with specific field value
     *
     * @param array $entities Array of entities
     * @param string $field Field name
     * @param mixed $value Expected value
     * @return void
     */
    protected function assertCollectionHasEntityWith(
        array $entities,
        string $field,
        $value
    ): void {
        $found = false;
        
        foreach ($entities as $entity) {
            if ($entity->get($field) === $value) {
                $found = true;
                break;
            }
        }
        
        $this->assertTrue(
            $found,
            "Expected collection to contain entity with {$field}={$value} but it was not found"
        );
    }

    /**
     * Assert that all entities in collection have a specific property
     *
     * @param array $entities Array of entities
     * @param string $property Property name
     * @return void
     */
    protected function assertAllEntitiesHaveProperty(
        array $entities,
        string $property
    ): void {
        foreach ($entities as $index => $entity) {
            $this->assertTrue(
                $entity->has($property),
                "Entity at index {$index} does not have property '{$property}'"
            );
        }
    }

    // ============================================================
    // JSON Assertions
    // ============================================================

    /**
     * Assert that JSON response contains specific structure
     *
     * @param array $json Decoded JSON data
     * @param array $expectedKeys Expected keys
     * @return void
     */
    protected function assertJsonStructure(array $json, array $expectedKeys): void
    {
        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $json,
                "JSON response does not have expected key '{$key}'"
            );
        }
    }

    /**
     * Assert that JSON response matches expected data
     *
     * @param array $expected Expected data
     * @param array $actual Actual JSON data
     * @param array $ignoreKeys Keys to ignore in comparison
     * @return void
     */
    protected function assertJsonMatches(
        array $expected,
        array $actual,
        array $ignoreKeys = []
    ): void {
        foreach ($ignoreKeys as $key) {
            unset($expected[$key], $actual[$key]);
        }
        
        $this->assertEquals(
            $expected,
            $actual,
            'JSON response does not match expected data'
        );
    }

    // ============================================================
    // HTTP Response Assertions (for Controller tests)
    // ============================================================

    /**
     * Assert that response is successful (2xx status)
     *
     * @param \Cake\Http\Response|null $response Response object
     * @return void
     */
    protected function assertResponseSuccess($response = null): void
    {
        $response = $response ?? $this->_response;
        
        $this->assertTrue(
            $response->isOk() || $response->isSuccess(),
            "Expected successful response but got status {$response->getStatusCode()}"
        );
    }

    /**
     * Assert that response has specific status code
     *
     * @param int $expectedCode Expected HTTP status code
     * @param \Cake\Http\Response|null $response Response object
     * @return void
     */
    protected function assertResponseCode(int $expectedCode, $response = null): void
    {
        $response = $response ?? $this->_response;
        
        $this->assertEquals(
            $expectedCode,
            $response->getStatusCode(),
            "Expected response code {$expectedCode} but got {$response->getStatusCode()}"
        );
    }

    /**
     * Assert that response contains specific header
     *
     * @param string $header Header name
     * @param string|null $expectedValue Expected value (optional)
     * @param \Cake\Http\Response|null $response Response object
     * @return void
     */
    protected function assertResponseHasHeader(
        string $header,
        ?string $expectedValue = null,
        $response = null
    ): void {
        $response = $response ?? $this->_response;
        
        $this->assertTrue(
            $response->hasHeader($header),
            "Expected response to have header '{$header}' but it does not"
        );
        
        if ($expectedValue !== null) {
            $actualValue = $response->getHeaderLine($header);
            $this->assertEquals(
                $expectedValue,
                $actualValue,
                "Expected header '{$header}' to have value '{$expectedValue}' but got '{$actualValue}'"
            );
        }
    }
}
