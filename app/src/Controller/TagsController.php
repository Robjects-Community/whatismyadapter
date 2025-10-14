<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;

/**
 * Tags Controller
 *
 * Handles operations related to tags, including listing all tags and viewing articles associated with a specific tag.
 *
 * @property \App\Model\Table\TagsTable $Tags
 */
class TagsController extends AppController
{
    /**
     * Configures actions that can be accessed without authentication.
     *
     * @param \Cake\Event\EventInterface $event The event object.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['index', 'view', 'viewBySlug']);
    }

    /**
     * Displays a paginated list of all tags.
     *
     * @return void
     */
    public function index(): void
    {
        $tags = $this->Tags->find()->all();
        $this->set(compact('tags'));
    }

    /**
     * Displays a tag and its associated published articles.
     *
     * Retrieves a tag by its slug and loads associated published articles with their authors.
     * Throws an exception if the tag is not found.
     *
     * @param string $slug The unique slug of the tag to retrieve.
     * @throws \Cake\Http\Exception\NotFoundException If the tag is not found.
     * @return void
     */
    public function viewBySlug(string $slug): void
    {
        $query = $this->Tags->find()
            ->contain(['Articles' => function ($q) {
                return $q->select(['id', 'title', 'slug', 'user_id', 'image', 'created'])
                    ->where([
                        'Articles.is_published' => true,
                        'Articles.kind' => 'article',
                        ])
                    ->contain(['Users' => function ($q) {
                        return $q->select(['id', 'username']);
                    }]);
            }])
            ->where(['Tags.slug' => $slug]);

        $tag = $query->first();

        if (!$tag) {
            throw new NotFoundException(__('Tag not found'));
        }

        $this->set(compact('tag'));
    }
}
