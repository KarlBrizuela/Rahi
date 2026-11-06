@extends('layouts.app')

@section('title', 'Unit of Product — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/UnitOfProduct/unitofproduct.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Unit of Product</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="unitSearchInput" placeholder="Search unit of product, shortname..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Unit of Product Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload units via AJAX">
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
                    <button type="button" class="export-option" onclick="exportUnitDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable unit directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportUnitDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportUnitDirectory('csv')">
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
                <span>Create Unit</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Measurement Units</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Standard Units of Measure for Finished Goods & Supplies</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="unitTable">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Description</th>
                            <th style="min-width: 110px;">Short Name</th>
                            <th style="min-width: 120px;" class="text-center">For Product</th>
                            <th style="min-width: 120px;" class="text-center">For Supplies</th>
                            <th style="min-width: 90px;" class="text-right">Decimals</th>
                            <th style="min-width: 130px;">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="unitTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 unit entries</span>
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
     1. CREATE / EDIT UNIT MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="unitModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create Measurement Unit</h3>
                <p class="modal-subtitle">Specify unit of measure description, precision decimals, and catalog applicability.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="unitForm" onsubmit="handleUnitFormSubmit(event)">
            <input type="hidden" id="unitFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Description & Short Name -->
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="unitDescription">Description <span class="required">*</span></label>
                        <input type="text" id="unitDescription" class="form-input" placeholder="e.g. Pieces" required maxlength="100">
                    </div>
                    <div class="form-group flex-1">
                        <label for="unitShortName">Short Name (UOM) <span class="required">*</span></label>
                        <input type="text" id="unitShortName" class="form-input" placeholder="e.g. PCS" required maxlength="15" style="text-transform: uppercase;">
                    </div>
                </div>

                <!-- Row 2: Decimals Precision -->
                <div class="form-group">
                    <label for="unitDecimals">Decimals (Precision) <span class="required">*</span></label>
                    <input type="number" id="unitDecimals" class="form-input font-numeric" placeholder="e.g. 0 (Integer) or 2 (Fractional)" min="0" max="6" value="0" required>
                </div>

                <!-- Row 3: Dual Applicability Checkboxes -->
                <div class="checkbox-dual-grid">
                    <div class="form-checkbox-card">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" id="unitForProduct" class="real-checkbox">
                            <span class="custom-checkmark"><i data-lucide="check"></i></span>
                            <div class="checkbox-text-group">
                                <span class="checkbox-label-text">For Product</span>
                                <span class="checkbox-hint-text">Applicable to Finished Goods, sales orders, and invoices.</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-checkbox-card">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" id="unitForSupplies" class="real-checkbox">
                            <span class="custom-checkmark"><i data-lucide="check"></i></span>
                            <div class="checkbox-text-group">
                                <span class="checkbox-label-text">For Supplies</span>
                                <span class="checkbox-hint-text">Applicable to Raw Materials, consumables, and requisitions.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Unit</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="unitDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Unit Deletion</h3>
                    <p class="modal-subtitle">Sets the record status to deleted as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete unit <strong id="deleteTargetUnitDesc" class="delete-target-text">—</strong>?
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Unit</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory records
let unitRecords = [
    {
        id: 1,
        description: 'Pieces',
        shortname: 'PCS',
        for_product: true,
        for_supplies: true,
        decimals: 0,
        deleted_date: '—'
    },
    {
        id: 2,
        description: 'Kilograms',
        shortname: 'KG',
        for_product: true,
        for_supplies: true,
        decimals: 2,
        deleted_date: '—'
    },
    {
        id: 3,
        description: 'Box / Carton',
        shortname: 'BOX',
        for_product: true,
        for_supplies: false,
        decimals: 0,
        deleted_date: '—'
    },
    {
        id: 4,
        description: 'Meters / Linear',
        shortname: 'MTR',
        for_product: false,
        for_supplies: true,
        decimals: 2,
        deleted_date: '—'
    },
    {
        id: 5,
        description: 'Liters / Bulk (Archived)',
        shortname: 'LTR',
        for_product: false,
        for_supplies: true,
        decimals: 2,
        deleted_date: '2026-08-14'
    }
];

let pendingDeleteUnitId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderUnitTable(unitRecords);

    // Modal backdrops
    const unitModalBackdrop = document.getElementById('unitModalBackdrop');
    const unitDeleteModalBackdrop = document.getElementById('unitDeleteModalBackdrop');

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
    const searchInput = document.getElementById('unitSearchInput');

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
        document.getElementById('unitForm').reset();
        document.getElementById('unitFormId').value = '';
        document.getElementById('unitForProduct').checked = true;
        document.getElementById('unitForSupplies').checked = true;
        document.getElementById('modalTitle').textContent = 'Create Measurement Unit';
        unitModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        unitModalBackdrop.classList.remove('show');
        unitDeleteModalBackdrop.classList.remove('show');
        pendingDeleteUnitId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [unitModalBackdrop, unitDeleteModalBackdrop].forEach(function (backdrop) {
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeAllModals();
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // 3. Confirm Delete Action (Soft Delete with Date)
    btnConfirmDeleteAction.addEventListener('click', function () {
        if (pendingDeleteUnitId !== null) {
            const item = unitRecords.find(function (r) {
                return r.id === pendingDeleteUnitId;
            });
            if (item) {
                const today = new Date().toISOString().split('T')[0];
                item.deleted_date = today;
                filterRecords();
            }
        }
        closeAllModals();
    });

    // 4. Search Filter
    function filterRecords() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const filtered = unitRecords.filter(function (u) {
            const rowText = `${u.description} ${u.shortname} ${u.decimals} ${u.for_product ? 'product' : ''} ${u.for_supplies ? 'supplies' : ''} ${u.deleted_date}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderUnitTable(filtered);
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
function toggleUnitActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`unitActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Toggle For Product / For Supplies Checkbox directly from table
function toggleUnitScope(id, scope, isChecked) {
    const item = unitRecords.find(function (r) {
        return r.id === id;
    });
    if (item) {
        if (scope === 'product') {
            item.for_product = isChecked;
        } else if (scope === 'supplies') {
            item.for_supplies = isChecked;
        }
    }
}

// Render Table
function renderUnitTable(dataset) {
    const tbody = document.getElementById('unitTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="7" class="empty-cell">No matching unit of product entries found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${unitRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="unit-desc-text ${isDeleted ? 'text-faint' : ''}">${rec.description}</strong></td>
            <td><span class="shortname-badge ${isDeleted ? 'badge-faint' : ''}">${rec.shortname}</span></td>
            <td class="text-center">
                <div class="table-checkbox-wrap">
                    <input type="checkbox" class="table-checkbox" ${rec.for_product ? 'checked' : ''} onchange="toggleUnitScope(${rec.id}, 'product', this.checked)" title="Applicable to Finished Goods & Products">
                </div>
            </td>
            <td class="text-center">
                <div class="table-checkbox-wrap">
                    <input type="checkbox" class="table-checkbox" ${rec.for_supplies ? 'checked' : ''} onchange="toggleUnitScope(${rec.id}, 'supplies', this.checked)" title="Applicable to Materials & Supplies">
                </div>
            </td>
            <td class="text-right font-numeric">${rec.decimals}</td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleUnitActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="unitActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewUnitRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editUnitRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openUnitDeleteModal(${rec.id})">
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
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${unitRecords.length} unit entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Record Modal
function viewUnitRow(id) {
    const rec = unitRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('unitFormId').value = rec.id;
    document.getElementById('unitDescription').value = rec.description;
    document.getElementById('unitShortName').value = rec.shortname;
    document.getElementById('unitDecimals').value = rec.decimals;
    document.getElementById('unitForProduct').checked = !!rec.for_product;
    document.getElementById('unitForSupplies').checked = !!rec.for_supplies;

    document.getElementById('modalTitle').textContent = 'View Unit — ' + rec.shortname;
    document.getElementById('unitModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editUnitRow(id) {
    const rec = unitRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('unitFormId').value = rec.id;
    document.getElementById('unitDescription').value = rec.description;
    document.getElementById('unitShortName').value = rec.shortname;
    document.getElementById('unitDecimals').value = rec.decimals;
    document.getElementById('unitForProduct').checked = !!rec.for_product;
    document.getElementById('unitForSupplies').checked = !!rec.for_supplies;

    document.getElementById('modalTitle').textContent = 'Edit Unit — ' + rec.shortname;
    document.getElementById('unitModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openUnitDeleteModal(id) {
    const rec = unitRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteUnitId = id;
    document.getElementById('deleteTargetUnitDesc').textContent = `${rec.description} (${rec.shortname})`;
    document.getElementById('unitDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handleUnitFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('unitFormId').value;
    const description = document.getElementById('unitDescription').value.trim();
    const shortname = document.getElementById('unitShortName').value.trim().toUpperCase();
    const decimals = parseInt(document.getElementById('unitDecimals').value, 10) || 0;
    const for_product = document.getElementById('unitForProduct').checked;
    const for_supplies = document.getElementById('unitForSupplies').checked;

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = unitRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.description = description;
            existing.shortname = shortname;
            existing.decimals = decimals;
            existing.for_product = for_product;
            existing.for_supplies = for_supplies;
        }
    } else {
        // Create new
        const newId = unitRecords.length ? Math.max.apply(null, unitRecords.map(function (r) { return r.id; })) + 1 : 1;
        unitRecords.push({
            id: newId,
            description: description,
            shortname: shortname,
            decimals: decimals,
            for_product: for_product,
            for_supplies: for_supplies,
            deleted_date: '—'
        });
    }

    const searchInput = document.getElementById('unitSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = unitRecords.filter(function (u) {
        const rowText = `${u.description} ${u.shortname} ${u.decimals} ${u.for_product ? 'product' : ''} ${u.for_supplies ? 'supplies' : ''} ${u.deleted_date}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderUnitTable(filtered);
    document.getElementById('unitModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportUnitDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'Description,Short Name,For Product,For Supplies,Decimals,Deleted Date\n';
        unitRecords.forEach(function (r) {
            const cleanDesc = `"${r.description.replace(/"/g, '""')}"`;
            const prodStr = r.for_product ? 'YES' : 'NO';
            const suppStr = r.for_supplies ? 'YES' : 'NO';
            csvContent += `${cleanDesc},"${r.shortname}","${prodStr}","${suppStr}",${r.decimals},"${r.deleted_date}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Unit_of_Product_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        unitRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.description}</td>
                    <td>${r.shortname}</td>
                    <td>${r.for_product ? 'YES' : 'NO'}</td>
                    <td>${r.for_supplies ? 'YES' : 'NO'}</td>
                    <td>${r.decimals}</td>
                    <td>${r.deleted_date}</td>
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
                <h2>Intracode ERP — Unit of Product Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Short Name</th>
                            <th>For Product</th>
                            <th>For Supplies</th>
                            <th>Decimals</th>
                            <th>Deleted Date</th>
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
        link.download = `Unit_of_Product_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

