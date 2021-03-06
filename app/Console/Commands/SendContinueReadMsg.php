<?php
/**
 * 定时推送
 * 继续阅读提醒
 */
namespace App\Console\Commands;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\UserPreregRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\WechatRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class SendContinueReadMsg extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-continue-read-msg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '用户阅读后 xxx 小时没有继续阅读的提醒.';

    protected $users;
    protected $nvwechats;
    protected $wechatConfigs;
    protected $domains;
    protected $extendLinks;
    protected $readLogs;

    protected $pageRows = 1000;
    protected $now;
    protected $host;
    protected $images;
    protected $today_end_sec; // 今日结束还有多长时间
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
        $this->extendLinks      = new ExtendLinkRepository();
        $this->readLogs         = new ReadLogRepository();
        $this->nvwechats        = new WechatRepository();

        $this->now = time();
        $this->today_end_sec = strtotime(date('Y-m-d', ($this->now + 86400))) - $this->now;
        $exinfo = $this->extendLinks->ExtendPageInfos();
        $this->images = $exinfo['banners'];
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
            
            if (!$config['pushconf'] || !isset($config['pushconf']['day_read']) || !$config['pushconf']['day_read']) {
                // 没有开启；就不发送
                continue;
            }

            // 检测几小时推一次
            $hour = date('H');
            if ($hour > 22 || $hour < 9 || (($hour-9) % $config['pushconf']['day_read']) != 0) continue;
            $this->customerUserSend($config, $config['pushconf']['day_read']);
        }
        $this->info($this->signature . ' over ' . date('Y-m-d H:i:s'));
    }
    /**
     * 给对应客户的用户发送消息
     * @param array $config
     */
    private function customerUserSend($config, $sendH) {
        $page = 1;
        $hour = date('H');
        $end = strtotime(date('Y-m-d H:0:0', $this->now));
        $start = $hour - 9 < $sendH ? (strtotime('-1 day', strtotime(date('Y-m-d 22:00:00', $this->now)))) : ($end - 3600 * $sendH);
        $updated_at = strtotime(date('Y-m-d', $this->now)) - 86400;
        $this->host = $this->domains->randOne(1, $config['customer_id']);

        while (true) {
            // 获取用户
            $map = [
                ['customer_id', $config['customer_id']],
                ['platform_wechat_id', $config['platform_wechat_id']],
                ['subscribe', 1],
                ['first_account', 0],
                ['updated_at', '>', $updated_at],
            ];
            $users = $this->users->model
                ->where($map)
                ->orderBy('id')
                ->offset(($page - 1) * $this->pageRows)->limit($this->pageRows)
                ->select(['id', 'openid'])
                ->get();
            if (!count($users)) break;
            $user_ids = $users->pluck('id')->toArray();
            $open_ids = $users->pluck('openid', 'id');
            $list = array_chunk($user_ids, 200);

            foreach ($list as $ids) {
                $logs = $this->readLogs->model
                    ->whereIn('user_id', $ids)
                    ->whereBetween('updated_at', [$start, $end])
                    ->select(['end_section_num', 'novel_id', 'name', 'title', 'user_id', 'view_cid', 'customer_id'])
                    ->orderBy('updated_at', 'desc')
                    ->get();
                $logs = $this->guileiViewCid($logs); // 按view_cid 进行归类排序

                $pushed_ids = [];
                foreach ($logs as $log) {
                    if (isset($pushed_ids[$log['user_id'] . '_'. $log['view_cid']]) ||
                        !Cache::add('continue-read-msg-'.$log['user_id'].'-'.$log['view_cid'], 1, $this->today_end_sec)) {
                        continue; // 表示该用户在该公众号已经推送过了
                    }

                    if ($log['view_cid'] == $log['customer_id']) {
                        // 用户就是在归属账号下
                        $log['openid'] = $open_ids[$log['user_id']];
                    } else {
                        // 从其他账号进入的系统阅读
                        $log['openid'] = $this->users->GerRealOpenid($log['user_id'], $log['view_cid']);
                        if (!$log['openid']) continue;
                    }
                    $content = $this->productMsgContend($log, $log['view_cid']);
                    $pushed_ids[$log['user_id'] . '_'. $log['view_cid']] = 1; // 表示该用户在该公众号已经推送过了
                    // 客户ID变了；就变更推送的公众号
                    if (isset($this->wechat) && $this->wechat['customer_id'] != $log['view_cid']) $this->wechat = null;
                    if (!isset($this->wechat) || !$this->wechat) {
                        try {
                            $this->initOfficialAccountTrait($log['view_cid']); // 防止公众号异常导致后续账号不能发送消息
                        } catch (\Exception $e) {
                            break;
                        }
                    }

                    $this->SendCustomMsg($log['view_cid'], $content, true);
                }
            }
            $page++;
        }
    }
    // 将列表按照view_cid 进行排序；防止多次实例化推送公众号
    private function guileiViewCid($list) {
        $data = $rel = [];
        $list = $this->readLogs->toArr($list);
        foreach ($list as $v) {
            if (isset($data[$v['view_cid']])) {
                $data[$v['view_cid']][] = $v;
            } else {
                $data[$v['view_cid']][0] = $v;
            }
        }
        foreach ($data as $varr) {
            $rel = array_merge($rel, $varr);
        }
        return $rel;
    }
    /**
     * 给对应客户的用户发送消息
     * @param array $config
     *
     * 上一版的用户属于哪个customer_id 就用哪个公众号推送
     */
    private function customerUserSend0($config, $sendH) {
        $page = 1;
        $hour = date('H');
        $end = strtotime(date('Y-m-d H:0:0', $this->now));
        $start = $hour - 13 < $sendH ? (strtotime('-1 day', strtotime(date('Y-m-d 22:00:00', $this->now)))) : ($end - 3600 * $sendH);
        $updated_at = strtotime('Y-m-d', $this->now) - 86400;
        $this->host = $this->domains->randOne(1, $config['customer_id']);

        while (true) {
            // 获取用户
            $users = $this->users->model
                ->where('customer_id', $config['customer_id'])
                ->where('platform_wechat_id', $config['platform_wechat_id'])
                ->where('subscribe', 1)
                ->where('updated_at', '>', $updated_at)
                ->orderBy('id')
                ->offset(($page - 1) * $this->pageRows)->limit($this->pageRows)
                ->select(['id', 'openid'])
                ->get();
            if (!count($users)) break;
            $user_ids = $users->pluck('id')->toArray();
            $open_ids = $users->pluck('openid', 'id');
            $list = array_chunk($user_ids, 200);

            foreach ($list as $ids) {
                $logs = $this->readLogs->model
                    ->whereIn('user_id', $ids)
                    ->whereBetween('updated_at', [$start, $end])
                    ->select(['end_section_num', 'novel_id', 'name', 'title', 'user_id'])
                    ->orderBy('updated_at', 'desc')
                    ->get();

                foreach ($logs as $log) {
                    if (!isset($open_ids[$log['user_id']])) continue;

                    $log['openid'] = $open_ids[$log['user_id']];
                    $content = $this->productMsgContend($log, $config['customer_id']);
                    $this->SendCustomMsg($config['customer_id'], $content, true);
                    unset($open_ids[$log['user_id']]);
                }
            }
            $page++;
        }
    }
    /**
     * 返还客服消息字符串
     * @param json $msgs
     * @param int $customer_id
     */
    private function productMsgContend($info, $customer_id) {
        $content = [
            'touser'    => $info['openid'],
            'msgtype'   => 'news',
            'news'      => [
                'articles'  =>  [
                    [
                        'title'         => '>>> 点击继续阅读',
                        'description'   => '您上次阅读第 '. $info['end_section_num'] .' 章，点我继续阅读',
                        'url'           => $this->host . route("jumpto", ['route'=>'section',
                                                                            'uid'   => $info['user_id'],
                                                                            'dtype' =>2,
                                                                            'section_num'=>$info['end_section_num'],
                                                                            'novel_id'=>$info['novel_id'],
                                                                            //'section'=>$info['end_section_num'],
                                                                            'customer_id'=>$customer_id
                                                                        ], false),
                        'picurl'        => config('app.url') . $this->images[array_rand($this->images)],
                    ],
                ],
            ],
        ];
        return $content;
    }

}
