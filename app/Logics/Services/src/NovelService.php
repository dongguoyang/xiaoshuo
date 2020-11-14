<?php

namespace App\Logics\Services\src;

use App\Logics\Models\ReadNovelLogs;
use App\Logics\Repositories\src\BookStoreRepository;
use App\Logics\Repositories\src\CoinLogRepository;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\CustomerRepository;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\ExtendLinkRepository;
use App\Logics\Repositories\src\IndexPageRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\NovelSectionRepository;
use App\Logics\Repositories\src\PageLogRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\TypeRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\NovelPayStatisticsRepository;
use App\Logics\Repositories\src\UserReadDayRepository;
use App\Logics\Repositories\src\ReadNovelLogsRepository;
use App\Logics\Services\Service;
use App\Logics\Services\src\NovelPayStatisticsService;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\PushmsgTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class NovelService extends Service {
    use OfficialAccountTrait, PushmsgTrait;

    protected $customers;
    protected $novels;
    protected $novelSections;
    protected $novelTypes;
    protected $users;
    protected $readLogs;
    protected $bookStores;
    protected $indexPages;
    protected $commonSets;
    protected $coinLogs;
    protected $extendLinks;
    protected $wechatConfigs;
    protected $domains;
    protected $pageLogs;
    protected $NovelPayStatistics;
    protected $UserReadDay;
    protected $ReadNovelLogs;

    public function Repositories() {
        return [
            'customers'     => CustomerRepository::class,
            'novels'        => NovelRepository::class,
            'novelSections' => NovelSectionRepository::class,
            'novelTypes'    => TypeRepository::class,
            'users'         => UserRepository::class,
            'readLogs'      => ReadLogRepository::class,
            'bookStores'    => BookStoreRepository::class,
            'indexPages'    => IndexPageRepository::class,
            'commonSets'    => CommonSetRepository::class,
            'coinLogs'      => CoinLogRepository::class,
            'extendLinks'   => ExtendLinkRepository::class,
            'wechatConfigs' => WechatConfigRepository::class,
            'domains'       => DomainRepository::class,
            'pageLogs'      => PageLogRepository::class,
            'NovelPayStatistics'=>NovelPayStatisticsRepository::class,
            'UserReadDay'   =>UserReadDayRepository::class,
            'ReadNovelLogs' =>ReadNovelLogsRepository::class,
        ];
    }
    /**
     * 获取用户签到信息
     */
    public function ToIndex() {
        $customer_id = request()->input('cid');
        if (!$customer_id) {
            throw new \Exception('用户异常！', 2000);
        }
        $customer = $this->customers->find($customer_id, ['web_tpl']);
        return redirect('/'.$customer['web_tpl']. '/#/index.html?cid='.$customer_id);
    }
    /**
     * 跳转到 section 信息页面
     */
    public function ToSection() {
        $novel_id = request()->input('novel_id');
        $section = request()->input('section');
        $customer_id = request()->input('customer_id');
        $otherdo   = request()->input('otherdo');
        $subscribe_section   = request()->input('subscribe_section');

        $customer = $this->customers->find($customer_id, ['web_tpl']);

        /*if ($otherdo == 'secondpush') {
            // 执行新用户二次推送
            $sess = $this->loginGetSession(true);
            $this->pushSecondMsg($sess, $novel_id, $customer_id);
        }*/

        // 小说详情 /#/detail/novel-16.html   16 是小说id
        // 小说阅读（章节详情） /#/read/16-0.html     16 是小说id ；0 是小说章节
        return redirect('/'.$customer['web_tpl'].'/#/read/'. $novel_id .'-'. $section .'.html?cid='.$customer_id.'&otherdo='.$otherdo.'&subscribe='.$subscribe_section);
    }
    /**
     * 推广链接跳转
     */
    public function ExtentLink($id) {
        $exl = $this->extendLinks->ExtendInfo($id);
        if (!$exl) {
            throw new \Exception('数据异常，链接不存在！', 2000);
        }
        $sess = $this->loginGetSession();

        $pages = config('frontpage');
        if ($exl['type'] == 2) {
            // 内推页面地址
            $backurl = $pages['section'];
        } else {
            // 外推页面地址
            $backurl = $pages['section_out'];
        }
        //$backurl = str_replace(['{novel_id}', '{section_num}'], [$exl['novel_id'], $exl['novel_section_num']], $backurl);
        //20200904将section_num修改为0
        $backurl = str_replace(['{novel_id}', '{section_num}'], [$exl['novel_id'], 0], $backurl);

        // 以 /home/ 开头表示不是框架；不需要指定模板
        if (strpos($backurl, '/home/') !== 0) {
            $customer = $this->customers->find($exl['customer_id'], ['web_tpl']);
            $backurl = '/' . $customer['web_tpl'] . $backurl;
        }

        $params['subscribe'] = ($exl['must_subscribe'] && $exl['subscribe_section']) ? $exl['subscribe_section'] : 0;
        $params['customer_id']       = $exl['customer_id'];
        $params['cid']               = $exl['customer_id'];
        $params['start']             = $exl['novel_section_num'];
        // 把参数添加到后面
        if (strpos($backurl, '?')) {
            $backurl .= '&' . http_build_query($params);
        } else {
            $backurl .= '?' . http_build_query($params);
        }

        if (!$sess['id']) {
            return redirect(route('h5wechat.login', ['el'=>$exl['id'], 'customer_id'=>$exl['customer_id'], 'cid'=>$exl['customer_id'], 'backurl'=>urlencode($backurl)], true));
        } else {
            return redirect($backurl);
        }

        // 获取推广链接信息

        $exl['extend_link_id']  = $exl['id'];
        $exl['section']         = $exl['novel_section_num'];
        $exl['id']              = $exl['novel_id'];
        $exl['cid']             = $exl['customer_id'];
        if (!$exl['status'] || ($exl['updated_at']+86400*7 < time())) {
            return redirect(route('novel.toindex', ['cid'=>$exl['customer_id']], true));
        }

        return redirect(route('novel.extendpage', $exl, true));
    }
    /**
     * 推广落地页
     */
    public function ExtentPage() {
        $extend_link_id = request()->input('extend_link_id');
        $data = $this->extendLinks->find($extend_link_id, $this->extendLinks->model->fields);
        $sections = $this->novelSections->ExtendLinkSections($data['novel_id'], $data['novel_section_num']);

        $tpl_data = ['data' => $data,'page_conf'=>json_decode($data['page_conf'], 1), 'sections'=>$sections];
        $tpl_data = array_merge($tpl_data, $this->extendLinks->ExtendPageInfos());
        return view('front.novel.extendpage', $tpl_data);
    }
    /**
     * 小说分类获取
     */
    public function NovelTypes() {
        $list = $this->novelTypes->typeTree();

        return $this->result($list);
    }
    /**
     * 小说首页数据
     */
    public function IndexData() {
        $sess = $this->loginGetSession(true);
        $customer_id = (isset($sess['view_cid']) && $sess['view_cid']) ? $sess['view_cid'] : $sess['customer_id'];
        $cahce_key = $customer_id.'_'.$sess['platform_wechat_id'];
        //if(Cache::get($cahce_key)){
        //    $rel = Cache::get($cahce_key);
        //}else{
        $rel = $this->indexPages->IndexData($customer_id, $sess['platform_wechat_id']);
        //    Cache::put($cahce_key,$rel);
        //}
        return $this->result($rel);
    }
    /**
     * 小说首页数据
     */
    public function IndexDataMore() {
        $sess = $this->loginGetSession(true);
        $type = request()->input('type');
        $page = request()->input('page', 1);
        $sex= request()->input('sex');
        if (!$type || !$sex || !in_array($sex, ['man', 'woman'])) {
            throw new \Exception('参数错误！', 2000);
        }
        $customer_id = (isset($sess['view_cid']) && $sess['view_cid']) ? $sess['view_cid'] : $sess['customer_id'];
        $data = $this->indexPages->IndexDataMore($customer_id, $sess['platform_wechat_id'], $type, $page);

        $rel['data'] = $data[$sex];
        $rel['last_page'] = count($rel['data'] ) < $this->indexPages->pagenum ? null : ++$page;

        return $this->result($rel);
    }
    /**
     * 小说信息获取
     */
    public function NovelInfo() {
        $id = request()->input('id');
        $info = $this->novels->NovelInfo($id);
        $section_info = $this->novelSections->First2Last($id);
        if (!$info) {
            throw new \Exception('未查询到数据！', 2000);
        }
        // 章节的首章和最后章信息
        $info['section_first2last'] = $section_info;
        $info['is_bookstore'] = false;

        $sess = $this->loginGetSession();
        if ($sess['id']) {
            $info['is_bookstore'] = $this->checkIsBookStore($id, $sess['id']);
        }

        return $this->result($info);
    }
    /**
     * 小说章节信息获取
     */
    public function NovelSection() {
        $id = request()->input('id'); // 小说ID
        $section = request()->input('section'); // 章节序号
        $start = request()->input('start'); // 指定章节序号
        $page = request()->input('page', 'next'); // 获取的上衣章还是下一章
        $subscribe_section = request()->input('subscribe', 0); // 获取的推广链接设置的关注章节
        if ($section == 0 || !is_numeric($section)) {
            // 获取最近阅读章节序号
            $section = $this->getReadLogSection($id);
            if( $section == 1 && $start && $start !== "undefined"){
                $section = $start;
            }
        }
        $i = 0;
        while ($i < 3) {
            $j = 0;
            while ($j < 3) {
                $info = $this->novelSections->SectionInfo($id, $section);
                if ($info && !$info['content']) {
                    usleep(500000); // 延迟500毫秒再请求章节内容
                    $j++;
                } else {
                    break;
                }
            }
            if ($info && $info['content']) break;
            $i++;
            $section = ($page == 'next') ? ($section + 1) : ($section - 1);
        }
        if (!$info) {
            throw new \Exception('未查询到数据！', 2000);
        }
        //$info['content'] = '<span style="font-weight:700;padding:0;line-height:0.6rem">'.$info['title'].'</span>'.$info['content'];
        //$info['content'] = $info['title'].$info['content'];
        // 添加阅读记录
        $sess = $this->loginGetSession(true);

        $novel = $this->novels->NovelInfo($id);
        if (!$novel) {
            throw new \Exception('未查询到小说数据！', 2000);
        }
        $info['novel_title'] = $novel['title'];
        $info['section_count'] = $novel['sections']; // 总的章节数
        $info['is_bookstore'] = false;

        // 设置了关注章节；就以关注章节为主；没有就默认关注页
        if ($novel['subscribe_section'] !=-1 && (($subscribe_section && $section >= $subscribe_section) || (!$subscribe_section && $section >= $novel['subscribe_section']))) {
            $this->checkSubscribe($sess); // 提示用户关注
        }

        if ($sess['id']) {
            $info['is_bookstore'] = $this->checkIsBookStore($id, $sess['id']);
            $readlog = $this->readLogs->GetReadLog($sess['id'], $info['novel_id']);
            //记录充值阅读记录小说
            /*$map = [
                'date_belongto' => date('Y-m-d'),
                'group_id'      => 1,
                'customer_id'   => $sess['customer_id'],
                'novel_id'=>$info['novel_id']
            ];
            $up_data = [
                'read_num'  => 1,
            ];
            $this->NovelPayStatistics->UpdateColumnNum($map,$up_data);*/
            if ($novel['need_buy_section'] <= $info['num'] && (!$readlog || strpos($readlog['sectionlist'], ','.$info['num'].',')===false )) {
                //$info['content'] = mb_substr($info['content'],0,40,'utf-8');
                $this->buyNovelSection($sess, $novel, $info['num']); // 需要购买阅读
            }
            $pre_content = request()->input('pre_content', 0); // 是否预加载
            $this->readLogs->AddLog($novel, $info, $sess, intval($pre_content)); // 添加阅读记录
            $this->readNovelLogs->AddNumLog($sess['customer_id'],$sess['platform_wechat_id'],$info['novel_id'],$info['num'],['title'=>$novel['title'],'section_title'=>$info['title']]);
        } else if ($novel['need_buy_section'] <= $info['num']) {
            throw new \Exception('请登录后操作！', 803);
        }
        if (!isset($info['content']) || !$info['content']) {
            $this->pageLogs->IncColNum('section_null'); // 统计内容为空的次数
        }
        $info['data_info'] = $_POST;
        return $this->result($info);
    }
    /**
     * 小说章节信息获取；没有正文内容
     */
    public function SectionInfo() {
        $id = request()->input('id'); // 小说ID
        $section = request()->input('section'); // 章节序号
        $info = $this->novelSections->SectionInfo($id, $section);
        $buy_section_coin = intval($this->commonSets->values('novel', 'buy_section_coin'));
        //显示正文内容的前80个字节
        $info['content'] = mb_substr($info['content'],0,80);
        $info['content'] = str_replace('<p>','',$info['content']);
        $info['content'] = str_replace('</p>','',$info['content']);
        $info['content'] = $info['content'].'......';
        $info['buy_section_coin'] = $buy_section_coin;
        //unset($info['content']);

        return $this->result($info);
    }
    /**
     * 获取最近阅读章节序号
     * @param int $novel_id
     * @return int $section_num
     */
    private function checkIsBookStore($novel_id, $user_id) {
        $bookstore = false;
        // 查询最近阅读的章节
        if ($user_id > 0) {
            $info = $this->bookStores->GetBookStoreLog($user_id, $novel_id);
            if ($info && $info['status']) {
                $bookstore = true;
            }
        }

        return $bookstore;
    }
    /**
     * 获取最近阅读章节序号
     * @param int $novel_id
     * @return int $section_num
     */
    private function getReadLogSection($novel_id) {
        $sess = $this->loginGetSession();

        $section = 1;
        // 查询最近阅读的章节
        if ($sess['id'] > 0) {
            $info = $this->readLogs->GetReadLog($sess['id'], $novel_id);
            if ($info) {
                $section = $info['end_section_num'];
            }
        }

        return $section;
    }
    /**
     * 关注阅读
     * @param array $user
     */
    private function checkSubscribe($user) {
        if (!$user['id']) {
            throw new \Exception('请登录后操作！', 803);
        }
        if ($user['customer_id'] == $user['view_cid']) {
            // 登录用户就是当前公众号用户
            if (!$user['subscribe']) {
                throw new \Exception('请关注公众号后再阅读！', 804);
            }
        } else {
            // 用户是子公众号进入的
            $sub_user = $this->users->SubUserCacheInfo($user['id'], $user['view_cid']);
            if(empty($sub_user)){
                throw new \Exception('请关注公众号后再阅读！', 804);
            }
            if (!$sub_user['subscribe']) {
                throw new \Exception('请关注公众号后再阅读！', 804);
            }
        }

    }
    /**
     * 检测用户是否可以阅读本章节小说
     * @param array $sess
     * @param array $novel
     * @param int $section_num
     */
    private function checkEnRead($sess) {
        if (!$sess['id']) {
            throw new \Exception('请登录后操作！', 803);
        }
        if ($sess['vip_end_at'] && $sess['vip_end_at'] > time()) {
            // 如果vip，并且未过期；则直接阅读即可
            return true;
        }
        $buy_section_coin = intval($this->commonSets->values('novel', 'buy_section_coin'));
        if ($sess['balance'] < $buy_section_coin) {
            throw new \Exception('书币余额不足，请充值！', 802);
        }

        return $buy_section_coin;
    }
    /**
     * 购买付费章节
     * @param array $sess
     * @param array $novel
     * @param int $section_num
     */
    private function buyNovelSection($sess, $novel, $section_num) {
        $buy_section_coin = $this->checkEnRead($sess);
        $res = (new NovelPayStatisticsService())->UserNovelPay($sess,$novel['id']);
        if($res){
            //记录充值充值记录小说
            $map = [
                'date_belongto' => date('Y-m-d'),
                'group_id'      => 1,
                'customer_id'   => $sess['customer_id'],
                'novel_id'      => $novel['id']
            ];
            $up_data = [
                'pay_num'  => 1,
            ];
            $this->NovelPayStatistics->UpdateColumnNum($map,$up_data);
        }
        if ($sess['vip_end_at'] && $sess['vip_end_at'] > time()) {
            // 如果vip，并且未过期；则直接阅读即可
            return true;
        }

        try
        {
            DB::beginTransaction();
            $data = [
                'customer_id'           => $sess['customer_id'],
                'platform_wechat_id'    => $sess['platform_wechat_id'],
                'user_id'               => $sess['id'],
                'type'                  => 3,
                'type_id'               => $novel['id'],
                'coin'                  => $buy_section_coin * -1,
                'balance'               => $sess['balance'] - $buy_section_coin,
                'title'                 => '阅读收费章节',
                'desc'                  => $novel['title'].'；第'.$section_num.'章',
                'status'                => 1,
            ];
            $this->coinLogs->create($data);//添加积分日志

            $sess['balance'] -= $buy_section_coin;// 更新用户缓存
            $this->users->UserToCache($sess);
            DB::commit();
            return true;
        }
        catch (\Exception $e)
        {
            DB::rollBack();
            throw new \Exception('用户阅读购买记录添加失败！'.$e->getMessage(), $e->getCode());
        }
    }
    /**
     * 小说章节列表获取
     */
    public function SectionList() {
        $id = request()->input('id');
        $page = request()->input('page', 1);
        $order = request()->input('order', 'asc');
        if (!in_array($order, ['asc', 'desc', 'ASC', 'DESC'])) {
            throw new \Exception('排序字段异常！', 2000);
        }

        $info = $this->novelSections->SectionList($id, $page, $order);

        if (!$info) {
            throw new \Exception('未查询到数据！', 2000);
        }

        $novel = $this->novels->NovelInfo($id);
        $rel['data'] = $info;
        $rel['last_page'] = count($info) < $this->novelSections->pagenum ? null : ++$page;
        $rel['section_count'] = $novel['sections']; // 总的章节数
        $rel['need_buy_section'] = $novel['need_buy_section']; // 开始付费的章节
        $rel['subscribe_section'] = $novel['subscribe_section']; // 强制关注章节

        return $this->result($rel);
        // return $this->result($info);
    }

    /**
     * 小说查询
     */
    public function SearchNovel()
    {
        $search = request()->input();

        if (isset($search['default']) && $search['default']) {
            // $rel['title_list'] = $this->novels->SearchRecommend();
            // $rel['novel_list'] = $this->novels->IsRecommend();
            $sess = $this->loginGetSession(true);
            $customer_id = $sess['id'] > 0 ? ((isset($sess['view_cid']) && $sess['view_cid']) ? $sess['view_cid'] : $sess['customer_id']) : 0;
            $platform_wechat_id = $sess['id'] > 0 ? $sess['platform_wechat_id'] : 0;
            $rel = $this->indexPages->SearchDefaultData($customer_id, $platform_wechat_id);
            return $this->result(['data'=>$rel, 'last_page'=>null]);
        } else {
            ksort($search);
            $novel_list = $this->novels->Search($search);
            $rel['title_list'] = []; // 查询的时候关键字为空
            $rel['novel_list'] = $novel_list; // 查询结果小说数据

            $last_page = (count($novel_list) < $this->novels->pagenum ? null : (isset($search['page']) ? ++$search['page'] : 2));
            if (isset($search['order']) && $search['order']) {
                $last_page = null;
            }

            return $this->result(['data'=>$rel, 'last_page'=>$last_page]);
            if (isset($search['page'])) {
                return $this->result(['data'=>$rel, 'last_page'=>(count($novel_list) < $this->novels->pagenum ? null : ++$search['page'])]);
            } else {
                return $this->result(['data'=>$rel]);
            }
        }
        // return $this->result($rel);
    }

    /**
     * 小说书架图书
     */
    public function BookStore()
    {
        $recommend = request()->input('recommend', false);
        $page = request()->input('page', 1);

        $sess = $this->loginGetSession(true);

        $customer_id = $sess['id'] > 0 ? ((isset($sess['view_cid']) && $sess['view_cid']) ? $sess['view_cid'] : $sess['customer_id']) : 0;
        $platform_wechat_id = $sess['id'] > 0 ? $sess['platform_wechat_id'] : 0;

        $recommend_list = $this->indexPages->bookStoresRecommendData($platform_wechat_id, $customer_id); // 获取书架里面的推荐小说

        if (!$recommend) {
            $list = $this->bookStores->UserBooks($sess['id'], $page);
            $rel['book_store'] = $list;
            $rel['recommend'] = $recommend_list;
            return $this->result(['data'=>$rel, 'last_page'=>(count($list) < $this->bookStores->pagenum ? null : ++$page)]);
        } else {
            $rel = $recommend_list;
            return $this->result($rel);
        }
        // return $this->result($rel);
    }
    /**
     * 删除小说书架图书
     */
    public function DelBookStore()
    {
        $input = request()->input(); // id 列表
        if (!isset($input['ids']) || !$input['ids']) {
            throw new \Exception('ID列表参数异常！', 2000);
        }
        $sess = $this->loginGetSession();

        if (IsJson($input['ids'])) {
            $input['ids'] = json_decode($input['ids'], 1);
        }
        if ($input['ids']) {
            $this->bookStores->model->where('user_id', $sess['id'])->whereIn('novel_id', $input['ids'])->update(['status'=>0]);
            $this->bookStores->ClearCache($sess['id'], $input['ids']);

            return $this->result(null, 0, '删除成功！');
        } else {
            return $this->result(null, 0, '未找到删除项！');
        }
    }

    /**
     * 小说阅读历史记录
     */
    public function ReadList()
    {
        $page = request()->input('page', 1);

        $sess = $this->loginGetSession();

        $list = $this->readLogs->UserList($sess['id'], $page);

        return $this->result(['data'=>$list, 'last_page'=>(count($list) < $this->readLogs->pagenum ? null : ++$page)]);
        // return $this->result($list);
    }
    /**
     * 删除小说阅读历史记录
     */
    public function DelReadLog()
    {
        $input = request()->input(); // id 列表
        if (!isset($input['ids']) || !$input['ids']) {
            throw new \Exception('ID列表参数异常2！', 2000);
        }
        $sess = $this->loginGetSession();
        if (IsJson($input['ids'])) {
            $input['ids'] = json_decode($input['ids'], 1);
        }
        if ($input['ids']) {
            $this->readLogs->model->where('user_id', $sess['id'])->whereIn('novel_id', $input['ids'])->update(['status'=>0]);
            $this->readLogs->ClearCache($sess['id']); // 清除缓存

            return $this->result(null, 0, '删除成功！');
        } else {
            return $this->result(null, 0, '未找到删除项！');
        }
    }
    /**
     * 查询余额是否足够下一章阅读
     */
    public function BalanceEnough() {
        $novel_id = request()->input('id');
        $section = request()->input('section');
        if (!$novel_id) {
            throw new \Exception('参数异常5！', 2000);
        }
        if ($section == 0) {
            // 获取最近阅读章节序号
            $section = $this->getReadLogSection($novel_id);
        }

        $novel = $this->novels->NovelInfo($novel_id);
        $sess = $this->loginGetSession(true);

        $readlog = $this->readLogs->GetReadLog($sess['id'], $novel_id);
        if ($novel['need_buy_section'] <= $section && (!$readlog || strpos($readlog['sectionlist'], ','. $section .',')===false )) {
            // 需要购买阅读
            try {
                $this->checkEnRead($sess);
                return $this->result(['en_read'=>true, 'balance'=>$sess['balance']]);
            } catch (\Exception $e) {
                return $this->result(['en_read'=>false, 'balance'=>$sess['balance']]);
            }
        }
        return $this->result(['en_read'=>true, 'balance'=>$sess['balance']]);
    }
    /**
     * 章节阅读页面的推荐小说
     */
    public function SectionRecommend() {
        $novel_id = request()->input('id');
        if (!$novel_id) {
            throw new \Exception('参数异常！', 2000);
        }
        $novel = $this->novels->NovelInfo($novel_id);
        if (!$novel) {
            throw new \Exception('小说不存在！', 2000);
        }

        $list = $this->novels->SectionRecommendList($novel['type_ids']);// 获取推荐列表
        $offset = ($novel_id % intval($this->novels->pagenum / 3)) * 3;
        $rel = array_slice($list, $offset, 3); // 只获取3步小说
        if (!$rel) {
            $rel[0] = $list[array_rand($list)];
        }
        return $this->result($rel);
    }
    /**
     * 小说获取接口
     * returnType [1=>'oss地址返回' 2=>'内容返回']
     */
    public function GetNovel(){
        $input=request()->input();
        if(empty($input['type']) || empty($input['start']) ||empty($input['end']) || empty($input['returnType']) ||empty($input['page'])){
            throw new \Exception('参数有误',101);
        }
        $startTime=$input['start'];
        $endTime=$input['end'];
        if($input['type'] =='all'){
            $whereO=[
                'status'=>1
            ];
        }else{
            $whereO=[
                'type_ids'=>$input['type'],
                'status'=>1
            ];
        }

        $novelData=$this->novels->model->where($whereO)->whereBetween('updated_at',[$startTime,$endTime])->get(); //小说
        if(!count($novelData)){
            return $this->result([],200,'success');
        }
        $novelData=$novelData->toarray();
        $dxr=[];
        $num=$input['returnType'] == 1?100:5;
        foreach ($novelData as $key=>$vs){
            $res=$this->novelSections->model->where('novel_id',$vs['id'])->paginate($num)->toarray();
            if(!$res){ //表示没有章节了
                continue;
            }
            $res=$res['data'];
            foreach ($res as $kres=>$vres){
                $name=base64_encode(json_encode($vs));
                if($input['returnType']==1) { //oss
                    $dxr[$name][$vres['num']] = $vres;
                }else if($input['returnType'] ==2){ //content

                    $dxr[$name][$vres['num']]=$vres;
                    $dxr[$name][$vres['num']]['content']=@file_get_contents($vres['content']);
                }else{  //

                }
            }unset($kres);unset($vres);
        }unset($key);unset($vs);
        return $this->result($dxr,200,'success');
    }

    /**
     * 小说信息获取接口
     */
    public function NovelSyncApi(){
        $input = request()->input();
        if (!isset($input['passwd']) || $input['passwd']!='890432jfkds8jh32hjkfdsjkjjkl21iuy8idhsajknfy241nmfdjksa32212u7e28winj') {
            return $this->result([], '403', '接口异常！');
        }

        if (isset($input['num']) && strlen($input['num']) > 0 && ((isset($input['novel_id']) && $input['novel_id']) || (isset($input['title']) && $input['title']))) {
            if (isset($input['novel_id']) && $input['novel_id']) {
                $novel_id = $input['novel_id'];
            } else {
                $map = $input;
                unset($map['passwd'], $map['num']);
                foreach ($map as $k=>$v) {
                    if (!$v) unset($map[$k]);
                }
                $novel = $this->novels->findByMap($map, ['id']);
                if (!$novel || !$map) return $this->result([], '404', '小说不存在！');
                $novel_id = $novel['id'];
            }
            return $this->syscSectionData($input, $novel_id);
        } else {
            return $this->syscNovelData($input);
        }
    }
    private function syscNovelData($input) {
        if ( empty($input['start']) || empty($input['end']) || empty($input['page']) ) {
            throw new \Exception('查询时间/参数有误',2001);
        }
        $startTime  = is_numeric($input['start']) ? $input['start'] : strtotime($input['start']);
        $endTime    = is_numeric($input['end']) ? $input['end'] : strtotime($input['end']);

        $map = [['id', '>', 0]];
        if (isset($input['type']) && $input['type'] !='all') {
            $map[] = ['type_ids', $input['type']];
        }

        $rows = 100;
        $novelData = $this->novels->model
            ->where($map)
            ->whereBetween('updated_at',[$startTime, $endTime])
            ->orderBy('id', 'asc')
            ->offset(($input['page'] - 1) * $rows)
            ->limit($rows)
            ->get(); // 小说
        $novelData = $this->novels->toArr($novelData);

        return $this->result($novelData);
    }
    private function syscSectionData($input, $novel_id) {
        if (
            !isset($input['num']) || strlen($input['num']) == 0
        ) {
            throw new \Exception('参数有误',101);
        }
        $map = [
            ['novel_id', $novel_id],
            ['num', '>', $input['num']],
        ];
        $sections = $this->novelSections->model->where($map)->orderBy('num')->limit(500)->get();
        $sections = $this->novelSections->toArr($sections);

        return $this->result($sections);
    }
    /**
     * 小说章节内容获取
     */
    public function NovelSectionContent(){
        $input = request()->input();
        if (!isset($input['passwd']) || $input['passwd']!='890432jfkds8jh32hjkfdsjkjjkl21iuy8idhsajknfy241nmfdjksa32212u7e28winj') {
            return $this->result([], '403', '接口异常！');
        }

        $num = $input['num'];
        unset($input['num'], $input['passwd']);
        $novel = $this->novels->findByMap($input, ['id']);
        if (!$novel) {
            return $this->result([], '404', '没有找到对应小说！');
        }

        $section = $this->novelSections->findByMap([
            ['novel_id', $novel['id']],
            ['num', $num],
        ], ['content']);
        if (!$section) {
            return $this->result([], '404', '没有找到对应小说章节！');
        }

        $i = 0;
        while ($i < 3) {
            $content = @file_get_contents($section['content']);
            if ($content) break;
            sleep(2);
            $i++;
        }
        return $this->result($content);
    }

}