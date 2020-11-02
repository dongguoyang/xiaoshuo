<?php

namespace App\Admin\Controllers;

use App\Admin\Models\PrizeLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\Prize;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;

class PrizeLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '抽奖日志';
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
        $grid = new Grid(new PrizeLog);
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
        $grid->column('username', __('用户'));
        $grid->column('prize_name', __('奖品'));
        $grid->column('desc', __('备注'));
        $grid->column('status', __('状态'))->display(function ($status) {
            return $status ? '<span style="color:#36ef08;">正常</span>' : '<span style="color:#ef0837;">失效</span>';
        });
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
            $filter->equal('prize_id', '奖品')->select(function () {
                return Optional(Prize::orderBy('id', 'asc')->limit(10)->get())->pluck('name', 'id');
            })->ajax('/administrator/api/prizes');
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
        $show = new Show(PrizeLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信'));
        $show->field('prize_id', __('奖品ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('username', __('用户'));
        $show->field('prize_name', __('奖品'));
        $show->field('desc', __('备注'));
        $show->field('status', __('状态'));
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
        $form = new Form(new PrizeLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信'))->disable();
        $form->number('prize_id', __('奖品ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->text('username', __('用户'))->disable();
        $form->text('prize_name', __('奖品'))->disable();
        $form->text('desc', __('备注'))->disable();
        $form->switch('status', __('状态'))->default(1)->disable();

        return $form;
    }
}
