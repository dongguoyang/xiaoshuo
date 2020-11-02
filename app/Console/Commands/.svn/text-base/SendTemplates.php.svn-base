<?php
/**
 * 发送所有配置公众号继续阅读模板消息的命令
 */

namespace App\Console\Commands;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\TemplateMsgRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Console\Command;

class SendTemplates extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-templates-day_read';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send wechat template for users';


    protected $wechatConfRep;
    protected $platWechatRep;
    protected $templateMsgRep;
    protected $userRep;
    protected $domainRep;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(WechatConfigRepository $wechatConfRep,
                                PlatformWechatRepository $platWechatRep,
                                TemplateMsgRepository $templateMsgRep,
                                UserRepository $userRep,
                                DomainRepository $domainRep)
    {
        parent::__construct();
        $this->wechatConfRep = $wechatConfRep;
        $this->platWechatRep = $platWechatRep;
        $this->templateMsgRep= $templateMsgRep;
        $this->userRep       = $userRep;
        $this->domainRep     = $domainRep;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $this->info("Starting at ".date('Y-m-d H:i:s').". Running initial for 【{$this->signature}】");

        $configs = $this->wechatConfigs();
        foreach ($configs as $config) {
            $config['pushconf'] = json_decode($config['pushconf'], 1);
            $config['pushconf']['day_read'] = 1;
            if (!isset($config['pushconf']['day_read']) || !$config['pushconf']['day_read']) {
                continue;
            }
            $this->doTemplate($config);
        }

        $this->info("End at ".date('Y-m-d H:i:s').". over for 【{$this->signature}】");
    }
    /**
     * 执行发送模板消息
     */
    private function doTemplate($wechat) {
        $template = $this->templateInfo($wechat);
        if (!$template) {
            return false; // 没有模板消息内容；直接返还
        }

        $min_id = 0; // 最小的 user_id
        while (true) {
            $users = $this->userRep->model
                ->where([
                    ['customer_id', $wechat['customer_id']],
                    ['platform_wechat_id', $wechat['platform_wechat_id']],
                    ['subscribe', 1],
                    ['id', '>', $min_id],
                ])->orderBy('id')
                ->select(['id', 'name', 'sex', 'openid', 'recharge_money'])
                ->limit(200)
                ->get();
            $users = $this->userRep->toArr($users);
            if (!$users) {
                break;   // 没有查询到合适的用户；直接返回
            }
            foreach ($users as $user) {
                $template['touser'] = $user['openid'];
                /*foreach ($template['data'] as $k=>$v) {
                    // 替换模板消息的内容
                    $template['data'][$k] = $this->templateMsgRep->ReplaceTempKeyword($v, ['username'=>$user['name']]);
                }*/
                $this->SendTemplate($wechat['customer_id'], $template, true);
            }
            $min_id = $user['id'];
        }
        return 'ok';
    }
    /**
     * 获取所有的公众号配置信息
     */
    private function wechatConfigs() {
        $list = $this->wechatConfRep->model->where('pushconf', '!=', '')->whereNotNull('pushconf')->select(['pushconf', 'customer_id', 'platform_wechat_id'])->get();
        $list = $this->wechatConfRep->toArr($list);

        return $list;
    }
    /**
     * 获取模板消息信息
     */
    private function templateInfo($wechat) {
        $info = $this->templateMsgRep->model
            ->where([
                ['platform_wechat_id', $wechat['platform_wechat_id']],
                ['type', 1],
                ['status', 1],
            ])->select(['template_id', 'content'])
            ->first();
        if (!$info) {
            return [];
        }
        $info = $this->templateMsgRep->toArr($info);

        $content = json_decode($info['content'], 1);

        $host = $this->domainRep->randOne(2, $wechat['customer_id']) . '/front?cid='.$wechat['customer_id'];
        $data = [
            'touser'        => null,
            'template_id'   => $info['template_id'],
            'url'           => $host, // 继续阅读页面url地址
            'data'          => $content,
        ];

        return $data;
    }
}
