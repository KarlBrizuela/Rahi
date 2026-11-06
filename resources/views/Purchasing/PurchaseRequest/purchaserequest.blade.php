@extends('layouts.app')

@section('title', 'Purchase Requests (PR) — Intracode ERP')

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
            <span class="bc-current">Purchase Request</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search PR Number, Requisitioner, Purpose..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Purchase Requisition Register</h1>
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
                            <strong>Print Requisition Log</strong>
                            <small>Print purchase requisitions summary</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting PR CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Download purchase requests data</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Purchase Request</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Approved">Approved for PO</option>
                <option value="Pending Approval">Pending Approval</option>
                <option value="Ordered">PO Issued</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Priority</label>
            <select class="filter-select" id="filterPriority">
                <option value="">All Priorities</option>
                <option value="Urgent">Urgent (Expedited)</option>
                <option value="High">High Priority</option>
                <option value="Standard">Standard</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Requesting Dept</label>
            <select class="filter-select" id="filterDept">
                <option value="">All Departments</option>
                <option value="Production SMT">Production SMT</option>
                <option value="Extrusion Dept">Extrusion Dept</option>
                <option value="Warehouse & Logistics">Warehouse & Logistics</option>
                <option value="Quality Assurance">Quality Assurance</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Date To</label>
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
            <span class="kpi-label">TOTAL REQUISITIONS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Active purchase requests</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL ESTIMATED VALUE</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiEstValue">₱ 1,485,200.00</span>
            <span class="kpi-subtext">Aggregated requisition value</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">PENDING MANAGEMENT REVIEW</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiPending">1</span>
            <span class="kpi-subtext">Awaiting budget authorization</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">URGENT EXPEDITE REQUESTS</span>
            <span class="kpi-value font-numeric" style="color: #dc2626;" id="kpiUrgent">1</span>
            <span class="kpi-subtext">Production line stoppage risk</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Purchase Requisition Register</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkApprovePR()">
                    <i data-lucide="check-circle-2"></i>
                    <span>Approve PR</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkGeneratePO()">
                    <i data-lucide="shopping-cart"></i>
                    <span>Convert to PO</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintPR()">
                    <i data-lucide="printer"></i>
                    <span>Print Requisitions</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="prTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>PR NUMBER</th>
                        <th>DATE</th>
                        <th>DEPARTMENT</th>
                        <th>REQUISITIONER</th>
                        <th>PURPOSE / REASON</th>
                        <th>PRIORITY</th>
                        <th>REQUIRED DATE</th>
                        <th class="text-right">EST. AMOUNT</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="prTableBody">
                    <tr data-id="1" data-dept="Production SMT" data-priority="Urgent" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPR('1')">PR-2026-0048</a>
                        </td>
                        <td class="font-numeric">2026-11-06</td>
                        <td>Production SMT</td>
                        <td>Engr. Danilo Ramos</td>
                        <td>Oxygen-Free Copper Wire replenishment for WO-0814</td>
                        <td><span class="status-badge" style="background:#fee2e2; color:#b91c1c;">Urgent</span></td>
                        <td class="font-numeric font-bold" style="color: #dc2626;">2026-11-12</td>
                        <td class="text-right font-numeric font-bold">₱ 364,000.00</td>
                        <td><span class="status-badge status-completed">Approved</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewPR('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPR('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPR('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PR</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePR('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PR</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-dept="Extrusion Dept" data-priority="High" data-status="Approved">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPR('2')">PR-2026-0047</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td>Extrusion Dept</td>
                        <td>Alvin Mercado</td>
                        <td>PET Insulation Film White standard 120mm rolls</td>
                        <td><span class="status-badge" style="background:#fef3c7; color:#92400e;">High</span></td>
                        <td class="font-numeric">2026-11-16</td>
                        <td class="text-right font-numeric font-bold">₱ 560,000.00</td>
                        <td><span class="status-badge status-completed">Approved</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewPR('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPR('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPR('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PR</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePR('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PR</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-dept="Warehouse & Logistics" data-priority="Standard" data-status="Ordered">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPR('3')">PR-2026-0046</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td>Warehouse & Logistics</td>
                        <td>Nestor Bautista</td>
                        <td>Export Heavy-Duty Corrugated Cartons bulk order</td>
                        <td><span class="status-badge" style="background:#f1f5f9; color:#475569;">Standard</span></td>
                        <td class="font-numeric">2026-11-20</td>
                        <td class="text-right font-numeric font-bold">₱ 367,750.00</td>
                        <td><span class="status-badge status-instock">PO Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewPR('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPR('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPR('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PR</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePR('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PR</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-dept="Production SMT" data-priority="Standard" data-status="Pending Approval">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPR('4')">PR-2026-0045</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td>Production SMT</td>
                        <td>Engr. Danilo Ramos</td>
                        <td>Thermosetting Heat-Seal Adhesive Resin Grade TS-88</td>
                        <td><span class="status-badge" style="background:#f1f5f9; color:#475569;">Standard</span></td>
                        <td class="font-numeric">2026-11-25</td>
                        <td class="text-right font-numeric font-bold">₱ 193,450.00</td>
                        <td><span class="status-badge status-reorder">Pending Approval</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewPR('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPR('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PR</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPR('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PR</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePR('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PR</span>
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
                <h3 class="modal-title" id="entryModalTitle">Create Purchase Requisition</h3>
                <p class="modal-subtitle">Initiate a formal procurement request for raw materials or industrial supplies.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="savePR(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Requisition Header & Origin</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">PR Number</label>
                        <input type="text" class="field-input font-code" id="prNumber" value="PR-2026-0049" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Requisition Date</label>
                        <input type="date" class="field-input" id="prDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Priority Level</label>
                        <select class="field-select" id="prPriority" required>
                            <option value="Standard">Standard (Normal Lead Time)</option>
                            <option value="High">High Priority</option>
                            <option value="Urgent">Urgent (Expedited Action)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Requesting Department</label>
                        <select class="field-select" id="prDept" required>
                            <option value="Production SMT">Production SMT</option>
                            <option value="Extrusion Dept">Extrusion Dept</option>
                            <option value="Warehouse & Logistics">Warehouse & Logistics</option>
                            <option value="Quality Assurance">Quality Assurance</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Requisitioner Name</label>
                        <input type="text" class="field-input" id="prRequisitioner" value="Engr. Danilo Ramos" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Required Delivery Date</label>
                        <input type="date" class="field-input" id="prRequiredDate" value="2026-11-15" required>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Material Specification & Estimated Cost</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Requested Material / Supply Item</label>
                        <select class="field-select" id="prMaterial" required>
                            <option value="MAT-CU-001">MAT-CU-001 — Copper Conductor Wire 0.12mm (Oxygen-Free)</option>
                            <option value="MAT-PET-004">MAT-PET-004 — PET Insulation Film 0.05mm White</option>
                            <option value="MAT-ADH-012">MAT-ADH-012 — Thermosetting Heat-Seal Adhesive Resin</option>
                            <option value="MAT-BOX-020">MAT-BOX-020 — Heavy-Duty Corrugated Export Carton</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Purpose / Job Order Reference</label>
                        <input type="text" class="field-input" id="prPurpose" placeholder="e.g. Replenishment for WO-2026-0828" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Requested Quantity</label>
                        <input type="number" class="field-input" id="prQty" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Unit of Measure</label>
                        <select class="field-select" id="prUOM" required>
                            <option value="MTR">MTR - Meters</option>
                            <option value="ROLL">ROLL - Rolls</option>
                            <option value="DRUM">DRUM - Drums</option>
                            <option value="PCS">PCS - Pieces</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Estimated Total Budget (PHP)</label>
                        <input type="number" step="0.01" class="field-input" id="prEstAmount" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Procurement Remarks / Preferred Supplier</label>
                        <textarea class="field-textarea" id="prRemarks" rows="2" placeholder="Preferred vendor Sumitomo Electric, delivery address Mandaluyong Plant..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Submit Requisition</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Purchase Requisition Specification</h3>
                <p class="modal-subtitle">Official requisition document and procurement routing detail.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Official PR</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Purchase Requisition</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel requisition <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will withdraw the purchase approval request.</p>
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
        document.getElementById("filterPriority").addEventListener("change", filterTable);
        document.getElementById("filterDept").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterPriority").value = "";
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
        const prio = document.getElementById("filterPriority").value.toLowerCase();
        const dept = document.getElementById("filterDept").value.toLowerCase();
        const rows = document.querySelectorAll("#prTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowDept = (row.getAttribute("data-dept") || "").toLowerCase();
            const rowPrio = (row.getAttribute("data-priority") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchPrio = !prio || rowPrio.includes(prio);
            const matchDept = !dept || rowDept.includes(dept);

            if (matchQuery && matchStatus && matchPrio && matchDept) {
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
        document.getElementById("entryModalTitle").innerText = "Create Purchase Requisition";
        document.getElementById("saveBtnLabel").innerText = "Submit Requisition";
        document.getElementById("entryForm").reset();
        document.getElementById("prNumber").value = "PR-2026-0049";
        document.getElementById("prDate").value = "2026-11-06";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editPR(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Purchase Requisition PR-2026-004" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Requisition";

        if (id === '1') {
            document.getElementById("prNumber").value = "PR-2026-0048";
            document.getElementById("prDate").value = "2026-11-06";
            document.getElementById("prPriority").value = "Urgent";
            document.getElementById("prDept").value = "Production SMT";
            document.getElementById("prRequisitioner").value = "Engr. Danilo Ramos";
            document.getElementById("prRequiredDate").value = "2026-11-12";
            document.getElementById("prMaterial").value = "MAT-CU-001";
            document.getElementById("prPurpose").value = "Oxygen-Free Copper Wire replenishment for WO-0814";
            document.getElementById("prQty").value = "8000";
            document.getElementById("prUOM").value = "MTR";
            document.getElementById("prEstAmount").value = "364000.00";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function savePR(e) {
        e.preventDefault();
        alert("Purchase requisition submitted for approval.");
        closeEntryModal();
    }

    function viewPR(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Requisition PR Number</span>
                    <span class="spec-value font-bold font-code">PR-2026-0048</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Date Submitted</span>
                    <span class="spec-value font-numeric">2026-11-06</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Approved for PO</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Requesting Department</span>
                    <span class="spec-value">Production SMT (Line 01)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Requisitioner</span>
                    <span class="spec-value">Engr. Danilo Ramos</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Priority Level</span>
                    <span class="spec-value"><span class="status-badge" style="background:#fee2e2; color:#b91c1c;">Urgent</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Requested Material</span>
                    <span class="spec-value font-bold">Copper Conductor Wire 0.12mm (MAT-CU-001)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Requested Qty</span>
                    <span class="spec-value font-numeric font-bold">8,000 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Estimated Budget</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">₱ 364,000.00</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="form-section-header">
                    <span class="step-num"><i data-lucide="clipboard-list" style="width: 14px; height: 14px;"></i></span>
                    <span>Procurement Justification & Technical Requirements</span>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 12px; font-size: 13px; color: #334155;">
                    Immediate replenishment required for upcoming high-volume export run under Work Order WO-2026-0814. Oxygen-free grade copper with RoHS 3 compliance certificate required upon factory intake.
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printPR(id) {
        viewPR(id);
        setTimeout(() => window.print(), 300);
    }

    function deletePR(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "PR-2026-004" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Purchase requisition cancelled.");
        closeDeleteModal();
    }

    function bulkApprovePR() {
        alert("Selected purchase requisitions approved.");
    }

    function bulkGeneratePO() {
        alert("Convert to Purchase Order wizard initiated.");
    }

    function bulkPrintPR() {
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

