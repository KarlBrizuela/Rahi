@extends('layouts.app')

@section('title', 'Material Requirement Plan (MRP) — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/MaterialRequirementPlan/materialrequirementplan.css') }}">
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
            <span class="bc-current">Material Requirement Plan</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="mrpSearchInput" placeholder="Search MRP#, Customer, Product, TFL..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Material Requirement Plan (MRP) Register</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Refresh MRP Plans">
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
                    <button type="button" class="export-option" onclick="exportMrpPlans('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable MRP production ledger</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportMrpPlans('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Production material requirements schedule</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportMrpPlans('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Comma-separated engineering planning data</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create MRP Button -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Material Plan</span>
            </button>
        </div>
    </section>

    <section class="filter-strip-bar no-print">
        <!-- Status Filter -->
        <div class="filter-item">
            <span class="filter-label">Status:</span>
            <select id="filterStatus" class="filter-select">
                <option value="ALL">All Statuses</option>
                <option value="Approved">Approved</option>
                <option value="In Production">In Production</option>
                <option value="Pending Review">Pending Review</option>
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

        <!-- Covered Requirement Date Filter -->
        <div class="filter-item">
            <span class="filter-label">Req. Date:</span>
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
            <span class="kpi-label">Total Material Plans</span>
            <span class="kpi-value" id="kpiTotalPlans">0</span>
            <span class="kpi-sub" id="kpiActivePlansSub">0 active records</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Total Production Units</span>
            <span class="kpi-value" id="kpiTotalQty" style="color: #2563eb;">0</span>
            <span class="kpi-sub">Aggregated order batch quantity</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Approved & Released</span>
            <span class="kpi-value" id="kpiApprovedCount" style="color: #10b981;">0</span>
            <span class="kpi-sub">Ready for factory extrusion</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Pending Engineering Review</span>
            <span class="kpi-value" id="kpiPendingCount" style="color: #f59e0b;">0</span>
            <span class="kpi-sub">Dimension validation required</span>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="erp-table-panel">
            <div class="table-panel-header no-print">
                <div class="panel-title-cluster">
                    <span class="panel-title">Production Bill of Materials & Specification Ledger</span>
                    <span class="count-pill" id="mrpCountBadge">0 Records</span>
                    <span class="selection-pill" id="bulkActionBar">
                        <i data-lucide="check-square" style="width: 13px; height: 13px;"></i>
                        <span id="selectedCountText">0 selected</span>
                        <button type="button" class="btn-bulk-delete" id="btnBulkDelete">Delete</button>
                    </span>
                </div>
            </div>

            <!-- Horizontal Scroll Table Container -->
            <div class="table-scroll-container">
                <table class="erp-table" id="mrpDataTable">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">
                                <input type="checkbox" id="selectAllCheckbox" class="erp-cb" title="Select All">
                            </th>
                            <th style="min-width: 140px;">MRP No</th>
                            <th style="min-width: 120px;">Requirement Date</th>
                            <th style="min-width: 180px;">Customer Name</th>
                            <th style="min-width: 200px;">Product Name</th>
                            <th style="min-width: 70px;" class="text-center">Rev</th>
                            <th style="min-width: 110px;">TFL Code</th>
                            <th style="min-width: 90px;" class="text-right">Qty</th>
                            <th style="min-width: 70px;" class="text-center">Pins</th>
                            <th style="min-width: 80px;" class="text-center">Reel</th>
                            <th style="min-width: 90px;" class="text-right">Pcs/Lot</th>
                            <th style="min-width: 90px;" class="text-right">Lot/Month</th>
                            <th style="min-width: 140px;">Conductor Type 1</th>
                            <th style="min-width: 80px;" class="text-center">X (Pitch/Dim)</th>
                            <th style="min-width: 140px;">Conductor Type 2</th>
                            <th style="min-width: 90px;" class="text-right">Bobbin Qty</th>
                            <th style="min-width: 160px;">Insulation Type Desc</th>
                            <th style="min-width: 95px;" class="text-right">Width Old</th>
                            <th style="min-width: 95px;" class="text-right">Width New</th>
                            <th style="min-width: 120px;" class="text-center">Status</th>
                            <th style="min-width: 110px;" class="text-center">Deleted Date</th>
                            <th style="width: 70px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="mrpTableBody">
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

    <div class="modal-backdrop" id="mrpModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-xlarge">
            <!-- Modal Header -->
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="mrpModalTitle">Create Material Requirement Plan</h2>
                    <span class="modal-subtitle">Configure engineering bill of materials, conductor gauges, bobbin lot parameters, and insulation profiling.</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsMrpModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form id="mrpForm" autocomplete="off">
                <input type="hidden" id="formMrpId" value="">

                <div class="modal-body">
                    <!-- SECTION 1: REQUIREMENT IDENTIFIERS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">1</span>
                                <span class="section-label">Requirement Header & Identifiers</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formMrpNo">MRP No <span class="required">*</span></label>
                                <input type="text" id="formMrpNo" class="form-input" placeholder="e.g. MRP-2026-0091" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formReqDate">Requirement Date <span class="required">*</span></label>
                                <input type="date" id="formReqDate" class="form-input" required>
                            </div>

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
                                <label for="formStatus">Status <span class="required">*</span></label>
                                <select id="formStatus" class="form-select" required>
                                    <option value="Approved">Approved</option>
                                    <option value="In Production">In Production</option>
                                    <option value="Pending Review">Pending Review</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-2">
                                <label for="formCustomerName">Customer Name <span class="required">*</span></label>
                                <input type="text" id="formCustomerName" class="form-input" placeholder="e.g. Sumitomo Wiring Systems Corp" required>
                            </div>

                            <div class="form-group flex-2">
                                <label for="formProductName">Product Name <span class="required">*</span></label>
                                <input type="text" id="formProductName" class="form-input" placeholder="e.g. FFC Flat Flexible Ribbon Cable 1.0mm Pitch" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formRev">Rev (Revision) <span class="required">*</span></label>
                                <input type="text" id="formRev" class="form-input" placeholder="e.g. Rev C" value="Rev A" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formTflCode">TFL Code <span class="required">*</span></label>
                                <input type="text" id="formTflCode" class="form-input" placeholder="e.g. TFL-8820-PH" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: PRODUCTION VOLUMES & LOT SIZING -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">2</span>
                                <span class="section-label">Production Volumes & Packaging Lot Sizing</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formQty">Required Qty <span class="required">*</span></label>
                                <input type="number" id="formQty" class="form-input" placeholder="e.g. 50000" min="1" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formPins">Pins <span class="required">*</span></label>
                                <input type="number" id="formPins" class="form-input" placeholder="e.g. 16" min="1" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formReel">Reel (Qty / Type)</label>
                                <input type="text" id="formReel" class="form-input" placeholder="e.g. 25 Reels (Type-R2)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formPcsLot">Pcs / Lot <span class="required">*</span></label>
                                <input type="number" id="formPcsLot" class="form-input" placeholder="e.g. 2000" min="1" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formLotMonth">Lot / Month</label>
                                <input type="number" id="formLotMonth" class="form-input" placeholder="e.g. 25" min="1">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formBobbinQty">Bobbin Qty <span class="required">*</span></label>
                                <input type="number" id="formBobbinQty" class="form-input" placeholder="e.g. 50" min="1" required>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: CONDUCTOR SPECIFICATIONS & DIMENSIONS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">3</span>
                                <span class="section-label">Conductor Types & Wire Geometry</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-2">
                                <label for="formConductorType1">Conductor Type 1 <span class="required">*</span></label>
                                <input type="text" id="formConductorType1" class="form-input" placeholder="e.g. Tinned Copper Ribbon 0.1 x 0.8mm" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formX">X (Pitch / Dimension) <span class="required">*</span></label>
                                <input type="text" id="formX" class="form-input" placeholder="e.g. 1.00 mm Pitch" required>
                            </div>

                            <div class="form-group flex-2">
                                <label for="formConductorType2">Conductor Type 2</label>
                                <input type="text" id="formConductorType2" class="form-input" placeholder="e.g. Annealed Bare Copper 0.05 x 0.65mm (Secondary)">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formTotalStripLength">Total Strip Length</label>
                                <input type="text" id="formTotalStripLength" class="form-input" placeholder="e.g. 120 mm">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: INSULATION & TAPE WIDTH PROFILING -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">4</span>
                                <span class="section-label">Insulation & Tape Width Profiling</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-2">
                                <label for="formInsulationTypeDesc">Insulation Type Desc <span class="required">*</span></label>
                                <input type="text" id="formInsulationTypeDesc" class="form-input" placeholder="e.g. White Polyester (PET) 0.043mm + Flame Retardant Adhesive" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formWidthOld">Width Old (mm) <span class="required">*</span></label>
                                <input type="number" step="0.001" id="formWidthOld" class="form-input" placeholder="e.g. 17.000" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formWidthNew">Width New (mm) <span class="required">*</span></label>
                                <input type="number" step="0.001" id="formWidthNew" class="form-input" placeholder="e.g. 17.500" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formMarginOld">Margin Old (mm)</label>
                                <input type="number" step="0.001" id="formMarginOld" class="form-input" placeholder="e.g. 0.500">
                            </div>

                            <div class="form-group flex-1">
                                <label for="formMarginNew">Margin New (mm)</label>
                                <input type="number" step="0.001" id="formMarginNew" class="form-input" placeholder="e.g. 0.750">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: ENGINEERING SIGN-OFFS & REMARKS -->
                    <div class="form-section-card">
                        <div class="section-badge-title">
                            <div class="section-title-left">
                                <span class="badge-num">5</span>
                                <span class="section-label">Engineering Sign-offs & Production Remarks</span>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group flex-1">
                                <label for="formPreparedBy">Prepared By <span class="required">*</span></label>
                                <input type="text" id="formPreparedBy" class="form-input" value="Karl Burgos (Production Eng.)" required>
                            </div>

                            <div class="form-group flex-1">
                                <label for="formApprovedBy">Approved By</label>
                                <input type="text" id="formApprovedBy" class="form-input" placeholder="e.g. Engr. Victor Ramos (Technical Lead)">
                            </div>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="formRemarks">Production & Quality Remarks</label>
                            <textarea id="formRemarks" class="form-textarea" rows="2" placeholder="Special engineering instructions, tolerance limits, test jig requirements, or extrusion reel notes..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer">
                    <button type="button" class="btn-outline" id="btnCancelMrpModal">Cancel</button>
                    <button type="submit" class="btn-primary" id="btnSaveMrp">
                        <i data-lucide="check" class="btn-icon"></i>
                        <span id="btnSaveMrpText">Save Material Plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-backdrop" id="mrpViewModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-large">
            <div class="modal-header">
                <div class="modal-title-cluster">
                    <h2 class="modal-title" id="viewMrpTitle">Material Requirement Dossier</h2>
                    <span class="modal-subtitle" id="viewMrpSubtitle">Engineering Bill of Materials & Technical Profile</span>
                </div>
                <button type="button" class="modal-close-btn" id="btnClsMrpViewModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="view-dossier-grid">
                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">General Specification</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">MRP No:</span>
                                <span class="dossier-val bold-text" id="vMrpNo">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Requirement Date:</span>
                                <span class="dossier-val" id="vReqDate">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Branch:</span>
                                <span class="dossier-val" id="vBranch">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Customer Name:</span>
                                <span class="dossier-val bold-text" id="vCustomerName">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Product Name:</span>
                                <span class="dossier-val" id="vProductName">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Revision & TFL:</span>
                                <span class="dossier-val" id="vRevTfl">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Status:</span>
                                <span class="dossier-val" id="vStatus">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Lot & Bobbin Sizing</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Required Qty:</span>
                                <span class="dossier-val bold-text" style="color: #2563eb;" id="vQty">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Pins:</span>
                                <span class="dossier-val" id="vPins">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Reel:</span>
                                <span class="dossier-val" id="vReel">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Pcs / Lot:</span>
                                <span class="dossier-val" id="vPcsLot">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Lot / Month:</span>
                                <span class="dossier-val" id="vLotMonth">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Bobbin Qty:</span>
                                <span class="dossier-val" id="vBobbinQty">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technical Dimensions Grid -->
                <div class="view-dossier-grid" style="margin-top: 14px;">
                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Conductor & Geometry</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Conductor Type 1:</span>
                                <span class="dossier-val" id="vConductor1">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">X (Pitch/Dim):</span>
                                <span class="dossier-val font-code font-bold" id="vX">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Conductor Type 2:</span>
                                <span class="dossier-val" id="vConductor2">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Strip Length:</span>
                                <span class="dossier-val" id="vStripLength">-</span>
                            </div>
                        </div>
                    </div>

                    <div class="dossier-col">
                        <div class="dossier-card">
                            <h4 class="dossier-card-title">Insulation & Tape Profiling</h4>
                            <div class="dossier-row">
                                <span class="dossier-label">Insulation Type:</span>
                                <span class="dossier-val" id="vInsulation">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Width Old:</span>
                                <span class="dossier-val font-numeric" id="vWidthOld">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Width New:</span>
                                <span class="dossier-val font-numeric font-bold" id="vWidthNew">-</span>
                            </div>
                            <div class="dossier-row">
                                <span class="dossier-label">Margin (Old / New):</span>
                                <span class="dossier-val font-numeric" id="vMargin">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- View Remarks -->
                <div class="dossier-card" style="margin-top: 14px;">
                    <h4 class="dossier-card-title">Engineering Notes & Sign-offs</h4>
                    <p id="vRemarks" style="font-size: 12.5px; color: #334155; margin: 0; line-height: 1.5;">-</p>
                    <div style="display: flex; gap: 24px; margin-top: 10px; font-size: 11.5px; color: #64748b;">
                        <span>Prepared By: <strong id="vPreparedBy" style="color: #0f172a;">-</strong></span>
                        <span>Approved By: <strong id="vApprovedBy" style="color: #0f172a;">-</strong></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" onclick="printCurrentMrp()">
                    <i data-lucide="printer" class="btn-icon"></i>
                    <span>Print Specsheet</span>
                </button>
                <button type="button" class="btn-primary" id="btnClsMrpViewModal2">Close</button>
            </div>
        </div>
    </div>

    <div class="modal-backdrop" id="mrpDeleteModal" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="modal-dialog modal-dialog-small">
            <div class="modal-header">
                <h2 class="modal-title" style="color: #dc2626;">Confirm Deletion</h2>
                <button type="button" class="modal-close-btn" id="btnClsMrpDeleteModal" title="Close dialog">
                    <i data-lucide="x" style="width: 18px; height: 18px;"></i>
                </button>
            </div>
            <div class="modal-body">
                <p style="font-size: 13px; color: #334155; line-height: 1.5;">
                    Are you sure you want to delete material requirement plan <strong id="deleteMrpNumPrompt"></strong>?
                </p>
                <p style="font-size: 11.5px; color: #64748b; margin-top: 8px;">
                    This plan will be archived with an audit timestamp in the system ledger.
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

    // State: Material Requirement Plans
    let mrpPlans = [
        {
            id: 'MRP-001',
            mrpNo: 'MRP-2026-0041',
            reqDate: '2026-11-04',
            branch: 'Head Office (Mandaluyong)',
            customerName: 'Sumitomo Wiring Systems Corp',
            productName: 'FFC Flexible Ribbon Cable 1.0mm Pitch',
            rev: 'Rev C',
            tflCode: 'TFL-8820-PH',
            qty: 50000,
            pins: 16,
            reel: '25 Reels (Type-R2)',
            pcsLot: 2000,
            lotMonth: 25,
            conductor1: 'Tinned Copper Ribbon 0.1 x 0.8mm',
            x: '1.00 mm',
            conductor2: 'Bare Copper Ground 0.05 x 0.65mm',
            bobbinQty: 50,
            insulationDesc: 'White PET 0.043mm + Flame Retardant Adhesive',
            widthOld: 17.000,
            widthNew: 17.500,
            marginOld: 0.500,
            marginNew: 0.750,
            stripLength: '120 mm',
            status: 'Approved',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Production Eng.)',
            approvedBy: 'Engr. Victor Ramos (Technical Lead)',
            remarks: 'High temperature resistance grade (105°C UL 20624). Certified QA passed.'
        },
        {
            id: 'MRP-002',
            mrpNo: 'MRP-2026-0042',
            reqDate: '2026-11-05',
            branch: 'Makati Sales Hub',
            customerName: 'Yazaki-Torres Mfg. Inc.',
            productName: 'Shielded Harness Wire Assembly 0.5mm',
            rev: 'Rev B',
            tflCode: 'TFL-5512-YZ',
            qty: 35000,
            pins: 24,
            reel: '15 Reels (Type-R3)',
            pcsLot: 2500,
            lotMonth: 14,
            conductor1: 'Nickel-Plated Copper 0.08 x 0.6mm',
            x: '0.50 mm',
            conductor2: 'Aluminum Shielding Foil Laminate',
            bobbinQty: 30,
            insulationDesc: 'Black Polyimide (Kapton) 0.025mm Film',
            widthOld: 12.800,
            widthNew: 13.000,
            marginOld: 0.400,
            marginNew: 0.500,
            stripLength: '85 mm',
            status: 'In Production',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Production Eng.)',
            approvedBy: 'Engr. Victor Ramos (Technical Lead)',
            remarks: 'Automotive dashboard cluster interconnect spec.'
        },
        {
            id: 'MRP-003',
            mrpNo: 'MRP-2026-0043',
            reqDate: '2026-11-06',
            branch: 'Laguna Plant Depot',
            customerName: 'Furukawa Electric Philippines',
            productName: 'Fine Pitch Jumper Lead Assembly',
            rev: 'Rev A',
            tflCode: 'TFL-9910-FK',
            qty: 20000,
            pins: 32,
            reel: '10 Reels (Type-R1)',
            pcsLot: 2000,
            lotMonth: 10,
            conductor1: 'Silver-Plated Copper 0.05 x 0.35mm',
            x: '0.30 mm',
            conductor2: 'None',
            bobbinQty: 20,
            insulationDesc: 'Clear Polyester Film 0.035mm Grade A',
            widthOld: 10.200,
            widthNew: 10.500,
            marginOld: 0.300,
            marginNew: 0.450,
            stripLength: '60 mm',
            status: 'Pending Review',
            deletedDate: '-',
            preparedBy: 'Karl Burgos (Production Eng.)',
            approvedBy: 'Pending Review',
            remarks: 'Awaiting customer confirmation on dielectric test clearance.'
        },
        {
            id: 'MRP-004',
            mrpNo: 'MRP-2026-0044',
            reqDate: '2026-11-02',
            branch: 'Cebu Logistics Depot',
            customerName: 'Continental Temic Electronics',
            productName: 'Ultra-Flex Flat Cable 1.25mm Pitch',
            rev: 'Rev 01',
            tflCode: 'TFL-3301-CT',
            qty: 15000,
            pins: 12,
            reel: '8 Reels (Type-R2)',
            pcsLot: 1500,
            lotMonth: 10,
            conductor1: 'Tinned Copper Flat Wire 0.12 x 0.9mm',
            x: '1.25 mm',
            conductor2: 'None',
            bobbinQty: 16,
            insulationDesc: 'Reinforced PVC Adhesive Backed 0.050mm',
            widthOld: 16.500,
            widthNew: 16.500,
            marginOld: 0.600,
            marginNew: 0.600,
            stripLength: '150 mm',
            status: 'Cancelled',
            deletedDate: '-',
            preparedBy: 'Jonathan Reyes',
            approvedBy: 'Engr. Victor Ramos',
            remarks: 'Discontinued due to revised engineering change order ECO-9941.'
        }
    ];

    let selectedMrpIds = new Set();
    let currentDeleteId = null;

    // DOM Elements
    const mrpTableBody = document.getElementById('mrpTableBody');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    const selectedCountText = document.getElementById('selectedCountText');
    const btnBulkDelete = document.getElementById('btnBulkDelete');

    // Filters
    const mrpSearchInput = document.getElementById('mrpSearchInput');
    const filterStatus = document.getElementById('filterStatus');
    const filterBranch = document.getElementById('filterBranch');
    const filterDateFrom = document.getElementById('filterDateFrom');
    const filterDateTo = document.getElementById('filterDateTo');
    const btnResetFilters = document.getElementById('btnResetFilters');

    // KPI Elements
    const kpiTotalPlans = document.getElementById('kpiTotalPlans');
    const kpiActivePlansSub = document.getElementById('kpiActivePlansSub');
    const kpiTotalQty = document.getElementById('kpiTotalQty');
    const kpiApprovedCount = document.getElementById('kpiApprovedCount');
    const kpiPendingCount = document.getElementById('kpiPendingCount');
    const mrpCountBadge = document.getElementById('mrpCountBadge');
    const paginationMeta = document.getElementById('paginationMeta');

    // Modals
    const mrpModal = document.getElementById('mrpModal');
    const mrpForm = document.getElementById('mrpForm');
    const mrpModalTitle = document.getElementById('mrpModalTitle');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnClsMrpModal = document.getElementById('btnClsMrpModal');
    const btnCancelMrpModal = document.getElementById('btnCancelMrpModal');

    // View Modal
    const mrpViewModal = document.getElementById('mrpViewModal');
    const btnClsMrpViewModal = document.getElementById('btnClsMrpViewModal');
    const btnClsMrpViewModal2 = document.getElementById('btnClsMrpViewModal2');

    // Delete Modal
    const mrpDeleteModal = document.getElementById('mrpDeleteModal');
    const deleteMrpNumPrompt = document.getElementById('deleteMrpNumPrompt');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const btnClsMrpDeleteModal = document.getElementById('btnClsMrpDeleteModal');

    // Export & Refresh
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportMenu = document.getElementById('exportMenu');
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');

    // Initialize Default Dates
    const todayStr = new Date().toISOString().split('T')[0];
    document.getElementById('formReqDate').value = todayStr;

    // Render Table Function
    function renderTable() {
        const query = (mrpSearchInput.value || '').trim().toLowerCase();
        const statusFilter = filterStatus.value;
        const branchFilter = filterBranch.value;
        const dateFrom = filterDateFrom.value;
        const dateTo = filterDateTo.value;

        const filtered = mrpPlans.filter(item => {
            if (query) {
                const matchQuery =
                    item.mrpNo.toLowerCase().includes(query) ||
                    item.customerName.toLowerCase().includes(query) ||
                    item.productName.toLowerCase().includes(query) ||
                    item.tflCode.toLowerCase().includes(query) ||
                    item.branch.toLowerCase().includes(query);
                if (!matchQuery) return false;
            }

            if (statusFilter !== 'ALL' && item.status !== statusFilter) {
                return false;
            }

            if (branchFilter !== 'ALL' && item.branch !== branchFilter) {
                return false;
            }

            if (dateFrom && item.reqDate < dateFrom) {
                return false;
            }

            if (dateTo && item.reqDate > dateTo) {
                return false;
            }

            return true;
        });

        updateKPIs(mrpPlans);

        mrpTableBody.innerHTML = '';

        if (filtered.length === 0) {
            mrpTableBody.innerHTML = `
                <tr>
                    <td colspan="22" style="text-align: center; padding: 36px; color: #64748b;">
                        <i data-lucide="inbox" style="width: 28px; height: 28px; margin-bottom: 6px; stroke-width: 1.5; color: #94a3b8;"></i>
                        <p style="font-size: 13px; font-weight: 500;">No material requirement plans found matching your criteria.</p>
                        <span style="font-size: 11.5px; color: #94a3b8;">Try clearing filters or search terms.</span>
                    </td>
                </tr>
            `;
            if (typeof lucide !== 'undefined') lucide.createIcons();
            mrpCountBadge.textContent = '0 Records';
            paginationMeta.textContent = 'Showing 0 of 0 entries';
            return;
        }

        filtered.forEach(plan => {
            const tr = document.createElement('tr');
            if (plan.deletedDate !== '-') {
                tr.classList.add('row-deleted');
            }

            const isChecked = selectedMrpIds.has(plan.id);
            if (isChecked) {
                tr.classList.add('row-selected');
            }

            let statusBadgeClass = 'status-pill-pending';
            if (plan.status === 'Approved') statusBadgeClass = 'status-pill-approved';
            else if (plan.status === 'In Production') statusBadgeClass = 'status-pill-production';
            else if (plan.status === 'Cancelled') statusBadgeClass = 'status-pill-cancelled';

            tr.innerHTML = `
                <td style="width: 40px; text-align: center;" class="no-print">
                    <input type="checkbox" class="erp-cb row-checkbox" data-id="${plan.id}" ${isChecked ? 'checked' : ''}>
                </td>
                <td class="font-bold font-code" style="min-width: 140px;">
                    <a href="javascript:void(0)" onclick="viewMrpPlan('${plan.id}')" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                        ${escapeHtml(plan.mrpNo)}
                    </a>
                </td>
                <td class="font-numeric" style="min-width: 120px;">${escapeHtml(plan.reqDate)}</td>
                <td style="min-width: 180px; font-weight: 500;">${escapeHtml(plan.customerName)}</td>
                <td style="min-width: 200px;">${escapeHtml(plan.productName)}</td>
                <td class="text-center font-code" style="min-width: 70px;">${escapeHtml(plan.rev)}</td>
                <td class="font-code font-bold" style="min-width: 110px; color: #475569;">${escapeHtml(plan.tflCode)}</td>
                <td class="text-right font-numeric font-bold" style="min-width: 90px; color: #0f172a;">${(Number(plan.qty) || 0).toLocaleString()}</td>
                <td class="text-center font-numeric" style="min-width: 70px;">${plan.pins}</td>
                <td class="text-center font-code" style="min-width: 80px;">${escapeHtml(plan.reel)}</td>
                <td class="text-right font-numeric" style="min-width: 90px;">${(Number(plan.pcsLot) || 0).toLocaleString()}</td>
                <td class="text-right font-numeric" style="min-width: 90px;">${plan.lotMonth || '-'}</td>
                <td style="min-width: 140px;">${escapeHtml(plan.conductor1)}</td>
                <td class="text-center font-code font-bold" style="min-width: 80px;">${escapeHtml(plan.x)}</td>
                <td style="min-width: 140px;">${escapeHtml(plan.conductor2 || '-')}</td>
                <td class="text-right font-numeric" style="min-width: 90px;">${plan.bobbinQty}</td>
                <td style="min-width: 160px;">${escapeHtml(plan.insulationDesc)}</td>
                <td class="text-right font-numeric font-code" style="min-width: 95px;">${Number(plan.widthOld).toFixed(3)}</td>
                <td class="text-right font-numeric font-code font-bold" style="min-width: 95px; color: #2563eb;">${Number(plan.widthNew).toFixed(3)}</td>
                <td class="text-center" style="min-width: 120px;">
                    <span class="status-pill ${statusBadgeClass}">
                        ${escapeHtml(plan.status)}
                    </span>
                </td>
                <td class="text-center font-numeric text-muted" style="min-width: 110px; font-size: 11.5px;">
                    ${escapeHtml(plan.deletedDate)}
                </td>
                <td class="actions-cell text-center no-print" style="width: 70px;">
                    <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '${plan.id}')" title="Actions">
                        <i data-lucide="more-horizontal" style="width: 15px; height: 15px;"></i>
                    </button>
                    <div class="action-menu-dropdown" id="actionMenu-${plan.id}">
                        <button type="button" class="action-menu-item" onclick="viewMrpPlan('${plan.id}')">
                            <i data-lucide="eye"></i>
                            <span>View Dossier</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editMrpPlan('${plan.id}')">
                            <i data-lucide="edit"></i>
                            <span>Edit Plan</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="promptDeleteMrp('${plan.id}')">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </td>
            `;

            mrpTableBody.appendChild(tr);
        });

        mrpCountBadge.textContent = `${filtered.length} Records`;
        paginationMeta.textContent = `Showing 1 to ${filtered.length} of ${filtered.length} entries`;

        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        updateSelectAllState(filtered);
    }

    function updateKPIs(data) {
        const active = data.filter(d => d.deletedDate === '-');
        kpiTotalPlans.textContent = data.length;
        kpiActivePlansSub.textContent = `${active.length} active records`;

        let totalQty = 0;
        let approved = 0;
        let pending = 0;

        active.forEach(item => {
            totalQty += Number(item.qty) || 0;
            if (item.status === 'Approved') approved++;
            else if (item.status === 'Pending Review') pending++;
        });

        kpiTotalQty.textContent = totalQty.toLocaleString();
        kpiApprovedCount.textContent = approved;
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
    mrpTableBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('row-checkbox')) {
            const id = e.target.getAttribute('data-id');
            if (e.target.checked) {
                selectedMrpIds.add(id);
            } else {
                selectedMrpIds.delete(id);
            }
            updateBulkActionBar();
        }
    });

    selectAllCheckbox.addEventListener('change', function () {
        const checkboxes = mrpTableBody.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = selectAllCheckbox.checked;
            const id = cb.getAttribute('data-id');
            if (selectAllCheckbox.checked) {
                selectedMrpIds.add(id);
            } else {
                selectedMrpIds.delete(id);
            }
        });
        updateBulkActionBar();
    });

    function updateSelectAllState(currentFilteredList) {
        const allVisibleSelected = currentFilteredList.length > 0 &&
            currentFilteredList.every(item => selectedMrpIds.has(item.id));
        selectAllCheckbox.checked = allVisibleSelected;
        updateBulkActionBar();
    }

    function updateBulkActionBar() {
        if (selectedMrpIds.size > 0) {
            bulkActionBar.classList.add('show');
            selectedCountText.textContent = `${selectedMrpIds.size} selected`;
        } else {
            bulkActionBar.classList.remove('show');
        }
    }

    btnBulkDelete.addEventListener('click', function () {
        if (selectedMrpIds.size === 0) return;
        if (confirm(`Are you sure you want to delete ${selectedMrpIds.size} selected material requirement plan(s)?`)) {
            const nowTime = new Date().toLocaleString();
            mrpPlans.forEach(item => {
                if (selectedMrpIds.has(item.id)) {
                    item.deletedDate = nowTime;
                }
            });
            selectedMrpIds.clear();
            renderTable();
        }
    });

    // Filters Listeners
    [mrpSearchInput, filterStatus, filterBranch, filterDateFrom, filterDateTo].forEach(el => {
        el.addEventListener('input', renderTable);
        el.addEventListener('change', renderTable);
    });

    btnResetFilters.addEventListener('click', function () {
        mrpSearchInput.value = '';
        filterStatus.value = 'ALL';
        filterBranch.value = 'ALL';
        filterDateFrom.value = '';
        filterDateTo.value = '';
        selectedMrpIds.clear();
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

    window.exportMrpPlans = function (type) {
        exportMenu.classList.remove('show');
        if (type === 'pdf') {
            window.print();
        } else if (type === 'csv' || type === 'excel') {
            downloadCSV();
        }
    };

    function downloadCSV() {
        let csv = 'MRP No,Requirement Date,Customer Name,Product Name,Rev,TFL Code,Qty,Pins,Reel,Pcs/Lot,Lot/Month,Conductor 1,X,Conductor 2,Bobbin Qty,Insulation Desc,Width Old,Width New,Status,Deleted Date\n';
        mrpPlans.forEach(item => {
            csv += `"${item.mrpNo}","${item.reqDate}","${item.customerName}","${item.productName}","${item.rev}","${item.tflCode}",${item.qty},${item.pins},"${item.reel}",${item.pcsLot},${item.lotMonth || 0},"${item.conductor1}","${item.x}","${item.conductor2 || ''}",${item.bobbinQty},"${item.insulationDesc}",${item.widthOld},${item.widthNew},"${item.status}","${item.deletedDate}"\n`;
        });
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Material_Requirement_Plans_${todayStr}.csv`;
        a.click();
        URL.revokeObjectURL(url);
    }

    // Modal Create / Edit
    btnOpenCreateModal.addEventListener('click', function () {
        mrpForm.reset();
        document.getElementById('formMrpId').value = '';
        mrpModalTitle.textContent = 'Create Material Requirement Plan';
        document.getElementById('btnSaveMrpText').textContent = 'Save Material Plan';
        document.getElementById('formReqDate').value = todayStr;
        document.getElementById('formPreparedBy').value = 'Karl Burgos (Production Eng.)';
        document.getElementById('formRev').value = 'Rev A';

        mrpModal.classList.add('show');
        mrpModal.setAttribute('aria-hidden', 'false');
    });

    function closeMrpModal() {
        mrpModal.classList.remove('show');
        mrpModal.setAttribute('aria-hidden', 'true');
    }
    btnClsMrpModal.addEventListener('click', closeMrpModal);
    btnCancelMrpModal.addEventListener('click', closeMrpModal);

    // Form Submit
    mrpForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const editId = document.getElementById('formMrpId').value;
        const mrpNo = document.getElementById('formMrpNo').value.trim();
        const reqDate = document.getElementById('formReqDate').value;
        const branch = document.getElementById('formBranch').value;
        const status = document.getElementById('formStatus').value;
        const customerName = document.getElementById('formCustomerName').value.trim();
        const productName = document.getElementById('formProductName').value.trim();
        const rev = document.getElementById('formRev').value.trim();
        const tflCode = document.getElementById('formTflCode').value.trim();
        const qty = Number(document.getElementById('formQty').value) || 0;
        const pins = Number(document.getElementById('formPins').value) || 0;
        const reel = document.getElementById('formReel').value.trim();
        const pcsLot = Number(document.getElementById('formPcsLot').value) || 0;
        const lotMonth = Number(document.getElementById('formLotMonth').value) || 0;
        const bobbinQty = Number(document.getElementById('formBobbinQty').value) || 0;
        const conductor1 = document.getElementById('formConductorType1').value.trim();
        const x = document.getElementById('formX').value.trim();
        const conductor2 = document.getElementById('formConductorType2').value.trim();
        const stripLength = document.getElementById('formTotalStripLength').value.trim();
        const insulationDesc = document.getElementById('formInsulationTypeDesc').value.trim();
        const widthOld = Number(document.getElementById('formWidthOld').value) || 0;
        const widthNew = Number(document.getElementById('formWidthNew').value) || 0;
        const marginOld = Number(document.getElementById('formMarginOld').value) || 0;
        const marginNew = Number(document.getElementById('formMarginNew').value) || 0;
        const preparedBy = document.getElementById('formPreparedBy').value.trim();
        const approvedBy = document.getElementById('formApprovedBy').value.trim();
        const remarks = document.getElementById('formRemarks').value.trim();

        if (editId) {
            const idx = mrpPlans.findIndex(m => m.id === editId);
            if (idx !== -1) {
                mrpPlans[idx] = {
                    ...mrpPlans[idx],
                    mrpNo,
                    reqDate,
                    branch,
                    status,
                    customerName,
                    productName,
                    rev,
                    tflCode,
                    qty,
                    pins,
                    reel,
                    pcsLot,
                    lotMonth,
                    bobbinQty,
                    conductor1,
                    x,
                    conductor2,
                    stripLength,
                    insulationDesc,
                    widthOld,
                    widthNew,
                    marginOld,
                    marginNew,
                    preparedBy,
                    approvedBy,
                    remarks
                };
            }
        } else {
            const newId = 'MRP-' + Date.now().toString().slice(-4);
            mrpPlans.unshift({
                id: newId,
                mrpNo,
                reqDate,
                branch,
                status,
                customerName,
                productName,
                rev,
                tflCode,
                qty,
                pins,
                reel,
                pcsLot,
                lotMonth,
                bobbinQty,
                conductor1,
                x,
                conductor2,
                stripLength,
                insulationDesc,
                widthOld,
                widthNew,
                marginOld,
                marginNew,
                preparedBy,
                approvedBy,
                remarks,
                deletedDate: '-'
            });
        }

        closeMrpModal();
        renderTable();
    });

    // View Dossier
    window.viewMrpPlan = function (id) {
        const item = mrpPlans.find(m => m.id === id);
        if (!item) return;

        document.getElementById('viewMrpTitle').textContent = `MRP Dossier: ${item.mrpNo}`;
        document.getElementById('viewMrpSubtitle').textContent = `Manufacturing Specifications — ${item.customerName}`;

        document.getElementById('vMrpNo').textContent = item.mrpNo;
        document.getElementById('vReqDate').textContent = item.reqDate;
        document.getElementById('vBranch').textContent = item.branch;
        document.getElementById('vCustomerName').textContent = item.customerName;
        document.getElementById('vProductName').textContent = item.productName;
        document.getElementById('vRevTfl').textContent = `${item.rev} / ${item.tflCode}`;
        document.getElementById('vStatus').textContent = item.status;

        document.getElementById('vQty').textContent = (Number(item.qty) || 0).toLocaleString() + ' Units';
        document.getElementById('vPins').textContent = item.pins + ' Pins';
        document.getElementById('vReel').textContent = item.reel || 'Standard';
        document.getElementById('vPcsLot').textContent = (Number(item.pcsLot) || 0).toLocaleString() + ' pcs';
        document.getElementById('vLotMonth').textContent = (item.lotMonth || 0) + ' Lots/Mo';
        document.getElementById('vBobbinQty').textContent = item.bobbinQty + ' Bobbins';

        document.getElementById('vConductor1').textContent = item.conductor1;
        document.getElementById('vX').textContent = item.x;
        document.getElementById('vConductor2').textContent = item.conductor2 || 'N/A';
        document.getElementById('vStripLength').textContent = item.stripLength || 'Standard';

        document.getElementById('vInsulation').textContent = item.insulationDesc;
        document.getElementById('vWidthOld').textContent = Number(item.widthOld).toFixed(3) + ' mm';
        document.getElementById('vWidthNew').textContent = Number(item.widthNew).toFixed(3) + ' mm';
        document.getElementById('vMargin').textContent = `${Number(item.marginOld || 0).toFixed(3)} mm / ${Number(item.marginNew || 0).toFixed(3)} mm`;

        document.getElementById('vRemarks').textContent = item.remarks || 'No special engineering notes provided.';
        document.getElementById('vPreparedBy').textContent = item.preparedBy;
        document.getElementById('vApprovedBy').textContent = item.approvedBy || 'Pending Sign-off';

        mrpViewModal.classList.add('show');
        mrpViewModal.setAttribute('aria-hidden', 'false');
    };

    function closeMrpViewModal() {
        mrpViewModal.classList.remove('show');
        mrpViewModal.setAttribute('aria-hidden', 'true');
    }
    btnClsMrpViewModal.addEventListener('click', closeMrpViewModal);
    btnClsMrpViewModal2.addEventListener('click', closeMrpViewModal);

    window.printCurrentMrp = function () {
        window.print();
    };

    // Edit Plan
    window.editMrpPlan = function (id) {
        const item = mrpPlans.find(m => m.id === id);
        if (!item) return;

        document.getElementById('formMrpId').value = item.id;
        mrpModalTitle.textContent = `Edit Material Plan: ${item.mrpNo}`;
        document.getElementById('btnSaveMrpText').textContent = 'Update Material Plan';

        document.getElementById('formMrpNo').value = item.mrpNo;
        document.getElementById('formReqDate').value = item.reqDate;
        document.getElementById('formBranch').value = item.branch;
        document.getElementById('formStatus').value = item.status;
        document.getElementById('formCustomerName').value = item.customerName;
        document.getElementById('formProductName').value = item.productName;
        document.getElementById('formRev').value = item.rev;
        document.getElementById('formTflCode').value = item.tflCode;
        document.getElementById('formQty').value = item.qty;
        document.getElementById('formPins').value = item.pins;
        document.getElementById('formReel').value = item.reel || '';
        document.getElementById('formPcsLot').value = item.pcsLot;
        document.getElementById('formLotMonth').value = item.lotMonth || '';
        document.getElementById('formBobbinQty').value = item.bobbinQty;
        document.getElementById('formConductorType1').value = item.conductor1;
        document.getElementById('formX').value = item.x;
        document.getElementById('formConductorType2').value = item.conductor2 || '';
        document.getElementById('formTotalStripLength').value = item.stripLength || '';
        document.getElementById('formInsulationTypeDesc').value = item.insulationDesc;
        document.getElementById('formWidthOld').value = item.widthOld;
        document.getElementById('formWidthNew').value = item.widthNew;
        document.getElementById('formMarginOld').value = item.marginOld || '';
        document.getElementById('formMarginNew').value = item.marginNew || '';
        document.getElementById('formPreparedBy').value = item.preparedBy;
        document.getElementById('formApprovedBy').value = item.approvedBy || '';
        document.getElementById('formRemarks').value = item.remarks || '';

        mrpModal.classList.add('show');
        mrpModal.setAttribute('aria-hidden', 'false');
    };

    // Delete Prompt
    window.promptDeleteMrp = function (id) {
        const item = mrpPlans.find(m => m.id === id);
        if (!item) return;
        currentDeleteId = item.id;
        deleteMrpNumPrompt.textContent = item.mrpNo;
        mrpDeleteModal.classList.add('show');
        mrpDeleteModal.setAttribute('aria-hidden', 'false');
    };

    function closeDeleteModal() {
        mrpDeleteModal.classList.remove('show');
        mrpDeleteModal.setAttribute('aria-hidden', 'true');
        currentDeleteId = null;
    }
    btnCancelDelete.addEventListener('click', closeDeleteModal);
    btnClsMrpDeleteModal.addEventListener('click', closeDeleteModal);

    btnConfirmDelete.addEventListener('click', function () {
        if (!currentDeleteId) return;
        const target = mrpPlans.find(m => m.id === currentDeleteId);
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

