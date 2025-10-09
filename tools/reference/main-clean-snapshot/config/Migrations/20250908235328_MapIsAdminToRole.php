<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class MapIsAdminToRole extends BaseMigration
{
    /**
     * Up Method.
     *
     * Maps existing is_admin values to the new role field.
     *
     * @return void
     */
    public function up(): void
    {
        // Update existing admin users to have 'admin' role
        $this->execute("UPDATE users SET role = 'admin' WHERE is_admin = 1");
        
        // Update any remaining users to have 'user' role
        $this->execute("UPDATE users SET role = 'user' WHERE role IS NULL OR role = ''");
    }

    /**
     * Down Method.
     *
     * Reverts the role mapping back to is_admin.
     *
     * @return void
     */
    public function down(): void
    {
        // Set is_admin back to 1 for admin role users
        $this->execute("UPDATE users SET is_admin = 1 WHERE role = 'admin'");
        
        // Set is_admin to 0 for all other roles
        $this->execute("UPDATE users SET is_admin = 0 WHERE role != 'admin'");
    }
}
