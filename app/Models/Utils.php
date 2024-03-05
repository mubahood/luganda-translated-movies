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
        // self::get_remote_movies_links();
        // self::download_pending_movies();
        // self::download_pending_thumbs();
        self::process_thumbs();
        return 'Done';
    }

    public static function process_thumbs()
    {
        if (!self::is_localhost_server()) {
            return false;
        }
        //links where processed is no limit 10
        //set unlimited time limit
        set_time_limit(0);
        //set unlimited memory limit
        ini_set('memory_limit', '-1');
        /* Link::where([])->update([
            'processed' => 'No',
            'success' => 'No',
            'error' => null,
        ]); */
        $links = Link::where('processed', 'No')->limit(1000)->get();
        $movies = MovieModel::where('thumbnail_url',null)->get();

        foreach ($links as $key => $link) {
            $new_movies = self::sortBySimilarity($movies, $link->title);
            $movie = null;
            $count = 0;
            $down_link = null;
            foreach ($new_movies as $key => $val) {
                $similarity = self::has_similar_words($val->title, $link->title);

                if ($similarity < 1) {
                    continue;
                }

                $count++;
                if ($count > 5) {
                    break;
                }

                $movie = $val;
                break;
            }

            if ($movie == null) {
                $link->processed = 'Yes';
                $link->success = 'No';
                $link->error = 'No similar movie found';
                continue;
            }

            $thumbnail_url = 'https://movies.ug' . $link->thumbnail;
            $public_path = public_path() . '/storage/images';

            //check if public_path does not exist
            if (!file_exists($public_path)) {
                //mkdir($public_path);
            }

            //extension of thumbnail
            $extension = pathinfo($thumbnail_url, PATHINFO_EXTENSION);
            if ($extension == null || $extension == '') {
                $extension = 'jpg';
            }

            //download file
            try {
                $ch = curl_init($thumbnail_url);
                $fp = fopen($public_path . '/' . $movie->id . '.' . $extension, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                $link->processed = 'Yes';
                $link->success = 'Yes';
                $link->save();
                $movie->thumbnail_url = 'images/' . $link->id . '.jpg';
                $movie->save();
                echo 'Downloaded ' . $link->id . '. ' . $link->title . ' ' . $link->thumbnail . ' ' . $link->external_id . ' ' . $link->url . ' ' . $link->processed . ' ' . $link->success . ' ' . $link->error . '<br>';
            } catch (\Throwable $th) {
                $link->processed = 'Yes';
                $link->success = 'No';
                $link->error = $th->getMessage();
                $link->save();
                echo 'Error ' . $link->id . ' ' . $link->title . ' ' . $link->thumbnail . ' ' . $link->external_id . ' ' . $link->url . ' ' . $link->processed . ' ' . $link->success . ' ' . $link->error . '<br>';
            }
        }
    }

    //check if two strings contains similar words
    public static function has_similar_words($str1, $str2)
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);

        //replace / with ''

        $str1 = explode(' ', $str1);
        $str2 = explode(' ', $str2);
        $similar_words = 0;
        $skip = [
            'the',
            'a',
            'an',
            'and',
            'or',
            'of',
            'in',
            'on',
            'at',
            'to',
            'for',
            'with',
            'by',
            'from',
            'up',
            'Series',
            'Season',
            'Episode',
            'Movie',
            'Film',
            'TV',
            'Show',
            'Full',
            'HD',
            '1080p',
            '720p',
            '480p',
            '360p',
            '240p',
            '144p',
            'Download',
            'Watch',
            'Online',
            'Free',
            'Streaming',
            'Video',
            'Clip',
            'Vj',
        ];
        //coverting skip to lowercase
        $skip = array_map('strtolower', $skip);

        foreach ($str1 as $key => $val) {
            //$skip to lowercase
            $val = strtolower($val);
            //str1 to lowercase
            $val = strtolower($val);
            //skip if in skip
            if (in_array($val, $skip)) {
                continue;
            }

            if (in_array($val, $skip)) {
                continue;
            }
            if (in_array($val, $str2)) {
                $similar_words++;
            }
        }
        return $similar_words;
    }


    public static function getSimilarityScore($str1, $str2)
    {
        $len1 = mb_strlen($str1, 'UTF-8');
        $len2 = mb_strlen($str2, 'UTF-8');

        $matrix = [];

        for ($i = 0; $i <= $len1; $i++) {
            $matrix[$i] = [$i];
        }

        for ($j = 0; $j <= $len2; $j++) {
            $matrix[0][$j] = $j;
        }

        for ($i = 1; $i <= $len1; $i++) {
            for ($j = 1; $j <= $len2; $j++) {
                $cost = (mb_substr($str1, $i - 1, 1, 'UTF-8') != mb_substr($str2, $j - 1, 1, 'UTF-8')) ? 1 : 0;
                $matrix[$i][$j] = min(
                    $matrix[$i - 1][$j] + 1,
                    $matrix[$i][$j - 1] + 1,
                    $matrix[$i - 1][$j - 1] + $cost
                );
            }
        }

        return $matrix[$len1][$len2];
    }


    public static function sortBySimilarity($movies, $searchString)
    {
        $searchString = strtolower($searchString);

        $sortedMovies = $movies->sortBy(function ($movie) use ($searchString) {
            return self::getSimilarityScore(strtolower($movie->title), $searchString);
        });

        return $sortedMovies;
    }




    //movie search algorithm

    public static function download_pending_thumbs()
    {


        $start_num = 0;
        $max_num = 10;

        //get last page
        $last_page = Page::orderBy('id', 'desc')->first();
        if ($last_page != null) {
            $start_num = $last_page->id;
        }
        $end_num = $start_num + $max_num;

        $base_url = 'https://movies.ug/index.php?page=';
        for ($i = $start_num; $i < $end_num; $i++) {
            $url = $base_url . $i;

            //check if there is no Page with this url
            $page = Page::where('url', $url)->first();
            if ($page != null) {
                continue;
            }
            $html = null;
            try {
                $html = file_get_html($url);
            } catch (\Throwable $th) {
                continue;
            }
            if ($html == null) {
                continue;
            }


            foreach ($html->find('a') as $e) {
                if ($e->href == null) {
                    continue;
                }
                if (!str_contains($e->href, 'play.php?')) {
                    continue;
                }
                if ($e->children == null) {
                    continue;
                }
                foreach ($e->children as $key1 => $child) {
                    if ($child->tag != 'img') {
                        continue;
                    }
                    if ($child->src == null) {
                        continue;
                    }
                    //check if there is no Link with this src
                    $link = Link::where('thumbnail', $child->src)->first();
                    if ($link != null) {
                        continue;
                    }
                    $l = new Link();
                    $l->thumbnail = $child->src;
                    $l->title = $child->title;
                    $l->url = $e->href;
                    $l->external_id = $e->href;
                    $l->save();
                }
            }

            $page = new Page();
            $page->url = $url;
            $page->title = $url;
            $page->save();
        }

        return true;
        die('done');



        //get last movie where video_is_downloaded_to_server is no
        $last_movie = MovieModel::where([
            'image_url' => null,
        ])->orderBy('id', 'desc')->first();


        $search_url = 'https://movies.ug/?search=' . ($last_movie->title);

        die($search_url);

        dd($last_movie->title);

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
        $file_name = time() . "_" . rand(1000, 100000);
        //cjheck if contains ? and remove ? and everything after
        //get file extension
        if (str_contains($download_url, '.')) {
            $file_extension = explode('.', $download_url)[1];
        } else {
            $file_extension = '.mp4';
        }
        //check if file extension is not mp4 or mkv or avi or flv or wmv or mov or webm
        if (
            $file_extension != 'mp4' &&
            $file_extension != 'mkv' &&
            $file_extension != 'avi' &&
            $file_extension != 'flv' &&
            $file_extension != 'wmv' &&
            $file_extension != 'mov' &&
            $file_extension != 'webm'
        ) {
            $file_name .= '.mp4';
        } else if ($file_extension == 'webm') {
            $file_name .= '.mp4';
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
        $file_name = time() . "_" . rand(1000, 100000);
        //cjheck if contains ? and remove ? and everything after
        //get file extension
        if (str_contains($download_url, '.')) {
            $file_extension = explode('.', $download_url)[1];
        } else {
            $file_extension = '.mp4';
        }
        //check if file extension is not mp4 or mkv or avi or flv or wmv or mov or webm
        if (
            $file_extension != 'mp4' &&
            $file_extension != 'mkv' &&
            $file_extension != 'avi' &&
            $file_extension != 'flv' &&
            $file_extension != 'wmv' &&
            $file_extension != 'mov' &&
            $file_extension != 'webm'
        ) {
            $file_name .= '.mp4';
        } else if ($file_extension == 'webm') {
            $file_name .= '.mp4';
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
