<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\BaseMigration;

class SeedDefaultProductFormFields extends BaseMigration
{
    /**
     * Seed default product form fields with AI-based auto-fill capabilities
     *
     * @return void
     */
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define default product form fields with AI capabilities
        $formFields = [
            // Basic Information Section
            [
                'id' => Text::uuid(),
                'field_name' => 'title',
                'field_label' => 'Product Name',
                'field_type' => 'text',
                'field_placeholder' => 'e.g., USB-C to HDMI Adapter',
                'field_help_text' => 'Use a clear, descriptive name that includes the product type and key features.',
                'field_group' => 'basic_information',
                'display_order' => 10,
                'column_width' => 8,
                'is_required' => true,
                'is_active' => true,
                'ai_enabled' => true,
                'ai_prompt_template' => 'Based on the manufacturer "{manufacturer}" and description "{description}", suggest an optimal product title that is clear, concise, and includes key features.',
                'ai_field_mapping' => json_encode(['source_fields' => ['manufacturer', 'description', 'model_number']]),
                'field_validation' => json_encode(['required' => true, 'min_length' => 3, 'max_length' => 255]),
                'css_classes' => 'form-control-lg',
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'field_name' => 'manufacturer',
                'field_label' => 'Manufacturer',
                'field_type' => 'text',
                'field_placeholder' => 'e.g., Anker, Belkin, Apple',
                'field_help_text' => 'Enter the brand or company that manufactures this product.',
                'field_group' => 'basic_information',
                'display_order' => 20,
                'column_width' => 4,
                'is_required' => true,
                'is_active' => true,
                'ai_enabled' => true,
                'ai_prompt_template' => 'From the product title "{title}" and description "{description}", identify and suggest the most likely manufacturer or brand name.',
                'ai_field_mapping' => json_encode(['source_fields' => ['title', 'description']]),
                'field_validation' => json_encode(['required' => true, 'min_length' => 2, 'max_length' => 100]),
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'field_name' => 'description',
                'field_label' => 'Product Description',
                'field_type' => 'textarea',
                'field_placeholder' => 'Describe what this product does, its key features, compatibility, and any other important details...',
                'field_help_text' => 'Include key features, compatibility information, and what makes this product useful.',
                'field_group' => 'basic_information',
                'display_order' => 30,
                'column_width' => 12,
                'is_required' => true,
                'is_active' => true,
                'ai_enabled' => true,
                'ai_prompt_template' => 'Create a comprehensive product description for "{title}" by {manufacturer}. Include key features, technical specifications, compatibility information, and benefits.',
                'ai_field_mapping' => json_encode(['source_fields' => ['title', 'manufacturer', 'model_number', 'technical_specifications']]),
                'field_validation' => json_encode(['required' => true, 'min_length' => 20, 'max_length' => 2000]),
                'html_attributes' => json_encode(['rows' => '4']),
                'created' => $now,
                'modified' => $now,
            ],
        ];
        
        // Insert all form fields
        $this->table('product_form_fields')->insert($formFields)->save();
    }
}
