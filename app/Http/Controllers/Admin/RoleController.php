<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::withCount('users')->orderBy('name')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', ['role' => new Role]);
    }

    public function store(Request $request): RedirectResponse
    {
        Role::create($this->validateRole($request));

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', compact('role'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $role->update($this->validateRole($request, $role));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->isSystem()) {
            return redirect()->route('admin.roles.index')->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()->route('admin.roles.index')->with('error', 'Cannot delete a role that is assigned to users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|alpha_dash|unique:roles,slug,'.($role?->id ?? 'NULL'),
            'description' => 'nullable|string|max:255',
            'can_access_admin' => 'boolean',
        ]);

        $validated['can_access_admin'] = $request->boolean('can_access_admin');

        if ($role?->isSystem()) {
            $validated['slug'] = $role->slug;
        }

        return $validated;
    }
}
