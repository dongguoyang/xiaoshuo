<?php
/**
 * 每日推送信息
 * 6点；12点；18点；21点；23点
 */
namespace App\Console\Commands;

use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\MoneyBtnRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\StorageImgRepository;
use App\Logics\Repositories\src\StorageTitleRepository;
use App\Logics\Repositories\src\UserPreregRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\WechatRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SendDailyMsg extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-daily-msg {--type= : 推送哪一个类型}';

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
    protected $storageTitles;
    protected $storageImgs;
    protected $novels;
    protected $commonSets;

    protected $abnv_num = 7;
    protected $pageRows = 300;
    protected $now;
    protected $host;
    protected $images;
    protected $type;    // 推送类型
    protected $rmaxNov; // 阅读量最高的小说
    protected $rechargeMoney;   // 优惠活动信息

    private $regirstExtend = true; // 21点推送推广链接的小说
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
        $this->storageTitles    = new StorageTitleRepository();
        $this->storageImgs      = new StorageImgRepository();
        $this->novels           = new NovelRepository();
        $this->moneyBtns        = new MoneyBtnRepository();
        $this->commonSets       = new CommonSetRepository();

        $this->now = time();
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
        $this->type = $this->option('type');

        $nvwechats = $this->nvwechats->allByMap([['status', 1]], ['id']);
        $nvw_ids = $this->nvwechats->toPluck($nvwechats, 'id');
        $configs = $this->wechatConfigs->model->whereIn('platform_wechat_id', $nvw_ids)->select(['id', 'customer_id', 'platform_wechat_id', 'daily_push', 'subscribe_msg_12h'])->get();
        $this->info($this->signature . ' start ' . date('Y-m-d H:i:s'));
        foreach ($configs as $k=>$config) {
            if ($this->type == 'h88') {
                $this->customerUserSend($config);
                continue;
            }
            $config['daily_push'] = json_decode($config['daily_push'], 1);
            
            if (!$config['daily_push'] || !isset($config['daily_push'][$this->type]) || !$config['daily_push'][$this->type]) {
                // 没有开启；就不发送
                continue;
            }
            // 检测几小时推一次
            $this->customerUserSend($config);
        }
        $this->info($this->signature . ' over ' . date('Y-m-d H:i:s'));
    }
    /**
     * 给对应客户的用户发送消息
     * @param array $config
     */
    private function customerUserSend($config) {
        $this->wechat = null;

        try {
            $this->initOfficialAccountTrait($config['customer_id']); // 防止公众号异常导致后续账号不能发送消息
        } catch (\Exception $e) {
            return false;
        }

        $page = 1;
        $updated_at = strtotime(date('Y-m-d', $this->now)) - 172800 ;
        $this->host = $this->domains->randOne(1, $config['customer_id']);
        while (true) {
            // 获取用户
            $map = [
                ['customer_id', $config['customer_id']],
                ['platform_wechat_id', $config['platform_wechat_id']],
                ['subscribe', 1],
                ['updated_at', '>', $updated_at],
            ];
            if ($this->type == 'h21' && $this->regirstExtend) {
                // 9点推送用户注册的推广链接小说
                $map[] = ['created_at', '>', ($this->now - 5 * 86400)];
                $map[] = ['extend_link_id', '>', 0];
            }
            $users = $this->users->model
                ->where($map)
                ->orderBy('id')
                ->offset(($page - 1) * $this->pageRows)->limit($this->pageRows)
                ->select(['id', 'openid', 'name', 'first_account', 'extend_link_id'])
                ->get();
            if (!count($users)) break;
            foreach ($users as $k => $user) {
                if (($k % 100) === 0) {
                    // 推送100个用户就随机换一个域名
                    $this->host = $this->domains->randOne(1, $config['customer_id']);
                }
                $content = $this->productMsgContend($user, $config);
                if (!$content) continue;
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
    private function productMsgContend($user, $config) {
        $user_id = $user['first_account'] > 0 ? $user['first_account'] : $user['id'];
        if (in_array($this->type, ['h21', 'h6', 'h88'])) { // h88 是自己添加的；手动执行的推送任务
            switch ($this->type) {
                case 'h88':
                    // 推送推广链接的小说
                    $text = $this->h88PushContent($user, $config, $user_id);
                    if (!$text) return $text;
                    break;
                case 'h21':
                    if ($this->regirstExtend) {
                        // 推送推广链接的小说
                        $text = $this->regirstExtendNovel($user, $config, $user_id);
                        if (!$text) return $text;
                    }
                    // 推送自定义小说信息
                    $text = "✈您的私人惊喜已送到，请注意查收\r\n\r\n";
                    $text .= "       🌱详情留意下方蓝色字🌱\r\n\r\n";
                    $url  = $this->host . route("jumpto", ['route'=>'read_log', 'customer_id'=>$config['customer_id'], 'cid'=>$config['customer_id'], 'dtype'=>2], false);// 阅读记录
                    $text .= "             ✨<a href='{$url}'>《阅读记录》</a>👈\r\n\r\n";
                    $novel  = $this->weeklyReadMax(2, 5);
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $title  = $this->storageInfo('title');
                    $text  .= "      点击进入📚<a href='{$url}'>《{$title}》</a>\r\n\r\n";
                    $novel  = $this->weeklyReadMax(2, 6);
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $title  = $this->storageInfo('title');
                    $text  .= "      点击进入📚<a href='{$url}'>《{$title}》</a>\r\n\r\n";
                    $url  = $this->host . route("jumpto", ['route'=>'index', 'customer_id'=>$config['customer_id'], 'cid'=>$config['customer_id'], 'dtype'=>2], false);// 阅读记录
                    $text  .= "🌴温馨提示：充值成功后可参与充值抽奖活动，点击<a href='{$url}'>首页</a> - 活动 即可进入🎊";

                    /*
                    // 推送充值优惠活动
                    $moneyBtn = $this->rechargeMoneyInfo();
                    $url    = $this->host . "/common/jumpto?btn_id={$moneyBtn['id']}&type=act&route=recharge_act&dtype=2&customer_id={$config['customer_id']}&cid={$config['customer_id']}";
                    $text   = "您的新用户专享礼包已送达\r\n\r\n" . str_replace('<br>', '，', $moneyBtn['title']) . "，只有一次机会哦！\r\n\r\n";
                    $text  .= "过期失效，不要错过！\r\n\r\n<a href='{$url}'>点击立即领取>></a>\r\n\r\n";
                    $text  .= $this->strEndKefu($config['customer_id']);*/
                    break;
                case 'h6':
                    // 推送签到和推荐小说
                    $text   = "亲爱的@{$user['name']}，您今日还未签到，本次签到最高可领取150书币哦\r\n\r\n";
                    $novel  = $this->weeklyReadMax(2, 1);
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $text  .= "<a href='{$url}'>❤点击此处签到领书币</a>\r\n\r\n\r\n";

                    $novel  = $this->weeklyReadMax(2, 0);
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $title  = $this->storageInfo('title');
                    $text  .= "👉👉<a href='{$url}'>{$title}</a>\r\n\r\n";

                    $novel  = $this->weeklyReadMax(2, 2);
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $title  = $this->storageInfo('title');
                    $text  .= "👉👉<a href='{$url}'>{$title}</a>\r\n\r\n";

                    $text  .= "为方便下次阅读，请<a href='{$this->host}/img/wechat.totop.png'>置顶公众号</a>\r\n\r\n";
                    $url    = $this->host . route('jumpto', ['route'=>'contact', 'cid'=>$config['customer_id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'jumpto'=>'index'], false);  // 个人中心
                    $text  .= "如有问题，快去<a href='{$url}'>联系客服</a>吧！";
                    break;
            }
            $content = [
                'touser'    => $user['openid'],
                'msgtype'   => 'text',
                'text'      => [
                    'content'   => $text,
                ],
            ];
        } else {
            switch ($this->type) {
                case 'h6':
                    $novel  = $this->weeklyReadMax();
                    $title  = "恭喜亲爱的@{$user['name']}，获得随机【签到卡】";
                    $desc   = "👆👆👆点击此处查看";
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    $img    = $this->storageInfo('img');
                    break;
                case 'h12':
                    $title  = "@亲爱的{$user['name']}，点击这里，未读新消息不错过";
                    $desc   = "👆👆👆点击此处查看";
                    $url    = $this->host . route("jumpto", ['route'=>'read_log', 'cid'=>$config['customer_id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2], false);
                    $img    = $this->storageInfo('img');
                    break;
                case 'h18':
                    $novel  = $this->weeklyReadMax(2, 3);
                    list($title, $img) = $this->storageInfo('all');
                    $desc   = "👆👆👆点击此处查看";
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    break;
                case 'h23':
                    $novel  = $this->weeklyReadMax(2, 4);
                    list($title, $img) = $this->storageInfo('all');
                    $desc   = "👆👆👆点击此处查看";
                    $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
                    break;
            }

            $content = [
                'touser'    => $user['openid'],
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => $title,
                            'description'   => $desc,
                            'url'           => $url,
                            'picurl'        => $img,
                        ],
                    ],
                ],
            ];
        }

        return $content;
    }

    private function h88PushContent($user, $config, $user_id) {
        // 推送签到和推荐小说
        $text   = "亲爱的@{$user['name']}，您充值的书币已到账\r\n\r\n由于系统原因，导致延迟到账，给您带来的不便，请谅解，我们将竭诚为您服务\r\n\r\n";
        $novel  = $this->weeklyReadMax(2, 1);
        $url    = $this->host . route("jumpto", ['route'=>'center', 'customer_id'=>$config['customer_id'], 'cid'=>$config['customer_id'], 'dtype'=>2], false);
        $text  .= "<a href='{$url}'>❤点我查看书币</a>\r\n\r\n\r\n";

        $novel  = $this->weeklyReadMax(2, 0);
        $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
        $title  = $this->storageInfo('title');
        $text  .= "👉👉<a href='{$url}'>{$title}</a>\r\n\r\n";

        $novel  = $this->weeklyReadMax(2, 2);
        $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
        $title  = $this->storageInfo('title');
        $text  .= "👉👉<a href='{$url}'>{$title}</a>\r\n\r\n";

        $url    = $this->host . route('jumpto', ['route'=>'center', 'cid'=>$config['customer_id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2], false);  // 个人中心
        $text  .= "如有问题，快去<a href='{$url}'>联系客服</a>吧！";
        return $text;
    }
    protected $extendNovels; // 推广链接的小说信息
    private function regirstExtendNovel($user, $config, $user_id) {
        if (!isset($this->extendNovels[$user['extend_link_id']])) {
            $this->extendNovels[$user['extend_link_id']] = $this->extendLinks->ExtendInfo($user['extend_link_id']);
        }
        if (!$this->extendNovels[$user['extend_link_id']]) return false;

        $text = "@{$user['name']}，您上次关注未阅读完的章节\r\n\r\n";
        $url  = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$this->extendNovels[$user['extend_link_id']]['novel_id'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
        $text .= "👉👉 <a href='{$url}'>点击我继续阅读</a>\r\n\r\n";
        $text .= "为方便您下次阅读，请<a href='{$this->host}/img/wechat.totop.png'>置顶公众号（点击我查看如何置顶）</a>\r\n\r\n";
        $text .= $this->strEndKefu($config['customer_id']);
        return $text;
    }

    private $storageTitleList;
    private $storageImgList;
    // 标题库，图片库信息
    private function storageInfo($type = 'all') {
        if (!$this->storageTitleList) {
            $this->storageTitleList = $this->storageTitles->TitleList();
        }
        if (!$this->storageImgList) {
            $this->storageImgList   = $this->storageImgs->ImgList();
        }

        switch ($type) {
            case 'img':
                $info = $this->storageImgList[array_rand($this->storageImgList)];
                if ($info) {
                    return $info['img'];
                } else {
                    return $this->host . $this->images[array_rand($this->images)];
                }
                break;

            case 'title':
                $info = $this->storageTitleList[array_rand($this->storageTitleList)];
                if ($info) {
                    return $info['title'];
                } else {
                    return '更多精彩来临~~~';
                }
                break;
            default:
                $info = $this->storageImgList[array_rand($this->storageImgList)];
                if ($info) {
                    $img = $info['img'];
                } else {
                    $img = $this->host . $this->images[array_rand($this->images)];
                }
                $info = $this->storageTitleList[array_rand($this->storageTitleList)];
                if ($info) {
                    $title = $info['title'];
                } else {
                    $title = '更多精彩来临~~~';
                }
                return [$title, $img];
        }
    }
    // 周阅读量最高的小说
    private function weeklyReadMax($status = 3, $index = 0) {
        if (!$this->rmaxNov || !isset($this->rmaxNov[$index])) {
            $normal_list = $this->novels->model
                ->where('status', 1)
                ->where('sections', '>', 20)
                ->orderBy('week_read_num', 'desc')
                ->select(['id', 'title'])
                ->limit(10)->get();
            $normal_list = $this->novels->toArr($normal_list);

            $abnormal_list = $this->abnormalNovels();

            // 默认取正经小说
            if ($status == 3) {
                $normal_list = array_merge($normal_list, $abnormal_list); // 正经的和烧的随机获取
            } else if ($status == 2) {
                $normal_list = ($abnormal_list && count($abnormal_list) >= $this->abnv_num) ? $abnormal_list : $normal_list; // 只取烧的
            }

            $this->rmaxNov = $normal_list;
        }

        return isset($this->rmaxNov[$index]) ? $this->rmaxNov[$index] : $this->rmaxNov[0];
    }
    private function abnormalNovels() {
        $tdday = date('d');
        $day_key = config('app.name').'send_daily_msg_day';
        if (Cache::has($day_key)) {
            $cday = Cache::get($day_key);
            $cday = explode('-', $cday);
            $day = $cday[0];
            if ($tdday != $cday[1]) {
                $day++;
            }
        } else {
            $day = 0;
        }

        $abnormal_list = $this->novels->model
            ->where('status', 2)
            ->where('sections', '>', 20)
            ->orderBy('id', 'desc')
            ->offset($day * $this->abnv_num)
            ->limit($this->abnv_num)
            ->select(['id', 'title'])
            ->get();
        $abnormal_list = $this->novels->toArr($abnormal_list);
        if (count($abnormal_list) < $this->abnv_num) {
            if (Cache::has($day_key)) Cache::forget($day_key);
            $abnormal_list = $this->abnormalNovels();
        }
        // 保存当前的天数
        if (Cache::has($day_key)) {
            Cache::put($day_key, $day.'-'.$tdday, 86400);
        } else {
            Cache::add($day_key, $day.'-'.$tdday, 86400);
        }

        return $abnormal_list;
    }
    // 优惠活动充值金额信息
    private function rechargeMoneyInfo() {
        if (!$this->rechargeMoney) {
            $this->rechargeMoney = $this->moneyBtns->model
                ->where([['status', 1], ['default',7]])
                ->select($this->moneyBtns->model->fields)
                ->first();
            $this->rechargeMoney = $this->moneyBtns->toArr($this->rechargeMoney);
        }

        return $this->rechargeMoney;
    }
    // 添加客服或者置顶公众号
    private function strEndKefu($customer_id) {
        // $str = "为方便下次阅读，请<a href='{$this->host}/img/wechat.totop.png'>置顶 公众号</a>\r\n\r\n";
        $customer_link = $this->commonSets->values('service', 'customer_link');
        if (!empty($customer_link)) {
            $url = $customer_link;
        } else {
            $url = $this->host . route("jumpto", [
                    'route'         => 'contact',
                    'cid'           => $customer_id,
                    'customer_id'   => $customer_id,
                    'dtype'         => 2,
                    'jumpto'        => 'index',
                ], false);
        }
        $str = "需要帮助！添加 ，<a href='{$url}'> 人工客服</a>";
        return $str;
    }

}
