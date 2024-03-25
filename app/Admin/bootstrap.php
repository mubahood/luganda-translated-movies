<?php

use App\Models\MovieModel;
use App\Models\Utils;
Encore\Admin\Form::forget(['map', 'editor']);

//Utils::system_boot();
/* foreach (MovieModel::all() as $key => $value) {
    $value->title = str_replace('/', '', $value->title);
    $value->save(); 
} */