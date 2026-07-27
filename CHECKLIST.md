# ERP System — Deployment & Operations Checklist

## Environment variables (`.env`)

- [ ] `APP_NAME`, `APP_ENV=production`, `APP_KEY` (run `php artisan key:generate` if blank), `APP_DEBUG=false`, `APP_URL`
- [ ] `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- [ ] `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database`
- [ ] `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` (set a real SMTP driver in production — `log` only writes mail to the log file)
- [ ] `FILESYSTEM_DISK=local` (default — file uploads and backups are written under `storage/app/private` and `storage/app/public`)
- [ ] Optional notification toggles (`config/erp.php`): `NOTIFY_ORDER_PLACED`, `NOTIFY_ORDER_STATUS`, `NOTIFY_PASSWORD_RESET` (default `true`)

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
