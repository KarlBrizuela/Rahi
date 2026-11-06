@extends('layouts.app')

@section('title', 'Product Category — Configuration')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/sidenav/sidenav.css') }}">
<link rel="stylesheet" href="{{ asset('css/Configuration/ProductCategory/productcategory.css') }}">
@endsection

@section('content')

@include('components.sidenav')

<div class="page-container" id="printableArea">

    <!-- 1. TOP UTILITY BAR (BREADCRUMB + CLEAN SEARCH, NO KBD) -->
    <header class="top-utility-bar no-print">
        <div class="breadcrumb-trail">
            <span class="bc-module">Configuration</span>
            <span class="bc-separator">/</span>
            <span class="bc-current">Product Category</span>
        </div>

        <div class="top-bar-right">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" id="categorySearchInput" placeholder="Search product category..." autocomplete="off">
            </div>
        </div>
    </header>

    <!-- 2. EXECUTIVE HEADER & ACTIONS (NO SUBTITLE PER UI.MD) -->
    <section class="page-header-row no-print">
        <div class="page-heading">
            <h1 class="page-title">Product Category Directory</h1>
        </div>

        <div class="action-cluster">
            <!-- Refresh Button (Simulated AJAX) -->
            <button type="button" class="btn-outline" id="btnRefresh" title="Reload product categories via AJAX">
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
                    <button type="button" class="export-option" onclick="exportCategoryDirectory('pdf')">
                        <i data-lucide="printer"></i>
                        <div class="opt-text">
                            <strong>Print / PDF</strong>
                            <small>Printable category directory</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCategoryDirectory('excel')">
                        <i data-lucide="sheet"></i>
                        <div class="opt-text">
                            <strong>Excel (.xls)</strong>
                            <small>Formatted workbook template</small>
                        </div>
                    </button>
                    <button type="button" class="export-option" onclick="exportCategoryDirectory('csv')">
                        <i data-lucide="file-spreadsheet"></i>
                        <div class="opt-text">
                            <strong>CSV (.csv)</strong>
                            <small>Standard comma-separated file</small>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Create Button (Opens Modal) -->
            <button type="button" class="btn-primary" id="btnOpenCreateModal">
                <i data-lucide="plus" class="btn-icon"></i>
                <span>Create Category</span>
            </button>
        </div>
    </section>

    <!-- 3. DATA TABLE PANEL -->
    <main class="page-content-wrapper">
        <div class="erp-table-panel">

            <!-- Panel Subheader -->
            <div class="table-panel-header no-print">
                <div class="panel-header-left">
                    <span class="panel-title">Product Categories</span>
                    <span class="panel-count-badge" id="recordCount">5 Records</span>
                </div>

                <div class="panel-header-right">
                    <span class="panel-status-note">Finished Goods & Assembly Classifications</span>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="erp-table" id="categoryTable">
                    <thead>
                        <tr>
                            <th>Product Category</th>
                            <th style="width: 220px;">Deleted Date</th>
                            <th style="width: 80px;" class="text-center no-print">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        <!-- Populated by JavaScript -->
                    </tbody>
                </table>
            </div>

            <!-- Table Footer Pagination / Meta -->
            <div class="table-panel-footer no-print">
                <span class="footer-meta-text" id="footerMetaText">Showing 1 to 5 of 5 category entries</span>
                <div class="pagination-mock">
                    <button type="button" class="page-btn disabled" disabled>Previous</button>
                    <button type="button" class="page-btn active">1</button>
                    <button type="button" class="page-btn disabled" disabled>Next</button>
                </div>
            </div>

        </div>
    </main>

</div>

<!-- ==========================================================================
     1. CREATE / EDIT PRODUCT CATEGORY MODAL (NO DELETED DATE IN FORM)
     ========================================================================== -->
<div class="modal-backdrop" id="categoryModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="modalTitle">

        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title" id="modalTitle">Create Product Category</h3>
                <p class="modal-subtitle">Specify product classification name for inventory and sales grouping.</p>
            </div>
            <button type="button" class="modal-close-btn" id="btnModalCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <form id="categoryForm" onsubmit="handleCategoryFormSubmit(event)">
            <input type="hidden" id="categoryFormId" value="">

            <div class="modal-body">
                <div class="form-group">
                    <label for="categoryName">Product Category <span class="required">*</span></label>
                    <input type="text" id="categoryName" class="form-input" placeholder="e.g. Finished Goods" required maxlength="100">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-outline" id="btnModalClose">Close</button>
                <button type="submit" class="btn-primary" id="btnModalSave">Save Category</button>
            </div>
        </form>

    </div>
</div>

<!-- ==========================================================================
     2. DELETE CONFIRMATION MODAL (NO ALERT)
     ========================================================================== -->
<div class="modal-backdrop" id="categoryDeleteModalBackdrop">
    <div class="modal-dialog modal-dialog-sm" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">

        <div class="modal-header modal-header-danger">
            <div class="danger-header-cluster">
                <div class="danger-icon-badge">
                    <i data-lucide="alert-triangle"></i>
                </div>
                <div class="modal-title-group">
                    <h3 class="modal-title" id="deleteModalTitle">Confirm Deletion</h3>
                    <p class="modal-subtitle">Sets the record status to deleted as of today.</p>
                </div>
            </div>
            <button type="button" class="modal-close-btn" id="btnDeleteCloseCross" title="Close modal">
                <i data-lucide="x"></i>
            </button>
        </div>

        <div class="modal-body">
            <p class="delete-warning-text">
                Are you sure you want to delete product category <strong id="deleteTargetCategoryName" class="delete-target-text">—</strong>?
            </p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn-outline" id="btnDeleteCancel">Cancel</button>
            <button type="button" class="btn-danger" id="btnConfirmDeleteAction">Delete Category</button>
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
// Mock in-memory records
let productCategoryRecords = [
    { id: 1, category: 'Finished Goods', deleted_date: '—' },
    { id: 2, category: 'Semi-Finished / WIP', deleted_date: '—' },
    { id: 3, category: 'Sub-Assemblies & Modules', deleted_date: '—' },
    { id: 4, category: 'Commercial Retail Packs', deleted_date: '—' },
    { id: 5, category: 'Promotional Items (Archived)', deleted_date: '2026-08-14' }
];

let pendingDeleteCategoryId = null;

document.addEventListener('DOMContentLoaded', function () {
    // Initial Render
    renderCategoryTable(productCategoryRecords);

    // Modal backdrops
    const categoryModalBackdrop = document.getElementById('categoryModalBackdrop');
    const categoryDeleteModalBackdrop = document.getElementById('categoryDeleteModalBackdrop');

    // Buttons
    const btnOpenCreate = document.getElementById('btnOpenCreateModal');
    const btnModalClose = document.getElementById('btnModalClose');
    const btnModalCloseCross = document.getElementById('btnModalCloseCross');

    const btnDeleteCancel = document.getElementById('btnDeleteCancel');
    const btnDeleteCloseCross = document.getElementById('btnDeleteCloseCross');
    const btnConfirmDeleteAction = document.getElementById('btnConfirmDeleteAction');

    const btnExportToggle = document.getElementById('btnExportToggle');
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');

    const btnRefresh = document.getElementById('btnRefresh');
    const refreshIcon = document.getElementById('refreshIcon');
    const searchInput = document.getElementById('categorySearchInput');

    // 1. Export Dropdown Toggle
    btnExportToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        exportDropdownWrapper.classList.toggle('active');
    });

    document.addEventListener('click', function (e) {
        if (!exportDropdownWrapper.contains(e.target)) {
            exportDropdownWrapper.classList.remove('active');
        }
    });

    // 2. Open Create Modal
    btnOpenCreate.addEventListener('click', function () {
        document.getElementById('categoryForm').reset();
        document.getElementById('categoryFormId').value = '';
        document.getElementById('modalTitle').textContent = 'Create Product Category';
        categoryModalBackdrop.classList.add('show');
    });

    function closeAllModals() {
        categoryModalBackdrop.classList.remove('show');
        categoryDeleteModalBackdrop.classList.remove('show');
        pendingDeleteCategoryId = null;
    }

    [btnModalClose, btnModalCloseCross, btnDeleteCancel, btnDeleteCloseCross].forEach(function (btn) {
        if (btn) btn.addEventListener('click', closeAllModals);
    });

    [categoryModalBackdrop, categoryDeleteModalBackdrop].forEach(function (backdrop) {
        if (backdrop) {
            backdrop.addEventListener('click', function (e) {
                if (e.target === backdrop) closeAllModals();
            });
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeAllModals();
    });

    // 3. Confirm Delete Action (Soft Delete with Date)
    btnConfirmDeleteAction.addEventListener('click', function () {
        if (pendingDeleteCategoryId !== null) {
            const item = productCategoryRecords.find(function (r) {
                return r.id === pendingDeleteCategoryId;
            });
            if (item) {
                const today = new Date().toISOString().split('T')[0];
                item.deleted_date = today;
                filterRecords();
            }
        }
        closeAllModals();
    });

    // 4. Search Filter
    function filterRecords() {
        if (!searchInput) return;
        const query = searchInput.value.toLowerCase().trim();
        const filtered = productCategoryRecords.filter(function (c) {
            const rowText = `${c.category} ${c.deleted_date}`.toLowerCase();
            return !query || rowText.includes(query);
        });
        renderCategoryTable(filtered);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterRecords);
    }

    // 5. Refresh (Simulated AJAX)
    btnRefresh.addEventListener('click', function () {
        refreshIcon.classList.add('spin-anim');
        btnRefresh.classList.add('loading');

        setTimeout(function () {
            filterRecords();
            refreshIcon.classList.remove('spin-anim');
            btnRefresh.classList.remove('loading');
        }, 350);
    });

    // Close any open action menu on outside click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.row-action-dropdown')) {
            document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
                m.classList.remove('show');
            });
        }
    });

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
});

// Toggle 3-Dots Action Menu
function toggleCategoryActionMenu(event, id) {
    event.stopPropagation();
    const menu = document.getElementById(`categoryActionMenu-${id}`);
    const isAlreadyOpen = menu && menu.classList.contains('show');
    document.querySelectorAll('.row-action-menu.show').forEach(function (m) {
        m.classList.remove('show');
    });
    if (menu && !isAlreadyOpen) {
        menu.classList.add('show');
    }
}

// Render Table
function renderCategoryTable(dataset) {
    const tbody = document.getElementById('categoryTableBody');
    tbody.innerHTML = '';

    if (!dataset.length) {
        tbody.innerHTML = `<tr><td colspan="3" class="empty-cell">No matching product categories found.</td></tr>`;
        document.getElementById('recordCount').textContent = `0 Records`;
        document.getElementById('footerMetaText').textContent = `Showing 0 of ${productCategoryRecords.length} entries`;
        return;
    }

    dataset.forEach(function (rec) {
        const tr = document.createElement('tr');
        const isDeleted = rec.deleted_date && rec.deleted_date !== '—';

        if (isDeleted) {
            tr.classList.add('row-deleted');
        }

        tr.innerHTML = `
            <td><strong class="category-name-text ${isDeleted ? 'text-faint' : ''}">${rec.category}</strong></td>
            <td>${isDeleted ? `<span class="deleted-date-tag">${rec.deleted_date}</span>` : `<span class="text-muted">—</span>`}</td>
            <td class="text-center no-print">
                <div class="row-action-dropdown">
                    <button type="button" class="row-menu-trigger" title="More Actions" onclick="toggleCategoryActionMenu(event, ${rec.id})">
                        <i data-lucide="more-horizontal"></i>
                    </button>
                    <div class="row-action-menu" id="categoryActionMenu-${rec.id}">
                        <button type="button" class="action-menu-item" onclick="viewCategoryRow(${rec.id})">
                            <i data-lucide="eye"></i>
                            <span>View</span>
                        </button>
                        <button type="button" class="action-menu-item" onclick="editCategoryRow(${rec.id})">
                            <i data-lucide="edit-3"></i>
                            <span>Edit</span>
                        </button>
                        <button type="button" class="action-menu-item item-danger" onclick="openCategoryDeleteModal(${rec.id})">
                            <i data-lucide="trash-2"></i>
                            <span>Delete</span>
                        </button>
                    </div>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('recordCount').textContent = `${dataset.length} Records`;
    document.getElementById('footerMetaText').textContent = `Showing 1 to ${dataset.length} of ${productCategoryRecords.length} product category entries`;

    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }
}

// View Category Modal
function viewCategoryRow(id) {
    const rec = productCategoryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('categoryFormId').value = rec.id;
    document.getElementById('categoryName').value = rec.category;

    document.getElementById('modalTitle').textContent = 'View Product Category — ' + rec.category;
    document.getElementById('categoryModalBackdrop').classList.add('show');
}

// Open Edit Modal
function editCategoryRow(id) {
    const rec = productCategoryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    document.getElementById('categoryFormId').value = rec.id;
    document.getElementById('categoryName').value = rec.category;

    document.getElementById('modalTitle').textContent = 'Edit Product Category — ' + rec.category;
    document.getElementById('categoryModalBackdrop').classList.add('show');
}

// Open Delete Modal
function openCategoryDeleteModal(id) {
    const rec = productCategoryRecords.find(function (item) {
        return item.id === id;
    });
    if (!rec) return;

    pendingDeleteCategoryId = id;
    document.getElementById('deleteTargetCategoryName').textContent = rec.category;
    document.getElementById('categoryDeleteModalBackdrop').classList.add('show');
}

// Handle Form Submit (Create / Edit)
function handleCategoryFormSubmit(event) {
    event.preventDefault();

    const formId = document.getElementById('categoryFormId').value;
    const category = document.getElementById('categoryName').value.trim();

    if (formId) {
        // Edit existing
        const targetId = parseInt(formId, 10);
        const existing = productCategoryRecords.find(function (r) {
            return r.id === targetId;
        });
        if (existing) {
            existing.category = category;
        }
    } else {
        // Create new
        const newId = productCategoryRecords.length ? Math.max.apply(null, productCategoryRecords.map(function (r) { return r.id; })) + 1 : 1;
        productCategoryRecords.push({
            id: newId,
            category: category,
            deleted_date: '—'
        });
    }

    const searchInput = document.getElementById('categorySearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const filtered = productCategoryRecords.filter(function (c) {
        const rowText = `${c.category} ${c.deleted_date}`.toLowerCase();
        return !query || rowText.includes(query);
    });

    renderCategoryTable(filtered);
    document.getElementById('categoryModalBackdrop').classList.remove('show');
}

// Export / Print Directory Function
function exportCategoryDirectory(format) {
    const exportDropdownWrapper = document.getElementById('exportDropdownWrapper');
    if (exportDropdownWrapper) {
        exportDropdownWrapper.classList.remove('active');
    }

    if (format === 'pdf') {
        window.print();
        return;
    }

    if (format === 'csv') {
        let csvContent = 'Product Category,Deleted Date\n';
        productCategoryRecords.forEach(function (r) {
            csvContent += `"${r.category}","${r.deleted_date}"\n`;
        });

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Product_Category_Directory_${new Date().toISOString().split('T')[0]}.csv`;
        link.click();
        return;
    }

    if (format === 'excel') {
        let rowsHtml = '';
        productCategoryRecords.forEach(function (r) {
            rowsHtml += `
                <tr>
                    <td>${r.category}</td>
                    <td>${r.deleted_date}</td>
                </tr>
            `;
        });

        const template = `
            <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
            <head>
                <meta charset="utf-8">
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th { background-color: #2563eb; color: #ffffff; font-weight: bold; border: 1px solid #1e40af; padding: 8px 10px; }
                    td { border: 1px solid #d1d5db; padding: 6px 10px; }
                </style>
            </head>
            <body>
                <h2>Intracode ERP — Product Category Directory</h2>
                <p>Export Date: ${new Date().toLocaleString()}</p>
                <table border="1">
                    <thead>
                        <tr>
                            <th>Product Category</th>
                            <th>Deleted Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </body>
            </html>
        `;

        const blob = new Blob([template], { type: 'application/vnd.ms-excel;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Product_Category_Directory_${new Date().toISOString().split('T')[0]}.xls`;
        link.click();
    }
}
</script>
@endpush

