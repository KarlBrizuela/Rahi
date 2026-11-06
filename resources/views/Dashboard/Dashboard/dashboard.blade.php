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
            <button type="button" class="filter-reset-btn" onclick="window.location.reload()">Reset Filters</button>
        </div>

        <div class="kpi-strip">
            <div class="kpi-cell">
                <div class="kpi-header">
                    <span class="kpi-title">Revenue (MTD)</span>
                    <span class="kpi-trend">+12.4% vs last mo.</span>
                </div>
                <div class="kpi-value">₱ 14,820,500.00</div>
                <div class="kpi-meta">
                    <span class="status-badge success">
                        <span class="status-dot"></span>
                        <span>On target</span>
                    </span>
                    <span>94.2% quota</span>
                </div>
            </div>

            <div class="kpi-cell">
                <div class="kpi-header">
                    <span class="kpi-title">Active Production</span>
                    <span class="kpi-trend">97.8% yield</span>
                </div>
                <div class="kpi-value font-numeric">18 Runs</div>
                <div class="kpi-meta">
                    <span class="status-badge success">
                        <span class="status-dot"></span>
                        <span>Operational</span>
                    </span>
                    <span class="font-numeric">94,850 good units</span>
                </div>
            </div>

            <div class="kpi-cell">
                <div class="kpi-header">
                    <span class="kpi-title">Warehouse Inventory</span>
                    <span class="kpi-trend neutral">42 Active Bins</span>
                </div>
                <div class="kpi-value">₱ 8,450,200.00</div>
                <div class="kpi-meta">
                    <span class="status-badge warn">
                        <span class="status-dot"></span>
                        <span>Reorder alert</span>
                    </span>
                    <span>2 items critical</span>
                </div>
            </div>

            <div class="kpi-cell">
                <div class="kpi-header">
                    <span class="kpi-title">Open Procurement</span>
                    <span class="kpi-trend neutral">6 POs in transit</span>
                </div>
                <div class="kpi-value font-numeric">₱ 3,190,400.00</div>
                <div class="kpi-meta">
                    <span class="status-badge neutral">
                        <span class="status-dot"></span>
                        <span>Committed</span>
                    </span>
                    <span>Avg delivery 3.2d</span>
                </div>
            </div>
        </div>

        <div class="dashboard-middle-grid">
            <div class="erp-card">
                <div class="card-header-row">
                    <div class="card-title">Department Operational Health</div>
                    <div class="card-subtitle">Performance breakdown across core modules</div>
                </div>

                <div class="dept-summary-grid">
                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Sales & Commercial</span>
                            <span class="status-badge success">
                                <span class="status-dot"></span>
                                <span>Active</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">128 Orders</span>
                            <span class="dept-stat-sub">₱ 14.82M Total</span>
                        </div>
                        <div class="dept-footer">
                            <span>Fulfillment rate: 96.1%</span>
                            <a href="{{ route('salesorder') }}" class="dept-link">View Sales →</a>
                        </div>
                    </div>

                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Production & Assembly</span>
                            <span class="status-badge success">
                                <span class="status-dot"></span>
                                <span>Operational</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">94,850 Units</span>
                            <span class="dept-stat-sub">Scrap: 1.5%</span>
                        </div>
                        <div class="dept-footer">
                            <span>SMT Line 01 & 02 active</span>
                            <a href="{{ route('production.output.index') }}" class="dept-link">View Production →</a>
                        </div>
                    </div>

                    <div class="dept-summary-item">
                        <div class="dept-header">
                            <span class="dept-title">Warehouse & Logistics</span>
                            <span class="status-badge warn">
                                <span class="status-dot"></span>
                                <span>Stock Warning</span>
                            </span>
                        </div>
                        <div class="dept-stat-row">
                            <span class="dept-stat-main font-numeric">28,218 Units</span>
                            <span class="dept-stat-sub">42 Storage Bins</span>
                        </div>
                        <div class="dept-footer">
                            <span>PET Film near threshold</span>
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
                            <span class="dept-stat-main font-numeric">₱ 3.19M</span>
                            <span class="dept-stat-sub">8 Suppliers</span>
                        </div>
                        <div class="dept-footer">
                            <span>Sumitomo order confirmed</span>
                            <a href="{{ route('purchasing.po.index') }}" class="dept-link">View Purchasing →</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="erp-card">
                <div class="card-header-row">
                    <div class="card-title">Operational Alerts</div>
                    <span class="status-badge warn">
                        <span class="status-dot"></span>
                        <span>3 Pending</span>
                    </span>
                </div>

                <div class="feed-list">
                    <div class="feed-item">
                        <div class="feed-header">
                            <span class="feed-title">Safety Stock Threshold</span>
                            <span class="feed-time">14m ago</span>
                        </div>
                        <p class="feed-desc">PET Insulation Film 0.05mm is below safety replenishment level (2,800 units).</p>
                        <a href="{{ route('purchasing.pr.index') }}" class="feed-link">Create Purchase Request →</a>
                    </div>

                    <div class="feed-item">
                        <div class="feed-header">
                            <span class="feed-title">Lot Inspection Completed</span>
                            <span class="feed-time">1h ago</span>
                        </div>
                        <p class="feed-desc">Production Output WO-2026-0814 ready for finished goods transfer.</p>
                        <a href="{{ route('production.fgts.index') }}" class="feed-link">Generate FGTS →</a>
                    </div>

                    <div class="feed-item">
                        <div class="feed-header">
                            <span class="feed-title">Order Engineering Review</span>
                            <span class="feed-time">3h ago</span>
                        </div>
                        <p class="feed-desc">SO-2026-0044 awaiting dimensional validation from technical team.</p>
                        <a href="{{ route('salesorder') }}" class="feed-link">Review Order →</a>
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

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});
</script>
@endpush
