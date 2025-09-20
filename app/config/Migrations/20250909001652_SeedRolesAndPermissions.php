<?php
declare(strict_types=1);

use Migrations\BaseMigration;
use Cake\Utility\Text;

class SeedRolesAndPermissions extends BaseMigration
{
    /**
     * Up Method.
     *
     * Seeds initial roles and permissions data
     * @return void
     */
    public function up(): void
    {
        // Define roles with UUIDs stored for reference
        $adminId = Text::uuid();
        $editorId = Text::uuid();
        $authorId = Text::uuid();
        $contributorId = Text::uuid();
        $subscriberId = Text::uuid();
        $customerId = Text::uuid();
        $shopManagerId = Text::uuid();
        $loggedInId = Text::uuid();
        $guestId = Text::uuid();
        
        $roles = [
            [
                'id' => $adminId,
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Full system access',
                'is_system' => true,
                'priority' => 100,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $editorId,
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Can edit all content (articles, products)',
                'is_system' => true,
                'priority' => 80,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $authorId,
                'name' => 'Author',
                'slug' => 'author',
                'description' => 'Can create and edit own content',
                'is_system' => true,
                'priority' => 60,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $shopManagerId,
                'name' => 'Shop Manager',
                'slug' => 'shop_manager',
                'description' => 'Can manage products and orders',
                'is_system' => true,
                'priority' => 70,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $contributorId,
                'name' => 'Contributor',
                'slug' => 'contributor',
                'description' => 'Can create content but needs approval',
                'is_system' => true,
                'priority' => 50,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $subscriberId,
                'name' => 'Subscriber',
                'slug' => 'subscriber',
                'description' => 'Can view premium content',
                'is_system' => true,
                'priority' => 40,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $customerId,
                'name' => 'Customer',
                'slug' => 'customer',
                'description' => 'Can view and purchase products',
                'is_system' => true,
                'priority' => 30,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $loggedInId,
                'name' => 'Logged In User',
                'slug' => 'logged_in',
                'description' => 'Default role for registered users',
                'is_system' => true,
                'priority' => 20,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ],
            [
                'id' => $guestId,
                'name' => 'Guest',
                'slug' => 'guest',
                'description' => 'Non-authenticated users',
                'is_system' => true,
                'priority' => 10,
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            ]
        ];

        // Insert roles
        $this->table('roles')->insert($roles)->save();

        // Update existing users
        $this->execute("UPDATE users SET role_id = '$adminId' WHERE is_admin = 1");
        $this->execute("UPDATE users SET role_id = '$loggedInId' WHERE is_admin = 0 AND role = 'user'");
    }

    /**
     * Down Method.
     */
    public function down(): void
    {
        $this->execute('UPDATE users SET role_id = NULL');
        $this->execute('DELETE FROM roles');
    }
}
