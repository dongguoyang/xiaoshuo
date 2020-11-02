<?php

namespace App\Logics\Services\src;

use App\Jobs\OrderNoPay;
use App\Logics\Repositories\src\CoinLogRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\CustomerMoneyLogRepository;
use App\Logics\Repositories\src\CustomerRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\MoneyBtnRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\RechargeLogRepository;
use App\Logics\Repositories\src\TestRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\WechatQrcodeRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\PushmsgTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Support\Facades\DB;
use Yansongda\Pay\Log;
use Yansongda\Pay\Pay;

class PaymentService extends Service {
    use PushmsgTrait;

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
		];
	}

	private function initPay($type = 'wechat') {
	    if ($type == 'wechat') {
	        // 微信配置
            $config = [
                'app_id'    => $this->wechat['appid'],  // env('WECHAT_APP_ID', 'wx650c518f5af2060e'),// 公众号 APPID
                'miniapp_id'=> env('WECHAT_MINIAPP_ID', ''),    // 小程序 APPID
                'appid'     => env('WECHAT_APPID', ''), // APP 引用的 appid
                'mch_id'    => $this->wechat['mch_id'], // env('WECHAT_MCH_ID', '1501308161'),// 微信支付分配的微信商户号
                'notify_url'=> route('payment_notify', ['type' => 'wechat'], true), // 微信支付异步通知地址
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
            $this->PayObj = Pay::wechat($config);
        } else {
	        // 支付宝配置
            $config = [
                'app_id' => env('ALI_APP_ID', ''),// 支付宝分配的 APPID
                'notify_url' => '',// 支付宝异步通知地址
                'return_url' => '',// 支付成功后同步通知地址
                'ali_public_key' => env('ALI_PUBLIC_KEY', ''),// 阿里公共密钥，验证签名时使用
                'private_key' => env('ALI_PRIVATE_KEY', ''),// 自己的私钥，签名时使用
                'log' => [// optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.pay.log'
                    'file' => storage_path('logs/alipay.log'),
                    'level' => 'debug',
                    'type' => 'daily', // optional, 可选 daily.  single
                    'max_file' => 30,
                ],
                // optional，设置此参数，将进入沙箱模式
                // 'mode' => 'dev',
            ];
            $this->PayObj = Pay::alipay($config);
        }

	    return $this->PayObj;
    }
    /**
     * 获取充值金额按钮列表
     */
    public function MoneyBtns() {
        $type = request()->input('type');
        switch ($type) {
            case 'vip':
                $list = $this->moneyBtns->allByMap([['status', 1], ['default',9]], $this->moneyBtns->model->fields);
                break;
            case 'first':
                $list = $this->moneyBtns->findByMap([['status', 1], ['default',8]], $this->moneyBtns->model->fields);
                break;
            default:
                $list = $this->moneyBtns->allByMap([['status', 1], ['default', '<', 5]], $this->moneyBtns->model->fields);
                break;
        }

        $rel['btns'] = $list;
        //$rel['pay_domain'] = $this->commonSets->values('payment', 'authurl'); // 支付页面的安全域名
        $wechat = $this->payWechats->initWechat(1, true);
        $rel['pay_domain'] = $wechat['redirect_uri'] . route('paymen.login2unifiedorder', [], false); // 支付页面的安全域名+地址

        return $this->result($rel);
    }

    /**
     * H5 微信统一下单
     */
    public function ToPay($type = 'wechat') {
        $order = $this->assembleOrder($type); // 组合订单信息
        $data['out_trade_no'] = $order['out_trade_no'];

        if ($type == 'wechat') {
            if (isWechatBrowser()) {
                // 公众号支付
                $ordered = $this->PayObj->mp($order);
                if (!isset($ordered['appId']) || !isset($ordered['paySign']) || !$ordered['paySign'] || !isset($ordered['package']) || !$ordered['package']) {
                    throw new \Exception('统一下单发生异常！', 2000);
                }
                $data['order'] = $ordered;
                return $this->result($data);
            } else {
                // H5 支付
                return $this->PayObj->wap($order);
            }
        } else {
            return $this->PayObj->wap($order);
        }
    }
    /**
     * 组合订单信息
     */
    private function assembleOrder($type = 'wechat')
    {
        $money_btn_id = request()->input('money_btn_id');
        $money_btn = $this->moneyBtns->find($money_btn_id, $this->moneyBtns->model->fields);

        if (!$money_btn) {
            throw new \Exception('金额异常！', 2000);
        }

        $order['out_trade_no'] = date('YmdHis') . RandCode(15, 12);

        $user = $this->loginGetSession(true);
        if ($user['id']) {
            if ($money_btn['default'] == 9) {
                $desc = '开通年费VIP';
            } else {
                $desc = '充值书币';
            }
            $desc .= '|' . $money_btn['desc'];
            // $desc = $user['name'] . ' 充值 '. ($money_btn['price']/100) . ' 元';
        } else {
            $desc = '未登录支付 充值';
            throw new \Exception('用户未登录；请登录后执行操作！', 800);
        }
        if ($type == 'wechat') {
            $this->initWechatInfo('platform_wechat_id', $user['platform_wechat_id'])->initPay($type);
            // 微信订单信息
            if (isWechatBrowser()) {
                $order['openid'] = $user['openid'];
            }
            $order['body'] = $desc;
            $order['total_fee'] = $money_btn['price'].'';
        } else {
            ###需示例支付宝 $this->PayObj 对象
            // 支付宝订单信息
            $order['subject'] = $desc;
            $order['total_amount'] = $money_btn['price'];
        }

        $this->addRechargeLog($order, $user, $money_btn); // 添加充值记录

        return $order;
    }

    /**
     * 插入充值记录
     * @param array $order 订单信息
     */
    private function addRechargeLog($order, $user, $money_btn)
    {
        $data = [
            'platform_wechat_id'=> $user['platform_wechat_id'],
            'customer_id'       => $user['customer_id'],
            'money_btn_id'      => $money_btn['id'],
            'user_id'       => $user['id'],
            'money'         => isset($order['total_fee']) ? $order['total_fee'] : $order['total_amount'],
            'coin'          => ($money_btn['default'] == 9 ? 365 : $money_btn['coin']),
            'balance'       => $user['balance'] + $money_btn['coin'],
            'out_trade_no'  => $order['out_trade_no'],
            'payment_no'    => '',
            'type'          => isset($order['total_fee']) ? 1 : 2,
            'desc'          => isset($order['body']) ? $order['body'] : $order['subject'],
        ];

        $rel = $this->rechargeLogs->create($data);
        $this->rechargeLogs->ClearCache($user['id']); // 清除用户充值记录缓存

        if ($user['extend_link_id'] && $user['created_at'] + $this->extendLinks->validTime > time()) {
            // 推广链接增加订单
            $this->extendLinks->UpdateInfo($user['extend_link_id'], ['recharge'=>1]);
        }
        if ($user['wechat_qrcode_id'] && $user['created_at'] + $this->wechatQrcodes->validTime > time()) {
            // 推广二维码增加订单
            $this->wechatQrcodes->IncColumns($user['wechat_qrcode_id'], ['order_num'=>1, 'order_money'=>$data['money']]);
        }
        return $rel;
    }

    /**
     * 支付异步通知地址
     */
    public function NotifyUrl($type)
    {
        $this->insertTest('notify');
        $input = request()->input();

        if ($type == 'wechat') {
            $this->initWechatInfo('notify')->initPay($type);
            $data = $this->PayObj->verify(); // 验证签名
            Log::debug('Wechat notify', $data->all());

            $this->wechatNotify(); // 微信支付异步通知结果处理

            return $this->PayObj->success();
        } else {
            ###需示例支付宝 $this->PayObj 对象
            $data = $this->PayObj->verify(); // 验证签名
            Log::debug('Alipay notify', $data->all());

            return $this->PayObj->success();
        }
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
                $user = $this->loginGetSession(true);
                $weconf = $this->wechatConfigs->FindByCustomer2PlatWechat($user['customer_id'], $user['platform_wechat_id']);
                if (isset($weconf['pushconf']['nopay']) && $weconf['pushconf']['nopay']) {
                    // 开启了未支付推送的才添加推送任务
                    $this->RechargeMsg($user);
                }
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
            $this->initWechatInfo('platform_wechat_id', $info['platform_wechat_id'])->initPay('wechat');
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
        if ($log['status'] != 0) {
            return $this->result([], 0, '该充值订单已更新数据！');
        }

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

            // 更新用户信息
            $user = $this->users->UserCacheInfo($log['user_id']);
            $money_btn = $this->moneyBtns->find($log['money_btn_id'], $this->moneyBtns->model->fields);
            if ($money_btn['default'] == 9) {
                $user_data['vip_end_at'] = strtotime(date("Y-m-d",strtotime("+1 year"))); // 设置用户VIP截止时间
                if ($money_btn['coin'] > 0) $user_data['balance'] = '+'.$money_btn['coin'];// 增加用户书币
            } else {
                $user_data['balance']    = '+'.$money_btn['coin'];// 增加用户书币
            }
            $user_data['recharge_money'] = $user['recharge_money'] + $log['money'];
            $this->users->UpdateFromDB($user['id'], $user_data); // 更新悬赏主余额

            // 插入客户收益记录
            $customer = $this->customers->find($log['customer_id'], $this->customers->model->fields);
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

            if ($user['extend_link_id'] && $user['created_at'] + $this->extendLinks->validTime > time()) {
                // 推广链接增加收益
                $this->extendLinks->UpdateInfo($user['extend_link_id'], ['recharge_succ'=>1, 'money'=>$log['money']]);
            }
            if ($user['wechat_qrcode_id'] && $user['created_at'] + $this->wechatQrcodes->validTime > time()) {
                // 推广二维码增加订单
                $this->wechatQrcodes->IncColumns($user['wechat_qrcode_id'], ['recharge_num'=>1, 'recharge_money'=>$log['money']]);
            }

            DB::commit();
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
     * 查询用户信息和微信信息
     * @param string $type notify   customer_id     platform_wechat_id
     * @param int $platform_wechat_id
     */
    private function initWechatInfo($type, $val = '') {
        $fields = ['mch_id', 'mch_secret', 'mch_pem_dir', 'customer_id', 'id', 'component_appid', 'appid', 'app_secret',];
        switch ($type) {
            case 'notify':
                $xml = file_get_contents('php://input');
                $rel = XmlToArray($xml);
                $wechat = $this->platformWechats->findBy('appid', $rel['appid'], $fields);
                break;
            case 'platform_wechat_id':
                $wechat = $this->platformWechats->find($val, $fields);
                break;
            case 'customer_id':
                $wechat = $this->platformWechats->findBy('customer_id', $val, $fields);
                break;
            default:
                throw new \Exception('查询公众号信息的类型异常！', 2000);
        }
        if (!$wechat) {
            throw new \Exception('公众号异常！没有找到对应公众号！', 2000);
        }
        $this->wechat = $this->platformWechats->toArr($wechat);
        return $this;
    }


    /**
     * 查询是否支付成功
     */
    public function OutTradeNoCheck()
    {
        $out_trade_no = request()->input('out_trade_no');
        if (!$out_trade_no) {
            throw new \Exception('参数异常3！', 2000);
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
                $weconf = $this->wechatConfigs->FindByCustomer2PlatWechat($info['customer_id'], $info['platform_wechat_id']);
                if (isset($weconf['pushconf']['nopay']) && $weconf['pushconf']['nopay']) {
                    // 开启了未支付推送的才添加推送任务
                    $user = $this->users->UserCacheInfo($info['user_id']);
                    $this->RechargeMsg($user);
                }
                throw new \Exception($e->getMessage(), $e->getCode());
            }
        }

        throw new \Exception('充值记录状态异常！', 2001);
    }

}