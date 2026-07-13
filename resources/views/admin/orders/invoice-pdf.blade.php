<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            line-height: 1.5;
        }
        .invoice-page { padding: 24px; }
        .invoice-header {
            width: 100%;
            margin-bottom: 24px;
            padding-bottom: 24px;
            border-bottom: 2px solid #111827;
        }
        .invoice-header-table { width: 100%; border-collapse: collapse; }
        .invoice-header-table td { vertical-align: top; }
        .invoice-header-table td.meta-cell { text-align: right; width: 45%; }
        .brand-logo {
            max-height: 72px;
            max-width: 220px;
        }
        .brand-fallback {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #0891b2;
            color: #fff;
            text-align: center;
            line-height: 72px;
            font-size: 28px;
            font-weight: 700;
        }
        .brand-name {
            margin: 12px 0 0;
            font-size: 18px;
            font-weight: 700;
        }
        .brand-address {
            margin-top: 8px;
            color: #4b5563;
            font-size: 11px;
            max-width: 320px;
        }
        .invoice-title {
            margin: 0 0 12px;
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #0891b2;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .meta-table th,
        .meta-table td {
            padding: 4px 0 4px 12px;
            text-align: right;
            vertical-align: top;
        }
        .meta-table th {
            color: #6b7280;
            font-weight: 600;
            white-space: nowrap;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-processing { background: #dbeafe; color: #1d4ed8; }
        .status-shipped { background: #ede9fe; color: #6d28d9; }
        .status-completed { background: #d1fae5; color: #047857; }
        .status-cancelled { background: #fee2e2; color: #b91c1c; }
        .status-paid { background: #d1fae5; color: #047857; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-due { background: #fee2e2; color: #b91c1c; }
        .address-section {
            width: 100%;
            margin: 24px 0;
        }
        .address-section-table { width: 100%; border-collapse: collapse; }
        .address-section-table td { width: 50%; vertical-align: top; padding-right: 16px; }
        .address-box h3 {
            margin: 0 0 8px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
        }
        .address-box p { margin: 0; color: #111827; }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .items-table thead th {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #374151;
        }
        .items-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 8px 10px;
            vertical-align: top;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-style { font-weight: 600; }
        .item-meta { margin-top: 4px; font-size: 10px; color: #6b7280; }
        .totals-wrap { margin-top: 20px; text-align: right; }
        .totals-table {
            width: 300px;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals-table th,
        .totals-table td {
            padding: 6px 0;
            text-align: right;
        }
        .totals-table th {
            color: #6b7280;
            font-weight: 600;
            padding-right: 20px;
        }
        .totals-table .grand-total th,
        .totals-table .grand-total td {
            border-top: 2px solid #111827;
            padding-top: 10px;
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
        .amount-words {
            margin-top: 16px;
            padding: 12px 14px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            font-size: 11px;
        }
        .invoice-footer {
            margin-top: 28px;
            padding-top: 14px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="invoice-page">
        @php($pdfMode = true)
        @include('admin.orders.partials.invoice-content')
    </div>
</body>
</html>
