@extends('layouts.app')

@section('title', 'Delivery Transfer Slip (DTS) — Intracode ERP')

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
            <span class="bc-current">Delivery Transfer Slip</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search DTS#, DR Ref, Destination Hub..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Delivery Transfer Slip (DTS) Register</h1>
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
                            <strong>Print Manifest</strong>
                            <small>Print selected DTS dispatch documents</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting DTS Log...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Transfer log spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Transfer Slip</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Transfer Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="In Transit">In Transit</option>
                <option value="Completed">Completed / Received</option>
                <option value="Draft">Draft / Staging</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Destination Hub</label>
            <select class="filter-select" id="filterDestination">
                <option value="">All Destinations</option>
                <option value="Laguna Logistics Hub">Laguna Logistics Hub</option>
                <option value="Batangas Port Staging">Batangas Port Staging</option>
                <option value="Cebu Forwarding Bay">Cebu Forwarding Bay</option>
                <option value="Clark Air Freight Bay">Clark Air Freight Bay</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Transfer Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Transfer Date To</label>
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
            <span class="kpi-label">TOTAL DISPATCH SLIPS</span>
            <span class="kpi-value font-numeric" id="kpiTotalSlips">4</span>
            <span class="kpi-subtext">Registered transfer documents</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">IN TRANSIT</span>
            <span class="kpi-value font-numeric" style="color: #2563eb;" id="kpiInTransit">2</span>
            <span class="kpi-subtext">Active on-road freight movements</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL CARTONS DISPATCHED</span>
            <span class="kpi-value font-numeric font-bold" id="kpiCartons">1,480</span>
            <span class="kpi-subtext">Aggregated packaging units</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL NET WEIGHT</span>
            <span class="kpi-value font-numeric" style="color: #059669;" id="kpiWeight">12,650.4 KG</span>
            <span class="kpi-subtext">Combined gross freight payload</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Finished Goods Delivery Transfer Manifests</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkPrintManifest()">
                    <i data-lucide="printer"></i>
                    <span>Print Manifests</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkDispatch()">
                    <i data-lucide="truck"></i>
                    <span>Confirm Dispatch</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="dtsTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>DTS NUMBER</th>
                        <th>DISPATCH DATE</th>
                        <th>SOURCE PLANT</th>
                        <th>DESTINATION HUB</th>
                        <th>DR / SI REFERENCE</th>
                        <th>CARRIER / TRUCK #</th>
                        <th class="text-right">CARTONS</th>
                        <th class="text-right">WEIGHT (KG)</th>
                        <th>PREPARED BY</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="dtsTableBody">
                    <tr data-id="1" data-dts="DTS-2026-0041" data-dest="Laguna Logistics Hub" data-status="In Transit">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewDts('1')">DTS-2026-0041</a>
                        </td>
                        <td class="font-numeric">2026-11-06</td>
                        <td>Plant 1 (Mandaluyong FG Central)</td>
                        <td><span class="location-badge">Laguna Logistics Hub</span></td>
                        <td><span class="font-code font-bold">DR-2026-0089</span></td>
                        <td>DHL Express / NCK-4912</td>
                        <td class="text-right font-numeric font-bold">420</td>
                        <td class="text-right font-numeric">3,540.0</td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-intransit">In Transit</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewDts('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editDts('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printDts('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteDts('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel DTS</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-dts="DTS-2026-0040" data-dest="Batangas Port Staging" data-status="Completed">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewDts('2')">DTS-2026-0040</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td>Plant 1 (Mandaluyong FG Central)</td>
                        <td><span class="location-badge">Batangas Port Staging</span></td>
                        <td><span class="font-code font-bold">SI-EXP-2026-008</span></td>
                        <td>FastFreight Logistics / WET-9182</td>
                        <td class="text-right font-numeric font-bold">580</td>
                        <td class="text-right font-numeric">5,120.4</td>
                        <td>Alvin Mercado</td>
                        <td><span class="status-badge status-completed">Completed</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewDts('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editDts('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printDts('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteDts('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel DTS</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-dts="DTS-2026-0039" data-dest="Clark Air Freight Bay" data-status="In Transit">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewDts('3')">DTS-2026-0039</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td>Plant 1 (Mandaluyong FG Central)</td>
                        <td><span class="location-badge">Clark Air Freight Bay</span></td>
                        <td><span class="font-code font-bold">SI-EXP-2026-007</span></td>
                        <td>Nippon Express / CBX-8819</td>
                        <td class="text-right font-numeric font-bold">320</td>
                        <td class="text-right font-numeric">2,680.0</td>
                        <td>Nestor Bautista</td>
                        <td><span class="status-badge status-intransit">In Transit</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewDts('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editDts('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printDts('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteDts('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel DTS</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-dts="DTS-2026-0038" data-dest="Cebu Forwarding Bay" data-status="Draft">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewDts('4')">DTS-2026-0038</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td>Plant 1 (Mandaluyong FG Central)</td>
                        <td><span class="location-badge">Cebu Forwarding Bay</span></td>
                        <td><span class="font-code font-bold">DR-2026-0085</span></td>
                        <td>2GO Logistics / MV-SAN-PEDRO</td>
                        <td class="text-right font-numeric font-bold">160</td>
                        <td class="text-right font-numeric">1,310.0</td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-draft">Draft</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewDts('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editDts('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit DTS</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printDts('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Slip</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteDts('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel DTS</span>
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
                <h3 class="modal-title" id="entryModalTitle">Create Delivery Transfer Slip</h3>
                <p class="modal-subtitle">Generate internal inter-plant warehouse transfer document and carrier manifest.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveDts(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Transfer Route & Dispatch Information</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">DTS Number</label>
                        <input type="text" class="field-input font-code" id="dtsNumber" value="DTS-2026-0042" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Dispatch Date</label>
                        <input type="date" class="field-input" id="dtsDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="dtsStatus" required>
                            <option value="Draft">Draft / Staging</option>
                            <option value="In Transit" selected>In Transit</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Source Warehouse / Plant</label>
                        <select class="field-select" id="dtsSource" required>
                            <option value="Plant 1 (Mandaluyong FG Central)">Plant 1 (Mandaluyong FG Central)</option>
                            <option value="Plant 2 (Cavite Sub-assembly)">Plant 2 (Cavite Sub-assembly)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Destination Hub / Facility</label>
                        <select class="field-select" id="dtsDestination" required>
                            <option value="Laguna Logistics Hub">Laguna Logistics Hub</option>
                            <option value="Batangas Port Staging">Batangas Port Staging</option>
                            <option value="Clark Air Freight Bay">Clark Air Freight Bay</option>
                            <option value="Cebu Forwarding Bay">Cebu Forwarding Bay</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Reference & Carrier Logistics</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">DR / SI Reference</label>
                        <input type="text" class="field-input font-code" id="dtsRef" placeholder="e.g. DR-2026-0090" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Logistics Provider / Carrier</label>
                        <input type="text" class="field-input" id="dtsCarrier" placeholder="e.g. DHL Express, 2GO Logistics" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Truck Plate / Container No.</label>
                        <input type="text" class="field-input" id="dtsVehicle" placeholder="e.g. NCK-4912" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Total Cartons</label>
                        <input type="number" class="field-input" id="dtsCartons" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Total Net Weight (KG)</label>
                        <input type="number" step="0.1" class="field-input" id="dtsWeight" min="0" placeholder="0.0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Prepared By</label>
                        <input type="text" class="field-input" id="dtsPreparedBy" value="Engr. Danilo Ramos" required>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Remarks / Handling Instructions</label>
                        <textarea class="field-textarea" id="dtsRemarks" rows="2" placeholder="Fragile electronic cables, temperature sensitive, seal number..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Transfer Slip</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Delivery Transfer Slip Specification</h3>
                <p class="modal-subtitle">Official inter-warehouse transfer documentation and verification record.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Official DTS</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Delivery Transfer Slip</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel DTS document <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will revert all staged items back to unassigned finished goods inventory.</p>
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
        document.getElementById("filterDestination").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterDestination").value = "";
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
        const dest = document.getElementById("filterDestination").value.toLowerCase();
        const rows = document.querySelectorAll("#dtsTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowDest = (row.getAttribute("data-dest") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchDest = !dest || rowDest.includes(dest);

            if (matchQuery && matchStatus && matchDest) {
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
        document.getElementById("entryModalTitle").innerText = "Create Delivery Transfer Slip";
        document.getElementById("saveBtnLabel").innerText = "Save Transfer Slip";
        document.getElementById("entryForm").reset();
        document.getElementById("dtsNumber").value = "DTS-2026-0042";
        document.getElementById("dtsDate").value = "2026-11-06";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editDts(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Transfer Slip DTS-2026-004" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Slip";

        if (id === '1') {
            document.getElementById("dtsNumber").value = "DTS-2026-0041";
            document.getElementById("dtsDate").value = "2026-11-06";
            document.getElementById("dtsStatus").value = "In Transit";
            document.getElementById("dtsSource").value = "Plant 1 (Mandaluyong FG Central)";
            document.getElementById("dtsDestination").value = "Laguna Logistics Hub";
            document.getElementById("dtsRef").value = "DR-2026-0089";
            document.getElementById("dtsCarrier").value = "DHL Express";
            document.getElementById("dtsVehicle").value = "NCK-4912";
            document.getElementById("dtsCartons").value = "420";
            document.getElementById("dtsWeight").value = "3540.0";
            document.getElementById("dtsPreparedBy").value = "Engr. Danilo Ramos";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveDts(e) {
        e.preventDefault();
        alert("Delivery Transfer Slip successfully saved.");
        closeEntryModal();
    }

    function viewDts(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">DTS Number</span>
                    <span class="spec-value font-bold font-code">DTS-2026-0041</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Dispatch Date</span>
                    <span class="spec-value font-numeric">2026-11-06</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Transfer Status</span>
                    <span class="spec-value"><span class="status-badge status-intransit">In Transit</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Source Facility</span>
                    <span class="spec-value">Plant 1 (Mandaluyong FG Central)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Destination Hub</span>
                    <span class="spec-value font-bold">Laguna Logistics Hub</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Delivery Receipt Ref</span>
                    <span class="spec-value font-code">DR-2026-0089</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Carrier & Vehicle</span>
                    <span class="spec-value">DHL Express / NCK-4912</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Total Volume</span>
                    <span class="spec-value font-numeric font-bold">420 Cartons (3,540.0 KG)</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="form-section-header">
                    <span class="step-num"><i data-lucide="package" style="width: 14px; height: 14px;"></i></span>
                    <span>Dispatched Finished Goods Items</span>
                </div>
                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>Lot Number</th>
                                <th class="text-right">Cartons</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-right">Net Weight</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-code font-bold">TFL-8820-P1</td>
                                <td>FFC Flexible Ribbon Cable 1.0mm Pitch</td>
                                <td class="font-code">LOT-2611-01A</td>
                                <td class="text-right font-numeric">250</td>
                                <td class="text-right font-numeric font-bold">25,000 PCS</td>
                                <td class="text-right font-numeric">2,100.0 KG</td>
                            </tr>
                            <tr>
                                <td class="font-code font-bold">TFL-5512-Y4</td>
                                <td>Shielded Harness Wire Assembly 0.5mm</td>
                                <td class="font-code">LOT-2611-02B</td>
                                <td class="text-right font-numeric">170</td>
                                <td class="text-right font-numeric font-bold">17,000 PCS</td>
                                <td class="text-right font-numeric">1,440.0 KG</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printDts(id) {
        viewDts(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteDts(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "DTS-2026-004" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("DTS document cancelled.");
        closeDeleteModal();
    }

    function bulkPrintManifest() {
        window.print();
    }

    function bulkDispatch() {
        alert("Selected DTS transfers marked as dispatched.");
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

