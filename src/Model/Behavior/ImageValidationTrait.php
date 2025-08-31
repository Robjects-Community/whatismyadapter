<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Validation\Validator;

/**
 * ImageValidationTrait
 *
 * Provides reusable image validation rules for models that handle image uploads.
 * This trait consolidates common image validation logic that was previously
 * duplicated across multiple table classes (Images, Articles, Users, Tags).
 *
 * Usage:
 * ```php
 * use App\Model\Behavior\ImageValidationTrait;
 *
 * class ImagesTable extends Table
 * {
 *     use ImageValidationTrait;
 *
 *     public function validationCreate(Validator $validator): Validator
 *     {
 *         return $this->addImageValidationRules($validator, 'image', true);
 *     }
 * }
 * ```
 */
trait ImageValidationTrait
{
    /**
     * Add standard image validation rules to a validator
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param bool $required Whether the image field is required (default: false)
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addImageValidationRules(
        Validator $validator,
        string $field = 'image',
        bool $required = false,
        array $options = [],
    ): Validator {
        // Merge default options with provided options
        $config = array_merge([
            'allowedMimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
            'maxFileSize' => '10MB',
            'messages' => [
                'mimeType' => __('Please upload only images (jpeg, png, gif).'),
                'fileSize' => __('Image must be less than 10MB.'),
                'required' => __('An image file is required.'),
            ],
        ], $options);

        // Handle required validation
        if ($required) {
            $validator
                ->requirePresence($field, 'create')
                ->notEmptyFile($field, $config['messages']['required']);
        } else {
            $validator->allowEmptyFile($field);
        }

        // Add file-specific validation rules
        return $validator->add($field, [
            'mimeType' => [
                'rule' => ['mimeType', $config['allowedMimeTypes']],
                'message' => $config['messages']['mimeType'],
                'on' => function ($context) use ($field) {
                    // Only validate mime type if file was uploaded successfully
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
            'fileSize' => [
                'rule' => ['fileSize', '<=', $config['maxFileSize']],
                'message' => $config['messages']['fileSize'],
                'on' => function ($context) use ($field) {
                    // Only validate file size if file was uploaded successfully
                    return !empty($context['data'][$field])
                        && is_object($context['data'][$field])
                        && method_exists($context['data'][$field], 'getError')
                        && $context['data'][$field]->getError() === UPLOAD_ERR_OK;
                },
            ],
        ]);
    }

    /**
     * Add image validation rules for create operations (required)
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addRequiredImageValidation(
        Validator $validator,
        string $field = 'image',
        array $options = [],
    ): Validator {
        return $this->addImageValidationRules($validator, $field, true, $options);
    }

    /**
     * Add image validation rules for update operations (optional)
     *
     * @param \Cake\Validation\Validator $validator The validator instance
     * @param string $field The field name to validate (default: 'image')
     * @param array $options Additional options for customization
     * @return \Cake\Validation\Validator
     */
    public function addOptionalImageValidation(
        Validator $validator,
        string $field = 'image',
        array $options = [],
    ): Validator {
        return $this->addImageValidationRules($validator, $field, false, $options);
    }
}
