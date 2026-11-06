@extends('layouts.app')

@section('title', 'Sales Invoice Directory — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/Invoice/invoice.css') }}">
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
            <span class="bc-current">Invoice</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="siSearchInput" placeholder="Search SI#, Customer, Bill to, Ref#..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Sales Invoice Register</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Invoices">
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
                    <button type="button" class="export-option" onclick="exportInvoices('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable Sales Invoice ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportInvoices('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Spreadsheet billing schedule</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportInvoices('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated dataset</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Invoice Button -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal" onclick="openCreateSIModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Invoice</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <!-- Status Filter -->
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select" onchange="applyFilters()">
                <option value="ALL">All Statuses</option>
                <option value="Not Yet Posted">Not Yet Posted</option>
                <option value="Posted">Posted</option>
                <option value="Cancelled">Cancelled</option>
            </select>
        </div>

        <!-- Branch Filter -->
        <div class="filter-item">
            <span class="filter-label">Branch:</span>
            <select id="filterBranch" class="filter-select" onchange="applyFilters()">
                <option value="ALL">All Branches</option>
                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                <option value="Makati Sales Center">Makati Sales Center</option>
                <option value="Laguna Main Plant Site">Laguna Main Plant Site</option>
                <option value="Cebu Distribution Depot">Cebu Distribution Depot</option>
                <option value="Davao Regional Hub">Davao Regional Hub</option>
            </select>
        </div>

        <!-- Txn Covered Date (From - To) -->
        <div class="filter-item">
            <span class="filter-label">Txn Date:</span>
            <div class="filter-date-group">
                <input type="date" id="filterTxnFrom" class="filter-date-input" title="Txn Covered Date From" onchange="applyFilters()">
                <span class="filter-sep">to</span>
                <input type="date" id="filterTxnTo" class="filter-date-input" title="Txn Covered Date To" onchange="applyFilters()">
            </div>
        </div>

        <!-- Covered Due Date (From - To) -->
        <div class="filter-item">
            <span class="filter-label">Due Date:</span>
            <div class="filter-date-group">
                <input type="date" id="filterDueFrom" class="filter-date-input" title="Covered Due Date From" onchange="applyFilters()">
                <span class="filter-sep">to</span>
                <input type="date" id="filterDueTo" class="filter-date-input" title="Covered Due Date To" onchange="applyFilters()">
            </div>
        </div>

        <!-- Reset Button -->
        <button type="button" class="btn-filter-reset" onclick="resetAllFilters()">
            <i data-lucide="rotate-ccw" style="width: 12px; height: 12px;"></i>
            <span>Reset</span>
        </button>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Total Invoices</span>
            <span class="kpi-value" id="kpiTotalInvoices">0</span>
            <span class="kpi-sub">Commercial billings</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Gross Amount</span>
            <span class="kpi-value" id="kpiTotalGross">₱0.00</span>
            <span class="kpi-sub">Billed invoice gross</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Net Receivable</span>
            <span class="kpi-value" style="color: #2563eb;" id="kpiTotalNet">₱0.00</span>
            <span class="kpi-sub">Net after discount & tax</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Outstanding Balance</span>
            <span class="kpi-value" style="color: #d97706;" id="kpiTotalBalance">₱0.00</span>
            <span class="kpi-sub">Unsettled receivable</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Header -->
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Sales Invoice Master Register</span>
                    <span class="count-pill" id="totalRecordsBadge">0 Invoices</span>
                    <span class="selection-pill" id="selectionCountBadge">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectionText">0 selected</span>
                    </span>
                </div>
            </div>

            <!-- Horizontal Scroll Table Container -->
            <div class="table-scroll-container">
                <table class="erp-table" id="siMainTable">
                    <thead>
                        <tr>

                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" class="erp-cb" id="selectAllSICb" onchange="toggleSelectAllSI(this.checked)" title="Select / Deselect All">
                            </th>

                            <th style="min-width: 120px;">SI#</th>

                            <th style="min-width: 150px;">Branch</th>

                            <th style="min-width: 220px;">Bill to</th>

                            <th style="min-width: 140px;">Prepared By</th>

                            <th style="min-width: 110px;">SI Txn Date</th>

                            <th style="min-width: 110px;">Due Date</th>

                            <th style="min-width: 90px;" class="text-center">Currency</th>

                            <th style="min-width: 120px;" class="text-right">Gross Amount</th>

                            <th style="min-width: 110px;" class="text-right">Disc Amount</th>

                            <th style="min-width: 120px;" class="text-right">Net Amount</th>

                            <th style="min-width: 120px;" class="text-right">Credit Amount</th>

                            <th style="min-width: 120px;" class="text-right">Balance</th>

                            <th style="min-width: 130px;" class="text-center">Status</th>

                            <th style="min-width: 110px;" class="text-center">Deleted Date</th>

                            <th style="width: 70px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="siTableBody">
                        <!-- Populated dynamically via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Panel Footer -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="paginationSummary">Showing 0 of 0 sales invoice entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<div class="modal-backdrop" id="siFormModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-xlarge">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="siModalTitle">Create Sales Invoice</h3>
                <span class="modal-subtitle">Configure commercial invoice headers, customer accounts, line item grid, and credit balances</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSIModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="siForm" onsubmit="handleSIFormSubmit(event)">
            <input type="hidden" id="siRecordId" value="">

            <div class="modal-body" style="max-height: calc(88vh - 120px); overflow-y: auto;">

                <!-- SECTION 1: HEADER & INVOICE IDENTIFIERS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">1</span>
                            <span class="section-label">General & Invoice Identifiers</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siBranch">Branch <span class="required">*</span></label>
                            <select id="siBranch" class="form-select" required>
                                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                                <option value="Makati Sales Center">Makati Sales Center</option>
                                <option value="Laguna Main Plant Site">Laguna Main Plant Site</option>
                                <option value="Cebu Distribution Depot">Cebu Distribution Depot</option>
                                <option value="Davao Regional Hub">Davao Regional Hub</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siNumber">SI Number <span class="required">*</span></label>
                            <input type="text" id="siNumber" class="form-input font-code font-bold" placeholder="e.g. SI-2026-001" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siTxnDate">SI Txn Date <span class="required">*</span></label>
                            <input type="date" id="siTxnDate" class="form-input" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siDueDate">Due Date <span class="required">*</span></label>
                            <input type="date" id="siDueDate" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siStatus">Status <span class="required">*</span></label>
                            <select id="siStatus" class="form-select" required>
                                <option value="Not Yet Posted">Not Yet Posted</option>
                                <option value="Posted">Posted</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siRefNumber">Ref Number (P.O. #)</label>
                            <input type="text" id="siRefNumber" class="form-input font-code" placeholder="e.g. PO-SMC-9901">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siDtsRefNumber">DTS Ref Number</label>
                            <input type="text" id="siDtsRefNumber" class="form-input font-code" placeholder="e.g. DTS-8841-A">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siReprintCount">Reprint Count</label>
                            <input type="number" id="siReprintCount" class="form-input font-numeric" min="0" value="0">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CUSTOMER & LOGISTICS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">2</span>
                            <span class="section-label">Customer Billing & Logistics</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siCustomer">Customer Name <span class="required">*</span></label>
                            <input type="text" id="siCustomer" class="form-input" placeholder="e.g. San Miguel Corporation" required oninput="autoFillBillTo(this.value)">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siBillTo">Bill To <span class="required">*</span></label>
                            <input type="text" id="siBillTo" class="form-input" placeholder="e.g. San Miguel Corp — Accounts Payable" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siPaymentTerm">Payment Term <span class="required">*</span></label>
                            <select id="siPaymentTerm" class="form-select" required>
                                <option value="30 Days">30 Days from Invoice</option>
                                <option value="60 Days">60 Days from Invoice</option>
                                <option value="15th of Following Month">15th of Following Month</option>
                                <option value="COD">Upon Receipt of Goods (COD)</option>
                                <option value="7 Days">7 Days Term</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-2">
                            <label for="siAddress">Billing & Delivery Address <span class="required">*</span></label>
                            <input type="text" id="siAddress" class="form-input" placeholder="e.g. 40 San Miguel Ave, Ortigas Center, Mandaluyong City" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siShipment">Shipment Method <span class="required">*</span></label>
                            <select id="siShipment" class="form-select" required>
                                <option value="Company Truck Delivery">Company Truck Delivery</option>
                                <option value="Customer Plant Pickup">Customer Plant Pickup</option>
                                <option value="Third-Party Freight / Logistics">Third-Party Freight / Logistics</option>
                                <option value="Courier Express Air">Courier Express Air</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siCountryOrigin">Country Of Origin <span class="required">*</span></label>
                            <select id="siCountryOrigin" class="form-select" required>
                                <option value="Philippines">Philippines</option>
                                <option value="Japan">Japan</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="United States">United States</option>
                                <option value="South Korea">South Korea</option>
                                <option value="China">China</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- SECTION 3: GRID DETAILS (PRODUCT LINE ITEMS) -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">3</span>
                            <span class="section-label">Grid Details (Ordered Product Items)</span>
                        </div>
                        <button type="button" class="btn-outline" style="height: 30px; font-size: 11.5px; padding: 0 10px;" onclick="addGridItemRow()">
                            <i data-lucide="plus" style="width: 13px; height: 13px;"></i>
                            <span>Add Item</span>
                        </button>
                    </div>

                    <p style="font-size: 11.5px; color: #64748b; margin-bottom: 8px;">
                        Select products from Product Master. Prices, descriptions, and taxes will auto-populate and can be adjusted.
                    </p>

                    <div class="nested-table-wrap">
                        <table class="nested-erp-table" id="gridItemsTable">
                            <thead>
                                <tr>
                                    <th style="min-width: 120px;">Prod Id</th>
                                    <th style="min-width: 110px;">Product Code</th>
                                    <th style="min-width: 180px;">Description</th>
                                    <th style="min-width: 70px;">UOM</th>
                                    <th style="min-width: 70px;" class="text-right">Qty</th>
                                    <th style="min-width: 95px;" class="text-right">Selling Price</th>
                                    <th style="min-width: 75px;">Currency</th>
                                    <th style="min-width: 85px;">VAT</th>
                                    <th style="min-width: 95px;" class="text-right">Gross Amount</th>
                                    <th style="min-width: 75px;" class="text-right">Disc %</th>
                                    <th style="min-width: 85px;" class="text-right">Disc Amt</th>
                                    <th style="min-width: 95px;" class="text-right">Net Amount</th>
                                    <th style="min-width: 95px;" class="text-right">Amount</th>
                                    <th style="min-width: 95px;">DR Number</th>
                                    <th style="min-width: 100px;">DR Date</th>
                                    <th style="min-width: 95px;">SO Number</th>
                                    <th style="min-width: 95px;">SO Ref #</th>
                                    <th style="min-width: 75px;" class="text-right">SO Balance</th>
                                    <th style="width: 50px; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="gridItemsTableBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SECTION 4: PRICING, CURRENCY & TOTALS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">4</span>
                            <span class="section-label">Pricing, Currency & Commercial Totals</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siCurrency">Currency <span class="required">*</span></label>
                            <select id="siCurrency" class="form-select" onchange="calculateSITotals()">
                                <option value="PHP">PHP (₱)</option>
                                <option value="USD">USD ($)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siRate">Rate (Exchange Multiplier)</label>
                            <input type="number" id="siRate" class="form-input font-numeric text-right" step="0.0001" value="1.0000">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siVatCode">Vat Code <span class="required">*</span></label>
                            <select id="siVatCode" class="form-select" onchange="calculateSITotals()">
                                <option value="VAT Output (12%)">VAT Output (12%)</option>
                                <option value="Zero-Rated (0%)">Zero-Rated (0%)</option>
                                <option value="VAT Exempt">VAT Exempt (0%)</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siItemCount">Item Count</label>
                            <input type="number" id="siItemCount" class="form-input font-numeric text-right font-bold" readonly value="0">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siGrossAmount">Gross Amount</label>
                            <input type="number" id="siGrossAmount" class="form-input font-numeric text-right font-bold" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siDiscountAmount">Discount Amount</label>
                            <input type="number" id="siDiscountAmount" class="form-input font-numeric text-right" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siVatAmount">VAT Amount</label>
                            <input type="number" id="siVatAmount" class="form-input font-numeric text-right" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siNetAmount">Net Amount</label>
                            <input type="number" id="siNetAmount" class="form-input font-numeric text-right font-bold" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="siTotalAmount">Total Amount</label>
                            <input type="number" id="siTotalAmount" class="form-input font-numeric text-right font-bold" style="color: #2563eb;" readonly value="0.00">
                        </div>
                    </div>

                    <!-- Live Calculation Visual Strip -->
                    <div class="financial-strip-card">
                        <div class="financial-item">
                            <span class="financial-item-label">Gross Billed Value</span>
                            <span class="financial-item-value" id="stripGross">₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Deducted Discounts</span>
                            <span class="financial-item-value" style="color: #dc2626;" id="stripDisc">-₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Value-Added Tax (VAT)</span>
                            <span class="financial-item-value" id="stripVat">+₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Total Commercial Receivable</span>
                            <span class="financial-item-value accent-total" id="stripTotal">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: APPROVALS & SIGNATURES -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">5</span>
                            <span class="section-label">Signatures & Approval Authorization</span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="siPreparedBy">Prepared By <span class="required">*</span></label>
                            <input type="text" id="siPreparedBy" class="form-input" placeholder="e.g. Karl Bautista" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siCheckedBy">Checked By <span class="required">*</span></label>
                            <input type="text" id="siCheckedBy" class="form-input" placeholder="e.g. Maria Santos" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="siApprovedBy">Approved By <span class="required">*</span></label>
                            <input type="text" id="siApprovedBy" class="form-input" placeholder="e.g. Juan Dela Cruz" required>
                        </div>
                    </div>
                </div>

                <!-- SECTION 6: REMARKS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <div class="section-title-left">
                            <span class="badge-num">6</span>
                            <span class="section-label">Commercial Remarks & Accounting Notes</span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="siRemarks">Remarks</label>
                        <textarea id="siRemarks" class="form-textarea" rows="2" placeholder="Specify any credit arrangements, bank payment channels, withholding tax certificates (BIR 2307), or delivery receipt cross-references."></textarea>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeSIModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSiSubmit">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSiSubmitText">Save Sales Invoice</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="siViewModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-xlarge">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewSiTitle">Sales Invoice: SI-2026-001</h3>
                <span class="modal-subtitle" id="viewSiSub">Official commercial invoice dossier & payment tracking</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSIViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" style="max-height: calc(88vh - 120px); overflow-y: auto;">
            <div class="view-dossier-grid">
                <!-- Card 1: Invoice Identifiers -->
                <div class="dossier-card">
                    <div class="dossier-card-title">Invoice Identifiers</div>
                    <div class="dossier-row"><span class="dossier-key">SI Number:</span><span class="dossier-val font-code font-bold" id="vSiNum">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Branch:</span><span class="dossier-val" id="vSiBranch">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">SI Txn Date:</span><span class="dossier-val font-numeric" id="vSiTxnDate">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Due Date:</span><span class="dossier-val font-numeric" id="vSiDueDate">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Ref Number:</span><span class="dossier-val font-code" id="vSiRefNum">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">DTS Ref:</span><span class="dossier-val font-code" id="vSiDtsRef">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Status:</span><span class="dossier-val" id="vSiStatusPill">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Reprint Count:</span><span class="dossier-val font-numeric" id="vSiReprints">—</span></div>
                </div>

                <!-- Card 2: Customer & Shipping -->
                <div class="dossier-card">
                    <div class="dossier-card-title">Customer & Logistics</div>
                    <div class="dossier-row"><span class="dossier-key">Customer:</span><span class="dossier-val" id="vSiCustomer">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Bill To:</span><span class="dossier-val" id="vSiBillTo">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Billing Address:</span><span class="dossier-val" style="max-width: 250px;" id="vSiAddress">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Payment Term:</span><span class="dossier-val" id="vSiPaymentTerm">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Shipment:</span><span class="dossier-val" id="vSiShipment">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Country Of Origin:</span><span class="dossier-val" id="vSiOrigin">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Archived Date:</span><span class="dossier-val font-numeric" id="vSiDeletedDate">—</span></div>
                </div>
            </div>

            <!-- Card 3: Line Items Details -->
            <div class="dossier-card" style="margin-top: 14px;">
                <div class="dossier-card-title">Billed Line Items (<span id="vSiItemCount">0</span> items)</div>
                <div class="nested-table-wrap">
                    <table class="nested-erp-table" id="viewGridTable">
                        <thead>
                            <tr>
                                <th>Prod Id</th>
                                <th>Product Code</th>
                                <th>Description</th>
                                <th>UOM</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Selling Price</th>
                                <th>VAT</th>
                                <th class="text-right">Gross</th>
                                <th class="text-right">Disc</th>
                                <th class="text-right">Net</th>
                                <th class="text-right">Amount</th>
                                <th>DR #</th>
                                <th>SO #</th>
                            </tr>
                        </thead>
                        <tbody id="viewGridBody">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Card 4: Financial Summary & Approval Signatures -->
            <div class="dossier-card" style="margin-top: 14px; background: #f8fafc;">
                <div class="dossier-card-title">Financial Summary & Authorizations</div>
                <div class="view-dossier-grid">
                    <div>
                        <div class="dossier-row"><span class="dossier-key">Currency & Rate:</span><span class="dossier-val" id="vSiCurrencyRate">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Gross Amount:</span><span class="dossier-val font-numeric font-bold" id="vSiGross">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Discount:</span><span class="dossier-val font-numeric" style="color: #dc2626;" id="vSiDiscount">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">VAT Amount:</span><span class="dossier-val font-numeric" id="vSiVat">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Net Receivable:</span><span class="dossier-val font-numeric font-bold" id="vSiNet">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Credit Amount:</span><span class="dossier-val font-numeric" id="vSiCredit">—</span></div>
                        <div class="dossier-row" style="border-top: 1px solid #cbd5e1; padding-top: 8px; margin-top: 4px;">
                            <span class="dossier-key" style="font-weight: 700; color: #0f172a;">Outstanding Balance:</span>
                            <span class="dossier-val font-numeric font-bold" style="color: #2563eb; font-size: 16px;" id="vSiBalance">—</span>
                        </div>
                    </div>
                    <div>
                        <div class="dossier-row"><span class="dossier-key">Prepared By:</span><span class="dossier-val font-bold" id="vSiPreparedBy">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Checked By:</span><span class="dossier-val font-bold" id="vSiCheckedBy">—</span></div>
                        <div class="dossier-row"><span class="dossier-key">Approved By:</span><span class="dossier-val font-bold" id="vSiApprovedBy">—</span></div>
                        <div class="dossier-row" style="margin-top: 10px;"><span class="dossier-key">Remarks:</span><span class="dossier-val" id="vSiRemarks">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeSIViewModal()">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit" class="btn-icon"></i>
                <span>Edit Invoice</span>
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="siDeleteModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-small">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" style="color: #dc2626;">Archive Sales Invoice</h3>
                <span class="modal-subtitle">Soft-delete invoice record</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSIDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size: 13px; color: #334155; line-height: 1.5;">
                Are you sure you want to mark Sales Invoice <strong id="deleteTargetSiNum" class="font-code">SI-2026-001</strong> as archived?
            </p>
            <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                Customer / Bill To: <span id="deleteTargetBillTo" class="font-bold text-dark"></span>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeSIDeleteModal()">Cancel</button>
            <button type="button" class="btn-primary" style="background: #dc2626; border-color: #dc2626;" onclick="confirmDeleteSI()">
                <span>Archive Invoice</span>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Global HTML Escape helper
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Currency formatting helper
    function fmtCurrency(val, curr = 'PHP') {
        const symbol = curr === 'PHP' ? '₱' : (curr === 'USD' ? '$' : (curr === 'EUR' ? '€' : '¥'));
        return `${symbol}${parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // Product Master Catalog reference
    const productMaster = [
        { id: 'PRD-001', code: 'PRD-8821', desc: 'Industrial IoT Controller Assembly Rev A.0', uom: 'PCS', price: 1850.00, currency: 'PHP', vat: '12%' },
        { id: 'PRD-002', code: 'ENC-7700', desc: 'IP67 Waterproof Enclosure CNC Machined', uom: 'PCS', price: 640.00, currency: 'PHP', vat: '12%' },
        { id: 'PRD-003', code: 'SEN-3301', desc: 'High-Precision Laser Distance Sensor Module', uom: 'SET', price: 3200.00, currency: 'PHP', vat: '0%' },
        { id: 'PRD-004', code: 'CAB-9900', desc: 'Heavy-Duty Industrial Shielded Cable Harness', uom: 'BOX', price: 450.00, currency: 'PHP', vat: '12%' },
        { id: 'PRD-005', code: 'PWR-1200', desc: '24V DC Industrial DIN-Rail Power Supply Unit', uom: 'UNIT', price: 1250.00, currency: 'PHP', vat: '12%' }
    ];

    // 1. DATA MASTERSET

    let invoicesData = [
        {
            id: 1,
            si_number: 'SI-2026-001',
            branch: 'Head Office (Mandaluyong)',
            customer: 'San Miguel Corporation',
            bill_to: 'San Miguel Corp — Accounts Payable',
            address: '40 San Miguel Ave, Ortigas Center, Mandaluyong City',
            prepared_by: 'Karl Bautista',
            checked_by: 'Maria Santos',
            approved_by: 'Juan Dela Cruz',
            si_txn_date: '2026-11-01',
            due_date: '2026-12-01',
            currency: 'PHP',
            rate: 1.0000,
            payment_term: '30 Days',
            shipment: 'Company Truck Delivery',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            ref_number: 'PO-SMC-9901',
            dts_ref: 'DTS-8841-A',
            reprint_count: 0,
            status: 'Posted',
            gross_amount: 185000.00,
            discount_amount: 9250.00,
            vat_amount: 21090.00,
            net_amount: 196840.00,
            credit_amount: 50000.00,
            balance: 146840.00,
            total_amount: 196840.00,
            item_count: 1,
            remarks: 'Official invoice for Laguna Brewery batch delivery.',
            deleted_date: '—',
            grid_items: [
                {
                    prod_id: 'PRD-001',
                    prod_code: 'PRD-8821',
                    desc: 'Industrial IoT Controller Assembly Rev A.0',
                    uom: 'PCS',
                    qty: 100,
                    price: 1850.00,
                    currency: 'PHP',
                    vat: '12%',
                    gross: 185000.00,
                    disc_rate: 5.0,
                    disc_amt: 9250.00,
                    net: 196840.00,
                    amount: 196840.00,
                    dr_number: 'DR-2026-081',
                    dr_date: '2026-11-01',
                    so_number: 'SO-2026-001',
                    so_ref: 'PO-SMC-9901',
                    so_balance: 0
                }
            ]
        },
        {
            id: 2,
            si_number: 'SI-2026-002',
            branch: 'Makati Sales Center',
            customer: 'Universal Robina Corporation',
            bill_to: 'URC Corporate Procurement & Finance',
            address: '8th Floor Tera Tower, Bridgetowne, Quezon City',
            prepared_by: 'Juan Dela Cruz',
            checked_by: 'Maria Santos',
            approved_by: 'Karl Bautista',
            si_txn_date: '2026-11-03',
            due_date: '2027-01-02',
            currency: 'PHP',
            rate: 1.0000,
            payment_term: '60 Days',
            shipment: 'Third-Party Freight / Logistics',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            ref_number: 'PO-URC-4412',
            dts_ref: 'DTS-9102-B',
            reprint_count: 1,
            status: 'Posted',
            gross_amount: 160000.00,
            discount_amount: 5000.00,
            vat_amount: 18600.00,
            net_amount: 173600.00,
            credit_amount: 0.00,
            balance: 173600.00,
            total_amount: 173600.00,
            item_count: 1,
            remarks: 'Batch delivery of 250 units IP67 CNC enclosures.',
            deleted_date: '—',
            grid_items: [
                {
                    prod_id: 'PRD-002',
                    prod_code: 'ENC-7700',
                    desc: 'IP67 Waterproof Enclosure CNC Machined',
                    uom: 'PCS',
                    qty: 250,
                    price: 640.00,
                    currency: 'PHP',
                    vat: '12%',
                    gross: 160000.00,
                    disc_rate: 3.125,
                    disc_amt: 5000.00,
                    net: 173600.00,
                    amount: 173600.00,
                    dr_number: 'DR-2026-085',
                    dr_date: '2026-11-03',
                    so_number: 'SO-2026-002',
                    so_ref: 'PO-URC-4412',
                    so_balance: 150
                }
            ]
        },
        {
            id: 3,
            si_number: 'SI-2026-003',
            branch: 'Laguna Main Plant Site',
            customer: 'Robinsons Retail Holdings',
            bill_to: 'Robinsons Supermarket HQ Accounting',
            address: '110 E. Rodriguez Jr. Ave, Libis, Quezon City',
            prepared_by: 'Maria Santos',
            checked_by: 'Carlos Reyes',
            approved_by: 'Karl Bautista',
            si_txn_date: '2026-11-04',
            due_date: '2026-12-15',
            currency: 'PHP',
            rate: 1.0000,
            payment_term: '15th of Following Month',
            shipment: 'Company Truck Delivery',
            country_origin: 'Japan',
            vat_code: 'Zero-Rated (0%)',
            ref_number: 'PO-RRH-1084',
            dts_ref: 'DTS-3390-C',
            reprint_count: 0,
            status: 'Not Yet Posted',
            gross_amount: 160000.00,
            discount_amount: 0.00,
            vat_amount: 0.00,
            net_amount: 160000.00,
            credit_amount: 0.00,
            balance: 160000.00,
            total_amount: 160000.00,
            item_count: 1,
            remarks: 'Zero-rated export zone delivery. Awaiting BIR PEZA endorsement.',
            deleted_date: '—',
            grid_items: [
                {
                    prod_id: 'PRD-003',
                    prod_code: 'SEN-3301',
                    desc: 'High-Precision Laser Distance Sensor Module',
                    uom: 'SET',
                    qty: 50,
                    price: 3200.00,
                    currency: 'PHP',
                    vat: '0%',
                    gross: 160000.00,
                    disc_rate: 0,
                    disc_amt: 0.00,
                    net: 160000.00,
                    amount: 160000.00,
                    dr_number: 'DR-2026-092',
                    dr_date: '2026-11-04',
                    so_number: 'SO-2026-003',
                    so_ref: 'PO-RRH-1084',
                    so_balance: 50
                }
            ]
        },
        {
            id: 4,
            si_number: 'SI-2026-004',
            branch: 'Cebu Distribution Depot',
            customer: 'Apex Precision Engineering',
            bill_to: 'Apex Hardware & Parts Accounting',
            address: '88 Industrial Road, Karuhatan, Valenzuela City',
            prepared_by: 'Carlos Reyes',
            checked_by: 'Maria Santos',
            approved_by: 'Juan Dela Cruz',
            si_txn_date: '2026-10-26',
            due_date: '2026-10-26',
            currency: 'PHP',
            rate: 1.0000,
            payment_term: 'COD',
            shipment: 'Customer Plant Pickup',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            ref_number: 'PO-APX-7719',
            dts_ref: 'DTS-4411-X',
            reprint_count: 0,
            status: 'Cancelled',
            gross_amount: 36000.00,
            discount_amount: 900.00,
            vat_amount: 4212.00,
            net_amount: 39312.00,
            credit_amount: 0.00,
            balance: 0.00,
            total_amount: 39312.00,
            item_count: 1,
            remarks: 'Invoice cancelled due to engineering order cancellation.',
            deleted_date: '2026-11-02',
            grid_items: [
                {
                    prod_id: 'PRD-004',
                    prod_code: 'CAB-9900',
                    desc: 'Heavy-Duty Industrial Shielded Cable Harness',
                    uom: 'BOX',
                    qty: 80,
                    price: 450.00,
                    currency: 'PHP',
                    vat: '12%',
                    gross: 36000.00,
                    disc_rate: 2.5,
                    disc_amt: 900.00,
                    net: 39312.00,
                    amount: 39312.00,
                    dr_number: 'DR-2026-077',
                    dr_date: '2026-10-26',
                    so_number: 'SO-2026-004',
                    so_ref: 'PO-APX-7719',
                    so_balance: 0
                }
            ]
        }
    ];

    let selectedSiIds = new Set();
    let currentModalItems = [];
    let deleteTargetId = null;
    let activeViewId = null;

    // DOM Elements
    const siTableBody = document.getElementById('siTableBody');
    const siSearchInput = document.getElementById('siSearchInput');
    const selectAllSICb = document.getElementById('selectAllSICb');
    const totalRecordsBadge = document.getElementById('totalRecordsBadge');
    const selectionCountBadge = document.getElementById('selectionCountBadge');
    const selectionText = document.getElementById('selectionText');
    const paginationSummary = document.getElementById('paginationSummary');

    // Filters
    const filterStatus = document.getElementById('filterStatus');
    const filterBranch = document.getElementById('filterBranch');
    const filterTxnFrom = document.getElementById('filterTxnFrom');
    const filterTxnTo = document.getElementById('filterTxnTo');
    const filterDueFrom = document.getElementById('filterDueFrom');
    const filterDueTo = document.getElementById('filterDueTo');

    // KPIs
    const kpiTotalInvoices = document.getElementById('kpiTotalInvoices');
    const kpiTotalGross = document.getElementById('kpiTotalGross');
    const kpiTotalNet = document.getElementById('kpiTotalNet');
    const kpiTotalBalance = document.getElementById('kpiTotalBalance');

    // Modals
    const siFormModalBackdrop = document.getElementById('siFormModalBackdrop');
    const siViewModalBackdrop = document.getElementById('siViewModalBackdrop');
    const siDeleteModalBackdrop = document.getElementById('siDeleteModalBackdrop');
    const siModalTitle = document.getElementById('siModalTitle');
    const btnSiSubmitText = document.getElementById('btnSiSubmitText');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');

    // Export & Refresh
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // 2. RENDER TABLE & UPDATE KPI

    function renderSITable(dataset = invoicesData) {
        siTableBody.innerHTML = '';
        const total = dataset.length;

        // KPI calculations (active invoices only)
        const activeRows = dataset.filter(r => !r.deleted_date || r.deleted_date === '—');
        kpiTotalInvoices.textContent = activeRows.length;
        const totalGrossSum = activeRows.reduce((acc, r) => acc + (parseFloat(r.gross_amount) || 0), 0);
        const totalNetSum = activeRows.reduce((acc, r) => acc + (parseFloat(r.net_amount) || 0), 0);
        const totalBalSum = activeRows.reduce((acc, r) => acc + (parseFloat(r.balance) || 0), 0);

        kpiTotalGross.textContent = `₱${totalGrossSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        kpiTotalNet.textContent = `₱${totalNetSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        kpiTotalBalance.textContent = `₱${totalBalSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

        totalRecordsBadge.textContent = `${total} Invoice${total === 1 ? '' : 's'}`;
        paginationSummary.textContent = `Showing 1 to ${total} of ${total} sales invoice entries`;

        if (total === 0) {
            siTableBody.innerHTML = `
                <tr>
                    <td colspan="16" class="text-center text-muted" style="padding: 40px 16px;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <i data-lucide="receipt" style="width: 32px; height: 32px; color: #cbd5e1;"></i>
                            <span style="font-size: 13px; font-weight: 500; color: #475569;">No sales invoices matched your filter criteria</span>
                            <span style="font-size: 11.5px; color: #94a3b8;">Try resetting date ranges, status or branch filters.</span>
                        </div>
                    </td>
                </tr>
            `;
            updateSelectAllState();
            if (window.lucide) window.lucide.createIcons();
            return;
        }

        dataset.forEach(row => {
            const tr = document.createElement('tr');
            const isSelected = selectedSiIds.has(row.id);
            const isDeleted = row.deleted_date && row.deleted_date !== '—';

            if (isSelected) tr.classList.add('row-selected');
            if (isDeleted) tr.classList.add('row-deleted');

            // Status Pill
            let statusBadgeClass = 'status-pill-pending';
            if (row.status === 'Posted') statusBadgeClass = 'status-pill-posted';
            else if (row.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';
            if (isDeleted) statusBadgeClass = 'status-pill-archived';

            const statusLabel = isDeleted ? `Archived (${escapeHtml(row.deleted_date)})` : escapeHtml(row.status);

            tr.innerHTML = `

                <td style="width: 40px; text-align: center;">
                    <input type="checkbox" class="erp-cb si-row-cb" data-id="${row.id}" ${isSelected ? 'checked' : ''} onchange="toggleSelectSI(${row.id}, this.checked)">
                </td>

                <td>
                    <a href="javascript:void(0)" onclick="openViewSIModal(${row.id})" class="font-code font-bold text-dark" style="color: #2563eb; text-decoration: none;">
                        ${escapeHtml(row.si_number)}
                    </a>
                </td>

                <td>${escapeHtml(row.branch)}</td>

                <td>
                    <span class="font-medium text-dark">${escapeHtml(row.bill_to)}</span>
                </td>

                <td>${escapeHtml(row.prepared_by)}</td>

                <td class="font-numeric">${escapeHtml(row.si_txn_date)}</td>

                <td class="font-numeric font-medium">${escapeHtml(row.due_date)}</td>

                <td class="text-center font-code">${escapeHtml(row.currency)}</td>

                <td class="font-numeric text-right font-bold">${fmtCurrency(row.gross_amount, row.currency)}</td>

                <td class="font-numeric text-right" style="color: #dc2626;">-${fmtCurrency(row.discount_amount, row.currency)}</td>

                <td class="font-numeric text-right font-bold">${fmtCurrency(row.net_amount, row.currency)}</td>

                <td class="font-numeric text-right">${fmtCurrency(row.credit_amount, row.currency)}</td>

                <td class="font-numeric text-right font-bold" style="color: #2563eb;">${fmtCurrency(row.balance, row.currency)}</td>

                <td class="text-center">
                    <span class="status-pill ${statusBadgeClass}">${statusLabel}</span>
                </td>

                <td class="text-center ${isDeleted ? 'font-code' : 'text-muted'}" style="${isDeleted ? 'color: #dc2626; font-weight: 600;' : ''}">
                    ${escapeHtml(row.deleted_date)}
                </td>

                <td class="actions-cell">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, ${row.id})" title="Actions">
                        <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                    </button>
                    <div class="action-menu-dropdown drop-down" id="actionMenu_${row.id}">
                        <button type="button" class="action-menu-item" onclick="openViewSIModal(${row.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="openEditSIModal(${row.id})">
                            <i data-lucide="edit"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openDeleteSIModal(${row.id})">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </td>
            `;

            siTableBody.appendChild(tr);
        });

        updateSelectAllState();
        if (window.lucide) window.lucide.createIcons();
    }

    // 3. SELECTION STATE MANAGEMENT

    window.toggleSelectSI = function (id, checked) {
        if (checked) {
            selectedSiIds.add(id);
        } else {
            selectedSiIds.delete(id);
        }
        updateSelectAllState();
        highlightRows();
    };

    window.toggleSelectAllSI = function (checked) {
        const visibleIds = getCurrentlyFilteredData().map(r => r.id);
        if (checked) {
            visibleIds.forEach(id => selectedSiIds.add(id));
        } else {
            visibleIds.forEach(id => selectedSiIds.delete(id));
        }
        updateSelectAllState();
        highlightRows();
    };

    function updateSelectAllState() {
        const visibleIds = getCurrentlyFilteredData().map(r => r.id);
        const totalVisible = visibleIds.length;
        const selectedVisible = visibleIds.filter(id => selectedSiIds.has(id)).length;

        if (selectAllSICb) {
            selectAllSICb.checked = totalVisible > 0 && selectedVisible === totalVisible;
            selectAllSICb.indeterminate = selectedVisible > 0 && selectedVisible < totalVisible;
        }

        const totalSelected = selectedSiIds.size;
        if (totalSelected > 0) {
            selectionCountBadge.classList.add('show');
            selectionText.textContent = `${totalSelected} selected`;
        } else {
            selectionCountBadge.classList.remove('show');
        }
    }

    function highlightRows() {
        document.querySelectorAll('.si-row-cb').forEach(cb => {
            const id = parseInt(cb.getAttribute('data-id'), 10);
            const tr = cb.closest('tr');
            if (tr) {
                if (selectedSiIds.has(id)) {
                    tr.classList.add('row-selected');
                    cb.checked = true;
                } else {
                    tr.classList.remove('row-selected');
                    cb.checked = false;
                }
            }
        });
    }

    // 4. ACTION MENUS (SAFE POSITIONING PREVENTS CLIPPING)

    window.toggleActionMenu = function (event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`actionMenu_${id}`);
        const isShown = menu.classList.contains('show');

        closeAllActionMenus();

        if (!isShown) {
            const btn = event.currentTarget;
            const rect = btn.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;

            if (spaceBelow < 150) {
                menu.classList.remove('drop-down');
                menu.classList.add('drop-up');
            } else {
                menu.classList.remove('drop-up');
                menu.classList.add('drop-down');
            }

            menu.classList.add('show');
        }
    };

    function closeAllActionMenus() {
        document.querySelectorAll('.action-menu-dropdown.show').forEach(m => m.classList.remove('show'));
    }

    document.addEventListener('click', function () {
        closeAllActionMenus();
        if (exportMenu) exportMenu.classList.remove('show');
    });

    // 5. SEARCH & ADVANCED FILTERS (STATUS, BRANCH, COVERED DATES)

    function getCurrentlyFilteredData() {
        const query = (siSearchInput.value || '').trim().toLowerCase();
        const statusVal = filterStatus.value;
        const branchVal = filterBranch.value;
        const txnFrom = filterTxnFrom.value;
        const txnTo = filterTxnTo.value;
        const dueFrom = filterDueFrom.value;
        const dueTo = filterDueTo.value;

        return invoicesData.filter(r => {
            // Text search
            const matchesText = !query ||
                (r.si_number && r.si_number.toLowerCase().includes(query)) ||
                (r.customer && r.customer.toLowerCase().includes(query)) ||
                (r.bill_to && r.bill_to.toLowerCase().includes(query)) ||
                (r.branch && r.branch.toLowerCase().includes(query)) ||
                (r.ref_number && r.ref_number.toLowerCase().includes(query)) ||
                (r.prepared_by && r.prepared_by.toLowerCase().includes(query));

            // Status filter
            const matchesStatus = (statusVal === 'ALL') || (r.status === statusVal);

            // Branch filter
            const matchesBranch = (branchVal === 'ALL') || (r.branch === branchVal);

            // Txn Date filter
            let matchesTxnDate = true;
            if (txnFrom && r.si_txn_date < txnFrom) matchesTxnDate = false;
            if (txnTo && r.si_txn_date > txnTo) matchesTxnDate = false;

            // Due Date filter
            let matchesDueDate = true;
            if (dueFrom && r.due_date < dueFrom) matchesDueDate = false;
            if (dueTo && r.due_date > dueTo) matchesDueDate = false;

            return matchesText && matchesStatus && matchesBranch && matchesTxnDate && matchesDueDate;
        });
    }

    window.applyFilters = function () {
        closeAllActionMenus();
        renderSITable(getCurrentlyFilteredData());
    };

    window.resetAllFilters = function () {
        if (siSearchInput) siSearchInput.value = '';
        filterStatus.value = 'ALL';
        filterBranch.value = 'ALL';
        filterTxnFrom.value = '';
        filterTxnTo.value = '';
        filterDueFrom.value = '';
        filterDueTo.value = '';
        applyFilters();
    };

    if (siSearchInput) {
        siSearchInput.addEventListener('input', applyFilters);
    }

    // 6. GRID DETAILS (LINE ITEMS) SYSTEM & COMPUTATIONS

    window.autoFillBillTo = function (custVal) {
        const billInput = document.getElementById('siBillTo');
        if (billInput && !billInput.value) {
            billInput.value = `${custVal} — Accounts Payable`;
        }
    };

    function renderGridItems() {
        const tbody = document.getElementById('gridItemsTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (currentModalItems.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="19" class="text-center text-muted" style="padding: 16px;">
                        No line items added yet. Click <strong>+ Add Item</strong> above to select products.
                    </td>
                </tr>
            `;
            calculateSITotals();
            return;
        }

        currentModalItems.forEach((item, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `

                <td>
                    <select class="grid-cell-input" onchange="onGridProdSelect(${idx}, this.value)">
                        <option value="">-- Select --</option>
                        ${productMaster.map(p => `
                            <option value="${p.id}" ${item.prod_id === p.id ? 'selected' : ''}>${p.id} (${p.code})</option>
                        `).join('')}
                    </select>
                </td>

                <td>
                    <input type="text" class="grid-cell-input font-code font-bold" readonly value="${escapeHtml(item.prod_code || '')}">
                </td>

                <td>
                    <input type="text" class="grid-cell-input" value="${escapeHtml(item.desc || '')}" oninput="updateGridField(${idx}, 'desc', this.value)">
                </td>

                <td>
                    <input type="text" class="grid-cell-input" style="width: 55px;" value="${escapeHtml(item.uom || 'PCS')}" oninput="updateGridField(${idx}, 'uom', this.value)">
                </td>

                <td>
                    <input type="number" class="grid-cell-input text-right font-numeric font-bold" min="1" value="${item.qty || 1}" oninput="updateGridField(${idx}, 'qty', parseInt(this.value, 10) || 0)">
                </td>

                <td>
                    <input type="number" class="grid-cell-input text-right font-numeric font-bold" step="0.01" min="0" value="${parseFloat(item.price || 0).toFixed(2)}" oninput="updateGridField(${idx}, 'price', parseFloat(this.value) || 0)">
                </td>

                <td>
                    <select class="grid-cell-input" onchange="updateGridField(${idx}, 'currency', this.value)">
                        <option value="PHP" ${item.currency === 'PHP' ? 'selected' : ''}>PHP</option>
                        <option value="USD" ${item.currency === 'USD' ? 'selected' : ''}>USD</option>
                        <option value="JPY" ${item.currency === 'JPY' ? 'selected' : ''}>JPY</option>
                        <option value="EUR" ${item.currency === 'EUR' ? 'selected' : ''}>EUR</option>
                    </select>
                </td>

                <td>
                    <select class="grid-cell-input" onchange="updateGridField(${idx}, 'vat', this.value)">
                        <option value="12%" ${item.vat === '12%' ? 'selected' : ''}>12%</option>
                        <option value="0%" ${item.vat === '0%' ? 'selected' : ''}>0%</option>
                        <option value="Exempt" ${item.vat === 'Exempt' ? 'selected' : ''}>Exempt</option>
                    </select>
                </td>

                <td>
                    <input type="text" class="grid-cell-input text-right font-numeric font-bold" readonly value="${parseFloat(item.gross || 0).toFixed(2)}">
                </td>

                <td>
                    <input type="number" class="grid-cell-input text-right font-numeric" step="0.1" min="0" max="100" value="${item.disc_rate || 0}" oninput="updateGridField(${idx}, 'disc_rate', parseFloat(this.value) || 0)">
                </td>

                <td>
                    <input type="number" class="grid-cell-input text-right font-numeric" step="0.01" min="0" value="${parseFloat(item.disc_amt || 0).toFixed(2)}" oninput="updateGridField(${idx}, 'disc_amt_manual', parseFloat(this.value) || 0)">
                </td>

                <td>
                    <input type="text" class="grid-cell-input text-right font-numeric font-bold" readonly value="${parseFloat(item.net || 0).toFixed(2)}">
                </td>

                <td>
                    <input type="text" class="grid-cell-input text-right font-numeric font-bold" style="color: #2563eb;" readonly value="${parseFloat(item.amount || 0).toFixed(2)}">
                </td>

                <td>
                    <input type="text" class="grid-cell-input font-code" placeholder="DR-2026-..." value="${escapeHtml(item.dr_number || '')}" oninput="updateGridField(${idx}, 'dr_number', this.value)">
                </td>

                <td>
                    <input type="date" class="grid-cell-input" value="${item.dr_date || ''}" oninput="updateGridField(${idx}, 'dr_date', this.value)">
                </td>

                <td>
                    <input type="text" class="grid-cell-input font-code" placeholder="SO-2026-..." value="${escapeHtml(item.so_number || '')}" oninput="updateGridField(${idx}, 'so_number', this.value)">
                </td>

                <td>
                    <input type="text" class="grid-cell-input font-code" placeholder="PO-..." value="${escapeHtml(item.so_ref || '')}" oninput="updateGridField(${idx}, 'so_ref', this.value)">
                </td>

                <td>
                    <input type="number" class="grid-cell-input text-right font-numeric" min="0" value="${item.so_balance || 0}" oninput="updateGridField(${idx}, 'so_balance', parseInt(this.value, 10) || 0)">
                </td>

                <td style="text-align: center;">
                    <button type="button" class="btn-del-mini" onclick="removeGridItem(${idx})" title="Remove item">
                        <i data-lucide="trash-2" style="width: 12px; height: 12px;"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        calculateSITotals();
        if (window.lucide) window.lucide.createIcons();
    }

    window.addGridItemRow = function () {
        const defaultProd = productMaster[0];
        const defaultGross = defaultProd.price * 1;
        const defaultVat = defaultProd.vat === '12%' ? (defaultGross * 0.12) : 0;
        const defaultTotal = defaultGross + defaultVat;

        currentModalItems.push({
            prod_id: defaultProd.id,
            prod_code: defaultProd.code,
            desc: defaultProd.desc,
            uom: defaultProd.uom,
            qty: 1,
            price: defaultProd.price,
            currency: defaultProd.currency,
            vat: defaultProd.vat,
            gross: defaultGross,
            disc_rate: 0,
            disc_amt: 0.00,
            net: defaultTotal,
            amount: defaultTotal,
            dr_number: '',
            dr_date: new Date().toISOString().split('T')[0],
            so_number: '',
            so_ref: '',
            so_balance: 0
        });

        renderGridItems();
    };

    window.removeGridItem = function (idx) {
        currentModalItems.splice(idx, 1);
        renderGridItems();
    };

    window.onGridProdSelect = function (idx, prodId) {
        const prod = productMaster.find(p => p.id === prodId);
        if (!prod) return;

        currentModalItems[idx].prod_id = prod.id;
        currentModalItems[idx].prod_code = prod.code;
        currentModalItems[idx].desc = prod.desc;
        currentModalItems[idx].uom = prod.uom;
        currentModalItems[idx].price = prod.price;
        currentModalItems[idx].currency = prod.currency;
        currentModalItems[idx].vat = prod.vat;

        recalculateRow(idx);
        renderGridItems();
    };

    window.updateGridField = function (idx, field, val) {
        if (!currentModalItems[idx]) return;

        if (field === 'disc_amt_manual') {
            currentModalItems[idx].disc_amt = val;
            const gross = currentModalItems[idx].gross || 0;
            currentModalItems[idx].disc_rate = gross > 0 ? ((val / gross) * 100).toFixed(2) : 0;
        } else {
            currentModalItems[idx][field] = val;
            if (field === 'qty' || field === 'price' || field === 'disc_rate' || field === 'vat') {
                recalculateRow(idx);
            }
        }

        renderGridItems();
    };

    function recalculateRow(idx) {
        const item = currentModalItems[idx];
        const qty = item.qty || 0;
        const price = item.price || 0;
        const gross = qty * price;
        item.gross = gross;

        const discRate = item.disc_rate || 0;
        const discAmt = gross * (discRate / 100);
        item.disc_amt = discAmt;

        const grossAfterDisc = gross - discAmt;
        const vatAmt = item.vat === '12%' ? (grossAfterDisc * 0.12) : 0;

        const net = grossAfterDisc + vatAmt;
        item.net = net;
        item.amount = net;
    }

    window.calculateSITotals = function () {
        const itemCount = currentModalItems.length;
        let totalGross = 0;
        let totalDisc = 0;
        let totalNet = 0;

        currentModalItems.forEach(item => {
            totalGross += (parseFloat(item.gross) || 0);
            totalDisc += (parseFloat(item.disc_amt) || 0);
            totalNet += (parseFloat(item.net) || 0);
        });

        const grossAfterDisc = totalGross - totalDisc;
        const vatCode = document.getElementById('siVatCode').value;
        const vatAmt = vatCode === 'VAT Output (12%)' ? (grossAfterDisc * 0.12) : 0;
        const grandTotal = grossAfterDisc + vatAmt;
        const curr = document.getElementById('siCurrency').value || 'PHP';

        // Update Inputs
        document.getElementById('siItemCount').value = itemCount;
        document.getElementById('siGrossAmount').value = totalGross.toFixed(2);
        document.getElementById('siDiscountAmount').value = totalDisc.toFixed(2);
        document.getElementById('siVatAmount').value = vatAmt.toFixed(2);
        document.getElementById('siNetAmount').value = grandTotal.toFixed(2);
        document.getElementById('siTotalAmount').value = grandTotal.toFixed(2);

        // Visual Highlight Strip
        document.getElementById('stripGross').textContent = fmtCurrency(totalGross, curr);
        document.getElementById('stripDisc').textContent = `-${fmtCurrency(totalDisc, curr)}`;
        document.getElementById('stripVat').textContent = `+${fmtCurrency(vatAmt, curr)}`;
        document.getElementById('stripTotal').textContent = fmtCurrency(grandTotal, curr);
    };

    // 7. CREATE & EDIT MODAL OPERATIONS

    window.openCreateSIModal = function () {
        closeAllActionMenus();
        document.getElementById('siForm').reset();
        document.getElementById('siRecordId').value = '';
        siModalTitle.textContent = 'Create Sales Invoice';
        btnSiSubmitText.textContent = 'Save Sales Invoice';

        // Auto-generate SI Number
        const nextId = invoicesData.length > 0 ? Math.max(...invoicesData.map(s => s.id)) + 1 : 1;
        document.getElementById('siNumber').value = `SI-2026-${String(nextId).padStart(3, '0')}`;

        // Defaults
        const today = new Date().toISOString().split('T')[0];
        const nextMonth = new Date(Date.now() + 30 * 86400000).toISOString().split('T')[0];
        document.getElementById('siTxnDate').value = today;
        document.getElementById('siDueDate').value = nextMonth;
        document.getElementById('siPreparedBy').value = 'Karl Bautista';
        document.getElementById('siCheckedBy').value = 'Maria Santos';
        document.getElementById('siApprovedBy').value = 'Juan Dela Cruz';
        document.getElementById('siBranch').value = 'Head Office (Mandaluyong)';
        document.getElementById('siStatus').value = 'Not Yet Posted';
        document.getElementById('siCurrency').value = 'PHP';
        document.getElementById('siRate').value = '1.0000';
        document.getElementById('siPaymentTerm').value = '30 Days';
        document.getElementById('siShipment').value = 'Company Truck Delivery';
        document.getElementById('siCountryOrigin').value = 'Philippines';
        document.getElementById('siVatCode').value = 'VAT Output (12%)';
        document.getElementById('siReprintCount').value = '0';

        // Initial default line item
        currentModalItems = [
            {
                prod_id: 'PRD-001',
                prod_code: 'PRD-8821',
                desc: 'Industrial IoT Controller Assembly Rev A.0',
                uom: 'PCS',
                qty: 10,
                price: 1850.00,
                currency: 'PHP',
                vat: '12%',
                gross: 18500.00,
                disc_rate: 0,
                disc_amt: 0.00,
                net: 20720.00,
                amount: 20720.00,
                dr_number: 'DR-2026-095',
                dr_date: today,
                so_number: 'SO-2026-001',
                so_ref: 'PO-SMC-9901',
                so_balance: 0
            }
        ];

        renderGridItems();

        siFormModalBackdrop.classList.add('show');
        document.getElementById('siCustomer').focus();
        if (window.lucide) window.lucide.createIcons();
    };

    window.openEditSIModal = function (id) {
        closeAllActionMenus();
        const rec = invoicesData.find(r => r.id === id);
        if (!rec) return;

        document.getElementById('siForm').reset();
        document.getElementById('siRecordId').value = rec.id;
        siModalTitle.textContent = `Edit Sales Invoice: ${rec.si_number}`;
        btnSiSubmitText.textContent = 'Update Sales Invoice';

        document.getElementById('siBranch').value = rec.branch;
        document.getElementById('siNumber').value = rec.si_number;
        document.getElementById('siTxnDate').value = rec.si_txn_date;
        document.getElementById('siDueDate').value = rec.due_date;
        document.getElementById('siStatus').value = rec.status;
        document.getElementById('siRefNumber').value = rec.ref_number || '';
        document.getElementById('siDtsRefNumber').value = rec.dts_ref || '';
        document.getElementById('siReprintCount').value = rec.reprint_count || 0;

        document.getElementById('siCustomer').value = rec.customer;
        document.getElementById('siBillTo').value = rec.bill_to;
        document.getElementById('siPaymentTerm').value = rec.payment_term;
        document.getElementById('siAddress').value = rec.address;
        document.getElementById('siShipment').value = rec.shipment;
        document.getElementById('siCountryOrigin').value = rec.country_origin;

        document.getElementById('siCurrency').value = rec.currency;
        document.getElementById('siRate').value = parseFloat(rec.rate || 1).toFixed(4);
        document.getElementById('siVatCode').value = rec.vat_code;

        document.getElementById('siPreparedBy').value = rec.prepared_by;
        document.getElementById('siCheckedBy').value = rec.checked_by;
        document.getElementById('siApprovedBy').value = rec.approved_by;
        document.getElementById('siRemarks').value = rec.remarks || '';

        // Line Items
        currentModalItems = rec.grid_items ? JSON.parse(JSON.stringify(rec.grid_items)) : [];
        renderGridItems();

        siFormModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    window.closeSIModal = function () {
        siFormModalBackdrop.classList.remove('show');
    };

    // Form Submit
    window.handleSIFormSubmit = function (event) {
        event.preventDefault();

        const idVal = document.getElementById('siRecordId').value;
        const branch = document.getElementById('siBranch').value;
        const siNum = document.getElementById('siNumber').value.trim();
        const txnDate = document.getElementById('siTxnDate').value;
        const dueDate = document.getElementById('siDueDate').value;
        const status = document.getElementById('siStatus').value;
        const refNum = document.getElementById('siRefNumber').value.trim();
        const dtsRef = document.getElementById('siDtsRefNumber').value.trim();
        const reprintCount = parseInt(document.getElementById('siReprintCount').value, 10) || 0;

        const customer = document.getElementById('siCustomer').value.trim();
        const billTo = document.getElementById('siBillTo').value.trim();
        const paymentTerm = document.getElementById('siPaymentTerm').value;
        const address = document.getElementById('siAddress').value.trim();
        const shipment = document.getElementById('siShipment').value;
        const countryOrigin = document.getElementById('siCountryOrigin').value;

        const currency = document.getElementById('siCurrency').value;
        const rate = parseFloat(document.getElementById('siRate').value) || 1.0;
        const vatCode = document.getElementById('siVatCode').value;

        const preparedBy = document.getElementById('siPreparedBy').value.trim();
        const checkedBy = document.getElementById('siCheckedBy').value.trim();
        const approvedBy = document.getElementById('siApprovedBy').value.trim();
        const remarks = document.getElementById('siRemarks').value.trim();

        const gross = parseFloat(document.getElementById('siGrossAmount').value) || 0;
        const disc = parseFloat(document.getElementById('siDiscountAmount').value) || 0;
        const vat = parseFloat(document.getElementById('siVatAmount').value) || 0;
        const net = parseFloat(document.getElementById('siNetAmount').value) || 0;
        const total = parseFloat(document.getElementById('siTotalAmount').value) || 0;
        const itemCount = currentModalItems.length;

        if (idVal) {
            // Update
            const index = invoicesData.findIndex(s => s.id === parseInt(idVal, 10));
            if (index !== -1) {
                const prev = invoicesData[index];
                invoicesData[index] = {
                    ...prev,
                    branch, si_number: siNum, si_txn_date: txnDate, due_date: dueDate,
                    status, ref_number: refNum, dts_ref: dtsRef, reprint_count: reprintCount,
                    customer, bill_to: billTo, payment_term: paymentTerm, address, shipment,
                    country_origin: countryOrigin, currency, rate, vat_code: vatCode,
                    prepared_by: preparedBy, checked_by: checkedBy, approved_by: approvedBy,
                    gross_amount: gross, discount_amount: disc, vat_amount: vat,
                    net_amount: net, total_amount: total, item_count: itemCount,
                    balance: (net - (prev.credit_amount || 0)),
                    remarks, grid_items: JSON.parse(JSON.stringify(currentModalItems))
                };
            }
        } else {
            // Create
            const newId = invoicesData.length > 0 ? Math.max(...invoicesData.map(s => s.id)) + 1 : 1;
            const newInv = {
                id: newId,
                branch, si_number: siNum, si_txn_date: txnDate, due_date: dueDate,
                status, ref_number: refNum, dts_ref: dtsRef, reprint_count: reprintCount,
                customer, bill_to: billTo, payment_term: paymentTerm, address, shipment,
                country_origin: countryOrigin, currency, rate, vat_code: vatCode,
                prepared_by: preparedBy, checked_by: checkedBy, approved_by: approvedBy,
                gross_amount: gross, discount_amount: disc, vat_amount: vat,
                net_amount: net, total_amount: total, credit_amount: 0.00,
                balance: net, item_count: itemCount, remarks,
                grid_items: JSON.parse(JSON.stringify(currentModalItems)),
                deleted_date: '—'
            };
            invoicesData.unshift(newInv);
        }

        closeSIModal();
        applyFilters();
    };

    // 8. VIEW MODAL OPERATIONS

    window.openViewSIModal = function (id) {
        closeAllActionMenus();
        const rec = invoicesData.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;

        document.getElementById('viewSiTitle').textContent = `Sales Invoice: ${rec.si_number}`;
        document.getElementById('viewSiSub').textContent = `${rec.customer} • Prepared By: ${rec.prepared_by}`;

        document.getElementById('vSiNum').textContent = rec.si_number;
        document.getElementById('vSiBranch').textContent = rec.branch;
        document.getElementById('vSiTxnDate').textContent = rec.si_txn_date;
        document.getElementById('vSiDueDate').textContent = rec.due_date;
        document.getElementById('vSiRefNum').textContent = rec.ref_number || 'None';
        document.getElementById('vSiDtsRef').textContent = rec.dts_ref || 'None';

        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
        let statusBadgeClass = 'status-pill-pending';
        if (rec.status === 'Posted') statusBadgeClass = 'status-pill-posted';
        else if (rec.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';
        if (isDeleted) statusBadgeClass = 'status-pill-archived';

        document.getElementById('vSiStatusPill').innerHTML = `
            <span class="status-pill ${statusBadgeClass}">${isDeleted ? `Archived (${rec.deleted_date})` : rec.status}</span>
        `;
        document.getElementById('vSiReprints').textContent = rec.reprint_count || 0;

        document.getElementById('vSiCustomer').textContent = rec.customer;
        document.getElementById('vSiBillTo').textContent = rec.bill_to;
        document.getElementById('vSiAddress').textContent = rec.address;
        document.getElementById('vSiPaymentTerm').textContent = rec.payment_term;
        document.getElementById('vSiShipment').textContent = rec.shipment;
        document.getElementById('vSiOrigin').textContent = rec.country_origin;
        document.getElementById('vSiDeletedDate').textContent = rec.deleted_date || '—';

        // Line Items
        const gridBody = document.getElementById('viewGridBody');
        gridBody.innerHTML = '';
        const items = rec.grid_items || [];
        document.getElementById('vSiItemCount').textContent = items.length;

        if (items.length === 0) {
            gridBody.innerHTML = '<tr><td colspan="13" class="text-center text-muted" style="padding: 12px;">No line items attached</td></tr>';
        } else {
            items.forEach(it => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-code">${escapeHtml(it.prod_id)}</td>
                    <td class="font-code font-bold">${escapeHtml(it.prod_code)}</td>
                    <td>${escapeHtml(it.desc)}</td>
                    <td>${escapeHtml(it.uom)}</td>
                    <td class="text-right font-numeric font-bold">${Number(it.qty).toLocaleString()}</td>
                    <td class="text-right font-numeric">${fmtCurrency(it.price, it.currency)}</td>
                    <td>${escapeHtml(it.vat)}</td>
                    <td class="text-right font-numeric">${fmtCurrency(it.gross, it.currency)}</td>
                    <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(it.disc_amt, it.currency)}</td>
                    <td class="text-right font-numeric font-bold">${fmtCurrency(it.net, it.currency)}</td>
                    <td class="text-right font-numeric font-bold" style="color: #2563eb;">${fmtCurrency(it.amount, it.currency)}</td>
                    <td class="font-code">${escapeHtml(it.dr_number || '—')}</td>
                    <td class="font-code">${escapeHtml(it.so_number || '—')}</td>
                `;
                gridBody.appendChild(tr);
            });
        }

        // Financials
        document.getElementById('vSiCurrencyRate').textContent = `${rec.currency} @ ${parseFloat(rec.rate || 1).toFixed(4)}`;
        document.getElementById('vSiGross').textContent = fmtCurrency(rec.gross_amount, rec.currency);
        document.getElementById('vSiDiscount').textContent = `-${fmtCurrency(rec.discount_amount, rec.currency)}`;
        document.getElementById('vSiVat').textContent = `${rec.vat_code} — ${fmtCurrency(rec.vat_amount, rec.currency)}`;
        document.getElementById('vSiNet').textContent = fmtCurrency(rec.net_amount, rec.currency);
        document.getElementById('vSiCredit').textContent = fmtCurrency(rec.credit_amount || 0, rec.currency);
        document.getElementById('vSiBalance').textContent = fmtCurrency(rec.balance, rec.currency);

        document.getElementById('vSiPreparedBy').textContent = rec.prepared_by;
        document.getElementById('vSiCheckedBy').textContent = rec.checked_by;
        document.getElementById('vSiApprovedBy').textContent = rec.approved_by;
        document.getElementById('vSiRemarks').textContent = rec.remarks || 'None';

        if (btnViewEditShortcut) {
            btnViewEditShortcut.onclick = function () {
                closeSIViewModal();
                openEditSIModal(id);
            };
        }

        siViewModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    window.closeSIViewModal = function () {
        siViewModalBackdrop.classList.remove('show');
    };

    // 9. DELETE / ARCHIVE MODAL OPERATIONS

    window.openDeleteSIModal = function (id) {
        closeAllActionMenus();
        const rec = invoicesData.find(r => r.id === id);
        if (!rec) return;

        deleteTargetId = id;
        document.getElementById('deleteTargetSiNum').textContent = rec.si_number;
        document.getElementById('deleteTargetBillTo').textContent = rec.bill_to;

        siDeleteModalBackdrop.classList.add('show');
    };

    window.closeSIDeleteModal = function () {
        siDeleteModalBackdrop.classList.remove('show');
        deleteTargetId = null;
    };

    window.confirmDeleteSI = function () {
        if (!deleteTargetId) return;
        const rec = invoicesData.find(r => r.id === deleteTargetId);
        if (rec) {
            rec.deleted_date = new Date().toISOString().split('T')[0];
            rec.status = 'Cancelled';
        }
        closeSIDeleteModal();
        applyFilters();
    };

    // 10. REFRESH & EXPORT ACTIONS

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            setTimeout(() => {
                if (refreshIcon) refreshIcon.classList.remove('spin-anim');
                applyFilters();
            }, 450);
        });
    }

    if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            exportMenu.classList.toggle('show');
        });
    }

    window.exportInvoices = function (format) {
        if (exportMenu) exportMenu.classList.remove('show');
        const data = getCurrentlyFilteredData();

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csv = 'SI#,Branch,Bill To,Prepared By,SI Txn Date,Due Date,Currency,Gross Amount,Disc Amount,Net Amount,Credit Amount,Balance,Status,Deleted Date\n';
            data.forEach(r => {
                csv += `"${r.si_number}","${r.branch}","${r.bill_to}","${r.prepared_by}","${r.si_txn_date}","${r.due_date}","${r.currency}",${r.gross_amount},${r.discount_amount},${r.net_amount},${r.credit_amount || 0},${r.balance},"${r.status}","${r.deleted_date}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Invoices_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        } else if (format === 'excel') {
            let html = '<table border="1"><tr><th>SI#</th><th>Branch</th><th>Bill To</th><th>Prepared By</th><th>SI Txn Date</th><th>Due Date</th><th>Currency</th><th>Gross Amount</th><th>Disc Amount</th><th>Net Amount</th><th>Credit Amount</th><th>Balance</th><th>Status</th><th>Deleted Date</th></tr>';
            data.forEach(r => {
                html += `<tr><td>${r.si_number}</td><td>${r.branch}</td><td>${r.bill_to}</td><td>${r.prepared_by}</td><td>${r.si_txn_date}</td><td>${r.due_date}</td><td>${r.currency}</td><td>${r.gross_amount}</td><td>${r.discount_amount}</td><td>${r.net_amount}</td><td>${r.credit_amount || 0}</td><td>${r.balance}</td><td>${r.status}</td><td>${r.deleted_date}</td></tr>`;
            });
            html += '</table>';
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Invoices_${new Date().toISOString().split('T')[0]}.xls`;
            a.click();
            URL.revokeObjectURL(url);
        }
    };

    // Initial render
    renderSITable();

})();
</script>
@endpush

