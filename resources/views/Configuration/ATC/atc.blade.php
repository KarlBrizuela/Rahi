@extends('layouts.app')

@section('title', 'ATC — Alphanumeric Tax Code')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/ATC/atc.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">ATC</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search ATC code, description..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Alphanumeric Tax Code (ATC)</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload grid data via AJAX">
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
                    <button type="button" class="export-option" onclick="exportDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable document format</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportDirectory('csv')">
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
                <span>Create ATC</span>
            </button>
        </div>
    </section>

    <!-- 3. MAIN WORKSPACE / DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Tax Code Directory</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Active & Archived Codes</span>
                </div>
            </div>

            <!-- Data Table Container -->
            <div class="table-responsive">
                <table class="erp-table" id="atcTable">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Code</th>
                            <th>Description</th>
                            <th style="width: 110px;" class="text-right">Rate (%)</th>
                            <th style="width: 160px;">Account Code</th>
                            <th style="width: 150px;">Deleted Date</th>
                            <th style="width: 130px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="atcTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 tax code entries</span>
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
     1. CREATE / EDIT MODAL (NO DELETED DATE FIELD)
     ========================================================================== -->
<div class="modal-backdrop" id="atcModalBackdrop">
    <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New ATC</h3>
                <p class="modal-subtitle">Specify alphanumeric tax classification, rate, and financial account mapping.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="atcForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" id="formId" value="">

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="atcCode">ATC Code <span class="required">*</span></label>
                        <input type="text" id="atcCode" class="form-input" placeholder="e.g. WI100" required maxlength="20">
                    </div>

                    <div class="form-group col-half">
                        <label for="atcRate">Rate (%) <span class="required">*</span></label>
                        <input type="number" id="atcRate" class="form-input" placeholder="e.g. 10.00" step="0.01" min="0" max="100" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="atcDesc">Description <span class="required">*</span></label>
                    <input type="text" id="atcDesc" class="form-input" placeholder="e.g. Professional Fees / Talent Fees" required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="atcAccountCode">Account Code <span class="required">*</span></label>
                    <input type="text" id="atcAccountCode" class="form-input" placeholder="e.g. 2010-01-00" required maxlength="50">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="deleteConfirmModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Record Deletion</h3>
                    <p class="modal-subtitle">This action will set the record's deleted status as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-confirmation-text">
                Are you sure you want to delete the ATC record for:
            </p>
            <div class="delete-target-preview">
                <strong id="deleteTargetCode">—</strong>
                <span id="deleteTargetDesc">—</span>
            </div>
            <p class="delete-note">
                <i data-lucide="info"></i>
                The deleted date will be automatically set to today's date (<strong id="deleteTargetDate">—</strong>).
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Record</button>
        </div>

    </div>
</div>

<!-- ==========================================================================
     3. SINGLE-ITEM PRINT / EXPORT MODAL PREVIEW
     ========================================================================== -->
<div class="modal-backdrop" id="itemPrintModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true">

        <div class="modal-header no-print">
            <div class="modal-title-group">
                <h3 class="modal-title">ATC Record Print Preview</h3>
                <p class="modal-subtitle">Official Alphanumeric Tax Code Record Voucher</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnItemPrintCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body print-slip-body">
            <!-- Printable Certificate Document -->
            <div class="printable-voucher" id="printVoucher">
                <div class="voucher-header">
                    <div class="voucher-brand">
                        <img src="{{ asset('fav/fav.png') }}" alt="Logo" class="voucher-logo">
                        <div>
                            <h4>Intracode IT Solutions</h4>
                            <small>Enterprise Tax Classification System</small>
                        </div>
                    </div>
                    <div class="voucher-meta">
                        <span class="voucher-badge">ATC Record Sheet</span>
                        <div class="voucher-date">Generated: <span id="voucherDate">—</span></div>
                    </div>
                </div>

                <div class="voucher-divider"></div>

                <div class="voucher-grid">
                    <div class="voucher-field">
                        <label>ATC Code</label>
                        <div class="field-val highlight" id="voucherCode">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Tax Rate</label>
                        <div class="field-val" id="voucherRate">—</div>
                    </div>

                    <div class="voucher-field full-width">
                        <label>Description</label>
                        <div class="field-val" id="voucherDesc">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Account Code</label>
                        <div class="field-val" id="voucherAccountCode">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Status / Deleted Date</label>
                        <div class="field-val" id="voucherStatus">—</div>
                    </div>
                </div>

                <div class="voucher-footer">
                    <div class="sign-block">
                        <div class="sign-line"></div>
                        <span>Prepared By</span>
                    </div>
                    <div class="sign-block">
                        <div class="sign-line"></div>
                        <span>Verified & Approved</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer no-print">
            <button type="button" class="btn-outline" id="btnItemPrintClose">Close</button>
            <button type="button" class="btn-outline" id="btnItemExportExcel">
                <i data-lucide="sheet" class="btn-icon"></i>
                <span>Download Excel</span>
            </button>
            <button type="button" class="btn-primary" id="btnItemPrintNow">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Record</span>
            </button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory records
let records = [
    { id: 1, code: 'WI100', desc: 'Professional Fees / Talent Fees', rate: '10.00%', account_code: '2010-01-00', deleted_date: '—' },
    { id: 2, code: 'WI158', desc: 'Rental of Real / Personal Properties', rate: '5.00%', account_code: '2010-02-00', deleted_date: '—' },
    { id: 3, code: 'WC160', desc: 'Income payments made to general professional partnerships', rate: '15.00%', account_code: '2010-03-00', deleted_date: '—' },
    { id: 4, code: 'WV010', desc: 'Value Added Tax on Goods (Archived)', rate: '12.00%', account_code: '2020-01-00', deleted_date: '2026-08-14' }
];

let pendingDeleteId = null;
let currentPreviewItem = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderTable();

    // DOM Elements
    const atcModalBackdrop = document.getElementById('atcModalBackdrop');
    const deleteConfirmModalBackdrop = document.getElementById('deleteConfirmModalBackdrop');
    const itemPrintModalBackdrop = document.getElementById('itemPrintModalBackdrop');

    const btnOpenCreate = document.getElementById('btnOpenCreateModal');
    const btnModalClose = document.getElementById('btnModalClose');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');

    const btnDeleteCancel = document.getElementById('btnDeleteCancel');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnConfirmDeleteAction = document.getElementById('btnConfirmDeleteAction');

    const btnItemPrintClose = document.getElementById('btnItemPrintClose');
    const btnItemPrintCloseCross = document.getElementById('btnItemPrintCloseCross');
    const btnItemPrintNow = document.getElementById('btnItemPrintNow');
    const btnItemExportExcel = document.getElementById('btnItemExportExcel');

    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');

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
        document.getElementById('atcForm').reset();
        document.getElementById('formId').value = '';
        document.getElementById('modalTitle').textContent = 'Create New ATC';
        atcModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        atcModalBackdrop.classList.remove('show');
        deleteConfirmModalBackdrop.classList.remove('show');
        itemPrintModalBackdrop.classList.remove('show');
        pendingDeleteId = null;
    }

    // Modal Close Triggers
    btnModalClose.addEventListener('click', closeAllModals);
    btnModalCloseCross.addEventListener('click', closeAllModals);
    btnDeleteCancel.addEventListener('click', closeAllModals);
    btnDeleteCloseCross.addEventListener('click', closeAllModals);
    btnItemPrintClose.addEventListener('click', closeAllModals);
    btnItemPrintCloseCross.addEventListener('click', closeAllModals);

    [atcModalBackdrop, deleteConfirmModalBackdrop, itemPrintModalBackdrop].forEach(function (backdrop) {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeAllModals();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // 3. Confirm Delete Action
    btnConfirmDeleteAction.addEventListener('click', function () {
        if (pendingDeleteId !== null) {
            const rec = records.find(r => r.id === pendingDeleteId);
            if (rec) {
                const today = new Date().toISOString().split('T')[0];
                rec.deleted_date = today;
                renderTable();
            }
        }
        closeAllModals();
    });

    // 4. Print Item Modal Actions
    btnItemPrintNow.addEventListener('click', function () {
        window.print();
    });

    btnItemExportExcel.addEventListener('click', function () {
        if (currentPreviewItem) {
            exportSingleItemExcel(currentPreviewItem);
        }
    });

    // 5. Simulated AJAX Refresh
    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        btnRefresh.classList.add('loading');

        setTimeout(function () {
            renderTable();
            refreshIcon.classList.remove('spin-anim');
            btnRefresh.classList.remove('loading');
        }, 400);
    });

    // Top Bar Search Filter
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#atcTableBody tr');
            let visibleCount = 0;
            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                const matches = text.includes(query);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });
            document.getElementById('footerMetaText').textContent = `Showing ${visibleCount} of ${records.length} tax code entries`;
        });
    }

    // Close any open action menu on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.row-action-dropdown')) {
            document.querySelectorAll('.row-action-menu.show').forEach(m => m.classList.remove('show'));
        }
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

// Toggle 3-Dots Action Menu
function toggleActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`actionMenu-${id}`);
    const isAlreadyOpen = menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(m => m.classList.remove('show'));
    if (!isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Table
function renderTable() {
    const tbody = document.getElementById('atcTableBody');
    tbody.innerHTML = '';

    records.forEach(function (rec) {
        const tr = document.createElement('tr');
        tr.setAttribute('data-id', rec.id);
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="code-badge ${isDeleted ? 'text-faint' : ''}">${rec.code}</strong></td>
            <td class="cell-desc ${isDeleted ? 'text-muted' : ''}">${rec.desc}</td>
            <td class="text-right cell-numeric ${isDeleted ? 'text-muted' : ''}">${rec.rate}</td>
            <td><span class="account-code-pill ${isDeleted ? 'pill-faint' : ''}">${rec.account_code}</span></td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="actionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="openPrintModal(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View / Print</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openDeleteModal(${rec.id})">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('recordCount').textContent = `${records.length} Records`;
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${records.length} of ${records.length} tax code entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Open Edit Modal
function editRow(id) {
    const rec = records.find(item => item.id === id);
    if (!rec) return;

    document.getElementById('formId').value = rec.id;
    document.getElementById('atcCode').value = rec.code;
    document.getElementById('atcDesc').value = rec.desc;
    document.getElementById('atcRate').value = parseFloat(rec.rate);
    document.getElementById('atcAccountCode').value = rec.account_code;

    document.getElementById('modalTitle').textContent = 'Edit ATC — ' + rec.code;
    document.getElementById('atcModalBackdrop').classList.add('show');
}

// Open Custom Delete Modal (NO ALERT)
function openDeleteModal(id) {
    const rec = records.find(item => item.id === id);
    if (!rec) return;

    pendingDeleteId = id;
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('deleteTargetCode').textContent = rec.code;
    document.getElementById('deleteTargetDesc').textContent = ' — ' + rec.desc;
    document.getElementById('deleteTargetDate').textContent = today;

    document.getElementById('deleteConfirmModalBackdrop').classList.add('show');
}

// Open Single Item Print Slip Modal
function openPrintModal(id) {
    const rec = records.find(item => item.id === id);
    if (!rec) return;

    currentPreviewItem = rec;
    const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

    document.getElementById('voucherDate').textContent = today;
    document.getElementById('voucherCode').textContent = rec.code;
    document.getElementById('voucherRate').textContent = rec.rate;
    document.getElementById('voucherDesc').textContent = rec.desc;
    document.getElementById('voucherAccountCode').textContent = rec.account_code;

    const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
    document.getElementById('voucherStatus').innerHTML = isDeleted
        ? `<span class="badge-status-deleted">Archived (Deleted: ${rec.deleted_date})</span>`
        : `<span class="badge-status-active">Active</span>`;

    document.getElementById('itemPrintModalBackdrop').classList.add('show');

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Form Submit Handler (NO DELETED DATE REQUIRED)
function handleFormSubmit(e) {
    e.preventDefault();
    const idVal = document.getElementById('formId').value;
    const code = document.getElementById('atcCode').value.trim();
    const desc = document.getElementById('atcDesc').value.trim();
    const rate = parseFloat(document.getElementById('atcRate').value).toFixed(2) + '%';
    const account_code = document.getElementById('atcAccountCode').value.trim();

    if (idVal) {
        const rec = records.find(item => item.id == idVal);
        if (rec) {
            rec.code = code;
            rec.desc = desc;
            rec.rate = rate;
            rec.account_code = account_code;
        }
    } else {
        const newId = records.length ? Math.max(...records.map(r => r.id)) + 1 : 1;
        records.unshift({
            id: newId,
            code: code,
            desc: desc,
            rate: rate,
            account_code: account_code,
            deleted_date: '—'
        });
    }

    renderTable();
    document.getElementById('atcModalBackdrop').classList.remove('show');
}

// ==========================================================================
// EXPORT & PRINT HANDLERS (PDF, EXCEL TEMPLATE, CSV)
// ==========================================================================

function exportDirectory(format) {
    document.getElementById('exportDropdownWrapper').classList.remove('active');

    if (format === 'pdf') {
        window.print();
    } else if (format === 'excel') {
        exportDirectoryExcel();
    } else if (format === 'csv') {
        exportDirectoryCSV();
    }
}

// Formatted Excel Template for Directory (.xls)
function exportDirectoryExcel() {
    let rowsHtml = '';
    records.forEach(r => {
        rowsHtml += `
            <tr>
                <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold;">${r.code}</td>
                <td style="border:1px solid #cbd5e1; padding:8px;">${r.desc}</td>
                <td style="border:1px solid #cbd5e1; padding:8px; text-align:right;">${r.rate}</td>
                <td style="border:1px solid #cbd5e1; padding:8px;">${r.account_code}</td>
                <td style="border:1px solid #cbd5e1; padding:8px; color:${r.deleted_date !== '—' ? '#b91c1c' : '#64748b'};">${r.deleted_date}</td>
            </tr>`;
    });

    const excelTemplate = `
        <html>
        <head>
            <meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"/>
        </head>
        <body style="font-family: Arial, sans-serif;">
            <table border="1" style="border-collapse:collapse; width:100%;">
                <tr>
                    <th colspan="5" style="background:#2563eb; color:#ffffff; font-size:16px; padding:12px; text-align:left; font-weight:bold;">
                        Intracode ERP — Alphanumeric Tax Code (ATC) Directory
                    </th>
                </tr>
                <tr>
                    <td colspan="5" style="padding:6px; color:#64748b; font-size:11px;">
                        Exported on: ${new Date().toLocaleString()} | Total Records: ${records.length}
                    </td>
                </tr>
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">ATC Code</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Description</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:right;">Rate (%)</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Account Code</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Deleted Date</th>
                </tr>
                ${rowsHtml}
            </table>
        </body>
        </html>`;

    downloadFile(excelTemplate, 'ATC_Directory_Template.xls', 'application/vnd.ms-excel');
}

// Formatted Excel Template for Single Item
function exportSingleItemExcel(item) {
    const excelTemplate = `
        <html>
        <head>
            <meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"/>
        </head>
        <body style="font-family: Arial, sans-serif;">
            <table border="1" style="border-collapse:collapse; width:450px;">
                <tr>
                    <th colspan="2" style="background:#2563eb; color:#ffffff; font-size:14px; padding:10px; text-align:left;">
                        Intracode IT Solutions — ATC Record Voucher
                    </th>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; width:140px; background:#f8fafc;">ATC Code</td>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; color:#0f172a;">${item.code}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Tax Rate</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.rate}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Description</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.desc}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Account Code</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.account_code}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Deleted Date</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.deleted_date}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Status</td>
                    <td style="border:1px solid #cbd5e1; padding:8px; color:${item.deleted_date !== '—' ? '#b91c1c' : '#047857'}; font-weight:bold;">
                        ${item.deleted_date !== '—' ? 'Archived' : 'Active'}
                    </td>
                </tr>
            </table>
        </body>
        </html>`;

    downloadFile(excelTemplate, `ATC_Record_${item.code}.xls`, 'application/vnd.ms-excel');
}

// CSV Export
function exportDirectoryCSV() {
    let csv = 'ATC Code,Description,Rate (%),Account Code,Deleted Date\r\n';
    records.forEach(r => {
        csv += `"${r.code}","${r.desc.replace(/"/g, '""')}","${r.rate}","${r.account_code}","${r.deleted_date}"\r\n`;
    });
    downloadFile(csv, 'ATC_Directory.csv', 'text/csv;charset=utf-8;');
}

function downloadFile(content, fileName, mimeType) {
    const blob = new Blob([content], { type: mimeType });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.setAttribute('download', fileName);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush
