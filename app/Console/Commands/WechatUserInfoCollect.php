<?php
/**
 * 发送模板消息；主要用户后台主动推送模板消息
 */

namespace App\Console\Commands;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\TemplateMsgRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use App\Admin\Models\WechatsUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WechatUserInfoCollect extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:wechat-get-user-list {id} {customer_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get wechat user for list';
    protected $user_map; // 查询user表的条件
    private   $next_openid = '';
    protected $wechatConfRep;
    protected $platWechatRep;
    protected $templateMsgRep;
    protected $userRep;
    protected $domainRep;
    protected $readLogRep;
    protected $novels;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->initRep();
    }
    /**
     * 实例化需用的Repository
     */
    private function initRep() {
        $this->wechatConfRep = new WechatConfigRepository();
        $this->platWechatRep = new PlatformWechatRepository();
        $this->templateMsgRep= new TemplateMsgRepository();
        $this->userRep       = new UserRepository();
        $this->domainRep     = new DomainRepository();
        $this->readLogRep    = new ReadLogRepository();
        $this->novels        = new NovelRepository();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $platform_wechat_id    = $this->argument('id');
        $customer_id           = $this->argument('customer_id');
        $this->info("Starting at ".date('Y-m-d H:i:s').". Running initial for 【{$this->signature}】");
        $this->getUserInfoList($platform_wechat_id,$customer_id);
        $this->info("End at ".date('Y-m-d H:i:s').". over for 【{$this->signature}】");
    }
    /**
     * 执行发送模板消息
     */
    private function getUserInfoList($platform_wechat_id,$customer_id) {
        $user_lists = [];
        $WechatsUserModel = new WechatsUser();
        $WechatsUserModel->where('platform_wechat_id','=',$platform_wechat_id)->delete();
        while (true) {
            $user_list = [];
            $openids = $this->getOpenidList($customer_id);
            if (empty($openids)) break;
            foreach ($openids as $openid) {
                $user_info = $this->getUserInfo($openid);
                if(empty($user_info)) continue; 
                $user_list['name'] = $user_info['nickname'];
                $user_list['created_at'] = time();
                $user_list['updated_at'] = time();
                $user_list['img'] = $user_info['headimgurl'];
                $user_list['sex'] = $user_info['sex'];
                $user_list['openid'] = $user_info['openid'];
                $user_list['platform_wechat_id'] = $platform_wechat_id;
                $user_list['customer_id'] = $customer_id;
                $user_list['subscribe_scene'] = $user_info['subscribe_scene'];
                $user_list['subscribe_time'] = $user_info['subscribe_time'];
                $WechatsUserModel::create($user_list);
                $user_lists[] = $user_list;
            }
            $WechatsUserModel->save($user_lists);
        }
        return 'ok';
    }

    /**
     * 获取用户列表
     */
    private function getOpenidList($customer_id) {
        $this->wechat = $this->getWechatsInOfficialAccountTrait()->getWechatForCustomer($customer_id);
        $rel = $this->GetUserList($this->wechat['customer_id'], $this->next_openid);
        $this->next_openid = $rel['next_openid'];
        $users = isset($rel['data']['openid']) ? $rel['data']['openid'] : [];
        return $users;
    }
    /**
     * 获取用户信息
     * 昵称；头像；性别等
     */
    private function getUserInfo($openid){
        try {
            $user =  $this->GetWechatUserInfo($this->wechat['token'], $openid, true); // 获取用户信息
            if( isset($user['openid']) && $user['openid'] ){
                //当获取失败时
                return ['openid' => $user['openid'], 'nickname' => $user['nickname'], 'sex' => $user['sex'],'headimgurl'=>$user['headimgurl'],'subscribe_scene'=>$user['subscribe_scene'],'subscribe_time'=>$user['subscribe_time']];
            }else{
                return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }
}
