# ERP System — Claude Code Master Prompts (13 Phases)

### Stack: Laravel 11 + AdminLTE 3 + Spatie Permission + MySQL

> **নির্দেশনা:** প্রতিটা Phase একটি আলাদা Claude Code session এ দাও।
> প্রতিটা Phase শেষ হলে `git commit` করো, তারপর পরের Phase দাও।
> কোনো Phase এ error হলে সেই Phase এর prompt এর শেষে error message যোগ করে আবার দাও।

---

## ⚙️ PHASE 1 — Project Foundation + Auth + Department + Role

```
You are a senior Laravel developer. Set up a new Laravel 11 project with the following stack and configuration. Follow each step exactly.

### STACK
- Laravel 11 (fresh install assumed, files already exist)
- AdminLTE 3 via jeroennoten/laravel-adminlte package
- Laravel Breeze (Blade stack, no Inertia)
- Spatie Laravel-Permission package
- MySQL database

### STEP 1: Install Required Packages
Run these commands:
```

composer require jeroennoten/laravel-adminlte
composer require spatie/laravel-permission
php artisan adminlte:install
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate

```

### STEP 2: Database — Create Migrations
Create migrations in this exact order:


**departments table:**
- id, name (string, unique), description (text, nullable), timestamps, softDeletes

**Update users table migration (or create new migration to add columns):**
- Add: department_id (foreignId, nullable), employee_id (string, nullable), is_admin (boolean, default false), status (boolean, default true)

### STEP 3: Models
- **Department** model: HasMany users, SoftDeletes
- **User** model: Add BelongsTo Department, use HasRoles (Spatie), add fillable fields

### STEP 4: Seeders
Create `DatabaseSeeder` that runs:
1. Create Super Admin user: name="Super Admin", email="admin@erp.com", password="Admin@1234", is_admin=true
2. Create roles: "Admin", "Accountant", "Employee", "Local Seller", "Store Manager"
3. Create basic permissions (list below) and assign all to Admin role
4. Assign Admin role to the super admin user

Permissions to create:
- department.view, department.create, department.edit, department.delete
- role.view, user.view, user.create, user.edit, user.delete
- client.view, client.create, client.edit, client.delete
- product.view, product.create, product.edit, product.delete
- stock.view, stock.create, stock.edit, stock.delete
- purchase.view, purchase.create, purchase.edit, purchase.delete
- sale.view, sale.create, sale.edit, sale.delete
- order.view, order.create, order.edit, order.delete
- expense.view, expense.create, expense.edit, expense.delete
- cash.view, cash.create, cash.edit, cash.delete
- asset.view, asset.create, asset.edit, asset.delete
- report.view, invoice.view

### STEP 5: Auth Setup
- Configure AdminLTE login page (override the default)
- Login with email + password (Admin only, no self-registration)
- After login redirect to `/admin/dashboard`
- Logout route: POST /logout

### STEP 6: Password Change
Create route: GET/POST `/admin/password/change`
- Form: current_password, new_password, new_password_confirmation
- Validate current password before updating
- Show success/error flash message

### STEP 7: Department CRUD
Routes (prefix: `/admin/departments`):
- index: list with DataTables
- create/store: name + description
- edit/update
- destroy (soft delete)
- Validate: name required, unique
- Use SweetAlert2 for delete confirmation

### STEP 8: Role List
Route: GET `/admin/roles` — show all roles from Spatie roles table (view only, no create/edit/delete from UI)

### STEP 9: AdminLTE Sidebar Menu
Configure `config/adminlte.php` sidebar with:
- Dashboard
- User Management (Departments, Roles, Employees)
- Clients/Agents
- Products (Categories, Products, Stock)
- Purchases
- Sales
- Orders
- Store & Delivery
- Returns
- Expenses
- Cash & Bank
- Assets
- Reports & Invoices

### STEP 10: Dashboard

Route: GET `/admin/dashboard`
Show cards: Total Clients, Total Products, Total Stock Value, Today's Sales, Pending Orders, Low Stock Alert count

Run `php artisan db:seed` at the end to verify seeder works.

Generate all Controllers in `App\Http\Controllers\Admin\` namespace.
Use Form Request classes for validation.
Use resource routes where appropriate.
All views go in `resources/views/admin/` directory.
```

---

## 👥 PHASE 2 — Client/Agent Module + Employee Management

```
You are a senior Laravel developer working on an existing Laravel 11 + AdminLTE + Spatie Permission project. The foundation (Phase 1) is already complete.

### TASK: Build Client/Agent Module + Employee Management

### UNIQUE ID LOGIC (reusable helper)
Create a helper function `generateUniqueId($model, $prefix='')`:
- Format: YY + MM + 4-digit serial (auto-increment per month)
- Example: Client created in April 2021, 1st of month → "2104001"
- Store in `unique_id` column (string)
- Helper goes in `App\Helpers\UniqueIdHelper.php`
- Register in composer.json autoload files array

### EMPLOYEE (User) MANAGEMENT
Migration additions (if not done): department_id, employee_id (auto-generated unique ID), is_admin, status, phone (nullable), address (text, nullable)

Routes (prefix: `/admin/employees`):
- index: DataTables list showing name, employee_id, department, role, status
- create/store: name, email, password, phone, department_id, role (single Spatie role)
- edit/update: all fields except password (separate password reset)
- destroy (soft delete)
- POST `/admin/employees/{id}/reset-password`: Admin resets employee password

Constraints:
- Admin cannot create another Admin from UI (is_admin=false always for employees)
- Employee role list: Accountant, Employee, Local Seller, Store Manager
- Status toggle (active/inactive)

### CLIENT/AGENT MANAGEMENT
**clients table migration:**
- id, unique_id (string, generated), name, email, phone, address (text),
  business_name (nullable), type (enum: client, agent),
  password (hashed), status (boolean, default true),
  profile_photo (nullable), timestamps, softDeletes

**Client model:** SoftDeletes, unique_id generation on creating event

Routes (prefix: `/admin/clients`):
- index: DataTables with search by ID or name
- create/store: unique_id auto-generated, name, email, phone, address, business_name, type, password
- edit/update
- destroy (soft delete)
- POST `/admin/clients/{id}/reset-password`: generate random password, email it to client

### CLIENT AUTH (separate guard)
- Add 'client' guard in `config/auth.php`
- Login route: GET/POST `/client/login`
- After login: redirect to `/client/dashboard`
- Client dashboard: show their orders, order status, notifications
- Client can change their own password: GET/POST `/client/password/change`

### ADMIN-CLIENT MESSAGING
**messages table migration:**
- id, sender_type (enum: admin, client), sender_id, receiver_type, receiver_id,
  message (text), is_read (boolean, default false), timestamps

Routes:
- GET `/admin/messages` — Admin sees all conversations (grouped by client)
- GET `/admin/messages/{clientId}` — Admin views + replies to specific client
- POST `/admin/messages/{clientId}` — Admin sends message
- GET `/client/messages` — Client sees their conversation with admin
- POST `/client/messages` — Client sends message to admin

Use polling (setInterval every 10s) to refresh messages without page reload.
Show unread message count badge in sidebar.

### NOTIFICATION
**notifications table (use Laravel built-in notifications or custom):**
- When admin resets password → email notification to client
- When admin sends message → in-app notification badge

All controllers in `App\Http\Controllers\Admin\` for admin side.
All client controllers in `App\Http\Controllers\Client\` namespace.
```

---

## 📦 PHASE 3 — Category, Sub-Category + Product Management

```
You are a senior Laravel developer. Phase 1 and 2 are complete. Now build Product Catalog module.

### MIGRATIONS

**categories table:**
- id, name, slug, parent_id (nullable, self-referencing for sub-category),
  description (text, nullable), status (boolean), timestamps, softDeletes

**units table:**
- id, name (e.g. KG, Piece, Box, Liter), symbol, timestamps

**products table:**
- id, unique_id (generated), category_id (FK), sub_category_id (nullable, FK to categories),
  name, slug, description (text), unit_id (FK),
  mrp_price (decimal 10,2 — admin only field),
  purchase_price (decimal 10,2),
  sale_price (decimal 10,2),
  vat_percentage (decimal 5,2, default 0),
  image (nullable), status (boolean, default true),
  expire_alert_1month (boolean, default false),
  expire_alert_3month (boolean, default false),
  timestamps, softDeletes

**product_discounts table:**
- id, product_id (FK), discount_type (enum: percentage, fixed),
  discount_value (decimal 10,2), start_date, end_date (nullable),
  applicable_to (enum: all, client_agent, local), status (boolean), timestamps

### MODELS
- Category (self-referencing: parent hasMany children, child belongsTo parent), SoftDeletes
- Unit
- Product (belongsTo Category, belongsTo Unit, hasMany ProductDiscount, hasMany StockItems), SoftDeletes
- ProductDiscount (belongsTo Product)

### CATEGORY CRUD (Routes prefix: `/admin/categories`)
- index: DataTables showing category + sub-categories (tree or flat with parent column)
- create/store: name, description, parent_id (dropdown — only root categories as parent, no nested beyond 2 levels)
- edit/update
- destroy (soft delete — block if products exist)

### UNIT CRUD (Routes prefix: `/admin/units`)
- Simple CRUD: index, create, edit, destroy

### PRODUCT CRUD (Routes prefix: `/admin/products`)
- index: DataTables with search by product name, ID, category. Show: ID, name, category, unit, mrp_price, sale_price, stock_qty (from stocks table), status
- create/store:
  - Category dropdown (on change, load sub-categories via AJAX)
  - All fields as per migration
  - MRP price field: only visible/editable by Admin (use middleware check)
  - Image upload (store in storage/app/public/products)
  - VAT: if 0 show "No VAT", if >0 calculate and show inline
- edit/update: same as create
- destroy: soft delete (block if active orders exist)
- show: product detail page with current stock, purchase history, sales history tabs

### PRODUCT DISCOUNT CRUD (nested under product)
- Routes: `/admin/products/{product}/discounts`
- index, create, edit, destroy
- Validation: dates must not overlap for same product + same applicable_to

### PRODUCT REPORTS
- Route: GET `/admin/products/report`
- Filter: from_date, to_date, category_id (optional)
- Export to Excel (using Maatwebsite Laravel Excel)
- Export to PDF (using DomPDF)

### PRODUCT SEARCH
- Route: GET `/admin/products/search?q=`
- Returns: product name, ID, category, unit, mrp_price, sale_price, current stock, expire dates
- Used as AJAX endpoint in other modules (sales, purchase etc.)

Use AJAX for category→sub-category dependent dropdown.
Run `composer require maatwebsite/excel barryvdh/laravel-dompdf` if not already installed.
```

---

## 📊 PHASE 4 — Stock Management

```
You are a senior Laravel developer. Phases 1-3 are complete. Now build Stock Management.

### MIGRATIONS

**stores table:**
- id, name, location, description (text, nullable), status (boolean), timestamps, softDeletes

**stocks table:**
- id, product_id (FK), store_id (FK), quantity (decimal 10,2),
  expiry_date (date, nullable), batch_number (nullable),
  purchase_price (decimal 10,2), timestamps

**stock_movements table (audit log):**
- id, product_id (FK), store_id (FK), movement_type (enum: purchase_in, purchase_return_out, sale_out, return_in, adjustment),
  reference_type, reference_id (morphs), quantity (decimal),
  before_quantity, after_quantity, note (text, nullable), created_by (FK users), timestamps

### MODELS
- Store (hasMany Stock), SoftDeletes
- Stock (belongsTo Product, belongsTo Store)
- StockMovement (morphTo reference)

### STORE CRUD (Routes prefix: `/admin/stores`)
- index, create, edit, destroy (soft delete)
- Admin only

### STOCK MANAGEMENT (Routes prefix: `/admin/stocks`)

**Stock List:**
- GET `/admin/stocks`: DataTables with filter by:
  - Product name/ID
  - Category
  - Store
  - Date range
- Columns: Product, Category, Store, Batch, Quantity, Unit, Expiry Date, Status
- Color coding: red = expired, orange = expires within 1 month, yellow = within 3 months

**Stock CRUD:**
- create/store: Add stock manually (adjustment)
- edit/update: Adjust quantity with reason
- destroy: Remove stock entry (with stock movement log)

**Low Quantity List:**
- GET `/admin/stocks/low-quantity`
- Show products where total stock across all stores < product's minimum threshold
- Add `min_stock_threshold` column to products table

**Expiry Lists:**
- GET `/admin/stocks/expiry/one-month` — products expiring within 30 days
- GET `/admin/stocks/expiry/three-month` — products expiring within 90 days

### STOCK HELPER (Service Class)
Create `App\Services\StockService`:
- `addStock($productId, $storeId, $quantity, $purchasePrice, $expiryDate, $referenceType, $referenceId, $createdBy)`
- `deductStock($productId, $storeId, $quantity, $referenceType, $referenceId, $createdBy)` — FIFO (oldest batch first)
- `getAvailableStock($productId, $storeId = null)` — sum across stores or specific store
- `logMovement(...)` — always called internally

This StockService will be used by Purchase, Sales, Return modules later.
```

---

## 🛒 PHASE 5 — Purchase Module

````
You are a senior Laravel developer. Phases 1-4 are complete. Now build Purchase Module.

### MIGRATIONS

**suppliers table:**
- id, unique_id (generated, format YYMM+serial), name, email (nullable), phone,
  address (text), company_name (nullable), status (boolean), timestamps, softDeletes

**purchases table:**
- id, purchase_id (string, unique, YYMM+serial), supplier_id (FK),
  purchase_date (date), invoice_number (nullable),
  subtotal (decimal 10,2), vat_amount (decimal 10,2),
  total_amount (decimal 10,2), paid_amount (decimal 10,2),
  due_amount (decimal 10,2, generated), payment_status (enum: unpaid, partial, paid),
  payment_method (enum: cash, bank), note (text, nullable),
  created_by (FK users), timestamps, softDeletes

**purchase_items table:**
- id, purchase_id (FK), product_id (FK), store_id (FK),
  quantity (decimal 10,2), purchase_price (decimal 10,2),
  vat_percentage (decimal 5,2, default 0),
  vat_amount (decimal 10,2, default 0),
  total_price (decimal 10,2), expiry_date (date, nullable), timestamps

**purchase_returns table:**
- id, return_id (string, unique, YYMM+serial), purchase_id (FK),
  return_date (date), reason (text),
  total_amount (decimal 10,2), created_by (FK users), timestamps, softDeletes

**purchase_return_items table:**
- id, purchase_return_id (FK), purchase_item_id (FK), product_id (FK),
  quantity (decimal 10,2), unit_price (decimal 10,2), total_price (decimal 10,2), timestamps

### MODELS
- Supplier (SoftDeletes)
- Purchase (belongsTo Supplier, hasMany PurchaseItems, hasMany PurchaseReturns), SoftDeletes
- PurchaseItem (belongsTo Purchase, belongsTo Product)
- PurchaseReturn (belongsTo Purchase, hasMany PurchaseReturnItems), SoftDeletes
- PurchaseReturnItem

### SUPPLIER CRUD (Routes prefix: `/admin/suppliers`)
- index: DataTables with search by supplier ID or name
- create/store, edit/update, destroy (soft delete)

### PURCHASE CRUD (Routes prefix: `/admin/purchases`)

**Create Purchase:**
- Select supplier, purchase date, invoice number
- Dynamic product rows (add/remove using JS):
  - Product search (AJAX typeahead)
  - Store selection
  - Quantity, Purchase Price, VAT %
  - Per-row: VAT Amount = price × qty × (vat/100) if vat > 0, else 0
  - Per-row total = (price × qty) + vat_amount
- Footer totals: Subtotal, Total VAT, Grand Total
- Paid amount input → Due = Total - Paid
- Payment method (cash/bank)
- On store → trigger StockService::addStock() for each item
- On store → update Cash/Bank (deduct paid_amount from selected method)

**Purchase List (index):**
- DataTables with search: from_date, to_date, supplier, purchase_id
- Columns: Purchase ID, Supplier, Date, Total, Paid, Due, Status, Actions
- Filter: weekly/monthly/yearly radio buttons

**Edit Purchase:** allow editing if no purchase_return exists. Adjust stock via StockService on update.

**Delete Purchase:** soft delete. Reverse stock additions via StockService. Only allowed if not returned.

**Purchase Return:**
Routes prefix: `/admin/purchases/{purchase}/returns`
- create: select items from original purchase, enter return quantities (≤ purchased qty)
- store: auto-generate return_id, deduct from stock via StockService::deductStock()
- list: show all returns for this purchase
- update/delete return: reverse/redo stock movements

**Purchase Report:**
- GET `/admin/purchases/report`
- Filter: from_date, to_date, supplier_id
- Show: list + totals (subtotal, vat, grand total, paid, due)
- Export Excel + PDF

### VAT CALCULATION RULE
Always apply in code:
```php
$vatAmount = ($vatPercentage > 0) ? ($price * $quantity * $vatPercentage / 100) : 0;
````

### CASH/BANK INTEGRATION

Create `App\Services\CashBankService` (to be expanded in Phase 11):

- `debit($amount, $method, $referenceType, $referenceId, $note, $userId)` — money out
- `credit($amount, $method, $referenceType, $referenceId, $note, $userId)` — money in
- Just create the service skeleton + `cash_bank_transactions` migration now:
    - id, transaction_type (enum: debit, credit), amount, method (enum: cash, bank),
      reference_type, reference_id, note, balance_after (decimal), created_by, timestamps

```

---

## 🧾 PHASE 6 — Sales Module

```

You are a senior Laravel developer. Phases 1-5 are complete. Now build Sales Module.

### MIGRATIONS

**sales table:**

- id, sale_id (string, unique, YYMM+serial),
  customer_type (enum: local, client_agent),
  customer_id (nullable, FK clients table for client_agent type),
  customer_name (nullable — for local customers, no account needed),
  sale_date (date),
  subtotal (decimal 10,2), discount_amount (decimal 10,2, default 0),
  vat_amount (decimal 10,2, default 0),
  total_amount (decimal 10,2),
  payment_method (enum: cash, bank, mobile_banking),
  payment_status (enum: paid, partial, unpaid),
  paid_amount (decimal 10,2), due_amount (decimal 10,2),
  transaction_reference (nullable),
  note (text, nullable),
  created_by (FK users), timestamps, softDeletes

**sale_items table:**

- id, sale_id (FK), product_id (FK), store_id (FK),
  quantity (decimal 10,2), unit_price (decimal 10,2),
  discount_amount (decimal 10,2, default 0),
  vat_percentage (decimal 5,2, default 0),
  vat_amount (decimal 10,2, default 0),
  total_price (decimal 10,2), timestamps

### PERMISSION & ROLE RULES (implement via middleware/policy)

1. Local Seller can only create sales (no edit/delete)
2. Employee can create sales (no edit/delete)
3. Admin can create, edit, delete any sale
4. Specific Employee (has `sale.edit` permission) can also edit/delete
5. Local Seller → customer_type MUST be 'local' (cannot sell to client/agent)
6. Admin / Employee with `sale.client_agent` permission → can sell to client/agent
7. Local Seller CANNOT give extra discount (discount_amount always 0)
8. Admin / Employee with `sale.discount` permission → can set extra discount

Add new permissions: `sale.client_agent`, `sale.discount`

### ROUTES (prefix: `/admin/sales`)

- index: DataTables with filter (from_date, to_date, product_name, customer_type)
- create/store
- edit/update (Admin/permitted Employee only)
- destroy (Admin only)
- show: sale detail/invoice view

### CREATE SALE FORM

- Customer type toggle: Local / Client-Agent
    - If Local: text input for customer name
    - If Client-Agent: AJAX search by client unique_id or name (dropdown)
- Dynamic product rows:
    - Product AJAX search
    - Store select (show available stock qty)
    - Quantity (validate ≤ available stock)
    - Unit price (auto-filled from product sale_price)
    - Discount per item (only show if user has sale.discount permission)
    - VAT auto-calculated per item
- Footer: Subtotal, Total Discount, Total VAT, Grand Total
- Payment: method, paid_amount, transaction_reference
- On store: deduct stock via StockService::deductStock(), credit cash/bank via CashBankService::credit()

### SALE REPORT

- GET `/admin/sales/report`
- Filter: from_date, to_date, customer_type, product_name
- Totals: Subtotal, Discount, VAT, Grand Total, Paid, Due
- Weekly/Monthly/Yearly presets
- Export Excel + PDF

```

---

## 📋 PHASE 7 — Order Management (Client-Facing)

```

You are a senior Laravel developer. Phases 1-6 are complete. Now build Order Management.

### MIGRATIONS

**orders table:**

- id, order_id (string, unique, YYMM+serial),
  client_id (FK clients table),
  status (enum: pending, processing, confirmed, on_delivery, delivered, rejected, cancelled),
  payment_method (enum: cash_on_delivery, bank, mobile_banking),
  transaction_reference (nullable),
  payment_receipt (nullable — file path for bank payment),
  subtotal, discount_amount, vat_amount, total_amount,
  delivery_charge (decimal 10,2, default 0),
  note (text, nullable),
  admin_note (text, nullable),
  cancel_reason (text, nullable),
  created_by (FK — client_id), timestamps, softDeletes

**order_items table:**

- id, order_id (FK), product_id (FK), quantity (decimal 10,2),
  unit_price (decimal 10,2), discount_amount (decimal 10,2, default 0),
  vat_amount (decimal 10,2), total_price (decimal 10,2), timestamps

**order_status_logs table:**

- id, order_id (FK), from_status, to_status, changed_by_type (enum: admin, client),
  changed_by_id, note (text, nullable), timestamps

**feedbacks table:**

- id, order_id (FK), client_id (FK), type (enum: product, delivery, agent),
  rating (tinyint 1-5), comment (text, nullable), timestamps

### CLIENT SIDE — Order Flow

**Routes (prefix: `/client/orders`)**

- GET index: List own orders with status badges (color coded)
- GET create: Product catalog browse → add to cart
- POST store: Submit order request
    - Select payment method
    - If bank: must upload payment_receipt
    - If mobile_banking: must provide transaction_reference
    - COD: no extra field
    - All items validated (product exists, quantity > 0)
- GET show/{order}: Order detail + timeline of status changes
- POST cancel/{order}: Cancel with reason (only allowed if status = pending OR processing, NOT on_delivery/delivered)
- POST feedback/{order}: Submit feedback (only if status = delivered)

**Client Dashboard widgets:**

- Pending orders count
- Orders in delivery count
- Last 5 order activity

### ADMIN SIDE — Order Management

**Routes (prefix: `/admin/orders`)**

- GET index: DataTables with filter (status, from_date, to_date, order_id, client_name)
- GET show/{order}: Full order detail with status history timeline
- POST accept/{order}: Change status pending→processing, send notification to client
- POST reject/{order}: Change status pending→rejected with reason, notify client
- POST update-status/{order}: Move through statuses (processing→confirmed→on_delivery→delivered)
- GET /admin/orders/pending: Quick view of all pending orders (dashboard widget)

**Notification when:**

- Order submitted → notify Admin (in-app badge)
- Order accepted/rejected → notify Client (in-app + optionally email)
- Order status changed → notify Client

### PACKAGING

- When status changes to confirmed → create packaging record
- **packagings table:** id, order_id (FK), packed_by (FK users), packed_at, notes, timestamps
- Route: POST `/admin/orders/{order}/pack`

### ORDER SEARCH

- Admin: search by order_id, client name/ID, product name, date range
- Client: search own orders by status or date range

### PAYMENT VALIDATION RULES

```php
if ($paymentMethod === 'bank') {
    // must have payment_receipt uploaded
    $rules['payment_receipt'] = 'required|file|mimes:jpg,jpeg,png,pdf|max:2048';
}
if ($paymentMethod === 'mobile_banking') {
    // must have transaction_reference
    $rules['transaction_reference'] = 'required|string|max:100';
}
```

```

---

## 💳 PHASE 8 — Payment Method + Cash/Bank Full Integration

```

You are a senior Laravel developer. Phases 1-7 are complete. Now build full Payment and Cash/Bank integration.

### CASH/BANK TRANSACTIONS (expand Phase 5 skeleton)

**cash_bank_transactions table (finalize):**

- id, transaction_type (enum: credit, debit),
  method (enum: cash, bank, mobile_banking),
  amount (decimal 10,2),
  reference_type (nullable — 'purchase', 'sale', 'order', 'expense', 'salary', 'transfer'),
  reference_id (nullable),
  description (text),
  transaction_date (date),
  created_by (FK users),
  balance_cash_after (decimal 10,2),
  balance_bank_after (decimal 10,2),
  timestamps

**cash_transfers table:**

- id, transfer_id (YYMM+serial), from_method (enum: cash, bank),
  to_method (enum: cash, bank), amount (decimal 10,2),
  transfer_date (date), note (text, nullable),
  created_by (FK users), timestamps

**opening_balances table:**

- id, method (enum: cash, bank), amount (decimal 10,2), date (date),
  set_by (FK users), timestamps

### COMPLETE CashBankService

```php
class CashBankService {
    public function credit($amount, $method, $referenceType, $referenceId, $description, $userId) {}
    public function debit($amount, $method, $referenceType, $referenceId, $description, $userId) {}
    public function transfer($fromMethod, $toMethod, $amount, $note, $userId) {}
    public function getCashBalance() {} // sum all cash credits - debits
    public function getBankBalance() {} // sum all bank credits - debits
    public function getTransactionHistory($method = null, $fromDate = null, $toDate = null) {}
}
```

### PAYMENT METHOD ROUTES (Admin)

Prefix: `/admin/cash-bank`

- GET index: Show Cash Balance, Bank Balance, recent transactions (last 30)
- GET transactions: Full DataTables with filter (method, type, from_date, to_date, reference_type)
- GET transfer: Form to transfer cash↔bank
- POST transfer: Execute transfer (debit from, credit to, log both)
- GET transfer-history: List all transfers

### ACCOUNT PAYABLE / RECEIVABLE

**accounts table:**

- id, type (enum: payable, receivable),
  party_type (enum: supplier, client), party_id,
  amount (decimal 10,2), due_date (date, nullable),
  description (text), is_settled (boolean, default false),
  settled_at (nullable), created_by, updated_by, timestamps

**Rules:**

- Accountant: view only (no create/edit/delete)
- Admin: full CRUD
- When a purchase is created with due_amount > 0 → auto-create Payable record
- When a sale/order with due_amount > 0 → auto-create Receivable record
- When settled → mark is_settled=true + credit/debit cash/bank

Routes prefix: `/admin/accounts`

- GET payable: list all payables (filter: settled/unsettled, supplier, date)
- GET receivable: list all receivables (filter: client, date)
- POST settle/{account}: Admin only — mark settled + cash/bank entry
- CRUD for manual entries (Admin only)

### SALARY MODULE (basic)

**salaries table:**

- id, user_id (FK), month (string e.g. '2024-04'),
  basic_salary (decimal), deduction (decimal, default 0),
  net_salary (decimal), payment_method (enum: cash, bank),
  paid_at (date, nullable), note (text, nullable), created_by, timestamps

Routes prefix: `/admin/salaries`

- index, create/store, show
- On payment: debit from cash/bank via CashBankService

### DASHBOARD CASH WIDGETS

Add to admin dashboard:

- Current Cash Balance card
- Current Bank Balance card
- Today's total sales revenue
- Today's total expenses

```

---

## 🔄 PHASE 9 — Store, Delivery + Return Management

```

You are a senior Laravel developer. Phases 1-8 are complete. Now build Store/Delivery workflow and Return Management.

### STORE SECTION (extend Phase 4 stores)

**store_dispatch_logs table:**

- id, order_id (FK), store_id (FK), dispatched_by (FK users),
  dispatched_at (datetime), delivery_note (text, nullable), timestamps

When order status moves to `confirmed` (from Phase 7):

- Admin clicks "Send to Store" → creates dispatch log
- Store Manager can see pending dispatches in their dashboard

Routes:

- GET `/admin/store/dispatch-queue`: Orders confirmed but not yet dispatched
- POST `/admin/store/dispatch/{order}`: Mark as dispatched

### DELIVERY SECTION

**deliveries table:**

- id, order_id (FK), store_dispatch_log_id (FK),
  delivery_person_name (nullable), delivery_date (date, nullable),
  status (enum: pending, out_for_delivery, delivered, failed),
  delivery_note (text, nullable), delivered_at (nullable), timestamps

When dispatch created → auto-create delivery record (status: pending)
When order confirmed & dispatched → order status becomes on_delivery

Routes:

- GET `/admin/delivery`: List all deliveries with status filter
- POST `/admin/delivery/{delivery}/out`: Mark as out_for_delivery → update order status
- POST `/admin/delivery/{delivery}/delivered`: Mark delivered → update order status to delivered
- POST `/admin/delivery/{delivery}/failed`: Mark failed → revert order to processing

### RETURN MANAGEMENT

**return_types table:**

- id, name (e.g. "Damaged", "Wrong Item", "Usable Return"),
  disposition (enum: damage_section, restock),
  description (nullable), timestamps

**returns table:**

- id, return_id (YYMM+serial), order_id (FK), client_id (FK),
  return_type_id (FK), reason (text), note (text, nullable),
  status (enum: pending, approved, rejected),
  refund_amount (decimal 10,2, nullable),
  refund_method (enum: cash, bank, mobile_banking, nullable),
  requested_at (datetime), approved_at (nullable),
  approved_by (nullable FK users), timestamps

**return_items table:**

- id, return_id (FK), order_item_id (FK), product_id (FK),
  quantity (decimal 10,2), unit_price (decimal 10,2),
  total_price (decimal 10,2), timestamps

### RETURN RULES

- Client can only return after order status = 'delivered'
- Client cannot cancel orders (cancel is different from return)
- Return reason required + return type selection required
- Refund = product price only (NOT delivery_charge, NOT vat)
- If return_type disposition = 'restock' → call StockService::addStock()
- If return_type disposition = 'damage_section' → log to damage_logs table (don't restock)

**damage_logs table:**

- id, return_id (FK), product_id (FK), quantity, reason (text), logged_at, timestamps

### CLIENT ROUTES (Returns)

- GET `/client/returns`: List own returns
- GET `/client/returns/create/{order}`: Return form for a delivered order
- POST `/client/returns`: Submit return request

### ADMIN ROUTES (Returns)

Prefix: `/admin/returns`

- GET index: DataTables list with filter (status, client, date, return_id)
- GET show/{return}: Full detail
- POST approve/{return}: Approve, specify refund amount + method → credit client / debit cash/bank
- POST reject/{return}: Reject with reason
- Admin can create return types: GET/POST `/admin/return-types` CRUD

### FEEDBACK

- After order delivered → client can submit feedback (one per order)
- Feedback for: product / delivery / agent
- Rating 1-5 + comment
- Admin views feedback: GET `/admin/feedbacks` (DataTables, filter by type, rating, date)

```

---

## 💰 PHASE 10 — Expenses + Asset Management

```

You are a senior Laravel developer. Phases 1-9 are complete. Now build Expense and Asset Management.

### EXPENSE MODULE

**expense_heads table:**

- id, name, description (nullable), created_by (FK users), timestamps, softDeletes

**expenses table:**

- id, expense_id (YYMM+serial), expense_head_id (FK),
  amount (decimal 10,2), expense_date (date),
  payment_method (enum: cash, bank),
  description (text, nullable), receipt_file (nullable),
  created_by (FK users), timestamps, softDeletes

### EXPENSE RULES

- Admin + Accountant can add/edit/delete expenses
- Expense Heads created by Admin/Accountant (dynamic, not fixed)
- On create expense → debit cash/bank via CashBankService::debit()
- On delete expense → reverse the debit (credit back)
- On update expense → reverse old, apply new

### EXPENSE CRUD ROUTES (prefix: `/admin/expenses`)

- index: DataTables with filter (expense_head, from_date, to_date, method)
- create/store, edit/update, destroy

### EXPENSE HEAD CRUD (prefix: `/admin/expense-heads`)

- index, create/store, edit/update, destroy (block if expenses exist)

### EXPENSE REPORT

- GET `/admin/expenses/report`
- Filter: from_date, to_date, expense_head_id
- Totals by head, grand total
- Export Excel + PDF

### ASSET MANAGEMENT

**assets table:**

- id, asset_id (YYMM+serial), name, serial_number (nullable),
  category (nullable — simple string, not FK),
  purchase_price (decimal 10,2), purchase_date (date),
  warranty_until (date, nullable), expire_date (date, nullable),
  place_of_purchase (nullable), quantity (int, default 1),
  description (text, nullable),
  supplier_name (nullable), supplier_address (text, nullable),
  invoice_file (nullable),
  status (enum: active, disposed, lost),
  created_by (FK users), timestamps, softDeletes

### ASSET RULES

- Admin only: full CRUD
- No cash/bank integration (assets are tracked separately, not expensed through this module unless admin chooses)

### ASSET CRUD ROUTES (prefix: `/admin/assets`)

- index: DataTables with search (asset_id, name, serial_number, status)
- create/store: all fields, invoice file upload
- edit/update
- destroy (soft delete)
- show: full detail page

### ASSET REPORT

- GET `/admin/assets/report`
- Filter: status, purchase_date range
- Export Excel + PDF

```

---

## 📊 PHASE 11 — Reports + Invoices

```

You are a senior Laravel developer. Phases 1-10 are complete. Now build Reports and Invoice Generation.

### REPORT TYPES

**1. Sales Report** (GET `/admin/reports/sales`)
Filter: from_date, to_date, customer_type, product_id, employee_id
Columns: Sale ID, Date, Customer, Products, Subtotal, Discount, VAT, Total, Payment Method, Paid, Due
Totals row: sum of each amount column
Export: Excel + PDF

**2. Purchase Report** (GET `/admin/reports/purchases`)
Filter: from_date, to_date, supplier_id
Columns: Purchase ID, Date, Supplier, Items, Subtotal, VAT, Total, Paid, Due
Totals row
Export: Excel + PDF

**3. Expense Report** (GET `/admin/reports/expenses`)
Filter: from_date, to_date, expense_head_id
Show breakdown by expense head + grand total
Export: Excel + PDF

**4. Profit Report** (GET `/admin/reports/profit`)
Filter: from_date, to_date
Calculation:

```
Gross Revenue = Sum of all sales total_amount in period
Cost of Goods = Sum of (purchase_price × quantity) for items sold in period
Gross Profit = Gross Revenue - Cost of Goods
Total Expenses = Sum of expenses in period
Total VAT Collected = Sum of vat_amount from sales
Net Profit = Gross Profit - Total Expenses
```

Show: Gross Revenue, COGS, Gross Profit, Expenses, VAT, Net Profit in a summary card + details
Export: Excel + PDF

**5. Stock Report** (GET `/admin/reports/stock`)
Filter: from_date, to_date, category_id, store_id
Current stock levels, value (quantity × purchase_price)
Export: Excel + PDF

**6. Order Report** (GET `/admin/reports/orders`)
Filter: from_date, to_date, status, client_id
Export: Excel + PDF

### INVOICE GENERATION

**invoices table:**

- id, invoice_number (YYMM+serial), invoice_type (enum: sale, purchase, expense, profit),
  reference_type, reference_id,
  generated_by (FK users), generated_at,
  file_path (nullable), timestamps

**Invoice Views (Blade templates for DomPDF rendering):**

1. **Sales Invoice** (GET `/admin/invoices/sale/{sale_id}`)
    - Company header, Invoice #, Date
    - Client/Customer info
    - Item table: Product, Qty, Unit Price, Discount, VAT, Total
    - Summary: Subtotal, Discount, VAT, Grand Total, Paid, Due
    - Payment method

2. **Purchase Invoice** (GET `/admin/invoices/purchase/{purchase_id}`)
    - Company header, Supplier info
    - Item table with VAT breakdown

3. **Expense Invoice** (GET `/admin/invoices/expense/{expense_id}`)
    - Single expense detail with receipt

4. **Order Invoice** (GET `/admin/invoices/order/{order_id}`)
    - Similar to sales invoice but from order data

**Routes:**

- GET `/admin/invoices/sale/{id}?format=pdf` → download PDF
- GET `/admin/invoices/sale/{id}?format=html` → view in browser
- GET `/admin/invoices/purchase/{id}?format=pdf`
- GET `/admin/invoices/order/{id}?format=pdf`

### REPORT DATE PRESETS (JS helper)

Add buttons on all report pages: Today, This Week, This Month, Last Month, This Year
Auto-fill from_date and to_date inputs on click.

### EXCEL EXPORT CLASS STRUCTURE

Create separate Export class per report using Maatwebsite Excel:

- `SalesExport`, `PurchaseExport`, `ExpenseExport`, `ProfitExport`, `StockExport`
  Each uses `FromCollection` + `WithHeadings` + `WithStyles` interfaces.

```

---

## 🔍 PHASE 12 — Search Module + Notifications + Communication Polish

```

You are a senior Laravel developer. Phases 1-11 are complete. Now build global search, notifications polish, and communication layer.

### GLOBAL SEARCH ENDPOINTS

Create `App\Http\Controllers\Admin\SearchController` with these AJAX endpoints:

**GET `/admin/search/clients?q=`**
Search by: unique_id OR name
Return JSON: [{id, unique_id, name, phone, type}]
Used in: Sales create (client selection), Order management

**GET `/admin/search/products?q=`**
Search by: product unique_id OR name
Return JSON: [{id, unique_id, name, category, unit, mrp_price, sale_price, stock_qty, expiry_dates}]
Used in: Sales, Purchase, Order item rows

**GET `/admin/search/suppliers?q=`**
Search by: unique_id OR name
Return JSON: [{id, unique_id, name, phone, company_name}]

**GET `/admin/search/orders?q=`**
Search by: order_id OR client name/ID
Return JSON: [{id, order_id, client_name, status, total, date}]

**GET `/admin/search/sales?q=`**
Search by: sale_id OR product name OR customer name
Date filter: from_date, to_date

**GET `/admin/search/returns?q=`**
Search by: return_id OR client name
Date filter: from_date, to_date

**GET `/admin/search/stocks?q=`**
Search by: product_id OR product name
Date filter: from_date, to_date

### NOTIFICATION SYSTEM (Database Notifications)

Use Laravel's built-in database notifications:
`php artisan notifications:table && php artisan migrate`

Create Notification classes:

- `OrderSubmittedNotification` → notify Admin users when client places order
- `OrderStatusChangedNotification` → notify Client when admin changes order status
- `MessageReceivedNotification` → notify when new message in conversation
- `LowStockNotification` → notify Admin/Store Manager when stock below threshold

**Notification Bell (AdminLTE header):**

- Show unread count badge
- Dropdown showing last 5 notifications with timestamp
- GET `/admin/notifications` → full list, mark all as read
- POST `/admin/notifications/{id}/read` → mark single as read
- Use polling every 30 seconds for updates (or Laravel Echo if Pusher configured)

**Client Notification Bell:**

- Same pattern for client-side navbar

### COMMUNICATION POLISH

- Admin messages page: show conversations list (left panel) + active chat (right panel) — like a simple chat UI
- Unread message indicator per conversation
- Admin can mark conversation as resolved

### EMAIL NOTIFICATIONS (optional, use queue)

Create `config/erp.php` with:

```php
'notifications' => [
    'order_placed' => env('NOTIFY_ORDER_PLACED', true),
    'order_status_change' => env('NOTIFY_ORDER_STATUS', true),
    'password_reset' => env('NOTIFY_PASSWORD_RESET', true),
]
```

Use Laravel Queues (database driver) for all email sends:
`php artisan queue:table && php artisan migrate`

### DASHBOARD FINAL POLISH

Update Admin dashboard with:

- Sales chart (last 30 days — Chart.js line chart)
- Top 5 selling products (bar chart)
- Order status distribution (pie chart — Chart.js)
- Recent activities feed (last 10 actions: orders, sales, purchases)
- Quick access buttons: New Sale, New Purchase, View Orders, View Reports

```

---

## 🚀 PHASE 13 — Final Polish + Security + Optimization

```

You are a senior Laravel developer. All 12 phases are complete. Now do final hardening, security, and optimization.

### AUTHORIZATION AUDIT

Review and add Gates/Policies for every module:

- Ensure Spatie permission checks on every sensitive route
- Admin-only routes: mrp_price editing, account payable/receivable CRUD, system settings
- Accountant: cash/bank view + expense management, no delete on payable/receivable
- Employee: sale create only, no edit/delete
- Local Seller: local sale create only, no client/agent access
- Store Manager: dispatch queue access

Add `php artisan make:policy` for: ProductPolicy, SalePolicy, OrderPolicy, PurchasePolicy

### MIDDLEWARE

Create custom middleware:

- `AdminOnly` → abort 403 if not is_admin
- `ClientAuth` → check client guard
- `CheckPermission($permission)` → wrapper for Spatie

### ACTIVITY LOG

Install `spatie/laravel-activitylog`:

```
composer require spatie/laravel-activitylog
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan migrate
```

Add `LogsActivity` trait to: Product, Purchase, Sale, Order, Client, Expense, Asset models
Route: GET `/admin/activity-log` — DataTables list, filter by model/user/date

### SETTINGS MODULE

**settings table:**

- id, key (string, unique), value (text), group (string), updated_by, timestamps

Default settings to seed:

- company_name, company_address, company_phone, company_email, company_logo
- currency_symbol (default: ৳)
- low_stock_threshold_default (default: 10)
- vat_registration_number

Route: GET/POST `/admin/settings` — Admin only form to update settings
Use Settings in invoice headers, reports etc.

### INPUT VALIDATION HARDENING

- Add FormRequest classes for every store/update route that doesn't have one yet
- Ensure all file uploads: mime validation, max size, store in correct disk
- Add CSRF verification review
- Ensure softDeletes check before any hard relationships

### PERFORMANCE

- Add database indexes on frequently queried columns:
    - `unique_id` on all relevant tables
    - `created_at` on purchases, sales, orders, expenses
    - `status` on orders, stocks
    - `client_id` on orders
    - `product_id` on stock, sale_items, purchase_items
- Add query caching for: product list, category list, settings (use Laravel Cache)
- Optimize N+1 queries: audit all DataTables queries, add `with()` eager loading

### BACKUP COMMAND

Create Artisan command `php artisan erp:backup`:

- Export all critical tables to JSON in storage/backups/{date}/
- Schedule daily in `app/Console/Kernel.php`

### FINAL CHECKLIST

Generate a file `CHECKLIST.md` in project root listing:

- [ ] All .env variables required (DB, MAIL, QUEUE, APP settings)
- [ ] `php artisan storage:link` must be run
- [ ] `php artisan queue:work` for notifications
- [ ] Default login credentials
- [ ] All seeder commands
- [ ] Production deployment notes (cache config, optimize autoload)

````

---

## 📌 Quick Reference — All Routes Summary

| Module | Prefix | Notes |
|--------|--------|-------|
| Admin Auth | `/admin` | Login, logout, password change |
| Client Auth | `/client` | Separate guard |
| Departments | `/admin/departments` | Admin only |
| Roles | `/admin/roles` | View only |
| Employees | `/admin/employees` | Admin only |
| Clients/Agents | `/admin/clients` | Admin only |
| Categories | `/admin/categories` | Admin only |
| Units | `/admin/units` | Admin only |
| Products | `/admin/products` | Admin only (mrp edit) |
| Stores | `/admin/stores` | Admin only |
| Stocks | `/admin/stocks` | Admin + Store Manager |
| Suppliers | `/admin/suppliers` | Admin only |
| Purchases | `/admin/purchases` | Admin only |
| Sales | `/admin/sales` | Role-based |
| Orders (Admin) | `/admin/orders` | Admin + Employee |
| Orders (Client) | `/client/orders` | Client guard |
| Payments/Cash | `/admin/cash-bank` | Admin + Accountant |
| Returns (Admin) | `/admin/returns` | Admin |
| Returns (Client) | `/client/returns` | Client guard |
| Expenses | `/admin/expenses` | Admin + Accountant |
| Assets | `/admin/assets` | Admin only |
| Reports | `/admin/reports` | Admin + Accountant |
| Invoices | `/admin/invoices` | Admin + Accountant |
| Search (AJAX) | `/admin/search/*` | All authenticated |
| Settings | `/admin/settings` | Admin only |
| Activity Log | `/admin/activity-log` | Admin only |
| Messages | `/admin/messages`, `/client/messages` | Both sides |

---

## 🛠️ Recommended Package List

```bash
composer require jeroennoten/laravel-adminlte
composer require spatie/laravel-permission
composer require spatie/laravel-activitylog
composer require maatwebsite/excel
composer require barryvdh/laravel-dompdf
composer require yajra/laravel-datatables-oracle
````

---

_Generated for: Laravel 13 + AdminLTE 3 + Spatie Permission ERP System_
_Total Phases: 13 | Estimated Modules: 25+ | Estimated Tables: 45+_
