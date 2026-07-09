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
        return array_keys(self::assignable());
    }

    public static function assignable(): array
    {
        return array_filter(
            self::all(),
            fn (array $feature) => ! isset($feature['permission'])
        );
    }

    public static function permissionFor(string $key): string
    {
        $features = self::all();

        return $features[$key]['permission'] ?? $key;
    }

    public static function featureForNavigation(string $key): ?array
    {
        $feature = self::all()[$key] ?? null;

        if (! $feature) {
            return null;
        }

        return collect($feature)->except('permission')->all();
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
        $menu = self::menuConfig();
        $navigation = [];

        foreach ($menu['standalone'] ?? [] as $item) {
            $navItem = self::resolveMenuItem($item, $user);

            if ($navItem) {
                $navigation[] = [
                    'type' => 'item',
                    'key' => $navItem['key'],
                    'feature' => $navItem['feature'],
                ];
            }
        }

        foreach ($menu['groups'] ?? [] as $groupKey => $group) {
            $items = [];

            foreach ($group['items'] ?? [] as $item) {
                $navItem = self::resolveMenuItem($item, $user);

                if ($navItem) {
                    $items[] = $navItem;
                }
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

    /**
     * @param  string  $item
     * @return array{key: string, feature: array}|null
     */
    private static function resolveMenuItem(string $item, User $user): ?array
    {
        if (! isset(self::all()[$item]) || ! $user->canAccessAdminFeature(self::permissionFor($item))) {
            return null;
        }

        return [
            'key' => $item,
            'feature' => self::featureForNavigation($item),
        ];
    }

    public static function isNavigationItemActive(array $feature): bool
    {
        $active = $feature['active'];

        if (str_contains($active, '|')) {
            return request()->routeIs(explode('|', $active));
        }

        return request()->routeIs($active);
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
