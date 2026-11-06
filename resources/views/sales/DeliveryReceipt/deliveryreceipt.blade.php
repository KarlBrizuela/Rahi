@extends('layouts.app')

@section('title', 'Delivery Receipt Register — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/DeliveryReceipt/deliveryreceipt.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">

    <header class="top-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-item">Revenue & Operations</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Sales</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Delivery Receipt</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="drSearchInput" placeholder="Search DR#, Customer, Bill to, SO Ref#..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Delivery Receipt Register</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Delivery Receipts">
                <i data-lucide="refresh-cw" class="btn-icon" id="refreshIcon"></i>
                <span>Refresh</span>
            </button>

            <!-- Export / Print List Dropdown -->
            <div class="dropdown-wrapper" id="exportDropdownWrapper">
                <button type="button" class="btn-outline" id="btnExportToggle">
                    <i data-lucide="download" class="btn-icon"></i>
                    <span>Export / Print</span>
                    <i data-lucide="chevron-down" class="dropdown-chevron"></i>
                </button>
                <div class="export-dropdown-menu" id="exportMenu">
                    <button type="button" class="export-option" onclick="exportDeliveryReceipts('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable Delivery Receipt register</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportDeliveryReceipts('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Spreadsheet dispatch schedule</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportDeliveryReceipts('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated dispatch records</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create DR Button -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Delivery Receipt</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <!-- Status Filter -->
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="ALL">All Statuses</option>
                <option value="Not Yet Posted">Not Yet Posted</option>
                <option value="Posted">Posted</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Branch Filter -->
        <div class="filter-item">
            <span class="filter-label">Branch:</span>
            <select id="filterBranch" class="filter-select">
                <option value="ALL">All Branches</option>
                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                <option value="Makati Sales Hub">Makati Sales Hub</option>
                <option value="Laguna Plant Depot">Laguna Plant Depot</option>
                <option value="Cebu Logistics Depot">Cebu Logistics Depot</option>
                <option value="Davao Regional Hub">Davao Regional Hub</option>
            </select>
        </div>

        <!-- DR Date Range Filter -->
        <div class="filter-item">
            <span class="filter-label">DR Date:</span>
            <div class="filter-date-group">
                <input type="date" id="filterDateFrom" class="filter-date-input" title="From Date">
                <span class="filter-sep">to</span>
                <input type="date" id="filterDateTo" class="filter-date-input" title="To Date">
            </div>
        </div>

        <!-- Reset Button -->
        <button type="button" class="btn-filter-reset" id="btnResetFilters">
            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
            <span>Reset Filters</span>
        </button>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Total Delivery Receipts</span>
            <span class="kpi-value" id="kpiTotalDRs">0</span>
            <span class="kpi-sub" id="kpiActiveDRsSub">0 active records</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Dispatched Units</span>
            <span class="kpi-value" id="kpiTotalUnits">0</span>
            <span class="kpi-sub">Aggregated dispatch quantity</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Posted & Dispatched</span>
            <span class="kpi-value" id="kpiPostedCount" style="color: #10b981;">0</span>
            <span class="kpi-sub">Fulfillment completed</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Pending Release</span>
            <span class="kpi-value" id="kpiPendingCount" style="color: #f59e0b;">0</span>
            <span class="kpi-sub">Not yet posted / staging</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Dispatch Manifest Ledger</span>
                    <span class="count-pill" id="drCountBadge">0 Records</span>
                    <span class="selection-pill" id="bulkActionBar">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedCountText">0 selected</span>
                        <button type="button" class="btn-bulk-delete" id="btnBulkDelete">Delete</button>
                    </span>
                </div>
            </div>

            <!-- Horizontal Scroll Table Container -->
            <div class="table-scroll-container">
                <table class="erp-table" id="drDataTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb" title="Select All">
                            </th>
                            <th style="min-width: 130px;">DR Number</th>
                            <th style="min-width: 170px;">Branch</th>
                            <th style="min-width: 200px;">Customer</th>
                            <th style="min-width: 200px;">Bill To</th>
                            <th style="min-width: 110px;">DR Date</th>
                            <th style="min-width: 120px;">SO Ref. Number</th>
                            <th style="min-width: 120px;">Inv Ref. Number</th>
                            <th style="min-width: 150px;">Prepared By</th>
                            <th style="min-width: 90px;" class="text-right">Items Count</th>
                            <th style="min-width: 100px;" class="text-right">Total Qty</th>
                            <th style="min-width: 130px;" class="text-center">Status</th>
                            <th style="min-width: 110px;" class="text-center">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="drTableBody">
                        <!-- Populated dynamically via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer & Pagination -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="paginationMeta">Showing 0 of 0 entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn" id="btnPrevPage" disabled>Previous</button>
                    <button type="button" class="page-btn active" id="page1">1</button>
                    <button type="button" class="page-btn" id="btnNextPage" disabled>Next</button>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-backdrop" id="drModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-xlarge">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="drModalTitle">Create Delivery Receipt</h2>
                    <span class="modal-subtitle">Configure branch dispatch, invoice linkage, line items, and delivery authorizations.</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsDrModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="drForm" autocomplete="off">
                <input type="hidden" id="formDrId" value="">

                <div class="modal-body">
                    <!-- SECTION 1: HEADER & DOCUMENT IDENTIFIERS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">1</span>
                                <span class="section-label">General & Document Identifiers</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formBranch">Branch <span class="required">*</span></label>
                                <select id="formBranch" class="form-select" required>
                                    <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                                    <option value="Makati Sales Hub">Makati Sales Hub</option>
                                    <option value="Laguna Plant Depot">Laguna Plant Depot</option>
                                    <option value="Cebu Logistics Depot">Cebu Logistics Depot</option>
                                    <option value="Davao Regional Hub">Davao Regional Hub</option>
                                </select>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formDrNumber">DR Number (Pre-numbered) <span class="required">*</span></label>
                                <input type="text" id="formDrNumber" class="form-input" placeholder="e.g. DR-2026-0042" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formDrDate">DR Date <span class="required">*</span></label>
                                <input type="date" id="formDrDate" class="form-input" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formStatus">Status <span class="required">*</span></label>
                                <select id="formStatus" class="form-select" required>
                                    <option value="Not Yet Posted">Not Yet Posted</option>
                                    <option value="Posted">Posted</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formInvRefNumber">Inv Ref Number (Link Invoice)</label>
                                <div style="display: flex; gap: 6px;">
                                    <select id="formInvRefNumber" class="form-select">
                                        <option value="">-- None (Manual Delivery Receipt) --</option>
                                        <option value="SI-2026-001">SI-2026-001 (TechCorp Solutions)</option>
                                        <option value="SI-2026-002">SI-2026-002 (Apex Systems Mfg.)</option>
                                        <option value="SI-2026-003">SI-2026-003 (Pacific Electronics)</option>
                                        <option value="SI-2026-004">SI-2026-004 (Global Vanguard Corp)</option>
                                    </select>
                                    <button type="button" class="btn-outline" id="btnPullInvoiceData" style="height: 34px; padding: 0 10px;" title="Load Invoice Lines & Customer Data">
                                        <i data-lucide="link" style="width: 14px; height: 14px;"></i>
                                        <span>Link</span>
                                    </button>
                                </div>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formSoRefNumber">SO Ref. Number (Customer P.O.)</label>
                                <input type="text" id="formSoRefNumber" class="form-input" placeholder="e.g. PO-TC-9941 / SO-2026-012">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: CUSTOMER & DESTINATION -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">2</span>
                                <span class="section-label">Customer & Delivery Destination</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formCustomer">Customer <span class="required">*</span></label>
                                <input type="text" id="formCustomer" class="form-input" placeholder="e.g. TechCorp Solutions Inc." required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formBillTo">Bill To <span class="required">*</span></label>
                                <input type="text" id="formBillTo" class="form-input" placeholder="e.g. TechCorp Receiving Dept / Procurement" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="formAddress">Billing / Delivery Address <span class="required">*</span></label>
                            <input type="text" id="formAddress" class="form-input" placeholder="e.g. Bldg 4, Laguna Technopark Special Economic Zone, Biñan, Laguna" required>
                        </div>
                    </div>

                    <!-- SECTION 3: GRID DETAILS (LINE ITEMS) -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">3</span>
                                <span class="section-label">Grid Details — Line Items</span>
                            </div>
                            <button type="button" class="btn-outline" id="btnAddGridRow" style="height: 28px; padding: 0 10px; font-size: 11.5px;">
                                <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                                <span>Add Line Item</span>
                            </button>
                        </div>

                        <div class="nested-table-wrap">
                            <table class="nested-erp-table" id="drItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Sort #</th>
                                        <th style="width: 140px;">Prod Id <span class="required">*</span></th>
                                        <th style="width: 120px;">Product Code</th>
                                        <th style="width: 220px;">Description</th>
                                        <th style="width: 80px;">UOP</th>
                                        <th style="width: 90px;" class="text-right">SO Bal</th>
                                        <th style="width: 90px;" class="text-right">Qty <span class="required">*</span></th>
                                        <th style="width: 100px;" class="text-right">Allocated Qty</th>
                                        <th style="width: 110px;">SO No</th>
                                        <th style="width: 110px;">Inv Number</th>
                                        <th style="width: 110px;">Fg Number</th>
                                        <th style="width: 160px;">Remarks</th>
                                        <th style="width: 50px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="drItemsBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Strip -->
                        <div class="summary-strip-card">
                            <div class="summary-item">
                                <span class="summary-item-label">Line Items</span>
                                <span class="summary-item-value" id="gridTotalItems">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-item-label">Total Dispatch Qty</span>
                                <span class="summary-item-value accent-total" id="gridTotalQty">0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-item-label">Total FG Allocated</span>
                                <span class="summary-item-value" id="gridTotalAllocated">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: SIGNATURES & AUTHORIZATIONS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">4</span>
                                <span class="section-label">Signatures & Authorizations</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formPreparedBy">Prepared By <span class="required">*</span></label>
                                <input type="text" id="formPreparedBy" class="form-input" value="Karl Burgos (Logistics Enc.)" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formCheckedBy">Checked By</label>
                                <input type="text" id="formCheckedBy" class="form-input" placeholder="e.g. Maria Santos (QA/QC Inspector)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formApprovedBy">Approved By</label>
                                <input type="text" id="formApprovedBy" class="form-input" placeholder="e.g. Robert Tan (Warehouse Head)">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: REMARKS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">5</span>
                                <span class="section-label">Remarks & Logistics Notes</span>
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <textarea id="formRemarks" class="form-textarea" rows="2" placeholder="Optional notes or additional information related to the delivery receipt (e.g., driver plate number, gate pass requirements)..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn-outline" id="btnCancelDrModal">Cancel</button>
                    <button type="submit" class="btn-primary" id="btnSaveDr">
                        <i data-lucide="check" class="btn-icon"></i>
                        <span id="btnSaveDrText">Save Delivery Receipt</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="drViewModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-large">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="viewDrTitle">Delivery Receipt Dossier</h2>
                    <span class="modal-subtitle" id="viewDrSubtitle">Official Warehouse Outbound Dispatch Manifest</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsDrViewModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="view-dossier-grid">
                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Manifest Particulars</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">DR Number:</span>
                                <span class="dossier-val bold-text" id="vDrNumber">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Branch:</span>
                                <span class="dossier-val" id="vBranch">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">DR Date:</span>
                                <span class="dossier-val" id="vDrDate">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Status:</span>
                                <span class="dossier-val" id="vStatus">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Invoice Ref:</span>
                                <span class="dossier-val" id="vInvRef">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Customer SO Ref:</span>
                                <span class="dossier-val" id="vSoRef">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Consignee & Destination</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Customer:</span>
                                <span class="dossier-val bold-text" id="vCustomer">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Bill To:</span>
                                <span class="dossier-val" id="vBillTo">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Address:</span>
                                <span class="dossier-val" id="vAddress">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Prepared By:</span>
                                <span class="dossier-val" id="vPreparedBy">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Checked By:</span>
                                <span class="dossier-val" id="vCheckedBy">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Approved By:</span>
                                <span class="dossier-val" id="vApprovedBy">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items Table in View -->
                <div style="margin-top: 16px;">
                    <h4 style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Dispatched Line Items</h4>
                    <div class="nested-table-wrap">
                        <table class="nested-erp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Code</th>
                                    <th>Description</th>
                                    <th>UOP</th>
                                    <th class="text-right">SO Bal</th>
                                    <th class="text-right">Dispatch Qty</th>
                                    <th class="text-right">Allocated Qty</th>
                                    <th>SO No</th>
                                    <th>Inv Number</th>
                                    <th>FG No</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="vItemsBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- View Remarks -->
                <div class="dossier-card" style="margin-top: 14px;">
                    <h4 class="dossier-card-title">Remarks & Manifest Notes</h4>
                    <p id="vRemarks" style="font-size: 12.5px; color: #334155; margin: 0; line-height: 1.5;">-</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="printCurrentDR()">
                    <i data-lucide="printer" class="btn-icon"></i>
                    <span>Print Manifest</span>
                </button>
                <button type="button" class="btn-primary" id="btnClsDrViewModal2">Close</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="drDeleteModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-small">
            <div class="modal-header">
                <h2 class="modal-title" style="color: #dc2626;">Confirm Deletion</h2>
                <button type="button" class="modal-close-btn" id="btnClsDrDeleteModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13px; color: #334155; line-height: 1.5;">
                    Are you sure you want to delete delivery receipt <strong id="deleteDrNumPrompt"></strong>?
                </p>
                <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                    This action marks the delivery receipt as deleted with an official audit timestamp.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnCancelDelete">Cancel</button>
                <button type="button" class="btn-primary" id="btnConfirmDelete" style="background: #dc2626; border-color: #dc2626;">
                    <span>Delete Record</span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Master Product Catalog for dynamic lookup
    const PRODUCT_MASTER = [
        { id: 'PROD-001', code: 'ITM-8840', desc: 'Embedded Microcontroller PCB Rev 3.2', uop: 'PCS' },
        { id: 'PROD-002', code: 'ITM-9210', desc: 'High-Torque Industrial Stepper Motor 24V', uop: 'UNIT' },
        { id: 'PROD-003', code: 'ITM-4402', desc: 'Fiber Optic Transceiver Module SFP+ 10G', uop: 'BOX' },
        { id: 'PROD-004', code: 'ITM-1055', desc: 'Precision CNC Machined Aluminum Enclosure', uop: 'PCS' },
        { id: 'PROD-005', code: 'ITM-7719', desc: 'Heavy Duty Industrial Power Relay 40A', uop: 'PCS' }
    ];

    // Master Invoice Reference Presets (links customer & items)
    const INVOICE_MASTER = {
        'SI-2026-001': {
            branch: 'Head Office (Mandaluyong)',
            customer: 'TechCorp Solutions Inc.',
            billTo: 'TechCorp Logistics Division',
            address: 'Bldg 4, Laguna Technopark Special Economic Zone, Biñan, Laguna',
            soRefNumber: 'PO-TC-9941',
            items: [
                { prodId: 'PROD-001', code: 'ITM-8840', desc: 'Embedded Microcontroller PCB Rev 3.2', uop: 'PCS', soBal: 120, qty: 80, allocQty: 80, soNo: 'SO-2026-001', invNo: 'SI-2026-001', fgNo: 'FG-884-A', remarks: 'Batch tested QA Passed' },
                { prodId: 'PROD-004', code: 'ITM-1055', desc: 'Precision CNC Machined Aluminum Enclosure', uop: 'PCS', soBal: 80, qty: 80, allocQty: 80, soNo: 'SO-2026-001', invNo: 'SI-2026-001', fgNo: 'FG-105-B', remarks: 'Standard anodized finish' }
            ]
        },
        'SI-2026-002': {
            branch: 'Makati Sales Hub',
            customer: 'Apex Systems Mfg.',
            billTo: 'Apex Systems Receiving Dock 2',
            address: 'Lot 12 Blk 5, First Cavite Industrial Estate, Dasmariñas, Cavite',
            soRefNumber: 'PO-APX-4420',
            items: [
                { prodId: 'PROD-002', code: 'ITM-9210', desc: 'High-Torque Industrial Stepper Motor 24V', uop: 'UNIT', soBal: 50, qty: 35, allocQty: 35, soNo: 'SO-2026-002', invNo: 'SI-2026-002', fgNo: 'FG-921-C', remarks: 'With mounting brackets' }
            ]
        },
        'SI-2026-003': {
            branch: 'Laguna Plant Depot',
            customer: 'Pacific Electronics Ltd.',
            billTo: 'Pacific Warehouse Staging Hub',
            address: 'Gate 3, Light Industry & Science Park II, Calamba City, Laguna',
            soRefNumber: 'PO-PAC-8812',
            items: [
                { prodId: 'PROD-003', code: 'ITM-4402', desc: 'Fiber Optic Transceiver Module SFP+ 10G', uop: 'BOX', soBal: 200, qty: 150, allocQty: 150, soNo: 'SO-2026-003', invNo: 'SI-2026-003', fgNo: 'FG-440-D', remarks: 'Sealed ESD antistatic packs' }
            ]
        },
        'SI-2026-004': {
            branch: 'Cebu Logistics Depot',
            customer: 'Global Vanguard Corp',
            billTo: 'Global Vanguard Central Depot',
            address: 'Mandaue City Export Zone, Cebu 6014',
            soRefNumber: 'PO-GV-3301',
            items: [
                { prodId: 'PROD-005', code: 'ITM-7719', desc: 'Heavy Duty Industrial Power Relay 40A', uop: 'PCS', soBal: 100, qty: 100, allocQty: 100, soNo: 'SO-2026-004', invNo: 'SI-2026-004', fgNo: 'FG-771-E', remarks: 'Direct shipment palletized' }
            ]
        }
    };

    // State: Delivery Receipts List
    let deliveryReceipts = [
        {
            id: 'DR-001',
            drNumber: 'DR-2026-001',
            branch: 'Head Office (Mandaluyong)',
            customer: 'TechCorp Solutions Inc.',
            billTo: 'TechCorp Logistics Division',
            address: 'Bldg 4, Laguna Technopark Special Economic Zone, Biñan, Laguna',
            drDate: '2026-11-04',
            soRefNumber: 'PO-TC-9941',
            invRefNumber: 'SI-2026-001',
            preparedBy: 'Karl Burgos (Logistics)',
            checkedBy: 'Maria Santos (QA)',
            approvedBy: 'Robert Tan (Warehouse Mgr)',
            status: 'Posted',
            deletedDate: '-',
            remarks: 'Handed over to Transport Forwarder Truck 4 (Plate: NBD-9912). All seals intact.',
            items: [
                { sortNumber: 1, prodId: 'PROD-001', code: 'ITM-8840', desc: 'Embedded Microcontroller PCB Rev 3.2', uop: 'PCS', soBal: 120, qty: 80, allocQty: 80, soNo: 'SO-2026-001', invNo: 'SI-2026-001', fgNo: 'FG-884-A', remarks: 'Batch tested QA Passed' },
                { sortNumber: 2, prodId: 'PROD-004', code: 'ITM-1055', desc: 'Precision CNC Machined Aluminum Enclosure', uop: 'PCS', soBal: 80, qty: 80, allocQty: 80, soNo: 'SO-2026-001', invNo: 'SI-2026-001', fgNo: 'FG-105-B', remarks: 'Standard anodized finish' }
            ]
        },
        {
            id: 'DR-002',
            drNumber: 'DR-2026-002',
            branch: 'Makati Sales Hub',
            customer: 'Apex Systems Mfg.',
            billTo: 'Apex Systems Receiving Dock 2',
            address: 'Lot 12 Blk 5, First Cavite Industrial Estate, Dasmariñas, Cavite',
            drDate: '2026-11-05',
            soRefNumber: 'PO-APX-4420',
            invRefNumber: 'SI-2026-002',
            preparedBy: 'Karl Burgos (Logistics)',
            checkedBy: 'Elena Cruz (QC)',
            approvedBy: 'Robert Tan (Warehouse Mgr)',
            status: 'Posted',
            deletedDate: '-',
            remarks: 'Express dispatch via Courier Services. Tracking # EXP-99412.',
            items: [
                { sortNumber: 1, prodId: 'PROD-002', code: 'ITM-9210', desc: 'High-Torque Industrial Stepper Motor 24V', uop: 'UNIT', soBal: 50, qty: 35, allocQty: 35, soNo: 'SO-2026-002', invNo: 'SI-2026-002', fgNo: 'FG-921-C', remarks: 'With mounting brackets' }
            ]
        },
        {
            id: 'DR-003',
            drNumber: 'DR-2026-003',
            branch: 'Laguna Plant Depot',
            customer: 'Pacific Electronics Ltd.',
            billTo: 'Pacific Warehouse Staging Hub',
            address: 'Gate 3, Light Industry & Science Park II, Calamba City, Laguna',
            drDate: '2026-11-06',
            soRefNumber: 'PO-PAC-8812',
            invRefNumber: 'SI-2026-003',
            preparedBy: 'Karl Burgos (Logistics)',
            checkedBy: 'Carlos Mendoza',
            approvedBy: 'Robert Tan (Warehouse Mgr)',
            status: 'Not Yet Posted',
            deletedDate: '-',
            remarks: 'Staged at Loading Bay 3 awaiting carrier pickup.',
            items: [
                { sortNumber: 1, prodId: 'PROD-003', code: 'ITM-4402', desc: 'Fiber Optic Transceiver Module SFP+ 10G', uop: 'BOX', soBal: 200, qty: 150, allocQty: 150, soNo: 'SO-2026-003', invNo: 'SI-2026-003', fgNo: 'FG-440-D', remarks: 'Sealed ESD antistatic packs' }
            ]
        },
        {
            id: 'DR-004',
            drNumber: 'DR-2026-004',
            branch: 'Cebu Logistics Depot',
            customer: 'Global Vanguard Corp',
            billTo: 'Global Vanguard Central Depot',
            address: 'Mandaue City Export Zone, Cebu 6014',
            drDate: '2026-11-03',
            soRefNumber: 'PO-GV-3301',
            invRefNumber: 'SI-2026-004',
            preparedBy: 'Jonathan Reyes',
            checkedBy: 'Carlos Mendoza',
            approvedBy: 'Robert Tan (Warehouse Mgr)',
            status: 'Cancelled',
            deletedDate: '-',
            remarks: 'Cancelled due to customer site renovation reschedule.',
            items: [
                { sortNumber: 1, prodId: 'PROD-005', code: 'ITM-7719', desc: 'Heavy Duty Industrial Power Relay 40A', uop: 'PCS', soBal: 100, qty: 100, allocQty: 100, soNo: 'SO-2026-004', invNo: 'SI-2026-004', fgNo: 'FG-771-E', remarks: 'Returned to Finished Goods Staging' }
            ]
        }
    ];

    let selectedDrIds = new Set();
    let currentDeleteId = null;

    // DOM Elements
    const drTableBody = document.getElementById('drTableBody');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountText = document.getElementById('selectedCountText');
    const btnBulkDelete = document.getElementById('btnBulkDelete');

    // Filter elements
    const drSearchInput = document.getElementById('drSearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterBranch = document.getElementById('filterBranch');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    const btnResetFilters = document.getElementById('btnResetFilters');

    // KPI elements
    const kpiTotalDRs = document.getElementById('kpiTotalDRs');
    const kpiActiveDRsSub = document.getElementById('kpiActiveDRsSub');
    const kpiTotalUnits = document.getElementById('kpiTotalUnits');
    const kpiPostedCount = document.getElementById('kpiPostedCount');
    const kpiPendingCount = document.getElementById('kpiPendingCount');
    const drCountBadge = document.getElementById('drCountBadge');
    const paginationMeta = document.getElementById('paginationMeta');

    // Modal elements
    const drModal = document.getElementById('drModal');
    const drForm = document.getElementById('drForm');
    const drModalTitle = document.getElementById('drModalTitle');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnClsDrModal = document.getElementById('btnClsDrModal');
    const btnCancelDrModal = document.getElementById('btnCancelDrModal');
    const drItemsBody = document.getElementById('drItemsBody');
    const btnAddGridRow = document.getElementById('btnAddGridRow');
    const btnPullInvoiceData = document.getElementById('btnPullInvoiceData');

    // View Modal
    const drViewModal = document.getElementById('drViewModal');
    const btnClsDrViewModal = document.getElementById('btnClsDrViewModal');
    const btnClsDrViewModal2 = document.getElementById('btnClsDrViewModal2');

    // Delete Modal
    const drDeleteModal = document.getElementById('drDeleteModal');
    const deleteDrNumPrompt = document.getElementById('deleteDrNumPrompt');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const btnClsDrDeleteModal = document.getElementById('btnClsDrDeleteModal');

    // Dropdown / Refresh
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportMenu = document.getElementById('exportMenu');
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');

    // Initialize Default Dates
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('formDrDate').value = todayStr;

    // Render Table Function
    function renderTable() {
        const query = (drSearchInput.value || '').trim().toLowerCase();
        const statusFilter = filterStatus.value;
        const branchFilter = filterBranch.value;
        const dateFrom = filterDateFrom.value;
        const dateTo = filterDateTo.value;

        const filtered = deliveryReceipts.filter(dr => {
            if (query) {
                const matchQuery = 
                    dr.drNumber.toLowerCase().includes(query) ||
                    dr.customer.toLowerCase().includes(query) ||
                    dr.billTo.toLowerCase().includes(query) ||
                    (dr.soRefNumber && dr.soRefNumber.toLowerCase().includes(query)) ||
                    (dr.invRefNumber && dr.invRefNumber.toLowerCase().includes(query)) ||
                    dr.branch.toLowerCase().includes(query);
                if (!matchQuery) return false;
            }

            if (statusFilter !== 'ALL' && dr.status !== statusFilter) {
                return false;
            }

            if (branchFilter !== 'ALL' && dr.branch !== branchFilter) {
                return false;
            }

            if (dateFrom && dr.drDate < dateFrom) {
                return false;
            }

            if (dateTo && dr.drDate > dateTo) {
                return false;
            }

            return true;
        });

        // Compute KPIs
        updateKPIs(deliveryReceipts);

        drTableBody.innerHTML = '';

        if (filtered.length === 0) {
            drTableBody.innerHTML = `
                <tr>
                    <td colspan="14" style="text-align: center; padding: 36px; color: #64748b;">
                        <i data-lucide="inbox" style="width: 28px; height: 28px; margin-bottom: 6px; stroke-width: 1.5; color: #94a3b8;"></i>
                        <p style="font-size: 13px; font-weight: 500;">No delivery receipts found matching your criteria.</p>
                        <span style="font-size: 11.5px; color: #94a3b8;">Try clearing filters or search terms.</span>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            drCountBadge.textContent = '0 Records';
            paginationMeta.textContent = 'Showing 0 of 0 entries';
            return;
        }

        filtered.forEach((dr, index) => {
            const tr = document.createElement('tr');
            if (dr.deletedDate !== '-') {
                tr.classList.add('row-deleted');
            }

            const isChecked = selectedDrIds.has(dr.id);
            if (isChecked) {
                tr.classList.add('row-selected');
            }

            const totalQty = (dr.items || []).reduce((acc, it) => acc + (Number(it.qty) || 0), 0);
            const itemsCount = (dr.items || []).length;

            let statusBadgeClass = 'status-pill-pending';
            if (dr.status === 'Posted') statusBadgeClass = 'status-pill-posted';
            else if (dr.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';

            tr.innerHTML = `
                <td style="width: 40px; text-align: center;" class="no-print">
                    <input type="checkbox" class="erp-cb row-checkbox" data-id="${dr.id}" ${isChecked ? 'checked' : ''}>
                </td>
                <td class="font-bold font-code" style="min-width: 130px;">
                    <a href="javascript:void(0)" onclick="viewDeliveryReceipt('${dr.id}')" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                        ${escapeHtml(dr.drNumber)}
                    </a>
                </td>
                <td style="min-width: 170px;">${escapeHtml(dr.branch)}</td>
                <td style="min-width: 200px; font-weight: 500;">${escapeHtml(dr.customer)}</td>
                <td style="min-width: 200px;">${escapeHtml(dr.billTo)}</td>
                <td class="font-numeric" style="min-width: 110px;">${escapeHtml(dr.drDate)}</td>
                <td class="font-code" style="min-width: 120px;">${escapeHtml(dr.soRefNumber || '-')}</td>
                <td class="font-code" style="min-width: 120px;">${escapeHtml(dr.invRefNumber || '-')}</td>
                <td style="min-width: 150px;">${escapeHtml(dr.preparedBy)}</td>
                <td class="text-right font-numeric" style="min-width: 90px;">${itemsCount}</td>
                <td class="text-right font-numeric font-bold" style="min-width: 100px;">${totalQty.toLocaleString()}</td>
                <td class="text-center" style="min-width: 130px;">
                    <span class="status-pill ${statusBadgeClass}">
                        ${escapeHtml(dr.status)}
                    </span>
                </td>
                <td class="text-center font-numeric text-muted" style="min-width: 110px; font-size: 11.5px;">
                    ${escapeHtml(dr.deletedDate)}
                </td>
                <td class="actions-cell text-center no-print" style="width: 70px;">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '${dr.id}')" title="Actions">
                        <i data-lucide="more-horizontal" style="width: 15px; height: 15px;"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${dr.id}">
                        <button type="button" class="action-menu-item" onclick="viewDeliveryReceipt('${dr.id}')">
                            <i data-lucide="eye"></i>
                            <span>View Dossier</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editDeliveryReceipt('${dr.id}')">
                            <i data-lucide="edit"></i>
                            <span>Edit DR</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteDR('${dr.id}')">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </td>
            `;

            drTableBody.appendChild(tr);
        });

        drCountBadge.textContent = `${filtered.length} Records`;
        paginationMeta.textContent = `Showing 1 to ${filtered.length} of ${filtered.length} entries`;

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        updateSelectAllState(filtered);
    }

    function updateKPIs(data) {
        const activeRecords = data.filter(d => d.deletedDate === '-');
        kpiTotalDRs.textContent = data.length;
        kpiActiveDRsSub.textContent = `${activeRecords.length} active records`;

        let totalDispatched = 0;
        let postedCount = 0;
        let pendingCount = 0;

        activeRecords.forEach(dr => {
            const sumQty = (dr.items || []).reduce((acc, it) => acc + (Number(it.qty) || 0), 0);
            totalDispatched += sumQty;
            if (dr.status === 'Posted') postedCount++;
            else if (dr.status === 'Not Yet Posted') pendingCount++;
        });

        kpiTotalUnits.textContent = totalDispatched.toLocaleString();
        kpiPostedCount.textContent = postedCount;
        kpiPendingCount.textContent = pendingCount;
    }

    // Escape HTML Helper
    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Toggle Action Menu Dropdown with safe positioning
    window.toggleActionMenu = function (e, id) {
        e.stopPropagation();
        const menu = document.getElementById(`actionMenu-${id}`);
        const allMenus = document.querySelectorAll('.action-menu-dropdown');
        
        allMenus.forEach(m => {
            if (m !== menu) m.classList.remove('show');
        });

        if (menu) {
            const isShown = menu.classList.contains('show');
            if (isShown) {
                menu.classList.remove('show');
            } else {
                // Determine drop-up vs drop-down based on window bottom
                const triggerRect = e.currentTarget.getBoundingClientRect();
                const spaceBelow = window.innerHeight - triggerRect.bottom;
                if (spaceBelow < 140) {
                    menu.classList.add('drop-up');
                    menu.classList.remove('drop-down');
                } else {
                    menu.classList.add('drop-down');
                    menu.classList.remove('drop-up');
                }
                menu.classList.add('show');
            }
        }
    };

    // Close action menus & export on click outside
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.action-menu-dropdown').forEach(m => m.classList.remove('show'));
        if (exportDropdownWrapper && !exportDropdownWrapper.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
    });

    // Checkbox & Selection Handlers
    drTableBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-checkbox')) {
            const id = e.target.getAttribute('data-id');
            if (e.target.checked) {
                selectedDrIds.add(id);
            } else {
                selectedDrIds.delete(id);
            }
            updateBulkActionBar();
        }
    });

    selectAllCheckbox.addEventListener('change', function () {
        const checkboxes = drTableBody.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
            const id = cb.getAttribute('data-id');
            if (selectAllCheckbox.checked) {
                selectedDrIds.add(id);
            } else {
                selectedDrIds.delete(id);
            }
        });
        updateBulkActionBar();
    });

    function updateSelectAllState(currentFilteredList) {
        const allVisibleSelected = currentFilteredList.length > 0 &&
            currentFilteredList.every(dr => selectedDrIds.has(dr.id));
        selectAllCheckbox.checked = allVisibleSelected;
        updateBulkActionBar();
    }

    function updateBulkActionBar() {
        if (selectedDrIds.size > 0) {
            bulkActionBar.classList.add('show');
            selectedCountText.textContent = `${selectedDrIds.size} selected`;
        } else {
            bulkActionBar.classList.remove('show');
        }
    }

    // Bulk Delete
    btnBulkDelete.addEventListener('click', function () {
        if (selectedDrIds.size === 0) return;
        if (confirm(`Are you sure you want to delete ${selectedDrIds.size} selected delivery receipt(s)?`)) {
            const nowTime = new Date().toLocaleString();
            deliveryReceipts.forEach(dr => {
                if (selectedDrIds.has(dr.id)) {
                    dr.deletedDate = nowTime;
                }
            });
            selectedDrIds.clear();
            renderTable();
        }
    });

    // Search & Filter Listeners
    [drSearchInput, filterStatus, filterBranch, filterDateFrom, filterDateTo].forEach(el => {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    btnResetFilters.addEventListener('click', function () {
        drSearchInput.value = '';
        filterStatus.value = 'ALL';
        filterBranch.value = 'ALL';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        selectedDrIds.clear();
        renderTable();
    });

    // Refresh simulation
    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        setTimeout(() => {
            refreshIcon.classList.remove('spin-anim');
            renderTable();
        }, 500);
    });

    // Export Dropdown Toggle
    btnExportToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        exportMenu.classList.toggle('show');
    });

    // Export handlers
    window.exportDeliveryReceipts = function (type) {
        exportMenu.classList.remove('show');
        if (type === 'pdf') {
            window.print();
        } else if (type === 'csv') {
            downloadCSV();
        } else if (type === 'excel') {
            downloadExcelMock();
        }
    };

    function downloadCSV() {
        let csv = 'DR Number,Branch,Customer,Bill To,DR Date,SO Ref#,Inv Ref#,Prepared By,Items Count,Total Qty,Status,Deleted Date\n';
        deliveryReceipts.forEach(d => {
            const totalQty = (d.items || []).reduce((acc, it) => acc + (Number(it.qty) || 0), 0);
            csv += `"${d.drNumber}","${d.branch}","${d.customer}","${d.billTo}","${d.drDate}","${d.soRefNumber || ''}","${d.invRefNumber || ''}","${d.preparedBy}",${(d.items || []).length},${totalQty},"${d.status}","${d.deletedDate}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Delivery_Receipts_${todayStr}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }

    function downloadExcelMock() {
        downloadCSV(); // Safe fallback for client-side workbook download
    }

    // Modal Handlers: Open Create
    btnOpenCreateModal.addEventListener('click', function () {
        drForm.reset();
        document.getElementById('formDrId').value = '';
        drModalTitle.textContent = 'Create Delivery Receipt';
        document.getElementById('btnSaveDrText').textContent = 'Save Delivery Receipt';
        document.getElementById('formDrDate').value = todayStr;
        document.getElementById('formPreparedBy').value = 'Karl Burgos (Logistics Enc.)';
        drItemsBody.innerHTML = '';
        
        // Add one initial line row
        addGridRow();
        updateGridTotals();

        drModal.classList.add('show');
        drModal.setAttribute('aria-hidden', 'false');
    });

    // Close Create/Edit Modal
    function closeDrModal() {
        drModal.classList.remove('show');
        drModal.setAttribute('aria-hidden', 'true');
    }
    btnClsDrModal.addEventListener('click', closeDrModal);
    btnCancelDrModal.addEventListener('click', closeDrModal);

    // Dynamic Line Items Grid
    function addGridRow(itemData = null) {
        const rowIndex = drItemsBody.children.length + 1;
        const tr = document.createElement('tr');

        const defaultProd = PRODUCT_MASTER[0];
        const selectedProdId = itemData ? itemData.prodId : defaultProd.id;
        const selectedProd = PRODUCT_MASTER.find(p => p.id === selectedProdId) || defaultProd;

        let optionsHtml = '';
        PRODUCT_MASTER.forEach(p => {
            optionsHtml += `<option value="${p.id}" ${p.id === selectedProdId ? 'selected' : ''}>${p.id} (${p.code})</option>`;
        });

        tr.innerHTML = `
            <td style="text-align: center; font-weight: 600;" class="row-seq">${rowIndex}</td>
            <td>
                <select class="grid-cell-input row-prod-id" onchange="onProductSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" class="grid-cell-input row-prod-code" value="${itemData ? itemData.code : selectedProd.code}" readonly>
            </td>
            <td>
                <input type="text" class="grid-cell-input row-desc" value="${itemData ? itemData.desc : selectedProd.desc}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-uop" value="${itemData ? itemData.uop : selectedProd.uop}">
            </td>
            <td>
                <input type="number" class="grid-cell-input row-sobal" style="text-align: right;" value="${itemData ? itemData.soBal : 100}">
            </td>
            <td>
                <input type="number" class="grid-cell-input row-qty" style="text-align: right; font-weight: 600;" value="${itemData ? itemData.qty : 10}" min="1" oninput="updateGridTotals()">
            </td>
            <td>
                <input type="number" class="grid-cell-input row-alloc-qty" style="text-align: right;" value="${itemData ? itemData.allocQty : 10}" oninput="updateGridTotals()">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-sono" value="${itemData ? itemData.soNo : 'SO-2026-001'}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-invno" value="${itemData ? itemData.invNo : (document.getElementById('formInvRefNumber').value || 'SI-2026-001')}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-fgno" value="${itemData ? itemData.fgNo : 'FG-' + Math.floor(100 + Math.random() * 900) + '-A'}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-remarks" value="${itemData ? itemData.remarks : ''}" placeholder="Notes...">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-del-mini" onclick="removeGridRow(this)" title="Remove item">
                    <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                </button>
            </td>
        `;

        drItemsBody.appendChild(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        resequenceRows();
        updateGridTotals();
    }

    btnAddGridRow.addEventListener('click', function () {
        addGridRow();
    });

    window.removeGridRow = function (btn) {
        const row = btn.closest('tr');
        if (drItemsBody.children.length <= 1) {
            alert('At least one item line must remain in the delivery receipt.');
            return;
        }
        row.remove();
        resequenceRows();
        updateGridTotals();
    };

    function resequenceRows() {
        const rows = drItemsBody.querySelectorAll('tr');
        rows.forEach((r, idx) => {
            const seqEl = r.querySelector('.row-seq');
            if (seqEl) seqEl.textContent = idx + 1;
        });
    }

    window.onProductSelectChange = function (selectEl) {
        const prodId = selectEl.value;
        const prod = PRODUCT_MASTER.find(p => p.id === prodId);
        if (prod) {
            const row = selectEl.closest('tr');
            row.querySelector('.row-prod-code').value = prod.code;
            row.querySelector('.row-desc').value = prod.desc;
            row.querySelector('.row-uop').value = prod.uop;
        }
        updateGridTotals();
    };

    window.updateGridTotals = function () {
        const rows = drItemsBody.querySelectorAll('tr');
        let totalItems = rows.length;
        let totalQty = 0;
        let totalAlloc = 0;

        rows.forEach(r => {
            const q = Number(r.querySelector('.row-qty')?.value) || 0;
            const a = Number(r.querySelector('.row-alloc-qty')?.value) || 0;
            totalQty += q;
            totalAlloc += a;
        });

        document.getElementById('gridTotalItems').textContent = totalItems;
        document.getElementById('gridTotalQty').textContent = totalQty.toLocaleString();
        document.getElementById('gridTotalAllocated').textContent = totalAlloc.toLocaleString();
    };

    // Auto-populate when Linking Invoice Reference
    btnPullInvoiceData.addEventListener('click', function () {
        const selectedInv = document.getElementById('formInvRefNumber').value;
        if (!selectedInv) {
            alert('Please select an Invoice Reference first.');
            return;
        }

        const invData = INVOICE_MASTER[selectedInv];
        if (invData) {
            document.getElementById('formBranch').value = invData.branch;
            document.getElementById('formCustomer').value = invData.customer;
            document.getElementById('formBillTo').value = invData.billTo;
            document.getElementById('formAddress').value = invData.address;
            document.getElementById('formSoRefNumber').value = invData.soRefNumber;

            // Populate Grid items
            drItemsBody.innerHTML = '';
            invData.items.forEach(it => {
                addGridRow(it);
            });
            updateGridTotals();
        } else {
            alert('No preset data found for invoice reference ' + selectedInv + '. You may enter line items manually.');
        }
    });

    // Save Delivery Receipt Form
    drForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const editId = document.getElementById('formDrId').value;
        const drNumber = document.getElementById('formDrNumber').value.trim();
        const branch = document.getElementById('formBranch').value;
        const drDate = document.getElementById('formDrDate').value;
        const status = document.getElementById('formStatus').value;
        const invRefNumber = document.getElementById('formInvRefNumber').value;
        const soRefNumber = document.getElementById('formSoRefNumber').value.trim();
        const customer = document.getElementById('formCustomer').value.trim();
        const billTo = document.getElementById('formBillTo').value.trim();
        const address = document.getElementById('formAddress').value.trim();
        const preparedBy = document.getElementById('formPreparedBy').value.trim();
        const checkedBy = document.getElementById('formCheckedBy').value.trim();
        const approvedBy = document.getElementById('formApprovedBy').value.trim();
        const remarks = document.getElementById('formRemarks').value.trim();

        // Collect Grid Line Items
        const rows = drItemsBody.querySelectorAll('tr');
        const items = [];
        rows.forEach((r, idx) => {
            items.push({
                sortNumber: idx + 1,
                prodId: r.querySelector('.row-prod-id').value,
                code: r.querySelector('.row-prod-code').value,
                desc: r.querySelector('.row-desc').value,
                uop: r.querySelector('.row-uop').value,
                soBal: Number(r.querySelector('.row-sobal').value) || 0,
                qty: Number(r.querySelector('.row-qty').value) || 0,
                allocQty: Number(r.querySelector('.row-alloc-qty').value) || 0,
                soNo: r.querySelector('.row-sono').value,
                invNo: r.querySelector('.row-invno').value,
                fgNo: r.querySelector('.row-fgno').value,
                remarks: r.querySelector('.row-remarks').value
            });
        });

        if (editId) {
            // Update Existing
            const idx = deliveryReceipts.findIndex(d => d.id === editId);
            if (idx !== -1) {
                deliveryReceipts[idx] = {
                    ...deliveryReceipts[idx],
                    drNumber,
                    branch,
                    drDate,
                    status,
                    invRefNumber,
                    soRefNumber,
                    customer,
                    billTo,
                    address,
                    preparedBy,
                    checkedBy,
                    approvedBy,
                    remarks,
                    items
                };
            }
        } else {
            // Create New
            const newId = 'DR-' + Date.now().toString().slice(-4);
            deliveryReceipts.unshift({
                id: newId,
                drNumber,
                branch,
                drDate,
                status,
                invRefNumber,
                soRefNumber,
                customer,
                billTo,
                address,
                preparedBy,
                checkedBy,
                approvedBy,
                remarks,
                deletedDate: '-',
                items
            });
        }

        closeDrModal();
        renderTable();
    });

    // View Modal
    let activeViewingDr = null;
    window.viewDeliveryReceipt = function (id) {
        const dr = deliveryReceipts.find(d => d.id === id);
        if (!dr) return;
        activeViewingDr = dr;

        document.getElementById('viewDrTitle').textContent = `Delivery Receipt: ${dr.drNumber}`;
        document.getElementById('viewDrSubtitle').textContent = `Outbound Dispatch Manifest — ${dr.branch}`;

        document.getElementById('vDrNumber').textContent = dr.drNumber;
        document.getElementById('vBranch').textContent = dr.branch;
        document.getElementById('vDrDate').textContent = dr.drDate;
        document.getElementById('vStatus').textContent = dr.status;
        document.getElementById('vInvRef').textContent = dr.invRefNumber || 'N/A';
        document.getElementById('vSoRef').textContent = dr.soRefNumber || 'N/A';

        document.getElementById('vCustomer').textContent = dr.customer;
        document.getElementById('vBillTo').textContent = dr.billTo;
        document.getElementById('vAddress').textContent = dr.address;
        document.getElementById('vPreparedBy').textContent = dr.preparedBy;
        document.getElementById('vCheckedBy').textContent = dr.checkedBy || 'Pending Verification';
        document.getElementById('vApprovedBy').textContent = dr.approvedBy || 'Pending Warehouse Release';
        document.getElementById('vRemarks').textContent = dr.remarks || 'No special remarks encoded.';

        // Items body
        const vItemsBody = document.getElementById('vItemsBody');
        vItemsBody.innerHTML = '';
        (dr.items || []).forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight: 600;">${it.sortNumber}</td>
                <td style="font-weight: 600; color: #2563eb;">${escapeHtml(it.code)}</td>
                <td>${escapeHtml(it.desc)}</td>
                <td>${escapeHtml(it.uop)}</td>
                <td class="text-right font-mono">${(it.soBal || 0).toLocaleString()}</td>
                <td class="text-right font-mono" style="font-weight: 700; color: #0f172a;">${(it.qty || 0).toLocaleString()}</td>
                <td class="text-right font-mono">${(it.allocQty || 0).toLocaleString()}</td>
                <td>${escapeHtml(it.soNo || '-')}</td>
                <td>${escapeHtml(it.invNo || '-')}</td>
                <td>${escapeHtml(it.fgNo || '-')}</td>
                <td>${escapeHtml(it.remarks || '-')}</td>
            `;
            vItemsBody.appendChild(tr);
        });

        drViewModal.classList.add('show');
        drViewModal.setAttribute('aria-hidden', 'false');
    };

    function closeDrViewModal() {
        drViewModal.classList.remove('show');
        drViewModal.setAttribute('aria-hidden', 'true');
    }
    btnClsDrViewModal.addEventListener('click', closeDrViewModal);
    btnClsDrViewModal2.addEventListener('click', closeDrViewModal);

    window.printCurrentDR = function () {
        window.print();
    };

    // Edit Modal
    window.editDeliveryReceipt = function (id) {
        const dr = deliveryReceipts.find(d => d.id === id);
        if (!dr) return;

        document.getElementById('formDrId').value = dr.id;
        drModalTitle.textContent = `Edit Delivery Receipt: ${dr.drNumber}`;
        document.getElementById('btnSaveDrText').textContent = 'Update Delivery Receipt';

        document.getElementById('formBranch').value = dr.branch;
        document.getElementById('formDrNumber').value = dr.drNumber;
        document.getElementById('formDrDate').value = dr.drDate;
        document.getElementById('formStatus').value = dr.status;
        document.getElementById('formInvRefNumber').value = dr.invRefNumber || '';
        document.getElementById('formSoRefNumber').value = dr.soRefNumber || '';
        document.getElementById('formCustomer').value = dr.customer;
        document.getElementById('formBillTo').value = dr.billTo;
        document.getElementById('formAddress').value = dr.address;
        document.getElementById('formPreparedBy').value = dr.preparedBy;
        document.getElementById('formCheckedBy').value = dr.checkedBy || '';
        document.getElementById('formApprovedBy').value = dr.approvedBy || '';
        document.getElementById('formRemarks').value = dr.remarks || '';

        // Fill Items
        drItemsBody.innerHTML = '';
        if (dr.items && dr.items.length > 0) {
            dr.items.forEach(it => {
                addGridRow(it);
            });
        } else {
            addGridRow();
        }
        updateGridTotals();

        drModal.classList.add('show');
        drModal.setAttribute('aria-hidden', 'false');
    };

    // Delete Prompt Modal
    window.promptDeleteDR = function (id) {
        const dr = deliveryReceipts.find(d => d.id === id);
        if (!dr) return;
        currentDeleteId = dr.id;
        deleteDrNumPrompt.textContent = dr.drNumber;
        drDeleteModal.classList.add('show');
        drDeleteModal.setAttribute('aria-hidden', 'false');
    };

    function closeDeleteModal() {
        drDeleteModal.classList.remove('show');
        drDeleteModal.setAttribute('aria-hidden', 'true');
        currentDeleteId = null;
    }

    btnCancelDelete.addEventListener('click', closeDeleteModal);
    btnClsDrDeleteModal.addEventListener('click', closeDeleteModal);

    btnConfirmDelete.addEventListener('click', function () {
        if (!currentDeleteId) return;
        const target = deliveryReceipts.find(d => d.id === currentDeleteId);
        if (target) {
            target.deletedDate = new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'short',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        closeDeleteModal();
        renderTable();
    });

    // Initial render
    renderTable();
});
</script>
@endpush
@endsection

