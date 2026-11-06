@extends('layouts.app')

@section('title', 'Shipping Invoice Register — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/ShippingInvoice/shippinginvoice.css') }}">
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
            <span class="bc-current">Shipping Invoice</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="shipSearchInput" placeholder="Search Shipping Inv#, Buyer, Bill to, Ref#..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Shipping Invoice Register</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Shipping Invoices">
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
                    <button type="button" class="export-option" onclick="exportShippingInvoices('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable shipping manifest ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportShippingInvoices('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Spreadsheet customs & billing schedule</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportShippingInvoices('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated shipping invoice records</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Shipping Invoice Button -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Shipping Invoice</span>
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

        <!-- Covered Shipping Date Filter -->
        <div class="filter-item">
            <span class="filter-label">Covered Date:</span>
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
            <span class="kpi-label">Total Shipping Invoices</span>
            <span class="kpi-value" id="kpiTotalInvoices">0</span>
            <span class="kpi-sub" id="kpiActiveInvoicesSub">0 active records</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Invoice Value</span>
            <span class="kpi-value" id="kpiTotalValue" style="color: #2563eb;">₱0.00</span>
            <span class="kpi-sub">Total payable amount (inc. VAT)</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Posted & Cleared</span>
            <span class="kpi-value" id="kpiPostedCount" style="color: #10b981;">0</span>
            <span class="kpi-sub">Customs & logistics processed</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Pending Release</span>
            <span class="kpi-value" id="kpiPendingCount" style="color: #f59e0b;">0</span>
            <span class="kpi-sub">Not yet posted / in preparation</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Shipping Invoice Master Register</span>
                    <span class="count-pill" id="shipCountBadge">0 Records</span>
                    <span class="selection-pill" id="bulkActionBar">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedCountText">0 selected</span>
                        <button type="button" class="btn-bulk-delete" id="btnBulkDelete">Delete</button>
                    </span>
                </div>
            </div>

            <!-- Horizontal Scroll Table Container -->
            <div class="table-scroll-container">
                <table class="erp-table" id="shipDataTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb" title="Select All">
                            </th>
                            <th style="min-width: 140px;">Shipping Inv#</th>
                            <th style="min-width: 170px;">Branch</th>
                            <th style="min-width: 200px;">Buyer</th>
                            <th style="min-width: 200px;">Bill To</th>
                            <th style="min-width: 110px;">Shipping Date</th>
                            <th style="min-width: 110px;">Txn Date</th>
                            <th style="min-width: 100px;">Shipment</th>
                            <th style="min-width: 80px;" class="text-center">Curr</th>
                            <th style="min-width: 120px;" class="text-right">Gross Amount</th>
                            <th style="min-width: 110px;" class="text-right">Disc Amount</th>
                            <th style="min-width: 120px;" class="text-right">Net Amount</th>
                            <th style="min-width: 110px;" class="text-right">VAT Amount</th>
                            <th style="min-width: 130px;" class="text-right">Total Amount</th>
                            <th style="min-width: 130px;" class="text-center">Status</th>
                            <th style="min-width: 110px;" class="text-center">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="shipTableBody">
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

    <div class="modal-backdrop" id="shipModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-xlarge">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="shipModalTitle">Create Shipping Invoice</h2>
                    <span class="modal-subtitle">Configure freight terms, buyer billing, notify parties, logistics specifications, and customs lines.</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsShipModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="shipForm" autocomplete="off">
                <input type="hidden" id="formShipId" value="">

                <div class="modal-body">
                    <!-- SECTION 1: HEADER & DOCUMENT IDENTIFIERS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">1</span>
                                <span class="section-label">Header & Document Identifiers</span>
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
                                <label for="formShipInvNumber">Shipping Inv Number <span class="required">*</span></label>
                                <input type="text" id="formShipInvNumber" class="form-input" placeholder="e.g. SI-EXP-2026-008" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formShippingDate">Shipping Date <span class="required">*</span></label>
                                <input type="date" id="formShippingDate" class="form-input" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formShippingTxnDate">Shipping Txn Date <span class="required">*</span></label>
                                <input type="date" id="formShippingTxnDate" class="form-input" required title="Transaction date payment term will be based">
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
                    </div>

                    <!-- SECTION 2: BUYER, BILL TO & NOTIFY PARTIES -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">2</span>
                                <span class="section-label">Buyer, Bill To & Consignee / Notify Parties</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formBuyer">Buyer <span class="required">*</span></label>
                                <input type="text" id="formBuyer" class="form-input" placeholder="Customer placing the order (e.g. Apex Electronics Ltd.)" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formBillTo">Bill To <span class="required">*</span></label>
                                <input type="text" id="formBillTo" class="form-input" placeholder="Entity responsible for payment" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formAttention">Attention</label>
                                <input type="text" id="formAttention" class="form-input" placeholder="Contact person name (e.g. Ms. Sarah Jenkins)">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formNotify">Notify</label>
                                <input type="text" id="formNotify" class="form-input" placeholder="Party to be notified (e.g. FastFreight Trans Intl)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formNotifyAttentionTo">Notify Attention To</label>
                                <input type="text" id="formNotifyAttentionTo" class="form-input" placeholder="Contact person to be notified (e.g. Operations Mgr)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formRefNumber">Ref. Number (Customer P.O.)</label>
                                <input type="text" id="formRefNumber" class="form-input" placeholder="e.g. PO-EXP-99214">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: COMMERCIAL & LOGISTICS TERMS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">3</span>
                                <span class="section-label">Commercial, Freight & Logistics Parameters</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formCurrency">Currency <span class="required">*</span></label>
                                <select id="formCurrency" class="form-select" onchange="calculateAllFinancials()" required>
                                    <option value="PHP">PHP (Philippine Peso)</option>
                                    <option value="USD">USD (US Dollar)</option>
                                    <option value="EUR">EUR (Euro)</option>
                                    <option value="JPY">JPY (Japanese Yen)</option>
                                </select>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formRate">Rate (to PHP) <span class="required">*</span></label>
                                <input type="number" step="0.0001" id="formRate" class="form-input" value="1.0000" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formCountryOfOrigin">Country of Origin <span class="required">*</span></label>
                                <input type="text" id="formCountryOfOrigin" class="form-input" value="Philippines" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formCountryOfDestination">Country of Destination <span class="required">*</span></label>
                                <input type="text" id="formCountryOfDestination" class="form-input" placeholder="e.g. Japan / United States" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formPaymentTerm">Payment Term <span class="required">*</span></label>
                                <select id="formPaymentTerm" class="form-select" required>
                                    <option value="Net 30 Days">Net 30 Days</option>
                                    <option value="Net 60 Days">Net 60 Days</option>
                                    <option value="Cash on Delivery (COD)">Cash on Delivery (COD)</option>
                                    <option value="Letter of Credit (L/C)">Letter of Credit (L/C)</option>
                                    <option value="30% DP, 70% Upon BL">30% DP, 70% Upon BL</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formShipment">Shipment Mode <span class="required">*</span></label>
                                <select id="formShipment" class="form-select" required>
                                    <option value="By Air">By Air</option>
                                    <option value="By Sea / Boat">By Sea / Boat</option>
                                    <option value="By Land">By Land</option>
                                </select>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formShipmentDate">Shipment Date <span class="required">*</span></label>
                                <input type="date" id="formShipmentDate" class="form-input" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formLogistics">Logistics Provider <span class="required">*</span></label>
                                <input type="text" id="formLogistics" class="form-input" placeholder="e.g. DHL Global Forwarding / Nippon Express" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formDeliveryTerms">Delivery Terms <span class="required">*</span></label>
                                <select id="formDeliveryTerms" class="form-select" required>
                                    <option value="FOB (Free on Board)">FOB (Free on Board)</option>
                                    <option value="CIF (Cost, Insurance & Freight)">CIF (Cost, Insurance & Freight)</option>
                                    <option value="EXW (Ex Works)">EXW (Ex Works)</option>
                                    <option value="DDP (Delivered Duty Paid)">DDP (Delivered Duty Paid)</option>
                                    <option value="CFR (Cost and Freight)">CFR (Cost and Freight)</option>
                                </select>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formVatCode">VAT Code <span class="required">*</span></label>
                                <select id="formVatCode" class="form-select" onchange="calculateAllFinancials()" required>
                                    <option value="VAT_ZERO">Zero-Rated (Export 0%)</option>
                                    <option value="VAT_12">VAT 12% (Standard)</option>
                                    <option value="VAT_EXEMPT">VAT Exempt</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formCNo">C/No (Carton / Box Number)</label>
                                <input type="text" id="formCNo" class="form-input" placeholder="e.g. C/NO. 1 - 25">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formMeasurementTotal">Measurement Total</label>
                                <input type="text" id="formMeasurementTotal" class="form-input" placeholder="e.g. 14.85 CBM / 25 Packages">
                            </div>

                            <div class="form-group flex-2">
                                <label for="formShipmentRemarks">Shipment Remarks</label>
                                <input type="text" id="formShipmentRemarks" class="form-input" placeholder="Cargo handling notes, container #, flight/voyage ref...">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: GRID DETAILS (NESTED LINE ITEMS) -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">4</span>
                                <span class="section-label">Grid Details — Line Items</span>
                            </div>
                            <button type="button" class="btn-outline" id="btnAddGridRow" style="height: 28px; padding: 0 10px; font-size: 11.5px;">
                                <i data-lucide="plus" style="width: 12px; height: 12px;"></i>
                                <span>Add Product Line</span>
                            </button>
                        </div>

                        <div class="nested-table-wrap">
                            <table class="nested-erp-table" id="shipItemsTable">
                                <thead>
                                    <tr>
                                        <th style="width: 45px;">Sort#</th>
                                        <th style="width: 130px;">Prod Id <span class="required">*</span></th>
                                        <th style="width: 100px;">HS Code</th>
                                        <th style="width: 110px;">Product Code</th>
                                        <th style="width: 200px;">Description</th>
                                        <th style="width: 70px;">UOM</th>
                                        <th style="width: 80px;" class="text-right">Qty <span class="required">*</span></th>
                                        <th style="width: 80px;" class="text-right">Weight</th>
                                        <th style="width: 70px;">Unit(w)</th>
                                        <th style="width: 85px;" class="text-right">Gross Wt</th>
                                        <th style="width: 70px;">Unit(G)</th>
                                        <th style="width: 90px;">Measurement</th>
                                        <th style="width: 70px;" class="text-right">Carton</th>
                                        <th style="width: 100px;" class="text-right">Selling Price <span class="required">*</span></th>
                                        <th style="width: 70px;" class="text-center">Curr</th>
                                        <th style="width: 75px;" class="text-right">VAT%</th>
                                        <th style="width: 100px;" class="text-right">Gross Amt</th>
                                        <th style="width: 80px;" class="text-right">Disc%</th>
                                        <th style="width: 90px;" class="text-right">Disc Amt</th>
                                        <th style="width: 100px;" class="text-right">Net Amt</th>
                                        <th style="width: 110px;" class="text-right">Amount</th>
                                        <th style="width: 105px;">DR Number</th>
                                        <th style="width: 95px;">DR Date</th>
                                        <th style="width: 105px;">SO Number</th>
                                        <th style="width: 105px;">SO Ref Number</th>
                                        <th style="width: 45px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="shipItemsBody">
                                    <!-- Populated dynamically via JavaScript -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Financial Totals Strip -->
                        <div class="financial-totals-card">
                            <div class="fin-item">
                                <span class="fin-label">Gross Amount</span>
                                <span class="fin-val" id="calcGrossAmount">0.00</span>
                            </div>
                            <div class="fin-item">
                                <span class="fin-label">Discount Amount</span>
                                <span class="fin-val" style="color: #dc2626;" id="calcDiscountAmount">0.00</span>
                            </div>
                            <div class="fin-item">
                                <span class="fin-label">Net Amount</span>
                                <span class="fin-val" id="calcNetAmount">0.00</span>
                            </div>
                            <div class="fin-item">
                                <span class="fin-label">VAT Amount</span>
                                <span class="fin-val" id="calcVatAmount">0.00</span>
                            </div>
                            <div class="fin-item">
                                <span class="fin-label">Total Amount</span>
                                <span class="fin-val accent-primary" id="calcTotalAmount">0.00</span>
                            </div>
                            <div class="fin-item">
                                <span class="fin-label">Reprint Count</span>
                                <span class="fin-val" id="dispReprintCount">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: SIGNATURES & GENERAL REMARKS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">5</span>
                                <span class="section-label">Signatures, Approvals & Remarks</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formPreparedBy">Prepared By <span class="required">*</span></label>
                                <input type="text" id="formPreparedBy" class="form-input" value="Karl Burgos (Documentation)" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formCheckedBy">Checked By</label>
                                <input type="text" id="formCheckedBy" class="form-input" placeholder="e.g. Maria Santos (Logistics QA)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formApprovedBy">Approved By</label>
                                <input type="text" id="formApprovedBy" class="form-input" placeholder="e.g. Robert Tan (Export Manager)">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="formRemarks">Invoice Remarks</label>
                            <textarea id="formRemarks" class="form-textarea" rows="2" placeholder="Optional notes or additional information related to the shipping invoice, letter of credit clauses, customs exemptions..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn-outline" id="btnCancelShipModal">Cancel</button>
                    <button type="submit" class="btn-primary" id="btnSaveShip">
                        <i data-lucide="check" class="btn-icon"></i>
                        <span id="btnSaveShipText">Save Shipping Invoice</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="shipViewModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-large">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="viewShipTitle">Shipping Invoice Dossier</h2>
                    <span class="modal-subtitle" id="viewShipSubtitle">Official Customs & Logistics Billing Record</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsShipViewModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="view-dossier-grid">
                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Commercial & Manifest Particulars</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Shipping Inv#:</span>
                                <span class="dossier-val bold-text" id="vShipInvNum">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Branch:</span>
                                <span class="dossier-val" id="vBranch">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Shipping Date:</span>
                                <span class="dossier-val" id="vShipDate">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Shipping Txn Date:</span>
                                <span class="dossier-val" id="vShipTxnDate">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Status:</span>
                                <span class="dossier-val" id="vStatus">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Shipment Mode:</span>
                                <span class="dossier-val" id="vShipment">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Delivery Terms:</span>
                                <span class="dossier-val" id="vDeliveryTerms">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Logistics Forwarder:</span>
                                <span class="dossier-val" id="vLogistics">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Consignee, Destination & Parties</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Buyer:</span>
                                <span class="dossier-val bold-text" id="vBuyer">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Bill To:</span>
                                <span class="dossier-val" id="vBillTo">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Attention:</span>
                                <span class="dossier-val" id="vAttention">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Notify Party:</span>
                                <span class="dossier-val" id="vNotify">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Destination Country:</span>
                                <span class="dossier-val" id="vDestination">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Customer P.O. Ref:</span>
                                <span class="dossier-val" id="vPoRef">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Payment Term:</span>
                                <span class="dossier-val" id="vPaymentTerm">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Total Amount:</span>
                                <span class="dossier-val bold-text" style="color: #2563eb;" id="vTotalAmount">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Items in View Dossier -->
                <div style="margin-top: 16px;">
                    <h4 style="font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Manifest Product Line Items</h4>
                    <div class="nested-table-wrap">
                        <table class="nested-erp-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>HS Code</th>
                                    <th>Product Code</th>
                                    <th>Description</th>
                                    <th>UOM</th>
                                    <th class="text-right">Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Gross Amt</th>
                                    <th class="text-right">Disc Amt</th>
                                    <th class="text-right">Final Amount</th>
                                    <th>DR Number</th>
                                    <th>SO Number</th>
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
                    <h4 class="dossier-card-title">Manifest Remarks & Customs Notes</h4>
                    <p id="vRemarks" style="font-size: 12.5px; color: #334155; margin: 0; line-height: 1.5;">-</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="printCurrentShippingInvoice()">
                    <i data-lucide="printer" class="btn-icon"></i>
                    <span>Print Invoice</span>
                </button>
                <button type="button" class="btn-primary" id="btnClsShipViewModal2">Close</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="shipDeleteModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-small">
            <div class="modal-header">
                <h2 class="modal-title" style="color: #dc2626;">Confirm Deletion</h2>
                <button type="button" class="modal-close-btn" id="btnClsShipDeleteModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13px; color: #334155; line-height: 1.5;">
                    Are you sure you want to delete shipping invoice <strong id="deleteShipNumPrompt"></strong>?
                </p>
                <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                    This record will be marked as archived with an audit timestamp in the system ledger.
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

    // Master Product Catalog
    const PRODUCT_MASTER = [
        { id: 'PROD-001', hsCode: '8542.31.00', code: 'ITM-8840', desc: 'Embedded Microcontroller PCB Rev 3.2', uom: 'PCS', price: 1250.00, weight: 0.15, grossWeight: 0.20, unitW: 'KG', unitG: 'KG', measurement: '0.002 CBM' },
        { id: 'PROD-002', hsCode: '8501.10.91', code: 'ITM-9210', desc: 'High-Torque Industrial Stepper Motor 24V', uom: 'UNIT', price: 3450.00, weight: 1.45, grossWeight: 1.70, unitW: 'KG', unitG: 'KG', measurement: '0.008 CBM' },
        { id: 'PROD-003', hsCode: '8517.62.90', code: 'ITM-4402', desc: 'Fiber Optic Transceiver Module SFP+ 10G', uom: 'BOX', price: 5800.00, weight: 0.40, grossWeight: 0.55, unitW: 'KG', unitG: 'KG', measurement: '0.004 CBM' },
        { id: 'PROD-004', hsCode: '7616.99.90', code: 'ITM-1055', desc: 'Precision CNC Machined Aluminum Enclosure', uom: 'PCS', price: 890.00, weight: 0.85, grossWeight: 1.05, unitW: 'KG', unitG: 'KG', measurement: '0.006 CBM' },
        { id: 'PROD-005', hsCode: '8536.49.00', code: 'ITM-7719', desc: 'Heavy Duty Industrial Power Relay 40A', uom: 'PCS', price: 620.00, weight: 0.25, grossWeight: 0.32, unitW: 'KG', unitG: 'KG', measurement: '0.003 CBM' }
    ];

    // State: Shipping Invoices
    let shippingInvoices = [
        {
            id: 'SHIP-001',
            shipInvNumber: 'SI-EXP-2026-001',
            branch: 'Head Office (Mandaluyong)',
            shippingDate: '2026-11-04',
            shippingTxnDate: '2026-11-04',
            buyer: 'Tokyo Electron Components Corp',
            billTo: 'Tokyo Electron Tokyo Procurement Center',
            attention: 'Kenji Sato (Import Lead)',
            notify: 'Nippon Express Air Cargo Haneda',
            notifyAttentionTo: 'Air Freight Clearing Team',
            currency: 'USD',
            rate: 58.50,
            countryOfOrigin: 'Philippines',
            countryOfDestination: 'Japan',
            paymentTerm: 'Net 30 Days',
            shipment: 'By Air',
            shipmentDate: '2026-11-05',
            shipmentRemarks: 'Direct flight JL742 MNL to HND. Fragile electronic subassemblies.',
            logistics: 'DHL Express International',
            vatCode: 'VAT_ZERO',
            deliveryTerms: 'FOB (Free on Board)',
            cNo: 'C/NO. 1 - 10',
            measurementTotal: '1.25 CBM / 10 Cartons',
            reprintCount: 1,
            status: 'Posted',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Documentation)',
            checkedBy: 'Maria Santos (QA)',
            approvedBy: 'Robert Tan (Export Manager)',
            refNumber: 'PO-TEC-8849',
            remarks: 'Commercial invoice for customs export clearance under PEZA transit permit # 9941-A.',
            items: [
                {
                    sortNumber: 1,
                    prodId: 'PROD-001',
                    hsCode: '8542.31.00',
                    code: 'ITM-8840',
                    desc: 'Embedded Microcontroller PCB Rev 3.2',
                    uom: 'PCS',
                    qty: 500,
                    weight: 75.0,
                    unitW: 'KG',
                    grossWeight: 100.0,
                    unitG: 'KG',
                    measurement: '0.65 CBM',
                    carton: 5,
                    sellingPrice: 22.00,
                    currency: 'USD',
                    vatRate: 0,
                    grossAmount: 11000.00,
                    discountRate: 0,
                    discAmount: 0.00,
                    netAmount: 11000.00,
                    amount: 11000.00,
                    drNumber: 'DR-2026-001',
                    drDate: '2026-11-04',
                    soNumber: 'SO-2026-001',
                    soRefNumber: 'PO-TEC-8849'
                },
                {
                    sortNumber: 2,
                    prodId: 'PROD-004',
                    hsCode: '7616.99.90',
                    code: 'ITM-1055',
                    desc: 'Precision CNC Machined Aluminum Enclosure',
                    uom: 'PCS',
                    qty: 500,
                    weight: 425.0,
                    unitW: 'KG',
                    grossWeight: 525.0,
                    unitG: 'KG',
                    measurement: '0.60 CBM',
                    carton: 5,
                    sellingPrice: 15.50,
                    currency: 'USD',
                    vatRate: 0,
                    grossAmount: 7750.00,
                    discountRate: 0,
                    discAmount: 0.00,
                    netAmount: 7750.00,
                    amount: 7750.00,
                    drNumber: 'DR-2026-001',
                    drDate: '2026-11-04',
                    soNumber: 'SO-2026-001',
                    soRefNumber: 'PO-TEC-8849'
                }
            ]
        },
        {
            id: 'SHIP-002',
            shipInvNumber: 'SI-DOM-2026-002',
            branch: 'Makati Sales Hub',
            shippingDate: '2026-11-05',
            shippingTxnDate: '2026-11-05',
            buyer: 'Apex Systems Mfg. Cavite',
            billTo: 'Apex Systems Accounting Head Office',
            attention: 'Elena Gomez (Logistics)',
            notify: 'Apex Cavite Receiving Dock',
            notifyAttentionTo: 'Dock Supervisor',
            currency: 'PHP',
            rate: 1.00,
            countryOfOrigin: 'Philippines',
            countryOfDestination: 'Philippines',
            paymentTerm: 'Net 30 Days',
            shipment: 'By Land',
            shipmentDate: '2026-11-06',
            shipmentRemarks: 'Dedicated logistics truck delivery (Plate # NFD-1122).',
            logistics: 'LBC Cargo Logistics',
            vatCode: 'VAT_12',
            deliveryTerms: 'DDP (Delivered Duty Paid)',
            cNo: 'C/NO. 1 - 4',
            measurementTotal: '0.85 CBM / 4 Cartons',
            reprintCount: 0,
            status: 'Posted',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Documentation)',
            checkedBy: 'Carlos Mendoza',
            approvedBy: 'Robert Tan (Export Manager)',
            refNumber: 'PO-APX-4420',
            remarks: 'Standard domestic supply with 12% output VAT applied.',
            items: [
                {
                    sortNumber: 1,
                    prodId: 'PROD-002',
                    hsCode: '8501.10.91',
                    code: 'ITM-9210',
                    desc: 'High-Torque Industrial Stepper Motor 24V',
                    uom: 'UNIT',
                    qty: 40,
                    weight: 58.0,
                    unitW: 'KG',
                    grossWeight: 68.0,
                    unitG: 'KG',
                    measurement: '0.85 CBM',
                    carton: 4,
                    sellingPrice: 3450.00,
                    currency: 'PHP',
                    vatRate: 12,
                    grossAmount: 138000.00,
                    discountRate: 5,
                    discAmount: 6900.00,
                    netAmount: 131100.00,
                    amount: 146832.00,
                    drNumber: 'DR-2026-002',
                    drDate: '2026-11-05',
                    soNumber: 'SO-2026-002',
                    soRefNumber: 'PO-APX-4420'
                }
            ]
        },
        {
            id: 'SHIP-003',
            shipInvNumber: 'SI-EXP-2026-003',
            branch: 'Laguna Plant Depot',
            shippingDate: '2026-11-06',
            shippingTxnDate: '2026-11-06',
            buyer: 'SingaOptics Pte Ltd',
            billTo: 'SingaOptics Finance Department',
            attention: 'David Lim (Procurement Director)',
            notify: 'Changi Air Cargo Logistics Terminal 3',
            notifyAttentionTo: 'Changi Inbound Hub',
            currency: 'USD',
            rate: 58.50,
            countryOfOrigin: 'Philippines',
            countryOfDestination: 'Singapore',
            paymentTerm: 'Net 60 Days',
            shipment: 'By Air',
            shipmentDate: '2026-11-08',
            shipmentRemarks: 'Air waybill # SQ-9912041 via Singapore Airlines Cargo.',
            logistics: 'FedEx Express Intl',
            vatCode: 'VAT_ZERO',
            deliveryTerms: 'CIF (Cost, Insurance & Freight)',
            cNo: 'C/NO. 1 - 2',
            measurementTotal: '0.35 CBM / 2 Boxes',
            reprintCount: 0,
            status: 'Not Yet Posted',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Documentation)',
            checkedBy: 'Carlos Mendoza',
            approvedBy: 'Pending Clearance',
            refNumber: 'PO-SGP-1099',
            remarks: 'Pending customs inspection clearance certificate.',
            items: [
                {
                    sortNumber: 1,
                    prodId: 'PROD-003',
                    hsCode: '8517.62.90',
                    code: 'ITM-4402',
                    desc: 'Fiber Optic Transceiver Module SFP+ 10G',
                    uom: 'BOX',
                    qty: 120,
                    weight: 48.0,
                    unitW: 'KG',
                    grossWeight: 66.0,
                    unitG: 'KG',
                    measurement: '0.35 CBM',
                    carton: 2,
                    sellingPrice: 105.00,
                    currency: 'USD',
                    vatRate: 0,
                    grossAmount: 12600.00,
                    discountRate: 0,
                    discAmount: 0.00,
                    netAmount: 12600.00,
                    amount: 12600.00,
                    drNumber: 'DR-2026-003',
                    drDate: '2026-11-06',
                    soNumber: 'SO-2026-003',
                    soRefNumber: 'PO-SGP-1099'
                }
            ]
        },
        {
            id: 'SHIP-004',
            shipInvNumber: 'SI-EXP-2026-004',
            branch: 'Cebu Logistics Depot',
            shippingDate: '2026-11-02',
            shippingTxnDate: '2026-11-02',
            buyer: 'Vanguard Global Marine Corp',
            billTo: 'Vanguard Global Accounting',
            attention: 'Robert Evans',
            notify: 'Busan Container Terminal Gate 2',
            notifyAttentionTo: 'Consignee Receiver',
            currency: 'USD',
            rate: 58.50,
            countryOfOrigin: 'Philippines',
            countryOfDestination: 'South Korea',
            paymentTerm: 'Letter of Credit (L/C)',
            shipment: 'By Sea / Boat',
            shipmentDate: '2026-11-03',
            shipmentRemarks: 'Container seal # KR-0048892.',
            logistics: 'Maersk Ocean Lines',
            vatCode: 'VAT_ZERO',
            deliveryTerms: 'FOB (Free on Board)',
            cNo: 'C/NO. 1 - 20',
            measurementTotal: '8.40 CBM / 20 Cartons',
            reprintCount: 2,
            status: 'Cancelled',
            deletedDate: '-',
            preparedBy: 'Jonathan Reyes',
            checkedBy: 'Carlos Mendoza',
            approvedBy: 'Robert Tan (Export Manager)',
            refNumber: 'PO-VG-0012',
            remarks: 'Cancelled due to ocean freight vessel voyage cancellation.',
            items: [
                {
                    sortNumber: 1,
                    prodId: 'PROD-005',
                    hsCode: '8536.49.00',
                    code: 'ITM-7719',
                    desc: 'Heavy Duty Industrial Power Relay 40A',
                    uom: 'PCS',
                    qty: 500,
                    weight: 125.0,
                    unitW: 'KG',
                    grossWeight: 160.0,
                    unitG: 'KG',
                    measurement: '8.40 CBM',
                    carton: 20,
                    sellingPrice: 11.20,
                    currency: 'USD',
                    vatRate: 0,
                    grossAmount: 5600.00,
                    discountRate: 0,
                    discAmount: 0.00,
                    netAmount: 5600.00,
                    amount: 5600.00,
                    drNumber: 'DR-2026-004',
                    drDate: '2026-11-02',
                    soNumber: 'SO-2026-004',
                    soRefNumber: 'PO-VG-0012'
                }
            ]
        }
    ];

    let selectedShipIds = new Set();
    let currentDeleteId = null;

    // DOM Elements
    const shipTableBody = document.getElementById('shipTableBody');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountText = document.getElementById('selectedCountText');
    const btnBulkDelete = document.getElementById('btnBulkDelete');

    // Filters
    const shipSearchInput = document.getElementById('shipSearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterBranch = document.getElementById('filterBranch');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    const btnResetFilters = document.getElementById('btnResetFilters');

    // KPI Elements
    const kpiTotalInvoices = document.getElementById('kpiTotalInvoices');
    const kpiActiveInvoicesSub = document.getElementById('kpiActiveInvoicesSub');
    const kpiTotalValue = document.getElementById('kpiTotalValue');
    const kpiPostedCount = document.getElementById('kpiPostedCount');
    const kpiPendingCount = document.getElementById('kpiPendingCount');
    const shipCountBadge = document.getElementById('shipCountBadge');
    const paginationMeta = document.getElementById('paginationMeta');

    // Modals
    const shipModal = document.getElementById('shipModal');
    const shipForm = document.getElementById('shipForm');
    const shipModalTitle = document.getElementById('shipModalTitle');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnClsShipModal = document.getElementById('btnClsShipModal');
    const btnCancelShipModal = document.getElementById('btnCancelShipModal');
    const shipItemsBody = document.getElementById('shipItemsBody');
    const btnAddGridRow = document.getElementById('btnAddGridRow');

    // View Modal
    const shipViewModal = document.getElementById('shipViewModal');
    const btnClsShipViewModal = document.getElementById('btnClsShipViewModal');
    const btnClsShipViewModal2 = document.getElementById('btnClsShipViewModal2');

    // Delete Modal
    const shipDeleteModal = document.getElementById('shipDeleteModal');
    const deleteShipNumPrompt = document.getElementById('deleteShipNumPrompt');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const btnClsShipDeleteModal = document.getElementById('btnClsShipDeleteModal');

    // Export & Refresh
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportMenu = document.getElementById('exportMenu');
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');

    // Initialize Default Dates
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('formShippingDate').value = todayStr;
    document.getElementById('formShippingTxnDate').value = todayStr;
    document.getElementById('formShipmentDate').value = todayStr;

    // Render Table Function
    function renderTable() {
        const query = (shipSearchInput.value || '').trim().toLowerCase();
        const statusFilter = filterStatus.value;
        const branchFilter = filterBranch.value;
        const dateFrom = filterDateFrom.value;
        const dateTo = filterDateTo.value;

        const filtered = shippingInvoices.filter(si => {
            if (query) {
                const matchQuery =
                    si.shipInvNumber.toLowerCase().includes(query) ||
                    si.buyer.toLowerCase().includes(query) ||
                    si.billTo.toLowerCase().includes(query) ||
                    (si.refNumber && si.refNumber.toLowerCase().includes(query)) ||
                    si.branch.toLowerCase().includes(query);
                if (!matchQuery) return false;
            }

            if (statusFilter !== 'ALL' && si.status !== statusFilter) {
                return false;
            }

            if (branchFilter !== 'ALL' && si.branch !== branchFilter) {
                return false;
            }

            if (dateFrom && si.shippingDate < dateFrom) {
                return false;
            }

            if (dateTo && si.shippingDate > dateTo) {
                return false;
            }

            return true;
        });

        updateKPIs(shippingInvoices);

        shipTableBody.innerHTML = '';

        if (filtered.length === 0) {
            shipTableBody.innerHTML = `
                <tr>
                    <td colspan="17" style="text-align: center; padding: 36px; color: #64748b;">
                        <i data-lucide="inbox" style="width: 28px; height: 28px; margin-bottom: 6px; stroke-width: 1.5; color: #94a3b8;"></i>
                        <p style="font-size: 13px; font-weight: 500;">No shipping invoices found matching your criteria.</p>
                        <span style="font-size: 11.5px; color: #94a3b8;">Try clearing filters or search terms.</span>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            shipCountBadge.textContent = '0 Records';
            paginationMeta.textContent = 'Showing 0 of 0 entries';
            return;
        }

        filtered.forEach(si => {
            const tr = document.createElement('tr');
            if (si.deletedDate !== '-') {
                tr.classList.add('row-deleted');
            }

            const isChecked = selectedShipIds.has(si.id);
            if (isChecked) {
                tr.classList.add('row-selected');
            }

            // Calculate totals
            const totals = calculateInvoiceTotals(si);

            let statusBadgeClass = 'status-pill-pending';
            if (si.status === 'Posted') statusBadgeClass = 'status-pill-posted';
            else if (si.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';

            const currSymbol = si.currency === 'USD' ? '$' : (si.currency === 'EUR' ? '€' : (si.currency === 'JPY' ? '¥' : '₱'));

            tr.innerHTML = `
                <td style="width: 40px; text-align: center;" class="no-print">
                    <input type="checkbox" class="erp-cb row-checkbox" data-id="${si.id}" ${isChecked ? 'checked' : ''}>
                </td>
                <td class="font-bold font-code" style="min-width: 140px;">
                    <a href="javascript:void(0)" onclick="viewShippingInvoice('${si.id}')" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                        ${escapeHtml(si.shipInvNumber)}
                    </a>
                </td>
                <td style="min-width: 170px;">${escapeHtml(si.branch)}</td>
                <td style="min-width: 200px; font-weight: 500;">${escapeHtml(si.buyer)}</td>
                <td style="min-width: 200px;">${escapeHtml(si.billTo)}</td>
                <td class="font-numeric" style="min-width: 110px;">${escapeHtml(si.shippingDate)}</td>
                <td class="font-numeric" style="min-width: 110px;">${escapeHtml(si.shippingTxnDate)}</td>
                <td style="min-width: 100px;">${escapeHtml(si.shipment)}</td>
                <td class="text-center font-bold font-code" style="min-width: 80px;">${si.currency}</td>
                <td class="text-right font-numeric" style="min-width: 120px;">${currSymbol}${totals.gross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric" style="min-width: 110px; color: #dc2626;">${currSymbol}${totals.discount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric" style="min-width: 120px;">${currSymbol}${totals.net.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric" style="min-width: 110px;">${currSymbol}${totals.vat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric font-bold" style="min-width: 130px; color: #0f172a;">${currSymbol}${totals.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-center" style="min-width: 130px;">
                    <span class="status-pill ${statusBadgeClass}">
                        ${escapeHtml(si.status)}
                    </span>
                </td>
                <td class="text-center font-numeric text-muted" style="min-width: 110px; font-size: 11.5px;">
                    ${escapeHtml(si.deletedDate)}
                </td>
                <td class="actions-cell text-center no-print" style="width: 70px;">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '${si.id}')" title="Actions">
                        <i data-lucide="more-horizontal" style="width: 15px; height: 15px;"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${si.id}">
                        <button type="button" class="action-menu-item" onclick="viewShippingInvoice('${si.id}')">
                            <i data-lucide="eye"></i>
                            <span>View Dossier</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editShippingInvoice('${si.id}')">
                            <i data-lucide="edit"></i>
                            <span>Edit Invoice</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteShip('${si.id}')">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </td>
            `;

            shipTableBody.appendChild(tr);
        });

        shipCountBadge.textContent = `${filtered.length} Records`;
        paginationMeta.textContent = `Showing 1 to ${filtered.length} of ${filtered.length} entries`;

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        updateSelectAllState(filtered);
    }

    function calculateInvoiceTotals(invoice) {
        let gross = 0;
        let discount = 0;
        let net = 0;
        let vat = 0;
        let total = 0;

        (invoice.items || []).forEach(it => {
            const g = Number(it.grossAmount) || (Number(it.qty) * Number(it.sellingPrice)) || 0;
            const d = Number(it.discAmount) || 0;
            const n = g - d;
            const vRate = invoice.vatCode === 'VAT_12' ? 0.12 : 0;
            const v = n * vRate;
            gross += g;
            discount += d;
            net += n;
            vat += v;
            total += (n + v);
        });

        return { gross, discount, net, vat, total };
    }

    function updateKPIs(data) {
        const active = data.filter(d => d.deletedDate === '-');
        kpiTotalInvoices.textContent = data.length;
        kpiActiveInvoicesSub.textContent = `${active.length} active records`;

        let totalPHP = 0;
        let posted = 0;
        let pending = 0;

        active.forEach(si => {
            const totals = calculateInvoiceTotals(si);
            const rate = Number(si.rate) || 1.0;
            totalPHP += totals.total * rate;
            if (si.status === 'Posted') posted++;
            else if (si.status === 'Not Yet Posted') pending++;
        });

        kpiTotalValue.textContent = '₱' + totalPHP.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        kpiPostedCount.textContent = posted;
        kpiPendingCount.textContent = pending;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // Toggle Action Menu with drop-up/drop-down safe positioning
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

    document.addEventListener('click', function (e) {
        document.querySelectorAll('.action-menu-dropdown').forEach(m => m.classList.remove('show'));
        if (exportDropdownWrapper && !exportDropdownWrapper.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
    });

    // Checkboxes & Bulk Actions
    shipTableBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-checkbox')) {
            const id = e.target.getAttribute('data-id');
            if (e.target.checked) {
                selectedShipIds.add(id);
            } else {
                selectedShipIds.delete(id);
            }
            updateBulkActionBar();
        }
    });

    selectAllCheckbox.addEventListener('change', function () {
        const checkboxes = shipTableBody.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
            const id = cb.getAttribute('data-id');
            if (selectAllCheckbox.checked) {
                selectedShipIds.add(id);
            } else {
                selectedShipIds.delete(id);
            }
        });
        updateBulkActionBar();
    });

    function updateSelectAllState(currentFilteredList) {
        const allVisibleSelected = currentFilteredList.length > 0 &&
            currentFilteredList.every(si => selectedShipIds.has(si.id));
        selectAllCheckbox.checked = allVisibleSelected;
        updateBulkActionBar();
    }

    function updateBulkActionBar() {
        if (selectedShipIds.size > 0) {
            bulkActionBar.classList.add('show');
            selectedCountText.textContent = `${selectedShipIds.size} selected`;
        } else {
            bulkActionBar.classList.remove('show');
        }
    }

    btnBulkDelete.addEventListener('click', function () {
        if (selectedShipIds.size === 0) return;
        if (confirm(`Are you sure you want to delete ${selectedShipIds.size} selected shipping invoice(s)?`)) {
            const nowTime = new Date().toLocaleString();
            shippingInvoices.forEach(si => {
                if (selectedShipIds.has(si.id)) {
                    si.deletedDate = nowTime;
                }
            });
            selectedShipIds.clear();
            renderTable();
        }
    });

    // Filters Listeners
    [shipSearchInput, filterStatus, filterBranch, filterDateFrom, filterDateTo].forEach(el => {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    btnResetFilters.addEventListener('click', function () {
        shipSearchInput.value = '';
        filterStatus.value = 'ALL';
        filterBranch.value = 'ALL';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        selectedShipIds.clear();
        renderTable();
    });

    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        setTimeout(() => {
            refreshIcon.classList.remove('spin-anim');
            renderTable();
        }, 500);
    });

    btnExportToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        exportMenu.classList.toggle('show');
    });

    window.exportShippingInvoices = function (type) {
        exportMenu.classList.remove('show');
        if (type === 'pdf') {
            window.print();
        } else if (type === 'csv' || type === 'excel') {
            downloadCSV();
        }
    };

    function downloadCSV() {
        let csv = 'Shipping Inv#,Branch,Buyer,Bill To,Shipping Date,Txn Date,Shipment,Currency,Rate,Gross Amount,Disc Amount,Net Amount,VAT Amount,Total Amount,Status,Deleted Date\n';
        shippingInvoices.forEach(si => {
            const totals = calculateInvoiceTotals(si);
            csv += `"${si.shipInvNumber}","${si.branch}","${si.buyer}","${si.billTo}","${si.shippingDate}","${si.shippingTxnDate}","${si.shipment}","${si.currency}",${si.rate},${totals.gross},${totals.discount},${totals.net},${totals.vat},${totals.total},"${si.status}","${si.deletedDate}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Shipping_Invoices_${todayStr}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }

    // Modal Create / Edit
    btnOpenCreateModal.addEventListener('click', function () {
        shipForm.reset();
        document.getElementById('formShipId').value = '';
        shipModalTitle.textContent = 'Create Shipping Invoice';
        document.getElementById('btnSaveShipText').textContent = 'Save Shipping Invoice';
        document.getElementById('formShippingDate').value = todayStr;
        document.getElementById('formShippingTxnDate').value = todayStr;
        document.getElementById('formShipmentDate').value = todayStr;
        document.getElementById('formPreparedBy').value = 'Karl Burgos (Documentation)';
        document.getElementById('dispReprintCount').textContent = '0';
        shipItemsBody.innerHTML = '';

        addGridRow();
        calculateAllFinancials();

        shipModal.classList.add('show');
        shipModal.setAttribute('aria-hidden', 'false');
    });

    function closeShipModal() {
        shipModal.classList.remove('show');
        shipModal.setAttribute('aria-hidden', 'true');
    }
    btnClsShipModal.addEventListener('click', closeShipModal);
    btnCancelShipModal.addEventListener('click', closeShipModal);

    // Dynamic Line Items Grid
    function addGridRow(itemData = null) {
        const rowIndex = shipItemsBody.children.length + 1;
        const tr = document.createElement('tr');

        const defaultProd = PRODUCT_MASTER[0];
        const selectedProdId = itemData ? itemData.prodId : defaultProd.id;
        const selectedProd = PRODUCT_MASTER.find(p => p.id === selectedProdId) || defaultProd;

        let optionsHtml = '';
        PRODUCT_MASTER.forEach(p => {
            optionsHtml += `<option value="${p.id}" ${p.id === selectedProdId ? 'selected' : ''}>${p.id} (${p.code})</option>`;
        });

        const activeCurrency = document.getElementById('formCurrency').value || 'USD';
        const isVat12 = document.getElementById('formVatCode').value === 'VAT_12';

        const qty = itemData ? itemData.qty : 100;
        const price = itemData ? itemData.sellingPrice : selectedProd.price;
        const discRate = itemData ? (itemData.discountRate || 0) : 0;
        const gross = qty * price;
        const discAmt = itemData ? itemData.discAmount : (gross * (discRate / 100));
        const net = gross - discAmt;
        const vatRate = isVat12 ? 12 : 0;
        const vatAmt = net * (vatRate / 100);
        const finalAmt = net + vatAmt;

        tr.innerHTML = `
            <td style="text-align: center; font-weight: 600;" class="row-seq">${rowIndex}</td>
            <td>
                <select class="grid-cell-input row-prod-id" onchange="onProductSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td>
                <input type="text" class="grid-cell-input row-hs-code" value="${itemData ? itemData.hsCode : selectedProd.hsCode}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-prod-code" value="${itemData ? itemData.code : selectedProd.code}" readonly>
            </td>
            <td>
                <input type="text" class="grid-cell-input row-desc" value="${itemData ? itemData.desc : selectedProd.desc}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-uom" value="${itemData ? itemData.uom : selectedProd.uom}">
            </td>
            <td>
                <input type="number" class="grid-cell-input row-qty" style="text-align: right; font-weight: 600;" value="${qty}" min="1" oninput="recomputeRow(this)">
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-weight" style="text-align: right;" value="${itemData ? itemData.weight : (selectedProd.weight * qty).toFixed(2)}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-unit-w" value="${itemData ? itemData.unitW : selectedProd.unitW}">
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-gross-weight" style="text-align: right;" value="${itemData ? itemData.grossWeight : (selectedProd.grossWeight * qty).toFixed(2)}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-unit-g" value="${itemData ? itemData.unitG : selectedProd.unitG}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-measurement" value="${itemData ? itemData.measurement : selectedProd.measurement}">
            </td>
            <td>
                <input type="number" class="grid-cell-input row-carton" style="text-align: right;" value="${itemData ? itemData.carton : Math.ceil(qty / 100)}">
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-price" style="text-align: right; font-weight: 600;" value="${price}" oninput="recomputeRow(this)">
            </td>
            <td style="text-align: center;">
                <span class="row-curr font-code font-bold">${itemData ? itemData.currency : activeCurrency}</span>
            </td>
            <td>
                <input type="number" class="grid-cell-input row-vat-rate" style="text-align: right;" value="${vatRate}" readonly>
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-gross" style="text-align: right;" value="${gross.toFixed(2)}" readonly>
            </td>
            <td>
                <input type="number" step="0.1" class="grid-cell-input row-disc-rate" style="text-align: right;" value="${discRate}" oninput="recomputeRow(this)">
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-disc-amt" style="text-align: right;" value="${discAmt.toFixed(2)}" oninput="recomputeRow(this, true)">
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-net" style="text-align: right;" value="${net.toFixed(2)}" readonly>
            </td>
            <td>
                <input type="number" step="0.01" class="grid-cell-input row-amount font-bold" style="text-align: right; color: #2563eb;" value="${finalAmt.toFixed(2)}" readonly>
            </td>
            <td>
                <input type="text" class="grid-cell-input row-dr-no" value="${itemData ? itemData.drNumber : 'DR-2026-001'}">
            </td>
            <td>
                <input type="date" class="grid-cell-input row-dr-date" value="${itemData ? itemData.drDate : todayStr}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-so-no" value="${itemData ? itemData.soNumber : 'SO-2026-001'}">
            </td>
            <td>
                <input type="text" class="grid-cell-input row-so-ref" value="${itemData ? itemData.soRefNumber : (document.getElementById('formRefNumber').value || 'PO-EXP-001')}">
            </td>
            <td style="text-align: center;">
                <button type="button" class="btn-del-mini" onclick="removeGridRow(this)" title="Remove line">
                    <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                </button>
            </td>
        `;

        shipItemsBody.appendChild(tr);
        if (typeof lucide !== 'undefined') lucide.createIcons();
        resequenceRows();
        calculateAllFinancials();
    }

    btnAddGridRow.addEventListener('click', function () {
        addGridRow();
    });

    window.removeGridRow = function (btn) {
        if (shipItemsBody.children.length <= 1) {
            alert('A shipping invoice must contain at least one product line.');
            return;
        }
        btn.closest('tr').remove();
        resequenceRows();
        calculateAllFinancials();
    };

    function resequenceRows() {
        const rows = shipItemsBody.querySelectorAll('tr');
        rows.forEach((r, idx) => {
            const seq = r.querySelector('.row-seq');
            if (seq) seq.textContent = idx + 1;
        });
    }

    window.onProductSelectChange = function (selectEl) {
        const prodId = selectEl.value;
        const prod = PRODUCT_MASTER.find(p => p.id === prodId);
        if (prod) {
            const row = selectEl.closest('tr');
            row.querySelector('.row-hs-code').value = prod.hsCode;
            row.querySelector('.row-prod-code').value = prod.code;
            row.querySelector('.row-desc').value = prod.desc;
            row.querySelector('.row-uom').value = prod.uom;
            row.querySelector('.row-price').value = prod.price;
            row.querySelector('.row-unit-w').value = prod.unitW;
            row.querySelector('.row-unit-g').value = prod.unitG;
            row.querySelector('.row-measurement').value = prod.measurement;

            const qty = Number(row.querySelector('.row-qty').value) || 1;
            row.querySelector('.row-weight').value = (prod.weight * qty).toFixed(2);
            row.querySelector('.row-gross-weight').value = (prod.grossWeight * qty).toFixed(2);

            recomputeRow(row.querySelector('.row-qty'));
        }
    };

    window.recomputeRow = function (inputEl, fromDiscAmt = false) {
        const row = inputEl.closest('tr');
        const qty = Number(row.querySelector('.row-qty').value) || 0;
        const price = Number(row.querySelector('.row-price').value) || 0;
        const isVat12 = document.getElementById('formVatCode').value === 'VAT_12';
        const vatRate = isVat12 ? 12 : 0;

        const gross = qty * price;
        row.querySelector('.row-gross').value = gross.toFixed(2);

        let discRate = Number(row.querySelector('.row-disc-rate').value) || 0;
        let discAmt = Number(row.querySelector('.row-disc-amt').value) || 0;

        if (fromDiscAmt) {
            discRate = gross > 0 ? ((discAmt / gross) * 100) : 0;
            row.querySelector('.row-disc-rate').value = discRate.toFixed(1);
        } else {
            discAmt = gross * (discRate / 100);
            row.querySelector('.row-disc-amt').value = discAmt.toFixed(2);
        }

        const net = Math.max(0, gross - discAmt);
        const vatAmt = net * (vatRate / 100);
        const finalAmt = net + vatAmt;

        row.querySelector('.row-vat-rate').value = vatRate;
        row.querySelector('.row-net').value = net.toFixed(2);
        row.querySelector('.row-amount').value = finalAmt.toFixed(2);

        calculateAllFinancials();
    };

    window.calculateAllFinancials = function () {
        const currency = document.getElementById('formCurrency').value || 'USD';
        const isVat12 = document.getElementById('formVatCode').value === 'VAT_12';
        const vatRate = isVat12 ? 12 : 0;

        const rows = shipItemsBody.querySelectorAll('tr');
        let totalGross = 0;
        let totalDiscount = 0;
        let totalNet = 0;
        let totalVat = 0;
        let totalFinal = 0;

        rows.forEach(r => {
            const currEl = r.querySelector('.row-curr');
            if (currEl) currEl.textContent = currency;

            const vatRateInput = r.querySelector('.row-vat-rate');
            if (vatRateInput) vatRateInput.value = vatRate;

            const g = Number(r.querySelector('.row-gross')?.value) || 0;
            const d = Number(r.querySelector('.row-disc-amt')?.value) || 0;
            const n = Math.max(0, g - d);
            const v = n * (vatRate / 100);
            const a = n + v;

            const netInput = r.querySelector('.row-net');
            const amtInput = r.querySelector('.row-amount');
            if (netInput) netInput.value = n.toFixed(2);
            if (amtInput) amtInput.value = a.toFixed(2);

            totalGross += g;
            totalDiscount += d;
            totalNet += n;
            totalVat += v;
            totalFinal += a;
        });

        document.getElementById('calcGrossAmount').textContent = totalGross.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcDiscountAmount').textContent = totalDiscount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcNetAmount').textContent = totalNet.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcVatAmount').textContent = totalVat.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        document.getElementById('calcTotalAmount').textContent = totalFinal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // Form Submit
    shipForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const editId = document.getElementById('formShipId').value;
        const branch = document.getElementById('formBranch').value;
        const shipInvNumber = document.getElementById('formShipInvNumber').value.trim();
        const shippingDate = document.getElementById('formShippingDate').value;
        const shippingTxnDate = document.getElementById('formShippingTxnDate').value;
        const status = document.getElementById('formStatus').value;
        const buyer = document.getElementById('formBuyer').value.trim();
        const billTo = document.getElementById('formBillTo').value.trim();
        const attention = document.getElementById('formAttention').value.trim();
        const notify = document.getElementById('formNotify').value.trim();
        const notifyAttentionTo = document.getElementById('formNotifyAttentionTo').value.trim();
        const refNumber = document.getElementById('formRefNumber').value.trim();
        const currency = document.getElementById('formCurrency').value;
        const rate = Number(document.getElementById('formRate').value) || 1.0;
        const countryOfOrigin = document.getElementById('formCountryOfOrigin').value.trim();
        const countryOfDestination = document.getElementById('formCountryOfDestination').value.trim();
        const paymentTerm = document.getElementById('formPaymentTerm').value;
        const shipment = document.getElementById('formShipment').value;
        const shipmentDate = document.getElementById('formShipmentDate').value;
        const logistics = document.getElementById('formLogistics').value.trim();
        const deliveryTerms = document.getElementById('formDeliveryTerms').value;
        const vatCode = document.getElementById('formVatCode').value;
        const cNo = document.getElementById('formCNo').value.trim();
        const measurementTotal = document.getElementById('formMeasurementTotal').value.trim();
        const shipmentRemarks = document.getElementById('formShipmentRemarks').value.trim();
        const preparedBy = document.getElementById('formPreparedBy').value.trim();
        const checkedBy = document.getElementById('formCheckedBy').value.trim();
        const approvedBy = document.getElementById('formApprovedBy').value.trim();
        const remarks = document.getElementById('formRemarks').value.trim();

        const rows = shipItemsBody.querySelectorAll('tr');
        const items = [];
        rows.forEach((r, idx) => {
            items.push({
                sortNumber: idx + 1,
                prodId: r.querySelector('.row-prod-id').value,
                hsCode: r.querySelector('.row-hs-code').value,
                code: r.querySelector('.row-prod-code').value,
                desc: r.querySelector('.row-desc').value,
                uom: r.querySelector('.row-uom').value,
                qty: Number(r.querySelector('.row-qty').value) || 0,
                weight: Number(r.querySelector('.row-weight').value) || 0,
                unitW: r.querySelector('.row-unit-w').value,
                grossWeight: Number(r.querySelector('.row-gross-weight').value) || 0,
                unitG: r.querySelector('.row-unit-g').value,
                measurement: r.querySelector('.row-measurement').value,
                carton: Number(r.querySelector('.row-carton').value) || 0,
                sellingPrice: Number(r.querySelector('.row-price').value) || 0,
                currency: currency,
                vatRate: Number(r.querySelector('.row-vat-rate').value) || 0,
                grossAmount: Number(r.querySelector('.row-gross').value) || 0,
                discountRate: Number(r.querySelector('.row-disc-rate').value) || 0,
                discAmount: Number(r.querySelector('.row-disc-amt').value) || 0,
                netAmount: Number(r.querySelector('.row-net').value) || 0,
                amount: Number(r.querySelector('.row-amount').value) || 0,
                drNumber: r.querySelector('.row-dr-no').value,
                drDate: r.querySelector('.row-dr-date').value,
                soNumber: r.querySelector('.row-so-no').value,
                soRefNumber: r.querySelector('.row-so-ref').value
            });
        });

        if (editId) {
            const idx = shippingInvoices.findIndex(s => s.id === editId);
            if (idx !== -1) {
                shippingInvoices[idx] = {
                    ...shippingInvoices[idx],
                    branch,
                    shipInvNumber,
                    shippingDate,
                    shippingTxnDate,
                    status,
                    buyer,
                    billTo,
                    attention,
                    notify,
                    notifyAttentionTo,
                    refNumber,
                    currency,
                    rate,
                    countryOfOrigin,
                    countryOfDestination,
                    paymentTerm,
                    shipment,
                    shipmentDate,
                    logistics,
                    deliveryTerms,
                    vatCode,
                    cNo,
                    measurementTotal,
                    shipmentRemarks,
                    preparedBy,
                    checkedBy,
                    approvedBy,
                    remarks,
                    items
                };
            }
        } else {
            const newId = 'SHIP-' + Date.now().toString().slice(-4);
            shippingInvoices.unshift({
                id: newId,
                shipInvNumber,
                branch,
                shippingDate,
                shippingTxnDate,
                status,
                buyer,
                billTo,
                attention,
                notify,
                notifyAttentionTo,
                refNumber,
                currency,
                rate,
                countryOfOrigin,
                countryOfDestination,
                paymentTerm,
                shipment,
                shipmentDate,
                logistics,
                deliveryTerms,
                vatCode,
                cNo,
                measurementTotal,
                shipmentRemarks,
                preparedBy,
                checkedBy,
                approvedBy,
                remarks,
                reprintCount: 0,
                deletedDate: '-',
                items
            });
        }

        closeShipModal();
        renderTable();
    });

    // View Dossier
    window.viewShippingInvoice = function (id) {
        const si = shippingInvoices.find(s => s.id === id);
        if (!si) return;

        document.getElementById('viewShipTitle').textContent = `Shipping Invoice: ${si.shipInvNumber}`;
        document.getElementById('viewShipSubtitle').textContent = `Export & Domestic Manifest — ${si.branch}`;

        document.getElementById('vShipInvNum').textContent = si.shipInvNumber;
        document.getElementById('vBranch').textContent = si.branch;
        document.getElementById('vShipDate').textContent = si.shippingDate;
        document.getElementById('vShipTxnDate').textContent = si.shippingTxnDate;
        document.getElementById('vStatus').textContent = si.status;
        document.getElementById('vShipment').textContent = si.shipment;
        document.getElementById('vDeliveryTerms').textContent = si.deliveryTerms;
        document.getElementById('vLogistics').textContent = si.logistics;

        document.getElementById('vBuyer').textContent = si.buyer;
        document.getElementById('vBillTo').textContent = si.billTo;
        document.getElementById('vAttention').textContent = si.attention || 'N/A';
        document.getElementById('vNotify').textContent = si.notify || 'Same as Consignee';
        document.getElementById('vDestination').textContent = si.countryOfDestination;
        document.getElementById('vPoRef').textContent = si.refNumber || 'N/A';
        document.getElementById('vPaymentTerm').textContent = si.paymentTerm;

        const totals = calculateInvoiceTotals(si);
        const currSymbol = si.currency === 'USD' ? '$' : (si.currency === 'EUR' ? '€' : (si.currency === 'JPY' ? '¥' : '₱'));
        document.getElementById('vTotalAmount').textContent = `${currSymbol}${totals.total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} (${si.currency})`;

        document.getElementById('vRemarks').textContent = si.remarks || 'No special remarks recorded.';

        const vItemsBody = document.getElementById('vItemsBody');
        vItemsBody.innerHTML = '';
        (si.items || []).forEach(it => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="font-weight: 600;">${it.sortNumber}</td>
                <td class="font-code">${escapeHtml(it.hsCode || '-')}</td>
                <td class="font-code font-bold" style="color: #2563eb;">${escapeHtml(it.code)}</td>
                <td>${escapeHtml(it.desc)}</td>
                <td>${escapeHtml(it.uom)}</td>
                <td class="text-right font-numeric">${(it.qty || 0).toLocaleString()}</td>
                <td class="text-right font-numeric">${currSymbol}${(it.sellingPrice || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric">${currSymbol}${(it.grossAmount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric" style="color: #dc2626;">${currSymbol}${(it.discAmount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td class="text-right font-numeric font-bold">${currSymbol}${(it.amount || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                <td>${escapeHtml(it.drNumber || '-')}</td>
                <td>${escapeHtml(it.soNumber || '-')}</td>
            `;
            vItemsBody.appendChild(tr);
        });

        shipViewModal.classList.add('show');
        shipViewModal.setAttribute('aria-hidden', 'false');
    };

    function closeShipViewModal() {
        shipViewModal.classList.remove('show');
        shipViewModal.setAttribute('aria-hidden', 'true');
    }
    btnClsShipViewModal.addEventListener('click', closeShipViewModal);
    btnClsShipViewModal2.addEventListener('click', closeShipViewModal);

    window.printCurrentShippingInvoice = function () {
        window.print();
    };

    // Edit Shipping Invoice
    window.editShippingInvoice = function (id) {
        const si = shippingInvoices.find(s => s.id === id);
        if (!si) return;

        document.getElementById('formShipId').value = si.id;
        shipModalTitle.textContent = `Edit Shipping Invoice: ${si.shipInvNumber}`;
        document.getElementById('btnSaveShipText').textContent = 'Update Shipping Invoice';

        document.getElementById('formBranch').value = si.branch;
        document.getElementById('formShipInvNumber').value = si.shipInvNumber;
        document.getElementById('formShippingDate').value = si.shippingDate;
        document.getElementById('formShippingTxnDate').value = si.shippingTxnDate;
        document.getElementById('formStatus').value = si.status;
        document.getElementById('formBuyer').value = si.buyer;
        document.getElementById('formBillTo').value = si.billTo;
        document.getElementById('formAttention').value = si.attention || '';
        document.getElementById('formNotify').value = si.notify || '';
        document.getElementById('formNotifyAttentionTo').value = si.notifyAttentionTo || '';
        document.getElementById('formRefNumber').value = si.refNumber || '';
        document.getElementById('formCurrency').value = si.currency;
        document.getElementById('formRate').value = si.rate;
        document.getElementById('formCountryOfOrigin').value = si.countryOfOrigin;
        document.getElementById('formCountryOfDestination').value = si.countryOfDestination;
        document.getElementById('formPaymentTerm').value = si.paymentTerm;
        document.getElementById('formShipment').value = si.shipment;
        document.getElementById('formShipmentDate').value = si.shipmentDate;
        document.getElementById('formLogistics').value = si.logistics;
        document.getElementById('formDeliveryTerms').value = si.deliveryTerms;
        document.getElementById('formVatCode').value = si.vatCode;
        document.getElementById('formCNo').value = si.cNo || '';
        document.getElementById('formMeasurementTotal').value = si.measurementTotal || '';
        document.getElementById('formShipmentRemarks').value = si.shipmentRemarks || '';
        document.getElementById('formPreparedBy').value = si.preparedBy;
        document.getElementById('formCheckedBy').value = si.checkedBy || '';
        document.getElementById('formApprovedBy').value = si.approvedBy || '';
        document.getElementById('formRemarks').value = si.remarks || '';
        document.getElementById('dispReprintCount').textContent = si.reprintCount || 0;

        shipItemsBody.innerHTML = '';
        if (si.items && si.items.length > 0) {
            si.items.forEach(it => {
                addGridRow(it);
            });
        } else {
            addGridRow();
        }
        calculateAllFinancials();

        shipModal.classList.add('show');
        shipModal.setAttribute('aria-hidden', 'false');
    };

    // Delete Prompt
    window.promptDeleteShip = function (id) {
        const si = shippingInvoices.find(s => s.id === id);
        if (!si) return;
        currentDeleteId = si.id;
        deleteShipNumPrompt.textContent = si.shipInvNumber;
        shipDeleteModal.classList.add('show');
        shipDeleteModal.setAttribute('aria-hidden', 'false');
    };

    function closeDeleteModal() {
        shipDeleteModal.classList.remove('show');
        shipDeleteModal.setAttribute('aria-hidden', 'true');
        currentDeleteId = null;
    }
    btnCancelDelete.addEventListener('click', closeDeleteModal);
    btnClsShipDeleteModal.addEventListener('click', closeDeleteModal);

    btnConfirmDelete.addEventListener('click', function () {
        if (!currentDeleteId) return;
        const target = shippingInvoices.find(s => s.id === currentDeleteId);
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

    // Initial table render
    renderTable();
});
</script>
@endpush
@endsection

