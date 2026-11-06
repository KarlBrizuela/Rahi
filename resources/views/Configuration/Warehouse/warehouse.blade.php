@extends('layouts.app')

@section('title', 'Warehouse Storage Facility — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/Warehouse/warehouse.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Warehouse</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="whSearchInput" placeholder="Search warehouse code, description..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Warehouse Facility Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload warehouse facilities via AJAX">
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
                    <button type="button" class="export-option" onclick="exportWarehouseDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable facility directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportWarehouseDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportWarehouseDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Warehouse Button (Opens Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Warehouse</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Storage Facilities</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Company Storage Buildings & Inventory Depots</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="whTable">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">Code</th>
                            <th style="min-width: 280px;">Description</th>
                            <th style="min-width: 140px;">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="whTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 warehouse facilities</span>
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
     1. CREATE / EDIT WAREHOUSE MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="whModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create Storage Facility</h3>
                <p class="modal-subtitle">Define warehouse code and operational plant description.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="whForm" onsubmit="handleWhFormSubmit(event)">
            <input type="hidden" id="whFormId" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label for="whCode">Warehouse Code <span class="required">*</span></label>
                    <input type="text" id="whCode" class="form-input" placeholder="e.g. WH-01" required maxlength="20" style="text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label for="whDescription">Description <span class="required">*</span></label>
                    <input type="text" id="whDescription" class="form-input" placeholder="e.g. Main Finished Goods Depot (Head Office)" required maxlength="120">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnModalCancel">Cancel</button>
                <button type="submit" class="btn-save" id="btnSubmitForm">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSubmitLabel">Save Warehouse</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. VIEW WAREHOUSE MODAL (READONLY DETAILS)
     ========================================================================== -->
<div class="modal-backdrop" id="viewModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="viewModalTitle">Warehouse Facility Profile</h3>
                <p class="modal-subtitle">Storage details and facility operational status.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnViewModalCloseCross" title="Close view modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="view-hero-box">
                <div class="view-code-tag" id="viewCode">WH-01</div>
                <div class="view-hero-text">
                    <h4 class="view-hero-heading" id="viewDescription">Main Finished Goods Depot</h4>
                    <span class="view-hero-sub">Standard ERP Storage Facility</span>
                </div>
                <span class="status-pill status-pill-active" id="viewStatusPill">Active</span>
            </div>

            <div class="view-details-grid">
                <div class="view-field-item">
                    <span class="view-field-label">Facility Code</span>
                    <span class="view-field-value font-code" id="viewFieldCode">WH-01</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Deleted / Archive Date</span>
                    <span class="view-field-value" id="viewDeletedDate">—</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnViewModalClose">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit-2" class="btn-icon"></i>
                <span>Edit Warehouse</span>
            </button>
        </div>

    </div>
</div>

<!-- ==========================================================================
     3. DELETE CONFIRMATION MODAL (ZERO BROWSER ALERTS)
     ========================================================================== -->
<div class="modal-backdrop" id="deleteConfirmBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-group">
                <div class="danger-icon-circle">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div>
                    <h3 class="modal-title" id="deleteModalTitle">Delete Warehouse</h3>
                    <p class="modal-subtitle">Confirm archiving of this facility.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to archive warehouse <strong id="deleteTargetCode">WH-01</strong> (<span id="deleteTargetDesc">Description</span>)?
            </p>
            <p class="delete-sub-text">
                The facility will be soft-deleted and marked with today's date (<span id="deleteTargetDate">2026-11-05</span>).
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCancelDelete">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDelete">
                <i data-lucide="trash-2" class="btn-icon"></i>
                <span>Confirm Delete</span>
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------------------------------
    // 1. DATA REPOSITORY & INITIAL STATE
    // -------------------------------------------------------------------------
    let whRecords = [
        {
            id: 1,
            code: 'WH-01',
            description: 'Main Finished Goods Depot (Head Office)',
            deleted_date: '—'
        },
        {
            id: 2,
            code: 'WH-02',
            description: 'Raw Materials & Chemical Storage',
            deleted_date: '—'
        },
        {
            id: 3,
            code: 'WH-03',
            description: 'Packaging & Transit Warehouse',
            deleted_date: '—'
        },
        {
            id: 4,
            code: 'WH-04',
            description: 'Quarantine & Inspection Warehouse',
            deleted_date: '—'
        },
        {
            id: 5,
            code: 'WH-05',
            description: 'Annex Storage Facility (Archived)',
            deleted_date: '2026-08-14'
        }
    ];

    let currentOpenActionMenuId = null;
    let activeDeleteId = null;
    let activeViewId = null;

    // DOM Elements
    const whTableBody = document.getElementById('whTableBody');
    const whSearchInput = document.getElementById('whSearchInput');
    const recordCountBadge = document.getElementById('recordCount');
    const footerMetaText = document.getElementById('footerMetaText');

    // Modals
    const whModalBackdrop = document.getElementById('whModalBackdrop');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');
    const btnModalCancel = document.getElementById('btnModalCancel');
    const whForm = document.getElementById('whForm');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');
    const whFormId = document.getElementById('whFormId');
    const whCode = document.getElementById('whCode');
    const whDescription = document.getElementById('whDescription');

    // View Modal
    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const btnViewModalCloseCross = document.getElementById('btnViewModalCloseCross');
    const btnViewModalClose = document.getElementById('btnViewModalClose');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');
    const viewCode = document.getElementById('viewCode');
    const viewDescription = document.getElementById('viewDescription');
    const viewFieldCode = document.getElementById('viewFieldCode');
    const viewDeletedDate = document.getElementById('viewDeletedDate');
    const viewStatusPill = document.getElementById('viewStatusPill');

    // Delete Modal
    const deleteConfirmBackdrop = document.getElementById('deleteConfirmBackdrop');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const deleteTargetCode = document.getElementById('deleteTargetCode');
    const deleteTargetDesc = document.getElementById('deleteTargetDesc');
    const deleteTargetDate = document.getElementById('deleteTargetDate');

    // Refresh & Export
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // -------------------------------------------------------------------------
    // 2. RENDER TABLE WITH 3-DOTS ACTION MENU (VIEW, EDIT, DELETE)
    // -------------------------------------------------------------------------
    function renderWhTable(items) {
        if (!whTableBody) return;
        whTableBody.innerHTML = '';

        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="4" class="table-empty-state">
                    <div class="empty-box">
                        <i data-lucide="warehouse" class="empty-icon"></i>
                        <span class="empty-text">No warehouse facilities found</span>
                        <small class="empty-subtext">Try refining your search query or create a new warehouse.</small>
                    </div>
                </td>
            `;
            whTableBody.appendChild(tr);
            if (window.lucide) window.lucide.createIcons();
            updateMetaCounts(0, whRecords.length);
            return;
        }

        items.forEach(rec => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', rec.id);

            const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
            if (isDeleted) tr.classList.add('row-deleted');

            tr.innerHTML = `
                <td>
                    <span class="code-pill ${isDeleted ? 'pill-faint' : ''}">${escapeHtml(rec.code)}</span>
                </td>
                <td class="font-medium ${isDeleted ? 'text-muted' : 'text-dark'}">${escapeHtml(rec.description)}</td>
                <td>
                    ${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}
                </td>
                <td class="text-center no-print">
                    <div class="action-dropdown-wrapper">
                        <button type="button" 
                                class="action-dots-btn" 
                                onclick="toggleWhActionMenu(event, ${rec.id})"
                                title="Actions for ${escapeHtml(rec.code)}">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="action-dropdown-menu" id="actionMenu-${rec.id}">
                            <button type="button" class="action-menu-item" onclick="openViewWhModal(${rec.id})">
                                <i data-lucide="eye"></i>
                                <span>View</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="openEditWhModal(${rec.id})">
                                <i data-lucide="edit-2"></i>
                                <span>Edit</span>
                            </button>
                            <div class="menu-divider"></div>
                            <button type="button" class="action-menu-item item-danger" onclick="openDeleteWhModal(${rec.id})">
                                <i data-lucide="trash-2"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </td>
            `;

            whTableBody.appendChild(tr);
        });

        if (window.lucide) window.lucide.createIcons();
        updateMetaCounts(items.length, whRecords.length);
    }

    function updateMetaCounts(filteredCount, totalCount) {
        if (recordCountBadge) recordCountBadge.textContent = `${filteredCount} Records`;
        if (footerMetaText) footerMetaText.textContent = `Showing 1 to ${filteredCount} of ${totalCount} warehouse facilities`;
    }

    // -------------------------------------------------------------------------
    // 3. ACTION MENU TOGGLE & SMART DROP-UP
    // -------------------------------------------------------------------------
    window.toggleWhActionMenu = function (event, recId) {
        event.stopPropagation();
        const btn = event.currentTarget;
        const targetMenu = document.getElementById(`actionMenu-${recId}`);

        if (currentOpenActionMenuId === recId) {
            closeAllActionMenus();
            return;
        }

        closeAllActionMenus();

        if (targetMenu) {
            const tr = btn.closest('tr');
            const tbody = tr ? tr.parentElement : null;
            const rows = tbody ? Array.from(tbody.querySelectorAll('tr[data-id]')) : [];
            const rowIndex = rows.indexOf(tr);
            const totalRows = rows.length;

            // Never drop up on row 0 or when total filtered rows <= 2 (prevents clipping with <thead>)
            if (rowIndex <= 0 || totalRows <= 2) {
                targetMenu.classList.remove('drop-up');
            } else if (rowIndex >= totalRows - 2) {
                targetMenu.classList.add('drop-up');
            } else {
                const btnRect = btn.getBoundingClientRect();
                const spaceBelow = window.innerHeight - btnRect.bottom;
                if (spaceBelow < 150) {
                    targetMenu.classList.add('drop-up');
                } else {
                    targetMenu.classList.remove('drop-up');
                }
            }

            targetMenu.classList.add('show');
            currentOpenActionMenuId = recId;
        }
    };

    function closeAllActionMenus() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        currentOpenActionMenuId = null;
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action-dropdown-wrapper')) closeAllActionMenus();
        if (exportDropdownWrapper && !exportDropdownWrapper.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionMenus();
            closeWhModal();
            closeViewModal();
            closeDeleteModal();
            if (exportMenu) exportMenu.classList.remove('show');
        }
    });

    // -------------------------------------------------------------------------
    // 4. SEARCH FILTERING
    // -------------------------------------------------------------------------
    if (whSearchInput) {
        whSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            closeAllActionMenus();

            if (!query) {
                renderWhTable(whRecords);
                return;
            }

            const filtered = whRecords.filter(r => {
                return r.code.toLowerCase().includes(query) ||
                       r.description.toLowerCase().includes(query);
            });

            renderWhTable(filtered);
        });
    }

    // -------------------------------------------------------------------------
    // 5. VIEW WAREHOUSE MODAL
    // -------------------------------------------------------------------------
    window.openViewWhModal = function (id) {
        closeAllActionMenus();
        const rec = whRecords.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;
        viewCode.textContent = rec.code;
        viewDescription.textContent = rec.description;
        viewFieldCode.textContent = rec.code;

        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
        viewDeletedDate.textContent = isDeleted ? rec.deleted_date : '—';
        viewStatusPill.className = `status-pill ${isDeleted ? 'status-pill-archived' : 'status-pill-active'}`;
        viewStatusPill.textContent = isDeleted ? 'Archived' : 'Active';

        viewModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    function closeViewModal() {
        if (viewModalBackdrop) viewModalBackdrop.classList.remove('show');
        activeViewId = null;
    }

    if (btnViewModalCloseCross) btnViewModalCloseCross.addEventListener('click', closeViewModal);
    if (btnViewModalClose) btnViewModalClose.addEventListener('click', closeViewModal);
    if (btnViewEditShortcut) {
        btnViewEditShortcut.addEventListener('click', function () {
            if (activeViewId) {
                const targetId = activeViewId;
                closeViewModal();
                openEditWhModal(targetId);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 6. CREATE / EDIT WAREHOUSE MODAL
    // -------------------------------------------------------------------------
    function openCreateWhModal() {
        closeAllActionMenus();
        whForm.reset();
        whFormId.value = '';
        modalTitle.textContent = 'Create Storage Facility';
        btnSubmitLabel.textContent = 'Save Warehouse';
        whModalBackdrop.classList.add('show');
        whCode.focus();
        if (window.lucide) window.lucide.createIcons();
    }

    window.openEditWhModal = function (id) {
        closeAllActionMenus();
        const rec = whRecords.find(r => r.id === id);
        if (!rec) return;

        whForm.reset();
        whFormId.value = rec.id;
        modalTitle.textContent = `Edit Warehouse: ${rec.code}`;
        btnSubmitLabel.textContent = 'Update Warehouse';

        whCode.value = rec.code;
        whDescription.value = rec.description;

        whModalBackdrop.classList.add('show');
        whCode.focus();
        if (window.lucide) window.lucide.createIcons();
    };

    function closeWhModal() {
        if (whModalBackdrop) whModalBackdrop.classList.remove('show');
    }

    if (btnOpenCreateModal) btnOpenCreateModal.addEventListener('click', openCreateWhModal);
    if (btnModalCloseCross) btnModalCloseCross.addEventListener('click', closeWhModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeWhModal);

    window.handleWhFormSubmit = function (event) {
        event.preventDefault();

        const idVal = whFormId.value;
        const code = whCode.value.trim().toUpperCase();
        const desc = whDescription.value.trim();

        if (!code || !desc) return;

        if (idVal) {
            const index = whRecords.findIndex(r => r.id === parseInt(idVal, 10));
            if (index !== -1) {
                whRecords[index].code = code;
                whRecords[index].description = desc;
            }
        } else {
            const nextId = whRecords.length > 0 ? Math.max(...whRecords.map(r => r.id)) + 1 : 1;
            const newRec = {
                id: nextId,
                code: code,
                description: desc,
                deleted_date: '—'
            };
            whRecords.unshift(newRec);
        }

        closeWhModal();
        if (whSearchInput) whSearchInput.value = '';
        renderWhTable(whRecords);
    };

    // -------------------------------------------------------------------------
    // 7. DELETE CONFIRMATION MODAL (SOFT DELETE)
    // -------------------------------------------------------------------------
    window.openDeleteWhModal = function (id) {
        closeAllActionMenus();
        const rec = whRecords.find(r => r.id === id);
        if (!rec) return;

        activeDeleteId = id;
        const today = new Date().toISOString().split('T')[0];

        deleteTargetCode.textContent = rec.code;
        deleteTargetDesc.textContent = rec.description;
        deleteTargetDate.textContent = today;

        deleteConfirmBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    function closeDeleteModal() {
        if (deleteConfirmBackdrop) deleteConfirmBackdrop.classList.remove('show');
        activeDeleteId = null;
    }

    if (btnDeleteCloseCross) btnDeleteCloseCross.addEventListener('click', closeDeleteModal);
    if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDeleteModal);

    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function () {
            if (!activeDeleteId) return;

            const rec = whRecords.find(r => r.id === activeDeleteId);
            if (rec) {
                const today = new Date().toISOString().split('T')[0];
                rec.deleted_date = today;
            }

            closeDeleteModal();
            if (whSearchInput) whSearchInput.value = '';
            renderWhTable(whRecords);
        });
    }

    // -------------------------------------------------------------------------
    // 8. REFRESH & EXPORT ACTIONS
    // -------------------------------------------------------------------------
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            closeAllActionMenus();
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            btnRefresh.disabled = true;

            setTimeout(() => {
                if (whSearchInput) whSearchInput.value = '';
                renderWhTable(whRecords);
                if (refreshIcon) refreshIcon.classList.remove('spin-anim');
                btnRefresh.disabled = false;
            }, 400);
        });
    }

    if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAllActionMenus();
            exportMenu.classList.toggle('show');
        });
    }

    window.exportWarehouseDirectory = function (format) {
        exportMenu.classList.remove('show');

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csvContent = 'Code,Description,Deleted Date\n';
            whRecords.forEach(r => {
                csvContent += `"${r.code}","${r.description}","${r.deleted_date}"\n`;
            });
            downloadFile(csvContent, 'warehouse-directory.csv', 'text/csv;charset=utf-8;');
        } else if (format === 'excel') {
            let tableHtml = '<table border="1"><thead><tr><th>Code</th><th>Description</th><th>Deleted Date</th></tr></thead><tbody>';
            whRecords.forEach(r => {
                tableHtml += `<tr><td>${r.code}</td><td>${r.description}</td><td>${r.deleted_date}</td></tr>`;
            });
            tableHtml += '</tbody></table>';
            downloadFile(tableHtml, 'warehouse-directory.xls', 'application/vnd.ms-excel');
        }
    };

    function downloadFile(content, fileName, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // -------------------------------------------------------------------------
    // 9. INITIALIZATION
    // -------------------------------------------------------------------------
    renderWhTable(whRecords);
});
</script>
@endpush

@endsection

