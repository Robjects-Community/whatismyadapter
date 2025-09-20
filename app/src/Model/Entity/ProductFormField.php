<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ProductFormField Entity
 *
 * @property string $id
 * @property string $field_name
 * @property string $field_label
 * @property string $field_type
 * @property string|null $field_placeholder
 * @property string|null $field_help_text
 * @property array|null $field_options
 * @property array|null $field_validation
 * @property string|null $field_group
 * @property int $display_order
 * @property int $column_width
 * @property bool $is_required
 * @property bool $is_active
 * @property bool $ai_enabled
 * @property string|null $ai_prompt_template
 * @property array|null $ai_field_mapping
 * @property array|null $conditional_logic
 * @property string|null $default_value
 * @property string|null $css_classes
 * @property array|null $html_attributes
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
class ProductFormField extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'field_name' => true,
        'field_label' => true,
        'field_type' => true,
        'field_placeholder' => true,
        'field_help_text' => true,
        'field_options' => true,
        'field_validation' => true,
        'field_group' => true,
        'display_order' => true,
        'column_width' => true,
        'is_required' => true,
        'is_active' => true,
        'ai_enabled' => true,
        'ai_prompt_template' => true,
        'ai_field_mapping' => true,
        'conditional_logic' => true,
        'default_value' => true,
        'css_classes' => true,
        'html_attributes' => true,
        'created' => true,
        'modified' => true,
    ];
}
