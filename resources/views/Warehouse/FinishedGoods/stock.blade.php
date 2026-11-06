@extends('layouts.app')

@section('title', 'Finished Goods Stock & Bin Allocation — Intracode ERP')

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
            <span class="bc-current">FG Stock</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Bin, Rack, Product Code, Lot..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Finished Goods Stock & Bin Allocation</h1>
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
                            <strong>Print Bin Directory</strong>
                            <small>Print warehouse rack allocation list</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Bin Data...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Stock bin allocations spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Assign New Bin Slot</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Warehouse Zone</label>
            <select class="filter-select" id="filterZone">
                <option value="">All Zones</option>
                <option value="Zone A">Zone A - High Turnover Electronics</option>
                <option value="Zone B">Zone B - Heavy Assemblies</option>
                <option value="Zone C">Zone C - Quarantine / Quality Hold</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Allocation Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Occupied">Occupied (Active)</option>
                <option value="Near Capacity">Near Capacity</option>
                <option value="Available Space">Available Space</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Rack Level</label>
            <select class="filter-select" id="filterLevel">
                <option value="">All Levels</option>
                <option value="Rack 1">Level 1 - Ground Picking</option>
                <option value="Rack 2">Level 2 - Mid Storage</option>
                <option value="Rack 3">Level 3 - High Bay</option>
                <option value="Rack 4">Level 4 - High Bay</option>
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
            <span class="kpi-label">TOTAL ASSIGNED BINS</span>
            <span class="kpi-value font-numeric" id="kpiTotalBins">4</span>
            <span class="kpi-subtext">Active finished goods pallet slots</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL STOCK ON HAND</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiOnHand">87,400 PCS</span>
            <span class="kpi-subtext">Physical balance in FG racks</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">ALLOCATED / RESERVED</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiReserved">42,000 PCS</span>
            <span class="kpi-subtext">Committed to active sales orders</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">AVAILABLE FOR DISPATCH</span>
            <span class="kpi-value font-numeric" style="color: #059669;" id="kpiAvailable">45,400 PCS</span>
            <span class="kpi-subtext">Unallocated free floor inventory</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Warehouse Bin & Rack Inventory Allocation</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkRelocate()">
                    <i data-lucide="move"></i>
                    <span>Relocate Bins</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintBinCards()">
                    <i data-lucide="printer"></i>
                    <span>Print Bin Labels</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="stockTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>BIN / RACK ID</th>
                        <th>ZONE</th>
                        <th>PRODUCT CODE & DESCRIPTION</th>
                        <th>LOT NUMBER</th>
                        <th class="text-right">ON HAND</th>
                        <th class="text-right">RESERVED</th>
                        <th class="text-right">AVAILABLE</th>
                        <th>LAST CYCLE COUNT</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    <tr data-id="1" data-zone="Zone A" data-status="Occupied">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('1')">BAY-A01-R4</a>
                        </td>
                        <td><span class="location-badge">Zone A (High Turnover)</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-8820-P1</span>
                                <span class="prod-sub">FFC Flexible Ribbon Cable 1.0mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-01A</span></td>
                        <td class="text-right font-numeric font-bold">35,000</td>
                        <td class="text-right font-numeric" style="color: #d97706;">25,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">10,000</td>
                        <td class="font-numeric">2026-11-05</td>
                        <td><span class="status-badge status-completed">Occupied</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewBin('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editBin('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Allocation</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Label</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Bin</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-zone="Zone A" data-status="Occupied">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('2')">BAY-A03-R2</a>
                        </td>
                        <td><span class="location-badge">Zone A (High Turnover)</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-5512-Y4</span>
                                <span class="prod-sub">Shielded Harness Wire Assembly 0.5mm</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-02B</span></td>
                        <td class="text-right font-numeric font-bold">18,400</td>
                        <td class="text-right font-numeric" style="color: #d97706;">17,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">1,400</td>
                        <td class="font-numeric">2026-11-04</td>
                        <td><span class="status-badge status-completed">Occupied</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewBin('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editBin('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Allocation</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Label</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Bin</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-zone="Zone B" data-status="Occupied">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('3')">BAY-B01-R1</a>
                        </td>
                        <td><span class="location-badge">Zone B (Heavy Assemblies)</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-9910-F2</span>
                                <span class="prod-sub">Fine Pitch Jumper Lead Assembly</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-03C</span></td>
                        <td class="text-right font-numeric font-bold">12,000</td>
                        <td class="text-right font-numeric" style="color: #d97706;">0</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">12,000</td>
                        <td class="font-numeric">2026-11-03</td>
                        <td><span class="status-badge status-instock">Available Space</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewBin('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editBin('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Allocation</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Label</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Bin</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-zone="Zone B" data-status="Occupied">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('4')">BAY-B04-R3</a>
                        </td>
                        <td><span class="location-badge">Zone B (Heavy Assemblies)</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-3301-C8</span>
                                <span class="prod-sub">Ultra-Flex Flat Cable 1.25mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-04D</span></td>
                        <td class="text-right font-numeric font-bold">22,000</td>
                        <td class="text-right font-numeric" style="color: #d97706;">0</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">22,000</td>
                        <td class="font-numeric">2026-11-01</td>
                        <td><span class="status-badge status-completed">Occupied</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewBin('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editBin('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Allocation</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Label</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Bin</span>
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
                <h3 class="modal-title" id="entryModalTitle">Assign Bin & Pallet Slot</h3>
                <p class="modal-subtitle">Define physical warehouse location and dedicate capacity for finished good SKUs.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveBin(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Bin Identification & Location</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Bin / Slot ID</label>
                        <input type="text" class="field-input font-code" id="binId" placeholder="e.g. BAY-A02-R1" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Zone</label>
                        <select class="field-select" id="binZone" required>
                            <option value="Zone A">Zone A - High Turnover Electronics</option>
                            <option value="Zone B">Zone B - Heavy Assemblies</option>
                            <option value="Zone C">Zone C - Quarantine Hold</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Rack Level</label>
                        <select class="field-select" id="binRack" required>
                            <option value="Rack 1">Level 1 - Ground Picking</option>
                            <option value="Rack 2">Level 2 - Mid Storage</option>
                            <option value="Rack 3">Level 3 - High Bay</option>
                            <option value="Rack 4">Level 4 - High Bay</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Product Assignment & Quantities</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Finished Good Item</label>
                        <select class="field-select" id="binProduct" required>
                            <option value="TFL-8820-P1">TFL-8820-P1 — FFC Flexible Ribbon Cable 1.0mm Pitch</option>
                            <option value="TFL-5512-Y4">TFL-5512-Y4 — Shielded Harness Wire Assembly 0.5mm</option>
                            <option value="TFL-9910-F2">TFL-9910-F2 — Fine Pitch Jumper Lead Assembly</option>
                            <option value="TFL-3301-C8">TFL-3301-C8 — Ultra-Flex Flat Cable 1.25mm Pitch</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Allocated Lot Number</label>
                        <input type="text" class="field-input font-code" id="binLot" placeholder="e.g. LOT-2611-06F" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">On Hand (PCS)</label>
                        <input type="number" class="field-input" id="binOnHand" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Reserved for Orders</label>
                        <input type="number" class="field-input" id="binReserved" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Bin Max Capacity</label>
                        <input type="number" class="field-input" id="binMax" min="1" placeholder="40000" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Bin Allocation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Bin & Pallet Stock Card</h3>
                <p class="modal-subtitle">Real-time bin storage specs, pallet counts, and allocation state.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Bin Label</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Vacate Bin Location</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to vacate bin slot <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">Ensure all physical product cartons have been relocated before vacating.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-danger-solid" onclick="confirmDelete()">Confirm Vacate</button>
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
        document.getElementById("filterZone").addEventListener("change", filterTable);
        document.getElementById("filterStatus").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterZone").value = "";
            document.getElementById("filterStatus").value = "";
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
        const zone = document.getElementById("filterZone").value.toLowerCase();
        const status = document.getElementById("filterStatus").value.toLowerCase();
        const rows = document.querySelectorAll("#stockTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowZone = (row.getAttribute("data-zone") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchZone = !zone || rowZone.includes(zone);
            const matchStatus = !status || text.includes(status);

            if (matchQuery && matchZone && matchStatus) {
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
        document.getElementById("entryModalTitle").innerText = "Assign Bin & Pallet Slot";
        document.getElementById("saveBtnLabel").innerText = "Save Bin Allocation";
        document.getElementById("entryForm").reset();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editBin(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Bin Slot " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Allocation";

        if (id === '1') {
            document.getElementById("binId").value = "BAY-A01-R4";
            document.getElementById("binZone").value = "Zone A";
            document.getElementById("binRack").value = "Rack 4";
            document.getElementById("binProduct").value = "TFL-8820-P1";
            document.getElementById("binLot").value = "LOT-2611-01A";
            document.getElementById("binOnHand").value = "35000";
            document.getElementById("binReserved").value = "25000";
            document.getElementById("binMax").value = "40000";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveBin(e) {
        e.preventDefault();
        alert("Bin slot allocation successfully saved.");
        closeEntryModal();
    }

    function viewBin(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Bin Slot ID</span>
                    <span class="spec-value font-bold font-code">BAY-A01-R4</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Zone & Bay</span>
                    <span class="spec-value">Zone A - High Turnover (Rack 4)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Occupancy Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Occupied</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Allocated Product</span>
                    <span class="spec-value font-bold">FFC Flexible Ribbon Cable 1.0mm Pitch (TFL-8820-P1)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Lot Allocation</span>
                    <span class="spec-value font-code">LOT-2611-01A</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Stock Physical Balance</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">35,000 PCS</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Reserved for Orders</span>
                    <span class="spec-value font-numeric" style="color: #d97706;">25,000 PCS</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Available for Dispatch</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">10,000 PCS</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printBin(id) {
        viewBin(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteBin(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "BAY-A0" + id + "-R" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Bin slot vacated.");
        closeDeleteModal();
    }

    function bulkRelocate() {
        alert("Batch warehouse bin relocation dialog initiated.");
    }

    function bulkPrintBinCards() {
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

