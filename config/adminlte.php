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

    'title' => 'AdminLTE 3',
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

    'logo' => '<b>Admin</b>LTE',
    'logo_img' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

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
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

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
    'profile_url' => false,
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
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items:
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],

        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text' => 'Dashboard',
            'route' => 'admin.dashboard',
            'icon' => 'fas fa-fw fa-tachometer-alt',
            'icon_color' => 'info',
            'can' => 'dashboard.view',
        ],
        [
            'text' => 'Home',
            'route' => 'admin.home',
            'icon' => 'fas fa-fw fa-home',
            'icon_color' => 'info',
            'can' => 'view-home-fallback',
        ],
        ['header' => 'User Management', 'can' => ['department.view', 'role.view', 'user.view']],
        [
            'text' => 'Departments',
            'route' => 'admin.departments.index',
            'icon' => 'fas fa-fw fa-building',
            'icon_color' => 'primary',
            'can' => 'department.view',
        ],
        [
            'text' => 'Roles',
            'route' => 'admin.roles.index',
            'icon' => 'fas fa-fw fa-user-shield',
            'icon_color' => 'purple',
            'can' => 'role.view',
        ],
        [
            'text' => 'Employees',
            'route' => 'admin.employees.index',
            'icon' => 'fas fa-fw fa-user-tie',
            'icon_color' => 'navy',
            'can' => 'user.view',
        ],
        ['header' => 'Modules', 'can' => [
            'client.view', 'message.view', 'product.view', 'stock.view',
            'purchase.view', 'sale.view', 'order.view', 'delivery.view',
            'return.view', 'expense.view', 'cash.view', 'asset.view', 'report.view',
        ]],
        [
            'text' => 'Clients / Agents',
            'route' => 'admin.clients.index',
            'icon' => 'fas fa-fw fa-handshake',
            'icon_color' => 'success',
            'can' => 'client.view',
        ],
        [
            'text' => 'Messages',
            'route' => 'admin.messages.index',
            'icon' => 'fas fa-fw fa-comments',
            'icon_color' => 'warning',
            'can' => 'message.view',
        ],
        [
            'text' => 'Products',
            'icon' => 'fas fa-fw fa-box-open',
            'icon_color' => 'orange',
            'can' => ['product.view', 'stock.view'],
            'submenu' => [
                ['text' => 'Categories', 'route' => 'admin.categories.index', 'can' => 'product.view'],
                ['text' => 'Units', 'route' => 'admin.units.index', 'can' => 'product.view'],
                ['text' => 'Products', 'route' => 'admin.products.index', 'can' => 'product.view'],
                ['text' => 'Product Report', 'route' => 'admin.products.report', 'can' => 'product.view'],
                [
                    'text' => 'Stock',
                    'can' => 'stock.view',
                    'submenu' => [
                        // Stores CRUD is admin-only at the route level (admin.only middleware).
                        ['text' => 'Stores', 'route' => 'admin.stores.index', 'can' => 'access-system-area'],
                        ['text' => 'Stock List', 'route' => 'admin.stocks.index', 'can' => 'stock.view'],
                        ['text' => 'Low Quantity', 'route' => 'admin.stocks.low-quantity', 'can' => 'stock.view'],
                        ['text' => 'Expiring (1 Month)', 'route' => 'admin.stocks.expiry.one-month', 'can' => 'stock.view'],
                        ['text' => 'Expiring (3 Months)', 'route' => 'admin.stocks.expiry.three-month', 'can' => 'stock.view'],
                    ],
                ],
            ],
        ],
        [
            'text' => 'Purchases',
            'icon' => 'fas fa-fw fa-truck-loading',
            'icon_color' => 'maroon',
            'can' => 'purchase.view',
            'submenu' => [
                ['text' => 'Suppliers', 'route' => 'admin.suppliers.index', 'can' => 'purchase.view'],
                ['text' => 'Purchases', 'route' => 'admin.purchases.index', 'can' => 'purchase.view'],
                ['text' => 'New Purchase', 'route' => 'admin.purchases.create', 'can' => 'purchase.view'],
                ['text' => 'Purchase Report', 'route' => 'admin.purchases.report', 'can' => 'purchase.view'],
            ],
        ],
        [
            'text' => 'Sales',
            'icon' => 'fas fa-fw fa-cash-register',
            'icon_color' => 'lime',
            'can' => 'sale.view',
            'submenu' => [
                ['text' => 'Sales', 'route' => 'admin.sales.index', 'can' => 'sale.view'],
                ['text' => 'New Sale', 'route' => 'admin.sales.create', 'can' => 'sale.view'],
                ['text' => 'Sale Report', 'route' => 'admin.sales.report', 'can' => 'sale.view'],
            ],
        ],
        [
            'text' => 'Orders',
            'route' => 'admin.orders.index',
            'icon' => 'fas fa-fw fa-shopping-cart',
            'icon_color' => 'danger',
            'can' => 'order.view',
        ],
        [
            'text' => 'Store & Delivery',
            'icon' => 'fas fa-fw fa-shipping-fast',
            'icon_color' => 'teal',
            'can' => 'delivery.view',
            'submenu' => [
                ['text' => 'Dispatch Queue', 'route' => 'admin.store.dispatch-queue', 'can' => 'delivery.view'],
                ['text' => 'Deliveries', 'route' => 'admin.deliveries.index', 'can' => 'delivery.view'],
            ],
        ],
        [
            'text' => 'Returns',
            'icon' => 'fas fa-fw fa-undo',
            'icon_color' => 'fuchsia',
            'can' => 'return.view',
            'submenu' => [
                ['text' => 'All Returns', 'route' => 'admin.returns.index', 'can' => 'return.view'],
                ['text' => 'Return Types', 'route' => 'admin.return-types.index', 'can' => 'return.view'],
                ['text' => 'Client Feedback', 'route' => 'admin.feedbacks.index', 'can' => 'return.view'],
            ],
        ],
        [
            'text' => 'Expenses',
            'icon' => 'fas fa-fw fa-money-bill-wave',
            'icon_color' => 'warning',
            'can' => 'expense.view',
            'submenu' => [
                ['text' => 'Expenses', 'route' => 'admin.expenses.index', 'can' => 'expense.view'],
                ['text' => 'Expense Heads', 'route' => 'admin.expense-heads.index', 'can' => 'expense.view'],
                ['text' => 'Expense Report', 'route' => 'admin.expenses.report', 'can' => 'expense.view'],
            ],
        ],
        [
            'text' => 'Cash & Bank',
            'icon' => 'fas fa-fw fa-university',
            'icon_color' => 'olive',
            'can' => 'cash.view',
            'submenu' => [
                ['text' => 'Overview', 'route' => 'admin.cash-bank.index', 'can' => 'cash.view'],
                ['text' => 'Transactions', 'route' => 'admin.cash-bank.transactions', 'can' => 'cash.view'],
                ['text' => 'Transfer', 'route' => 'admin.cash-bank.transfer.form', 'can' => 'cash.view'],
                ['text' => 'Transfer History', 'route' => 'admin.cash-bank.transfer-history', 'can' => 'cash.view'],
                ['text' => 'Accounts Payable', 'route' => 'admin.accounts.payable', 'can' => 'cash.view'],
                ['text' => 'Accounts Receivable', 'route' => 'admin.accounts.receivable', 'can' => 'cash.view'],
                ['text' => 'Salaries', 'route' => 'admin.salaries.index', 'can' => 'cash.view'],
            ],
        ],
        [
            'text' => 'Assets',
            'icon' => 'fas fa-fw fa-boxes',
            'icon_color' => 'indigo',
            'can' => 'asset.view',
            'submenu' => [
                ['text' => 'Assets', 'route' => 'admin.assets.index', 'can' => 'asset.view'],
                ['text' => 'Asset Report', 'route' => 'admin.assets.report', 'can' => 'asset.view'],
            ],
        ],
        [
            'text' => 'Reports & Invoices',
            'icon' => 'fas fa-fw fa-chart-bar',
            'icon_color' => 'cyan',
            'can' => 'report.view',
            // Each entry is gated by the permission its own route enforces, not
            // by report.view alone, so no link here can 403 on click.
            'submenu' => [
                ['text' => 'Reports Hub', 'route' => 'admin.reports.index', 'can' => 'report.view'],
                ['text' => 'Sales Report', 'route' => 'admin.sales.report', 'can' => 'sale.view'],
                ['text' => 'Purchase Report', 'route' => 'admin.purchases.report', 'can' => 'purchase.view'],
                ['text' => 'Expense Report', 'route' => 'admin.expenses.report', 'can' => 'expense.view'],
                ['text' => 'Profit Report', 'route' => 'admin.reports.profit', 'can' => 'report.view'],
                ['text' => 'Stock Report', 'route' => 'admin.reports.stock', 'can' => 'report.view'],
                ['text' => 'Order Report', 'route' => 'admin.reports.orders', 'can' => 'report.view'],
            ],
        ],
        ['header' => 'System', 'can' => 'access-system-area'],
        [
            'text' => 'Activity Log',
            'route' => 'admin.activity-log.index',
            'icon' => 'fas fa-fw fa-history',
            'icon_color' => 'secondary',
            'can' => 'access-system-area',
        ],
        [
            'text' => 'Settings',
            'route' => 'admin.settings.index',
            'icon' => 'fas fa-fw fa-cogs',
            'icon_color' => 'gray',
            'can' => 'access-system-area',
        ],
        ['header' => 'Account'],
        [
            'text' => 'Change Password',
            'route' => 'admin.password.edit',
            'icon' => 'fas fa-fw fa-lock',
            'icon_color' => 'pink',
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
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js',
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
