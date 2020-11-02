<?php

namespace App\Admin\Controllers;

use App\Admin\Models\StorageImg;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\MessageBag;

class StorageImgsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '图片库管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new StorageImg);

        $grid->column('id', __('ID'))->sortable();
        $grid->column('img', __('图片'))->image('', 100);
        //$grid->column('type', __('分类'))->editable('select', [1 => 'option1', 2 => 'option2', 3 => 'option3']);
        $grid->column('status', __('状态'))->switch(StorageImg::switchStatus())->sortable();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            //$filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('img', '图片地址名称');
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
        $show = new Show(StorageImg::findOrFail($id));

        $show->field('id', __('ID'));
        $show->field('img', __('图片'));
        $show->field('status', __('状态'))->switch(StorageImg::switchStatus());
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
        $form = new Form(new StorageImg);

        $form->display('id', __('ID'));
        $form->image('img', __('图片'))//->disk('local')
            ->move('img-storage')
            ->removable()->downloadable()
            ->help('建议图片尺寸 200x200像素；大小不超过30KB')
            ->required();
        $form->switch('status', __('状态'))->states(StorageImg::switchStatus())->default(StorageImg::STATUS_1);
        $form->footer(function ($footer) {
            $footer->disableViewCheck();
            $footer->disableEditingCheck();
            $footer->disableCreatingCheck();
        });
        return $form;
    }
}
