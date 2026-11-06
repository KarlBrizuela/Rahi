@extends('layouts.app')

@section('title', 'Supplies Stock Adjustment — Intracode ERP')

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
            <span class="bc-current">Supplies Adjustment</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Adjustment No, Material, Reason..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplies & Raw Materials Adjustments</h1>
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
                            <strong>Print Variance Report</strong>
                            <small>Supplies inventory adjustment voucher</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Adjustment CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Raw materials variance log</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Material Adjustment</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Approved">Approved & Posted</option>
                <option value="Pending Review">Pending Review</option>
                <option value="Rejected">Rejected</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="filterCat">
                <option value="">All Categories</option>
                <option value="Conductors">Conductors & Wire</option>
                <option value="Insulation">Insulation & Sheathing</option>
                <option value="Chemicals">Chemicals & Resins</option>
                <option value="Packaging">Packaging Supplies</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Adjustment Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Adjustment Date To</label>
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
            <span class="kpi-label">TOTAL ADJUSTMENTS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Material balance variance tickets</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">NET ADJUSTMENT VALUE</span>
            <span class="kpi-value font-numeric font-bold" style="color: #dc2626;" id="kpiNetValue">-₱ 9,100.00</span>
            <span class="kpi-subtext">Net inventory financial reconciliation</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">NEGATIVE DISCREPANCIES</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiNeg">2</span>
            <span class="kpi-subtext">Physical counts below book inventory</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PENDING APPROVAL</span>
            <span class="kpi-value font-numeric" style="color: #2563eb;" id="kpiPending">1</span>
            <span class="kpi-subtext">Requires plant manager sign-off</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Supplies & Raw Materials Adjustment Log</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkApprove()">
                    <i data-lucide="check-circle-2"></i>
                    <span>Approve Selected</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintVouchers()">
                    <i data-lucide="printer"></i>
                    <span>Print Slips</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="adjTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>ADJUSTMENT NO</th>
                        <th>DATE</th>
                        <th>MATERIAL CODE & DESCRIPTION</th>
                        <th>CATEGORY</th>
                        <th class="text-right">BOOK QTY</th>
                        <th class="text-right">ACTUAL QTY</th>
                        <th class="text-right">VARIANCE</th>
                        <th>REASON</th>
                        <th>AUTHORIZED BY</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="adjTableBody">
                    <tr data-id="1" data-cat="Conductors" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('1')">SMA-2026-0021</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-CU-001</span>
                                <span class="prod-sub">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Conductors</span></td>
                        <td class="text-right font-numeric">14,400 MTR</td>
                        <td class="text-right font-numeric font-bold">14,200 MTR</td>
                        <td class="text-right font-numeric font-bold" style="color: #dc2626;">-200 MTR</td>
                        <td><span class="category-pill">Spool Tension Waste</span></td>
                        <td>Alvin Mercado</td>
                        <td><span class="status-badge status-completed">Approved</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewAdjustment('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editAdjustment('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printVoucher('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void Voucher</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-cat="Packaging" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('2')">SMA-2026-0020</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-BOX-020</span>
                                <span class="prod-sub">Heavy-Duty Corrugated Export Carton 450x300x250mm</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Packaging</span></td>
                        <td class="text-right font-numeric">11,150 PCS</td>
                        <td class="text-right font-numeric font-bold">11,200 PCS</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">+50 PCS</td>
                        <td><span class="category-pill">Count Reconciliation</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-completed">Approved</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewAdjustment('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editAdjustment('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printVoucher('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void Voucher</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-cat="Insulation" data-status="Pending Review">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('3')">SMA-2026-0019</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-PET-004</span>
                                <span class="prod-sub">PET Insulation Film 0.05mm White</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Insulation</span></td>
                        <td class="text-right font-numeric">2,850 ROLL</td>
                        <td class="text-right font-numeric font-bold">2,800 ROLL</td>
                        <td class="text-right font-numeric font-bold" style="color: #dc2626;">-50 ROLL</td>
                        <td><span class="category-pill">Moisture Damage Write-off</span></td>
                        <td>Nestor Bautista</td>
                        <td><span class="status-badge status-reorder">Pending Review</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewAdjustment('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editAdjustment('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printVoucher('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void Voucher</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-cat="Chemicals" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('4')">SMA-2026-0018</a>
                        </td>
                        <td class="font-numeric">2026-11-01</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-ADH-012</span>
                                <span class="prod-sub">Thermosetting Heat-Seal Adhesive Resin</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Chemicals</span></td>
                        <td class="text-right font-numeric">18 DRUM</td>
                        <td class="text-right font-numeric font-bold">18 DRUM</td>
                        <td class="text-right font-numeric font-bold" style="color: #64748b;">0</td>
                        <td><span class="category-pill">Annual Audit Verification</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-completed">Approved</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewAdjustment('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editAdjustment('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printVoucher('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void Voucher</span>
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
                <h3 class="modal-title" id="entryModalTitle">Create Supplies Stock Adjustment</h3>
                <p class="modal-subtitle">Document material count discrepancies, moisture damage, or scrap reconciliations.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveAdjustment(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Adjustment Voucher Identification</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Adjustment No</label>
                        <input type="text" class="field-input font-code" id="adjNumber" value="SMA-2026-0022" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Adjustment Date</label>
                        <input type="date" class="field-input" id="adjDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="adjStatus" required>
                            <option value="Pending Review">Pending Review</option>
                            <option value="Approved">Approved</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Raw Material Item & Discrepancy</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Material Item</label>
                        <select class="field-select" id="adjMaterial" required>
                            <option value="MAT-CU-001">MAT-CU-001 — Copper Conductor Wire 0.12mm (Oxygen-Free)</option>
                            <option value="MAT-PET-004">MAT-PET-004 — PET Insulation Film 0.05mm White</option>
                            <option value="MAT-ADH-012">MAT-ADH-012 — Thermosetting Heat-Seal Adhesive Resin</option>
                            <option value="MAT-BOX-020">MAT-BOX-020 — Heavy-Duty Corrugated Export Carton</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Storage Rack / Bin</label>
                        <input type="text" class="field-input" id="adjLocation" value="Rack A-02 / Bin 14" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Book (System) Quantity</label>
                        <input type="number" class="field-input" id="adjBookQty" value="14400" oninput="calcVariance()" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Actual Physical Quantity</label>
                        <input type="number" class="field-input" id="adjActualQty" value="14200" oninput="calcVariance()" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Calculated Variance</label>
                        <input type="text" class="field-input font-bold" id="adjVariance" value="-200" readonly style="background:#f1f5f9; color:#dc2626;">
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Reason Code</label>
                        <select class="field-select" id="adjReason" required>
                            <option value="Spool Tension Waste">Spool Tension Waste / Scrap</option>
                            <option value="Count Reconciliation">Physical Count Reconciliation</option>
                            <option value="Moisture Damage Write-off">Moisture Damage Write-off</option>
                            <option value="Handling Breakage">Handling Breakage / Expired Shelf-Life</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Authorized Officer</label>
                        <input type="text" class="field-input" id="adjAuthorizer" value="Engr. Danilo Ramos" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Adjustment</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Supplies Adjustment Voucher</h3>
                <p class="modal-subtitle">Authorized material adjustment slip with valuation impact.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Official Slip</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Void Adjustment Voucher</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to void voucher <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will revert the material ledger discrepancy balance.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-danger-solid" onclick="confirmDelete()">Confirm Void</button>
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
        document.getElementById("filterCat").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterCat").value = "";
            filterTable();
        });

        document.getElementById("btnRefresh").addEventListener("click", function () {
            const icon = document.getElementById("refreshIcon");
            icon.classList.add("spin");
            setTimeout(() => icon.classList.remove("spin"), 600);
        });
    });

    function calcVariance() {
        const book = parseFloat(document.getElementById("adjBookQty").value) || 0;
        const actual = parseFloat(document.getElementById("adjActualQty").value) || 0;
        const diff = actual - book;
        const varInput = document.getElementById("adjVariance");
        varInput.value = (diff > 0 ? "+" : "") + diff;
        varInput.style.color = diff < 0 ? "#dc2626" : (diff > 0 ? "#059669" : "#1e293b");
    }

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
        const cat = document.getElementById("filterCat").value.toLowerCase();
        const rows = document.querySelectorAll("#adjTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowCat = (row.getAttribute("data-cat") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchCat = !cat || rowCat.includes(cat);

            if (matchQuery && matchStatus && matchCat) {
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
        document.getElementById("entryModalTitle").innerText = "Create Supplies Stock Adjustment";
        document.getElementById("saveBtnLabel").innerText = "Save Adjustment";
        document.getElementById("entryForm").reset();
        document.getElementById("adjNumber").value = "SMA-2026-0022";
        document.getElementById("adjDate").value = "2026-11-06";
        document.getElementById("adjBookQty").value = "14400";
        document.getElementById("adjActualQty").value = "14200";
        calcVariance();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editAdjustment(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Material Adjustment " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Voucher";

        if (id === '1') {
            document.getElementById("adjNumber").value = "SMA-2026-0021";
            document.getElementById("adjDate").value = "2026-11-05";
            document.getElementById("adjStatus").value = "Approved";
            document.getElementById("adjMaterial").value = "MAT-CU-001";
            document.getElementById("adjLocation").value = "Rack A-02 / Bin 14";
            document.getElementById("adjBookQty").value = "14400";
            document.getElementById("adjActualQty").value = "14200";
            document.getElementById("adjReason").value = "Spool Tension Waste";
            document.getElementById("adjAuthorizer").value = "Alvin Mercado";
            calcVariance();
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveAdjustment(e) {
        e.preventDefault();
        alert("Supplies stock adjustment posted successfully.");
        closeEntryModal();
    }

    function viewAdjustment(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Voucher Number</span>
                    <span class="spec-value font-bold font-code">SMA-2026-0021</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Adjustment Date</span>
                    <span class="spec-value font-numeric">2026-11-05</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Approved & Posted</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Material Code</span>
                    <span class="spec-value font-bold font-code">MAT-CU-001</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Location</span>
                    <span class="spec-value font-code">Rack A-02 / Bin 14</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Authorized By</span>
                    <span class="spec-value">Alvin Mercado</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Book (System) Balance</span>
                    <span class="spec-value font-numeric">14,400 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Actual Count</span>
                    <span class="spec-value font-numeric font-bold">14,200 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Variance & Valuation</span>
                    <span class="spec-value font-numeric font-bold" style="color: #dc2626;">-200 MTR (-₱ 9,100.00)</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printVoucher(id) {
        viewAdjustment(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteAdjustment(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "SMA-2026-002" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Adjustment voucher voided.");
        closeDeleteModal();
    }

    function bulkApprove() {
        alert("Selected vouchers approved.");
    }

    function bulkPrintVouchers() {
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

