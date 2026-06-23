<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SslCommerzService
{
    public function __construct(private SiteSettingsService $settings) {}

    public function isConfigured(): bool
    {
        return $this->settings->sslCommerzConfigured();
    }

    public function initiatePayment(Order $order): string
    {
        $response = Http::asForm()
            ->post($this->settings->sslCommerzApiUrl(), $this->payload($order))
            ->throw()
            ->json();

        if (($response['status'] ?? '') !== 'SUCCESS' || empty($response['GatewayPageURL'])) {
            throw new RuntimeException($response['failedreason'] ?? 'Could not start SSLCommerz payment session.');
        }

        return (string) $response['GatewayPageURL'];
    }

    public function validateTransaction(string $validationId): array
    {
        $response = Http::get($this->settings->sslCommerzValidationUrl(), [
            'val_id' => $validationId,
            'store_id' => $this->settings->sslCommerzStoreId(),
            'store_passwd' => $this->settings->sslCommerzStorePassword(),
            'v' => 1,
            'format' => 'json',
        ])->throw()->json();

        if (($response['status'] ?? '') !== 'VALID' && ($response['status'] ?? '') !== 'VALIDATED') {
            throw new RuntimeException($response['error'] ?? 'SSLCommerz payment validation failed.');
        }

        return $response;
    }

    public function markOrderPaid(Order $order, array $gatewayData): void
    {
        $paidAmount = round((float) ($gatewayData['amount'] ?? $order->total), 2);

        $order->update([
            'payment_method' => 'sslcommerz',
            'reference_code' => $gatewayData['bank_tran_id'] ?? $gatewayData['tran_id'] ?? $order->reference_code,
            'payment_status' => 'paid',
            'amount_paid' => min($paidAmount, (float) $order->total),
            'notes' => trim(($order->notes ? $order->notes.' ' : '').'SSLCommerz: '.($gatewayData['card_type'] ?? 'Online payment')),
        ]);
    }

    /** @return array<string, mixed> */
    private function payload(Order $order): array
    {
        $items = $order->items()->get();
        $productNames = $items->pluck('product_name')->filter()->take(3)->implode(', ');

        return [
            'store_id' => $this->settings->sslCommerzStoreId(),
            'store_passwd' => $this->settings->sslCommerzStorePassword(),
            'total_amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => config('currency.code', 'BDT'),
            'tran_id' => $order->number,
            'success_url' => route('sslcommerz.success'),
            'fail_url' => route('sslcommerz.fail'),
            'cancel_url' => route('sslcommerz.cancel'),
            'ipn_url' => route('sslcommerz.ipn'),
            'cus_name' => $order->customer_name,
            'cus_email' => $order->customer_email,
            'cus_phone' => $order->customer_phone ?: '01700000000',
            'cus_add1' => $order->address ?: 'N/A',
            'cus_city' => $order->city ?: 'N/A',
            'cus_postcode' => $order->zip ?: '0000',
            'cus_country' => 'Bangladesh',
            'shipping_method' => 'NO',
            'product_name' => $productNames ?: 'Store order',
            'product_category' => 'Ecommerce',
            'product_profile' => 'general',
            'value_a' => (string) $order->id,
        ];
    }
}
