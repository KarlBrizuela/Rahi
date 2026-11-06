@extends('layouts.app')

@section('title', 'Finished Goods Receiving — Intracode ERP')

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
            <span class="bc-current">FG Receiving</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search Receiving#, FGTS Ref, Lot#, SKU..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Finished Goods Receiving Register</h1>
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
                            <strong>Print Receiving Log</strong>
                            <small>Print inward production intake vouchers</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting Receiving CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Spreadsheet of warehouse intakes</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Receive Production Lot</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">Intake Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Accepted">Accepted & Binned</option>
                <option value="Under QA Inspection">Under QA Inspection</option>
                <option value="Quarantined">Quarantined</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Production Line</label>
            <select class="filter-select" id="filterLine">
                <option value="">All Production Lines</option>
                <option value="SMT Line 01">SMT Line 01</option>
                <option value="SMT Line 02">SMT Line 02</option>
                <option value="Extruder Line A">Extruder Line A</option>
                <option value="Assembly Cell 4">Assembly Cell 4</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Receiving Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Receiving Date To</label>
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
            <span class="kpi-label">TOTAL INTAKE ENTRIES</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Verified production transfer tickets</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL UNITS RECEIVED</span>
            <span class="kpi-value font-numeric font-bold" style="color: #059669;" id="kpiUnits">94,850 PCS</span>
            <span class="kpi-subtext">Transferred into warehouse storage</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL CARTONS BINNED</span>
            <span class="kpi-value font-numeric" id="kpiCartons">948 CTN</span>
            <span class="kpi-subtext">Palletized packaged inventory</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">UNDER INSPECTION</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiInspect">0</span>
            <span class="kpi-subtext">Awaiting QA release tag</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Inward Production Receiving Manifest</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkBinAssign()">
                    <i data-lucide="archive"></i>
                    <span>Assign Bin Rack</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintIntake()">
                    <i data-lucide="printer"></i>
                    <span>Print Receipts</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="rcvTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>RECEIVING NO</th>
                        <th>DATE</th>
                        <th>FGTS / WO REF</th>
                        <th>PRODUCT CODE & DESCRIPTION</th>
                        <th>LOT NUMBER</th>
                        <th class="text-right">RECEIVED QTY</th>
                        <th class="text-right">CARTONS</th>
                        <th>ASSIGNED BIN</th>
                        <th>INSPECTED BY</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="rcvTableBody">
                    <tr data-id="1" data-line="SMT Line 01" data-status="Accepted">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReceiving('1')">FGR-2026-0044</a>
                        </td>
                        <td class="font-numeric">2026-11-06</td>
                        <td>
                            <span class="font-code font-bold">FGTS-2026-0091</span>
                            <div class="text-muted" style="font-size:11px;">WO-2026-0814</div>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-8820-P1</span>
                                <span class="prod-sub">FFC Flexible Ribbon Cable 1.0mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-01A</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">24,700 PCS</td>
                        <td class="text-right font-numeric">247</td>
                        <td><span class="location-badge">Bay A-01 / Rack 4</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-completed">Accepted</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewReceiving('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReceiving('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReceiving('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Receipt</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReceiving('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Intake</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-line="SMT Line 02" data-status="Accepted">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReceiving('2')">FGR-2026-0043</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td>
                            <span class="font-code font-bold">FGTS-2026-0090</span>
                            <div class="text-muted" style="font-size:11px;">WO-2026-0819</div>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-5512-Y4</span>
                                <span class="prod-sub">Shielded Harness Wire Assembly 0.5mm</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-02B</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">29,400 PCS</td>
                        <td class="text-right font-numeric">294</td>
                        <td><span class="location-badge">Bay A-03 / Rack 2</span></td>
                        <td>Alvin Mercado</td>
                        <td><span class="status-badge status-completed">Accepted</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewReceiving('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReceiving('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReceiving('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Receipt</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReceiving('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Intake</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-line="Extruder Line A" data-status="Accepted">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReceiving('3')">FGR-2026-0042</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td>
                            <span class="font-code font-bold">FGTS-2026-0089</span>
                            <div class="text-muted" style="font-size:11px;">WO-2026-0824</div>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-9910-F2</span>
                                <span class="prod-sub">Fine Pitch Jumper Lead Assembly</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-03C</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">19,650 PCS</td>
                        <td class="text-right font-numeric">197</td>
                        <td><span class="location-badge">Bay B-01 / Rack 1</span></td>
                        <td>Engr. Danilo Ramos</td>
                        <td><span class="status-badge status-completed">Accepted</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewReceiving('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReceiving('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReceiving('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Receipt</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReceiving('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Intake</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-line="Assembly Cell 4" data-status="Accepted">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link" onclick="viewReceiving('4')">FGR-2026-0041</a>
                        </td>
                        <td class="font-numeric">2026-11-03</td>
                        <td>
                            <span class="font-code font-bold">FGTS-2026-0088</span>
                            <div class="text-muted" style="font-size:11px;">WO-2026-0801</div>
                        </td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title font-code font-bold">TFL-3301-C8</span>
                                <span class="prod-sub">Ultra-Flex Flat Cable 1.25mm Pitch</span>
                            </div>
                        </td>
                        <td><span class="font-code">LOT-2611-04D</span></td>
                        <td class="text-right font-numeric font-bold" style="color: #059669;">21,100 PCS</td>
                        <td class="text-right font-numeric">210</td>
                        <td><span class="location-badge">Bay B-04 / Rack 3</span></td>
                        <td>Nestor Bautista</td>
                        <td><span class="status-badge status-completed">Accepted</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewReceiving('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editReceiving('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit Intake</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printReceiving('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print Receipt</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deleteReceiving('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel Intake</span>
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
                <h3 class="modal-title" id="entryModalTitle">Receive Finished Goods from Production</h3>
                <p class="modal-subtitle">Log physical warehouse intake against approved FGTS production transfer slips.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="saveReceiving(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Document & Source Handover</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Receiving Number</label>
                        <input type="text" class="field-input font-code" id="rcvNumber" value="FGR-2026-0045" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Receiving Date</label>
                        <input type="date" class="field-input" id="rcvDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="rcvStatus" required>
                            <option value="Accepted">Accepted & Binned</option>
                            <option value="Under QA Inspection">Under QA Inspection</option>
                            <option value="Quarantined">Quarantined</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">FGTS Reference Slip</label>
                        <input type="text" class="field-input font-code" id="rcvFgtsRef" placeholder="e.g. FGTS-2026-0092" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Work Order Ref</label>
                        <input type="text" class="field-input font-code" id="rcvWoRef" placeholder="e.g. WO-2026-0825" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Production Line / Cell</label>
                        <select class="field-select" id="rcvLine" required>
                            <option value="SMT Line 01">SMT Line 01</option>
                            <option value="SMT Line 02">SMT Line 02</option>
                            <option value="Extruder Line A">Extruder Line A</option>
                            <option value="Assembly Cell 4">Assembly Cell 4</option>
                        </select>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Finished Goods Specification & Storage Bay</span>
                </div>
                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Finished Good Item</label>
                        <select class="field-select" id="rcvProduct" required>
                            <option value="TFL-8820-P1">TFL-8820-P1 — FFC Flexible Ribbon Cable 1.0mm Pitch</option>
                            <option value="TFL-5512-Y4">TFL-5512-Y4 — Shielded Harness Wire Assembly 0.5mm</option>
                            <option value="TFL-9910-F2">TFL-9910-F2 — Fine Pitch Jumper Lead Assembly</option>
                            <option value="TFL-3301-C8">TFL-3301-C8 — Ultra-Flex Flat Cable 1.25mm Pitch</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Production Lot Number</label>
                        <input type="text" class="field-input font-code" id="rcvLot" placeholder="e.g. LOT-2611-05E" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Quantity Received (PCS)</label>
                        <input type="number" class="field-input" id="rcvQty" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Total Cartons</label>
                        <input type="number" class="field-input" id="rcvCartons" min="1" placeholder="0" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Assigned Bay / Rack</label>
                        <input type="text" class="field-input" id="rcvLocation" placeholder="e.g. Bay A-02 / Rack 3" required>
                    </div>
                </div>

                <div class="form-row-grid grid-2">
                    <div class="form-field">
                        <label class="field-label required">Warehouse Intake Officer</label>
                        <input type="text" class="field-input" id="rcvInspector" value="Engr. Danilo Ramos" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">Remarks / Pallet Identification</label>
                        <input type="text" class="field-input" id="rcvRemarks" placeholder="Pallet barcode, QA green sticker verified...">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Confirm Receiving</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Finished Goods Receiving Voucher</h3>
                <p class="modal-subtitle">Official warehouse intake and bin allocation verification ticket.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Intake Slip</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Receiving Record</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel receiving record <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will revert the product quantity back to the production floor.</p>
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
        document.getElementById("filterLine").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterLine").value = "";
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
        const line = document.getElementById("filterLine").value.toLowerCase();
        const rows = document.querySelectorAll("#rcvTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowLine = (row.getAttribute("data-line") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchLine = !line || rowLine.includes(line);

            if (matchQuery && matchStatus && matchLine) {
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
        document.getElementById("entryModalTitle").innerText = "Receive Finished Goods from Production";
        document.getElementById("saveBtnLabel").innerText = "Confirm Receiving";
        document.getElementById("entryForm").reset();
        document.getElementById("rcvNumber").value = "FGR-2026-0045";
        document.getElementById("rcvDate").value = "2026-11-06";
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editReceiving(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Receiving Voucher " + id;
        document.getElementById("saveBtnLabel").innerText = "Update Voucher";

        if (id === '1') {
            document.getElementById("rcvNumber").value = "FGR-2026-0044";
            document.getElementById("rcvDate").value = "2026-11-06";
            document.getElementById("rcvStatus").value = "Accepted";
            document.getElementById("rcvFgtsRef").value = "FGTS-2026-0091";
            document.getElementById("rcvWoRef").value = "WO-2026-0814";
            document.getElementById("rcvLine").value = "SMT Line 01";
            document.getElementById("rcvProduct").value = "TFL-8820-P1";
            document.getElementById("rcvLot").value = "LOT-2611-01A";
            document.getElementById("rcvQty").value = "24700";
            document.getElementById("rcvCartons").value = "247";
            document.getElementById("rcvLocation").value = "Bay A-01 / Rack 4";
            document.getElementById("rcvInspector").value = "Engr. Danilo Ramos";
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function saveReceiving(e) {
        e.preventDefault();
        alert("Finished goods intake successfully posted to stock ledger.");
        closeEntryModal();
    }

    function viewReceiving(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Receiving No</span>
                    <span class="spec-value font-bold font-code">FGR-2026-0044</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Date Received</span>
                    <span class="spec-value font-numeric">2026-11-06</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Intake Status</span>
                    <span class="spec-value"><span class="status-badge status-completed">Accepted & Binned</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">FGTS Handover Ref</span>
                    <span class="spec-value font-code font-bold">FGTS-2026-0091</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Source Work Order</span>
                    <span class="spec-value font-code">WO-2026-0814 (SMT Line 01)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Assigned Warehouse Bay</span>
                    <span class="spec-value font-code">Bay A-01 / Rack 4</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Product Name</span>
                    <span class="spec-value font-bold">FFC Flexible Ribbon Cable 1.0mm Pitch (TFL-8820-P1)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Lot & Packaging</span>
                    <span class="spec-value">LOT-2611-01A (247 Cartons)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Total Intake Qty</span>
                    <span class="spec-value font-numeric font-bold" style="color: #059669;">24,700 PCS</span>
                </div>
            </div>
        `;
        document.getElementById("viewModal").classList.add("show");
        lucide.createIcons();
    }

    function closeViewModal() {
        document.getElementById("viewModal").classList.remove("show");
    }

    function printReceiving(id) {
        viewReceiving(id);
        setTimeout(() => window.print(), 300);
    }

    function deleteReceiving(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "FGR-2026-004" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Receiving voucher cancelled.");
        closeDeleteModal();
    }

    function bulkBinAssign() {
        alert("Batch bin location assignment modal opened.");
    }

    function bulkPrintIntake() {
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

