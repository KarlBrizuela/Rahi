@extends('layouts.app')

@section('title', 'Currency Management — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/Currency/currency.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Currency</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="currencySearchInput" placeholder="Search currency name, code, symbol..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Currency Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload currency records via AJAX">
                <i data-lucide="refresh-cw" class="btn-icon" id="refreshIcon"></i>
                <span>Refresh</span>
            </button>

            <!-- Export / Print List Dropdown -->
            <div class="dropdown-wrapper" id="exportDropdownWrapper">
                <button type="button" class="btn-outline" id="btnExportToggle">
                    <i data-lucide="download" class="btn-icon"></i>
                    <span>Export / Print</span>
                    <i data-lucide="chevron-down" class="dropdown-chevron"></i>
                </button>
                <div class="export-dropdown-menu" id="exportMenu">
                    <button type="button" class="export-option" onclick="exportCurrencyDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable currency directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCurrencyDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCurrencyDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Button (Opens Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Currency</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">System Currencies</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Transactional & Valuation Exchange Units</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="currencyTable">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">Name</th>
                            <th style="min-width: 110px;">Short Name</th>
                            <th style="min-width: 160px;">Check Name</th>
                            <th style="min-width: 100px;">Symbol</th>
                            <th style="min-width: 120px;" class="text-center">Default</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="currencyTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 currency entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn disabled" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn disabled" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- ==========================================================================
     1. CREATE / EDIT CURRENCY MODAL
     ========================================================================== -->
<div class="modal-backdrop" id="currencyModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Currency</h3>
                <p class="modal-subtitle">Configure foreign exchange identifiers, monetary symbol, and cheque wording.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="currencyForm" onsubmit="handleCurrencyFormSubmit(event)">
            <input type="hidden" id="currencyFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Name & Short Name -->
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="currencyName">Currency Name <span class="required">*</span></label>
                        <input type="text" id="currencyName" class="form-input" placeholder="e.g. Philippine Peso" required maxlength="100">
                    </div>
                    <div class="form-group flex-1">
                        <label for="currencyShortName">Short Name (ISO) <span class="required">*</span></label>
                        <input type="text" id="currencyShortName" class="form-input" placeholder="e.g. PHP" required maxlength="10" style="text-transform: uppercase;">
                    </div>
                </div>

                <!-- Row 2: Check Name & Symbol -->
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="currencyCheckName">Check Name (Words) <span class="required">*</span></label>
                        <input type="text" id="currencyCheckName" class="form-input" placeholder="e.g. PESOS" required maxlength="100" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group flex-1">
                        <label for="currencySymbol">Symbol <span class="required">*</span></label>
                        <input type="text" id="currencySymbol" class="form-input" placeholder="e.g. ₱ or $" required maxlength="10">
                    </div>
                </div>

                <!-- Row 3: Default Checkbox -->
                <div class="form-checkbox-row">
                    <label class="custom-checkbox-container">
                        <input type="checkbox" id="currencyIsDefault" class="real-checkbox">
                        <span class="custom-checkmark"><i data-lucide="check"></i></span>
                        <div class="checkbox-text-group">
                            <span class="checkbox-label-text">Set as System Default Currency</span>
                            <span class="checkbox-hint-text">Automatically selects this currency in sales orders, purchase vouchers, and financial ledgers.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Currency</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="currencyDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Currency Deletion</h3>
                    <p class="modal-subtitle">Permanent removal of currency from active configuration.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete currency <strong id="deleteTargetCurrencyName" class="delete-target-text">—</strong>?
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Currency</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory currency records
let currencyRecords = [
    {
        id: 1,
        name: 'Philippine Peso',
        shortname: 'PHP',
        check_name: 'PESOS',
        symbol: '₱',
        is_default: true
    },
    {
        id: 2,
        name: 'US Dollar',
        shortname: 'USD',
        check_name: 'US DOLLARS',
        symbol: '$',
        is_default: false
    },
    {
        id: 3,
        name: 'Japanese Yen',
        shortname: 'JPY',
        check_name: 'YEN',
        symbol: '¥',
        is_default: false
    },
    {
        id: 4,
        name: 'Euro',
        shortname: 'EUR',
        check_name: 'EUROS',
        symbol: '€',
        is_default: false
    }
];

let pendingDeleteCurrencyId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderCurrencyTable(currencyRecords);

    // Modal backdrops
    const currencyModalBackdrop = document.getElementById('currencyModalBackdrop');
    const currencyDeleteModalBackdrop = document.getElementById('currencyDeleteModalBackdrop');

    // Buttons
    const btnOpenCreate = document.getElementById('btnOpenCreateModal');
    const btnModalClose = document.getElementById('btnModalClose');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');

    const btnDeleteCancel = document.getElementById('btnDeleteCancel');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnConfirmDeleteAction = document.getElementById('btnConfirmDeleteAction');

    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const searchInput = document.getElementById('currencySearchInput');

    // 1. Export Dropdown Toggle
    btnExportToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        exportDropdownWrapper.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!exportDropdownWrapper.contains(e.target)) {
            exportDropdownWrapper.classList.remove('active');
        }
    });

    // 2. Open Create Modal
    btnOpenCreate.addEventListener('click', function () {
        document.getElementById('currencyForm').reset();
        document.getElementById('currencyFormId').value = '';
        document.getElementById('currencyIsDefault').checked = false;
        document.getElementById('modalTitle').textContent = 'Create New Currency';
        currencyModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        currencyModalBackdrop.classList.remove('show');
        currencyDeleteModalBackdrop.classList.remove('show');
        pendingDeleteCurrencyId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [currencyModalBackdrop, currencyDeleteModalBackdrop].forEach(function (backdrop) {
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeAllModals();
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // 3. Confirm Delete Action
    btnConfirmDeleteAction.addEventListener('click', function () {
        if (pendingDeleteCurrencyId !== null) {
            currencyRecords = currencyRecords.filter(function (r) {
                return r.id !== pendingDeleteCurrencyId;
            });
            filterRecords();
        }
        closeAllModals();
    });

    // 4. Search Filter
    function filterRecords() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const filtered = currencyRecords.filter(function (c) {
            const rowText = `${c.name} ${c.shortname} ${c.check_name} ${c.symbol} ${c.is_default ? 'default' : ''}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderCurrencyTable(filtered);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRecords);
    }

    // 5. Refresh (Simulated AJAX)
    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        btnRefresh.classList.add('loading');

        setTimeout(function () {
            filterRecords();
            refreshIcon.classList.remove('spin-anim');
            btnRefresh.classList.remove('loading');
        }, 350);
    });

    // Close any open action menu on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.row-action-dropdown')) {
            document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
                m.classList.remove('show');
            });
        }
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

// Toggle 3-Dots Action Menu
function toggleCurrencyActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`currencyActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Currency Table
function renderCurrencyTable(dataset) {
    const tbody = document.getElementById('currencyTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="6" class="empty-cell">No matching currency records found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${currencyRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');

        const defaultBadge = rec.is_default
            ? `<span class="status-pill status-success"><span class="status-dot"></span> Default</span>`
            : `<span class="text-muted">—</span>`;

        tr.innerHTML = `
            <td><strong class="currency-name-text">${rec.name}</strong></td>
            <td><span class="shortname-badge">${rec.shortname}</span></td>
            <td><span class="checkname-pill">${rec.check_name}</span></td>
            <td><span class="symbol-pill">${rec.symbol}</span></td>
            <td class="text-center">${defaultBadge}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleCurrencyActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="currencyActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewCurrencyRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editCurrencyRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openCurrencyDeleteModal(${rec.id})">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('recordCount').textContent = `${dataset.length} Records`;
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${currencyRecords.length} currency entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Currency Modal
function viewCurrencyRow(id) {
    const rec = currencyRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('currencyFormId').value = rec.id;
    document.getElementById('currencyName').value = rec.name;
    document.getElementById('currencyShortName').value = rec.shortname;
    document.getElementById('currencyCheckName').value = rec.check_name;
    document.getElementById('currencySymbol').value = rec.symbol;
    document.getElementById('currencyDefault').checked = !!rec.default;

    document.getElementById('modalTitle').textContent = 'View Currency — ' + rec.name;
    document.getElementById('currencyModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editCurrencyRow(id) {
    const rec = currencyRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('currencyFormId').value = rec.id;
    document.getElementById('currencyName').value = rec.name;
    document.getElementById('currencyShortName').value = rec.shortname;
    document.getElementById('currencyCheckName').value = rec.check_name;
    document.getElementById('currencySymbol').value = rec.symbol;
    document.getElementById('currencyIsDefault').checked = !!rec.is_default;

    document.getElementById('modalTitle').textContent = 'Edit Currency — ' + rec.shortname;
    document.getElementById('currencyModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openCurrencyDeleteModal(id) {
    const rec = currencyRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteCurrencyId = id;
    document.getElementById('deleteTargetCurrencyName').textContent = `${rec.name} (${rec.shortname})`;
    document.getElementById('currencyDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handleCurrencyFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('currencyFormId').value;
    const name = document.getElementById('currencyName').value.trim();
    const shortname = document.getElementById('currencyShortName').value.trim().toUpperCase();
    const check_name = document.getElementById('currencyCheckName').value.trim().toUpperCase();
    const symbol = document.getElementById('currencySymbol').value.trim();
    const is_default = document.getElementById('currencyIsDefault').checked;

    // If set as default, demote others
    if (is_default) {
        currencyRecords.forEach(function (c) {
            c.is_default = false;
        });
    }

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = currencyRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.name = name;
            existing.shortname = shortname;
            existing.check_name = check_name;
            existing.symbol = symbol;
            existing.is_default = is_default;
        }
    } else {
        // Create new
        const newId = currencyRecords.length ? Math.max.apply(null, currencyRecords.map(function (r) { return r.id; })) + 1 : 1;
        currencyRecords.push({
            id: newId,
            name: name,
            shortname: shortname,
            check_name: check_name,
            symbol: symbol,
            is_default: is_default
        });
    }

    const searchInput = document.getElementById('currencySearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = currencyRecords.filter(function (c) {
        const rowText = `${c.name} ${c.shortname} ${c.check_name} ${c.symbol} ${c.is_default ? 'default' : ''}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderCurrencyTable(filtered);
    document.getElementById('currencyModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportCurrencyDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'Name,Short Name,Check Name,Symbol,Default\n';
        currencyRecords.forEach(function (r) {
            const isDefStr = r.is_default ? 'YES (Default)' : 'NO';
            csvContent += `"${r.name}","${r.shortname}","${r.check_name}","${r.symbol}","${isDefStr}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Currency_Directory_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        currencyRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.name}</td>
                    <td>${r.shortname}</td>
                    <td>${r.check_name}</td>
                    <td>${r.symbol}</td>
                    <td>${r.is_default ? 'YES (Default)' : 'NO'}</td>
                </tr>
            `;
        });

        const template = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th { background-color: #2563eb; color: #ffffff; font-weight: bold; border: 1px solid #1e40af; padding: 8px 10px; }
                    td { border: 1px solid #d1d5db; padding: 6px 10px; }
                </style>
            </head>
            <body>
                <h2>Intracode ERP — Currency Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Check Name</th>
                            <th>Symbol</th>
                            <th>Default</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </body>
            </html>
        `;

        const blob = new Blob([template], { type: 'application/vnd.ms-excel;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Currency_Directory_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

