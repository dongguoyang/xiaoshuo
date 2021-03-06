<?php

namespace App\Logics\Services\src;

use App\Jobs\OrderNoPay;
use App\Logics\Repositories\src\CoinLogRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\CustomerMoneyLogRepository;
use App\Logics\Repositories\src\CustomerRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\MoneyBtnRepository;
use App\Logics\Repositories\src\PageLogRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\RechargeLogRepository;
use App\Logics\Repositories\src\TestRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\PayWechatRepository;
use App\Logics\Repositories\src\StatisticRepository;
use App\Logics\Repositories\src\WechatQrcodeRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\PushmsgTrait;
use App\Logics\Traits\RedisCacheTrait;
use Illuminate\Support\Facades\DB;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;
use App\Logics\Services\src\UserService;

class PaymentWechatService extends Service {
    use PushmsgTrait, RedisCacheTrait;

    private $PayObj;
    private $wechat;

    protected $tests;
    protected $users;
    protected $customers;
    protected $customerMoneyLogs;
    protected $moneyBtns;
    protected $rechargeLogs;
    protected $platformWechats;
    protected $wechatConfigs;
    protected $extendLinks;
    protected $wechatQrcodes;
    protected $commonSets;
    protected $coinLogs;
    protected $payWechats;
    protected $domains;
    protected $statistics;
    protected $pageLogs;

    public function Repositories() {
        return [
            'tests'         => TestRepository::class,
            'users'         => UserRepository::class,
            'moneyBtns'     => MoneyBtnRepository::class,
            'rechargeLogs'  => RechargeLogRepository::class,
            'customers'     => CustomerRepository::class,
            'customerMoneyLogs' => CustomerMoneyLogRepository::class,
            'platformWechats'   => PlatformWechatRepository::class,
            'wechatConfigs' => WechatConfigRepository::class,
            'extendLinks'   => ExtendLinkRepository::class,
            'wechatQrcodes' => WechatQrcodeRepository::class,
            'commonSets'    => CommonSetRepository::class,
            'coinLogs'      => CoinLogRepository::class,
            'payWechats'    => PayWechatRepository::class,
            'domains'       => DomainRepository::class,
            'statistics'    =>  StatisticRepository::class,
            'pageLogs'      => PageLogRepository::class,
        ];
    }

    private function initPay($wechat = [] ,$id = 0) {
        if (!$this->wechat) {
            if (!$wechat) {
                if($id){
                    $this->wechat = $this->payWechats->initWechat(1, true, 0, $id);
                }else{
                    $this->wechat = $this->payWechats->initWechat(1, true);
                }
            } else {
                $this->wechat = $wechat;
            }
        }
        if($this->wechat['mch_id'] != '1600374670'){
            $notify_url = route('payment_notify', ['type' => 'wechat','id'=>$this->wechat['mch_id'],'end'=>'end'], true);
        }else{
            $notify_url = route('payment_notify', ['type' => 'wechat'], true);
        }
        //$notify_url = route('payment_notify', ['type' => 'wechat'], true);
        // 微信配置
        $config = [
            'app_id'    => $this->wechat['appid'],  // env('WECHAT_APP_ID', 'wx650c518f5af2060e'),// 公众号 APPID
            'miniapp_id'=> env('WECHAT_MINIAPP_ID', ''),    // 小程序 APPID
            'appid'     => env('WECHAT_APPID', ''), // APP 引用的 appid
            'mch_id'    => $this->wechat['mch_id'], // env('WECHAT_MCH_ID', '1501308161'),// 微信支付分配的微信商户号
            'notify_url'=> $notify_url, // 微信支付异步通知地址
            'key'       => $this->wechat['mch_secret'],    // env('WECHAT_KEY', '00977fef1ca3c1c0983b69b932ba8301'),// 微信支付签名秘钥
            'cert_client'   => resource_path('cert/'.$this->wechat['mch_id'].'/') . 'apiclient_cert.pem',// 客户端证书路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
            'cert_key'  => resource_path('cert/'.$this->wechat['mch_id'].'/') . 'apiclient_key.pem',// 客户端秘钥路径，退款、红包等需要用到。请填写绝对路径，linux 请确保权限问题。pem 格式。
            'log'       => [// optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
                'file'  => storage_path('logs/'.$this->wechat['mch_id'].'/wechat.log'),
                'level' => 'debug',
                'type'  => 'daily', // optional, 可选 daily. single
                'max_file'  => 30,
            ],
            // optional
            // 'dev' 时为沙箱模式
            // 'hk' 时为东南亚节点
            // 'mode' => 'dev',
        ];
        //dd($config);
        $this->PayObj = Pay::wechat($config);
        return $this->PayObj;
    }
    /**
     * 获取充值金额按钮列表
     */
    public function MoneyBtns() {
        $type = request()->input('type');
        $customer_id = request()->input('customer_id');
        switch ($type) {
            case 'vip': // 年费或者季度会员充值
                $list = $this->moneyBtns->allByMap([['status', 1], ['default',9]], $this->moneyBtns->model->fields);
                break;
            case 'first': // 首充优惠
                $list = $this->moneyBtns->findByMap([['status', 1], ['default',8]], $this->moneyBtns->model->fields);
                break;
            case 'page_act': // 充值页面底部的优惠活动
                $list = $this->moneyBtns->findByMap([['status', 1], ['default',6]], $this->moneyBtns->model->fields);
                break;
            case 'act': // 优惠活动
                $btn_id = request()->input('btn_id');
                /*if (!$btn_id) {
                    throw new \Exception('金额按钮不存在！', 2000);
                }
                $act_map = [['status', 1], ['default',7], ['id', $btn_id]];*/
                //$act_map = [['status', 1], ['default',7]];
                $act_map = [['default',7]];
                if ($btn_id) {
                    $act_map[] = ['id', $btn_id];
                }
                $list = $this->moneyBtns->findByMap($act_map, $this->moneyBtns->model->fields);
                if($list){
                    $start_time = strtotime($list['start_at']);
                    $end_time = strtotime($list['end_at']);
                    if(time() >$end_time){  //活动结束
                        $list['status'] = 2;
                    }
                    if($start_time > time()){ //活动还没开始
                        $list['status'] = 3;
                    }
                }
                break;
            default:
                $list = $this->moneyBtns->allByMap([['status', 1], ['default', '<', 2]], $this->moneyBtns->model->fields);
                $list = $this->moneyBtns->toArr($list);
                $sess = $this->loginGetSession(true);
                if (!$sess['id'] || $sess['recharge_money']==0) { // 添加首充按钮
                    $first = $this->moneyBtns->findByMap([['status', 1], ['default',5]], $this->moneyBtns->model->fields);
                    $first = $this->moneyBtns->toArr($first);
                    if ($first) {
                        array_unshift($list, $first);
                    }
                }
                $en_getcashids = $this->commonSets->values('getcash', 'user_ids');
                if ($sess['id'] && strpos(','.$en_getcashids.',', ','.$sess['id'].',') !== false) {
                    $bigmoney = $this->moneyBtns->findByMap([['status', 1], ['default', 4]], $this->moneyBtns->model->fields);
                    $bigmoney = $this->moneyBtns->toArr($bigmoney);
                    if ($bigmoney) {
                        array_unshift($list, $bigmoney);
                    }
                }
                break;
        }
        if (!$list) {
            throw new \Exception('数据异常！', 2000);
        }

        $rel['btns'] = $list;

        //$rel['pay_domain'] = $this->commonSets->values('payment', 'authurl'); // 支付页面的安全域名
        if($customer_id){
            $wechat = $this->payWechats->initWechat(1, true,$customer_id);
            $rel['pay_domain'] = $wechat['redirect_uri'] . route('paymen.login2unifiedorder', [], false); // 支付页面的安全域名+地址
        }else{
            $wechat = $this->payWechats->initWechat(1, true);
            $rel['pay_domain'] = $wechat['redirect_uri'] . route('paymen.login2unifiedorder', [], false); // 支付页面的安全域名+地址
        }


        return $this->result($rel);
    }
    /**
     * 登录并统一下单
     */
    public function Login2Unifiedorder() {
        if (!isset($_GET['code']) || !$_GET['code']) {
            $this->pageLogs->IncColNum('recharge_start');// 统计进入支付页面次数
            // 没有授权 code 跳转获取授权code
            $customer_id = request()->input('cid', 0);
            $user_id     = request()->input('user_id', 0);
            $money_btn_id= request()->input('money_btn_id');
            $backurl     = request()->input('backurl');
            if (/*!$customer_id || !$user_id ||*/ !$money_btn_id || !$backurl) {
                throw new \Exception("参数异常1： {$customer_id};  {$user_id};  {$money_btn_id};  {$backurl}", 2000);
            }
            $replace     = request()->input('replace', 0);
            $replace = ($replace==='1' || $replace==='true' || $replace===1 || $replace===true) ? 1 : 0;

            $state = [
                'c'  => $customer_id, // 客户id
                'b'  => $money_btn_id,// 金额按钮id
                'u'  => $user_id,     // 用户id
                'r'  => $replace,     // 是否代付
                'p'  => WechatStateStr($backurl, 'encode'),// 返回页面地址
            ];
            return $this->payWechats->ToAuthCode($state);
        } else {
            list($openid, $token, $wechat, $state) = $this->payWechats->AuthToken2Openid();// 返回openid和网页授权token
            $this->initPay($wechat);
            //判断支付是否到达上限 到达就切账号
            if($wechat['money_today'] + 3000 >= $wechat['money_max']){  //切换支付
                $this->payWechats->update(['status'=>0],$wechat['id']);
            }
            // 不是代付的时候；没有用户信息可以从数据库里面查询获取
            if ((!isset($state['r']) || !$state['r']) &&
                (!isset($state['u']) || !$state['u'] || !is_numeric($state['u']) ||
                    !isset($state['c']) || !$state['c'] || !is_numeric($state['c']))
            ) {
                $user = $this->users->FindByPayOpenid($openid);
                $state['u'] = $user['id'];
                if ($user['view_cid'] && $this->customers->findByMap([
                        ['id', $user['view_cid']],
                        ['status', 1],
                        ['pid', '>', 0],
                    ], ['web_tpl'])) {
                    $state['c'] = $user['view_cid'];
                } else {
                    $state['c'] = $user['customer_id'];
                }
            }
            if (empty($state['u']) || empty($state['c'])) {
                // 没有用户信息；就重新返回前端执行登录
                $pages = config('frontpage');
                $customer = $this->customers->findBy('status', 1, ['web_tpl', 'id']);
                return redirect('/' . $customer['web_tpl'] . $pages['recharge'] . '?cid='. $customer['id'] . '&customer_id=' .$customer['id']);
                // throw new \Exception('用户信息异常，暂不能支付！', 3000);
            }

            $user = $this->users->UserCacheInfo($state['u']);

            if (empty($state['r']) && $user['pay_openid'] != $openid) {
                // 支付号改变；更新用户支付号数据
                $up_data['pay_openid'] = $openid;
                $user = $this->users->UpdateFromDB($user['id'], $up_data);
            }
            //记录一下那个支付号支付的
            $user['pay_wechat_id'] = $wechat['id'];
            // 统一下单并添加充值记录
            $data = $this->unifiedOrder($user, $state, $openid);
            $dtype = $user['subscribe'] ? 2 : 5;

            $data['backhost']   = urlencode($this->domains->randOne($dtype, $state['c'])) ;
            $data['user_id']    = $state['u'];
            $data['cid']        = $state['c'];
            $data['order']      = urlencode(json_encode($data['order']));
            $data['backurl']    = WechatStateStr($state['p'], 'decode');
            if (strpos($data['backurl'], '?')) {
                $data['backurl'] .= '&cid=' . $data['cid'];
            } else {
                $data['backurl'] .= '?cid=' . $data['cid'];
            }
            $data['backurl'] = urlencode($data['backurl']);
            $data['money_btn_id']   = $state['b'];

            //$host = $this->commonSets->values('payment', 'authurl'); // 支付页面的安全域名+地址
            $host = $this->wechat['redirect_uri']; // 支付页面的安全域名+地址
            $customer = $this->customers->find($state['c'], ['web_tpl']);
            $this->pageLogs->IncColNum('recharge_end'); // 统计支付页面打开次数
            return redirect($host . '/' . $customer['web_tpl'] . '/#/wx-pay.html?' . http_build_query($data));
        }
    }

    /**
     * H5 微信统一下单
     */
    public function unifiedOrder($user, $state, $openid) {
        $order = $this->assembleOrder($user, $state['b'], $openid, $state['r']); // 组合订单信息
        $data['out_trade_no'] = $order['out_trade_no'];

        //if (isWechatBrowser()) {
        // 公众号支付
        $ordered = $this->PayObj->mp($order);
        if (!isset($ordered['appId']) || !isset($ordered['paySign']) || !$ordered['paySign'] || !isset($ordered['package']) || !$ordered['package']) {
            throw new \Exception('统一下单发生异常！', 2000);
        }
        $data['order'] = $ordered;
        return $data;
        //} else {
        // H5 支付
        //return $this->PayObj->wap($order);
        //}
    }
    /**
     * 组合订单信息
     */
    private function assembleOrder($user, $money_btn_id, $openid, $replace = 0)
    {
        $money_btn = $this->moneyBtns->find($money_btn_id, $this->moneyBtns->model->fields);

        if (!$money_btn) {
            throw new \Exception('金额异常！', 2000);
        }

        $order['out_trade_no'] = date('YmdHis') . RandCode(15, 12);

        if ($money_btn['default'] == 9) {
            $desc = '开通年费VIP';
        } else {
            $desc = '充值书币';
        }
        $replace = empty($replace) ? '' : '找人代付充值：';
        $desc .= '|' . $replace . $money_btn['desc'];

        // 微信订单信息
        if (isWechatBrowser()) {
            $order['openid'] = $openid;
        }
        $order['body'] = $desc;
        $order['total_fee'] = $money_btn['price'].'';


        $this->addRechargeLog($order, $user, $money_btn); // 添加充值记录

        return $order;
    }

    /**
     * 插入充值记录
     * @param array $order 订单信息
     */
    private function addRechargeLog($order, $user, $money_btn)
    {
        $extend_link_id = 0; // 推广链接ID
        $wechat_qrcode_id = 0;// 推广二维码ID
        $customer_id = $user['customer_id'];
        $platform_wechat_id = $user['platform_wechat_id'];
        if (!$user['view_cid'] || $user['view_cid'] == $user['customer_id']) {
            if ($user['extend_link_id'] && $user['created_at'] + $this->extendLinks->validTime > time()) {
                // 就是主用户进行充值，且在推广链接记录有效期内
                $extend_link_id = $user['extend_link_id'];
            }
            if ($user['wechat_qrcode_id'] && $user['created_at'] + $this->wechatQrcodes->validTime > time()) {
                // 就是主用户进行充值，且在推广链接记录有效期内
                $wechat_qrcode_id = $user['wechat_qrcode_id'];
            }
        } else {
            $sub_user = $this->users->SubUserCacheInfo($user['id'], $user['view_cid']);
            if ($sub_user) {
                if ( $sub_user['extend_link_id'] && $sub_user['created_at'] + $this->extendLinks->validTime > time() ) {
                    // 子用户进行充值，且在推广链接记录有效期内
                    $extend_link_id = $sub_user['extend_link_id'];
                }
                if ( $sub_user['wechat_qrcode_id'] && $sub_user['created_at'] + $this->wechatQrcodes->validTime > time() ) {
                    // 子用户进行充值，且在推广链接记录有效期内
                    $wechat_qrcode_id = $sub_user['wechat_qrcode_id'];
                }
                //$customer_id = $sub_user['customer_id'];
                //$platform_wechat_id = $sub_user['platform_wechat_id'];
            }
        }

        $data = [
            'platform_wechat_id'=> $platform_wechat_id,
            'customer_id'       => $customer_id, // 哪个公众号进来的就统计到哪个号里面
            'money_btn_id'      => $money_btn['id'],
            'user_id'           => $user['id'],
            'money'             => isset($order['total_fee']) ? $order['total_fee'] : $order['total_amount'],
            'coin'              => ($money_btn['default'] == 9 ? 365 : $money_btn['coin']),
            'balance'           => $user['balance'] + $money_btn['coin'],
            'out_trade_no'      => $order['out_trade_no'],
            'payment_no'        => '',
            'type'              => isset($order['total_fee']) ? 1 : 2,
            'desc'              => isset($order['body']) ? $order['body'] : $order['subject'],
            'view_cid'          => $user['view_cid'],
            'extend_link_id'    => $extend_link_id,
            'wechat_qrcode_id'  => $wechat_qrcode_id,
            'pay_wechat_id'     => isset($user['pay_wechat_id']) ? $user['pay_wechat_id'] : 0,
        ];

        $rel = $this->rechargeLogs->create($data);
        $this->rechargeLogs->ClearCache($user['id']); // 清除用户充值记录缓存

        if ($extend_link_id > 0) {
            // 推广链接增加订单
            $this->extendLinks->UpdateInfo($extend_link_id, ['recharge'=>1]);
        }
        if ($wechat_qrcode_id > 0) {
            // 推广二维码增加订单
            $this->wechatQrcodes->IncColumns($wechat_qrcode_id, ['order_num'=>1, 'order_money'=>$data['money']]);
        }

        // 统计订单数据；是哪个账号进来的就统计到哪个账号里面
        $customer = $this->customers->findByMap([
            ['id', $customer_id],
            ['status', 1],
        ], $this->customers->model->fields);
        //$customer = $this->customers->find($customer_id);
        if($customer) {
            $current_date = date('Y-m-d');
            $customer = $customer->toArray();
            $group_id = $customer['pid'] ?: $customer['id'];
            // 归属商户
            $map = [
                'date_belongto' => $current_date,
                'group_id'      => $group_id,
                'customer_id'   => $customer['id'],
            ];
            $up_data = [
                'unpay_num'  => 1,
            ];
            $this->statistics->UpdateColumnNum($map, $up_data);

            // 商户小组
            $map['customer_id'] = 0;
            $this->statistics->UpdateColumnNum($map, $up_data);
        } else {
            // 没有对应公众号账号就只统计总数据
            $customerPar = $this->customers->model->where('status', 1)->orderBy('id', 'asc')->select(['id'])->first(); // 查询总账号
            $current_date = date('Y-m-d');
            $map = [
                'date_belongto' => $current_date,
                'group_id'      => $customerPar['id'],
                'customer_id'   => 0,
            ];
            $up_data = [
                'unpay_num'  => 1,
            ];
            // 商户小组
            $this->statistics->UpdateColumnNum($map, $up_data);
        }

        return $rel;
    }

    /**
     * 支付异步通知地址
     */
    public function NotifyUrl($type,$id)
    {
        $this->insertTest('notify');
        $input = request()->input();
        if($id){
            $info = ($this->payWechats->model)->where('status', 1)->where('type', 1)->where('mch_id',$id)->select($this->payWechats->model->fields)->first();
            if ($info) {
                $info = $this->payWechats->toArr($info);
                $id = $info['id'];
            }
            $this->initPay([],$id);
        }else{
            $this->initPay();
        }
        //dd(route('payment_notify', ['type' => 'wechat','id'=>123123123], true));
        $data = $this->PayObj->verify(); // 验证签名
        Log::debug('Wechat notify', $data->all());

        $this->wechatNotify(); // 微信支付异步通知结果处理

        return $this->PayObj->success();
    }
    /**
     * 微信支付通知
     */
    private function wechatNotify()
    {
        $xml = file_get_contents('php://input');
        $rel = XmlToArray($xml);
        if ($rel['return_code'] != 'SUCCESS') {
            throw new \Exception($rel['return_msg'], 4000);
        }
        if ($rel['result_code'] != 'SUCCESS') {
            throw new \Exception($rel['err_code_des'], $rel['err_code']);
        }

        $info = $this->rechargeLogs->findBy('out_trade_no', $rel['out_trade_no'], $this->rechargeLogs->model->fields);

        $this->rechargeSucc($info, $rel);
    }
    /**
     * 支付成功后同步通知地址
     */
    public function ReturnUrl($type)
    {
        $this->insertTest('return');
        $input = request()->input();

        return $this->tests->create(['content'=>json_encode($input)]);
    }
    /**
     * 查询是否支付成功
     */
    public function FindSucc()
    {
        $sess = $this->loginGetSession();
        $id = request()->input('id');
        $out_trade_no = request()->input('out_trade_no');
        if ($id) {
            $info = $this->rechargeLogs->find($id, $this->rechargeLogs->model->fields);
        } elseif($out_trade_no) {
            $info = $this->rechargeLogs->findBy('out_trade_no', $out_trade_no, $this->rechargeLogs->model->fields);
        } else {
            $info = $this->rechargeLogs->model
                ->where('user_id', $sess['id'])
                ->select($this->rechargeLogs->model->fields)
                ->orderBy('id', 'desc')
                ->first();
        }

        if (!$info || !$sess) {
            throw new \Exception('数据异常！', 2000);
        }

        if ($info['user_id']!=$sess['id']) {
            throw new \Exception('用户异常，不是该用户充值记录！', 2000);
        }

        if ($info['status'] == 1) {
            $user = $this->loginGetSession(true);
            return $this->result(['balance'=>$user['balance']]);
        }

        if ($info['status'] == 0) {
            try {
                return $this->doFind($info); // 获取查询结果
            } catch (\Exception $e) {
                // 没有支付成功；添加推送提示任务
                $this->pushNoRechargeMsg($info['user_id'], $info['view_cid']);

                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        throw new \Exception('充值记录状态异常！', 2001);
    }
    /**
     * 执行查询
     * @param array $info RechargeLogs
     */
    private function doFind($info, $re = 0)
    {
        $order = [
            'out_trade_no'  => $info['out_trade_no'],
        ];

        if ($info['type'] == 1) {
            $this->initPay();
            $rel = $this->PayObj->find($order);
        } else {
            ###需示例支付宝 $this->PayObj 对象
            $rel = $this->PayObj->find($order);
        }

        if ($rel['return_code'] != 'SUCCESS') {
            if ($re < 2) {
                return $this->doFind($info, ++$re);
            }
            throw new \Exception($rel['return_msg'], 4000);
        }

        $this->insertTest('find', $rel);

        if ($rel['result_code'] != 'SUCCESS') {
            throw new \Exception($rel['err_code_des'], $rel['err_code']);
        }
        if ($rel['trade_state'] != 'SUCCESS') {
            throw new \Exception($this->tradeStateDesc($rel['trade_state']), 4000);
        }

        return $this->rechargeSucc($info, $rel);
    }
    /**
     * 设置支付成功信息
     * @param array $rel 查询的支付结果
     * @param obj $log 充值记录日志
     */
    private function rechargeSucc($log, $rel)
    {
        $key = 'order-' . $log['out_trade_no'];
        if ($log['status'] != 0 || !$this->setLock($key)) {
            // 如果订单状态异常 或者 加锁失败；就直接返回
            return $this->result([], 0, '该充值订单已更新数据！');
        }
        //$this->users->PutTouInfo(2,38857);
        // 更新充值订单信息和悬赏主信息
        try
        {
            DB::beginTransaction();

            // 设置充值记录为已支付
            $log = $this->rechargeLogs->toArr($log);
            $log['status']      = 1;
            $log['payment_no']  = $rel['transaction_id'];
            $log['pay_time']    = $rel['time_end'];
            $this->rechargeLogs->update($log, $log['id']);
            $this->rechargeLogs->ClearCache($log['user_id']);// 清除用户充值记录缓存
            //更新支付号今天的支付金额
            $info = ($this->payWechats->model)->where('id', $log['pay_wechat_id'])->select($this->payWechats->model->fields)->first();
            if($info){
                //$this->payWechats->updateByMap(['money_today'=>$info['money_today']+$log['money']],$log['pay_wechat_id'],['mch_id'=>$info['mch_id']]);
                $day_pay = ($this->payWechats->model)->where('mch_id', $info['mch_id'])->select($this->payWechats->model->fields)->orderBy('money_today','desc')->first();
                $this->payWechats->updateByMap(['money_today'=>$day_pay['money_today']+$log['money']],['mch_id'=>$info['mch_id']]);
            }
            // 更新用户信息
            $user = $this->users->UserCacheInfo($log['user_id']);
            $money_btn = $this->moneyBtns->find($log['money_btn_id'], $this->moneyBtns->model->fields);
            if ($money_btn['default'] == 9) {
                if ($money_btn['price'] == 36500) {
                    $user_data['vip_end_at'] = strtotime(date("Y-m-d",strtotime("+1 year"))); // 设置用户VIP截止时间
                } else {
                    $user_data['vip_end_at'] = strtotime(date("Y-m-d",strtotime("+3 month"))); // 设置用户VIP截止时间
                }
                if ($user['vip_end_at'] - time() > 0) {
                    $user_data['vip_end_at'] += $user['vip_end_at'] - time();
                }
                if ($money_btn['coin'] > 0) $user_data['balance'] = '+'.$money_btn['coin'];// 增加用户书币
            } else {
                $user_data['balance']    = '+'.$money_btn['coin'];// 增加用户书币
            }
            $user_data['recharge_money'] = $user['recharge_money'] + $log['money'];
            $this->users->UpdateFromDB($user['id'], $user_data); // 更新用户余额

            // 增加积分日志
            $coinLogData = [
                'user_id'   => $user['id'],
                'type'      => $this->coinLogs->getType('recharge'),
                'type_id'   => $log['id'],
                'coin'      => $log['coin'],
                'balance'   => $user['balance'] + $log['coin'] ,
                'title'     => '用户充值',
                'desc'      => '用户充值，获得' . $log['coin'] . '书币！',
                'status'    => 1,
                'customer_id'   => $user['customer_id'],
                'platform_wechat_id'   => $user['platform_wechat_id'],
            ];
            $this->coinLogs->create($coinLogData);
            $this->coinLogs->ClearCache($user['id']);

            if ($log['extend_link_id'] > 0) {
                // 推广链接增加收益
                $this->extendLinks->UpdateInfo($log['extend_link_id'], ['recharge_succ'=>1, 'money'=>$log['money']]);
            }
            if ($log['wechat_qrcode_id'] > 0) {
                // 推广二维码增加支付订单信息
                $this->wechatQrcodes->IncColumns($log['wechat_qrcode_id'], ['recharge_num'=>1, 'recharge_money'=>$log['money']]);
            }
            /*//充值记录表没有extend_link_id 的时候才走这个更新推广记录
            if (!$log['view_cid'] || $log['view_cid'] == $user['customer_id']) {
                // 就是主用户进行充值
                if ($user['extend_link_id'] && $user['created_at'] + 86400 * 7 > time()) {
                    // 推广链接增加收益
                    $this->extendLinks->UpdateInfo($user['extend_link_id'], ['recharge_succ'=>1, 'money'=>$log['money']]);
                }
            } else {
                $sub_user = $this->users->SubUserCacheInfo($log['user_id'], $log['view_cid']);
                if ($sub_user && $sub_user['extend_link_id'] && $sub_user['created_at'] + 86400 * 7 > time()) {
                    // 子用户的推广链接增加收益
                    $this->extendLinks->UpdateInfo($sub_user['extend_link_id'], ['recharge_succ'=>1, 'money'=>$log['money']]);
                }
            }*/

            // 插入客户收益记录
            $customer = $this->customers->findByMap([
                ['id', $log['customer_id']],
                ['status', 1],
            ], $this->customers->model->fields);

            // 统计订单数据
            if($customer) {
                // 插入客户收益记录
                $moneyLog = [
                    'customer_id'       => $log['customer_id'],
                    'platform_wechat_id'=> $log['platform_wechat_id'],
                    'user_id'           => $log['user_id'],
                    'recharge_log_id'   => $log['id'],
                    'money'             => $log['money'],
                    'balance'           => $log['money'] + $customer['balance'],
                    'status'            => 1,
                ];
                $this->customerMoneyLogs->create($moneyLog);
                // 更新customer余额
                $customer->balance += $log['money'];
                $customer->save();

                $current_date = date('Y-m-d');
                $customer = $customer->toArray();
                $group_id = $customer['pid'] ?: $customer['id'];
                // 归属商户
                $map = [
                    'date_belongto' => $current_date,
                    'group_id'      => $group_id,
                    'customer_id'   => $customer['id'],
                ];
                $up_data = [
                    'paid_num'      => 1,
                    'unpay_num'     => -1,
                    'recharge_money'=> $log['money'],
                ];
                $this->statistics->UpdateColumnNum($map, $up_data);

                // 商户小组
                $map['customer_id'] = 0;
                $this->statistics->UpdateColumnNum($map, $up_data);
            } else {
                // 没有对应公众号账号就只统计总数据
                $customerPar = $this->customers->model->where('status', 1)->orderBy('id', 'asc')->select(['id'])->first(); // 查询总账号
                $current_date = date('Y-m-d');
                $map = [
                    'date_belongto' => $current_date,
                    'group_id'      => $customerPar['id'],
                    'customer_id'   => 0,
                ];
                $up_data = [
                    'paid_num'      => 1,
                    'unpay_num'     => -1,
                    'recharge_money'=> $log['money'],
                ];
                // 商户小组
                $this->statistics->UpdateColumnNum($map, $up_data);
            }
            DB::commit();
            (new UserService())->PutTouInfo(2,$moneyLog['user_id']);
            return $this->result(['balance' => $moneyLog['balance']], 0, '充值成功！');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            $this->users->ClearCache($log['user_id']);
            return $this->result([], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 交易最终状态描述
     */
    private function tradeStateDesc($type, $log = [])
    {
        $msgs = [
            'SUCCESS'   => '支付成功',
            'REFUND'   => '转入退款',
            'NOTPAY'   => '未支付',
            'CLOSED'   => '已关闭',
            'REVOKED'   => '已撤销（付款码支付）',
            'USERPAYING'   => '用户支付中（付款码支付）',
            'PAYERROR'   => '支付失败(其他原因，如银行返回失败)',
        ];

        if (isset($msgs[$type])) {
            return $msgs[$type];
        } else {
            return '其他原因 支付失败';
        }
    }
    /**
     * 插入test 表数据
     */
    private function insertTest($where = 'notify', $content = '')
    {
        if (!$content) {
            $xml = file_get_contents('php://input');
            $input = request()->all();

            $this->tests->create(['type' => $where.'___xml', 'content' => $xml]);
            $this->tests->create(['type' => $where.'___input', 'content' => http_build_query($input)]);
        } else {
            if (!is_string($content))  $content = http_build_query($content);
            $this->tests->create(['type' => $where.'___txt', 'content' => $content]);
        }
    }

    /**
     * 查询是否支付成功
     */
    public function OutTradeNoCheck()
    {
        $out_trade_no = request()->input('out_trade_no');
        if (!$out_trade_no) {
            throw new \Exception('参数异常2！', 2000);
        }

        $info = $this->rechargeLogs->findBy('out_trade_no', $out_trade_no, $this->rechargeLogs->model->fields);


        if (!$info) {
            throw new \Exception('info数据异常！', 2000);
        }

        if ($info['status'] == 1) {
            return $this->result([], '支付成功！', 0);
        }

        if ($info['status'] == 0) {
            try {
                return $this->doFind($info); // 获取查询结果
            } catch (\Exception $e) {
                // 没有支付成功；添加推送提示任务
                $this->pushNoRechargeMsg($info['user_id'], $info['view_cid']);
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        throw new \Exception('充值记录状态异常！', 2001);
    }
    // 未支付成功的推送提醒
    private function pushNoRechargeMsg($user_id = 0, $customer_id = 0) {
        if (!$user_id) {
            $user = $this->loginGetSession(true);
        } else {
            $user = $this->users->UserCacheInfo($user_id);
        }
        $customer_id = $customer_id ? $customer_id : ($user['view_cid'] ? $user['view_cid'] : $user['customer_id']);
        $weconf = $this->wechatConfigs->FindByCustomerID($customer_id);
        if (isset($weconf['pushconf']['nopay']) && $weconf['pushconf']['nopay']) {
            // 开启了未支付推送的才添加推送任务
            $this->RechargeMsg($user, $customer_id);
        }
    }
    // 企业付款到零钱
    public function Pay2User($openid, $money, $name, $partner_trade_no = '') {
        $this->initPay();
        $partner_trade_no = $partner_trade_no ? $partner_trade_no : (date('YmdHis') . $this->wechat['mch_id'] . RandCode(8, 1));
        $order = [
            'partner_trade_no' => $partner_trade_no,              //商户订单号
            'openid' => $openid,                        //收款人的openid
            'check_name' => 'FORCE_CHECK',            //NO_CHECK：不校验真实姓名\FORCE_CHECK：强校验真实姓名
            're_user_name'=> $name,              //check_name为 FORCE_CHECK 校验实名的时候必须提交
            'amount' => $money,                       //企业付款金额，单位为分
            'desc' => $name . ' - 提现',                  //付款说明
        ];

        $result = $this->PayObj->transfer($order);
        return [$result, $order];
    }
    // 查询企业付款到零钱是否支付成功
    /*public function FindPay2UserSucc($partner_trade_no) {
        if (!$this->PayObj) {
            $this->initPay();
        }
        $order = [
            'partner_trade_no' => $partner_trade_no,              //商户订单号
        ];
        $result = $this->PayObj->find($order, 'transfer');

        return $result;
    }*/
    //每天清空支付号的今日支付金额
    public function empty_money_today(){
        //开启
        DB::enableQueryLog();
        $result = $this->payWechats->model->where('money_today','>','0')->update(['money_today'=>0]);
        //打印
        dd(DB::getQueryLog());
    }
}