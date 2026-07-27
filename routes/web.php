<?php

use App\Support\AdminLanding;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect(AdminLanding::urlFor(auth()->user()))
        : redirect()->route('login');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/client.php';
