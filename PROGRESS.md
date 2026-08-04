# ERP System — Progress Checklist

`ERP_Claude_Code_Master_Prompts.md` অনুযায়ী ১৩টি Phase এর কাজের অবস্থা।
যাচাই করা হয়েছে: migrations, models, controllers, services, routes, views, config — ২০২৬-০৮-০৪।

**সারসংক্ষেপ: ১৩/১৩ Phase সম্পন্ন।** কয়েকটি জায়গায় spec থেকে ইচ্ছাকৃত deviation আছে — নিচে "⚠️" দিয়ে চিহ্নিত।

---

## ⚙️ PHASE 1 — Foundation + Auth + Department + Role

- [x] Laravel + AdminLTE 3 (`jeroennoten/laravel-adminlte`) ইনস্টল
- [x] Spatie Laravel-Permission ইনস্টল (`create_permission_tables`)
- [x] `departments` migration (softDeletes সহ)
- [x] users টেবিলে department_id, employee_id, is_admin, status যোগ
- [x] Department model (HasMany users, SoftDeletes)
- [x] User model (BelongsTo Department, HasRoles)
- [x] `DatabaseSeeder` + `RolesAndPermissionsSeeder` — Super Admin, ৫টি role, সব permission
- [x] AdminLTE login page override, `/admin/dashboard` redirect, POST logout
- [x] Password change — `PasswordController` + `/admin/password/change`
- [x] Department CRUD (`DepartmentController`, DataTables, SweetAlert2 delete)
- [x] Role list (view only) — `RoleController`
- [x] `config/adminlte.php` sidebar menu
- [x] Dashboard cards — `DashboardController` + `dashboard.blade.php`

## 👥 PHASE 2 — Client/Agent + Employee Management

- [x] `App\Helpers\UniqueIdHelper.php` (YYMM+serial), composer autoload files এ registered
- [x] users টেবিলে phone, address, softDeletes migration
- [x] Employee CRUD — `EmployeeController` (list, create, edit, delete, reset-password)
- [x] `clients` migration + Client model (unique_id auto-generate, SoftDeletes)
- [x] Client CRUD — `ClientController` + reset-password
- [x] Client guard (`config/auth.php`) + `/client/login` + client dashboard
- [x] Client password change — `Client\PasswordController`
- [x] `messages` migration + Admin/Client messaging (`MessageController` দুই পাশেই)
- [x] Polling দিয়ে message refresh + unread badge
- [x] Password reset notification — `ClientPasswordResetNotification`, `StaffPasswordResetNotification`

## 📦 PHASE 3 — Category, Sub-Category + Product

- [x] `categories` migration (self-referencing parent_id, softDeletes)
- [x] `units` migration ⚠️ পরে `symbol` কলাম drop করা হয়েছে (`drop_symbol_from_units_table`)
- [x] `products` migration (mrp/purchase/sale price, VAT, expire alert flags)
- [x] `product_discounts` migration
- [x] Models: Category, Unit, Product, ProductDiscount
- [x] Category CRUD — `CategoryController`
- [x] Unit CRUD — `UnitController`
- [x] Product CRUD — `ProductController` (AJAX sub-category, MRP admin-only, image upload)
- [x] Product Discount CRUD — `ProductDiscountController`
- [x] Product report + Excel/PDF export (`products/report`, `ProductsExport`)
- [x] Product search AJAX — `products/search`
- [x] `maatwebsite/excel` + `barryvdh/laravel-dompdf` ইনস্টল
- [x] ➕ অতিরিক্ত: `product_images` টেবিল + `ProductImage` model (multi-image)

## 📊 PHASE 4 — Stock Management

- [x] `stores`, `stocks`, `stock_movements` migrations
- [x] Models: Store, Stock, StockMovement
- [x] Store CRUD — `StoreController`
- [x] Stock list + filter + CRUD — `StockController`
- [x] `min_stock_threshold` products টেবিলে যোগ
- [x] Low quantity list — `/admin/stocks/low-quantity`
- [x] Expiry lists — `/admin/stocks/expiry/one-month`, `/three-month`
- [x] `App\Services\StockService` (addStock, deductStock FIFO, getAvailableStock, logMovement)

## 🛒 PHASE 5 — Purchase Module

- [x] `suppliers`, `purchases`, `purchase_items`, `purchase_returns`, `purchase_return_items` migrations
- [x] Models: Supplier, Purchase, PurchaseItem, PurchaseReturn, PurchaseReturnItem
- [x] Supplier CRUD — `SupplierController`
- [x] Purchase CRUD — `PurchaseController` (dynamic rows, VAT, paid/due, StockService integration)
- [x] Purchase Return — `PurchaseReturnController` + `PurchaseReturnListController`
- [x] Purchase report + Excel/PDF (`purchases/report`, `PurchasesExport`)
- [x] `cash_bank_transactions` migration + `CashBankService` skeleton

## 🧾 PHASE 6 — Sales Module

- [x] `sales`, `sale_items` migrations
- [x] Role rules: `sale.client_agent`, `sale.discount` permissions + `SalePolicy`
- [x] Sale CRUD — `SaleController` (index/create/edit/destroy/show)
- [x] Create form: local vs client-agent toggle, AJAX product/client search, stock validation
- [x] Store এ StockService::deductStock + CashBankService::credit
- [x] Sale report + Excel/PDF (`sales/report`, `SalesExport`)
- [x] ➕ অতিরিক্ত: Sale Return module (`sale_returns` tables, `SaleReturnController`)

## 📋 PHASE 7 — Order Management

- [x] `orders`, `order_items`, `order_status_logs`, `feedback`, `packagings` migrations
- [x] Models: Order, OrderItem, OrderStatusLog, Feedback, Packaging
- [x] Client side — `Client\OrderController` (index, create/cart, store, show, cancel, feedback)
- [x] Client dashboard widgets — `Client\DashboardController`
- [x] Admin side — `Admin\OrderController` (index, show, accept, reject, update-status, pending)
- [x] Payment validation (bank → receipt, mobile banking → transaction reference)
- [x] Packaging record + `/admin/orders/{order}/pack`
- [x] `OrderPolicy` + order search

## 💳 PHASE 8 — Payment Method + Cash/Bank Integration

- [x] `cash_bank_transactions` finalize + `cash_transfers` + `opening_balances` migrations
- [x] সম্পূর্ণ `CashBankService` (credit, debit, transfer, balances, history)
- [x] `/admin/cash-bank` — `CashBankController` (index, transactions, transfer, history)
- [x] `accounts` migration (payable/receivable) + `Account` model + `AccountPolicy`
- [x] `AccountController` — payable/receivable list, settle, manual CRUD
- [x] `salaries` migration + `SalaryController` + cash/bank debit
- [x] Dashboard cash/bank widgets
- [x] ➕ অতিরিক্ত: Party payment ledger (`party_payments` tables, `PartyLedgerService`,
      `PartyPaymentController`, `SupplierPaymentController`, `CustomerPaymentController`)

## 🔄 PHASE 9 — Store, Delivery + Return Management

- [x] `store_dispatch_logs` migration + `StoreDispatchController` (dispatch-queue, dispatch)
- [x] `deliveries` migration + `DeliveryController` (out, delivered, failed)
- [x] `return_types`, `order_returns`, `return_items`, `damage_logs` migrations
      ⚠️ `returns` টেবিলের নাম `order_returns` (MySQL reserved word এড়াতে)
- [x] Models: StoreDispatchLog, Delivery, ReturnType, OrderReturn, ReturnItem, DamageLog
- [x] Client returns — `Client\ReturnController`
- [x] Admin returns — `Admin\OrderReturnController` (approve/reject/refund)
- [x] Return type CRUD — `ReturnTypeController`
- [x] Restock vs damage disposition logic
- [x] Feedback list — `FeedbackController`

## 💰 PHASE 10 — Expenses + Asset Management

- [x] `expense_heads`, `expenses` migrations
- [x] Expense CRUD — `ExpenseController` + CashBankService debit/reverse
- [x] Expense Head CRUD — `ExpenseHeadController`
- [x] Expense report + Excel/PDF (`expenses/report`, `ExpensesExport`)
- [x] `assets` migration + `Asset` model
- [x] Asset CRUD — `AssetController` (invoice file upload, show page)
- [x] Asset report + Excel/PDF (`assets/report`, `AssetsExport`)

## 📊 PHASE 11 — Reports + Invoices

- [x] Sales Report ⚠️ route `/admin/sales/report` (spec এ ছিল `/admin/reports/sales`)
- [x] Purchase Report ⚠️ route `/admin/purchases/report`
- [x] Expense Report ⚠️ route `/admin/expenses/report`
- [x] Profit Report — `/admin/reports/profit` (COGS, gross/net profit, VAT)
- [x] Stock Report — `/admin/reports/stock`
- [x] Order Report — `/admin/reports/orders`
- [x] Reports index page — `/admin/reports`
- [x] `invoices` migration + `Invoice` model + `InvoiceController`
- [x] Invoice Blade templates: sale, purchase, expense, order (+ shared layout)
- [x] `?format=pdf` / `?format=html` support
- [x] Date preset buttons (Today / Week / Month / Last Month / Year)
- [x] Export classes: Sales, Purchases, Expenses, Profit, Stock, Orders, Products, Assets

## 🔍 PHASE 12 — Search + Notifications + Communication

- [x] `SearchController` — suppliers, orders, sales, returns, stocks, global
- [x] Client search — `/admin/clients/search`; Product search — `/admin/products/search`
      ⚠️ SearchController এর বাইরে নিজ নিজ module controller এ রাখা হয়েছে
- [x] `notifications` migration (Laravel database notifications)
- [x] Notification classes: OrderSubmitted, OrderStatusChanged, MessageReceived, LowStock
- [x] Admin notification bell + `/admin/notifications` + poll + mark read
- [x] Client notification bell — `Client\NotificationController`
- [x] Chat UI polish + conversation resolved flag (`add_conversation_resolved_to_clients_table`)
- [x] `config/erp.php` — notification toggles
- [x] Queue (database driver) — `jobs` table
- [x] Dashboard charts (Chart.js): sales chart, top products, order status, activity feed, quick buttons

## 🚀 PHASE 13 — Final Polish + Security + Optimization

- [x] Policies: ProductPolicy, SalePolicy, OrderPolicy, PurchasePolicy, AccountPolicy
- [x] Middleware: `AdminOnly`, `ClientAuth`, `CheckPermission`, `EnsureUserIsAdminOrAccountant`
- [x] `spatie/laravel-activitylog` ইনস্টল + migrations + `/admin/activity-log`
- [x] `settings` migration + `Setting` model + `SettingsController` + `SettingsSeeder`
- [x] FormRequest validation + file upload hardening (`MediaService`)
- [x] Performance indexes — `add_performance_indexes` migration
- [x] `php artisan erp:backup` command + `bootstrap/app.php` এ daily schedule
- [x] `CHECKLIST.md` তৈরি (env, storage:link, queue, credentials, seeders, deployment notes)

---

## ✅ পরবর্তীতে সম্পন্ন (২০২৬-০৮-০৪)

- [x] **yajra server-side DataTables** — `yajra/laravel-datatables-oracle` v13.1 ইনস্টল;
      ২৪টি admin listing পেজ server-side pagination/sort/search এ রূপান্তরিত
    - শেয়ার্ড হেল্পার `ERP.serverTable()` — [public/assets/js/app.js](public/assets/js/app.js)
    - প্রতিটি listing controller এ `indexData()` — ajax হলে DataTables JSON, নাহলে view
    - row-cell গুলো Blade partial এ (`resources/views/admin/*/partials/`), তাই ফরম্যাটিং Blade-এই রইল
    - delete/confirm handler আগে থেকেই document-delegated, তাই AJAX-এ আসা row কাজ করে
    - `cash-bank/transactions` এর type ও reference ফিল্টার আগে collection এ চলত — এখন SQL এ
    - `party-payments` এর balance হিসাব `PartyLedgerService`-এই রইল (money maths অপরিবর্তিত),
      পেজিং `DataTables::collection()` দিয়ে
- [x] **Report route alias** — `/admin/reports/sales`, `/purchases`, `/expenses` যোগ (redirect,
      তাই permission যাচাই target route-এই হয়)
- [x] **Search endpoint একত্রীকরণ** — `SearchController@clients`/`@products` canonical;
      `/admin/search/clients`, `/admin/search/products` যোগ; পুরনো
      `/admin/clients/search`, `/admin/products/search` alias হিসেবে অক্ষত।
      spec অনুযায়ী product search এ `expiry_dates` যোগ হয়েছে।
- [x] **টেস্ট** — [tests/Feature/DataTableListingTest.php](tests/Feature/DataTableListingTest.php):
      ২৬টি endpoint × (page render + DataTables payload + search) + real-row rendering। ৮১টি পাস।

## ⚠️ Spec থেকে জ্ঞাত পার্থক্য

| বিষয় | Spec | বাস্তবায়ন | প্রভাব |
|---|---|---|---|
| Returns টেবিল | `returns` | `order_returns` | MySQL reserved word এড়ানো — ইচ্ছাকৃত, রাখা হয়েছে |
| Units | `symbol` কলাম | drop করা হয়েছে | শুধু name ব্যবহৃত — ইচ্ছাকৃত, রাখা হয়েছে |

## 📝 জানা সীমাবদ্ধতা

- `tests/Feature/Auth/*`, `ProfileTest`, `ExampleTest` — Breeze scaffolding এর ডিফল্ট টেস্ট,
  `dashboard`/`register`/`profile.edit` route ধরে নেয় যেগুলো এই ERP তে নেই। ১৮টি ব্যর্থ হয়,
  এই কাজের আগেও হত। মুছে ফেলা বা ERP-এর route অনুযায়ী লেখা দরকার।
