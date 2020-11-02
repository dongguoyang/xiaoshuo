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


class SendUPush extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-upush {--type= : 推送哪一个类型}';

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
    private $storageTitleList;
    private $storageImgList;

    private $regirstExtend = false; // 21点推送推广链接的小说
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
        $this->novels           = new NovelRepository();
        $this->commonSets       = new CommonSetRepository();
        $this->storageTitles    = new StorageTitleRepository();
        $this->storageImgs      = new StorageImgRepository();
        $this->now = time();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->type = $this->option('type');
        $now_h = date('H');
        $nvwechats = $this->nvwechats->allByMap([['status', 1]], ['id']);
        $nvw_ids = $this->nvwechats->toPluck($nvwechats, 'id');
        $configs = $this->wechatConfigs->model->whereIn('platform_wechat_id', $nvw_ids)->select(['id', 'customer_id', 'platform_wechat_id', 'user_hpush'])->get();
        $this->info($this->signature . ' start ' . date('Y-m-d H:i:s'));
        foreach ($configs as $k=>$config) {
            if (!$config['user_hpush']) continue;
            $config['user_hpush'] = json_decode($config['user_hpush'], 1);
            if (!isset($config['user_hpush']['switch']) || !$config['user_hpush']['switch']) {
                // 没有开启；就不发送
                continue;
            }
            $sconfig = $config['user_hpush']['s'];
            foreach ($sconfig as $item) {
                if($item['h'] == -1){
                    continue;
                }
                if ($item['h'] == $now_h) {
                    // 检测小时推送
                    $this->customerUserSend($item, $config);
                    break;
                }
            }
        }
        $this->info($this->signature . ' over ' . date('Y-m-d H:i:s'));
    }
    /**
     * 给对应客户的用户发送消息
     * @param array $config
     */
    private function customerUserSend($item, $config) {
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
                $content = $this->productMsgContend($user, $config, $item);
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
    private function productMsgContend($user, $config, $item) {
        //$user_id = $user['first_account'] > 0 ? $user['first_account'] : $user['id'];
        $user_id = $user['id'];
        if(empty($item['n'])){//随机小说
            $novel = $this->randData('n');
            $item['n'] = $novel['id'];
            echo $item['n'] .'------';
        }
        if(empty($item['t'])){//随机标题
            $item['t'] = $this->randData('t');
        }
        if(empty($item['d'])){//随机描述
            $item['d']   = "👆👆👆点击此处查看";
        }
        if(empty($item['p'])){//随机图片
            $item['p'] = $this->randData('p');
        }
        $url    = $this->host . route("jumpto", ['route'=>'novel', 'novel_id'=>$item['n'], 'customer_id'=>$config['customer_id'], 'dtype'=>2, 'uid'=>$user_id], false);
        $content = [
            'touser'    => $user['openid'],
            'msgtype'   => 'news',
            'news'      => [
                'articles'  =>  [
                    [
                        'title'         => $item['t'],
                        'description'   => $item['d'],
                        'url'           => $url,
                        'picurl'        => $item['p'],
                    ],
                ],
            ],
        ];
        return $content;
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

    //如果标题 描述 图片 小说为空时就随机
    private function randData($type){
        switch ($type){
            case 'n': //随机小说
                $index = rand(2,8);
                $conent  = $this->weeklyReadMax(2, $index);
                break;
            case 't':
                $conent = $this->storageInfo('title');
                break;
            case 'p':
                $conent = $this->storageInfo('img');
                break;
        }
        return $conent;
    }


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
            //$abnormal_list = $this->abnormalNovels();
            $abnormal_list = [];
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

}
