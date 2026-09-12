<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->away('http://localhost:4200');
});

require __DIR__.'/auth.php';
