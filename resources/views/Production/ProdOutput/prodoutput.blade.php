@extends('layouts.app')

@section('title', 'Production Output — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Production/ProdOutput/prodoutput.css') }}">
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
            <span class="bc-current">Production Output</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Output#, Work Order, Line, Shift, Operator..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Production Output Register</h1>
        </div>

        <div class="action-cluster">
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Output Records">
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
                            <strong>Print Run Ledger</strong>
                            <small>Printable output run ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated output run data</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" id="btnCreateOutput">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Log Production Output</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="">All Statuses</option>
                <option value="Posted">Posted</option>
                <option value="In Process">In Process</option>
                <option value="Draft">Draft</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Shift:</span>
            <select id="filterShift" class="filter-select">
                <option value="">All Shifts</option>
                <option value="Shift A (Day)">Shift A (Day)</option>
                <option value="Shift B (Night)">Shift B (Night)</option>
                <option value="Shift C (Graveyard)">Shift C (Graveyard)</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Line/Machine:</span>
            <select id="filterLine" class="filter-select">
                <option value="">All Lines</option>
                <option value="SMT Line 01">SMT Line 01</option>
                <option value="SMT Line 02">SMT Line 02</option>
                <option value="Extruder Line A">Extruder Line A</option>
                <option value="Assembly Cell 4">Assembly Cell 4</option>
            </select>
        </div>

        <div class="filter-item">
            <span class="filter-label">Output Date:</span>
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
            <span class="kpi-label">Total Output Runs</span>
            <span class="kpi-value" id="kpiTotalRuns">4</span>
            <span class="kpi-sub">Shift logging entries</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Good Output Units</span>
            <span class="kpi-value" id="kpiGoodUnits" style="color: #10b981;">94,850</span>
            <span class="kpi-sub">Validated passed units</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Average Line Yield</span>
            <span class="kpi-value" id="kpiAvgYield" style="color: #2563eb;">98.4%</span>
            <span class="kpi-sub">Target efficiency threshold: 97.0%</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Scrap & Defects</span>
            <span class="kpi-value" id="kpiScrapUnits" style="color: #f59e0b;">1,530</span>
            <span class="kpi-sub">Quarantine / recycle disposal</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Production Output Run Ledger</span>
                    <span class="count-pill" id="tableBadgeCount">4 Records</span>
                    <span class="selection-pill" id="bulkActionGroup">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedRowCount">0 selected</span>
                        <button type="button" class="btn-bulk-delete" onclick="bulkPostOutput()">Post Selected</button>
                    </span>
                </div>
            </div>

            <div class="table-scroll-container">
                <table class="erp-table" id="outputTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb">
                            </th>
                            <th style="min-width: 140px;">Output No</th>
                            <th style="min-width: 110px;">Output Date</th>
                            <th style="min-width: 130px;">Work Order Ref</th>
                            <th style="min-width: 140px;">Machine / Line</th>
                            <th style="min-width: 130px;">Shift</th>
                            <th style="min-width: 160px;">Supervisor</th>
                            <th style="min-width: 100px;" class="text-right">Target Qty</th>
                            <th style="min-width: 100px;" class="text-right">Good Qty</th>
                            <th style="min-width: 90px;" class="text-right">Scrap Qty</th>
                            <th style="min-width: 90px;" class="text-right">Rework Qty</th>
                            <th style="min-width: 90px;" class="text-right">Yield Rate</th>
                            <th style="min-width: 110px;" class="text-center">Status</th>
                            <th style="min-width: 100px;" class="text-center">Deleted Date</th>
                            <th style="width: 60px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="outputTableBody">
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

<div class="modal-backdrop" id="outputModal">
    <div class="modal-dialog modal-dialog-xlarge">
        <form id="outputForm" onsubmit="saveOutputForm(event)">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h3 class="modal-title" id="outputModalTitle">Log Production Output</h3>
                    <span class="modal-subtitle">Log completed units, shift parameters, line yields, and QA dispositions</span>
                </div>
                <button type="button" class="modal-close-btn" onclick="closeOutputModal()">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">1</span>
                        <span>Production Run Header</span>
                    </div>

                    <div class="form-grid-4">
                        <div class="form-group">
                            <label class="form-label">Branch <span class="req-star">*</span></label>
                            <select id="modalBranch" class="form-control" required>
                                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                                <option value="Laguna Plant Depot" selected>Laguna Plant Depot</option>
                                <option value="Cebu Logistics Hub">Cebu Logistics Hub</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Output Number <span class="req-star">*</span></label>
                            <input type="text" id="modalOutputNo" class="form-control" placeholder="OUT-2026-XXXX" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Output Date <span class="req-star">*</span></label>
                            <input type="date" id="modalOutputDate" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Shift <span class="req-star">*</span></label>
                            <select id="modalShift" class="form-control" required>
                                <option value="Shift A (Day)">Shift A (Day)</option>
                                <option value="Shift B (Night)">Shift B (Night)</option>
                                <option value="Shift C (Graveyard)">Shift C (Graveyard)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid-4" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Work Order / MRP Ref <span class="req-star">*</span></label>
                            <input type="text" id="modalWorkOrder" class="form-control" placeholder="e.g. WO-2026-0814" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Line / Machine <span class="req-star">*</span></label>
                            <select id="modalLine" class="form-control" required>
                                <option value="SMT Line 01">SMT Line 01</option>
                                <option value="SMT Line 02">SMT Line 02</option>
                                <option value="Extruder Line A">Extruder Line A</option>
                                <option value="Assembly Cell 4">Assembly Cell 4</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Supervisor <span class="req-star">*</span></label>
                            <input type="text" id="modalSupervisor" class="form-control" placeholder="Production Supervisor" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">QC Inspector <span class="req-star">*</span></label>
                            <input type="text" id="modalQcInspector" class="form-control" placeholder="Quality Lead name" required>
                        </div>
                    </div>

                    <div class="form-grid-3" style="margin-top: 10px;">
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select id="modalStatus" class="form-control">
                                <option value="Draft">Draft</option>
                                <option value="In Process">In Process</option>
                                <option value="Posted">Posted</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="form-group col-span-2">
                            <label class="form-label">Run Remarks / Stoppage Notes</label>
                            <input type="text" id="modalRemarks" class="form-control" placeholder="Optional operator remarks, downtime explanation, or raw material lot notes">
                        </div>
                    </div>
                </div>

                <div class="form-section-card">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div class="section-badge-title" style="margin-bottom: 0;">
                            <span class="badge-num">2</span>
                            <span>Output Metrics & Line Item Yield</span>
                        </div>
                        <button type="button" class="btn-add-item-row" onclick="addModalOutputLine()" style="margin-top: 0;">
                            <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                            <span>Add Item Line</span>
                        </button>
                    </div>

                    <div class="modal-table-wrapper">
                        <table class="modal-grid-table">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Product Code</th>
                                    <th>Description</th>
                                    <th style="width: 65px;">UOM</th>
                                    <th style="width: 95px; text-align: right;">Target Qty</th>
                                    <th style="width: 95px; text-align: right;">Good Qty</th>
                                    <th style="width: 85px; text-align: right;">Scrap Qty</th>
                                    <th style="width: 85px; text-align: right;">Rework</th>
                                    <th style="width: 85px; text-align: right;">Yield %</th>
                                    <th style="width: 120px;">Lot Serial</th>
                                    <th style="width: 105px;">QC Verdict</th>
                                    <th style="width: 35px;"></th>
                                </tr>
                            </thead>
                            <tbody id="modalOutputLineBody">
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeOutputModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSaveOutput">Save Production Run</button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewModalTitle">Production Output Dossier</h3>
                <span class="modal-subtitle">Official shift logging record and quality assurance disposition</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeViewModal()"><i data-lucide="x"></i></button>
        </div>
        <div class="modal-body" id="viewModalBody">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer" class="btn-icon"></i>
                <span>Print Dossier</span>
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
                Are you sure you want to mark production run <strong id="deleteOutputNo"></strong> as deleted?
            </p>
            <p style="font-size: 11.5px; color: #94a3b8;">
                This soft-deletes the output manifest and preserves the record with a deletion timestamp for audit traceability.
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeDeleteModal()">Cancel</button>
            <button type="button" class="btn-primary" style="background: #dc2626; border-color: #dc2626;" onclick="confirmDeleteOutput()">Delete Record</button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://unpkg.com/lucide@latest"></script>
<script>
    let outputRecords = [
        {
            id: 1,
            outputNo: "OUT-2026-0001",
            date: "2026-11-04",
            workOrder: "WO-2026-0814",
            line: "SMT Line 01",
            shift: "Shift A (Day)",
            supervisor: "Engr. Danilo Ramos",
            qcInspector: "Ma. Cristina Santos",
            branch: "Laguna Plant Depot",
            targetQty: 25000,
            goodQty: 24700,
            scrapQty: 210,
            reworkQty: 90,
            yieldRate: "98.8%",
            status: "Posted",
            deletedDate: "-",
            remarks: "Standard batch execution. Zero line downtime recorded.",
            lines: [
                {
                    prodCode: "FFC-05-100",
                    description: "FFC Cable 0.50mm Pitch 100mm",
                    uom: "PCS",
                    targetQty: 25000,
                    goodQty: 24700,
                    scrapQty: 210,
                    reworkQty: 90,
                    yieldRate: "98.8%",
                    lotSerial: "LOT-261104-A1",
                    verdict: "Passed"
                }
            ]
        },
        {
            id: 2,
            outputNo: "OUT-2026-0002",
            date: "2026-11-05",
            workOrder: "WO-2026-0819",
            line: "SMT Line 02",
            shift: "Shift B (Night)",
            supervisor: "Alvin Mercado",
            qcInspector: "Reginald Reyes",
            branch: "Laguna Plant Depot",
            targetQty: 30000,
            goodQty: 29400,
            scrapQty: 480,
            reworkQty: 120,
            yieldRate: "98.0%",
            status: "Posted",
            deletedDate: "-",
            remarks: "Slight feeder misalignment corrected during first hour inspection.",
            lines: [
                {
                    prodCode: "SH-05-HARN",
                    description: "Shielded Harness Wire 0.5mm",
                    uom: "PCS",
                    targetQty: 30000,
                    goodQty: 29400,
                    scrapQty: 480,
                    reworkQty: 120,
                    yieldRate: "98.0%",
                    lotSerial: "LOT-261105-B2",
                    verdict: "Passed"
                }
            ]
        },
        {
            id: 3,
            outputNo: "OUT-2026-0003",
            date: "2026-11-06",
            workOrder: "WO-2026-0824",
            line: "Extruder Line A",
            shift: "Shift A (Day)",
            supervisor: "Engr. Danilo Ramos",
            qcInspector: "Ma. Cristina Santos",
            branch: "Laguna Plant Depot",
            targetQty: 20000,
            goodQty: 19650,
            scrapQty: 250,
            reworkQty: 100,
            yieldRate: "98.3%",
            status: "In Process",
            deletedDate: "-",
            remarks: "Extrusion run in progress. Target completion by 16:00.",
            lines: [
                {
                    prodCode: "JMP-05-LEAD",
                    description: "Fine Pitch Jumper Lead 28AWG",
                    uom: "PCS",
                    targetQty: 20000,
                    goodQty: 19650,
                    scrapQty: 250,
                    reworkQty: 100,
                    yieldRate: "98.3%",
                    lotSerial: "LOT-261106-E1",
                    verdict: "Conditional"
                }
            ]
        },
        {
            id: 4,
            outputNo: "OUT-2026-0004",
            date: "2026-11-03",
            workOrder: "WO-2026-0801",
            line: "Assembly Cell 4",
            shift: "Shift C (Graveyard)",
            supervisor: "Nestor Bautista",
            qcInspector: "Reginald Reyes",
            branch: "Head Office (Mandaluyong)",
            targetQty: 22000,
            goodQty: 21100,
            scrapQty: 590,
            reworkQty: 310,
            yieldRate: "95.9%",
            status: "Cancelled",
            deletedDate: "-",
            remarks: "Batch cancelled due to non-conforming polymer insulation reel.",
            lines: [
                {
                    prodCode: "UFC-125-FLAT",
                    description: "Ultra-Flex Flat Cable 1.25mm",
                    uom: "PCS",
                    targetQty: 22000,
                    goodQty: 21100,
                    scrapQty: 590,
                    reworkQty: 310,
                    yieldRate: "95.9%",
                    lotSerial: "LOT-261103-C4",
                    verdict: "Failed"
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

        document.getElementById("btnCreateOutput").addEventListener("click", openCreateModal);
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
        const filterShift = document.getElementById("filterShift");
        const filterLine = document.getElementById("filterLine");
        const filterDateFrom = document.getElementById("filterDateFrom");
        const filterDateTo = document.getElementById("filterDateTo");
        const btnReset = document.getElementById("btnResetFilters");

        const handler = () => renderTable();

        searchInput.addEventListener("input", handler);
        filterStatus.addEventListener("change", handler);
        filterShift.addEventListener("change", handler);
        filterLine.addEventListener("change", handler);
        filterDateFrom.addEventListener("change", handler);
        filterDateTo.addEventListener("change", handler);

        btnReset.addEventListener("click", () => {
            searchInput.value = "";
            filterStatus.value = "";
            filterShift.value = "";
            filterLine.value = "";
            filterDateFrom.value = "";
            filterDateTo.value = "";
            renderTable();
        });
    }

    function renderTable() {
        const tbody = document.getElementById("outputTableBody");
        const search = (document.getElementById("globalSearchInput").value || "").toLowerCase().trim();
        const status = document.getElementById("filterStatus").value;
        const shift = document.getElementById("filterShift").value;
        const line = document.getElementById("filterLine").value;
        const dFrom = document.getElementById("filterDateFrom").value;
        const dTo = document.getElementById("filterDateTo").value;

        const filtered = outputRecords.filter(r => {
            if (status && r.status !== status) return false;
            if (shift && r.shift !== shift) return false;
            if (line && r.line !== line) return false;
            if (dFrom && r.date < dFrom) return false;
            if (dTo && r.date > dTo) return false;

            if (search) {
                const hay = `${r.outputNo} ${r.workOrder} ${r.line} ${r.shift} ${r.supervisor} ${r.qcInspector}`.toLowerCase();
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
                        <div>No matching production output logs found</div>
                    </td>
                </tr>
            `;
            lucide.createIcons();
            updateKPIs(filtered);
            return;
        }

        filtered.forEach(r => {
            const tr = document.createElement("tr");

            let statusClass = "status-pill-hold";
            if (r.status === "Posted") statusClass = "status-pill-completed";
            else if (r.status === "In Process") statusClass = "status-pill-progress";
            else if (r.status === "Cancelled") statusClass = "status-pill-cancelled";

            tr.innerHTML = `
                <td style="text-align: center;">
                    <input type="checkbox" class="row-select-check erp-cb" value="${r.id}" onchange="updateBulkSelection()">
                </td>
                <td>
                    <a href="javascript:void(0)" class="font-code font-bold" style="color: #2563eb; text-decoration: none;" onclick="viewOutputDossier(${r.id})">${r.outputNo}</a>
                </td>
                <td class="font-numeric">${r.date}</td>
                <td><strong class="font-code" style="color: #334155;">${r.workOrder}</strong></td>
                <td>${r.line}</td>
                <td>${r.shift}</td>
                <td>${r.supervisor}</td>
                <td class="font-numeric text-right">${r.targetQty.toLocaleString()}</td>
                <td class="font-numeric text-right font-bold" style="color: #059669;">${r.goodQty.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #dc2626;">${r.scrapQty.toLocaleString()}</td>
                <td class="font-numeric text-right" style="color: #d97706;">${r.reworkQty.toLocaleString()}</td>
                <td class="font-numeric text-right font-bold">${r.yieldRate}</td>
                <td class="text-center"><span class="status-pill ${statusClass}">${r.status}</span></td>
                <td class="text-center font-numeric" style="color: #94a3b8; font-size: 11px;">${r.deletedDate}</td>
                <td class="actions-cell">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, ${r.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${r.id}">
                        <button type="button" class="action-menu-item" onclick="viewOutputDossier(${r.id})">
                            <i data-lucide="eye"></i> View Dossier
                        </button>
                        <button type="button" class="action-menu-item" onclick="editOutputRecord(${r.id})">
                            <i data-lucide="edit"></i> Edit Output
                        </button>
                        <button type="button" class="action-menu-item" onclick="printOutputRun(${r.id})">
                            <i data-lucide="printer"></i> Print Sheet
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteOutput(${r.id})">
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
        document.getElementById("kpiTotalRuns").innerText = data.length;
        const totalGood = data.reduce((acc, r) => acc + (r.goodQty || 0), 0);
        const totalTarget = data.reduce((acc, r) => acc + (r.targetQty || 0), 0);
        const totalScrap = data.reduce((acc, r) => acc + (r.scrapQty || 0), 0);

        document.getElementById("kpiGoodUnits").innerText = totalGood.toLocaleString();
        document.getElementById("kpiScrapUnits").innerText = totalScrap.toLocaleString();

        const avgYield = totalTarget > 0 ? ((totalGood / totalTarget) * 100).toFixed(1) + "%" : "0.0%";
        document.getElementById("kpiAvgYield").innerText = avgYield;
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

    function bulkPostOutput() {
        const checked = document.querySelectorAll(".row-select-check:checked");
        checked.forEach(c => {
            const rec = outputRecords.find(r => r.id === parseInt(c.value));
            if (rec) rec.status = "Posted";
        });
        renderTable();
        updateBulkSelection();
    }

    function openCreateModal() {
        currentEditingId = null;
        document.getElementById("outputModalTitle").innerText = "Log Production Output";
        document.getElementById("outputForm").reset();

        const now = new Date();
        document.getElementById("modalOutputDate").value = now.toISOString().split("T")[0];
        document.getElementById("modalOutputNo").value = `OUT-2026-${String(outputRecords.length + 1).padStart(4, "0")}`;
        document.getElementById("modalSupervisor").value = "Engr. Danilo Ramos";
        document.getElementById("modalQcInspector").value = "Ma. Cristina Santos";

        const lineBody = document.getElementById("modalOutputLineBody");
        lineBody.innerHTML = "";
        addModalOutputLine();

        document.getElementById("outputModal").classList.add("show");
        lucide.createIcons();
    }

    function editOutputRecord(id) {
        currentEditingId = id;
        const rec = outputRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("outputModalTitle").innerText = `Edit Production Output — ${rec.outputNo}`;
        document.getElementById("modalBranch").value = rec.branch;
        document.getElementById("modalOutputNo").value = rec.outputNo;
        document.getElementById("modalOutputDate").value = rec.date;
        document.getElementById("modalShift").value = rec.shift;
        document.getElementById("modalWorkOrder").value = rec.workOrder;
        document.getElementById("modalLine").value = rec.line;
        document.getElementById("modalSupervisor").value = rec.supervisor;
        document.getElementById("modalQcInspector").value = rec.qcInspector;
        document.getElementById("modalStatus").value = rec.status;
        document.getElementById("modalRemarks").value = rec.remarks || "";

        const lineBody = document.getElementById("modalOutputLineBody");
        lineBody.innerHTML = "";
        if (rec.lines && rec.lines.length > 0) {
            rec.lines.forEach(line => addModalOutputLine(line));
        } else {
            addModalOutputLine({
                prodCode: "FFC-05-100",
                description: "FFC Cable Assembly",
                uom: "PCS",
                targetQty: rec.targetQty,
                goodQty: rec.goodQty,
                scrapQty: rec.scrapQty,
                reworkQty: rec.reworkQty,
                yieldRate: rec.yieldRate,
                lotSerial: "LOT-" + rec.outputNo,
                verdict: "Passed"
            });
        }

        document.getElementById("outputModal").classList.add("show");
        lucide.createIcons();
    }

    function addModalOutputLine(data = null) {
        const lineBody = document.getElementById("modalOutputLineBody");
        const tr = document.createElement("tr");

        const pCode = data ? data.prodCode : "FFC-05-100";
        const desc = data ? data.description : "FFC Cable 0.50mm Pitch 100mm";
        const uom = data ? data.uom : "PCS";
        const tQty = data ? data.targetQty : 10000;
        const gQty = data ? data.goodQty : 9850;
        const sQty = data ? data.scrapQty : 100;
        const rQty = data ? data.reworkQty : 50;
        const yRate = data ? data.yieldRate : "98.5%";
        const lot = data ? data.lotSerial : "LOT-" + Math.floor(100000 + Math.random() * 900000);
        const verdict = data ? data.verdict : "Passed";

        tr.innerHTML = `
            <td>
                <select class="line-prod-select" onchange="handleLineProdChange(this)">
                    <option value="FFC-05-100" ${pCode === 'FFC-05-100' ? 'selected' : ''}>FFC-05-100</option>
                    <option value="SH-05-HARN" ${pCode === 'SH-05-HARN' ? 'selected' : ''}>SH-05-HARN</option>
                    <option value="JMP-05-LEAD" ${pCode === 'JMP-05-LEAD' ? 'selected' : ''}>JMP-05-LEAD</option>
                    <option value="UFC-125-FLAT" ${pCode === 'UFC-125-FLAT' ? 'selected' : ''}>UFC-125-FLAT</option>
                </select>
            </td>
            <td><input type="text" class="line-desc" value="${desc}"></td>
            <td><input type="text" class="line-uom" value="${uom}" style="width: 55px;"></td>
            <td><input type="number" class="line-target font-numeric" value="${tQty}" oninput="calcLineYield(this)" style="text-align: right;"></td>
            <td><input type="number" class="line-good font-numeric" value="${gQty}" oninput="calcLineYield(this)" style="text-align: right; font-weight: 700; color: #059669;"></td>
            <td><input type="number" class="line-scrap font-numeric" value="${sQty}" oninput="calcLineYield(this)" style="text-align: right; color: #dc2626;"></td>
            <td><input type="number" class="line-rework font-numeric" value="${rQty}" style="text-align: right; color: #d97706;"></td>
            <td><input type="text" class="line-yield font-numeric" value="${yRate}" readonly style="text-align: right; font-weight: 700; background: #f8fafc;"></td>
            <td><input type="text" class="line-lot font-code" value="${lot}"></td>
            <td>
                <select class="line-verdict">
                    <option value="Passed" ${verdict === 'Passed' ? 'selected' : ''}>Passed</option>
                    <option value="Conditional" ${verdict === 'Conditional' ? 'selected' : ''}>Conditional</option>
                    <option value="Failed" ${verdict === 'Failed' ? 'selected' : ''}>Failed</option>
                </select>
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-remove-line" onclick="removeModalOutputLine(this)" title="Remove item">
                    <i data-lucide="trash-2"></i>
                </button>
            </td>
        `;
        lineBody.appendChild(tr);
        lucide.createIcons();
    }

    function handleLineProdChange(select) {
        const tr = select.closest("tr");
        const val = select.value;
        const descInput = tr.querySelector(".line-desc");
        if (val === "FFC-05-100") descInput.value = "FFC Cable 0.50mm Pitch 100mm";
        else if (val === "SH-05-HARN") descInput.value = "Shielded Harness Wire 0.5mm";
        else if (val === "JMP-05-LEAD") descInput.value = "Fine Pitch Jumper Lead 28AWG";
        else if (val === "UFC-125-FLAT") descInput.value = "Ultra-Flex Flat Cable 1.25mm";
    }

    function calcLineYield(input) {
        const tr = input.closest("tr");
        const target = parseFloat(tr.querySelector(".line-target").value) || 0;
        const good = parseFloat(tr.querySelector(".line-good").value) || 0;
        const yieldInput = tr.querySelector(".line-yield");

        if (target > 0) {
            yieldInput.value = ((good / target) * 100).toFixed(1) + "%";
        } else {
            yieldInput.value = "0.0%";
        }
    }

    function removeModalOutputLine(btn) {
        const tbody = document.getElementById("modalOutputLineBody");
        if (tbody.querySelectorAll("tr").length <= 1) {
            alert("A production output run must contain at least one item line.");
            return;
        }
        btn.closest("tr").remove();
    }

    function closeOutputModal() {
        document.getElementById("outputModal").classList.remove("show");
    }

    function saveOutputForm(event) {
        event.preventDefault();

        const rows = document.querySelectorAll("#modalOutputLineBody tr");
        let sumTarget = 0;
        let sumGood = 0;
        let sumScrap = 0;
        let sumRework = 0;
        const linesData = [];

        rows.forEach(tr => {
            const pCode = tr.querySelector(".line-prod-select").value;
            const desc = tr.querySelector(".line-desc").value;
            const uom = tr.querySelector(".line-uom").value;
            const tQty = parseFloat(tr.querySelector(".line-target").value) || 0;
            const gQty = parseFloat(tr.querySelector(".line-good").value) || 0;
            const sQty = parseFloat(tr.querySelector(".line-scrap").value) || 0;
            const rQty = parseFloat(tr.querySelector(".line-rework").value) || 0;
            const yRate = tr.querySelector(".line-yield").value;
            const lot = tr.querySelector(".line-lot").value;
            const verdict = tr.querySelector(".line-verdict").value;

            sumTarget += tQty;
            sumGood += gQty;
            sumScrap += sQty;
            sumRework += rQty;

            linesData.push({
                prodCode: pCode,
                description: desc,
                uom: uom,
                targetQty: tQty,
                goodQty: gQty,
                scrapQty: sQty,
                reworkQty: rQty,
                yieldRate: yRate,
                lotSerial: lot,
                verdict: verdict
            });
        });

        const overallYield = sumTarget > 0 ? ((sumGood / sumTarget) * 100).toFixed(1) + "%" : "0.0%";

        const payload = {
            outputNo: document.getElementById("modalOutputNo").value,
            date: document.getElementById("modalOutputDate").value,
            workOrder: document.getElementById("modalWorkOrder").value,
            line: document.getElementById("modalLine").value,
            shift: document.getElementById("modalShift").value,
            supervisor: document.getElementById("modalSupervisor").value,
            qcInspector: document.getElementById("modalQcInspector").value,
            branch: document.getElementById("modalBranch").value,
            status: document.getElementById("modalStatus").value,
            remarks: document.getElementById("modalRemarks").value,
            targetQty: sumTarget,
            goodQty: sumGood,
            scrapQty: sumScrap,
            reworkQty: sumRework,
            yieldRate: overallYield,
            lines: linesData
        };

        if (currentEditingId) {
            const idx = outputRecords.findIndex(r => r.id === currentEditingId);
            if (idx !== -1) {
                outputRecords[idx] = { ...outputRecords[idx], ...payload };
            }
        } else {
            payload.id = outputRecords.length > 0 ? Math.max(...outputRecords.map(r => r.id)) + 1 : 1;
            payload.deletedDate = "-";
            outputRecords.unshift(payload);
        }

        closeOutputModal();
        renderTable();
    }

    function viewOutputDossier(id) {
        const rec = outputRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("viewModalTitle").innerText = `Production Output Manifest — ${rec.outputNo}`;
        const body = document.getElementById("viewModalBody");

        let linesHtml = "";
        (rec.lines || []).forEach(l => {
            linesHtml += `
                <tr>
                    <td><strong class="font-code">${l.prodCode}</strong></td>
                    <td>${l.description}</td>
                    <td>${l.uom}</td>
                    <td class="font-numeric text-right">${l.targetQty.toLocaleString()}</td>
                    <td class="font-numeric text-right font-bold" style="color: #059669;">${l.goodQty.toLocaleString()}</td>
                    <td class="font-numeric text-right" style="color: #dc2626;">${l.scrapQty.toLocaleString()}</td>
                    <td class="font-numeric text-right" style="color: #d97706;">${l.reworkQty.toLocaleString()}</td>
                    <td class="font-numeric text-right font-bold">${l.yieldRate}</td>
                    <td class="font-code">${l.lotSerial}</td>
                    <td><span class="status-pill ${l.verdict === 'Passed' ? 'status-pill-completed' : 'status-pill-hold'}">${l.verdict}</span></td>
                </tr>
            `;
        });

        body.innerHTML = `
            <div class="form-section-card" style="margin-bottom: 16px;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                    <div>
                        <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin: 0;">${rec.outputNo}</h2>
                        <span style="font-size: 12px; color: #64748b;">Work Order: <strong>${rec.workOrder}</strong></span>
                    </div>
                    <span class="status-pill ${rec.status === 'Posted' ? 'status-pill-completed' : 'status-pill-hold'}" style="font-size: 12px; padding: 4px 10px;">${rec.status}</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; font-size: 12px;">
                    <div><span style="color: #64748b;">Branch:</span><br><strong>${rec.branch}</strong></div>
                    <div><span style="color: #64748b;">Run Date:</span><br><strong class="font-numeric">${rec.date}</strong></div>
                    <div><span style="color: #64748b;">Machine / Line:</span><br><strong>${rec.line}</strong></div>
                    <div><span style="color: #64748b;">Shift:</span><br><strong>${rec.shift}</strong></div>
                    <div><span style="color: #64748b;">Supervisor:</span><br><strong>${rec.supervisor}</strong></div>
                    <div><span style="color: #64748b;">QC Inspector:</span><br><strong>${rec.qcInspector}</strong></div>
                    <div><span style="color: #64748b;">Target Qty:</span><br><strong class="font-numeric">${rec.targetQty.toLocaleString()} PCS</strong></div>
                    <div><span style="color: #64748b;">Overall Yield:</span><br><strong class="font-numeric font-bold" style="color: #2563eb;">${rec.yieldRate}</strong></div>
                </div>
                ${rec.remarks ? `<div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #475569;"><strong>Notes:</strong> ${rec.remarks}</div>` : ''}
            </div>

            <div class="form-section-card">
                <div class="section-badge-title">
                    <span class="badge-num"><i data-lucide="package-check" style="width: 12px; height: 12px;"></i></span>
                    <span>Line Item Production & Quality Details</span>
                </div>

                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>UOM</th>
                                <th class="text-right">Target</th>
                                <th class="text-right">Good Units</th>
                                <th class="text-right">Scrap</th>
                                <th class="text-right">Rework</th>
                                <th class="text-right">Yield</th>
                                <th>Lot Serial</th>
                                <th>QC Result</th>
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

    function promptDeleteOutput(id) {
        pendingDeleteId = id;
        const rec = outputRecords.find(r => r.id === id);
        if (!rec) return;

        document.getElementById("deleteOutputNo").innerText = rec.outputNo;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
        pendingDeleteId = null;
    }

    function confirmDeleteOutput() {
        if (!pendingDeleteId) return;

        const idx = outputRecords.findIndex(r => r.id === pendingDeleteId);
        if (idx !== -1) {
            outputRecords[idx].deletedDate = new Date().toISOString().split("T")[0];
            outputRecords[idx].status = "Cancelled";
        }

        closeDeleteModal();
        renderTable();
    }

    function printOutputRun(id) {
        viewOutputDossier(id);
        setTimeout(() => window.print(), 300);
    }
</script>
@endsection
