<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Ad;
use App\Admin\Models\AdPosition;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class AdPositionsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '广告位管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new AdPosition);

        $grid->model()->orderBy('status', 'desc');

        $grid->model()->orderBy('id', 'desc');
        $grid->id('ID')->sortable();
        $grid->name('广告位名称');
        $grid->remark('广告位标识');
        $grid->page('显示页面')->display(function ($page) {
            return AdPosition::getPage(1)[$page];
        });
        $grid->status("状态")->switch(AdPosition::switchStatus());

        $grid->ad()->title('广告标题');
        $grid->ad()->type('广告显示类型')->display(function ($type) {return Ad::getType(1)[$type];});

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();

            $filter->column(1 / 2, function ($filter) {
                $filter->like('name', '名称');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->like('remark', '标识');
            });
        });
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
        $show = new Show(AdPosition::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('广告位名称'));
        $show->field('remark', __('广告位标识'));
        $show->field('page', __('显示页面'));
        $show->field('status', __('状态'))->as(function ($status) {
            return AdPosition::switchStatus(1)[$status];
        })->label();
        $show->field('ad_id', __('对应广告'))->as(function ($ad_id) {
            return Ad::find($ad_id)->value('title');
        });
        $show->field('code', __('特殊广告代码'));
        $show->field('updated_at', __('最近更新'))->as(function ($updated_at) {
            return date('Y-m-d H:i:s', $updated_at);
        });
        $show->field('created_at', __('创建时间'))->as(function ($created_at) {
            return date('Y-m-d H:i:s', $created_at);
        });

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new AdPosition);

        $form->text('name', '广告位名称');

        if (request()->route()->getActionMethod() === 'edit' && !request()->input('en_edit')) {
            $form->display('remark', '广告位标识');
        } else {
            $form->text('remark', '广告位标识');
        }
        $form->select('page', '显示页面')->options(AdPosition::getPage())->rules('required', [ 'required' => '请选择显示页面', ]);

        $form->select('ad_id', '广告')->options(Ad::select('id', 'title')->get()->pluck("title", 'id'))->rules('required', [ 'required' => '请选择广告', ]);
        $form->switch('status', '状态')->states(AdPosition::switchStatus())->default(AdPosition::STATUS_0);
        $form->textarea('code', '广告代码')->rows(8);

        $form->tools(function (Form\Tools $tools) {
            // 去掉跳转列表按钮
            $tools->disableListButton();
        });
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
            // $footer->disableCreatingCheck();
        });
        $form->saved(function (Form $form) {
            $this->lmCacheForget();
        });

        return $form;
    }

    public function lmCacheForget(){
        $AdCont = new AdsController();
        $AdCont->lmCacheForget();
    }
}
