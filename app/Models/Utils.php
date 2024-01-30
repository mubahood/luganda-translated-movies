<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

include_once('simple_html_dom.php');

class Utils
{

    public static function system_boot()
    {
        self::get_remote_movies_links();
        self::download_pending_movies();
        die('done');
        return 'Done';
    }

    public static function download_pending_movies()
    {
        //get video that is now video_is_downloaded_to_server_status is yes
        $video_is_downloaded_to_server_status_yes = MovieModel::where([
            'video_is_downloaded_to_server_status' => 'downloading',
        ])->orderBy('id', 'desc')->first();

        if ($video_is_downloaded_to_server_status_yes != null) {
            //check if its started 2 hours ago and reset it to no
            $now = Carbon::now();
            $video_downloaded_to_server_start_time = Carbon::parse($video_is_downloaded_to_server_status_yes->video_downloaded_to_server_start_time);
            //if started less than 2 hours ago return
            if ($video_downloaded_to_server_start_time->addHours(2)->greaterThan($now)) {
                return false;
            } 
            $video_is_downloaded_to_server_status_yes->video_downloaded_to_server_end_time = Carbon::now();
            $video_is_downloaded_to_server_status_yes->video_is_downloaded_to_server_status = 'error';
            $video_is_downloaded_to_server_status_yes->video_is_downloaded_to_server = 'yes';
            $video_is_downloaded_to_server_status_yes->video_is_downloaded_to_server_error_message = 'download took more than 2 hours';
            $video_is_downloaded_to_server_status_yes->save();
        }

        //get last movie where video_is_downloaded_to_server is no
        $last_movie = MovieModel::where([
            'video_is_downloaded_to_server' => 'no',
        ])->orderBy('id', 'desc')->first();

        if ($last_movie == null) {
            return false;
        }

        //check if video_downloaded_to_server_start_time time is not null and strlen is greater than 4
        if (
            $last_movie->video_downloaded_to_server_start_time != null &&
            strlen($last_movie->video_downloaded_to_server_start_time) > 4
        ) {
            $now = Carbon::now();
            $video_downloaded_to_server_start_time = Carbon::parse($last_movie->video_downloaded_to_server_start_time);
            //if started less than 5 minutes ago return
            if ($video_downloaded_to_server_start_time->addMinutes(5)->greaterThan($now)) {
                //return false;
            }
        }

        $download_url = 'https://images.pexels.com/photos/934011/pexels-photo-934011.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2';
        if (!self::is_localhost_server()) {
            $download_url = $last_movie->external_url;
        }

        //public_path
        $public_path = public_path() . '/storage/videos';

        //check if public_path does not exist
        if (!file_exists($public_path)) {
            mkdir($public_path);
        }

        //get last url segment
        $url_segments = explode('/', $download_url);
        $file_name = time() . "_" . rand(1000, 100000) . $url_segments[count($url_segments) - 1];
        //cjheck if contains ? and remove ? and everything after
        if (str_contains($file_name, '?')) {
            $file_name = explode('?', $file_name)[0];
        }
        $local_file_path = $public_path . '/' . $file_name;

        //set unlimited time limit
        set_time_limit(0);
        //set unlimited memory limit
        ini_set('memory_limit', '-1');

        $last_movie->video_downloaded_to_server_start_time = Carbon::now();
        $last_movie->video_is_downloaded_to_server_status = 'downloading';
        $last_movie->save();
        try {
            //download file
            $ch = curl_init($download_url);
            $fp = fopen($local_file_path, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            $last_movie->video_is_downloaded_to_server_status = 'success';
            $last_movie->video_is_downloaded_to_server = 'yes';
            $last_movie->video_is_downloaded_to_server = 'yes';
            $last_movie->video_downloaded_to_server_end_time = Carbon::now();
            $last_movie->url = 'videos/' . $file_name;
            $last_movie->save();
        } catch (\Throwable $th) {
            $last_movie->video_is_downloaded_to_server = 'yes';
            $last_movie->video_is_downloaded_to_server_status = 'error';
            $last_movie->video_is_downloaded_to_server_error_message = $th->getMessage();
            $last_movie->save();
            return false;
        }
    }




    //check if is localhost
    public static function is_localhost_server()
    {

        $server = $_SERVER['SERVER_NAME'];
        if ($server == 'localhost' || $server == '127.0.0.1') {
            return true;
        }
        return false;
    }


    public static function get_remote_movies_links()
    {


        //get last scraped movie where created_at is greater than now minus 5 minutes
        $last_scraped_movie = ScraperModel::where([
            'type' => 'movie',
            'status' => 'success',
        ])->orderBy('id', 'desc')->first();


        $can_scrape = false;
        if ($last_scraped_movie == null) {
            $can_scrape = true;
        }

        $minutes_from_last_scraped_movie = 0;
        if ($last_scraped_movie != null) {
            //now minus 5 minutes
            $now_minus_5_minutes = date('Y-m-d H:i:s', strtotime('-5 minutes'));
            //check if last scraped movie was done 5 minutes ago
            $last_scraped_movie_time = Carbon::parse($last_scraped_movie->created_at);
            if ($last_scraped_movie_time->lessThan($now_minus_5_minutes)) {
                $can_scrape = true;
            }
            $minutes_from_last_scraped_movie = $last_scraped_movie_time->diffInMinutes(Carbon::now());
        }

        if (!$can_scrape) {
            return false;
        }

        //check if there is any pending scraping latest
        $pending_scrap = ScraperModel::where([
            'type' => 'movie',
            'status' => 'pending',
        ])->orderBy('id', 'desc')->first();


        $new_scrap = new ScraperModel();

        if ($pending_scrap != null) {
            $new_scrap = $pending_scrap;
        } else {
            $new_scrap->type = 'movie';
            $new_scrap->status = 'pending';
            $new_scrap->save();
        }
        $new_scrap->url = 'https://movies.ug/videos/';

        $html = null;
        try {
            $html = file_get_html($new_scrap->url);
        } catch (\Throwable $th) {
            $new_scrap->status = 'error';
            $new_scrap->error = 'file_get_html';
            $new_scrap->error_message = $th->getMessage();
            $new_scrap->save();
        }
        if ($html == null) {
            return false;
        }

        $base_url = 'https://movies.ug/videos/';
        $movies_count = 0;
        // find all link
        try {
            foreach ($html->find('a') as $e) {
                //check if last does not contain .mp4 or .mkv or .avi or .flv or .wmv or .mov or .webm and continue
                if (!str_contains($e->href, '.mp4') && !str_contains($e->href, '.mkv') && !str_contains($e->href, '.avi') && !str_contains($e->href, '.flv') && !str_contains($e->href, '.wmv') && !str_contains($e->href, '.mov') && !str_contains($e->href, '.webm')) {
                    continue;
                }
                $movies_count++;
                $url = $base_url . $e->href;
                //check if there is no MovieModel with this url
                $movie = MovieModel::where('external_url', $url)->first();
                if ($movie != null) {
                    continue;
                }
                $movie = new MovieModel();
                $movie->url = null;
                $movie->external_url = $url;
                $movie->title = self::get_movie_title_from_url($url);
                //check if title contains season or series or episode and make type to series else make type to movie
                $temp_title = strtolower($movie->title);
                if (str_contains($temp_title, 'season') || str_contains($temp_title, 'series') || str_contains($temp_title, 'episode')) {
                    $movie->type = 'series';
                } else {
                    $movie->type = 'movie';
                }
                $movie->status = 'pending';
                $movie->downloads_count = 0;
                $movie->views_count = 0;
                $movie->likes_count = 0;
                $movie->dislikes_count = 0;
                $movie->comments_count = 0;
                $movie->video_is_downloaded_to_server = 'no';
                $movie->save();
            }
        } catch (\Throwable $th) {
            $new_scrap->status = 'error';
            $new_scrap->error = 'find all link';
            $new_scrap->error_message = $th->getMessage();
            $new_scrap->save();
        }

        //size of content from url
        $new_scrap->datae = strlen($html);
        //to mb
        $new_scrap->datae = $new_scrap->datae / 1000000;
        $new_scrap->datae = round($new_scrap->datae, 2);
        $new_scrap->title = $movies_count;
        $new_scrap->status = 'success';
        $new_scrap->save();
        return true;
    }

    public static function get_movie_title_from_url($url)
    {
        $url = str_replace('https://movies.ug/videos/', '', $url);
        //remove url encoded characters
        $url = urldecode($url);
        //remove html entities
        $url = html_entity_decode($url);
        $url = str_replace('.mp4', '', $url);
        $url = str_replace('.mkv', '', $url);
        $url = str_replace('.avi', '', $url);
        $url = str_replace('.flv', '', $url);
        $url = str_replace('.wmv', '', $url);
        $url = str_replace('.mov', '', $url);
        $url = str_replace('.webm', '', $url);
        $url = str_replace('-', ' ', $url);
        $url = str_replace('_', ' ', $url);
        $url = str_replace('.', ' ', $url);
        $url = str_replace('  ', ' ', $url);
        $url = str_replace('  ', ' ', $url);
        $url = str_replace('  ', ' ', $url);
        return $url;
    }
    public static function file_upload($file)
    {
        if ($file == null) {
            return '';
        }
        //get file extension
        $file_extension = $file->getClientOriginalExtension();
        $file_name = time() . "_" . rand(1000, 100000) . "." . $file_extension;
        $public_path = public_path() . "/storage/images";
        $file->move($public_path, $file_name);
        $url = 'images/' . $file_name;
        return $url;
    }

    public static function get_user(Request $r)
    {
        $logged_in_user_id = $r->header('logged_in_user_id');
        $u = User::find($logged_in_user_id);
        return $u;
    }

    public static function success($data, $message)
    {
        //set header response to json
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'code' => 1,
            'message' => $message,
            'data' => $data,
        ]);
        die();
    }

    public static function error($message)
    {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'code' => 0,
            'message' => $message,
            'data' => null,
        ]);
        die();
    }

    static function getActiveFinancialPeriod($company_id)
    {
        return FinancialPeriod::where('company_id', $company_id)
            ->where('status', 'Active')->first();
    }

    static public function generateSKU($sub_category_id)
    {
        //year-subcategory-id-serial
        $year = date('Y');
        $sub_category = StockSubCategory::find($sub_category_id);
        $serial = StockItem::where('stock_sub_category_id', $sub_category_id)->count() + 1;
        $sku = $year . "-" . $sub_category->id . "-" . $serial;
        return $sku;
    }
}
