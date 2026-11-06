@extends('layouts.app')

@section('title', 'Supplies & Material Issuance — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Warehouse/warehouse.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">
    <header class="top-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-item">Supply Chain</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Warehouse</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Supplies/Raw Matl’s</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Supplies Issuance</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Issuance No, WO#, Requisitioner..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplies & Raw Materials Issuance</h1>
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
                            <strong>Print Issuance Slips</strong>
                            <small>Material requisition discharge vouchers</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Issuance CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Materials consumption register</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Material Issuance</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Issued">Issued & Discharged</option>
                <option value="Pending Requisition">Pending Requisition</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Department / Work Center</label>
            <select class="filter-select" id="filterDept">
                <option value="">All Departments</option>
                <option value="SMT Line 01">SMT Line 01</option>
                <option value="SMT Line 02">SMT Line 02</option>
                <option value="Extrusion Shop">Extrusion Shop</option>
                <option value="Packaging Cell">Packaging Cell</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Issuance Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Issuance Date To</label>
            <input type="date" class="filter-input" id="filterDateTo">
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
            <span class="kpi-label">TOTAL ISSUANCES</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Shopfloor material discharges</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL ISSUED VALUATION</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiValue">₱ 418,920.00</span>
            <span class="kpi-subtext">Direct materials debited to WIP</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PRIMARY RECIPIENT</span>
            <span class="kpi-value" style="font-size: 16px; font-weight: 600; color: #1e293b;">SMT Line 01</span>
            <span class="kpi-subtext">Highest material consumption</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PENDING REQUISITIONS</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiPending">1</span>
            <span class="kpi-subtext">Awaiting picking & warehouse dispatch</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Material Store Issuance Register</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkIssue()">
                    <i data-lucide="check"></i>
                    <span>Confirm Dispatch</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintSlips()">
                    <i data-lucide="printer"></i>
                    <span>Print Requisitions</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="issuanceTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>ISSUANCE NO</th>
                        <th>DATE</th>
                        <th>WORK CENTER / DEPT</th>
                        <th>REQUISITIONER</th>
                        <th>MATERIAL CODE & DESCRIPTION</th>
                        <th class="text-right">ISSUED QTY</th>
                        <th>UOM</th>
                        <th>PURPOSE / WO REF</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="issuanceTableBody">
                    <tr data-id="1" data-dept="SMT Line 01" data-status="Issued">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewIssuance('1')">ISS-2026-0044</a>
                        </td>
                        <td class="font-numeric">2026-11-06</td>
                        <td><span class="location-badge">SMT Line 01</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-CU-001</span>
                                <span class="prod-sub">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">1,800</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td><span class="font-code font-bold">WO-2026-0814</span></td>
                        <td><span class="status-badge status-completed">Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewIssuance('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editIssuance('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printIssuance('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteIssuance('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Issuance</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-dept="Extrusion Shop" data-status="Issued">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewIssuance('2')">ISS-2026-0043</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td><span class="location-badge">Extrusion Shop</span></td>
                        <td>Alvin Mercado</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-PET-004</span>
                                <span class="prod-sub">PET Insulation Film 0.05mm White</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">250</td>
                        <td><span class="uom-pill">ROLL</span></td>
                        <td><span class="font-code font-bold">WO-2026-0819</span></td>
                        <td><span class="status-badge status-completed">Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewIssuance('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editIssuance('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printIssuance('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteIssuance('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Issuance</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-dept="SMT Line 02" data-status="Issued">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewIssuance('3')">ISS-2026-0042</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td><span class="location-badge">SMT Line 02</span></td>
                        <td>Nestor Bautista</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-ADH-012</span>
                                <span class="prod-sub">Thermosetting Heat-Seal Adhesive Resin</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">3</td>
                        <td><span class="uom-pill">DRUM</span></td>
                        <td><span class="font-code font-bold">WO-2026-0824</span></td>
                        <td><span class="status-badge status-completed">Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewIssuance('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editIssuance('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printIssuance('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteIssuance('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Issuance</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-dept="Packaging Cell" data-status="Pending Requisition">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewIssuance('4')">ISS-2026-0041</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td><span class="location-badge">Packaging Cell</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-BOX-020</span>
                                <span class="prod-sub">Heavy-Duty Corrugated Export Carton</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #d97706;">500</td>
                        <td><span class="uom-pill">PCS</span></td>
                        <td><span class="font-code font-bold">WO-2026-0825</span></td>
                        <td><span class="status-badge status-reorder">Pending Requisition</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewIssuance('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editIssuance('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printIssuance('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteIssuance('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Issuance</span>
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
                <h3 class="modal-title" id="entryModalTitle">Create Supplies Issuance Slip</h3>
                <p class="modal-subtitle">Authorize stock withdrawal from warehouse store to factory production line.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveIssuance(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Requisition & Department Identifiers</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Issuance Number</label>
                        <input type="text" class="field-input font-code" id="issNumber" value="ISS-2026-0045" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Date of Issuance</label>
                        <input type="date" class="field-input" id="issDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="issStatus" required>
                            <option value="Issued">Issued & Discharged</option>
                            <option value="Pending Requisition">Pending Requisition</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Recipient Department</label>
                        <select class="field-select" id="issDept" required>
                            <option value="SMT Line 01">SMT Line 01</option>
                            <option value="SMT Line 02">SMT Line 02</option>
                            <option value="Extrusion Shop">Extrusion Shop</option>
                            <option value="Packaging Cell">Packaging Cell</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Requisitioner Name</label>
                        <input type="text" class="field-input" id="issRequisitioner" value="Engr. Danilo Ramos" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Work Order / Purpose</label>
                        <input type="text" class="field-input font-code" id="issPurpose" placeholder="e.g. WO-2026-0826" required>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Material Item & Quantities</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Material Code / Item</label>
                        <select class="field-select" id="issMaterial" required>
                            <option value="MAT-CU-001">MAT-CU-001 — Copper Conductor Wire 0.12mm (Oxygen-Free)</option>
                            <option value="MAT-PET-004">MAT-PET-004 — PET Insulation Film 0.05mm White</option>
                            <option value="MAT-ADH-012">MAT-ADH-012 — Thermosetting Heat-Seal Adhesive Resin</option>
                            <option value="MAT-BOX-020">MAT-BOX-020 — Heavy-Duty Corrugated Export Carton</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Warehouse Bin Rack</label>
                        <input type="text" class="field-input font-code" id="issLocation" value="Rack A-02 / Bin 14" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Quantity to Issue</label>
                        <input type="number" class="field-input" id="issQty" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Unit of Measure</label>
                        <select class="field-select" id="issUOM" required>
                            <option value="MTR">MTR - Meters</option>
                            <option value="ROLL">ROLL - Rolls</option>
                            <option value="DRUM">DRUM - Drums</option>
                            <option value="PCS">PCS - Pieces</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Store Custodian</label>
                        <input type="text" class="field-input" id="issCustodian" value="Alvin Mercado" required>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Remarks / Batch Special Instructions</label>
                        <textarea class="field-textarea" id="issRemarks" rows="2" placeholder="Line setup material, urgent shift continuation..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save & Post Issuance</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Supplies Issuance Slip</h3>
                <p class="modal-subtitle">Official material requisition voucher and store discharge verification.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Requisition Slip</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Issuance Slip</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel issuance <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will replenish the warehouse store balance.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-danger-solid" onclick="confirmDelete()">Confirm Cancellation</button>
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
        document.getElementById("filterDept").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterDept").value = "";
            document.getElementById("filterDateFrom").value = "";
            document.getElementById("filterDateTo").value = "";
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
        const dept = document.getElementById("filterDept").value.toLowerCase();
        const rows = document.querySelectorAll("#issuanceTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowDept = (row.getAttribute("data-dept") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchDept = !dept || rowDept.includes(dept);

            if (matchQuery && matchStatus && matchDept) {
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
        document.getElementById("entryModalTitle").innerText = "Create Supplies Issuance Slip";
        document.getElementById("saveBtnLabel").innerText = "Save & Post Issuance";
        document.getElementById("entryForm").reset();
        document.getElementById("issNumber").value = "ISS-2026-0045";
        document.getElementById("issDate").value = "2026-11-06";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editIssuance(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Issuance Slip ISS-2026-004" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Slip";

        if (id === '1') {
            document.getElementById("issNumber").value = "ISS-2026-0044";
            document.getElementById("issDate").value = "2026-11-06";
            document.getElementById("issStatus").value = "Issued";
            document.getElementById("issDept").value = "SMT Line 01";
            document.getElementById("issRequisitioner").value = "Engr. Danilo Ramos";
            document.getElementById("issPurpose").value = "WO-2026-0814";
            document.getElementById("issMaterial").value = "MAT-CU-001";
            document.getElementById("issLocation").value = "Rack A-02 / Bin 14";
            document.getElementById("issQty").value = "1800";
            document.getElementById("issUOM").value = "MTR";
            document.getElementById("issCustodian").value = "Alvin Mercado";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveIssuance(e) {
        e.preventDefault();
        alert("Supplies issuance successfully posted.");
        closeEntryModal();
    }

    function viewIssuance(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Issuance Slip No</span>
                    <span class="spec-value font-bold font-code">ISS-2026-0044</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Issuance Date</span>
                    <span class="spec-value font-numeric">2026-11-06</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Issued & Discharged</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Department</span>
                    <span class="spec-value">SMT Line 01</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Requisitioner</span>
                    <span class="spec-value">Engr. Danilo Ramos</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Work Order Ref</span>
                    <span class="spec-value font-code font-bold">WO-2026-0814</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Material Description</span>
                    <span class="spec-value font-bold">Copper Conductor Wire 0.12mm (MAT-CU-001)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Issued Quantity</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">1,800 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Store Valuation</span>
                    <span class="spec-value font-numeric font-bold">₱ 81,900.00</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printIssuance(id) {
        viewIssuance(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteIssuance(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "ISS-2026-004" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Issuance voucher cancelled.");
        closeDeleteModal();
    }

    function bulkIssue() {
        alert("Selected issuance requests confirmed.");
    }

    function bulkPrintSlips() {
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

