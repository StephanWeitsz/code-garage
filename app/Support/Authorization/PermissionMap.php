<?php

namespace App\Support\Authorization;

class PermissionMap
{
    public static function all(): array
    {
        $permissions = [];

        foreach (config('permissions.modules', []) as $module => $abilities) {
            foreach ($abilities as $ability) {
                $permissions[] = sprintf('%s.%s', $module, $ability);
            }
        }

        return array_values(array_unique($permissions));
    }
}
