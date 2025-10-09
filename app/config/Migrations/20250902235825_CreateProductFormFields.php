<?php
declare(strict_types=1);

use Migrations\AbstractMigration;

class CreateProductFormFields extends AbstractMigration
{
    /**
     * Create dynamic product form fields table for admin-configurable forms
     * with AI-based field suggestions and auto-fill capabilities.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('product_form_fields', ['id' => false, 'primary_key' => ['id']]);
        $table->addColumn('id', 'uuid')
              ->addColumn('field_name', 'string', ['limit' => 100, 'null' => false])
              ->addColumn('field_label', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('field_type', 'string', ['limit' => 50, 'null' => false, 'comment' => 'text, textarea, select, checkbox, radio, number, email, url, date, file'])
              ->addColumn('field_placeholder', 'text', ['null' => true])
              ->addColumn('field_help_text', 'text', ['null' => true])
              ->addColumn('field_options', 'json', ['null' => true, 'comment' => 'JSON array for select/radio/checkbox options'])
              ->addColumn('field_validation', 'json', ['null' => true, 'comment' => 'JSON validation rules (required, min_length, max_length, etc.)'])
              ->addColumn('field_group', 'string', ['limit' => 100, 'null' => true, 'comment' => 'Group name for organizing fields into sections'])
              ->addColumn('display_order', 'integer', ['null' => false, 'default' => 0])
              ->addColumn('column_width', 'integer', ['null' => false, 'default' => 12, 'comment' => 'Bootstrap column width 1-12'])
              ->addColumn('is_required', 'boolean', ['null' => false, 'default' => false])
              ->addColumn('is_active', 'boolean', ['null' => false, 'default' => true])
              ->addColumn('ai_enabled', 'boolean', ['null' => false, 'default' => false, 'comment' => 'Enable AI suggestions for this field'])
              ->addColumn('ai_prompt_template', 'text', ['null' => true, 'comment' => 'AI prompt template for generating suggestions'])
              ->addColumn('ai_field_mapping', 'json', ['null' => true, 'comment' => 'Map this field to product data keys for AI context'])
              ->addColumn('conditional_logic', 'json', ['null' => true, 'comment' => 'Show/hide field based on other field values'])
              ->addColumn('default_value', 'text', ['null' => true])
              ->addColumn('css_classes', 'string', ['limit' => 255, 'null' => true, 'comment' => 'Additional CSS classes'])
              ->addColumn('html_attributes', 'json', ['null' => true, 'comment' => 'Additional HTML attributes'])
              ->addColumn('created', 'datetime', ['null' => false])
              ->addColumn('modified', 'datetime', ['null' => false])
              ->addIndex(['field_name'], ['unique' => true])
              ->addIndex(['field_group', 'display_order'])
              ->addIndex(['is_active'])
              ->addIndex(['ai_enabled'])
              ->create();
    }
}
