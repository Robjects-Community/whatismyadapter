<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->username) ?></h3>
            <table>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= h($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Email') ?></th>
                    <td><?= h($user->email) ?></td>
                </tr>
                <tr>
                    <th><?= __('Image') ?></th>
                    <td><?= h($user->image) ?></td>
                </tr>
                <tr>
                    <th><?= __('Alt Text') ?></th>
                    <td><?= h($user->alt_text) ?></td>
                </tr>
                <tr>
                    <th><?= __('Keywords') ?></th>
                    <td><?= h($user->keywords) ?></td>
                </tr>
                <tr>
                    <th><?= __('Name') ?></th>
                    <td><?= h($user->name) ?></td>
                </tr>
                <tr>
                    <th><?= __('Dir') ?></th>
                    <td><?= h($user->dir) ?></td>
                </tr>
                <tr>
                    <th><?= __('Mime') ?></th>
                    <td><?= h($user->mime) ?></td>
                </tr>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Size') ?></th>
                    <td><?= $user->size === null ? '' : $this->Number->format($user->size) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($user->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($user->modified) ?></td>
                </tr>
                <tr>
                    <th><?= __('Is Admin') ?></th>
                    <td><?= $user->is_admin ? __('Yes') : __('No'); ?></td>
                </tr>
                <tr>
                    <th><?= __('Active') ?></th>
                    <td><?= $user->active ? __('Yes') : __('No'); ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Articles') ?></h4>
                <?php if (!empty($user->articles)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Kind') ?></th>
                            <th><?= __('Featured') ?></th>
                            <th><?= __('Title') ?></th>
                            <th><?= __('Lede') ?></th>
                            <th><?= __('Slug') ?></th>
                            <th><?= __('Body') ?></th>
                            <th><?= __('Markdown') ?></th>
                            <th><?= __('Summary') ?></th>
                            <th><?= __('Image') ?></th>
                            <th><?= __('Alt Text') ?></th>
                            <th><?= __('Keywords') ?></th>
                            <th><?= __('Name') ?></th>
                            <th><?= __('Dir') ?></th>
                            <th><?= __('Size') ?></th>
                            <th><?= __('Mime') ?></th>
                            <th><?= __('Is Published') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th><?= __('Published') ?></th>
                            <th><?= __('Meta Title') ?></th>
                            <th><?= __('Meta Description') ?></th>
                            <th><?= __('Meta Keywords') ?></th>
                            <th><?= __('Facebook Description') ?></th>
                            <th><?= __('Linkedin Description') ?></th>
                            <th><?= __('Instagram Description') ?></th>
                            <th><?= __('Twitter Description') ?></th>
                            <th><?= __('Word Count') ?></th>
                            <th><?= __('Parent Id') ?></th>
                            <th><?= __('Lft') ?></th>
                            <th><?= __('Rght') ?></th>
                            <th><?= __('Main Menu') ?></th>
                            <th><?= __('View Count') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->articles as $article) : ?>
                        <tr>
                            <td><?= h($article->id) ?></td>
                            <td><?= h($article->user_id) ?></td>
                            <td><?= h($article->kind) ?></td>
                            <td><?= h($article->featured) ?></td>
                            <td><?= h($article->title) ?></td>
                            <td><?= h($article->lede) ?></td>
                            <td><?= h($article->slug) ?></td>
                            <td><?= h($article->body) ?></td>
                            <td><?= h($article->markdown) ?></td>
                            <td><?= h($article->summary) ?></td>
                            <td><?= h($article->image) ?></td>
                            <td><?= h($article->alt_text) ?></td>
                            <td><?= h($article->keywords) ?></td>
                            <td><?= h($article->name) ?></td>
                            <td><?= h($article->dir) ?></td>
                            <td><?= h($article->size) ?></td>
                            <td><?= h($article->mime) ?></td>
                            <td><?= h($article->is_published) ?></td>
                            <td><?= h($article->created) ?></td>
                            <td><?= h($article->modified) ?></td>
                            <td><?= h($article->published) ?></td>
                            <td><?= h($article->meta_title) ?></td>
                            <td><?= h($article->meta_description) ?></td>
                            <td><?= h($article->meta_keywords) ?></td>
                            <td><?= h($article->facebook_description) ?></td>
                            <td><?= h($article->linkedin_description) ?></td>
                            <td><?= h($article->instagram_description) ?></td>
                            <td><?= h($article->twitter_description) ?></td>
                            <td><?= h($article->word_count) ?></td>
                            <td><?= h($article->parent_id) ?></td>
                            <td><?= h($article->lft) ?></td>
                            <td><?= h($article->rght) ?></td>
                            <td><?= h($article->main_menu) ?></td>
                            <td><?= h($article->view_count) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Articles', 'action' => 'view', $article->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Articles', 'action' => 'edit', $article->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Articles', 'action' => 'delete', $article->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $article->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Comments') ?></h4>
                <?php if (!empty($user->comments)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Foreign Key') ?></th>
                            <th><?= __('Model') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Content') ?></th>
                            <th><?= __('Display') ?></th>
                            <th><?= __('Is Inappropriate') ?></th>
                            <th><?= __('Is Analyzed') ?></th>
                            <th><?= __('Inappropriate Reason') ?></th>
                            <th><?= __('Created') ?></th>
                            <th><?= __('Modified') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->comments as $comment) : ?>
                        <tr>
                            <td><?= h($comment->id) ?></td>
                            <td><?= h($comment->foreign_key) ?></td>
                            <td><?= h($comment->model) ?></td>
                            <td><?= h($comment->user_id) ?></td>
                            <td><?= h($comment->content) ?></td>
                            <td><?= h($comment->display) ?></td>
                            <td><?= h($comment->is_inappropriate) ?></td>
                            <td><?= h($comment->is_analyzed) ?></td>
                            <td><?= h($comment->inappropriate_reason) ?></td>
                            <td><?= h($comment->created) ?></td>
                            <td><?= h($comment->modified) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Comments', 'action' => 'view', $comment->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Comments', 'action' => 'edit', $comment->id]) ?>
                                <?= $this->Form->postLink(
                                    __('Delete'),
                                    ['controller' => 'Comments', 'action' => 'delete', $comment->id],
                                    [
                                        'method' => 'delete',
                                        'confirm' => __('Are you sure you want to delete # {0}?', $comment->id),
                                    ]
                                ) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>