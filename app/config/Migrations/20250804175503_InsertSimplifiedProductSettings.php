<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\AbstractMigration;

class InsertSimplifiedProductSettings extends AbstractMigration
{
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 50,
                'category' => 'Products',
                'key_name' => 'enabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable the products system. When disabled, products will not be accessible on the frontend.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 51,
                'category' => 'Products',
                'key_name' => 'userSubmissions',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Allow users to submit products for review. When enabled, registered users can add products that require approval.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 52,
                'category' => 'Products',
                'key_name' => 'aiVerificationEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable AI-powered verification of product submissions. Uses AI to validate product information and suggest improvements.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 53,
                'category' => 'Products',
                'key_name' => 'peerVerificationEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable peer verification where users can verify and rate product accuracy.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 54,
                'category' => 'Products',
                'key_name' => 'minVerificationScore',
                'value' => '3.0',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Minimum verification score (0-5) required for automatic approval. Products below this score require manual review.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 55,
                'category' => 'Products',
                'key_name' => 'autoPublishThreshold',
                'value' => '4.0',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Reliability score threshold for automatic publishing. Products scoring above this will be automatically published.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 56,
                'category' => 'Products',
                'key_name' => 'maxUserSubmissionsPerDay',
                'value' => '5',
                'value_type' => 'numeric',
                'value_obscure' => false,
                'description' => 'Maximum number of products a user can submit per day. Set to 0 for unlimited submissions.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 57,
                'category' => 'Products',
                'key_name' => 'duplicateDetectionEnabled',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Enable duplicate detection to prevent submission of identical products based on title and manufacturer.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 58,
                'category' => 'Products',
                'key_name' => 'productImageRequired',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Require at least one product image for publication. Helps maintain visual consistency.',
                'data' => null,
                'column_width' => 2,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 59,
                'category' => 'Products',
                'key_name' => 'technicalSpecsRequired',
                'value' => '1',
                'value_type' => 'bool',
                'value_obscure' => false,
                'description' => 'Require basic technical specifications (description, manufacturer, model) for product approval.',
                'data' => null,
                'column_width' => 2,
            ])
            ->save();
    }
}
