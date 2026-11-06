@extends('layouts.app')

@section('title', 'Dashboard — Intracode ERP')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Dashboard/Dashboard/dashboard.css') }}">
@endsection

@section('content')
@include('components.sidenav')

<div class="main-content">
    <header class="top-bar">
        <div class="breadcrumb-trail">
            <span class="bc-item">Overview</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Dashboard</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" placeholder="Search orders, SKU, output, vendors..." autocomplete="off">
            </div>
        </div>
    </header>

    <div class="dashboard-container">
        <div class="page-header-row">
            <h1 class="page-title">Dashboard</h1>

            <div class="action-cluster">
                <button type="button" class="btn-outline" onclick="window.location.reload()">
                    <span>Refresh</span>
                </button>
                <button type="button" class="btn-outline" onclick="window.print()">
                    <span>Print Report</span>
                </button>
                <a href="{{ route('salesorder') }}" class="btn-primary">
                    <i data-lucide="plus" class="btn-icon"></i>
                    <span>New Sales Order</span>
                </a>
            </div>
        </div>

        <div class="filter-bar">
            <div class="filter-pill">
                <span>Period:</span>
                <strong>This Month (MTD)</strong>
                <i data-lucide="chevron-down" class="filter-chevron"></i>
            </div>
            <div class="filter-pill">
                <span>Department:</span>
                <strong>All Modules</strong>
                <i data-lucide="chevron-down" class="filter-chevron"></i>
            </div>
            <div class="filter-pill">
                <span>Plant Location:</span>
                <strong>Main Production Plant</strong>
                <i data-lucide="chevron-down" class="filter-chevron"></i>
            </div>
            <button type="button" class="btn-filter-reset" id="btnResetFilters" onclick="window.location.reload()">
                <i data-lucide="rotate-ccw"></i>
                <span>Reset Filters</span>
            </button>
        </div>

        <div class="erp-card analytics-graph-card">
            <div class="card-header-row">
                <div>
                    <div class="card-title">Revenue Trend</div>
                    <div class="card-subtitle">Monthly revenue progression</div>
                </div>
            </div>

            <div class="graph-kpi-headline">
                <div class="headline-primary">
                    <span class="headline-label">Current Revenue MTD</span>
                    <div class="headline-value-group">
                        <span class="headline-value font-numeric">₱ 14,820,500.00</span>
                        <span class="status-badge success">
                            <span class="status-dot"></span>
                            <span>94.2% Quota</span>
                        </span>
                    </div>
                </div>
                <div class="headline-sub-info">
                    <span class="trend-pill-positive">+12.4% vs last month</span>
                </div>
            </div>

            <div class="chart-canvas-container">
                <svg viewBox="0 0 940 210" class="performance-chart-svg" preserveAspectRatio="none">
                    <line x1="55" y1="20" x2="930" y2="20" class="chart-grid-line" />
                    <text x="45" y="24" class="chart-axis-label">₱16M</text>

                    <line x1="55" y1="60" x2="930" y2="60" class="chart-grid-line" />
                    <text x="45" y="64" class="chart-axis-label">₱12M</text>

                    <line x1="55" y1="100" x2="930" y2="100" class="chart-grid-line" />
                    <text x="45" y="104" class="chart-axis-label">₱8M</text>

                    <line x1="55" y1="140" x2="930" y2="140" class="chart-grid-line" />
                    <text x="45" y="144" class="chart-axis-label">₱4M</text>

                    <line x1="55" y1="180" x2="930" y2="180" class="chart-grid-base" />
                    <text x="45" y="184" class="chart-axis-label">₱0</text>

                    <line x1="55" y1="30" x2="930" y2="30" class="chart-quota-line" />

                    <rect x="75" y="78" width="42" height="102" rx="4" class="chart-bar" data-month="January" data-val="₱ 10,200,000.00" data-pct="68.0%" />
                    <text x="96" y="198" class="chart-col-label">Jan</text>

                    <rect x="153" y="66" width="42" height="114" rx="4" class="chart-bar" data-month="February" data-val="₱ 11,400,000.00" data-pct="76.0%" />
                    <text x="174" y="198" class="chart-col-label">Feb</text>

                    <rect x="231" y="52" width="42" height="128" rx="4" class="chart-bar" data-month="March" data-val="₱ 12,800,000.00" data-pct="85.3%" />
                    <text x="252" y="198" class="chart-col-label">Mar</text>

                    <rect x="309" y="49" width="42" height="131" rx="4" class="chart-bar" data-month="April" data-val="₱ 13,100,000.00" data-pct="87.3%" />
                    <text x="330" y="198" class="chart-col-label">Apr</text>

                    <rect x="387" y="54" width="42" height="126" rx="4" class="chart-bar" data-month="May" data-val="₱ 12,600,000.00" data-pct="84.0%" />
                    <text x="408" y="198" class="chart-col-label">May</text>

                    <rect x="465" y="41" width="42" height="139" rx="4" class="chart-bar" data-month="June" data-val="₱ 13,900,000.00" data-pct="92.7%" />
                    <text x="486" y="198" class="chart-col-label">Jun</text>

                    <rect x="543" y="45" width="42" height="135" rx="4" class="chart-bar" data-month="July" data-val="₱ 13,500,000.00" data-pct="90.0%" />
                    <text x="564" y="198" class="chart-col-label">Jul</text>

                    <rect x="621" y="38" width="42" height="142" rx="4" class="chart-bar" data-month="August" data-val="₱ 14,200,000.00" data-pct="94.7%" />
                    <text x="642" y="198" class="chart-col-label">Aug</text>

                    <rect x="699" y="42" width="42" height="138" rx="4" class="chart-bar" data-month="September" data-val="₱ 13,800,000.00" data-pct="92.0%" />
                    <text x="720" y="198" class="chart-col-label">Sep</text>

                    <rect x="777" y="36" width="42" height="144" rx="4" class="chart-bar" data-month="October" data-val="₱ 14,400,000.00" data-pct="96.0%" />
                    <text x="798" y="198" class="chart-col-label">Oct</text>

                    <rect x="855" y="32" width="42" height="148" rx="4" class="chart-bar chart-bar-active" data-month="November (MTD)" data-val="₱ 14,820,500.00" data-pct="94.2%" />
                    <text x="876" y="198" class="chart-col-label chart-col-active">Nov (MTD)</text>
                </svg>

                <div class="chart-tooltip" id="chartTooltip">
                    <div class="tooltip-month" id="ttMonth">November (MTD)</div>
                    <div class="tooltip-value font-numeric" id="ttVal">₱ 14,820,500.00</div>
                    <div class="tooltip-meta" id="ttPct">94.2% of ₱ 15M Quota</div>
                </div>
            </div>
        </div>

        <div class="erp-card dept-overview-card">
            <div class="card-header-row">
                <div>
                    <div class="card-title">Department Operational Health</div>
                    <div class="card-subtitle">Performance breakdown across core modules</div>
                </div>
            </div>

            <div class="dept-summary-scroll">
                <div class="dept-summary-grid">
                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Sales & Commercial</span>
                            <span class="status-badge success">
                                <span class="status-dot"></span>
                                <span>94.2% Quota</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">128 Orders</span>
                        </div>
                        <div class="dept-footer">
                            <span>₱ 14.82M · 96.1% fulfillment</span>
                            <a href="{{ route('salesorder') }}" class="dept-link">View Sales →</a>
                        </div>
                    </div>

                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Production & Assembly</span>
                            <span class="status-badge success">
                                <span class="status-dot"></span>
                                <span>97.8% Yield</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">94,850 Units</span>
                        </div>
                        <div class="dept-footer">
                            <span>SMT Line active · 1.5% scrap</span>
                            <a href="{{ route('production.output.index') }}" class="dept-link">View Production →</a>
                        </div>
                    </div>

                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Warehouse & Logistics</span>
                            <span class="status-badge warn">
                                <span class="status-dot"></span>
                                <span>2 Stock Alerts</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">28,218 Units</span>
                        </div>
                        <div class="dept-footer">
                            <span>42 bins · PET Film threshold</span>
                            <a href="{{ route('warehouse.supplies.stock') }}" class="dept-link">View Warehouse →</a>
                        </div>
                    </div>

                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Purchasing & Vendors</span>
                            <span class="status-badge neutral">
                                <span class="status-dot"></span>
                                <span>6 Open POs</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">₱ 3,190,400.00</span>
                        </div>
                        <div class="dept-footer">
                            <span>8 suppliers · Sumitomo shipment</span>
                            <a href="{{ route('purchasing.po.index') }}" class="dept-link">View Purchasing →</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="erp-card">
            <div class="card-header-row">
                <div>
                    <div class="card-title">Recent Enterprise Transactions</div>
                    <div class="card-subtitle">Synchronized activity ledger across sales, production, and procurement</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="erp-data-table">
                    <thead>
                        <tr>
                            <th>Transaction Ref</th>
                            <th>Module</th>
                            <th>Counterparty / Entity</th>
                            <th>Date</th>
                            <th class="text-right">Valuation / Qty</th>
                            <th>Status</th>
                            <th class="text-right" style="width: 50px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="font-weight: 500; color: #2563eb;">SO-2026-0041</td>
                            <td>Sales Order</td>
                            <td>Sumitomo Wiring Systems Corp</td>
                            <td class="font-numeric">2026-11-06</td>
                            <td class="font-numeric text-right">₱ 1,480,000.00</td>
                            <td>
                                <span class="status-badge success">
                                    <span class="status-dot"></span>
                                    <span>Posted</span>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-menu-container">
                                    <button type="button" class="action-menu-trigger" title="Actions">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>
                                    <div class="action-menu-dropdown">
                                        <a href="{{ route('salesorder') }}" class="action-dropdown-item">View Order</a>
                                        <button type="button" class="action-dropdown-item" onclick="window.print()">Print Document</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500; color: #2563eb;">OUT-2026-0001</td>
                            <td>Production Output</td>
                            <td>Extruder Line A (SMT 01)</td>
                            <td class="font-numeric">2026-11-06</td>
                            <td class="font-numeric text-right">24,700 Units</td>
                            <td>
                                <span class="status-badge success">
                                    <span class="status-dot"></span>
                                    <span>Passed QA</span>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-menu-container">
                                    <button type="button" class="action-menu-trigger" title="Actions">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>
                                    <div class="action-menu-dropdown">
                                        <a href="{{ route('production.output.index') }}" class="action-dropdown-item">View Run</a>
                                        <button type="button" class="action-dropdown-item" onclick="window.print()">Print Document</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500; color: #2563eb;">PO-2026-0012</td>
                            <td>Purchase Order</td>
                            <td>Nippon Seisen Co., Ltd.</td>
                            <td class="font-numeric">2026-11-05</td>
                            <td class="font-numeric text-right">₱ 820,000.00</td>
                            <td>
                                <span class="status-badge warn">
                                    <span class="status-dot"></span>
                                    <span>In Transit</span>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-menu-container">
                                    <button type="button" class="action-menu-trigger" title="Actions">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>
                                    <div class="action-menu-dropdown">
                                        <a href="{{ route('purchasing.po.index') }}" class="action-dropdown-item">View PO</a>
                                        <button type="button" class="action-dropdown-item" onclick="window.print()">Print Document</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td style="font-weight: 500; color: #2563eb;">MAT-PET-004</td>
                            <td>Stock Adjustment</td>
                            <td>Rack B-01 / Bin 03</td>
                            <td class="font-numeric">2026-11-04</td>
                            <td class="font-numeric text-right">2,800 Rolls</td>
                            <td>
                                <span class="status-badge warn">
                                    <span class="status-dot"></span>
                                    <span>Reorder Level</span>
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="action-menu-container">
                                    <button type="button" class="action-menu-trigger" title="Actions">
                                        <i data-lucide="more-horizontal"></i>
                                    </button>
                                    <div class="action-menu-dropdown">
                                        <a href="{{ route('warehouse.supplies.stock') }}" class="action-dropdown-item">View Bin</a>
                                        <a href="{{ route('purchasing.pr.index') }}" class="action-dropdown-item">Create PR</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const actionContainers = document.querySelectorAll('.action-menu-container');

    actionContainers.forEach(function (container) {
        const trigger = container.querySelector('.action-menu-trigger');
        if (!trigger) return;

        trigger.addEventListener('click', function (e) {
            e.stopPropagation();
            const isOpen = container.classList.contains('open');

            actionContainers.forEach(function (c) {
                c.classList.remove('open');
                c.classList.remove('dropup');
            });

            if (!isOpen) {
                const rect = trigger.getBoundingClientRect();
                const dropdownHeight = 90;
                const spaceBelow = window.innerHeight - rect.bottom;

                if (spaceBelow < dropdownHeight && rect.top > dropdownHeight) {
                    container.classList.add('dropup');
                }
                container.classList.add('open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action-menu-container')) {
            actionContainers.forEach(function (c) {
                c.classList.remove('open');
                c.classList.remove('dropup');
            });
        }
    });

    const chartBars = document.querySelectorAll('.chart-bar');
    const tooltip = document.getElementById('chartTooltip');
    const ttMonth = document.getElementById('ttMonth');
    const ttVal = document.getElementById('ttVal');
    const ttPct = document.getElementById('ttPct');
    const chartContainer = document.querySelector('.chart-canvas-container');

    if (tooltip && chartContainer) {
        chartBars.forEach(function (bar) {
            bar.addEventListener('mouseenter', function () {
                const month = bar.getAttribute('data-month');
                const val = bar.getAttribute('data-val');
                const pct = bar.getAttribute('data-pct');

                if (ttMonth) ttMonth.textContent = month;
                if (ttVal) ttVal.textContent = val;
                if (ttPct) ttPct.textContent = pct + ' of ₱ 15M Quota';

                const barRect = bar.getBoundingClientRect();
                const containerRect = chartContainer.getBoundingClientRect();

                tooltip.style.left = (barRect.left + barRect.width / 2 - containerRect.left) + 'px';
                tooltip.style.top = (barRect.top - containerRect.top) + 'px';
                tooltip.style.display = 'block';
            });

            bar.addEventListener('mouseleave', function () {
                tooltip.style.display = 'none';
            });
        });
    }

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});
</script>
@endpush
