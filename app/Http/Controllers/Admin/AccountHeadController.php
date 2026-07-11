<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountHead;
use App\Models\AccountHeadType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountHeadController extends Controller
{
    public function index(Request $request): View
    {
        $query = AccountHead::query()
            ->with('accountHeadType')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($typeId = $request->input('type')) {
            $query->where('account_head_type_id', $typeId);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $allHeads = AccountHead::query()->get();
        $types = AccountHeadType::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.account-heads.index', [
            'accountHeads' => $query->paginate(15)->withQueryString(),
            'accountTypes' => $types,
            'search' => $search ?? '',
            'typeFilter' => $typeId ?? '',
            'stats' => [
                'total' => $allHeads->count(),
                'active' => $allHeads->where('is_active', true)->count(),
                'types' => $types->count(),
                'inactive' => $allHeads->where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.account-heads.form', [
            'accountHead' => new AccountHead(['is_active' => true, 'sort_order' => 0]),
            'accountTypes' => $this->activeTypes(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        AccountHead::create($this->validateAccountHead($request));

        return redirect()
            ->route('admin.account-heads.index')
            ->with('success', 'Account head created.');
    }

    public function edit(AccountHead $accountHead): View
    {
        return view('admin.account-heads.form', [
            'accountHead' => $accountHead->load('accountHeadType'),
            'accountTypes' => $this->activeTypes($accountHead->account_head_type_id),
        ]);
    }

    public function update(Request $request, AccountHead $accountHead): RedirectResponse
    {
        $accountHead->update($this->validateAccountHead($request, $accountHead));

        return redirect()
            ->route('admin.account-heads.index')
            ->with('success', 'Account head updated.');
    }

    public function destroy(AccountHead $accountHead): RedirectResponse
    {
        $accountHead->delete();

        return redirect()
            ->route('admin.account-heads.index')
            ->with('success', 'Account head deleted.');
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, AccountHeadType> */
    private function activeTypes(?int $includeTypeId = null)
    {
        return AccountHeadType::query()
            ->where(function ($query) use ($includeTypeId) {
                $query->where('is_active', true);

                if ($includeTypeId) {
                    $query->orWhere('id', $includeTypeId);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /** @return array<string, mixed> */
    private function validateAccountHead(Request $request, ?AccountHead $accountHead = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('account_heads', 'code')->ignore($accountHead?->id),
            ],
            'account_head_type_id' => ['required', Rule::exists('account_head_types', 'id')],
            'description' => 'nullable|string|max:500',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $validated['code'] = filled($validated['code'] ?? null) ? strtoupper(trim($validated['code'])) : null;

        return $validated;
    }
}
