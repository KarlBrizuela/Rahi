@extends('layouts.app')

@section('title', 'Sales Performance Report — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/sales/SalesReport/salesreport.css') }}">
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
            <span class="bc-current">Sales Report</span>
        </div>

        <div class="top-bar-right">
            <div class="data-source-badge">
                <span class="pulse-dot"></span>
                <span>Consolidated from Posted Invoices</span>
            </div>
        </div>
    </header>

    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Sales Performance Report</h1>
            <p class="page-subtext">Commercial revenue analytics, monthly sales progression, and customer-wise sales consolidation</p>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Recalculate report from posted invoices">
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
                    <button type="button" class="export-option" onclick="exportSalesReport('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF Report</strong>
                            <small>Executive monthly & customer dossier</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportSalesReport('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel Workbook (.xls)</strong>
                            <small>Formatted period financial analysis</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportSalesReport('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Raw transactional dataset</small>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <section class="report-control-card no-print">
        <div class="date-controls-group">
            <!-- Covered Date Range -->
            <div class="control-item">
                <span class="control-label">Covered Period:</span>
                <div class="date-range-wrap">
                    <input type="date" id="reportDateFrom" class="date-input" title="Date From" value="2026-10-01" onchange="onDateInputChange()">
                    <span class="date-sep">to</span>
                    <input type="date" id="reportDateTo" class="date-input" title="Date To" value="2026-12-31" onchange="onDateInputChange()">
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="preset-pills-cluster">
                <button type="button" class="preset-pill" onclick="setPeriodPreset('thisMonth')">This Month</button>
                <button type="button" class="preset-pill" onclick="setPeriodPreset('lastMonth')">Last Month</button>
                <button type="button" class="preset-pill" onclick="setPeriodPreset('thisQuarter')">This Quarter</button>
                <button type="button" class="preset-pill active" id="presetYTD" onclick="setPeriodPreset('ytd')">Year to Date (YTD)</button>
            </div>

            <!-- Branch Filter -->
            <div class="control-item" style="margin-left: 8px;">
                <span class="control-label">Branch:</span>
                <select id="reportBranchSelect" class="select-input" onchange="generateReport()">
                    <option value="ALL">All Branches</option>
                    <option value="Head Office (Mandaluyong)">Head Office (Mandaluyong)</option>
                    <option value="Makati Sales Center">Makati Sales Center</option>
                    <option value="Laguna Main Plant Site">Laguna Main Plant Site</option>
                    <option value="Cebu Distribution Depot">Cebu Distribution Depot</option>
                </select>
            </div>

            <!-- Customer Filter -->
            <div class="control-item">
                <span class="control-label">Customer:</span>
                <select id="reportCustomerSelect" class="select-input" onchange="generateReport()">
                    <option value="ALL">All Customers</option>
                    <option value="San Miguel Corporation">San Miguel Corporation</option>
                    <option value="Universal Robina Corporation">Universal Robina Corporation</option>
                    <option value="Robinsons Retail Holdings">Robinsons Retail Holdings</option>
                    <option value="Apex Precision Engineering">Apex Precision Engineering</option>
                </select>
            </div>
        </div>

        <button type="button" class="btn-primary" onclick="generateReport()">
            <i data-lucide="filter" class="btn-icon"></i>
            <span>Apply Analysis</span>
        </button>
    </section>

    <section class="kpi-strip no-print">
        <div class="kpi-col">
            <span class="kpi-label">Total Net Sales</span>
            <span class="kpi-value" style="color: #2563eb;" id="kpiNetSales">₱0.00</span>
            <span class="kpi-sub" id="kpiGrossSub">Gross: ₱0.00</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Posted Invoices</span>
            <span class="kpi-value" id="kpiInvoiceCount">0</span>
            <span class="kpi-sub">Commercial billings</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Average Order Value (AOV)</span>
            <span class="kpi-value" id="kpiAvgOrderValue">₱0.00</span>
            <span class="kpi-sub">Net revenue per invoice</span>
        </div>
        <div class="kpi-col">
            <span class="kpi-label">Outstanding Receivables</span>
            <span class="kpi-value" style="color: #d97706;" id="kpiOutstandingBalance">₱0.00</span>
            <span class="kpi-sub" id="kpiCollectionRate">Collection rate: 0.0%</span>
        </div>
    </section>

    <section class="visual-analytics-grid no-print">
        <!-- Chart 1: Monthly Sales Progression Bar Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-title-cluster">
                    <span class="chart-card-title">Monthly Net Sales Progression</span>
                    <span class="chart-card-subtitle">Month-over-month consolidated revenue progression</span>
                </div>
                <div class="chart-badge">
                    <span>Monthly Velocity</span>
                </div>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="monthlySalesChart"></canvas>
            </div>
        </div>

        <!-- Chart 2: Customer Sales Distribution Donut Chart -->
        <div class="chart-card">
            <div class="chart-card-header">
                <div class="chart-title-cluster">
                    <span class="chart-card-title">Customer Revenue Contribution</span>
                    <span class="chart-card-subtitle">Net sales distribution across major commercial accounts</span>
                </div>
                <div class="chart-badge">
                    <span>Account Share</span>
                </div>
            </div>
            <div class="chart-canvas-wrap">
                <canvas id="customerShareChart"></canvas>
            </div>
        </div>
    </section>

    <main class="page-content-wrapper">
        <div class="report-panel">

            <!-- Report View Switcher Tabs -->
            <div class="report-tab-header no-print">
                <div class="report-tabs">
                    <button type="button" class="report-tab-btn active" id="tabBtnMonthly" onclick="switchReportTab('monthly')">
                        <i data-lucide="calendar" style="width: 15px; height: 15px;"></i>
                        <span>Monthly Sales Summary</span>
                        <span class="tab-badge" id="monthlyCountBadge">0 Months</span>
                    </button>
                    <button type="button" class="report-tab-btn" id="tabBtnCustomer" onclick="switchReportTab('customer')">
                        <i data-lucide="building-2" style="width: 15px; height: 15px;"></i>
                        <span>Customer-Wise Total Sales</span>
                        <span class="tab-badge" id="customerCountBadge">0 Clients</span>
                    </button>
                </div>

                <div style="font-size: 11.5px; color: #64748b;" id="activePeriodLabel">
                    Covered: Oct 01, 2026 — Dec 31, 2026
                </div>
            </div>

            <!-- TAB 1: MONTHLY SALES SUMMARY TABLE -->
            <div class="report-tab-pane active" id="paneMonthly">
                <div class="table-scroll-container">
                    <table class="erp-table" id="monthlySalesTable">
                        <thead>
                            <tr>
                                <th style="min-width: 140px;">Month / Period</th>
                                <th style="min-width: 110px;" class="text-right">Invoices</th>
                                <th style="min-width: 140px;" class="text-right">Gross Sales</th>
                                <th style="min-width: 130px;" class="text-right">Total Discounts</th>
                                <th style="min-width: 130px;" class="text-right">Consolidated VAT</th>
                                <th style="min-width: 140px;" class="text-right">Net Sales</th>
                                <th style="min-width: 140px;" class="text-right">Balance Outstanding</th>
                                <th style="min-width: 160px;">% Sales Contribution</th>
                                <th style="width: 100px;" class="text-center no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody id="monthlyTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                        <tfoot id="monthlyTableFoot">
                            <!-- Monthly totals dynamically computed -->
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- TAB 2: CUSTOMER-WISE TOTAL SALES TABLE -->
            <div class="report-tab-pane" id="paneCustomer">
                <div class="table-scroll-container">
                    <table class="erp-table" id="customerSalesTable">
                        <thead>
                            <tr>
                                <th style="min-width: 220px;">Customer Account</th>
                                <th style="min-width: 160px;">Primary Branch</th>
                                <th style="min-width: 90px;" class="text-right">Invoices</th>
                                <th style="min-width: 100px;" class="text-right">Total Units Sold</th>
                                <th style="min-width: 130px;" class="text-right">Gross Billed</th>
                                <th style="min-width: 120px;" class="text-right">Total Discounts</th>
                                <th style="min-width: 140px;" class="text-right">Net Sales</th>
                                <th style="min-width: 140px;" class="text-right">Outstanding Balance</th>
                                <th style="min-width: 120px;" class="text-center">Collection Status</th>
                                <th style="width: 110px;" class="text-center no-print">Action</th>
                            </tr>
                        </thead>
                        <tbody id="customerTableBody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                        <tfoot id="customerTableFoot">
                            <!-- Customer totals dynamically computed -->
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </main>

</div>

<div class="modal-backdrop" id="drilldownModalBackdrop" role="dialog" aria-modal="true">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-title-cluster">
                <h3 class="modal-title" id="drilldownModalTitle">Consolidated Invoices</h3>
                <span class="modal-subtitle" id="drilldownModalSub">Itemized posted invoices comprising this sales performance figure</span>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeDrilldownModal()">
                <i data-lucide="x"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="table-scroll-container">
                <table class="erp-table" id="drilldownTable">
                    <thead>
                        <tr>
                            <th>SI #</th>
                            <th>Txn Date</th>
                            <th>Customer</th>
                            <th>Branch</th>
                            <th class="text-right">Gross</th>
                            <th class="text-right">Discount</th>
                            <th class="text-right">Net Sales</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody id="drilldownTableBody">
                        <!-- Populated dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-outline" onclick="closeDrilldownModal()">Close</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    // 1. CONSOLIDATED POSTED INVOICES DATASET

    const postedInvoicesMaster = [
        {
            si_number: 'SI-2026-001',
            si_txn_date: '2026-11-01',
            due_date: '2026-12-01',
            customer: 'San Miguel Corporation',
            branch: 'Head Office (Mandaluyong)',
            units_sold: 100,
            currency: 'PHP',
            gross_amount: 185000.00,
            discount_amount: 9250.00,
            vat_amount: 21090.00,
            net_amount: 196840.00,
            credit_amount: 50000.00,
            balance: 146840.00,
            status: 'Posted'
        },
        {
            si_number: 'SI-2026-002',
            si_txn_date: '2026-11-03',
            due_date: '2027-01-02',
            customer: 'Universal Robina Corporation',
            branch: 'Makati Sales Center',
            units_sold: 250,
            currency: 'PHP',
            gross_amount: 160000.00,
            discount_amount: 5000.00,
            vat_amount: 18600.00,
            net_amount: 173600.00,
            credit_amount: 0.00,
            balance: 173600.00,
            status: 'Posted'
        },
        {
            si_number: 'SI-2026-003',
            si_txn_date: '2026-11-04',
            due_date: '2026-12-15',
            customer: 'Robinsons Retail Holdings',
            branch: 'Laguna Main Plant Site',
            units_sold: 50,
            currency: 'PHP',
            gross_amount: 160000.00,
            discount_amount: 0.00,
            vat_amount: 0.00,
            net_amount: 160000.00,
            credit_amount: 160000.00,
            balance: 0.00,
            status: 'Posted'
        },
        {
            si_number: 'SI-2026-005',
            si_txn_date: '2026-10-14',
            due_date: '2026-11-14',
            customer: 'San Miguel Corporation',
            branch: 'Head Office (Mandaluyong)',
            units_sold: 80,
            currency: 'PHP',
            gross_amount: 148000.00,
            discount_amount: 7400.00,
            vat_amount: 16872.00,
            net_amount: 157472.00,
            credit_amount: 157472.00,
            balance: 0.00,
            status: 'Posted'
        },
        {
            si_number: 'SI-2026-006',
            si_txn_date: '2026-10-22',
            due_date: '2026-11-22',
            customer: 'Universal Robina Corporation',
            branch: 'Makati Sales Center',
            units_sold: 120,
            currency: 'PHP',
            gross_amount: 76800.00,
            discount_amount: 2400.00,
            vat_amount: 8928.00,
            net_amount: 83328.00,
            credit_amount: 83328.00,
            balance: 0.00,
            status: 'Posted'
        },
        {
            si_number: 'SI-2026-007',
            si_txn_date: '2026-12-05',
            due_date: '2027-01-05',
            customer: 'Apex Precision Engineering',
            branch: 'Cebu Distribution Depot',
            units_sold: 150,
            currency: 'PHP',
            gross_amount: 67500.00,
            discount_amount: 1350.00,
            vat_amount: 7938.00,
            net_amount: 74088.00,
            credit_amount: 74088.00,
            balance: 0.00,
            status: 'Posted'
        }
    ];

    // DOM Elements
    const reportDateFrom = document.getElementById('reportDateFrom');
    const reportDateTo = document.getElementById('reportDateTo');
    const reportBranchSelect = document.getElementById('reportBranchSelect');
    const reportCustomerSelect = document.getElementById('reportCustomerSelect');
    const activePeriodLabel = document.getElementById('activePeriodLabel');

    // KPI Elements
    const kpiNetSales = document.getElementById('kpiNetSales');
    const kpiGrossSub = document.getElementById('kpiGrossSub');
    const kpiInvoiceCount = document.getElementById('kpiInvoiceCount');
    const kpiAvgOrderValue = document.getElementById('kpiAvgOrderValue');
    const kpiOutstandingBalance = document.getElementById('kpiOutstandingBalance');
    const kpiCollectionRate = document.getElementById('kpiCollectionRate');

    // Tables
    const monthlyTableBody = document.getElementById('monthlyTableBody');
    const monthlyTableFoot = document.getElementById('monthlyTableFoot');
    const customerTableBody = document.getElementById('customerTableBody');
    const customerTableFoot = document.getElementById('customerTableFoot');
    const monthlyCountBadge = document.getElementById('monthlyCountBadge');
    const customerCountBadge = document.getElementById('customerCountBadge');

    // Export & Refresh
    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // Drilldown
    const drilldownModalBackdrop = document.getElementById('drilldownModalBackdrop');
    const drilldownModalTitle = document.getElementById('drilldownModalTitle');
    const drilldownModalSub = document.getElementById('drilldownModalSub');
    const drilldownTableBody = document.getElementById('drilldownTableBody');

    let currentFilteredInvoices = [];
    let monthlyChartInstance = null;
    let customerChartInstance = null;

    // 2. PERIOD PRESETS & FILTERING

    window.setPeriodPreset = function (preset) {
        document.querySelectorAll('.preset-pill').forEach(p => p.classList.remove('active'));
        const now = new Date();
        const year = now.getFullYear();
        let fromDate = '';
        let toDate = '';

        if (preset === 'thisMonth') {
            const m = String(now.getMonth() + 1).padStart(2, '0');
            fromDate = `${year}-${m}-01`;
            const lastDay = new Date(year, now.getMonth() + 1, 0).getDate();
            toDate = `${year}-${m}-${String(lastDay).padStart(2, '0')}`;
        } else if (preset === 'lastMonth') {
            const prevMonthDate = new Date(year, now.getMonth() - 1, 1);
            const m = String(prevMonthDate.getMonth() + 1).padStart(2, '0');
            const py = prevMonthDate.getFullYear();
            fromDate = `${py}-${m}-01`;
            const lastDay = new Date(py, prevMonthDate.getMonth() + 1, 0).getDate();
            toDate = `${py}-${m}-${String(lastDay).padStart(2, '0')}`;
        } else if (preset === 'thisQuarter') {
            const q = Math.floor(now.getMonth() / 3);
            const startMonth = String(q * 3 + 1).padStart(2, '0');
            fromDate = `${year}-${startMonth}-01`;
            const endMonth = q * 3 + 3;
            const lastDay = new Date(year, endMonth, 0).getDate();
            toDate = `${year}-${String(endMonth).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`;
        } else if (preset === 'ytd') {
            fromDate = `${year}-01-01`;
            toDate = `${year}-12-31`;
            const ytdBtn = document.getElementById('presetYTD');
            if (ytdBtn) ytdBtn.classList.add('active');
        }

        reportDateFrom.value = fromDate;
        reportDateTo.value = toDate;
        generateReport();
    };

    window.onDateInputChange = function () {
        document.querySelectorAll('.preset-pill').forEach(p => p.classList.remove('active'));
        generateReport();
    };

    // 3. GENERATE CONSOLIDATED REPORT

    window.generateReport = function () {
        const from = reportDateFrom.value;
        const to = reportDateTo.value;
        const branchVal = reportBranchSelect.value;
        const customerVal = reportCustomerSelect.value;

        activePeriodLabel.textContent = `Covered: ${from || 'Earliest'} — ${to || 'Latest'}`;

        // Filter posted invoices
        currentFilteredInvoices = postedInvoicesMaster.filter(inv => {
            const matchesFrom = !from || inv.si_txn_date >= from;
            const matchesTo = !to || inv.si_txn_date <= to;
            const matchesBranch = (branchVal === 'ALL') || (inv.branch === branchVal);
            const matchesCustomer = (customerVal === 'ALL') || (inv.customer === customerVal);
            return matchesFrom && matchesTo && matchesBranch && matchesCustomer;
        });

        // 1. Calculate Executive KPIs
        const totalNet = currentFilteredInvoices.reduce((acc, r) => acc + r.net_amount, 0);
        const totalGross = currentFilteredInvoices.reduce((acc, r) => acc + r.gross_amount, 0);
        const totalCount = currentFilteredInvoices.length;
        const totalBalance = currentFilteredInvoices.reduce((acc, r) => acc + r.balance, 0);
        const totalCollected = totalNet - totalBalance;
        const collectionRate = totalNet > 0 ? ((totalCollected / totalNet) * 100).toFixed(1) : '100.0';
        const aov = totalCount > 0 ? (totalNet / totalCount) : 0;

        kpiNetSales.textContent = fmtCurrency(totalNet);
        kpiGrossSub.textContent = `Gross Billed: ${fmtCurrency(totalGross)}`;
        kpiInvoiceCount.textContent = totalCount;
        kpiAvgOrderValue.textContent = fmtCurrency(aov);
        kpiOutstandingBalance.textContent = fmtCurrency(totalBalance);
        kpiCollectionRate.textContent = `Collection rate: ${collectionRate}%`;

        // 2. Render Monthly Breakdown
        renderMonthlyBreakdown(currentFilteredInvoices, totalNet);

        // 3. Render Customer-Wise Breakdown
        renderCustomerBreakdown(currentFilteredInvoices, totalNet);

        // 4. Update Visual Analytics Charts
        updateCharts(currentFilteredInvoices);

        if (window.lucide) window.lucide.createIcons();
    };

    // 4. MONTHLY BREAKDOWN CONSOLIDATION

    function renderMonthlyBreakdown(invoices, totalNetRevenue) {
        monthlyTableBody.innerHTML = '';
        monthlyTableFoot.innerHTML = '';

        if (invoices.length === 0) {
            monthlyTableBody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted" style="padding: 36px 16px;">
                        No posted invoices consolidated for the selected date range.
                    </td>
                </tr>
            `;
            monthlyCountBadge.textContent = '0 Months';
            return;
        }

        // Group by Month (YYYY-MM)
        const groups = {};
        invoices.forEach(inv => {
            const ym = inv.si_txn_date.slice(0, 7); // e.g. "2026-11"
            if (!groups[ym]) {
                groups[ym] = {
                    key: ym,
                    invoices: [],
                    gross: 0,
                    discount: 0,
                    vat: 0,
                    net: 0,
                    balance: 0
                };
            }
            groups[ym].invoices.push(inv);
            groups[ym].gross += inv.gross_amount;
            groups[ym].discount += inv.discount_amount;
            groups[ym].vat += inv.vat_amount;
            groups[ym].net += inv.net_amount;
            groups[ym].balance += inv.balance;
        });

        const sortedMonths = Object.keys(groups).sort();
        monthlyCountBadge.textContent = `${sortedMonths.length} Month${sortedMonths.length === 1 ? '' : 's'}`;

        let sumGross = 0, sumDisc = 0, sumVat = 0, sumNet = 0, sumBal = 0, sumInv = 0;

        sortedMonths.forEach(ym => {
            const g = groups[ym];
            sumGross += g.gross;
            sumDisc += g.discount;
            sumVat += g.vat;
            sumNet += g.net;
            sumBal += g.balance;
            sumInv += g.invoices.length;

            const sharePct = totalNetRevenue > 0 ? ((g.net / totalNetRevenue) * 100).toFixed(1) : '0.0';

            // Format Month Label (e.g. November 2026)
            const [y, m] = ym.split('-');
            const monthDate = new Date(parseInt(y, 10), parseInt(m, 10) - 1, 1);
            const monthName = monthDate.toLocaleString('en-US', { month: 'long', year: 'numeric' });

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="font-bold text-dark">${monthName}</span>
                </td>
                <td class="text-right font-numeric font-medium">${g.invoices.length}</td>
                <td class="text-right font-numeric font-bold">${fmtCurrency(g.gross)}</td>
                <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(g.discount)}</td>
                <td class="text-right font-numeric">${fmtCurrency(g.vat)}</td>
                <td class="text-right font-numeric font-bold" style="color: #2563eb;">${fmtCurrency(g.net)}</td>
                <td class="text-right font-numeric font-medium" style="color: ${g.balance > 0 ? '#d97706' : '#059669'};">
                    ${fmtCurrency(g.balance)}
                </td>
                <td>
                    <div class="contrib-progress-wrap">
                        <div class="contrib-progress-bar">
                            <div class="contrib-progress-fill" style="width: ${sharePct}%;"></div>
                        </div>
                        <span class="contrib-text">${sharePct}%</span>
                    </div>
                </td>
                <td class="text-center no-print">
                    <button type="button" class="btn-outline" style="height: 26px; padding: 0 8px; font-size: 11px;" onclick="openMonthDrilldown('${ym}', '${monthName}')">
                        <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                        <span>Details</span>
                    </button>
                </td>
            `;
            monthlyTableBody.appendChild(tr);
        });

        // Foot Totals
        monthlyTableFoot.innerHTML = `
            <tr>
                <td>Total Monthly Consolidation</td>
                <td class="text-right font-numeric">${sumInv}</td>
                <td class="text-right font-numeric">${fmtCurrency(sumGross)}</td>
                <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(sumDisc)}</td>
                <td class="text-right font-numeric">${fmtCurrency(sumVat)}</td>
                <td class="text-right font-numeric" style="color: #2563eb;">${fmtCurrency(sumNet)}</td>
                <td class="text-right font-numeric" style="color: ${sumBal > 0 ? '#d97706' : '#059669'};">${fmtCurrency(sumBal)}</td>
                <td><span style="font-size: 11px; font-weight: 700;">100.0% Contribution</span></td>
                <td class="no-print"></td>
            </tr>
        `;
    }

    // 5. CUSTOMER-WISE BREAKDOWN CONSOLIDATION

    function renderCustomerBreakdown(invoices, totalNetRevenue) {
        customerTableBody.innerHTML = '';
        customerTableFoot.innerHTML = '';

        if (invoices.length === 0) {
            customerTableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted" style="padding: 36px 16px;">
                        No customer accounts found for the covered date range.
                    </td>
                </tr>
            `;
            customerCountBadge.textContent = '0 Clients';
            return;
        }

        // Group by Customer
        const custGroups = {};
        invoices.forEach(inv => {
            const cust = inv.customer;
            if (!custGroups[cust]) {
                custGroups[cust] = {
                    customer: cust,
                    branch: inv.branch,
                    invoices: [],
                    units: 0,
                    gross: 0,
                    discount: 0,
                    net: 0,
                    balance: 0
                };
            }
            custGroups[cust].invoices.push(inv);
            custGroups[cust].units += inv.units_sold;
            custGroups[cust].gross += inv.gross_amount;
            custGroups[cust].discount += inv.discount_amount;
            custGroups[cust].net += inv.net_amount;
            custGroups[cust].balance += inv.balance;
        });

        const sortedCustomers = Object.keys(custGroups).sort((a, b) => custGroups[b].net - custGroups[a].net);
        customerCountBadge.textContent = `${sortedCustomers.length} Client${sortedCustomers.length === 1 ? '' : 's'}`;

        let sumUnits = 0, sumGross = 0, sumDisc = 0, sumNet = 0, sumBal = 0, sumInv = 0;

        sortedCustomers.forEach(cName => {
            const cg = custGroups[cName];
            sumUnits += cg.units;
            sumGross += cg.gross;
            sumDisc += cg.discount;
            sumNet += cg.net;
            sumBal += cg.balance;
            sumInv += cg.invoices.length;

            // Collection Health Badge
            let healthBadge = '<span class="health-pill health-pill-healthy"><span class="pulse-dot"></span> Fully Settled</span>';
            if (cg.balance > 0) {
                if (cg.balance === cg.net) {
                    healthBadge = '<span class="health-pill health-pill-attention">● Unpaid (Aging)</span>';
                } else {
                    healthBadge = '<span class="health-pill health-pill-attention">● Partial Balance</span>';
                }
            }

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <span class="font-bold text-dark">${escapeHtml(cg.customer)}</span>
                </td>
                <td>${escapeHtml(cg.branch)}</td>
                <td class="text-right font-numeric font-medium">${cg.invoices.length}</td>
                <td class="text-right font-numeric font-bold">${cg.units.toLocaleString()}</td>
                <td class="text-right font-numeric">${fmtCurrency(cg.gross)}</td>
                <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(cg.discount)}</td>
                <td class="text-right font-numeric font-bold" style="color: #2563eb;">${fmtCurrency(cg.net)}</td>
                <td class="text-right font-numeric font-medium" style="color: ${cg.balance > 0 ? '#d97706' : '#059669'};">
                    ${fmtCurrency(cg.balance)}
                </td>
                <td class="text-center">${healthBadge}</td>
                <td class="text-center no-print">
                    <button type="button" class="btn-outline" style="height: 26px; padding: 0 8px; font-size: 11px;" onclick="openCustomerDrilldown('${escapeHtml(cg.customer)}')">
                        <i data-lucide="eye" style="width: 12px; height: 12px;"></i>
                        <span>Invoices</span>
                    </button>
                </td>
            `;
            customerTableBody.appendChild(tr);
        });

        // Foot Totals
        customerTableFoot.innerHTML = `
            <tr>
                <td>Consolidated Customer Totals</td>
                <td>All Operational Hubs</td>
                <td class="text-right font-numeric">${sumInv}</td>
                <td class="text-right font-numeric font-bold">${sumUnits.toLocaleString()}</td>
                <td class="text-right font-numeric">${fmtCurrency(sumGross)}</td>
                <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(sumDisc)}</td>
                <td class="text-right font-numeric" style="color: #2563eb;">${fmtCurrency(sumNet)}</td>
                <td class="text-right font-numeric" style="color: ${sumBal > 0 ? '#d97706' : '#059669'};">${fmtCurrency(sumBal)}</td>
                <td class="text-center"><span style="font-size: 11px; font-weight: 600; color: #059669;">Audited Net</span></td>
                <td class="no-print"></td>
            </tr>
        `;
    }

    // 5.5. CHART.JS VISUAL ANALYTICS RENDERING

    function updateCharts(invoices) {
        if (typeof Chart === 'undefined') return;

        // --- Chart 1: Monthly Progression ---
        const monthlyGroups = {};
        invoices.forEach(inv => {
            const ym = inv.si_txn_date.slice(0, 7);
            if (!monthlyGroups[ym]) monthlyGroups[ym] = 0;
            monthlyGroups[ym] += inv.net_amount;
        });

        const sortedMonths = Object.keys(monthlyGroups).sort();
        const monthLabels = sortedMonths.map(ym => {
            const [y, m] = ym.split('-');
            const monthDate = new Date(parseInt(y, 10), parseInt(m, 10) - 1, 1);
            return monthDate.toLocaleString('en-US', { month: 'short', year: 'numeric' });
        });
        const monthData = sortedMonths.map(ym => monthlyGroups[ym]);

        const monthlyCanvas = document.getElementById('monthlySalesChart');
        if (monthlyCanvas) {
            if (monthlyChartInstance) {
                monthlyChartInstance.destroy();
            }

            const ctx1 = monthlyCanvas.getContext('2d');
            monthlyChartInstance = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: monthLabels.length ? monthLabels : ['No Data'],
                    datasets: [{
                        label: 'Net Sales (₱)',
                        data: monthData.length ? monthData : [0],
                        backgroundColor: '#2563eb',
                        hoverBackgroundColor: '#1d4ed8',
                        borderRadius: 4,
                        barPercentage: 0.5
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#2563eb',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function (context) {
                                    return `Net Sales: ₱${context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 2 })}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { font: { size: 11, family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto" }, color: '#64748b' }
                        },
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                font: { size: 11, family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto" },
                                color: '#64748b',
                                callback: function (value) {
                                    return '₱' + (value >= 1000 ? (value / 1000).toFixed(0) + 'k' : value);
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- Chart 2: Customer Contribution Donut ---
        const custGroups = {};
        invoices.forEach(inv => {
            const c = inv.customer;
            if (!custGroups[c]) custGroups[c] = 0;
            custGroups[c] += inv.net_amount;
        });

        const sortedCusts = Object.keys(custGroups).sort((a, b) => custGroups[b] - custGroups[a]);
        const custLabels = sortedCusts.map(c => c.length > 20 ? c.substring(0, 18) + '...' : c);
        const custData = sortedCusts.map(c => custGroups[c]);
        const colors = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ea580c'];

        const customerCanvas = document.getElementById('customerShareChart');
        if (customerCanvas) {
            if (customerChartInstance) {
                customerChartInstance.destroy();
            }

            const ctx2 = customerCanvas.getContext('2d');
            customerChartInstance = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: custLabels.length ? custLabels : ['No Accounts'],
                    datasets: [{
                        data: custData.length ? custData : [1],
                        backgroundColor: custData.length ? colors.slice(0, custData.length) : ['#e2e8f0'],
                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                boxWidth: 10,
                                boxHeight: 10,
                                usePointStyle: true,
                                pointStyle: 'circle',
                                font: { size: 11, family: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto" },
                                color: '#334155',
                                padding: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#ffffff',
                            titleColor: '#0f172a',
                            bodyColor: '#334155',
                            borderColor: '#e2e8f0',
                            borderWidth: 1,
                            padding: 10,
                            callbacks: {
                                label: function (context) {
                                    const val = context.raw || 0;
                                    const total = custData.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ` ₱${val.toLocaleString('en-US', { minimumFractionDigits: 2 })} (${pct}%)`;
                                }
                            }
                        }
                    }
                }
            });
        }
    }

    // 6. TAB SWITCHER

    window.switchReportTab = function (tabKey) {
        const tabBtnMonthly = document.getElementById('tabBtnMonthly');
        const tabBtnCustomer = document.getElementById('tabBtnCustomer');
        const paneMonthly = document.getElementById('paneMonthly');
        const paneCustomer = document.getElementById('paneCustomer');

        if (tabKey === 'monthly') {
            tabBtnMonthly.classList.add('active');
            tabBtnCustomer.classList.remove('active');
            paneMonthly.classList.add('active');
            paneCustomer.classList.remove('active');
        } else {
            tabBtnCustomer.classList.add('active');
            tabBtnMonthly.classList.remove('active');
            paneCustomer.classList.add('active');
            paneMonthly.classList.remove('active');
        }
        if (window.lucide) window.lucide.createIcons();
    };

    // 7. DRILLDOWN MODAL

    window.openMonthDrilldown = function (ym, monthLabel) {
        const items = currentFilteredInvoices.filter(inv => inv.si_txn_date.startsWith(ym));
        renderDrilldownTable(`Monthly Invoices: ${monthLabel}`, `Consolidated posted invoices for ${monthLabel}`, items);
    };

    window.openCustomerDrilldown = function (customerName) {
        const items = currentFilteredInvoices.filter(inv => inv.customer === customerName);
        renderDrilldownTable(`Customer Invoices: ${customerName}`, `Consolidated posted billings for ${customerName}`, items);
    };

    function renderDrilldownTable(title, sub, items) {
        drilldownModalTitle.textContent = title;
        drilldownModalSub.textContent = sub;
        drilldownTableBody.innerHTML = '';

        if (items.length === 0) {
            drilldownTableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted" style="padding: 20px;">No posted transactions found</td></tr>';
        } else {
            items.forEach(inv => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="font-code font-bold">${escapeHtml(inv.si_number)}</td>
                    <td class="font-numeric">${escapeHtml(inv.si_txn_date)}</td>
                    <td class="font-medium text-dark">${escapeHtml(inv.customer)}</td>
                    <td>${escapeHtml(inv.branch)}</td>
                    <td class="text-right font-numeric font-bold">${fmtCurrency(inv.gross_amount)}</td>
                    <td class="text-right font-numeric" style="color: #dc2626;">-${fmtCurrency(inv.discount_amount)}</td>
                    <td class="text-right font-numeric font-bold" style="color: #2563eb;">${fmtCurrency(inv.net_amount)}</td>
                    <td class="text-right font-numeric" style="color: ${inv.balance > 0 ? '#d97706' : '#059669'};">${fmtCurrency(inv.balance)}</td>
                    <td class="text-center"><span class="health-pill health-pill-healthy">${escapeHtml(inv.status)}</span></td>
                `;
                drilldownTableBody.appendChild(tr);
            });
        }

        drilldownModalBackdrop.classList.add('show');
    }

    window.closeDrilldownModal = function () {
        drilldownModalBackdrop.classList.remove('show');
    };

    // 8. EXPORT & REFRESH ACTIONS

    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            setTimeout(() => {
                if (refreshIcon) refreshIcon.classList.remove('spin-anim');
                generateReport();
            }, 450);
        });
    }

    if (btnExportToggle) {
        btnExportToggle.addEventListener('click', function (e) {
            e.stopPropagation();
            exportMenu.classList.toggle('show');
        });
    }

    document.addEventListener('click', function () {
        if (exportMenu) exportMenu.classList.remove('show');
    });

    window.exportSalesReport = function (format) {
        if (exportMenu) exportMenu.classList.remove('show');

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csv = 'SI#,Txn Date,Customer,Branch,Units Sold,Gross Amount,Discount,Net Sales,Balance,Status\n';
            currentFilteredInvoices.forEach(r => {
                csv += `"${r.si_number}","${r.si_txn_date}","${r.customer}","${r.branch}",${r.units_sold},${r.gross_amount},${r.discount_amount},${r.net_amount},${r.balance},"${r.status}"\n`;
            });
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Performance_Report_${reportDateFrom.value}_to_${reportDateTo.value}.csv`;
            a.click();
            URL.revokeObjectURL(url);
        } else if (format === 'excel') {
            let html = '<table border="1"><tr><th>SI#</th><th>Txn Date</th><th>Customer</th><th>Branch</th><th>Units Sold</th><th>Gross Amount</th><th>Discount</th><th>Net Sales</th><th>Balance</th><th>Status</th></tr>';
            currentFilteredInvoices.forEach(r => {
                html += `<tr><td>${r.si_number}</td><td>${r.si_txn_date}</td><td>${r.customer}</td><td>${r.branch}</td><td>${r.units_sold}</td><td>${r.gross_amount}</td><td>${r.discount_amount}</td><td>${r.net_amount}</td><td>${r.balance}</td><td>${r.status}</td></tr>`;
            });
            html += '</table>';
            const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Sales_Performance_Report_${reportDateFrom.value}_to_${reportDateTo.value}.xls`;
            a.click();
            URL.revokeObjectURL(url);
        }
    };

    // Initial load
    generateReport();

})();
</script>
@endpush

