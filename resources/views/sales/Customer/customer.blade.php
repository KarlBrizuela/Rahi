@extends('layouts.app')

@section('title', 'Customer Directory — Sales')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/Customer/customer.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Sales</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Customer</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="custSearchInput" placeholder="Search customer name, TIN, email, contact..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Customer Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload customers via AJAX">
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
                    <button type="button" class="export-option" onclick="exportCustomerDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable customer master list</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCustomerDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCustomerDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Customer Button (Opens Tabbed Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Customer</span>
            </button>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Client Master List</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Commercial Accounts & Order Billing Profiles</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="customerTable">
                    <thead>
                        <tr>
                            <th style="min-width: 170px;">Name</th>
                            <th style="min-width: 80px;">Shortname</th>
                            <th style="min-width: 130px;">Business Style</th>
                            <th style="min-width: 200px;">Address</th>
                            <th style="min-width: 120px;">TIN</th>
                            <th style="min-width: 130px;">Contact Person</th>
                            <th style="min-width: 115px;">Phone Number</th>
                            <th style="min-width: 105px;">Fax No</th>
                            <th style="min-width: 120px;">Country of Origin</th>
                            <th style="min-width: 160px;">Email Address</th>
                            <th style="min-width: 120px;">Payment Terms</th>
                            <th style="min-width: 105px;">Deleted Date</th>
                            <th style="width: 60px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="customerTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 customer entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn disabled" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn disabled" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<div class="modal-backdrop" id="custModalBackdrop">
    <div class="modal-dialog modal-dialog-lg" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Customer</h3>
                <p class="modal-subtitle">Configure commercial account details, addresses, order billing, and DR defaults.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <!-- MODAL TAB NAVIGATION (CUSTOMER INFO VS SALES/DR) -->
        <div class="modal-tab-bar">
            <button type="button" class="modal-tab-btn active" id="btnTabCustInfo" onclick="switchModalTab('cust-info')">
                <i data-lucide="user" class="tab-btn-icon"></i>
                <span>Customer Info</span>
            </button>
            <button type="button" class="modal-tab-btn" id="btnTabSalesDr" onclick="switchModalTab('sales-dr')">
                <i data-lucide="file-text" class="tab-btn-icon"></i>
                <span>Sales / DR</span>
            </button>
        </div>

        <form id="customerForm" onsubmit="handleCustomerFormSubmit(event)">
            <input type="hidden" id="custFormId" value="">

            <div class="modal-body tabbed-modal-body">

                <!-- --------------------------------------------------------------
                     TAB 1: CUSTOMER INFO
                     -------------------------------------------------------------- -->
                <div class="modal-tab-pane active" id="paneCustInfo">

                    <!-- Section 1: Basic Info -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">1</span>
                            <span class="section-label">Basic Information</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="custCompanyId">Company ID <span class="required">*</span></label>
                                <input type="text" id="custCompanyId" class="form-input font-code" placeholder="e.g. CUST-001" required maxlength="30">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custSalesInCharge">Sales In-Charge <span class="required">*</span></label>
                                <input type="text" id="custSalesInCharge" class="form-input" placeholder="e.g. Karl Bautista" required maxlength="80">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-2">
                                <label for="custName">Customer Name <span class="required">*</span></label>
                                <input type="text" id="custName" class="form-input" placeholder="e.g. San Miguel Corporation" required maxlength="120" oninput="syncNameToSalesDr(this.value)">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custShortName">Short Name <span class="required">*</span></label>
                                <input type="text" id="custShortName" class="form-input" placeholder="e.g. SMC" required maxlength="20" style="text-transform: uppercase;">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custBusinessStyle">Business Style <span class="required">*</span></label>
                                <input type="text" id="custBusinessStyle" class="form-input" placeholder="e.g. Manufacturer / Retail" required maxlength="80">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Address -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">2</span>
                            <span class="section-label">Address Management</span>
                        </div>
                        <div class="form-group">
                            <label for="custOfficeAddress">Office Address <span class="required">*</span></label>
                            <input type="text" id="custOfficeAddress" class="form-input" placeholder="e.g. 40 San Miguel Ave, Ortigas Center, Mandaluyong City" required maxlength="200" oninput="updateAddressOptions()">
                        </div>

                        <!-- Dynamic Delivery Addresses -->
                        <div class="delivery-addresses-container">
                            <div class="delivery-header-row">
                                <label class="delivery-label">Delivery Addresses <small>(Add multiple site/depot destinations)</small></label>
                                <button type="button" class="btn-outline btn-xs" onclick="addDeliveryAddressField('')">
                                    <i data-lucide="plus" class="btn-icon"></i>
                                    <span>Add Delivery Address</span>
                                </button>
                            </div>
                            <div class="delivery-address-list" id="deliveryAddressList">
                                <!-- Populated dynamically with delete buttons -->
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Other Info -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">3</span>
                            <span class="section-label">Other Information</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="custTin">TIN <span class="required">*</span></label>
                                <input type="text" id="custTin" class="form-input font-code" placeholder="e.g. 000-123-456-000" required maxlength="30">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custContactPerson">Contact Person <span class="required">*</span></label>
                                <input type="text" id="custContactPerson" class="form-input" placeholder="e.g. Roberto Tan" required maxlength="80">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custEmail">Email Address <span class="required">*</span></label>
                                <input type="email" id="custEmail" class="form-input" placeholder="e.g. purchasing@sanmiguel.ph" required maxlength="100">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="custCountryOrigin">Country of Origin <span class="required">*</span></label>
                                <input type="text" id="custCountryOrigin" class="form-input" placeholder="e.g. Philippines" required maxlength="60">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custCountry">Country <span class="required">*</span></label>
                                <input type="text" id="custCountry" class="form-input" placeholder="e.g. Philippines" required maxlength="60">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custFax">Fax No.</label>
                                <input type="text" id="custFax" class="form-input" placeholder="e.g. (02) 8632-3000" maxlength="30">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custPhone">Phone Number <span class="required">*</span></label>
                                <input type="text" id="custPhone" class="form-input" placeholder="e.g. (02) 8632-3100" required maxlength="30">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="custRemarks">Remarks</label>
                            <input type="text" id="custRemarks" class="form-input" placeholder="Optional internal notes, preferred courier, credit terms, etc." maxlength="200">
                        </div>
                    </div>

                </div>

                <!-- --------------------------------------------------------------
                     TAB 2: SALES / DR
                     -------------------------------------------------------------- -->
                <div class="modal-tab-pane" id="paneSalesDr">

                    <!-- Section 1: Sales -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">A</span>
                            <span class="section-label">Sales Order Defaults</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-2">
                                <label for="salesDrName">Sales Name <span class="required">*</span></label>
                                <input type="text" id="salesDrName" class="form-input" placeholder="Customer Trade / Billing Name" required maxlength="120">
                            </div>
                            <div class="form-group flex-1">
                                <label for="salesDecimalPrice">Decimal in Price <span class="required">*</span></label>
                                <input type="number" id="salesDecimalPrice" class="form-input font-numeric" placeholder="2" min="0" max="6" value="2" required>
                            </div>
                            <div class="form-group flex-1">
                                <label for="salesDecimalTotal">Decimal in Total Price <span class="required">*</span></label>
                                <input type="number" id="salesDecimalTotal" class="form-input font-numeric" placeholder="2" min="0" max="6" value="2" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="salesPaymentTerms">Payment Terms <span class="required">*</span></label>
                                <select id="salesPaymentTerms" class="form-select" required>
                                    <option value="30 Days">30 Days from Invoice Date</option>
                                    <option value="Upon Receipt of Goods (COD)">Upon Receipt of Goods (COD)</option>
                                    <option value="15th of Following Month">15th of Following Month</option>
                                    <option value="60 Days">60 Days Net</option>
                                    <option value="90 Days">90 Days Post-Audit</option>
                                </select>
                            </div>
                            <div class="form-group flex-2">
                                <label for="salesAddressSelect">Address (Office or Delivery Destination) <span class="required">*</span></label>
                                <select id="salesAddressSelect" class="form-select" required>
                                    <option value="office">Office Address</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="salesDiscountVal">Discount (Rate / Amount)</label>
                                <div class="discount-combo-wrap">
                                    <select id="salesDiscountType" class="discount-type-select">
                                        <option value="percent">%</option>
                                        <option value="amount">₱</option>
                                    </select>
                                    <input type="number" id="salesDiscountVal" class="form-input discount-input font-numeric" placeholder="0.00" step="0.01" min="0" value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Delivery Receipt (DR) -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">B</span>
                            <span class="section-label">Delivery Receipt (DR) Defaults</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="drName">DR Recipient / Consignee Name <span class="required">*</span></label>
                                <input type="text" id="drName" class="form-input" placeholder="e.g. SMC Warehouse Receiving Dept." required maxlength="120">
                            </div>
                            <div class="form-group flex-2">
                                <label for="drAddressSelect">DR Delivery Address <span class="required">*</span></label>
                                <select id="drAddressSelect" class="form-select" required>
                                    <option value="office">Office Address</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Tax -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">C</span>
                            <span class="section-label">Tax Parameters</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="taxVatCode">VAT Code <span class="required">*</span></label>
                                <input type="text" id="taxVatCode" class="form-input font-code" placeholder="e.g. V001 or VAT Output" required maxlength="40">
                            </div>
                            <div class="form-group flex-1">
                                <label for="taxInclusiveExclusive">Inclusive or Exclusive <span class="required">*</span></label>
                                <select id="taxInclusiveExclusive" class="form-select" required>
                                    <option value="VAT Inclusive">VAT Inclusive</option>
                                    <option value="VAT Exclusive">VAT Exclusive</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="taxTaxCode">Tax Code (ATC) <span class="required">*</span></label>
                                <select id="taxTaxCode" class="form-select" required>
                                    <option value="WI100">WI100 — Professional Fees (10%)</option>
                                    <option value="WI158">WI158 — Rental of Properties (5%)</option>
                                    <option value="WC160">WC160 — Income Payments to Corp (15%)</option>
                                    <option value="WV010">WV010 — VAT on Goods (12%)</option>
                                    <option value="NONE">NONE — Not Subject to Withholding</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnModalCancel">Cancel</button>
                <button type="submit" class="btn-save" id="btnSubmitForm">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSubmitLabel">Save Customer</span>
                </button>
            </div>
        </form>

    </div>
</div>

<div class="modal-backdrop" id="viewModalBackdrop">
    <div class="modal-dialog modal-dialog-lg" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="viewModalTitle">Customer Profile & Settings</h3>
                <p class="modal-subtitle">Comprehensive client identity, delivery sites, order pricing, and tax defaults.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnViewModalCloseCross" title="Close view modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body view-modal-body">
            <!-- Hero -->
            <div class="view-cust-hero">
                <div class="view-avatar-circle" id="viewAvatarText">SM</div>
                <div class="view-hero-meta">
                    <div class="hero-name-row">
                        <h4 class="view-fullname" id="viewName">San Miguel Corporation</h4>
                        <span class="status-pill status-pill-active" id="viewStatusPill">Active</span>
                    </div>
                    <span class="view-code-sub" id="viewCompanySub">Company ID: CUST-001 &bull; Short Name: SMC</span>
                </div>
            </div>

            <!-- Details 2-Column Grid -->
            <div class="view-sections-grid">
                <!-- Col 1: Customer Information & Contact -->
                <div class="view-card-box">
                    <span class="view-box-title">Customer Information & Identity</span>
                    <div class="view-kv-list">
                        <div class="kv-item"><span class="kv-label">Company ID:</span><span class="kv-val font-code" id="viewCompanyId">CUST-001</span></div>
                        <div class="kv-item"><span class="kv-label">Customer Name:</span><span class="kv-val font-medium" id="viewCustomerName">San Miguel Corporation</span></div>
                        <div class="kv-item"><span class="kv-label">Short Name:</span><span class="kv-val shortname-badge" id="viewShortName">SMC</span></div>
                        <div class="kv-item"><span class="kv-label">Business Style:</span><span class="kv-val" id="viewBusinessStyle">Manufacturer</span></div>
                        <div class="kv-item"><span class="kv-label">Sales In-Charge:</span><span class="kv-val font-medium" id="viewSalesInCharge">Karl Bautista</span></div>
                        <div class="kv-item"><span class="kv-label">Office Address:</span><span class="kv-val" id="viewOfficeAddress">Ortigas Center, Mandaluyong</span></div>
                        <div class="kv-item kv-item-vertical">
                            <span class="kv-label">Delivery Addresses:</span>
                            <div class="view-dest-list" id="viewDeliveryAddresses">None specified</div>
                        </div>
                        <div class="kv-item"><span class="kv-label">TIN:</span><span class="kv-val font-code" id="viewTin">000-123-456-000</span></div>
                        <div class="kv-item"><span class="kv-label">Contact Person:</span><span class="kv-val" id="viewContactPerson">Roberto Tan</span></div>
                        <div class="kv-item"><span class="kv-label">Email Address:</span><span class="kv-val" id="viewEmail">purchasing@sanmiguel.ph</span></div>
                        <div class="kv-item"><span class="kv-label">Phone Number:</span><span class="kv-val" id="viewPhone">(02) 8632-3100</span></div>
                        <div class="kv-item"><span class="kv-label">Fax No.:</span><span class="kv-val" id="viewFax">(02) 8632-3000</span></div>
                        <div class="kv-item"><span class="kv-label">Country of Origin:</span><span class="kv-val" id="viewCountryOrigin">Philippines</span></div>
                        <div class="kv-item"><span class="kv-label">Country:</span><span class="kv-val" id="viewCountry">Philippines</span></div>
                        <div class="kv-item"><span class="kv-label">Remarks:</span><span class="kv-val" id="viewRemarks">None</span></div>
                    </div>
                </div>

                <!-- Col 2: Sales / DR Defaults & Tax -->
                <div class="view-card-box">
                    <span class="view-box-title">Sales, Delivery & Tax Defaults</span>
                    <div class="view-kv-list">
                        <div class="kv-item"><span class="kv-label">Sales Name:</span><span class="kv-val font-medium" id="viewSalesName">San Miguel Corporation</span></div>
                        <div class="kv-item"><span class="kv-label">Decimal in Price:</span><span class="kv-val font-numeric" id="viewDecimalPrice">2 decimals</span></div>
                        <div class="kv-item"><span class="kv-label">Decimal in Total Price:</span><span class="kv-val font-numeric" id="viewDecimalTotal">2 decimals</span></div>
                        <div class="kv-item"><span class="kv-label">Payment Terms:</span><span class="kv-val term-pill" id="viewPaymentTerms">30 Days</span></div>
                        <div class="kv-item"><span class="kv-label">Default Sales Address:</span><span class="kv-val" id="viewSalesAddress">Office Address</span></div>
                        <div class="kv-item"><span class="kv-label">Discount:</span><span class="kv-val font-medium" id="viewDiscount">0.00%</span></div>
                        <div class="kv-item"><span class="kv-label">DR Recipient / Consignee:</span><span class="kv-val font-medium" id="viewDrName">SMC Receiving</span></div>
                        <div class="kv-item"><span class="kv-label">DR Delivery Address:</span><span class="kv-val" id="viewDrAddress">Office Address</span></div>
                        <div class="kv-item"><span class="kv-label">VAT Code:</span><span class="kv-val font-code" id="viewVatCode">VAT Output</span></div>
                        <div class="kv-item"><span class="kv-label">Inclusive / Exclusive:</span><span class="kv-val" id="viewVatInclusiveExclusive">VAT Inclusive</span></div>
                        <div class="kv-item"><span class="kv-label">Tax Code (ATC):</span><span class="kv-val font-code" id="viewTaxCode">WI100</span></div>
                        <div class="kv-item"><span class="kv-label">Account Status:</span><span class="kv-val" id="viewAccountStatus">Active</span></div>
                        <div class="kv-item"><span class="kv-label">Deleted Date:</span><span class="kv-val" id="viewDeletedDate">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnViewModalClose">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit-2" class="btn-icon"></i>
                <span>Edit Customer</span>
            </button>
        </div>

    </div>
</div>

<div class="modal-backdrop" id="deleteConfirmBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-group">
                <div class="danger-icon-circle">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div>
                    <h3 class="modal-title" id="deleteModalTitle">Delete Customer</h3>
                    <p class="modal-subtitle">Soft-delete and archive this client account.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete customer account <strong id="deleteTargetName">Customer Name</strong> (<span id="deleteTargetShort">Short</span>)?
            </p>
            <p class="delete-sub-text">
                This account will be marked as archived on today's date (<span id="deleteTargetDate">2026-11-05</span>).
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnCancelDelete">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDelete">
                <i data-lucide="trash-2" class="btn-icon"></i>
                <span>Confirm Delete</span>
            </button>
        </div>

    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // 1. DATA REPOSITORY & INITIAL STATE

    let customersData = [
        {
            id: 1,
            company_id: 'CUST-001',
            sales_in_charge: 'Karl Bautista',
            name: 'San Miguel Corporation',
            short_name: 'SMC',
            business_style: 'Food & Beverage Manufacturer',
            office_address: '40 San Miguel Avenue, Ortigas Center, Mandaluyong City',
            delivery_addresses: [
                'Polo Brewery, McArthur Highway, Valenzuela City',
                'SMC Distribution Depot, Canlubang, Laguna'
            ],
            tin: '000-123-456-000',
            contact_person: 'Roberto Tan',
            email: 'purchasing@sanmiguel.ph',
            country_origin: 'Philippines',
            country: 'Philippines',
            fax: '(02) 8632-3000',
            phone: '(02) 8632-3100',
            category: 'Corporate / Key Account',
            remarks: 'Priority delivery schedule on Mondays and Thursdays.',
            // Sales / DR
            sales_name: 'San Miguel Corporation',
            decimal_price: 2,
            decimal_total: 2,
            payment_terms: '30 Days',
            sales_address: 'office',
            discount_type: 'percent',
            discount_val: 2.50,
            dr_name: 'SMC Receiving Central Depot',
            dr_address: '0',
            tax_vat_code: 'VAT Output',
            tax_inclusive_exclusive: 'VAT Inclusive',
            tax_code: 'WI100',
            deleted_date: '—'
        },
        {
            id: 2,
            company_id: 'CUST-002',
            sales_in_charge: 'Maria Santos',
            name: 'Universal Robina Corporation',
            short_name: 'URC',
            business_style: 'Consumer Goods & Agro-Industrial',
            office_address: 'Tera Tower, Bridgetowne, E. Rodriguez Jr. Ave, Quezon City',
            delivery_addresses: [
                'URC Flour Plant, Pasig Boulevard, Pasig City'
            ],
            tin: '000-456-789-000',
            contact_person: 'Elena Mendoza',
            email: 'procurement@urc.com.ph',
            country_origin: 'Philippines',
            country: 'Philippines',
            fax: '(02) 8555-1234',
            phone: '(02) 8555-5678',
            category: 'Corporate / Key Account',
            remarks: 'Requires electronic invoicing approval before delivery.',
            sales_name: 'Universal Robina Corporation',
            decimal_price: 2,
            decimal_total: 2,
            payment_terms: '60 Days',
            sales_address: 'office',
            discount_type: 'percent',
            discount_val: 0.00,
            dr_name: 'URC Warehousing & Logistics',
            dr_address: 'office',
            tax_vat_code: 'VAT Output',
            tax_inclusive_exclusive: 'VAT Inclusive',
            tax_code: 'WC160',
            deleted_date: '—'
        },
        {
            id: 3,
            company_id: 'CUST-003',
            sales_in_charge: 'Juan Dela Cruz',
            name: 'Robinsons Supermarket Corporation',
            short_name: 'RSC',
            business_style: 'Retail Grocery Supermarket Chain',
            office_address: 'Galleria Corporate Center, EDSA corner Ortigas, Quezon City',
            delivery_addresses: [
                'DC Tarlac Logistics Hub, Luisita Industrial Park, Tarlac',
                'DC Cebu, Mandaue City, Cebu'
            ],
            tin: '001-234-567-000',
            contact_person: 'Arthur Lim',
            email: 'vendor.relations@robinsons.com.ph',
            country_origin: 'Philippines',
            country: 'Philippines',
            fax: '(02) 8631-1256',
            phone: '(02) 8631-1250',
            category: 'Distributor',
            remarks: 'Direct drop shipments to distribution centers.',
            sales_name: 'Robinsons Supermarket Corporation',
            decimal_price: 2,
            decimal_total: 2,
            payment_terms: '15th of Following Month',
            sales_address: '0',
            discount_type: 'amount',
            discount_val: 500.00,
            dr_name: 'Arthur Lim (Logistics Head)',
            dr_address: '1',
            tax_vat_code: 'VAT Output',
            tax_inclusive_exclusive: 'VAT Exclusive',
            tax_code: 'WI158',
            deleted_date: '—'
        },
        {
            id: 4,
            company_id: 'CUST-004',
            sales_in_charge: 'Carlos Reyes',
            name: 'Apex Precision Engineering Supplies (Archived)',
            short_name: 'APEX',
            business_style: 'Hardware & Machine Parts Retailer',
            office_address: '88 Industrial Road, Karuhatan, Valenzuela City',
            delivery_addresses: [],
            tin: '002-345-678-000',
            contact_person: 'Dennis Sy',
            email: 'admin@apexprecision.com',
            country_origin: 'Philippines',
            country: 'Philippines',
            fax: '—',
            phone: '(02) 8292-1144',
            category: 'Direct Client',
            remarks: 'Account dormant after liquidation.',
            sales_name: 'Apex Precision Engineering Supplies',
            decimal_price: 2,
            decimal_total: 2,
            payment_terms: 'Upon Receipt of Goods (COD)',
            sales_address: 'office',
            discount_type: 'percent',
            discount_val: 0.00,
            dr_name: 'Dennis Sy',
            dr_address: 'office',
            tax_vat_code: 'VAT Output',
            tax_inclusive_exclusive: 'VAT Inclusive',
            tax_code: 'NONE',
            deleted_date: '2026-08-14'
        }
    ];

    let currentOpenActionMenuId = null;
    let activeDeleteId = null;
    let activeViewId = null;

    // DOM Elements
    const customerTableBody = document.getElementById('customerTableBody');
    const custSearchInput = document.getElementById('custSearchInput');
    const recordCountBadge = document.getElementById('recordCount');
    const footerMetaText = document.getElementById('footerMetaText');

    // Create / Edit Modal Elements
    const custModalBackdrop = document.getElementById('custModalBackdrop');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');
    const btnModalCancel = document.getElementById('btnModalCancel');
    const customerForm = document.getElementById('customerForm');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');
    const deliveryAddressList = document.getElementById('deliveryAddressList');

    // View Modal Elements
    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const btnViewModalCloseCross = document.getElementById('btnViewModalCloseCross');
    const btnViewModalClose = document.getElementById('btnViewModalClose');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');

    // Delete Modal Elements
    const deleteConfirmBackdrop = document.getElementById('deleteConfirmBackdrop');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const deleteTargetName = document.getElementById('deleteTargetName');
    const deleteTargetShort = document.getElementById('deleteTargetShort');
    const deleteTargetDate = document.getElementById('deleteTargetDate');

    // Refresh & Export
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // 2. MODAL TAB SWITCHER (CUSTOMER INFO VS SALES/DR)

    window.switchModalTab = function (tabKey) {
        const btnCust = document.getElementById('btnTabCustInfo');
        const btnSales = document.getElementById('btnTabSalesDr');
        const paneCust = document.getElementById('paneCustInfo');
        const paneSales = document.getElementById('paneSalesDr');

        if (tabKey === 'cust-info') {
            btnCust.classList.add('active');
            btnSales.classList.remove('active');
            paneCust.classList.add('active');
            paneSales.classList.remove('active');
        } else {
            btnSales.classList.add('active');
            btnCust.classList.remove('active');
            paneSales.classList.add('active');
            paneCust.classList.remove('active');
            updateAddressOptions();
        }
        if (window.lucide) window.lucide.createIcons();
    };

    window.syncNameToSalesDr = function (val) {
        const sName = document.getElementById('salesDrName');
        if (sName && !sName.getAttribute('data-customized')) {
            sName.value = val;
        }
    };

    // 3. DYNAMIC DELIVERY ADDRESSES MANAGEMENT

    window.addDeliveryAddressField = function (addressValue = '') {
        const row = document.createElement('div');
        row.className = 'delivery-addr-item';
        row.innerHTML = `
            <div class="delivery-item-input-wrap">
                <i data-lucide="map-pin" class="delivery-pin-icon"></i>
                <input type="text" class="form-input delivery-addr-input" placeholder="e.g. Warehouse 3, Plant Site, Laguna" value="${escapeHtml(addressValue)}" oninput="updateAddressOptions()">
            </div>
            <button type="button" class="btn-del-addr" onclick="removeDeliveryAddressRow(this)" title="Remove address">
                <i data-lucide="trash-2"></i>
            </button>
        `;
        deliveryAddressList.appendChild(row);
        if (window.lucide) window.lucide.createIcons();
        updateAddressOptions();
    };

    window.removeDeliveryAddressRow = function (btn) {
        const item = btn.closest('.delivery-addr-item');
        if (item) {
            item.remove();
            updateAddressOptions();
        }
    };

    window.updateAddressOptions = function () {
        const salesSelect = document.getElementById('salesAddressSelect');
        const drSelect = document.getElementById('drAddressSelect');
        const officeAddr = document.getElementById('custOfficeAddress').value.trim();

        const currentSalesVal = salesSelect.value;
        const currentDrVal = drSelect.value;

        let optionsHtml = `<option value="office">Office Address ${officeAddr ? `(${officeAddr.substring(0, 30)}...)` : ''}</option>`;

        const deliveryInputs = document.querySelectorAll('.delivery-addr-input');
        deliveryInputs.forEach((inp, idx) => {
            const val = inp.value.trim();
            optionsHtml += `<option value="${idx}">Delivery Address ${idx + 1}: ${val ? val.substring(0, 35) : '(Empty)'}</option>`;
        });

        salesSelect.innerHTML = optionsHtml;
        drSelect.innerHTML = optionsHtml;

        if (salesSelect.querySelector(`option[value="${currentSalesVal}"]`)) {
            salesSelect.value = currentSalesVal;
        }
        if (drSelect.querySelector(`option[value="${currentDrVal}"]`)) {
            drSelect.value = currentDrVal;
        }
    };

    // 4. RENDER TABLE WITH 3-DOTS ACTION MENU (VIEW, EDIT, DELETE)

    function renderCustomerTable(items) {
        if (!customerTableBody) return;
        customerTableBody.innerHTML = '';

        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="13" class="table-empty-state">
                    <div class="empty-box">
                        <i data-lucide="users" class="empty-icon"></i>
                        <span class="empty-text">No customer accounts found</span>
                        <small class="empty-subtext">Try refining your search query or create a new customer record.</small>
                    </div>
                </td>
            `;
            customerTableBody.appendChild(tr);
            if (window.lucide) window.lucide.createIcons();
            updateMetaCounts(0, customersData.length);
            return;
        }

        items.forEach(rec => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', rec.id);

            const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
            if (isDeleted) tr.classList.add('row-deleted');

            tr.innerHTML = `
                <td>
                    <strong class="${isDeleted ? 'text-muted' : 'text-dark'}">${escapeHtml(rec.name)}</strong>
                </td>
                <td>
                    <span class="shortname-badge ${isDeleted ? 'badge-faint' : ''}">${escapeHtml(rec.short_name)}</span>
                </td>
                <td class="${isDeleted ? 'text-muted' : ''}">${escapeHtml(rec.business_style)}</td>
                <td class="cell-address ${isDeleted ? 'text-muted' : ''}" title="${escapeHtml(rec.office_address)}">${escapeHtml(rec.office_address)}</td>
                <td>
                    <span class="font-code text-muted">${escapeHtml(rec.tin)}</span>
                </td>
                <td>${escapeHtml(rec.contact_person)}</td>
                <td>${escapeHtml(rec.phone)}</td>
                <td>${rec.fax && rec.fax !== '—' ? escapeHtml(rec.fax) : '<span class="text-muted">—</span>'}</td>
                <td>${escapeHtml(rec.country_origin)}</td>
                <td>
                    <a href="mailto:${escapeHtml(rec.email)}" class="email-link">${escapeHtml(rec.email)}</a>
                </td>
                <td>
                    <span class="term-pill">${escapeHtml(rec.payment_terms)}</span>
                </td>
                <td>
                    ${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}
                </td>
                <td class="text-center no-print">
                    <div class="action-dropdown-wrapper">
                        <button type="button" 
                                class="action-dots-btn" 
                                onclick="toggleCustomerActionMenu(event, ${rec.id})"
                                title="Actions for ${escapeHtml(rec.short_name)}">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="action-dropdown-menu" id="actionMenu-${rec.id}">
                            <button type="button" class="action-menu-item" onclick="openViewCustomerModal(${rec.id})">
                                <i data-lucide="eye"></i>
                                <span>View</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="openEditCustomerModal(${rec.id})">
                                <i data-lucide="edit-2"></i>
                                <span>Edit</span>
                            </button>
                            <div class="menu-divider"></div>
                            <button type="button" class="action-menu-item item-danger" onclick="openDeleteCustomerModal(${rec.id})">
                                <i data-lucide="trash-2"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </td>
            `;

            customerTableBody.appendChild(tr);
        });

        if (window.lucide) window.lucide.createIcons();
        updateMetaCounts(items.length, customersData.length);
    }

    function updateMetaCounts(filteredCount, totalCount) {
        if (recordCountBadge) recordCountBadge.textContent = `${filteredCount} Records`;
        if (footerMetaText) footerMetaText.textContent = `Showing 1 to ${filteredCount} of ${totalCount} customer entries`;
    }

    // 5. ACTION MENU TOGGLE & SMART DROP-UP

    window.toggleCustomerActionMenu = function (event, recId) {
        event.stopPropagation();
        const btn = event.currentTarget;
        const targetMenu = document.getElementById(`actionMenu-${recId}`);

        if (currentOpenActionMenuId === recId) {
            closeAllActionMenus();
            return;
        }

        closeAllActionMenus();

        if (targetMenu) {
            const tr = btn.closest('tr');
            const tbody = tr ? tr.parentElement : null;
            const rows = tbody ? Array.from(tbody.querySelectorAll('tr[data-id]')) : [];
            const rowIndex = rows.indexOf(tr);
            const totalRows = rows.length;

            // Never drop up on row 0 or when total filtered rows <= 2 (prevents clipping with <thead>)
            if (rowIndex <= 0 || totalRows <= 2) {
                targetMenu.classList.remove('drop-up');
            } else if (rowIndex >= totalRows - 2) {
                targetMenu.classList.add('drop-up');
            } else {
                const btnRect = btn.getBoundingClientRect();
                const spaceBelow = window.innerHeight - btnRect.bottom;
                if (spaceBelow < 150) {
                    targetMenu.classList.add('drop-up');
                } else {
                    targetMenu.classList.remove('drop-up');
                }
            }

            targetMenu.classList.add('show');
            currentOpenActionMenuId = recId;
        }
    };

    function closeAllActionMenus() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(m => m.classList.remove('show'));
        currentOpenActionMenuId = null;
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action-dropdown-wrapper')) closeAllActionMenus();
        if (exportDropdownWrapper && !exportDropdownWrapper.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionMenus();
            closeCustModal();
            closeViewModal();
            closeDeleteModal();
            if (exportMenu) exportMenu.classList.remove('show');
        }
    });

    // 6. SEARCH FILTERING

    if (custSearchInput) {
        custSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            closeAllActionMenus();

            if (!query) {
                renderCustomerTable(customersData);
                return;
            }

            const filtered = customersData.filter(r => {
                return r.name.toLowerCase().includes(query) ||
                       r.short_name.toLowerCase().includes(query) ||
                       r.tin.toLowerCase().includes(query) ||
                       r.contact_person.toLowerCase().includes(query) ||
                       r.email.toLowerCase().includes(query) ||
                       r.business_style.toLowerCase().includes(query) ||
                       r.office_address.toLowerCase().includes(query);
            });

            renderCustomerTable(filtered);
        });
    }

    // 7. VIEW CUSTOMER MODAL

    window.openViewCustomerModal = function (id) {
        closeAllActionMenus();
        const rec = customersData.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;

        // Initials & Hero Meta
        const initials = rec.short_name ? rec.short_name.substring(0, 2).toUpperCase() : 'CU';
        document.getElementById('viewAvatarText').textContent = initials;
        document.getElementById('viewName').textContent = rec.name;
        document.getElementById('viewCompanySub').textContent = `Company ID: ${rec.company_id} • Short Name: ${rec.short_name}`;

        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
        const pill = document.getElementById('viewStatusPill');
        pill.className = `status-pill ${isDeleted ? 'status-pill-archived' : 'status-pill-active'}`;
        pill.textContent = isDeleted ? 'Archived' : 'Active Account';

        // Column 1: Customer Information & Identity
        document.getElementById('viewCompanyId').textContent = rec.company_id || '—';
        document.getElementById('viewCustomerName').textContent = rec.name || '—';
        document.getElementById('viewShortName').textContent = rec.short_name || '—';
        document.getElementById('viewBusinessStyle').textContent = rec.business_style || '—';
        document.getElementById('viewSalesInCharge').textContent = rec.sales_in_charge || '—';
        document.getElementById('viewOfficeAddress').textContent = rec.office_address || '—';

        // Delivery Addresses (itemized list)
        const delivContainer = document.getElementById('viewDeliveryAddresses');
        if (rec.delivery_addresses && rec.delivery_addresses.length > 0) {
            delivContainer.innerHTML = rec.delivery_addresses
                .map((addr, idx) => `
                    <div class="view-dest-item">
                        <span class="view-dest-num">#${idx + 1}</span>
                        <span class="view-dest-val">${escapeHtml(addr)}</span>
                    </div>
                `)
                .join('');
        } else {
            delivContainer.innerHTML = '<span class="text-muted">None specified (Default ships to Office)</span>';
        }

        document.getElementById('viewTin').textContent = rec.tin || '—';
        document.getElementById('viewContactPerson').textContent = rec.contact_person || '—';
        document.getElementById('viewEmail').textContent = rec.email || '—';
        document.getElementById('viewPhone').textContent = rec.phone || '—';
        document.getElementById('viewFax').textContent = (rec.fax && rec.fax !== '—') ? rec.fax : '—';
        document.getElementById('viewCountryOrigin').textContent = rec.country_origin || '—';
        document.getElementById('viewCountry').textContent = rec.country || '—';
        document.getElementById('viewRemarks').textContent = rec.remarks || 'None';

        // Column 2: Sales, Delivery & Tax Defaults
        document.getElementById('viewSalesName').textContent = rec.sales_name || rec.name;
        document.getElementById('viewDecimalPrice').textContent = `${rec.decimal_price} decimals`;
        document.getElementById('viewDecimalTotal').textContent = `${rec.decimal_total} decimals`;
        document.getElementById('viewPaymentTerms').textContent = rec.payment_terms || '—';

        const salesAddrDisplay = rec.sales_address === 'office'
            ? `Office Address: ${rec.office_address}`
            : (rec.delivery_addresses && rec.delivery_addresses[parseInt(rec.sales_address, 10)]
                ? `Delivery Address #${parseInt(rec.sales_address, 10) + 1}: ${rec.delivery_addresses[parseInt(rec.sales_address, 10)]}`
                : 'Office Address');
        document.getElementById('viewSalesAddress').textContent = salesAddrDisplay;

        document.getElementById('viewDiscount').textContent = rec.discount_type === 'percent' 
            ? `${parseFloat(rec.discount_val || 0).toFixed(2)}%` 
            : `₱ ${parseFloat(rec.discount_val || 0).toFixed(2)}`;

        document.getElementById('viewDrName').textContent = rec.dr_name || rec.name;

        const drAddrDisplay = rec.dr_address === 'office'
            ? `Office Address: ${rec.office_address}`
            : (rec.delivery_addresses && rec.delivery_addresses[parseInt(rec.dr_address, 10)]
                ? `Delivery Address #${parseInt(rec.dr_address, 10) + 1}: ${rec.delivery_addresses[parseInt(rec.dr_address, 10)]}`
                : 'Office Address');
        document.getElementById('viewDrAddress').textContent = drAddrDisplay;

        document.getElementById('viewVatCode').textContent = rec.tax_vat_code || '—';
        document.getElementById('viewVatInclusiveExclusive').textContent = rec.tax_inclusive_exclusive || '—';
        document.getElementById('viewTaxCode').textContent = rec.tax_code || '—';
        document.getElementById('viewAccountStatus').textContent = isDeleted ? 'Archived' : 'Active Account';
        document.getElementById('viewDeletedDate').textContent = isDeleted ? rec.deleted_date : '—';

        viewModalBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    function closeViewModal() {
        if (viewModalBackdrop) viewModalBackdrop.classList.remove('show');
        activeViewId = null;
    }

    if (btnViewModalCloseCross) btnViewModalCloseCross.addEventListener('click', closeViewModal);
    if (btnViewModalClose) btnViewModalClose.addEventListener('click', closeViewModal);
    if (btnViewEditShortcut) {
        btnViewEditShortcut.addEventListener('click', function () {
            if (activeViewId) {
                const targetId = activeViewId;
                closeViewModal();
                openEditCustomerModal(targetId);
            }
        });
    }

    // 8. CREATE / EDIT CUSTOMER MODAL

    function openCreateCustomerModal() {
        closeAllActionMenus();
        customerForm.reset();
        document.getElementById('custFormId').value = '';
        deliveryAddressList.innerHTML = '';
        modalTitle.textContent = 'Create New Customer';
        btnSubmitLabel.textContent = 'Save Customer';

        // Auto-assign next Company ID
        const nextId = customersData.length > 0 ? Math.max(...customersData.map(c => c.id)) + 1 : 1;
        document.getElementById('custCompanyId').value = `CUST-${String(nextId).padStart(3, '0')}`;
        document.getElementById('salesDecimalPrice').value = '2';
        document.getElementById('salesDecimalTotal').value = '2';
        document.getElementById('salesDiscountVal').value = '0.00';

        switchModalTab('cust-info');
        custModalBackdrop.classList.add('show');
        document.getElementById('custSalesInCharge').focus();
        if (window.lucide) window.lucide.createIcons();
    }

    window.openEditCustomerModal = function (id) {
        closeAllActionMenus();
        const rec = customersData.find(r => r.id === id);
        if (!rec) return;

        customerForm.reset();
        deliveryAddressList.innerHTML = '';
        document.getElementById('custFormId').value = rec.id;
        modalTitle.textContent = `Edit Customer: ${rec.short_name} — ${rec.name}`;
        btnSubmitLabel.textContent = 'Update Customer';

        // Tab 1: Customer Info
        document.getElementById('custCompanyId').value = rec.company_id;
        document.getElementById('custSalesInCharge').value = rec.sales_in_charge;
        document.getElementById('custName').value = rec.name;
        document.getElementById('custShortName').value = rec.short_name;
        document.getElementById('custBusinessStyle').value = rec.business_style;
        document.getElementById('custOfficeAddress').value = rec.office_address;

        if (rec.delivery_addresses && rec.delivery_addresses.length > 0) {
            rec.delivery_addresses.forEach(addr => addDeliveryAddressField(addr));
        }

        document.getElementById('custTin').value = rec.tin;
        document.getElementById('custContactPerson').value = rec.contact_person;
        document.getElementById('custEmail').value = rec.email;
        document.getElementById('custCountryOrigin').value = rec.country_origin;
        document.getElementById('custCountry').value = rec.country;
        document.getElementById('custFax').value = rec.fax && rec.fax !== '—' ? rec.fax : '';
        document.getElementById('custPhone').value = rec.phone;
        document.getElementById('custRemarks').value = rec.remarks || '';

        // Tab 2: Sales / DR
        document.getElementById('salesDrName').value = rec.sales_name || rec.name;
        document.getElementById('salesDecimalPrice').value = rec.decimal_price;
        document.getElementById('salesDecimalTotal').value = rec.decimal_total;
        document.getElementById('salesPaymentTerms').value = rec.payment_terms;
        document.getElementById('salesDiscountType').value = rec.discount_type || 'percent';
        document.getElementById('salesDiscountVal').value = parseFloat(rec.discount_val || 0).toFixed(2);
        document.getElementById('drName').value = rec.dr_name;
        document.getElementById('taxVatCode').value = rec.tax_vat_code;
        document.getElementById('taxInclusiveExclusive').value = rec.tax_inclusive_exclusive;
        document.getElementById('taxTaxCode').value = rec.tax_code;

        updateAddressOptions();
        document.getElementById('salesAddressSelect').value = rec.sales_address;
        document.getElementById('drAddressSelect').value = rec.dr_address;

        switchModalTab('cust-info');
        custModalBackdrop.classList.add('show');
        document.getElementById('custName').focus();
        if (window.lucide) window.lucide.createIcons();
    };

    function closeCustModal() {
        if (custModalBackdrop) custModalBackdrop.classList.remove('show');
    }

    if (btnOpenCreateModal) btnOpenCreateModal.addEventListener('click', openCreateCustomerModal);
    if (btnModalCloseCross) btnModalCloseCross.addEventListener('click', closeCustModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeCustModal);

    window.handleCustomerFormSubmit = function (event) {
        event.preventDefault();

        const idVal = document.getElementById('custFormId').value;
        const compId = document.getElementById('custCompanyId').value.trim();
        const salesInCharge = document.getElementById('custSalesInCharge').value.trim();
        const name = document.getElementById('custName').value.trim();
        const shortName = document.getElementById('custShortName').value.trim().toUpperCase();
        const businessStyle = document.getElementById('custBusinessStyle').value.trim();
        const officeAddress = document.getElementById('custOfficeAddress').value.trim();

        // Delivery addresses array
        const deliveryAddrs = [];
        document.querySelectorAll('.delivery-addr-input').forEach(inp => {
            const val = inp.value.trim();
            if (val) deliveryAddrs.push(val);
        });

        const tin = document.getElementById('custTin').value.trim();
        const contactPerson = document.getElementById('custContactPerson').value.trim();
        const email = document.getElementById('custEmail').value.trim();
        const countryOrigin = document.getElementById('custCountryOrigin').value.trim();
        const country = document.getElementById('custCountry').value.trim();
        const fax = document.getElementById('custFax').value.trim() || '—';
        const phone = document.getElementById('custPhone').value.trim();
        const remarks = document.getElementById('custRemarks').value.trim();

        // Sales / DR
        const salesName = document.getElementById('salesDrName').value.trim() || name;
        const decPrice = parseInt(document.getElementById('salesDecimalPrice').value, 10) || 2;
        const decTotal = parseInt(document.getElementById('salesDecimalTotal').value, 10) || 2;
        const paymentTerms = document.getElementById('salesPaymentTerms').value;
        const salesAddress = document.getElementById('salesAddressSelect').value;
        const discountType = document.getElementById('salesDiscountType').value;
        const discountVal = parseFloat(document.getElementById('salesDiscountVal').value) || 0;

        const drName = document.getElementById('drName').value.trim() || name;
        const drAddress = document.getElementById('drAddressSelect').value;

        const vatCode = document.getElementById('taxVatCode').value.trim();
        const vatIncExc = document.getElementById('taxInclusiveExclusive').value;
        const taxCode = document.getElementById('taxTaxCode').value;

        if (!compId || !name || !shortName || !officeAddress || !tin) return;

        if (idVal) {
            const index = customersData.findIndex(c => c.id === parseInt(idVal, 10));
            if (index !== -1) {
                customersData[index].company_id = compId;
                customersData[index].sales_in_charge = salesInCharge;
                customersData[index].name = name;
                customersData[index].short_name = shortName;
                customersData[index].business_style = businessStyle;
                customersData[index].office_address = officeAddress;
                customersData[index].delivery_addresses = deliveryAddrs;
                customersData[index].tin = tin;
                customersData[index].contact_person = contactPerson;
                customersData[index].email = email;
                customersData[index].country_origin = countryOrigin;
                customersData[index].country = country;
                customersData[index].fax = fax;
                customersData[index].phone = phone;
                customersData[index].remarks = remarks;

                customersData[index].sales_name = salesName;
                customersData[index].decimal_price = decPrice;
                customersData[index].decimal_total = decTotal;
                customersData[index].payment_terms = paymentTerms;
                customersData[index].sales_address = salesAddress;
                customersData[index].discount_type = discountType;
                customersData[index].discount_val = discountVal;

                customersData[index].dr_name = drName;
                customersData[index].dr_address = drAddress;

                customersData[index].tax_vat_code = vatCode;
                customersData[index].tax_inclusive_exclusive = vatIncExc;
                customersData[index].tax_code = taxCode;
            }
        } else {
            const nextId = customersData.length > 0 ? Math.max(...customersData.map(c => c.id)) + 1 : 1;
            const newCust = {
                id: nextId,
                company_id: compId,
                sales_in_charge: salesInCharge,
                name: name,
                short_name: shortName,
                business_style: businessStyle,
                office_address: officeAddress,
                delivery_addresses: deliveryAddrs,
                tin: tin,
                contact_person: contactPerson,
                email: email,
                country_origin: countryOrigin,
                country: country,
                fax: fax,
                phone: phone,
                remarks: remarks,
                sales_name: salesName,
                decimal_price: decPrice,
                decimal_total: decTotal,
                payment_terms: paymentTerms,
                sales_address: salesAddress,
                discount_type: discountType,
                discount_val: discountVal,
                dr_name: drName,
                dr_address: drAddress,
                tax_vat_code: vatCode,
                tax_inclusive_exclusive: vatIncExc,
                tax_code: taxCode,
                deleted_date: '—'
            };
            customersData.unshift(newCust);
        }

        closeCustModal();
        if (custSearchInput) custSearchInput.value = '';
        renderCustomerTable(customersData);
    };

    // 9. DELETE CONFIRMATION MODAL (SOFT DELETE)

    window.openDeleteCustomerModal = function (id) {
        closeAllActionMenus();
        const rec = customersData.find(r => r.id === id);
        if (!rec) return;

        activeDeleteId = id;
        const today = new Date().toISOString().split('T')[0];

        deleteTargetName.textContent = rec.name;
        deleteTargetShort.textContent = rec.short_name;
        deleteTargetDate.textContent = today;

        deleteConfirmBackdrop.classList.add('show');
        if (window.lucide) window.lucide.createIcons();
    };

    function closeDeleteModal() {
        if (deleteConfirmBackdrop) deleteConfirmBackdrop.classList.remove('show');
        activeDeleteId = null;
    }

    if (btnDeleteCloseCross) btnDeleteCloseCross.addEventListener('click', closeDeleteModal);
    if (btnCancelDelete) btnCancelDelete.addEventListener('click', closeDeleteModal);

    if (btnConfirmDelete) {
        btnConfirmDelete.addEventListener('click', function () {
            if (!activeDeleteId) return;

            const rec = customersData.find(r => r.id === activeDeleteId);
            if (rec) {
                const today = new Date().toISOString().split('T')[0];
                rec.deleted_date = today;
            }

            closeDeleteModal();
            if (custSearchInput) custSearchInput.value = '';
            renderCustomerTable(customersData);
        });
    }

    // 10. REFRESH & EXPORT ACTIONS

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            closeAllActionMenus();
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            btnRefresh.disabled = true;

            setTimeout(() => {
                if (custSearchInput) custSearchInput.value = '';
                renderCustomerTable(customersData);
                if (refreshIcon) refreshIcon.classList.remove('spin-anim');
                btnRefresh.disabled = false;
            }, 400);
        });
    }

    if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            closeAllActionMenus();
            exportMenu.classList.toggle('show');
        });
    }

    window.exportCustomerDirectory = function (format) {
        exportMenu.classList.remove('show');

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csvContent = 'Name,Short Name,Business Style,Office Address,TIN,Contact Person,Phone Number,Fax No,Country of Origin,Email Address,Payment Terms,Deleted Date\n';
            customersData.forEach(r => {
                csvContent += `"${r.name}","${r.short_name}","${r.business_style}","${r.office_address}","${r.tin}","${r.contact_person}","${r.phone}","${r.fax}","${r.country_origin}","${r.email}","${r.payment_terms}","${r.deleted_date}"\n`;
            });
            downloadFile(csvContent, 'customer-directory.csv', 'text/csv;charset=utf-8;');
        } else if (format === 'excel') {
            let tableHtml = '<table border="1"><thead><tr><th>Name</th><th>Short Name</th><th>Business Style</th><th>Office Address</th><th>TIN</th><th>Contact Person</th><th>Phone</th><th>Fax</th><th>Origin</th><th>Email</th><th>Payment Terms</th><th>Deleted Date</th></tr></thead><tbody>';
            customersData.forEach(r => {
                tableHtml += `<tr><td>${r.name}</td><td>${r.short_name}</td><td>${r.business_style}</td><td>${r.office_address}</td><td>${r.tin}</td><td>${r.contact_person}</td><td>${r.phone}</td><td>${r.fax}</td><td>${r.country_origin}</td><td>${r.email}</td><td>${r.payment_terms}</td><td>${r.deleted_date}</td></tr>`;
            });
            tableHtml += '</tbody></table>';
            downloadFile(tableHtml, 'customer-directory.xls', 'application/vnd.ms-excel');
        }
    };

    function downloadFile(content, fileName, mimeType) {
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // 11. INITIALIZATION

    renderCustomerTable(customersData);
});
</script>
@endpush

@endsection

