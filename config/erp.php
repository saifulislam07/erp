<?php

return [

    'notifications' => [
        'order_placed' => env('NOTIFY_ORDER_PLACED', true),
        'order_status_change' => env('NOTIFY_ORDER_STATUS', true),
        'password_reset' => env('NOTIFY_PASSWORD_RESET', true),
    ],

];
