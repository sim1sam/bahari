<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountHeadType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountHeadTypeController extends Controller
{
    public function index(): View
    {
        $types = AccountHeadType::query()
            ->withCount('accountHeads')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        $allTypes = AccountHeadType::query()->withCount('accountHeads')->get();

        return view('admin.account-types.index', [
            'accountTypes' => $types,
            'stats' => [
                'total' => $allTypes->count(),
                'active' => $allTypes->where('is_active', true)->count(),
                'in_use' => $allTypes->where('account_heads_count', '>', 0)->count(),
                'heads' => $allTypes->sum('account_heads_count'),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.account-types.form', [
            'accountType' => new AccountHeadType(['is_active' => true, 'sort_order' => 0]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccountHeadType::create($this->validateAccountType($request));

        return redirect()
            ->route('admin.account-types.index')
            ->with('success', 'Account type created.');
    }

    public function edit(AccountHeadType $accountType): View
    {
        return view('admin.account-types.form', [
            'accountType' => $accountType,
        ]);
    }

    public function update(Request $request, AccountHeadType $accountType): RedirectResponse
    {
        $accountType->update($this->validateAccountType($request, $accountType));

        return redirect()
            ->route('admin.account-types.index')
            ->with('success', 'Account type updated.');
    }

    public function destroy(AccountHeadType $accountType): RedirectResponse
    {
        if ($accountType->accountHeads()->exists()) {
            return redirect()
                ->route('admin.account-types.index')
                ->with('error', 'Cannot delete a type that is assigned to account heads.');
        }

        $accountType->delete();

        return redirect()
            ->route('admin.account-types.index')
            ->with('success', 'Account type deleted.');
    }

    /** @return array<string, mixed> */
    private function validateAccountType(Request $request, ?AccountHeadType $accountType = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => [
                'nullable',
                'string',
                'max:50',
                'alpha_dash',
                Rule::unique('account_head_types', 'slug')->ignore($accountType?->id),
            ],
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['slug'] = filled($validated['slug'] ?? null)
            ? strtolower(trim($validated['slug']))
            : AccountHeadType::slugFromName($validated['name']);

        return $validated;
    }
}
