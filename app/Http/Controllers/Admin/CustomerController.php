<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::customers()->with('role')->withCount('orders')->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return view('admin.customers.index', [
            'customers' => $query->paginate(15)->withQueryString(),
            'search' => $search ?? '',
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.form', [
            'customer' => new User,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCustomer($request);

        $customerRoleId = Role::active()->where('slug', Role::SLUG_CUSTOMER)->value('id');

        if (! $customerRoleId) {
            throw ValidationException::withMessages([
                'email' => 'Customer role is not available. Please activate the Customer role first.',
            ]);
        }

        User::create([
            ...$validated,
            'role_id' => $customerRoleId,
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function edit(User $customer): View
    {
        $this->ensureCustomer($customer);

        return view('admin.customers.form', [
            'customer' => $customer,
        ]);
    }

    public function update(Request $request, User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);

        $customer->update($this->validateCustomer($request, $customer));

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(User $customer): RedirectResponse
    {
        $this->ensureCustomer($customer);

        $customer->delete();

        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted.');
    }

    private function ensureCustomer(User $customer): void
    {
        if (! $customer->hasRole(Role::SLUG_CUSTOMER)) {
            abort(404);
        }
    }

    private function validateCustomer(Request $request, ?User $customer = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email,'.($customer?->id ?? 'NULL'),
            'password' => [$customer ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        return $validated;
    }
}
