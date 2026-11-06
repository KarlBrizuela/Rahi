@extends('layouts.app')

@section('title', 'Finished Goods Transfer Slip (FGTS) — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Production/FGTS/fgts.css') }}">
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
            <span class="bc-current">Finished Goods Transfer Slip</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search FGTS#, Output Ref, Source, Destination, Custodian..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Finished Goods Transfer Slip (FGTS)</h1>
        </div>

        <div class="action-cluster">
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh FGTS Manifests">
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
                            <strong>Print Transfer Slips</strong>
                            <small>Official FGTS warehouse handover slips</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated manifest records</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" id="btnCreateFGTS">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create FG Transfer Slip</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="Transferred">Transferred</option>
                <option value="In Transit">In Transit</option>
                <option value="Pending Receipt">Pending Receipt</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Branch:</span>
            <select id="filterBranch" class="filter-select">
                <option value="">All Branches</option>
                <option value="Laguna Plant Depot">Laguna Plant Depot</option>
                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                <option value="Cebu Logistics Hub">Cebu Logistics Hub</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Transfer Date:</span>
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
            <span class="kpi-label">Total Transfer Slips</span>
            <span class="kpi-value" id="kpiTotalSlips">4</span>
            <span class="kpi-sub">Handover manifests</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Handed Over Units</span>
            <span class="kpi-value" id="kpiTransferredUnits" style="color: #2563eb;">54,000</span>
            <span class="kpi-sub">Aggregated product volume</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Warehouse Received</span>
            <span class="kpi-value" id="kpiReceivedUnits" style="color: #10b981;">44,000</span>
            <span class="kpi-sub">Stocked in FG warehouse</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">In Transit / Pending</span>
            <span class="kpi-value" id="kpiTransitUnits" style="color: #f59e0b;">10,000</span>
            <span class="kpi-sub">Awaiting bay receiving check</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Finished Goods Transfer Ledger</span>
                    <span class="count-pill" id="tableBadgeCount">4 Records</span>
                    <span class="selection-pill" id="bulkActionGroup">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedRowCount">0 selected</span>
                        <button type="button" class="btn-bulk-delete" onclick="bulkReceiveFGTS()">Confirm Receipt Selected</button>
                    </span>
                </div>
            </div>

            <div class="table-scroll-container">
                <table class="erp-table" id="fgtsTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb">
                            </th>
                            <th style="min-width: 140px;">FGTS No</th>
                            <th style="min-width: 110px;">Transfer Date</th>
                            <th style="min-width: 140px;">Branch</th>
                            <th style="min-width: 180px;">Source Location</th>
                            <th style="min-width: 180px;">Destination Warehouse</th>
                            <th style="min-width: 160px;">Output / WO Ref</th>
                            <th style="min-width: 150px;">Transferred By</th>
                            <th style="min-width: 150px;">Received By</th>
                            <th style="min-width: 90px;" class="text-right">Cartons</th>
                            <th style="min-width: 110px;" class="text-right">Transferred Qty</th>
                            <th style="min-width: 120px;" class="text-center">Status</th>
                            <th style="min-width: 100px;" class="text-center">Deleted Date</th>
                            <th style="width: 60px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="fgtsTableBody">

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

<div class="modal-backdrop" id="fgtsModal">
    <div class="modal-dialog modal-dialog-xlarge">
        <form id="fgtsForm" onsubmit="saveFGTSForm(event)">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h3 class="modal-title" id="fgtsModalTitle">Create Finished Goods Transfer Slip</h3>
                    <span class="modal-subtitle">Official inventory handover from production staging to FG warehouse</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeFGTSModal()"><i data-lucide="x"></i></button>
            </div>

            <div class="modal-body">
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">1</span>
                        <span>Transfer Slip Identifiers & Routing</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group">
                            <label class="form-label">Branch <span class="req-star">*</span></label>
                            <select id="modalBranch" class="form-control" required>
                                <option value="Laguna Plant Depot">Laguna Plant Depot</option>
                                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                                <option value="Cebu Logistics Hub">Cebu Logistics Hub</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">FGTS Number <span class="req-star">*</span></label>
                            <input type="text" id="modalFGTSNo" class="form-control" placeholder="FGTS-2026-XXXX" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Transfer Date <span class="req-star">*</span></label>
                            <input type="date" id="modalTransferDate" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status <span class="req-star">*</span></label>
                            <select id="modalStatus" class="form-control" required>
                                <option value="Pending Receipt">Pending Receipt</option>
                                <option value="In Transit">In Transit</option>
                                <option value="Transferred">Transferred</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-4" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Source Location (Line Staging) <span class="req-star">*</span></label>
                            <select id="modalSourceLoc" class="form-control" required>
                                <option value="Bay 01 - SMT Line 1 Buffer">Bay 01 - SMT Line 1 Buffer</option>
                                <option value="Bay 02 - SMT Line 2 Buffer">Bay 02 - SMT Line 2 Buffer</option>
                                <option value="Bay 03 - Extrusion Staging">Bay 03 - Extrusion Staging</option>
                                <option value="Bay 04 - Post-Cure Holding">Bay 04 - Post-Cure Holding</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Destination Warehouse <span class="req-star">*</span></label>
                            <select id="modalDestWarehouse" class="form-control" required>
                                <option value="FG Central Bay 1">FG Central Bay 1 (Main Rack)</option>
                                <option value="FG Central Bay 2">FG Central Bay 2 (Export Staging)</option>
                                <option value="FG High-Density Bay 4">FG High-Density Bay 4</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Output Run Ref <span class="req-star">*</span></label>
                            <input type="text" id="modalOutputRef" class="form-control" placeholder="OUT-2026-0001" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Work Order Ref <span class="req-star">*</span></label>
                            <input type="text" id="modalWorkOrder" class="form-control" placeholder="WO-2026-0814" required>
                        </div>
                    </div>

                    <div class="form-grid-3" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Transferred By (Production Lead) <span class="req-star">*</span></label>
                            <input type="text" id="modalTransferredBy" class="form-control" placeholder="Production Lead" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Received By (Warehouse Custodian) <span class="req-star">*</span></label>
                            <input type="text" id="modalReceivedBy" class="form-control" placeholder="FG Custodian name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">QC Release Tag / Pass No</label>
                            <input type="text" id="modalQcTag" class="form-control" placeholder="e.g. QC-REL-8891">
                        </div>
                    </div>

                    <div class="form-group" style="margin-top: 10px;">
                        <label class="form-label">Transfer Notes / Transport Mode</label>
                        <input type="text" id="modalRemarks" class="form-control" placeholder="Internal forklift transit, sealed pallet IDs, handling requirements...">
                    </div>
                </div>

                <div class="form-section-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div class="section-badge-title" style="margin-bottom: 0;">
                            <span class="badge-num">2</span>
                            <span>Transfer Manifest Line Items</span>
                        </div>
                        <button type="button" class="btn-add-item-row" onclick="addModalFGTSLine()" style="margin-top: 0;">
                            <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                            <span>Add Product Line</span>
                        </button>
                    </div>

                    <div class="modal-table-wrapper">
                        <table class="modal-grid-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Product Code</th>
                                    <th>Description</th>
                                    <th style="width: 65px;">UOM</th>
                                    <th style="width: 120px;">Lot Serial</th>
                                    <th style="width: 100px; text-align: right;">Cartons</th>
                                    <th style="width: 110px; text-align: right;">Transfer Qty</th>
                                    <th style="width: 120px;">Target FG Bin</th>
                                    <th style="width: 105px;">QC Status</th>
                                    <th style="width: 35px;"></th>
                                </tr>
                            </thead>
                            <tbody id="modalFGTSLineBody">

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeFGTSModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSaveFGTS">Save & Issue FGTS</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewModalTitle">FGTS Transfer Manifest</h3>
                <span class="modal-subtitle">Official inventory receipt and bin stocking verification</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="viewModalBody">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Transfer Slip</span>
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
                Are you sure you want to mark transfer slip <strong id="deleteFGTSNo"></strong> as deleted?
            </p>
            <p style="font-size: 11.5px; color: #94a3b8;">
                This reverses the warehouse handover transaction and logs the cancellation timestamp.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-primary" style="background: #dc2626; border-color: #dc2626;" onclick="confirmDeleteFGTS()">Delete Record</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>


    let fgtsRecords = [
        {
            id: 1,
            fgtsNo: "FGTS-2026-0091",
            date: "2026-11-04",
            branch: "Laguna Plant Depot",
            sourceLoc: "Bay 01 - SMT Line 1 Buffer",
            destWarehouse: "FG Central Bay 1",
            outputRef: "OUT-2026-0001",
            workOrder: "WO-2026-0814",
            transferredBy: "Karl Burgos (Production)",
            receivedBy: "Marvin Del Rosario (Warehouse)",
            cartons: 40,
            transferredQty: 20000,
            status: "Transferred",
            deletedDate: "-",
            qcTag: "QC-PASS-261104-01",
            remarks: "Standard transfer. All reels sealed with anti-static shielding.",
            lines: [
                {
                    prodCode: "FFC-05-100",
                    description: "FFC Flexible Ribbon Cable 1.0mm Pitch",
                    uom: "PCS",
                    lotSerial: "LOT-261104-A1",
                    cartons: 40,
                    qty: 20000,
                    binLoc: "RACK-A-04-1",
                    qcStatus: "Verified"
                }
            ]
        },
        {
            id: 2,
            fgtsNo: "FGTS-2026-0092",
            date: "2026-11-05",
            branch: "Laguna Plant Depot",
            sourceLoc: "Bay 02 - SMT Line 2 Buffer",
            destWarehouse: "FG Central Bay 2",
            outputRef: "OUT-2026-0002",
            workOrder: "WO-2026-0819",
            transferredBy: "Alvin Mercado",
            receivedBy: "Marvin Del Rosario (Warehouse)",
            cartons: 48,
            transferredQty: 24000,
            status: "Transferred",
            deletedDate: "-",
            qcTag: "QC-PASS-261105-09",
            remarks: "Pallet #2 passed visual packaging inspection without defect.",
            lines: [
                {
                    prodCode: "SH-05-HARN",
                    description: "Shielded Harness Wire Assembly 0.5mm",
                    uom: "PCS",
                    lotSerial: "LOT-261105-B2",
                    cartons: 48,
                    qty: 24000,
                    binLoc: "RACK-B-02-3",
                    qcStatus: "Verified"
                }
            ]
        },
        {
            id: 3,
            fgtsNo: "FGTS-2026-0093",
            date: "2026-11-06",
            branch: "Laguna Plant Depot",
            sourceLoc: "Bay 03 - Extrusion Staging",
            destWarehouse: "FG High-Density Bay 4",
            outputRef: "OUT-2026-0003",
            workOrder: "WO-2026-0824",
            transferredBy: "Engr. Danilo Ramos",
            receivedBy: "Pending Custodian Signature",
            cartons: 20,
            transferredQty: 10000,
            status: "In Transit",
            deletedDate: "-",
            qcTag: "QC-PASS-261106-03",
            remarks: "Forklift transit initiated at 11:20 AM.",
            lines: [
                {
                    prodCode: "JMP-05-LEAD",
                    description: "Fine Pitch Jumper Lead 28AWG",
                    uom: "PCS",
                    lotSerial: "LOT-261106-E1",
                    cartons: 20,
                    qty: 10000,
                    binLoc: "RACK-D-01-2",
                    qcStatus: "Inspected"
                }
            ]
        },
        {
            id: 4,
            fgtsNo: "FGTS-2026-0094",
            date: "2026-11-03",
            branch: "Head Office (Mandaluyong)",
            sourceLoc: "Bay 04 - Post-Cure Holding",
            destWarehouse: "FG Central Bay 1",
            outputRef: "OUT-2026-0004",
            workOrder: "WO-2026-0801",
            transferredBy: "Nestor Bautista",
            receivedBy: "-",
            cartons: 0,
            transferredQty: 0,
            status: "Cancelled",
            deletedDate: "-",
            qcTag: "QC-REJ-8810",
            remarks: "Cancelled prior to physical dispatch due to QC hold.",
            lines: [
                {
                    prodCode: "UFC-125-FLAT",
                    description: "Ultra-Flex Flat Cable 1.25mm",
                    uom: "PCS",
                    lotSerial: "LOT-261103-C4",
                    cartons: 0,
                    qty: 0,
                    binLoc: "N/A",
                    qcStatus: "Rejected"
                }
            ]
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

        document.getElementById("btnCreateFGTS").addEventListener("click", openCreateModal);
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
        const filterBranch = document.getElementById("filterBranch");
        const filterDateFrom = document.getElementById("filterDateFrom");
        const filterDateTo = document.getElementById("filterDateTo");
        const btnReset = document.getElementById("btnResetFilters");

        const handler = () => renderTable();

        searchInput.addEventListener("input", handler);
        filterStatus.addEventListener("change", handler);
        filterBranch.addEventListener("change", handler);
        filterDateFrom.addEventListener("change", handler);
        filterDateTo.addEventListener("change", handler);

        btnReset.addEventListener("click", () => {
            searchInput.value = "";
            filterStatus.value = "";
            filterBranch.value = "";
            filterDateFrom.value = "";
            filterDateTo.value = "";
            renderTable();
        });
    }


    function renderTable() {
        const tbody = document.getElementById("fgtsTableBody");
        const search = (document.getElementById("globalSearchInput").value || "").toLowerCase().trim();
        const status = document.getElementById("filterStatus").value;
        const branch = document.getElementById("filterBranch").value;
        const dFrom = document.getElementById("filterDateFrom").value;
        const dTo = document.getElementById("filterDateTo").value;

        const filtered = fgtsRecords.filter(r => {
            if (status && r.status !== status) return false;
            if (branch && r.branch !== branch) return false;
            if (dFrom && r.date < dFrom) return false;
            if (dTo && r.date > dTo) return false;

            if (search) {
                const hay = `${r.fgtsNo} ${r.sourceLoc} ${r.destWarehouse} ${r.outputRef} ${r.workOrder} ${r.transferredBy} ${r.receivedBy}`.toLowerCase();
                if (!hay.includes(search)) return false;
            }
            return true;
        });

        tbody.innerHTML = "";

        if (filtered.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="14" style="text-align: center; padding: 36px; color: #94a3b8;">
                        <i data-lucide="inbox" style="margin-bottom: 6px; width: 28px; height: 28px;"></i>
                        <div>No matching FGTS transfer slips found</div>
                    </td>
                </tr>
            `;
            lucide.createIcons();
            updateKPIs(filtered);
            return;
        }

        filtered.forEach(r => {
            const tr = document.createElement("tr");

            let statusClass = "status-pill-completed";
            if (r.status === "In Transit") statusClass = "status-pill-progress";
            else if (r.status === "Pending Receipt") statusClass = "status-pill-hold";
            else if (r.status === "Cancelled") statusClass = "status-pill-cancelled";

            tr.innerHTML = `
                <td style="text-align: center;">
                    <input type="checkbox" class="row-select-check erp-cb" value="${r.id}" onchange="updateBulkSelection()">
                </td>
                <td>
                    <a href="javascript:void(0)" class="font-code font-bold" style="color: #2563eb; text-decoration: none;" onclick="viewFGTSDossier(${r.id})">${r.fgtsNo}</a>
                </td>
                <td class="font-numeric">${r.date}</td>
                <td>${r.branch}</td>
                <td>${r.sourceLoc}</td>
                <td><strong style="color: #334155;">${r.destWarehouse}</strong></td>
                <td><span class="font-code font-bold" style="color: #2563eb;">${r.outputRef}</span> <small style="color: #94a3b8;">(${r.workOrder})</small></td>
                <td>${r.transferredBy}</td>
                <td>${r.receivedBy}</td>
                <td class="font-numeric text-right">${r.cartons}</td>
                <td class="font-numeric text-right font-bold" style="color: #059669;">${r.transferredQty.toLocaleString()}</td>
                <td class="text-center"><span class="status-pill ${statusClass}">${r.status}</span></td>
                <td class="text-center font-numeric" style="color: #94a3b8; font-size: 11px;">${r.deletedDate}</td>
                <td class="actions-cell">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, ${r.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${r.id}">
                        <button type="button" class="action-menu-item" onclick="viewFGTSDossier(${r.id})">
                            <i data-lucide="eye"></i> View Manifest
                        </button>
                        <button type="button" class="action-menu-item" onclick="editFGTSRecord(${r.id})">
                            <i data-lucide="edit"></i> Edit Slip
                        </button>
                        <button type="button" class="action-menu-item" onclick="printFGTSSlip(${r.id})">
                            <i data-lucide="printer"></i> Print Slip
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteFGTS(${r.id})">
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
        document.getElementById("kpiTotalSlips").innerText = data.length;
        const totalQty = data.reduce((acc, r) => acc + (r.transferredQty || 0), 0);
        const received = data.filter(r => r.status === "Transferred").reduce((acc, r) => acc + (r.transferredQty || 0), 0);
        const transit = data.filter(r => r.status === "In Transit" || r.status === "Pending Receipt").reduce((acc, r) => acc + (r.transferredQty || 0), 0);

        document.getElementById("kpiTransferredUnits").innerText = totalQty.toLocaleString();
        document.getElementById("kpiReceivedUnits").innerText = received.toLocaleString();
        document.getElementById("kpiTransitUnits").innerText = transit.toLocaleString();
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

    function bulkReceiveFGTS() {
        const checked = document.querySelectorAll(".row-select-check:checked");
        checked.forEach(c => {
            const rec = fgtsRecords.find(r => r.id === parseInt(c.value));
            if (rec) {
                rec.status = "Transferred";
                rec.receivedBy = "Marvin Del Rosario (Warehouse)";
            }
        });
        renderTable();
        updateBulkSelection();
    }


    function openCreateModal() {
        currentEditingId = null;
        document.getElementById("fgtsModalTitle").innerText = "Create Finished Goods Transfer Slip";
        document.getElementById("fgtsForm").reset();

        const now = new Date();
        document.getElementById("modalTransferDate").value = now.toISOString().split("T")[0];
        document.getElementById("modalFGTSNo").value = `FGTS-2026-${String(fgtsRecords.length + 91).padStart(4, "0")}`;
        document.getElementById("modalOutputRef").value = "OUT-2026-0003";
        document.getElementById("modalWorkOrder").value = "WO-2026-0824";
        document.getElementById("modalTransferredBy").value = "Karl Burgos (Production)";
        document.getElementById("modalReceivedBy").value = "Marvin Del Rosario (Warehouse)";
        document.getElementById("modalQcTag").value = "QC-REL-" + Math.floor(1000 + Math.random() * 9000);

        const lineBody = document.getElementById("modalFGTSLineBody");
        lineBody.innerHTML = "";
        addModalFGTSLine();

        document.getElementById("fgtsModal").classList.add("show");
        lucide.createIcons();
    }

    function editFGTSRecord(id) {
        currentEditingId = id;
        const rec = fgtsRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("fgtsModalTitle").innerText = `Edit FGTS Slip — ${rec.fgtsNo}`;
        document.getElementById("modalBranch").value = rec.branch;
        document.getElementById("modalFGTSNo").value = rec.fgtsNo;
        document.getElementById("modalTransferDate").value = rec.date;
        document.getElementById("modalStatus").value = rec.status;
        document.getElementById("modalSourceLoc").value = rec.sourceLoc;
        document.getElementById("modalDestWarehouse").value = rec.destWarehouse;
        document.getElementById("modalOutputRef").value = rec.outputRef;
        document.getElementById("modalWorkOrder").value = rec.workOrder;
        document.getElementById("modalTransferredBy").value = rec.transferredBy;
        document.getElementById("modalReceivedBy").value = rec.receivedBy;
        document.getElementById("modalQcTag").value = rec.qcTag || "";
        document.getElementById("modalRemarks").value = rec.remarks || "";

        const lineBody = document.getElementById("modalFGTSLineBody");
        lineBody.innerHTML = "";
        if (rec.lines && rec.lines.length > 0) {
            rec.lines.forEach(l => addModalFGTSLine(l));
        } else {
            addModalFGTSLine({
                prodCode: "FFC-05-100",
                description: "FFC Cable Assembly",
                uom: "PCS",
                lotSerial: "LOT-FGTS-" + rec.fgtsNo,
                cartons: rec.cartons,
                qty: rec.transferredQty,
                binLoc: "RACK-A-01",
                qcStatus: "Verified"
            });
        }

        document.getElementById("fgtsModal").classList.add("show");
        lucide.createIcons();
    }

    function addModalFGTSLine(data = null) {
        const lineBody = document.getElementById("modalFGTSLineBody");
        const tr = document.createElement("tr");

        const pCode = data ? data.prodCode : "FFC-05-100";
        const desc = data ? data.description : "FFC Flexible Ribbon Cable 1.0mm Pitch";
        const uom = data ? data.uom : "PCS";
        const lot = data ? data.lotSerial : "LOT-261104-A1";
        const cartons = data ? data.cartons : 20;
        const qty = data ? data.qty : 10000;
        const bin = data ? data.binLoc : "RACK-A-04-1";
        const qc = data ? data.qcStatus : "Verified";

        tr.innerHTML = `
            <td>
                <select class="fgts-prod-select" onchange="handleFGTSProdChange(this)">
                    <option value="FFC-05-100" ${pCode === 'FFC-05-100' ? 'selected' : ''}>FFC-05-100</option>
                    <option value="SH-05-HARN" ${pCode === 'SH-05-HARN' ? 'selected' : ''}>SH-05-HARN</option>
                    <option value="JMP-05-LEAD" ${pCode === 'JMP-05-LEAD' ? 'selected' : ''}>JMP-05-LEAD</option>
                    <option value="UFC-125-FLAT" ${pCode === 'UFC-125-FLAT' ? 'selected' : ''}>UFC-125-FLAT</option>
                </select>
            </td>
            <td><input type="text" class="fgts-desc" value="${desc}"></td>
            <td><input type="text" class="fgts-uom" value="${uom}" style="width: 55px;"></td>
            <td><input type="text" class="fgts-lot font-code" value="${lot}"></td>
            <td><input type="number" class="fgts-cartons font-numeric" value="${cartons}" style="text-align: right;"></td>
            <td><input type="number" class="fgts-qty font-numeric font-bold" value="${qty}" style="text-align: right; color: #059669;"></td>
            <td><input type="text" class="fgts-bin font-code font-bold" value="${bin}"></td>
            <td>
                <select class="fgts-qc">
                    <option value="Verified" ${qc === 'Verified' ? 'selected' : ''}>Verified</option>
                    <option value="Inspected" ${qc === 'Inspected' ? 'selected' : ''}>Inspected</option>
                    <option value="Pending" ${qc === 'Pending' ? 'selected' : ''}>Pending</option>
                </select>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-line" onclick="removeModalFGTSLine(this)">
                    <i data-lucide="trash-2"></i>
                </button>
            </td>
        `;
        lineBody.appendChild(tr);
        lucide.createIcons();
    }

    function handleFGTSProdChange(sel) {
        const tr = sel.closest("tr");
        const val = sel.value;
        const descInput = tr.querySelector(".fgts-desc");
        if (val === "FFC-05-100") descInput.value = "FFC Flexible Ribbon Cable 1.0mm Pitch";
        else if (val === "SH-05-HARN") descInput.value = "Shielded Harness Wire Assembly 0.5mm";
        else if (val === "JMP-05-LEAD") descInput.value = "Fine Pitch Jumper Lead Assembly";
        else if (val === "UFC-125-FLAT") descInput.value = "Ultra-Flex Flat Cable 1.25mm Pitch";
    }

    function removeModalFGTSLine(btn) {
        const tbody = document.getElementById("modalFGTSLineBody");
        if (tbody.querySelectorAll("tr").length <= 1) {
            alert("An FGTS slip must contain at least one line item.");
            return;
        }
        btn.closest("tr").remove();
    }

    function closeFGTSModal() {
        document.getElementById("fgtsModal").classList.remove("show");
    }

    function saveFGTSForm(event) {
        event.preventDefault();

        const rows = document.querySelectorAll("#modalFGTSLineBody tr");
        let sumCartons = 0;
        let sumQty = 0;
        const linesData = [];

        rows.forEach(tr => {
            const pCode = tr.querySelector(".fgts-prod-select").value;
            const desc = tr.querySelector(".fgts-desc").value;
            const uom = tr.querySelector(".fgts-uom").value;
            const lot = tr.querySelector(".fgts-lot").value;
            const cartons = parseFloat(tr.querySelector(".fgts-cartons").value) || 0;
            const qty = parseFloat(tr.querySelector(".fgts-qty").value) || 0;
            const bin = tr.querySelector(".fgts-bin").value;
            const qc = tr.querySelector(".fgts-qc").value;

            sumCartons += cartons;
            sumQty += qty;

            linesData.push({
                prodCode: pCode,
                description: desc,
                uom: uom,
                lotSerial: lot,
                cartons: cartons,
                qty: qty,
                binLoc: bin,
                qcStatus: qc
            });
        });

        const payload = {
            fgtsNo: document.getElementById("modalFGTSNo").value,
            date: document.getElementById("modalTransferDate").value,
            branch: document.getElementById("modalBranch").value,
            status: document.getElementById("modalStatus").value,
            sourceLoc: document.getElementById("modalSourceLoc").value,
            destWarehouse: document.getElementById("modalDestWarehouse").value,
            outputRef: document.getElementById("modalOutputRef").value,
            workOrder: document.getElementById("modalWorkOrder").value,
            transferredBy: document.getElementById("modalTransferredBy").value,
            receivedBy: document.getElementById("modalReceivedBy").value,
            qcTag: document.getElementById("modalQcTag").value,
            remarks: document.getElementById("modalRemarks").value,
            cartons: sumCartons,
            transferredQty: sumQty,
            lines: linesData
        };

        if (currentEditingId) {
            const idx = fgtsRecords.findIndex(r => r.id === currentEditingId);
            if (idx !== -1) {
                fgtsRecords[idx] = { ...fgtsRecords[idx], ...payload };
            }
        } else {
            payload.id = fgtsRecords.length > 0 ? Math.max(...fgtsRecords.map(r => r.id)) + 1 : 1;
            payload.deletedDate = "-";
            fgtsRecords.unshift(payload);
        }

        closeFGTSModal();
        renderTable();
    }


    function viewFGTSDossier(id) {
        const rec = fgtsRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("viewModalTitle").innerText = `FGTS Manifest — ${rec.fgtsNo}`;
        const body = document.getElementById("viewModalBody");

        let linesHtml = "";
        (rec.lines || []).forEach(l => {
            linesHtml += `
                <tr>
                    <td><strong class="font-code">${l.prodCode}</strong></td>
                    <td>${l.description}</td>
                    <td>${l.uom}</td>
                    <td class="font-code">${l.lotSerial}</td>
                    <td class="font-numeric text-right">${l.cartons}</td>
                    <td class="font-numeric text-right font-bold" style="color: #059669;">${l.qty.toLocaleString()}</td>
                    <td><strong class="font-code">${l.binLoc}</strong></td>
                    <td><span class="status-pill status-pill-completed">${l.qcStatus}</span></td>
                </tr>
            `;
        });

        body.innerHTML = `
            <div class="form-section-card" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">${rec.fgtsNo}</h2>
                        <span style="font-size: 12px; color: #64748b;">Output Ref: <strong>${rec.outputRef}</strong> | WO Ref: <strong>${rec.workOrder}</strong></span>
                    </div>
                    <span class="status-pill status-pill-completed" style="font-size: 12px; padding: 4px 10px;">${rec.status}</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; font-size: 12px;">
                    <div><span style="color: #64748b;">Branch:</span><br><strong>${rec.branch}</strong></div>
                    <div><span style="color: #64748b;">Transfer Date:</span><br><strong class="font-numeric">${rec.date}</strong></div>
                    <div><span style="color: #64748b;">From (Source):</span><br><strong>${rec.sourceLoc}</strong></div>
                    <div><span style="color: #64748b;">To (Destination):</span><br><strong>${rec.destWarehouse}</strong></div>
                    <div><span style="color: #64748b;">Transferred By:</span><br><strong>${rec.transferredBy}</strong></div>
                    <div><span style="color: #64748b;">Received By:</span><br><strong>${rec.receivedBy}</strong></div>
                    <div><span style="color: #64748b;">Total Packages:</span><br><strong class="font-numeric">${rec.cartons} Cartons / Reels</strong></div>
                    <div><span style="color: #64748b;">QC Release Pass:</span><br><strong style="color: #2563eb;">${rec.qcTag}</strong></div>
                </div>
                ${rec.remarks ? `<div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #475569;"><strong>Notes:</strong> ${rec.remarks}</div>` : ''}
            </div>

            <div class="form-section-card">
                <div class="section-badge-title">
                    <span class="badge-num"><i data-lucide="package" style="width: 12px; height: 12px;"></i></span>
                    <span>Transferred Product Units & FG Bin Allocation</span>
                </div>

                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>UOM</th>
                                <th>Lot Serial</th>
                                <th class="text-right">Cartons</th>
                                <th class="text-right">Quantity</th>
                                <th>FG Bin</th>
                                <th>QC Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${linesHtml}
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


    function promptDeleteFGTS(id) {
        pendingDeleteId = id;
        const rec = fgtsRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("deleteFGTSNo").innerText = rec.fgtsNo;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
        pendingDeleteId = null;
    }

    function confirmDeleteFGTS() {
        if (!pendingDeleteId) return;

        const idx = fgtsRecords.findIndex(r => r.id === pendingDeleteId);
        if (idx !== -1) {
            fgtsRecords[idx].deletedDate = new Date().toISOString().split("T")[0];
            fgtsRecords[idx].status = "Cancelled";
        }

        closeDeleteModal();
        renderTable();
    }

    function printFGTSSlip(id) {
        viewFGTSDossier(id);
        setTimeout(() => window.print(), 300);
    }
</script>
@endsection
