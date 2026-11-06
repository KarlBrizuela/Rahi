@extends('layouts.app')

@section('title', 'Value Added Tax (VAT) — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/VAT/vat.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">VAT</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="vatSearchInput" placeholder="Search VAT code, description, type..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Value Added Tax (VAT) Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload VAT records via AJAX">
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
                    <button type="button" class="export-option" onclick="exportVatDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable VAT directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportVatDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportVatDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create VAT Button (Opens Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create VAT</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Tax Classification Codes</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Statutory VAT Rates & Account Mappings</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="vatTable">
                    <thead>
                        <tr>
                            <th style="min-width: 90px;">Code</th>
                            <th style="min-width: 200px;">Description</th>
                            <th style="min-width: 100px;">Type</th>
                            <th style="min-width: 120px;">Class</th>
                            <th style="min-width: 100px;" class="text-right">Rate% Prod</th>
                            <th style="min-width: 110px;">VAT Ind.</th>
                            <th style="min-width: 120px;">Account Code</th>
                            <th style="min-width: 120px;">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="vatTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 tax entries</span>
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
     1. CREATE / EDIT VAT MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="vatModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create Value Added Tax (VAT)</h3>
                <p class="modal-subtitle">Configure tax code, classification, percentage rate, and general ledger account mapping.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="vatForm" onsubmit="handleVatFormSubmit(event)">
            <input type="hidden" id="vatFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Code & Description -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="vatCode">VAT Code <span class="required">*</span></label>
                        <input type="text" id="vatCode" class="form-input" placeholder="e.g. V001" required maxlength="20" style="text-transform: uppercase;">
                    </div>
                    <div class="form-group flex-2">
                        <label for="vatDescription">Description <span class="required">*</span></label>
                        <input type="text" id="vatDescription" class="form-input" placeholder="e.g. VAT on Local Purchases of Goods" required maxlength="120">
                    </div>
                </div>

                <!-- Row 2: Type & Class -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="vatType">Type <span class="required">*</span></label>
                        <select id="vatType" class="form-select" required>
                            <option value="VAT Input">VAT Input</option>
                            <option value="VAT Output">VAT Output</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="vatClass">Class <span class="required">*</span></label>
                        <input type="text" id="vatClass" class="form-input" placeholder="e.g. Standard, Services, Goods" required maxlength="50">
                    </div>
                </div>

                <!-- Row 3: Rate% Prod, VAT Indicator, Account Code -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="vatRate">Rate (%) Prod <span class="required">*</span></label>
                        <input type="number" id="vatRate" class="form-input font-numeric" placeholder="12.00" step="0.01" min="0" max="100" value="12.00" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="vatIndicator">VAT Indicator <span class="required">*</span></label>
                        <select id="vatIndicator" class="form-select" required>
                            <option value="Taxable">Taxable</option>
                            <option value="Zero-Rated">Zero-Rated</option>
                            <option value="Exempt">Exempt</option>
                            <option value="Non-Taxable">Non-Taxable</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="vatAccountCode">Account Code <span class="required">*</span></label>
                        <input type="text" id="vatAccountCode" class="form-input font-code" placeholder="e.g. 110-20-00" required maxlength="30">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnModalCancel">Cancel</button>
                <button type="submit" class="btn-save" id="btnSubmitForm">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSubmitLabel">Save VAT</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. VIEW VAT MODAL (READONLY DETAILS)
     ========================================================================== -->
<div class="modal-backdrop" id="viewModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="viewModalTitle">VAT Code Details</h3>
                <p class="modal-subtitle">Review statutory tax classification parameters and financial account mapping.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnViewModalCloseCross" title="Close view modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <div class="view-hero-box">
                <div class="view-code-tag" id="viewCode">V001</div>
                <div class="view-hero-text">
                    <h4 class="view-hero-heading" id="viewDescription">VAT on Local Purchases of Goods</h4>
                    <span class="view-hero-sub" id="viewTypeClass">Type: Input &bull; Class: Standard</span>
                </div>
                <span class="status-pill status-pill-active" id="viewStatusPill">Active</span>
            </div>

            <div class="view-details-grid">
                <div class="view-field-item">
                    <span class="view-field-label">Rate (%) Prod</span>
                    <span class="view-field-value font-numeric" id="viewRate">12.00%</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">VAT Indicator</span>
                    <span class="view-field-value" id="viewIndicator">Taxable</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Account Code</span>
                    <span class="view-field-value font-code" id="viewAccountCode">110-20-00</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Archived / Deleted Date</span>
                    <span class="view-field-value" id="viewDeletedDate">—</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnViewModalClose">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit-2" class="btn-icon"></i>
                <span>Edit VAT</span>
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
                    <h3 class="modal-title" id="deleteModalTitle">Delete VAT Code</h3>
                    <p class="modal-subtitle">This action will soft-delete and archive this tax code record.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete VAT record <strong id="deleteTargetCode">V001</strong> (<span id="deleteTargetDesc">Description</span>)?
            </p>
            <p class="delete-sub-text">
                Archived tax codes remain visible in audit histories and will be marked with today's date (<span id="deleteTargetDate">2026-11-05</span>).
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
    let vatRecords = [
        {
            id: 1,
            code: 'V001',
            description: 'VAT on Local Purchases of Goods',
            type: 'VAT Input',
            class_name: 'Standard',
            rate: 12.00,
            indicator: 'Taxable',
            account_code: '110-20-00',
            deleted_date: '—'
        },
        {
            id: 2,
            code: 'V002',
            description: 'VAT on Local Purchases of Services',
            type: 'VAT Input',
            class_name: 'Services',
            rate: 12.00,
            indicator: 'Taxable',
            account_code: '110-20-01',
            deleted_date: '—'
        },
        {
            id: 3,
            code: 'V003',
            description: 'VAT on Sales of Goods (Output Tax)',
            type: 'VAT Output',
            class_name: 'Goods',
            rate: 12.00,
            indicator: 'Taxable',
            account_code: '220-10-00',
            deleted_date: '—'
        },
        {
            id: 4,
            code: 'V004',
            description: 'VAT on Sales of Services (Output Tax)',
            type: 'VAT Output',
            class_name: 'Services',
            rate: 12.00,
            indicator: 'Taxable',
            account_code: '220-10-01',
            deleted_date: '—'
        },
        {
            id: 5,
            code: 'V005',
            description: 'VAT on Capital Goods Purchases (Archived)',
            type: 'VAT Input',
            class_name: 'Capital Assets',
            rate: 12.00,
            indicator: 'Taxable',
            account_code: '110-20-02',
            deleted_date: '2026-08-14'
        }
    ];

    let currentOpenActionMenuId = null;
    let activeDeleteId = null;
    let activeViewId = null;

    // DOM Elements
    const vatTableBody = document.getElementById('vatTableBody');
    const vatSearchInput = document.getElementById('vatSearchInput');
    const recordCountBadge = document.getElementById('recordCount');
    const footerMetaText = document.getElementById('footerMetaText');

    // Modals
    const vatModalBackdrop = document.getElementById('vatModalBackdrop');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');
    const btnModalCancel = document.getElementById('btnModalCancel');
    const vatForm = document.getElementById('vatForm');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');
    const vatFormId = document.getElementById('vatFormId');
    const vatCode = document.getElementById('vatCode');
    const vatDescription = document.getElementById('vatDescription');
    const vatType = document.getElementById('vatType');
    const vatClass = document.getElementById('vatClass');
    const vatRate = document.getElementById('vatRate');
    const vatIndicator = document.getElementById('vatIndicator');
    const vatAccountCode = document.getElementById('vatAccountCode');

    // View Modal
    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const btnViewModalCloseCross = document.getElementById('btnViewModalCloseCross');
    const btnViewModalClose = document.getElementById('btnViewModalClose');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');
    const viewCode = document.getElementById('viewCode');
    const viewDescription = document.getElementById('viewDescription');
    const viewTypeClass = document.getElementById('viewTypeClass');
    const viewRate = document.getElementById('viewRate');
    const viewIndicator = document.getElementById('viewIndicator');
    const viewAccountCode = document.getElementById('viewAccountCode');
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
    function renderVatTable(items) {
        if (!vatTableBody) return;
        vatTableBody.innerHTML = '';

        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="9" class="table-empty-state">
                    <div class="empty-box">
                        <i data-lucide="percent" class="empty-icon"></i>
                        <span class="empty-text">No VAT tax records found</span>
                        <small class="empty-subtext">Try refining your search query or configure a new VAT code.</small>
                    </div>
                </td>
            `;
            vatTableBody.appendChild(tr);
            if (window.lucide) window.lucide.createIcons();
            updateMetaCounts(0, vatRecords.length);
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
                    <span class="type-pill ${rec.type.toLowerCase().includes('input') ? 'input' : 'output'}">${escapeHtml(rec.type)}</span>
                </td>
                <td class="${isDeleted ? 'text-muted' : ''}">${escapeHtml(rec.class_name)}</td>
                <td class="text-right font-numeric font-medium">${parseFloat(rec.rate).toFixed(2)}%</td>
                <td>
                    <span class="indicator-tag">${escapeHtml(rec.indicator)}</span>
                </td>
                <td>
                    <span class="account-code-pill font-code">${escapeHtml(rec.account_code)}</span>
                </td>
                <td>
                    ${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}
                </td>
                <td class="text-center no-print">
                    <div class="action-dropdown-wrapper">
                        <button type="button" 
                                class="action-dots-btn" 
                                onclick="toggleVatActionMenu(event, ${rec.id})"
                                title="Actions for ${escapeHtml(rec.code)}">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="action-dropdown-menu" id="actionMenu-${rec.id}">
                            <button type="button" class="action-menu-item" onclick="openViewVatModal(${rec.id})">
                                <i data-lucide="eye"></i>
                                <span>View</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="openEditVatModal(${rec.id})">
                                <i data-lucide="edit-2"></i>
                                <span>Edit</span>
                            </button>
                            <div class="menu-divider"></div>
                            <button type="button" class="action-menu-item item-danger" onclick="openDeleteVatModal(${rec.id})">
                                <i data-lucide="trash-2"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </td>
            `;

            vatTableBody.appendChild(tr);
        });

        if (window.lucide) window.lucide.createIcons();
        updateMetaCounts(items.length, vatRecords.length);
    }

    function updateMetaCounts(filteredCount, totalCount) {
        if (recordCountBadge) recordCountBadge.textContent = `${filteredCount} Records`;
        if (footerMetaText) footerMetaText.textContent = `Showing 1 to ${filteredCount} of ${totalCount} tax entries`;
    }

    // -------------------------------------------------------------------------
    // 3. ACTION MENU TOGGLE & SMART DROP-UP
    // -------------------------------------------------------------------------
    window.toggleVatActionMenu = function (event, recId) {
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
            closeVatModal();
            closeViewModal();
            closeDeleteModal();
            if (exportMenu) exportMenu.classList.remove('show');
        }
    });

    // -------------------------------------------------------------------------
    // 4. SEARCH FILTERING
    // -------------------------------------------------------------------------
    if (vatSearchInput) {
        vatSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            closeAllActionMenus();

            if (!query) {
                renderVatTable(vatRecords);
                return;
            }

            const filtered = vatRecords.filter(r => {
                return r.code.toLowerCase().includes(query) ||
                       r.description.toLowerCase().includes(query) ||
                       r.type.toLowerCase().includes(query) ||
                       r.class_name.toLowerCase().includes(query) ||
                       r.indicator.toLowerCase().includes(query) ||
                       r.account_code.toLowerCase().includes(query);
            });

            renderVatTable(filtered);
        });
    }

    // -------------------------------------------------------------------------
    // 5. VIEW VAT MODAL
    // -------------------------------------------------------------------------
    window.openViewVatModal = function (id) {
        closeAllActionMenus();
        const rec = vatRecords.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;
        viewCode.textContent = rec.code;
        viewDescription.textContent = rec.description;
        viewTypeClass.textContent = `Type: ${rec.type} • Class: ${rec.class_name}`;
        viewRate.textContent = `${parseFloat(rec.rate).toFixed(2)}%`;
        viewIndicator.textContent = rec.indicator;
        viewAccountCode.textContent = rec.account_code;

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
                openEditVatModal(targetId);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 6. CREATE / EDIT VAT MODAL
    // -------------------------------------------------------------------------
    function openCreateVatModal() {
        closeAllActionMenus();
        vatForm.reset();
        vatFormId.value = '';
        modalTitle.textContent = 'Create Value Added Tax (VAT)';
        btnSubmitLabel.textContent = 'Save VAT';
        vatModalBackdrop.classList.add('show');
        vatCode.focus();
        if (window.lucide) window.lucide.createIcons();
    }

    window.openEditVatModal = function (id) {
        closeAllActionMenus();
        const rec = vatRecords.find(r => r.id === id);
        if (!rec) return;

        vatForm.reset();
        vatFormId.value = rec.id;
        modalTitle.textContent = `Edit VAT Code: ${rec.code}`;
        btnSubmitLabel.textContent = 'Update VAT';

        vatCode.value = rec.code;
        vatDescription.value = rec.description;
        vatType.value = rec.type;
        vatClass.value = rec.class_name;
        vatRate.value = parseFloat(rec.rate);
        vatIndicator.value = rec.indicator;
        vatAccountCode.value = rec.account_code;

        vatModalBackdrop.classList.add('show');
        vatCode.focus();
        if (window.lucide) window.lucide.createIcons();
    };

    function closeVatModal() {
        if (vatModalBackdrop) vatModalBackdrop.classList.remove('show');
    }

    if (btnOpenCreateModal) btnOpenCreateModal.addEventListener('click', openCreateVatModal);
    if (btnModalCloseCross) btnModalCloseCross.addEventListener('click', closeVatModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeVatModal);

    window.handleVatFormSubmit = function (event) {
        event.preventDefault();

        const idVal = vatFormId.value;
        const code = vatCode.value.trim().toUpperCase();
        const desc = vatDescription.value.trim();
        const type = vatType.value;
        const className = vatClass.value.trim();
        const rate = parseFloat(vatRate.value) || 0;
        const indicator = vatIndicator.value;
        const acct = vatAccountCode.value.trim();

        if (!code || !desc || !acct) return;

        if (idVal) {
            const index = vatRecords.findIndex(r => r.id === parseInt(idVal, 10));
            if (index !== -1) {
                vatRecords[index].code = code;
                vatRecords[index].description = desc;
                vatRecords[index].type = type;
                vatRecords[index].class_name = className;
                vatRecords[index].rate = rate;
                vatRecords[index].indicator = indicator;
                vatRecords[index].account_code = acct;
            }
        } else {
            const nextId = vatRecords.length > 0 ? Math.max(...vatRecords.map(r => r.id)) + 1 : 1;
            const newRec = {
                id: nextId,
                code: code,
                description: desc,
                type: type,
                class_name: className,
                rate: rate,
                indicator: indicator,
                account_code: acct,
                deleted_date: '—'
            };
            vatRecords.unshift(newRec);
        }

        closeVatModal();
        if (vatSearchInput) vatSearchInput.value = '';
        renderVatTable(vatRecords);
    };

    // -------------------------------------------------------------------------
    // 7. DELETE CONFIRMATION MODAL (SOFT DELETE)
    // -------------------------------------------------------------------------
    window.openDeleteVatModal = function (id) {
        closeAllActionMenus();
        const rec = vatRecords.find(r => r.id === id);
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

            const rec = vatRecords.find(r => r.id === activeDeleteId);
            if (rec) {
                const today = new Date().toISOString().split('T')[0];
                rec.deleted_date = today;
            }

            closeDeleteModal();
            if (vatSearchInput) vatSearchInput.value = '';
            renderVatTable(vatRecords);
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
                if (vatSearchInput) vatSearchInput.value = '';
                renderVatTable(vatRecords);
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

    window.exportVatDirectory = function (format) {
        exportMenu.classList.remove('show');

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csvContent = 'Code,Description,Type,Class,Rate% Prod,VAT Ind.,Account Code,Deleted Date\n';
            vatRecords.forEach(r => {
                csvContent += `"${r.code}","${r.description}","${r.type}","${r.class_name}","${r.rate}%","${r.indicator}","${r.account_code}","${r.deleted_date}"\n`;
            });
            downloadFile(csvContent, 'vat-directory.csv', 'text/csv;charset=utf-8;');
        } else if (format === 'excel') {
            let tableHtml = '<table border="1"><thead><tr><th>Code</th><th>Description</th><th>Type</th><th>Class</th><th>Rate% Prod</th><th>VAT Ind.</th><th>Account Code</th><th>Deleted Date</th></tr></thead><tbody>';
            vatRecords.forEach(r => {
                tableHtml += `<tr><td>${r.code}</td><td>${r.description}</td><td>${r.type}</td><td>${r.class_name}</td><td>${r.rate}%</td><td>${r.indicator}</td><td>${r.account_code}</td><td>${r.deleted_date}</td></tr>`;
            });
            tableHtml += '</tbody></table>';
            downloadFile(tableHtml, 'vat-directory.xls', 'application/vnd.ms-excel');
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
    renderVatTable(vatRecords);
});
</script>
@endpush

@endsection

