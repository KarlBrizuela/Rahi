@extends('layouts.app')

@section('title', 'Supplies Catalog & Pricing — Intracode ERP')

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
            <span class="bc-current">Supplies</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Item Code, Description, Vendor..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Purchasing Supplies & Price Catalog</h1>
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
                            <strong>Print Catalog</strong>
                            <small>Print procurement price matrix</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Catalog CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Download supplies pricing spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Register Purchasing Item</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Item Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Active">Active Catalog</option>
                <option value="Evaluation">Sample Evaluation</option>
                <option value="Phased Out">Phased Out</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Category</label>
            <select class="filter-select" id="filterCategory">
                <option value="">All Categories</option>
                <option value="Conductors">Conductors & Wire</option>
                <option value="Insulation">Insulation & Sheathing</option>
                <option value="Chemicals">Chemicals & Lubricants</option>
                <option value="Packaging">Packaging Supplies</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Primary Vendor</label>
            <select class="filter-select" id="filterVendor">
                <option value="">All Suppliers</option>
                <option value="Sumitomo Metal">Sumitomo Metal & Wire Corp.</option>
                <option value="Toray Polymers">Toray Polymers Asia</option>
                <option value="Apex Packaging">Apex Packaging Solutions</option>
                <option value="Shin-Etsu Chemical">Shin-Etsu Chemical</option>
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
            <span class="kpi-label">TOTAL CATALOG ITEMS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Contracted procurement items</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">AVERAGE UNIT PRICE</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiAvgPrice">₱ 10,743.15</span>
            <span class="kpi-subtext">Weighted standard procurement cost</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">AVERAGE SUPPLIER LEAD TIME</span>
            <span class="kpi-value font-numeric font-bold" style="color: #059669;" id="kpiLeadTime">7.5 Days</span>
            <span class="kpi-subtext">Production replenishment cycle</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">QUALIFIED CONTRACTS</span>
            <span class="kpi-value font-numeric" style="color: #1e293b;" id="kpiContracts">4 of 4</span>
            <span class="kpi-subtext">100% contracted with SLA terms</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Procurement Supplies Master & Contract Pricing</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkRequestQuote()">
                    <i data-lucide="tag"></i>
                    <span>Request Quotation</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintCatalog()">
                    <i data-lucide="printer"></i>
                    <span>Print Specification Sheets</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="suppliesCatalogTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>SUPPLY CODE</th>
                        <th>DESCRIPTION</th>
                        <th>CATEGORY</th>
                        <th>PRIMARY SUPPLIER</th>
                        <th class="text-right">STD UNIT PRICE</th>
                        <th>UOM</th>
                        <th class="text-right">LEAD TIME</th>
                        <th class="text-right">MIN ORDER (MOQ)</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="suppliesCatalogTableBody">
                    <tr data-id="1" data-cat="Conductors" data-vendor="Sumitomo Metal" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewCatalogItem('1')">MAT-CU-001</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                                <span class="prod-sub">Spool Standard 5000m Reel, RoHS 3 Compliant</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Conductors</span></td>
                        <td><span class="location-badge">Sumitomo Metal & Wire Corp.</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 45.50</td>
                        <td><span class="uom-pill">MTR</span></td>
                        <td class="text-right font-numeric">5 Days</td>
                        <td class="text-right font-numeric font-bold">5,000</td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewCatalogItem('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editCatalogItem('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Pricing</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCatalogItem('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Spec</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteCatalogItem('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-cat="Insulation" data-vendor="Toray Polymers" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewCatalogItem('2')">MAT-PET-004</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">PET Insulation Film 0.05mm White</span>
                                <span class="prod-sub">Roll Width 120mm, Grade UL94 V-0 Flame Retardant</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Insulation</span></td>
                        <td><span class="location-badge">Toray Polymers Asia</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 280.00</td>
                        <td><span class="uom-pill">ROLL</span></td>
                        <td class="text-right font-numeric">7 Days</td>
                        <td class="text-right font-numeric font-bold">500</td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewCatalogItem('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editCatalogItem('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Pricing</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCatalogItem('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Spec</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteCatalogItem('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-cat="Chemicals" data-vendor="Shin-Etsu Chemical" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewCatalogItem('3')">MAT-ADH-012</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Thermosetting Heat-Seal Adhesive Resin</span>
                                <span class="prod-sub">Drum 200kg Grade TS-88, Thermal Class F</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Chemicals</span></td>
                        <td><span class="location-badge">Shin-Etsu Chemical Philippines</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 42,500.00</td>
                        <td><span class="uom-pill">DRUM</span></td>
                        <td class="text-right font-numeric">14 Days</td>
                        <td class="text-right font-numeric font-bold">2</td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewCatalogItem('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editCatalogItem('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Pricing</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCatalogItem('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Spec</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteCatalogItem('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Deactivate</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-cat="Packaging" data-vendor="Apex Packaging" data-status="Active">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewCatalogItem('4')">MAT-BOX-020</a>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Heavy-Duty Corrugated Export Carton</span>
                                <span class="prod-sub">450x300x250mm Double Wall Kraft 5-Ply</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Packaging</span></td>
                        <td><span class="location-badge">Apex Packaging Solutions</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 147.10</td>
                        <td><span class="uom-pill">PCS</span></td>
                        <td class="text-right font-numeric">4 Days</td>
                        <td class="text-right font-numeric font-bold">1,000</td>
                        <td><span class="status-badge status-completed">Active</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewCatalogItem('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Details</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editCatalogItem('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Pricing</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printCatalogItem('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Spec</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteCatalogItem('4')">
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
                <h3 class="modal-title" id="entryModalTitle">Register Supplies Catalog Item</h3>
                <p class="modal-subtitle">Add a qualified procurement item with negotiated vendor contract pricing and MOQ.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveCatalogItem(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Item Identification & Classification</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Supply Item Code</label>
                        <input type="text" class="field-input font-code" id="catCode" placeholder="e.g. MAT-CU-003" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Category</label>
                        <select class="field-select" id="catCategory" required>
                            <option value="Conductors">Conductors & Wire</option>
                            <option value="Insulation">Insulation & Sheathing</option>
                            <option value="Chemicals">Chemicals & Lubricants</option>
                            <option value="Packaging">Packaging Supplies</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Standard UOM</label>
                        <select class="field-select" id="catUOM" required>
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
                        <label class="field-label required">Material Commercial Name</label>
                        <input type="text" class="field-input" id="catName" placeholder="Full descriptive name" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Specification / Dimensions</label>
                        <input type="text" class="field-input" id="catSpec" placeholder="Standard packaging, spool dimension, temperature class">
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Contract Pricing & Supplier Logistics</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Primary Contracted Supplier</label>
                        <select class="field-select" id="catVendor" required>
                            <option value="Sumitomo Metal & Wire Corp.">Sumitomo Metal & Wire Corp.</option>
                            <option value="Toray Polymers Asia">Toray Polymers Asia</option>
                            <option value="Apex Packaging Solutions">Apex Packaging Solutions</option>
                            <option value="Shin-Etsu Chemical Philippines">Shin-Etsu Chemical Philippines</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Contracted Unit Price (PHP)</label>
                        <input type="number" step="0.01" class="field-input" id="catPrice" min="0" placeholder="0.00" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Lead Time (Days)</label>
                        <input type="number" class="field-input" id="catLeadTime" min="1" placeholder="7" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Minimum Order Qty (MOQ)</label>
                        <input type="number" class="field-input" id="catMoq" min="1" placeholder="100" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Catalog Status</label>
                        <select class="field-select" id="catStatus" required>
                            <option value="Active">Active Catalog</option>
                            <option value="Evaluation">Sample Evaluation</option>
                            <option value="Phased Out">Phased Out</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Save Catalog Item</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Purchasing Supplies Specification</h3>
                <p class="modal-subtitle">Official catalog spec, price history, and vendor performance matrix.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Spec Sheet</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Deactivate Catalog Item</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to deactivate catalog item <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This item will no longer appear as selectable for new purchase requisitions.</p>
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
        document.getElementById("filterVendor").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterCategory").value = "";
            document.getElementById("filterVendor").value = "";
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
        const vendor = document.getElementById("filterVendor").value.toLowerCase();
        const rows = document.querySelectorAll("#suppliesCatalogTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowCat = (row.getAttribute("data-cat") || "").toLowerCase();
            const rowVendor = (row.getAttribute("data-vendor") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchCat = !cat || rowCat.includes(cat);
            const matchVendor = !vendor || rowVendor.includes(vendor);

            if (matchQuery && matchStatus && matchCat && matchVendor) {
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
        document.getElementById("entryModalTitle").innerText = "Register Supplies Catalog Item";
        document.getElementById("saveBtnLabel").innerText = "Save Catalog Item";
        document.getElementById("entryForm").reset();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editCatalogItem(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Catalog Item " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Item";

        if (id === '1') {
            document.getElementById("catCode").value = "MAT-CU-001";
            document.getElementById("catCategory").value = "Conductors";
            document.getElementById("catUOM").value = "MTR";
            document.getElementById("catName").value = "Copper Conductor Wire 0.12mm (Oxygen-Free)";
            document.getElementById("catSpec").value = "Spool Standard 5000m Reel, RoHS 3 Compliant";
            document.getElementById("catVendor").value = "Sumitomo Metal & Wire Corp.";
            document.getElementById("catPrice").value = "45.50";
            document.getElementById("catLeadTime").value = "5";
            document.getElementById("catMoq").value = "5000";
            document.getElementById("catStatus").value = "Active";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveCatalogItem(e) {
        e.preventDefault();
        alert("Supplies catalog pricing item saved.");
        closeEntryModal();
    }

    function viewCatalogItem(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Supply Code</span>
                    <span class="spec-value font-bold font-code">MAT-CU-001</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Category</span>
                    <span class="spec-value">Conductors & Wire</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Active Catalog</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Material Name</span>
                    <span class="spec-value font-bold">Copper Conductor Wire 0.12mm (Oxygen-Free)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Primary Contracted Supplier</span>
                    <span class="spec-value font-bold">Sumitomo Metal & Wire Corp.</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Negotiated Unit Price</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">₱ 45.50 / MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Standard Lead Time</span>
                    <span class="spec-value font-numeric font-bold">5 Days</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Minimum Order Qty (MOQ)</span>
                    <span class="spec-value font-numeric">5,000 MTR</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Quality Compliance</span>
                    <span class="spec-value">RoHS 3 & REACH Certified</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printCatalogItem(id) {
        viewCatalogItem(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteCatalogItem(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "MAT-CU-00" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Catalog item deactivated.");
        closeDeleteModal();
    }

    function bulkRequestQuote() {
        alert("RFQ drafted for selected items.");
    }

    function bulkPrintCatalog() {
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

