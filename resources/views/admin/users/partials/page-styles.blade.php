@include('admin.settings.partials.page-styles')
<style>
    .users-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        font-size: 0.82rem;
        font-weight: 700;
        color: #fff;
        background: linear-gradient(135deg, #0e7490, #0891b2);
    }

    .users-avatar img {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
    }

    .users-cell {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        min-width: 0;
    }

    .users-cell-name {
        font-weight: 700;
        color: #334155;
    }

    .users-role-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .users-role-badge--admin { color: #1d4ed8; background: #eff6ff; }
    .users-role-badge--other { color: #64748b; background: #f1f5f9; }
    .users-role-badge--system { color: #6d28d9; background: #f5f3ff; }

    .users-filter-grid {
        display: grid;
        grid-template-columns: 1fr auto auto;
        gap: 0.65rem;
        align-items: end;
    }

    @media (max-width: 767.98px) {
        .users-filter-grid {
            grid-template-columns: 1fr;
        }
    }

    .role-feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 0.55rem;
    }

    .role-feature-chip {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        padding: 0.55rem 0.75rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.65rem;
        background: #fff;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .role-feature-chip:hover {
        border-color: #a5f3fc;
        background: #f0fdfa;
    }

    .role-feature-chip input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .role-feature-chip--checked {
        border-color: #0891b2;
        background: #ecfeff;
        box-shadow: 0 0 0 2px rgba(8, 145, 178, 0.12);
    }

    .role-feature-chip i {
        width: 1.1rem;
        color: #0891b2;
        text-align: center;
        flex-shrink: 0;
    }

    .role-feature-chip span {
        font-size: 0.82rem;
        font-weight: 600;
        color: #334155;
    }

    .role-bulk-actions {
        display: flex;
        gap: 0.45rem;
        flex-wrap: wrap;
    }
</style>
