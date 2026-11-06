@extends('layouts.app')

@section('title', 'Production Output Inventory — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Production/ProdOutputInv/prodoutputinv.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">

    <header class="top-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-item">Revenue & Operations</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Production</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Production Output Inventory</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Lot#, Output#, Product Code, Staging Bay..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Production Output Inventory</h1>
        </div>

        <div class="action-cluster">
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Inventory Buffer">
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
                            <strong>Print Staging Ledger</strong>
                            <small>Floor staging inventory sheet</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated staging buffer data</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" id="btnRegisterLot">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Register Staging Lot</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="Available">Available</option>
                <option value="Partially Transferred">Partially Transferred</option>
                <option value="Fully Transferred">Fully Transferred</option>
                <option value="Quarantine">Quarantine</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Staging Bay:</span>
            <select id="filterBay" class="filter-select">
                <option value="">All Staging Bays</option>
                <option value="Bay 01 - SMT Line 1 Buffer">Bay 01 - SMT Line 1 Buffer</option>
                <option value="Bay 02 - SMT Line 2 Buffer">Bay 02 - SMT Line 2 Buffer</option>
                <option value="Bay 03 - Extrusion Staging">Bay 03 - Extrusion Staging</option>
                <option value="Bay 04 - Post-Cure Holding">Bay 04 - Post-Cure Holding</option>
                <option value="Quarantine Inspection Cell">Quarantine Inspection Cell</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Prod Date:</span>
            <div class="filter-date-group">
                <input type="date" id="filterDateFrom" class="filter-date-input">
                <span class="filter-sep">to</span>
                <input type="date" id="filterDateTo" class="filter-date-input">
            </div>
        </div>

        <button type="button" class="btn-filter-reset" id="btnResetFilters">
            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
            <span>Reset Filters</span>
        </button>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Active Staging Lots</span>
            <span class="kpi-value" id="kpiTotalLots">4</span>
            <span class="kpi-sub">Floor inventory lots</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Buffer Units</span>
            <span class="kpi-value" id="kpiBufferUnits" style="color: #2563eb;">93,750</span>
            <span class="kpi-sub">Total manufactured units</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Ready for FGTS Handover</span>
            <span class="kpi-value" id="kpiAvailableUnits" style="color: #10b981;">54,350</span>
            <span class="kpi-sub">Passed inspection buffer</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Quarantine & Hold</span>
            <span class="kpi-value" id="kpiQuarantineUnits" style="color: #f59e0b;">2,400</span>
            <span class="kpi-sub">Pending QA disposition</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Production Staging Inventory Ledger</span>
                    <span class="count-pill" id="tableBadgeCount">4 Records</span>
                    <span class="selection-pill" id="bulkActionGroup">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedRowCount">0 selected</span>
                        <button type="button" class="btn-bulk-delete" onclick="bulkTransferFGTS()">Handover via FGTS</button>
                    </span>
                </div>
            </div>

            <div class="table-scroll-container">
                <table class="erp-table" id="invTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb">
                            </th>
                            <th style="min-width: 140px;">Inventory Lot No</th>
                            <th style="min-width: 130px;">Output Ref No</th>
                            <th style="min-width: 120px;">Work Order</th>
                            <th style="min-width: 120px;">Product Code</th>
                            <th style="min-width: 220px;">Product Description</th>
                            <th style="min-width: 180px;">Staging Bay</th>
                            <th style="min-width: 110px;">Prod Date</th>
                            <th style="min-width: 100px;" class="text-right">Total Units</th>
                            <th style="min-width: 105px;" class="text-right">Available Units</th>
                            <th style="min-width: 95px;" class="text-right">Transferred</th>
                            <th style="min-width: 95px;" class="text-right">Quarantine</th>
                            <th style="min-width: 130px;" class="text-center">Status</th>
                            <th style="min-width: 100px;" class="text-center">Deleted Date</th>
                            <th style="width: 60px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="invTableBody">

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

<div class="modal-backdrop" id="invModal">
    <div class="modal-dialog modal-dialog-large">
        <form id="invForm" onsubmit="saveInvLotForm(event)">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h3 class="modal-title" id="invModalTitle">Register Staging Lot</h3>
                    <span class="modal-subtitle">Register and balance floor buffer lots prior to Finished Goods transfer</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeInvModal()"><i data-lucide="x"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">1</span>
                        <span>Inventory Lot Identifiers</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group">
                            <label class="form-label">Inventory Lot No <span class="req-star">*</span></label>
                            <input type="text" id="modalLotNo" class="form-control" placeholder="LOT-2026-XXXX" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Output Run Ref <span class="req-star">*</span></label>
                            <input type="text" id="modalOutputRef" class="form-control" placeholder="OUT-2026-0001" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Work Order / MRP <span class="req-star">*</span></label>
                            <input type="text" id="modalWorkOrder" class="form-control" placeholder="WO-2026-0814" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Production Date <span class="req-star">*</span></label>
                            <input type="date" id="modalProdDate" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-grid-3" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Product <span class="req-star">*</span></label>
                            <select id="modalProductSelect" class="form-control" required>
                                <option value="FFC-05-100">FFC-05-100 — FFC Flexible Ribbon Cable 1.0mm</option>
                                <option value="SH-05-HARN">SH-05-HARN — Shielded Harness Wire Assembly 0.5mm</option>
                                <option value="JMP-05-LEAD">JMP-05-LEAD — Fine Pitch Jumper Lead Assembly</option>
                                <option value="UFC-125-FLAT">UFC-125-FLAT — Ultra-Flex Flat Cable 1.25mm</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Staging Bay Location <span class="req-star">*</span></label>
                            <select id="modalStagingBay" class="form-control" required>
                                <option value="Bay 01 - SMT Line 1 Buffer">Bay 01 - SMT Line 1 Buffer</option>
                                <option value="Bay 02 - SMT Line 2 Buffer">Bay 02 - SMT Line 2 Buffer</option>
                                <option value="Bay 03 - Extrusion Staging">Bay 03 - Extrusion Staging</option>
                                <option value="Bay 04 - Post-Cure Holding">Bay 04 - Post-Cure Holding</option>
                                <option value="Quarantine Inspection Cell">Quarantine Inspection Cell</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="req-star">*</span></label>
                            <select id="modalLotStatus" class="form-control" required>
                                <option value="Available">Available</option>
                                <option value="Partially Transferred">Partially Transferred</option>
                                <option value="Fully Transferred">Fully Transferred</option>
                                <option value="Quarantine">Quarantine</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">2</span>
                        <span>Quantity Balance & Allocations</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group">
                            <label class="form-label">Total Produced Units <span class="req-star">*</span></label>
                            <input type="number" id="modalTotalUnits" class="form-control font-numeric" value="25000" oninput="recalcModalUnits()" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Transferred Units (FGTS)</label>
                            <input type="number" id="modalTransferredUnits" class="form-control font-numeric" value="0" oninput="recalcModalUnits()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Quarantine Units</label>
                            <input type="number" id="modalQuarantineUnits" class="form-control font-numeric" value="0" oninput="recalcModalUnits()">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Available for FGTS <span class="req-star">*</span></label>
                            <input type="number" id="modalAvailableUnits" class="form-control font-numeric font-bold" value="25000" readonly style="color: #059669; background: #f8fafc;">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label">Lot Storage & Handling Precautions</label>
                        <input type="text" id="modalLotNotes" class="form-control" placeholder="Storage shelf, bin ID, packaging reel details, or special handling precautions...">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeInvModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSaveInvLot">Save Inventory Lot</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewModalTitle">Staging Lot Ledger Card</h3>
                <span class="modal-subtitle">Floor buffer custody and inventory balance traceability</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="viewModalBody">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Lot Ticket</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-dialog modal-dialog-small">
        <div class="modal-header">
            <h3 class="modal-title" style="color: #dc2626;">Confirm Deletion</h3>
            <button type="button" class="modal-close-btn" onclick="closeDeleteModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body">
            <p style="font-size: 13px; color: #475569; margin-bottom: 8px;">
                Are you sure you want to delete staging inventory record <strong id="deleteLotNo"></strong>?
            </p>
            <p style="font-size: 11.5px; color: #94a3b8;">
                This action is audited. Units will be retracted from available floor inventory balance.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-primary" style="background: #dc2626; border-color: #dc2626;" onclick="confirmDeleteInvLot()">Delete Record</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>


    let stagingLots = [
        {
            id: 1,
            lotNo: "LOT-2026-0881",
            outputRef: "OUT-2026-0001",
            workOrder: "WO-2026-0814",
            prodCode: "FFC-05-100",
            description: "FFC Flexible Ribbon Cable 1.0mm Pitch",
            stagingBay: "Bay 01 - SMT Line 1 Buffer",
            date: "2026-11-04",
            totalUnits: 24700,
            availableUnits: 14700,
            transferredUnits: 10000,
            quarantineUnits: 0,
            status: "Partially Transferred",
            deletedDate: "-",
            notes: "Packed in 50-unit reels with ESD shielding. Moisture barrier bag vacuum sealed."
        },
        {
            id: 2,
            lotNo: "LOT-2026-0882",
            outputRef: "OUT-2026-0002",
            workOrder: "WO-2026-0819",
            prodCode: "SH-05-HARN",
            description: "Shielded Harness Wire Assembly 0.5mm",
            stagingBay: "Bay 02 - SMT Line 2 Buffer",
            date: "2026-11-05",
            totalUnits: 29400,
            availableUnits: 29400,
            transferredUnits: 0,
            quarantineUnits: 0,
            status: "Available",
            deletedDate: "-",
            notes: "Passed 100% automated continuity test. Ready for warehouse handover."
        },
        {
            id: 3,
            lotNo: "LOT-2026-0883",
            outputRef: "OUT-2026-0003",
            workOrder: "WO-2026-0824",
            prodCode: "JMP-05-LEAD",
            description: "Fine Pitch Jumper Lead Assembly",
            stagingBay: "Bay 03 - Extrusion Staging",
            date: "2026-11-06",
            totalUnits: 19650,
            availableUnits: 10250,
            transferredUnits: 7000,
            quarantineUnits: 2400,
            status: "Partially Transferred",
            deletedDate: "-",
            notes: "2,400 units placed under quarantine review due to tensile elongation testing."
        },
        {
            id: 4,
            lotNo: "LOT-2026-0884",
            outputRef: "OUT-2026-0004",
            workOrder: "WO-2026-0801",
            prodCode: "UFC-125-FLAT",
            description: "Ultra-Flex Flat Cable 1.25mm Pitch",
            stagingBay: "Quarantine Inspection Cell",
            date: "2026-11-03",
            totalUnits: 20000,
            availableUnits: 0,
            transferredUnits: 20000,
            quarantineUnits: 0,
            status: "Fully Transferred",
            deletedDate: "-",
            notes: "Transferred in full under FGTS-2026-0091."
        }
    ];

    let currentEditingId = null;
    let pendingDeleteId = null;
    let openActionDropdown = null;


    document.addEventListener("DOMContentLoaded", () => {
        renderTable();
        initDropdowns();
        initSearchAndFilters();
        lucide.createIcons();
    });

    function initDropdowns() {
        const btnExportToggle = document.getElementById("btnExportToggle");
        const exportMenu = document.getElementById("exportMenu");
        if (btnExportToggle && exportMenu) {
            btnExportToggle.addEventListener("click", (e) => {
                e.stopPropagation();
                exportMenu.classList.toggle("show");
            });
        }

        document.addEventListener("click", (e) => {
            if (exportMenu && !exportMenu.contains(e.target) && e.target !== btnExportToggle) {
                exportMenu.classList.remove("show");
            }
            if (openActionDropdown && !openActionDropdown.contains(e.target)) {
                openActionDropdown.classList.remove("show");
                openActionDropdown = null;
            }
        });

        document.getElementById("btnRegisterLot").addEventListener("click", openCreateModal);
        document.getElementById("btnRefresh").addEventListener("click", renderTable);

        document.getElementById("selectAllCheckbox").addEventListener("change", function() {
            const checks = document.querySelectorAll(".row-select-check");
            checks.forEach(c => c.checked = this.checked);
            updateBulkSelection();
        });
    }

    function initSearchAndFilters() {
        const searchInput = document.getElementById("globalSearchInput");
        const filterStatus = document.getElementById("filterStatus");
        const filterBay = document.getElementById("filterBay");
        const filterDateFrom = document.getElementById("filterDateFrom");
        const filterDateTo = document.getElementById("filterDateTo");
        const btnReset = document.getElementById("btnResetFilters");

        const handler = () => renderTable();

        searchInput.addEventListener("input", handler);
        filterStatus.addEventListener("change", handler);
        filterBay.addEventListener("change", handler);
        filterDateFrom.addEventListener("change", handler);
        filterDateTo.addEventListener("change", handler);

        btnReset.addEventListener("click", () => {
            searchInput.value = "";
            filterStatus.value = "";
            filterBay.value = "";
            filterDateFrom.value = "";
            filterDateTo.value = "";
            renderTable();
        });
    }


    function renderTable() {
        const tbody = document.getElementById("invTableBody");
        const search = (document.getElementById("globalSearchInput").value || "").toLowerCase().trim();
        const status = document.getElementById("filterStatus").value;
        const bay = document.getElementById("filterBay").value;
        const dFrom = document.getElementById("filterDateFrom").value;
        const dTo = document.getElementById("filterDateTo").value;

        const filtered = stagingLots.filter(l => {
            if (status && l.status !== status) return false;
            if (bay && l.stagingBay !== bay) return false;
            if (dFrom && l.date < dFrom) return false;
            if (dTo && l.date > dTo) return false;

            if (search) {
                const hay = `${l.lotNo} ${l.outputRef} ${l.workOrder} ${l.prodCode} ${l.description} ${l.stagingBay}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }
            return true;
        });

        tbody.innerHTML = "";

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="15" style="text-align: center; padding: 36px; color: #94a3b8;">
                        <i data-lucide="inbox" style="margin-bottom: 6px; width: 28px; height: 28px;"></i>
                        <div>No matching staging inventory records found</div>
                    </td>
                </tr>
            `;
            lucide.createIcons();
            updateKPIs(filtered);
            return;
        }

        filtered.forEach(l => {
            const tr = document.createElement("tr");

            let statusClass = "status-pill-completed";
            if (l.status === "Partially Transferred") statusClass = "status-pill-progress";
            else if (l.status === "Fully Transferred") statusClass = "status-pill-completed";
            else if (l.status === "Quarantine") statusClass = "status-pill-hold";

            tr.innerHTML = `
                <td style="text-align: center;">
                    <input type="checkbox" class="row-select-check erp-cb" value="${l.id}" onchange="updateBulkSelection()">
                </td>
                <td>
                    <a href="javascript:void(0)" class="font-code font-bold" style="color: #2563eb; text-decoration: none;" onclick="viewLotDossier(${l.id})">${l.lotNo}</a>
                </td>
                <td class="font-code">${l.outputRef}</td>
                <td><strong class="font-code" style="color: #334155;">${l.workOrder}</strong></td>
                <td><strong class="font-code" style="color: #2563eb;">${l.prodCode}</strong></td>
                <td>${l.description}</td>
                <td>${l.stagingBay}</td>
                <td class="font-numeric">${l.date}</td>
                <td class="font-numeric text-right">${l.totalUnits.toLocaleString()}</td>
                <td class="font-numeric text-right font-bold" style="color: #059669;">${l.availableUnits.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #2563eb;">${l.transferredUnits.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #d97706;">${l.quarantineUnits.toLocaleString()}</td>
                <td class="text-center"><span class="status-pill ${statusClass}">${l.status}</span></td>
                <td class="text-center font-numeric" style="color: #94a3b8; font-size: 11px;">${l.deletedDate}</td>
                <td class="actions-cell">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, ${l.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${l.id}">
                        <button type="button" class="action-menu-item" onclick="viewLotDossier(${l.id})">
                            <i data-lucide="eye"></i> View Lot Details
                        </button>
                        <button type="button" class="action-menu-item" onclick="editInvLot(${l.id})">
                            <i data-lucide="edit"></i> Edit Inventory Lot
                        </button>
                        <button type="button" class="action-menu-item" onclick="printLotCard(${l.id})">
                            <i data-lucide="printer"></i> Print Lot Card
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteLot(${l.id})">
                            <i data-lucide="trash-2"></i> Delete
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
        document.getElementById("kpiTotalLots").innerText = data.length;
        const totalBuffer = data.reduce((acc, l) => acc + (l.totalUnits || 0), 0);
        const totalAvail = data.reduce((acc, l) => acc + (l.availableUnits || 0), 0);
        const totalQuar = data.reduce((acc, l) => acc + (l.quarantineUnits || 0), 0);

        document.getElementById("kpiBufferUnits").innerText = totalBuffer.toLocaleString();
        document.getElementById("kpiAvailableUnits").innerText = totalAvail.toLocaleString();
        document.getElementById("kpiQuarantineUnits").innerText = totalQuar.toLocaleString();
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
        const spaceBelow = window.innerHeight - rect.bottom;
        if (spaceBelow < 160) {
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

    function bulkTransferFGTS() {
        window.location.href = "{{ route('production.fgts.index') }}";
    }


    function openCreateModal() {
        currentEditingId = null;
        document.getElementById("invModalTitle").innerText = "Register Staging Lot";
        document.getElementById("invForm").reset();

        const now = new Date();
        document.getElementById("modalProdDate").value = now.toISOString().split("T")[0];
        document.getElementById("modalLotNo").value = `LOT-2026-${String(stagingLots.length + 881).padStart(4, "0")}`;
        document.getElementById("modalOutputRef").value = `OUT-2026-000${stagingLots.length + 1}`;
        document.getElementById("modalWorkOrder").value = "WO-2026-0830";
        document.getElementById("modalTotalUnits").value = 15000;
        document.getElementById("modalTransferredUnits").value = 0;
        document.getElementById("modalQuarantineUnits").value = 0;
        document.getElementById("modalAvailableUnits").value = 15000;

        document.getElementById("invModal").classList.add("show");
        lucide.createIcons();
    }

    function editInvLot(id) {
        currentEditingId = id;
        const rec = stagingLots.find(l => l.id === id);
        if (!rec) return;

        document.getElementById("invModalTitle").innerText = `Edit Staging Lot — ${rec.lotNo}`;
        document.getElementById("modalLotNo").value = rec.lotNo;
        document.getElementById("modalOutputRef").value = rec.outputRef;
        document.getElementById("modalWorkOrder").value = rec.workOrder;
        document.getElementById("modalProdDate").value = rec.date;
        document.getElementById("modalProductSelect").value = rec.prodCode;
        document.getElementById("modalStagingBay").value = rec.stagingBay;
        document.getElementById("modalLotStatus").value = rec.status;
        document.getElementById("modalTotalUnits").value = rec.totalUnits;
        document.getElementById("modalTransferredUnits").value = rec.transferredUnits;
        document.getElementById("modalQuarantineUnits").value = rec.quarantineUnits;
        document.getElementById("modalAvailableUnits").value = rec.availableUnits;
        document.getElementById("modalLotNotes").value = rec.notes || "";

        document.getElementById("invModal").classList.add("show");
        lucide.createIcons();
    }

    function recalcModalUnits() {
        const total = parseFloat(document.getElementById("modalTotalUnits").value) || 0;
        const trans = parseFloat(document.getElementById("modalTransferredUnits").value) || 0;
        const quar = parseFloat(document.getElementById("modalQuarantineUnits").value) || 0;
        const avail = Math.max(0, total - trans - quar);

        document.getElementById("modalAvailableUnits").value = avail;
    }

    function closeInvModal() {
        document.getElementById("invModal").classList.remove("show");
    }

    function saveInvLotForm(event) {
        event.preventDefault();

        const prodCode = document.getElementById("modalProductSelect").value;
        let desc = "FFC Flexible Ribbon Cable 1.0mm Pitch";
        if (prodCode === "SH-05-HARN") desc = "Shielded Harness Wire Assembly 0.5mm";
        else if (prodCode === "JMP-05-LEAD") desc = "Fine Pitch Jumper Lead Assembly";
        else if (prodCode === "UFC-125-FLAT") desc = "Ultra-Flex Flat Cable 1.25mm Pitch";

        const total = parseFloat(document.getElementById("modalTotalUnits").value) || 0;
        const trans = parseFloat(document.getElementById("modalTransferredUnits").value) || 0;
        const quar = parseFloat(document.getElementById("modalQuarantineUnits").value) || 0;
        const avail = Math.max(0, total - trans - quar);

        const payload = {
            lotNo: document.getElementById("modalLotNo").value,
            outputRef: document.getElementById("modalOutputRef").value,
            workOrder: document.getElementById("modalWorkOrder").value,
            prodCode: prodCode,
            description: desc,
            stagingBay: document.getElementById("modalStagingBay").value,
            date: document.getElementById("modalProdDate").value,
            totalUnits: total,
            transferredUnits: trans,
            quarantineUnits: quar,
            availableUnits: avail,
            status: document.getElementById("modalLotStatus").value,
            notes: document.getElementById("modalLotNotes").value
        };

        if (currentEditingId) {
            const idx = stagingLots.findIndex(l => l.id === currentEditingId);
            if (idx !== -1) {
                stagingLots[idx] = { ...stagingLots[idx], ...payload };
            }
        } else {
            payload.id = stagingLots.length > 0 ? Math.max(...stagingLots.map(l => l.id)) + 1 : 1;
            payload.deletedDate = "-";
            stagingLots.unshift(payload);
        }

        closeInvModal();
        renderTable();
    }


    function viewLotDossier(id) {
        const rec = stagingLots.find(l => l.id === id);
        if (!rec) return;

        document.getElementById("viewModalTitle").innerText = `Staging Lot Dossier — ${rec.lotNo}`;
        const body = document.getElementById("viewModalBody");

        body.innerHTML = `
            <div class="form-section-card" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">${rec.lotNo}</h2>
                        <span style="font-size: 12px; color: #64748b;">Output Ref: <strong>${rec.outputRef}</strong> | Work Order: <strong>${rec.workOrder}</strong></span>
                    </div>
                    <span class="status-pill status-pill-completed" style="font-size: 12px; padding: 4px 10px;">${rec.status}</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; font-size: 12px;">
                    <div><span style="color: #64748b;">Product Code:</span><br><strong class="font-code" style="color: #2563eb;">${rec.prodCode}</strong></div>
                    <div><span style="color: #64748b;">Description:</span><br><strong>${rec.description}</strong></div>
                    <div><span style="color: #64748b;">Staging Bay:</span><br><strong>${rec.stagingBay}</strong></div>
                    <div><span style="color: #64748b;">Production Date:</span><br><strong class="font-numeric">${rec.date}</strong></div>
                </div>
            </div>

            <div class="form-section-card">
                <div class="section-badge-title">
                    <span class="badge-num"><i data-lucide="layers" style="width: 12px; height: 12px;"></i></span>
                    <span>Inventory Balance Breakdown</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px;">
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; background: #ffffff;">
                        <div style="font-size: 10.5px; font-weight: 600; color: #64748b; text-transform: uppercase;">Total Produced</div>
                        <div class="font-numeric font-bold" style="font-size: 18px; color: #0f172a; margin-top: 4px;">${rec.totalUnits.toLocaleString()}</div>
                    </div>
                    <div style="border: 1px solid #a7f3d0; border-radius: 6px; padding: 10px; background: #ecfdf5;">
                        <div style="font-size: 10.5px; font-weight: 600; color: #059669; text-transform: uppercase;">Available (Buffer)</div>
                        <div class="font-numeric font-bold" style="font-size: 18px; color: #059669; margin-top: 4px;">${rec.availableUnits.toLocaleString()}</div>
                    </div>
                    <div style="border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px; background: #eff6ff;">
                        <div style="font-size: 10.5px; font-weight: 600; color: #1e40af; text-transform: uppercase;">Transferred (FGTS)</div>
                        <div class="font-numeric font-bold" style="font-size: 18px; color: #2563eb; margin-top: 4px;">${rec.transferredUnits.toLocaleString()}</div>
                    </div>
                    <div style="border: 1px solid #fde68a; border-radius: 6px; padding: 10px; background: #fffbeb;">
                        <div style="font-size: 10.5px; font-weight: 600; color: #d97706; text-transform: uppercase;">Quarantine</div>
                        <div class="font-numeric font-bold" style="font-size: 18px; color: #d97706; margin-top: 4px;">${rec.quarantineUnits.toLocaleString()}</div>
                    </div>
                </div>

                ${rec.notes ? `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 10px; font-size: 12px; margin-top: 10px;">
                        <strong style="color: #334155;">Lot Precautions & Handling:</strong>
                        <p style="color: #64748b; margin-top: 4px;">${rec.notes}</p>
                    </div>
                ` : ''}
            </div>
        `;

        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }


    function promptDeleteLot(id) {
        pendingDeleteId = id;
        const rec = stagingLots.find(l => l.id === id);
        if (!rec) return;

        document.getElementById("deleteLotNo").innerText = rec.lotNo;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
        pendingDeleteId = null;
    }

    function confirmDeleteInvLot() {
        if (!pendingDeleteId) return;

        const idx = stagingLots.findIndex(l => l.id === pendingDeleteId);
        if (idx !== -1) {
            stagingLots[idx].deletedDate = new Date().toISOString().split("T")[0];
            stagingLots[idx].status = "Quarantine";
        }

        closeDeleteModal();
        renderTable();
    }

    function printLotCard(id) {
        viewLotDossier(id);
        setTimeout(() => window.print(), 300);
    }
</script>
@endsection
