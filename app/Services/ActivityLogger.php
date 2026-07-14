<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function log(
        string $action,
        string $description,
        ?User $user = null,
        ?Request $request = null,
        array $properties = [],
    ): ActivityLog {
        $request ??= request();
        $user ??= auth()->user();

        return ActivityLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_email' => $user?->email,
            'action' => $action,
            'description' => $description,
            'method' => $request?->method(),
            'route_name' => $request?->route()?->getName(),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => substr((string) $request?->userAgent(), 0, 500) ?: null,
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }

    public function logFromRequest(Request $request, ?User $user = null): ?ActivityLog
    {
        if (! $this->shouldLog($request)) {
            return null;
        }

        [$action, $description] = $this->resolveAction($request);

        return $this->log($action, $description, $user, $request, [
            'route_parameters' => $request->route()?->parameters()
                ? collect($request->route()->parameters())
                    ->map(fn ($value) => is_object($value) && method_exists($value, 'getKey') ? $value->getKey() : (is_scalar($value) ? $value : null))
                    ->filter()
                    ->all()
                : [],
        ]);
    }

    public function shouldLog(Request $request): bool
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        $routeName = $request->route()?->getName() ?? '';

        if ($routeName === '' || ! str_starts_with($routeName, 'admin.')) {
            return false;
        }

        $skip = [
            'admin.login.submit',
            'admin.logout',
            'admin.activity-logs.',
        ];

        foreach ($skip as $prefix) {
            if ($routeName === rtrim($prefix, '.') || str_starts_with($routeName, $prefix)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public function resolveAction(Request $request): array
    {
        $routeName = $request->route()?->getName() ?? '';
        $parts = explode('.', $routeName);
        $verb = end($parts) ?: strtolower($request->method());

        $action = match ($verb) {
            'store' => 'created',
            'update' => 'updated',
            'destroy' => 'deleted',
            'status' => 'updated',
            'approve' => 'approved',
            'reject' => 'rejected',
            'sync', 'sync-received', 'syncFromReceived' => 'synced',
            default => $verb,
        };

        $resource = $parts[1] ?? 'item';
        $resourceLabel = str_replace(['-', '_'], ' ', $resource);
        $resourceLabel = ucwords($resourceLabel);

        $description = match ($action) {
            'created' => "Created {$resourceLabel}",
            'updated' => "Updated {$resourceLabel}",
            'deleted' => "Deleted {$resourceLabel}",
            'approved' => "Approved {$resourceLabel}",
            'rejected' => "Rejected {$resourceLabel}",
            'synced' => "Synced {$resourceLabel}",
            default => ucfirst($action).' '.$resourceLabel,
        };

        $params = $request->route()?->parameters() ?? [];
        foreach ($params as $param) {
            if (is_object($param) && method_exists($param, 'getKey')) {
                $description .= ' #'.$param->getKey();
                break;
            }
            if (is_scalar($param)) {
                $description .= ' #'.$param;
                break;
            }
        }

        return [$action, $description];
    }
}
