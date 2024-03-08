<?php

namespace App\Admin\Controllers;

use App\Models\LearningMaterialCategory;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class LearningMaterialCategoryController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'LearningMaterialCategory';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LearningMaterialCategory());

        $grid->column('id', __('Id'));
        $grid->column('created_at', __('Created at'));
        $grid->column('updated_at', __('Updated at'));
        $grid->column('name', __('Name'));
        $grid->column('short_description', __('Short description'));
        $grid->column('description', __('Description'));
        $grid->column('image', __('Image'));
        $grid->column('color', __('Color'));
        $grid->column('icon', __('Icon'));
        $grid->column('slug', __('Slug'));
        $grid->column('order', __('Order'));
        $grid->column('status', __('Status'));
        $grid->column('external_url', __('External url'));

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
        $show = new Show(LearningMaterialCategory::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('name', __('Name'));
        $show->field('short_description', __('Short description'));
        $show->field('description', __('Description'));
        $show->field('image', __('Image'));
        $show->field('color', __('Color'));
        $show->field('icon', __('Icon'));
        $show->field('slug', __('Slug'));
        $show->field('order', __('Order'));
        $show->field('status', __('Status'));
        $show->field('external_url', __('External url'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LearningMaterialCategory());

        $form->textarea('name', __('Name'));
        $form->textarea('short_description', __('Short description'));
        $form->textarea('description', __('Description'));
        $form->textarea('image', __('Image'));
        $form->textarea('color', __('Color'));
        $form->textarea('icon', __('Icon'));
        $form->textarea('slug', __('Slug'));
        $form->number('order', __('Order'));
        $form->number('status', __('Status'));
        $form->textarea('external_url', __('External url'));

        return $form;
    }
}
