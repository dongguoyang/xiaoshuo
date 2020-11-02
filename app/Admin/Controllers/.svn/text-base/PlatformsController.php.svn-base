<?php

namespace App\Admin\Controllers;

use App\Admin\Models\Platform;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PlatformsController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '开放平台信息';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Platform);

        $grid->column('id', __('Id'));
        $grid->column('name', __('名称'));
        $grid->column('img', __('Logo'))->image('', 100, 100);;
        $grid->column('appid', __('Appid'));
        $grid->column('appsecret', __('App秘钥'));
        $grid->column('auth_domain', __('授权域名'));
        $grid->column('auth_url', __('授权地址'))->display(function ($val) {
            return "<span title='{$val}'>". substr($val, 0, 12) ."</span>";
        });
        $grid->column('event_url', __('消息接收地址'))->display(function ($val) {
            return "<span title='{$val}'>". substr($val, 0, 12) ."</span>";
        });
        $grid->column('webauth_url', __('网页授权地址'))->display(function ($val) {
            return "<span title='{$val}'>". substr($val, 0, 12) ."</span>";
        });
        // $grid->column('token', __('Token'));
        // $grid->column('token_key', __('消息加解密Key'));
        $grid->column('status', __('状态'))->switch(Platform::switchStatus());
        $grid->column('is_pub', __('全网发布'))->switch(Platform::switchStatus(0, ['未发布', '已发布']));
        $grid->column('updated_at', __('最近更新'));
        // $grid->column('component_verify_ticket', __('Component verify ticket'));
        // $grid->column('authorizer_refresh_token', __('Authorizer refresh token'));
        // $grid->column('component_access_token', __('Component access token'));
        // $grid->column('token_out_time', __('Token out time'));

        $grid->disableExport(); // 去掉导出按钮
        $grid->disableFilter(); // 禁用查询过滤器
        // 添加表格头部工具
        $grid->tools(function ($actions) {
            // $actions->prepend('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            $actions->append('<a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><label class="btn btn-warning btn-sm"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="confirm2doall btn btn-danger btn-sm" data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="批量删除" style="color: #fff;"><i class="fa fa-minus"></i> 批量删除 </label>');
        });
        // 添加表格行工具
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function ($actions) use($uri) {
            // $actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
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
        $show = new Show(Platform::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('img', __('Img'));
        $show->field('appid', __('Appid'));
        $show->field('appsecret', __('Appsecret'));
        $show->field('auth_domain', __('Auth domain'));
        $show->field('auth_url', __('Auth url'));
        $show->field('event_url', __('Event url'));
        $show->field('token', __('Token'));
        $show->field('token_key', __('Token key'));
        $show->field('status', __('Status'));
        $show->field('is_pub', __('Is pub'));
        $show->field('updated_at', __('Updated at'));
        $show->field('created_at', __('Created at'));
        $show->field('component_verify_ticket', __('Component verify ticket'));
        $show->field('authorizer_refresh_token', __('Authorizer refresh token'));
        $show->field('component_access_token', __('Component access token'));
        $show->field('token_out_time', __('Token out time'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Platform);

        $form->text('name', __('名称'));
        $form->image('img', __('Logo'));
        $form->text('appid', __('Appid'));
        $form->text('appsecret', __('App秘钥'));
        $form->text('auth_domain', __('授权域名(, 分隔)'));
        $form->text('auth_url', __('授权地址'));
        $form->text('event_url', __('消息推送地址'));
        $form->text('webauth_url', __('网页授权地址'));
        $form->text('token', __('Token'));
        $form->text('token_key', __('消息加解密Key'));
        $form->switch('status', __('状态'))->default(Platform::STATUS_1)->states(Platform::switchStatus());
        $form->switch('is_pub', __('全网发布'))->default(Platform::STATUS_0)->states(Platform::switchStatus(0, ['未发布', '已发布']));
        $form->display('component_verify_ticket', __('Component verify ticket'));
        $form->display('authorizer_refresh_token', __('Authorizer refresh token'));
        $form->display('component_access_token', __('Component access token'));
        $form->display('token_out_time', __('Token过期时间'));

        $form->tools(function (Form\Tools $tools) {
            // $tools->disableList();// 去掉`列表`按钮
            $tools->disableDelete();// 去掉`删除`按钮
            $tools->disableView();// 去掉`查看`按钮
        });
        $form->footer(function ($footer) {
            $footer->disableViewCheck();// 去掉`查看`checkbox
            $footer->disableEditingCheck();// 去掉`继续编辑`checkbox
            $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
        });

        return $form;
    }
}
