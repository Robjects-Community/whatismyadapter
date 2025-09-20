<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\Datasource\EntityInterface;

/**
 * SeoFieldsTrait
 *
 * Provides common SEO field management functionality for Table classes.
 * This trait consolidates duplicate SEO field handling logic that was
 * previously scattered across multiple table classes.
 */
trait SeoFieldsTrait
{
    /**
     * Get the standard SEO fields used across the application
     *
     * @return array<string> List of standard SEO field names
     */
    protected function getStandardSeoFields(): array
    {
        return [
            'meta_title',
            'meta_description',
            'meta_keywords',
            'facebook_description',
            'linkedin_description',
            'twitter_description',
            'instagram_description',
        ];
    }

    /**
     * Get all SEO fields for this table (override in table classes if needed)
     *
     * @return array<string> List of all SEO field names for this table
     */
    protected function getAllSeoFields(): array
    {
        return $this->getStandardSeoFields();
    }

    /**
     * Checks if any of the SEO fields are empty
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to check
     * @return array<string> List of empty SEO field names
     */
    public function emptySeoFields(EntityInterface $entity): array
    {
        $seoFields = $this->getAllSeoFields();

        return array_filter($seoFields, fn($field) => empty($entity->{$field}));
    }

    /**
     * Checks if any of the original language fields for translation are empty
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to check
     * @return array<string> List of empty translation field names
     */
    public function emptyTranslationFields(EntityInterface $entity): array
    {
        if ($this->behaviors()->has('Translate')) {
            $config = $this->behaviors()->get('Translate')->getConfig();

            return array_filter($config['fields'], fn($field) => empty($entity->{$field}));
        }

        return [];
    }

    /**
     * Update only empty SEO fields with new values
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to update
     * @param array<string, string> $seoData Array of SEO field values
     * @return array<string> List of fields that were updated
     */
    public function updateEmptySeoFields(EntityInterface $entity, array $seoData): array
    {
        $emptyFields = $this->emptySeoFields($entity);
        $updatedFields = [];

        foreach ($emptyFields as $field) {
            if (isset($seoData[$field]) && !empty($seoData[$field])) {
                $entity->{$field} = $seoData[$field];
                $updatedFields[] = $field;
            }
        }

        return $updatedFields;
    }
}
