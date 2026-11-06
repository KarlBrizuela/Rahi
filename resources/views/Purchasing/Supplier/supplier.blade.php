@extends('layouts.app')

@section('title', 'Suppliers & Vendors Directory — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Purchasing/purchasing.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">
    <header class="top-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-item">Supply Chain</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Purchasing</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Supplier</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Supplier Name, TIN, Contact..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplier & Vendor Directory</h1>
        </div>

        <div class="action-cluster">
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh">
                <i data-lucide="refresh-cw" class="btn-icon" id="refreshIcon"></i>
                <span>Refresh</span>
            </button>

            <div class="dropdown-wrapper" id="exportDropdownWrapper">
                <button type="button" class="btn-outline" id="btnExportToggle">
                    <i data-lucide="download" class="btn-icon"></i>
                    <span>Export / Print</span>
                    <i data-lucide="chevron-down" class="dropdown-chevron"></i>
                </button>
                <div class="export-dropdown-menu" id="exportMenu">
                    <button type="button" class="export-option" onclick="window.print()">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print Directory</strong>
                            <small>Print supplier profiles register</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Supplier CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Approved vendor contacts spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Add Supplier Profile</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Vendor Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Active">Active (Accredited)</option>
                <option value="Under Evaluation">Under Evaluation</option>
                <option value="Suspended">Suspended</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Vendor Category</label>
            <select class="filter-select" id="filterCategory">
                <option value="">All Categories</option>
                <option value="Raw Materials">Raw Materials & Conductors</option>
                <option value="Polymers & Resins">Polymers & Resins</option>
                <option value="Packaging Materials">Packaging Materials</option>
                <option value="Industrial Chemicals">Industrial Chemicals</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Payment Terms</label>
            <select class="filter-select" id="filterTerms">
                <option value="">All Terms</option>
                <option value="Net 30 Days">Net 30 Days</option>
                <option value="Net 60 Days">Net 60 Days</option>
                <option value="Net 45 Days">Net 45 Days</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="button" class="btn-text" id="btnResetFilters">
                <i data-lucide="rotate-ccw"></i>
                <span>Reset Filters</span>
            </button>
        </div>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL ACCREDITED SUPPLIERS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Active vendor procurement master</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PRIMARY SUPPLY REGION</span>
            <span class="kpi-value" style="font-size: 16px; font-weight: 600; color: #1e293b;">Calabarzon / NCR</span>
            <span class="kpi-subtext">Domestic manufacturing hub</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">AVERAGE LEAD TIME</span>
            <span class="kpi-value font-numeric font-bold" style="color: #059669;" id="kpiAvgLead">6.5 Days</span>
            <span class="kpi-subtext">Procurement dispatch to factory intake</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">ON-TIME DELIVERY SCORE</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiOtd">98.4%</span>
            <span class="kpi-subtext">Supplier SLA performance index</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Approved Supplier Directory</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkSendInquiry()">
                    <i data-lucide="mail"></i>
                    <span>Send Inquiry</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintSupplierCards()">
                    <i data-lucide="printer"></i>
                    <span>Print Vendor Profiles</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="supplierTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>SUPPLIER CODE</th>
                        <th>SUPPLIER NAME</th>
                        <th>TIN NUMBER</th>
                        <th>CATEGORY</th>
                        <th>CONTACT PERSON</th>
                        <th>EMAIL</th>
                        <th>PHONE</th>
                        <th>PAYMENT TERMS</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="supplierTableBody">
                    <tr data-id="1" data-cat="Raw Materials" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewSupplier('1')">SUP-001</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Sumitomo Metal & Wire Corp.</span>
                                <span class="prod-sub">Light Industry & Science Park II, Calamba, Laguna</span>
                            </div>
                        </td>
                        <td class="font-code">004-918-201-000</td>
                        <td><span class="category-pill">Raw Materials</span></td>
                        <td>Kenji Takahashi</td>
                        <td>ktakahashi@sumitomo-metal.ph</td>
                        <td class="font-numeric">+63 49 545 8891</td>
                        <td><span class="location-badge">Net 30 Days</span></td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewSupplier('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editSupplier('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printSupplier('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Profile</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteSupplier('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-cat="Polymers & Resins" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewSupplier('2')">SUP-002</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Toray Polymers Asia</span>
                                <span class="prod-sub">First Philippine Industrial Park, Tanauan, Batangas</span>
                            </div>
                        </td>
                        <td class="font-code">008-331-509-000</td>
                        <td><span class="category-pill">Polymers & Resins</span></td>
                        <td>Ailene Santos</td>
                        <td>asantos@toray-polymers.com.ph</td>
                        <td class="font-numeric">+63 43 778 1204</td>
                        <td><span class="location-badge">Net 60 Days</span></td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewSupplier('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editSupplier('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printSupplier('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Profile</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteSupplier('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-cat="Packaging Materials" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewSupplier('3')">SUP-003</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Apex Packaging Solutions</span>
                                <span class="prod-sub">Carmona Industrial Estate, Cavite</span>
                            </div>
                        </td>
                        <td class="font-code">001-844-902-000</td>
                        <td><span class="category-pill">Packaging Materials</span></td>
                        <td>Roberto Lim</td>
                        <td>roberto.lim@apexpack.ph</td>
                        <td class="font-numeric">+63 46 430 9922</td>
                        <td><span class="location-badge">Net 30 Days</span></td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewSupplier('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editSupplier('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printSupplier('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Profile</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteSupplier('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-cat="Industrial Chemicals" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewSupplier('4')">SUP-004</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Shin-Etsu Chemical Philippines</span>
                                <span class="prod-sub">Laguna International Industrial Park, Binan, Laguna</span>
                            </div>
                        </td>
                        <td class="font-code">007-910-833-000</td>
                        <td><span class="category-pill">Industrial Chemicals</span></td>
                        <td>Hiroshi Sato</td>
                        <td>hsato@shinetsu.ph</td>
                        <td class="font-numeric">+63 49 539 0180</td>
                        <td><span class="location-badge">Net 45 Days</span></td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewSupplier('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editSupplier('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Profile</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printSupplier('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Profile</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteSupplier('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="ledger-pagination no-print">
            <span class="pagination-info">Showing 1 to 4 of 4 records</span>
            <div class="pagination-controls">
                <button type="button" class="page-btn" disabled>&laquo; Previous</button>
                <button type="button" class="page-btn active">1</button>
                <button type="button" class="page-btn" disabled>Next &raquo;</button>
            </div>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="entryModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="entryModalTitle">Register New Supplier Profile</h3>
                <p class="modal-subtitle">Enroll an accredited supplier into the corporate procurement and billing directory.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveSupplier(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Corporate Identity & Tax Registration</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Supplier Code</label>
                        <input type="text" class="field-input font-code" id="supCode" placeholder="e.g. SUP-005" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Tax Identification No (TIN)</label>
                        <input type="text" class="field-input font-code" id="supTin" placeholder="000-000-000-000" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Vendor Category</label>
                        <select class="field-select" id="supCategory" required>
                            <option value="Raw Materials">Raw Materials & Conductors</option>
                            <option value="Polymers & Resins">Polymers & Resins</option>
                            <option value="Packaging Materials">Packaging Materials</option>
                            <option value="Industrial Chemicals">Industrial Chemicals</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Company Registered Name</label>
                        <input type="text" class="field-input" id="supName" placeholder="Full corporate entity name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Plant / Office Business Address</label>
                        <input type="text" class="field-input" id="supAddress" placeholder="Industrial park, City, Province" required>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Contact Information & Commercial Terms</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Key Contact Person</label>
                        <input type="text" class="field-input" id="supContact" placeholder="Sales or Account Executive" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Corporate Email</label>
                        <input type="email" class="field-input" id="supEmail" placeholder="orders@supplier.com" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Telephone / Mobile</label>
                        <input type="text" class="field-input" id="supPhone" placeholder="+63 49 000 0000" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Default Payment Terms</label>
                        <select class="field-select" id="supTerms" required>
                            <option value="Net 30 Days">Net 30 Days</option>
                            <option value="Net 60 Days">Net 60 Days</option>
                            <option value="Net 45 Days">Net 45 Days</option>
                            <option value="COD / Immediate">COD / Immediate</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Billing Currency</label>
                        <select class="field-select" id="supCurrency" required>
                            <option value="PHP">PHP - Philippine Peso</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Accreditation Status</label>
                        <select class="field-select" id="supStatus" required>
                            <option value="Active">Active (Accredited)</option>
                            <option value="Under Evaluation">Under Evaluation</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Supplier Profile</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Supplier Accreditation Profile</h3>
                <p class="modal-subtitle">Official corporate profile, commercial terms, and past purchasing performance.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Profile</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Deactivate Supplier</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to deactivate supplier <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will prevent new purchase orders from being issued to this vendor.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-danger-solid" onclick="confirmDelete()">Confirm Deactivation</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let activeEditId = null;
    let activeDeleteId = null;

    document.addEventListener("DOMContentLoaded", function () {
        lucide.createIcons();

        document.getElementById("btnExportToggle").addEventListener("click", function (e) {
            e.stopPropagation();
            document.getElementById("exportMenu").classList.toggle("show");
        });

        document.addEventListener("click", function () {
            document.getElementById("exportMenu").classList.remove("show");
        });

        document.getElementById("selectAllCheckbox").addEventListener("change", function () {
            const checkboxes = document.querySelectorAll(".row-checkbox");
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkCount();
        });

        document.querySelectorAll(".row-checkbox").forEach(cb => {
            cb.addEventListener("change", updateBulkCount);
        });

        document.getElementById("globalSearchInput").addEventListener("input", filterTable);
        document.getElementById("filterStatus").addEventListener("change", filterTable);
        document.getElementById("filterCategory").addEventListener("change", filterTable);
        document.getElementById("filterTerms").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterCategory").value = "";
            document.getElementById("filterTerms").value = "";
            filterTable();
        });

        document.getElementById("btnRefresh").addEventListener("click", function () {
            const icon = document.getElementById("refreshIcon");
            icon.classList.add("spin");
            setTimeout(() => icon.classList.remove("spin"), 600);
        });
    });

    function updateBulkCount() {
        const checked = document.querySelectorAll(".row-checkbox:checked");
        const count = checked.length;
        const bulkDiv = document.getElementById("bulkActions");
        document.getElementById("selectedCount").innerText = count;
        bulkDiv.style.display = count > 0 ? "flex" : "none";
    }

    function filterTable() {
        const query = document.getElementById("globalSearchInput").value.toLowerCase();
        const status = document.getElementById("filterStatus").value.toLowerCase();
        const cat = document.getElementById("filterCategory").value.toLowerCase();
        const terms = document.getElementById("filterTerms").value.toLowerCase();
        const rows = document.querySelectorAll("#supplierTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowCat = (row.getAttribute("data-cat") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchCat = !cat || rowCat.includes(cat);
            const matchTerms = !terms || text.includes(terms);

            if (matchQuery && matchStatus && matchCat && matchTerms) {
                row.style.display = "";
                visibleCount++;
            } else {
                row.style.display = "none";
            }
        });

        document.getElementById("recordCount").innerText = visibleCount;
    }

    function openCreateModal() {
        activeEditId = null;
        document.getElementById("entryModalTitle").innerText = "Register New Supplier Profile";
        document.getElementById("saveBtnLabel").innerText = "Save Supplier Profile";
        document.getElementById("entryForm").reset();
        document.getElementById("supCode").value = "SUP-005";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editSupplier(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Supplier Profile SUP-00" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Profile";

        if (id === '1') {
            document.getElementById("supCode").value = "SUP-001";
            document.getElementById("supTin").value = "004-918-201-000";
            document.getElementById("supCategory").value = "Raw Materials";
            document.getElementById("supName").value = "Sumitomo Metal & Wire Corp.";
            document.getElementById("supAddress").value = "Light Industry & Science Park II, Calamba, Laguna";
            document.getElementById("supContact").value = "Kenji Takahashi";
            document.getElementById("supEmail").value = "ktakahashi@sumitomo-metal.ph";
            document.getElementById("supPhone").value = "+63 49 545 8891";
            document.getElementById("supTerms").value = "Net 30 Days";
            document.getElementById("supCurrency").value = "PHP";
            document.getElementById("supStatus").value = "Active";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveSupplier(e) {
        e.preventDefault();
        alert("Supplier profile successfully saved.");
        closeEntryModal();
    }

    function viewSupplier(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Supplier Code</span>
                    <span class="spec-value font-bold font-code">SUP-001</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Company Name</span>
                    <span class="spec-value font-bold">Sumitomo Metal & Wire Corp.</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Accreditation</span>
                    <span class="spec-value"><span class="status-badge status-completed">Active (Accredited)</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">TIN Number</span>
                    <span class="spec-value font-code">004-918-201-000</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Contact Person</span>
                    <span class="spec-value">Kenji Takahashi (Key Accounts Mgr)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Email & Phone</span>
                    <span class="spec-value">ktakahashi@sumitomo-metal.ph | +63 49 545 8891</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Plant Facility Address</span>
                    <span class="spec-value">Light Industry & Science Park II, Calamba, Laguna</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Payment Terms</span>
                    <span class="spec-value font-bold">Net 30 Days (PHP)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">On-Time Delivery Performance</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">99.2% (Grade A+)</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printSupplier(id) {
        viewSupplier(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteSupplier(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "SUP-00" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Supplier profile deactivated.");
        closeDeleteModal();
    }

    function bulkSendInquiry() {
        alert("Bulk vendor inquiry drafted.");
    }

    function bulkPrintSupplierCards() {
        window.print();
    }

    window.toggleActionMenu = function (event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`actionMenu_${id}`);
        const isShown = menu.classList.contains("show");
        closeAllActionMenus();
        if (!isShown) {
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            if (spaceBelow < 170) {
                menu.classList.remove("drop-down");
                menu.classList.add("drop-up");
            } else {
                menu.classList.remove("drop-up");
                menu.classList.add("drop-down");
            }
            menu.classList.add("show");
        }
    };

    function closeAllActionMenus() {
        document.querySelectorAll(".action-menu-dropdown.show").forEach(m => m.classList.remove("show"));
    }

    document.addEventListener("click", function () {
        closeAllActionMenus();
    });
</script>
@endsection

