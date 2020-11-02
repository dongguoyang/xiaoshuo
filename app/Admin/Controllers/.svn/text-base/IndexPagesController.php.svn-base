<?php

namespace App\Admin\Controllers;

use App\Admin\Models\IndexPage;
use App\Admin\Models\Wechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;

class IndexPagesController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '推荐管理';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new IndexPage);

        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if ($customer['pid']) {
            $grid->model()->where('customer_id', $customer['id']);
        }
        $grid->column('id', __('Id'));
        $grid->column('suitable_sex', __('使用性别'))->display(function ($val){ return IndexPage::selectList(1, [1=>'男', 2=>'女'])[$val]; });
        $grid->column('type', __('类型'))->display(function ($val) { return IndexPage::selectList(1, IndexPage::$type_s)[$val]; });
        $grid->column('novel_id', __('小说ID'));
        $grid->column('novel.title', __('小说名称'));
        $grid->column('img', __('图片'))->image('', 100);
        $grid->column('updated_at', __('最近更新'));

        $grid->disableExport(); // 去掉导出按钮
        $grid->disableFilter(); // 禁用查询过滤器
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
        $show = new Show(IndexPage::findOrFail($id));

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
        $form = new Form(new IndexPage);

        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $map = [['customer_id', $customer['id']]];
        if ($customer['pid']) {
            $map[] = ['status', 1];
        }
        $wechat = Wechat::where($map)->select('id')->first();
        $form->hidden('customer_id', '客户ID')->value($customer['id']);
        if(!$wechat) {
            $form->display('platform_wechat_id', '公众号ID')->with(function () {
                return '<span class="btn btn-xs btn-danger" onclick="window.location.href=\'/'.config('admin.route.prefix').'/wechats\';">未配置公众号，请前往配置</span>';
            });
            $form->ignore(['platform_wechat_id']);
        } else {
            $form->hidden('platform_wechat_id', '公众号ID')->value($wechat->id);
        }
        $form->select('suitable_sex', __('适用人群'))->options([1=>'男生', 2=>'女生'])->required();
        $form->select('type', __('类型'))->options(IndexPage::$type_s)->required();
        $form->text('novel_id', __('小说ID'))->rules('required|regex:/^\d+$/|min:1', [
            'regex' => '小说ID必须全部为数字',
            'min'   => '小说ID不能少于1个字符',
        ]);
        $form->image('img', __('图片'))->help('顶部轮播图必须上传图片；其他不传！')->dir('novel/indexpage');

        $form->tools(function (Form\Tools $tools) {
            // $tools->disableList();// 去掉`列表`按钮
            $tools->disableDelete();// 去掉`删除`按钮
            $tools->disableView();// 去掉`查看`按钮
        });
        $form->footer(function (Form\Footer $footer) use ($wechat) {
            $footer->disableViewCheck();// 去掉`查看`checkbox
            $footer->disableEditingCheck();// 去掉`继续编辑`checkbox
            $footer->disableCreatingCheck();// 去掉`继续创建`checkbox
            if(!$wechat) {
                $footer->disableSubmit();
            }
        });
        //保存后回调
        $form->saved(function (Form $form) {
            Cache::tags(['novel_list'])->flush();
        });
        // 抛出错误信息
        $form->saving(function (Form $form) {
            if (request()->route()->getActionMethod() === 'store' && $form->type == 1 && !is_file($form->img)) {
                $error = new MessageBag([
                    'title'   => '错误',
                    'message' => '轮播图必须上传图片',
                ]);
                return back()->with(compact('error'));
            }
        });

        return $form;
    }
}
