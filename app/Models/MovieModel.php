<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
