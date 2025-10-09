<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class SeedProductFormSettings extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Seeds initial Product form settings to support the admin interface
     * and avoid validation errors on first use.
     *
     * @return void
     */
    public function change(): void
    {
        $now = date('Y-m-d H:i:s');
        
        // Define product form settings with their defaults
        $settings = [
            [
                'id' => Text::uuid(),
                'ordering' => 200,
                'category' => 'Products',
                'key_name' => 'enable_public_submissions',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Allow public users to submit products via frontend forms',
                'data' => null,
                'column_width' => 2,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 201,
                'category' => 'Products',
                'key_name' => 'require_admin_approval',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Whether user-submitted products require admin approval before publication',
                'data' => null,
                'column_width' => 2,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 202,
                'category' => 'Products',
                'key_name' => 'default_status',
                'value' => 'pending',
                'value_type' => 'select',
                'value_obscure' => false,
                'description' => 'Default verification status for user-submitted products',
                'data' => json_encode([
                    'pending' => 'Pending Review',
                    'approved' => 'Approved', 
                    'rejected' => 'Rejected'
                ]),
                'column_width' => 2,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 203,
                'category' => 'Products',
                'key_name' => 'max_file_size',
                'value' => '5',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum file size in MB for product image uploads',
                'data' => null,
                'column_width' => 2,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 204,
                'category' => 'Products',
                'key_name' => 'allowed_file_types',
                'value' => 'jpg,jpeg,png,gif,webp',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Comma-separated list of allowed file extensions for product images',
                'data' => null,
                'column_width' => 4,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 205,
                'category' => 'Products',
                'key_name' => 'required_fields',
                'value' => 'title,description,manufacturer',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Comma-separated list of required form fields',
                'data' => null,
                'column_width' => 4,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 206,
                'category' => 'Products',
                'key_name' => 'notification_email',
                'value' => '0',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Email address to notify when new products are submitted (use 0 to disable)',
                'data' => null,
                'column_width' => 3,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 207,
                'category' => 'Products',
                'key_name' => 'success_message',
                'value' => 'Your product has been submitted and is awaiting review. Thank you for contributing to our adapter database!',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'Message shown to users after successful product submission',
                'data' => null,
                'column_width' => 6,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 208,
                'category' => 'Products',
                'key_name' => 'quiz_enabled',
                'value' => '0',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable quiz-based adapter finder to help users discover suitable adapters',
                'data' => null,
                'column_width' => 2,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 209,
                'category' => 'Products',
                'key_name' => 'quiz_config_json',
                'value' => '{}',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'JSON configuration for quiz questions, branching logic, and scoring algorithm',
                'data' => null,
                'column_width' => 12,
                'created' => $now,
                'modified' => $now,
            ],
            [
                'id' => Text::uuid(),
                'ordering' => 210,
                'category' => 'Products',
                'key_name' => 'quiz_results_page',
                'value' => '0',
                'value_type' => 'select-page',
                'value_obscure' => false,
                'description' => 'Page to redirect users to after quiz completion (0 = disabled)',
                'data' => null,
                'column_width' => 3,
                'created' => $now,
                'modified' => $now,
            ],
        ];
        
        // Insert all settings
        $this->table('settings')->insert($settings)->save();
    }
}
