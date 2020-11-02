<?php
namespace App\Logics\Traits;

use App\Jobs\SendCustomerMsg;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\CustomerRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\PlatformRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\RechargeLogRepository;
use App\Logics\Repositories\src\SignLogRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Services\src\OperateService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

trait PushmsgTrait
{
    /**
     * 推送关注推送的二次模板消息
     */
    protected function pushSecondMsg($sess, $novel_id, $customer_id) {
        if (!$sess['id']) {
            return false;
        }
        if ($sess['customer_id'] == $customer_id) {
            $openid = $sess['openid'];
        } else {
            $openid = $this->getUsersInPushmsgTrait()->GerRealOpenid($sess['id'], $customer_id);
        }

        $key = config('app.name') . 'push_second_msg_' . $sess['id'] . '_' . $customer_id;
        $last_time = Cache::get($key);
        if ($last_time && $last_time > (time() - 1800)) {
            return false; // 10小时内推送过了，就不推送
        }
        Cache::add($key, time(), Carbon::now()->addMinutes(600));

        $wechatConf = $this->getWechatConfigsInPushmsgTrait()->FindByCustomerID($customer_id);
        if (!$wechatConf) {
            return false;
        }
        $config = json_decode($wechatConf['subscribe_msg_next'], 1);

        // $url = '/front/#/detail/novel-258.html';
        //$url = $host . route("novel.tosection", ['novel_id'=>$novel_id, 'section'=>$section, 'customer_id'=>$plat_wechat['customer_id'], 'do'=>'secondpush'], false);

        $host = $this->getDomainsInPushmsgTrait()->randOne(1, $customer_id);
        $str = "恭喜您；获得以下图书优先阅读权！\r\n\r\n";
        if ($novel_id) {
            $str .= "👉<a href='" . $host . route("jumpto", ['route'=>'section', 'section_num'=>0, 'dtype'=>2, 'novel_id'=>$novel_id, 'section'=>0, 'customer_id'=>$customer_id, 'uid'=>$sess['id']], false) ."'>点我继续上次阅读</a>\r\n\r\n
【今日推荐】\r\n\r\n";
        }
        // 查询网站模板
        //$customer = $this->getCustomersInPushmsgTrait()->find($customer_id, ['web_tpl']);
        //$host = $host . route('jumpto', ['route'=>'novel', 'novel_id'=>$config['nid'][0], 'cid'=>$customer_id], false) '/'. $customer['web_tpl'] . '/#/detail/novel-';//258.html';
        $str .="👉<a href='{$host}" . route('jumpto', ['route'=>'novel', 'dtype'=>2, 'novel_id'=>$config['nid'][0], 'cid'=>$customer_id, 'customer_id'=>$customer_id, 'uid'=>$sess['id']], false) . "'>{$config['title'][0]}</a>\r\n\r\n
👉<a href='{$host}" . route('jumpto', ['route'=>'novel', 'dtype'=>2, 'novel_id'=>$config['nid'][1], 'cid'=>$customer_id, 'customer_id'=>$customer_id, 'uid'=>$sess['id']], false) . "'>{$config['title'][1]}</a>\r\n\r\n
👉<a href='{$host}" . route('jumpto', ['route'=>'novel', 'dtype'=>2, 'novel_id'=>$config['nid'][2], 'cid'=>$customer_id, 'customer_id'=>$customer_id, 'uid'=>$sess['id']], false) . "'>{$config['title'][2]}</a>\r\n\r\n
👉";// "{$config['bottom'][0]}，<a href='{$config['bottom'][1]}'>{$config['bottom'][2]}</a>";
        if (strpos($config['bottom'][2], '客服')) {
            $str .= $this->strEndKefuLink($host, $customer_id);
        } else {
            $str .= "{$config['bottom'][0]}，<a href='{$config['bottom'][1]}'>{$config['bottom'][2]}</a>";
        }


        $content = [
            'touser'    => $openid,
            'msgtype'   => 'text',
            'text'      => [
                'content'   => $str,
            ],
        ];
        try {
            $this->SendCustomMsg($customer_id, $content, true);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * 签到 关键字字段执行签到并回复签到成功
     */
    protected function signReply($message) {
        $sess = $this->getUsersInPushmsgTrait()->findBy('openid', $message['FromUserName'], $this->getUsersInPushmsgTrait()->model->fields);
        $user_id = $sess['first_account'] > 0 ? $sess['first_account'] : $sess['id'];
        $info = $this->getSignLogsInPushmsgTrait()->LastSignInfo($user_id);
        if ($info['signed']) {
            return '今天您已签到成功！';
        }
        $operateSer = new OperateService();
        return $operateSer->InserSignData($info, $sess);
        // return '您已成功签到，获得'.$sign['last_coin'].'书币！请明天继续签到！';
    }
    /**
     * 充值失败提醒
     */
    protected function RechargeMsg($user, $customer_id = 0) {
        if (!$customer_id) {
            $customer_id = $user['customer_id'];
        }
        $key = config('app.name') . 'recharge_msg_' . $user['id'] . '_' . $customer_id;
        $last_time = Cache::get($key);
        if ($last_time && $last_time > (time() - 1800)) {
            return false; // 半小时内推送过了，就不推送
        }
        Cache::add($key, time(), Carbon::now()->addMinutes(30));

        if ($user['customer_id'] == $customer_id) {
            $openid = $user['openid'];
        } else {
            $openid = $this->getUsersInPushmsgTrait()->GerRealOpenid($user['id'], $customer_id);
        }

        SendCustomerMsg::dispatch('recharge-fail', $customer_id, $openid, $user['recharge_money'])->delay(120);
/* //由于集群版不支持下列方式调用queue；改写为参数调用
        $host = $this->getDomainsInPushmsgTrait()->randOne(2, $customer_id);
        if ($user['recharge_money']) {
            // 充值失败提醒
            $content = [
                'touser'    => $openid,
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => '>>> 充值失败了💔💔💔',
                            'description'   => '亲，您刚刚提交的充值订单充值失败了，点我重新充值吧！',
                            'url'           => $host . route('jumpto', ['route'=>'recharge', 'dtype'=>2, 'customer_id'=>$customer_id], false),
                            'picurl'        => 'https://novelsys.oss-cn-shenzhen.aliyuncs.com/coupon/images/49c27ed5be905cb2a2ac050ead023ec.png',
                        ],
                    ],
                ],
            ];
            //$this->SendCustomMsg($user['customer_id'], $content, true);
        } else {
            // 首充优惠提醒
            $content = [
                'touser'    => $openid,
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => '>>> 首充优惠活动🎉🎉🎉',
                            'description'   => '亲，首次充值仅需9.9元，还送您900个书币，点击前往！',
                            'url'           => $host . route('jumpto', ['route'=>'first_recharge', 'dtype'=>2, 'customer_id'=>$customer_id], false),
                            'picurl'        => 'https://novelsys.oss-cn-shenzhen.aliyuncs.com/coupon/images/f4abd987efd929090b69a0414c8f077.png',
                        ],
                    ],
                ],
            ];
            //$this->SendCustomMsg($customer_id, $content, true);
        }
        $content = json_encode($content);
        SendCustomerMsg::dispatch($customer_id, $content)->delay(120);*/
    }

    /**
     * 发送签到模板消息
     */
    private function sendSignMsg($info, $user) {
        if ($user['customer_id'] == $user['view_cid']) {
            $data['touser']  = $user['openid'];
        } else {
            $data['touser'] = $this->getUsersInPushmsgTrait()->GerRealOpenid($user['id'], $user['view_cid']);
        }
        $customer_id = isset($user['view_cid']) && $user['view_cid'] ? $user['view_cid'] : $user['customer_id'];

        $data['msgtype'] = 'text';

        $data['text']['content'] = $this->signMsgContent($customer_id, $info, $user);

        try {
            $this->SendCustomMsg($customer_id, $data, true); // 执行发送
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    // 签到回复消息的内容文本
    private function signMsgContent($customer_id, $info, $user) {
        $host = $this->getDomainsInPushmsgTrait()->randOne(2, $customer_id);
        $content = "{$user['name']}，今日签到成功，已连续签到{$info['continue_day']}天，获得{$info['coin']}书币，连续签到获得书币会翻倍哟~\r\n\r\n";
        $list = $this->getReadLogsInPushmsgTrait()->model
            ->where([['user_id', $user['id'], ['status', 1]]])
            ->select($this->getReadLogsInPushmsgTrait()->model->fields)
            ->orderBy('updated_at', 'desc')
            ->limit(3)
            ->get();
        $list = $this->getReadLogsInPushmsgTrait()->toArr($list);
        if (isset($list[0])) {
            $readLog = $list[0];
            $url = $host . route("jumpto", [
                    'route'         =>'section',
                    'section_num'   =>0,
                    'dtype'         =>2,
                    'novel_id'      =>$readLog['novel_id'],
                    'section'       =>$readLog['end_section_num'],
                    'customer_id'   =>$customer_id,
                    'uid'           =>$user['id']
                ], false);
            $content .= "<a href='{$url}'>【点此继续阅读】</a>\r\n\r\n ";
            unset($list[0]);
        }
        if (isset($list[1])) {
            $content .= "历史阅读记录： \r\n\r\n";
            foreach ($list as $readLog) {
                $url = $host . route("jumpto", [
                        'route'         =>'section',
                        'section_num'   =>0,
                        'dtype'         =>2,
                        'novel_id'      =>$readLog['novel_id'],
                        'section'       =>$readLog['end_section_num'],
                        'customer_id'   =>$customer_id
                    ], false);
                $content .= "<a href='{$url}'> {$readLog['name']}</a>\r\n\r\n";
            }
        }
        //$content .= "为了方便下次阅读，请<a href='{$host}/img/wechat.totop.png'> 置顶 公众号</a>";
        $content .= $this->strEndKefuLink($host, $customer_id);
        return $content;
    }

    // 添加客服或者置顶公众号
    protected function strEndKefuLink($host, $customer_id) {
        // $str = "为方便下次阅读，请<a href='{$this->host}/img/wechat.totop.png'>置顶 公众号</a>\r\n\r\n";
        $customer_link = $this->getCommonSetsInPushmsgTrait()->values('service', 'customer_link');
        if (!empty($customer_link)) {
            $url = $customer_link;
        } else {
            $url = $host . route("jumpto", [
                    'route' => 'contact',
                    'cid' => $customer_id,
                    'customer_id' => $customer_id,
                    'dtype' => 2,
                    'jumpto' => 'index',
                ], false);
        }
        $str = "需要帮助！添加 ，<a href='{$url}'> 人工客服</a>";
        return $str;
    }


    public function getSignLogsInPushmsgTrait() {
        if (!isset($this->signLogs) || !$this->signLogs) {
            $this->signLogs = new SignLogRepository();
        }
        return $this->signLogs;
    }
    public function getPlatformWechatsInPushmsgTrait() {
        if (!isset($this->platformWechats) || !$this->platformWechats) {
            $this->platformWechats = new PlatformWechatRepository();
        }
        return $this->platformWechats;
    }
    public function getPlatformsInPushmsgTrait() {
        if (!isset($this->platforms) || !$this->platforms) {
            $this->platforms = new PlatformRepository();
        }
        return $this->platforms;
    }
    public function getUsersInPushmsgTrait() {
        if (!isset($this->users) || !$this->users) {
            $this->users = new UserRepository();
        }
        return $this->users;
    }
    public function getWechatConfigsInPushmsgTrait() {
        if (!isset($this->wechatConfigs) || !$this->wechatConfigs) {
            $this->wechatConfigs = new WechatConfigRepository();
        }
        return $this->wechatConfigs;
    }
    public function getDomainsInPushmsgTrait() {
        if (!isset($this->domains) || !$this->domains) {
            $this->domains = new DomainRepository();
        }
        return $this->domains;
    }
    public function getCustomersInPushmsgTrait() {
        if (!isset($this->customers) || !$this->customers) {
            $this->customers = new CustomerRepository();
        }
        return $this->customers;
    }
    public function getRechargeLogsInPushmsgTrait() {
        if (!isset($this->rechargeLogs) || !$this->rechargeLogs) {
            $this->rechargeLogs = new RechargeLogRepository();
        }
        return $this->rechargeLogs;
    }
    public function getReadLogsInPushmsgTrait() {
        if (!isset($this->readLogs) || !$this->readLogs) {
            $this->readLogs = new ReadLogRepository();
        }
        return $this->readLogs;
    }
    public function getCommonSetsInPushmsgTrait() {
        if (!isset($this->commonSets) || !$this->commonSets) {
            $this->commonSets = new CommonSetRepository();
        }
        return $this->commonSets;
    }
}
