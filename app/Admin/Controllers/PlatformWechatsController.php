<?php

namespace App\Admin\Controllers;

use App\Admin\Models\PlatformWechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class PlatformWechatsController extends AdminController
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
        $grid = new Grid(new PlatformWechat);

        // 组员只能查询自己公众号信息

        $grid->column('id', __('Id'));
        $grid->column('customer.name', __('客户名称'));
        $grid->column('img', __('公众号二维码'))->image('', 100, 100);
        $grid->column('appid', __('Appid'));
        // $grid->column('app_secret', __('App秘钥'));
        $grid->column('app_name', __('公众号'));
        $grid->column('mch_id', __('商户号'));
        $grid->column('service_type', __('公众号类型'))->display(function ($val){return PlatformWechat::selectList(1, ['订阅号', '订阅号老', '服务号'])[$val];});
        $grid->column('auth_time', __('授权时间'));
        $grid->column('status', __('状态'))->switch(PlatformWechat::switchStatus());
        $grid->column('updated_at', __('最近更新'));

        $grid->disableExport(); // 去掉导出按钮
        // 添加表格头部工具
        $grid->tools(function ($actions) {
            // $actions->prepend('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
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
        $grid->filter(function($filter){
            $filter->disableIdFilter();// 去掉默认的id过滤器
            $filter->column(1/2, function ($filter) {
                $filter->like('name', '公众号');
            });
            $filter->column(1/2, function ($filter) {
                $filter->equal ('appid', 'Appid');
            });
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
        $show = new Show(PlatformWechat::findOrFail($id));

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
        $form = new Form(new PlatformWechat);

        $form->text('app_name', __('名称'));
        $form->image('img', __('公众号二维码'));
        $form->text('appid', __('Appid'));
        $form->text('app_secret', __('App秘钥'));
        $form->text('js_api_ticket', __('js_api_ticket'));
        $form->text('js_api_ticket_out_time', __('js_api_ticket_out_time'));
        $form->text('origin_id', __('原始ID'));
        $form->switch('status', __('状态'))->default(PlatformWechat::STATUS_1)->states(PlatformWechat::switchStatus());
        $form->display('token', __('Token'));
        $form->display('token_out', __('Token失效时间'));
        $form->display('refresh_token', __('RefreshToken'));
        $form->display('refresh_out', __('RefreshToken失效时间'));
        $form->display('verify_ticket', __('verify_ticket'));
        $form->text('mch_id', __('商户号'));
        $form->text('mch_secret', __('商户号API秘钥'));
        $form->multipleFile('mch_pem_dir', __('商户证书'))->disk('resource')->dir(request()->input('mch_id'));

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
