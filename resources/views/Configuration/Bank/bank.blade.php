@extends('layouts.app')

@section('title', 'Bank Accounts — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/Bank/bank.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Bank Accounts</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="bankSearchInput" placeholder="Search bank name, account no, branch..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Bank Directory & Accounts</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload bank records via AJAX">
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
                    <button type="button" class="export-option" onclick="exportBankDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable bank account directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportBankDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportBankDirectory('csv')">
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
                <span>Create Bank</span>
            </button>
        </div>
    </section>

    <!-- 4. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Registered Bank Accounts</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">General Ledger Connected</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="bankTable">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">Bank Name</th>
                            <th style="min-width: 85px;">Shortname</th>
                            <th style="min-width: 140px;">Account No</th>
                            <th style="min-width: 190px;">Account Name</th>
                            <th style="min-width: 110px;">SWIFT Code</th>
                            <th style="min-width: 150px;">Branch Name</th>
                            <th style="min-width: 180px;">Address</th>
                            <th style="min-width: 120px;">Account Type</th>
                            <th style="min-width: 80px;">Currency</th>
                            <th style="min-width: 200px;">Account (COA)</th>
                            <th style="min-width: 110px;">Deleted Date</th>
                            <th style="width: 110px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bankTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 bank entries</span>
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
     1. CREATE / EDIT BANK MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="bankModalBackdrop">
    <div class="modal-dialog modal-dialog-lg" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Bank Account</h3>
                <p class="modal-subtitle">Register institutional bank details and financial chart of accounts linkage.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="bankForm" onsubmit="handleBankFormSubmit(event)">
            <input type="hidden" id="bankFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Name & Shortname -->
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="bankName">Bank Name <span class="required">*</span></label>
                        <input type="text" id="bankName" class="form-input" placeholder="e.g. Banco de Oro Unibank, Inc." required maxlength="150">
                    </div>
                    <div class="form-group flex-1">
                        <label for="bankShortname">Shortname <span class="required">*</span></label>
                        <input type="text" id="bankShortname" class="form-input" placeholder="e.g. BDO" required maxlength="20">
                    </div>
                </div>

                <!-- Row 2: Account No & Account Name -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="bankAccountNo">Account No <span class="required">*</span></label>
                        <input type="text" id="bankAccountNo" class="form-input font-numeric" placeholder="e.g. 0012-3456-7890" required maxlength="50">
                    </div>
                    <div class="form-group flex-1">
                        <label for="bankAccountName">Account Name <span class="required">*</span></label>
                        <input type="text" id="bankAccountName" class="form-input" placeholder="e.g. Intracode IT Solutions Inc." required maxlength="150">
                    </div>
                </div>

                <!-- Row 3: SWIFT Code & Branch Name -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="bankSwift">SWIFT Code <span class="required">*</span></label>
                        <input type="text" id="bankSwift" class="form-input" placeholder="e.g. BNORPHMM" required maxlength="30">
                    </div>
                    <div class="form-group flex-1">
                        <label for="bankBranchName">Branch Name <span class="required">*</span></label>
                        <input type="text" id="bankBranchName" class="form-input" placeholder="e.g. Makati Main Branch" required maxlength="100">
                    </div>
                </div>

                <!-- Row 4: Address -->
                <div class="form-group">
                    <label for="bankAddress">Branch Address <span class="required">*</span></label>
                    <input type="text" id="bankAddress" class="form-input" placeholder="e.g. Makati Ave. cor. Ayala Ave., Makati City" required maxlength="255">
                </div>

                <!-- Row 5: Account Type & Currency -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="bankAccountType">Account Type <span class="required">*</span></label>
                        <select id="bankAccountType" class="form-input" required>
                            <option value="">Select Account Type</option>
                            <option value="Checking Account">Checking Account</option>
                            <option value="Savings Account">Savings Account</option>
                            <option value="Time Deposit">Time Deposit</option>
                            <option value="Payroll Account">Payroll Account</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="bankCurrency">Currency <span class="required">*</span></label>
                        <select id="bankCurrency" class="form-input" required>
                            <option value="PHP">PHP — Philippine Peso</option>
                            <option value="USD">USD — US Dollar</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="JPY">JPY — Japanese Yen</option>
                        </select>
                    </div>
                </div>

                <!-- Row 6: Linked Account (Chart of Accounts) -->
                <div class="form-group">
                    <label for="bankCoaAccount">Account (Linked Chart of Accounts Title) <span class="required">*</span></label>
                    <input type="text" id="bankCoaAccount" class="form-input" placeholder="e.g. 1010-01 Cash in Bank - BDO Operating" required maxlength="150">
                    <small class="form-helper">The linked Account Title from the Accounting Chart of Accounts, ensuring proper posting of transactions.</small>
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
<div class="modal-backdrop" id="bankDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title">Confirm Bank Record Deletion</h3>
                    <p class="modal-subtitle">This will set the record's deleted status as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-confirmation-text">
                Are you sure you want to delete the bank account record for:
            </p>
            <div class="delete-target-preview">
                <strong id="deleteTargetBankName">—</strong>
                <span id="deleteTargetAccountNo">—</span>
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
     3. SINGLE ITEM PRINT VOUCHER MODAL
     ========================================================================== -->
<div class="modal-backdrop" id="bankPrintModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true">

        <div class="modal-header no-print">
            <div class="modal-title-group">
                <h3 class="modal-title">Bank Account Details Slip</h3>
                <p class="modal-subtitle">Official Banking Channel Record Sheet</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnPrintSlipCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body print-slip-body">
            <div class="printable-voucher" id="bankPrintVoucher">
                <div class="voucher-header">
                    <div class="voucher-brand">
                        <img src="{{ asset('fav/fav.png') }}" alt="Logo" class="voucher-logo">
                        <div>
                            <h4>Intracode IT Solutions</h4>
                            <small>Enterprise Financial Bank Directory</small>
                        </div>
                    </div>
                    <div class="voucher-meta">
                        <span class="voucher-badge">Bank Record Sheet</span>
                        <div class="voucher-date">Generated: <span id="voucherDate">—</span></div>
                    </div>
                </div>

                <div class="voucher-divider"></div>

                <div class="voucher-grid">
                    <div class="voucher-field">
                        <label>Bank Name</label>
                        <div class="field-val highlight" id="voucherBankName">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Shortname</label>
                        <div class="field-val" id="voucherShortname">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Account Number</label>
                        <div class="field-val font-numeric" id="voucherAccountNo">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Account Name</label>
                        <div class="field-val" id="voucherAccountName">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>SWIFT Code</label>
                        <div class="field-val" id="voucherSwift">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Branch Name</label>
                        <div class="field-val" id="voucherBranchName">—</div>
                    </div>

                    <div class="voucher-field full-width">
                        <label>Branch Address</label>
                        <div class="field-val" id="voucherAddress">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Account Type</label>
                        <div class="field-val" id="voucherAccountType">—</div>
                    </div>

                    <div class="voucher-field">
                        <label>Currency</label>
                        <div class="field-val" id="voucherCurrency">—</div>
                    </div>

                    <div class="voucher-field full-width">
                        <label>Linked Chart of Accounts Title</label>
                        <div class="field-val" id="voucherCoaAccount">—</div>
                    </div>

                    <div class="voucher-field full-width">
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
            <button type="button" class="btn-outline" id="btnPrintSlipClose">Close</button>
            <button type="button" class="btn-outline" id="btnSingleExportExcel">
                <i data-lucide="sheet" class="btn-icon"></i>
                <span>Download Excel</span>
            </button>
            <button type="button" class="btn-primary" id="btnSinglePrintNow">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Record</span>
            </button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory Bank records
let bankRecords = [
    {
        id: 1,
        name: 'Banco de Oro Unibank, Inc.',
        shortname: 'BDO',
        account_no: '0012-3456-7890',
        account_name: 'Intracode IT Solutions Inc.',
        swift_code: 'BNORPHMM',
        branch_name: 'Makati Main Branch',
        address: 'Makati Ave. cor. Ayala Ave., Makati City',
        account_type: 'Checking Account',
        currency: 'PHP',
        coa_account: '1010-01 Cash in Bank - BDO Operating',
        deleted_date: '—'
    },
    {
        id: 2,
        name: 'Bank of the Philippine Islands',
        shortname: 'BPI',
        account_no: '0021-9876-5432',
        account_name: 'Intracode IT Solutions Inc.',
        swift_code: 'BOPIPHMM',
        branch_name: 'BGC High Street Branch',
        address: '7th Ave. cor. 28th St., Bonifacio Global City, Taguig',
        account_type: 'Checking Account',
        currency: 'PHP',
        coa_account: '1010-02 Cash in Bank - BPI Payroll',
        deleted_date: '—'
    },
    {
        id: 3,
        name: 'Metrobank (Metropolitan Bank & Trust Co.)',
        shortname: 'MBTC',
        account_no: '0035-1122-3344',
        account_name: 'Intracode IT Solutions Inc.',
        swift_code: 'MBTCPHMM',
        branch_name: 'Ortigas Center Branch',
        address: 'ADB Ave. cor. Julia Vargas, Pasig City',
        account_type: 'Savings Account',
        currency: 'USD',
        coa_account: '1010-03 Cash in Bank - MBTC Dollar Reserve',
        deleted_date: '—'
    },
    {
        id: 4,
        name: 'Security Bank Corporation',
        shortname: 'SBC',
        account_no: '0048-5566-7788',
        account_name: 'Intracode IT Solutions Inc. (Archived)',
        swift_code: 'SETCPHMM',
        branch_name: 'Alabang Madrigal Branch',
        address: 'Madrigal Business Park, Ayala Alabang, Muntinlupa',
        account_type: 'Checking Account',
        currency: 'PHP',
        coa_account: '1010-04 Cash in Bank - SBC General',
        deleted_date: '2026-10-15'
    }
];

let pendingDeleteBankId = null;
let currentPreviewBankItem = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderBankTable(bankRecords);

    // Modal backdrops
    const bankModalBackdrop = document.getElementById('bankModalBackdrop');
    const bankDeleteModalBackdrop = document.getElementById('bankDeleteModalBackdrop');
    const bankPrintModalBackdrop = document.getElementById('bankPrintModalBackdrop');

    // Action buttons
    const btnOpenCreate = document.getElementById('btnOpenCreateModal');
    const btnModalClose = document.getElementById('btnModalClose');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');

    const btnDeleteCancel = document.getElementById('btnDeleteCancel');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnConfirmDeleteAction = document.getElementById('btnConfirmDeleteAction');

    const btnPrintSlipClose = document.getElementById('btnPrintSlipClose');
    const btnPrintSlipCloseCross = document.getElementById('btnPrintSlipCloseCross');
    const btnSinglePrintNow = document.getElementById('btnSinglePrintNow');
    const btnSingleExportExcel = document.getElementById('btnSingleExportExcel');

    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const searchInput = document.getElementById('bankSearchInput');

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
        document.getElementById('bankForm').reset();
        document.getElementById('bankFormId').value = '';
        document.getElementById('modalTitle').textContent = 'Create New Bank Account';
        bankModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        bankModalBackdrop.classList.remove('show');
        bankDeleteModalBackdrop.classList.remove('show');
        bankPrintModalBackdrop.classList.remove('show');
        pendingDeleteBankId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross, btnPrintSlipClose, btnPrintSlipCloseCross].forEach(btn => {
        btn.addEventListener('click', closeAllModals);
    });

    [bankModalBackdrop, bankDeleteModalBackdrop, bankPrintModalBackdrop].forEach(backdrop => {
        backdrop.addEventListener('click', function (e) {
            if (e.target === backdrop) closeAllModals();
        });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // 3. Confirm Delete Action
    btnConfirmDeleteAction.addEventListener('click', function () {
        if (pendingDeleteBankId !== null) {
            const item = bankRecords.find(r => r.id === pendingDeleteBankId);
            if (item) {
                const today = new Date().toISOString().split('T')[0];
                item.deleted_date = today;
                renderBankTable(bankRecords);
            }
        }
        closeAllModals();
    });

    // 4. Single Item Actions
    btnSinglePrintNow.addEventListener('click', function () {
        window.print();
    });

    btnSingleExportExcel.addEventListener('click', function () {
        if (currentPreviewBankItem) {
            exportSingleBankExcel(currentPreviewBankItem);
        }
    });

    // 5. Search Filter
    function filterRecords() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const filtered = bankRecords.filter(function (b) {
            const rowText = `${b.name} ${b.shortname} ${b.account_no} ${b.account_name} ${b.swift_code} ${b.branch_name} ${b.address} ${b.account_type} ${b.currency} ${b.coa_account}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderBankTable(filtered);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRecords);
    }

    // 6. Refresh (Simulated AJAX)
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
            document.querySelectorAll('.row-action-menu.show').forEach(m => m.classList.remove('show'));
        }
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

// Toggle 3-Dots Action Menu
function toggleBankActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`bankActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(m => m.classList.remove('show'));
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Bank Table
function renderBankTable(dataset) {
    const tbody = document.getElementById('bankTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="12" class="empty-cell">No matching bank records found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${bankRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="bank-name-text ${isDeleted ? 'text-faint' : ''}">${rec.name}</strong></td>
            <td><span class="shortname-badge ${isDeleted ? 'badge-faint' : ''}">${rec.shortname}</span></td>
            <td><span class="font-numeric ${isDeleted ? 'text-muted' : ''}">${rec.account_no}</span></td>
            <td class="${isDeleted ? 'text-muted' : ''}">${rec.account_name}</td>
            <td><span class="swift-pill ${isDeleted ? 'badge-faint' : ''}">${rec.swift_code}</span></td>
            <td class="${isDeleted ? 'text-muted' : ''}">${rec.branch_name}</td>
            <td class="cell-address ${isDeleted ? 'text-muted' : ''}" title="${rec.address}">${rec.address}</td>
            <td><span class="account-type-pill">${rec.account_type}</span></td>
            <td><span class="currency-badge">${rec.currency}</span></td>
            <td class="cell-coa ${isDeleted ? 'text-muted' : ''}" title="${rec.coa_account}">
                <span class="coa-pill">${rec.coa_account}</span>
            </td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleBankActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="bankActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="openBankPrintModal(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View / Print</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editBankRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openBankDeleteModal(${rec.id})">
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
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${bankRecords.length} bank entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Edit Bank Row
function editBankRow(id) {
    const item = bankRecords.find(b => b.id === id);
    if (!item) return;

    document.getElementById('bankFormId').value = item.id;
    document.getElementById('bankName').value = item.name;
    document.getElementById('bankShortname').value = item.shortname;
    document.getElementById('bankAccountNo').value = item.account_no;
    document.getElementById('bankAccountName').value = item.account_name;
    document.getElementById('bankSwift').value = item.swift_code;
    document.getElementById('bankBranchName').value = item.branch_name;
    document.getElementById('bankAddress').value = item.address;
    document.getElementById('bankAccountType').value = item.account_type;
    document.getElementById('bankCurrency').value = item.currency;
    document.getElementById('bankCoaAccount').value = item.coa_account;

    document.getElementById('modalTitle').textContent = `Edit Bank — ${item.shortname}`;
    document.getElementById('bankModalBackdrop').classList.add('show');
}

// Open Delete Confirmation Modal (NO ALERT)
function openBankDeleteModal(id) {
    const item = bankRecords.find(b => b.id === id);
    if (!item) return;

    pendingDeleteBankId = id;
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('deleteTargetBankName').textContent = `${item.name} (${item.shortname})`;
    document.getElementById('deleteTargetAccountNo').textContent = ` — Acct: ${item.account_no}`;
    document.getElementById('deleteTargetDate').textContent = today;

    document.getElementById('bankDeleteModalBackdrop').classList.add('show');
}

// Open Single Item Print Voucher Modal
function openBankPrintModal(id) {
    const item = bankRecords.find(b => b.id === id);
    if (!item) return;

    currentPreviewBankItem = item;
    const today = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

    document.getElementById('voucherDate').textContent = today;
    document.getElementById('voucherBankName').textContent = item.name;
    document.getElementById('voucherShortname').textContent = item.shortname;
    document.getElementById('voucherAccountNo').textContent = item.account_no;
    document.getElementById('voucherAccountName').textContent = item.account_name;
    document.getElementById('voucherSwift').textContent = item.swift_code;
    document.getElementById('voucherBranchName').textContent = item.branch_name;
    document.getElementById('voucherAddress').textContent = item.address;
    document.getElementById('voucherAccountType').textContent = item.account_type;
    document.getElementById('voucherCurrency').textContent = item.currency;
    document.getElementById('voucherCoaAccount').textContent = item.coa_account;

    const isDeleted = item.deleted_date && item.deleted_date !== '—';
    document.getElementById('voucherStatus').innerHTML = isDeleted
        ? `<span class="badge-status-deleted">Archived (Deleted: ${item.deleted_date})</span>`
        : `<span class="badge-status-active">Active</span>`;

    document.getElementById('bankPrintModalBackdrop').classList.add('show');

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// Form Submit Handler (Create & Update - NO DELETED DATE FIELD)
function handleBankFormSubmit(e) {
    e.preventDefault();
    const idVal = document.getElementById('bankFormId').value;
    const name = document.getElementById('bankName').value.trim();
    const shortname = document.getElementById('bankShortname').value.trim();
    const account_no = document.getElementById('bankAccountNo').value.trim();
    const account_name = document.getElementById('bankAccountName').value.trim();
    const swift_code = document.getElementById('bankSwift').value.trim();
    const branch_name = document.getElementById('bankBranchName').value.trim();
    const address = document.getElementById('bankAddress').value.trim();
    const account_type = document.getElementById('bankAccountType').value;
    const currency = document.getElementById('bankCurrency').value;
    const coa_account = document.getElementById('bankCoaAccount').value.trim();

    if (idVal) {
        const item = bankRecords.find(b => b.id == idVal);
        if (item) {
            item.name = name;
            item.shortname = shortname;
            item.account_no = account_no;
            item.account_name = account_name;
            item.swift_code = swift_code;
            item.branch_name = branch_name;
            item.address = address;
            item.account_type = account_type;
            item.currency = currency;
            item.coa_account = coa_account;
        }
    } else {
        const newId = bankRecords.length ? Math.max(...bankRecords.map(b => b.id)) + 1 : 1;
        bankRecords.unshift({
            id: newId,
            name: name,
            shortname: shortname,
            account_no: account_no,
            account_name: account_name,
            swift_code: swift_code,
            branch_name: branch_name,
            address: address,
            account_type: account_type,
            currency: currency,
            coa_account: coa_account,
            deleted_date: '—'
        });
    }

    renderBankTable(bankRecords);
    document.getElementById('bankModalBackdrop').classList.remove('show');
}

// ==========================================================================
// EXPORT & PRINT HANDLERS (PDF, EXCEL TEMPLATE, CSV)
// ==========================================================================
function exportBankDirectory(format) {
    document.getElementById('exportDropdownWrapper').classList.remove('active');

    if (format === 'pdf') {
        window.print();
    } else if (format === 'excel') {
        exportBankExcel();
    } else if (format === 'csv') {
        exportBankCSV();
    }
}

// Formatted Excel Template for Bank Directory (.xls)
function exportBankExcel() {
    let rowsHtml = '';
    bankRecords.forEach(b => {
        rowsHtml += `
            <tr>
                <td style="border:1px solid #cbd5e1; padding:7px; font-weight:bold;">${b.name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.shortname}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.account_no}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.account_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.swift_code}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.branch_name}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.address}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.account_type}</td>
                <td style="border:1px solid #cbd5e1; padding:7px; text-align:center;">${b.currency}</td>
                <td style="border:1px solid #cbd5e1; padding:7px;">${b.coa_account}</td>
                <td style="border:1px solid #cbd5e1; padding:7px; color:${b.deleted_date !== '—' ? '#b91c1c' : '#64748b'};">${b.deleted_date}</td>
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
                    <th colspan="11" style="background:#2563eb; color:#ffffff; font-size:16px; padding:12px; text-align:left; font-weight:bold;">
                        Intracode ERP — Bank Directory & Linked Chart of Accounts Report
                    </th>
                </tr>
                <tr>
                    <td colspan="11" style="padding:6px; color:#64748b; font-size:11px;">
                        Generated: ${new Date().toLocaleString()} | Total Accounts: ${bankRecords.length}
                    </td>
                </tr>
                <tr style="background:#f1f5f9; font-weight:bold;">
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Bank Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Shortname</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Account No</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Account Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">SWIFT Code</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Branch Name</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Address</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Account Type</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:center;">Currency</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Account (COA)</th>
                    <th style="border:1px solid #cbd5e1; padding:8px; text-align:left;">Deleted Date</th>
                </tr>
                ${rowsHtml}
            </table>
        </body>
        </html>`;

    downloadFile(excelTemplate, 'Bank_Directory_Report.xls', 'application/vnd.ms-excel');
}

// Formatted Single Item Excel Voucher
function exportSingleBankExcel(item) {
    const excelTemplate = `
        <html>
        <head>
            <meta http-equiv="content-type" content="application/vnd.ms-excel; charset=UTF-8"/>
        </head>
        <body style="font-family: Arial, sans-serif;">
            <table border="1" style="border-collapse:collapse; width:520px;">
                <tr>
                    <th colspan="2" style="background:#2563eb; color:#ffffff; font-size:14px; padding:10px; text-align:left;">
                        Intracode IT Solutions — Bank Account Record Voucher
                    </th>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; width:160px; background:#f8fafc;">Bank Name</td>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; color:#0f172a;">${item.name} (${item.shortname})</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Account Number</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.account_no}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Account Name</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.account_name}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">SWIFT Code</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.swift_code}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Branch Name</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.branch_name}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Branch Address</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.address}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Account Type</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.account_type}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Currency</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.currency}</td>
                </tr>
                <tr>
                    <td style="border:1px solid #cbd5e1; padding:8px; font-weight:bold; background:#f8fafc;">Account (COA)</td>
                    <td style="border:1px solid #cbd5e1; padding:8px;">${item.coa_account}</td>
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

    downloadFile(excelTemplate, `Bank_Record_${item.shortname}_${item.account_no}.xls`, 'application/vnd.ms-excel');
}

// CSV Export
function exportBankCSV() {
    let csv = 'Bank Name,Shortname,Account No,Account Name,SWIFT Code,Branch Name,Address,Account Type,Currency,Account (COA),Deleted Date\r\n';
    bankRecords.forEach(b => {
        csv += `"${b.name}","${b.shortname}","${b.account_no}","${b.account_name}","${b.swift_code}","${b.branch_name}","${b.address.replace(/"/g, '""')}","${b.account_type}","${b.currency}","${b.coa_account}","${b.deleted_date}"\r\n`;
    });
    downloadFile(csv, 'Bank_Directory_Report.csv', 'text/csv;charset=utf-8;');
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

