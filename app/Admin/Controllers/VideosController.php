<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Type;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VideosController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '分类管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Type);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('分类名称'));
        $grid->column('sort', __('排序'))->sortable()->editable();
        $grid->column('status', __('状态'))->switch(Type::switchStatus())->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '分类名称');
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
        });

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed   $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Type::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('分类名称'));
        $show->field('sort', __('排序'));
        $show->field('status', __('状态'))->switch(Type::switchStatus());
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Type);

        $form->display('id', __('ID'));
        $form->text('name', __('分类名称'))->required();
        $form->number('sort', __('排序'))->default(100)->min(0)->max(250)->required();
        $form->switch('status', __('状态'))->states(Type::switchStatus())->default(Type::STATUS_1);
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
