<?php

namespace App\Admin\Controllers;

use App\Admin\Models\CommonSet;
use App\Admin\Models\InteractMsg;
use App\Admin\Models\MoneyBtn;
use App\Admin\Models\Novel;
use App\Admin\Models\PlatformWechat;
use App\Admin\Models\TemplateMsg;
use App\Admin\Models\Wechat;
use App\Admin\Models\WechatConfig;
use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Traits\ApiResponseTrait;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\InfoBox;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Cache;


class WechatConfigsController extends AdminController
{
    use OfficialAccountTrait, ApiResponseTrait;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '开放平台信息';
    /**
     * 公众号信息配置首页
     */
    public function index(Content $content)
    {
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('公众号设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $wechat = Wechat::where('customer_id', $customer['id'])->where('status', 1)->first();
        $wtypes = ['订阅号', '升级的订阅号', '服务号']; //0代表订阅号，1代表由历史老帐号升级后的订阅号，2代表服务号
        $content->view('admin.wechatconfigs.index', ['wechat'=>$wechat, 'wtypes'=>$wtypes]);

        return $content;
    }
    /**
     * 关注回复配置
     */
    public function subscribe(Content $content)
    {
        if (request()->method() == 'POST') {
            if (WechatConfig::where('id', request()->input('id'))->update(['subscribe_msg'=>request()->input('subscribe_msg')])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('公众号关注回复设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $wechat['subscribe_content'] = json_decode($wechat['subscribe_content'], 1);

        $content->view('admin.wechatconfigs.subscribe', ['wechat'=>$wechat, 'url'=>route('novel.toindex', ['cid'=>$wechat['customer_id']])]);

        return $content;
    }
    private function getConfWechat() {
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $map = [['customer_id', $customer['id']]];

        //if ($customer['pid']) {
        $map[] = ['status', 1];
        //}
        $wechat = Wechat::where($map)->first();
        $wechat = WechatConfig::where('customer_id', $customer['id'])->where('platform_wechat_id', $wechat['id'])->first();

        return $wechat;
    }
    /**
     * 智能推送配置
     */
    public function pushconf(Content $content)
    {
        $wechat = $this->getConfWechat();
        if (request()->method() == 'POST') {
            $keys = ['readed8h', 'day_read', 'first_recharge', 'sign', 'nopay', 'subs12h'];
            // 智能推送配置 {"day_read":"1","readed8h":"1","nopay":0,"sign":0,"first_recharge":0}
            // 1继续阅读提醒；2首充优惠图文推送；3签到图文推送；4未支付提醒；
            $data = request()->input('pushconf');
            $day_read = request()->input('day_read');
            $subs12h = request()->input('subs12h');
            $data['day_read'] = (isset($data['day_read']) && $data['day_read'] && $day_read) ? $day_read : 0;
            $data['subs12h'] = (isset($data['subs12h']) && $data['subs12h'] && $subs12h) ? $subs12h : 0;

            foreach ($keys as $k=>$v) {
                $data[$v] = isset($data[$v]) ? $data[$v] : 0;
                /*if ($data[$v]) {
                    if (!TemplateMsg::where([['type', $k], ['customer_id', $wechat['customer_id']], ['platform_wechat_id', $wechat['platform_wechat_id']]])->first()) {
                        $types = ['继续阅读提醒','继续阅读提醒','首充优惠图文推送','签到图文推送','未支付提醒'];
                        return ['code'=>2000, 'msg'=>'保存失败，您还没有建立 '.$types[$k].' 相关模板消息！'];
                    }
                }*/
            }
            if (WechatConfig::where('id', request()->input('id'))->update(['pushconf'=>json_encode($data)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('智能推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '智能推送配置']
        );
        // 直接渲染视图输出，Since v1.6.12
        if ($wechat && $wechat->pushconf) {
            $wechat->pushconf = json_decode($wechat->pushconf, 1);
        }
        $subscribe_msg_12h=$wechat->subscribe_msg_12h;
        if(!empty($subscribe_msg_12h)){
            $subscribe_msg_12h=json_decode($subscribe_msg_12h,1);
        }else{
            $subscribe_msg_12h=[
                'title'=>['没有设置!!!','没有设置!!!','没有设置!!','没有设置!!']
            ];
        }

        $content->view('admin.wechatconfigs.pushconf', ['wechat'=>$wechat,'subscribe_msg_12h'=>$subscribe_msg_12h]);

        return $content;
    }
    /**
     * 每日推送配置
     */
    public function dailypush(Content $content)
    {
        $wechat = $this->getConfWechat();
        if (request()->method() == 'POST') {
            // 6点；12点；18点；21点；23点
            $keys = ['h6', 'h12', 'h18', 'h21', 'h23'];
            // 智能推送配置 {"h6":"1","h12":"1","h18":0,"h21":0,"h23":0} 1表示开启推送；0关闭
            $data = request()->input('daily_push');
            foreach ($keys as $k=>$v) {
                $data[$v] = isset($data[$v]) ? $data[$v] : 0;
            }
            if (WechatConfig::where('id', request()->input('id'))->update(['daily_push'=>json_encode($data)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('每日推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '每日推送配置']
        );
        // 直接渲染视图输出，Since v1.6.12
        if ($wechat && $wechat->daily_push) {
            $wechat->daily_push = json_decode($wechat->daily_push, 1);
        }
        $moneyBtn = MoneyBtn::where([['status', 1], ['default',7]])->first();
        $content->view('admin.wechatconfigs.dailypush', ['wechat'=>$wechat, 'moneyBtn'=>$moneyBtn]);

        return $content;
    }
    /**
     * 直接关注的推送配置
     */
    public function searchsub(Content $content)
    {
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注的推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $account = Wechat::select(['name'])->find($wechat['platform_wechat_id']);
        $search_sub = json_decode($wechat['search_sub'], 1);
        if (request()->method() == 'POST') {
            $search_sub['switch'] = request()->input('switch', 0);
            if (WechatConfig::where('id', request()->input('id'))->update(['search_sub'=>json_encode($search_sub, JSON_UNESCAPED_UNICODE)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        $novels = [];
        if (isset($search_sub['nid'])) {
            $novels = Novel::where('status', '>', 0)->whereIn('id', $search_sub['nid'])->orderBy('id', 'desc')->select(['id', 'title'])->get();
        }
        $content->view('admin.wechatconfigs.search_subscribe', ['wechat'=>$wechat, 'account'=>$account, 'novels'=>$novels, 'search_sub'=>$search_sub]);

        return $content;
    }

    /**
     *   新直接关注的推送配置
     */
    public function newsearchsub(Content $content){
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注的推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $account = Wechat::select(['name'])->find($wechat['platform_wechat_id']);
        $search_sub = json_decode($wechat['search_sub'], 1);
        if (request()->method() == 'POST') {
            $search_sub['switch'] = request()->input('switch', 0);
            if (WechatConfig::where('id', request()->input('id'))->update(['search_sub'=>json_encode($search_sub, JSON_UNESCAPED_UNICODE)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        $novels = [];
        if (isset($search_sub['nid'])) {
            $novels = Novel::where('status', '>', 0)->whereIn('id', $search_sub['nid'])->orderBy('id', 'desc')->select(['id', 'title'])->get();
        }
        $content->view('admin.wechatconfigs.search_subscribe', ['wechat'=>$wechat, 'account'=>$account, 'novels'=>$novels, 'search_sub'=>$search_sub]);

        return $content;
    }
    /**
     * 直接关注推送配置
     */
    public function searchSubEdit(Content $content)
    {
        if (request()->method() == 'POST') {
            return $this->searchSubSave();
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注回复设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $search_sub = json_decode($wechat['search_sub'], 1);
        $novels = Novel::where('status', '>', 0)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        $content->view('admin.wechatconfigs.searchsub_edit', ['wechat'=>$wechat, 'novels'=>$novels, 'search_sub'=>$search_sub]);

        return $content;
    }
    public function searchSubSave() {
        $data = request()->input();
        $num = 4;
        if (count($data['nid']) != $num || count($data['title'])!=$num || count($data['snum'])!=$num) {
            return ['code'=>2000, 'msg'=>'数据异常，请补充完整！'];
        }
        if (count(array_unique($data['nid'])) != $num) {
            return ['code'=>2000, 'msg'=>'数据异常，请选择不重复的小说！'];
        }

        $id = $data['id'];unset($data['id']);
        $data['switch'] = 1;
        if (WechatConfig::where('id', $id)->update(['search_sub'=>json_encode($data)])) {
            return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'searchsub'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }
    /**
     * 直接关注的推送配置
     */
    public function searchsub0(Content $content)
    {
        $wechat = $this->getConfWechat();
        if (request()->method() == 'POST') {
            $data['switch'] = request()->input('switch');
            $data['title'] = request()->input('title');
            $data['link'] = request()->input('link');
            if (WechatConfig::where('id', request()->input('id'))->update(['search_sub'=>json_encode($data)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注回复推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '每日推送配置']
        );
        // 直接渲染视图输出，Since v1.6.12
        if ($wechat && $wechat->search_sub) {
            $wechat->search_sub = json_decode($wechat->search_sub, 1);
        }

        $content->view('admin.wechatconfigs.search_sub', ['wechat'=>$wechat]);

        return $content;
    }
    // 给所有关注用户添加标签
    private function doAllUserTag($customer_id, $tag_id, $type = 'batch-tag') {
        $next_openid = '';
        while (true) {
            $rel = $this->GetUserList($customer_id, $next_openid);
            $openids = isset($rel['data']['openid']) ? $rel['data']['openid'] : [];
            if (!$openids) break;
            $list = array_chunk($openids, 50);
            foreach ($list as $k=>$users) {
                $data = [
                    'openid_list' => $users,
                    'tagid' => $tag_id,
                ];
                $this->UserTagManage($customer_id, $type, $data);
            }
            if (!isset($rel['next_openid']) || !$rel['next_openid']) break;
            $next_openid = $rel['next_openid'];
        }
    }
    /**
     * 生成用户标签
     */
    public function userTags(Content $content)
    {
        $wechat = $this->getConfWechat();
        if (request()->method() == 'POST') {
            set_time_limit(0);
            $switch = request()->input('switch', 0);
            try {
                if ($switch == 1) {
                    if ($wechat->user_tags) {
                        return ['code'=>2000, 'msg'=>'标签已被创建！'];
                    }
                    $data = [
                        'tag'   => [
                            'name'  => '已关注',
                        ]
                    ];
                    $this->deleteUserTag($wechat); // 删除老标签
                    $rel = $this->TagsManage($wechat['customer_id'], 'create', $data);
                    $this->doAllUserTag($wechat['customer_id'], $rel['tag']['id']); // 给所有关注用户打标签
                    if (WechatConfig::where('id', request()->input('id'))->update(['user_tags'=>json_encode($rel, JSON_UNESCAPED_UNICODE)])) {
                        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                        $this->productWechatMenus($customer, 3);
                        return ['code'=>0, 'msg'=>'创建标签成功！'];
                    } else {
                        return ['code'=>2000, 'msg'=>'创建标签失败！'];
                    }
                } else {
                    $rel = $this->deleteUserTag($wechat);
                    if ($rel['code'] == 200 && WechatConfig::where('id', request()->input('id'))->update(['user_tags'=>''])) {
                        return ['code'=>2000, 'msg'=>'标签异常，已删除老标签！'];
                    }
                    return $rel;
                }
            } catch (\Exception $e) {
                return ['code'=>2002, 'msg'=>$e->getMessage() . '___' . $e->getCode()];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('用户标签配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '用户标签配置']
        );
        // 直接渲染视图输出，Since v1.6.12

        $content->view('admin.wechatconfigs.user_tags', ['wechat'=>$wechat]);

        return $content;
    }
    private function deleteUserTag($wechat) {
        $tags = $this->TagsManage($wechat['customer_id'], 'get');
        if (!isset($tags['tags']) || !$tags['tags']) return ['code'=>2000, 'msg'=>'删除标签失败！'];
        $tags = $tags['tags'];
        foreach ($tags as $tag) {
            // $tag  {"id":2,"name":"星标组","count":0}
            if ($tag['name'] == '已关注') {
                // {"tag":{"id":100,"name":"已关注"}}
                //$data = json_decode($wechat->user_tags, 1);
                /*$data = [
                    'tag'   => [
                        'id'  => 123,
                    ]
                ];*/
                $this->doAllUserTag($wechat['customer_id'], $tag['id'], 'batch-un-tag'); // 给所有关注用户取消标签
                $data = [
                    'tag' => [
                        'id'    => $tag['id']
                    ]
                ];
                $rel = $this->TagsManage($wechat['customer_id'], 'delete', $data);
                if ($rel['errcode'] == 0) {
                    if (WechatConfig::where('id', request()->input('id'))->update(['user_tags'=>''])) {
                        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
                        $this->productWechatMenus($customer, 3);
                        return ['code'=>0, 'msg'=>'删除标签成功！'];
                    }
                }
                return ['code'=>2000, 'msg'=>'删除标签失败！'];
            }
        }
        return ['code'=>200, 'msg'=>'没有已关注标签 - 删除标签失败！'];
    }
    /**
     * 关键字回复配置
     */
    public function keyword(Content $content)
    {
        if (request()->method() == 'POST') {
            if (WechatConfig::where('id', request()->input('id'))->update(['subscribe_msg'=>request()->input('subscribe_msg')])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('关键词回复管理');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '关键词回复管理']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $content->view('admin.wechatconfigs.keyword', ['wechat'=>$wechat, 'url'=>route('novel.toindex', ['cid'=>$wechat['customer_id']])]);

        return $content;
    }
    /**
     * 互动消息配置
     * @param int $id 互动消息ID
     * @param int $tester_id 测试用户ID
     * @param array $interacInfo 测试互动消息信息所需内容
     *              ['platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type']
     */
    public function interactMsg($id, $tester_id=0, $interacInfo=[])
    {
        try
        {
            if ($tester_id > 0) {
                // 发送执行测试任务
                $plat_wechat = PlatformWechat::select(['id', 'customer_id'])->find($interacInfo['platform_wechat_id']);
                \App\Jobs\InteractMsg::dispatch($plat_wechat, /*$users,*/ $interacInfo, $tester_id); // 延迟执行任务

                return $this->result([]);
            }

            $interacInfo = InteractMsg::select([ 'id', 'platform_wechat_id', 'name', 'type', 'content', 'send_to', 'send_type', 'send_at', 'status'])->find($id);
            if (!$interacInfo) {
                throw new \Exception('没有互动任务信息！', 2000);
            }
            $plat_wechat = PlatformWechat::select(['id', 'customer_id'])->find($interacInfo['platform_wechat_id']);

            $userRep = new UserRepository();
            $users = $userRep->NoteInteractInfo($plat_wechat);
            if (!$users) {
                throw new \Exception('没有最近活跃用户！', 2000);
            }
            \App\Jobs\InteractMsg::dispatch($plat_wechat, /*$users,*/ $interacInfo)->delay(Carbon::createFromTimestamp($interacInfo['send_at'])); // 延迟执行任务

            return $this->result([]);
        }
        catch (\Exception $e)
        {
            return $this->result(['file'=>$e->getFile(), 'line'=>$e->getLine()], $e->getCode(), $e->getMessage());
        }
    }
    /**
     * 菜单配置
     */
    public function menulist(Content $content)
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        if (request()->method() == 'POST') {
            try
            {
                return $this->productWechatMenus($customer);
                //return $this->productNewWechatMenus($customer);
            }
            catch (\Exception $e)
            {
                return ['code'=>$e->getCode(), 'msg'=>$e->getMessage()];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('菜单设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '菜单设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        if ($wechat && $wechat->menu_list) {
            $wechat->menu_list = json_decode($wechat->menu_list, 1);
        }
        $content->view('admin.wechatconfigs.menulist', ['wechat'=>$wechat]);

        return $content;
    }

    /**
     * 新菜单配置
     */
    public function newmenulist(Content $content)
    {
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();

        if (request()->method() == 'POST') {
            try
            {
                return $this->productNewWechatMenus($customer);
            }
            catch (\Exception $e)
            {
                return ['code'=>$e->getCode(), 'msg'=>$e->getMessage()];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('菜单设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '菜单设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        if ($wechat && $wechat->menu_list) {
            $wechat->menu_list = json_decode($wechat->menu_list, 1);
        }
        $content->view('admin.wechatconfigs.newmenulist', ['wechat'=>$wechat]);

        return $content;
    }

    /**
     * 菜单编辑
     */
    public function newmenulistEdit(Content $content)
    {
        if (request()->method() == 'POST') {
            return $this->productNewWechatMenus();
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注回复设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $wechat['menu_list'] = json_decode($wechat['menu_list'],true);
        $content->view('admin.wechatconfigs.newmenulist_edit', ['wechat'=>$wechat]);
        return $content;
    }

    //中部菜单栏设置
    public function centreMenuList(Content $content){
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('用户自定义推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $data = json_decode($wechat['centre_menu'], 1);
        if (request()->method() == 'POST') {
            unset($data['switch']);
            if(!$data && request()->input('switch')==1) {
                WechatConfig::where('id', request()->input('id'))->update(['centre_menu'=>json_encode(['switch'=>0], JSON_UNESCAPED_UNICODE)]);
                return ['code'=>2000, 'msg'=>'没有配置小说，开启失败！'];
            }
            $data['switch'] = request()->input('switch', 0);
            if (WechatConfig::where('id', request()->input('id'))->update(['centre_menu'=>json_encode($data, JSON_UNESCAPED_UNICODE)])) {
                $menu_list = json_decode($wechat->menu_list, 1);
                if($data['switch'] == 1){  //生成新的菜单栏
                    $domainRep = new DomainRepository();
                    $host = $domainRep->randOne(4, $wechat['customer_id']);
                    if($data){
                        $new_center_menu = [];
                        $new_center_menu['name'] = $data['t'];
                        $new_center_menu['sub_button'] = [];
                        foreach ($data['m'] as $key=>$value){
                            if(empty($value['name'])) continue;
                            if(empty($value['novel_id'])) continue;
                            $button = [
                                'name'=>$value['name'],
                                'type'=>'view',
                                'url'=>$host.'/jiaoyu/weivip/'.$value['url']
                            ];
                            $new_center_menu['sub_button'][] =$button;
                        }
                    }
                    $menu_list['menu']['button'][1] =  $new_center_menu;
                    $rel = $this->ProductWechatMenu($wechat['customer_id'], $menu_list['menu']);
                }else{ //还原菜单栏
                    $rel = $this->ProductWechatMenu($wechat['customer_id'], $menu_list['menu']);
                }
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        $novels = [];
        $content->view('admin.wechatconfigs.centre_menu', ['wechat'=>$wechat, 'novels'=>$novels, 'data'=>$data]);
        return $content;
    }


    public function centreMenuView(Content $content){
        if (request()->method() == 'POST') {
            return $this->centreSave();
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注回复设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $data = json_decode($wechat['centre_menu'], 1);
        $novels = Novel::where('status', '>', 0)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        $content->view('admin.wechatconfigs.centre_menu_edit', ['wechat'=>$wechat,'novels'=>$novels,'data'=>$data]);
        return $content;
    }

    private function productWechatMenus($customer, $menu_list_type = 0) {
        $menu_list['type'] = $menu_list_type>0 ? $menu_list_type : request()->input('menu_list_type', 0);
        $menu_list['menu'] = $this->menuInfo($menu_list['type'], $customer['id'], $customer['web_tpl']);
        // 查询是否固定菜单
        $must_menu = CommonSet::where('type', 'wechat_menu')->where('name', 'cid-'.$customer['id'])->where('status', 1)->select(['id', 'value'])->first();
        if ($must_menu && $must_menu['value']) {
            $must_menu = json_decode($must_menu['value'], 1);
            if ($must_menu) {// 固定菜单
                $menu_list['menu'] = $must_menu;
            }
        }
        $wechat_conf = $this->getConfWechat();
        $tags = json_decode($wechat_conf->user_tags, 1);

        if ($tags && isset($tags['tag']['id'])) {
            $domainRep = new DomainRepository();
            $host = $domainRep->randOne(4, $customer['id']);
            $notag_menuname = CommonSet::where('type', 'menu')->where('name', 'notag_menuname')->where('status', 1)->select(['id', 'value'])->first();
            $menuname = (isset($notag_menuname['value']) && $notag_menuname['value']) ? $notag_menuname['value'] : '点击上方(👆)关注继续阅读';
            $menu = [
                'button' => [
                    [
                        'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                        'name'=>$menuname,
                        'url'=>$host . '/tousu/autoclose.html',
                    ]
                ]
            ];
            $this->ProductWechatMenu($customer['id'], $menu);// 配置默认菜单

            $menu_list['menu'] = array_merge($menu_list['menu'], ['matchrule' => ['tag_id' => $tags['tag']['id']]]);
            $rel = $this->ProductWechatConditionMenu($customer['id'], $menu_list['menu']);
            $menu_list['menu']['menuid'] = $rel['menuid'];
        } else {
            $rel = $this->ProductWechatMenu($customer['id'], $menu_list['menu']);
        }
        if ($rel && WechatConfig::where('id', request()->input('id'))->update(['menu_list'=>json_encode($menu_list)])) {
            $menus = json_decode($wechat_conf['menu_list'], 1);
            if (isset($menus['menu']['menuid']) && $menus['menu']['menuid'] && (!isset($tags['tag']['id']) || !$tags['tag']['id'] || $menus['menu']['matchrule']['tag_id']!=$tags['tag']['id'])) {
                $data = ['menuid' => $menus['menu']['menuid']];
                $this->ProductWechatConditionMenu($customer['id'], $data, 'delconditional');
            }
            return ['code'=>0, 'msg'=>'保存成功！'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }

    private function productNewWechatMenus() {
        $data = request()->input();
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if(!isset($data['name'])||empty($data['name'])){
            $menu_data = [
                'type'=>1,
                'menu'=>[
                    'button'=>[
                        ['type'=>'view','name'=>'','url'=>'']
                    ]
                ]

            ];
            $rel = $this->ProductWechatDelMenu($customer['id']);
        }else{
            $menu_data = [
                'type'=>1,
                'menu'=>[
                    'button'=>[
                        ['type'=>'view','name'=>$data['name'],'url'=>$data['url']]
                    ]
                ]

            ];
            $menu_wx_data = [
                'button'=>[
                    ['type'=>'view','name'=>$data['name'],'url'=>$data['url']]
                ]
            ];
            $rel = $this->ProductWechatMenu($customer['id'], $menu_wx_data);
        }
        if ($rel && WechatConfig::where('id', $data['id'])->update(['menu_list'=>json_encode($menu_data)])) {
            return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'newmenulist'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }

    private function menuInfo($type, $customer_id, $web_tpl) {
        $domainRep = new DomainRepository();
        $host = $domainRep->randOne(4, $customer_id);
        if (!$host) {
            throw new \Exception('没有合适的菜单域名；请添加域名！', 2000);
        }
        /*
                $read_log   = $host . "/{$web_tpl}/#/book-history.html?cid={$customer_id}"; // 阅读记录
                $selection  = $host . "/{$web_tpl}/#/index.html?cid={$customer_id}";// 精品书城
                $signpage   = $host . "/{$web_tpl}/#/sign-in.html?cid={$customer_id}";  // 签到送币
                $hot_rank   = $host . "/{$web_tpl}/#/list/weekly-man.html?cid={$customer_id}";  // 热门排行榜
                $all_rank   = $host . "/{$web_tpl}/#/top-list..html?cid={$customer_id}";  // 榜单
                $week_news  = $host . "/{$web_tpl}/#/list/news-man.html?cid={$customer_id}";  // 本周新书
                $my_center  = $host . "/{$web_tpl}/#/mine.html?cid={$customer_id}"; // 个人信息
                $novel_index= $host . "/{$web_tpl}/#/index.html?cid={$customer_id}";  // 书城首页
                $customer_service   = $host ."/{$web_tpl}/#/contact.html?cid={$customer_id}";  // 联系客服
                $index_recommend    = $host . "/{$web_tpl}/#/index.html?cid={$customer_id}";  // 首页推荐
                $recharge_coin      = $host . "/{$web_tpl}/#/pay.html?cid={$customer_id}";  // 充值书币
        */


        $params = ['cid'=>$customer_id, 'customer_id'=>$customer_id, 'dtype'=>2, 'route'=>null];

        $params['route'] = 'read_log';
        $read_log   = $host . route('jumpto', $params, false); // 阅读记录
        $params['route'] = 'index';
        $index_recommend = $novel_index = $selection  = $host . route('jumpto', $params, false);// 精品书城 // 书城首页 // 首页推荐
        $params['route'] = 'sign';
        $signpage   = $host . route('jumpto', $params, false);  // 签到送币
        $params['route'] = 'hot_rank';
        $hot_rank   = $host . route('jumpto', $params, false);  // 热门排行榜
        $params['route'] = 'rank';
        $all_rank   = $host . route('jumpto', $params, false);  // 榜单
        $params['route'] = 'week_news';
        $week_news  = $host . route('jumpto', $params, false);  // 本周新书
        $params['route'] = 'center';
        $my_center  = $host . route('jumpto', $params, false); // 个人信息
        $params['route'] = 'contact';
        $customer_service   = $host . route('jumpto', $params, false);  // 联系客服
        $params['route'] = 'recharge';
        $recharge_coin      = $host . route('jumpto', $params, false);  // 充值书币

        switch ($type) {
            case 1:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'阅读记录',
                            'url'=>$read_log,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'精品书城',
                            'url'=>$selection,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'签到送币',
                            'url'=>$signpage,
                        ],
                    ],
                ];
                break;
            case 2:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'阅读记录',
                            'url'=>$read_log,
                        ],
                        [
                            'name'=>'精品书城',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'首页推荐',
                                    'url'=>$index_recommend,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'热门排行榜',
                                    'url'=>$hot_rank,
                                ],
                            ],
                        ],
                        [
                            'type'=>'view',
                            'name'=>'签到送币',
                            'url'=>$signpage,
                        ],
                    ],
                ];
                break;
            case 3:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'阅读记录',
                            'url'=>$read_log,
                        ],
                        [
                            'name'=>'精品书城',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'首页推荐',
                                    'url'=>$index_recommend,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'热门排行榜',
                                    'url'=>$hot_rank,
                                ],
                            ],
                        ],
                        [
                            'name'=>'签到送币',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'充值书币',
                                    'url'=>$recharge_coin,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'签到免费看书',
                                    'url'=>$signpage,
                                ],
                            ],
                        ],
                    ],
                ];
                break;
            case 4:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'阅读记录',
                            'url'=>$read_log,
                        ],
                        [
                            'name'=>'精品书城',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'书城首页',
                                    'url'=>$novel_index,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'排行榜',
                                    'url'=>$all_rank,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'新书',
                                    'url'=>$week_news,
                                ],
                            ],
                        ],
                        [
                            'name'=>'用户中心',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'我要充值',
                                    'url'=>$recharge_coin,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'每日签到',
                                    'url'=>$signpage,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'联系客服',
                                    'url'=>$customer_service,
                                ],
                            ],
                        ],
                    ],
                ];
                break;
            case 5:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'每日签到',
                            'url'=>$signpage,
                        ],
                        [
                            'name'=>'精品书城',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'书城首页',
                                    'url'=>$novel_index,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'热门榜单',
                                    'url'=>$hot_rank,
                                ],
                            ],
                        ],
                        [
                            'name'=>'用户中心',
                            'sub_button'=>[
                                [
                                    'type'=>'view',
                                    'name'=>'个人信息',
                                    'url'=>$my_center,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'我要充值',
                                    'url'=>$recharge_coin,
                                ],
                                [
                                    'type'=>'view',
                                    'name'=>'联系我们',
                                    'url'=>$customer_service,
                                ],
                            ],
                        ],
                    ],
                ];
                break;
            case 6:
                $menu = [
                    'button' => [
                        [
                            'type'=>'view',//view表示网页类型，click表示点击类型，miniprogram表示小程序类型
                            'name'=>'最近阅读',
                            'url'=>$read_log,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'书城首页',
                            'url'=>$novel_index,
                        ],
                        [
                            'type'=>'view',
                            'name'=>'排行榜',
                            'url'=>$all_rank,
                        ],
                    ],
                ];
                break;
            default:
                throw new \Exception('类型异常，等待后续开发！', 2000);
                break;
        }

        return $menu;
    }
    /**
     * 关注回复的图文信息配置
     */
    public function subscribeEdit(Content $content)
    {
        if (request()->method() == 'POST') {
            $id = request()->input('id');
            $old_img = request()->input('old_img');
            $subscribe_content['title'] = request()->input('title');
            $subscribe_content['desc']  = request()->input('desc');
            if (!$subscribe_content['title']) {
                return ['code'=>2000, 'msg'=>'请填写标题！'];
            }
            if (strlen($subscribe_content['title']) > 45) {
                return ['code'=>2000, 'msg'=>'标题过长，请缩短到15个字内！'];
            }
            if (!$subscribe_content['desc']) {
                return ['code'=>2000, 'msg'=>'请填写描述！'];
            }
            if (strlen($subscribe_content['desc']) > 150) {
                return ['code'=>2000, 'msg'=>'标题过长，请缩短到50个字内！'];
            }
            if (!request()->file('img') && !$old_img) {
                return ['code'=>2000, 'msg'=>'请上传图片！'];
            }

            if (request()->file('img')) {
                $path = 'novel/subscribe/mgs/img/';
                if (Storage::putFileAs($path, request()->file('img'), 'icon.'.$id.'.jpg')) {
                    $subscribe_content['img']   = Storage::url($path . 'icon.'.$id.'.jpg');
                } else {
                    return ['code'=>2000, 'msg'=>'图片上传失败！'];
                }
            } else {
                $subscribe_content['img']   = $old_img;
            }

            if (WechatConfig::where('id', $id)->update(['subscribe_content'=>json_encode($subscribe_content)])) {
                return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'/'.config('admin.route.prefix').'/wechatconfigs/subscribe'];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('关键词回复图文编辑');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '关键词回复图文编辑']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = WechatConfig::where('id', request()->input('id'))->first();
        $wechat['subscribe_content'] = json_decode($wechat['subscribe_content'], 1);
        $content->view('admin.wechatconfigs.subscribe_edit', ['wechat'=>$wechat]);

        return $content;
    }


    /**
     * 关注回复后的下一条推送配置
     */
    public function subscribeNext(Content $content)
    {
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('新用户第二次推送设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $next_msg = json_decode($wechat['subscribe_msg_next'], 1);
        $novels = Novel::where('status', '>', 0)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        $content->view('admin.wechatconfigs.subscribe_next', ['wechat'=>$wechat, 'novels'=>$novels, 'next_msg'=>$next_msg]);

        return $content;
    }
    /**
     * 关注回复后的下一条推送配置
     */
    public function subscribeNextEdit(Content $content)
    {
        if (request()->method() == 'POST') {
            return $this->subscribeNextSave();
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('新用户第二次推送设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $next_msg = json_decode($wechat['subscribe_msg_next'], 1);
        $novels = Novel::where('status', '>', 0)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        $content->view('admin.wechatconfigs.subscribe_next_edit', ['wechat'=>$wechat, 'novels'=>$novels, 'next_msg'=>$next_msg]);

        return $content;
    }
    public function subscribeNextSave() {
        $data = request()->input();
        if (count($data['nid']) != 3 || count($data['title'])!=3 || count($data['bottom'])!=3) {
            return ['code'=>2000, 'msg'=>'数据异常，请补充完整！'];
        }
        if (count(array_unique($data['nid'])) != 3) {
            return ['code'=>2000, 'msg'=>'数据异常，请选择不重复的小说！'];
        }

        $rule = '/^((ht|f)tps?):\/\/([\w\-]+(\.[\w\-]+)*\/)*[\w\-]+(\.[\w\-]+)*\/?(\?([\w\-\.,@?^=%&:\/~\+#]*)+)?/';
        if (!preg_match($rule, $data['bottom']['1'])) {
            return ['code'=>2000, 'msg'=>'链接地址异常！'];
        }

        $id = $data['id'];unset($data['id']);
        if (WechatConfig::where('id', $id)->update(['subscribe_msg_next'=>json_encode($data)])) {
            return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'subscribenext'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }
    //24小时推送的信息
    public function publish24(){
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if(request()->getMethod()=='GET'){
            $script = <<<EOT
$(document).off('change', ".nid1");
$(document).on('change', ".nid1", function () {
    var t= $(".nid1").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title1").val(data);
    })
});


$(document).off('change', ".nid2");
$(document).on('change', ".nid2", function () {
    var t= $(".nid2").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title2").val(data);
    })
});

$(document).off('change', ".nid3");
$(document).on('change', ".nid3", function () {
    var t= $(".nid3").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title3").val(data);
    })
});


$(document).off('change', ".nid4");
$(document).on('change', ".nid4", function () {
    var t= $(".nid4").val();
    $.get('NovelSelect',{q:t},function(data){
        $("#title4").val(data);
    })
});
EOT;
            Admin::script($script);

            $novel=Novel::get();
            $options=[];
            foreach ($novel as $vs){
                $options[$vs['id']]=$vs['title'];
            }
            $myWechatInfo=WechatConfig::where('customer_id',$customer->id)->first();
            $data=$myWechatInfo-> subscribe_msg_12h;
            $dx=[];
            if($data) {
                $ds = \GuzzleHttp\json_decode($data, 1);
                foreach ($ds as $ky=> $v){
                    if($ky == 'nid'){
                        $dx['nid1']=$v[0];
                        $dx['nid2']=$v[1];
                        $dx['nid3']=$v[2];
                        $dx['nid4']=$v[3];
                    }
                    if($ky == 'title'){
                        $dx['title1']=$v[0];
                        $dx['title2']=$v[1];
                        $dx['title3']=$v[2];
                        $dx['title4']=$v[3];
                    }
                }

            }


            $content=new Content();
            $form=new \Encore\Admin\Widgets\Form($dx);
            //第一步
            $form->html('<h4>第一部小说</h4>');
            $form->select('nid1','第一部小说')->options($options);
            $form->text('title1','第一步小说标题');
            $form->html('<h4>第二部小说</h4>');
            $form->select('nid2','第二部小说')->options($options);
            $form->text('title2','第二部小说标题');
            $form->html('<h4>第三部小说</h4>');
            $form->select('nid3','第三部小说')->options($options);
            $form->text('title3','第三部小说标题');
            $form->html('<h4>第四部小说</h4>');
            $form->select('nid4','第四部小说')->options($options);
            $form->text('title4','第四部小说标题');
            $form->action('publish24');
            $form->render();
            $content->body($form);
            return $content;
        }else {
            $input=request()->input();

            $data=[
                'nid'=>[$input['nid1'],$input['nid2'],$input['nid3'],$input['nid4']],
                'title'=>[$input['title1'],$input['title2'],$input['title3'],$input['title4']]
            ];
            $data=json_encode($data);
            $myWechat = $this->getConfWechat();
            $myWechat->subscribe_msg_12h=$data;
            $status=$myWechat->save();
            if($status){
                admin_toastr('保存成功！！', 'success');
                return redirect('/administrator/wechatconfigs/pushconf');

            }else{
                admin_toastr('保存失败！！', 'warning');
            }


        }


    }

    public function NovelSelect(){
        $q=request()->get('q');
        $data=Novel::where('id',$q)->first();
        return $data->title;
    }

    /**
     * 用户自定义推送配置
     */
    public function userHPush(Content $content)
    {
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('用户自定义推送配置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $user_hpush = json_decode($wechat['user_hpush'], 1);

        if (request()->method() == 'POST') {
            unset($user_hpush['switch']);
            if(!$user_hpush && request()->input('switch')==1) {
                WechatConfig::where('id', request()->input('id'))->update(['user_hpush'=>json_encode(['switch'=>0], JSON_UNESCAPED_UNICODE)]);
                return ['code'=>2000, 'msg'=>'没有配置小说，开启失败！'];
            }
            $user_hpush['switch'] = request()->input('switch', 0);
            if (WechatConfig::where('id', request()->input('id'))->update(['user_hpush'=>json_encode($user_hpush, JSON_UNESCAPED_UNICODE)])) {
                return ['code'=>0, 'msg'=>'保存成功！'];
            } else {
                return ['code'=>2000, 'msg'=>'保存失败！'];
            }
        }
        $novels = [];
        if (isset($user_hpush['nid'])) {
            $novels = Novel::where('status', '>', 0)->whereIn('id', $user_hpush['nid'])->orderBy('id', 'desc')->select(['id', 'title'])->get();
        }
        $content->view('admin.wechatconfigs.user_hpush', ['wechat'=>$wechat, 'novels'=>$novels, 'user_hpush'=>$user_hpush]);

        return $content;
    }
    /**
     * 直接关注推送配置
     */
    public function userHPushEdit(Content $content)
    {
        if (request()->method() == 'POST') {
            return $this->userHPushSave();
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('直接关注回复设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '公众号关注回复设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        $user_hpush = json_decode($wechat['user_hpush'], 1);
        $novels = Novel::where('status', '>', 0)->orderBy('id', 'desc')->select(['id', 'title'])->get();
        $content->view('admin.wechatconfigs.user_hpush_edit', ['wechat'=>$wechat, 'novels'=>$novels, 'user_hpush'=>$user_hpush]);

        return $content;
    }
    public function userHPushSave() {
        $data = request()->input();
        /*foreach ($data['s'] as $k => $item) {
            if ($item['n'] && (!$item['t'] || !$item['d'] || !$item['p'] || !$item['h'])) {
                return ['code'=>2000, 'msg'=>'数据异常，请填写完整！'];
            }
            if (!$item['n'] || strlen($item['h'])==0 || $item['h']>23 || $item['h']<0) {
                unset($data['s'][$k]);
            }
        }*/

        $id = $data['id'];unset($data['id']);
        if (!$data) {
            $data['switch'] = 0;
        } else {
            $data['switch'] = 1;
        }
        $data = json_encode($data);
        if (strlen($data) > 800) {
            return ['code'=>2000, 'msg'=>'配置信息过长，保存失败！'];
        }
        if (WechatConfig::where('id', $id)->update(['user_hpush'=>$data])) {
            return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'userhpush'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }

    public function centreSave(){
        $data = request()->input();
        $wechat = $this->getConfWechat();
        $menu_data = [];
        foreach ($data['s'] as $k => $item) {
            /*if ($item['n'] && (!$item['t'])) {
                unset($data['s'][$k]);
            }*/
            //生成链接
            $menu_data[$k]['name'] = $item['t'];
            $menu_data[$k]['novel_id'] = $item['n'];
            //http://m.yuedu1.shedn.cn/jiaoyu/weivip?cid=48&customer_id=48&dtype=2&novel_id=188704&section_num=0&route=read_novel_log
            $url = '?cid='.$wechat['customer_id'].'&customer_id='.$wechat['customer_id'].'&dtype=2&novel_id='.$item['n'].'&section_num=0&route=read_novel_log';
            $menu_data[$k]['url'] = $url;
            $menu_data[$k]['sections_id'] = 0;
        }
        $menu_data_list['m'] = $menu_data;
        $id = $data['id'];unset($data['id']);
        $menu_data_list['t'] = $data['t'];
        if (!$data) {
            $menu_data_list['switch'] = 0;
        } else {
            $menu_data_list['switch'] = 1;
        }
        $menu_data_list = json_encode($menu_data_list);
        if (strlen($menu_data_list) > 1800) {
            return ['code'=>2000, 'msg'=>'配置信息过长，保存失败！'];
        }
        if (WechatConfig::where('id', $id)->update(['centre_menu'=>$menu_data_list])) {
            return ['code'=>302, 'msg'=>'保存成功！', 'url'=>'centreMenuList'];
        } else {
            return ['code'=>2000, 'msg'=>'保存失败！'];
        }
    }

    /**
     * 短路生成
     */
    public function shorturl(Content $content){
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        if (request()->method() == 'POST') {
            try
            {
                $data = request()->input();
                $w_url =  $this->getShorturl($customer['id'],$data['or_url']);
                if(isset($w_url) && $w_url['errmsg'] == 'ok'){
                    $code = ['code'=>302, 'msg'=>'保存成功！', 'short_url'=>$w_url['short_url'],'url'=>'shorturl'];
                }else{
                    return ['code'=>2000, 'msg'=>'保存失败！'];
                }
                return $code;
            }
            catch (\Exception $e)
            {
                return ['code'=>$e->getCode(), 'msg'=>$e->getMessage()];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('菜单设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '菜单设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        if ($wechat && $wechat->menu_list) {
            $wechat->menu_list = json_decode($wechat->menu_list, 1);
        }
        $content->view('admin.wechatconfigs.shorturl', ['wechat'=>$wechat]);
        return $content;
    }



    /**
     * 发送模版消息
     */
    public function sendMessage(Content $content){
        $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
        $wechat = Wechat::where('customer_id', $customer['id'])->where('status', 1)->first();
        if (request()->method() == 'POST') {
            try
            {
                $data = request()->input();
                if(empty($data['id'])||empty($data['openid'])){
                    return ['code'=>2000, 'msg'=>'参数缺失！'];
                }
                //获取模板消息
                $message_info =TemplateMsg::where('id','=',$data['id'])->first();
                $message_content_json = json_encode($message_info['content']);
                $date = date('Y-m-d H:m:s',time());
                $number = rand(90,100).'.00';
                //var_dump($info['content']);
                $message_content_json = str_replace('date',$date,$message_content_json);
                $message_content_json = str_replace('number',$number,$message_content_json);
                $content = json_decode($message_content_json, 1);
                $template = [
                    'touser'        => $data['openid'],
                    'template_id'   => $message_info['template_id'],
                    'url'           => $message_info['url'],
                    'data'          => $content,
                ];
                $rel = $this->SendTemplate($wechat['customer_id'], $template, false);
                if (isset($rel['errcode']) && $rel['errcode'] == 0) {
                    $code = ['code'=>302, 'msg'=>'发送成功！','url'=>'send_message'];
                }else{
                    $code = ['code'=>2000, 'msg'=>'发送失败！'];
                }
                return $code;
            }
            catch (\Exception $e)
            {
                return ['code'=>$e->getCode(), 'msg'=>$e->getMessage()];
            }
        }
        // 选填
        $content->header('公众号管理');
        // 选填
        $content->description('菜单设置');
        // 添加面包屑导航 since v1.5.7
        $content->breadcrumb(
            ['text' => '首页', 'url' => '/'.config('admin.route.prefix')],
            ['text' => '公众号管理', 'url' => '/'.config('admin.route.prefix').'/wechatconfigs/index'],
            ['text' => '菜单设置']
        );
        // 直接渲染视图输出，Since v1.6.12
        $wechat = $this->getConfWechat();
        //模版消息
        $message_list =TemplateMsg::where('customer_id','=',$wechat['customer_id'])->get()->toArray();
        if ($wechat && $wechat->menu_list) {
            $wechat->menu_list = json_decode($wechat->menu_list, 1);
        }
        $content->view('admin.wechatconfigs.send_message', ['wechat'=>$wechat,'message_list'=>$message_list]);
        return $content;
    }
}
