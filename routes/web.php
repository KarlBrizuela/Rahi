<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login.login');
});

Route::get('/dashboard', function () {
    return view('Dashboard.Dashboard.dashboard');
})->name('dashboard');

Route::get('/overview', function () {
    return redirect()->route('dashboard');
});



Route::post('/Sales-Order',function(){
    return redirect()->route('salesorder');
})->name('login.submit');


Route::get('/Sales-Order',function(){
    return view('sales.SalesOrder.salesorder');
})->name('salesorder');

Route::get('/sales/sales-order', function(){
    return view('sales.SalesOrder.salesorder');
});

Route::get('/sales/invoice', function () {
    return view('sales.Invoice.invoice');
})->name('invoice.index');

Route::get('/sales/sales-report', function () {
    return view('sales.SalesReport.salesreport');
})->name('salesreport.index');

Route::get('/sales/delivery-receipt', function () {
    return view('sales.DeliveryReceipt.deliveryreceipt');
})->name('deliveryreceipt.index');

Route::get('/sales/shipping-invoice', function () {
    return view('sales.ShippingInvoice.shippinginvoice');
})->name('shippinginvoice.index');

Route::get('/sales/material-requirement-plan', function () {
    return view('sales.MaterialRequirementPlan.materialrequirementplan');
})->name('mrp.index');

Route::get('/production/output', function () {
    return view('Production.ProdOutput.prodoutput');
})->name('production.output.index');

Route::get('/production/output-inventory', function () {
    return view('Production.ProdOutputInv.prodoutputinv');
})->name('production.outputinv.index');

Route::get('/production/fgts', function () {
    return view('Production.FGTS.fgts');
})->name('production.fgts.index');

Route::get('/warehouse/inventory/fg', function () {
    return view('Warehouse.Inventory.fg');
})->name('warehouse.inventory.fg');

Route::get('/warehouse/inventory/supplies', function () {
    return view('Warehouse.Inventory.supplies');
})->name('warehouse.inventory.supplies');

Route::get('/warehouse/finished-goods/dts', function () {
    return view('Warehouse.FinishedGoods.dts');
})->name('warehouse.fg.dts');

Route::get('/warehouse/finished-goods/adjustment', function () {
    return view('Warehouse.FinishedGoods.adjustment');
})->name('warehouse.fg.adjustment');

Route::get('/warehouse/finished-goods/receiving', function () {
    return view('Warehouse.FinishedGoods.receiving');
})->name('warehouse.fg.receiving');

Route::get('/warehouse/finished-goods/stock', function () {
    return view('Warehouse.FinishedGoods.stock');
})->name('warehouse.fg.stock');

Route::get('/warehouse/supplies/adjustment', function () {
    return view('Warehouse.Supplies.adjustment');
})->name('warehouse.supplies.adjustment');

Route::get('/warehouse/supplies/issuance', function () {
    return view('Warehouse.Supplies.issuance');
})->name('warehouse.supplies.issuance');

Route::get('/warehouse/supplies/return', function () {
    return view('Warehouse.Supplies.return');
})->name('warehouse.supplies.return');

Route::get('/warehouse/supplies/stock', function () {
    return view('Warehouse.Supplies.stock');
})->name('warehouse.supplies.stock');

Route::get('/warehouse/supplies/stock-alias', function () {
    return redirect()->route('warehouse.supplies.stock');
})->name('warehouse.supplies.stock.index');

Route::get('/purchasing/purchase-request', function () {
    return view('Purchasing.PurchaseRequest.purchaserequest');
})->name('purchasing.pr.index');

Route::get('/purchasing/purchase-order', function () {
    return view('Purchasing.PurchaseOrder.purchaseorder');
})->name('purchasing.po.index');

Route::get('/purchasing/supplier', function () {
    return view('Purchasing.Supplier.supplier');
})->name('purchasing.supplier.index');

Route::get('/purchasing/supplies', function () {
    return view('Purchasing.Supplies.supplies');
})->name('purchasing.supplies.index');

Route::get('/sales/customer', function () {
    return view('sales.Customer.customer');
})->name('customer.index');

Route::get('/sales/product', function () {
    return view('sales.Product.product');
})->name('product.index');

Route::get('/sidenav',function(){
    return view('components.sidenav');
})->name('sidenav');

Route::get('/configuration/atc', function () {
    return view('Configuration.ATC.atc');
})->name('atc.index');

Route::get('/configuration/audit-trail', function () {
    return view('Configuration.AuditTrail.audittrail');
})->name('audittrail.index');

Route::get('/configuration/bank', function () {
    return view('Configuration.Bank.bank');
})->name('bank.index');

Route::get('/configuration/branch', function () {
    return view('Configuration.Branch.branch');
})->name('branch.index');

Route::get('/configuration/country', function () {
    return view('Configuration.Country.country');
})->name('country.index');

Route::get('/configuration/currency', function () {
    return view('Configuration.Currency.currency');
})->name('currency.index');

Route::get('/configuration/customer-category', function () {
    return view('Configuration.CustomerCategory.customercategory');
})->name('customercategory.index');

Route::get('/configuration/payment-terms', function () {
    return view('Configuration.PaymentTerm.paymentterm');
})->name('paymentterm.index');

Route::get('/configuration/product-category', function () {
    return view('Configuration.ProductCategory.productcategory');
})->name('productcategory.index');

Route::get('/configuration/supplies-category', function () {
    return view('Configuration.SuppliesCategory.suppliescategory');
})->name('suppliescategory.index');

Route::get('/configuration/unit-of-product', function () {
    return view('Configuration.UnitOfProduct.unitofproduct');
})->name('unitofproduct.index');

Route::get('/configuration/user', function () {
    return view('Configuration.User.user');
})->name('user.index');

Route::get('/configuration/vat', function () {
    return view('Configuration.VAT.vat');
})->name('vat.index');

Route::get('/configuration/warehouse', function () {
    return view('Configuration.Warehouse.warehouse');
})->name('warehouse.index');

Route::get('/configuration/warehouse-location', function () {
    return view('Configuration.WarehouseLocation.warehouselocation');
})->name('warehouselocation.index');
