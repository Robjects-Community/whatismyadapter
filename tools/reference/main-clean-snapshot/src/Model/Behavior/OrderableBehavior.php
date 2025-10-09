<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Exception\PersistenceFailedException;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * OrderableBehavior provides hierarchical ordering capabilities for CakePHP models.
 *
 * This behavior automatically includes and configures the Tree behavior, providing
 * methods to manage hierarchical data structures with ordering capabilities.
 * It allows for:
 * - Moving items between different levels of hierarchy
 * - Reordering items within the same level
 * - Root level and nested level ordering
 *
 * Required table columns (configurable via TreeBehavior):
 * - parent_id (integer|null): References the parent record
 * - lft (integer): Left value for nested set model
 * - rght (integer): Right value for nested set model
 *
 * @property \Cake\ORM\Table $_table The table instance this behavior is attached to
 */
class OrderableBehavior extends Behavior
{
    /**
     * Default configuration for the behavior.
     *
     * Configuration options:
     * - treeConfig: Configuration array for the Tree behavior
     *   - parent: The foreign key column for the parent (default: 'parent_id')
     *   - left: The column for the left value (default: 'lft')
     *   - right: The column for the right value (default: 'rght')
     * - displayField: The field to use for display in the tree (default: null, uses table's displayField)
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'treeConfig' => [
            'parent' => 'parent_id',
            'left' => 'lft',
            'right' => 'rght',
        ],
        'displayField' => null,
    ];

    /**
     * Initializes the behavior.
     *
     * Sets up the Tree behavior with merged configuration settings if it's not
     * already present on the table. This ensures all necessary tree functionality
     * is available for ordering operations.
     *
     * @param array $config Configuration array with optional 'treeConfig' key for
     *                      customizing the Tree behavior settings.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // Merge any provided tree configuration with defaults
        $treeConfig = array_merge(
            $this->_defaultConfig['treeConfig'],
            $config['treeConfig'] ?? [],
        );

        // Add the Tree behavior if it's not already added
        if (!$this->_table->hasBehavior('Tree')) {
            $this->_table->addBehavior('Tree', $treeConfig);
        }
    }

    /**
     * Reorders a model record within a hierarchical structure.
     *
     * This method handles both the parent reassignment and sibling reordering
     * in a single operation, wrapped in a database transaction. It supports:
     * - Moving an item to the root level
     * - Moving an item under a new parent
     * - Reordering items within their current level
     *
     * The method ensures proper tree structure maintenance through the Tree behavior
     * and updates the nested set values accordingly.
     *
     * @param array $data An associative array containing:
     *                    - 'id' (int|string): The ID of the record to be reordered.
     *                    - 'newParentId' (mixed): The ID of the new parent record, or 'root' to move to the root level.
     *                    - 'newIndex' (int): The new position index among siblings (zero-based).
     * @throws \InvalidArgumentException If the provided data is missing required keys or has invalid types.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException If the record to move or target parent is not found.
     * @throws \Cake\ORM\Exception\PersistenceFailedException If the record cannot be saved during parent update.
     * @throws \RuntimeException If moving the item up/down fails.
     * @throws \LogicException If the item cannot be found among its new siblings after parent move.
     * @return bool Returns true on successful reordering.
     */
    public function reorder(array $data): bool
    {
        $result = $this->_table->getConnection()->transactional(function () use ($data) {
            if (
                !isset($data['id'], $data['newParentId'], $data['newIndex']) ||
                (!is_numeric($data['id']) && !is_string($data['id'])) || // ID can be int or string (e.g. UUID)
                !is_numeric($data['newIndex'])
            ) {
                throw new InvalidArgumentException(
                    'Required data (id, newParentId, newIndex) missing or newIndex is not numeric for reorder.',
                );
            }
            if (
                $data['newParentId'] !== 'root'
                && !is_numeric($data['newParentId'])
                && !is_string($data['newParentId'])
            ) {
                 throw new InvalidArgumentException('newParentId must be "root", numeric, or string.');
            }

            $itemId = $data['id'];
            $newParentIdentifier = $data['newParentId'];
            $newIndex = (int)$data['newIndex'];

            /** @var \Cake\ORM\Entity&\Cake\ORM\Behavior\Tree\NodeInterface $item */
            $item = $this->_table->get($itemId); // Throws RecordNotFoundException if not found

            $actualNewParentId = $newParentIdentifier === 'root' ? null : $newParentIdentifier;
            $parentField = $this->getConfig('treeConfig.parent');

            if ($item->get($parentField) !== $actualNewParentId) {
                if ($actualNewParentId !== null) {
                    // Ensure the new parent exists (unless it's root)
                    $this->_table->get($actualNewParentId); // Throws RecordNotFoundException
                    $item->set($parentField, $actualNewParentId);
                } else {
                    $item->set($parentField, null);
                }

                if (!$this->_table->save($item, ['checkRules' => false])) {
                    throw new PersistenceFailedException($item, ['Save failed during parent update.']);
                }
            }

            // Adjust position among new siblings
            $siblingsQuery = null;
            $parentFieldValue = $item->get($parentField); // Get parent_id value

            if ($parentFieldValue === null) { // Item is now a root node
                $siblingsQuery = $this->_table->find()
                    ->where([$this->_table->aliasField($parentField) . ' IS' => null]);
            } else { // Item has a parent
                $siblingsQuery = $this->_table->find(
                    'children',
                    for: $parentFieldValue, // Use named argument 'for'
                    direct: true, // Use named argument 'direct'
                );
            }

            $leftField = $this->getConfig('treeConfig.left');
            $siblings = $siblingsQuery->orderBy([$this->_table->aliasField($leftField) => 'ASC'])
                ->all()
                ->toArray();

            $currentPosition = false;
            $primaryKeyField = (array)$this->_table->getPrimaryKey(); // getPrimaryKey can return string or array
            $primaryKeyField = reset($primaryKeyField); // Use the first primary key

            foreach ($siblings as $index => $sibling) {
                if ($sibling->get($primaryKeyField) == $item->get($primaryKeyField)) {
                    $currentPosition = $index;
                    break;
                }
            }

            if ($currentPosition === false) {
                throw new LogicException("Moved item not found among its new siblings. Item ID: {$itemId}");
            }

            $targetPosition = $newIndex;

            if ($currentPosition !== $targetPosition) {
                $distance = abs($targetPosition - $currentPosition);
                if ($targetPosition > $currentPosition) { // Moving down the list
                    // TreeBehavior's moveDown moves it $number positions *lower* (larger lft)
                    if (!$this->_table->moveDown($item, $distance)) {
                        throw new RuntimeException("Failed to move item ID {$itemId} down by {$distance} positions.");
                    }
                } else { // Moving up the list
                    // TreeBehavior's moveUp moves it $number positions *higher* (smaller lft)
                    if (!$this->_table->moveUp($item, $distance)) {
                        throw new RuntimeException("Failed to move item ID {$itemId} up by {$distance} positions.");
                    }
                }
            }

            return true; // Transactional callback succeeded
        });

        return $result;
    }

    /**
     * Gets a hierarchical tree structure of records.
     *
     * Retrieves records in a threaded format, including essential fields for tree structure
     * and any additional conditions specified.
     *
     * @param array $additionalConditions Additional conditions to apply to the query.
     * @param array $fields Additional fields to select (beyond id, parent_id, and displayField).
     * @return array<\Cake\Datasource\EntityInterface> Array of entities in threaded format.
     */
    public function getTree(array $additionalConditions = [], array $fields = []): array
    {
        $displayField = $this->getConfig('displayField') ?? $this->_table->getDisplayField();
        $parentFieldKey = $this->getConfig('treeConfig.parent');
        $leftFieldKey = $this->getConfig('treeConfig.left');
        $primaryKey = (array)$this->_table->getPrimaryKey();
        $primaryKeyField = reset($primaryKey);

        // Base fields that are always needed for tree structure
        $baseFields = [
            $this->_table->aliasField($primaryKeyField),
            $this->_table->aliasField($parentFieldKey),
            $this->_table->aliasField($displayField),
        ];

        // Merge base fields with any additional fields
        $selectFields = array_unique(array_merge($baseFields, $fields));

        $query = $this->_table->find()
            ->select($selectFields)
            ->where($additionalConditions)
            ->orderBy([$this->_table->aliasField($leftFieldKey) => 'ASC']);

        return $query->find('threaded')->toArray();
    }
}
