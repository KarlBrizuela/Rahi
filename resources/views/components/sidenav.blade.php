<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">

<aside class="sidenav">

    <div class="header-nav">
        <div class="logo-nav">
            <img src="{{ asset('fav/fav.png') }}" alt="Logo">
            <div class="brand-text">
                <h3>Intracode ERP</h3>
            </div>
        </div>

        <button type="button" class="collapse-btn" title="Toggle Sidebar">
            <i data-lucide="panel-left"></i>
        </button>
    </div>

    <div class="workspace-card-wrapper filebtn">
        <button type="button"
                class="workspace-card filebtn-selection"
                id="fileDropdownBtn"
                aria-expanded="false">
            <div class="workspace-avatar">
                <i data-lucide="file-text"></i>
            </div>
            <div class="workspace-info file-text">
                <div class="workspace-name">File</div>
            </div>
            <i data-lucide="chevron-down" class="workspace-chevron dropdown-arrow"></i>
        </button>

        <div class="file-dropdown" id="fileDropdown">
            <form method="POST">
                @csrf
                <button type="submit" class="dropdown-item logout-item">
                    <i data-lucide="log-out"></i>
                    <span>Logout</span>
                </button>
            </form>

            <button type="button"
                    class="dropdown-item exit-item"
                    onclick="window.close()">
                <i data-lucide="power"></i>
                <span>Exit</span>
            </button>
        </div>
    </div>

    <nav class="nav-content">

        <div class="nav-section-title">Overview</div>

        <div class="nav-single-wrapper">
            <a href="{{ route('dashboard') }}" class="nav-single-link {{ request()->is('dashboard*') || request()->is('overview*') ? 'active' : '' }}" title="Dashboard">
                <span class="item-left">
                    <i data-lucide="layout-dashboard" class="item-icon"></i>
                    <span class="item-label">Dashboard</span>
                </span>
            </a>
            <div class="nav-single-flyout">Dashboard</div>
        </div>

        <div class="nav-section-title">Revenue & Operations</div>

        <div class="nav-group {{ request()->is('Sales-Order*') || request()->is('sales*') ? 'open' : '' }}">
            <button type="button" class="nav-group-trigger">
                <span class="item-left">
                    <i data-lucide="trending-up" class="item-icon"></i>
                    <span class="item-label">Sales</span>
                </span>
                <i data-lucide="chevron-right" class="group-arrow"></i>
            </button>
            <div class="nav-sublist">
                <div class="flyout-header">Sales</div>
                <a href="{{ route('customer.index') }}" class="sub-link {{ request()->is('sales/customer*') ? 'active' : '' }}">Customer</a>
                <a href="{{ route('product.index') }}" class="sub-link {{ request()->is('sales/product*') ? 'active' : '' }}">Product</a>
                <a href="{{ route('salesorder') }}" class="sub-link {{ request()->is('Sales-Order*') || request()->is('sales/sales-order*') || request()->is('sales-order*') ? 'active' : '' }}">Sales Order</a>
                <a href="{{ route('invoice.index') }}" class="sub-link {{ request()->is('sales/invoice*') ? 'active' : '' }}">Invoice</a>
                <a href="{{ route('salesreport.index') }}" class="sub-link {{ request()->is('sales/sales-report*') ? 'active' : '' }}">Sales Report</a>
                <a href="{{ route('deliveryreceipt.index') }}" class="sub-link {{ request()->is('sales/delivery-receipt*') ? 'active' : '' }}">Delivery Receipt</a>
                <a href="{{ route('shippinginvoice.index') }}" class="sub-link {{ request()->is('sales/shipping-invoice*') ? 'active' : '' }}">Shipping Invoice</a>
                <a href="{{ route('mrp.index') }}" class="sub-link {{ request()->is('sales/material-requirement-plan*') ? 'active' : '' }}">Material Requirement Plan</a>
            </div>
        </div>

        <div class="nav-group {{ request()->is('production*') ? 'open' : '' }}">
            <button type="button" class="nav-group-trigger">
                <span class="item-left">
                    <i data-lucide="factory" class="item-icon"></i>
                    <span class="item-label">Production</span>
                </span>
                <i data-lucide="chevron-right" class="group-arrow"></i>
            </button>
            <div class="nav-sublist">
                <div class="flyout-header">Production</div>
                <a href="{{ route('production.output.index') }}" class="sub-link {{ request()->is('production/output') ? 'active' : '' }}">Production Output</a>
                <a href="{{ route('production.outputinv.index') }}" class="sub-link {{ request()->is('production/output-inventory*') ? 'active' : '' }}">Production Output Inventory</a>
                <a href="{{ route('production.fgts.index') }}" class="sub-link {{ request()->is('production/fgts*') ? 'active' : '' }}">FGTS</a>
            </div>
        </div>

        <div class="nav-section-title">Supply Chain</div>

        <div class="nav-group {{ request()->is('warehouse*') ? 'open' : '' }}">
            <button type="button" class="nav-group-trigger">
                <span class="item-left">
                    <i data-lucide="warehouse" class="item-icon"></i>
                    <span class="item-label">Warehouse</span>
                </span>
                <i data-lucide="chevron-right" class="group-arrow"></i>
            </button>
            <div class="nav-sublist">
                <div class="flyout-header">Warehouse</div>

                <div class="nav-nested-group {{ request()->is('warehouse/inventory*') ? 'open' : '' }}">
                    <button type="button" class="nested-trigger">
                        <span>Inventory</span>
                        <i data-lucide="chevron-right" class="nested-arrow"></i>
                    </button>
                    <div class="nested-sublist">
                        <a href="{{ route('warehouse.inventory.fg') }}" class="sub-link {{ request()->routeIs('warehouse.inventory.fg') ? 'active' : '' }}">FG</a>
                        <a href="{{ route('warehouse.inventory.supplies') }}" class="sub-link {{ request()->routeIs('warehouse.inventory.supplies') ? 'active' : '' }}">Supplies/Raw Matl's</a>
                    </div>
                </div>

                <div class="nav-nested-group {{ request()->is('warehouse/finished-goods*') ? 'open' : '' }}">
                    <button type="button" class="nested-trigger">
                        <span>Finished Goods</span>
                        <i data-lucide="chevron-right" class="nested-arrow"></i>
                    </button>
                    <div class="nested-sublist">
                        <a href="{{ route('warehouse.fg.dts') }}" class="sub-link {{ request()->routeIs('warehouse.fg.dts') ? 'active' : '' }}">Delivery Transfer Slip</a>
                        <a href="{{ route('warehouse.fg.adjustment') }}" class="sub-link {{ request()->routeIs('warehouse.fg.adjustment') ? 'active' : '' }}">FG Adjustment</a>
                        <a href="{{ route('warehouse.fg.receiving') }}" class="sub-link {{ request()->routeIs('warehouse.fg.receiving') ? 'active' : '' }}">FG Receiving</a>
                        <a href="{{ route('warehouse.fg.stock') }}" class="sub-link {{ request()->routeIs('warehouse.fg.stock') ? 'active' : '' }}">FG Stock</a>
                    </div>
                </div>

                <div class="nav-nested-group {{ request()->is('warehouse/supplies*') ? 'open' : '' }}">
                    <button type="button" class="nested-trigger">
                        <span>Supplies/Raw Matl’s</span>
                        <i data-lucide="chevron-right" class="nested-arrow"></i>
                    </button>
                    <div class="nested-sublist">
                        <a href="{{ route('warehouse.supplies.adjustment') }}" class="sub-link {{ request()->routeIs('warehouse.supplies.adjustment') ? 'active' : '' }}">Supplies Adjustment</a>
                        <a href="{{ route('warehouse.supplies.issuance') }}" class="sub-link {{ request()->routeIs('warehouse.supplies.issuance') ? 'active' : '' }}">Supplies Issuance</a>
                        <a href="{{ route('warehouse.supplies.return') }}" class="sub-link {{ request()->routeIs('warehouse.supplies.return') ? 'active' : '' }}">Supplies Return</a>
                        <a href="{{ route('warehouse.supplies.stock') }}" class="sub-link {{ request()->routeIs('warehouse.supplies.stock') ? 'active' : '' }}">Supplies Stock</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="nav-group {{ request()->is('purchasing*') ? 'open' : '' }}">
            <button type="button" class="nav-group-trigger">
                <span class="item-left">
                    <i data-lucide="shopping-cart" class="item-icon"></i>
                    <span class="item-label">Purchasing</span>
                </span>
                <i data-lucide="chevron-right" class="group-arrow"></i>
            </button>
            <div class="nav-sublist">
                <div class="flyout-header">Purchasing</div>
                <a href="{{ route('purchasing.pr.index') }}" class="sub-link {{ request()->routeIs('purchasing.pr.index') ? 'active' : '' }}">Purchase Request</a>
                <a href="{{ route('purchasing.po.index') }}" class="sub-link {{ request()->routeIs('purchasing.po.index') ? 'active' : '' }}">Purchase Order</a>
                <a href="{{ route('purchasing.supplier.index') }}" class="sub-link {{ request()->routeIs('purchasing.supplier.index') ? 'active' : '' }}">Supplier</a>
                <a href="{{ route('purchasing.supplies.index') }}" class="sub-link {{ request()->routeIs('purchasing.supplies.index') ? 'active' : '' }}">Supplies</a>
            </div>
        </div>

        <div class="nav-section-title">System & Configuration</div>

        <div class="nav-group {{ request()->is('configuration*') ? 'open' : '' }}">
            <button type="button" class="nav-group-trigger">
                <span class="item-left">
                    <i data-lucide="sliders" class="item-icon"></i>
                    <span class="item-label">Configuration</span>
                </span>
                <i data-lucide="chevron-right" class="group-arrow"></i>
            </button>
            <div class="nav-sublist">
                <div class="flyout-header">Configuration</div>
                <a href="{{ route('atc.index') }}" class="sub-link {{ request()->is('configuration/atc*') ? 'active' : '' }}">ATC</a>
                <a href="{{ route('audittrail.index') }}" class="sub-link {{ request()->is('configuration/audit-trail*') ? 'active' : '' }}">Audit Trail</a>
                <a href="{{ route('bank.index') }}" class="sub-link {{ request()->is('configuration/bank*') ? 'active' : '' }}">Bank</a>
                <a href="{{ route('branch.index') }}" class="sub-link {{ request()->is('configuration/branch*') ? 'active' : '' }}">Branch</a>
                <a href="{{ route('country.index') }}" class="sub-link {{ request()->is('configuration/country*') ? 'active' : '' }}">Country</a>
                <a href="{{ route('currency.index') }}" class="sub-link {{ request()->is('configuration/currency*') ? 'active' : '' }}">Currency</a>
                <a href="{{ route('customercategory.index') }}" class="sub-link {{ request()->is('configuration/customer-category*') ? 'active' : '' }}">Customer Category</a>
                <a href="{{ route('paymentterm.index') }}" class="sub-link {{ request()->is('configuration/payment-term*') ? 'active' : '' }}">Payment Terms</a>
                <a href="{{ route('productcategory.index') }}" class="sub-link {{ request()->is('configuration/product-category*') ? 'active' : '' }}">Product Category</a>
                <a href="{{ route('suppliescategory.index') }}" class="sub-link {{ request()->is('configuration/supplies-category*') ? 'active' : '' }}">Supplies Category</a>
                <a href="{{ route('unitofproduct.index') }}" class="sub-link {{ request()->is('configuration/unit-of-product*') ? 'active' : '' }}">Unit of Product</a>
                <a href="{{ route('user.index') }}" class="sub-link {{ request()->is('configuration/user*') ? 'active' : '' }}">User</a>
                <a href="{{ route('vat.index') }}" class="sub-link {{ request()->is('configuration/vat*') ? 'active' : '' }}">VAT</a>
                <a href="{{ route('warehouse.index') }}" class="sub-link {{ request()->is('configuration/warehouse*') && !request()->is('configuration/warehouse-location*') ? 'active' : '' }}">Warehouse</a>
                <a href="{{ route('warehouselocation.index') }}" class="sub-link {{ request()->is('configuration/warehouse-location*') ? 'active' : '' }}">Warehouse Location</a>
            </div>
        </div>

    </nav>

    <div class="sidenav-system-footer">

        <div class="user-profile-card">
            <div class="user-avatar">
                <span>{{ strtoupper(substr(Auth::user()->name ?? 'AD', 0, 2)) }}</span>
            </div>
            <div class="user-meta">
                <span class="user-name">{{ Auth::user()->name ?? 'Administrator' }}</span>
                <span class="user-role">{{ Auth::user()->role ?? 'System Admin' }}</span>
            </div>
            <div class="user-status-dot" title="Online"></div>
        </div>

        <div class="software-meta-row">
            <span class="provider-label">Powered by: Intracode IT Solutions</span>
            <span class="version-badge">v1.0.0</span>
        </div>
    </div>

</aside>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sidenav = document.querySelector('.sidenav');
    const collapseBtn = document.querySelector('.collapse-btn');

    function closeAllFlyouts() {
        document.querySelectorAll('.nav-group.flyout-open').forEach(g => {
            g.classList.remove('flyout-open', 'flyout-bottom');
        });
    }

    function updateFlyoutPosition(group) {
        const rect = group.getBoundingClientRect();
        const flyout = group.querySelector('.nav-sublist');
        const height = flyout ? Math.min(flyout.scrollHeight, 440) : 340;
        if (rect.top + height > window.innerHeight && rect.top > 160) {
            group.classList.add('flyout-bottom');
        } else {
            group.classList.remove('flyout-bottom');
        }
    }

    if (collapseBtn && sidenav) {
        const isCollapsedPref = localStorage.getItem('intracode_sidebar_collapsed') === 'true';
        if (isCollapsedPref) {
            sidenav.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }

        collapseBtn.addEventListener('click', function (e) {
            e.preventDefault();
            closeAllFlyouts();
            const isNowCollapsed = sidenav.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed', isNowCollapsed);
            localStorage.setItem('intracode_sidebar_collapsed', isNowCollapsed);

            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });
    }

    const fileDropdownBtn = document.getElementById('fileDropdownBtn');
    const fileDropdown = document.getElementById('fileDropdown');
    const workspaceWrapper = document.querySelector('.workspace-card-wrapper');

    if (fileDropdownBtn && fileDropdown) {
        fileDropdownBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            const isOpen = fileDropdown.classList.toggle('show');
            fileDropdownBtn.setAttribute('aria-expanded', isOpen);
            if (workspaceWrapper) {
                workspaceWrapper.classList.toggle('active', isOpen);
            }
        });

        document.addEventListener('click', function (event) {
            if (!fileDropdown.contains(event.target) && !fileDropdownBtn.contains(event.target)) {
                fileDropdown.classList.remove('show');
                fileDropdownBtn.setAttribute('aria-expanded', 'false');
                if (workspaceWrapper) {
                    workspaceWrapper.classList.remove('active');
                }
            }
        });
    }

    document.querySelectorAll('.nav-group').forEach(function (group) {
        group.addEventListener('mouseenter', function () {
            if (sidenav && sidenav.classList.contains('collapsed')) {
                updateFlyoutPosition(this);
            }
        });
    });

    document.querySelectorAll('.nav-group-trigger').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.nav-group');
            if (!parent) return;

            const isCollapsed = sidenav && sidenav.classList.contains('collapsed');
            if (isCollapsed) {
                const wasOpen = parent.classList.contains('flyout-open');
                closeAllFlyouts();

                if (!wasOpen) {
                    updateFlyoutPosition(parent);
                    parent.classList.add('flyout-open');
                }
            } else {
                parent.classList.toggle('open');
            }
        });
    });

    document.querySelectorAll('.nested-trigger').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const parent = this.closest('.nav-nested-group');
            if (parent) {
                parent.classList.toggle('open');
            }
        });
    });

    document.addEventListener('click', function (e) {
        if (sidenav && sidenav.classList.contains('collapsed')) {
            if (!sidenav.contains(e.target)) {
                closeAllFlyouts();
            }
        }
    });

    const activeSubLink = document.querySelector('.sub-link.active');
    if (activeSubLink) {
        const parentGroup = activeSubLink.closest('.nav-group');
        if (parentGroup) {
            parentGroup.classList.add('open');
        }
        const parentNested = activeSubLink.closest('.nav-nested-group');
        if (parentNested) {
            parentNested.classList.add('open');
        }
    }

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});
</script>
@endpush
