<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Table\ProductFormFieldsTable;
use App\Service\Ai\AiProviderInterface;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;
use Exception;

/**
 * Product Form Field Service
 *
 * Handles dynamic form field generation, AI-based auto-fill suggestions,
 * and form validation for product submission forms.
 */
class ProductFormFieldService
{
    private ProductFormFieldsTable $ProductFormFields;
    private ?AiProviderInterface $aiProvider;

    /**
     * Constructor
     *
     * @param \App\Service\Ai\AiProviderInterface|null $aiProvider AI provider for form suggestions
     */
    public function __construct(?AiProviderInterface $aiProvider = null)
    {
        $this->ProductFormFields = TableRegistry::getTableLocator()->get('ProductFormFields');
        $this->aiProvider = $aiProvider;
    }

    /**
     * Get all active form fields grouped by field group
     *
     * @return array Associative array of field groups containing form field data
     */
    public function getActiveFormFields(): array
    {
        $fields = $this->ProductFormFields->find()
            ->where(['is_active' => true])
            ->orderAsc('field_group')
            ->orderAsc('display_order')
            ->toArray();

        $groupedFields = [];
        foreach ($fields as $field) {
            $group = $field->field_group ?: 'default';
            $groupedFields[$group][] = $field;
        }

        return $groupedFields;
    }

    /**
     * Get AI suggestions for a specific field based on existing form data
     *
     * @param string $fieldName Field name to get suggestions for
     * @param array $existingData Current form data for context
     * @return array Array with 'suggestions' and 'confidence' keys
     */
    public function getAiSuggestions(string $fieldName, array $existingData = []): array
    {
        if (!$this->aiProvider) {
            return [
                'suggestions' => [],
                'confidence' => 0,
                'reasoning' => 'AI provider not available',
            ];
        }

        $field = $this->ProductFormFields->find()
            ->where(['field_name' => $fieldName, 'is_active' => true, 'ai_enabled' => true])
            ->first();

        if (!$field) {
            return [
                'suggestions' => [],
                'confidence' => 0,
                'reasoning' => 'Field not found or AI not enabled for this field',
            ];
        }

        try {
            // Prepare AI context based on field mapping
            $aiContext = $this->buildAiContext($field, $existingData);

            // Generate AI prompt from template
            $prompt = $this->buildAiPrompt($field, $existingData);

            // Get AI suggestions
            $suggestions = $this->aiProvider->getSuggestions([
                'field_name' => $fieldName,
                'field_type' => $field->field_type,
                'prompt' => $prompt,
                'existing_data' => $existingData,
            ], $aiContext);

            return $suggestions;
        } catch (Exception $e) {
            Log::error('AI suggestion error for field ' . $fieldName . ': ' . $e->getMessage());

            return [
                'suggestions' => [],
                'confidence' => 0,
                'reasoning' => 'AI suggestion service temporarily unavailable',
            ];
        }
    }

    /**
     * Build AI context based on field mapping configuration
     *
     * @param object $field Form field entity
     * @param array $existingData Current form data
     * @return array Context array for AI processing
     */
    private function buildAiContext(object $field, array $existingData): array
    {
        $context = [
            'field_type' => $field->field_type,
            'field_validation' => $field->field_validation ? json_decode($field->field_validation, true) : [],
            'field_options' => $field->field_options ? json_decode($field->field_options, true) : [],
        ];

        if ($field->ai_field_mapping) {
            $mapping = json_decode($field->ai_field_mapping, true);
            if (isset($mapping['source_fields'])) {
                $context['source_data'] = [];
                foreach ($mapping['source_fields'] as $sourceField) {
                    if (isset($existingData[$sourceField])) {
                        $context['source_data'][$sourceField] = $existingData[$sourceField];
                    }
                }
            }
        }

        return $context;
    }

    /**
     * Build AI prompt from template with data substitution
     *
     * @param object $field Form field entity
     * @param array $existingData Current form data
     * @return string Formatted AI prompt
     */
    private function buildAiPrompt(object $field, array $existingData): string
    {
        $prompt = $field->ai_prompt_template ?: '';

        // Replace placeholders with actual data
        foreach ($existingData as $key => $value) {
            $placeholder = '{' . $key . '}';
            $prompt = str_replace($placeholder, (string)$value, $prompt);
        }

        return $prompt;
    }

    /**
     * Validate form data against field validation rules
     *
     * @param array $formData Form data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateFormData(array $formData): array
    {
        $errors = [];
        $fields = $this->ProductFormFields->find()
            ->where(['is_active' => true])
            ->toArray();

        foreach ($fields as $field) {
            $fieldName = $field->field_name;
            $value = $formData[$fieldName] ?? null;
            $validation = $field->field_validation ? json_decode($field->field_validation, true) : [];

            // Required field validation
            if ($field->is_required && empty($value)) {
                $errors[$fieldName][] = $field->field_label . ' is required.';
                continue;
            }

            // Skip further validation if field is empty and not required
            if (empty($value) && !$field->is_required) {
                continue;
            }

            // Apply validation rules
            foreach ($validation as $rule => $ruleValue) {
                switch ($rule) {
                    case 'min_length':
                        if (strlen((string)$value) < (int)$ruleValue) {
                            $errors[$fieldName][] = $field->field_label . ' must be at least ' .
                                $ruleValue . ' characters long.';
                        }
                        break;
                    case 'max_length':
                        if (strlen((string)$value) > (int)$ruleValue) {
                            $errors[$fieldName][] = $field->field_label . ' must not exceed ' .
                                $ruleValue . ' characters.';
                        }
                        break;
                    case 'min':
                        if (is_numeric($value) && (float)$value < (float)$ruleValue) {
                            $errors[$fieldName][] = $field->field_label . ' must be at least ' . $ruleValue . '.';
                        }
                        break;
                    case 'max':
                        if (is_numeric($value) && (float)$value > (float)$ruleValue) {
                            $errors[$fieldName][] = $field->field_label . ' must not exceed ' . $ruleValue . '.';
                        }
                        break;
                }
            }
        }

        return $errors;
    }

    /**
     * Get form field configuration for rendering
     *
     * @param string $fieldName Field name
     * @return array|null Field configuration or null if not found
     */
    public function getFieldConfig(string $fieldName): ?array
    {
        $field = $this->ProductFormFields->find()
            ->where(['field_name' => $fieldName, 'is_active' => true])
            ->first();

        if (!$field) {
            return null;
        }

        return [
            'name' => $field->field_name,
            'label' => $field->field_label,
            'type' => $field->field_type,
            'placeholder' => $field->field_placeholder,
            'help_text' => $field->field_help_text,
            'options' => $field->field_options ? json_decode($field->field_options, true) : [],
            'validation' => $field->field_validation ? json_decode($field->field_validation, true) : [],
            'group' => $field->field_group,
            'order' => $field->display_order,
            'width' => $field->column_width,
            'required' => $field->is_required,
            'ai_enabled' => $field->ai_enabled,
            'default_value' => $field->default_value,
            'css_classes' => $field->css_classes,
            'html_attributes' => $field->html_attributes ? json_decode($field->html_attributes, true) : [],
        ];
    }

    /**
     * Update field configuration
     *
     * @param string $fieldName Field name to update
     * @param array $config New configuration data
     * @return bool Success status
     */
    public function updateFieldConfig(string $fieldName, array $config): bool
    {
        $field = $this->ProductFormFields->find()
            ->where(['field_name' => $fieldName])
            ->first();

        if (!$field) {
            return false;
        }

        try {
            $field = $this->ProductFormFields->patchEntity($field, $config);

            return $this->ProductFormFields->save($field) !== false;
        } catch (Exception $e) {
            Log::error('Error updating field config: ' . $e->getMessage());

            return false;
        }
    }

    /**
     * Get group display names and configuration
     *
     * @return array Group configuration
     */
    public function getFieldGroups(): array
    {
        return [
            'basic_information' => [
                'title' => 'Basic Information',
                'icon' => 'fas fa-edit',
                'description' => 'Essential product details and identification',
            ],
            'media' => [
                'title' => 'Product Images',
                'icon' => 'fas fa-image',
                'description' => 'Visual content and accessibility information',
            ],
            'technical_details' => [
                'title' => 'Technical Details',
                'icon' => 'fas fa-cog',
                'description' => 'Technical specifications and certifications',
            ],
            'categories' => [
                'title' => 'Categories & Tags',
                'icon' => 'fas fa-tags',
                'description' => 'Product categorization and classification',
            ],
        ];
    }
}
