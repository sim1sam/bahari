<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Invoice')</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            color: #1a1a1a;
            background: #f3f4f6;
            line-height: 1.5;
        }
        .invoice-page {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            padding: 40px;
            box-shadow: 0 2px 16px rgba(0, 0, 0, 0.08);
        }
        .invoice-toolbar {
            max-width: 900px;
            margin: 16px auto 0;
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            padding: 0 8px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            border: 1px solid #d1d5db;
            background: #fff;
            color: #111827;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-primary {
            background: #0891b2;
            border-color: #0891b2;
            color: #fff;
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            gap: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #111827;
        }
        .brand-logo {
            max-height: 72px;
            max-width: 220px;
            object-fit: contain;
            display: block;
        }
        .brand-fallback {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: #0891b2;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 700;
        }
        .brand-name {
            margin: 12px 0 0;
            font-size: 20px;
            font-weight: 700;
        }
        .brand-address {
            margin-top: 8px;
            color: #4b5563;
            font-size: 13px;
            max-width: 320px;
        }
        .invoice-meta {
            text-align: right;
            min-width: 240px;
        }
        .invoice-title {
            margin: 0 0 12px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: #0891b2;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
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
            font-size: 12px;
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
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin: 28px 0;
        }
        .address-box h3 {
            margin: 0 0 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
        }
        .address-box p {
            margin: 0;
            color: #111827;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .items-table thead th {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            text-align: left;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #374151;
        }
        .items-table tbody td {
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            vertical-align: top;
        }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }
        .item-style {
            font-weight: 600;
        }
        .item-meta {
            margin-top: 4px;
            font-size: 12px;
            color: #6b7280;
        }
        .totals-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 24px;
        }
        .totals-table {
            width: 320px;
            border-collapse: collapse;
        }
        .totals-table th,
        .totals-table td {
            padding: 8px 0;
            text-align: right;
        }
        .totals-table th {
            color: #6b7280;
            font-weight: 600;
            padding-right: 24px;
        }
        .totals-table .grand-total th,
        .totals-table .grand-total td {
            border-top: 2px solid #111827;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
        }
        .amount-words {
            margin-top: 20px;
            padding: 14px 16px;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
        }
        .amount-words strong {
            color: #111827;
        }
        .invoice-footer {
            margin-top: 32px;
            padding-top: 16px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        @media print {
            body { background: #fff; }
            .invoice-toolbar { display: none !important; }
            .invoice-page {
                margin: 0;
                box-shadow: none;
                padding: 24px;
                max-width: none;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    @yield('toolbar')

    <div class="invoice-page">
        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>
