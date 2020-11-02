<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Tag;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class TagsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '标签管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Tag);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('ID'))->sortable();
        $grid->column('name', __('标签名称'));
        $grid->column('img', __('图片'))->image(100, 100);
        $grid->column('status', __('状态'))->switch(Tag::switchStatus())->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '标签名称');
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
        $show = new Show(Tag::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('name', __('标签名称'));
        $show->field('img', __('图片'))->image(100, 100);
        $show->field('status', __('状态'))->switch(Tag::switchStatus())->sortable();
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
        $form = new Form(new Tag);

        $form->display('id', __('ID'));
        $form->text('name', __('标签名称'))->required();
        $form->image('img', __('标签图标'))->move(config('app.name') . '/tag/imgs')->uniqueName()->destroy();
        $form->switch('status', __('状态'))->states(Tag::switchStatus())->default(Tag::STATUS_1);
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
