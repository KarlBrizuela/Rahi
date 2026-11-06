@extends('layouts.app')

@section('title', 'Audit Trail — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/AuditTrail/audittrail.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (CLEAN BREADCRUMB ONLY) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Audit Trail</span>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER (CLEAN PAGE TITLE) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">System Audit Trail</h1>
        </div>
    </section>

    <!-- 3. INLINE TOOLBAR (FILTERS ON LEFT + REFRESH & EXPORT ON RIGHT) -->
    <section class="filter-bar-row no-print">
        <div class="filter-controls-group">

            <!-- Filter Controls (Left) -->
            <div class="filter-left-cluster">
                <!-- Search Filter -->
                <div class="filter-search-box">
                    <i data-lucide="search" class="search-icon"></i>
                    <input type="text" id="auditSearchInput" placeholder="Search user, action, form, IP..." autocomplete="off">
                </div>

                <div class="filter-divider"></div>

                <!-- Date Range Controls -->
                <div class="date-range-cluster">
                    <span class="filter-cluster-label">Date:</span>
                    <div class="date-input-wrap">
                        <input type="date" id="dateFrom" class="date-input" title="From date">
                    </div>
                    <span class="range-sep">to</span>
                    <div class="date-input-wrap">
                        <input type="date" id="dateTo" class="date-input" title="To date">
                    </div>
                </div>

                <div class="filter-divider"></div>

                <!-- Reset Filters -->
                <button type="button" class="btn-ghost-sm" id="btnResetFilters" title="Reset all filters">
                    <i data-lucide="rotate-ccw"></i>
                    <span>Reset</span>
                </button>
            </div>

            <!-- Actions (Right: Refresh & Export / Print) -->
            <div class="action-cluster">
                <!-- Refresh Button (Simulated AJAX) -->
                <button type="button" class="btn-outline" id="btnRefresh" title="Reload audit logs via AJAX">
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
                        <button type="button" class="export-option" onclick="exportAuditTrail('pdf')">
                            <i data-lucide="printer"></i>
                            <div class="opt-text">
                                <strong>Print / PDF</strong>
                                <small>Printable audit log report</small>
                            </div>
                        </button>
                        <button type="button" class="export-option" onclick="exportAuditTrail('excel')">
                            <i data-lucide="sheet"></i>
                            <div class="opt-text">
                                <strong>Excel (.xls)</strong>
                                <small>Formatted workbook template</small>
                            </div>
                        </button>
                        <button type="button" class="export-option" onclick="exportAuditTrail('csv')">
                            <i data-lucide="file-spreadsheet"></i>
                            <div class="opt-text">
                                <strong>CSV (.csv)</strong>
                                <small>Standard comma-separated file</small>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- 4. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Audit Trail Records</span>
                    <span class="panel-count-badge" id="recordCount">6 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Immutable System Log</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="auditTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Type Name</th>
                            <th style="width: 140px;">Form Name</th>
                            <th>Log Detail</th>
                            <th style="width: 150px;">User</th>
                            <th style="width: 140px;">Machine Name</th>
                            <th style="width: 120px;">IP Address</th>
                            <th style="width: 95px;">Status</th>
                            <th style="width: 155px;">Transaction Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="auditTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Panel Footer Pagination -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 6 of 6 audit log entries</span>
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
     LOG DETAIL INSPECTION MODAL
     ========================================================================== -->
<div class="modal-backdrop" id="logDetailModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title">Audit Log Event Inspection</h3>
                <p class="modal-subtitle">Full transaction metadata and descriptive narrative.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnLogModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="modal-detail-grid">
                <div class="detail-item">
                    <label>Transaction Date</label>
                    <div class="detail-val" id="modalTransDate">—</div>
                </div>

                <div class="detail-item">
                    <label>Action Type</label>
                    <div class="detail-val" id="modalTypeName">—</div>
                </div>

                <div class="detail-item">
                    <label>Form / Page Name</label>
                    <div class="detail-val" id="modalFormName">—</div>
                </div>

                <div class="detail-item">
                    <label>Transaction Status</label>
                    <div class="detail-val" id="modalStatus">—</div>
                </div>

                <div class="detail-item">
                    <label>User Name</label>
                    <div class="detail-val" id="modalUserName">—</div>
                </div>

                <div class="detail-item">
                    <label>Machine & IP Address</label>
                    <div class="detail-val" id="modalDevice">—</div>
                </div>

                <div class="detail-item full-width">
                    <label>Log Detail & Narrative</label>
                    <div class="detail-val narrative-box" id="modalLogDetail">—</div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnLogModalClose">Close</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock Audit Log Records
let auditRecords = [
    {
        id: 1,
        type_name: 'Create',
        form_name: 'ATC Configuration',
        log_detail: 'Created new alphanumeric tax code WI100 (Rate: 10.00%, Account: 2010-01-00).',
        first_name: 'Juan',
        last_name: 'Dela Cruz',
        machine_name: 'DESKTOP-FIN-01',
        ip_address: '192.168.1.104',
        status: 'Success',
        trans_date: '2026-11-05 08:32:15'
    },
    {
        id: 2,
        type_name: 'Modify',
        form_name: 'Sales Order',
        log_detail: 'Updated sales order quantity and billing terms for customer Apex Logistics.',
        first_name: 'Maria',
        last_name: 'Santos',
        machine_name: 'LAPTOP-SALES-02',
        ip_address: '192.168.1.118',
        status: 'Success',
        trans_date: '2026-11-05 08:14:02'
    },
    {
        id: 3,
        type_name: 'Print',
        form_name: 'ATC Configuration',
        log_detail: 'Printed official Alphanumeric Tax Code Directory and generated PDF report.',
        first_name: 'Juan',
        last_name: 'Dela Cruz',
        machine_name: 'DESKTOP-FIN-01',
        ip_address: '192.168.1.104',
        status: 'Success',
        trans_date: '2026-11-05 07:45:20'
    },
    {
        id: 4,
        type_name: 'Delete',
        form_name: 'ATC Configuration',
        log_detail: 'Soft-deleted tax code WV010 and marked record as archived.',
        first_name: 'Admin',
        last_name: 'User',
        machine_name: 'SERVER-ERP-CORE',
        ip_address: '10.0.0.12',
        status: 'Success',
        trans_date: '2026-11-04 17:20:44'
    },
    {
        id: 5,
        type_name: 'Login',
        form_name: 'Authentication',
        log_detail: 'Failed login attempt with incorrect credentials for username: jdelacruz.',
        first_name: 'Unknown',
        last_name: 'User',
        machine_name: 'UNKNOWN-NODE',
        ip_address: '192.168.1.250',
        status: 'Failed',
        trans_date: '2026-11-04 16:55:10'
    },
    {
        id: 6,
        type_name: 'Generate',
        form_name: 'Production Output',
        log_detail: 'Generated weekly production summary and inventory material requisitions.',
        first_name: 'Carlos',
        last_name: 'Reyes',
        machine_name: 'PLANT-PROD-04',
        ip_address: '192.168.2.55',
        status: 'Success',
        trans_date: '2026-11-04 14:10:33'
    }
];

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderAuditTable(auditRecords);

    // Filter Elements
    const searchInput = document.getElementById('auditSearchInput');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const btnReset = document.getElementById('btnResetFilters');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');

    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');

    const logModalBackdrop = document.getElementById('logDetailModalBackdrop');
    const btnLogClose = document.getElementById('btnLogModalClose');
    const btnLogCloseCross = document.getElementById('btnLogModalCloseCross');

    // 1. Export Dropdown
    btnExportToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        exportDropdownWrapper.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!exportDropdownWrapper.contains(e.target)) {
            exportDropdownWrapper.classList.remove('active');
        }
    });

    // 2. Filter Trigger
    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        const fromVal = dateFrom.value;
        const toVal = dateTo.value;

        const filtered = auditRecords.filter(function (item) {
            // Text Search
            const combinedText = `${item.type_name} ${item.form_name} ${item.log_detail} ${item.first_name} ${item.last_name} ${item.machine_name} ${item.ip_address} ${item.status}`.toLowerCase();
            const matchesText = !query || combinedText.includes(query);

            // Date Range
            const itemDate = item.trans_date.split(' ')[0];
            const matchesFrom = !fromVal || itemDate >= fromVal;
            const matchesTo = !toVal || itemDate <= toVal;

            return matchesText && matchesFrom && matchesTo;
        });

        renderAuditTable(filtered);
    }

    [searchInput, dateFrom, dateTo].forEach(el => {
        el.addEventListener('input', applyFilters);
        el.addEventListener('change', applyFilters);
    });

    // Reset Filters
    btnReset.addEventListener('click', function () {
        searchInput.value = '';
        dateFrom.value = '';
        dateTo.value = '';
        renderAuditTable(auditRecords);
    });

    // 3. Simulated AJAX Refresh
    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        btnRefresh.classList.add('loading');

        setTimeout(function () {
            applyFilters();
            refreshIcon.classList.remove('spin-anim');
            btnRefresh.classList.remove('loading');
        }, 350);
    });

    // 4. Modal Close
    function closeLogModal() {
        logModalBackdrop.classList.remove('show');
    }
    btnLogClose.addEventListener('click', closeLogModal);
    btnLogCloseCross.addEventListener('click', closeLogModal);
    logModalBackdrop.addEventListener('click', function (e) {
        if (e.target === logModalBackdrop) closeLogModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeLogModal();
    });

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
function toggleAuditActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`auditActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(m => m.classList.remove('show'));
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Print single audit log helper
function printSingleLog(id) {
    openLogModal(id);
    setTimeout(function () {
        window.print();
    }, 300);
}

// Render Table
function renderAuditTable(dataset) {
    const tbody = document.getElementById('auditTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="9" class="empty-cell">No matching audit trail records found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${auditRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');

        // Type Pill Color
        let typeClass = 'type-default';
        if (rec.type_name === 'Create') typeClass = 'type-create';
        else if (rec.type_name === 'Modify') typeClass = 'type-modify';
        else if (rec.type_name === 'Delete') typeClass = 'type-delete';
        else if (rec.type_name === 'Print') typeClass = 'type-print';
        else if (rec.type_name === 'Generate') typeClass = 'type-generate';

        // Status Dot Pill
        const statusHtml = rec.status === 'Success'
            ? `<span class="status-pill status-success"><span class="status-dot"></span> Success</span>`
            : `<span class="status-pill status-danger"><span class="status-dot"></span> Failed</span>`;

        tr.innerHTML = `
            <td><span class="type-badge ${typeClass}">${rec.type_name}</span></td>
            <td><strong class="form-name-text">${rec.form_name}</strong></td>
            <td class="log-detail-cell" title="${rec.log_detail}">${rec.log_detail}</td>
            <td><span class="user-name-text">${rec.first_name} ${rec.last_name}</span></td>
            <td><span class="device-text">${rec.machine_name}</span></td>
            <td><span class="ip-pill">${rec.ip_address}</span></td>
            <td>${statusHtml}</td>
            <td><span class="date-cell">${rec.trans_date}</span></td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleAuditActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="auditActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="openLogModal(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View Detail</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="printSingleLog(${rec.id})">
                            <i data-lucide="printer"></i>
                            <span>Print Log</span>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('recordCount').textContent = `${dataset.length} Records`;
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${auditRecords.length} entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Open Detail Modal
function openLogModal(id) {
    const item = auditRecords.find(r => r.id === id);
    if (!item) return;

    document.getElementById('modalTransDate').textContent = item.trans_date;
    document.getElementById('modalTypeName').innerHTML = `<span class="type-badge">${item.type_name}</span>`;
    document.getElementById('modalFormName').textContent = item.form_name;
    document.getElementById('modalStatus').innerHTML = item.status === 'Success'
        ? `<span class="status-pill status-success"><span class="status-dot"></span> Success</span>`
        : `<span class="status-pill status-danger"><span class="status-dot"></span> Failed</span>`;
    document.getElementById('modalUserName').textContent = `${item.first_name} ${item.last_name}`;
    document.getElementById('modalDevice').textContent = `${item.machine_name} (${item.ip_address})`;
    document.getElementById('modalLogDetail').textContent = item.log_detail;

    document.getElementById('logDetailModalBackdrop').classList.add('show');
}

// ==========================================================================
// EXPORT & PRINT HANDLERS (PDF, EXCEL TEMPLATE, CSV)
// ==========================================================================
function exportAuditTrail(format) {
    document.getElementById('exportDropdownWrapper').classList.remove('active');

    if (format === 'pdf') {
        window.print();
    } else if (format === 'excel') {
        exportAuditExcel();
    } else if (format === 'csv') {
        exportAuditCSV();
    }
}

// Formatted Excel Template for Audit Trail (.xls)
function exportAuditExcel() {
    let rowsHtml = '';
    auditRecords.forEach(r => {
        rowsHtml += `
            <tr>
                <td style="border:1px solid #cbd5e1; padding:7px; font-weight:bold;">${r.type_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.form_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.log_detail}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.first_name} ${r.last_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.machine_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.ip_address}</td>
                <td style="border:1px solid #cbd5e1; padding:7px; color:${r.status === 'Success' ? '#047857' : '#b91c1c'}; font-weight:bold;">${r.status}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${r.trans_date}</td>
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
                    <th colspan="8" style="background:#2563eb; color:#ffffff; font-size:16px; padding:12px; text-align:left; font-weight:bold;">
                        Intracode ERP — System Audit Trail Log Report
                    </th>
                </tr>
                <tr>
                    <td colspan="8" style="padding:6px; color:#64748b; font-size:11px;">
                        Generated: ${new Date().toLocaleString()} | Total Records: ${auditRecords.length}
                    </td>
                </tr>
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Type Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Form Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Log Detail</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">User</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Machine Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">IP Address</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Status</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Transaction Date</th>
                </tr>
                ${rowsHtml}
            </table>
        </body>
        </html>`;

    downloadFile(excelTemplate, 'Audit_Trail_Report.xls', 'application/vnd.ms-excel');
}

// CSV Export
function exportAuditCSV() {
    let csv = 'Type Name,Form Name,Log Detail,First Name,Last Name,Machine Name,IP Address,Status,Transaction Date\r\n';
    auditRecords.forEach(r => {
        csv += `"${r.type_name}","${r.form_name}","${r.log_detail.replace(/"/g, '""')}","${r.first_name}","${r.last_name}","${r.machine_name}","${r.ip_address}","${r.status}","${r.trans_date}"\r\n`;
    });
    downloadFile(csv, 'Audit_Trail_Report.csv', 'text/csv;charset=utf-8;');
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

