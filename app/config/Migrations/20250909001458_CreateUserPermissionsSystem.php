<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class CreateUserPermissionsSystem extends BaseMigration
{
    /**
     * Up Method.
     *
     * Creates the permissions system tables and updates existing structure
     * @return void
     */
    public function up(): void
    {
        // Create permissions table
        $permissions = $this->table('permissions', [
            'id' => false,
            'primary_key' => ['id']
        ]);
        
        $permissions->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('name', 'string', [
            'limit' => 100,
            'null' => false,
        ])
        ->addColumn('resource', 'string', [
            'limit' => 50,
            'null' => false,
            'comment' => 'Resource type: articles, products, users, etc.'
        ])
        ->addColumn('action', 'string', [
            'limit' => 50,
            'null' => false,
            'comment' => 'Action: view, create, edit, delete, publish, etc.'
        ])
        ->addColumn('description', 'text', [
            'null' => true,
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addIndex(['resource', 'action'], ['unique' => true])
        ->addIndex(['resource'])
        ->create();

        // Create roles table
        $roles = $this->table('roles', [
            'id' => false,
            'primary_key' => ['id']
        ]);
        
        $roles->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('name', 'string', [
            'limit' => 50,
            'null' => false,
        ])
        ->addColumn('slug', 'string', [
            'limit' => 50,
            'null' => false,
        ])
        ->addColumn('description', 'text', [
            'null' => true,
        ])
        ->addColumn('is_system', 'boolean', [
            'default' => false,
            'null' => false,
            'comment' => 'System roles cannot be deleted'
        ])
        ->addColumn('priority', 'integer', [
            'default' => 0,
            'null' => false,
            'comment' => 'Higher priority = more permissions'
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addIndex(['slug'], ['unique' => true])
        ->addIndex(['priority'])
        ->create();

        // Create roles_permissions pivot table
        $rolesPermissions = $this->table('roles_permissions', [
            'id' => false,
            'primary_key' => ['id']
        ]);
        
        $rolesPermissions->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('role_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('permission_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addIndex(['role_id', 'permission_id'], ['unique' => true])
        ->addIndex(['role_id'])
        ->addIndex(['permission_id'])
        ->create();

        // Create user_groups table for additional grouping
        $userGroups = $this->table('user_groups', [
            'id' => false,
            'primary_key' => ['id']
        ]);
        
        $userGroups->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('name', 'string', [
            'limit' => 100,
            'null' => false,
        ])
        ->addColumn('slug', 'string', [
            'limit' => 100,
            'null' => false,
        ])
        ->addColumn('description', 'text', [
            'null' => true,
        ])
        ->addColumn('is_active', 'boolean', [
            'default' => true,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addColumn('modified', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addIndex(['slug'], ['unique' => true])
        ->create();

        // Create users_groups pivot table
        $usersGroups = $this->table('users_groups', [
            'id' => false,
            'primary_key' => ['id']
        ]);
        
        $usersGroups->addColumn('id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('user_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('user_group_id', 'uuid', [
            'default' => null,
            'null' => false,
        ])
        ->addColumn('created', 'datetime', [
            'default' => null,
            'null' => true,
        ])
        ->addIndex(['user_id', 'user_group_id'], ['unique' => true])
        ->addIndex(['user_id'])
        ->addIndex(['user_group_id'])
        ->create();
        
        // Add role_id to users table
        $usersTable = $this->table('users');
        $userColumns = $this->getColumns('users');
        
        if (!in_array('role_id', $userColumns)) {
            $usersTable->addColumn('role_id', 'uuid', [
                'after' => 'role',
                'default' => null,
                'null' => true,
            ])
            ->addIndex(['role_id'])
            ->update();
        }

        // Add created_by and modified_by to content tables
        $contentTables = ['articles', 'products', 'tags', 'comments'];
        
        foreach ($contentTables as $tableName) {
            if ($this->hasTable($tableName)) {
                $table = $this->table($tableName);
                $columns = $this->getColumns($tableName);
                
                if (!in_array('created_by', $columns)) {
                    $table->addColumn('created_by', 'uuid', [
                        'after' => 'modified',
                        'default' => null,
                        'null' => true,
                    ]);
                }
                
                if (!in_array('modified_by', $columns)) {
                    $table->addColumn('modified_by', 'uuid', [
                        'after' => 'created_by',
                        'default' => null,
                        'null' => true,
                    ]);
                }
                
                if (!in_array('created_by', $columns) || !in_array('modified_by', $columns)) {
                    $table->update();
                }
            }
        }
    }
    
    /**
     * Get column names for a table
     */
    private function getColumns(string $tableName): array
    {
        $adapter = $this->getAdapter();
        $columns = $adapter->getColumns($tableName);
        return array_map(function($col) { return $col->getName(); }, $columns);
    }
    
    /**
     * Down method - for rollback
     */
    public function down(): void
    {
        // Remove added columns from content tables
        $contentTables = ['articles', 'products', 'tags', 'comments'];
        
        foreach ($contentTables as $tableName) {
            if ($this->hasTable($tableName)) {
                $table = $this->table($tableName);
                $columns = $this->getColumns($tableName);
                
                if (in_array('created_by', $columns)) {
                    $table->removeColumn('created_by');
                }
                
                if (in_array('modified_by', $columns)) {
                    $table->removeColumn('modified_by');
                }
                
                if (in_array('created_by', $columns) || in_array('modified_by', $columns)) {
                    $table->update();
                }
            }
        }
        
        // Remove role_id from users table
        if ($this->hasTable('users')) {
            $userColumns = $this->getColumns('users');
            if (in_array('role_id', $userColumns)) {
                $this->table('users')
                    ->removeColumn('role_id')
                    ->update();
            }
        }
        
        // Drop tables in reverse order
        $this->table('users_groups')->drop()->save();
        $this->table('user_groups')->drop()->save();
        $this->table('roles_permissions')->drop()->save();
        $this->table('roles')->drop()->save();
        $this->table('permissions')->drop()->save();
    }
}
