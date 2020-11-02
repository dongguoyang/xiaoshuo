<?php

namespace App\Admin\Controllers;

use App\Admin\Models\QrcodeData;
use App\Admin\Models\RechargeLog;
use App\Admin\Models\User;
use App\Admin\Models\WechatQrcode;
use App\Logics\Repositories\src\DomainRepository;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class QrcodeDatasController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '多二维码数据合并统计';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new QrcodeData);
        $grid->model()->orderBy('id', 'desc');
        $grid->column('id', __('ID'));
        $grid->column('name', __('筛选名称'));
        $grid->column('start_at', __('统计时间段'))->display(function ($start_at){
            return $start_at . ' 到 ' . $this->end_at;
        });
        // $grid->column('cost', __('渠道成本'))->fen2yuan();
        $grid->column('recharge_num', __('充值信息'))->display(function ($recharge_num){
            $rel = "订单数：{$recharge_num}";
            $rel .= "<br>成交数：{$this->recharge_succ_num}";
            $rel .= "<br>充值成功率：".($recharge_num ? (bcdiv(($this->recharge_succ_num * 100), $recharge_num, 2)).'%' : '暂无数据');
            $rel .= "<br>充值金额：￥". bcdiv($this->recharge_succ_money, 100, 2);
            $rel .= "<br>渠道成本：￥". bcdiv($this->cost, 100, 2);
            $rel .= "<br>回本率：".($this->cost ? (bcdiv(($this->recharge_succ_money * 100), $this->cost, 2)).'%' : '暂无数据');
            return $rel;
        });
        $grid->column('user_num', __('推广数据'))->display(function ($user_num){
            $rel = "新用户数：{$user_num}";
            $rel .= "<br>扫码次数：{$this->scan_num}";
            $rel .= "<br>关注次数：{$this->sub_num}";
            $rel .= "<br>充值/关注比：". ($this->sub_num ? (bcdiv(($this->recharge_num * 100), $this->sub_num, 2)).'%' : '暂无数据');
            $rel .= "<br>充值成功/关注比例：".($this->sub_num ? (bcdiv(($this->recharge_succ_num * 100), $this->sub_num, 2)).'%' : '暂无数据');
            return $rel;
        });
        $grid->column('updated_at', __('最近更新'));


        $grid->disableExport(); // 去掉导出按钮
        //$grid->disableFilter(); // 禁用查询过滤器
        // 添加表格头部工具
        $grid->tools(function ($actions) {
            // $actions->prepend('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="btn btn-warning btn-sm"><a target="_blank" href="' . route('platform.auth') . '" title="去授权" style="color: #fff;"><i class="fa fa-podcast"></i> 去授权 </label></a>');
            // $actions->append('<label class="confirm2doall btn btn-danger btn-sm" data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="批量删除" style="color: #fff;"><i class="fa fa-minus"></i> 批量删除 </label>');
        });
        // 添加表格行工具
        $uri = request()->getUri(); $uri = stripos($uri, '?') ? substr($uri, 0, stripos($uri, '?')) : $uri;
        $grid->actions(function ($actions) use($uri) {
            //$actions->disableDelete();// 去掉删除
            $actions->disableEdit();// 去掉编辑
            $actions->disableView();// 去掉查看
            // $actions->append('<label data-url="/'.config('admin.route.prefix').'/domains/dels" data-status="2" title="删除" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-trash"></i> 删除</label>');
            $actions->append('<a href="'. $uri.'/'.$actions->getKey() .'/edit" class="grid-row-edit btn btn-xs btn-primary" title="编辑"><i class="fa fa-edit"></i> 编辑</a>');
            $actions->append('<a href="javascript:void(0);" data-id="' .$actions->getKey(). '" class="grid-row-delete grid-row-delete2 btn btn-xs btn-danger" title="删除"><i class="fa fa-trash"></i> 删除</a>');
            $actions->append('<br><label data-url="/'.config('admin.route.prefix').'/qrcodedatas/setqrcodedatas" data-id="' . $actions->row['id'] . '" title="重新生成数据" class="confirm2do btn btn-danger btn-xs"><i class="fa fa-rotate-left"></i> 更新数据</label>');
        });
        $grid->filter(function($filter){
            $filter->disableIdFilter();// 去掉默认的id过滤器
            $filter->like('name', '筛选名称');
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
        $show = new Show(QrcodeData::findOrFail($id));

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
        $form = new Form(new QrcodeData);

        $form->text('name', __('筛选名称'))->required();
        $form->fencurrency('cost', __('渠道成本'))->required();
        // $form->switch('default', __('是否默认'))->default(QrcodeData::STATUS_0)->states(QrcodeData::switchStatus(0, ['否', '是']));
        $form->multipleSelect('wechat_qrcode_ids', __('二维码列表'))->options(WechatQrcode::orderBy('id', 'desc')->select(['id', 'name'])->get()->pluck('name', 'id'));
        $form->datetimeRange('start_at', 'end_at', '筛选范围');

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
        //保存后回调
        $form->saved(function (Form $form) {
            $this->SetQrcodeDatas($form->model()->id);
        });

        return $form;
    }

    public function SetQrcodeDatas($id = 0) {
        $id = $id > 0 ? $id : request()->input('id');

        $info = QrcodeData::find($id);
        if (!$info) {
            return ['err_code'=>2000, 'err_msg'=>'数据异常！'];
        }
        $qrcode_ids = $info['wechat_qrcode_ids'];
        $start_at = strtotime($info['start_at']);
        $end_at   = strtotime($info['end_at']);
        $up_data = [
            'recharge_num'  => 0,
            'recharge_succ_num'  => 0,
            'recharge_money'  => 0,
            'recharge_succ_money'  => 0,
            'user_num'  => 0,
            'scan_num'  => 0,
            'sub_num'  => 0,
        ];
        $up_data['recharge_num'] = RechargeLog::whereIn('wechat_qrcode_id', $qrcode_ids)->whereBetween('created_at', [$start_at, $end_at])->count();
        $up_data['recharge_succ_num'] = RechargeLog::whereIn('wechat_qrcode_id', $qrcode_ids)->whereBetween('created_at', [$start_at, $end_at])->where('status', 1)->count();
        $up_data['recharge_money'] = RechargeLog::whereIn('wechat_qrcode_id', $qrcode_ids)->whereBetween('created_at', [$start_at, $end_at])->sum('money');
        $up_data['recharge_succ_money'] = RechargeLog::whereIn('wechat_qrcode_id', $qrcode_ids)->whereBetween('created_at', [$start_at, $end_at])->where('status', 1)->sum('money');
        $up_data['user_num'] = User::whereIn('wechat_qrcode_id', $qrcode_ids)->whereBetween('created_at', [$start_at, $end_at])->count();
        $qr_list = WechatQrcode::whereIn('id', $qrcode_ids)->select(['id', 'scan_num', 'sub_num', 'user_num'])->get();
        foreach ($qr_list as $item) {
            $up_data['scan_num'] += $item['scan_num'];
            $up_data['sub_num'] += $item['sub_num'];
        }
        if (QrcodeData::where('id', $id)->update($up_data)) {
            return ['err_code'=>0, 'err_msg'=>'更新成功！'];
        }

        return ['err_code'=>2000, 'err_msg'=>'更新失败！'];
    }
}
