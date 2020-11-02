<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CustomerMoneyLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;

class CustomerMoneyLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '商家收益';
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
        $grid = new Grid(new CustomerMoneyLog);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('日志ID'));
        $grid->column('customer_id', __('商家'))->display(function ($customer_id) {
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
        $grid->column('recharge_log_id', __('充值日志ID'));
        $grid->column('money', __('入账金额'))->fen2yuan();
        $grid->column('balance', __('新余额快照'))->fen2yuan();
        $grid->column('status', __('入账状态'))->display(function ($status) {
            switch ($status) {
                case 0:
                    $style = 'color:#08efef;';
                    $title = '下单成功';
                    break;
                case 1:
                    $style = 'color:#36ef08;';
                    $title = '充值成功';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '充值失败';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('created_at', __('创建于'));
        $grid->column('updated_at', __('更新于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('status', '入账状态')->select([
                0   =>  '下单成功',
                1   =>  '充值成功',
                2   =>  '充值失败'
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
        $show = new Show(CustomerMoneyLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商家'));
        $show->field('platform_wechat_id', __('微信'));
        $show->field('user_id', __('用户'));
        $show->field('recharge_log_id', __('充值日志ID'));
        $show->field('money', __('入账金额'));
        $show->field('balance', __('新余额快照'));
        $show->field('status', __('入账状态'));
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
        $form = new Form(new CustomerMoneyLog);

        $form->number('customer_id', __('商家'))->disable();
        $form->number('platform_wechat_id', __('微信'))->disable();
        $form->number('user_id', __('用户'))->disable();
        $form->number('recharge_log_id', __('充值日志ID'))->disable();
        $form->number('money', __('入账金额'))->disable();
        $form->number('balance', __('新余额快照'))->disable();
        $form->switch('status', __('入账状态'))->disable();

        return $form;
    }
}
