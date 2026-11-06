@extends('layouts.app')

@section('title', 'Sales Order Directory — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/SalesOrder/salesorder.css') }}">
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
            <span class="bc-current">Sales Order</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="soSearchInput" placeholder="Search SO#, Customer, Branch, Product, Ref#..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Sales Order Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh Sales Orders">
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
                    <button type="button" class="export-option" onclick="exportSalesOrders('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable Sales Order schedule</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportSalesOrders('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook dataset</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportSalesOrders('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated list</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Sales Order Button -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal" onclick="openCreateSOModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Sales Order</span>
            </button>
        </div>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Total Orders</span>
            <span class="kpi-value" id="kpiTotalOrders">0</span>
            <span class="kpi-sub">Commercial bookings</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Gross Amount</span>
            <span class="kpi-value" id="kpiTotalGross">₱0.00</span>
            <span class="kpi-sub">Pre-discount order value</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Posted Orders</span>
            <span class="kpi-value" style="color: #059669;" id="kpiPostedOrders">0</span>
            <span class="kpi-sub">Approved for fulfillment</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Not Yet Posted</span>
            <span class="kpi-value" style="color: #d97706;" id="kpiPendingOrders">0</span>
            <span class="kpi-sub">Pending verification</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Header -->
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Sales Order Master Register</span>
                    <span class="count-pill" id="totalRecordsBadge">0 Orders</span>
                    <span class="selection-pill" id="selectionCountBadge">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectionText">0 selected</span>
                    </span>
                </div>
                <div class="panel-caption-meta">
                    Commercial booking commitments, delivery schedules & billing
                </div>
            </div>

            <!-- Horizontal Scroll Table Container -->
            <div class="table-scroll-container">
                <table class="erp-table" id="soMainTable">
                    <thead>
                        <tr>

                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" class="erp-cb" id="selectAllSOCb" onchange="toggleSelectAllSO(this.checked)" title="Select / Deselect All">
                            </th>

                            <th style="min-width: 120px;">SO#</th>

                            <th style="min-width: 140px;">Branch</th>

                            <th style="min-width: 200px;">Customer</th>

                            <th style="min-width: 140px;">Prepared By</th>

                            <th style="min-width: 110px;">SO Date</th>

                            <th style="min-width: 110px;">Ref #</th>

                            <th style="min-width: 180px;">Product Name</th>

                            <th style="min-width: 240px;">Product Description</th>

                            <th style="min-width: 110px;" class="text-right">Unit Price</th>

                            <th style="min-width: 80px;" class="text-right">Qty</th>

                            <th style="min-width: 80px;" class="text-right">Bal Qty</th>

                            <th style="min-width: 120px;" class="text-right">Gross Amount</th>

                            <th style="min-width: 110px;" class="text-right">Disc Amount</th>

                            <th style="min-width: 130px;" class="text-center">Status</th>

                            <th style="min-width: 110px;" class="text-center">Deleted Date</th>

                            <th style="width: 70px;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="soTableBody">
                        <!-- Populated dynamically via JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Panel Footer -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="paginationSummary">Showing 0 of 0 sales order entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<div class="modal-backdrop" id="soFormModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="soModalTitle">Create Sales Order</h3>
                <span class="modal-subtitle">Configure commercial order headers, customer billing, item lines, and financial parameters</span>
            </div>
            <button type="button" class="modal-close-btn" id="btnSoModalCloseCross" onclick="closeSOModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="soForm" onsubmit="handleSOFormSubmit(event)">
            <input type="hidden" id="soRecordId" value="">

            <div class="modal-body" style="max-height: calc(85vh - 120px); overflow-y: auto;">

                <!-- SECTION 1: HEADER & ORDER IDENTIFIERS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">1</span>
                        <span class="section-label">Order Identifiers & Scheduling</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soBranch">Branch <span class="required">*</span></label>
                            <select id="soBranch" class="form-select" required>
                                <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                                <option value="Makati Sales Center">Makati Sales Center</option>
                                <option value="Laguna Main Plant Site">Laguna Main Plant Site</option>
                                <option value="Cebu Distribution Depot">Cebu Distribution Depot</option>
                                <option value="Davao Regional Hub">Davao Regional Hub</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soNumber">SO# <span class="required">*</span></label>
                            <input type="text" id="soNumber" class="form-input font-code font-bold" placeholder="e.g. SO-2026-001" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soDate">SO Date <span class="required">*</span></label>
                            <input type="date" id="soDate" class="form-input" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soExpDelDate">Expected Del Date <span class="required">*</span></label>
                            <input type="date" id="soExpDelDate" class="form-input" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soStatus">Status <span class="required">*</span></label>
                            <select id="soStatus" class="form-select" required>
                                <option value="Not Yet Posted">Not Yet Posted</option>
                                <option value="Posted">Posted</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soPreparedBy">Prepared By <span class="required">*</span></label>
                            <input type="text" id="soPreparedBy" class="form-input" placeholder="e.g. Karl Bautista" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soRefNumber">Ref #</label>
                            <input type="text" id="soRefNumber" class="form-input font-code" placeholder="e.g. PO-SMC-9812">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soRePrints">Re-Prints</label>
                            <input type="number" id="soRePrints" class="form-input font-numeric" min="0" value="0">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: CUSTOMER & LOGISTICS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">2</span>
                        <span class="section-label">Customer & Commercial Logistics</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soCustomer">Customer Name <span class="required">*</span></label>
                            <input type="text" id="soCustomer" class="form-input" placeholder="e.g. San Miguel Corporation" required oninput="syncBillTo(this.value)">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soBillTo">Bill To <span class="required">*</span></label>
                            <input type="text" id="soBillTo" class="form-input" placeholder="e.g. San Miguel Corp — Accounts Payable" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soPaymentTerms">Payment Terms <span class="required">*</span></label>
                            <select id="soPaymentTerms" class="form-select" required>
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
                            <label for="soAddress">Delivery Address <span class="required">*</span></label>
                            <input type="text" id="soAddress" class="form-input" placeholder="e.g. 40 San Miguel Ave, Ortigas Center, Mandaluyong City" required>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soShipment">Shipment Method <span class="required">*</span></label>
                            <select id="soShipment" class="form-select" required>
                                <option value="Company Truck Delivery">Company Truck Delivery</option>
                                <option value="Customer Plant Pickup">Customer Plant Pickup</option>
                                <option value="Third-Party Freight / Logistics">Third-Party Freight / Logistics</option>
                                <option value="Courier Express Air">Courier Express Air</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soCountryOrigin">Country of Origin <span class="required">*</span></label>
                            <select id="soCountryOrigin" class="form-select" required>
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

                <!-- SECTION 3: PRODUCT ITEM SPECIFICATIONS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">3</span>
                        <span class="section-label">Product Item Line & Quantity</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soProductName">Product Name <span class="required">*</span></label>
                            <input type="text" id="soProductName" class="form-input font-bold" placeholder="e.g. PRD-8821 Mainboard" required>
                        </div>
                        <div class="form-group flex-2">
                            <label for="soProductDesc">Product Description <span class="required">*</span></label>
                            <input type="text" id="soProductDesc" class="form-input" placeholder="e.g. Industrial IoT High-Density Controller Assembly Rev A.0" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soUnitPrice">Unit Price <span class="required">*</span></label>
                            <input type="number" id="soUnitPrice" class="form-input font-numeric text-right" step="0.01" min="0" placeholder="0.00" required oninput="calculateSOFinancials()">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soQty">Order Qty <span class="required">*</span></label>
                            <input type="number" id="soQty" class="form-input font-numeric text-right" min="1" placeholder="1" required oninput="syncBalQty(this.value); calculateSOFinancials()">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soBalQty">Bal Qty (Balance) <span class="required">*</span></label>
                            <input type="number" id="soBalQty" class="form-input font-numeric text-right" min="0" placeholder="0" required>
                        </div>
                    </div>
                </div>

                <!-- SECTION 4: FINANCIALS, DISCOUNT & TAXES -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">4</span>
                        <span class="section-label">Pricing, Currency & Tax Calculations</span>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soCurrency">Currency <span class="required">*</span></label>
                            <select id="soCurrency" class="form-select" onchange="calculateSOFinancials()">
                                <option value="PHP">PHP (₱)</option>
                                <option value="USD">USD ($)</option>
                                <option value="JPY">JPY (¥)</option>
                                <option value="EUR">EUR (€)</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soRate">Exchange Rate</label>
                            <input type="number" id="soRate" class="form-input font-numeric text-right" step="0.0001" value="1.0000">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soDiscountType">Discount Type</label>
                            <select id="soDiscountType" class="form-select" onchange="calculateSOFinancials()">
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (₱)</option>
                            </select>
                        </div>
                        <div class="form-group flex-1">
                            <label for="soDiscountValue">Discount Value</label>
                            <input type="number" id="soDiscountValue" class="form-input font-numeric text-right" step="0.01" min="0" value="0.00" oninput="calculateSOFinancials()">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soVatCode">VAT Code <span class="required">*</span></label>
                            <select id="soVatCode" class="form-select" onchange="calculateSOFinancials()">
                                <option value="VAT Output (12%)">VAT Output (12%)</option>
                                <option value="Zero-Rated (0%)">Zero-Rated (0%)</option>
                                <option value="VAT Exempt">VAT Exempt (0%)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group flex-1">
                            <label for="soGrossAmount">Gross Amount</label>
                            <input type="number" id="soGrossAmount" class="form-input font-numeric text-right font-bold" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soDiscAmount">Disc Amount</label>
                            <input type="number" id="soDiscAmount" class="form-input font-numeric text-right" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soVatAmount">VAT Amount</label>
                            <input type="number" id="soVatAmount" class="form-input font-numeric text-right" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soNetAmount">Net Amount</label>
                            <input type="number" id="soNetAmount" class="form-input font-numeric text-right font-bold" readonly value="0.00">
                        </div>
                        <div class="form-group flex-1">
                            <label for="soTotalAmount">Total Payable Amount</label>
                            <input type="number" id="soTotalAmount" class="form-input font-numeric text-right font-bold" style="color: #2563eb;" readonly value="0.00">
                        </div>
                    </div>

                    <!-- Live Calculation Visual Strip -->
                    <div class="financial-strip-card">
                        <div class="financial-item">
                            <span class="financial-item-label">Gross Value</span>
                            <span class="financial-item-value" id="stripGross">₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Deducted Discount</span>
                            <span class="financial-item-value" style="color: #dc2626;" id="stripDisc">-₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Calculated Tax (VAT)</span>
                            <span class="financial-item-value" id="stripVat">+₱0.00</span>
                        </div>
                        <div class="financial-item">
                            <span class="financial-item-label">Net Payable Order Amount</span>
                            <span class="financial-item-value accent-total" id="stripTotal">₱0.00</span>
                        </div>
                    </div>
                </div>

                <!-- SECTION 5: REMARKS -->
                <div class="form-section-card">
                    <div class="section-badge-title">
                        <span class="badge-num">5</span>
                        <span class="section-label">Order Notes & Logistics Remarks</span>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="soRemarks">Remarks</label>
                        <textarea id="soRemarks" class="form-textarea" rows="2" placeholder="Provide any customer dispatch notes, quality inspect instructions, delivery schedules, etc."></textarea>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="closeSOModal()">Cancel</button>
                <button type="submit" class="btn-primary" id="btnSoSubmit">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSoSubmitText">Save Sales Order</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="soViewModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-large">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="viewSoTitle">Sales Order: SO-2026-001</h3>
                <span class="modal-subtitle" id="viewSoSub">Commercial order dossier & billing details</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSOViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" style="max-height: calc(85vh - 120px); overflow-y: auto;">
            <div class="view-dossier-grid">
                <!-- Card 1: Order Information -->
                <div class="dossier-card">
                    <div class="dossier-card-title">Order Information</div>
                    <div class="dossier-row"><span class="dossier-key">SO Number:</span><span class="dossier-val font-code font-bold" id="vSoNum">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Branch:</span><span class="dossier-val" id="vSoBranch">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">SO Date:</span><span class="dossier-val" id="vSoDate">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Expected Delivery:</span><span class="dossier-val" id="vSoExpDate">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Prepared By:</span><span class="dossier-val" id="vSoPreparedBy">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Ref Number:</span><span class="dossier-val font-code" id="vSoRefNum">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Status:</span><span class="dossier-val" id="vSoStatusPill">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Re-Prints:</span><span class="dossier-val" id="vSoRePrints">—</span></div>
                </div>

                <!-- Card 2: Customer & Shipping -->
                <div class="dossier-card">
                    <div class="dossier-card-title">Customer & Dispatch Details</div>
                    <div class="dossier-row"><span class="dossier-key">Customer:</span><span class="dossier-val" id="vSoCustomer">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Bill To:</span><span class="dossier-val" id="vSoBillTo">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Delivery Address:</span><span class="dossier-val" style="max-width: 250px;" id="vSoAddress">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Payment Terms:</span><span class="dossier-val" id="vSoPaymentTerms">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Shipment Method:</span><span class="dossier-val" id="vSoShipment">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Country of Origin:</span><span class="dossier-val" id="vSoOrigin">—</span></div>
                    <div class="dossier-row"><span class="dossier-key">Archived Date:</span><span class="dossier-val" id="vSoDeletedDate">—</span></div>
                </div>
            </div>

            <!-- Card 3: Line Item & Specifications -->
            <div class="dossier-card" style="margin-top: 14px;">
                <div class="dossier-card-title">Product Line Item Details</div>
                <div class="dossier-row"><span class="dossier-key">Product Name:</span><span class="dossier-val font-bold" id="vSoProdName">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Description:</span><span class="dossier-val" id="vSoProdDesc">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Unit Price:</span><span class="dossier-val font-numeric font-bold" id="vSoUnitPrice">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Order Quantity:</span><span class="dossier-val font-numeric font-bold" id="vSoQty">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Balance Quantity:</span><span class="dossier-val font-numeric" id="vSoBalQty">—</span></div>
            </div>

            <!-- Card 4: Financial Summary -->
            <div class="dossier-card" style="margin-top: 14px; background: #f8fafc;">
                <div class="dossier-card-title">Commercial Financial Computation</div>
                <div class="dossier-row"><span class="dossier-key">Currency & Rate:</span><span class="dossier-val" id="vSoCurrencyRate">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Gross Amount:</span><span class="dossier-val font-numeric font-bold" id="vSoGross">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Discount:</span><span class="dossier-val font-numeric" style="color: #dc2626;" id="vSoDiscount">—</span></div>
                <div class="dossier-row"><span class="dossier-key">VAT Code & Tax:</span><span class="dossier-val font-numeric" id="vSoVat">—</span></div>
                <div class="dossier-row"><span class="dossier-key">Net Order Amount:</span><span class="dossier-val font-numeric font-bold" style="font-size: 14px;" id="vSoNet">—</span></div>
                <div class="dossier-row" style="border-top: 1px solid #cbd5e1; padding-top: 8px; margin-top: 4px;">
                    <span class="dossier-key" style="font-weight: 700; color: #0f172a;">Total Payable:</span>
                    <span class="dossier-val font-numeric font-bold" style="color: #2563eb; font-size: 16px;" id="vSoTotal">—</span>
                </div>
                <div class="dossier-row" style="margin-top: 6px;"><span class="dossier-key">Remarks:</span><span class="dossier-val" id="vSoRemarks">—</span></div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeSOViewModal()">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit" class="btn-icon"></i>
                <span>Edit Order</span>
            </button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="soDeleteModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog modal-dialog-small">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" style="color: #dc2626;">Archive Sales Order</h3>
                <span class="modal-subtitle">Soft-delete order confirmation</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeSODeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p style="font-size: 13px; color: #334155; line-height: 1.5;">
                Are you sure you want to mark Sales Order <strong id="deleteTargetSoNum" class="font-code">SO-2026-001</strong> as archived?
            </p>
            <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                Customer: <span id="deleteTargetCustomer" class="font-bold text-dark"></span>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeSODeleteModal()">Cancel</button>
            <button type="button" class="btn-primary" style="background: #dc2626; border-color: #dc2626;" onclick="confirmDeleteSO()">
                <span>Archive Order</span>
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    // Global HTML Escape helper to prevent XSS
    function escapeHtml(str) {
        if (!str && str !== 0) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Number formatter for currency
    function fmtCurrency(val, curr = 'PHP') {
        const symbol = curr === 'PHP' ? '₱' : (curr === 'USD' ? '$' : (curr === 'EUR' ? '€' : '¥'));
        return `${symbol}${parseFloat(val || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    }

    // 1. DATA MASTERSET

    let salesOrdersData = [
        {
            id: 1,
            so_number: 'SO-2026-001',
            branch: 'Head Office (Mandaluyong)',
            customer: 'San Miguel Corporation',
            bill_to: 'San Miguel Corp — Accounts Payable',
            address: '40 San Miguel Ave, Ortigas Center, Mandaluyong City',
            prepared_by: 'Karl Bautista',
            so_date: '2026-11-01',
            exp_del_date: '2026-11-15',
            ref_number: 'PO-SMC-9921',
            product_name: 'PRD-8821 Mainboard',
            product_desc: 'Industrial IoT Controller Assembly Rev A.0',
            unit_price: 1850.00,
            qty: 100,
            bal_qty: 100,
            currency: 'PHP',
            rate: 1.0000,
            payment_terms: '30 Days',
            shipment: 'Company Truck Delivery',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            discount_type: 'percentage',
            discount_value: 5.0,
            disc_amount: 9250.00,
            gross_amount: 185000.00,
            vat_amount: 21090.00,
            net_amount: 196840.00,
            total_amount: 196840.00,
            re_prints: 0,
            status: 'Posted',
            remarks: 'Priority dispatch for Laguna Brewery manufacturing line.',
            deleted_date: '—'
        },
        {
            id: 2,
            so_number: 'SO-2026-002',
            branch: 'Makati Sales Center',
            customer: 'Universal Robina Corporation',
            bill_to: 'URC Corporate Procurement & Finance',
            address: '8th Floor Tera Tower, Bridgetowne, Quezon City',
            prepared_by: 'Juan Dela Cruz',
            so_date: '2026-11-02',
            exp_del_date: '2026-11-18',
            ref_number: 'PO-URC-4412',
            product_name: 'ENC-7700 Shield',
            product_desc: 'IP67 Waterproof Enclosure CNC Machined',
            unit_price: 640.00,
            qty: 250,
            bal_qty: 150,
            currency: 'PHP',
            rate: 1.0000,
            payment_terms: '60 Days',
            shipment: 'Third-Party Freight / Logistics',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            discount_type: 'fixed',
            discount_value: 5000.00,
            disc_amount: 5000.00,
            gross_amount: 160000.00,
            vat_amount: 18600.00,
            net_amount: 173600.00,
            total_amount: 173600.00,
            re_prints: 1,
            status: 'Posted',
            remarks: 'Partial batch delivered; balance 150 awaiting final assembly.',
            deleted_date: '—'
        },
        {
            id: 3,
            so_number: 'SO-2026-003',
            branch: 'Laguna Main Plant Site',
            customer: 'Robinsons Retail Holdings',
            bill_to: 'Robinsons Supermarket HQ Accounting',
            address: '110 E. Rodriguez Jr. Ave, Libis, Quezon City',
            prepared_by: 'Maria Santos',
            so_date: '2026-11-04',
            exp_del_date: '2026-11-20',
            ref_number: 'PO-RRH-1084',
            product_name: 'SEN-3301 Optic',
            product_desc: 'High-Precision Laser Distance Sensor Module',
            unit_price: 3200.00,
            qty: 50,
            bal_qty: 50,
            currency: 'PHP',
            rate: 1.0000,
            payment_terms: '15th of Following Month',
            shipment: 'Company Truck Delivery',
            country_origin: 'Japan',
            vat_code: 'Zero-Rated (0%)',
            discount_type: 'percentage',
            discount_value: 0.0,
            disc_amount: 0.00,
            gross_amount: 160000.00,
            vat_amount: 0.00,
            net_amount: 160000.00,
            total_amount: 160000.00,
            re_prints: 0,
            status: 'Not Yet Posted',
            remarks: 'Pending commercial review of PEZA tax exemption certificate.',
            deleted_date: '—'
        },
        {
            id: 4,
            so_number: 'SO-2026-004',
            branch: 'Cebu Distribution Depot',
            customer: 'Apex Precision Engineering',
            bill_to: 'Apex Hardware & Parts Accounting',
            address: '88 Industrial Road, Karuhatan, Valenzuela City',
            prepared_by: 'Carlos Reyes',
            so_date: '2026-10-25',
            exp_del_date: '2026-11-05',
            ref_number: 'PO-APX-7719',
            product_name: 'CAB-9900 Harness',
            product_desc: 'Heavy-Duty Industrial Shielded Cable Harness',
            unit_price: 450.00,
            qty: 80,
            bal_qty: 0,
            currency: 'PHP',
            rate: 1.0000,
            payment_terms: 'COD',
            shipment: 'Customer Plant Pickup',
            country_origin: 'Philippines',
            vat_code: 'VAT Output (12%)',
            discount_type: 'percentage',
            discount_value: 2.5,
            disc_amount: 900.00,
            gross_amount: 36000.00,
            vat_amount: 4212.00,
            net_amount: 39312.00,
            total_amount: 39312.00,
            re_prints: 0,
            status: 'Cancelled',
            remarks: 'Customer requested cancellation due to engineering redesign.',
            deleted_date: '2026-11-03'
        }
    ];

    let selectedSoIds = new Set();
    let deleteTargetId = null;
    let activeViewId = null;

    // DOM Elements
    const soTableBody = document.getElementById('soTableBody');
    const soSearchInput = document.getElementById('soSearchInput');
    const selectAllSOCb = document.getElementById('selectAllSOCb');
    const totalRecordsBadge = document.getElementById('totalRecordsBadge');
    const selectionCountBadge = document.getElementById('selectionCountBadge');
    const selectionText = document.getElementById('selectionText');
    const paginationSummary = document.getElementById('paginationSummary');

    // KPI Elements
    const kpiTotalOrders = document.getElementById('kpiTotalOrders');
    const kpiTotalGross = document.getElementById('kpiTotalGross');
    const kpiPostedOrders = document.getElementById('kpiPostedOrders');
    const kpiPendingOrders = document.getElementById('kpiPendingOrders');

    // Modal Backdrops
    const soFormModalBackdrop = document.getElementById('soFormModalBackdrop');
    const soViewModalBackdrop = document.getElementById('soViewModalBackdrop');
    const soDeleteModalBackdrop = document.getElementById('soDeleteModalBackdrop');
    const soModalTitle = document.getElementById('soModalTitle');
    const btnSoSubmitText = document.getElementById('btnSoSubmitText');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');

    // Export & Refresh
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // 2. RENDER TABLE & UPDATE KPI

    function renderSOTable(dataset = salesOrdersData) {
        soTableBody.innerHTML = '';
        const total = dataset.length;

        // Update KPIs
        const activeRows = dataset.filter(r => !r.deleted_date || r.deleted_date === '—');
        kpiTotalOrders.textContent = activeRows.length;
        const totalGrossSum = activeRows.reduce((acc, r) => acc + (parseFloat(r.gross_amount) || 0), 0);
        kpiTotalGross.textContent = `₱${totalGrossSum.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        kpiPostedOrders.textContent = activeRows.filter(r => r.status === 'Posted').length;
        kpiPendingOrders.textContent = activeRows.filter(r => r.status === 'Not Yet Posted').length;

        totalRecordsBadge.textContent = `${total} Order${total === 1 ? '' : 's'}`;
        paginationSummary.textContent = `Showing 1 to ${total} of ${total} sales order entries`;

        if (total === 0) {
            soTableBody.innerHTML = `
                <tr>
                    <td colspan="17" class="text-center text-muted" style="padding: 40px 16px;">
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <i data-lucide="file-question" style="width: 32px; height: 32px; color: #cbd5e1;"></i>
                            <span style="font-size: 13px; font-weight: 500; color: #475569;">No sales orders found</span>
                            <span style="font-size: 11.5px; color: #94a3b8;">Try adjusting your search criteria or create a new sales order.</span>
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
            const isSelected = selectedSoIds.has(row.id);
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
                    <input type="checkbox" class="erp-cb so-row-cb" data-id="${row.id}" ${isSelected ? 'checked' : ''} onchange="toggleSelectSO(${row.id}, this.checked)">
                </td>

                <td>
                    <a href="javascript:void(0)" onclick="openViewSOModal(${row.id})" class="font-code font-bold text-dark" style="color: #2563eb; text-decoration: none;">
                        ${escapeHtml(row.so_number)}
                    </a>
                </td>

                <td>${escapeHtml(row.branch)}</td>

                <td>
                    <span class="font-medium text-dark">${escapeHtml(row.customer)}</span>
                </td>

                <td>${escapeHtml(row.prepared_by)}</td>

                <td class="font-numeric">${escapeHtml(row.so_date)}</td>

                <td class="font-code">${escapeHtml(row.ref_number || '—')}</td>

                <td class="font-medium text-dark">${escapeHtml(row.product_name)}</td>

                <td style="max-width: 260px; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(row.product_desc)}">
                    ${escapeHtml(row.product_desc)}
                </td>

                <td class="font-numeric text-right">${fmtCurrency(row.unit_price, row.currency)}</td>

                <td class="font-numeric text-right font-bold">${Number(row.qty).toLocaleString()}</td>

                <td class="font-numeric text-right">${Number(row.bal_qty).toLocaleString()}</td>

                <td class="font-numeric text-right font-bold">${fmtCurrency(row.gross_amount, row.currency)}</td>

                <td class="font-numeric text-right" style="color: #dc2626;">-${fmtCurrency(row.disc_amount, row.currency)}</td>

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
                        <button type="button" class="action-menu-item" onclick="openViewSOModal(${row.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="openEditSOModal(${row.id})">
                            <i data-lucide="edit"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openDeleteSOModal(${row.id})">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </td>
            `;

            soTableBody.appendChild(tr);
        });

        updateSelectAllState();
        if (window.lucide) window.lucide.createIcons();
    }

    // 3. SELECTION STATE MANAGEMENT

    window.toggleSelectSO = function (id, checked) {
        if (checked) {
            selectedSoIds.add(id);
        } else {
            selectedSoIds.delete(id);
        }
        updateSelectAllState();
        highlightRows();
    };

    window.toggleSelectAllSO = function (checked) {
        const visibleIds = getCurrentlyFilteredData().map(r => r.id);
        if (checked) {
            visibleIds.forEach(id => selectedSoIds.add(id));
        } else {
            visibleIds.forEach(id => selectedSoIds.delete(id));
        }
        updateSelectAllState();
        highlightRows();
    };

    function updateSelectAllState() {
        const visibleIds = getCurrentlyFilteredData().map(r => r.id);
        const totalVisible = visibleIds.length;
        const selectedVisible = visibleIds.filter(id => selectedSoIds.has(id)).length;

        if (selectAllSOCb) {
            selectAllSOCb.checked = totalVisible > 0 && selectedVisible === totalVisible;
            selectAllSOCb.indeterminate = selectedVisible > 0 && selectedVisible < totalVisible;
        }

        const totalSelected = selectedSoIds.size;
        if (totalSelected > 0) {
            selectionCountBadge.classList.add('show');
            selectionText.textContent = `${totalSelected} selected`;
        } else {
            selectionCountBadge.classList.remove('show');
        }
    }

    function highlightRows() {
        document.querySelectorAll('.so-row-cb').forEach(cb => {
            const id = parseInt(cb.getAttribute('data-id'), 10);
            const tr = cb.closest('tr');
            if (tr) {
                if (selectedSoIds.has(id)) {
                    tr.classList.add('row-selected');
                    cb.checked = true;
                } else {
                    tr.classList.remove('row-selected');
                    cb.checked = false;
                }
            }
        });
    }

    // 4. ACTION MENUS (SAFE POSITIONING LOGIC PREVENTS OVERFLOW CLIPPING)

    window.toggleActionMenu = function (event, id) {
        event.stopPropagation();
        const menu = document.getElementById(`actionMenu_${id}`);
        const isShown = menu.classList.contains('show');

        closeAllActionMenus();

        if (!isShown) {
            // Check bounding rect for smart drop-up vs drop-down
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

    // 5. SEARCH & LIVE FILTERING

    function getCurrentlyFilteredData() {
        const query = (soSearchInput.value || '').trim().toLowerCase();
        if (!query) return salesOrdersData;

        return salesOrdersData.filter(r => {
            return (r.so_number && r.so_number.toLowerCase().includes(query)) ||
                   (r.customer && r.customer.toLowerCase().includes(query)) ||
                   (r.branch && r.branch.toLowerCase().includes(query)) ||
                   (r.product_name && r.product_name.toLowerCase().includes(query)) ||
                   (r.product_desc && r.product_desc.toLowerCase().includes(query)) ||
                   (r.ref_number && r.ref_number.toLowerCase().includes(query)) ||
                   (r.prepared_by && r.prepared_by.toLowerCase().includes(query)) ||
                   (r.status && r.status.toLowerCase().includes(query));
        });
    }

    if (soSearchInput) {
        soSearchInput.addEventListener('input', function () {
            closeAllActionMenus();
            renderSOTable(getCurrentlyFilteredData());
        });
    }

    // 6. REAL-TIME FINANCIAL CALCULATOR

    window.syncBalQty = function (qtyVal) {
        const balInput = document.getElementById('soBalQty');
        const idVal = document.getElementById('soRecordId').value;
        if (!idVal && balInput) {
            balInput.value = qtyVal;
        }
    };

    window.syncBillTo = function (custVal) {
        const billInput = document.getElementById('soBillTo');
        if (billInput && !billInput.value) {
            billInput.value = `${custVal} — Accounts Payable`;
        }
    };

    window.calculateSOFinancials = function () {
        const unitPrice = parseFloat(document.getElementById('soUnitPrice').value) || 0;
        const qty = parseInt(document.getElementById('soQty').value, 10) || 0;
        const discType = document.getElementById('soDiscountType').value;
        const discVal = parseFloat(document.getElementById('soDiscountValue').value) || 0;
        const vatCode = document.getElementById('soVatCode').value;
        const curr = document.getElementById('soCurrency').value || 'PHP';

        // 1. Gross Amount = Unit Price * Qty
        const gross = unitPrice * qty;

        // 2. Discount Amount
        let discAmt = 0;
        if (discType === 'percentage') {
            discAmt = gross * (discVal / 100);
        } else {
            discAmt = discVal;
        }
        if (discAmt > gross) discAmt = gross;

        const grossAfterDisc = gross - discAmt;

        // 3. VAT Amount
        let vatAmt = 0;
        if (vatCode === 'VAT Output (12%)') {
            vatAmt = grossAfterDisc * 0.12;
        }

        // 4. Net & Total
        const netAmt = grossAfterDisc + vatAmt;
        const totalAmt = netAmt;

        // Update Inputs
        document.getElementById('soGrossAmount').value = gross.toFixed(2);
        document.getElementById('soDiscAmount').value = discAmt.toFixed(2);
        document.getElementById('soVatAmount').value = vatAmt.toFixed(2);
        document.getElementById('soNetAmount').value = netAmt.toFixed(2);
        document.getElementById('soTotalAmount').value = totalAmt.toFixed(2);

        // Update Visual Highlights
        document.getElementById('stripGross').textContent = fmtCurrency(gross, curr);
        document.getElementById('stripDisc').textContent = `-${fmtCurrency(discAmt, curr)}`;
        document.getElementById('stripVat').textContent = `+${fmtCurrency(vatAmt, curr)}`;
        document.getElementById('stripTotal').textContent = fmtCurrency(totalAmt, curr);
    };

    // 7. CREATE & EDIT MODAL OPERATIONS

    window.openCreateSOModal = function () {
        closeAllActionMenus();
        document.getElementById('soForm').reset();
        document.getElementById('soRecordId').value = '';
        soModalTitle.textContent = 'Create Sales Order';
        btnSoSubmitText.textContent = 'Save Sales Order';

        // Auto-generate SO Number
        const nextId = salesOrdersData.length > 0 ? Math.max(...salesOrdersData.map(s => s.id)) + 1 : 1;
        document.getElementById('soNumber').value = `SO-2026-${String(nextId).padStart(3, '0')}`;

        // Set default dates
        const today = new Date().toISOString().split('T')[0];
        const nextWeek = new Date(Date.now() + 14 * 86400000).toISOString().split('T')[0];
        document.getElementById('soDate').value = today;
        document.getElementById('soExpDelDate').value = nextWeek;
        document.getElementById('soPreparedBy').value = 'Karl Bautista';
        document.getElementById('soBranch').value = 'Head Office (Mandaluyong)';
        document.getElementById('soStatus').value = 'Not Yet Posted';
        document.getElementById('soCurrency').value = 'PHP';
        document.getElementById('soRate').value = '1.0000';
        document.getElementById('soDiscountType').value = 'percentage';
        document.getElementById('soDiscountValue').value = '0.00';
        document.getElementById('soVatCode').value = 'VAT Output (12%)';
        document.getElementById('soRePrints').value = '0';
        document.getElementById('soBalQty').value = '1';
        document.getElementById('soQty').value = '1';

        calculateSOFinancials();

        soFormModalBackdrop.classList.add('show');
        document.getElementById('soCustomer').focus();
        if (window.lucide) window.lucide.createIcons();
    };

    window.openEditSOModal = function (id) {
        closeAllActionMenus();
        const rec = salesOrdersData.find(r => r.id === id);
        if (!rec) return;

        document.getElementById('soForm').reset();
        document.getElementById('soRecordId').value = rec.id;
        soModalTitle.textContent = `Edit Sales Order: ${rec.so_number}`;
        btnSoSubmitText.textContent = 'Update Sales Order';

        document.getElementById('soBranch').value = rec.branch;
        document.getElementById('soNumber').value = rec.so_number;
        document.getElementById('soDate').value = rec.so_date;
        document.getElementById('soExpDelDate').value = rec.exp_del_date;
        document.getElementById('soStatus').value = rec.status;
        document.getElementById('soPreparedBy').value = rec.prepared_by;
        document.getElementById('soRefNumber').value = rec.ref_number || '';
        document.getElementById('soRePrints').value = rec.re_prints || 0;

        document.getElementById('soCustomer').value = rec.customer;
        document.getElementById('soBillTo').value = rec.bill_to;
        document.getElementById('soPaymentTerms').value = rec.payment_terms;
        document.getElementById('soAddress').value = rec.address;
        document.getElementById('soShipment').value = rec.shipment;
        document.getElementById('soCountryOrigin').value = rec.country_origin;

        document.getElementById('soProductName').value = rec.product_name;
        document.getElementById('soProductDesc').value = rec.product_desc;
        document.getElementById('soUnitPrice').value = parseFloat(rec.unit_price).toFixed(2);
        document.getElementById('soQty').value = rec.qty;
        document.getElementById('soBalQty').value = rec.bal_qty;

        document.getElementById('soCurrency').value = rec.currency;
        document.getElementById('soRate').value = parseFloat(rec.rate || 1).toFixed(4);
        document.getElementById('soDiscountType').value = rec.discount_type || 'percentage';
        document.getElementById('soDiscountValue').value = parseFloat(rec.discount_value || 0).toFixed(2);
        document.getElementById('soVatCode').value = rec.vat_code || 'VAT Output (12%)';
        document.getElementById('soRemarks').value = rec.remarks || '';

        calculateSOFinancials();

        soFormModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    window.closeSOModal = function () {
        soFormModalBackdrop.classList.remove('show');
    };

    // Form Submit
    window.handleSOFormSubmit = function (event) {
        event.preventDefault();

        const idVal = document.getElementById('soRecordId').value;
        const branch = document.getElementById('soBranch').value;
        const soNum = document.getElementById('soNumber').value.trim();
        const soDate = document.getElementById('soDate').value;
        const expDate = document.getElementById('soExpDelDate').value;
        const status = document.getElementById('soStatus').value;
        const prepBy = document.getElementById('soPreparedBy').value.trim();
        const refNum = document.getElementById('soRefNumber').value.trim();
        const rePrints = parseInt(document.getElementById('soRePrints').value, 10) || 0;

        const customer = document.getElementById('soCustomer').value.trim();
        const billTo = document.getElementById('soBillTo').value.trim();
        const paymentTerms = document.getElementById('soPaymentTerms').value;
        const address = document.getElementById('soAddress').value.trim();
        const shipment = document.getElementById('soShipment').value;
        const countryOrigin = document.getElementById('soCountryOrigin').value;

        const prodName = document.getElementById('soProductName').value.trim();
        const prodDesc = document.getElementById('soProductDesc').value.trim();
        const unitPrice = parseFloat(document.getElementById('soUnitPrice').value) || 0;
        const qty = parseInt(document.getElementById('soQty').value, 10) || 1;
        const balQty = parseInt(document.getElementById('soBalQty').value, 10) || 0;

        const currency = document.getElementById('soCurrency').value;
        const rate = parseFloat(document.getElementById('soRate').value) || 1.0;
        const discType = document.getElementById('soDiscountType').value;
        const discVal = parseFloat(document.getElementById('soDiscountValue').value) || 0;
        const vatCode = document.getElementById('soVatCode').value;
        const remarks = document.getElementById('soRemarks').value.trim();

        const gross = parseFloat(document.getElementById('soGrossAmount').value) || 0;
        const discAmt = parseFloat(document.getElementById('soDiscAmount').value) || 0;
        const vatAmt = parseFloat(document.getElementById('soVatAmount').value) || 0;
        const netAmt = parseFloat(document.getElementById('soNetAmount').value) || 0;
        const totalAmt = parseFloat(document.getElementById('soTotalAmount').value) || 0;

        if (idVal) {
            // Update
            const index = salesOrdersData.findIndex(s => s.id === parseInt(idVal, 10));
            if (index !== -1) {
                salesOrdersData[index] = {
                    ...salesOrdersData[index],
                    branch, so_number: soNum, so_date: soDate, exp_del_date: expDate,
                    status, prepared_by: prepBy, ref_number: refNum, re_prints: rePrints,
                    customer, bill_to: billTo, payment_terms: paymentTerms, address, shipment,
                    country_origin: countryOrigin, product_name: prodName, product_desc: prodDesc,
                    unit_price: unitPrice, qty, bal_qty: balQty, currency, rate,
                    discount_type: discType, discount_value: discVal, disc_amount: discAmt,
                    gross_amount: gross, vat_code: vatCode, vat_amount: vatAmt,
                    net_amount: netAmt, total_amount: totalAmt, remarks
                };
            }
        } else {
            // Create
            const newId = salesOrdersData.length > 0 ? Math.max(...salesOrdersData.map(s => s.id)) + 1 : 1;
            const newOrder = {
                id: newId,
                branch, so_number: soNum, so_date: soDate, exp_del_date: expDate,
                status, prepared_by: prepBy, ref_number: refNum, re_prints: rePrints,
                customer, bill_to: billTo, payment_terms: paymentTerms, address, shipment,
                country_origin: countryOrigin, product_name: prodName, product_desc: prodDesc,
                unit_price: unitPrice, qty, bal_qty: balQty, currency, rate,
                discount_type: discType, discount_value: discVal, disc_amount: discAmt,
                gross_amount: gross, vat_code: vatCode, vat_amount: vatAmt,
                net_amount: netAmt, total_amount: totalAmt, remarks,
                deleted_date: '—'
            };
            salesOrdersData.unshift(newOrder);
        }

        closeSOModal();
        if (soSearchInput) soSearchInput.value = '';
        renderSOTable(salesOrdersData);
    };

    // 8. VIEW MODAL OPERATIONS

    window.openViewSOModal = function (id) {
        closeAllActionMenus();
        const rec = salesOrdersData.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;

        document.getElementById('viewSoTitle').textContent = `Sales Order: ${rec.so_number}`;
        document.getElementById('viewSoSub').textContent = `${rec.customer} • Prepared By: ${rec.prepared_by}`;

        document.getElementById('vSoNum').textContent = rec.so_number;
        document.getElementById('vSoBranch').textContent = rec.branch;
        document.getElementById('vSoDate').textContent = rec.so_date;
        document.getElementById('vSoExpDate').textContent = rec.exp_del_date;
        document.getElementById('vSoPreparedBy').textContent = rec.prepared_by;
        document.getElementById('vSoRefNum').textContent = rec.ref_number || 'None';

        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
        let statusBadgeClass = 'status-pill-pending';
        if (rec.status === 'Posted') statusBadgeClass = 'status-pill-posted';
        else if (rec.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';
        if (isDeleted) statusBadgeClass = 'status-pill-archived';

        document.getElementById('vSoStatusPill').innerHTML = `
            <span class="status-pill ${statusBadgeClass}">${isDeleted ? `Archived (${rec.deleted_date})` : rec.status}</span>
        `;
        document.getElementById('vSoRePrints').textContent = rec.re_prints || 0;

        document.getElementById('vSoCustomer').textContent = rec.customer;
        document.getElementById('vSoBillTo').textContent = rec.bill_to;
        document.getElementById('vSoAddress').textContent = rec.address;
        document.getElementById('vSoPaymentTerms').textContent = rec.payment_terms;
        document.getElementById('vSoShipment').textContent = rec.shipment;
        document.getElementById('vSoOrigin').textContent = rec.country_origin;
        document.getElementById('vSoDeletedDate').textContent = rec.deleted_date || '—';

        document.getElementById('vSoProdName').textContent = rec.product_name;
        document.getElementById('vSoProdDesc').textContent = rec.product_desc;
        document.getElementById('vSoUnitPrice').textContent = fmtCurrency(rec.unit_price, rec.currency);
        document.getElementById('vSoQty').textContent = `${rec.qty} Units`;
        document.getElementById('vSoBalQty').textContent = `${rec.bal_qty} Units`;

        document.getElementById('vSoCurrencyRate').textContent = `${rec.currency} @ rate ${parseFloat(rec.rate || 1).toFixed(4)}`;
        document.getElementById('vSoGross').textContent = fmtCurrency(rec.gross_amount, rec.currency);
        document.getElementById('vSoDiscount').textContent = `-${fmtCurrency(rec.disc_amount, rec.currency)} (${rec.discount_type === 'percentage' ? `${rec.discount_value}%` : 'Fixed'})`;
        document.getElementById('vSoVat').textContent = `${rec.vat_code} — ${fmtCurrency(rec.vat_amount, rec.currency)}`;
        document.getElementById('vSoNet').textContent = fmtCurrency(rec.net_amount, rec.currency);
        document.getElementById('vSoTotal').textContent = fmtCurrency(rec.total_amount, rec.currency);
        document.getElementById('vSoRemarks').textContent = rec.remarks || 'None';

        if (btnViewEditShortcut) {
            btnViewEditShortcut.onclick = function () {
                closeSOViewModal();
                openEditSOModal(id);
            };
        }

        soViewModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    window.closeSOViewModal = function () {
        soViewModalBackdrop.classList.remove('show');
    };

    // 9. DELETE / ARCHIVE MODAL OPERATIONS

    window.openDeleteSOModal = function (id) {
        closeAllActionMenus();
        const rec = salesOrdersData.find(r => r.id === id);
        if (!rec) return;

        deleteTargetId = id;
        document.getElementById('deleteTargetSoNum').textContent = rec.so_number;
        document.getElementById('deleteTargetCustomer').textContent = rec.customer;

        soDeleteModalBackdrop.classList.add('show');
    };

    window.closeSODeleteModal = function () {
        soDeleteModalBackdrop.classList.remove('show');
        deleteTargetId = null;
    };

    window.confirmDeleteSO = function () {
        if (!deleteTargetId) return;
        const rec = salesOrdersData.find(r => r.id === deleteTargetId);
        if (rec) {
            rec.deleted_date = new Date().toISOString().split('T')[0];
            rec.status = 'Cancelled';
        }
        closeSODeleteModal();
        renderSOTable(getCurrentlyFilteredData());
    };

    // 10. REFRESH & EXPORT / PRINT ACTIONS

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            setTimeout(() => {
                if (refreshIcon) refreshIcon.classList.remove('spin-anim');
                renderSOTable(getCurrentlyFilteredData());
            }, 450);
        });
    }

    if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            exportMenu.classList.toggle('show');
        });
    }

    window.exportSalesOrders = function (format) {
        if (exportMenu) exportMenu.classList.remove('show');
        const data = getCurrentlyFilteredData();

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csv = 'SO#,Branch,Customer,Prepared By,SO Date,Ref#,Product Name,Description,Unit Price,Qty,Bal Qty,Gross Amount,Disc Amount,Status,Deleted Date\n';
            data.forEach(r => {
                csv += `"${r.so_number}","${r.branch}","${r.customer}","${r.prepared_by}","${r.so_date}","${r.ref_number || ''}","${r.product_name}","${r.product_desc.replace(/"/g, '""')}",${r.unit_price},${r.qty},${r.bal_qty},${r.gross_amount},${r.disc_amount},"${r.status}","${r.deleted_date}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Orders_${new Date().toISOString().split('T')[0]}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        } else if (format === 'excel') {
            let html = '<table border="1"><tr><th>SO#</th><th>Branch</th><th>Customer</th><th>Prepared By</th><th>SO Date</th><th>Ref #</th><th>Product Name</th><th>Product Description</th><th>Unit Price</th><th>Qty</th><th>Bal Qty</th><th>Gross Amount</th><th>Disc Amount</th><th>Status</th><th>Deleted Date</th></tr>';
            data.forEach(r => {
                html += `<tr><td>${r.so_number}</td><td>${r.branch}</td><td>${r.customer}</td><td>${r.prepared_by}</td><td>${r.so_date}</td><td>${r.ref_number || ''}</td><td>${r.product_name}</td><td>${r.product_desc}</td><td>${r.unit_price}</td><td>${r.qty}</td><td>${r.bal_qty}</td><td>${r.gross_amount}</td><td>${r.disc_amount}</td><td>${r.status}</td><td>${r.deleted_date}</td></tr>`;
            });
            html += '</table>';
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Orders_${new Date().toISOString().split('T')[0]}.xls`;
            a.click();
            URL.revokeObjectURL(url);
        }
    };

    // Initial table render
    renderSOTable();

})();
</script>
@endpush
