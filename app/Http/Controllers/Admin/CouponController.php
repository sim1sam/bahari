<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(): View
    {
        return view('admin.coupons.index', [
            'coupons' => Coupon::query()
                ->with('customers:id,name,email')
                ->latest()
                ->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.form', [
            'coupon' => new Coupon(['audience' => Coupon::AUDIENCE_PUBLIC, 'is_active' => true]),
            'customers' => $this->customers(),
            'selectedCustomers' => [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCoupon($request);
        $coupon = Coupon::create($validated);
        $this->syncCustomers($coupon, $request);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.form', [
            'coupon' => $coupon->load('customers:id'),
            'customers' => $this->customers(),
            'selectedCustomers' => $coupon->customers->pluck('id')->all(),
        ]);
    }

    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $coupon->update($this->validateCoupon($request, $coupon));
        $this->syncCustomers($coupon, $request);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        Coupon::query()->whereKey($coupon->getKey())->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted.');
    }

    private function customers()
    {
        return User::query()
            ->whereHas('role', fn ($query) => $query->where('slug', Role::SLUG_CUSTOMER))
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }

    /** @return array<string, mixed> */
    private function validateCoupon(Request $request, ?Coupon $coupon = null): array
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code'))),
        ]);

        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
                Rule::unique('coupons', 'code')->ignore($coupon),
            ],
            'label' => 'nullable|string|max:255',
            'discount_type' => ['required', Rule::in([Coupon::TYPE_PERCENT, Coupon::TYPE_FIXED])],
            'discount_value' => 'required|numeric|min:0.01',
            'audience' => ['required', Rule::in([Coupon::AUDIENCE_PUBLIC, Coupon::AUDIENCE_CUSTOMERS])],
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'max_uses' => 'nullable|integer|min:1',
            'per_customer_limit' => 'nullable|integer|min:1',
            'customer_ids' => 'required_if:audience,'.Coupon::AUDIENCE_CUSTOMERS.'|array',
            'customer_ids.*' => 'integer|exists:users,id',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        unset($validated['customer_ids']);

        return $validated;
    }

    private function syncCustomers(Coupon $coupon, Request $request): void
    {
        if ($coupon->audience === Coupon::AUDIENCE_PUBLIC) {
            $coupon->customers()->detach();

            return;
        }

        $coupon->customers()->sync($request->input('customer_ids', []));
    }
}
