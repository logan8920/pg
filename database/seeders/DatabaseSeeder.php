<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::create([
            'name' => 'Test User',
            'email' => 'admin@gmail.com',
            'password' => 'admin123'
        ]);

        $role = Role::create(['name' => 'admin']);

        Permission::insert([
            ['name' => 'dashboard', 'guard_name' => 'web','parent' => 0],
            ['name' => 'user-management', 'guard_name' => 'web', 'parent' => 0],
            ['name' => 'role-list', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-create', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-store', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-show', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-update', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-destroy', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-edit', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'role-assginPermssion', 'guard_name' => 'web', 'parent' => 2],

            ['name' => 'permission', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-list', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-create', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-store', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-show', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-update', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-destroy', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'permission-edit', 'guard_name' => 'web', 'parent' => 2],

            ['name' => 'user', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-list', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-create', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-store', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-show', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-update', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-destroy', 'guard_name' => 'web', 'parent' => 2],
            ['name' => 'user-edit', 'guard_name' => 'web', 'parent' => 2],
        ]);

        $permissions = Permission::pluck('name')->toArray();

        $role->syncPermissions($permissions);

        $user->assignRole($role);

    }
}
