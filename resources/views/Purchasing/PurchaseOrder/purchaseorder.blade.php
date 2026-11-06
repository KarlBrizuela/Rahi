@extends('layouts.app')

@section('title', 'Purchase Orders (PO) — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Purchasing/purchasing.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">
    <header class="top-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-item">Supply Chain</span>
            <span class="bc-separator">/</span>
            <span class="bc-item">Purchasing</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Purchase Order</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="globalSearchInput" placeholder="Search PO Number, Supplier, PR Ref..." autocomplete="off">
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Purchase Order (PO) Register</h1>
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
                            <strong>Print PO Register</strong>
                            <small>Commercial purchase orders summary</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="alert('Exporting PO CSV...')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>Export CSV</strong>
                            <small>Download purchase orders spreadsheet</small>
                        </div>
                    </button>
                </div>
            </div>

            <button type="button" class="btn-primary" onclick="openCreateModal()">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Purchase Order</span>
            </button>
        </div>
    </section>

    <section class="filter-strip no-print">
        <div class="filter-group">
            <label class="filter-label">PO Status</label>
            <select class="filter-select" id="filterStatus">
                <option value="">All Statuses</option>
                <option value="Issued">Issued to Supplier</option>
                <option value="Acknowledged">Supplier Acknowledged</option>
                <option value="Partially Delivered">Partially Delivered</option>
                <option value="Completed">Completed / Closed</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Supplier</label>
            <select class="filter-select" id="filterSupplier">
                <option value="">All Suppliers</option>
                <option value="Sumitomo Metal">Sumitomo Metal & Wire Corp.</option>
                <option value="Toray Polymers">Toray Polymers Asia</option>
                <option value="Apex Packaging">Apex Packaging Solutions</option>
                <option value="Shin-Etsu Chemical">Shin-Etsu Chemical Philippines</option>
            </select>
        </div>

        <div class="filter-group">
            <label class="filter-label">Date From</label>
            <input type="date" class="filter-input" id="filterDateFrom">
        </div>

        <div class="filter-group">
            <label class="filter-label">Date To</label>
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
            <span class="kpi-label">TOTAL PURCHASE ORDERS</span>
            <span class="kpi-value font-numeric" id="kpiTotal">4</span>
            <span class="kpi-subtext">Commercial procurement commitments</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">TOTAL ORDER COMMITMENT</span>
            <span class="kpi-value font-numeric font-bold" style="color: #2563eb;" id="kpiCommitment">₱ 1,842,850.00</span>
            <span class="kpi-subtext">Committed payables incl. VAT</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">OPEN DELIVERIES PENDING</span>
            <span class="kpi-value font-numeric" style="color: #d97706;" id="kpiPending">3</span>
            <span class="kpi-subtext">Awaiting warehouse intake</span>
        </div>
        <div class="kpi-cell">
            <span class="kpi-label">COMPLETED & BILLED</span>
            <span class="kpi-value font-numeric" style="color: #059669;" id="kpiCompleted">1</span>
            <span class="kpi-subtext">Matched with receiving report</span>
        </div>
    </section>

    <div class="ledger-container">
        <div class="ledger-header no-print">
            <div class="ledger-title-cluster">
                <h2 class="ledger-title">Commercial Purchase Orders Register</h2>
                <span class="ledger-badge"><span id="recordCount">4</span> Records</span>
            </div>

            <div class="bulk-actions" id="bulkActions" style="display: none;">
                <span class="selected-text"><strong id="selectedCount">0</strong> selected</span>
                <button type="button" class="bulk-btn" onclick="bulkSendEmail()">
                    <i data-lucide="mail"></i>
                    <span>Email to Vendors</span>
                </button>
                <button type="button" class="bulk-btn" onclick="bulkPrintPO()">
                    <i data-lucide="printer"></i>
                    <span>Print Orders</span>
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table" id="poTable">
                <thead>
                    <tr>
                        <th class="th-checkbox no-print">
                            <input type="checkbox" id="selectAllCheckbox" title="Select all rows">
                        </th>
                        <th>PO NUMBER</th>
                        <th>PO DATE</th>
                        <th>PR REFERENCE</th>
                        <th>SUPPLIER NAME</th>
                        <th>PAYMENT TERMS</th>
                        <th>CURRENCY</th>
                        <th class="text-right">SUBTOTAL</th>
                        <th class="text-right">VAT (12%)</th>
                        <th class="text-right">TOTAL AMOUNT</th>
                        <th>DELIVERY DUE</th>
                        <th>STATUS</th>
                        <th class="text-center no-print" style="width: 70px;">ACTIONS</th>
                    </tr>
                </thead>
                <tbody id="poTableBody">
                    <tr data-id="1" data-sup="Sumitomo Metal" data-status="Issued">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="1">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPO('1')">PO-2026-0032</a>
                        </td>
                        <td class="font-numeric">2026-11-06</td>
                        <td><span class="font-code font-bold">PR-2026-0048</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Sumitomo Metal & Wire Corp.</span>
                                <span class="prod-sub">TIN: 004-918-201-000</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Net 30 Days</span></td>
                        <td>PHP</td>
                        <td class="text-right font-numeric">₱ 364,000.00</td>
                        <td class="text-right font-numeric">₱ 43,680.00</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 407,680.00</td>
                        <td class="font-numeric font-bold" style="color: #dc2626;">2026-11-12</td>
                        <td><span class="status-badge status-intransit">Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '1')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_1">
                                <button type="button" class="action-menu-item" onclick="viewPO('1')">
                                    <i data-lucide="eye"></i>
                                    <span>View PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPO('1')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPO('1')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PO</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePO('1')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PO</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="2" data-sup="Toray Polymers" data-status="Acknowledged">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="2">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPO('2')">PO-2026-0031</a>
                        </td>
                        <td class="font-numeric">2026-11-05</td>
                        <td><span class="font-code font-bold">PR-2026-0047</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Toray Polymers Asia</span>
                                <span class="prod-sub">TIN: 008-331-509-000</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Net 60 Days</span></td>
                        <td>PHP</td>
                        <td class="text-right font-numeric">₱ 560,000.00</td>
                        <td class="text-right font-numeric">₱ 67,200.00</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 627,200.00</td>
                        <td class="font-numeric">2026-11-16</td>
                        <td><span class="status-badge status-reorder">Acknowledged</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '2')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_2">
                                <button type="button" class="action-menu-item" onclick="viewPO('2')">
                                    <i data-lucide="eye"></i>
                                    <span>View PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPO('2')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPO('2')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PO</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePO('2')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PO</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="3" data-sup="Apex Packaging" data-status="Completed">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="3">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPO('3')">PO-2026-0030</a>
                        </td>
                        <td class="font-numeric">2026-11-04</td>
                        <td><span class="font-code font-bold">PR-2026-0046</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Apex Packaging Solutions</span>
                                <span class="prod-sub">TIN: 001-844-902-000</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Net 30 Days</span></td>
                        <td>PHP</td>
                        <td class="text-right font-numeric">₱ 367,750.00</td>
                        <td class="text-right font-numeric">₱ 44,130.00</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 411,880.00</td>
                        <td class="font-numeric">2026-11-08</td>
                        <td><span class="status-badge status-completed">Completed</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '3')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_3">
                                <button type="button" class="action-menu-item" onclick="viewPO('3')">
                                    <i data-lucide="eye"></i>
                                    <span>View PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPO('3')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPO('3')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PO</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePO('3')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PO</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr data-id="4" data-sup="Shin-Etsu Chemical" data-status="Issued">
                        <td class="td-checkbox no-print">
                            <input type="checkbox" class="row-checkbox" value="4">
                        </td>
                        <td>
                            <a href="javascript:void(0)" class="table-id-link font-code font-bold" onclick="viewPO('4')">PO-2026-0029</a>
                        </td>
                        <td class="font-numeric">2026-11-01</td>
                        <td><span class="font-code font-bold">PR-2026-0044</span></td>
                        <td>
                            <div class="product-cell">
                                <span class="prod-title">Shin-Etsu Chemical Philippines</span>
                                <span class="prod-sub">TIN: 007-910-833-000</span>
                            </div>
                        </td>
                        <td><span class="category-pill">Net 45 Days</span></td>
                        <td>PHP</td>
                        <td class="text-right font-numeric">₱ 353,650.00</td>
                        <td class="text-right font-numeric">₱ 42,438.00</td>
                        <td class="text-right font-numeric font-bold" style="color: #2563eb;">₱ 396,088.00</td>
                        <td class="font-numeric">2026-11-18</td>
                        <td><span class="status-badge status-intransit">Issued</span></td>
                        <td class="actions-cell no-print">
                            <button type="button" class="btn-action-trigger" onclick="toggleActionMenu(event, '4')" title="Actions">
                                <i data-lucide="more-horizontal" style="width: 14px; height: 14px;"></i>
                            </button>
                            <div class="action-menu-dropdown drop-down" id="actionMenu_4">
                                <button type="button" class="action-menu-item" onclick="viewPO('4')">
                                    <i data-lucide="eye"></i>
                                    <span>View PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="editPO('4')">
                                    <i data-lucide="pencil"></i>
                                    <span>Edit PO</span>
                                </button>
                                <button type="button" class="action-menu-item" onclick="printPO('4')">
                                    <i data-lucide="printer"></i>
                                    <span>Print PO</span>
                                </button>
                                <button type="button" class="action-menu-item item-danger" onclick="deletePO('4')">
                                    <i data-lucide="trash-2"></i>
                                    <span>Cancel PO</span>
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
                <h3 class="modal-title" id="entryModalTitle">Generate Purchase Order</h3>
                <p class="modal-subtitle">Issue binding purchase contract to certified supplier with delivery and billing terms.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeEntryModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="entryForm" onsubmit="savePO(event)">
            <div class="modal-body">
                <div class="form-section-header">
                    <span class="step-num">1</span>
                    <span>Order Contract & Vendor Parameters</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">PO Number</label>
                        <input type="text" class="field-input font-code" id="poNumber" value="PO-2026-0033" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">PO Date</label>
                        <input type="date" class="field-input" id="poDate" value="2026-11-06" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">PR Reference</label>
                        <input type="text" class="field-input font-code" id="poPrRef" placeholder="e.g. PR-2026-0048" required>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Supplier</label>
                        <select class="field-select" id="poSupplier" required>
                            <option value="Sumitomo Metal & Wire Corp.">Sumitomo Metal & Wire Corp.</option>
                            <option value="Toray Polymers Asia">Toray Polymers Asia</option>
                            <option value="Apex Packaging Solutions">Apex Packaging Solutions</option>
                            <option value="Shin-Etsu Chemical Philippines">Shin-Etsu Chemical Philippines</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Payment Terms</label>
                        <select class="field-select" id="poTerms" required>
                            <option value="Net 30 Days">Net 30 Days</option>
                            <option value="Net 60 Days">Net 60 Days</option>
                            <option value="Net 45 Days">Net 45 Days</option>
                            <option value="COD / Immediate">COD / Immediate</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Delivery Due Date</label>
                        <input type="date" class="field-input" id="poDeliveryDue" value="2026-11-15" required>
                    </div>
                </div>

                <div class="form-section-header">
                    <span class="step-num">2</span>
                    <span>Commercial & Tax Valuation</span>
                </div>
                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Order Currency</label>
                        <select class="field-select" id="poCurrency" required>
                            <option value="PHP">PHP - Philippine Peso</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Exchange Rate (to PHP)</label>
                        <input type="number" step="0.0001" class="field-input" id="poRate" value="1.0000" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label required">Status</label>
                        <select class="field-select" id="poStatus" required>
                            <option value="Issued">Issued to Supplier</option>
                            <option value="Acknowledged">Supplier Acknowledged</option>
                            <option value="Partially Delivered">Partially Delivered</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-grid grid-3">
                    <div class="form-field">
                        <label class="field-label required">Subtotal Net Amount</label>
                        <input type="number" step="0.01" class="field-input" id="poSubtotal" value="364000.00" oninput="calcPOTotals()" required>
                    </div>
                    <div class="form-field">
                        <label class="field-label">VAT Amount (12%)</label>
                        <input type="number" step="0.01" class="field-input" id="poVat" value="43680.00" readonly style="background:#f1f5f9;">
                    </div>
                    <div class="form-field">
                        <label class="field-label">Total Payable</label>
                        <input type="number" step="0.01" class="field-input font-bold" id="poTotal" value="407680.00" readonly style="background:#f1f5f9; color:#2563eb;">
                    </div>
                </div>

                <div class="form-row-grid grid-1">
                    <div class="form-field">
                        <label class="field-label">Delivery Instructions & Billing Remarks</label>
                        <textarea class="field-textarea" id="poRemarks" rows="2" placeholder="Deliver to Mandaluyong receiving bay gate 2, attached certificate of analysis required..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeEntryModal()">Cancel</button>
                <button type="submit" class="btn-save">
                    <i data-lucide="check"></i>
                    <span id="saveBtnLabel">Issue Purchase Order</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal-backdrop" id="viewModal">
    <div class="modal-panel modal-lg">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title" id="viewModalTitle">Purchase Order Contract</h3>
                <p class="modal-subtitle">Official commercial purchase order agreement and delivery specifications.</p>
            </div>
            <button type="button" class="modal-close" onclick="closeViewModal()">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body" id="viewModalContent"></div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="window.print()">
                <i data-lucide="printer"></i>
                <span>Print Official PO</span>
            </button>
            <button type="button" class="btn-primary" onclick="closeViewModal()">Close</button>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="deleteModal">
    <div class="modal-panel modal-sm">
        <div class="modal-header">
            <div class="modal-title-box">
                <h3 class="modal-title text-danger">Cancel Purchase Order</h3>
            </div>
            <button type="button" class="modal-close" onclick="closeDeleteModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to cancel purchase order <strong id="deleteTargetCode"></strong>?</p>
            <p class="text-muted" style="margin-top: 8px; font-size: 12px;">This action will notify the vendor and release all committed budgetary allocations.</p>
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
        document.getElementById("filterSupplier").addEventListener("change", filterTable);
        document.getElementById("filterDateFrom").addEventListener("change", filterTable);
        document.getElementById("filterDateTo").addEventListener("change", filterTable);

        document.getElementById("btnResetFilters").addEventListener("click", function () {
            document.getElementById("globalSearchInput").value = "";
            document.getElementById("filterStatus").value = "";
            document.getElementById("filterSupplier").value = "";
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

    function calcPOTotals() {
        const subtotal = parseFloat(document.getElementById("poSubtotal").value) || 0;
        const vat = subtotal * 0.12;
        const total = subtotal + vat;
        document.getElementById("poVat").value = vat.toFixed(2);
        document.getElementById("poTotal").value = total.toFixed(2);
    }

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
        const sup = document.getElementById("filterSupplier").value.toLowerCase();
        const rows = document.querySelectorAll("#poTableBody tr");

        let visibleCount = 0;
        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            const rowSup = (row.getAttribute("data-sup") || "").toLowerCase();
            const rowStatus = (row.getAttribute("data-status") || "").toLowerCase();

            const matchQuery = !query || text.includes(query);
            const matchStatus = !status || rowStatus === status;
            const matchSup = !sup || rowSup.includes(sup);

            if (matchQuery && matchStatus && matchSup) {
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
        document.getElementById("entryModalTitle").innerText = "Generate Purchase Order";
        document.getElementById("saveBtnLabel").innerText = "Issue Purchase Order";
        document.getElementById("entryForm").reset();
        document.getElementById("poNumber").value = "PO-2026-0033";
        document.getElementById("poDate").value = "2026-11-06";
        document.getElementById("poSubtotal").value = "364000.00";
        calcPOTotals();
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function closeEntryModal() {
        document.getElementById("entryModal").classList.remove("show");
    }

    function editPO(id) {
        activeEditId = id;
        document.getElementById("entryModalTitle").innerText = "Edit Purchase Order PO-2026-003" + id;
        document.getElementById("saveBtnLabel").innerText = "Update Order";

        if (id === '1') {
            document.getElementById("poNumber").value = "PO-2026-0032";
            document.getElementById("poDate").value = "2026-11-06";
            document.getElementById("poPrRef").value = "PR-2026-0048";
            document.getElementById("poSupplier").value = "Sumitomo Metal & Wire Corp.";
            document.getElementById("poTerms").value = "Net 30 Days";
            document.getElementById("poDeliveryDue").value = "2026-11-12";
            document.getElementById("poCurrency").value = "PHP";
            document.getElementById("poRate").value = "1.0000";
            document.getElementById("poSubtotal").value = "364000.00";
            calcPOTotals();
        }
        document.getElementById("entryModal").classList.add("show");
        lucide.createIcons();
    }

    function savePO(e) {
        e.preventDefault();
        alert("Purchase Order issued successfully.");
        closeEntryModal();
    }

    function viewPO(id) {
        const modalBody = document.getElementById("viewModalContent");
        modalBody.innerHTML = `
            <div class="view-spec-grid">
                <div class="spec-cell">
                    <span class="spec-label">Purchase Order No</span>
                    <span class="spec-value font-bold font-code">PO-2026-0032</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Order Date</span>
                    <span class="spec-value font-numeric">2026-11-06</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Order Status</span>
                    <span class="spec-value"><span class="status-badge status-intransit">Issued to Supplier</span></span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Contracted Supplier</span>
                    <span class="spec-value font-bold">Sumitomo Metal & Wire Corp. (TIN: 004-918-201-000)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">PR Reference</span>
                    <span class="spec-value font-code">PR-2026-0048</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Commercial Terms</span>
                    <span class="spec-value">Net 30 Days (FOB Mandaluyong Plant)</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Subtotal (Net of VAT)</span>
                    <span class="spec-value font-numeric">₱ 364,000.00</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Value Added Tax (12%)</span>
                    <span class="spec-value font-numeric">₱ 43,680.00</span>
                </div>
                <div class="spec-cell">
                    <span class="spec-label">Total Payable Amount</span>
                    <span class="spec-value font-numeric font-bold" style="color: #2563eb;">₱ 407,680.00</span>
                </div>
            </div>

            <div style="margin-top: 20px;">
                <div class="form-section-header">
                    <span class="step-num"><i data-lucide="package" style="width: 14px; height: 14px;"></i></span>
                    <span>Line Items & Pricing Schedule</span>
                </div>
                <div class="modal-table-wrapper">
                    <table class="modal-grid-table">
                        <thead>
                            <tr>
                                <th>Item Code</th>
                                <th>Description</th>
                                <th class="text-right">Qty</th>
                                <th>UOM</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Net Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="font-code font-bold">MAT-CU-001</td>
                                <td>Copper Conductor Wire 0.12mm (Oxygen-Free)</td>
                                <td class="text-right font-numeric font-bold">8,000</td>
                                <td>MTR</td>
                                <td class="text-right font-numeric">₱ 45.50</td>
                                <td class="text-right font-numeric font-bold">₱ 364,000.00</td>
                            </tr>
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

    function printPO(id) {
        viewPO(id);
        setTimeout(() => window.print(), 300);
    }

    function deletePO(id) {
        activeDeleteId = id;
        document.getElementById("deleteTargetCode").innerText = "PO-2026-003" + id;
        document.getElementById("deleteModal").classList.add("show");
    }

    function closeDeleteModal() {
        document.getElementById("deleteModal").classList.remove("show");
    }

    function confirmDelete() {
        alert("Purchase Order cancelled.");
        closeDeleteModal();
    }

    function bulkSendEmail() {
        alert("Purchase Orders successfully queued for automated email transmission to suppliers.");
    }

    function bulkPrintPO() {
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

