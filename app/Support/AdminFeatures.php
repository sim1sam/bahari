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

    public static function menuConfig(): array
    {
        return config('admin_menu', []);
    }

    /**
     * @return array<int, array{type: string, key?: string, group?: string, label?: string, icon?: string, items?: array<int, array{key: string, feature: array}>}>
     */
    public static function navigationFor(User $user): array
    {
        $features = self::all();
        $menu = self::menuConfig();
        $navigation = [];

        foreach ($menu['standalone'] ?? [] as $key) {
            if (! isset($features[$key]) || ! $user->canAccessAdminFeature($key)) {
                continue;
            }

            $navigation[] = [
                'type' => 'item',
                'key' => $key,
                'feature' => $features[$key],
            ];
        }

        foreach ($menu['groups'] ?? [] as $groupKey => $group) {
            $items = [];

            foreach ($group['items'] ?? [] as $key) {
                if (! isset($features[$key]) || ! $user->canAccessAdminFeature($key)) {
                    continue;
                }

                $items[] = [
                    'key' => $key,
                    'feature' => $features[$key],
                ];
            }

            if ($items === []) {
                continue;
            }

            if (count($items) === 1) {
                $navigation[] = [
                    'type' => 'item',
                    'key' => $items[0]['key'],
                    'feature' => $items[0]['feature'],
                ];

                continue;
            }

            $navigation[] = [
                'type' => 'group',
                'group' => $groupKey,
                'label' => $group['label'],
                'icon' => $group['icon'],
                'items' => $items,
            ];
        }

        return $navigation;
    }

    public static function isNavigationItemActive(array $feature): bool
    {
        return request()->routeIs($feature['active']);
    }

    public static function isNavigationGroupActive(array $items): bool
    {
        foreach ($items as $item) {
            if (self::isNavigationItemActive($item['feature'])) {
                return true;
            }
        }

        return false;
    }
}
