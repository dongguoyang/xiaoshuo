<?php

namespace App\Admin\Controllers;

use App\Admin\Models\RewardLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\Goods;
use App\Admin\Models\Novel;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RewardLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '打赏日志';
    public $customer;

    public function customer() {
        if(!empty($this->customer)) {
            return $this->customer;
        } else {
            return $this->customer = Admin::user();
        }
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RewardLog);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('日志ID'));
        $grid->column('customer_id', __('商户'))->display(function ($customer_id) {
            $customer = Customer::find($customer_id);
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        $grid->column('platform_wechat_id', __('微信'))->display(function ($platform_wechat_id) {
            $platform_wechat = PlatformWechat::find($platform_wechat_id);
            return $platform_wechat ? $platform_wechat['app_name'] : '';
        });
        $grid->column('novel_id', __('小说'))->display(function ($novel_id) {
            $novel = Novel::find($novel_id);
            return $novel ? ($novel['title'] ?: $novel['desc']) : '<span style="color:red">不存在</span>';
        });
        $grid->column('user', __('用户'))->display(function () {
            return '<img src="'.$this->user_img.'" onerror="this.src=\'https://novelsys.oss-cn-shenzhen.aliyuncs.com/user/avatar/default-avatar.png\';" style="max-width:50px;max-height:50px" class="img img-thumbnail">&nbsp;&nbsp;<span style="color:#f15c5c;">'.$this->username.'</span>';
        });
        $grid->column('goods_id', __('礼物'))->display(function ($goods_id) {
            $goods = Goods::find($goods_id);
            return $goods ? $goods->name : '<span style="color:red">不存在</span>';
        });
        $grid->column('goods_num', __('礼物数'));
        $grid->column('coin_num', __('价值（书币）'));
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('status', '状态')->select([
                0   =>  '失效',
                1   =>  '正常'
            ]);
            if($this->customer()->isAdministrator()) {
                $filter->equal('customer_id', '商家')->select(function () {
                    return Optional(Customer::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
                })->ajax('/administrator/api/customers');
            }
            $filter->equal('platform_wechat_id', '微信')->select(function () {
                return Optional(PlatformWechat::orderBy('id', 'asc')->limit(10)->get())->pluck('app_name', 'id');
            })->ajax('/administrator/api/platform-wechats');
            $filter->equal('novel_id', '小说')->select(function () {
                return Optional(Novel::orderBy('id', 'asc')->limit(10)->get())->pluck('title', 'id');
            })->ajax('/administrator/api/novels');
            $filter->equal('user_id', '用户')->select(function () {
                return optional(User::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/users');
            $filter->equal('goods_id', '礼物')->select(function () {
                return optional(Goods::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/goods');
        });

        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->actions(function (Grid\Displayers\Actions $actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
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
        $show = new Show(RewardLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信ID'));
        $show->field('novel_id', __('小说ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('username', __('用户名'));
        $show->field('user_img', __('头像'));
        $show->field('goods_id', __('礼物ID'));
        $show->field('goods_num', __('数量'));
        $show->field('coin_num', __('价值（书币）'));
        $show->field('created_at', __('创建于'));
        $show->field('updated_at', __('更新于'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RewardLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信ID'))->disable();
        $form->number('novel_id', __('小说ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->text('username', __('用户名'))->disable();
        $form->text('user_img', __('头像'))->disable();
        $form->number('goods_id', __('礼物ID'))->disable();
        $form->number('goods_num', __('数量'))->disable();
        $form->number('coin_num', __('价值（书币）'))->disable();

        return $form;
    }
}
