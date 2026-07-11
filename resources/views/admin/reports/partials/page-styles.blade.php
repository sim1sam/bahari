<style>
    .reports-page {
        --reports-ink: #0f172a;
        --reports-muted: #64748b;
        --reports-border: #e2e8f0;
    }

    .reports-hero {
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

    .reports-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .reports-hero h2 {
        margin: 0;
        font-size: 1.55rem;
        font-weight: 700;
    }

    .reports-hero p {
        margin: 0.4rem 0 0;
        color: rgba(255, 255, 255, 0.82);
    }

    .reports-hero-meta {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .reports-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #ecfeff;
        font-size: 0.74rem;
        font-weight: 600;
    }

    .reports-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .reports-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .reports-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 1rem;
        padding: 0.65rem;
        border: 1px solid var(--reports-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .reports-nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.45rem 0.85rem;
        border: 2px solid transparent;
        border-radius: 999px;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
        background: #f8fafc;
        transition: all 0.15s ease;
    }

    .reports-nav-link:hover {
        color: #0891b2;
        background: #ecfeff;
        text-decoration: none;
    }

    .reports-nav-link.active {
        border-color: #0891b2;
        background: linear-gradient(135deg, #0891b2, #0e7490);
        color: #fff;
        box-shadow: 0 4px 14px rgba(8, 145, 178, 0.28);
    }

    .reports-filters-card {
        margin-bottom: 1.25rem;
        border: 1px solid var(--reports-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .reports-filters-head {
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .reports-filters-head h3 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--reports-ink);
    }

    .reports-filters-body {
        padding: 1rem 1.15rem 0.85rem;
    }

    .reports-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 0.75rem;
    }

    .reports-filter-field label {
        display: block;
        margin-bottom: 0.3rem;
        color: var(--reports-muted);
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .reports-filter-field .form-control {
        min-height: 2.45rem;
        border-color: #dbe3ed;
        border-radius: 0.6rem;
        background: #fff;
        box-shadow: none;
    }

    .reports-filter-field .form-control:focus {
        border-color: #22d3ee;
        box-shadow: 0 0 0 3px rgba(34, 211, 238, 0.12);
    }

    .reports-filter-check {
        display: flex;
        align-items: center;
        min-height: 2.45rem;
        padding-top: 1.35rem;
    }

    .reports-filter-check label {
        margin: 0;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 600;
        text-transform: none;
        letter-spacing: 0;
    }

    .reports-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.85rem;
        padding-top: 0.85rem;
        border-top: 1px solid #eef2f7;
    }

    .reports-filter-actions .btn {
        font-weight: 700;
        border: 0;
    }

    .reports-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--reports-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .reports-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .reports-stat--revenue .reports-stat-icon { background: #ecfeff; color: #0891b2; }
    .reports-stat--profit .reports-stat-icon { background: #ecfdf5; color: #059669; }
    .reports-stat--loss .reports-stat-icon { background: #fef2f2; color: #dc2626; }
    .reports-stat--cash .reports-stat-icon { background: #fffbeb; color: #d97706; }
    .reports-stat--orders .reports-stat-icon { background: #eff6ff; color: #2563eb; }
    .reports-stat--receivable .reports-stat-icon { background: #f5f3ff; color: #7c3aed; }
    .reports-stat--inventory .reports-stat-icon { background: #f0fdf4; color: #16a34a; }
    .reports-stat--expense .reports-stat-icon { background: #fff7ed; color: #ea580c; }

    .reports-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--reports-ink);
        line-height: 1.1;
    }

    .reports-stat-label {
        margin-top: 0.15rem;
        color: var(--reports-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .reports-metric-card {
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--reports-border);
        border-radius: 0.95rem;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .reports-metric-label {
        color: var(--reports-muted);
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .reports-metric-value {
        margin-top: 0.35rem;
        color: var(--reports-ink);
        font-size: 1.35rem;
        font-weight: 700;
        line-height: 1.1;
    }

    .reports-card {
        border: 1px solid var(--reports-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .reports-card-head {
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #fff;
    }

    .reports-card-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--reports-ink);
    }

    .reports-card-head p {
        margin: 0.25rem 0 0;
        font-size: 0.8rem;
        color: var(--reports-muted);
    }

    .reports-quick-links {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 0.75rem;
        padding: 1rem 1.15rem 1.15rem;
    }

    .reports-quick-link {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border: 1px solid var(--reports-border);
        border-radius: 0.9rem;
        background: #fff;
        color: inherit;
        text-decoration: none;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }

    .reports-quick-link:hover {
        transform: translateY(-2px);
        text-decoration: none;
        color: inherit;
        box-shadow: 0 10px 24px rgba(8, 145, 178, 0.1);
    }

    .reports-quick-link-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.7rem;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .reports-quick-link--pl .reports-quick-link-icon { background: #ecfeff; color: #0891b2; }
    .reports-quick-link--bs .reports-quick-link-icon { background: #eff6ff; color: #2563eb; }
    .reports-quick-link--ledger .reports-quick-link-icon { background: #f5f3ff; color: #7c3aed; }
    .reports-quick-link--expense .reports-quick-link-icon { background: #fff7ed; color: #ea580c; }

    .reports-quick-link strong {
        display: block;
        color: #334155;
        font-size: 0.88rem;
    }

    .reports-quick-link span {
        display: block;
        margin-top: 0.1rem;
        color: var(--reports-muted);
        font-size: 0.72rem;
    }

    .reports-quick-link--pl:hover { border-color: #a5f3fc; }
    .reports-quick-link--bs:hover { border-color: #93c5fd; }
    .reports-quick-link--ledger:hover { border-color: #c4b5fd; }
    .reports-quick-link--expense:hover { border-color: #fdba74; }

    @media (max-width: 767.98px) {
        .reports-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .reports-hero h2 {
            font-size: 1.3rem;
        }

        .reports-hero-actions {
            width: 100%;
        }

        .reports-hero-actions .btn {
            flex: 1;
        }
    }
</style>
