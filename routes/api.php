<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

/*/:::::::::::::::::: Authentication API (Admin, VIP, Normal) ::::::::::::::::::/*/
require __DIR__ . '/apis/apiAuth.php';

/*/:::::::::::::::::: User API ::::::::::::::::::/*/
require __DIR__ . '/apis/apiUser.php';
