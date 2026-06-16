<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentBank;
use App\Services\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentBankController extends Controller
{
    public function index(): View
    {
        return view('admin.payment-banks.index', [
            'banks' => PaymentBank::query()
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get(),
        ]);
    }

    public function store(Request $request, MediaStorageService $media): RedirectResponse
    {
        $validated = $this->validateBank($request);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $media->storeUpload($request->file('image'), 'payment-banks', field: 'image');
        }

        PaymentBank::create($validated);

        return back()->with('success', 'Payment bank added.');
    }

    public function update(Request $request, PaymentBank $paymentBank, MediaStorageService $media): RedirectResponse
    {
        $validated = $this->validateBank($request);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->boolean('remove_image')) {
            $media->delete($paymentBank->image);
            $validated['image'] = null;
        }

        if ($request->hasFile('image')) {
            $validated['image'] = $media->storeUpload(
                $request->file('image'),
                'payment-banks',
                $paymentBank->image,
                field: 'image'
            );
        }

        $paymentBank->update($validated);

        return back()->with('success', 'Payment bank updated.');
    }

    public function destroy(PaymentBank $paymentBank, MediaStorageService $media): RedirectResponse
    {
        $media->delete($paymentBank->image);
        PaymentBank::destroy($paymentBank->getKey());

        return back()->with('success', 'Payment bank removed.');
    }

    /** @return array<string, mixed> */
    private function validateBank(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'account_name' => 'nullable|string|max:150',
            'account_number' => 'nullable|string|max:100',
            'branch' => 'nullable|string|max:150',
            'instructions' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);
    }
}
