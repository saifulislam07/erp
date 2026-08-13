<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'ERP',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => 'ERP',
    'logo_img' => null,
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
    'usermenu_profile_url' => true,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'admin.dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => null,
    'password_reset_url' => 'password.request',
    'password_email_url' => 'password.email',
    'profile_url' => 'admin.profile.edit',
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
     | Menu grouping follows the day's work rather than the database: what you
     | sell, what you buy and stock, where the money is, and who uses the
     | system. Each entry carries an `active` pattern because the packaged
     | active-state check only matches a link's own URL — without them a
     | create/edit page leaves the whole sidebar unhighlighted.
     */
    'menu' => [
        [
            'type' => 'sidebar-menu-search',
            'text' => 'Search the menu',
            'input_name' => 'menuSearch',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'can' => 'dashboard.view',
            'active' => ['admin/dashboard'],
        ],
        [
            'text' => 'Home',
            'route' => 'admin.home',
            'icon' => 'fas fa-fw fa-home',
            'can' => 'view-home-fallback',
            'active' => ['admin/home'],
        ],

        ['header' => 'Selling', 'can' => ['sale.view', 'order.view', 'client.view', 'delivery.view']],
        [
            'text' => 'Sales',
            'icon' => 'fas fa-fw fa-cash-register',
            'can' => 'sale.view',
            'submenu' => [
                [
                    'text' => 'All sales',
                    'route' => 'admin.sales.index',
                    'icon' => 'fas fa-fw fa-list',
                    // Everything under admin/sales except the report, which is
                    // its own entry below.
                    'active' => ['regex:@^admin/sales(?!/report)(?!/create)@'],
                ],
                [
                    'text' => 'New sale',
                    'route' => 'admin.sales.create',
                    'icon' => 'fas fa-fw fa-plus',
                    'active' => ['admin/sales/create'],
                ],
                [
                    'text' => 'Sale returns',
                    'route' => 'admin.sale-returns.index',
                    'icon' => 'fas fa-fw fa-undo',
                    'active' => ['admin/sale-returns*'],
                ],
                [
                    'text' => 'Customer dues',
                    'route' => 'admin.customer-payments.index',
                    'icon' => 'fas fa-fw fa-hand-holding-usd',
                    'active' => ['admin/customer-payments*'],
                ],
                [
                    'text' => 'Sales report',
                    'route' => 'admin.sales.report',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'active' => ['admin/sales/report*'],
                ],
            ],
        ],
        [
            'text' => 'Orders',
            'icon' => 'fas fa-fw fa-shopping-cart',
            'can' => ['order.view', 'return.view'],
            'submenu' => [
                [
                    'text' => 'All orders',
                    'route' => 'admin.orders.index',
                    'icon' => 'fas fa-fw fa-list',
                    'can' => 'order.view',
                    'active' => ['regex:@^admin/orders(?!/pending)@'],
                ],
                [
                    'text' => 'Pending orders',
                    'route' => 'admin.orders.pending',
                    'icon' => 'fas fa-fw fa-hourglass-half',
                    'can' => 'order.view',
                    'active' => ['admin/orders/pending'],
                ],
                [
                    'text' => 'Order returns',
                    'route' => 'admin.returns.index',
                    'icon' => 'fas fa-fw fa-undo',
                    'can' => 'return.view',
                    'active' => ['admin/returns*'],
                ],
                [
                    'text' => 'Return types',
                    'route' => 'admin.return-types.index',
                    'icon' => 'fas fa-fw fa-tags',
                    'can' => 'return.view',
                    'active' => ['admin/return-types*'],
                ],
                [
                    'text' => 'Client feedback',
                    'route' => 'admin.feedbacks.index',
                    'icon' => 'fas fa-fw fa-comment-dots',
                    'can' => 'return.view',
                    'active' => ['admin/feedbacks*'],
                ],
            ],
        ],
        [
            'text' => 'Store & delivery',
            'icon' => 'fas fa-fw fa-shipping-fast',
            'can' => 'delivery.view',
            'submenu' => [
                [
                    'text' => 'Dispatch queue',
                    'route' => 'admin.store.dispatch-queue',
                    'icon' => 'fas fa-fw fa-dolly',
                    'active' => ['admin/store/dispatch*'],
                ],
                [
                    'text' => 'Deliveries',
                    'route' => 'admin.deliveries.index',
                    'icon' => 'fas fa-fw fa-truck',
                    'active' => ['admin/delivery*'],
                ],
            ],
        ],
        [
            'text' => 'Clients & agents',
            'route' => 'admin.clients.index',
            'icon' => 'fas fa-fw fa-handshake',
            'can' => 'client.view',
            'active' => ['admin/clients*'],
        ],
        [
            'text' => 'Messages',
            'route' => 'admin.messages.index',
            'icon' => 'fas fa-fw fa-comments',
            'can' => 'message.view',
            'active' => ['admin/messages*'],
        ],

        ['header' => 'Buying & stock', 'can' => ['purchase.view', 'product.view', 'stock.view']],
        [
            'text' => 'Purchases',
            'icon' => 'fas fa-fw fa-truck-loading',
            'can' => 'purchase.view',
            'submenu' => [
                [
                    'text' => 'All purchases',
                    'route' => 'admin.purchases.index',
                    'icon' => 'fas fa-fw fa-list',
                    'active' => ['regex:@^admin/purchases(?!/report)(?!/create)@'],
                ],
                [
                    'text' => 'New purchase',
                    'route' => 'admin.purchases.create',
                    'icon' => 'fas fa-fw fa-plus',
                    'active' => ['admin/purchases/create'],
                ],
                [
                    'text' => 'Purchase returns',
                    'route' => 'admin.purchase-returns.index',
                    'icon' => 'fas fa-fw fa-undo',
                    'active' => ['admin/purchase-returns*'],
                ],
                [
                    'text' => 'Supplier dues',
                    'route' => 'admin.supplier-payments.index',
                    'icon' => 'fas fa-fw fa-file-invoice-dollar',
                    'active' => ['admin/supplier-payments*'],
                ],
                [
                    'text' => 'Suppliers',
                    'route' => 'admin.suppliers.index',
                    'icon' => 'fas fa-fw fa-industry',
                    'active' => ['admin/suppliers*'],
                ],
                [
                    'text' => 'Purchase report',
                    'route' => 'admin.purchases.report',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'active' => ['admin/purchases/report*'],
                ],
            ],
        ],
        [
            'text' => 'Catalogue',
            'icon' => 'fas fa-fw fa-box-open',
            'can' => 'product.view',
            'submenu' => [
                [
                    'text' => 'Products',
                    'route' => 'admin.products.index',
                    'icon' => 'fas fa-fw fa-boxes',
                    'active' => ['regex:@^admin/products(?!/report)(?!/create)@'],
                ],
                [
                    'text' => 'Add product',
                    'route' => 'admin.products.create',
                    'icon' => 'fas fa-fw fa-plus',
                    'active' => ['admin/products/create'],
                ],
                [
                    'text' => 'Categories',
                    'route' => 'admin.categories.index',
                    'icon' => 'fas fa-fw fa-folder-open',
                    'active' => ['admin/categories*'],
                ],
                [
                    'text' => 'Units',
                    'route' => 'admin.units.index',
                    'icon' => 'fas fa-fw fa-ruler',
                    'active' => ['admin/units*'],
                ],
                [
                    'text' => 'Product report',
                    'route' => 'admin.products.report',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'active' => ['admin/products/report*'],
                ],
            ],
        ],
        [
            'text' => 'Stock',
            'icon' => 'fas fa-fw fa-warehouse',
            'can' => 'stock.view',
            'submenu' => [
                [
                    'text' => 'Stock list',
                    'route' => 'admin.stocks.index',
                    'icon' => 'fas fa-fw fa-layer-group',
                    'active' => ['regex:@^admin/stocks(?!/low-quantity)(?!/expiry)@'],
                ],
                [
                    'text' => 'Low quantity',
                    'route' => 'admin.stocks.low-quantity',
                    'icon' => 'fas fa-fw fa-exclamation-triangle',
                    'active' => ['admin/stocks/low-quantity'],
                ],
                [
                    'text' => 'Expiring in 1 month',
                    'route' => 'admin.stocks.expiry.one-month',
                    'icon' => 'fas fa-fw fa-hourglass-end',
                    'active' => ['admin/stocks/expiry/one-month'],
                ],
                [
                    'text' => 'Expiring in 3 months',
                    'route' => 'admin.stocks.expiry.three-month',
                    'icon' => 'fas fa-fw fa-hourglass-half',
                    'active' => ['admin/stocks/expiry/three-month'],
                ],
                [
                    // Store CRUD is admin-only at the route level.
                    'text' => 'Stores',
                    'route' => 'admin.stores.index',
                    'icon' => 'fas fa-fw fa-store',
                    'can' => 'access-system-area',
                    'active' => ['admin/stores*'],
                ],
            ],
        ],

        ['header' => 'Money', 'can' => ['cash.view', 'expense.view', 'asset.view']],
        [
            'text' => 'Cash & bank',
            'icon' => 'fas fa-fw fa-wallet',
            'can' => 'cash.view',
            'submenu' => [
                [
                    'text' => 'Overview',
                    'route' => 'admin.cash-bank.index',
                    'icon' => 'fas fa-fw fa-chart-pie',
                    'active' => ['admin/cash-bank'],
                ],
                [
                    'text' => 'Transactions',
                    'route' => 'admin.cash-bank.transactions',
                    'icon' => 'fas fa-fw fa-exchange-alt',
                    'active' => ['admin/cash-bank/transactions'],
                ],
                [
                    'text' => 'Transfer money',
                    'route' => 'admin.cash-bank.transfer.form',
                    'icon' => 'fas fa-fw fa-money-bill-wave',
                    'active' => ['admin/cash-bank/transfer'],
                ],
                [
                    'text' => 'Transfer history',
                    'route' => 'admin.cash-bank.transfer-history',
                    'icon' => 'fas fa-fw fa-history',
                    'active' => ['admin/cash-bank/transfer-history'],
                ],
                [
                    'text' => 'Payables',
                    'route' => 'admin.accounts.payable',
                    'icon' => 'fas fa-fw fa-arrow-circle-up',
                    'active' => ['admin/accounts/payable'],
                ],
                [
                    'text' => 'Receivables',
                    'route' => 'admin.accounts.receivable',
                    'icon' => 'fas fa-fw fa-arrow-circle-down',
                    'active' => ['admin/accounts/receivable'],
                ],
                [
                    'text' => 'Salaries',
                    'route' => 'admin.salaries.index',
                    'icon' => 'fas fa-fw fa-money-check-alt',
                    'active' => ['admin/salaries*'],
                ],
            ],
        ],
        [
            'text' => 'Expenses',
            'icon' => 'fas fa-fw fa-receipt',
            'can' => 'expense.view',
            'submenu' => [
                [
                    'text' => 'All expenses',
                    'route' => 'admin.expenses.index',
                    'icon' => 'fas fa-fw fa-list',
                    'active' => ['regex:@^admin/expenses(?!/report)(?!/create)@'],
                ],
                [
                    'text' => 'Record expense',
                    'route' => 'admin.expenses.create',
                    'icon' => 'fas fa-fw fa-plus',
                    'active' => ['admin/expenses/create'],
                ],
                [
                    'text' => 'Expense heads',
                    'route' => 'admin.expense-heads.index',
                    'icon' => 'fas fa-fw fa-tags',
                    'active' => ['admin/expense-heads*'],
                ],
                [
                    'text' => 'Expense report',
                    'route' => 'admin.expenses.report',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'active' => ['admin/expenses/report*'],
                ],
            ],
        ],
        [
            'text' => 'Assets',
            'icon' => 'fas fa-fw fa-university',
            'can' => 'asset.view',
            'submenu' => [
                [
                    'text' => 'All assets',
                    'route' => 'admin.assets.index',
                    'icon' => 'fas fa-fw fa-list',
                    'active' => ['regex:@^admin/assets(?!/report)@'],
                ],
                [
                    'text' => 'Asset report',
                    'route' => 'admin.assets.report',
                    'icon' => 'fas fa-fw fa-chart-line',
                    'active' => ['admin/assets/report*'],
                ],
            ],
        ],

        ['header' => 'Insight', 'can' => 'report.view'],
        [
            'text' => 'Reports',
            'icon' => 'fas fa-fw fa-chart-bar',
            'can' => 'report.view',
            // Each entry is gated by the permission its own route enforces, not
            // by report.view alone, so no link here can 403 on click.
            'submenu' => [
                [
                    'text' => 'Reports hub',
                    'route' => 'admin.reports.index',
                    'icon' => 'fas fa-fw fa-compass',
                    'can' => 'report.view',
                    'active' => ['admin/reports'],
                ],
                [
                    'text' => 'Profit & loss',
                    'route' => 'admin.reports.profit',
                    'icon' => 'fas fa-fw fa-balance-scale',
                    'can' => 'report.view',
                    'active' => ['admin/reports/profit*'],
                ],
                [
                    'text' => 'Stock valuation',
                    'route' => 'admin.reports.stock',
                    'icon' => 'fas fa-fw fa-boxes',
                    'can' => 'report.view',
                    'active' => ['admin/reports/stock*'],
                ],
                [
                    'text' => 'Order report',
                    'route' => 'admin.reports.orders',
                    'icon' => 'fas fa-fw fa-clipboard-list',
                    'can' => 'report.view',
                    'active' => ['admin/reports/orders*'],
                ],
            ],
        ],

        ['header' => 'People', 'can' => ['user.view', 'department.view', 'role.view']],
        [
            'text' => 'Employees',
            'route' => 'admin.employees.index',
            'icon' => 'fas fa-fw fa-user-tie',
            'can' => 'user.view',
            'active' => ['admin/employees*'],
        ],
        [
            'text' => 'Departments',
            'route' => 'admin.departments.index',
            'icon' => 'fas fa-fw fa-sitemap',
            'can' => 'department.view',
            'active' => ['admin/departments*'],
        ],
        [
            'text' => 'Roles & permissions',
            'route' => 'admin.roles.index',
            'icon' => 'fas fa-fw fa-user-shield',
            'can' => 'role.view',
            'active' => ['admin/roles*'],
        ],

        ['header' => 'System', 'can' => 'access-system-area'],
        [
            'text' => 'Settings',
            'route' => 'admin.settings.index',
            'icon' => 'fas fa-fw fa-cog',
            'can' => 'access-system-area',
            'active' => ['admin/settings*'],
        ],
        [
            'text' => 'Activity log',
            'route' => 'admin.activity-log.index',
            'icon' => 'fas fa-fw fa-history',
            'can' => 'access-system-area',
            'active' => ['admin/activity-log*'],
        ],

        ['header' => 'Account'],
        [
            'text' => 'My profile',
            'route' => 'admin.profile.edit',
            'icon' => 'fas fa-fw fa-user-circle',
            'active' => ['admin/profile*', 'admin/password/change'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Support\AdminLte\UnreadMessagesBadgeFilter::class,
        App\Support\AdminLte\PendingOrdersBadgeFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => true,
                    'location' => 'assets/js/chart.umd.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
