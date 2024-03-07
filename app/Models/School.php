<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    //creatig boot avoid duplicate url entries
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($school) {
            //check if the url already exists
            $existingSchool = School::where('url', $school->url)->first();
            if ($existingSchool) {
                return false;
            } 
            //check if email is valid and se has_email 
            if (filter_var($school->email, FILTER_VALIDATE_EMAIL)) {
                $school->has_email = 'Yes';
            } else {
                $school->email = null;
                $school->has_email = 'No';
            } 
            //check if website is valid and se has_website
            if (filter_var($school->website, FILTER_VALIDATE_URL)) {
                $school->has_website = 'Yes';
            } else {
                $school->website = null;
                $school->has_website = 'No';
            }

            //check if has_phone has digits
            if (preg_match('/[0-9]/', $school->phone)) {
                $school->has_phone = 'Yes';
            } else {
                $school->phone = null;
                $school->has_phone = 'No';
            } 
        });

        static::updating(function ($school) {
            //check if the url already exists
            $existingSchool = School::where('url', $school->url)
                ->where('id', '!=', $school->id)
                ->first();
            if ($existingSchool) {
                return false;
            } 
            //check if email is valid and se has_email 
            if (filter_var($school->email, FILTER_VALIDATE_EMAIL)) {
                $school->has_email = 'Yes';
            } else {
                $school->email = null;
                $school->has_email = 'No';
            } 
            //check if website is valid and se has_website
            if (filter_var($school->website, FILTER_VALIDATE_URL)) {
                $school->has_website = 'Yes';
            } else {
                $school->website = null;
                $school->has_website = 'No';
            }

            //check if has_phone has digits
            if (preg_match('/[0-9]/', $school->phone)) {
                $school->has_phone = 'Yes';
            } else {
                $school->phone = null;
                $school->has_phone = 'No';
            } 
        });
    } 
}
