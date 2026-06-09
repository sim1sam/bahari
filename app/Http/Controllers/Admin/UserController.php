<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::staff()->with('role')->withCount('orders')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->input('role')) {
            $query->where('role_id', $roleId);
        }

        return view('admin.users.index', [
            'users' => $query->paginate(15)->withQueryString(),
            'roles' => Role::active()->withAdminAccess()->orderBy('name')->get(),
            'search' => $search ?? '',
            'roleFilter' => $roleId ?? '',
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'roles' => Role::active()->withAdminAccess()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user): View
    {
        $this->ensureStaff($user);

        return view('admin.users.form', [
            'user' => $user,
            'roles' => Role::query()
                ->withAdminAccess()
                ->where(function ($q) use ($user) {
                    $q->active();
                    if ($user->role_id) {
                        $q->orWhere('id', $user->role_id);
                    }
                })
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureStaff($user);

        $user->update($this->validateUser($request, $user));

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->ensureStaff($user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted.');
    }

    private function ensureStaff(User $user): void
    {
        if (! $user->role?->can_access_admin) {
            abort(404);
        }
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,'.($user?->id ?? 'NULL'),
            'role_id' => 'required|exists:roles,id',
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        $role = Role::find($validated['role_id']);

        if (! $role || ! $role->can_access_admin) {
            throw ValidationException::withMessages([
                'role_id' => 'Select a valid staff/admin role.',
            ]);
        }

        if (! $role->is_active && $role->id !== $user?->role_id) {
            throw ValidationException::withMessages([
                'role_id' => 'Selected role is inactive. Choose an active role.',
            ]);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        return $validated;
    }
}
