@extends('layouts.app')

@section('title', 'Finished Goods Inventory — Intracode ERP')

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
            <span class="bc-current">Finished Goods</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search SKU, Product, Warehouse Bay..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Finished Goods Inventory Ledger</h1>
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
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated inventory stock records</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" id="btnRegisterStock">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Register FG Item</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <div class="filter-item">
            <span class="filter-label">Warehouse:</span>
            <select id="filterWarehouse" class="filter-select">
                <option value="">All Warehouses</option>
                <option value="Central FG Warehouse">Central FG Warehouse</option>
                <option value="Export Staging Bay 2">Export Staging Bay 2</option>
                <option value="High-Density Rack Bay 4">High-Density Rack Bay 4</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Category:</span>
            <select id="filterCategory" class="filter-select">
                <option value="">All Categories</option>
                <option value="Flat Ribbon Cable">Flat Ribbon Cable</option>
                <option value="Wire Harness">Wire Harness</option>
                <option value="Jumper Cable">Jumper Cable</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Stock Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="In Stock">In Stock</option>
                <option value="Low Stock">Low Stock</option>
                <option value="Allocated">Allocated</option>
            </select>
        </div>

        <button type="button" class="btn-filter-reset" id="btnResetFilters">
            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
            <span>Reset Filters</span>
        </button>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Total FG SKUs</span>
            <span class="kpi-value" id="kpiTotalSkus">4</span>
            <span class="kpi-sub">Active finished product lines</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Stock On Hand</span>
            <span class="kpi-value" id="kpiTotalUnits" style="color: #2563eb;">118,500</span>
            <span class="kpi-sub">Aggregated physical inventory</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Available for Dispatch</span>
            <span class="kpi-value" id="kpiAvailableUnits" style="color: #059669;">86,000</span>
            <span class="kpi-sub">Unreserved stock balance</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Valuation (PHP)</span>
            <span class="kpi-value" id="kpiTotalValuation" style="color: #0f172a;">₱ 5,324,800.00</span>
            <span class="kpi-sub">FIFO cost basis</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Finished Goods Stock Ledger</span>
                    <span class="count-pill" id="tableBadgeCount">4 Records</span>
                    <span class="selection-pill" id="bulkActionGroup">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedRowCount">0 selected</span>
                        <button type="button" class="btn-bulk-delete" onclick="bulkDispatch()">Batch Allocate</button>
                    </span>
                </div>
            </div>

            <div class="table-scroll-container">
                <table class="erp-table" id="stockTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb">
                            </th>
                            <th style="min-width: 140px;">SKU / Product Code</th>
                            <th style="min-width: 220px;">Product Name & Specification</th>
                            <th style="min-width: 140px;">Category</th>
                            <th style="min-width: 180px;">Warehouse Bay / Bin</th>
                            <th style="min-width: 100px;" class="text-right">Total Stock</th>
                            <th style="min-width: 100px;" class="text-right">Allocated Qty</th>
                            <th style="min-width: 105px;" class="text-right">Available Qty</th>
                            <th style="min-width: 90px;" class="text-right">Reorder Pt</th>
                            <th style="min-width: 110px;" class="text-right">Unit Cost</th>
                            <th style="min-width: 120px;" class="text-right">Total Valuation</th>
                            <th style="min-width: 110px;" class="text-center">Status</th>
                            <th style="width: 60px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="stockTableBody">
                    </tbody>
                </table>
            </div>

            <div class="table-panel-footer no-print">
                <div class="panel-footer-info" id="paginationSummary">
                    Showing 1 to 4 of 4 entries
                </div>
                <div class="panel-pagination">
                    <button type="button" class="page-btn" disabled>&laquo; Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn" disabled>Next &raquo;</button>
                </div>
            </div>
        </div>
    </main>

</div>

<div class="modal-backdrop" id="stockModal">
    <div class="modal-dialog modal-dialog-large">
        <form id="stockForm" onsubmit="saveStockForm(event)">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h3 class="modal-title" id="stockModalTitle">Register Finished Goods Item</h3>
                    <span class="modal-subtitle">Define catalog parameters, warehouse bin location, and reorder threshold</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeStockModal()"><i data-lucide="x"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">1</span>
                        <span>Item Identification & Catalog Link</span>
                    </div>

                    <div class="form-grid-3">
                        <div class="form-group">
                            <label class="form-label">SKU / Product Code <span class="req-star">*</span></label>
                            <input type="text" id="modalSku" class="form-control" placeholder="e.g. FFC-05-100" required>
                        </div>

                        <div class="form-group col-span-2">
                            <label class="form-label">Product Name & Description <span class="req-star">*</span></label>
                            <input type="text" id="modalDesc" class="form-control" placeholder="FFC Flexible Ribbon Cable 1.0mm Pitch" required>
                        </div>
                    </div>

                    <div class="form-grid-3" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Category <span class="req-star">*</span></label>
                            <select id="modalCategory" class="form-control" required>
                                <option value="Flat Ribbon Cable">Flat Ribbon Cable</option>
                                <option value="Wire Harness">Wire Harness</option>
                                <option value="Jumper Cable">Jumper Cable</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Warehouse Facility <span class="req-star">*</span></label>
                            <select id="modalWarehouse" class="form-control" required>
                                <option value="Central FG Warehouse">Central FG Warehouse</option>
                                <option value="Export Staging Bay 2">Export Staging Bay 2</option>
                                <option value="High-Density Rack Bay 4">High-Density Rack Bay 4</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Bin Location <span class="req-star">*</span></label>
                            <input type="text" id="modalBin" class="form-control" placeholder="e.g. RACK-A-04-1" required>
                        </div>
                    </div>
                </div>

                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">2</span>
                        <span>Inventory Levels & Valuation</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group">
                            <label class="form-label">Initial Physical Stock <span class="req-star">*</span></label>
                            <input type="number" id="modalStock" class="form-control font-numeric" value="25000" oninput="calcAvailable()" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Allocated to Orders</label>
                            <input type="number" id="modalAllocated" class="form-control font-numeric" value="5000" oninput="calcAvailable()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Available for Dispatch</label>
                            <input type="number" id="modalAvailable" class="form-control font-numeric font-bold" value="20000" readonly style="color: #059669; background: #f8fafc;">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Reorder Safety Level</label>
                            <input type="number" id="modalReorder" class="form-control font-numeric" value="5000" required>
                        </div>
                    </div>

                    <div class="form-grid-3" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Unit Cost (PHP) <span class="req-star">*</span></label>
                            <input type="number" step="0.01" id="modalCost" class="form-control font-numeric" value="45.50" oninput="calcValuation()" required>
                        </div>

                        <div class="form-group col-span-2">
                            <label class="form-label">Total Inventory Valuation</label>
                            <input type="text" id="modalValuation" class="form-control font-numeric font-bold" value="₱ 1,137,500.00" readonly style="background: #f8fafc;">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeStockModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSaveStock">Save Finished Goods SKU</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewModalTitle">Finished Goods Stock Card</h3>
                <span class="modal-subtitle">Bin card movements, storage locations, and audit balance</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="viewModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Stock Card</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    let stockItems = [
        {
            id: 1,
            sku: "FFC-05-100",
            desc: "FFC Flexible Ribbon Cable 1.0mm Pitch 100mm",
            category: "Flat Ribbon Cable",
            warehouse: "Central FG Warehouse",
            bin: "RACK-A-04-1",
            totalStock: 35000,
            allocated: 10000,
            available: 25000,
            reorder: 5000,
            unitCost: 45.50,
            status: "In Stock"
        },
        {
            id: 2,
            sku: "SH-05-HARN",
            desc: "Shielded Harness Wire Assembly 0.5mm AWG28",
            category: "Wire Harness",
            warehouse: "Central FG Warehouse",
            bin: "RACK-B-02-3",
            totalStock: 48000,
            allocated: 18000,
            available: 30000,
            reorder: 8000,
            unitCost: 52.00,
            status: "In Stock"
        },
        {
            id: 3,
            sku: "JMP-05-LEAD",
            desc: "Fine Pitch Jumper Lead Assembly Dual Connector",
            category: "Jumper Cable",
            warehouse: "Export Staging Bay 2",
            bin: "RACK-D-01-2",
            totalStock: 25500,
            allocated: 4500,
            available: 21000,
            reorder: 4000,
            unitCost: 38.00,
            status: "In Stock"
        },
        {
            id: 4,
            sku: "UFC-125-FLAT",
            desc: "Ultra-Flex Flat Cable 1.25mm Pitch Heavy Duty",
            category: "Flat Ribbon Cable",
            warehouse: "High-Density Rack Bay 4",
            bin: "RACK-E-03-4",
            totalStock: 10000,
            allocated: 0,
            available: 10000,
            reorder: 3000,
            unitCost: 65.00,
            status: "In Stock"
        }
    ];

    let currentEditingId = null;
    let openActionDropdown = null;

    document.addEventListener("DOMContentLoaded", () => {
        renderTable();
        initDropdowns();
        initFilters();
        lucide.createIcons();
    });

    function initDropdowns() {
        const btnExport = document.getElementById("btnExportToggle");
        const exportMenu = document.getElementById("exportMenu");
        if (btnExport && exportMenu) {
            btnExport.addEventListener("click", (e) => {
                e.stopPropagation();
                exportMenu.classList.toggle("show");
            });
        }

        document.addEventListener("click", (e) => {
            if (exportMenu && !exportMenu.contains(e.target) && e.target !== btnExport) {
                exportMenu.classList.remove("show");
            }
            if (openActionDropdown && !openActionDropdown.contains(e.target)) {
                openActionDropdown.classList.remove("show");
                openActionDropdown = null;
            }
        });

        document.getElementById("btnRegisterStock").addEventListener("click", openCreateModal);
        document.getElementById("btnRefresh").addEventListener("click", renderTable);

        document.getElementById("selectAllCheckbox").addEventListener("change", function() {
            document.querySelectorAll(".row-select-check").forEach(c => c.checked = this.checked);
            updateBulkSelection();
        });
    }

    function initFilters() {
        const searchInput = document.getElementById("globalSearchInput");
        const filterWarehouse = document.getElementById("filterWarehouse");
        const filterCategory = document.getElementById("filterCategory");
        const filterStatus = document.getElementById("filterStatus");
        const btnReset = document.getElementById("btnResetFilters");

        const handler = () => renderTable();

        searchInput.addEventListener("input", handler);
        filterWarehouse.addEventListener("change", handler);
        filterCategory.addEventListener("change", handler);
        filterStatus.addEventListener("change", handler);

        btnReset.addEventListener("click", () => {
            searchInput.value = "";
            filterWarehouse.value = "";
            filterCategory.value = "";
            filterStatus.value = "";
            renderTable();
        });
    }

    function renderTable() {
        const tbody = document.getElementById("stockTableBody");
        const search = (document.getElementById("globalSearchInput").value || "").toLowerCase().trim();
        const warehouse = document.getElementById("filterWarehouse").value;
        const category = document.getElementById("filterCategory").value;
        const status = document.getElementById("filterStatus").value;

        const filtered = stockItems.filter(item => {
            if (warehouse && item.warehouse !== warehouse) return false;
            if (category && item.category !== category) return false;
            if (status && item.status !== status) return false;

            if (search) {
                const hay = `${item.sku} ${item.desc} ${item.category} ${item.warehouse} ${item.bin}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }
            return true;
        });

        tbody.innerHTML = "";

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="13" style="text-align: center; padding: 36px; color: #94a3b8;">
                        <i data-lucide="inbox" style="margin-bottom: 6px; width: 28px; height: 28px;"></i>
                        <div>No matching finished goods inventory records found</div>
                    </td>
                </tr>
            `;
            lucide.createIcons();
            updateKPIs(filtered);
            return;
        }

        filtered.forEach(item => {
            const tr = document.createElement("tr");
            const valuation = item.totalStock * item.unitCost;

            tr.innerHTML = `
                <td style="text-align: center;">
                    <input type="checkbox" class="row-select-check erp-cb" value="${item.id}" onchange="updateBulkSelection()">
                </td>
                <td>
                    <a href="javascript:void(0)" class="font-code font-bold" style="color: #2563eb; text-decoration: none;" onclick="viewStockCard(${item.id})">${item.sku}</a>
                </td>
                <td>${item.desc}</td>
                <td>${item.category}</td>
                <td><strong class="font-code" style="color: #334155;">${item.bin}</strong> <small style="color: #94a3b8;">(${item.warehouse})</small></td>
                <td class="font-numeric text-right font-bold">${item.totalStock.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #d97706;">${item.allocated.toLocaleString()}</td>
                <td class="font-numeric text-right font-bold" style="color: #059669;">${item.available.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #64748b;">${item.reorder.toLocaleString()}</td>
                <td class="font-numeric text-right">₱ ${item.unitCost.toFixed(2)}</td>
                <td class="font-numeric text-right font-bold" style="color: #0f172a;">₱ ${valuation.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-center"><span class="status-pill status-pill-completed">${item.status}</span></td>
                <td class="actions-cell">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, ${item.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${item.id}">
                        <button type="button" class="action-menu-item" onclick="viewStockCard(${item.id})">
                            <i data-lucide="eye"></i> View Bin Card
                        </button>
                        <button type="button" class="action-menu-item" onclick="editStockItem(${item.id})">
                            <i data-lucide="edit"></i> Edit Item
                        </button>
                        <button type="button" class="action-menu-item" onclick="printStockCard(${item.id})">
                            <i data-lucide="printer"></i> Print Bin Card
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        });

        document.getElementById("tableBadgeCount").innerText = `${filtered.length} Records`;
        document.getElementById("paginationSummary").innerText = `Showing 1 to ${filtered.length} of ${filtered.length} entries`;

        updateKPIs(filtered);
        lucide.createIcons();
    }

    function updateKPIs(data) {
        document.getElementById("kpiTotalSkus").innerText = data.length;
        const totalUnits = data.reduce((acc, i) => acc + (i.totalStock || 0), 0);
        const availUnits = data.reduce((acc, i) => acc + (i.available || 0), 0);
        const totalVal = data.reduce((acc, i) => acc + (i.totalStock * i.unitCost), 0);

        document.getElementById("kpiTotalUnits").innerText = totalUnits.toLocaleString();
        document.getElementById("kpiAvailableUnits").innerText = availUnits.toLocaleString();
        document.getElementById("kpiTotalValuation").innerText = `₱ ${totalVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function toggleActionMenu(event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`actionMenu-${id}`);

        if (openActionDropdown && openActionDropdown !== menu) {
            openActionDropdown.classList.remove("show");
        }

        if (menu.classList.contains("show")) {
            menu.classList.remove("show");
            openActionDropdown = null;
            return;
        }

        const rect = menu.parentElement.getBoundingClientRect();
        if (window.innerHeight - rect.bottom < 150) {
            menu.classList.add("drop-up");
            menu.classList.remove("drop-down");
        } else {
            menu.classList.add("drop-down");
            menu.classList.remove("drop-up");
        }

        menu.classList.add("show");
        openActionDropdown = menu;
    }

    function updateBulkSelection() {
        const checked = document.querySelectorAll(".row-select-check:checked");
        const bulkGroup = document.getElementById("bulkActionGroup");
        const countSpan = document.getElementById("selectedRowCount");

        if (checked.length > 0) {
            bulkGroup.classList.add("show");
            countSpan.innerText = `${checked.length} selected`;
        } else {
            bulkGroup.classList.remove("show");
            document.getElementById("selectAllCheckbox").checked = false;
        }
    }

    function bulkDispatch() {
        alert("Batch allocation initiated for selected FG inventory items.");
    }

    function openCreateModal() {
        currentEditingId = null;
        document.getElementById("stockModalTitle").innerText = "Register Finished Goods Item";
        document.getElementById("stockForm").reset();
        calcAvailable();
        calcValuation();
        document.getElementById("stockModal").classList.add("show");
        lucide.createIcons();
    }

    function editStockItem(id) {
        currentEditingId = id;
        const item = stockItems.find(i => i.id === id);
        if (!item) return;

        document.getElementById("stockModalTitle").innerText = `Edit FG Item — ${item.sku}`;
        document.getElementById("modalSku").value = item.sku;
        document.getElementById("modalDesc").value = item.desc;
        document.getElementById("modalCategory").value = item.category;
        document.getElementById("modalWarehouse").value = item.warehouse;
        document.getElementById("modalBin").value = item.bin;
        document.getElementById("modalStock").value = item.totalStock;
        document.getElementById("modalAllocated").value = item.allocated;
        document.getElementById("modalReorder").value = item.reorder;
        document.getElementById("modalCost").value = item.unitCost;

        calcAvailable();
        calcValuation();
        document.getElementById("stockModal").classList.add("show");
        lucide.createIcons();
    }

    function calcAvailable() {
        const stock = parseFloat(document.getElementById("modalStock").value) || 0;
        const alloc = parseFloat(document.getElementById("modalAllocated").value) || 0;
        document.getElementById("modalAvailable").value = Math.max(0, stock - alloc);
    }

    function calcValuation() {
        const stock = parseFloat(document.getElementById("modalStock").value) || 0;
        const cost = parseFloat(document.getElementById("modalCost").value) || 0;
        const val = stock * cost;
        document.getElementById("modalValuation").value = `₱ ${val.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    function closeStockModal() {
        document.getElementById("stockModal").classList.remove("show");
    }

    function saveStockForm(event) {
        event.preventDefault();

        const stock = parseFloat(document.getElementById("modalStock").value) || 0;
        const alloc = parseFloat(document.getElementById("modalAllocated").value) || 0;
        const cost = parseFloat(document.getElementById("modalCost").value) || 0;

        const payload = {
            sku: document.getElementById("modalSku").value,
            desc: document.getElementById("modalDesc").value,
            category: document.getElementById("modalCategory").value,
            warehouse: document.getElementById("modalWarehouse").value,
            bin: document.getElementById("modalBin").value,
            totalStock: stock,
            allocated: alloc,
            available: Math.max(0, stock - alloc),
            reorder: parseFloat(document.getElementById("modalReorder").value) || 0,
            unitCost: cost,
            status: "In Stock"
        };

        if (currentEditingId) {
            const idx = stockItems.findIndex(i => i.id === currentEditingId);
            if (idx !== -1) {
                stockItems[idx] = { ...stockItems[idx], ...payload };
            }
        } else {
            payload.id = stockItems.length > 0 ? Math.max(...stockItems.map(i => i.id)) + 1 : 1;
            stockItems.unshift(payload);
        }

        closeStockModal();
        renderTable();
    }

    function viewStockCard(id) {
        const item = stockItems.find(i => i.id === id);
        if (!item) return;

        document.getElementById("viewModalTitle").innerText = `Finished Goods Stock Card — ${item.sku}`;
        const body = document.getElementById("viewModalBody");

        const valuation = item.totalStock * item.unitCost;

        body.innerHTML = `
            <div class="form-section-card" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">${item.sku}</h2>
                        <span style="font-size: 12px; color: #64748b;">${item.desc}</span>
                    </div>
                    <span class="status-pill status-pill-completed" style="font-size: 12px; padding: 4px 10px;">${item.status}</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; font-size: 12px;">
                    <div><span style="color: #64748b;">Category:</span><br><strong>${item.category}</strong></div>
                    <div><span style="color: #64748b;">Warehouse:</span><br><strong>${item.warehouse}</strong></div>
                    <div><span style="color: #64748b;">Bin Location:</span><br><strong class="font-code">${item.bin}</strong></div>
                    <div><span style="color: #64748b;">Reorder Level:</span><br><strong class="font-numeric">${item.reorder.toLocaleString()}</strong></div>
                    <div><span style="color: #64748b;">Physical Stock:</span><br><strong class="font-numeric">${item.totalStock.toLocaleString()}</strong></div>
                    <div><span style="color: #64748b;">Allocated:</span><br><strong class="font-numeric" style="color: #d97706;">${item.allocated.toLocaleString()}</strong></div>
                    <div><span style="color: #64748b;">Available:</span><br><strong class="font-numeric" style="color: #059669;">${item.available.toLocaleString()}</strong></div>
                    <div><span style="color: #64748b;">Valuation:</span><br><strong class="font-numeric" style="color: #2563eb;">₱ ${valuation.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</strong></div>
                </div>
            </div>

            <div class="form-section-card">
                <div class="section-badge-title">
                    <span class="badge-num"><i data-lucide="history" style="width: 12px; height: 12px;"></i></span>
                    <span>Recent Inventory Movement Log</span>
                </div>
                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Txn Date</th>
                                <th>Movement Ref</th>
                                <th>Source / Doc</th>
                                <th class="text-right">Qty In</th>
                                <th class="text-right">Qty Out</th>
                                <th class="text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-numeric">2026-11-04</td>
                                <td class="font-code">MOV-261104-01</td>
                                <td>FGTS-2026-0091 (Line Output Handover)</td>
                                <td class="font-numeric text-right font-bold" style="color: #059669;">+20,000</td>
                                <td class="font-numeric text-right">-</td>
                                <td class="font-numeric text-right font-bold">35,000</td>
                            </tr>
                            <tr>
                                <td class="font-numeric">2026-11-03</td>
                                <td class="font-code">MOV-261103-08</td>
                                <td>DR-2026-001 (Customer Delivery)</td>
                                <td class="font-numeric text-right">-</td>
                                <td class="font-numeric text-right font-bold" style="color: #dc2626;">-5,000</td>
                                <td class="font-numeric text-right font-bold">15,000</td>
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

    function printStockCard(id) {
        viewStockCard(id);
        setTimeout(() => window.print(), 300);
    }
</script>
@endsection

