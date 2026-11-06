@extends('layouts.app')

@section('title', 'Finished Goods Stock Adjustment — Intracode ERP')

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
            <span class="bc-item">Finished Goods</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">FG Adjustment</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Adjustment No, Product, Authorizer..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Finished Goods Stock Adjustments</h1>
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
                            <small>Stock count discrepancy document</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Adjustment CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Spreadsheet of inventory adjustments</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create FG Adjustment</span>
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
            <label class="filter-label">Reason Code</label>
            <select class="filter-select" id="filterReason">
                <option value="">All Reasons</option>
                <option value="Cycle Count Variance">Cycle Count Variance</option>
                <option value="Packaging Damage">Packaging Damage</option>
                <option value="QA Quarantine Reclass">QA Quarantine Reclass</option>
                <option value="Customer Return Restock">Customer Return Restock</option>
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
            <span class="kpi-subtext">Fiscal year adjustment vouchers</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">NET QUANTITY VARIANCE</span>
            <span class="kpi-value font-numeric font-bold" style="color: #dc2626;" id="kpiNetVariance">-420 PCS</span>
            <span class="kpi-subtext">Net physical vs system stock</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">FINANCIAL IMPACT</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiImpact">-₱ 18,480.00</span>
            <span class="kpi-subtext">Net inventory write-off balance</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PENDING APPROVAL</span>
            <span class="kpi-value font-numeric" style="color: #2563eb;" id="kpiPending">1</span>
            <span class="kpi-subtext">Awaiting warehouse supervisor review</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Finished Goods Adjustment Log</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkApprove()">
                    <i data-lucide="check-circle-2"></i>
                    <span>Batch Approve</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintVouchers()">
                    <i data-lucide="printer"></i>
                    <span>Print Vouchers</span>
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
                        <th>PRODUCT CODE & DESCRIPTION</th>
                        <th>LOCATION</th>
                        <th class="text-right">BOOK QTY</th>
                        <th class="text-right">ACTUAL QTY</th>
                        <th class="text-right">VARIANCE</th>
                        <th>REASON CODE</th>
                        <th>AUTHORIZED BY</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="adjTableBody">
                    <tr data-id="1" data-code="FGA-2026-0012" data-reason="Cycle Count Variance" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('1')">FGA-2026-0012</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-8820-P1</span>
                                <span class="prod-sub">FFC Flexible Ribbon Cable 1.0mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="location-badge">Bay A-01 / Rack 4</span></td>
                        <td class="text-right font-numeric">35,500</td>
                        <td class="text-right font-numeric font-bold">35,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #dc2626;">-500</td>
                        <td><span class="category-pill">Cycle Count Variance</span></td>
                        <td>Engr. Danilo Ramos</td>
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
                                    <span>Print Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-code="FGA-2026-0011" data-reason="Customer Return Restock" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('2')">FGA-2026-0011</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-5512-Y4</span>
                                <span class="prod-sub">Shielded Harness Wire Assembly 0.5mm</span>
                            </div>
                        </td>
                        <td><span class="location-badge">Bay A-03 / Rack 2</span></td>
                        <td class="text-right font-numeric">18,200</td>
                        <td class="text-right font-numeric font-bold">18,400</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">+200</td>
                        <td><span class="category-pill">Customer Return Restock</span></td>
                        <td>Alvin Mercado</td>
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
                                    <span>Print Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-code="FGA-2026-0010" data-reason="Packaging Damage" data-status="Pending Review">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('3')">FGA-2026-0010</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-9910-F2</span>
                                <span class="prod-sub">Fine Pitch Jumper Lead Assembly</span>
                            </div>
                        </td>
                        <td><span class="location-badge">Bay B-01 / Rack 1</span></td>
                        <td class="text-right font-numeric">12,120</td>
                        <td class="text-right font-numeric font-bold">12,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #dc2626;">-120</td>
                        <td><span class="category-pill">Packaging Damage</span></td>
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
                                    <span>Print Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-code="FGA-2026-0009" data-reason="Cycle Count Variance" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewAdjustment('4')">FGA-2026-0009</a>
                        </td>
                        <td class="font-numeric">2026-11-01</td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-3301-C8</span>
                                <span class="prod-sub">Ultra-Flex Flat Cable 1.25mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="location-badge">Bay B-04 / Rack 3</span></td>
                        <td class="text-right font-numeric">22,000</td>
                        <td class="text-right font-numeric font-bold">22,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #64748b;">0</td>
                        <td><span class="category-pill">Cycle Count Variance</span></td>
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
                                    <span>Print Voucher</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteAdjustment('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Void</span>
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
                <h3 class="modal-title" id="entryModalTitle">Create Finished Goods Stock Adjustment</h3>
                <p class="modal-subtitle">Document cycle count variance or damage write-offs with audit justifications.</p>
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
                        <input type="text" class="field-input font-code" id="adjNumber" value="FGA-2026-0013" required>
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
                    <span>Item & Stock Discrepancy</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Finished Good Item</label>
                        <select class="field-select" id="adjProduct" required>
                            <option value="TFL-8820-P1">TFL-8820-P1 — FFC Flexible Ribbon Cable 1.0mm Pitch</option>
                            <option value="TFL-5512-Y4">TFL-5512-Y4 — Shielded Harness Wire Assembly 0.5mm</option>
                            <option value="TFL-9910-F2">TFL-9910-F2 — Fine Pitch Jumper Lead Assembly</option>
                            <option value="TFL-3301-C8">TFL-3301-C8 — Ultra-Flex Flat Cable 1.25mm Pitch</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Warehouse Location / Bay</label>
                        <input type="text" class="field-input" id="adjLocation" value="Bay A-01 / Rack 4" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Book (System) Quantity</label>
                        <input type="number" class="field-input" id="adjBookQty" value="35000" oninput="calcVariance()" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Actual Counted Quantity</label>
                        <input type="number" class="field-input" id="adjActualQty" value="35000" oninput="calcVariance()" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Calculated Variance</label>
                        <input type="text" class="field-input font-bold" id="adjVariance" value="0" readonly style="background:#f1f5f9;">
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Reason Code</label>
                        <select class="field-select" id="adjReason" required>
                            <option value="Cycle Count Variance">Cycle Count Variance</option>
                            <option value="Packaging Damage">Packaging Damage</option>
                            <option value="QA Quarantine Reclass">QA Quarantine Reclass</option>
                            <option value="Customer Return Restock">Customer Return Restock</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Authorized Approver</label>
                        <input type="text" class="field-input" id="adjAuthorizer" value="Engr. Danilo Ramos" required>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Audit Notes & Technical Justification</label>
                        <textarea class="field-textarea" id="adjNotes" rows="2" placeholder="Detailed reason for physical discrepancy, count cycle verification details..."></textarea>
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
                <h3 class="modal-title" id="viewModalTitle">Finished Goods Adjustment Voucher</h3>
                <p class="modal-subtitle">Authorized stock reconciliation record with ledger impact.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Official Voucher</span>
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
            <p>Are you sure you want to void adjustment <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will reverse the inventory ledger adjustment.</p>
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
        const reason = document.getElementById("filterReason").value.toLowerCase();
        const rows = document.querySelectorAll("#adjTableBody tr");

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
        document.getElementById("entryModalTitle").innerText = "Create Finished Goods Stock Adjustment";
        document.getElementById("saveBtnLabel").innerText = "Save Adjustment";
        document.getElementById("entryForm").reset();
        document.getElementById("adjNumber").value = "FGA-2026-0013";
        document.getElementById("adjDate").value = "2026-11-06";
        document.getElementById("adjBookQty").value = "35000";
        document.getElementById("adjActualQty").value = "35000";
        calcVariance();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editAdjustment(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Adjustment Voucher " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Voucher";

        if (id === '1') {
            document.getElementById("adjNumber").value = "FGA-2026-0012";
            document.getElementById("adjDate").value = "2026-11-05";
            document.getElementById("adjStatus").value = "Approved";
            document.getElementById("adjProduct").value = "TFL-8820-P1";
            document.getElementById("adjLocation").value = "Bay A-01 / Rack 4";
            document.getElementById("adjBookQty").value = "35500";
            document.getElementById("adjActualQty").value = "35000";
            document.getElementById("adjReason").value = "Cycle Count Variance";
            document.getElementById("adjAuthorizer").value = "Engr. Danilo Ramos";
            calcVariance();
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveAdjustment(e) {
        e.preventDefault();
        alert("Stock adjustment successfully saved and posted.");
        closeEntryModal();
    }

    function viewAdjustment(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Adjustment No</span>
                    <span class="spec-value font-bold font-code">FGA-2026-0012</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Date Recorded</span>
                    <span class="spec-value font-numeric">2026-11-05</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Approval Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Approved & Posted</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Product Code</span>
                    <span class="spec-value font-code font-bold">TFL-8820-P1</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Bay Location</span>
                    <span class="spec-value font-code">Bay A-01 / Rack 4</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Authorized Approver</span>
                    <span class="spec-value">Engr. Danilo Ramos</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Stock Book Qty</span>
                    <span class="spec-value font-numeric">35,500 PCS</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Actual Count Qty</span>
                    <span class="spec-value font-numeric font-bold">35,000 PCS</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Net Variance</span>
                    <span class="spec-value font-numeric font-bold" style="color: #dc2626;">-500 PCS (-₱ 22,000.00)</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="form-section-header">
                    <span class="step-num"><i data-lucide="file-text" style="width: 14px; height: 14px;"></i></span>
                    <span>Reason & Audit Notes</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; font-size: 13px; color: #334155;">
                    Discrepancy identified during bi-monthly physical cycle count on Bay A-01. Sealed carton count revealed 1 missing carton from Lot 2610-09. Stock ledger adjusted to reflect true on-floor physical availability.
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
        document.getElementById("deleteTargetCode").innerText = "FGA-2026-001" + id;
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
        alert("Selected adjustments approved.");
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

