<?php

use App\Models\MovieModel;
use App\Models\Utils;
Encore\Admin\Form::forget(['map', 'editor']);

//Utils::system_boot();
/* foreach (MovieModel::all() as $key => $value) {
    $value->title = str_replace('/', '', $value->title);
    $value->save(); 
} */

try {
    $u = \App\Models\User::where('email', 'mubahood360@gmail.com')->first();
    $code = rand(100000, 999999);
    $u->secret_code = $code;
    $u->save();

    $mail_body = <<<EOD
    <p>Dear {$u->name},</p>
    <p>Your password reset code is <b>$code</b></p>
    <p>Thank you.</p>
    EOD;

    $data['email'] = $u->email;
    $date = date('Y-m-d');
    $data['subject'] = "Password Reset Code - ".env('APP_NAME');
    $data['body'] = $mail_body;
    $data['data'] = $data['body'];
    $data['name'] = $u->name;
    try {
        Utils::mail_sender($data);
    } catch (\Throwable $th) {
    }
    dd("success");
} catch (\Exception $e) {
    dd("failed because " . $e->getMessage());
    return Utils::error($e->getMessage());
}
die("done");