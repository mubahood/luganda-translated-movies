<?php

namespace App\Admin\Controllers;

use App\Models\LearningMaterialPost;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LearningMaterialPostController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LearningMaterialPost';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LearningMaterialPost());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('learning_material_category_id', __('Learning material category id'));
        $grid->column('title', __('Title'));
        $grid->column('external_id', __('External id'));
        $grid->column('short_description', __('Short description'));
        $grid->column('description', __('Description'));
        $grid->column('image', __('Image'));
        $grid->column('slug', __('Slug'));
        $grid->column('external_url', __('External url'));
        $grid->column('external_download_url', __('External download url'));
        $grid->column('download_url', __('Download url'));

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
        $show = new Show(LearningMaterialPost::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('learning_material_category_id', __('Learning material category id'));
        $show->field('title', __('Title'));
        $show->field('external_id', __('External id'));
        $show->field('short_description', __('Short description'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
        $show->field('slug', __('Slug'));
        $show->field('external_url', __('External url'));
        $show->field('external_download_url', __('External download url'));
        $show->field('download_url', __('Download url'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LearningMaterialPost());

        $form->number('learning_material_category_id', __('Learning material category id'));
        $form->textarea('title', __('Title'));
        $form->textarea('external_id', __('External id'));
        $form->textarea('short_description', __('Short description'));
        $form->textarea('description', __('Description'));
        $form->textarea('image', __('Image'));
        $form->textarea('slug', __('Slug'));
        $form->textarea('external_url', __('External url'));
        $form->textarea('external_download_url', __('External download url'));
        $form->textarea('download_url', __('Download url'));

        return $form;
    }
}
