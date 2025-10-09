<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use ArrayObject;
use Cake\Cache\Cache;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use Cake\ORM\ResultSet;

/**
 * CommentableBehavior class
 *
 * This behavior provides functionality to manage comments associated with a model.
 */
class CommentableBehavior extends Behavior
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'commentsTable' => 'Comments',
        'foreignKey' => 'foreign_key',
        'modelField' => 'model',
        'userField' => 'user_id',
        'contentField' => 'content',
    ];

    /**
     * Initialize the behavior with the given configuration.
     *
     * @param array<string, mixed> $config The configuration settings provided to the behavior.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->_table->hasMany($this->getConfig('commentsTable'), [
            'foreignKey' => $this->getConfig('foreignKey'),
            'conditions' => [
                $this->getConfig('commentsTable') . '.' . $this->getConfig('modelField') => $this->_table->getAlias(),
            ],
            'dependent' => true,
        ]);
    }

    /**
     * Clear the cache for a specific entity.
     *
     * @param string $entityId The ID of the entity to clear cache for.
     * @return void
     */
    protected function clearEntityCache(string $entityId): void
    {
        $entity = $this->_table->get($entityId);
        if ($entity->slug) {
            Cache::delete("article_{$entity->slug}", 'content');
        }
    }

    /**
     * Before save callback.
     *
     * Automatically saves new comments associated with the entity.
     *
     * @param \Cake\Event\EventInterface $event The beforeSave event that was fired.
     * @param \Cake\Datasource\EntityInterface $entity The entity that is going to be saved.
     * @param \ArrayObject $options The options passed to the save operation.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity, ArrayObject $options): void
    {
        if ($entity->isNew() && !empty($entity->comments)) {
            $commentsTable = $this->_table->getAssociation($this->getConfig('commentsTable'))->getTarget();
            foreach ($entity->comments as $comment) {
                $commentEntity = $commentsTable->newEntity($comment);
                $commentEntity->set($this->getConfig('foreignKey'), $entity->id);
                $commentEntity->set($this->getConfig('modelField'), $this->_table->getAlias());
                $commentsTable->save($commentEntity);
            }
        }
    }

    /**
     * Add a comment to an entity.
     *
     * @param string $entityId The ID of the entity to which the comment is related.
     * @param string $userId The ID of the user who made the comment.
     * @param string $content The content of the comment.
     * @return \Cake\Datasource\EntityInterface|false The saved comment entity or false on failure.
     */
    public function addComment(string $entityId, string $userId, string $content): EntityInterface|false
    {
        $commentsTable = $this->_table->getAssociation($this->getConfig('commentsTable'))->getTarget();
        $comment = $commentsTable->newEntity([
            $this->getConfig('foreignKey') => $entityId,
            $this->getConfig('modelField') => $this->_table->getAlias(),
            $this->getConfig('userField') => $userId,
            $this->getConfig('contentField') => $content,
        ]);

        $result = $commentsTable->save($comment);

        if ($result) {
            $this->clearEntityCache($entityId);
        }

        return $result;
    }

    /**
     * Retrieve comments associated with an entity.
     *
     * @param string $entityId The ID of the entity for which to retrieve comments.
     * @param array<string, mixed> $options Options for the query, such as 'order' and 'limit'.
     * @return \Cake\ORM\ResultSet The result set containing the comments.
     */
    public function getComments(string $entityId, array $options = []): ResultSet
    {
        $commentsTable = $this->_table->getAssociation($this->getConfig('commentsTable'))->getTarget();
        $query = $commentsTable->find();
        $query->where([
            $this->getConfig('foreignKey') => $entityId,
            $this->getConfig('modelField') => $this->_table->getAlias(),
            'display' => true,
        ]);

        if (!empty($options['order'])) {
            $query->orderBy($options['order']);
        } else {
            $query->orderBy(['created' => 'DESC']);
        }

        if (!empty($options['limit'])) {
            $query->limit($options['limit']);
        }

        return $query->all();
    }
}
