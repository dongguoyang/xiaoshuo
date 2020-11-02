<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CouponLog;
use App\Admin\Models\Coupon;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Database\Eloquent\Builder;

class CouponLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '书券使用记录';
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
        $grid = new Grid(new CouponLog);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('记录ID'));
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
        $grid->column('coupon_id', __('书券'))->display(function ($coupon_id) {
            $coupon = Coupon::find($coupon_id);
            return $coupon ? $coupon->name : '<span style="color:red">-未知-</span>';
        });
        $grid->column('status', __('状态'))->display(function ($status) {
            switch ($status) {
                case 1:
                    $style = 'color:#36ef08;';
                    $title = '可用';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '已用';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '失效';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('valid_date_range', __('有效期'))->display(function () {
            return '<span style="color:#36ef08;">'.$this->start_at.'</span>&nbsp;&nbsp;至&nbsp;&nbsp;<span style="color:#ef0837;">'.$this->end_at.'</span>';
        });
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('status', '状态')->select([
                0   =>  '失效',
                1   =>  '可用',
                2   =>  '已用'
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
            $filter->equal('coupon_id', '书券')->select(function () {
                return optional(Coupon::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/coupons');
            $filter->where(function (Builder $query) {
                $query->where('start_at', '>=', strtotime($this->input));
            }, '起始时间', 'start_at')->date()->default(date('Y-m-d'));
            $filter->where(function (Builder $query) {
                $query->where('end_at', '<=', strtotime($this->input));
            }, '截止时间', 'end_at')->date()->default(date('Y-m-d'));
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
        $show = new Show(CouponLog::findOrFail($id));

        $show->field('id', __('记录ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('coupon_id', __('书券ID'));
        $show->field('status', __('状态'));
        $show->field('start_at', __('起始时间'));
        $show->field('end_at', __('截止时间'));
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
        $form = new Form(new CouponLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->number('coupon_id', __('书券ID'))->disable();
        $form->switch('status', __('状态'))->default(1)->disable();
        $form->number('start_at', __('起始时间'))->disable();
        $form->number('end_at', __('截止时间'))->disable();

        return $form;
    }
}
