@include('admin.settings.partials.page-styles')
<style>
    .terminal-tool-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }

    .terminal-tool-card {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.15rem;
        border: 1px solid var(--settings-border);
        border-radius: 0.95rem;
        background: #fff;
        box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .terminal-tool-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    }

    .terminal-tool-card--ready { border-color: #6ee7b7; }
    .terminal-tool-card--action { border-color: #fcd34d; }

    .terminal-tool-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
        margin-bottom: 0.75rem;
    }

    .terminal-tool-icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.75rem;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .terminal-tool-icon--migration { background: #eff6ff; color: #2563eb; }
    .terminal-tool-icon--build { background: #f5f3ff; color: #7c3aed; }
    .terminal-tool-icon--storage { background: #ecfdf5; color: #059669; }

    .terminal-tool-title {
        margin: 0;
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--settings-ink);
    }

    .terminal-tool-desc {
        margin: 0 0 1rem;
        flex-grow: 1;
        color: var(--settings-muted);
        font-size: 0.82rem;
        line-height: 1.5;
    }

    .terminal-status {
        display: inline-block;
        padding: 0.22rem 0.55rem;
        border-radius: 999px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .terminal-status--ready { color: #047857; background: #ecfdf5; }
    .terminal-status--action { color: #b45309; background: #fffbeb; }
    .terminal-status--error { color: #b91c1c; background: #fef2f2; }

    .terminal-info-grid {
        display: grid;
        gap: 0.55rem;
    }

    .terminal-info-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.65rem 0.85rem;
        border: 1px solid #eef2f7;
        border-radius: 0.65rem;
        background: #fafbfc;
        font-size: 0.84rem;
    }

    .terminal-info-row strong {
        color: #475569;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        flex-shrink: 0;
        min-width: 7rem;
    }

    .terminal-info-row span,
    .terminal-info-row code {
        color: #334155;
        text-align: right;
        word-break: break-all;
    }

    .terminal-alert {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.84rem;
        line-height: 1.5;
    }

    .terminal-alert i { margin-top: 0.15rem; flex-shrink: 0; }

    .terminal-alert--warning {
        border: 1px solid #fcd34d;
        background: #fffbeb;
        color: #92400e;
    }

    .terminal-alert--danger {
        border: 1px solid #fca5a5;
        background: #fef2f2;
        color: #991b1b;
    }

    .terminal-alert--success {
        border: 1px solid #6ee7b7;
        background: #ecfdf5;
        color: #065f46;
    }

    .terminal-console {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid #1e293b;
        border-radius: 0.75rem;
        background: #0f172a;
        color: #e2e8f0;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-size: 0.78rem;
        line-height: 1.55;
        max-height: 320px;
        overflow: auto;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .terminal-console-label {
        display: block;
        margin-bottom: 0.45rem;
        color: #334155;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
    }

    .terminal-pending-list {
        margin: 0.5rem 0 0;
        padding-left: 1.1rem;
    }

    .terminal-pending-list li {
        margin-bottom: 0.25rem;
        font-size: 0.82rem;
    }

    .terminal-upload-zone {
        padding: 1rem;
        border: 1px dashed #cbd5e1;
        border-radius: 0.75rem;
        background: #fafbfc;
    }
</style>
