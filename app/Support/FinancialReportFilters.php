<?php

namespace App\Support;

use Illuminate\Http\Request;

class FinancialReportFilters
{
    public function __construct(
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public string $basis = 'accrual',
        public ?string $status = null,
        public ?string $paymentStatus = null,
        public ?string $paymentMethod = null,
        public ?string $orderType = null,
        public bool $excludeCancelled = true,
    ) {}

    public static function fromRequest(Request $request): self
    {
        return new self(
            dateFrom: $request->query('date_from') ?: null,
            dateTo: $request->query('date_to') ?: null,
            basis: in_array($request->query('basis'), ['accrual', 'cash'], true)
                ? $request->query('basis')
                : 'accrual',
            status: $request->query('status') ?: null,
            paymentStatus: $request->query('payment_status') ?: null,
            paymentMethod: $request->query('payment_method') ?: null,
            orderType: $request->query('order_type') ?: null,
            excludeCancelled: ! $request->boolean('include_cancelled'),
        );
    }

    /** @return array<string, mixed> */
    public function toQueryArray(): array
    {
        return array_filter([
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
            'basis' => $this->basis,
            'status' => $this->status,
            'payment_status' => $this->paymentStatus,
            'payment_method' => $this->paymentMethod,
            'order_type' => $this->orderType,
            'include_cancelled' => $this->excludeCancelled ? null : '1',
        ], fn ($value) => $value !== null && $value !== '');
    }
}
