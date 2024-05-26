<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MovieModel extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = date('Y-m-d H:i:s');
            //check if type is series
            if ($model->type == 'Series') {
                $series = SeriesMovie::find($model->category_id);
                $model->category = $series->title;
                if ($model->thumbnail_url == null || $model->thumbnail_url == '') {
                    $model->thumbnail_url = $series->thumbnail;
                }
            }
        });

        static::updating(function ($model) {
            if ($model->type == 'Series') {
                $series = SeriesMovie::find($model->category_id);
                $model->category = $series->title;
                $model->category = $series->title;
                if ($model->thumbnail_url == null || $model->thumbnail_url == '') {
                    $model->thumbnail_url = $series->thumbnail;
                }
            }

            $video_downloaded_to_server_duration = 0;
            if ($model->video_downloaded_to_server_start_time && $model->video_downloaded_to_server_end_time) {
                try {
                    $video_downloaded_to_server_duration = strtotime($model->video_downloaded_to_server_end_time) - strtotime($model->video_downloaded_to_server_start_time);
                } catch (\Exception $e) {
                    $video_downloaded_to_server_duration = -1;
                }
            }
        });
    }

    //getter for local_video_link
    public function getLocalVideoLinkAttribute($value)
    {
        if ($value == null || $value == '' || strlen($value) < 5) {
            return null;
        }
        return 'https://storage.googleapis.com/mubahood-movies/' . $value;
    }

    //title getter
    public function getTitleAttribute($value)
    {
        //check if title contains translatedfilms
        if (strpos($value, 'translatedfilms') !== false) {
            
            $names = explode('/', $value);
            if (count($names) > 1) {
                $value = $names[count($names) - 1];
                //escape  for ssql
                $this->title = $value;
                $this->save();
/*                 $value = str_replace("'", "\'", $value);
                $sql = "UPDATE movie_models SET title = '$value' WHERE id = {$this->id}";
                DB::update($sql); */
                return $value;
            }

            /* $new_title = str_replace('https://translatedfilms com/videos/', '', $value);
            $new_title = str_replace('https://translatedfilms.com/videos/', '', $new_title);
            $new_title = str_replace('https://translatedfilms com/', '', $value);
            $new_title = str_replace('https://translatedfilms.com videos/', '', $value);
            $new_title = str_replace('http://translatedfilms.com/videos/', '', $new_title);
            $new_title = str_replace('videos/', '', $new_title);
            $new_title = str_replace('translatedfilms.com', '', $new_title);
            $sql = "UPDATE movie_models SET title = '$new_title' WHERE id = {$this->id}";
            dd($sql);
            DB::update($sql);
            return $new_title; */
        } 
        //http://localhost:8888/movies-new/make-tsv

        return ucwords($value);
    }
}
