<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Article;
use App\Model\Entity\User;
use App\Model\Enum\Role;
use Authorization\IdentityInterface;

/**
 * Article Policy
 * 
 * Defines authorization rules for articles
 */
class ArticlePolicy
{
    /**
     * Check if user can index articles
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canIndex(IdentityInterface $user, Article $article): bool
    {
        // All admin users can index
        return $user->canAccessAdmin();
    }

    /**
     * Check if user can view an article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canView(IdentityInterface $user, Article $article): bool
    {
        // Admins and editors can view all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can view their own articles
        if ($user->isAuthor() && $article->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can add an article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Article $article): bool
    {
        // Admin, editor, and author can add articles
        return $user->canAccessAdmin();
    }

    /**
     * Check if user can edit an article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Article $article): bool
    {
        // Admins and editors can edit all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can edit their own articles
        if ($user->isAuthor() && $article->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can delete an article
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Article $article): bool
    {
        // Admins and editors can delete all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can delete their own articles
        if ($user->isAuthor() && $article->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can update tree structure
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canUpdateTree(IdentityInterface $user, Article $article): bool
    {
        // Only admins and editors can update tree structure
        return $user->canEditAnyContent();
    }

    /**
     * Check if user can access tree index
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Article $article The article.
     * @return bool
     */
    public function canTreeIndex(IdentityInterface $user, Article $article): bool
    {
        // All admin users can access tree index
        return $user->canAccessAdmin();
    }

    /**
     * Scope the index query based on user role
     * Authors can only see their own articles
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query $query The query.
     * @return \Cake\ORM\Query
     */
    public function scopeIndex(IdentityInterface $user, $query)
    {
        // Admins and editors see all articles
        if ($user->canEditAnyContent()) {
            return $query;
        }
        
        // Authors only see their own articles
        if ($user->isAuthor()) {
            return $query->where(['Articles.user_id' => $user->id]);
        }
        
        // Others see nothing (shouldn't get here in admin area)
        return $query->where(['1 = 0']);
    }
}
