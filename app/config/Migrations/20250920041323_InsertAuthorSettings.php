<?php
declare(strict_types=1);

use Cake\Utility\Text;
use Migrations\BaseMigration;

class InsertAuthorSettings extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     *
     * @return void
     */
    public function change(): void
    {
        $this->table('settings')
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 1,
                'category' => 'Author',
                'key_name' => 'fullName',
                'value' => '',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Your full name as it will appear on the "About the Author" page and throughout the site.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 2,
                'category' => 'Author',
                'key_name' => 'bio',
                'value' => '',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'A brief biography about yourself. This will be displayed on the "About the Author" page. You can use HTML formatting.',
                'data' => null,
                'column_width' => 6,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 3,
                'category' => 'Author',
                'key_name' => 'email',
                'value' => '',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Your contact email address. This will be displayed on contact forms and "Hire Me" page.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 4,
                'category' => 'Author',
                'key_name' => 'githubUrl',
                'value' => '',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Your GitHub profile URL (e.g., https://github.com/yourusername). Leave empty to hide the GitHub link.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 5,
                'category' => 'Author',
                'key_name' => 'linkedinUrl',
                'value' => '',
                'value_type' => 'text',
                'value_obscure' => false,
                'description' => 'Your LinkedIn profile URL (e.g., https://linkedin.com/in/yourprofile). Leave empty to hide the LinkedIn link.',
                'data' => null,
                'column_width' => 4,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 6,
                'category' => 'Author',
                'key_name' => 'aboutPageContent',
                'value' => '',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'Additional content for the "About the Author" page. This appears after your bio and can include more detailed information about your background, skills, or interests. You can use HTML formatting.',
                'data' => null,
                'column_width' => 6,
            ])
            ->insert([
                'id' => Text::uuid(),
                'ordering' => 7,
                'category' => 'Author',
                'key_name' => 'hireMeContent',
                'value' => '',
                'value_type' => 'textarea',
                'value_obscure' => false,
                'description' => 'Content for the "Hire Me" page describing your services, skills, and how potential clients can work with you. You can use HTML formatting.',
                'data' => null,
                'column_width' => 6,
            ])
            ->save();
    }
}
