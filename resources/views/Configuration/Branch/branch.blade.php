@extends('layouts.app')

@section('title', 'Branch Management — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/Branch/branch.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Branch</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="branchSearchInput" placeholder="Search branch name, shortname, TIN..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Branch Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload branch records via AJAX">
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
                    <button type="button" class="export-option" onclick="exportBranchDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable branch directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportBranchDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportBranchDirectory('csv')">
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
                <span>Create Branch</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Corporate Branches</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Registered Commercial Operating Units</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="branchTable">
                    <thead>
                        <tr>
                            <th style="min-width: 70px;">ID</th>
                            <th style="min-width: 170px;">Name</th>
                            <th style="min-width: 95px;">Short Name</th>
                            <th style="min-width: 190px;">Address</th>
                            <th style="min-width: 125px;">TIN</th>
                            <th style="min-width: 150px;">Business Style</th>
                            <th style="min-width: 160px;">Email Address</th>
                            <th style="min-width: 130px;">Telephone No</th>
                            <th style="min-width: 120px;">Fax No</th>
                            <th style="min-width: 140px;">Website</th>
                            <th style="min-width: 120px;">Business Tax</th>
                            <th style="min-width: 120px;" class="text-center">Main Branch</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="branchTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 branch entries</span>
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
     1. CREATE / EDIT BRANCH MODAL
     ========================================================================== -->
<div class="modal-backdrop" id="branchModalBackdrop">
    <div class="modal-dialog modal-dialog-lg" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Branch</h3>
                <p class="modal-subtitle">Register commercial branch unit details, statutory tax attributes, and operational contacts.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="branchForm" onsubmit="handleBranchFormSubmit(event)">
            <input type="hidden" id="branchFormId" value="">

            <div class="modal-body">
                <!-- Row 1: Name & Short Name -->
                <div class="form-row">
                    <div class="form-group flex-2">
                        <label for="branchName">Branch Name <span class="required">*</span></label>
                        <input type="text" id="branchName" class="form-input" placeholder="e.g. Manila Central Head Office" required maxlength="150">
                    </div>
                    <div class="form-group flex-1">
                        <label for="branchShortName">Short Name <span class="required">*</span></label>
                        <input type="text" id="branchShortName" class="form-input" placeholder="e.g. HO-MNL" required maxlength="25">
                    </div>
                </div>

                <!-- Row 2: Address -->
                <div class="form-group">
                    <label for="branchAddress">Address <span class="required">*</span></label>
                    <input type="text" id="branchAddress" class="form-input" placeholder="e.g. Suite 1201, Corporate Tower, Ayala Ave, Makati City" required maxlength="255">
                </div>

                <!-- Row 3: TIN, Business Tax, Business Style -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="branchTin">TIN <span class="required">*</span></label>
                        <input type="text" id="branchTin" class="form-input font-numeric" placeholder="e.g. 000-123-456-000" required maxlength="30">
                    </div>
                    <div class="form-group flex-1">
                        <label for="branchBusinessTax">Business Tax <span class="required">*</span></label>
                        <select id="branchBusinessTax" class="form-select" required>
                            <option value="VAT Registered">VAT Registered</option>
                            <option value="Non-VAT">Non-VAT</option>
                            <option value="Percentage Tax">Percentage Tax</option>
                            <option value="Zero-Rated">Zero-Rated</option>
                            <option value="Tax Exempt">Tax Exempt</option>
                        </select>
                    </div>
                    <div class="form-group flex-1">
                        <label for="branchBusinessStyle">Business Style <span class="required">*</span></label>
                        <input type="text" id="branchBusinessStyle" class="form-input" placeholder="e.g. IT Software Solutions" required maxlength="100">
                    </div>
                </div>

                <!-- Row 4: Email, Telephone, Fax -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="branchEmail">Email Address <span class="required">*</span></label>
                        <input type="email" id="branchEmail" class="form-input" placeholder="e.g. headoffice@intracode.ph" required maxlength="100">
                    </div>
                    <div class="form-group flex-1">
                        <label for="branchTelephone">Telephone Number <span class="required">*</span></label>
                        <input type="text" id="branchTelephone" class="form-input font-numeric" placeholder="e.g. +63 (2) 8888-1234" required maxlength="30">
                    </div>
                    <div class="form-group flex-1">
                        <label for="branchFax">Fax No</label>
                        <input type="text" id="branchFax" class="form-input font-numeric" placeholder="e.g. +63 (2) 8888-1235" maxlength="30">
                    </div>
                </div>

                <!-- Row 5: Website -->
                <div class="form-group">
                    <label for="branchWebsite">Website</label>
                    <input type="url" id="branchWebsite" class="form-input" placeholder="e.g. https://intracode.ph" maxlength="150">
                </div>

                <!-- Row 6: Main Branch Checkbox -->
                <div class="form-checkbox-row">
                    <label class="custom-checkbox-container">
                        <input type="checkbox" id="branchIsMain" class="real-checkbox">
                        <span class="custom-checkmark"><i data-lucide="check"></i></span>
                        <div class="checkbox-text-group">
                            <span class="checkbox-label-text">Designate as Main Branch (Head Office)</span>
                            <span class="checkbox-hint-text">Check if this location acts as the primary corporate headquarters and central tax reporting unit.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Branch</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="branchDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Branch Deletion</h3>
                    <p class="modal-subtitle">Permanent removal of branch records from active operation.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete branch <strong id="deleteTargetBranchName" class="delete-target-text">—</strong>?
                Transactions mapped to this branch will no longer associate with it.
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Branch</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory branch records
let branchRecords = [
    {
        id: 1,
        code: 'BR-001',
        name: 'Manila Central Head Office',
        short_name: 'HO-MNL',
        address: 'Tower 1, Ayala Triangle, Ayala Ave, Makati City',
        tin: '000-123-456-000',
        business_style: 'Software & Cloud Services',
        email: 'headoffice@intracode.ph',
        telephone: '+63 (2) 8888-1234',
        fax: '+63 (2) 8888-1235',
        website: 'https://intracode.ph',
        business_tax: 'VAT Registered',
        is_main: true
    },
    {
        id: 2,
        code: 'BR-002',
        name: 'Cebu Regional Hub',
        short_name: 'BR-CEB',
        address: '8th Flr, Cebu IT Park, Lahug, Cebu City',
        tin: '000-123-456-001',
        business_style: 'Regional Technical Support',
        email: 'cebu.hub@intracode.ph',
        telephone: '+63 (32) 234-5678',
        fax: '—',
        website: 'https://cebu.intracode.ph',
        business_tax: 'VAT Registered',
        is_main: false
    },
    {
        id: 3,
        code: 'BR-003',
        name: 'Davao Mindanao Branch',
        short_name: 'BR-DVO',
        address: 'Abreeza Corporate Center, J.P. Laurel Ave, Davao City',
        tin: '000-123-456-002',
        business_style: 'Logistics & Distribution',
        email: 'davao.office@intracode.ph',
        telephone: '+63 (82) 299-4321',
        fax: '—',
        website: 'https://davao.intracode.ph',
        business_tax: 'VAT Registered',
        is_main: false
    },
    {
        id: 4,
        code: 'BR-004',
        name: 'Clark Freeport Operations',
        short_name: 'BR-CRK',
        address: 'Bldg 2, Clark Global City, Mabalacat, Pampanga',
        tin: '000-123-456-003',
        business_style: 'Export & Enterprise Systems',
        email: 'clark.ops@intracode.ph',
        telephone: '+63 (45) 599-8800',
        fax: '+63 (45) 599-8801',
        website: 'https://clark.intracode.ph',
        business_tax: 'Zero-Rated',
        is_main: false
    }
];

let pendingDeleteBranchId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderBranchTable(branchRecords);

    // Modal backdrops
    const branchModalBackdrop = document.getElementById('branchModalBackdrop');
    const branchDeleteModalBackdrop = document.getElementById('branchDeleteModalBackdrop');

    // Action buttons
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
    const searchInput = document.getElementById('branchSearchInput');

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
        document.getElementById('branchForm').reset();
        document.getElementById('branchFormId').value = '';
        document.getElementById('branchIsMain').checked = false;
        document.getElementById('modalTitle').textContent = 'Create New Branch';
        branchModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        branchModalBackdrop.classList.remove('show');
        branchDeleteModalBackdrop.classList.remove('show');
        pendingDeleteBranchId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [branchModalBackdrop, branchDeleteModalBackdrop].forEach(function (backdrop) {
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
        if (pendingDeleteBranchId !== null) {
            branchRecords = branchRecords.filter(function (r) {
                return r.id !== pendingDeleteBranchId;
            });
            filterRecords();
        }
        closeAllModals();
    });

    // 4. Search Filter
    function filterRecords() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const filtered = branchRecords.filter(function (b) {
            const rowText = `${b.id} ${b.code} ${b.name} ${b.short_name} ${b.address} ${b.tin} ${b.business_style} ${b.email} ${b.telephone} ${b.fax} ${b.website} ${b.business_tax} ${b.is_main ? 'main branch head office' : ''}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderBranchTable(filtered);
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
function toggleBranchActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`branchActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Branch Table
function renderBranchTable(dataset) {
    const tbody = document.getElementById('branchTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="13" class="empty-cell">No matching branch records found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${branchRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');

        const mainBranchBadge = rec.is_main
            ? `<span class="status-pill status-success"><span class="status-dot"></span> Head Office</span>`
            : `<span class="branch-pill-sub">Branch Office</span>`;

        tr.innerHTML = `
            <td><strong class="font-numeric id-badge">${rec.id}</strong></td>
            <td><strong class="branch-name-text">${rec.name}</strong></td>
            <td><span class="shortname-badge">${rec.short_name}</span></td>
            <td class="cell-address" title="${rec.address}">${rec.address}</td>
            <td><span class="tin-pill font-numeric">${rec.tin}</span></td>
            <td class="cell-style" title="${rec.business_style}">${rec.business_style}</td>
            <td><a href="mailto:${rec.email}" class="cell-email">${rec.email}</a></td>
            <td><span class="font-numeric text-muted">${rec.telephone}</span></td>
            <td><span class="font-numeric text-muted">${rec.fax || '—'}</span></td>
            <td>
                ${rec.website && rec.website !== '—' 
                    ? `<a href="${rec.website}" target="_blank" rel="noopener" class="cell-link">${rec.website.replace('https://', '')}</a>` 
                    : '<span class="text-muted">—</span>'}
            </td>
            <td><span class="tax-badge">${rec.business_tax}</span></td>
            <td class="text-center">${mainBranchBadge}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleBranchActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="branchActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewBranchRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editBranchRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openBranchDeleteModal(${rec.id})">
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
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${branchRecords.length} branch entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Branch Modal
function viewBranchRow(id) {
    const rec = branchRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('branchFormId').value = rec.id;
    document.getElementById('branchName').value = rec.name;
    document.getElementById('branchShortName').value = rec.short_name;
    document.getElementById('branchAddress').value = rec.address;
    document.getElementById('branchTin').value = rec.tin;
    document.getElementById('branchBusinessStyle').value = rec.business_style;
    document.getElementById('branchEmail').value = rec.email;
    document.getElementById('branchTelephone').value = rec.telephone;
    document.getElementById('branchFax').value = rec.fax;
    document.getElementById('branchWebsite').value = rec.website;
    document.getElementById('branchBusinessTax').value = rec.business_tax;
    document.getElementById('branchMainOffice').checked = !!rec.main_branch;

    document.getElementById('modalTitle').textContent = 'View Branch — ' + rec.name;
    document.getElementById('branchModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editBranchRow(id) {
    const rec = branchRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('branchFormId').value = rec.id;
    document.getElementById('branchName').value = rec.name;
    document.getElementById('branchShortName').value = rec.short_name;
    document.getElementById('branchAddress').value = rec.address;
    document.getElementById('branchTin').value = rec.tin;
    document.getElementById('branchBusinessTax').value = rec.business_tax;
    document.getElementById('branchBusinessStyle').value = rec.business_style;
    document.getElementById('branchEmail').value = rec.email;
    document.getElementById('branchTelephone').value = rec.telephone;
    document.getElementById('branchFax').value = rec.fax === '—' ? '' : rec.fax;
    document.getElementById('branchWebsite').value = rec.website === '—' ? '' : rec.website;
    document.getElementById('branchIsMain').checked = !!rec.is_main;

    document.getElementById('modalTitle').textContent = 'Edit Branch — ' + rec.short_name;
    document.getElementById('branchModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openBranchDeleteModal(id) {
    const rec = branchRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteBranchId = id;
    document.getElementById('deleteTargetBranchName').textContent = `${rec.name} (${rec.short_name})`;
    document.getElementById('branchDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handleBranchFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('branchFormId').value;
    const name = document.getElementById('branchName').value.trim();
    const short_name = document.getElementById('branchShortName').value.trim();
    const address = document.getElementById('branchAddress').value.trim();
    const tin = document.getElementById('branchTin').value.trim();
    const business_tax = document.getElementById('branchBusinessTax').value;
    const business_style = document.getElementById('branchBusinessStyle').value.trim();
    const email = document.getElementById('branchEmail').value.trim();
    const telephone = document.getElementById('branchTelephone').value.trim();
    const fax = document.getElementById('branchFax').value.trim() || '—';
    const website = document.getElementById('branchWebsite').value.trim() || '—';
    const is_main = document.getElementById('branchIsMain').checked;

    // If setting as main branch, demote any other main branch
    if (is_main) {
        branchRecords.forEach(function (b) {
            b.is_main = false;
        });
    }

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = branchRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.name = name;
            existing.short_name = short_name;
            existing.address = address;
            existing.tin = tin;
            existing.business_tax = business_tax;
            existing.business_style = business_style;
            existing.email = email;
            existing.telephone = telephone;
            existing.fax = fax;
            existing.website = website;
            existing.is_main = is_main;
        }
    } else {
        // Create new
        const newId = branchRecords.length ? Math.max.apply(null, branchRecords.map(function (r) { return r.id; })) + 1 : 1;
        const newCode = 'BR-' + String(newId).padStart(3, '0');
        branchRecords.push({
            id: newId,
            code: newCode,
            name: name,
            short_name: short_name,
            address: address,
            tin: tin,
            business_tax: business_tax,
            business_style: business_style,
            email: email,
            telephone: telephone,
            fax: fax,
            website: website,
            is_main: is_main
        });
    }

    const searchInput = document.getElementById('branchSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = branchRecords.filter(function (b) {
        const rowText = `${b.id} ${b.code} ${b.name} ${b.short_name} ${b.address} ${b.tin} ${b.business_style} ${b.email} ${b.telephone} ${b.fax} ${b.website} ${b.business_tax} ${b.is_main ? 'main branch head office' : ''}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderBranchTable(filtered);
    document.getElementById('branchModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportBranchDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'ID,Name,Short Name,Address,TIN,Business Style,Email Address,Telephone No,Fax No,Website,Business Tax,Main Branch\n';
        branchRecords.forEach(function (r) {
            const cleanAddr = `"${r.address.replace(/"/g, '""')}"`;
            const cleanStyle = `"${r.business_style.replace(/"/g, '""')}"`;
            const isMainStr = r.is_main ? 'YES (Head Office)' : 'NO (Branch Office)';
            csvContent += `${r.id},"${r.name}","${r.short_name}",${cleanAddr},"${r.tin}",${cleanStyle},"${r.email}","${r.telephone}","${r.fax}","${r.website}","${r.business_tax}","${isMainStr}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Branch_Directory_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        branchRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.name}</td>
                    <td>${r.short_name}</td>
                    <td>${r.address}</td>
                    <td>${r.tin}</td>
                    <td>${r.business_style}</td>
                    <td>${r.email}</td>
                    <td>${r.telephone}</td>
                    <td>${r.fax}</td>
                    <td>${r.website}</td>
                    <td>${r.business_tax}</td>
                    <td>${r.is_main ? 'Head Office' : 'Branch Office'}</td>
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
                <h2>Intracode ERP — Corporate Branch Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Short Name</th>
                            <th>Address</th>
                            <th>TIN</th>
                            <th>Business Style</th>
                            <th>Email Address</th>
                            <th>Telephone No</th>
                            <th>Fax No</th>
                            <th>Website</th>
                            <th>Business Tax</th>
                            <th>Main Branch</th>
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
        link.download = `Branch_Directory_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

