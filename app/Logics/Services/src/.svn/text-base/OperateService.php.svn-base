<?php

namespace App\Logics\Services\src;

use App\Logics\Repositories\src\BookStoreRepository;
use App\Logics\Repositories\src\CoinActRepository;
use App\Logics\Repositories\src\CoinLogRepository;
use App\Logics\Repositories\src\CommentRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\CouponLogRepository;
use App\Logics\Repositories\src\CouponRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\GoodsRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use App\Logics\Repositories\src\PrizeLogRepository;
use App\Logics\Repositories\src\PrizeRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\RechargeLogRepository;
use App\Logics\Repositories\src\RewardLogRepository;
use App\Logics\Repositories\src\SignLogRepository;
use App\Logics\Repositories\src\TemplateMsgRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\PushmsgTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OperateService extends Service {
    use OfficialAccountTrait, PushmsgTrait;

    protected $signLogs;
    protected $users;
    protected $commonSets;
    protected $coinLogs;
    protected $rewardLogs;
    protected $comments;
    protected $goodses;
    protected $bookStores;
    protected $novels;
    protected $novelSections;
    protected $couponLogs;
    protected $rechargeLogs;
    protected $prizes;
    protected $prizeLogs;
    protected $coupons;
    protected $templateMsgs;
    protected $domains;
    protected $readLogs;
    protected $coinActs;

	public function Repositories() {
		return [
		    'signLogs'      => SignLogRepository::class,
            'commonSets'    => CommonSetRepository::class,
            'users'         => UserRepository::class,
            'coinLogs'      => CoinLogRepository::class,
            'rewardLogs'    => RewardLogRepository::class,
            'comments'      => CommentRepository::class,
            'goodses'       => GoodsRepository::class,
            'bookStores'    => BookStoreRepository::class,
            'novels'        => NovelRepository::class,
            'novelSections' => NovelSectionRepository::class,
            'couponLogs'    => CouponLogRepository::class,
            'rechargeLogs'  => RechargeLogRepository::class,
            'prizes'        => PrizeRepository::class,
            'prizeLogs'     => PrizeLogRepository::class,
            'coupons'       => CouponRepository::class,
            'templateMsgs'  => TemplateMsgRepository::class,
            'domains'       => DomainRepository::class,
            'readLogs'      => ReadLogRepository::class,
            'coinActs'      => CoinActRepository::class,
		];
	}
    /**
     * 获取用户签到信息
     */
    public function SignInfo() {
        $sess = $this->loginGetSession();
        $info = $this->signLogs->LastSignInfo($sess['id']);
        $info['day_coins'] = json_decode($this->commonSets->values('sign', 'coin_nums'), 1);

        // 计算今日签到应获得书币数量
        $continue_day = $info['signed'] ? $info['continue_day'] : ($info['continue_day'] + 1);
        $continue_day = $continue_day > count($info['day_coins']) ? count($info['day_coins']) : $continue_day;
        $info['coin']      = $info['day_coins'][$continue_day];

        return $this->result($info);
    }
    /**
     * 获取用户签到信息
     */
    public function DoSign() {
        $sess = $this->loginGetSession(true);
        $login_sign = $this->commonSets->values('sign', 'login_sign');
        if (empty($login_sign) && !$sess['subscribe'] && request()->input('refer') == 'notsignpage') {
            throw new \Exception('未关注且未在签到页面执行！', 2000);
        }
        $info = $this->signLogs->LastSignInfo($sess['id']);
        if ($info['signed']) {
            throw new \Exception('今日已签到成功！', 2000);
        }
        return $this->result($this->InserSignData($info));
    }
    /**
     * 加入签到信息
     * @param array $info 最后一次的签到数据
     * @return array
     */
    public function InserSignData($info, $user = []) {
        $customer_id = false;
        if (!$user) {
            $user = $this->loginGetSession(true);
        } else {
            $customer_id = $user['customer_id'];
            if ($user['first_account'] > 0) {
                $user = $this->users->UserCacheInfo($user['first_account']);
            }
        }
        $sign_conf = json_decode($this->commonSets->values('sign', 'coin_nums'), 1); // 获取签到书币配置

        $continue_day = $info['continue_day'] + 1;

        $data = [
            'user_id'       => $user['id'],
            'continue_day'  => $continue_day,
            'coin'          => ($continue_day > 7 ? $sign_conf['7'] : $sign_conf[$continue_day]),
            'customer_id'           => $user['customer_id'],
            'platform_wechat_id'    => $user['platform_wechat_id'],
        ];

        try
        {
            DB::beginTransaction();
            $in = $this->signLogs->create($data);
            if (!$in) {
                throw new \Exception('签到日志添加失败！', 2001);
            }
            $updata = [
                'sign_day'  => $continue_day,
                'balance'   => '+' . $data['coin'],
            ];
            $user = $this->users->UpdateFromDB($user['id'], $updata);
            $sign = $this->signLogs->LastSignInfo($user['id'], true);
            $this->insertCoinLog($sign, $user);
            DB::commit();

            if ($customer_id) {
                // 签到关键字回复
                return $this->signMsgContent($customer_id, $data, $user);
            }

            $data['username'] = $user['name'];
            $data['openid'] = $user['openid'];
            // $this->sendSignTemplate($data); // 发送签到成功模板消息
            $this->sendSignMsg($data, $user); // 发送 签到成功客服消息

            return ['user'=>$user, 'sign'=>$sign];
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw new \Exception('签到失败！' . $e->getMessage(), 2001);
        }
    }
    /**
     * 发送模板消息
     */
    private function sendSignTemplate($info) {
        $template = $this->templateMsgs->findByMap([
            ['customer_id', $info['customer_id']],
            ['platform_wechat_id', $info['platform_wechat_id']],
            ['type', 5],
        ], ['template_id', 'content']);
        if (!$template) {
            return false; // 没有模板消息就不发送
        }

        $data['touser']      = $info['openid'];
        $data['template_id'] = $template['template_id'];
        $data['url']         = $this->domains->randOne(1, $info['customer_id']) . route('novel.toindex', ['cid'=>$info['customer_id']], false);
        $content = json_decode($template['content'], 1);
        foreach ($content as $k=>$v) {
            if (strpos($v['value'], '{username}')!==false) {
                $v['value'] = str_replace('{username}', $info['username'], $v['value']);
            }
            if (strpos($v['value'], '{date}')!==false) {
                $v['value'] = str_replace('{date}', date('Y-m-d'), $v['value']);
            }
            if (strpos($v['value'], '{datetime}')!==false) {
                $v['value'] = str_replace('{datetime}', date('Y-m-d H:i:s'), $v['value']);
            }
            if (strpos($v['value'], '{signday}')!==false) {
                $v['value'] = str_replace('{signday}', $info['continue_day'], $v['value']);
            }
            $content[$k] = $v;
        }
        $data['data'] = $content;
        try {
            $this->SendTemplate($info['customer_id'], $data); // 执行发送
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * 发送模板消息
     */
    private function sendSignMsg00($info, $user) {
        $data['touser']  = $info['openid'];
        $data['msgtype'] = 'text';

        $host = $this->domains->randOne(1, $user['customer_id']);
        $content = "{$user['name']}，今日签到成功，已连续签到{$info['continue_day']}天，获得{$info['coin']}书币，连续签到获得书币会翻倍哟~\r\n\r\n";
        $list = $this->readLogs->model->where([['user_id', $user['id'], ['status', 1]]])->select($this->readLogs->model->fields)->orderBy('updated_at', 'desc')->limit(3)->get();
        $list = $this->readLogs->toArr($list);
        if (isset($list[0])) {
            $readLog = $list[0];
            //$url = $host . route('novel.tosection', ['novel_id'=>$readLog['novel_id'], 'section'=>$readLog['end_section_num'], 'customer_id'=>$user['customer_id']], false);
            $url = $host . route("jumpto", ['route'=>'section', 'section_num'=>0, 'dtype'=>2, 'novel_id'=>$readLog['novel_id'], 'section'=>0, 'customer_id'=>$user['customer_id'], 'uid'=>$user['id']], false);
            $content .= "<a href='{$url}'>【点此继续阅读】</a>\r\n\r\n ";
            unset($list[0]);
        }
        if (isset($list[1])) {
            $content .= "历史阅读记录： \r\n\r\n";
            foreach ($list as $readLog) {
                $url = $host . route('novel.tosection', ['novel_id'=>$readLog['novel_id'], 'section'=>$readLog['end_section_num'], 'customer_id'=>$user['customer_id']], false);
                $content .= "<a href='{$url}'> {$readLog['name']}</a>\r\n\r\n";
            }
        }
        //$content .= "为了方便下次阅读，请<a href='{$host}/img/wechat.totop.png'> 置顶 公众号</a>";
        $content .= $this->strEndKefuLink($host, $user['customer_id']);
        $data['text']['content'] = $content;

        try {
            $this->SendCustomMsg($info['customer_id'], $data, true); // 执行发送
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
    /**
     * 插入书币日志
     * @param array $signdata 签到后的最近一次签到数据
     * @param array $user 签到后的用户数据
     */
    private function insertCoinLog($signdata, $user) {
        $data = [
            'user_id'   => $signdata['user_id'],
            'type'      => $this->coinLogs->getType('sign'),
            'type_id'   => $signdata['id'],
            'coin'      => $signdata['coin'],
            'balance'   => $user['balance'],
            'title'     => '用户签到',
            'desc'      => '用户签到' . $signdata['continue_day'] . '天，获得书币'. $signdata['coin'],
            'customer_id'           => $user['customer_id'],
            'platform_wechat_id'    => $user['platform_wechat_id'],
        ];
        $this->coinLogs->create($data);
    }

    /**
     * 小说打赏记录
     */
    public function RewardList() {
        $order = request()->input('order_column', 'coin_num');
        $novel_id = request()->input('novel_id');
        $page = request()->input('page', 1);

        if (!in_array($order, ['created_at', 'coin_num'])) {
            throw new \Exception('排序字段异常！', 2000);
        }

        $list = $this->rewardLogs->RewardList($novel_id, $page, $order);
        $list_page = (count($list) < $this->rewardLogs->pagenum ? null : ++$page);
        if (!$list || !count($list)) {
            $list = $this->shameRewardList($list, $novel_id);
        }
        return $this->result(['data'=>$list, 'last_page'=>$list_page]);
    }
    // 假打赏数据
    private function shameRewardList($data, $novel_id) {
        //'id', 'customer_id', 'platform_wechat_id', 'novel_id', 'user_id', 'username', 'user_img', 'goods_id', 'goods_num', 'goods_img', 'coin_num', 'created_at',
        $key = config('app.name') . 'shame_reward_list';
        $list = Cache::remember($key, 60, function (){
            $goods = $this->goodses->allByMap([['status', 1]], ['id as goods_id', 'name as goods_name', 'img as goods_img', 'coin as coin_num']);
            $users = $this->users->limitPage([['id', '>', 1]], 1, 50, ['id as user_id', 'name as username', 'img as user_img'], ['id'=>'asc']);
            $users = $this->users->toArr($users);
            $goods = $this->goodses->toArr($goods);
            $gcoun = count($goods);
            $now = time();

            $rel = [];
            foreach ($users as $k=>$v) {
                $temp = array_merge($v, $goods[$k % $gcoun]);
                $temp['goods_num'] = mt_rand(1, 5);
                $temp['coin_num'] *= $temp['goods_num'];
                $temp['created_at'] = $now - mt_rand(0, 7200);
                $rel[] = $temp;
            }

            return $rel;
        });

        shuffle($list);
        $rel = array_slice($list, 0, 10);
        foreach ($rel as $k=>$v) {
            $rel[$k]['novel_id'] = $novel_id;
        }
        if ($data) {
            $rel = array_merge($rel, $data);
        }
        return $rel;
    }
    /**
     * 小说评论记录
     */
    public function CommentList() {
        $novel_id = request()->input('novel_id');
        $page = request()->input('page', 1);

        $list = $this->comments->CommentList($novel_id, $page);
        $last_page = (count($list) < $this->comments->pagenum ? null : ++$page);
        if (!$list || !count($list)) {
            $list = $this->shameCommentList($list, $novel_id);
        }

        return $this->result(['data'=>$list, 'last_page'=>$last_page]);
        // return $this->result($list);
    }
    // 假评论数据
    private function shameCommentList($data, $novel_id) {
        //'id', 'novel_id', 'user_id', 'username', 'user_img', 'content', 'created_at',
        $key = config('app.name') . 'shame_comment_list';
        $list = Cache::remember($key, 60, function (){
            $users = $this->users->limitPage([['id', '>', 1]], 1, 50, ['id as user_id', 'name as username', 'img as user_img'], ['id'=>'asc']);
            $users = $this->users->toArr($users);
            $now = time();
            $comms = $this->shareCommentStr();

            $rel = [];
            foreach ($users as $k=>$v) {
                $v['content'] = trim($comms[$k]);
                $v['created_at'] = $now - mt_rand(0, 7200);
                $rel[] = $v;
            }

            return $rel;
        });

        shuffle($list);
        $rel = array_slice($list, 0, 10);
        foreach ($rel as $k=>$v) {
            $rel[$k]['novel_id'] = $novel_id;
        }
        if ($data) {
            $rel = array_merge($rel, $data);
        }
        return $rel;
    }
    private function shareCommentStr() {
        $str = "这是我看过的最好看的小说了（因为本人看的也不多），故事情节跌宕起伏出人意料却又能那么顺情衔接符合逻辑，让人欲罢不能，但求作者大人能文思泉涌，每天多更新几章，以解极度预知后事如何之苦，跪求
精彩不断，回味无穷
请写快点呀!这样读者才不会流失!!!
小说情感扣人心弦很可以！是本好书
从来没有看过这么好看的小说、非常感谢作者！
跟新快一点，多一些吧，天天坐等精彩绝伦
很好看，但是更新的有点慢
作者文笔太厉害了，精彩故事环环相扣，使人看到入了迷，反反复复连看几次都还想看，作者能快点更新就好。
太爽了，内容，就是能不能一次更新的稍微多一点
很看好，值得推荐
很好看
好看的不得了
书中描写人物生动形象
太好看了，欲罢不能。作者辛苦了！希望每天能多更新几章。作者多更新几章吧，看到精彩部分就完了，我都想哭了，拜托了，希望你能看到。
有点伤心啊 希望作者能写一个完美结局
人物动态很生动，细节也好看，超赞，更新加速哦！好期待。
作者辛苦了，希望每天多更新几章。谢谢
加油＾０＾~
第一次看小说 中毒太深，很看好，麻烦快点更新
我是一个从来不看小说的人，看了之后我中毒了
我天天在等更新， 很喜欢。
现在看的越来越入迷了，希望作者多多更新几章，更新速度慢
你的这个书写的太美啦、希望每天多更新几篇、🙏
看得我入迷了都，啥时候能更新完啊。
明天多更新几张，内容挺不错的
自从看了这本书后，就想着下一章的情节，想迫不及待的看完，期待作者的更新
不错不错
还不错要是拍成电视剧就好了
很好看。迫不及待的需要早更
觉得写的一环一环非常紧凑，喜欢
汪洋恣肆，纵横捭阖，英雄气短，儿女情长，荡气回肠，跌宕起伏，精彩绝伦，肆意挥洒，一气呵成！
原来这是电视剧的原著小说1，好看，正能量
很好看，没有多余的废话。情节扣人心悬
五星好评☞♞
正点，通俗易懂我喜欢。以后多发表文章
好看，写的不错，希望大家都来看
本书写的精彩，看的酣畅淋漓，有一种身临其境的感觉
好看！每一个胖子都是潜力股！感觉装傻的男主莫名的暖怎么办？
大大书太棒了，决定一追到底！
写的非常好，啥时候能看到电视剧？ 会有很多人喜欢的
太好看了
好虐啊，好心疼女主
我很喜欢这部书写得太好了
希望能出電視劇，太精彩了
我很喜欢这文章
非常好看，文昌比较精彩，鲜明
内容不错，一般女生都喜欢看
挺好的？越看越想看
要是有一个这么聪明伶俐又可爱的儿子就好啦
无法自拔 推荐推荐
看了一下就彻底爱上了";
        $arr = explode("\n", $str);
        return $arr;
    }

    /**
     * 小说打赏商品列表
     */
    public function GoodsList() {
        $list = $this->goodses->GoodsList();

        return $this->result($list);
    }

    /**
     * 执行打赏操作
     */
    public function DoReward() {
        $goods_id = request()->input('goods_id');
        $novel_id = request()->input('novel_id');
        $num = request()->input('num');
        if (!$goods_id || !$num) {
            throw new \Exception('参数异常6！', 2000);
        }

        $sess = $this->loginGetSession(true);
        $goods = $this->checkDoRewardPower($sess, $goods_id, $num); // 检测商品库存和用户书币数量

        $data = [
            'novel_id' => $novel_id,
            'user_id'  => $sess['id'],
            'username' => $sess['name'],
            'user_img' => $sess['img'],
            'goods_id' => $goods_id,
            'goods_num'=> $num,
            'goods_img'=> $goods['img'],
            'coin_num' => ($goods['coin'] * $num),
            'customer_id'           => $sess['customer_id'],
            'platform_wechat_id'    => $sess['platform_wechat_id'],
        ];
        $rewardData = [
            'user_id'   => $sess['id'],
            'type'      => $this->coinLogs->getType('zan'),
            'type_id'   => 0,
            'coin'      => $data['coin_num'] * -1,
            'balance'   => $sess['balance'] - $data['coin_num'] ,
            'title'     => '打赏作者',
            'desc'      => '打赏作者，消耗' . $data['coin_num'] . '书币！',
            'status'    => 1,
            'customer_id'   => $sess['customer_id'],
            'platform_wechat_id'   => $sess['platform_wechat_id'],
        ];
        try
        {
            DB::beginTransaction();
            $reward = $this->rewardLogs->create($data);
            $user_data = [
                'balance' => '-' . $data['coin_num'],
            ];
            $sess = $this->users->UpdateFromDB($sess['id'], $user_data);
            if ($goods['count'] > 0) {
                $goods->stock -= $num;
            }
            $goods->sale_num += $num;
            $goods->save();

            $this->novels->AddRewardCoin($novel_id, $data['coin_num']);// 增加小说收到的书币数

            $rewardData['type_id'] = $reward['id'];
            $this->coinLogs->create($rewardData);
            DB::commit();

            $this->rewardLogs->ClearCache($novel_id, $sess['id']); // 清除缓存
            $this->coinLogs->ClearCache($sess['id']);

            return $this->result($sess, 0, '打赏成功！');
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw new \Exception('打赏失败！'.$e->getMessage(), 2002);
        }
    }
    /**
     * 检测用户是否能够打赏
     * @param array $user
     * @param int $goods_id
     * @param int $num
     */
    private function checkDoRewardPower($user, $goods_id, $num) {
        $goods = $this->goodses->find($goods_id, $this->goodses->model->fields);
        if ($goods['count'] > 0 && ($goods['count'] - $goods['sale_num'] < $num)) {
            throw new \Exception('商品数量不足，请减少数量！', 2001);
        }
        $coin_num = $goods['coin'] * $num;
        if ($user['balance'] < $coin_num) {
            throw new \Exception('书币不足！请购买书币后进行操作！', 3001);
        }

        return $goods;
    }
    /**
     * 执行评论操作
     */
    public function DoComment() {
        $novel_id = request()->input('novel_id');
        $content = request()->input('content');

        if (strlen($content) > 450) {
            throw new \Exception('评论内容过长；请输入小于150个中文字符或小于450个英文字符', 2002);
        }
        $sess = $this->loginGetSession(true);

        // 记录评论次数
        $numkey = config('app.name') . 'comment_num' . $sess['id'];
        $num = Cache::get($numkey);
        if ($num > 8) {
            throw new \Exception('今日评论次数过多；后面再来吧！', 2002);
        }
        if ($num) {
            Cache::increment($numkey);
        } else {
            $mins = (strtotime(date('Y-m-d')) + 86400 - time()) / 60;
            Cache::put($numkey, 1, $mins);
        }

        $data = [
            'novel_id'  => $novel_id,
            'user_id'   => $sess['id'],
            'username'  => $sess['name'],
            'user_img'  => $sess['img'],
            'content'   => $content,
            'status'    => 0,
        ];
        $this->comments->create($data);
        $this->comments->ClearCache($novel_id);
        return $this->result(null, 0, '评论成功！');
    }
    /**
     * 执行加入书架
     */
    public function AddBookStores() {
        $novel_id = request()->input('novel_id');
        $section  = request()->input('section', 0);
        $sess = $this->loginGetSession();
        $data = [
            'novel_id'  => $novel_id,
            'user_id'   => $sess['id'],
        ];
        if ($had = $this->bookStores->findByMap($data, $this->bookStores->model->fields)) {
            if ($had['status']) {
                throw new \Exception('该书已在书架中，请勿重复添加', 2002);
            }
        }
        $novel = $this->novels->NovelInfo($novel_id);
        if (!$novel) {
            throw new \Exception('该书不存在！', 2002);
        }

        // 查询用户书架数量
        $limit = $this->commonSets->values('novel', 'bookstore_limit');
        $limit = $limit ? intval($limit) : 30;
        if ( $this->bookStores->BookStoreNum($sess['id']) >= $limit ) {
            throw new \Exception('书架图书过多；请删除后再添加！', 2002);
        }

        $data['novel_title'] = $novel['title'];
        $data['novel_img'] = $novel['img'];
        $data['section_num'] = $section;
        $data['status'] = 1;
        if ($had) {
            $this->bookStores->update($data, $had['id']);
        } else {
            $this->bookStores->create($data);
        }
        $this->bookStores->ClearCache($sess['id'], $novel_id);

        return $this->result(null, 0, '加入书架成功！');
    }
    /**
     * 用户优惠券列表获取
     */
    public function UserCoupons() {
        $valid = request()->input('valid');
        if (!in_array($valid, ['valid', 'invalid'])) {
            throw new \Exception('参数错误！', 2000);
        }

        $sess = $this->loginGetSession();
        $list = $this->couponLogs->UserCoupons($sess['id'], $valid);
        return $this->result($list);
    }

    /**
     * 用户充值记录
     */
    public function RechargeLogs() {
        $page = request()->input('page', 1);

        $sess = $this->loginGetSession();
        $list = $this->rechargeLogs->UserLogs($sess['id'], $page);
        return $this->result(['data'=>$list, 'last_page'=>(count($list) < $this->rewardLogs->pagenum ? null : ++$page)]);
        // return $this->result($list);
    }
    /**
     * 用户消费记录
     */
    public function CoinLogs() {
        $page = request()->input('page', 1);

        $sess = $this->loginGetSession();
        $list = $this->coinLogs->UserLogs($sess['id'], $page);
        return $this->result(['data'=>$list, 'last_page'=>(count($list) < $this->coinLogs->pagenum ? null : ++$page)]);
        //return $this->result($list);
    }
    /**
     * 抽奖页奖品信息列表
     */
    public function PrizeList() {
        $list = $this->prizes->allByMap([['status', 1]], ['id', 'name', 'img', 'send_num']);
        $rel['prize_info'] = $this->prizes->toArr($list);
        $prized_list = $this->prizeLogs->LastPrized();
        shuffle($prized_list);
        $rel['prized_list'] = $this->prizeLogs->toArr($prized_list);
        $prize_conf = $this->commonSets->values('prize');
        $rel['prize_bgimg'] = $prize_conf['background_img'];
        $rel['doprize_coin'] = intval($prize_conf['doprize_coin']) ?: 25;
        return $this->result($rel);
    }
    /**
     * 用户中奖记录列表
     */
    public function UserPrizeLog() {
        $sess = $this->loginGetSession();
        $page = request()->input('page', 1);
        $rel['data'] = $this->prizeLogs->UserPrizeLogs($sess['id'], $page);
        $rel['last_page'] = count($rel['data']) < $this->prizeLogs->pagenum ? null : ++$page;

        return $this->result($rel);
    }
    /**
     * 执行抽奖
     */
    public function DoPrize() {
        $sess = $this->loginGetSession(true);
        $prizeCoin = $this->commonSets->values('prize', 'doprize_coin'); // 执行抽奖消耗书币数量
        $prizeCoin = intval($prizeCoin);
        if ($sess['balance'] < $prizeCoin) {
            throw new \Exception('书币不足；充值后再来抽奖吧！', 3001);
        }
        $prizelist = $this->prizes->allByMap([['status', 1]], $this->prizes->model->fields);
        $count = 0;

        // 获取总的抽奖概率
        foreach ($prizelist as $k=>$prize) {
            if ($prize['count'] < 0 || $prize['send_num'] < $prize['count']) {
                $count += $prize['chance'];
            } else {
                unset($prizelist[$k]);
            }
        }

        // 执行抽奖
        $i = mt_rand(1, $count);

        $count = 0;
        $prized = [];
        $prized_index = -1;
        // 获取抽奖得到的奖品
        foreach ($prizelist as $k=>$prize) {
            //if ($prize['count'] < 0 || $prize['send_num'] < $prize['count']) {
                $count += $prize['chance'];
                if ($i <= $count) {
                    $prized = $prize;
                    $prized_index = $k;
                    break;
                }
            //}
        }

        if (!$prized) {
            throw new \Exception('抽奖异常！', 2002);
        }
        $this->prize2User($prized, $sess, $prizeCoin);
        $this->prizeLogs->ClearCache($sess['id']); // 清空奖品记录缓存

        unset($prized['chance']);
        $prized['index'] = $prized_index;
        return $this->result($prized);
    }
    /**
     * 发放奖品给用户
     * @param array $prize
     * @param array $user
     * @param int $prizeCoin 抽奖消耗积分数
     */
    private function prize2User($prize, $user, $prizeCoin) {
        try
        {
            DB::beginTransaction();
            if ($prize['type'] == 1) {
                $this->coinPrize($prize, $user, $prizeCoin);
            } else {
                $this->couponPrize($prize, $user, $prizeCoin);
            }
            DB::commit();
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw new \Exception('奖品发放失败！'.$e->getMessage(), 2004);
        }
    }
    private function coinPrize($prize, $user, $prizeCoin){
        $prize->send_num++;
        $prize->save();

        // 计算用户余额
        $user_data['balance'] = $user['balance'] - $prizeCoin;

        // 抽中奖品有积分奖励的才执行
        if ($prize['coin']) {
            // 添加奖品日志
            $log = $this->prizeLogs->ToUser($user, $prize);
            // 添加抽奖消耗积分日志
            $data = $this->prizeUsedCoinLog($user, $log, $prizeCoin, $user_data['balance']);
            // 奖品是积分，增加用户积分
            $user_data['balance'] += $prize['coin'];
            // 增加抽中奖的积分日志
            $data['coin'] = $prize['coin'];
            $data['balance'] = $user_data['balance'];
            $data['desc'] = '抽中奖品' . $prize['name'] . '获得' . $prize['coin'] . '书币！';
            $this->coinLogs->create($data);

            $this->coinLogs->ClearCache($user['id']);
        }

        // $user_data['balance'] = $user_data['balance']>0 ? ('+'.$user_data['balance']) : ('-'.abs($user_data['balance']));
        if ($user_data['balance'] != $user['balance']) {
            $this->users->UpdateFromDB($user['id'], $user_data);
        }
    }
    private function couponPrize($prize, $user, $prizeCoin){
        $prize->send_num++;
        $prize->save();

        // 计算用户余额
        $user_data['balance'] = $user['balance'] - $prizeCoin;

        // 奖品是优惠券
        $coupon = $this->coupons->find($prize['coupon_id'], $this->coupons->model->fields);
        // 抽中奖品是优惠券并且有对应优惠券信息才执行后续
        if ($prize['coupon_id'] && $coupon) {
            // 添加奖品日志
            $log = $this->prizeLogs->ToUser($user, $prize);
            // 添加抽奖消耗积分日志
            $data = $this->prizeUsedCoinLog($user, $log, $prizeCoin, $user_data['balance']);
            $this->coinLogs->ClearCache($user['id']);

            $data = [
                'user_id'   => $user['id'],
                'coupon_id' => $coupon['id'],
                'start_at'  => $coupon['start_at'],
                'end_at'    => $coupon['end_at'],
                'status'    => 1,
                'customer_id'           => $user['customer_id'],
                'platform_wechat_id'    => $user['platform_wechat_id'],
            ];// 发放优惠券给用户
            $this->couponLogs->create($data);
            $this->couponLogs->ClearCache($user['id']);
        }

        // $user_data['balance'] = $user_data['balance']>0 ? ('+'.$user_data['balance']) : ('-'.abs($user_data['balance']));
        $this->users->UpdateFromDB($user['id'], $user_data);
    }
    /**
     * 插入抽奖消耗积分日志
     */
    private function prizeUsedCoinLog($user, $log, $prizeCoin, $balance) {
        $data = [
            'user_id'   => $user['id'],
            'type'      => $this->coinLogs->getType('prize'),
            'type_id'   => $log['id'],
            'coin'      => $prizeCoin * -1,
            'balance'   => $balance,
            'title'     => '抽奖',
            'desc'      => '抽奖一次，消耗' . $prizeCoin . '书币！',
            'status'    => 1,
            'customer_id'   => $user['customer_id'],
            'platform_wechat_id'   => $user['platform_wechat_id'],
        ];
        $this->coinLogs->create($data);

        return $data;
    }
    /**
     * 执行反馈记录
     */
    public function DoFeedback() {
        return $this->result([]);
        // $content = request()->input('content');
    }
    /**
     * 送书币活动
     */
    public function CoinAct() {
        $act = $this->CoinActInfo(false);

        $sess = $this->loginGetSession(true);
        if (!$sess['id'] || !$sess['subscribe']) {
            // 没有登录或者没有关注
            throw new \Exception('请登录或关注后再领取！', 2000);
            /*$url = route('jumpto', ['route'=>'index', 'cid'=>$act['customer_id'], 'dtype'=>5], false);
            return redirect($url);*/
        }

        //$url = route('jumpto', ['route'=>'index', 'cid'=>$act['customer_id'], 'dtype'=>2], false);
        if ($act['start_at'] > time() ||
            $act['end_at'] < time() ||
            !$act['status'] ||
            ($act['count'] > 0 && $act['send_num'] >= $act['count'])
        ) {
            throw new \Exception('活动已结束，请关注后续活动！', 2000);
            //return redirect($url);
            // throw new \Exception('活动已结束 / 用户已领取！', 2000);
        }

        $this->coinActSend($act, $sess);
        return $this->result(['coin'=>$act['coin'], 'balance'=>$sess['balance']+$act['coin']], 0, '您已成功领取 ' . $act['coin'] . ' 书币！');
        //return redirect($url);
    }
    /**
     * 领取书币
     */
    private function coinActSend($act, $user) {
        $getnum = $this->coinLogs->model
            ->whereBetween('created_at', [$act['start_at'], $act['end_at']])
            ->where('user_id', $user['id'])
            ->where('type', 7)
            ->where('type_id', $act['id'])
            ->count();
        if ($getnum >= $act['limit']) {
            throw new \Exception('您的领取次数已达上限；下次活动再参与吧！', 2000);
            //return false;// 用户领取次数过多
        }
        try {
            DB::beginTransaction();
            $upda['balance'] = '+' . $act['coin'];
            $this->users->UpdateFromDB($user['id'], $upda);

            $this->coinActs->model->where('id', $act['id'])->increment('send_num', 1); // 增加已领取数量

            $log = [
                'customer_id'   => $user['customer_id'],
                'platform_wechat_id'   => $user['platform_wechat_id'],
                'user_id'       => $user['id'],
                'type'          => 7,
                'type_id'       => $act['id'],
                'coin'          => $act['coin'],
                'title'         => '送书币活动',
                'desc'          => '送书币活动，领取书币' . $act['coin'],
                'balance'       => $upda['balance'],
            ];
            $this->coinLogs->create($log);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('运气不好，领取失败了哟，欢迎下次再来！', 2000);
        }

    }
    /**
     * 书币活动信息
     */
    public function CoinActInfo($apirel = true) {
        $id = request()->input('id');
        $customer_id = request()->input('customer_id');
        if (!$id || !$customer_id) {
            throw new \Exception('参数错误！', 2000);
        }

        if (is_string($id) && strlen($id) > 10) {
            $id = decrypt($id);
        }
        $act = $this->coinActs->find($id, $this->coinActs->model->fields);
        if ($act['customer_id'] != $customer_id) {
            throw new \Exception('活动异常，请下次再来！', 2000);
        }

        if (!$apirel) {
            return $act;
        }

        return $this->result($act);
    }

}