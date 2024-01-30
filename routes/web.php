<?php

use App\Models\Utils;
use Illuminate\Support\Facades\Route;


/* Route::get('/', function () {
    return die('welcome');
});
Route::get('/home', function () {
    return die('welcome home');
});
*/
Route::get('/down', function () {
    Utils::system_boot(); 
});