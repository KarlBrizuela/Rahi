@extends('layouts.app')

@section('title', 'Supplies & Raw Materials Inventory — Intracode ERP')

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
            <span class="bc-item">Inventory</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Supplies & Raw Matl's</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Material Code, Description, Bin..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Supplies & Raw Materials Inventory</h1>
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
                            <strong>Print Stock Ledger</strong>
                            <small>Printable inventory balance ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Raw inventory balance data</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Register Material Item</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="filterCategory">
                <option value="">All Categories</option>
                <option value="Raw Materials">Raw Materials</option>
                <option value="Conductors">Conductors & Wire</option>
                <option value="Insulation">Insulation & Sheathing</option>
                <option value="Packaging">Packaging Supplies</option>
                <option value="Chemicals">Chemicals & Lubricants</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Storage Location</label>
            <select class="filter-select" id="filterLocation">
                <option value="">All Racks / Bins</option>
                <option value="Zone A">Zone A - Raw Metal Storage</option>
                <option value="Zone B">Zone B - Polymers & Resins</option>
                <option value="Zone C">Zone C - Packaging Bay</option>
                <option value="Zone D">Zone D - Chemicals Locker</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Stock Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Stock Levels</option>
                <option value="In Stock">In Stock (Healthy)</option>
                <option value="Reorder Level">Reorder Level (Low)</option>
                <option value="Critical Out">Critical / Zero Stock</option>
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
            <span class="kpi-label">TOTAL SKU ITEMS</span>
            <span class="kpi-value font-numeric" id="kpiTotalItems">4</span>
            <span class="kpi-subtext">Active raw materials and supplies</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL STOCK VALUATION</span>
            <span class="kpi-value font-numeric" style="color: #2563eb;" id="kpiValuation">₱ 3,842,500.00</span>
            <span class="kpi-subtext">Weighted average inventory value</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">ITEMS AT REORDER POINT</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiReorder">1</span>
            <span class="kpi-subtext">Stock &le; safety threshold</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">CRITICAL / STOCKOUT</span>
            <span class="kpi-value font-numeric" style="color: #dc2626;" id="kpiStockout">0</span>
            <span class="kpi-subtext">Requiring urgent purchasing PR</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Raw Materials & Supplies Stock Ledger</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkAdjust()">
                    <i data-lucide="sliders"></i>
                    <span>Batch Adjust</span>
                </button>
                <button type="button" class="bulk-btn text-danger" onclick="bulkPrintStockCards()">
                    <i data-lucide="printer"></i>
                    <span>Print Bin Cards</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="suppliesTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>ITEM CODE</th>
                        <th>DESCRIPTION</th>
                        <th>CATEGORY</th>
                        <th>STORAGE LOCATION</th>
                        <th class="text-right">ON HAND</th>
                        <th class="text-right">REORDER PT</th>
                        <th class="text-right">MAX LEVEL</th>
                        <th>UOM</th>
                        <th class="text-right">AVG UNIT COST</th>
                        <th class="text-right">TOTAL VALUE</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="suppliesTableBody">
                    <tr data-id="1" data-code="MAT-CU-001" data-cat="Conductors" data-loc="Zone A">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewItem('1')">MAT-CU-001</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                                <span class="prod-sub">Spool Standard 5000m Reel</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Conductors</span></td>
                        <td><span class="location-badge">Rack A-02 / Bin 14</span></td>
                        <td class="text-right font-numeric font-bold">14,200</td>
                        <td class="text-right font-numeric">4,000</td>
                        <td class="text-right font-numeric">25,000</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td class="text-right font-numeric">₱ 45.50</td>
                        <td class="text-right font-numeric font-bold">₱ 646,100.00</td>
                        <td><span class="status-badge status-instock">In Stock</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewItem('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editItem('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Item</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCard('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteItem('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-code="MAT-PET-004" data-cat="Insulation" data-loc="Zone B">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewItem('2')">MAT-PET-004</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">PET Insulation Film 0.05mm White</span>
                                <span class="prod-sub">Roll Width 120mm</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Insulation</span></td>
                        <td><span class="location-badge">Rack B-01 / Bin 03</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #d97706;">2,800</td>
                        <td class="text-right font-numeric">3,000</td>
                        <td class="text-right font-numeric">10,000</td>
                        <td><span class="uom-pill">ROLL</span></td>
                        <td class="text-right font-numeric">₱ 280.00</td>
                        <td class="text-right font-numeric font-bold">₱ 784,000.00</td>
                        <td><span class="status-badge status-reorder">Reorder Level</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewItem('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editItem('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Item</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCard('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteItem('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-code="MAT-ADH-012" data-cat="Chemicals" data-loc="Zone D">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewItem('3')">MAT-ADH-012</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Thermosetting Heat-Seal Adhesive Resin</span>
                                <span class="prod-sub">Drum 200kg Grade TS-88</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Chemicals</span></td>
                        <td><span class="location-badge">Zone D - Chem 02</span></td>
                        <td class="text-right font-numeric font-bold">18</td>
                        <td class="text-right font-numeric">5</td>
                        <td class="text-right font-numeric">30</td>
                        <td><span class="uom-pill">DRUM</span></td>
                        <td class="text-right font-numeric">₱ 42,500.00</td>
                        <td class="text-right font-numeric font-bold">₱ 765,000.00</td>
                        <td><span class="status-badge status-instock">In Stock</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewItem('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editItem('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Item</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCard('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteItem('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-code="MAT-BOX-020" data-cat="Packaging" data-loc="Zone C">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewItem('4')">MAT-BOX-020</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Heavy-Duty Corrugated Export Carton 450x300x250mm</span>
                                <span class="prod-sub">Double Wall Kraft 5-Ply</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Packaging</span></td>
                        <td><span class="location-badge">Rack C-04 / Pallet 09</span></td>
                        <td class="text-right font-numeric font-bold">11,200</td>
                        <td class="text-right font-numeric">2,500</td>
                        <td class="text-right font-numeric">15,000</td>
                        <td><span class="uom-pill">PCS</span></td>
                        <td class="text-right font-numeric">₱ 147.10</td>
                        <td class="text-right font-numeric font-bold">₱ 1,647,400.00</td>
                        <td><span class="status-badge status-instock">In Stock</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewItem('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editItem('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Item</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCard('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Bin Card</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteItem('4')">
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
                <h3 class="modal-title" id="entryModalTitle">Register New Material / Supply Item</h3>
                <p class="modal-subtitle">Define raw material specifications, minimum safety thresholds, and bin storage location.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveItem(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Item Identification & Classification</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Material Code</label>
                        <input type="text" class="field-input" id="itemCode" placeholder="e.g. MAT-CU-002" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Category</label>
                        <select class="field-select" id="itemCategory" required>
                            <option value="">Select Category</option>
                            <option value="Conductors">Conductors & Wire</option>
                            <option value="Insulation">Insulation & Sheathing</option>
                            <option value="Packaging">Packaging Supplies</option>
                            <option value="Chemicals">Chemicals & Lubricants</option>
                            <option value="General Supplies">General Supplies</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Unit of Measure (UOM)</label>
                        <select class="field-select" id="itemUOM" required>
                            <option value="MTR">MTR - Meters</option>
                            <option value="ROLL">ROLL - Rolls</option>
                            <option value="DRUM">DRUM - Drums</option>
                            <option value="PCS">PCS - Pieces</option>
                            <option value="KG">KG - Kilograms</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Item Description</label>
                        <input type="text" class="field-input" id="itemDescription" placeholder="Complete technical name of material" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Specification / Dimensions</label>
                        <input type="text" class="field-input" id="itemSpec" placeholder="e.g. 0.12mm diameter, AWG 36, Oxygen-Free">
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Storage Location & Inventory Thresholds</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Warehouse Bay / Rack</label>
                        <input type="text" class="field-input" id="itemLocation" placeholder="e.g. Rack A-03 / Bin 12" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Reorder Point (Min)</label>
                        <input type="number" class="field-input" id="itemReorder" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Maximum Capacity</label>
                        <input type="number" class="field-input" id="itemMax" min="0" placeholder="0" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Initial Stock On-Hand</label>
                        <input type="number" class="field-input" id="itemQty" min="0" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Average Unit Cost (PHP)</label>
                        <input type="number" step="0.01" class="field-input" id="itemCost" min="0" placeholder="0.00" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="itemStatus" required>
                            <option value="In Stock">In Stock</option>
                            <option value="Reorder Level">Reorder Level</option>
                            <option value="Deactivated">Deactivated</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Remarks / Supplier Notes</label>
                        <textarea class="field-textarea" id="itemRemarks" rows="2" placeholder="Primary supplier details, handling instructions, shelf life restrictions..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Material Item</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Material Stock Card & Movement History</h3>
                <p class="modal-subtitle">Real-time bin balances, movement records, and valuation.</p>
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
                <h3 class="modal-title text-danger">Scrap / Deactivate Item</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to deactivate material item <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will freeze new issuance requests against this material item.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-danger-solid" onclick="confirmDelete()">Deactivate</button>
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
        document.getElementById("filterCategory").addEventListener("change", filterTable);
        document.getElementById("filterLocation").addEventListener("change", filterTable);
        document.getElementById("filterStatus").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterCategory").value = "";
            document.getElementById("filterLocation").value = "";
            document.getElementById("filterStatus").value = "";
            filterTable();
        });

        document.getElementById("btnRefresh").addEventListener("click", function () {
            const icon = document.getElementById("refreshIcon");
            icon.classList.add("spin");
            setTimeout(() => icon.classList.remove("spin"), 600);
        });

        document.addEventListener("click", function () {
            closeAllActionMenus();
        });
    });

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

    function updateBulkCount() {
        const checked = document.querySelectorAll(".row-checkbox:checked");
        const count = checked.length;
        const bulkDiv = document.getElementById("bulkActions");
        document.getElementById("selectedCount").innerText = count;
        bulkDiv.style.display = count > 0 ? "flex" : "none";
    }

    function filterTable() {
        const query = document.getElementById("globalSearchInput").value.toLowerCase();
        const cat = document.getElementById("filterCategory").value.toLowerCase();
        const loc = document.getElementById("filterLocation").value.toLowerCase();
        const status = document.getElementById("filterStatus").value.toLowerCase();
        const rows = document.querySelectorAll("#suppliesTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowCat = (row.getAttribute("data-cat") || "").toLowerCase();
            const rowLoc = (row.getAttribute("data-loc") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchCat = !cat || rowCat.includes(cat);
            const matchLoc = !loc || rowLoc.includes(loc);
            const matchStatus = !status || text.includes(status);

            if (matchQuery && matchCat && matchLoc && matchStatus) {
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
        document.getElementById("entryModalTitle").innerText = "Register New Material / Supply Item";
        document.getElementById("saveBtnLabel").innerText = "Save Material Item";
        document.getElementById("entryForm").reset();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editItem(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Material Item " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Material";

        if (id === '1') {
            document.getElementById("itemCode").value = "MAT-CU-001";
            document.getElementById("itemCategory").value = "Conductors";
            document.getElementById("itemUOM").value = "MTR";
            document.getElementById("itemDescription").value = "Copper Conductor Wire 0.12mm (Oxygen-Free)";
            document.getElementById("itemSpec").value = "Spool Standard 5000m Reel";
            document.getElementById("itemLocation").value = "Rack A-02 / Bin 14";
            document.getElementById("itemReorder").value = "4000";
            document.getElementById("itemMax").value = "25000";
            document.getElementById("itemQty").value = "14200";
            document.getElementById("itemCost").value = "45.50";
            document.getElementById("itemStatus").value = "In Stock";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveItem(e) {
        e.preventDefault();
        alert("Material record successfully saved.");
        closeEntryModal();
    }

    function viewItem(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Material Code</span>
                    <span class="spec-value font-bold font-code">MAT-CU-001</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Category</span>
                    <span class="spec-value">Conductors & Wire</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Bin Location</span>
                    <span class="spec-value font-code">Rack A-02 / Bin 14</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Current Stock</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">14,200 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Average Unit Cost</span>
                    <span class="spec-value font-numeric">₱ 45.50</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Total Inventory Valuation</span>
                    <span class="spec-value font-numeric font-bold">₱ 646,100.00</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="form-section-header">
                    <span class="step-num"><i data-lucide="history" style="width: 14px; height: 14px;"></i></span>
                    <span>Recent Material Inward / Issuance Movements</span>
                </div>
                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Movement Date</th>
                                <th>Txn Ref</th>
                                <th>Type</th>
                                <th>Dept / Work Center</th>
                                <th class="text-right">In</th>
                                <th class="text-right">Out</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-numeric">2026-11-05</td>
                                <td class="font-code">ISS-2026-0044</td>
                                <td><span class="category-pill">Issuance</span></td>
                                <td>SMT Line 01 (WO-0814)</td>
                                <td class="text-right">-</td>
                                <td class="text-right font-numeric font-bold" style="color: #dc2626;">-1,800</td>
                                <td class="text-right font-numeric font-bold">14,200</td>
                            </tr>
                            <tr>
                                <td class="font-numeric">2026-11-02</td>
                                <td class="font-code">RCV-2026-0082</td>
                                <td><span class="category-pill" style="background:#ecfdf5; color:#065f46;">Receiving PO</span></td>
                                <td>Sumitomo Metal Supplier</td>
                                <td class="text-right font-numeric font-bold" style="color: #059669;">+8,000</td>
                                <td class="text-right">-</td>
                                <td class="text-right font-numeric font-bold">16,000</td>
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

    function printCard(id) {
        viewItem(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteItem(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "MAT-CU-00" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Material item successfully deactivated.");
        closeDeleteModal();
    }

    function bulkAdjust() {
        alert("Batch stock adjustment dialog initiated.");
    }

    function bulkPrintStockCards() {
        window.print();
    }
</script>
@endsection

