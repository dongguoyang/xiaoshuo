<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Domain;
use App\Admin\Models\DomainType;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class DomainTypesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '域名类型管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DomainType);

        $grid->column('id', __('Id'));
        $grid->column('name', __('分类名称'));
        $grid->column('status', __('状态'))->switch(Domain::switchStatus())->sortable();
        $grid->column('rand_num', __('随机域名基数'));
        $grid->column('pre', __('域名前缀'));
        // $grid->column('ip_num', __('曝光ip数'));
        //$grid->column('view_num', __('曝光数'));
        $grid->column('updated_at', __('最近更新'))->sortable();

        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $show = new Show(DomainType::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('status', __('Status'));
        $show->field('rand_num', __('Rand num'));
        $show->field('pre', __('Pre'));
        $show->field('ip_num', __('Ip num'));
        $show->field('no', __('No'));
        $show->field('view_num', __('View num'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new DomainType);

        $form->text('name', __('分类名称'));
        $form->number('rand_num', __('随机域名基数'))->min(1)->default(10);
        $form->switch('status', __('状态'))->states(Domain::switchStatus());
        $form->text('pre', __('域名前缀'));
        //$form->number('ip_num', __('曝光ip数'))->min(0)->default(0);
        //$form->number('no', __('使用组别'));
        //$form->number('view_num', __('报告次数'))->min(0)->default(0);
        //保存前回调
        $form->saving(function (Form $form) {
            $no = request()->input('no');
            $had = DomainType::where('no', $no)->first();
            if ($no && $had && request()->input('id') != $had['id'])
            {
                $error = new MessageBag([
                    'title'   => '域名组别异常',
                    'message' => '域名组别重复了',
                ]);
                return back()->with(compact('error'));
            }
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
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
            $id = $form->model()->id;
            $this->lmCacheForget($id);
        });
        return $form;
    }

    // 清除配置缓存
    public function lmCacheForget($type) {
        $key = config('app.name') . 'domain_type_info_'.$type;
        if (Cache::has($key)){
            Cache::forget($key);
        }
        $key = config('app.name') . 'domain_list_'.$type;
        if (Cache::has($key)){
            Cache::forget($key);
        }
    }
}
