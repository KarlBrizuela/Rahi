@extends('layouts.app')

@section('title', 'Product Catalog — Sales')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/Product/product.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Sales</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Product</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="prodSearchInput" placeholder="Search product code, description, item code, platform..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Product Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload products via AJAX">
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
                    <button type="button" class="export-option" onclick="exportProductDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable product spec sheet list</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportProductDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted product inventory template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportProductDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Product Button (Opens 4-Tab Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal" onclick="openCreateProductModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Product</span>
            </button>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Product Master Catalog</span>
                    <span class="panel-count-badge" id="recordCount">4 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Commercial Finished Goods & Industrial Assemblies</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="productTable">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Product Code</th>
                            <th style="min-width: 220px;">Description</th>
                            <th style="min-width: 65px;" class="text-center">Rev</th>
                            <th style="min-width: 130px;">Item Code</th>
                            <th style="min-width: 110px;">Platform</th>
                            <th style="min-width: 110px;">Plan Type</th>
                            <th style="min-width: 80px;" class="text-center">UOP</th>
                            <th style="min-width: 80px;" class="text-center">UOP2</th>
                            <th style="min-width: 95px;" class="text-right">UOP2 Qty</th>
                            <th style="min-width: 100px;">Color</th>
                            <th style="min-width: 90px;" class="text-right">Thickness</th>
                            <th style="min-width: 85px;" class="text-right">MOQ</th>
                            <th style="min-width: 85px;" class="text-right">SPQ</th>
                            <th style="width: 60px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody">
                        <!-- Populated dynamically by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 4 of 4 product entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<div class="modal-backdrop" id="prodModalBackdrop">
    <div class="modal-dialog modal-dialog-xl" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create New Product</h3>
                <p class="modal-subtitle">Configure engineering specifications, units, customer commitments, inventory schedules, and linked supplies.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" onclick="closeProdModal()" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-tab-bar">
            <button type="button" class="modal-tab-btn active" id="tabBtnBasic" onclick="switchProductModalTab('basic')">
                <i data-lucide="layers" class="tab-btn-icon"></i>
                <span>Basic Info</span>
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnCustomer" onclick="switchProductModalTab('customer')">
                <i data-lucide="users" class="tab-btn-icon"></i>
                <span>Customer</span>
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnSched" onclick="switchProductModalTab('sched')">
                <i data-lucide="calendar" class="tab-btn-icon"></i>
                <span>Inventory Sched</span>
            </button>
            <button type="button" class="modal-tab-btn" id="tabBtnSupplies" onclick="switchProductModalTab('supplies')">
                <i data-lucide="link" class="tab-btn-icon"></i>
                <span>Link Supplies</span>
            </button>
        </div>

        <form id="productForm" onsubmit="handleProductFormSubmit(event)">
            <input type="hidden" id="prodRecordId" value="">

            <div class="modal-body tabbed-modal-body">

                <div class="modal-tab-pane active" id="paneBasic">

                    <!-- Section 1.1: Basic Information -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">1</span>
                            <span class="section-label">Basic Information</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodProductId">Product ID <span class="required">*</span></label>
                                <input type="text" id="prodProductId" class="form-input font-code" placeholder="e.g. PRD-001" required maxlength="30">
                            </div>
                            <div class="form-group flex-2">
                                <label for="prodCode">Product Code <span class="required">*</span></label>
                                <input type="text" id="prodCode" class="form-input font-code" placeholder="e.g. PCBA-M100-STD" required maxlength="50" style="text-transform: uppercase;">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodRev">Revision (Rev) <span class="required">*</span></label>
                                <input type="text" id="prodRev" class="form-input text-center font-code" placeholder="e.g. A.0" required maxlength="10" value="A.0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="prodDescription">Description <span class="required">*</span></label>
                            <input type="text" id="prodDescription" class="form-input" placeholder="e.g. Industrial Controller Mainboard with Heat Dissipation" required maxlength="180">
                        </div>
                    </div>

                    <!-- Section 1.2: Unit Specifications -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">2</span>
                            <span class="section-label">Unit Specifications</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodUop">UOP (Primary Unit) <span class="required">*</span></label>
                                <select id="prodUop" class="form-select" required>
                                    <option value="PCS">PCS — Pieces</option>
                                    <option value="BOX">BOX — Box</option>
                                    <option value="SET">SET — Complete Set</option>
                                    <option value="ROLL">ROLL — Roll</option>
                                    <option value="MTR">MTR — Meter</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodUop2">UOP 2 (Secondary Unit)</label>
                                <select id="prodUop2" class="form-select">
                                    <option value="BOX">BOX — Box</option>
                                    <option value="CTN">CTN — Carton</option>
                                    <option value="PLT">PLT — Pallet</option>
                                    <option value="PK">PK — Pack</option>
                                    <option value="NONE">None</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodQtyPerUop">Qty per UOP2</label>
                                <input type="number" id="prodQtyPerUop" class="form-input font-numeric" placeholder="e.g. 50" min="1" step="1" value="50">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodMoq">MOQ (Min Order Qty) <span class="required">*</span></label>
                                <input type="number" id="prodMoq" class="form-input font-numeric" placeholder="e.g. 100" min="1" step="1" required value="100">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodSpq">SPQ (Std Pack Qty) <span class="required">*</span></label>
                                <input type="number" id="prodSpq" class="form-input font-numeric" placeholder="e.g. 50" min="1" step="1" required value="50">
                            </div>
                        </div>
                    </div>

                    <!-- Section 1.3: Sales & Pricing -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">3</span>
                            <span class="section-label">Sales & Pricing Defaults</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodVat">VAT Type <span class="required">*</span></label>
                                <select id="prodVat" class="form-select" required>
                                    <option value="Customer Setting">Customer Setting (Inherit from Customer)</option>
                                    <option value="VAT Inclusive">VAT Inclusive (12% tax built-in)</option>
                                    <option value="VAT Exclusive">VAT Exclusive (12% tax added on top)</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodCurrency">Currency <span class="required">*</span></label>
                                <select id="prodCurrency" class="form-select" required>
                                    <option value="PHP">PHP (₱) — Philippine Peso</option>
                                    <option value="USD">USD ($) — US Dollar</option>
                                    <option value="JPY">JPY (¥) — Japanese Yen</option>
                                    <option value="EUR">EUR (€) — Euro</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodUnitCost">Unit Cost</label>
                                <input type="number" id="prodUnitCost" class="form-input font-numeric" placeholder="0.00" step="0.01" min="0" value="0.00">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodSellingPrice">Selling Price <span class="required">*</span></label>
                                <input type="number" id="prodSellingPrice" class="form-input font-numeric" placeholder="0.00" step="0.01" min="0" required value="0.00">
                            </div>
                        </div>
                    </div>

                    <!-- Section 1.4: Process Route Manager (Add, Edit, Delete Text Processes) -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">4</span>
                            <span class="section-label">Manufacturing Process Routing</span>
                        </div>
                        <p class="section-hint-text">Define the sequential shopfloor stages or assembly steps required to produce this product.</p>

                        <div class="process-input-cluster">
                            <input type="text" id="newProcessInput" class="form-input flex-1" placeholder="Enter process name (e.g. SMT Surface Mount, Ultrasonic Soldering, QA Final Test)..." onkeydown="handleProcessInputKey(event)">
                            <button type="button" class="btn-outline" onclick="addProcessItem()">
                                <i data-lucide="plus" class="btn-icon"></i>
                                <span>Add Process</span>
                            </button>
                        </div>

                        <div class="process-sequence-list" id="processListContainer">
                            <!-- Populated dynamically with numbered step chips -->
                        </div>
                    </div>

                    <!-- Section 1.5: Other Information -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">5</span>
                            <span class="section-label">Technical & Engineering Attributes</span>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodModel">Model</label>
                                <input type="text" id="prodModel" class="form-input" placeholder="e.g. Model-X 2026" maxlength="50">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodCategory">Category <span class="required">*</span></label>
                                <select id="prodCategory" class="form-select" required>
                                    <option value="Electronics & PCB">Electronics & PCB</option>
                                    <option value="Precision Metal Parts">Precision Metal Parts</option>
                                    <option value="Molded Plastics">Molded Plastics</option>
                                    <option value="Industrial Hardware">Industrial Hardware</option>
                                    <option value="Packaging & Carton">Packaging & Carton</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodPlanType">Plan Type <span class="required">*</span></label>
                                <select id="prodPlanType" class="form-select" required>
                                    <option value="Make to Stock (MTS)">Make to Stock (MTS)</option>
                                    <option value="Make to Order (MTO)">Make to Order (MTO)</option>
                                    <option value="Assemble to Order (ATO)">Assemble to Order (ATO)</option>
                                    <option value="Engineer to Order (ETO)">Engineer to Order (ETO)</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodItemCode">Item Code</label>
                                <input type="text" id="prodItemCode" class="form-input font-code" placeholder="e.g. ITM-8840" maxlength="40">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodPlatform">Platform</label>
                                <input type="text" id="prodPlatform" class="form-input" placeholder="e.g. Linux Embedded / ARM" maxlength="50">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodColor">Color</label>
                                <input type="text" id="prodColor" class="form-input" placeholder="e.g. Matte Black / Natural" maxlength="40">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodThickness">Thickness</label>
                                <input type="text" id="prodThickness" class="form-input" placeholder="e.g. 1.6 mm" maxlength="30">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodWeight">Weight</label>
                                <input type="text" id="prodWeight" class="form-input" placeholder="e.g. 245 g" maxlength="30">
                            </div>
                            <div class="form-group flex-1">
                                <label for="prodDivisor">Divisor</label>
                                <input type="number" id="prodDivisor" class="form-input font-numeric" placeholder="1" min="1" value="1">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="prodHeader">Product Header</label>
                                <input type="text" id="prodHeader" class="form-input" placeholder="e.g. Industrial IoT Division" maxlength="80">
                            </div>
                            <div class="form-group flex-2">
                                <label for="prodRemarks">Remarks</label>
                                <input type="text" id="prodRemarks" class="form-input" placeholder="Optional internal notes, engineering changes, special handling, etc." maxlength="200">
                            </div>
                        </div>
                    </div>

                    <!-- Section 1.6: Office / Branch Availability Checklist -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">6</span>
                            <span class="section-label">Office & Facility Allocation</span>
                        </div>
                        <p class="section-hint-text">Manage authorized offices, plant sites, and depots. Select the facilities authorized for this product, or add new offices:</p>

                        <!-- Quick Add Office Bar -->
                        <div class="process-input-cluster">
                            <input type="text" id="newOfficeInput" class="form-input flex-1" placeholder="Enter office or facility name (e.g. Subic Logistics Hub)..." onkeydown="handleOfficeKey(event)">
                            <button type="button" class="btn-outline" onclick="addOfficeRow()">
                                <i data-lucide="plus" class="btn-icon"></i>
                                <span>Add Office</span>
                            </button>
                        </div>

                        <!-- Office List Table -->
                        <div class="nested-table-wrap" style="max-height: 220px; overflow-y: auto;">
                            <table class="nested-erp-table">
                                <thead>
                                    <tr>
                                        <th style="width: 38px; text-align: center;">
                                            <input type="checkbox" id="selectAllOfficesCb" onchange="toggleSelectAllOffices(this.checked)" title="Select / Deselect All">
                                        </th>
                                        <th>Office / Facility Name</th>
                                        <th style="width: 70px; text-align: center;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="modalOfficesTableBody">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Section 1.7: Tag Color -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">7</span>
                            <span class="section-label">Identification Tag Color</span>
                        </div>
                        <div class="form-group" style="max-width: 320px; margin-bottom: 0;">
                            <label for="prodTagColor">Tag Color <span class="required">*</span></label>
                            <select id="prodTagColor" class="form-select">
                                <option value="#2563eb">Blue</option>
                                <option value="#ef4444">Red</option>
                                <option value="#10b981">Green</option>
                                <option value="#f59e0b">Yellow</option>
                                <option value="#ea580c">Orange</option>
                                <option value="#8b5cf6">Purple</option>
                                <option value="#0f172a">Black</option>
                                <option value="#ffffff">White</option>
                                <option value="#64748b">Gray</option>
                            </select>
                        </div>
                    </div>

                </div>

                <div class="modal-tab-pane" id="paneCustomer">
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">A</span>
                            <span class="section-label">Customer Commitments & Life Cycle</span>
                        </div>
                        <div class="form-group">
                            <label for="custTargetName">Customer Name <span class="required">*</span></label>
                            <input type="text" id="custTargetName" class="form-input" placeholder="e.g. San Miguel Corporation or Universal Robina Corp." required maxlength="120">
                        </div>
                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="custApprovalDate">Approval Date</label>
                                <input type="date" id="custApprovalDate" class="form-input font-numeric">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custEqlDate">EQL Date (Engineering Qualification Level)</label>
                                <input type="date" id="custEqlDate" class="form-input font-numeric">
                            </div>
                            <div class="form-group flex-1">
                                <label for="custEolDate">EOL Date (End of Life)</label>
                                <input type="date" id="custEolDate" class="form-input font-numeric">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="custApprovalRemarks">Approval Remarks</label>
                            <input type="text" id="custApprovalRemarks" class="form-input" placeholder="e.g. Approved under PPAP Level 3 criteria" maxlength="200">
                        </div>
                        <div class="form-group">
                            <label for="custEqlRemarks">EQL Remarks</label>
                            <input type="text" id="custEqlRemarks" class="form-input" placeholder="e.g. Environmental chamber stress test passed at 85°C" maxlength="200">
                        </div>
                    </div>
                </div>

                <div class="modal-tab-pane" id="paneSched">
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">B</span>
                            <span class="section-label">Inventory Setup & Cycle Planning</span>
                        </div>
                        <div class="form-row align-center">
                            <div class="form-group flex-1">
                                <label for="schedTypeSelect">Cycle Type <span class="required">*</span></label>
                                <select id="schedTypeSelect" class="form-select">
                                    <option value="Monthly">Monthly Cycle</option>
                                    <option value="Quarterly">Quarterly Cycle</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="schedMonthInput">Month <span class="required">*</span></label>
                                <select id="schedMonthInput" class="form-select">
                                    <option value="January">January</option>
                                    <option value="February">February</option>
                                    <option value="March">March</option>
                                    <option value="April">April</option>
                                    <option value="May">May</option>
                                    <option value="June">June</option>
                                    <option value="July">July</option>
                                    <option value="August">August</option>
                                    <option value="September">September</option>
                                    <option value="October">October</option>
                                    <option value="November">November</option>
                                    <option value="December">December</option>
                                </select>
                            </div>
                            <div class="form-group flex-1">
                                <label for="schedCutoffInput">Sched Cutoff Date <span class="required">*</span></label>
                                <input type="text" id="schedCutoffInput" class="form-input font-code" placeholder="e.g. Jan-31 or 15th-EOM" maxlength="30">
                            </div>
                            <div class="form-group" style="align-self: flex-end;">
                                <button type="button" class="btn-primary" onclick="addScheduleEntry()">
                                    <i data-lucide="plus" class="btn-icon"></i>
                                    <span>Add Sched</span>
                                </button>
                            </div>
                        </div>

                        <!-- Schedules Table -->
                        <div class="nested-table-wrap">
                            <table class="nested-erp-table" id="schedEntriesTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;" class="text-center">#</th>
                                        <th style="min-width: 140px;">Month</th>
                                        <th style="min-width: 180px;">Sched Cutoff Date</th>
                                        <th style="min-width: 140px;">Cycle Status</th>
                                        <th style="width: 70px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="schedEntriesTableBody">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-tab-pane" id="paneSupplies">
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <span class="badge-num">C</span>
                            <span class="section-label">Link Supplies & Raw Materials</span>
                        </div>
                        <p class="section-hint-text">Define component supplies, bill of materials items, packaging, or consumables tied to this product.</p>

                        <div class="form-row align-center">
                            <div class="form-group flex-2">
                                <label for="newSupplyName">Supplies Name <span class="required">*</span></label>
                                <input type="text" id="newSupplyName" class="form-input" placeholder="e.g. FR4 PCB Substrate 4-Layer" maxlength="80">
                            </div>
                            <div class="form-group flex-2">
                                <label for="newSupplyDesc">Supplies Description</label>
                                <input type="text" id="newSupplyDesc" class="form-input" placeholder="e.g. High Tg 170°C Copper Clad Laminate" maxlength="120">
                            </div>
                            <div class="form-group flex-1">
                                <label for="newSupplyQty">Qty <span class="required">*</span></label>
                                <input type="number" id="newSupplyQty" class="form-input font-numeric" placeholder="1.00" step="0.01" min="0.01" value="1.00">
                            </div>
                            <div class="form-group flex-1">
                                <label for="newSupplyUom">UOM <span class="required">*</span></label>
                                <select id="newSupplyUom" class="form-select">
                                    <option value="PCS">PCS</option>
                                    <option value="KG">KG</option>
                                    <option value="MTR">MTR</option>
                                    <option value="SHT">SHT</option>
                                    <option value="SET">SET</option>
                                    <option value="ROLL">ROLL</option>
                                </select>
                            </div>
                            <div class="form-group" style="align-self: flex-end;">
                                <button type="button" class="btn-primary" onclick="addSupplyItem()">
                                    <i data-lucide="plus" class="btn-icon"></i>
                                    <span>Link Supply</span>
                                </button>
                            </div>
                        </div>

                        <!-- Link Supplies Table -->
                        <div class="nested-table-wrap">
                            <table class="nested-erp-table" id="suppliesEntriesTable">
                                <thead>
                                    <tr>
                                        <th style="min-width: 180px;">Supplies Name</th>
                                        <th style="min-width: 220px;">Supplies Description</th>
                                        <th style="min-width: 90px;" class="text-right">Qty</th>
                                        <th style="min-width: 80px;" class="text-center">UOM</th>
                                        <th style="width: 70px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="suppliesEntriesTableBody">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnModalCancel" onclick="closeProdModal()">Cancel</button>
                <button type="submit" class="btn-save" id="btnSubmitForm">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSubmitLabel">Save Product</span>
                </button>
            </div>
        </form>

    </div>
</div>

<div class="modal-backdrop" id="viewModalBackdrop">
    <div class="modal-dialog modal-dialog-xl" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="viewModalTitle">Product Specification Profile</h3>
                <p class="modal-subtitle">Comprehensive manufacturing data, process sequence, customer parameters, schedules, and bill of materials.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnViewModalCloseCross" title="Close view modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body view-modal-body">
            <!-- Hero -->
            <div class="view-prod-hero">
                <div class="view-tag-indicator" id="viewTagColorIndicator" style="background-color: #2563eb;"></div>
                <div class="view-hero-meta">
                    <div class="hero-name-row">
                        <h4 class="view-fullname" id="viewProductDesc">Product Description</h4>
                        <span class="status-pill status-pill-active" id="viewStatusPill">Active Catalog</span>
                    </div>
                    <span class="view-code-sub" id="viewCodeSub">Code: PCBA-001 &bull; Rev: A.0 &bull; Platform: Linux</span>
                </div>
            </div>

            <!-- Details 2-Column Grid -->
            <div class="view-sections-grid">

                <!-- Col 1: Basic Info, Units, Sales, Processes -->
                <div class="view-card-box">
                    <span class="view-box-title">Basic & Engineering Specs</span>
                    <div class="view-kv-list">
                        <div class="kv-item"><span class="kv-label">Product ID:</span><span class="kv-val font-code" id="viewProdId">—</span></div>
                        <div class="kv-item"><span class="kv-label">Product Code:</span><span class="kv-val font-code font-bold" id="viewCode">—</span></div>
                        <div class="kv-item"><span class="kv-label">Revision:</span><span class="kv-val font-code" id="viewRev">—</span></div>
                        <div class="kv-item"><span class="kv-label">Item Code:</span><span class="kv-val font-code" id="viewItemCode">—</span></div>
                        <div class="kv-item"><span class="kv-label">Platform:</span><span class="kv-val" id="viewPlatform">—</span></div>
                        <div class="kv-item"><span class="kv-label">Plan Type:</span><span class="kv-val font-medium" id="viewPlanType">—</span></div>
                        <div class="kv-item"><span class="kv-label">UOP / UOP2:</span><span class="kv-val font-medium" id="viewUnits">—</span></div>
                        <div class="kv-item"><span class="kv-label">MOQ & SPQ:</span><span class="kv-val font-numeric" id="viewMoqSpq">—</span></div>
                        <div class="kv-item"><span class="kv-label">VAT & Currency:</span><span class="kv-val font-medium" id="viewVatCurrency">—</span></div>
                        <div class="kv-item"><span class="kv-label">Unit Cost / Selling Price:</span><span class="kv-val font-numeric" id="viewPrices">—</span></div>
                        <div class="kv-item"><span class="kv-label">Color & Thickness:</span><span class="kv-val" id="viewPhysical">—</span></div>
                        <div class="kv-item"><span class="kv-label">Weight & Divisor:</span><span class="kv-val" id="viewWeightDivisor">—</span></div>
                        <div class="kv-item"><span class="kv-label">Product Header:</span><span class="kv-val" id="viewProductHeader">—</span></div>
                        <div class="kv-item"><span class="kv-label">Remarks:</span><span class="kv-val" id="viewRemarks">—</span></div>
                        <div class="kv-item kv-item-vertical">
                            <span class="kv-label">Authorized Offices:</span>
                            <div class="view-office-tags" id="viewOfficesList"></div>
                        </div>
                        <div class="kv-item kv-item-vertical">
                            <span class="kv-label">Process Sequence Routing:</span>
                            <div class="view-process-steps-list" id="viewProcessStepsList"></div>
                        </div>
                    </div>
                </div>

                <!-- Col 2: Customer, Inventory Sched, Link Supplies -->
                <div class="view-card-box">
                    <span class="view-box-title">Customer, Inventory & Linked Supplies</span>
                    <div class="view-kv-list">
                        <!-- Customer -->
                        <div class="kv-item"><span class="kv-label">Customer Name:</span><span class="kv-val font-bold" id="viewCustomerName">—</span></div>
                        <div class="kv-item"><span class="kv-label">Approval Date:</span><span class="kv-val font-numeric" id="viewApprovalDate">—</span></div>
                        <div class="kv-item"><span class="kv-label">EQL Date:</span><span class="kv-val font-numeric" id="viewEqlDate">—</span></div>
                        <div class="kv-item"><span class="kv-label">EOL Date:</span><span class="kv-val font-numeric" id="viewEolDate">—</span></div>
                        <div class="kv-item"><span class="kv-label">Approval Remarks:</span><span class="kv-val" id="viewApprovalRemarks">—</span></div>
                        <div class="kv-item"><span class="kv-label">EQL Remarks:</span><span class="kv-val" id="viewEqlRemarks">—</span></div>

                        <!-- Inventory Schedule -->
                        <div class="kv-item kv-item-vertical">
                            <span class="kv-label">Inventory Schedules:</span>
                            <div class="view-sched-summary" id="viewSchedSummary"></div>
                        </div>

                        <!-- Linked Supplies -->
                        <div class="kv-item kv-item-vertical">
                            <span class="kv-label">Linked Supplies (BOM Components):</span>
                            <div class="view-supplies-summary" id="viewSuppliesSummary"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnViewModalClose">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit-2" class="btn-icon"></i>
                <span>Edit Product</span>
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
                    <h3 class="modal-title" id="deleteModalTitle">Delete Product</h3>
                    <p class="modal-subtitle">Soft-delete and archive this catalog specification.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete product <strong id="deleteTargetCode">PCBA-001</strong> (<span id="deleteTargetDesc">Description</span>)?
            </p>
            <p class="delete-sub-text">
                This item will be soft-deleted on <span id="deleteTargetDate">2026-11-05</span> and marked as archived.
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
// Global HTML Escape Helper
function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {

    // 1. DATA REPOSITORY & INITIAL STATE

    let productsData = [
        {
            id: 1,
            product_id: 'PRD-001',
            product_code: 'PCB-CTL-400',
            description: 'Embedded Linux Mainboard Controller V4',
            rev: 'B.2',
            item_code: 'ITM-4001',
            platform: 'ARM Cortex',
            plan_type: 'Make to Stock (MTS)',
            uop: 'PCS',
            uop2: 'BOX',
            uop2_qty: 25,
            moq: 100,
            spq: 25,
            color: 'Matte Blue',
            thickness: '1.6 mm',
            weight: '180 g',
            divisor: 1,
            category: 'Electronics & PCB',
            model: 'X-Gen 400',
            header: 'Industrial Automation',
            remarks: 'Requires nitrogen reflow soldering.',
            offices: ['Head Office (Mandaluyong)', 'Laguna Main Plant Site', 'Makati Sales Center'],
            tag_color: '#2563eb',
            // Sales
            vat: 'Customer Setting',
            currency: 'PHP',
            unit_cost: 1450.00,
            selling_price: 2850.00,
            // Processes
            processes: ['SMT Component Placement', 'Reflow Soldering', 'In-Circuit Test (ICT)', 'Final Assembly'],
            // Customer
            customer_name: 'San Miguel Corporation',
            approval_date: '2026-02-15',
            eql_date: '2026-03-01',
            eol_date: '2030-12-31',
            approval_remarks: 'PPAP Level 3 Qualification complete',
            eql_remarks: 'Thermal stress tolerance passed',
            // Inventory Sched
            sched_type: 'Monthly',
            schedules: [
                { id: 1, month: 'January', sched: 'Jan-31', status: 'Scheduled' },
                { id: 2, month: 'February', sched: 'Feb-28', status: 'Scheduled' },
                { id: 3, month: 'March', sched: 'Mar-31', status: 'Scheduled' }
            ],
            // Link Supplies
            supplies: [
                { id: 1, name: 'FR4 Multilayer PCB', desc: '4-Layer TG170 Substrate', qty: 1.00, uom: 'PCS' },
                { id: 2, name: 'Microcontroller IC', desc: 'ARM 32-bit Cortex M4', qty: 1.00, uom: 'PCS' },
                { id: 3, name: 'Solder Paste Sn96.5', desc: 'Lead-Free SAC305 Paste', qty: 0.05, uom: 'KG' }
            ],
            deleted_date: '—'
        },
        {
            id: 2,
            product_id: 'PRD-002',
            product_code: 'AL-HEAT-70',
            description: 'Anodized Extruded Aluminum Heat Sink 70mm',
            rev: 'A.0',
            item_code: 'ITM-7022',
            platform: 'Hardware Platform',
            plan_type: 'Make to Order (MTO)',
            uop: 'PCS',
            uop2: 'CTN',
            uop2_qty: 50,
            moq: 200,
            spq: 50,
            color: 'Silver Anodized',
            thickness: '3.0 mm',
            weight: '95 g',
            divisor: 1,
            category: 'Precision Metal Parts',
            model: 'HS-70-EXT',
            header: 'Thermal Solutions',
            remarks: 'Deburr all perimeter fin edges.',
            offices: ['Head Office (Mandaluyong)', 'Laguna Main Plant Site'],
            tag_color: '#10b981',
            vat: 'VAT Inclusive',
            currency: 'PHP',
            unit_cost: 185.00,
            selling_price: 360.00,
            processes: ['CNC Billet Extrusion', 'Precision Cut-to-Length', 'Sulfuric Anodizing', 'QC Visual Check'],
            customer_name: 'Universal Robina Corporation',
            approval_date: '2026-04-10',
            eql_date: '2026-04-20',
            eol_date: '2029-06-30',
            approval_remarks: 'Sample fit certified on power supply chassis',
            eql_remarks: 'Salt spray corrosion test passed',
            sched_type: 'Monthly',
            schedules: [
                { id: 1, month: 'January', sched: 'Jan-31', status: 'Scheduled' },
                { id: 2, month: 'February', sched: 'Feb-28', status: 'Scheduled' }
            ],
            supplies: [
                { id: 1, name: 'Alloy 6063-T6 Bar', desc: 'Structural Extrusion Alloy', qty: 0.12, uom: 'KG' },
                { id: 2, name: 'Anodize Sealant Chemical', desc: 'Nickel Acetate Bath', qty: 0.02, uom: 'KG' }
            ],
            deleted_date: '—'
        },
        {
            id: 3,
            product_id: 'PRD-003',
            product_code: 'ENCL-ABS-12',
            description: 'Weatherproof ABS Enclosure IP67 Gray',
            rev: 'C.1',
            item_code: 'ITM-9100',
            platform: 'Enclosure Series',
            plan_type: 'Make to Stock (MTS)',
            uop: 'BOX',
            uop2: 'PLT',
            uop2_qty: 10,
            moq: 50,
            spq: 10,
            color: 'RAL 7035 Gray',
            thickness: '2.5 mm',
            weight: '410 g',
            divisor: 1,
            category: 'Molded Plastics',
            model: 'WP-120-ABS',
            header: 'Outdoor Hardware',
            remarks: 'Silicon O-ring gasket included in box.',
            offices: ['Laguna Main Plant Site', 'Cebu Distribution Depot'],
            tag_color: '#f59e0b',
            vat: 'VAT Inclusive',
            currency: 'PHP',
            unit_cost: 420.00,
            selling_price: 890.00,
            processes: ['Injection Molding', 'Gasket Channel Sealing', 'Ultrasonic Brass Thread Insert', 'Packaging'],
            customer_name: 'Robinsons Retail Holdings',
            approval_date: '2026-05-18',
            eql_date: '2026-06-01',
            eol_date: '2032-12-31',
            approval_remarks: 'IP67 water ingress certification verified',
            eql_remarks: 'UV exposure weatherability passed',
            sched_type: 'Quarterly',
            schedules: [
                { id: 1, month: 'March', sched: 'Mar-31', status: 'Scheduled' },
                { id: 2, month: 'June', sched: 'Jun-30', status: 'Scheduled' },
                { id: 3, month: 'September', sched: 'Sep-30', status: 'Scheduled' }
            ],
            supplies: [
                { id: 1, name: 'ABS Polymer Granules', desc: 'Flame Retardant UL94-V0', qty: 0.45, uom: 'KG' },
                { id: 2, name: 'EPDM Rubber Gasket', desc: 'Preformed O-Ring Seal', qty: 1.00, uom: 'PCS' },
                { id: 3, name: 'M4 Brass Threaded Inserts', desc: 'Hexagonal Tapered Nuts', qty: 4.00, uom: 'PCS' }
            ],
            deleted_date: '—'
        },
        {
            id: 4,
            product_id: 'PRD-004',
            product_code: 'BRG-SS-6202',
            description: 'Stainless Steel Ball Bearing 6202-2RS (Legacy)',
            rev: 'A.1',
            item_code: 'ITM-6202',
            platform: 'Rotating Assemblies',
            plan_type: 'Make to Stock (MTS)',
            uop: 'PCS',
            uop2: 'BOX',
            uop2_qty: 100,
            moq: 100,
            spq: 100,
            color: 'Natural Metal',
            thickness: '11.0 mm',
            weight: '45 g',
            divisor: 1,
            category: 'Industrial Hardware',
            model: 'BRG-6202',
            header: 'Machine Spares',
            remarks: 'Discontinued SKU replaced by 6202-ZZ series.',
            offices: ['Head Office (Mandaluyong)'],
            tag_color: '#475569',
            vat: 'VAT Exclusive',
            currency: 'PHP',
            unit_cost: 95.00,
            selling_price: 210.00,
            processes: ['Ring Machining', 'Heat Treatment & Hardening', 'Precision Grinding', 'Rubber Seal Assembly'],
            customer_name: 'Apex Precision Engineering Supplies',
            approval_date: '2025-01-15',
            eql_date: '2025-02-01',
            eol_date: '2026-08-14',
            approval_remarks: 'Discontinued product line agreement',
            eql_remarks: 'Archive record maintained for warranty',
            sched_type: 'Monthly',
            schedules: [
                { id: 1, month: 'August', sched: 'Aug-31', status: 'Completed' }
            ],
            supplies: [
                { id: 1, name: 'AISI 440C Stainless Rod', desc: 'Martensitic Bearing Steel', qty: 0.06, uom: 'KG' },
                { id: 2, name: 'Nitrile Rubber Seals', desc: 'Dual Contact Oil Seals', qty: 2.00, uom: 'PCS' }
            ],
            deleted_date: '2026-08-14'
        }
    ];

    let currentOpenActionMenuId = null;
    let activeDeleteId = null;
    let activeViewId = null;

    // Temporary working state for modal child arrays
    let modalProcesses = [];
    let modalSchedules = [];
    let modalSupplies = [];

    // DOM Elements
    const productTableBody = document.getElementById('productTableBody');
    const prodSearchInput = document.getElementById('prodSearchInput');
    const recordCountBadge = document.getElementById('recordCount');
    const footerMetaText = document.getElementById('footerMetaText');

    // Modal Elements
    const prodModalBackdrop = document.getElementById('prodModalBackdrop');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');
    const btnModalCancel = document.getElementById('btnModalCancel');
    const productForm = document.getElementById('productForm');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');

    // Child Containers
    const processListContainer = document.getElementById('processListContainer');
    const schedEntriesTableBody = document.getElementById('schedEntriesTableBody');
    const suppliesEntriesTableBody = document.getElementById('suppliesEntriesTableBody');

    // View Modal
    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const btnViewModalCloseCross = document.getElementById('btnViewModalCloseCross');
    const btnViewModalClose = document.getElementById('btnViewModalClose');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');

    // Delete Modal
    const deleteConfirmBackdrop = document.getElementById('deleteConfirmBackdrop');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const deleteTargetCode = document.getElementById('deleteTargetCode');
    const deleteTargetDesc = document.getElementById('deleteTargetDesc');
    const deleteTargetDate = document.getElementById('deleteTargetDate');

    // Refresh & Export
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // 2. MODAL TAB SWITCHER (BASIC, CUSTOMER, SCHED, SUPPLIES)

    window.switchProductModalTab = function (tabKey) {
        const tabs = ['basic', 'customer', 'sched', 'supplies'];
        tabs.forEach(t => {
            const btn = document.getElementById(`tabBtn${t.charAt(0).toUpperCase() + t.slice(1)}`);
            const pane = document.getElementById(`pane${t.charAt(0).toUpperCase() + t.slice(1)}`);
            if (t === tabKey) {
                if (btn) btn.classList.add('active');
                if (pane) pane.classList.add('active');
            } else {
                if (btn) btn.classList.remove('active');
                if (pane) pane.classList.remove('active');
            }
        });
        if (window.lucide) window.lucide.createIcons();
    };

    // 3. TAG COLOR SELECTION

    function setModalColor(col) {
        const select = document.getElementById('prodTagColor');
        if (select) {
            select.value = col;
            // If the color isn't one of the predefined options, append it dynamically
            if (!select.value && col) {
                const customOpt = document.createElement('option');
                customOpt.value = col;
                customOpt.textContent = `Other (${col})`;
                select.appendChild(customOpt);
                select.value = col;
            }
        }
    }

    // 3.5. OFFICE ALLOCATION MANAGEMENT (TABLE WITH CHECKBOXES & ADD OFFICE)

    const defaultOfficesList = [
        'Head Office (Mandaluyong)',
        'Makati Sales Center',
        'Laguna Main Plant Site',
        'Cebu Distribution Depot',
        'Davao Regional Hub'
    ];

    let modalOffices = [];

    function initModalOffices(selectedOffices = ['Head Office (Mandaluyong)', 'Makati Sales Center', 'Laguna Main Plant Site']) {
        const set = new Set(selectedOffices || []);
        const combined = [...defaultOfficesList];
        (selectedOffices || []).forEach(name => {
            if (!combined.includes(name)) combined.push(name);
        });

        modalOffices = combined.map((name, idx) => ({
            id: `off_${idx}_${Date.now()}`,
            name: name,
            selected: set.has(name)
        }));

        renderModalOffices();
    }

    window.addOfficeRow = function () {
        const input = document.getElementById('newOfficeInput');
        if (!input) return;
        const val = input.value.trim();
        if (!val) {
            input.focus();
            return;
        }

        const existing = modalOffices.find(o => o.name.toLowerCase() === val.toLowerCase());
        if (existing) {
            existing.selected = true;
        } else {
            modalOffices.push({
                id: `off_${Date.now()}`,
                name: val,
                selected: true
            });
        }

        input.value = '';
        renderModalOffices();
    };

    window.handleOfficeKey = function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addOfficeRow();
        }
    };

    window.toggleOfficeCb = function (idx, checked) {
        if (modalOffices[idx]) {
            modalOffices[idx].selected = checked;
        }
        updateSelectAllOfficesState();
    };

    window.toggleSelectAllOffices = function (checked) {
        modalOffices.forEach(o => o.selected = checked);
        renderModalOffices();
    };

    window.removeOfficeRow = function (idx) {
        modalOffices.splice(idx, 1);
        renderModalOffices();
    };

    function updateSelectAllOfficesState() {
        const selectAllCb = document.getElementById('selectAllOfficesCb');
        if (!selectAllCb) return;
        const total = modalOffices.length;
        const selectedCount = modalOffices.filter(o => o.selected).length;
        selectAllCb.checked = total > 0 && selectedCount === total;
        selectAllCb.indeterminate = selectedCount > 0 && selectedCount < total;
    }

    function renderModalOffices() {
        const tbody = document.getElementById('modalOfficesTableBody');
        if (!tbody) return;
        tbody.innerHTML = '';

        if (modalOffices.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center text-muted" style="padding: 14px;">
                        No offices configured. Add an office using the input above.
                    </td>
                </tr>
            `;
            updateSelectAllOfficesState();
            return;
        }

        modalOffices.forEach((office, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td style="width: 38px; text-align: center;">
                    <input type="checkbox" class="office-row-cb" ${office.selected ? 'checked' : ''} onchange="toggleOfficeCb(${idx}, this.checked)">
                </td>
                <td>
                    <span class="font-medium text-dark">${escapeHtml(office.name)}</span>
                </td>
                <td style="width: 70px; text-align: center;">
                    <button type="button" class="btn-del-mini" onclick="removeOfficeRow(${idx})" title="Remove office">
                        <i data-lucide="trash-2" style="width: 13px; height: 13px;"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updateSelectAllOfficesState();
        if (window.lucide) window.lucide.createIcons();
    }

    // 4. PROCESS MANAGEMENT (ADD, EDIT, DELETE TEXT PROCESSES)

    window.handleProcessInputKey = function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addProcessItem();
        }
    };

    window.addProcessItem = function () {
        const input = document.getElementById('newProcessInput');
        const val = input.value.trim();
        if (!val) return;
        modalProcesses.push(val);
        input.value = '';
        renderModalProcesses();
    };

    window.editProcessItem = function (index) {
        const currentVal = modalProcesses[index];
        const updated = prompt('Edit process name:', currentVal);
        if (updated !== null && updated.trim() !== '') {
            modalProcesses[index] = updated.trim();
            renderModalProcesses();
        }
    };

    window.removeProcessItem = function (index) {
        modalProcesses.splice(index, 1);
        renderModalProcesses();
    };

    function renderModalProcesses() {
        processListContainer.innerHTML = '';
        if (modalProcesses.length === 0) {
            processListContainer.innerHTML = '<span class="text-muted" style="font-size: 11.5px;">No processes added yet. Enter a process above.</span>';
            return;
        }

        modalProcesses.forEach((proc, idx) => {
            const item = document.createElement('div');
            item.className = 'process-step-chip';
            item.innerHTML = `
                <span class="process-step-num">${idx + 1}</span>
                <span class="process-step-text">${escapeHtml(proc)}</span>
                <div class="process-chip-actions">
                    <button type="button" class="btn-chip-action" onclick="editProcessItem(${idx})" title="Edit process">
                        <i data-lucide="edit-2"></i>
                    </button>
                    <button type="button" class="btn-chip-action btn-chip-del" onclick="removeProcessItem(${idx})" title="Remove process">
                        <i data-lucide="x"></i>
                    </button>
                </div>
            `;
            processListContainer.appendChild(item);
        });
        if (window.lucide) window.lucide.createIcons();
    }

    // 5. INVENTORY SCHEDULES MANAGEMENT (MONTH & SCHED CUTOFF)

    window.addScheduleEntry = function () {
        const monthSel = document.getElementById('schedMonthInput');
        const cutoffInput = document.getElementById('schedCutoffInput');
        const month = monthSel.value;
        const cutoff = cutoffInput.value.trim();

        if (!cutoff) {
            cutoffInput.focus();
            return;
        }

        modalSchedules.push({
            id: Date.now(),
            month: month,
            sched: cutoff,
            status: 'Scheduled'
        });

        cutoffInput.value = '';
        renderModalSchedules();
    };

    window.removeScheduleEntry = function (id) {
        modalSchedules = modalSchedules.filter(s => s.id !== id);
        renderModalSchedules();
    };

    function renderModalSchedules() {
        schedEntriesTableBody.innerHTML = '';
        if (modalSchedules.length === 0) {
            schedEntriesTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 16px;">
                        No schedule entries defined. Add months and cutoff dates above.
                    </td>
                </tr>
            `;
            return;
        }

        modalSchedules.forEach((s, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="text-center font-code">${idx + 1}</td>
                <td class="font-medium">${escapeHtml(s.month)}</td>
                <td class="font-code">${escapeHtml(s.sched)}</td>
                <td><span class="badge-status-neutral">${escapeHtml(s.status || 'Scheduled')}</span></td>
                <td class="text-center">
                    <button type="button" class="btn-del-mini" onclick="removeScheduleEntry(${s.id})" title="Remove schedule">
                        <i data-lucide="trash-2"></i>
                    </button>
                </td>
            `;
            schedEntriesTableBody.appendChild(tr);
        });
        if (window.lucide) window.lucide.createIcons();
    }

    // 6. LINK SUPPLIES MANAGEMENT (NAME, DESCRIPTION, QTY, UOM)

    window.addSupplyItem = function () {
        const nameInput = document.getElementById('newSupplyName');
        const descInput = document.getElementById('newSupplyDesc');
        const qtyInput = document.getElementById('newSupplyQty');
        const uomInput = document.getElementById('newSupplyUom');

        const name = nameInput.value.trim();
        const desc = descInput.value.trim() || '—';
        const qty = parseFloat(qtyInput.value) || 1.00;
        const uom = uomInput.value;

        if (!name) {
            nameInput.focus();
            return;
        }

        modalSupplies.push({
            id: Date.now(),
            name: name,
            desc: desc,
            qty: qty,
            uom: uom
        });

        nameInput.value = '';
        descInput.value = '';
        qtyInput.value = '1.00';
        renderModalSupplies();
    };

    window.removeSupplyItem = function (id) {
        modalSupplies = modalSupplies.filter(s => s.id !== id);
        renderModalSupplies();
    };

    function renderModalSupplies() {
        suppliesEntriesTableBody.innerHTML = '';
        if (modalSupplies.length === 0) {
            suppliesEntriesTableBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted" style="padding: 16px;">
                        No linked supplies or components configured yet.
                    </td>
                </tr>
            `;
            return;
        }

        modalSupplies.forEach(s => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-medium">${escapeHtml(s.name)}</td>
                <td class="text-muted">${escapeHtml(s.desc)}</td>
                <td class="text-right font-numeric font-medium">${parseFloat(s.qty).toFixed(2)}</td>
                <td class="text-center font-code">${escapeHtml(s.uom)}</td>
                <td class="text-center">
                    <button type="button" class="btn-del-mini" onclick="removeSupplyItem(${s.id})" title="Remove supply component">
                        <i data-lucide="trash-2"></i>
                    </button>
                </td>
            `;
            suppliesEntriesTableBody.appendChild(tr);
        });
        if (window.lucide) window.lucide.createIcons();
    }

    // 7. RENDER MASTER PRODUCT TABLE

    function renderProductTable(items) {
        if (!productTableBody) return;
        productTableBody.innerHTML = '';

        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="14" class="table-empty-state">
                    <div class="empty-box">
                        <i data-lucide="layers" class="empty-icon"></i>
                        <span class="empty-text">No products found</span>
                        <small class="empty-subtext">Try refining your search query or create a new product entry.</small>
                    </div>
                </td>
            `;
            productTableBody.appendChild(tr);
            if (window.lucide) window.lucide.createIcons();
            updateMetaCounts(0, productsData.length);
            return;
        }

        items.forEach(rec => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', rec.id);

            const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
            if (isDeleted) tr.classList.add('row-deleted');

            tr.innerHTML = `
                <td>
                    <div class="code-cell-wrapper">
                        <span class="tag-color-bullet" style="background-color: ${rec.tag_color || '#2563eb'};"></span>
                        <strong class="font-code ${isDeleted ? 'text-muted' : 'text-dark'}">${escapeHtml(rec.product_code)}</strong>
                    </div>
                </td>
                <td class="${isDeleted ? 'text-muted' : ''}">
                    <span class="cell-desc" title="${escapeHtml(rec.description)}">${escapeHtml(rec.description)}</span>
                </td>
                <td class="text-center font-code">${escapeHtml(rec.rev)}</td>
                <td class="font-code text-muted">${escapeHtml(rec.item_code || '—')}</td>
                <td>${escapeHtml(rec.platform || '—')}</td>
                <td><span class="plantype-pill">${escapeHtml(rec.plan_type)}</span></td>
                <td class="text-center font-code font-medium">${escapeHtml(rec.uop)}</td>
                <td class="text-center font-code text-muted">${escapeHtml(rec.uop2 || '—')}</td>
                <td class="text-right font-numeric">${rec.uop2_qty || '—'}</td>
                <td>${escapeHtml(rec.color || '—')}</td>
                <td class="text-right font-numeric">${escapeHtml(rec.thickness || '—')}</td>
                <td class="text-right font-numeric font-medium">${rec.moq}</td>
                <td class="text-right font-numeric">${rec.spq}</td>
                <td class="text-center no-print">
                    <div class="action-dropdown-wrapper">
                        <button type="button" 
                                class="action-dots-btn" 
                                onclick="toggleProductActionMenu(event, ${rec.id})"
                                title="Actions for ${escapeHtml(rec.product_code)}">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="action-dropdown-menu" id="actionMenu-${rec.id}">
                            <button type="button" class="action-menu-item" onclick="openViewProductModal(${rec.id})">
                                <i data-lucide="eye"></i>
                                <span>View</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="openEditProductModal(${rec.id})">
                                <i data-lucide="edit-2"></i>
                                <span>Edit</span>
                            </button>
                            <div class="menu-divider"></div>
                            <button type="button" class="action-menu-item item-danger" onclick="openDeleteProductModal(${rec.id})">
                                <i data-lucide="trash-2"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </td>
            `;

            productTableBody.appendChild(tr);
        });

        if (window.lucide) window.lucide.createIcons();
        updateMetaCounts(items.length, productsData.length);
    }

    function updateMetaCounts(filteredCount, totalCount) {
        if (recordCountBadge) recordCountBadge.textContent = `${filteredCount} Records`;
        if (footerMetaText) footerMetaText.textContent = `Showing 1 to ${filteredCount} of ${totalCount} product entries`;
    }

    // 8. ACTION MENU TOGGLE WITH PROVEN DROP-DOWN & DROP-UP SAFETY

    window.toggleProductActionMenu = function (event, recId) {
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

            // Never drop-up on row 0 or when total filtered rows <= 2 (prevents clipping with <thead>)
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
            closeProdModal();
            closeViewModal();
            closeDeleteModal();
            if (exportMenu) exportMenu.classList.remove('show');
        }
    });

    // 9. SEARCH FILTERING

    if (prodSearchInput) {
        prodSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            closeAllActionMenus();

            if (!query) {
                renderProductTable(productsData);
                return;
            }

            const filtered = productsData.filter(r => {
                return (r.product_code || '').toLowerCase().includes(query) ||
                       (r.description || '').toLowerCase().includes(query) ||
                       (r.item_code || '').toLowerCase().includes(query) ||
                       (r.platform || '').toLowerCase().includes(query) ||
                       (r.plan_type || '').toLowerCase().includes(query) ||
                       (r.color || '').toLowerCase().includes(query);
            });

            renderProductTable(filtered);
        });
    }

    // 10. CREATE / EDIT PRODUCT MODAL

    window.openCreateProductModal = function () {
        closeAllActionMenus();
        productForm.reset();
        document.getElementById('prodRecordId').value = '';
        modalTitle.textContent = 'Create New Product';
        btnSubmitLabel.textContent = 'Save Product';

        // Auto ID
        const nextId = productsData.length > 0 ? Math.max(...productsData.map(c => c.id)) + 1 : 1;
        document.getElementById('prodProductId').value = `PRD-${String(nextId).padStart(3, '0')}`;
        document.getElementById('prodRev').value = 'A.0';
        document.getElementById('prodQtyPerUop').value = '50';
        document.getElementById('prodMoq').value = '100';
        document.getElementById('prodSpq').value = '50';
        document.getElementById('prodUnitCost').value = '0.00';
        document.getElementById('prodSellingPrice').value = '0.00';
        document.getElementById('prodDivisor').value = '1';

        // Reset child arrays
        modalProcesses = ['SMT Surface Mount', 'Reflow Soldering', 'Functional Testing'];
        modalSchedules = [
            { id: Date.now(), month: 'January', sched: 'Jan-31', status: 'Scheduled' }
        ];
        modalSupplies = [];

        // Reset offices table (select default first 3)
        initModalOffices(['Head Office (Mandaluyong)', 'Makati Sales Center', 'Laguna Main Plant Site']);

        setModalColor('#2563eb');
        renderModalProcesses();
        renderModalSchedules();
        renderModalSupplies();

        switchProductModalTab('basic');
        prodModalBackdrop.classList.add('show');
        document.getElementById('prodCode').focus();
        if (window.lucide) window.lucide.createIcons();
    }

    window.openEditProductModal = function (id) {
        closeAllActionMenus();
        const rec = productsData.find(r => r.id === id);
        if (!rec) return;

        productForm.reset();
        document.getElementById('prodRecordId').value = rec.id;
        modalTitle.textContent = `Edit Product: ${rec.product_code} — Rev ${rec.rev}`;
        btnSubmitLabel.textContent = 'Update Product';

        // Tab 1: Basic
        document.getElementById('prodProductId').value = rec.product_id;
        document.getElementById('prodCode').value = rec.product_code;
        document.getElementById('prodRev').value = rec.rev;
        document.getElementById('prodDescription').value = rec.description;
        document.getElementById('prodUop').value = rec.uop;
        document.getElementById('prodUop2').value = rec.uop2 || 'NONE';
        document.getElementById('prodQtyPerUop').value = rec.uop2_qty || '';
        document.getElementById('prodMoq').value = rec.moq;
        document.getElementById('prodSpq').value = rec.spq;
        document.getElementById('prodVat').value = rec.vat || 'Customer Setting';
        document.getElementById('prodCurrency').value = rec.currency || 'PHP';
        document.getElementById('prodUnitCost').value = parseFloat(rec.unit_cost || 0).toFixed(2);
        document.getElementById('prodSellingPrice').value = parseFloat(rec.selling_price || 0).toFixed(2);
        document.getElementById('prodModel').value = rec.model || '';
        document.getElementById('prodCategory').value = rec.category;
        document.getElementById('prodPlanType').value = rec.plan_type;
        document.getElementById('prodItemCode').value = rec.item_code || '';
        document.getElementById('prodPlatform').value = rec.platform || '';
        document.getElementById('prodColor').value = rec.color || '';
        document.getElementById('prodThickness').value = rec.thickness || '';
        document.getElementById('prodWeight').value = rec.weight || '';
        document.getElementById('prodDivisor').value = rec.divisor || 1;
        document.getElementById('prodHeader').value = rec.header || '';
        document.getElementById('prodRemarks').value = rec.remarks || '';

        // Offices
        initModalOffices(rec.offices || []);

        // Tag Color
        setModalColor(rec.tag_color || '#2563eb');

        // Processes
        modalProcesses = rec.processes ? [...rec.processes] : [];
        renderModalProcesses();

        // Tab 2: Customer
        document.getElementById('custTargetName').value = rec.customer_name || '';
        document.getElementById('custApprovalDate').value = rec.approval_date || '';
        document.getElementById('custEqlDate').value = rec.eql_date || '';
        document.getElementById('custEolDate').value = rec.eol_date || '';
        document.getElementById('custApprovalRemarks').value = rec.approval_remarks || '';
        document.getElementById('custEqlRemarks').value = rec.eql_remarks || '';

        // Tab 3: Sched
        document.getElementById('schedTypeSelect').value = rec.sched_type || 'Monthly';
        modalSchedules = rec.schedules ? JSON.parse(JSON.stringify(rec.schedules)) : [];
        renderModalSchedules();

        // Tab 4: Supplies
        modalSupplies = rec.supplies ? JSON.parse(JSON.stringify(rec.supplies)) : [];
        renderModalSupplies();

        switchProductModalTab('basic');
        prodModalBackdrop.classList.add('show');
        document.getElementById('prodCode').focus();
        if (window.lucide) window.lucide.createIcons();
    };

    window.closeProdModal = function () {
        if (prodModalBackdrop) prodModalBackdrop.classList.remove('show');
    };

    if (btnOpenCreateModal) btnOpenCreateModal.addEventListener('click', openCreateProductModal);
    if (btnModalCloseCross) btnModalCloseCross.addEventListener('click', closeProdModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeProdModal);

    // 11. FORM SUBMIT HANDLER

    window.handleProductFormSubmit = function (event) {
        event.preventDefault();

        const idVal = document.getElementById('prodRecordId').value;
        const productId = document.getElementById('prodProductId').value.trim();
        const code = document.getElementById('prodCode').value.trim().toUpperCase();
        const rev = document.getElementById('prodRev').value.trim().toUpperCase();
        const desc = document.getElementById('prodDescription').value.trim();
        const uop = document.getElementById('prodUop').value;
        const uop2 = document.getElementById('prodUop2').value;
        const uop2Qty = parseInt(document.getElementById('prodQtyPerUop').value, 10) || 0;
        const moq = parseInt(document.getElementById('prodMoq').value, 10) || 1;
        const spq = parseInt(document.getElementById('prodSpq').value, 10) || 1;
        const vat = document.getElementById('prodVat').value;
        const currency = document.getElementById('prodCurrency').value;
        const unitCost = parseFloat(document.getElementById('prodUnitCost').value) || 0;
        const sellingPrice = parseFloat(document.getElementById('prodSellingPrice').value) || 0;
        const model = document.getElementById('prodModel').value.trim();
        const category = document.getElementById('prodCategory').value;
        const planType = document.getElementById('prodPlanType').value;
        const itemCode = document.getElementById('prodItemCode').value.trim();
        const platform = document.getElementById('prodPlatform').value.trim();
        const color = document.getElementById('prodColor').value.trim();
        const thickness = document.getElementById('prodThickness').value.trim();
        const weight = document.getElementById('prodWeight').value.trim();
        const divisor = parseInt(document.getElementById('prodDivisor').value, 10) || 1;
        const header = document.getElementById('prodHeader').value.trim();
        const remarks = document.getElementById('prodRemarks').value.trim();

        // Selected Offices
        const offices = modalOffices.filter(o => o.selected).map(o => o.name);

        const tagColor = (document.getElementById('prodTagColor') && document.getElementById('prodTagColor').value) || '#2563eb';

        // Tab 2 Customer
        const customerName = document.getElementById('custTargetName').value.trim();
        const approvalDate = document.getElementById('custApprovalDate').value;
        const eqlDate = document.getElementById('custEqlDate').value;
        const eolDate = document.getElementById('custEolDate').value;
        const approvalRemarks = document.getElementById('custApprovalRemarks').value.trim();
        const eqlRemarks = document.getElementById('custEqlRemarks').value.trim();

        // Tab 3 Sched
        const schedType = document.getElementById('schedTypeSelect').value;

        if (!productId || !code || !desc || !customerName) {
            alert('Please fill in the required Product Code, Description, and Customer Name.');
            return;
        }

        if (idVal) {
            const index = productsData.findIndex(p => p.id === parseInt(idVal, 10));
            if (index !== -1) {
                productsData[index].product_id = productId;
                productsData[index].product_code = code;
                productsData[index].rev = rev;
                productsData[index].description = desc;
                productsData[index].uop = uop;
                productsData[index].uop2 = uop2;
                productsData[index].uop2_qty = uop2Qty;
                productsData[index].moq = moq;
                productsData[index].spq = spq;
                productsData[index].vat = vat;
                productsData[index].currency = currency;
                productsData[index].unit_cost = unitCost;
                productsData[index].selling_price = sellingPrice;
                productsData[index].processes = [...modalProcesses];
                productsData[index].model = model;
                productsData[index].category = category;
                productsData[index].plan_type = planType;
                productsData[index].item_code = itemCode;
                productsData[index].platform = platform;
                productsData[index].color = color;
                productsData[index].thickness = thickness;
                productsData[index].weight = weight;
                productsData[index].divisor = divisor;
                productsData[index].header = header;
                productsData[index].remarks = remarks;
                productsData[index].offices = offices;
                productsData[index].tag_color = tagColor;

                productsData[index].customer_name = customerName;
                productsData[index].approval_date = approvalDate;
                productsData[index].eql_date = eqlDate;
                productsData[index].eol_date = eolDate;
                productsData[index].approval_remarks = approvalRemarks;
                productsData[index].eql_remarks = eqlRemarks;

                productsData[index].sched_type = schedType;
                productsData[index].schedules = JSON.parse(JSON.stringify(modalSchedules));
                productsData[index].supplies = JSON.parse(JSON.stringify(modalSupplies));
            }
        } else {
            const nextId = productsData.length > 0 ? Math.max(...productsData.map(p => p.id)) + 1 : 1;
            const newProd = {
                id: nextId,
                product_id: productId,
                product_code: code,
                rev: rev,
                description: desc,
                uop: uop,
                uop2: uop2,
                uop2_qty: uop2Qty,
                moq: moq,
                spq: spq,
                vat: vat,
                currency: currency,
                unit_cost: unitCost,
                selling_price: sellingPrice,
                processes: [...modalProcesses],
                model: model,
                category: category,
                plan_type: planType,
                item_code: itemCode,
                platform: platform,
                color: color,
                thickness: thickness,
                weight: weight,
                divisor: divisor,
                header: header,
                remarks: remarks,
                offices: offices,
                tag_color: tagColor,
                customer_name: customerName,
                approval_date: approvalDate,
                eql_date: eqlDate,
                eol_date: eolDate,
                approval_remarks: approvalRemarks,
                eql_remarks: eqlRemarks,
                sched_type: schedType,
                schedules: JSON.parse(JSON.stringify(modalSchedules)),
                supplies: JSON.parse(JSON.stringify(modalSupplies)),
                deleted_date: '—'
            };
            productsData.unshift(newProd);
        }

        closeProdModal();
        if (prodSearchInput) prodSearchInput.value = '';
        renderProductTable(productsData);
    };

    // 12. VIEW PRODUCT MODAL (COMPLETE READ-ONLY PROFILE)

    window.openViewProductModal = function (id) {
        closeAllActionMenus();
        const rec = productsData.find(r => r.id === id);
        if (!rec) return;

        activeViewId = id;

        // Header Hero
        document.getElementById('viewTagColorIndicator').style.backgroundColor = rec.tag_color || '#2563eb';
        document.getElementById('viewProductDesc').textContent = rec.description;
        document.getElementById('viewCodeSub').textContent = `Code: ${rec.product_code} • Rev: ${rec.rev} • Platform: ${rec.platform || 'General'}`;

        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';
        const statusPill = document.getElementById('viewStatusPill');
        statusPill.className = `status-pill ${isDeleted ? 'status-pill-archived' : 'status-pill-active'}`;
        statusPill.textContent = isDeleted ? `Archived (${rec.deleted_date})` : 'Active Catalog';

        // Column 1 Basic & Engineering
        document.getElementById('viewProdId').textContent = rec.product_id;
        document.getElementById('viewCode').textContent = rec.product_code;
        document.getElementById('viewRev').textContent = rec.rev;
        document.getElementById('viewItemCode').textContent = rec.item_code || '—';
        document.getElementById('viewPlatform').textContent = rec.platform || '—';
        document.getElementById('viewPlanType').textContent = rec.plan_type;
        document.getElementById('viewUnits').textContent = `${rec.uop} ${rec.uop2 && rec.uop2 !== 'NONE' ? `(Secondary: ${rec.uop2} @ ${rec.uop2_qty} / ${rec.uop2})` : ''}`;
        document.getElementById('viewMoqSpq').textContent = `MOQ: ${rec.moq} • SPQ: ${rec.spq}`;
        document.getElementById('viewVatCurrency').textContent = `${rec.vat} • ${rec.currency}`;
        document.getElementById('viewPrices').textContent = `Cost: ${rec.currency} ${parseFloat(rec.unit_cost || 0).toFixed(2)} • Selling: ${rec.currency} ${parseFloat(rec.selling_price || 0).toFixed(2)}`;
        document.getElementById('viewPhysical').textContent = `Color: ${rec.color || '—'} • Thickness: ${rec.thickness || '—'}`;
        document.getElementById('viewWeightDivisor').textContent = `Weight: ${rec.weight || '—'} • Divisor: ${rec.divisor || 1}`;
        document.getElementById('viewProductHeader').textContent = rec.header || 'None';
        document.getElementById('viewRemarks').textContent = rec.remarks || 'None';

        // Offices list
        const officesList = document.getElementById('viewOfficesList');
        if (rec.offices && rec.offices.length > 0) {
            officesList.innerHTML = rec.offices.map(o => `<span class="view-tag-chip">${escapeHtml(o)}</span>`).join('');
        } else {
            officesList.innerHTML = '<span class="text-muted">No offices assigned</span>';
        }

        // Processes list
        const procList = document.getElementById('viewProcessStepsList');
        if (rec.processes && rec.processes.length > 0) {
            procList.innerHTML = rec.processes.map((p, i) => `
                <div class="view-proc-badge"><span class="badge-seq">${i + 1}</span> ${escapeHtml(p)}</div>
            `).join('');
        } else {
            procList.innerHTML = '<span class="text-muted">No processes specified</span>';
        }

        // Column 2 Customer, Sched, Supplies
        document.getElementById('viewCustomerName').textContent = rec.customer_name || '—';
        document.getElementById('viewApprovalDate').textContent = rec.approval_date || '—';
        document.getElementById('viewEqlDate').textContent = rec.eql_date || '—';
        document.getElementById('viewEolDate').textContent = rec.eol_date || '—';
        document.getElementById('viewApprovalRemarks').textContent = rec.approval_remarks || 'None';
        document.getElementById('viewEqlRemarks').textContent = rec.eql_remarks || 'None';

        // Sched Summary
        const schedSummary = document.getElementById('viewSchedSummary');
        if (rec.schedules && rec.schedules.length > 0) {
            schedSummary.innerHTML = `
                <div class="view-sched-type-badge">Type: ${rec.sched_type || 'Monthly'}</div>
                <div class="view-sched-items-grid">
                    ${rec.schedules.map(s => `
                        <div class="view-sched-card">
                            <strong>${escapeHtml(s.month)}</strong>
                            <span>Cutoff: ${escapeHtml(s.sched)}</span>
                        </div>
                    `).join('')}
                </div>
            `;
        } else {
            schedSummary.innerHTML = '<span class="text-muted">No inventory schedules configured</span>';
        }

        // Supplies Summary
        const suppliesSummary = document.getElementById('viewSuppliesSummary');
        if (rec.supplies && rec.supplies.length > 0) {
            suppliesSummary.innerHTML = `
                <table class="view-mini-table">
                    <thead>
                        <tr>
                            <th>Supply Component</th>
                            <th>Description</th>
                            <th class="text-right">Qty</th>
                            <th class="text-center">UOM</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rec.supplies.map(s => `
                            <tr>
                                <td><strong>${escapeHtml(s.name)}</strong></td>
                                <td class="text-muted">${escapeHtml(s.desc)}</td>
                                <td class="text-right font-numeric">${parseFloat(s.qty).toFixed(2)}</td>
                                <td class="text-center font-code">${escapeHtml(s.uom)}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            `;
        } else {
            suppliesSummary.innerHTML = '<span class="text-muted">No supplies linked</span>';
        }

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
                openEditProductModal(targetId);
            }
        });
    }

    // 13. DELETE MODAL (SOFT DELETE)

    window.openDeleteProductModal = function (id) {
        closeAllActionMenus();
        const rec = productsData.find(r => r.id === id);
        if (!rec) return;

        activeDeleteId = id;
        const today = new Date().toISOString().split('T')[0];

        deleteTargetCode.textContent = rec.product_code;
        deleteTargetDesc.textContent = rec.description;
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

            const rec = productsData.find(r => r.id === activeDeleteId);
            if (rec) {
                const today = new Date().toISOString().split('T')[0];
                rec.deleted_date = today;
            }

            closeDeleteModal();
            if (prodSearchInput) prodSearchInput.value = '';
            renderProductTable(productsData);
        });
    }

    // 14. REFRESH & EXPORT ACTIONS

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            closeAllActionMenus();
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            btnRefresh.disabled = true;

            setTimeout(() => {
                if (prodSearchInput) prodSearchInput.value = '';
                renderProductTable(productsData);
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

    window.exportProductDirectory = function (format) {
        exportMenu.classList.remove('show');
        if (format === 'pdf') {
            window.print();
        } else if (format === 'excel' || format === 'csv') {
            let csvContent = 'Product Code,Description,Rev,Item Code,Platform,Plan Type,UOP,UOP2,UOP2 Qty,Color,Thickness,MOQ,SPQ,Customer,Selling Price\n';
            productsData.forEach(r => {
                csvContent += `"${r.product_code}","${r.description}","${r.rev}","${r.item_code || ''}","${r.platform || ''}","${r.plan_type}","${r.uop}","${r.uop2 || ''}","${r.uop2_qty || ''}","${r.color || ''}","${r.thickness || ''}","${r.moq}","${r.spq}","${r.customer_name || ''}","${r.selling_price || 0}"\n`;
            });

            const blob = new Blob([csvContent], { type: format === 'excel' ? 'application/vnd.ms-excel;charset=utf-8;' : 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = `Product_Directory_${new Date().toISOString().split('T')[0]}.${format === 'excel' ? 'xls' : 'csv'}`;
            link.click();
        }
    };

    // Initial table rendering
    renderProductTable(productsData);
});
</script>
@endpush

@endsection

