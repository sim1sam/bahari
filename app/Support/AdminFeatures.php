<?php

namespace App\Support;

use App\Models\User;

class AdminFeatures
{
    public static function all(): array
    {
        return config('admin_features', []);
    }

    public static function keys(): array
    {
        return array_keys(self::all());
    }

    public static function routeFor(string $key): ?string
    {
        return self::all()[$key]['route'] ?? null;
    }

    public static function firstAccessibleRoute(User $user): ?string
    {
        foreach (self::keys() as $key) {
            if ($user->canAccessAdminFeature($key)) {
                $route = self::routeFor($key);

                if ($route) {
                    return $route;
                }
            }
        }

        return null;
    }
}
