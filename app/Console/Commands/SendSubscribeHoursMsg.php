<?php
/**
 * 关注后几小时发送推荐阅读信息给用户
 */

namespace App\Console\Commands;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\WechatRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Console\Command;

class SendSubscribeHoursMsg extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-subscribe-hours-msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'New user subscribe xxx hours send recommend novels info.';

    protected $users;
    protected $wechatConfigs;
    protected $domains;
    protected $nvwechats;

    protected $pageRows = 100;
    protected $now;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->users            = new UserRepository();
        $this->wechatConfigs    = new WechatConfigRepository();
        $this->domains          = new DomainRepository();
        $this->nvwechats        = new WechatRepository();


        $this->now = time();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $nvwechats = $this->nvwechats->allByMap([['status', 1]], ['id']);
        $nvw_ids = $this->nvwechats->toPluck($nvwechats, 'id');
        $configs = $this->wechatConfigs->model->whereIn('platform_wechat_id', $nvw_ids)->select(['id', 'customer_id', 'platform_wechat_id', 'pushconf', 'subscribe_msg_12h'])->get();
        //$configs = $this->wechatConfigs->model->select(['id', 'customer_id', 'platform_wechat_id', 'pushconf', 'subscribe_msg_12h'])->get();

        $this->info($this->signature . ' start ' . date('Y-m-d H:i:s'));
        foreach ($configs as $k=>$config) {
            // {"day_read":"1","readed8h":0,"first_recharge":0,"sign":0,"nopay":0}
            $config['pushconf'] = json_decode($config['pushconf'], 1);
            if (!$config['pushconf'] || !isset($config['pushconf']['subs12h']) || !$config['pushconf']['subs12h'] || !$config['subscribe_msg_12h']) {
                // 没有开启或者没有配置小说；就不发送
                continue;
            }
            // 检测几小时推一次
            $hour = date('H', $this->now);
            if ($hour < 8 || (($hour - 8) % $config['pushconf']['subs12h']) != 0) continue;
            $this->customerUserSend($config);
        }
        $this->info($this->signature . ' over ' . date('Y-m-d H:i:s'));
    }
    /**
     * 给对应客户的用户发送消息
     * @param array $config
     */
    private function customerUserSend($config) {
        $this->wechat = null; // 重置当前公众号

        try {
            $this->initOfficialAccountTrait($config['customer_id']); // 防止公众号异常导致后续账号不能发送消息
        } catch (\Exception $e) {
            return false;
        }

        $page = 1;
        $content = $this->productMsgContend($config['subscribe_msg_12h'], $config['customer_id']);
        $hour = date('H', $this->now);
        $end = strtotime(date('Y-m-d H:0:0', $this->now));
        $start = $hour < 8 ? strtotime(date('Y-m-d', $this->now)) : ($end - 3600 * $config['pushconf']['subs12h']);

        while (true) {
            $users = $this->users->model->offset(($page - 1) * $this->pageRows)->limit($this->pageRows)
                ->where('customer_id', $config['customer_id'])
                ->where('platform_wechat_id', $config['platform_wechat_id'])
                ->where('subscribe', 1)
                ->whereBetween('created_at', [$start, $end])
                ->select(['id', 'name', 'openid'])
                ->get();
            if (!count($users)) break;

            foreach ($users as $user) {
                $content['touser'] = $user['openid'];
                $this->SendCustomMsg($config['customer_id'], $content, true);
            }
            $page++;
        }
    }
    /**
     * 返还客服消息字符串
     * @param json $msgs
     * @param int $customer_id
     */
    private function productMsgContend($msgs, $customer_id) {
        $host = $this->domains->randOne(1, $customer_id);
        //{"nid":["265","266","267","268"],"title":["\u5e1d\u5c11\u7684\u5ba0\u59bb","\u6843\u8272\u4e61\u6751","\u597d\u8272\u8273\u5987","\u900f\u89c6\u6751\u533b"]}
        //$url = $host . route("novel.tosection", ['novel_id'=>$novel_id, 'section'=>$section, 'customer_id'=>$plat_wechat['customer_id'], 'do'=>'secondpush'], false);
        $host = $host . route("novel.tosection", ['customer_id'=>$customer_id], false);

        $msgs = json_decode($msgs, 1);

        $rel = '';
        foreach ($msgs['nid'] as $k=>$v) {
            $rel .= "👉<a href='" . $host . route("jumpto", ['route'=>'section', 'section_num'=>0, 'dtype'=>2, 'novel_id'=>$v, 'section'=>0, 'customer_id'=>$customer_id], false) ."'>{$msgs['title'][$k]}</a>\r\n\r\n";
        }
        $rel .= '         ' . implode(' ', $this->emojiArr(3));

        $content = [
            'touser'    => null,
            'msgtype'   => 'text',
            'text'      => [
                'content'   => $rel,
            ],
        ];
        return $content;
    }
        
    
}
