<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    public function index(Request $request): View
    {
        return view('pages.order-tracking.index', [
            'order' => null,
            'search' => [
                'order_number' => $request->old('order_number', $request->query('order', '')),
                'contact' => $request->old('contact', ''),
            ],
        ]);
    }

    public function lookup(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_number' => ['required', 'string', 'max:50'],
            'contact' => ['required', 'string', 'max:255'],
        ], [
            'order_number.required' => 'Please enter your order number.',
            'contact.required' => 'Please enter your email address or mobile number.',
        ]);

        $orderNumber = trim($validated['order_number']);
        $contact = trim($validated['contact']);

        $order = Order::query()
            ->where('number', $orderNumber)
            ->where(function ($query) use ($contact) {
                $query->whereRaw('LOWER(customer_email) = ?', [strtolower($contact)]);

                $digits = preg_replace('/\D/', '', $contact);
                if (strlen($digits) >= 7) {
                    $query->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '(', ''), ')', ''), '+', '') LIKE ?",
                        ['%'.$digits.'%']
                    );
                }
            })
            ->with('items')
            ->first();

        if (! $order) {
            return back()
                ->withInput()
                ->withErrors([
                    'contact' => 'No order found with these details. Please check your order number and email or mobile number.',
                ]);
        }

        return view('pages.order-tracking.index', [
            'order' => $order,
            'search' => $validated,
        ]);
    }
}
