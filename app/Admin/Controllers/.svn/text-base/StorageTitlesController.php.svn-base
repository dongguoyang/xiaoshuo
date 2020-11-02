<?php

namespace App\Admin\Controllers;

use App\Admin\Models\StorageTitle;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class StorageTitlesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '标题库管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StorageTitle);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('title', __('标题'))->editable();
        $grid->column('desc', __('描述'))->editable();
        //$grid->column('type', __('分类'))->editable('select', [1 => 'option1', 2 => 'option2', 3 => 'option3']);
        $grid->column('status', __('状态'))->switch(StorageTitle::switchStatus())->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            //$filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('title', '标题');
            $filter->like('desc', '描述');
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
        $show = new Show(StorageTitle::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('title', __('标题'));
        $show->field('desc', __('描述'));
        $show->field('status', __('状态'))->switch(StorageTitle::switchStatus());
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
        $form = new Form(new StorageTitle);

        $form->display('id', __('ID'));
        $form->text('title', __('标题'))->required();
        $form->text('desc', __('简述'));
        $form->switch('status', __('状态'))->states(StorageTitle::switchStatus())->default(StorageTitle::STATUS_1);
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
