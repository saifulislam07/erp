<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Asset Version
    |--------------------------------------------------------------------------
    |
    | Appended as a query string to the panel's own CSS/JS so a deploy busts
    | the browser cache. Bump it whenever public/assets changes.
    |
    */

    'asset_version' => env('ERP_ASSET_VERSION', '1.1.0'),

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Used when the matching value has not been set in the Settings module.
    |
    */

    'currency_symbol' => env('ERP_CURRENCY_SYMBOL', 'BDT '),

    'notifications' => [
        'order_placed' => env('NOTIFY_ORDER_PLACED', true),
        'order_status_change' => env('NOTIFY_ORDER_STATUS', true),
        'password_reset' => env('NOTIFY_PASSWORD_RESET', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media
    |--------------------------------------------------------------------------
    |
    | Uploaded images are converted to WebP and written to
    | public/{root}/{module}/{yyyy}/{mm}/. `max_width` bounds the long edge of
    | the stored image; `quality` is the WebP encoder quality (0-100).
    |
    */

    'media' => [
        'root' => 'upload',
        'quality' => (int) env('ERP_IMAGE_QUALITY', 82),
        'max_width' => (int) env('ERP_IMAGE_MAX_WIDTH', 1600),
        'thumb_width' => (int) env('ERP_IMAGE_THUMB_WIDTH', 400),
    ],

];
