<?php

namespace App\Admin\Controllers;

use App\Admin\Models\SignLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class SignLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '签到日志';
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
        $grid = new Grid(new SignLog);
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
        $grid->column('user_id', __('用户'))->display(function ($user_id) {
            $user = User::find($user_id);
            return $user ? $user->name : '<span style="color:red">-未知-</span>';
        });
        $grid->column('continue_day', __('连续签到天数'));
        $grid->column('coin', __('奖励书币数量'));
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
            $filter->equal('user_id', '用户')->select(function () {
                return optional(User::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/users');
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
        $show = new Show(SignLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('continue_day', __('连续签到天数'));
        $show->field('coin', __('奖励书币数量'));
        $show->field('updated_at', __('更新于'));
        $show->field('created_at', __('创建于'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SignLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->number('continue_day', __('连续签到天数'))->default(1)->disable();
        $form->number('coin', __('奖励书币数量'))->disable();

        return $form;
    }
}
