<?php

use App\Http\Controllers\Admin\AccountController;
use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\AssetController;
use App\Http\Controllers\Admin\CashBankController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseHeadController;
use App\Http\Controllers\Admin\FeedbackController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\OrderReturnController;
use App\Http\Controllers\Admin\PasswordController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductDiscountController;
use App\Http\Controllers\Admin\PurchaseController;
use App\Http\Controllers\Admin\PurchaseReturnController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReturnTypeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SalaryController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StockController;
use App\Http\Controllers\Admin\StoreController;
use App\Http\Controllers\Admin\StoreDispatchController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UnitController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    // Intentionally ungated: this is the fallback landing page for users
    // without `dashboard.view`, so it must never 403.
    Route::get('home', [HomeController::class, 'index'])->name('home');

    Route::middleware('check.permission:dashboard.view')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

    Route::get('password/change', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('password/change', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware('check.permission:department.view')->group(function () {
        Route::resource('departments', DepartmentController::class)->except(['show']);
    });

    Route::middleware('check.permission:role.view')->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    });

    Route::middleware('check.permission:role.create')->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });

    Route::middleware('check.permission:role.edit')->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });

    Route::middleware('check.permission:role.delete')->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });

    Route::middleware('check.permission:user.view')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::post('employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])
            ->name('employees.reset-password');
        Route::post('employees/{employee}/toggle-status', [EmployeeController::class, 'toggleStatus'])
            ->name('employees.toggle-status');
    });

    // Left ungated: clients/search is used by Employee/Local Seller roles when
    // picking a client/agent while creating a sale.
    Route::get('clients/search', [ClientController::class, 'search'])->name('clients.search');

    Route::middleware('check.permission:client.view')->group(function () {
        Route::resource('clients', ClientController::class);
        Route::post('clients/{client}/reset-password', [ClientController::class, 'resetPassword'])
            ->name('clients.reset-password');
    });

    Route::middleware('check.permission:message.view')->group(function () {
        Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
        Route::get('messages/{client}', [MessageController::class, 'show'])->name('messages.show');
        Route::post('messages/{client}', [MessageController::class, 'store'])->name('messages.store');
        Route::post('messages/{client}/resolve', [MessageController::class, 'resolve'])->name('messages.resolve');
    });

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/poll', [NotificationController::class, 'poll'])->name('notifications.poll');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::prefix('search')->name('search.')->group(function () {
        Route::get('global', [SearchController::class, 'global'])->name('global');
        Route::get('suppliers', [SearchController::class, 'suppliers'])->name('suppliers');
        Route::get('orders', [SearchController::class, 'orders'])->name('orders');
        Route::get('sales', [SearchController::class, 'sales'])->name('sales');
        Route::get('returns', [SearchController::class, 'returns'])->name('returns');
        Route::get('stocks', [SearchController::class, 'stocks'])->name('stocks');
    });

    // Left ungated: products/search is an AJAX lookup used while building
    // purchase and sale item rows, so it must stay reachable for those roles.
    // Must stay above the products resource so it is not matched as products/{product}.
    Route::get('products/search', [ProductController::class, 'search'])->name('products.search');

    Route::middleware('check.permission:product.view')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'subcategories'])
            ->name('categories.subcategories');

        Route::resource('units', UnitController::class)->except(['show']);

        Route::get('products/report', [ProductController::class, 'report'])->name('products.report');
        Route::get('products/report/excel', [ProductController::class, 'reportExcel'])->name('products.report.excel');
        Route::get('products/report/pdf', [ProductController::class, 'reportPdf'])->name('products.report.pdf');

        Route::resource('products', ProductController::class);

        Route::resource('products.discounts', ProductDiscountController::class)
            ->parameters(['discounts' => 'discount'])
            ->except(['show']);
    });

    Route::middleware('admin.only')->group(function () {
        Route::resource('stores', StoreController::class)->except(['show']);
    });

    Route::middleware('check.permission:stock.view')->group(function () {
        Route::get('stocks/low-quantity', [StockController::class, 'lowQuantity'])->name('stocks.low-quantity');
        Route::get('stocks/expiry/one-month', [StockController::class, 'expiryOneMonth'])->name('stocks.expiry.one-month');
        Route::get('stocks/expiry/three-month', [StockController::class, 'expiryThreeMonth'])->name('stocks.expiry.three-month');
        Route::resource('stocks', StockController::class)->except(['show']);
    });

    Route::middleware('check.permission:purchase.view')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    Route::middleware('check.permission:purchase.view')->group(function () {
        // Must stay above the purchases resource so they are not matched as purchases/{purchase}.
        Route::get('purchases/report', [PurchaseController::class, 'report'])->name('purchases.report');
        Route::get('purchases/report/excel', [PurchaseController::class, 'reportExcel'])->name('purchases.report.excel');
        Route::get('purchases/report/pdf', [PurchaseController::class, 'reportPdf'])->name('purchases.report.pdf');

        Route::resource('purchases', PurchaseController::class);
    });

    Route::get('purchases/{purchase}/returns', [PurchaseReturnController::class, 'index'])->name('purchases.returns.index');
    Route::get('purchases/{purchase}/returns/create', [PurchaseReturnController::class, 'create'])->name('purchases.returns.create');
    Route::post('purchases/{purchase}/returns', [PurchaseReturnController::class, 'store'])->name('purchases.returns.store');
    Route::get('purchases/{purchase}/returns/{return}/edit', [PurchaseReturnController::class, 'edit'])->name('purchases.returns.edit');
    Route::put('purchases/{purchase}/returns/{return}', [PurchaseReturnController::class, 'update'])->name('purchases.returns.update');
    Route::delete('purchases/{purchase}/returns/{return}', [PurchaseReturnController::class, 'destroy'])->name('purchases.returns.destroy');

    Route::get('sales/report', [SaleController::class, 'report'])->name('sales.report');
    Route::get('sales/report/excel', [SaleController::class, 'reportExcel'])->name('sales.report.excel');
    Route::get('sales/report/pdf', [SaleController::class, 'reportPdf'])->name('sales.report.pdf');

    Route::resource('sales', SaleController::class);

    Route::get('orders/pending', [OrderController::class, 'pending'])->name('orders.pending');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('orders/{order}/accept', [OrderController::class, 'accept'])->name('orders.accept');
    Route::post('orders/{order}/reject', [OrderController::class, 'reject'])->name('orders.reject');
    Route::post('orders/{order}/update-status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::post('orders/{order}/pack', [OrderController::class, 'pack'])->name('orders.pack');

    Route::middleware('check.permission:cash.view')->group(function () {
        Route::get('cash-bank', [CashBankController::class, 'index'])->name('cash-bank.index');
        Route::get('cash-bank/transactions', [CashBankController::class, 'transactions'])->name('cash-bank.transactions');
        Route::get('cash-bank/transfer', [CashBankController::class, 'transferForm'])->name('cash-bank.transfer.form');
        Route::post('cash-bank/transfer', [CashBankController::class, 'transfer'])->name('cash-bank.transfer');
        Route::get('cash-bank/transfer-history', [CashBankController::class, 'transferHistory'])->name('cash-bank.transfer-history');
    });

    Route::get('accounts/payable', [AccountController::class, 'payable'])->name('accounts.payable');
    Route::get('accounts/receivable', [AccountController::class, 'receivable'])->name('accounts.receivable');
    Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::put('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::post('accounts/{account}/settle', [AccountController::class, 'settle'])->name('accounts.settle');

    Route::middleware('check.permission:cash.view')->group(function () {
        Route::resource('salaries', SalaryController::class)->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('check.permission:delivery.view')->group(function () {
        Route::get('store/dispatch-queue', [StoreDispatchController::class, 'dispatchQueue'])->name('store.dispatch-queue');
        Route::post('store/dispatch/{order}', [StoreDispatchController::class, 'dispatch'])->name('store.dispatch');

        Route::get('delivery', [DeliveryController::class, 'index'])->name('deliveries.index');
        Route::post('delivery/{delivery}/out', [DeliveryController::class, 'out'])->name('deliveries.out');
        Route::post('delivery/{delivery}/delivered', [DeliveryController::class, 'delivered'])->name('deliveries.delivered');
        Route::post('delivery/{delivery}/failed', [DeliveryController::class, 'failed'])->name('deliveries.failed');
    });

    Route::middleware('check.permission:return.view')->group(function () {
        Route::resource('return-types', ReturnTypeController::class)->except(['show']);

        Route::get('returns', [OrderReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{return}', [OrderReturnController::class, 'show'])->name('returns.show');
        Route::post('returns/{return}/approve', [OrderReturnController::class, 'approve'])->name('returns.approve');
        Route::post('returns/{return}/reject', [OrderReturnController::class, 'reject'])->name('returns.reject');

        Route::get('feedbacks', [FeedbackController::class, 'index'])->name('feedbacks.index');
    });

    Route::middleware('check.permission:expense.view')->group(function () {
        Route::resource('expense-heads', ExpenseHeadController::class)->except(['show']);

        Route::get('expenses/report', [ExpenseController::class, 'report'])->name('expenses.report');
        Route::get('expenses/report/excel', [ExpenseController::class, 'reportExcel'])->name('expenses.report.excel');
        Route::get('expenses/report/pdf', [ExpenseController::class, 'reportPdf'])->name('expenses.report.pdf');

        Route::resource('expenses', ExpenseController::class)->except(['show']);
    });

    Route::middleware('admin.only')->group(function () {
        Route::get('assets/report', [AssetController::class, 'report'])->name('assets.report');
        Route::get('assets/report/excel', [AssetController::class, 'reportExcel'])->name('assets.report.excel');
        Route::get('assets/report/pdf', [AssetController::class, 'reportPdf'])->name('assets.report.pdf');

        Route::resource('assets', AssetController::class);
    });

    Route::middleware('check.permission:report.view')->group(function () {
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');

        Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
        Route::get('reports/profit/excel', [ReportController::class, 'profitExcel'])->name('reports.profit.excel');
        Route::get('reports/profit/pdf', [ReportController::class, 'profitPdf'])->name('reports.profit.pdf');

        Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
        Route::get('reports/stock/excel', [ReportController::class, 'stockExcel'])->name('reports.stock.excel');
        Route::get('reports/stock/pdf', [ReportController::class, 'stockPdf'])->name('reports.stock.pdf');

        Route::get('reports/orders', [ReportController::class, 'orders'])->name('reports.orders');
        Route::get('reports/orders/excel', [ReportController::class, 'ordersExcel'])->name('reports.orders.excel');
        Route::get('reports/orders/pdf', [ReportController::class, 'ordersPdf'])->name('reports.orders.pdf');

    });

    Route::middleware('check.permission:invoice.view')->group(function () {
        Route::get('invoices/sale/{sale}', [InvoiceController::class, 'sale'])->name('invoices.sale');
        Route::get('invoices/purchase/{purchase}', [InvoiceController::class, 'purchase'])->name('invoices.purchase');
        Route::get('invoices/expense/{expense}', [InvoiceController::class, 'expense'])->name('invoices.expense');
        Route::get('invoices/order/{order}', [InvoiceController::class, 'order'])->name('invoices.order');
    });

    Route::middleware('admin.only')->group(function () {
        Route::get('activity-log', [ActivityLogController::class, 'index'])->name('activity-log.index');

        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
    });
});
