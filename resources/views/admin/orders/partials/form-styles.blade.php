<style>
    .order-form-page,
    .order-create-form {
        --order-ink: #0f172a;
        --order-muted: #64748b;
        --order-border: #e2e8f0;
        --order-soft: #f8fafc;
        --order-accent: #0891b2;
    }

    .order-form-hero,
    .order-create-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        background:
            radial-gradient(circle at 88% 15%, rgba(103, 232, 249, 0.25), transparent 35%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 55%, #0891b2 100%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .order-form-eyebrow,
    .order-create-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .order-form-hero h2,
    .order-create-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .order-form-hero p,
    .order-create-hero p {
        margin: 0.4rem 0 0;
        max-width: 38rem;
        color: rgba(255, 255, 255, 0.82);
    }

    .order-form-hero-actions,
    .order-create-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .order-form-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.75rem;
    }

    .order-form-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.28rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        border: 1px solid rgba(255, 255, 255, 0.22);
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
    }

    .order-form-alert {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.25rem;
        padding: 0.85rem 1rem;
        border: 1px solid #fecaca;
        border-radius: 0.85rem;
        background: #fef2f2;
        color: #991b1b;
    }

    .order-form-alert > i { font-size: 1.2rem; }
    .order-form-alert span { display: block; color: #b91c1c; font-size: 0.83rem; }

    .order-form-card {
        border: 1px solid var(--order-border);
        border-radius: 1rem;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.045);
        overflow: hidden;
    }

    .order-form-card-header {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        min-height: 4.4rem;
        padding: 0.85rem 1rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .order-form-card-header .card-title {
        float: none;
        margin: 0;
        color: var(--order-ink);
        font-size: 1rem;
        font-weight: 700;
    }

    .order-form-card-header p {
        margin: 0.15rem 0 0;
        color: var(--order-muted);
        font-size: 0.78rem;
        line-height: 1.25;
    }

    .order-section-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        flex: 0 0 auto;
    }

    .order-section-icon--cyan { color: #0891b2; background: #ecfeff; }
    .order-section-icon--violet { color: #7c3aed; background: #f5f3ff; }
    .order-section-icon--emerald { color: #059669; background: #ecfdf5; }
    .order-section-icon--amber { color: #d97706; background: #fffbeb; }
    .order-section-icon--blue { color: #2563eb; background: #eff6ff; }

    .order-section-number {
        position: absolute;
        top: 0.55rem;
        right: 0.75rem;
        color: #e2e8f0;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.08em;
    }

    .order-form-card-header .btn + .order-section-number { display: none; }

    .order-form-page label,
    .order-create-form label {
        margin-bottom: 0.35rem;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .order-form-page .form-control,
    .order-create-form .form-control {
        min-height: 2.55rem;
        border-color: #dbe3ed;
        border-radius: 0.55rem;
        color: var(--order-ink);
        box-shadow: none;
    }

    .order-form-page .form-control-sm,
    .order-create-form .form-control-sm {
        min-height: 2.15rem;
        border-radius: 0.45rem;
    }

    .order-form-page .form-control:focus,
    .order-create-form .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .order-form-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: var(--order-soft);
        color: var(--order-muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.045em;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .order-form-table td {
        padding: 0.55rem 0.35rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .order-form-table th:first-child,
    .order-form-table td:first-child { padding-left: 0.75rem; }

    .order-form-table th:last-child,
    .order-form-table td:last-child { padding-right: 0.75rem; }

    .order-items-scroll { min-height: 7rem; }
    .order-items-scroll table { min-width: 980px; }

    .order-empty-state {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 1.15rem;
        color: var(--order-muted);
    }

    .order-empty-state > span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.7rem;
        color: #059669;
        background: #ecfdf5;
    }

    .order-empty-state strong {
        display: block;
        color: #334155;
        font-size: 0.87rem;
    }

    .order-empty-state p {
        margin: 0.1rem 0 0;
        font-size: 0.78rem;
    }

    .order-total-card { border-top: 4px solid #f59e0b; }

    .order-total-preview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin: 0.25rem 0 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        color: #fff;
        background: linear-gradient(135deg, #0f3d47, #0891b2);
    }

    .order-total-preview span { font-size: 0.82rem; opacity: 0.85; }
    .order-total-preview strong { font-size: 1.15rem; }

    .order-form-sidebar .card-footer {
        border-top-color: #eef2f7;
        background: #f8fafc;
    }

    .order-row-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin: 0;
        cursor: pointer;
    }

    .order-row-remove input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .order-row-remove span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.85rem;
        height: 1.85rem;
        border: 1px solid #fecaca;
        border-radius: 0.45rem;
        background: #fff;
        color: #dc2626;
        font-size: 0.72rem;
        transition: all 0.15s ease;
    }

    .order-row-remove:hover span {
        background: #fef2f2;
        border-color: #f87171;
    }

    .order-row-remove input:checked + span {
        background: #dc2626;
        border-color: #dc2626;
        color: #fff;
    }

    .order-payment-date {
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .order-screenshot-preview {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        background: #f8fafc;
    }

    .order-screenshot-preview img {
        max-height: 4.5rem;
        border-radius: 0.45rem;
        border: 1px solid #e2e8f0;
    }

    .order-item-image-cell {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        min-width: 5.5rem;
    }

    .order-item-image-preview {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3.25rem;
        height: 3.25rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        background: #f8fafc;
        overflow: hidden;
        text-decoration: none;
    }

    .order-item-image-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .order-item-image-preview--empty {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    .order-item-upload {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
        width: 100%;
        min-height: 2rem;
        margin: 0;
        padding: 0.3rem 0.45rem;
        border: 1px dashed #94a3b8;
        border-radius: 0.45rem;
        background: #f8fafc;
        color: #64748b !important;
        cursor: pointer;
        font-size: 0.68rem !important;
        white-space: nowrap;
    }

    .order-item-upload:hover {
        border-color: #0891b2;
        background: #ecfeff;
        color: #0e7490 !important;
    }

    .order-item-upload input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .order-item-upload.has-file {
        border-style: solid;
        border-color: #10b981;
        background: #ecfdf5;
        color: #047857 !important;
    }

    @media (min-width: 1200px) {
        .order-form-sidebar,
        .order-create-sidebar {
            position: sticky;
            top: 4.25rem;
        }
    }

    @media (max-width: 767.98px) {
        .order-form-hero,
        .order-create-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .order-form-hero h2,
        .order-create-hero h2 { font-size: 1.3rem; }

        .order-form-hero-actions,
        .order-create-hero-actions {
            width: 100%;
            flex-wrap: wrap;
        }

        .order-form-hero-actions .btn,
        .order-create-hero-actions .btn { flex: 1 1 calc(50% - 0.3rem); }

        .order-form-hero-meta {
            gap: 0.35rem;
        }

        .order-form-card-header {
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .order-form-card-header .btn {
            width: 100%;
            margin-left: 0 !important;
        }

        .order-items-scroll,
        .order-payments-scroll {
            overflow: visible;
            min-height: 0;
        }

        .order-items-scroll table,
        .order-payments-scroll table {
            min-width: 0 !important;
        }

        .order-mobile-stack-table thead {
            display: none;
        }

        .order-mobile-stack-table tbody tr {
            display: block;
            margin: 0.75rem;
            padding: 0.9rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.95rem;
            background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.05);
        }

        .order-mobile-stack-table tbody td {
            display: block;
            padding: 0.45rem 0;
            border: none !important;
        }

        .order-mobile-stack-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 0.3rem;
            color: #64748b;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .order-mobile-stack-table tbody td[data-label=""]::before,
        .order-mobile-stack-table tbody td:not([data-label])::before {
            display: none;
        }

        .order-mobile-stack-table tbody td.order-mobile-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 0.65rem;
            margin-top: 0.35rem;
            border-top: 1px dashed #e2e8f0 !important;
        }

        .order-mobile-stack-table tbody td.order-mobile-lead::before {
            display: none;
        }

        .order-mobile-stack-table tbody td.order-mobile-lead .form-control {
            font-weight: 700;
        }

        .order-mobile-stack-table .form-control-sm {
            min-height: 2.45rem;
            font-size: 0.88rem;
        }

        .order-mobile-stack-table .order-item-image-cell {
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            width: 100%;
            gap: 0.65rem;
        }

        .order-mobile-stack-table .order-item-image-preview {
            width: 4.25rem;
            height: 4.25rem;
            flex-shrink: 0;
        }

        .order-mobile-stack-table .order-item-upload {
            flex: 1;
            min-height: 2.45rem;
        }

        .order-mobile-stack-table .order-payment-date {
            display: inline-flex;
            align-items: center;
            min-height: 2.45rem;
            padding: 0.45rem 0.65rem;
            border-radius: 0.55rem;
            background: #f1f5f9;
            font-size: 0.8rem;
        }

        .order-form-sidebar {
            position: static;
        }
    }
</style>
