<style>
    :root {
        --primary-color: #2563eb;
        --primary-dark: #1e40af;
        --secondary-color: #64748b;
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        --light-bg: #f8fafc;
        --border-color: #e2e8f0;
        --text-primary: #1e293b;
        --text-secondary: #64748b;
    }

    body {
        background: var(--light-bg);
        color: var(--text-primary);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

    /* Cards */
    .modern-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 24px;
    }

    .card-header-modern {
        background: white;
        border-bottom: 1px solid var(--border-color);
        padding: 20px 24px;
    }

    .content-card {
        background: transparent;
        padding: 20px 24px;
    }

    /* Typography */
    .page-title {
        color: var(--text-primary);
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        letter-spacing: -0.025em;
    }

    .page-subtitle {
        color: var(--text-secondary);
        font-size: 13px;
        margin-top: 4px;
        font-weight: 400;
    }

    .section-header {
        margin-bottom: 16px;
    }

    .section-title {
        color: var(--text-primary);
        font-size: 16px;
        font-weight: 600;
        margin: 0;
    }

    .section-subtitle {
        color: var(--text-secondary);
        font-size: 13px;
        margin-top: 2px;
    }

    /* Buttons */
    .btn-modern-add {
        background: var(--primary-color);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 500;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-modern-add:hover {
        background: var(--primary-dark);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.2);
    }

    .btn-action-edit {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: var(--warning-color);
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.15s ease;
        margin: 0 3px;
        font-size: 13px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-action-edit:hover {
        background: var(--warning-color);
        border-color: var(--warning-color);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(245, 158, 11, 0.2);
    }

    .btn-action-delete {
        background: #fef2f2;
        border: 1px solid #fca5a5;
        color: var(--danger-color);
        padding: 6px 10px;
        border-radius: 6px;
        transition: all 0.15s ease;
        margin: 0 3px;
        font-size: 13px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .btn-action-delete:hover {
        background: var(--danger-color);
        border-color: var(--danger-color);
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
    }

    /* Tables */
    .modern-table {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .modern-table thead {
        background: #f8fafc;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table thead th {
        color: var(--text-secondary);
        font-weight: 600;
        padding: 10px 12px;
        border: none;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .modern-table tbody tr {
        transition: background 0.15s ease;
        border-bottom: 1px solid var(--border-color);
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    .modern-table tbody tr:hover {
        background: #f8fafc;
    }

    .modern-table tbody td {
        padding: 12px;
        vertical-align: middle;
        border: none;
        color: var(--text-primary);
    }

    /* Badges */
    .code-badge {
        background: #f1f5f9;
        color: var(--text-primary);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        font-family: 'Monaco', 'Menlo', monospace;
    }

    .amount-badge {
        background: #f0fdf4;
        color: #166534;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        font-size: 14px;
        border: 1px solid #bbf7d0;
    }

    /* Stats Cards */
    .stats-container {
        padding: 0 24px 16px 24px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
    }

    .stat-card {
        background: white;
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 16px;
        transition: all 0.2s ease;
    }

    .stat-card:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 6px rgba(37, 99, 235, 0.1);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        margin-bottom: 10px;
    }

    .stat-icon.blue { background: #eff6ff; color: var(--primary-color); }
    .stat-icon.green { background: #f0fdf4; color: var(--success-color); }
    .stat-icon.purple { background: #faf5ff; color: #9333ea; }
    .stat-icon.orange { background: #fff7ed; color: #ea580c; }

    .stat-label {
        font-size: 11px;
        color: var(--text-secondary);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-primary);
        line-height: 1.2;
    }

    .stat-description {
        font-size: 11px;
        color: var(--text-secondary);
        margin-top: 4px;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 24px;
        color: var(--text-secondary);
    }

    .empty-state i {
        font-size: 40px;
        color: #cbd5e1;
        margin-bottom: 12px;
    }

    .empty-state p {
        font-size: 13px;
        margin: 6px 0 12px 0;
    }

    /* Custom Select */
    .custom-select-modern {
        height: 40px !important;
        padding: 6px 12px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background-color: white;
        font-size: 14px;
        color: var(--text-primary);
        line-height: normal;
    }

    .custom-select-modern:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        outline: none;
    }

    /* Search Box */
    .search-box {
        position: relative;
        max-width: 400px;
    }

    .search-input {
        padding-left: 50px !important;
        padding-right: 40px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        height: 40px;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 14px;
        pointer-events: none;
    }

    .clear-search {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        padding: 0;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        transition: all 0.2s ease;
    }

    .clear-search:hover {
        background: #f1f5f9;
        color: var(--danger-color);
    }

    /* Pagination Styling */
    .pagination {
        margin: 0;
        gap: 4px;
    }

    .pagination .page-item .page-link {
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s ease;
        margin: 0 2px;
    }

    .pagination .page-item .page-link:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .pagination .page-item.active .page-link {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        background: #f8fafc;
        border-color: var(--border-color);
        color: var(--text-secondary);
        opacity: 0.6;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .fade-in-up {
        animation: fadeIn 0.3s ease-out;
    }

    /* Modal Form Customization */
    .modal-content {
        border-radius: 12px;
        border: 1px solid var(--border-color);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    .modal-header {
        background: white;
        color: var(--text-primary);
        border-bottom: 1px solid var(--border-color);
        border-radius: 12px 12px 0 0;
        padding: 20px 24px;
    }
    .modal-title { font-weight: 600; font-size: 18px; color: var(--text-primary); }
    .form-group label { font-weight: 500; color: var(--text-primary); margin-bottom: 8px; font-size: 14px; }
    .form-control { border-radius: 8px; padding: 10px 12px; font-size: 14px; }
    .btn-success { background: var(--success-color); border: none; }
</style>
