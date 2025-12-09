<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('404_page');
});
