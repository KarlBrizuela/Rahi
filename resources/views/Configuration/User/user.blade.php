@extends('layouts.app')

@section('title', 'User Management & Permissions — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/User/user.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current" id="bcPageTitle">User</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box" id="topSearchBox">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="userSearchInput" placeholder="Search user ID, name, username..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title" id="pageHeaderTitle">User Directory</h1>
        </div>

        <div class="action-cluster" id="headerActionCluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload user records via AJAX">
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
                    <button type="button" class="export-option" onclick="exportUserDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable user directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportUserDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportUserDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create User Button (Opens Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create User</span>
            </button>
        </div>
    </section>

    <!-- 3. OCTAVE ERP TAB STRIP -->
    <nav class="erp-tab-strip no-print">
        <button type="button" class="erp-tab-btn active" data-tab="user-list" id="tabBtnList">
            <i data-lucide="users" class="tab-icon"></i>
            <span>User List</span>
            <span class="tab-badge" id="tabBadgeCount">5</span>
        </button>
        <button type="button" class="erp-tab-btn" data-tab="user-permission" id="tabBtnPerm">
            <i data-lucide="shield-check" class="tab-icon"></i>
            <span>User Permission</span>
        </button>
    </nav>

    <!-- 4. TAB CONTENTS WRAPPER -->
    <main class="page-content-wrapper">

        <!-- ==================================================================
             TAB PANE 1: USER LIST
             ================================================================== -->
        <div class="tab-content-pane active" id="paneUserList">
            <div class="erp-table-panel">

                <!-- Panel Subheader -->
                <div class="table-panel-header no-print">
                    <div class="panel-header-left">
                        <span class="panel-title">System User Accounts</span>
                        <span class="panel-count-badge" id="recordCount">5 Records</span>
                    </div>

                    <div class="panel-header-right">
                        <span class="panel-status-note">Authorized Intracode ERP Access Accounts</span>
                    </div>
                </div>

                <!-- Table Responsive Container -->
                <div class="table-responsive">
                    <table class="erp-table" id="userTable">
                        <thead>
                            <tr>
                                <th style="min-width: 90px;">ID</th>
                                <th style="min-width: 150px;">First Name</th>
                                <th style="min-width: 150px;">Last Name</th>
                                <th style="min-width: 140px;">Middle Name</th>
                                <th style="min-width: 150px;">Username</th>
                                <th style="width: 70px;" class="text-center no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody">
                            <!-- Populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer Pagination / Meta -->
                <div class="table-panel-footer no-print">
                    <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 user entries</span>
                    <div class="pagination-mock">
                        <button type="button" class="page-btn disabled" disabled>Previous</button>
                        <button type="button" class="page-btn active">1</button>
                        <button type="button" class="page-btn disabled" disabled>Next</button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==================================================================
             TAB PANE 2: USER PERMISSION
             ================================================================== -->
        <div class="tab-content-pane" id="paneUserPermission">

            <!-- 1. USER SELECTOR (SEARCHABLE COMBOBOX) & AUTO-FILL PROFILE BAR -->
            <div class="perm-card user-selector-card">
                <div class="perm-card-header">
                    <div class="header-title-wrap">
                        <span class="perm-card-title">Select User for Security Access Configuration</span>
                        <p class="perm-card-subtitle">Search or pick a user account to manage sidebar menu access, CRUD object permissions, credentials, and electronic signature.</p>
                    </div>
                    
                    <!-- Searchable Combobox for Target User -->
                    <div class="selector-dropdown-cluster">
                        <label class="selector-label">Target User:</label>
                        <div class="user-combobox-wrapper" id="userComboboxWrapper">
                            <button type="button" class="user-combobox-trigger" id="userComboboxTrigger">
                                <div class="trigger-selected-display" id="triggerSelectedDisplay">
                                    <i data-lucide="user" class="combobox-lead-icon"></i>
                                    <span class="trigger-placeholder-text">-- Select a User to Configure --</span>
                                </div>
                                <i data-lucide="chevron-down" class="combobox-arrow"></i>
                            </button>
                            
                            <!-- Searchable Dropdown Menu -->
                            <div class="user-combobox-dropdown" id="userComboboxDropdown">
                                <div class="combobox-search-row">
                                    <i data-lucide="search" class="cb-search-icon"></i>
                                    <input type="text" id="comboboxSearchInput" placeholder="Search user ID, name, username..." autocomplete="off">
                                </div>
                                <div class="combobox-items-list" id="comboboxItemsList">
                                    <!-- Populated dynamically with users -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="perm-card-body profile-autofill-body">
                    <!-- User Avatar & Core Profile Details -->
                    <div class="user-perm-hero">
                        <div class="perm-avatar-circle" id="permHeroAvatar">--</div>
                        <div class="perm-hero-meta">
                            <div class="hero-name-row">
                                <h3 class="perm-fullname" id="permHeroFullName">No User Selected</h3>
                                <span class="status-pill status-pill-muted" id="permHeroStatus">No Selection</span>
                            </div>
                            <span class="perm-hero-usercode" id="permHeroUserCode">Please choose a user account from the dropdown above</span>
                        </div>
                    </div>

                    <!-- Readonly Details Grid -->
                    <div class="profile-details-grid">
                        <div class="profile-field-box">
                            <span class="p-field-label">User ID</span>
                            <input type="text" id="permUserId" class="p-field-input font-code" readonly value="—">
                        </div>
                        <div class="profile-field-box">
                            <span class="p-field-label">Username</span>
                            <input type="text" id="permUsername" class="p-field-input font-code" readonly value="—">
                        </div>
                        <div class="profile-field-box">
                            <span class="p-field-label">First Name</span>
                            <input type="text" id="permFirstName" class="p-field-input" readonly value="—">
                        </div>
                        <div class="profile-field-box">
                            <span class="p-field-label">Middle Name</span>
                            <input type="text" id="permMiddleName" class="p-field-input" readonly value="—">
                        </div>
                        <div class="profile-field-box">
                            <span class="p-field-label">Last Name</span>
                            <input type="text" id="permLastName" class="p-field-input" readonly value="—">
                        </div>
                    </div>
                </div>
            </div>

            <!-- UNSELECTED USER NOTICE BANNER (SHOWN WHEN NO USER IS PICKED) -->
            <div class="perm-empty-prompt" id="permEmptyPrompt">
                <i data-lucide="user-check" class="empty-prompt-icon"></i>
                <div class="prompt-text">
                    <strong>Please select a Target User above to begin</strong>
                    <p>Once selected, you can configure their sidebar navigation menus, CRUD object permissions, password credentials, and electronic signature.</p>
                </div>
            </div>

            <!-- ACTIVE PERMISSION PANELS (LOCKED UNTIL USER IS SELECTED) -->
            <div class="perm-active-wrapper" id="permActiveWrapper" style="display: none;">

                <!-- 2. DUAL COLUMN: SET PASSWORD & E-SIGNATURE (E-SIG) -->
                <div class="perm-grid-2col">

                    <!-- SET / RESET PASSWORD CARD -->
                    <div class="perm-card">
                        <div class="perm-card-header">
                            <div>
                                <span class="perm-card-title">Set / Reset Password</span>
                                <p class="perm-card-subtitle">Update credentials directly for this selected user account.</p>
                            </div>
                            <i data-lucide="key" class="card-header-icon"></i>
                        </div>
                        <div class="perm-card-body">
                            <form id="permPasswordForm" onsubmit="handleSetPassword(event)">
                                <div class="form-group mb-3">
                                    <label for="permNewPassword">New Password <span class="required">*</span></label>
                                    <input type="password" id="permNewPassword" class="form-input" placeholder="Enter new secure password" required minlength="6">
                                </div>
                                <div class="form-group mb-3">
                                    <label for="permConfirmPassword">Confirm Password <span class="required">*</span></label>
                                    <input type="password" id="permConfirmPassword" class="form-input" placeholder="Re-enter password" required minlength="6">
                                </div>
                                <div class="pwd-action-row">
                                    <button type="submit" class="btn-primary btn-sm" id="btnUpdatePassword">
                                        <i data-lucide="lock" class="btn-icon"></i>
                                        <span>Set Password</span>
                                    </button>
                                    <span class="pwd-msg-feedback" id="pwdFeedback"></span>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- E-SIGNATURE (E-SIG) CARD -->
                    <div class="perm-card">
                        <div class="perm-card-header">
                            <div>
                                <span class="perm-card-title">Electronic Signature (E-Sig)</span>
                                <p class="perm-card-subtitle">Draw and assign an official signature for document & purchase approvals.</p>
                            </div>
                            <i data-lucide="pen-tool" class="card-header-icon"></i>
                        </div>
                        <div class="perm-card-body">
                            <div class="esig-canvas-wrap">
                                <canvas id="esigCanvas" width="380" height="110" class="esig-canvas"></canvas>
                                <span class="esig-watermark">Sign inside canvas</span>
                            </div>
                            <div class="esig-controls-row">
                                <div class="esig-meta-status">
                                    <span class="esig-status-dot active"></span>
                                    <span class="esig-status-text" id="esigStatusText">Ready to sign</span>
                                </div>
                                <div class="esig-btn-cluster">
                                    <button type="button" class="btn-outline btn-xs" id="btnClearEsig" title="Clear canvas">
                                        <i data-lucide="rotate-ccw" class="btn-icon"></i>
                                        <span>Clear</span>
                                    </button>
                                    <button type="button" class="btn-outline btn-xs" id="btnSaveEsig" title="Adopt signature">
                                        <i data-lucide="check" class="btn-icon"></i>
                                        <span>Adopt E-Sig</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- 3. PERMISSIONS SECTION: SUB-TABS (MENU ACCESS VS OBJECT PERMISSIONS) -->
                <div class="perm-card mt-3">
                    <div class="perm-subtab-header">
                        <div class="perm-nav-pills">
                            <button type="button" class="perm-subtab-btn active" data-subtab="menu-access" id="subtabBtnMenu">
                                <i data-lucide="layout-grid" class="tab-icon"></i>
                                <span>Menu Access (Sidebar Modules)</span>
                            </button>
                            <button type="button" class="perm-subtab-btn" data-subtab="object-permissions" id="subtabBtnObject">
                                <i data-lucide="check-square" class="tab-icon"></i>
                                <span>Object Permissions (CRUD & Capabilities)</span>
                            </button>
                        </div>

                        <div class="perm-header-quick-actions" id="permQuickActionsMenu">
                            <button type="button" class="btn-outline btn-xs" onclick="toggleAllMenus(true)">
                                <i data-lucide="check-check" class="btn-icon"></i>
                                <span>Select All Menus</span>
                            </button>
                            <button type="button" class="btn-outline btn-xs" onclick="toggleAllMenus(false)">
                                <i data-lucide="x" class="btn-icon"></i>
                                <span>Deselect All</span>
                            </button>
                        </div>
                    </div>

                    <div class="perm-card-body p-0">

                        <!-- SUB-PANE A: MENU ACCESS (EXHAUSTIVE SIDEBAR SUBMENUS) -->
                        <div class="perm-subpane active" id="subpaneMenuAccess">
                            <div class="menu-categories-grid">

                                <!-- Group 1: Sales Submenus (8 items) -->
                                <div class="menu-category-card">
                                    <div class="menu-cat-header">
                                        <div class="cat-title-group">
                                            <i data-lucide="trending-up" class="cat-icon"></i>
                                            <span class="cat-name">Sales Submenus</span>
                                        </div>
                                        <span class="cat-count">8 Items</span>
                                    </div>
                                    <div class="menu-items-checklist checklist-grid-2">
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-customer" checked>
                                            <span class="menu-item-name">Customer</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-product" checked>
                                            <span class="menu-item-name">Product</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-order" checked>
                                            <span class="menu-item-name">Sales Order</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-invoice" checked>
                                            <span class="menu-item-name">Invoice</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-report" checked>
                                            <span class="menu-item-name">Sales Report</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-dr" checked>
                                            <span class="menu-item-name">Delivery Receipt</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-shipping" checked>
                                            <span class="menu-item-name">Shipping Invoice</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="sales-mrp" checked>
                                            <span class="menu-item-name">Material Requirement Plan</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Group 2: Production Submenus (3 items) -->
                                <div class="menu-category-card">
                                    <div class="menu-cat-header">
                                        <div class="cat-title-group">
                                            <i data-lucide="factory" class="cat-icon"></i>
                                            <span class="cat-name">Production Submenus</span>
                                        </div>
                                        <span class="cat-count">3 Items</span>
                                    </div>
                                    <div class="menu-items-checklist">
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="prod-output" checked>
                                            <span class="menu-item-name">Production Output</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="prod-output-inv" checked>
                                            <span class="menu-item-name">Production Output Inventory</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="prod-fgts" checked>
                                            <span class="menu-item-name">FGTS</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Group 3: Warehouse Submenus (10 items) -->
                                <div class="menu-category-card">
                                    <div class="menu-cat-header">
                                        <div class="cat-title-group">
                                            <i data-lucide="warehouse" class="cat-icon"></i>
                                            <span class="cat-name">Warehouse Submenus</span>
                                        </div>
                                        <span class="cat-count">10 Items</span>
                                    </div>
                                    <div class="menu-items-checklist checklist-grid-2">
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-inv-fg" checked>
                                            <span class="menu-item-name">Inventory: FG</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-inv-supplies" checked>
                                            <span class="menu-item-name">Inventory: Supplies/Raw Matl's</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-fg-dts" checked>
                                            <span class="menu-item-name">FG: Delivery Transfer Slip</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-fg-adjust" checked>
                                            <span class="menu-item-name">FG: Adjustment</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-fg-recv" checked>
                                            <span class="menu-item-name">FG: Receiving</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-fg-stock" checked>
                                            <span class="menu-item-name">FG: Stock</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-sup-adjust" checked>
                                            <span class="menu-item-name">Supplies: Adjustment</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-sup-issuance" checked>
                                            <span class="menu-item-name">Supplies: Issuance</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-sup-return" checked>
                                            <span class="menu-item-name">Supplies: Return</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="wh-sup-stock" checked>
                                            <span class="menu-item-name">Supplies: Stock</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Group 4: Purchasing Submenus (4 items) -->
                                <div class="menu-category-card">
                                    <div class="menu-cat-header">
                                        <div class="cat-title-group">
                                            <i data-lucide="shopping-cart" class="cat-icon"></i>
                                            <span class="cat-name">Purchasing Submenus</span>
                                        </div>
                                        <span class="cat-count">4 Items</span>
                                    </div>
                                    <div class="menu-items-checklist">
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="pur-pr" checked>
                                            <span class="menu-item-name">Purchase Request</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="pur-po" checked>
                                            <span class="menu-item-name">Purchase Order</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="pur-supplier" checked>
                                            <span class="menu-item-name">Supplier Directory</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="pur-supplies" checked>
                                            <span class="menu-item-name">Supplies Catalog</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Group 5: Configuration Submenus (16 items) -->
                                <div class="menu-category-card menu-cat-span-2">
                                    <div class="menu-cat-header">
                                        <div class="cat-title-group">
                                            <i data-lucide="sliders" class="cat-icon"></i>
                                            <span class="cat-name">Configuration Submenus</span>
                                        </div>
                                        <span class="cat-count">16 Items</span>
                                    </div>
                                    <div class="menu-items-checklist checklist-grid-2">
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-atc" checked>
                                            <span class="menu-item-name">ATC (Alphanumeric Tax Code)</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-audittrail" checked>
                                            <span class="menu-item-name">Audit Trail</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-bank" checked>
                                            <span class="menu-item-name">Bank</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-branch" checked>
                                            <span class="menu-item-name">Branch</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-country" checked>
                                            <span class="menu-item-name">Country</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-currency" checked>
                                            <span class="menu-item-name">Currency</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-customercat" checked>
                                            <span class="menu-item-name">Customer Category</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-paymentterm" checked>
                                            <span class="menu-item-name">Payment Terms</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-productcat" checked>
                                            <span class="menu-item-name">Product Category</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-suppliescat" checked>
                                            <span class="menu-item-name">Supplies Category</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-uom" checked>
                                            <span class="menu-item-name">Unit of Product</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-user" checked>
                                            <span class="menu-item-name">User Accounts</span>
                                        </label>
                                        <!-- User Permission Submenu Option -->
                                        <label class="menu-check-item highlight-perm-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-userperm" checked>
                                            <span class="menu-item-name font-medium text-dark">User Permission (Access to this tab & RBAC)</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-vat" checked>
                                            <span class="menu-item-name">VAT</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-wh" checked>
                                            <span class="menu-item-name">Warehouse</span>
                                        </label>
                                        <label class="menu-check-item">
                                            <input type="checkbox" class="perm-checkbox menu-perm-cb" data-menu="cfg-whloc" checked>
                                            <span class="menu-item-name">Warehouse Location</span>
                                        </label>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- SUB-PANE B: OBJECT PERMISSIONS (CRUD & ACTION RIGHTS) -->
                        <div class="perm-subpane" id="subpaneObjectPermissions">
                            <div class="object-perm-table-wrap">
                                <table class="object-perm-table">
                                    <thead>
                                        <tr>
                                            <th style="min-width: 220px;">Module / Object</th>
                                            <th class="text-center" style="width: 100px;">View</th>
                                            <th class="text-center" style="width: 100px;">Add / Create</th>
                                            <th class="text-center" style="width: 100px;">Edit</th>
                                            <th class="text-center" style="width: 100px;">Delete</th>
                                            <th class="text-center" style="width: 100px;">Print / Export</th>
                                            <th class="text-center" style="width: 110px;">E-Sig / Sign</th>
                                        </tr>
                                    </thead>
                                    <tbody id="objectPermTableBody">
                                        <!-- Populated dynamically with matrix -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-- BOTTOM ACTIONS BAR (SAVE PERMISSIONS) -->
                    <div class="perm-footer-action-bar">
                        <div class="footer-left-info">
                            <i data-lucide="info" class="info-icon"></i>
                            <span>Permissions will take effect immediately upon saving.</span>
                        </div>
                        <div class="footer-btn-cluster">
                            <button type="button" class="btn-outline" onclick="resetSelectedUserPermissions()">Reset to Defaults</button>
                            <button type="button" class="btn-primary" id="btnSavePermissions" onclick="saveUserPermissions()">
                                <i data-lucide="check-circle-2" class="btn-icon"></i>
                                <span>Save User Permissions</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </main>

</div>

<!-- ==========================================================================
     1. CREATE / EDIT USER MODAL
     ========================================================================== -->
<div class="modal-backdrop" id="userModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create User Account</h3>
                <p class="modal-subtitle">Enter personal name details and unique authentication username.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="userForm" onsubmit="handleUserFormSubmit(event)">
            <input type="hidden" id="userFormId" value="">

            <div class="modal-body">
                <!-- Row 1: First Name & Middle Name -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="userFirstName">First Name <span class="required">*</span></label>
                        <input type="text" id="userFirstName" class="form-input" placeholder="e.g. Juan" required maxlength="60">
                    </div>
                    <div class="form-group flex-1">
                        <label for="userMiddleName">Middle Name</label>
                        <input type="text" id="userMiddleName" class="form-input" placeholder="e.g. Santos (Optional)" maxlength="60">
                    </div>
                </div>

                <!-- Row 2: Last Name & Username -->
                <div class="form-row">
                    <div class="form-group flex-1">
                        <label for="userLastName">Last Name <span class="required">*</span></label>
                        <input type="text" id="userLastName" class="form-input" placeholder="e.g. Dela Cruz" required maxlength="60">
                    </div>
                    <div class="form-group flex-1">
                        <label for="userUsername">Username <span class="required">*</span></label>
                        <input type="text" id="userUsername" class="form-input" placeholder="e.g. jdelacruz" required maxlength="40" style="text-transform: lowercase;">
                    </div>
                </div>

                <!-- Row 3: Password / Security (Optional for edit) -->
                <div class="form-row" id="passwordGroupRow">
                    <div class="form-group flex-1">
                        <label for="userPassword" id="labelPassword">Password <span class="required" id="passwordRequiredStar">*</span></label>
                        <input type="password" id="userPassword" class="form-input" placeholder="Enter secure password" autocomplete="new-password">
                        <small class="field-help-text" id="passwordHelpText">Minimum 6 characters. Required for new users.</small>
                    </div>
                    <div class="form-group flex-1">
                        <label for="userConfirmPassword" id="labelConfirmPassword">Confirm Password</label>
                        <input type="password" id="userConfirmPassword" class="form-input" placeholder="Re-type password" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel" id="btnModalCancel">Cancel</button>
                <button type="submit" class="btn-save" id="btnSubmitForm">
                    <i data-lucide="check" class="btn-icon"></i>
                    <span id="btnSubmitLabel">Save User</span>
                </button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. VIEW USER MODAL (OCTAVE READONLY DETAILS CARD)
     ========================================================================== -->
<div class="modal-backdrop" id="viewModalBackdrop">
    <div class="modal-dialog modal-dialog-md" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="viewModalTitle">User Account Profile</h3>
                <p class="modal-subtitle">Detailed information and identification for this system user.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnViewModalCloseCross" title="Close view modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body view-modal-content">
            <!-- User Avatar & Identity Header -->
            <div class="view-user-hero">
                <div class="view-user-avatar" id="viewAvatarText">--</div>
                <div class="view-user-hero-meta">
                    <h4 class="view-hero-fullname" id="viewFullName">User Profile</h4>
                    <span class="view-hero-username" id="viewHeroUsername">@user</span>
                </div>
                <div class="view-user-badge-slot">
                    <span class="status-pill status-pill-active" id="viewStatusPill">Active</span>
                </div>
            </div>

            <!-- Details Key-Value Grid -->
            <div class="view-details-grid">
                <div class="view-field-item">
                    <span class="view-field-label">User ID</span>
                    <span class="view-field-value font-code" id="viewUserId">—</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Username</span>
                    <span class="view-field-value font-code" id="viewUsername">—</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">First Name</span>
                    <span class="view-field-value" id="viewFirstName">—</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Middle Name</span>
                    <span class="view-field-value" id="viewMiddleName">—</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Last Name</span>
                    <span class="view-field-value" id="viewLastName">—</span>
                </div>
                <div class="view-field-item">
                    <span class="view-field-label">Account Security</span>
                    <span class="view-field-value text-muted">••••••••••••</span>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-cancel" id="btnViewModalClose">Close</button>
            <button type="button" class="btn-primary" id="btnViewEditShortcut">
                <i data-lucide="edit-2" class="btn-icon"></i>
                <span>Edit User</span>
            </button>
        </div>

    </div>
</div>

<!-- ==========================================================================
     3. DELETE CONFIRMATION MODAL (ZERO BROWSER ALERTS)
     ========================================================================== -->
<div class="modal-backdrop" id="deleteConfirmBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-group">
                <div class="danger-icon-circle">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div>
                    <h3 class="modal-title" id="deleteModalTitle">Delete User</h3>
                    <p class="modal-subtitle">Confirm removal of this user account.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to remove user account <strong id="deleteTargetUsername">@user</strong> (<span id="deleteTargetFullName">User Name</span>)?
            </p>
            <p class="delete-sub-text">
                This account will immediately lose access to the Intracode ERP system.
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

    // -------------------------------------------------------------------------
    // 1. DATA REPOSITORY & INITIAL STATE (DEFAULT: NO USER SELECTED)
    // -------------------------------------------------------------------------
    let usersData = [
        {
            id: 1,
            user_code: 'USR-001',
            first_name: 'Karl',
            last_name: 'Bautista',
            middle_name: 'Santos',
            username: 'karlb'
        },
        {
            id: 2,
            user_code: 'USR-002',
            first_name: 'Juan',
            last_name: 'Dela Cruz',
            middle_name: 'Santos',
            username: 'jdelacruz'
        },
        {
            id: 3,
            user_code: 'USR-003',
            first_name: 'Maria',
            last_name: 'Santos',
            middle_name: 'Reyes',
            username: 'msantos'
        },
        {
            id: 4,
            user_code: 'USR-004',
            first_name: 'Carlos',
            last_name: 'Reyes',
            middle_name: 'Aquino',
            username: 'creyes'
        },
        {
            id: 5,
            user_code: 'USR-005',
            first_name: 'Admin',
            last_name: 'User',
            middle_name: 'System',
            username: 'admin'
        }
    ];

    // Objects for Object Permissions Matrix
    const erpObjects = [
        { key: 'sales_customer', name: 'Sales: Customer Directory' },
        { key: 'sales_product', name: 'Sales: Product Catalog' },
        { key: 'sales_order', name: 'Sales: Sales Order' },
        { key: 'sales_invoice', name: 'Sales: Invoice Billing' },
        { key: 'sales_report', name: 'Sales: Sales Report' },
        { key: 'sales_dr', name: 'Sales: Delivery Receipt' },
        { key: 'sales_shipping', name: 'Sales: Shipping Invoice' },
        { key: 'sales_mrp', name: 'Sales: Material Requirement Plan' },
        { key: 'prod_output', name: 'Production: Output & Inventory' },
        { key: 'prod_fgts', name: 'Production: FGTS' },
        { key: 'wh_inventory', name: 'Warehouse: FG & Supplies Inventory' },
        { key: 'pur_po', name: 'Purchasing: Purchase Orders' },
        { key: 'cfg_atc', name: 'Configuration: ATC' },
        { key: 'cfg_bank', name: 'Configuration: Bank & Branches' },
        { key: 'cfg_currency', name: 'Configuration: Currency & Country' },
        { key: 'cfg_payment', name: 'Configuration: Payment Terms' },
        { key: 'cfg_category', name: 'Configuration: Product & Supplies Category' },
        { key: 'cfg_uom', name: 'Configuration: Unit of Product' },
        { key: 'cfg_user', name: 'Configuration: User & Permission Management' }
    ];

    let currentOpenActionMenuId = null;
    let activeDeleteId = null;
    let activeViewId = null;
    let selectedPermUserId = null; // DEFAULT: WALA MUNA NAKA-SELECT

    // DOM Elements - User List
    const userTableBody = document.getElementById('userTableBody');
    const userSearchInput = document.getElementById('userSearchInput');
    const recordCountBadge = document.getElementById('recordCount');
    const tabBadgeCount = document.getElementById('tabBadgeCount');
    const footerMetaText = document.getElementById('footerMetaText');
    const pageHeaderTitle = document.getElementById('pageHeaderTitle');
    const bcPageTitle = document.getElementById('bcPageTitle');
    const headerActionCluster = document.getElementById('headerActionCluster');
    const topSearchBox = document.getElementById('topSearchBox');

    // Tab Elements
    const tabBtnList = document.getElementById('tabBtnList');
    const tabBtnPerm = document.getElementById('tabBtnPerm');
    const paneUserList = document.getElementById('paneUserList');
    const paneUserPermission = document.getElementById('paneUserPermission');

    // Subtab Elements (Menu Access vs Object Permissions)
    const subtabBtnMenu = document.getElementById('subtabBtnMenu');
    const subtabBtnObject = document.getElementById('subtabBtnObject');
    const subpaneMenuAccess = document.getElementById('subpaneMenuAccess');
    const subpaneObjectPermissions = document.getElementById('subpaneObjectPermissions');
    const permQuickActionsMenu = document.getElementById('permQuickActionsMenu');

    // Target User Combobox Elements
    const userComboboxWrapper = document.getElementById('userComboboxWrapper');
    const userComboboxTrigger = document.getElementById('userComboboxTrigger');
    const userComboboxDropdown = document.getElementById('userComboboxDropdown');
    const comboboxSearchInput = document.getElementById('comboboxSearchInput');
    const comboboxItemsList = document.getElementById('comboboxItemsList');
    const triggerSelectedDisplay = document.getElementById('triggerSelectedDisplay');

    // User Profile Auto-fill Elements
    const permHeroAvatar = document.getElementById('permHeroAvatar');
    const permHeroFullName = document.getElementById('permHeroFullName');
    const permHeroUserCode = document.getElementById('permHeroUserCode');
    const permHeroStatus = document.getElementById('permHeroStatus');
    const permUserId = document.getElementById('permUserId');
    const permUsername = document.getElementById('permUsername');
    const permFirstName = document.getElementById('permFirstName');
    const permMiddleName = document.getElementById('permMiddleName');
    const permLastName = document.getElementById('permLastName');
    const permEmptyPrompt = document.getElementById('permEmptyPrompt');
    const permActiveWrapper = document.getElementById('permActiveWrapper');
    const objectPermTableBody = document.getElementById('objectPermTableBody');

    // Modals
    const userModalBackdrop = document.getElementById('userModalBackdrop');
    const btnOpenCreateModal = document.getElementById('btnOpenCreateModal');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');
    const btnModalCancel = document.getElementById('btnModalCancel');
    const userForm = document.getElementById('userForm');
    const modalTitle = document.getElementById('modalTitle');
    const btnSubmitLabel = document.getElementById('btnSubmitLabel');
    const userFormId = document.getElementById('userFormId');
    const userFirstName = document.getElementById('userFirstName');
    const userMiddleName = document.getElementById('userMiddleName');
    const userLastName = document.getElementById('userLastName');
    const userUsername = document.getElementById('userUsername');
    const userPassword = document.getElementById('userPassword');
    const userConfirmPassword = document.getElementById('userConfirmPassword');
    const passwordRequiredStar = document.getElementById('passwordRequiredStar');
    const passwordHelpText = document.getElementById('passwordHelpText');

    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const btnViewModalCloseCross = document.getElementById('btnViewModalCloseCross');
    const btnViewModalClose = document.getElementById('btnViewModalClose');
    const btnViewEditShortcut = document.getElementById('btnViewEditShortcut');
    const viewAvatarText = document.getElementById('viewAvatarText');
    const viewFullName = document.getElementById('viewFullName');
    const viewHeroUsername = document.getElementById('viewHeroUsername');
    const viewUserId = document.getElementById('viewUserId');
    const viewUsername = document.getElementById('viewUsername');
    const viewFirstName = document.getElementById('viewFirstName');
    const viewMiddleName = document.getElementById('viewMiddleName');
    const viewLastName = document.getElementById('viewLastName');

    const deleteConfirmBackdrop = document.getElementById('deleteConfirmBackdrop');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnCancelDelete = document.getElementById('btnCancelDelete');
    const btnConfirmDelete = document.getElementById('btnConfirmDelete');
    const deleteTargetUsername = document.getElementById('deleteTargetUsername');
    const deleteTargetFullName = document.getElementById('deleteTargetFullName');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    const exportMenu = document.getElementById('exportMenu');

    // -------------------------------------------------------------------------
    // 2. MAIN TABS SWITCHER (USER LIST VS USER PERMISSION)
    // -------------------------------------------------------------------------
    function switchMainTab(targetTab) {
        if (targetTab === 'user-list') {
            tabBtnList.classList.add('active');
            tabBtnPerm.classList.remove('active');
            paneUserList.classList.add('active');
            paneUserPermission.classList.remove('active');

            pageHeaderTitle.textContent = 'User Directory';
            bcPageTitle.textContent = 'User';
            headerActionCluster.style.display = 'flex';
            topSearchBox.style.display = 'flex';
        } else {
            tabBtnPerm.classList.add('active');
            tabBtnList.classList.remove('active');
            paneUserPermission.classList.add('active');
            paneUserList.classList.remove('active');

            pageHeaderTitle.textContent = 'User Access & Permissions';
            bcPageTitle.textContent = 'User Permission';
            headerActionCluster.style.display = 'none';
            topSearchBox.style.display = 'none';

            if (selectedPermUserId) {
                initEsigCanvas();
            }
        }
        if (window.lucide) window.lucide.createIcons();
    }

    if (tabBtnList) tabBtnList.addEventListener('click', () => switchMainTab('user-list'));
    if (tabBtnPerm) tabBtnPerm.addEventListener('click', () => switchMainTab('user-permission'));

    // -------------------------------------------------------------------------
    // 3. SUB-TABS SWITCHER (MENU ACCESS VS OBJECT PERMISSIONS)
    // -------------------------------------------------------------------------
    function switchSubTab(targetSubTab) {
        if (targetSubTab === 'menu-access') {
            subtabBtnMenu.classList.add('active');
            subtabBtnObject.classList.remove('active');
            subpaneMenuAccess.classList.add('active');
            subpaneObjectPermissions.classList.remove('active');
            permQuickActionsMenu.style.display = 'flex';
        } else {
            subtabBtnObject.classList.add('active');
            subtabBtnMenu.classList.remove('active');
            subpaneObjectPermissions.classList.add('active');
            subpaneMenuAccess.classList.remove('active');
            permQuickActionsMenu.style.display = 'none';
        }
        if (window.lucide) window.lucide.createIcons();
    }

    if (subtabBtnMenu) subtabBtnMenu.addEventListener('click', () => switchSubTab('menu-access'));
    if (subtabBtnObject) subtabBtnObject.addEventListener('click', () => switchSubTab('object-permissions'));

    // -------------------------------------------------------------------------
    // 4. SEARCHABLE COMBOBOX LOGIC FOR TARGET USER
    // -------------------------------------------------------------------------
    function renderComboboxItems(filterQuery = '') {
        if (!comboboxItemsList) return;
        comboboxItemsList.innerHTML = '';

        const query = filterQuery.toLowerCase().trim();
        const filtered = usersData.filter(u => {
            if (!query) return true;
            return u.user_code.toLowerCase().includes(query) ||
                   u.first_name.toLowerCase().includes(query) ||
                   u.last_name.toLowerCase().includes(query) ||
                   (u.middle_name || '').toLowerCase().includes(query) ||
                   u.username.toLowerCase().includes(query);
        });

        // Option: Clear selection
        const clearOpt = document.createElement('div');
        clearOpt.className = 'cb-item cb-item-clear';
        clearOpt.innerHTML = `
            <div class="cb-item-meta">
                <span class="cb-item-name text-muted">-- Select User  --</span>
            </div>
        `;
        clearOpt.addEventListener('click', () => {
            selectUserForPermission(null);
            closeCombobox();
        });
        comboboxItemsList.appendChild(clearOpt);

        if (filtered.length === 0) {
            const noMatch = document.createElement('div');
            noMatch.className = 'cb-no-results';
            noMatch.textContent = 'No matching user accounts found';
            comboboxItemsList.appendChild(noMatch);
            if (window.lucide) window.lucide.createIcons();
            return;
        }

        filtered.forEach(user => {
            const item = document.createElement('div');
            item.className = `cb-item ${selectedPermUserId === user.id ? 'selected' : ''}`;
            const firstInit = user.first_name ? user.first_name.charAt(0).toUpperCase() : '';
            const lastInit = user.last_name ? user.last_name.charAt(0).toUpperCase() : '';
            const initials = `${firstInit}${lastInit}` || 'US';

            item.innerHTML = `
                <div class="cb-item-avatar">${initials}</div>
                <div class="cb-item-meta">
                    <span class="cb-item-name">${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)}</span>
                    <span class="cb-item-sub">@${escapeHtml(user.username)} &bull; ${escapeHtml(user.user_code)}</span>
                </div>
                ${selectedPermUserId === user.id ? '<i data-lucide="check" class="cb-check-icon"></i>' : ''}
            `;

            item.addEventListener('click', () => {
                selectUserForPermission(user.id);
                closeCombobox();
            });

            comboboxItemsList.appendChild(item);
        });

        if (window.lucide) window.lucide.createIcons();
    }

    function toggleCombobox(e) {
        if (e) e.stopPropagation();
        const isOpen = userComboboxDropdown.classList.contains('show');
        if (isOpen) {
            closeCombobox();
        } else {
            userComboboxDropdown.classList.add('show');
            userComboboxTrigger.classList.add('active');
            if (comboboxSearchInput) {
                comboboxSearchInput.value = '';
                renderComboboxItems('');
                setTimeout(() => comboboxSearchInput.focus(), 50);
            }
        }
    }

    function closeCombobox() {
        if (userComboboxDropdown) userComboboxDropdown.classList.remove('show');
        if (userComboboxTrigger) userComboboxTrigger.classList.remove('active');
    }

    if (userComboboxTrigger) {
        userComboboxTrigger.addEventListener('click', toggleCombobox);
    }

    if (comboboxSearchInput) {
        comboboxSearchInput.addEventListener('input', function (e) {
            renderComboboxItems(this.value);
        });
        comboboxSearchInput.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }

    function selectUserForPermission(userId) {
        selectedPermUserId = userId;

        if (!userId) {
            // Reset to unselected / empty state
            triggerSelectedDisplay.innerHTML = `
                <i data-lucide="user" class="combobox-lead-icon"></i>
                <span class="trigger-placeholder-text">-- Select a User to Configure --</span>
            `;

            permHeroAvatar.textContent = '--';
            permHeroFullName.textContent = 'No User Selected';
            permHeroUserCode.textContent = 'Please choose a user account from the dropdown above';
            permHeroStatus.className = 'status-pill status-pill-muted';
            permHeroStatus.textContent = 'No Selection';

            permUserId.value = '—';
            permUsername.value = '—';
            permFirstName.value = '—';
            permMiddleName.value = '—';
            permLastName.value = '—';

            permEmptyPrompt.style.display = 'flex';
            permActiveWrapper.style.display = 'none';

            if (window.lucide) window.lucide.createIcons();
            return;
        }

        const user = usersData.find(u => u.id === userId);
        if (!user) return;

        // Update trigger display
        const firstInit = user.first_name ? user.first_name.charAt(0).toUpperCase() : '';
        const lastInit = user.last_name ? user.last_name.charAt(0).toUpperCase() : '';
        const initials = `${firstInit}${lastInit}` || 'US';

        triggerSelectedDisplay.innerHTML = `
            <div class="trigger-user-chip">
                <span class="trigger-avatar">${initials}</span>
                <span class="trigger-label"><strong>${escapeHtml(user.user_code)}</strong> &bull; ${escapeHtml(user.first_name)} ${escapeHtml(user.last_name)} (@${escapeHtml(user.username)})</span>
            </div>
        `;

        // Update Hero
        permHeroAvatar.textContent = initials;
        permHeroFullName.textContent = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
        permHeroUserCode.innerHTML = `@${escapeHtml(user.username)} &bull; ID: ${escapeHtml(user.user_code)}`;
        permHeroStatus.className = 'status-pill status-pill-active';
        permHeroStatus.textContent = 'Active Account';

        // Auto-fill Inputs
        permUserId.value = user.user_code;
        permUsername.value = user.username;
        permFirstName.value = user.first_name;
        permMiddleName.value = user.middle_name || '';
        permLastName.value = user.last_name;

        // Show configuration sections
        permEmptyPrompt.style.display = 'none';
        permActiveWrapper.style.display = 'block';

        // Reset password form
        const pwdForm = document.getElementById('permPasswordForm');
        if (pwdForm) pwdForm.reset();
        const pwdFeedback = document.getElementById('pwdFeedback');
        if (pwdFeedback) pwdFeedback.textContent = '';

        // Init and draw simulated signature
        initEsigCanvas();
        clearEsigCanvas();
        drawSimulatedSignature(user.first_name);

        if (window.lucide) window.lucide.createIcons();
    }

    window.jumpToUserPermissions = function (userId) {
        closeAllActionMenus();
        switchMainTab('user-permission');
        selectUserForPermission(userId);
    };

    // -------------------------------------------------------------------------
    // 5. RENDER USER TABLE WITH COMPACT 3-DOTS ACTION MENU
    // -------------------------------------------------------------------------
    function renderUserTable(items) {
        if (!userTableBody) return;
        userTableBody.innerHTML = '';

        if (!items || items.length === 0) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="6" class="table-empty-state">
                    <div class="empty-box">
                        <i data-lucide="users" class="empty-icon"></i>
                        <span class="empty-text">No user accounts found</span>
                        <small class="empty-subtext">Try refining your search query or create a new user account.</small>
                    </div>
                </td>
            `;
            userTableBody.appendChild(tr);
            if (window.lucide) window.lucide.createIcons();
            updateMetaCounts(0, usersData.length);
            return;
        }

        items.forEach(user => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-id', user.id);

            const middleDisplay = user.middle_name && user.middle_name.trim() !== '' 
                ? escapeHtml(user.middle_name) 
                : '<span class="text-muted">—</span>';

            tr.innerHTML = `
                <td class="font-code text-muted">${escapeHtml(user.user_code)}</td>
                <td class="font-medium text-dark">${escapeHtml(user.first_name)}</td>
                <td class="font-medium text-dark">${escapeHtml(user.last_name)}</td>
                <td>${middleDisplay}</td>
                <td>
                    <span class="username-pill">@${escapeHtml(user.username)}</span>
                </td>
                <td class="text-center no-print">
                    <div class="action-dropdown-wrapper">
                        <button type="button" 
                                class="action-dots-btn" 
                                onclick="toggleActionMenu(event, ${user.id})"
                                title="Actions for ${escapeHtml(user.username)}">
                            <i data-lucide="more-horizontal"></i>
                        </button>
                        <div class="action-dropdown-menu" id="actionMenu-${user.id}">
                            <button type="button" class="action-menu-item" onclick="openViewUserModal(${user.id})">
                                <i data-lucide="eye"></i>
                                <span>View</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="openEditUserModal(${user.id})">
                                <i data-lucide="edit-2"></i>
                                <span>Edit</span>
                            </button>
                            <button type="button" class="action-menu-item" onclick="jumpToUserPermissions(${user.id})">
                                <i data-lucide="shield"></i>
                                <span>Permissions</span>
                            </button>
                            <div class="menu-divider"></div>
                            <button type="button" class="action-menu-item item-danger" onclick="openDeleteUserModal(${user.id})">
                                <i data-lucide="trash-2"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>
                </td>
            `;

            userTableBody.appendChild(tr);
        });

        if (window.lucide) window.lucide.createIcons();
        updateMetaCounts(items.length, usersData.length);
    }

    function updateMetaCounts(filteredCount, totalCount) {
        if (recordCountBadge) recordCountBadge.textContent = `${filteredCount} Records`;
        if (tabBadgeCount) tabBadgeCount.textContent = totalCount;
        if (footerMetaText) footerMetaText.textContent = `Showing 1 to ${filteredCount} of ${totalCount} user entries`;
    }

    // -------------------------------------------------------------------------
    // 6. ACTION MENU TOGGLE & SMART DROP-UP CALCULATION
    // -------------------------------------------------------------------------
    window.toggleActionMenu = function (event, userId) {
        event.stopPropagation();
        const btn = event.currentTarget;
        const targetMenu = document.getElementById(`actionMenu-${userId}`);
        
        if (currentOpenActionMenuId === userId) {
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
            currentOpenActionMenuId = userId;
        }
    };

    function closeAllActionMenus() {
        document.querySelectorAll('.action-dropdown-menu.show').forEach(menu => {
            menu.classList.remove('show');
        });
        currentOpenActionMenuId = null;
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.action-dropdown-wrapper')) {
            closeAllActionMenus();
        }
        if (exportDropdownWrapper && !exportDropdownWrapper.contains(e.target)) {
            exportMenu.classList.remove('show');
        }
        if (userComboboxWrapper && !userComboboxWrapper.contains(e.target)) {
            closeCombobox();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllActionMenus();
            closeCombobox();
            closeUserModal();
            closeViewModal();
            closeDeleteModal();
            if (exportMenu) exportMenu.classList.remove('show');
        }
    });

    // -------------------------------------------------------------------------
    // 7. OBJECT PERMISSIONS MATRIX BUILDER
    // -------------------------------------------------------------------------
    function buildObjectPermissionsTable() {
        if (!objectPermTableBody) return;
        objectPermTableBody.innerHTML = '';

        erpObjects.forEach((obj, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="font-medium text-dark">${escapeHtml(obj.name)}</td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="view" checked>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="create" ${idx < 12 ? 'checked' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="edit" ${idx < 12 ? 'checked' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="delete" ${idx < 8 ? 'checked' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="print" checked>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="perm-checkbox obj-cb" data-obj="${obj.key}" data-act="sign" ${idx === 2 || idx === 3 || idx === 11 ? 'checked' : ''}>
                </td>
            `;
            objectPermTableBody.appendChild(tr);
        });
    }

    window.toggleAllMenus = function (state) {
        document.querySelectorAll('.menu-perm-cb').forEach(cb => {
            cb.checked = state;
        });
    };

    window.saveUserPermissions = function () {
        if (!selectedPermUserId) {
            alert('Please select a target user first.');
            return;
        }

        const btn = document.getElementById('btnSavePermissions');
        const origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<i data-lucide="loader-2" class="btn-icon spin-anim"></i> <span>Saving...</span>`;
        if (window.lucide) window.lucide.createIcons();

        setTimeout(() => {
            btn.innerHTML = `<i data-lucide="check" class="btn-icon"></i> <span>Permissions Saved!</span>`;
            if (window.lucide) window.lucide.createIcons();
            setTimeout(() => {
                btn.innerHTML = origHtml;
                btn.disabled = false;
                if (window.lucide) window.lucide.createIcons();
            }, 1800);
        }, 500);
    };

    window.resetSelectedUserPermissions = function () {
        document.querySelectorAll('.menu-perm-cb').forEach(cb => cb.checked = true);
        document.querySelectorAll('.obj-cb').forEach(cb => cb.checked = true);
        clearEsigCanvas();
        const user = usersData.find(u => u.id === selectedPermUserId);
        if (user) drawSimulatedSignature(user.first_name);
    };

    // -------------------------------------------------------------------------
    // 8. SET / RESET PASSWORD ACTION
    // -------------------------------------------------------------------------
    window.handleSetPassword = function (e) {
        e.preventDefault();
        const p1 = document.getElementById('permNewPassword').value;
        const p2 = document.getElementById('permConfirmPassword').value;
        const feedback = document.getElementById('pwdFeedback');

        if (p1 !== p2) {
            feedback.style.color = '#ef4444';
            feedback.textContent = 'Passwords do not match.';
            return;
        }

        feedback.style.color = '#059669';
        feedback.textContent = 'Password updated successfully!';
        document.getElementById('permPasswordForm').reset();
        setTimeout(() => { feedback.textContent = ''; }, 3000);
    };

    // -------------------------------------------------------------------------
    // 9. ELECTRONIC SIGNATURE (E-SIG) CANVAS
    // -------------------------------------------------------------------------
    const esigCanvas = document.getElementById('esigCanvas');
    let esigCtx = null;
    let isDrawing = false;

    function initEsigCanvas() {
        if (!esigCanvas) return;
        esigCtx = esigCanvas.getContext('2d');
        esigCtx.lineWidth = 2;
        esigCtx.lineCap = 'round';
        esigCtx.strokeStyle = '#1e3a8a';

        esigCanvas.removeEventListener('mousedown', startDrawing);
        esigCanvas.removeEventListener('mousemove', draw);
        esigCanvas.removeEventListener('mouseup', stopDrawing);
        esigCanvas.removeEventListener('mouseleave', stopDrawing);

        esigCanvas.addEventListener('mousedown', startDrawing);
        esigCanvas.addEventListener('mousemove', draw);
        esigCanvas.addEventListener('mouseup', stopDrawing);
        esigCanvas.addEventListener('mouseleave', stopDrawing);

        // Touch support
        esigCanvas.addEventListener('touchstart', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousedown', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            esigCanvas.dispatchEvent(mouseEvent);
        });
        esigCanvas.addEventListener('touchmove', (e) => {
            e.preventDefault();
            const touch = e.touches[0];
            const mouseEvent = new MouseEvent('mousemove', {
                clientX: touch.clientX,
                clientY: touch.clientY
            });
            esigCanvas.dispatchEvent(mouseEvent);
        });
        esigCanvas.addEventListener('touchend', () => stopDrawing());
    }

    function startDrawing(e) {
        if (!selectedPermUserId) return;
        isDrawing = true;
        const rect = esigCanvas.getBoundingClientRect();
        esigCtx.beginPath();
        esigCtx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    }

    function draw(e) {
        if (!isDrawing || !selectedPermUserId) return;
        const rect = esigCanvas.getBoundingClientRect();
        esigCtx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
        esigCtx.stroke();
    }

    function stopDrawing() {
        if (!isDrawing) return;
        isDrawing = false;
        if (esigCtx) esigCtx.closePath();
    }

    function clearEsigCanvas() {
        if (!esigCtx || !esigCanvas) return;
        esigCtx.clearRect(0, 0, esigCanvas.width, esigCanvas.height);
        const status = document.getElementById('esigStatusText');
        if (status) status.textContent = 'Cleared — sign inside canvas';
    }

    function drawSimulatedSignature(name) {
        if (!esigCtx || !esigCanvas) return;
        esigCtx.save();
        esigCtx.font = 'italic 28px "Brush Script MT", "Segoe Script", cursive, sans-serif';
        esigCtx.fillStyle = '#1d4ed8';
        esigCtx.fillText(`${name} Bautista`, 45, 65);
        esigCtx.restore();
        const status = document.getElementById('esigStatusText');
        if (status) status.textContent = 'Signature configured';
    }

    const btnClearEsig = document.getElementById('btnClearEsig');
    const btnSaveEsig = document.getElementById('btnSaveEsig');
    if (btnClearEsig) btnClearEsig.addEventListener('click', clearEsigCanvas);
    if (btnSaveEsig) {
        btnSaveEsig.addEventListener('click', function () {
            const status = document.getElementById('esigStatusText');
            if (status) {
                status.textContent = 'E-Signature adopted & verified';
                status.style.color = '#059669';
            }
        });
    }

    // -------------------------------------------------------------------------
    // 10. SEARCH FILTERING
    // -------------------------------------------------------------------------
    if (userSearchInput) {
        userSearchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            closeAllActionMenus();

            if (!query) {
                renderUserTable(usersData);
                return;
            }

            const filtered = usersData.filter(u => {
                const idMatch = u.user_code.toLowerCase().includes(query);
                const firstMatch = u.first_name.toLowerCase().includes(query);
                const lastMatch = u.last_name.toLowerCase().includes(query);
                const middleMatch = (u.middle_name || '').toLowerCase().includes(query);
                const userMatch = u.username.toLowerCase().includes(query);
                return idMatch || firstMatch || lastMatch || middleMatch || userMatch;
            });

            renderUserTable(filtered);
        });
    }

    // -------------------------------------------------------------------------
    // 11. VIEW MODAL MANAGEMENT
    // -------------------------------------------------------------------------
    window.openViewUserModal = function (userId) {
        closeAllActionMenus();
        const user = usersData.find(u => u.id === userId);
        if (!user) return;

        activeViewId = userId;

        const firstInit = user.first_name ? user.first_name.charAt(0).toUpperCase() : '';
        const lastInit = user.last_name ? user.last_name.charAt(0).toUpperCase() : '';
        viewAvatarText.textContent = `${firstInit}${lastInit}` || 'US';

        const fullName = `${user.first_name} ${user.middle_name ? user.middle_name + ' ' : ''}${user.last_name}`;
        viewFullName.textContent = fullName;
        viewHeroUsername.textContent = `@${user.username}`;
        viewUserId.textContent = user.user_code;
        viewUsername.textContent = user.username;
        viewFirstName.textContent = user.first_name;
        viewMiddleName.textContent = user.middle_name || '—';
        viewLastName.textContent = user.last_name;

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
                openEditUserModal(targetId);
            }
        });
    }

    // -------------------------------------------------------------------------
    // 12. CREATE / EDIT USER MODAL MANAGEMENT
    // -------------------------------------------------------------------------
    function openCreateUserModal() {
        closeAllActionMenus();
        userForm.reset();
        userFormId.value = '';
        modalTitle.textContent = 'Create User Account';
        btnSubmitLabel.textContent = 'Save User';

        if (passwordRequiredStar) passwordRequiredStar.style.display = 'inline';
        if (passwordHelpText) passwordHelpText.textContent = 'Minimum 6 characters. Required for new accounts.';
        userPassword.required = true;

        userModalBackdrop.classList.add('show');
        userFirstName.focus();
        if (window.lucide) window.lucide.createIcons();
    }

    window.openEditUserModal = function (userId) {
        closeAllActionMenus();
        const user = usersData.find(u => u.id === userId);
        if (!user) return;

        userForm.reset();
        userFormId.value = user.id;
        modalTitle.textContent = `Edit User: ${user.user_code}`;
        btnSubmitLabel.textContent = 'Update User';

        userFirstName.value = user.first_name;
        userMiddleName.value = user.middle_name || '';
        userLastName.value = user.last_name;
        userUsername.value = user.username;

        if (passwordRequiredStar) passwordRequiredStar.style.display = 'none';
        if (passwordHelpText) passwordHelpText.textContent = 'Leave blank to keep current password unchanged.';
        userPassword.required = false;

        userModalBackdrop.classList.add('show');
        userFirstName.focus();
        if (window.lucide) window.lucide.createIcons();
    };

    function closeUserModal() {
        if (userModalBackdrop) userModalBackdrop.classList.remove('show');
    }

    if (btnOpenCreateModal) btnOpenCreateModal.addEventListener('click', openCreateUserModal);
    if (btnModalCloseCross) btnModalCloseCross.addEventListener('click', closeUserModal);
    if (btnModalCancel) btnModalCancel.addEventListener('click', closeUserModal);

    window.handleUserFormSubmit = function (event) {
        event.preventDefault();

        const idVal = userFormId.value;
        const fName = userFirstName.value.trim();
        const mName = userMiddleName.value.trim();
        const lName = userLastName.value.trim();
        const uName = userUsername.value.trim().toLowerCase();
        const pwd = userPassword.value;
        const confirmPwd = userConfirmPassword.value;

        if (!fName || !lName || !uName) return;

        if (pwd && pwd !== confirmPwd) {
            alert('Passwords do not match. Please verify.');
            userConfirmPassword.focus();
            return;
        }

        if (idVal) {
            const index = usersData.findIndex(u => u.id === parseInt(idVal, 10));
            if (index !== -1) {
                usersData[index].first_name = fName;
                usersData[index].middle_name = mName;
                usersData[index].last_name = lName;
                usersData[index].username = uName;
            }
        } else {
            const nextId = usersData.length > 0 ? Math.max(...usersData.map(u => u.id)) + 1 : 1;
            const paddedId = String(nextId).padStart(3, '0');
            const newUser = {
                id: nextId,
                user_code: `USR-${paddedId}`,
                first_name: fName,
                last_name: lName,
                middle_name: mName,
                username: uName
            };
            usersData.unshift(newUser);
        }

        closeUserModal();
        if (userSearchInput) userSearchInput.value = '';
        renderUserTable(usersData);
        renderComboboxItems('');
    };

    // -------------------------------------------------------------------------
    // 13. DELETE CONFIRMATION MODAL
    // -------------------------------------------------------------------------
    window.openDeleteUserModal = function (userId) {
        closeAllActionMenus();
        const user = usersData.find(u => u.id === userId);
        if (!user) return;

        activeDeleteId = userId;
        deleteTargetUsername.textContent = `@${user.username}`;
        deleteTargetFullName.textContent = `${user.first_name} ${user.last_name}`;

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

            usersData = usersData.filter(u => u.id !== activeDeleteId);
            closeDeleteModal();

            if (selectedPermUserId === activeDeleteId) {
                selectUserForPermission(null);
            }

            if (userSearchInput) userSearchInput.value = '';
            renderUserTable(usersData);
            renderComboboxItems('');
        });
    }

    // -------------------------------------------------------------------------
    // 14. REFRESH & EXPORT ACTIONS
    // -------------------------------------------------------------------------
    if (btnRefresh) {
        btnRefresh.addEventListener('click', function () {
            closeAllActionMenus();
            if (refreshIcon) refreshIcon.classList.add('spin-anim');
            btnRefresh.disabled = true;

            setTimeout(() => {
                if (userSearchInput) userSearchInput.value = '';
                renderUserTable(usersData);
                renderComboboxItems('');
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

    window.exportUserDirectory = function (format) {
        exportMenu.classList.remove('show');

        if (format === 'pdf') {
            window.print();
        } else if (format === 'csv') {
            let csvContent = 'ID,First Name,Last Name,Middle Name,Username\n';
            usersData.forEach(u => {
                csvContent += `"${u.user_code}","${u.first_name}","${u.last_name}","${u.middle_name || ''}","${u.username}"\n`;
            });
            downloadFile(csvContent, 'user-directory.csv', 'text/csv;charset=utf-8;');
        } else if (format === 'excel') {
            let tableHtml = '<table border="1"><thead><tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Middle Name</th><th>Username</th></tr></thead><tbody>';
            usersData.forEach(u => {
                tableHtml += `<tr><td>${u.user_code}</td><td>${u.first_name}</td><td>${u.last_name}</td><td>${u.middle_name || ''}</td><td>${u.username}</td></tr>`;
            });
            tableHtml += '</tbody></table>';
            downloadFile(tableHtml, 'user-directory.xls', 'application/vnd.ms-excel');
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

    // -------------------------------------------------------------------------
    // 15. INITIALIZATION
    // -------------------------------------------------------------------------
    renderUserTable(usersData);
    renderComboboxItems('');
    buildObjectPermissionsTable();
    selectUserForPermission(null); // DEFAULT: WALA MUNANG USER NA NAKAPILI
});
</script>
@endpush

@endsection
