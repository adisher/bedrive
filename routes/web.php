<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Common\Core\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

//FRONT-END ROUTES THAT NEED TO BE PRE-RENDERED
Route::get('/', [HomeController::class, 'show'])->middleware(
    'prerenderIfCrawler:homepage',
);

//CATCH ALL ROUTES AND REDIRECT TO HOME
Route::get('{all}', [HomeController::class, 'show'])->where('all', '.*');
