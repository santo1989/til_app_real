<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->get('/user', function () {
    return auth()->user();
});
