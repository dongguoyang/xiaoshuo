<?php
/**
 * 发送所有配置公众号继续阅读模板消息的命令
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
use Illuminate\Console\Command;

class SendTemplateForUser extends Command
{
    use OfficialAccountTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'novel:send-template-for-user {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send wechat template for users';

    protected $user_map; // 查询user表的条件

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
        // $this->user_map = $this->argument('user_map');
        // $this->user_map = json_decode($this->user_map, 1);
        $template_id    = $this->argument('id');
        //
        $this->info("Starting at ".date('Y-m-d H:i:s').". Running initial for 【{$this->signature}】");

        $this->doSendTemplate($template_id); // 获取模版消息内容;

        $this->info("End at ".date('Y-m-d H:i:s').". over for 【{$this->signature}】");
    }
    /**
     * 执行发送模板消息
     */
    private function doSendTemplate($id) {
        // type    1=>'继续阅读提醒',2=>'首充优惠提醒',4=>'未支付提醒',5=>'推荐阅读提醒',
        $info = $this->templateMsgRep->find($id, ['template_id', 'content', 'url', 'type', 'customer_id', 'status']);
        if (!$info || $info->status != 1) {
            return false; // 没有模板消息内容；直接返还
        }
        $info = $this->templateMsgRep->toArr($info);
        $content = json_decode($info['content'], 1);

        $url = $info['url'];
        if (strpos($url, 'http') === false) {
            $host = $this->domainRep->randOne(1, $info['customer_id']);
            $url = $host . $url;
        }
        $template = [
            'touser'        => null,
            'template_id'   => $info['template_id'],
            'url'           => $url,
            'data'          => $content,
        ];

        $min_id = 0; // 最小的 user_id
        while (true) {
            $users = $this->userRep->model
                ->where('subscribe', 1)
                ->where('id', '>', $min_id)
                ->orderBy('id')
                ->select(['id', 'name', 'sex', 'openid', 'recharge_money'])
                ->limit(200)
                ->get();
            $users = $this->userRep->toArr($users);
            if (!$users || !count($users)) {
                break;   // 没有查询到合适的用户；直接返回
            }
            foreach ($users as $user) {
                // if($user['openid'] != 'ovlc_uDkaBGkaDOmG7aFubnMlcrU') continue; // 只发给 是哦 的
                $template['touser'] = $user['openid'];
                $this->SendTemplate($info['customer_id'], $template, true);
            }
            $min_id = $user['id'];
        }
        return 'ok';
    }
    /**
     * 获取用户查询条件
     */
    private function templateUserMap($customer_id, $type) {
        // type 1=>'继续阅读提醒',2=>'首充优惠提醒',3=>'签到成功提醒',4=>'未支付提醒',5=>'推荐阅读提醒',
        $map = [
            ['customer_id', $customer_id],
            ['subscribe',   1],
        ];

        switch ($type) {
            case 2:
                $map[] = ['recharge_money', '=', 0];
                break;
            default:
                break;
        }

        return $map;
    }
    /**
     * 重置继续阅读提醒
     */
    private function resetContinueRead($data, $user) {
        $log = $this->readLogRep->model->where('user_id', $user['id'])->orderBy('updated_at', 'desc')->select(['title', 'end_section_num', 'novel_id', 'name'])->first();
        if (!$log) {
            return null;
        }
        foreach ($data as $k=>$v) {
            if (strpos($v['value'], '{novel_title}') !== false) {
                $data[$k]['value'] = str_replace('{novel_title}', $log['name'], $v['value']);
            }
            if (strpos($v['value'], '{section_title}') !== false) {
                $data[$k]['value'] = str_replace('{section_title}', $log['title'], $v['value']);
            }
            if (strpos($v['value'], '{novel_desc}') !== false) {
                $novel = $this->novels->NovelInfo($log['novel_id']);
                $data[$k]['value'] = str_replace('{novel_desc}', $novel['desc'], $v['value']);
            }
        }

        return $data;
    }
}
