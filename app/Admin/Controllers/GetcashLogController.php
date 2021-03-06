<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonSet;
use App\Admin\Models\GetcashLog;
use App\Admin\Models\PayWechat;
use App\Admin\Models\User;
use App\Admin\Models\Customer;
use App\Logics\Services\src\PaymentWechatService;
use App\Logics\Traits\ApiResponseTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use function foo\func;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetcashLogController extends AdminController
{
    use ApiResponseTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '提现日志';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid ()
    {
        if (request()->input('type') == 'user') {
            $grid = new Grid(new User());
            $user_ids = CommonSet::where('type', 'getcash')->where('name', 'user_ids')->where('status', 1)->select(['id', 'value'])->first();
            if (!$user_ids) return redirect('/'. config('admin.route.prefix'). '/getcash-logs');
            $user_ids = explode(',', $user_ids['value']);
            $grid->model()->whereIn('id', $user_ids);

            $grid->column('id', __('用户ID'));
            $grid->column('name', __('昵称'));
            $grid->column('email', __('姓名'));
            $grid->column('child2_num', __('余额'))->fen2yuan();
            $grid->column('updated_at', __('更新于'));
            $grid->column('created_at', __('创建于'));

            $grid->filter(function (Grid\Filter $filter) {
                //$filter->disableIdFilter();
                $filter->equal('user_id', '用户ID');
                $filter->equal('name', '昵称');
                $filter->equal('email', '姓名');
            });

            $grid->disableCreateButton();
            //$grid->disableActions();
            $grid->batchActions(function ($batch) {
                $batch->disableDelete();
            });
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                //$actions->disableEdit();
                $actions->disableDelete();
                $row = $actions->row;
                $actions->append('&nbsp;&nbsp;<label data-url="/'.config('admin.route.prefix').'/getcash-logs/money2user" data-status="1" data-user_id="' . $row['id'] . '" title="提现账号余额" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-money"></i> 提现</label>');
            });
        } else {
            $grid = new Grid(new GetcashLog);
            $grid->model()->orderBy('id', 'desc');
            $grid->column('id', __('日志ID'));
            $grid->column('name', __('姓名'));
            //$grid->column('user_id', __('用户ID'));
            $grid->column('money', __('金额'))->fen2yuan();
            $grid->column('status', __('状态'))->display(function ($sta){
                $stats = GetcashLog::selectList(1, ['未到账', '已到账', '提现失败']);
                return isset($stats[$sta]) ? $stats[$sta] : $stats[0];
            });
            //$grid->column('partner_trade_no', __('商户订单号'));
            //$grid->column('payment_no', __('付款单号'));
            //$grid->column('payment_at', __('付款成功时间'));
            $grid->column('remark', __('备注'));
            //$grid->column('mch_id', __('商户号'));
            $grid->column('updated_at', __('更新于'));
            $grid->column('created_at', __('创建于'));
            $grid->filter(function (Grid\Filter $filter) {
                //$filter->disableIdFilter();
                $filter->equal('user_id', '用户ID');
                $filter->equal('name', '用户姓名');
                $filter->equal('partner_trade_no', '商户订单号');
                $filter->equal('payment_no', '付款单号');
                $filter->equal('mch_id', '商户号');
                $filter->equal('status', '状态')->select([
                    0   =>  '未到账',
                    1   =>  '已到账',
                    2   =>  '提现失败',
                ]);
            });
            $grid->disableCreateButton();
            $grid->batchActions(function ($batch) {
                $batch->disableDelete();
            });
            //$grid->disableActions();
            //$grid->disableBatchActions();
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->disableView();
                $actions->disableEdit();
                $row = $actions->row;
                if($row['status'] == 0){
                    $actions->append('<label data-url="/'.config('admin.route.prefix').'/getcash-logs/through" data-status="1" data-id="' . $row['id'] . '" title="提现账号余额" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-money"></i> 同意</label>');
                }

            });
        }

        return $grid;
    }
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new User());

        $form->text('name', __('昵称'))->disable();
        $form->text('email', __('姓名'))->required();
        $form->fencurrency('child2_num', __('余额'))->required();

        return $form;
    }
    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(User::findOrFail($id));

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
     * 通过提现申请
     */
    public function Money2User()
    {
        $user_id = request()->input('user_id');
        $status = request()->input('status'); // 1提现成功；0提交审核；2提现失败
        if (!$user_id || !in_array($status, [1, 2])) {
            return $this->result([], 2000, '参数错误！');
        }

        $user = User::select(['name as nickname', 'email as name', 'child2_num as money', 'pay_openid', 'id'])->find($user_id);
        if (!$user) {
            return $this->result([], 2000, '用户异常！');
        }
        $payWechat = PayWechat::where('status', 1)->where('type', 1)->first();
        if (!$payWechat) {
            return $this->result([], 2000, '支付公众号或商户号异常！');
        }
        $partner_trade_no = date('YmdHis') . $payWechat['mch_id'] . RandCode(8, 1);
        $log = [
            'name'  => $user['name'],
            'user_id'  => $user['id'],
            'money'  => $user['money'],
            'status'  => 0,
            'partner_trade_no'  => $partner_trade_no,
            'remark'  => $user['name'] . ' - 提现',
            'mch_id'  => $payWechat['mch_id'],
        ];

        try{
            DB::beginTransaction();
            if (!$log = GetcashLog::create($log)) {
                throw new \Exception('提现记录添加失败！', 2000);
            }
            if (!User::where('id', $user['id'])->update(['child2_num'=>0])) {
                throw new \Exception('用户余额更新失败！', 2000);
            }
            $paymentWechatSer = new PaymentWechatService();
            list($result, $order) = $paymentWechatSer->Pay2User($user['pay_openid'], $user['money'], $user['name'], $partner_trade_no);

            if ($result['return_code'] != 'SUCCESS') {
                throw new \Exception('支付错误---' . $result['return_msg']);
            }
            if ( $result['result_code'] != 'SUCCESS' || (isset($result['err_code']) && $result['err_code']) ) {
                throw new \Exception('支付错误---' . (isset($result['err_code_des']) ? $result['err_code_des'] : 'result_code FAIL'));
            }

            $up_data['payment_no'] = $result['payment_no'];
            $up_data['payment_at'] = $result['payment_time'];
            $up_data['status']     = $result['result_code']=='SUCCESS' ? 1 : 0;
            // 更新提现记录结果
            if (!GetcashLog::where('id', $log['id'])->update($up_data)) {
                throw new \Exception('更新提现记录信息失败！', 2000);
            }
            DB::commit();
            return $this->result([$log, 0, '提现成功！']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage() . '___'. $partner_trade_no . '___user_id = '.$log['user_id'].'___money = '.$log['money']);
            return $this->result([], 2000, $e->getMessage());
        }

    }
    
    
    public function through(){
        $id = request()->input('id');
        $GetcashLog = GetcashLog::find($id);
        $customer = Customer::find($GetcashLog->user_id);
        try{
            DB::beginTransaction();
            //1.修改提现记录状态
            if (!GetcashLog::where('id', $id)->update(['status'=>1])) {
                throw new \Exception('用户余额更新失败1！', 2000);
            }
            //2.增加提现金额和提现次数
            $data = [
                'cash_money'=>$customer->cash_money+$GetcashLog->money,
                'cashed_num'=>$customer->cashed_num + 1,
            ];
            if (!Customer::where('id', $customer->id)->update($data)) {
                throw new \Exception('用户余额更新失败2！', 2000);
            }
            DB::commit();
            return $this->result([[], 0, '提现成功！']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return $this->result([], 2000, $e->getMessage());
        }
    }
}
