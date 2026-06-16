<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAddressController extends Controller
{
    public function index(): View
    {
        $user = $this->user();

        return view('pages.account.addresses.index', [
            'addresses' => $user->addresses()->latest('is_default')->latest()->get(),
            'types' => CustomerAddress::types(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $this->user();
        $validated = $this->validateAddress($request);
        $makeDefault = $request->boolean('is_default') || ! $user->addresses()->exists();

        if ($makeDefault) {
            $user->addresses()->update(['is_default' => false]);
        }

        $user->addresses()->create($validated + ['is_default' => $makeDefault]);

        return back()->with('success', 'Shipping address added.');
    }

    public function update(Request $request, CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);

        $address->update($this->validateAddress($request));

        if ($request->boolean('is_default')) {
            $this->setDefaultAddress($address);
        }

        return back()->with('success', 'Shipping address updated.');
    }

    public function makeDefault(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);
        $this->setDefaultAddress($address);

        return back()->with('success', 'Default shipping address updated.');
    }

    public function destroy(CustomerAddress $address): RedirectResponse
    {
        $this->authorizeAddress($address);
        $wasDefault = $address->is_default;
        CustomerAddress::destroy($address->getKey());

        if ($wasDefault) {
            $nextAddress = $this->user()->addresses()->oldest()->first();

            if ($nextAddress) {
                $this->setDefaultAddress($nextAddress);
            }
        }

        return back()->with('success', 'Shipping address removed.');
    }

    /** @return array<string, mixed> */
    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'type' => 'required|in:home,office,other',
            'label' => 'nullable|string|max:100',
            'recipient_name' => 'required|string|max:200',
            'phone' => 'required|string|max:30',
            'address_line' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'zip' => 'required|string|max:30',
        ]);
    }

    private function authorizeAddress(CustomerAddress $address): void
    {
        abort_unless($address->user_id === Auth::id(), 403);
    }

    private function setDefaultAddress(CustomerAddress $address): void
    {
        $this->user()->addresses()->update(['is_default' => false]);
        $address->forceFill(['is_default' => true])->save();
    }

    private function user(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
