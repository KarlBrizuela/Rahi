@extends('layouts.app')

@section('title', 'Supplies & Material Return — Intracode ERP')

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
            <span class="bc-current">Supplies Return</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Return No, Issuance Ref, Material..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplies & Raw Materials Return Register</h1>
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
                            <strong>Print Return Slips</strong>
                            <small>Material return credit documentation</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Return CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Raw materials return register spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Log Material Return</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Return Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Restocked">Restocked into Store</option>
                <option value="Pending QA Inspection">Pending QA Inspection</option>
                <option value="Scrapped">Defective / Scrapped</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Return Reason</label>
            <select class="filter-select" id="filterReason">
                <option value="">All Reasons</option>
                <option value="Excess From Production">Excess From Production</option>
                <option value="Off-Spec Diameter">Off-Spec Diameter</option>
                <option value="Shift Order Cancellation">Shift Order Cancellation</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Return Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Return Date To</label>
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
            <span class="kpi-label">TOTAL RETURNS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Return to warehouse vouchers</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">RESTOCKED VALUATION</span>
            <span class="kpi-value font-numeric font-bold" style="color: #059669;" id="kpiValuation">₱ 52,430.00</span>
            <span class="kpi-subtext">Restored to active store balance</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">EXCESS MATERIAL RATE</span>
            <span class="kpi-value font-numeric" style="color: #2563eb;" id="kpiExcessRate">75.0%</span>
            <span class="kpi-subtext">Usable materials returned clean</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">QUALITY DEFECT RETURNS</span>
            <span class="kpi-value font-numeric" style="color: #dc2626;" id="kpiDefects">1</span>
            <span class="kpi-subtext">Quarantined for vendor claim</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Shopfloor Material Return Manifest</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkRestock()">
                    <i data-lucide="archive"></i>
                    <span>Batch Restock</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintReturns()">
                    <i data-lucide="printer"></i>
                    <span>Print Return Slips</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="returnTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>RETURN NO</th>
                        <th>DATE</th>
                        <th>ISSUANCE REF</th>
                        <th>RETURNING DEPT</th>
                        <th>MATERIAL CODE & DESCRIPTION</th>
                        <th class="text-right">RETURNED QTY</th>
                        <th>UOM</th>
                        <th>REASON</th>
                        <th>RECEIVED BY</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="returnTableBody">
                    <tr data-id="1" data-reason="Excess From Production" data-status="Restocked">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReturn('1')">RET-2026-0012</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td><span class="font-code font-bold">ISS-2026-0044</span></td>
                        <td>SMT Line 01</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-CU-001</span>
                                <span class="prod-sub">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">200</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td><span class="category-pill">Excess From Production</span></td>
                        <td>Alvin Mercado</td>
                        <td><span class="status-badge status-completed">Restocked</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewReturn('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReturn('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReturn('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReturn('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Return</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-reason="Excess From Production" data-status="Restocked">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReturn('2')">RET-2026-0011</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td><span class="font-code font-bold">ISS-2026-0043</span></td>
                        <td>Extrusion Shop</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-PET-004</span>
                                <span class="prod-sub">PET Insulation Film 0.05mm White</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">25</td>
                        <td><span class="uom-pill">ROLL</span></td>
                        <td><span class="category-pill">Excess From Production</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-completed">Restocked</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewReturn('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReturn('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReturn('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReturn('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Return</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-reason="Off-Spec Diameter" data-status="Pending QA Inspection">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReturn('3')">RET-2026-0010</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td><span class="font-code font-bold">ISS-2026-0040</span></td>
                        <td>SMT Line 02</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-CU-001</span>
                                <span class="prod-sub">Copper Conductor Wire 0.12mm</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #dc2626;">400</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td><span class="category-pill" style="background:#fee2e2; color:#b91c1c;">Off-Spec Diameter</span></td>
                        <td>Nestor Bautista</td>
                        <td><span class="status-badge status-reorder">Pending QA Inspection</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewReturn('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReturn('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReturn('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReturn('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Return</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-reason="Shift Order Cancellation" data-status="Restocked">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReturn('4')">RET-2026-0009</a>
                        </td>
                        <td class="font-numeric">2026-11-02</td>
                        <td><span class="font-code font-bold">ISS-2026-0038</span></td>
                        <td>Packaging Cell</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-BOX-020</span>
                                <span class="prod-sub">Heavy-Duty Corrugated Export Carton</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">100</td>
                        <td><span class="uom-pill">PCS</span></td>
                        <td><span class="category-pill">Shift Order Cancellation</span></td>
                        <td>Alvin Mercado</td>
                        <td><span class="status-badge status-completed">Restocked</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewReturn('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReturn('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Slip</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReturn('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReturn('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Return</span>
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
                <h3 class="modal-title" id="entryModalTitle">Log Material Return to Warehouse</h3>
                <p class="modal-subtitle">Record unconsumed or rejected shopfloor materials returned to raw materials stock.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveReturn(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Return Reference & Department</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Return Number</label>
                        <input type="text" class="field-input font-code" id="retNumber" value="RET-2026-0013" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Date of Return</label>
                        <input type="date" class="field-input" id="retDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="retStatus" required>
                            <option value="Restocked">Restocked into Store</option>
                            <option value="Pending QA Inspection">Pending QA Inspection</option>
                            <option value="Scrapped">Defective / Scrapped</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Original Issuance Ref</label>
                        <input type="text" class="field-input font-code" id="retIssRef" placeholder="e.g. ISS-2026-0044" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Returning Department</label>
                        <select class="field-select" id="retDept" required>
                            <option value="SMT Line 01">SMT Line 01</option>
                            <option value="SMT Line 02">SMT Line 02</option>
                            <option value="Extrusion Shop">Extrusion Shop</option>
                            <option value="Packaging Cell">Packaging Cell</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Material Specification & Returned Qty</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Material Item</label>
                        <select class="field-select" id="retMaterial" required>
                            <option value="MAT-CU-001">MAT-CU-001 — Copper Conductor Wire 0.12mm (Oxygen-Free)</option>
                            <option value="MAT-PET-004">MAT-PET-004 — PET Insulation Film 0.05mm White</option>
                            <option value="MAT-ADH-012">MAT-ADH-012 — Thermosetting Heat-Seal Adhesive Resin</option>
                            <option value="MAT-BOX-020">MAT-BOX-020 — Heavy-Duty Corrugated Export Carton</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Restock Bin / Rack</label>
                        <input type="text" class="field-input font-code" id="retLocation" value="Rack A-02 / Bin 14" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Returned Quantity</label>
                        <input type="number" class="field-input" id="retQty" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Return Reason</label>
                        <select class="field-select" id="retReason" required>
                            <option value="Excess From Production">Excess From Production</option>
                            <option value="Off-Spec Diameter">Off-Spec Diameter</option>
                            <option value="Shift Order Cancellation">Shift Order Cancellation</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Warehouse Receiver</label>
                        <input type="text" class="field-input" id="retReceiver" value="Alvin Mercado" required>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Inspection Notes & QA Disposition</label>
                        <textarea class="field-textarea" id="retNotes" rows="2" placeholder="Visual inspection details, seal intactness, QA clearance status..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Material Return</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Supplies Return Credit Voucher</h3>
                <p class="modal-subtitle">Official stock return receipt and inventory restitution record.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Return Voucher</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Return Slip</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel return voucher <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will revert the restocked inventory balance.</p>
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
        document.getElementById("filterReason").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterReason").value = "";
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
        const reason = document.getElementById("filterReason").value.toLowerCase();
        const rows = document.querySelectorAll("#returnTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowReason = (row.getAttribute("data-reason") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchReason = !reason || rowReason.includes(reason);

            if (matchQuery && matchStatus && matchReason) {
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
        document.getElementById("entryModalTitle").innerText = "Log Material Return to Warehouse";
        document.getElementById("saveBtnLabel").innerText = "Save Material Return";
        document.getElementById("entryForm").reset();
        document.getElementById("retNumber").value = "RET-2026-0013";
        document.getElementById("retDate").value = "2026-11-06";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editReturn(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Return Voucher RET-2026-001" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Voucher";

        if (id === '1') {
            document.getElementById("retNumber").value = "RET-2026-0012";
            document.getElementById("retDate").value = "2026-11-05";
            document.getElementById("retStatus").value = "Restocked";
            document.getElementById("retIssRef").value = "ISS-2026-0044";
            document.getElementById("retDept").value = "SMT Line 01";
            document.getElementById("retMaterial").value = "MAT-CU-001";
            document.getElementById("retLocation").value = "Rack A-02 / Bin 14";
            document.getElementById("retQty").value = "200";
            document.getElementById("retReason").value = "Excess From Production";
            document.getElementById("retReceiver").value = "Alvin Mercado";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveReturn(e) {
        e.preventDefault();
        alert("Material return credited and restocked.");
        closeEntryModal();
    }

    function viewReturn(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Return Voucher No</span>
                    <span class="spec-value font-bold font-code">RET-2026-0012</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Return Date</span>
                    <span class="spec-value font-numeric">2026-11-05</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Restocked into Store</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Original Issuance</span>
                    <span class="spec-value font-code font-bold">ISS-2026-0044</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Department</span>
                    <span class="spec-value">SMT Line 01</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Restocked Bin Location</span>
                    <span class="spec-value font-code">Rack A-02 / Bin 14</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Material Name</span>
                    <span class="spec-value font-bold">Copper Conductor Wire 0.12mm (MAT-CU-001)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Returned Quantity</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">200 MTR (+₱ 9,100.00)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Reason Code</span>
                    <span class="spec-value"><span class="category-pill">Excess From Production</span></span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printReturn(id) {
        viewReturn(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteReturn(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "RET-2026-001" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Return voucher cancelled.");
        closeDeleteModal();
    }

    function bulkRestock() {
        alert("Selected returns confirmed and restocked.");
    }

    function bulkPrintReturns() {
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

