<style>
    .settings-page {
        --settings-ink: #0f172a;
        --settings-muted: #64748b;
        --settings-border: #e2e8f0;
    }

    .settings-hero {
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

    .settings-eyebrow {
        display: block;
        margin-bottom: 0.2rem;
        color: #a5f3fc;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.09em;
        text-transform: uppercase;
    }

    .settings-hero h2 { margin: 0; font-size: 1.55rem; font-weight: 700; }
    .settings-hero p { margin: 0.4rem 0 0; color: rgba(255, 255, 255, 0.82); }

    .settings-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.65rem;
    }

    .settings-hero-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.65rem;
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.14);
        color: #ecfeff;
        font-size: 0.72rem;
        font-weight: 600;
    }

    .settings-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
        flex-shrink: 0;
    }

    .settings-hero-actions .btn {
        font-weight: 700;
        border: 0;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.18);
    }

    .settings-stat {
        display: flex;
        align-items: center;
        gap: 0.9rem;
        height: 100%;
        padding: 1rem 1.1rem;
        border: 1px solid var(--settings-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
    }

    .settings-stat-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .settings-stat--teal .settings-stat-icon { background: #ecfeff; color: #0891b2; }
    .settings-stat--green .settings-stat-icon { background: #ecfdf5; color: #059669; }
    .settings-stat--blue .settings-stat-icon { background: #eff6ff; color: #2563eb; }
    .settings-stat--purple .settings-stat-icon { background: #f5f3ff; color: #7c3aed; }
    .settings-stat--amber .settings-stat-icon { background: #fffbeb; color: #d97706; }
    .settings-stat--rose .settings-stat-icon { background: #fff1f2; color: #e11d48; }
    .settings-stat--rose .settings-stat-value { color: #e11d48; }

    .settings-stat-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--settings-ink);
        line-height: 1.1;
    }

    .settings-stat-label {
        margin-top: 0.15rem;
        color: var(--settings-muted);
        font-size: 0.82rem;
        font-weight: 500;
    }

    .settings-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-bottom: 1.25rem;
    }

    .settings-nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.85rem;
        border: 1px solid var(--settings-border);
        border-radius: 999px;
        background: #fff;
        color: #475569;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.15s ease;
    }

    .settings-nav-link:hover {
        color: #0891b2;
        border-color: #a5f3fc;
        background: #f0fdfa;
        text-decoration: none;
    }

    .settings-nav-link.active {
        color: #fff;
        border-color: transparent;
        background: linear-gradient(135deg, #0e7490, #0891b2);
        box-shadow: 0 4px 14px rgba(8, 145, 178, 0.25);
    }

    .settings-card {
        border: 1px solid var(--settings-border);
        border-radius: 1rem;
        background: #fff;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .settings-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        flex-wrap: wrap;
    }

    .settings-card-head h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .settings-card-head p {
        margin: 0.15rem 0 0;
        font-size: 0.8rem;
        color: var(--settings-muted);
    }

    .settings-card-body { padding: 1.15rem; }

    .settings-card-footer {
        padding: 1rem 1.15rem;
        border-top: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .settings-card-footer.d-flex.gap-2 {
        gap: 0.5rem;
    }

    .settings-form-panel {
        margin-bottom: 1.25rem;
        padding: 1.1rem;
        border: 1px solid #eef2f7;
        border-radius: 0.9rem;
        background: #fafbfc;
    }

    .settings-form-panel:last-child { margin-bottom: 0; }

    .settings-form-panel-head {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }

    .settings-form-panel-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 0.7rem;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .settings-form-panel-icon--brand { background: #ecfeff; color: #0891b2; }
    .settings-form-panel-icon--seo { background: #eff6ff; color: #2563eb; }
    .settings-form-panel-icon--footer { background: #f5f3ff; color: #7c3aed; }
    .settings-form-panel-icon--social { background: #ecfdf5; color: #059669; }
    .settings-form-panel-icon--newsletter { background: #fffbeb; color: #d97706; }
    .settings-form-panel-icon--topbar { background: #fff7ed; color: #ea580c; }
    .settings-form-panel-icon--colors { background: #fdf2f8; color: #db2777; }
    .settings-form-panel-icon--gtm { background: #fffbeb; color: #ca8a04; }
    .settings-form-panel-icon--shipping { background: #ecfdf5; color: #059669; }

    .settings-form-panel-head h4 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .settings-form-panel-head p {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        color: var(--settings-muted);
    }

    .settings-field { margin-bottom: 1rem; }
    .settings-field:last-child { margin-bottom: 0; }

    .settings-field label {
        display: block;
        margin-bottom: 0.35rem;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .settings-field-hint {
        display: block;
        margin-top: 0.35rem;
        color: var(--settings-muted);
        font-size: 0.78rem;
    }

    .settings-input-wrap {
        position: relative;
    }

    .settings-input-wrap .form-control {
        padding-left: 2.35rem;
        border-color: #dbe3ee;
        border-radius: 0.65rem;
    }

    .settings-input-wrap .form-control:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
    }

    .settings-input-icon {
        position: absolute;
        left: 0.85rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 0.85rem;
        pointer-events: none;
    }

    .settings-textarea.form-control {
        border-color: #dbe3ee;
        border-radius: 0.65rem;
    }

    .settings-textarea.form-control:focus {
        border-color: #0891b2;
        box-shadow: 0 0 0 3px rgba(8, 145, 178, 0.12);
    }

    .settings-color-field {
        display: flex;
        align-items: center;
        gap: 0.55rem;
    }

    .settings-color-field input[type="color"] {
        width: 2.6rem;
        height: 2.6rem;
        padding: 0.15rem;
        border: 1px solid var(--settings-border);
        border-radius: 0.55rem;
        cursor: pointer;
        flex-shrink: 0;
    }

    .settings-color-field .form-control {
        border-color: #dbe3ee;
        border-radius: 0.65rem;
        font-family: ui-monospace, monospace;
        font-size: 0.85rem;
    }

    .settings-media-preview {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
        padding: 0.65rem 0.85rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.65rem;
        background: #fff;
    }

    .settings-media-preview img {
        border-radius: 0.45rem;
        object-fit: contain;
    }

    .settings-toggle-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 0.85rem;
        padding: 1rem 1.1rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.85rem;
        background: #fff;
    }

    .settings-toggle-card:last-child { margin-bottom: 0; }
    .settings-toggle-card--on { border-color: #6ee7b7; background: #f0fdf4; }
    .settings-toggle-card--sandbox { border-color: #fcd34d; background: #fffbeb; }

    .settings-toggle-copy h5 {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .settings-toggle-copy p {
        margin: 0.2rem 0 0;
        font-size: 0.8rem;
        color: var(--settings-muted);
    }

    .settings-toggle {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        margin: 0;
        cursor: pointer;
    }

    .settings-toggle-input { position: absolute; opacity: 0; pointer-events: none; }

    .settings-toggle-track {
        position: relative;
        width: 2.8rem;
        height: 1.5rem;
        border-radius: 999px;
        background: #cbd5e1;
        transition: background 0.2s ease;
    }

    .settings-toggle-thumb {
        position: absolute;
        top: 0.15rem;
        left: 0.15rem;
        width: 1.2rem;
        height: 1.2rem;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.2);
        transition: transform 0.2s ease;
    }

    .settings-toggle-input:checked + .settings-toggle-track { background: #10b981; }
    .settings-toggle-input:checked + .settings-toggle-track .settings-toggle-thumb { transform: translateX(1.3rem); }

    .settings-toggle-label {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--settings-muted);
    }

    .settings-preview-bar {
        margin-top: 1rem;
        padding: 0.65rem 1rem;
        border-radius: 0.65rem;
        font-size: 0.88rem;
    }

    .settings-theme-preview {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: 0.75rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.75rem;
    }

    .settings-side-card {
        margin-bottom: 1rem;
        border: 1px solid var(--settings-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        overflow: hidden;
    }

    .settings-side-card:last-child { margin-bottom: 0; }

    .settings-side-head {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 1rem 1.1rem;
        border-bottom: 1px solid #eef2f7;
    }

    .settings-side-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.2rem;
        height: 2.2rem;
        border-radius: 0.6rem;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .settings-side-icon--info { background: #eff6ff; color: #2563eb; }
    .settings-side-icon--check { background: #ecfdf5; color: #059669; }

    .settings-side-head h4 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .settings-side-head p {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: var(--settings-muted);
    }

    .settings-side-body { padding: 1rem 1.1rem; }

    .settings-side-text {
        margin: 0 0 0.75rem;
        font-size: 0.84rem;
        color: #475569;
        line-height: 1.55;
    }

    .settings-side-list {
        margin: 0;
        padding-left: 1.1rem;
        color: #475569;
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .settings-side-list li { margin-bottom: 0.5rem; }
    .settings-side-list li:last-child { margin-bottom: 0; }

    .settings-module-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 1rem;
    }

    .settings-module-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.25rem 1.15rem;
        border: 1px solid var(--settings-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        text-align: center;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .settings-module-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .settings-module-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.85rem;
        border-radius: 0.85rem;
        font-size: 1.25rem;
    }

    .settings-module-icon--sliders { background: #ecfeff; color: #0891b2; }
    .settings-module-icon--banners { background: #fffbeb; color: #d97706; }
    .settings-module-icon--features { background: #eff6ff; color: #2563eb; }
    .settings-module-icon--links { background: #f1f5f9; color: #64748b; }
    .settings-module-icon--newsletter { background: #ecfdf5; color: #059669; }

    .settings-module-card h5 {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .settings-module-count {
        margin: 0.35rem 0 1rem;
        color: var(--settings-muted);
        font-size: 0.82rem;
    }

    .settings-filter-bar {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        padding: 0.85rem 1.15rem;
        border-bottom: 1px solid #eef2f7;
        background: #f8fafc;
    }

    .settings-filter-bar .form-control {
        border-radius: 0.6rem;
        border-color: #dbe3ee;
        max-width: 280px;
    }

    .settings-table thead th {
        border-top: 0;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: var(--settings-muted);
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        vertical-align: middle;
        white-space: nowrap;
    }

    .settings-table tbody td {
        padding-top: 0.85rem;
        padding-bottom: 0.85rem;
        border-top-color: #eef2f7;
        vertical-align: middle;
    }

    .settings-status {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 999px;
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    .settings-status--live { color: #047857; background: #ecfdf5; }
    .settings-status--hidden { color: #64748b; background: #f1f5f9; }
    .settings-status--public { color: #1d4ed8; background: #eff6ff; }
    .settings-status--private { color: #b45309; background: #fffbeb; }

    .settings-actions {
        display: inline-flex;
        align-items: center;
        justify-content: flex-end;
        gap: 0.35rem;
        flex-wrap: wrap;
    }

    .settings-actions .btn {
        font-weight: 600;
    }

    .settings-empty {
        padding: 3rem 1rem !important;
        text-align: center;
        color: var(--settings-muted);
    }

    .settings-empty i {
        display: block;
        margin-bottom: 0.75rem;
        font-size: 2rem;
        opacity: 0.45;
    }

    .settings-note {
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        background: #f0fdfa;
        border: 1px solid #99f6e4;
        color: #0f766e;
        font-size: 0.84rem;
    }

    .settings-note a { color: #0e7490; font-weight: 600; }

    .settings-back-link {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 1rem;
        color: #0891b2;
        font-size: 0.84rem;
        font-weight: 600;
        text-decoration: none;
    }

    .settings-back-link:hover { color: #0e7490; text-decoration: none; }

    @media (max-width: 767.98px) {
        .settings-hero {
            align-items: flex-start;
            flex-direction: column;
            padding: 1.1rem;
        }

        .settings-hero h2 { font-size: 1.3rem; }
        .settings-hero-actions { width: 100%; }
        .settings-toggle-card { flex-direction: column; align-items: flex-start; }
    }
</style>
