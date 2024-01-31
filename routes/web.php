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

Route::get('/africa', function () {
    $m = new \App\Models\AfricaTalkingResponse();
    $m->sessionId = request()->get('sessionId');
    $m->status = request()->get('status');
    $m->phoneNumber = request()->get('phoneNumber');
    $m->errorMessage = request()->get('errorMessage');
    $m->post = json_encode($_POST);
    $m->get = json_encode($_GET); 
    try {
        $m->save();
    } catch (\Throwable $th) {
        //throw $th;
    }  

    //change response to xml
    header('Content-type: text/plain');
    
    echo '<Response>
            <Play url="https://www2.cs.uic.edu/~i101/SoundFiles/gettysburg10.wav"/>
    </Response>';
    die(); 
});
Route::get('/down', function () {
    Utils::system_boot();
});
