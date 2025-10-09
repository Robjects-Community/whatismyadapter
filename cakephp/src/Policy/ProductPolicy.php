<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Product;
use App\Model\Entity\User;
use App\Model\Enum\Role;
use Authorization\IdentityInterface;

/**
 * Product Policy
 * 
 * Defines authorization rules for products
 */
class ProductPolicy
{
    /**
     * Check if user can index products
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canIndex(IdentityInterface $user, Product $product): bool
    {
        // All admin users can index
        return $user->canAccessAdmin();
    }

    /**
     * Check if user can view a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canView(IdentityInterface $user, Product $product): bool
    {
        // Admins and editors can view all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can view their own products
        if ($user->isAuthor() && $product->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can add a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canAdd(IdentityInterface $user, Product $product): bool
    {
        // Admin, editor, and author can add products
        return $user->canAccessAdmin();
    }

    /**
     * Check if user can edit a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canEdit(IdentityInterface $user, Product $product): bool
    {
        // Admins and editors can edit all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can edit their own products
        if ($user->isAuthor() && $product->user_id === $user->id) {
            return true;
        }
        
        // Editors can also edit associated product detail articles
        if ($user->isEditor() && $product->article_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can delete a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Product $product): bool
    {
        // Admins and editors can delete all
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can delete their own products
        if ($user->isAuthor() && $product->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can feature a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canFeature(IdentityInterface $user, Product $product): bool
    {
        // Only admins and editors can feature products
        return $user->canEditAnyContent();
    }

    /**
     * Check if user can publish a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canPublish(IdentityInterface $user, Product $product): bool
    {
        // Admins and editors can publish any product
        if ($user->canEditAnyContent()) {
            return true;
        }
        
        // Authors can publish their own products
        if ($user->isAuthor() && $product->user_id === $user->id) {
            return true;
        }
        
        return false;
    }

    /**
     * Check if user can verify a product
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Product $product The product.
     * @return bool
     */
    public function canVerify(IdentityInterface $user, Product $product): bool
    {
        // Only admins and editors can verify products
        return $user->canEditAnyContent();
    }

    /**
     * Scope the index query based on user role
     * Authors can only see their own products
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \Cake\ORM\Query $query The query.
     * @return \Cake\ORM\Query
     */
    public function scopeIndex(IdentityInterface $user, $query)
    {
        // Admins and editors see all products
        if ($user->canEditAnyContent()) {
            return $query;
        }
        
        // Authors only see their own products
        if ($user->isAuthor()) {
            return $query->where(['Products.user_id' => $user->id]);
        }
        
        // Others see nothing (shouldn't get here in admin area)
        return $query->where(['1 = 0']);
    }
}
