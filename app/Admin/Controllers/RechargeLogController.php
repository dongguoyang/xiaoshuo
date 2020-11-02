<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Grids\Filters\TimestampBetween;
use App\Admin\Models\RechargeLog;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\MoneyBtn;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class RechargeLogController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '用户充值';
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
        $grid = new Grid(new RechargeLog);
        $grid->model()->orderBy('id', 'desc');

        $user_create = request()->input('user.created_at');
        if (isset($user_create['start']) && $user_create['start'] && isset($user_create['end']) && $user_create['end']) {
            $grid->model()->whereHas('user', function ($query) use ($user_create){
                $query->whereBetween('created_at', [strtotime($user_create['start']), strtotime($user_create['end'])]);
            });
        }

        if (request()->input('extend_link_id') > 0 || request()->input('wechat_qrcode_id') > 0) {
            if (request()->input('extend_link_id') > 0) {
                $col_name = 'extend_link_id';
                $col_val = request()->input('extend_link_id');
            } else {
                $col_name = 'wechat_qrcode_id';
                $col_val = request()->input('wechat_qrcode_id');
            }
            $grid->model()->where($col_name, $col_val);
            $grid->tools(function ($tools) use ($col_name, $col_val) {
                $query = RechargeLog::where($col_name, $col_val)->where('status', 1);
                $created_at = request()->input('created_at');
                $updated_at = request()->input('updated_at');
                if ($created_at) {
                    $start = strtotime($created_at['start']);
                    $end   = strtotime($created_at['end']);
                    if ($start < $end) {
                        $query = $query->whereBetween('created_at', [$start, $end]);
                    }
                }
                if ($updated_at) {
                    $start = strtotime($updated_at['start']);
                    $end   = strtotime($updated_at['end']);
                    if ($start < $end) {
                        $query = $query->whereBetween('updated_at', [$start, $end]);
                    }
                }
                $money = $query->sum('money');
                $money = bcdiv($money, 100, 2);
                $tools->append(' <span style="margin: auto 20px;" class="btn btn-info btn-sm">充值成功金额： <span style="font-weight: bold;"> ￥'.$money.'</span></span> ');
            });
        }

        $grid->column('id', __('日志ID'));
        /*$grid->column('customer_id', __('商户'))->display(function ($customer_id) {
            $customer = Customer::select(['name', 'company'])->find($customer_id);
            return $customer['company'] ? '【'.$customer['company'].'】'.$customer['name'] : ($customer ? $customer['name'] : '');
        });
        $grid->column('platformWechat.app_name', __('微信'));*/
        $grid->column('user_id', __('用户ID'))->display(function ($user_id){
            return "<a target='_blank' href='users?id={$user_id}'>{$user_id}</a>";
        });
        $grid->column('user.name', __('用户'));
        $grid->column('user.created_at', __('用户注册时间'));
        $grid->column('moneyBtn.desc', __('入口'));
        $grid->column('money', __('金额'))->fen2yuan();
        $grid->column('balance', __('书币余额'));
        $grid->column('status', __('充值状态'))->display(function ($status) {
            switch ($status) {
                case 0:
                    $style = 'color:#08efef;';
                    $title = '未支付';
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
        $grid->column('out_trade_no', __('订单号'));
        $grid->column('payment_no', __('第三方订单号'));
        $grid->column('pay_time', __('付款时间'))->display(function ($val){
            if ($val) {
                $val =  substr($val, 0, 4) . '-' .
                        substr($val, 4, 2) . '-' .
                        substr($val, 6, 2) . ' ' .
                        substr($val, 8, 2) . ':' .
                        substr($val, 10, 2) . ':' .
                        substr($val, 12, 2);
            }
            return $val;
        });
        $grid->column('type', __('支付方式'))->display(function ($status) {
            switch ($status) {
                case 1:
                    $style = 'color:#1bdc0e;';
                    $title = '微信';
                    break;
                case 2:
                    $style = 'color:#0e9edc;';
                    $title = '支付宝';
                    break;
                case 3:
                    $style = 'color:#f66d9b;';
                    $title = '余额';
                    break;
                default:
                    $style = 'color:#a8b1b1;';
                    $title = '其他';
                    break;
            }
            return '<span style="'.$style.'">'.$title.'</span>';
        });;
        $grid->column('desc', __('备注'));
        $grid->column('created_at', __('下单时间'));
        $grid->column('updated_at', __('最后更新时间'));
        $grid->column('customer.name', __('账户名称'));

        $grid->filter(function (Grid\Filter $filter) {
            $filter->disableIdFilter();
            $filter->equal('type', '支付方式')->select([
                1   =>  '微信',
                2   =>  '支付宝',
                3   =>  '余额'
            ]);
            $filter->equal('status', '充值状态')->select([
                0   =>  '下单成功',
                1   =>  '充值成功',
                2   =>  '充值失败'
            ]);

            $filter->equal('user_id', '用户ID');
            $filter->equal('money_btn_id', '充值入口')->select(function () {
                return optional(MoneyBtn::orderBy('id', 'asc')->get())->pluck('desc', 'id');
            });
            $filter->equal('out_trade_no', '商户单号');
            $filter->equal('payment_no', '微信支付单号');
            $filter->use(new TimestampBetween('created_at', '下单时间'))->datetime();
            $filter->use(new TimestampBetween('updated_at', '最后更新时间'))->datetime();
            $filter->use(new TimestampBetween('user.created_at', '用户注册时间'))->datetime();
            // 组员只能查询自己公众号信息
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if (!$customer['pid']) {
                $filter->equal('customer_id', '账户名称')->select(Customer::where('status', 1)->get()->pluck('name', 'id'));
                //$filter->equal('platform_wechat_id', '公众号')->select(PlatformWechat::where('status', 1)->get()->pluck('app_name', 'id'));
            }
            //  $filter->equal('created_at', '下单时间');
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
        $show = new Show(RechargeLog::findOrFail($id));

        $show->field('id', __('日志ID'));
        $show->field('customer_id', __('商户'));
        $show->field('user_id', __('用户'));
        $show->field('money_btn_id', __('入口'));
        $show->field('money', __('金额'));
        $show->field('balance', __('书币余额'));
        $show->field('status', __('充值状态'));
        $show->field('out_trade_no', __('订单号'));
        $show->field('payment_no', __('第三方订单号'));
        $show->field('pay_time', __('付款时间'));
        $show->field('type', __('支付方式'));
        $show->field('desc', __('备注'));
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
        $form = new Form(new RechargeLog);

        $form->number('customer_id', __('商户'))->disable();
        $form->number('user_id', __('用户'))->disable();
        $form->number('money_btn_id', __('入口'))->disable();
        $form->number('money', __('金额'))->disable();
        $form->number('balance', __('书币余额'))->disable();
        $form->switch('status', __('充值状态'))->disable();
        $form->text('out_trade_no', __('订单号'))->disable();
        $form->text('payment_no', __('第三方订单号'))->disable();
        $form->number('pay_time', __('付款时间'))->disable();
        $form->switch('type', __('支付方式'))->default(1)->disable();
        $form->text('desc', __('备注'))->disable();

        return $form;
    }
}
