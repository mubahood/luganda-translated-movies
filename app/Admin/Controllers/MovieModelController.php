<?php

namespace App\Admin\Controllers;

use App\Models\MovieModel;
use App\Models\SeriesMovie;
use App\Models\Utils;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MovieModelController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Movies';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        /*  MovieModel::where([])
            ->update([
                'status' => 'Inactive',
            ]); */
        $grid = new Grid(new MovieModel());
        $grid->quickSearch('title');
        $grid->model()->orderBy('updated_at', 'desc');
        $grid->disableBatchActions();
        $grid->column('id', __('Id'))->sortable()->hide();
        $grid->column('created_at', __('Created'))
            ->display(function ($created_at) {
                return date('Y-m-d H:i:s', strtotime($created_at));
            })->sortable()->hide();
        $grid->column('updated_at', __('Updated'))
            ->display(function ($updated_at) {
                return date('Y-m-d H:i:s', strtotime($updated_at));
            })->sortable()->hide();
        $grid->column('title', __('Title'))->sortable();
        $grid->column('external_url', __('External URL'))->sortable()->hide();
        $grid->column('url', __('Url'))->sortable()
            ->display(function ($_url) {
                if (strlen($_url) < 2) {
                    return 'N/A';
                }
                $_url = url('/storage/' . $_url);
                return '<a href="' . $_url . '" target="_blank">' . 'VIEW' . '</a>';
            });

        $grid->column('thumbnail_url', __('Thumbnail'))
            ->image('', 50, 50)->sortable();
        $grid->column('description', __('Description'))->hide();
        $grid->column('year', __('Year'))->sortable()->hide();
        /*         
        $grid->column('rating', __('Rating'));
        $grid->column('duration', __('Duration'));
        $grid->column('size', __('Size'));
        $grid->column('genre', __('Genre'));
        $grid->column('director', __('Director'));
        $grid->column('stars', __('Stars'));
        $grid->column('country', __('Country'));
        $grid->column('language', __('Language'));
        $grid->column('imdb_url', __('Imdb url'));
        $grid->column('imdb_rating', __('Imdb rating'));
        $grid->column('imdb_votes', __('Imdb votes'));
        $grid->column('imdb_id', __('Imdb id')); */
        $grid->column('type', __('Type'))->sortable();
        $grid->column('error', __('Error'))->hide();
        $grid->column('error_message', __('Error message'))->hide();
        $grid->column('downloads_count', __('Downloads count'))->hide();
        $grid->column('views_count', __('Views count'))->hide();
        $grid->column('likes_count', __('Likes count'))->hide();
        $grid->column('dislikes_count', __('Dislikes count'))->hide();
        $grid->column('comments_count', __('Comments count'))->hide();
        $grid->column('comments', __('Comments'))->hide();
        $grid->column('video_is_downloaded_to_server', __('Downloaded'))->sortable()
            ->filter([
                'yes' => 'Yes',
                'no' => 'No',
            ])->hide();
        $grid->column('video_downloaded_to_server_start_time', __('Doenload Start Time'))
            ->display(function ($video_downloaded_to_server_start_time) {
                return date('Y-m-d H:i:s', strtotime($video_downloaded_to_server_start_time));
            })->sortable()->hide();
        $grid->column('video_downloaded_to_server_end_time', __('Downloaded End time'))
            ->display(function ($video_downloaded_to_server_end_time) {
                return date('Y-m-d H:i:s', strtotime($video_downloaded_to_server_end_time));
            })->sortable()
            ->hide();
        $grid->column('video_downloaded_to_server_duration', __('Video downloaded to server duration'))
            ->display(function ($video_downloaded_to_server_duration) {
                //convert seconds to minutes
                $minutes = floor($video_downloaded_to_server_duration / 60);
                $seconds = $video_downloaded_to_server_duration % 60;
                return $minutes . ':' . $seconds;
            })->sortable()->hide();
        $grid->column('video_is_downloaded_to_server_status', __('downloaded status'))
            ->filter([
                'downloading' => 'Downloading',
                'error' => 'eorr',
                'success' => 'success',
            ])->sortable()->hide();
        $grid->column('video_is_downloaded_to_server_error_message', __('Video is downloaded to server error message'))->hide();
        $grid->column('category', __('Category'))->hide();
        $grid->column('category_id', __('Category id'))->hide();
        $grid->column('status', __('Status'))
            ->filter([
                'yes' => 'Yes',
                'no' => 'No',
                'Failed' => 'Failed',
            ])->sortable();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(MovieModel::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('title', __('Title'));
        $show->field('external_url', __('External url'));
        $show->field('url', __('Url'));
        $show->field('image_url', __('Image url'));
        $show->field('thumbnail_url', __('Thumbnail url'));
        $show->field('description', __('Description'));
        $show->field('year', __('Year'));
        $show->field('rating', __('Rating'));
        $show->field('duration', __('Duration'));
        $show->field('size', __('Size'));
        $show->field('genre', __('Genre'));
        $show->field('director', __('Director'));
        $show->field('stars', __('Stars'));
        $show->field('country', __('Country'));
        $show->field('language', __('Language'));
        $show->field('imdb_url', __('Imdb url'));
        $show->field('imdb_rating', __('Imdb rating'));
        $show->field('imdb_votes', __('Imdb votes'));
        $show->field('imdb_id', __('Imdb id'));
        $show->field('type', __('Type'));
        $show->field('status', __('Status'));
        $show->field('error', __('Error'));
        $show->field('error_message', __('Error message'));
        $show->field('downloads_count', __('Downloads count'));
        $show->field('views_count', __('Views count'));
        $show->field('likes_count', __('Likes count'));
        $show->field('dislikes_count', __('Dislikes count'));
        $show->field('comments_count', __('Comments count'));
        $show->field('comments', __('Comments'));
        $show->field('video_is_downloaded_to_server', __('Video is downloaded to server'));
        $show->field('video_downloaded_to_server_start_time', __('Video downloaded to server start time'));
        $show->field('video_downloaded_to_server_end_time', __('Video downloaded to server end time'));
        $show->field('video_downloaded_to_server_duration', __('Video downloaded to server duration'));
        $show->field('video_is_downloaded_to_server_status', __('Video is downloaded to server status'));
        $show->field('video_is_downloaded_to_server_error_message', __('Video is downloaded to server error message'));
        $show->field('category', __('Category'));
        $show->field('category_id', __('Category id'));
        $show->field('is_processed', __('Is processed'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MovieModel());
        $form->radio('status', __('Status'))
            ->options([
                'Active' => 'Active',
                'Inactive' => 'Inactive',
            ])
            ->default('active')
            ->rules('required');
        $form->text('title', __('Title'))->rules('required');
        $form->image('thumbnail_url', __('Thumbnail'))
            ->removable()
            ->downloadable();
        $form->file('url', __('Movie file'));

        $form->select('genre', __('VJ'))
            ->options(
                Utils::$JV
            )->rules('required');
        $form->radio('type', __('Type'))
            ->options([
                'Movie' => 'Movie',
                'Series' => 'Series',
            ])
            ->default('movie')
            ->rules()
            ->when('Series', function (Form $form) {
                $form->select('category_id', __('Select Series'))->rules('required')
                    ->options(SeriesMovie::all()->pluck('title', 'id'));
                $form->decimal('country', 'Position')->rules('required');
            })->when('Movie', function (Form $form) {
                $form->select('category', __('Category'))
                    ->options(
                        Utils::$CATEGORIES
                    )->rules('required');
            });

        $form->divider();
        $form->decimal('year', __('Year'));
        $form->decimal('rating', __('Rating'));
        $form->decimal('duration', __('Duration'));
        $form->quill('description', __('Movie Description'));
        $form->text('image_url', __('Image url'));
        $form->text('external_url', __('External url'));
        $form->decimal('size', __('Size'));

        $form->text('director', __('Director'));
        $form->text('stars', __('Stars'));
        $form->text('language', __('Language'));
        $form->text('imdb_url', __('Imdb url'));
        $form->decimal('imdb_rating', __('Imdb rating'));
        $form->decimal('imdb_votes', __('Imdb votes'));
        $form->text('imdb_id', __('Imdb id'));



        $form->text('views_count', __('Views count'));
        $form->text('likes_count', __('Likes count'));
        $form->text('dislikes_count', __('Dislikes count'));
        $form->text('comments_count', __('Comments count'));
        $form->text('comments', __('Comments'));

        $form->text('is_processed', __('Is processed'));

        return $form;
    }
}
