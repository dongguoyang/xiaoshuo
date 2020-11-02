<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CoinLog;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\User;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class CoinLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户资金流记录';
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
        $grid = new Grid(new CoinLog);
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', __('日志ID'));
        /*$grid->column('customer_id', __('商户'))->display(function ($customer_id) {
            $customer = Customer::select(['company', 'name'])->find($customer_id);
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        $grid->column('platform_wechat_id', __('微信'))->display(function ($platform_wechat_id) {
            $platform_wechat = PlatformWechat::select(['app_name'])->find($platform_wechat_id);
            return $platform_wechat ? $platform_wechat['app_name'] : '';
        });*/
        $grid->column('user.name', __('用户'));
        $grid->column('user.created_at', __('用户注册时间'));
        $grid->column('type', __('记录类型'))->display(function ($type) {
            switch ($type) {
                case 1:
                    $style = 'color:#daaa3d;';
                    $title = '打赏';
                    break;
                case 2:
                    $style = 'color:#3dda42;';
                    $title = '充值';
                    break;
                case 3:
                    $style = 'color:#9e3dda;';
                    $title = '购书';
                    break;
                case 4:
                    $style = 'color:#3d58da;';
                    $title = '签到';
                    break;
                case 5:
                    $style = 'color:#da3d3d;';
                    $title = '抽奖';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('coin', __('书币数量'));
        $grid->column('balance', __('账户余额（书币）'));
        $grid->column('title', __('概要'));
        $grid->column('desc', __('详细'));
        $grid->column('status', __('状态'))->display(function ($status) {
            switch ($status) {
                case 0:
                    $style = 'color:#08efef;';
                    $title = '待审核';
                    break;
                case 1:
                    $style = 'color:#36ef08;';
                    $title = '生效';
                    break;
                case 2:
                    $style = 'color:#ef0837;';
                    $title = '失效';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });
        $grid->column('updated_at', __('更新于'));
        $grid->column('created_at', __('创建于'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('status', '状态')->select([
                0   =>  '待审核',
                1   =>  '生效',
                2   =>  '失效'
            ]);
            $filter->equal('type', '记录类型')->select([
                1   =>  '打赏',
                2   =>  '充值',
                3   =>  '购书',
                4   =>  '签到',
                5   =>  '抽奖',
                6   =>  '其他'
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
        $show = new Show(CoinLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户ID'));
        $show->field('platform_wechat_id', __('微信ID'));
        $show->field('user_id', __('用户ID'));
        $show->field('type', __('记录类型'));
        $show->field('type_id', __('目标ID'));
        $show->field('coin', __('书币数量'));
        $show->field('balance', __('账户余额（书币）'));
        $show->field('title', __('概要'));
        $show->field('desc', __('详细'));
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
        $form = new Form(new CoinLog);

        $form->number('customer_id', __('商户ID'))->disable();
        $form->number('platform_wechat_id', __('微信ID'))->disable();
        $form->number('user_id', __('用户ID'))->disable();
        $form->switch('type', __('记录类型'))->disable();
        $form->number('type_id', __('目标ID'))->disable();
        $form->number('coin', __('书币数量'))->disable();
        $form->number('balance', __('账户余额（书币）'))->disable();
        $form->text('title', __('概要'))->disable();
        $form->text('desc', __('详细'))->disable();
        $form->switch('status', __('状态'))->default(1)->disable();

        return $form;
    }
}
