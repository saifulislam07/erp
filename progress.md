# ERP System — Progress Tracker

সোর্স: `ERP_Claude_Code_Master_Prompts.md` (১৩ ফেজ) + `note.text` (ফলো-আপ কাস্টম আইটেম, কমিট `43b0253`-এ ক্লোজ হয়েছে)
সর্বশেষ আপডেট: 2026-09-06 · ব্রাঞ্চ `master`

**সারসংক্ষেপ: ১৩/১৩ ফেজ শেষ ✅ · note.text-এর ১৬/১৬ আইটেম শেষ ✅ · টেস্ট স্যুট ১৩৪/১৩৪ সবুজ ✅ · বাকি আছে শুধু সার্ভার-সাইড ডিপ্লয়মেন্ট**

---

## ⚙️ PHASE 1 — Project Foundation + Auth + Department + Role ✅

- [x] STEP 1 — Required packages ইনস্টল (Spatie Permission, AdminLTE, Excel, DomPDF)
- [x] STEP 2 — Migrations (মোট ৫৬টি migration ফাইল)
- [x] STEP 3 — Models (`User`, `Department` সহ ৪৩টি model)
- [x] STEP 4 — Seeders (`RolesAndPermissionsSeeder`, `SettingsSeeder`, default admin)
- [x] STEP 5 — Auth setup (`Auth/AuthenticatedSessionController`, `routes/auth.php`)
- [x] STEP 6 — Password change (`Admin/PasswordController`, `Client/PasswordController`)
- [x] STEP 7 — Department CRUD (`DepartmentController` + `views/admin/departments`)
- [x] STEP 8 — Role list (`RoleController` + permission ম্যাট্রিক্স)
- [x] STEP 9 — AdminLTE sidebar menu (`config/adminlte.php`, ৫০২ লাইন কাস্টমাইজড)
- [x] STEP 10 — Dashboard (`DashboardController` + `views/admin/dashboard.blade.php`)

## 👥 PHASE 2 — Client/Agent + Employee Management ✅

- [x] Unique ID helper (reusable)
- [x] Employee management (`EmployeeController` + index/show/form)
- [x] Client/Agent management (`ClientController` + `Client` model)
- [x] Client auth — আলাদা guard (`ClientAuth` middleware, `Client/AuthenticatedSessionController`)
- [x] Admin ↔ Client messaging (`Admin/MessageController`, `Client/MessageController`, `Message` model)
- [x] Notification (`MessageReceivedNotification`, দুই পাশেই `NotificationController`)

## 📦 PHASE 3 — Category, Sub-Category + Product ✅

- [x] Migrations + Models (`Category`, `Unit`, `Product`, `ProductImage`, `ProductDiscount`)
- [x] Category CRUD (`/admin/categories`)
- [x] Unit CRUD (`/admin/units`)
- [x] Product CRUD (`/admin/products` — create/edit/show/form blade)
- [x] Product discount CRUD (nested — `views/admin/products/discounts`)
- [x] Product reports (`products/report` + excel + pdf)
- [x] Product search (`SearchController`)

## 📊 PHASE 4 — Stock Management ✅

- [x] Migrations + Models (`Stock`, `StockMovement`, `Store`, `DamageLog`)
- [x] Store CRUD (`/admin/stores`)
- [x] Stock management (`/admin/stocks` — `StockController`)
- [x] `StockService` helper class (`app/Services/StockService.php`)

## 🛒 PHASE 5 — Purchase Module ✅

- [x] Migrations + Models (`Purchase`, `PurchaseItem`, `Supplier`, `PurchaseReturn`, `PurchaseReturnItem`)
- [x] Supplier CRUD (`/admin/suppliers`)
- [x] Purchase CRUD (`/admin/purchases` — create/edit/show)
- [x] VAT calculation rule
- [x] Cash/Bank integration (`CashBankService` স্কেলিটন → Phase 8-এ সম্পূর্ণ)

## 🧾 PHASE 6 — Sales Module ✅

- [x] Migrations + Models (`Sale`, `SaleItem`, `SaleReturn`, `SaleReturnItem`)
- [x] Permission ও role rules (`CheckPermission`, `AdminOnly`, `EnsureUserIsAdminOrAccountant` middleware)
- [x] Routes (`/admin/sales`)
- [x] Create sale form (ডাইনামিক আইটেম রো + ডিসকাউন্ট/VAT)
- [x] Sale report (`sales/report` + excel + pdf)

## 📋 PHASE 7 — Order Management (Client-Facing) ✅

- [x] Migrations + Models (`Order`, `OrderItem`, `OrderStatusLog`, `Packaging`)
- [x] Client side order flow (`Client/OrderController` + `views/client/orders`)
- [x] Admin side order management (`Admin/OrderController`)
- [x] Packaging (`Packaging` model)
- [x] Order search (`SearchController`)
- [x] Payment validation rules

## 💳 PHASE 8 — Payment Method + Cash/Bank Integration ✅

- [x] Cash/Bank transactions (`CashBankTransaction`, `CashTransfer`, `OpeningBalance`, `Account`)
- [x] সম্পূর্ণ `CashBankService`
- [x] Payment method routes (`AccountController`, `CashBankController`)
- [x] Account payable / receivable (`PartyLedgerService`, `PartyPayment`, `PartyPaymentAllocation`)
- [x] Salary module (`SalaryController`, `Salary` model)
- [x] Dashboard cash widgets

## 🔄 PHASE 9 — Store, Delivery + Return Management ✅

- [x] Store section extend (`StoreDispatchController`, `StoreDispatchLog`)
- [x] Delivery section (`DeliveryController`, `Delivery` model)
- [x] Return management (`OrderReturn`, `ReturnItem`, `ReturnType`)
- [x] Return rules + client routes (`Client/ReturnController`)
- [x] Admin return routes (approve / reject)
- [x] Feedback (`FeedbackController`, `Feedback` model)

## 💰 PHASE 10 — Expenses + Asset Management ✅

- [x] Expense module (`Expense`, `ExpenseHead`)
- [x] Expense CRUD (`/admin/expenses`) + Expense head CRUD (`/admin/expense-heads`)
- [x] Expense report (`expenses/report` + excel + pdf) — `ExpensesExport`
- [x] Asset management (`Asset` model, `/admin/assets`)
- [x] Asset report (`assets/report` + excel + pdf) — `AssetsExport`

## 📊 PHASE 11 — Reports + Invoices ✅

- [x] Report types — Profit, Stock, Orders (`ReportController`); Sales, Purchases, Products, Expenses, Assets (নিজ নিজ কন্ট্রোলারে)
- [x] Excel export ক্লাস ৮টি — `SalesExport`, `PurchasesExport`, `StockExport`, `ProfitExport`, `OrdersExport`, `ProductsExport`, `ExpensesExport`, `AssetsExport`
- [x] Invoice generation (`InvoiceController`, `Invoice` model, PDF: sale / purchase / order / expense)
- [x] Report date presets (JS helper)
- [x] প্রতিটি রিপোর্টে PDF ভার্সন (`*-pdf.blade.php`)

## 🔍 PHASE 12 — Search + Notifications + Communication Polish ✅

- [x] Global search endpoints (`SearchController` — products, orders, returns ইত্যাদি)
- [x] Database notification system (৬টি notification ক্লাস — low stock, order submitted/status, message, password reset ×2)
- [x] Communication polish (message thread UI)
- [x] Email notifications — queued (`InvoiceMail`, `TestMail`, `MailSettings`)
- [x] Dashboard final polish

## 🚀 PHASE 13 — Final Polish + Security + Optimization ✅

- [x] Authorization audit (route-লেভেল `check.permission` গ্রুপিং)
- [x] Middleware (`AdminOnly`, `CheckPermission`, `ClientAuth`, `EnsureUserIsAdminOrAccountant`)
- [x] Activity log (`ActivityLogController` + `activity_log` টেবিল)
- [x] Settings module (`SettingsController` + `Setting` model + `views/admin/settings`)
- [x] Input validation hardening (`app/Http/Requests/Admin/*` — FormRequest ক্লাস)
- [x] Performance (eager loading, `HtmlSanitizer`, asset versioning)
- [x] Backup command (`ErpBackupCommand` + scheduler `bootstrap/app.php`)
- [x] Final checklist → `CHECKLIST.md`

---

## 📝 note.text ফলো-আপ আইটেম (১৬/১৬ ✅)

| # | আইটেম | স্ট্যাটাস |
|---|---|---|
| 1 | 401/403/404/419/429/500/503 error page ডিজাইন | ✅ `resources/views/errors/` |
| 2 | AdminLTE থিম যেন বোঝা না যায় (rebrand) | ✅ `views/vendor/adminlte/` override + `Branding` |
| 3 | ড্যাশবোর্ডের কার্ড ছোট করে সাজানো | ✅ dashboard redesign |
| 4 | কাস্টম toastr / sweet alert | ✅ `public/assets/js/app.js` + `sweetalert2` |
| 5 | Unit create থেকে Symbol বাদ | ✅ `UnitRequest` + `Unit` model আপডেট |
| 6 | Active menu কাজ করা | ✅ `AdminLanding` + adminlte config |
| 7 | সাব-ক্যাটাগরি ডাবল আসা fix + Description text editor | ✅ `ProductController` / `ProductRequest` |
| 8 | Product multi image | ✅ `ProductImage` model |
| 9 | সব ইমেজ WebP → `public/upload/{module}/{yyyy}/{mm}/` | ✅ `MediaService` (+ `MediaServiceTest`) |
| 10 | Product view-এ ডিসকাউন্টসহ সব তথ্য | ✅ `products/show.blade.php` |
| 11 | Purchase invoice, Sale invoice | ✅ `views/admin/invoices/` |
| 12 | Purchase ও Sale-এর রিটার্ন লিস্ট | ✅ `purchase-returns.index`, `sale-returns.index` |
| 13 | সাপ্লায়ার/কাস্টমার কে কত পাবে + পেমেন্ট সিস্টেম | ✅ `PartyLedgerService`, `PartyPayment*`, ledger/history view |
| 14 | SMTP, invoice mail, password reset | ✅ Settings → Email, `MailSettings`, queued mail |
| 15 | Admin profile, logo set, login page | ✅ `ProfileController`, `Branding`, `auth/layout` |
| 16 | UI/ফাংশনাল গোছানো, হিসাবের গোলমাল ঠিক | ✅ `FormatHelper` (`money()`, `qty()`, `percent()`, `amount_in_words()`) |

---

## ✅ ফেজ-পরবর্তী কাজ (এই সেশনে শেষ)

### টেস্ট কভারেজ ✅

আগে: ৪০টি টেস্টের মধ্যে **২২টি পাস, ১৫টি ফেল + ৩টি এরর**। ফেলগুলো সব Laravel Breeze-এর scaffold টেস্ট — এই প্যানেলে নেই এমন ফিচার (public registration, email verification, password confirmation) খুঁজছিল।

- [x] অস্তিত্বহীন ফিচারের Breeze টেস্ট মুছে ফেলা (`RegistrationTest`, `EmailVerificationTest`, `PasswordConfirmationTest`, দুটি `ExampleTest`)
- [x] `AuthenticationTest`, `ProfileTest`, `PasswordUpdateTest` — বাস্তব রুট অনুযায়ী নতুন করে লেখা
- [x] `StockServiceTest` — FIFO ব্যাচ, স্টক কম থাকলে deduction আটকানো, low-stock alert
- [x] `PurchaseFlowTest` — VAT, স্টক বৃদ্ধি, ক্যাশ ডেবিট, supplier payable, delete-এ পূর্ণ রিভার্সাল
- [x] `SaleFlowTest` — স্টক কমা, ক্যাশ ক্রেডিট, client receivable, ডিসকাউন্ট ও client/agent পারমিশন, oversell ব্লক
- [x] `OrderFlowTest` — সার্ভার-সাইড প্রাইসিং (payload টেম্পার করলেও দাম বদলায় না), ডিসকাউন্ট উইন্ডো, স্ট্যাটাস ল্যাডার, ক্লায়েন্ট আইসোলেশন
- [x] `FormatHelperTest` (Unit) — `qty()`, `percent()`, `amount_in_words()`
- [x] ফ্যাক্টরি: `Category`, `Unit`, `Store`, `Supplier`, `Product`, `Client` + `UserFactory`-তে `admin()` / `inactive()` state

- [x] `CashBankServiceTest` — ওপেনিং ব্যালেন্স, credit/debit, দুই বাকেট আলাদা থাকা, mobile banking ব্যাংকে পড়া, ট্রান্সফারে মোট অপরিবর্তিত, হিস্ট্রি ফিল্টার
- [x] `SaleReturnFlowTest` — স্টক ফেরত, রিফান্ডে ক্যাশ কমা, পণ্যের দামের বেশি রিফান্ড ব্লক, একই ইউনিট দুবার ফেরত ব্লক, delete-এ রিভার্সাল
- [x] `PurchaseReturnFlowTest` — স্টক কমা, কেনা দামে হিসাব, একাধিক আংশিক রিটার্ন যোগ হওয়া, পারমিশন গেট

**ফলাফল: ১৩৪টি টেস্ট, ৩৩৪টি অ্যাসারশন, সব পাস।**

### টেস্ট লিখতে গিয়ে পাওয়া ২টি বাস্তব বাগ (ফিক্স করা হয়েছে) 🐛

| বাগ | ছিল | এখন |
|---|---|---|
| **রিফান্ড ছাড়া sale return সেভই হতো না** — `SaleReturnRequest`-এ `required_with:refund_amount`; `prepareForValidation` ফাঁকা বক্সকে `0` করত, কিন্তু `0`-ও "present", তাই সবসময় রিফান্ড মেথড চাইত | এক্সচেঞ্জ / ক্রেডিট নোট / অপরিশোধিত ইনভয়েসের ফেরত — কোনোটাই রেকর্ড করা যেত না | `Rule::requiredIf(refund_amount > 0)` |
| **Purchase return-এ কোনো পারমিশন চেক ছিল না** — `purchases/{purchase}/returns` রুট ৬টা কোনো `check.permission` গ্রুপের বাইরে, কন্ট্রোলারেও authorize নেই | পারমিশনবিহীন যেকোনো লগইন ইউজার URL জানলেই রিটার্ন বানিয়ে স্টক নাড়াতে পারত | `check.permission:purchase.view` গ্রুপে আনা হয়েছে |

দুটোরই রিগ্রেশন টেস্ট আছে।

### সম্পূর্ণ authorization অডিট ✅

উপরের দুটো বাগ Phase 13-এর audit ফসকে গিয়েছিল, তাই `routes/admin.php` ও `routes/client.php`-এর **প্রতিটি** রুট ধরে ধরে দেখা হয়েছে — কোনটা `check.permission`/`admin.only` গ্রুপের বাইরে, আর সেই কন্ট্রোলার নিজে authorize করে কিনা।

**আরও একটা ফাঁক পাওয়া গেছে ও ঠিক করা হয়েছে:**

| ফাঁক | ছিল | এখন |
|---|---|---|
| **`search/*` এন্ডপয়েন্টে কোনো authorization ছিল না** — ছয়টা রুটই গ্রুপের বাইরে, `SearchController`-এও কোনো authorize নেই | পারমিশনবিহীন যেকোনো লগইন ইউজার JSON এন্ডপয়েন্ট থেকে সাপ্লায়ারের ফোন/কোম্পানি, অর্ডারের টাকার অঙ্ক ও ক্লায়েন্টের নাম, বিক্রি, রিটার্ন, স্টক লেভেল — সব পড়তে পারত | প্রতিটা এন্ডপয়েন্ট নিজ মডিউলের পারমিশনে গেটেড; নেভবারের `global` সবার জন্য খোলা কিন্তু প্রতিটা সেকশন পারমিশন অনুযায়ী ফিল্টার হয় |

`SearchAuthorizationTest` — ১৮টি টেস্ট (প্রতি এন্ডপয়েন্টে refuse / allow / admin, আর global-এর ফিল্টারিং)।

**পরিষ্কার পাওয়া গেছে** (নিজেরাই ঠিকভাবে গেট করে, কোনো পরিবর্তন লাগেনি):

- `AccountController` — প্রতিটি মেথডে `AccountPolicy`
- `SaleController`, `OrderController` — প্রতিটি মেথডে policy
- `SaleReturnController`, `PartyPaymentController` (+ supplier/customer সাবক্লাস) — নিজস্ব `authorizeArea()`, delete শুধু admin
- `NotificationController` — `$request->user()->notifications()`-এ স্কোপড, তাই অন্যের নোটিফিকেশন ছোঁয়া যায় না
- সব `Client/*` কন্ট্রোলার — `user('client')`-এ স্কোপড, প্রতিটা `{order}` রুটে `abort_unless($order->client_id === ...)`
- ইচ্ছাকৃতভাবে খোলা রুটগুলো (`home`, `profile`, `clients/search`, `products/search`) — কোডে কমেন্ট দিয়ে কারণ লেখা আছে

### ডিফল্ট অ্যাডমিন পাসওয়ার্ড ✅

- [x] সিডার থেকে হার্ডকোড করা `password` সরানো হয়েছে
- [x] `ADMIN_EMAIL` / `ADMIN_PASSWORD` env দিয়ে সেট করা যায়; `ADMIN_PASSWORD` ফাঁকা থাকলে র‍্যান্ডম পাসওয়ার্ড তৈরি হয়ে একবারই কনসোলে ছাপা হয়
- [x] সিডার আবার চালালে বিদ্যমান অ্যাডমিনের পাসওয়ার্ড রিসেট হয় না
- [x] `DatabaseSeederTest` — কোনো পরিচিত দুর্বল পাসওয়ার্ড নেই, রি-সিডে পাসওয়ার্ড অক্ষত
- [x] `CHECKLIST.md` ও `.env.example` আপডেট

---

## ⏳ এখনো বাকি

- [ ] **ডিপ্লয়মেন্ট চেকলিস্ট** — `CHECKLIST.md`-এর env var, one-time setup command, queue worker, cron scheduler কোনোটাই এখনো টিক দেওয়া নেই। এগুলো প্রোডাকশন সার্ভারে করার কাজ, কোডে নয়
- [ ] **আনকমিটেড পরিবর্তন** — `composer.lock` ও `package-lock.json` সেশনের শুরু থেকেই modified; এছাড়া `config/erp.php` ও `resources/views/auth/*` -এ dev-login prefill নিয়ে আপনার নিজের কাজ চলছে (আমি ছুঁইনি)

---

## 🗂️ কমিট ইতিহাস (ফেজ ম্যাপিং)

| কমিট | কাজ |
|---|---|
| `9bd6dbf` | phase 11 done |
| `0a29f47` | phase 12 done |
| `0e3ce0e` | phase 13 done |
| `43d5b91` | report list — role/permission ম্যাট্রিক্স, dashboard redesign, client/employee show page |
| `43b0253` | note.text-এর ১৬টি আইটেম বাস্তবায়ন (MediaService, PartyLedger, SaleReturn, Mail, Settings, FormatHelper) |
| `2389a52` | note.text ডিলিট (সব আইটেম শেষ) |
