@extends('layouts.app')

@section('title', 'Country Management — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/Country/country.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Country</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="countrySearchInput" placeholder="Search country name..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Country Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload country records via AJAX">
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
                    <button type="button" class="export-option" onclick="exportCountryDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable country list</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCountryDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCountryDirectory('csv')">
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
                <span>Create Country</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Country Directory</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Geographic & Regional Classifications</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="countryTable">
                    <thead>
                        <tr>
                            <th>Country</th>
                            <th style="width: 220px;">Deleted Date</th>
                            <th style="width: 80px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="countryTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 country entries</span>
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
     1. CREATE / EDIT COUNTRY MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="countryModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Country</h3>
                <p class="modal-subtitle">Register international territory name for address classification.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="countryForm" onsubmit="handleCountryFormSubmit(event)">
            <input type="hidden" id="countryFormId" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label for="countryName">Country Name <span class="required">*</span></label>
                    <input type="text" id="countryName" class="form-input" placeholder="e.g. Philippines" required maxlength="100">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Country</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="countryDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Country Deletion</h3>
                    <p class="modal-subtitle">Sets the record status to deleted as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete country <strong id="deleteTargetCountryName" class="delete-target-text">—</strong>?
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Country</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory country records
let countryRecords = [
    { id: 1, country: 'Philippines', deleted_date: '—' },
    { id: 2, country: 'United States', deleted_date: '—' },
    { id: 3, country: 'Japan', deleted_date: '—' },
    { id: 4, country: 'Singapore', deleted_date: '—' },
    { id: 5, country: 'Australia (Archived)', deleted_date: '2026-08-14' }
];

let pendingDeleteCountryId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderCountryTable(countryRecords);

    // Modal backdrops
    const countryModalBackdrop = document.getElementById('countryModalBackdrop');
    const countryDeleteModalBackdrop = document.getElementById('countryDeleteModalBackdrop');

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
    const searchInput = document.getElementById('countrySearchInput');

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
        document.getElementById('countryForm').reset();
        document.getElementById('countryFormId').value = '';
        document.getElementById('modalTitle').textContent = 'Create New Country';
        countryModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        countryModalBackdrop.classList.remove('show');
        countryDeleteModalBackdrop.classList.remove('show');
        pendingDeleteCountryId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [countryModalBackdrop, countryDeleteModalBackdrop].forEach(function (backdrop) {
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
        if (pendingDeleteCountryId !== null) {
            const item = countryRecords.find(function (r) {
                return r.id === pendingDeleteCountryId;
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
        const filtered = countryRecords.filter(function (c) {
            const rowText = `${c.country} ${c.deleted_date}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderCountryTable(filtered);
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
function toggleCountryActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`countryActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Country Table
function renderCountryTable(dataset) {
    const tbody = document.getElementById('countryTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="empty-cell">No matching country records found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${countryRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="country-name-text ${isDeleted ? 'text-faint' : ''}">${rec.country}</strong></td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleCountryActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="countryActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewCountryRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editCountryRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openCountryDeleteModal(${rec.id})">
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
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${countryRecords.length} country entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Country Modal
function viewCountryRow(id) {
    const rec = countryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('countryFormId').value = rec.id;
    document.getElementById('countryName').value = rec.country;

    document.getElementById('modalTitle').textContent = 'View Country — ' + rec.country;
    document.getElementById('countryModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editCountryRow(id) {
    const rec = countryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('countryFormId').value = rec.id;
    document.getElementById('countryName').value = rec.country;

    document.getElementById('modalTitle').textContent = 'Edit Country — ' + rec.country;
    document.getElementById('countryModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openCountryDeleteModal(id) {
    const rec = countryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteCountryId = id;
    document.getElementById('deleteTargetCountryName').textContent = rec.country;
    document.getElementById('countryDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handleCountryFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('countryFormId').value;
    const country = document.getElementById('countryName').value.trim();

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = countryRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.country = country;
        }
    } else {
        // Create new
        const newId = countryRecords.length ? Math.max.apply(null, countryRecords.map(function (r) { return r.id; })) + 1 : 1;
        countryRecords.push({
            id: newId,
            country: country,
            deleted_date: '—'
        });
    }

    const searchInput = document.getElementById('countrySearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = countryRecords.filter(function (c) {
        const rowText = `${c.country} ${c.deleted_date}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderCountryTable(filtered);
    document.getElementById('countryModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportCountryDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'Country,Deleted Date\n';
        countryRecords.forEach(function (r) {
            csvContent += `"${r.country}","${r.deleted_date}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Country_Directory_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        countryRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.country}</td>
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
                <h2>Intracode ERP — Country Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Country</th>
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
        link.download = `Country_Directory_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

