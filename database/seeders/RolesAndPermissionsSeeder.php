<?php

namespace Database\Seeders;

use App\Support\Authorization\PermissionMap;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = config('permissions.guard_name', 'web');
        $allPermissions = PermissionMap::all();

        foreach ($allPermissions as $permission) {
            Permission::findOrCreate($permission, $guardName);
        }

        foreach (config('permissions.roles', []) as $roleName => $definition) {
            $role = Role::findOrCreate($roleName, $guardName);
            $permissions = $definition['permissions'] ?? [];

            if ($permissions === ['*']) {
                $role->syncPermissions($allPermissions);
                continue;
            }

            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
