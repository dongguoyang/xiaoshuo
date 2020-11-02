<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonText;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class CommonTextsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '任务管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CommonText);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('Id'));
        $grid->column('type', __('类型'));
        $grid->column('name', __('名称'));
        // $grid->column('value', __('文本详情'));
        // $grid->column('value_type', __('取值类型'));
        $grid->column('title', __('配置描述'));
        $grid->column('status', __('状态'))->switch(CommonText::switchStatus());
        $grid->column('sort', __('排序'));
        // $grid->column('created_at', __('创建时间'));
        $grid->column('updated_at', __('最近更新'));

        $grid->actions(function ($actions){
            $actions->disableView();
        });
        $grid->filter(function($filter){
            $filter->equal('type','类型');
            $filter->like('name','名称');
            $filter->equal('status','状态')->select(['关闭','开启']);
        });

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
        $show = new Show(CommonText::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('类型'));
        $show->field('name', __('名称'));
        $show->field('value', __('文本详情'))->as(function ($value) {
            return $value;
        });
        $show->field('title', __('配置描述'));
        $show->field('status', __('状态'));
        $show->field('sort', __('排序'));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('最近更新'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CommonText);



        if (request()->route()->getActionMethod() === 'edit') {
            $form->display('type', __('类型'));
            $form->display('name', __('名称'));
        } else {
            $form->text('type', __('类型'))
                ->rules('required', [
                    'required' => '类型必填',
                ]);
            $form->text('name', __('名称'))
                ->rules('required', [
                    'required' => '名称必填',
                ]);
        }

        $form->ckeditor('value', __('文本详情'));

        $form->text('title', __('配置描述'));
        $form->switch('status', __('状态'))->states(CommonText::switchStatus())->default(CommonText::STATUS_1);
        $form->number('sort', __('排序[越小越靠前]'))->default(100)->min(1)->max(250);
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            // $footer->disableReset();
            // 去掉`提交`按钮
            // $footer->disableSubmit();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });
        $form->saved(function (Form $form) {
            $type = $form->model()->type;
            $value = $form->model()->value;
            $name = $form->model()->name;
            $id = $form->model()->id;

            $info = CommonText::where('type', $type)->where('name', $name)->first();

            $this->lmCacheForget($type, $name, $id);
            // return redirect('/administrator/commonsettypes/index/' . $type);
        });
        return $form;
    }
    /**
     * 清除文章列表缓存
     */
    public function lmCacheForget($type, $name, $id) {
        $key = 'common_text_id_'.$id;
        if (Cache::has($key)) {
            Cache::forget($key);
        }
        $key = 'common_text_type_'.$type.'_name_'.$name;
        if (Cache::has($key)) {
            Cache::forget($key);
        }
    }

}
