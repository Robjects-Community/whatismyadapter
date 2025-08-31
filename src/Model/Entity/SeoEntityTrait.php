<?php
declare(strict_types=1);

namespace App\Model\Entity;

/**
 * SeoEntityTrait
 *
 * Provides common SEO field accessibility and helper methods for Entity classes.
 * This trait consolidates duplicate SEO field accessibility patterns that were
 * previously scattered across multiple entity classes.
 */
trait SeoEntityTrait
{
    /**
     * Get the standard SEO fields accessibility array
     *
     * @return array<string, bool> SEO fields with accessibility set to true
     */
    protected function getSeoAccessibleFields(): array
    {
        return [
            'meta_title' => true,
            'meta_description' => true,
            'meta_keywords' => true,
            'facebook_description' => true,
            'linkedin_description' => true,
            'twitter_description' => true,
            'instagram_description' => true,
        ];
    }

    /**
     * Check if any SEO fields have values
     *
     * @return bool True if at least one SEO field has a value
     */
    public function hasSeoContent(): bool
    {
        $seoFields = array_keys($this->getSeoAccessibleFields());

        foreach ($seoFields as $field) {
            if (!empty($this->{$field})) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all SEO field values as an array
     *
     * @return array<string, string|null> Array of SEO field name => value pairs
     */
    public function getSeoData(): array
    {
        $seoFields = array_keys($this->getSeoAccessibleFields());
        $seoData = [];

        foreach ($seoFields as $field) {
            $seoData[$field] = $this->{$field} ?? null;
        }

        return $seoData;
    }

    /**
     * Set multiple SEO fields at once
     *
     * @param array<string, string> $seoData Array of SEO field name => value pairs
     * @return $this
     */
    public function setSeoData(array $seoData)
    {
        $allowedFields = array_keys($this->getSeoAccessibleFields());

        foreach ($seoData as $field => $value) {
            if (in_array($field, $allowedFields)) {
                $this->{$field} = $value;
            }
        }

        return $this;
    }

    /**
     * Get the effective meta title (falls back to title if meta_title is empty)
     *
     * @return string|null
     */
    public function getEffectiveMetaTitle(): ?string
    {
        if (!empty($this->meta_title)) {
            return $this->meta_title;
        }

        // Fall back to entity title if available
        if (property_exists($this, 'title') && !empty($this->title)) {
            return $this->title;
        }

        // Fall back to entity name if available (for galleries)
        if (property_exists($this, 'name') && !empty($this->name)) {
            return $this->name;
        }

        return null;
    }

    /**
     * Get the effective meta description (falls back to description/lede if meta_description is empty)
     *
     * @return string|null
     */
    public function getEffectiveMetaDescription(): ?string
    {
        if (!empty($this->meta_description)) {
            return $this->meta_description;
        }

        // Fall back to entity description if available
        if (property_exists($this, 'description') && !empty($this->description)) {
            return $this->description;
        }

        // Fall back to entity lede if available (for articles)
        if (property_exists($this, 'lede') && !empty($this->lede)) {
            return $this->lede;
        }

        return null;
    }
}
