@extends('layouts.app')

@section('title', 'Supplies Stock & Bin Cards — Intracode ERP')

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
            <span class="bc-current">Supplies Stock</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Bin, Shelf, Material Code, Desc..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplies Stock & Bin Directory</h1>
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
                            <strong>Print Shelf Directory</strong>
                            <small>Print warehouse raw materials bin ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Stock CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Supplies bin storage spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Allocate Supplies Shelf</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Warehouse Zone</label>
            <select class="filter-select" id="filterZone">
                <option value="">All Zones</option>
                <option value="Zone A">Zone A - Metal & Conductors</option>
                <option value="Zone B">Zone B - Polymers & Films</option>
                <option value="Zone C">Zone C - Packaging Bay</option>
                <option value="Zone D">Zone D - Chemicals Vault</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="filterCat">
                <option value="">All Categories</option>
                <option value="Conductors">Conductors & Wire</option>
                <option value="Insulation">Insulation & Sheathing</option>
                <option value="Chemicals">Chemicals & Lubricants</option>
                <option value="Packaging">Packaging Supplies</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Shelf Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Healthy">Healthy Stock</option>
                <option value="Reorder Level">Reorder Level</option>
                <option value="Stockout">Critical Stockout</option>
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
            <span class="kpi-label">TOTAL SHELF SLOTS</span>
            <span class="kpi-value font-numeric" id="kpiSlots">4</span>
            <span class="kpi-subtext">Active raw materials bins</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL STOCK UNITS</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiUnits">28,218</span>
            <span class="kpi-subtext">Combined inventory items on shelf</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">INCOMING ON-ORDER</span>
            <span class="kpi-value font-numeric" style="color: #059669;" id="kpiIncoming">8,000</span>
            <span class="kpi-subtext">Committed on active vendor POs</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">REORDER REQUIRED</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiReorder">1</span>
            <span class="kpi-subtext">Below safety replenishment buffer</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Raw Materials & Supplies Bin Stock Directory</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkRelocate()">
                    <i data-lucide="move"></i>
                    <span>Relocate Shelves</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintBinCards()">
                    <i data-lucide="printer"></i>
                    <span>Print Bin Cards</span>
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
                        <th>BIN / SHELF LOCATION</th>
                        <th>ZONE</th>
                        <th>CATEGORY</th>
                        <th>MATERIAL CODE & DESCRIPTION</th>
                        <th class="text-right">SAFETY STOCK</th>
                        <th class="text-right">ON HAND</th>
                        <th class="text-right">ON ORDER</th>
                        <th>UOM</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="stockTableBody">
                    <tr data-id="1" data-zone="Zone A" data-cat="Conductors" data-status="Healthy">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('1')">RACK-A02-B14</a>
                        </td>
                        <td><span class="location-badge">Zone A - Metal</span></td>
                        <td><span class="category-pill">Conductors</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-CU-001</span>
                                <span class="prod-sub">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric">4,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">14,200</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">8,000</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td><span class="status-badge status-instock">Healthy Stock</span></td>
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
                                    <span>Edit Shelf</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Shelf</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-zone="Zone B" data-cat="Insulation" data-status="Reorder Level">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('2')">RACK-B01-B03</a>
                        </td>
                        <td><span class="location-badge">Zone B - Polymers</span></td>
                        <td><span class="category-pill">Insulation</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-PET-004</span>
                                <span class="prod-sub">PET Insulation Film 0.05mm White</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric">3,000</td>
                        <td class="text-right font-numeric font-bold" style="color: #d97706;">2,800</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">2,000</td>
                        <td><span class="uom-pill">ROLL</span></td>
                        <td><span class="status-badge status-reorder">Reorder Level</span></td>
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
                                    <span>Edit Shelf</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Shelf</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-zone="Zone D" data-cat="Chemicals" data-status="Healthy">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('3')">VAULT-D-CHEM02</a>
                        </td>
                        <td><span class="location-badge">Zone D - Vault</span></td>
                        <td><span class="category-pill">Chemicals</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-ADH-012</span>
                                <span class="prod-sub">Thermosetting Heat-Seal Adhesive Resin</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric">5</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">18</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">0</td>
                        <td><span class="uom-pill">DRUM</span></td>
                        <td><span class="status-badge status-instock">Healthy Stock</span></td>
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
                                    <span>Edit Shelf</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Shelf</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-zone="Zone C" data-cat="Packaging" data-status="Healthy">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewBin('4')">RACK-C04-PAL09</a>
                        </td>
                        <td><span class="location-badge">Zone C - Packaging</span></td>
                        <td><span class="category-pill">Packaging</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">MAT-BOX-020</span>
                                <span class="prod-sub">Heavy-Duty Corrugated Export Carton</span>
                            </div>
                        </td>
                        <td class="text-right font-numeric">2,500</td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">11,200</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">0</td>
                        <td><span class="uom-pill">PCS</span></td>
                        <td><span class="status-badge status-instock">Healthy Stock</span></td>
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
                                    <span>Edit Shelf</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printBin('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteBin('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Vacate Shelf</span>
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
                <h3 class="modal-title" id="entryModalTitle">Allocate Supplies Shelf & Bin Card</h3>
                <p class="modal-subtitle">Assign warehouse storage rack position for incoming materials and set replenishment thresholds.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveBin(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Bin Identification & Storage Location</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Shelf / Bin ID</label>
                        <input type="text" class="field-input font-code" id="binId" placeholder="e.g. RACK-A03-B01" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Zone</label>
                        <select class="field-select" id="binZone" required>
                            <option value="Zone A">Zone A - Metal & Conductors</option>
                            <option value="Zone B">Zone B - Polymers & Films</option>
                            <option value="Zone C">Zone C - Packaging Bay</option>
                            <option value="Zone D">Zone D - Chemicals Vault</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Category</label>
                        <select class="field-select" id="binCat" required>
                            <option value="Conductors">Conductors & Wire</option>
                            <option value="Insulation">Insulation & Sheathing</option>
                            <option value="Chemicals">Chemicals & Lubricants</option>
                            <option value="Packaging">Packaging Supplies</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Material Item & Inventory Thresholds</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Material Item</label>
                        <select class="field-select" id="binMaterial" required>
                            <option value="MAT-CU-001">MAT-CU-001 — Copper Conductor Wire 0.12mm (Oxygen-Free)</option>
                            <option value="MAT-PET-004">MAT-PET-004 — PET Insulation Film 0.05mm White</option>
                            <option value="MAT-ADH-012">MAT-ADH-012 — Thermosetting Heat-Seal Adhesive Resin</option>
                            <option value="MAT-BOX-020">MAT-BOX-020 — Heavy-Duty Corrugated Export Carton</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Unit of Measure (UOM)</label>
                        <select class="field-select" id="binUOM" required>
                            <option value="MTR">MTR - Meters</option>
                            <option value="ROLL">ROLL - Rolls</option>
                            <option value="DRUM">DRUM - Drums</option>
                            <option value="PCS">PCS - Pieces</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Safety Stock Buffer</label>
                        <input type="number" class="field-input" id="binSafety" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Current On Hand</label>
                        <input type="number" class="field-input" id="binOnHand" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Shelf Max Capacity</label>
                        <input type="number" class="field-input" id="binMax" min="1" placeholder="20000" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Shelf Allocation</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Supplies Bin & Shelf Card</h3>
                <p class="modal-subtitle">Real-time shelf storage parameters, material safety levels, and open orders.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Bin Card</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Vacate Shelf Slot</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to vacate shelf location <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">Ensure physical raw materials are transferred before clearing this shelf.</p>
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
        document.getElementById("filterCat").addEventListener("change", filterTable);
        document.getElementById("filterStatus").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterZone").value = "";
            document.getElementById("filterCat").value = "";
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
        const cat = document.getElementById("filterCat").value.toLowerCase();
        const status = document.getElementById("filterStatus").value.toLowerCase();
        const rows = document.querySelectorAll("#stockTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowZone = (row.getAttribute("data-zone") || "").toLowerCase();
            const rowCat = (row.getAttribute("data-cat") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchZone = !zone || rowZone.includes(zone);
            const matchCat = !cat || rowCat.includes(cat);
            const matchStatus = !status || text.includes(status);

            if (matchQuery && matchZone && matchCat && matchStatus) {
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
        document.getElementById("entryModalTitle").innerText = "Allocate Supplies Shelf & Bin Card";
        document.getElementById("saveBtnLabel").innerText = "Save Shelf Allocation";
        document.getElementById("entryForm").reset();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editBin(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Supplies Shelf " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Shelf";

        if (id === '1') {
            document.getElementById("binId").value = "RACK-A02-B14";
            document.getElementById("binZone").value = "Zone A";
            document.getElementById("binCat").value = "Conductors";
            document.getElementById("binMaterial").value = "MAT-CU-001";
            document.getElementById("binUOM").value = "MTR";
            document.getElementById("binSafety").value = "4000";
            document.getElementById("binOnHand").value = "14200";
            document.getElementById("binMax").value = "25000";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveBin(e) {
        e.preventDefault();
        alert("Supplies shelf allocation successfully saved.");
        closeEntryModal();
    }

    function viewBin(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Shelf / Bin ID</span>
                    <span class="spec-value font-bold font-code">RACK-A02-B14</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Warehouse Zone</span>
                    <span class="spec-value">Zone A - Metal & Conductors</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Storage Status</span>
                    <span class="spec-value"><span class="status-badge status-instock">Healthy Stock</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Material Description</span>
                    <span class="spec-value font-bold">Copper Conductor Wire 0.12mm (MAT-CU-001)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Stock On Hand</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">14,200 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Incoming On-Order</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">8,000 MTR (PO-2026-0032)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Safety Stock Reorder Point</span>
                    <span class="spec-value font-numeric">4,000 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Max Bin Capacity</span>
                    <span class="spec-value font-numeric">25,000 MTR</span>
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
        document.getElementById("deleteTargetCode").innerText = "RACK-A0" + id + "-B0" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Shelf location vacated.");
        closeDeleteModal();
    }

    function bulkRelocate() {
        alert("Batch shelf relocation dialog initiated.");
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

