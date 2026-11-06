@extends('layouts.app')

@section('title', 'Payment Terms — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/PaymentTerm/paymentterm.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Payment Terms</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="paymentTermSearchInput" placeholder="Search payment term, description..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Payment Terms Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload payment terms via AJAX">
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
                    <button type="button" class="export-option" onclick="exportPaymentTermDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable terms directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportPaymentTermDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportPaymentTermDirectory('csv')">
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
                <span>Create Payment Term</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Credit & Settlement Terms</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Billing Conditions for Customer Orders & Supplier Payables</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="paymentTermTable">
                    <thead>
                        <tr>
                            <th style="min-width: 180px;">Description</th>
                            <th style="min-width: 80px;" class="text-right">Days</th>
                            <th style="min-width: 80px;" class="text-right">Month</th>
                            <th style="min-width: 110px;" class="text-right">Date of Month</th>
                            <th style="min-width: 120px;" class="text-right">Days of Month</th>
                            <th style="min-width: 140px;">Due Date</th>
                            <th style="min-width: 120px;" class="text-center">For Customer</th>
                            <th style="min-width: 120px;" class="text-center">For Supplier</th>
                            <th style="min-width: 120px;">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="paymentTermTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 payment term entries</span>
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
     1. CREATE / EDIT PAYMENT TERM MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="paymentTermModalBackdrop">
    <div class="modal-dialog modal-dialog-lg" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Payment Term</h3>
                <p class="modal-subtitle">Configure maturity duration, schedule offsets, and transaction applicability.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="paymentTermForm" onsubmit="handlePaymentTermFormSubmit(event)">
            <input type="hidden" id="paymentTermFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Description -->
                <div class="form-group">
                    <label for="termDescription">Description <span class="required">*</span></label>
                    <input type="text" id="termDescription" class="form-input" placeholder="e.g. 30 Days Net" required maxlength="150">
                </div>

                <!-- Row 2: Days, Month, Date of Month, Days of Month -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="termDays">Days <span class="required">*</span></label>
                        <input type="number" id="termDays" class="form-input font-numeric" placeholder="e.g. 30" min="0" max="365" required>
                    </div>
                    <div class="form-group flex-1">
                        <label for="termMonth">Month</label>
                        <input type="number" id="termMonth" class="form-input font-numeric" placeholder="e.g. 0 or 1" min="0" max="12" value="0">
                    </div>
                    <div class="form-group flex-1">
                        <label for="termDateOfMonth">Date of Month</label>
                        <input type="number" id="termDateOfMonth" class="form-input font-numeric" placeholder="e.g. 15 or 30" min="1" max="31">
                    </div>
                    <div class="form-group flex-1">
                        <label for="termDaysOfMonth">Days of Month</label>
                        <input type="number" id="termDaysOfMonth" class="form-input font-numeric" placeholder="e.g. 30" min="1" max="31">
                    </div>
                </div>

                <!-- Row 3: Due Date Calculation Method -->
                <div class="form-group">
                    <label for="termDueDate">Due Date Rule / Narrative <span class="required">*</span></label>
                    <input type="text" id="termDueDate" class="form-input" placeholder="e.g. 30 Days from Invoice Date" required maxlength="100">
                </div>

                <!-- Row 4: Applicability Checkboxes -->
                <div class="checkbox-dual-grid">
                    <div class="form-checkbox-card">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" id="termForCustomer" class="real-checkbox">
                            <span class="custom-checkmark"><i data-lucide="check"></i></span>
                            <div class="checkbox-text-group">
                                <span class="checkbox-label-text">For Customer Transactions</span>
                                <span class="checkbox-hint-text">Applicable to Sales Orders, Billing Invoices, and Client Statements.</span>
                            </div>
                        </label>
                    </div>

                    <div class="form-checkbox-card">
                        <label class="custom-checkbox-container">
                            <input type="checkbox" id="termForSupplier" class="real-checkbox">
                            <span class="custom-checkmark"><i data-lucide="check"></i></span>
                            <div class="checkbox-text-group">
                                <span class="checkbox-label-text">For Supplier Transactions</span>
                                <span class="checkbox-hint-text">Applicable to Purchase Orders, Accounts Payable, and Vendor Bills.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Payment Term</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="paymentTermDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Term Deletion</h3>
                    <p class="modal-subtitle">Sets the record status to deleted as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete payment term <strong id="deleteTargetTermDesc" class="delete-target-text">—</strong>?
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Term</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory payment terms
let paymentTermRecords = [
    {
        id: 1,
        description: '30 Days Net',
        days: 30,
        month: 0,
        date_of_month: '—',
        days_of_month: '—',
        due_date: '30 Days from Invoice Date',
        for_customer: true,
        for_supplier: true,
        deleted_date: '—'
    },
    {
        id: 2,
        description: 'Cash On Delivery (COD)',
        days: 0,
        month: 0,
        date_of_month: '—',
        days_of_month: '—',
        due_date: 'Upon Receipt of Goods',
        for_customer: true,
        for_supplier: true,
        deleted_date: '—'
    },
    {
        id: 3,
        description: '15 Days End of Month',
        days: 15,
        month: 1,
        date_of_month: 15,
        days_of_month: 30,
        due_date: '15th of Following Month',
        for_customer: true,
        for_supplier: false,
        deleted_date: '—'
    },
    {
        id: 4,
        description: '60 Days Commercial Term',
        days: 60,
        month: 0,
        date_of_month: '—',
        days_of_month: '—',
        due_date: '60 Days from Delivery Slip',
        for_customer: false,
        for_supplier: true,
        deleted_date: '—'
    },
    {
        id: 5,
        description: '90 Days Extended (Archived)',
        days: 90,
        month: 0,
        date_of_month: '—',
        days_of_month: '—',
        due_date: '90 Days Post-Audit',
        for_customer: true,
        for_supplier: true,
        deleted_date: '2026-08-14'
    }
];

let pendingDeleteTermId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderPaymentTermTable(paymentTermRecords);

    // Modal backdrops
    const paymentTermModalBackdrop = document.getElementById('paymentTermModalBackdrop');
    const paymentTermDeleteModalBackdrop = document.getElementById('paymentTermDeleteModalBackdrop');

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
    const searchInput = document.getElementById('paymentTermSearchInput');

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
        document.getElementById('paymentTermForm').reset();
        document.getElementById('paymentTermFormId').value = '';
        document.getElementById('termForCustomer').checked = true;
        document.getElementById('termForSupplier').checked = false;
        document.getElementById('modalTitle').textContent = 'Create New Payment Term';
        paymentTermModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        paymentTermModalBackdrop.classList.remove('show');
        paymentTermDeleteModalBackdrop.classList.remove('show');
        pendingDeleteTermId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [paymentTermModalBackdrop, paymentTermDeleteModalBackdrop].forEach(function (backdrop) {
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
        if (pendingDeleteTermId !== null) {
            const item = paymentTermRecords.find(function (r) {
                return r.id === pendingDeleteTermId;
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
        const filtered = paymentTermRecords.filter(function (t) {
            const rowText = `${t.description} ${t.days} ${t.month} ${t.date_of_month} ${t.days_of_month} ${t.due_date} ${t.for_customer ? 'customer' : ''} ${t.for_supplier ? 'supplier' : ''} ${t.deleted_date}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderPaymentTermTable(filtered);
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
function togglePaymentTermActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`paymentTermActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Toggle For Customer / For Supplier Checkbox directly from table
function toggleTermScope(id, scope, isChecked) {
    const item = paymentTermRecords.find(function (r) {
        return r.id === id;
    });
    if (item) {
        if (scope === 'customer') {
            item.for_customer = isChecked;
        } else if (scope === 'supplier') {
            item.for_supplier = isChecked;
        }
    }
}

// Render Table
function renderPaymentTermTable(dataset) {
    const tbody = document.getElementById('paymentTermTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="10" class="empty-cell">No matching payment terms found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${paymentTermRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="term-desc-text ${isDeleted ? 'text-faint' : ''}">${rec.description}</strong></td>
            <td class="text-right font-numeric">${rec.days}</td>
            <td class="text-right font-numeric">${rec.month}</td>
            <td class="text-right font-numeric">${rec.date_of_month}</td>
            <td class="text-right font-numeric">${rec.days_of_month}</td>
            <td class="cell-due" title="${rec.due_date}">${rec.due_date}</td>
            <td class="text-center">
                <div class="table-checkbox-wrap">
                    <input type="checkbox" class="table-checkbox" ${rec.for_customer ? 'checked' : ''} onchange="toggleTermScope(${rec.id}, 'customer', this.checked)" title="Applicable to Customer transactions">
                </div>
            </td>
            <td class="text-center">
                <div class="table-checkbox-wrap">
                    <input type="checkbox" class="table-checkbox" ${rec.for_supplier ? 'checked' : ''} onchange="toggleTermScope(${rec.id}, 'supplier', this.checked)" title="Applicable to Supplier transactions">
                </div>
            </td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="togglePaymentTermActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="paymentTermActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewPaymentTermRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editPaymentTermRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openPaymentTermDeleteModal(${rec.id})">
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
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${paymentTermRecords.length} payment term entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Payment Term Modal
function viewPaymentTermRow(id) {
    const rec = paymentTermRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('termFormId').value = rec.id;
    document.getElementById('termDescription').value = rec.description;
    document.getElementById('termDays').value = rec.days;
    document.getElementById('termMonth').value = rec.month;
    document.getElementById('termDateOfMonth').value = rec.date_of_month;
    document.getElementById('termDaysOfMonth').value = rec.days_of_month;
    document.getElementById('termDueDate').value = rec.due_date;
    document.getElementById('termForCustomer').checked = !!rec.for_customer;
    document.getElementById('termForSupplier').checked = !!rec.for_supplier;

    document.getElementById('modalTitle').textContent = 'View Payment Term — ' + rec.description;
    document.getElementById('paymentTermModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editPaymentTermRow(id) {
    const rec = paymentTermRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('paymentTermFormId').value = rec.id;
    document.getElementById('termDescription').value = rec.description;
    document.getElementById('termDays').value = rec.days;
    document.getElementById('termMonth').value = rec.month;
    document.getElementById('termDateOfMonth').value = rec.date_of_month === '—' ? '' : rec.date_of_month;
    document.getElementById('termDaysOfMonth').value = rec.days_of_month === '—' ? '' : rec.days_of_month;
    document.getElementById('termDueDate').value = rec.due_date;
    document.getElementById('termForCustomer').checked = !!rec.for_customer;
    document.getElementById('termForSupplier').checked = !!rec.for_supplier;

    document.getElementById('modalTitle').textContent = 'Edit Payment Term — ' + rec.description;
    document.getElementById('paymentTermModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openPaymentTermDeleteModal(id) {
    const rec = paymentTermRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteTermId = id;
    document.getElementById('deleteTargetTermDesc').textContent = rec.description;
    document.getElementById('paymentTermDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handlePaymentTermFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('paymentTermFormId').value;
    const description = document.getElementById('termDescription').value.trim();
    const days = parseInt(document.getElementById('termDays').value, 10) || 0;
    const month = parseInt(document.getElementById('termMonth').value, 10) || 0;
    const date_of_month = document.getElementById('termDateOfMonth').value ? parseInt(document.getElementById('termDateOfMonth').value, 10) : '—';
    const days_of_month = document.getElementById('termDaysOfMonth').value ? parseInt(document.getElementById('termDaysOfMonth').value, 10) : '—';
    const due_date = document.getElementById('termDueDate').value.trim();
    const for_customer = document.getElementById('termForCustomer').checked;
    const for_supplier = document.getElementById('termForSupplier').checked;

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = paymentTermRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.description = description;
            existing.days = days;
            existing.month = month;
            existing.date_of_month = date_of_month;
            existing.days_of_month = days_of_month;
            existing.due_date = due_date;
            existing.for_customer = for_customer;
            existing.for_supplier = for_supplier;
        }
    } else {
        // Create new
        const newId = paymentTermRecords.length ? Math.max.apply(null, paymentTermRecords.map(function (r) { return r.id; })) + 1 : 1;
        paymentTermRecords.push({
            id: newId,
            description: description,
            days: days,
            month: month,
            date_of_month: date_of_month,
            days_of_month: days_of_month,
            due_date: due_date,
            for_customer: for_customer,
            for_supplier: for_supplier,
            deleted_date: '—'
        });
    }

    const searchInput = document.getElementById('paymentTermSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = paymentTermRecords.filter(function (t) {
        const rowText = `${t.description} ${t.days} ${t.month} ${t.date_of_month} ${t.days_of_month} ${t.due_date} ${t.for_customer ? 'customer' : ''} ${t.for_supplier ? 'supplier' : ''} ${t.deleted_date}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderPaymentTermTable(filtered);
    document.getElementById('paymentTermModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportPaymentTermDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'Description,Days,Month,Date of Month,Days of Month,Due Date,For Customer,For Supplier,Deleted Date\n';
        paymentTermRecords.forEach(function (r) {
            const cleanDesc = `"${r.description.replace(/"/g, '""')}"`;
            const cleanDue = `"${r.due_date.replace(/"/g, '""')}"`;
            const custStr = r.for_customer ? 'YES' : 'NO';
            const suppStr = r.for_supplier ? 'YES' : 'NO';
            csvContent += `${cleanDesc},${r.days},${r.month},"${r.date_of_month}","${r.days_of_month}",${cleanDue},"${custStr}","${suppStr}","${r.deleted_date}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Payment_Terms_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        paymentTermRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.description}</td>
                    <td>${r.days}</td>
                    <td>${r.month}</td>
                    <td>${r.date_of_month}</td>
                    <td>${r.days_of_month}</td>
                    <td>${r.due_date}</td>
                    <td>${r.for_customer ? 'YES' : 'NO'}</td>
                    <td>${r.for_supplier ? 'YES' : 'NO'}</td>
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
                <h2>Intracode ERP — Payment Terms Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Days</th>
                            <th>Month</th>
                            <th>Date of Month</th>
                            <th>Days of Month</th>
                            <th>Due Date</th>
                            <th>For Customer</th>
                            <th>For Supplier</th>
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
        link.download = `Payment_Terms_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

