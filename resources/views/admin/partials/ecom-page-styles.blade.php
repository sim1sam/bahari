<style>
    .ecom-page {
        --ecom-ink: #0f172a;
        --ecom-muted: #64748b;
        --ecom-border: #e2e8f0;
    }

    .ecom-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
        padding: 1.35rem 1.5rem;
        border-radius: 1.1rem;
        color: #fff;
        background:
            radial-gradient(circle at 88% 15%, rgba(103, 232, 249, 0.25), transparent 35%),
            linear-gradient(135deg, #0f3d47 0%, #0e7490 55%, #0891b2 100%);
        box-shadow: 0 14px 32px rgba(8, 145, 178, 0.18);
    }

    .ecom-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .ecom-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .ecom-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .ecom-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .ecom-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--ecom-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .ecom-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .ecom-stat--total .ecom-stat-icon { background: #ecfeff; color: #0891b2; }
    .ecom-stat--live .ecom-stat-icon { background: #ecfdf5; color: #059669; }
    .ecom-stat--manual .ecom-stat-icon { background: #eff6ff; color: #2563eb; }
    .ecom-stat--api .ecom-stat-icon { background: #f5f3ff; color: #7c3aed; }

    .ecom-stat-value {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--ecom-ink);
        line-height: 1.1;
    }

    .ecom-stat-label {
        margin-top: 0.15rem;
        color: var(--ecom-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .ecom-card {
        border: 1px solid var(--ecom-border);
        border-radius: 1rem;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .ecom-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
        flex-wrap: wrap;
    }

    .ecom-card-head h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--ecom-ink);
    }

    .ecom-card-head p {
        font-size: 0.8rem;
    }

    .ecom-bulk-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .ecom-select-all {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        color: #475569;
        font-size: 0.84rem;
        font-weight: 600;
        cursor: pointer;
    }

    .ecom-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--ecom-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .ecom-table tbody td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .ecom-product-cell,
    .ecom-category-cell,
    .ecom-brand-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        min-width: 0;
    }

    .ecom-product-thumb {
        width: 2.8rem;
        height: 3.2rem;
        border-radius: 0.55rem;
        object-fit: cover;
        border: 1px solid var(--ecom-border);
        flex-shrink: 0;
    }

    .ecom-product-thumb--empty,
    .ecom-category-icon,
    .ecom-brand-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 0.55rem;
        background: #f1f5f9;
        color: #94a3b8;
    }

    .ecom-product-thumb--empty {
        width: 2.8rem;
        height: 3.2rem;
        font-size: 0.9rem;
    }

    .ecom-category-icon,
    .ecom-brand-icon {
        width: 2.4rem;
        height: 2.4rem;
        font-size: 0.9rem;
        font-weight: 700;
    }

    .ecom-brand-icon {
        background: linear-gradient(135deg, #0e7490, #0891b2);
        color: #fff;
    }

    .ecom-product-name,
    .ecom-category-name,
    .ecom-brand-name {
        font-weight: 700;
        color: #334155;
    }

    .ecom-product-slug,
    .ecom-category-slug,
    .ecom-brand-slug {
        display: block;
        margin-top: 0.1rem;
        color: #94a3b8;
        font-size: 0.72rem;
        background: transparent;
    }

    .ecom-pill {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    .ecom-pill--manual { color: #1d4ed8; background: #eff6ff; }
    .ecom-pill--api { color: #7c3aed; background: #f5f3ff; }

    .ecom-price {
        font-weight: 700;
        color: var(--ecom-ink);
        white-space: nowrap;
    }

    .ecom-stock {
        font-weight: 600;
        color: #334155;
    }

    .ecom-stock--empty {
        color: #b91c1c;
    }

    .ecom-count-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .ecom-count-badge--api {
        background: #f5f3ff;
        color: #6d28d9;
    }

    .ecom-count-badge i {
        font-size: 0.7rem;
        opacity: 0.7;
    }

    .ecom-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .ecom-status--live { color: #047857; background: #ecfdf5; }
    .ecom-status--hidden { color: #64748b; background: #f1f5f9; }

    .ecom-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.25rem;
        flex-wrap: wrap;
    }

    .ecom-actions .btn {
        width: 1.85rem;
        height: 1.85rem;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .ecom-empty {
        padding: 3rem 1rem !important;
        text-align: center;
        color: var(--ecom-muted);
    }

    .ecom-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .ecom-empty strong {
        display: block;
        color: #334155;
        font-size: 1rem;
    }

    .ecom-empty p {
        margin: 0.35rem 0 0;
        font-size: 0.86rem;
    }

    .ecom-card-footer {
        padding: 0.85rem 1rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .ecom-app-list {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        padding: 0.85rem;
        background: #f8fafc;
    }

    .ecom-app-card {
        padding: 0.9rem;
        border: 1px solid var(--ecom-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
    }

    .ecom-app-card-top {
        display: grid;
        grid-template-columns: auto auto 1fr auto;
        gap: 0.65rem;
        align-items: start;
    }

    .ecom-app-select {
        margin: 0;
        cursor: pointer;
    }

    .ecom-app-select input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .ecom-app-select-mark {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.65rem;
        height: 1.65rem;
        border: 2px solid #cbd5e1;
        border-radius: 0.45rem;
        background: #fff;
        color: transparent;
        font-size: 0.68rem;
        transition: all 0.15s ease;
    }

    .ecom-app-select input:checked + .ecom-app-select-mark {
        background: #0891b2;
        border-color: #0891b2;
        color: #fff;
    }

    .ecom-app-card-thumb {
        width: 3.5rem;
        height: 4rem;
        border-radius: 0.6rem;
        object-fit: cover;
        border: 1px solid var(--ecom-border);
        flex-shrink: 0;
    }

    .ecom-app-card-thumb--empty {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 1rem;
    }

    .ecom-app-card-info {
        min-width: 0;
    }

    .ecom-app-card-name {
        font-weight: 800;
        color: #1e293b;
        font-size: 0.92rem;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .ecom-app-card-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.3rem;
        margin-top: 0.4rem;
    }

    .ecom-app-card-price {
        text-align: right;
        flex-shrink: 0;
    }

    .ecom-app-card-price strong {
        display: block;
        color: #0891b2;
        font-size: 0.95rem;
        white-space: nowrap;
    }

    .ecom-app-card-price small {
        color: #94a3b8;
        font-size: 0.72rem;
    }

    .ecom-app-card-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem 0.75rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px solid #eef2f7;
        color: #64748b;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .ecom-app-card-meta span {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .ecom-app-card-meta i {
        color: #94a3b8;
        font-size: 0.68rem;
    }

    .ecom-app-card-foot {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-top: 0.75rem;
        padding-top: 0.75rem;
        border-top: 1px dashed #e2e8f0;
    }

    .ecom-app-card-foot .btn {
        flex: 1;
        font-weight: 700;
    }

    .ecom-app-card-delete {
        margin: 0;
        flex: 0 0 auto;
    }

    .ecom-app-card-delete .btn {
        width: 2.15rem;
        padding-left: 0;
        padding-right: 0;
    }

    .ecom-app-empty {
        border: 1px dashed var(--ecom-border);
        border-radius: 1rem;
        background: #fff;
    }

    @media (max-width: 767.98px) {
        .ecom-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .ecom-hero h2 {
            font-size: 1.3rem;
        }

        .ecom-hero-actions {
            width: 100%;
        }

        .ecom-hero-actions .btn,
        .ecom-hero-actions form {
            flex: 1;
        }

        .ecom-hero-actions form .btn {
            width: 100%;
        }

        .ecom-card-head {
            flex-direction: column;
            align-items: stretch;
        }

        .ecom-bulk-actions {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
