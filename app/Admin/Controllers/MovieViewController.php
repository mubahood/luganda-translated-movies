<?php

namespace App\Admin\Controllers;

use App\Models\MovieView;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class MovieViewController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'MovieView';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new MovieView());
        $grid->orderBy('updated_at', 'desc');
        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Date'))->sortable();
        $grid->column('updated_at', __('Updated'))->sortable();
        $grid->column('progress', __('Progress'));
        $grid->column('movie_model_id', __('Movie model id'));
        $grid->column('user_id', __('User id'));
        $grid->column('ip_address', __('Ip address'));
        $grid->column('device', __('Device'));
        $grid->column('platform', __('Platform'));
        $grid->column('browser', __('Browser'));
        $grid->column('country', __('Country'));
        $grid->column('city', __('City'));
        $grid->column('status', __('Status'));

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
        $show = new Show(MovieView::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('movie_model_id', __('Movie model id'));
        $show->field('user_id', __('User id'));
        $show->field('ip_address', __('Ip address'));
        $show->field('device', __('Device'));
        $show->field('platform', __('Platform'));
        $show->field('browser', __('Browser'));
        $show->field('country', __('Country'));
        $show->field('city', __('City'));
        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new MovieView());

        $form->number('movie_model_id', __('Movie model id'));
        $form->number('user_id', __('User id'));
        $form->text('ip_address', __('Ip address'));
        $form->text('device', __('Device'));
        $form->text('platform', __('Platform'));
        $form->text('browser', __('Browser'));
        $form->text('country', __('Country'));
        $form->text('city', __('City'));
        $form->text('progress', __('progress'));
        $form->text('status', __('Status'))->default('Active');

        return $form;
    }
}
