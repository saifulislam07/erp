# ERP System — Deployment & Operations Checklist

## Environment variables (`.env`)

- [ ] `APP_NAME`, `APP_ENV=production`, `APP_KEY` (run `php artisan key:generate` if blank), `APP_DEBUG=false`, `APP_URL`
- [ ] `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- [ ] `MAIL_*` — only the fallback. Outgoing mail is configured in the UI at **Settings → Email**, and those values override `.env` at runtime (see "Email" below).
- [ ] `FILESYSTEM_DISK=local` (default — backups are written under `storage/app/private`; **uploaded images go to `public/upload/`**, not the storage disk)
- [ ] Optional notification toggles (`config/erp.php`): `NOTIFY_ORDER_PLACED`, `NOTIFY_ORDER_STATUS`, `NOTIFY_PASSWORD_RESET` (default `true`)
- [ ] Optional media tuning: `ERP_IMAGE_QUALITY` (default 82), `ERP_IMAGE_MAX_WIDTH` (1600), `ERP_IMAGE_THUMB_WIDTH` (400)
- [ ] Bump `ERP_ASSET_VERSION` after changing anything under `public/assets/` so browsers pick the new file up

## One-time setup commands

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate          # only if APP_KEY is blank
php artisan storage:link          # required for product images, receipts, asset invoices, payment receipts, company logo
php artisan migrate --force
php artisan db:seed --force       # roles/permissions, default settings, default admin user
```

## Seeder commands

- `php artisan db:seed --class=RolesAndPermissionsSeeder` — roles (Admin, Accountant, Employee, Local Seller, Store Manager) and permissions
- `php artisan db:seed --class=SettingsSeeder` — default company/system settings (company name, currency symbol, low-stock threshold, VAT reg. number)
- `php artisan db:seed` (no class) — runs both of the above plus creates the default admin user

## Default login credentials

- **Admin (web guard, `/login`)**: `admin@example.com` / `password` — **change this password immediately in production** (`admin/password/change` after first login, or update the seeder before deploying).
- Client accounts are created from the admin panel (Clients/Agents → Create) — there is no seeded default client.

## Background workers

- [ ] `php artisan queue:work` (or a supervisor-managed process) must be running for queued notification emails (order placed, order status change, password reset) to actually send — they are dispatched via the `database` queue connection and sit in the `jobs` table until a worker processes them.
- [ ] The task scheduler must run every minute via server cron: `* * * * * php /path-to-project/artisan schedule:run >> /dev/null 2>&1`. This drives the daily `erp:backup` command (registered in `bootstrap/app.php`).

## Email

- Configure the mail server at **Settings → Email** (Admin only). The values are stored in the `settings` table and applied to the runtime config on boot by `App\Support\MailSettings`, so the mail server can be changed without a deploy. Anything left blank there falls back to `.env`.
- The SMTP password is write-only in the UI: the field renders blank and only overwrites the stored value when something is typed into it.
- **Send test email** on the same screen proves the settings before anyone depends on them. With the transport set to `log` nothing leaves the server — the message is written to `storage/logs/laravel.log`.
- Three automatic emails can be switched off individually: invoice on sale, order status updates, password resets.
- Invoices are emailed from the sale/purchase detail pages ("Email customer" / "Email supplier"), with the PDF attached. Both the invoice mail and password resets are **queued** — a worker must be running.

## Uploads & images

- Uploaded images are converted to **WebP** by `App\Services\MediaService` and written to `public/upload/{module}/{yyyy}/{mm}/`, each with a `-thumb` variant. Modules in use: `products`, `branding`, `avatars`, `assets`, `expenses`, `payment-receipts`.
- Stored paths are relative to `public/` (e.g. `upload/products/2026/07/rice-ab12cd.webp`); read them in views with the `media_url()` helper, which also resolves records created before this pipeline (those still live on the `public` storage disk and need `storage:link`).
- `public/upload/` must be writable by the web server and included in your backup strategy. It is git-ignored.

## Money, quantity and date formatting

- Use the `money()`, `qty()`, `percent()` and `amount_in_words()` helpers (`app/Helpers/FormatHelper.php`) rather than `number_format()` so the currency symbol and rounding come from one place.
- Inside generated PDFs call `money($value, false)`: DomPDF's bundled DejaVu Sans has no glyph for several currency symbols, so documents state the currency once in the header and print bare numbers on each row.

## Party ledgers (supplier dues / customer dues)

- `App\Services\PartyLedgerService` owns the balances:
  - supplier balance = billed − returned − paid
  - customer balance = billed − returned − received + refunded
- A payment is recorded once and allocated across open invoices **oldest first**; anything left over is kept as an unallocated advance. Allocations are what make a payment reversible — deleting one puts the money back on each invoice and reverses the cash/bank entry.
- `purchases.paid_amount` / `sales.paid_amount` remain the single source of truth for what has been settled, so the invoice screens, the reports and the ledgers cannot disagree.
- Reversing a payment is **Admin only** — it moves money in the cash ledger.

## Storage & backups

- `php artisan erp:backup` exports `clients`, `products`, `categories`, `suppliers`, `stocks`, `orders`, `order_items`, `sales`, `sale_items`, `purchases`, `purchase_items`, `expenses`, `accounts`, `assets`, and `settings` to JSON under `storage/app/private/backups/{timestamp}/`. Scheduled to run daily — make sure the scheduler (above) is active and that this directory is included in your off-server backup/retention strategy.
- Uploaded files (product images, asset invoices, expense receipts, client payment receipts, company logo) are stored on the `public` disk under `storage/app/public/...` and served via the `storage:link` symlink — back this up too.

## Security / authorization notes

- Policies exist for `Sale`, `Product`, `Purchase`, `Order`, and `Account` — every admin-side controller for these models calls `$this->authorize(...)`. `is_admin` always bypasses; otherwise access follows the Spatie permission assigned to the user's role.
- `Department`, `Role`, `Employee`, and `Client` management routes are gated by the `check.permission:<permission>` middleware (department.view / role.view / user.view / client.view) — only Admin has these permissions by default. `clients/search` is intentionally left ungated since Employee/Local Seller need it to pick a client while creating a sale.
- `admin.only` (Admin-only), `admin_or_accountant` (Admin or Accountant), and `check.permission:<perm>` (Admin or holder of the named Spatie permission) are available as route middleware aliases.
- Client-portal routes use the `client.auth` middleware (not the framework's default `auth:client`) so unauthenticated visitors are redirected to `client.login` instead of the admin login page.
- All destructive/financial actions on Accounts (create/update/delete/settle) remain Admin-only by design — Accountants can view payable/receivable ledgers and manage expenses but cannot delete or settle entries.

## Activity log

- Powered by `spatie/laravel-activitylog` (v4.12, chosen for PHP 8.3 compatibility — the v5 line requires PHP 8.4).
- Logged models: `Product`, `Purchase`, `Sale`, `Order`, `Client`, `Expense`, `Asset` (fillable-attribute changes only; `Client` excludes `password`/`profile_photo` from the log for privacy).
- View at `/admin/activity-log` (Admin only), filterable by module, user, and date range.
- The `activity_log` table will grow indefinitely — consider periodically pruning old entries (e.g. `Activity::where('created_at', '<', now()->subYear())->delete()` on a schedule) once volume becomes a concern; nothing prunes it automatically today.

## Settings module

- `/admin/settings` (Admin only) manages: company name/address/phone/email/logo, currency symbol, default low-stock threshold, VAT registration number.
- Settings are cached indefinitely (`Cache::rememberForever`) and automatically invalidated on save — no manual cache-clear needed after changing a value in the UI.
- Invoice PDFs (sale/purchase/expense/order) pull the company header from these settings via `resources/views/admin/invoices/partials/header.blade.php`.

## Production deployment notes

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

- Re-run `config:cache`/`route:cache`/`view:cache` after every deploy that changes `.env`, routes, or Blade files — stale caches are a common source of "it works locally but not in production" bugs.
- Make sure `storage/` and `bootstrap/cache/` are writable by the web server user.
- Confirm the cron entry for `schedule:run` (above) is installed on the production server — it is not automatic.
