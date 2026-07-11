<style>
    .account-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        margin-bottom: 1.25rem;
        padding: 0.7rem;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
    }

    .account-nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        min-height: 2.65rem;
        padding: 0.45rem 1rem 0.45rem 0.45rem;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #f8fafc;
        color: #475569;
        font-size: 0.82rem;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .account-nav-link:hover {
        color: #0e7490;
        border-color: #a5f3fc;
        background: #ecfeff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .account-nav-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.85rem;
        height: 1.85rem;
        border-radius: 999px;
        background: #ecfeff;
        color: #0891b2;
        font-size: 0.78rem;
        flex-shrink: 0;
        transition: all 0.15s ease;
    }

    .account-nav-link.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #0e7490 0%, #0891b2 55%, #06b6d4 100%);
        box-shadow: 0 6px 18px rgba(8, 145, 178, 0.28);
    }

    .account-nav-link.active .account-nav-icon {
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
    }

    @media (max-width: 767.98px) {
        .account-nav {
            gap: 0.45rem;
            padding: 0.6rem;
        }

        .account-nav-link {
            flex: 1 1 calc(50% - 0.25rem);
            justify-content: center;
            padding: 0.5rem 0.75rem;
        }

        .account-nav-link span:last-child {
            font-size: 0.78rem;
        }
    }
</style>
