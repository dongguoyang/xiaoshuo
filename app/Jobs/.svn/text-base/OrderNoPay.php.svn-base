<?php
/**
 * 订单问支付要推送模板消息的任务
 */
namespace App\Jobs;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\TemplateMsgRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class OrderNoPay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, OfficialAccountTrait;

    protected $recharge_log; // 充值订单信息
    protected $user; // 用户信息
    protected $templateMsgs;
    protected $domains;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($recharge_log, $user)
    {
        //
        $this->recharge_log = $recharge_log;
        $this->user         = $user;
        $this->templateMsgs = new TemplateMsgRepository();
        $this->domains      = new DomainRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 1继续阅读提醒；2首充优惠图文推送；3签到图文推送；4未支付提醒；
        $template = $this->templateMsgs->CustomerTypeTemp(4, $this->user['customer_id'], $this->user['platform_wechat_id']);
        if (!$template) {
            throw new \Exception('没有对应模板！', 2000);
            return false;
        }
        $content = json_decode($template['content'], 1);

        $status = [0=>'未支付', 1=>'充值成功', 2=>'充值失败'];
        foreach ($content as $k=>$v) {
            if (strpos($v['value'], '{money}')) {
                $v['value'] = str_replace('{money}', ($this->recharge_log['money']/100), $v['value']);
                $content[$k] = $v;
            }
            if (strpos($v['value'], '{status}')) {
                $v['value'] = str_replace('{status}', $status[$this->recharge_log['status']], $v['value']);
                $content[$k] = $v;
            }
            if (strpos($v['value'], '{type}')) {
                $v['value'] = str_replace('{type}', $this->recharge_log['desc'], $v['value']);
                $content[$k] = $v;
            }
        }

        if ($this->user['subscribe'] == 1) {
            $host = $this->domains->randOne(1, $this->user['customer_id']);
        } else {
            $host = $this->domains->randOne(3, $this->user['customer_id']);
        }
        $data = [
            'touser'        => $this->user['openid'],
            'template_id'   => $template['template_id'],
            'url'           => $host . 'recharge', // 充值页面url地址
            'data'          => $content,
        ];

        try {
            $this->SendTemplate($this->user['customer_id'], $data, true);
        } catch (\Exception $e) {
            Log::info($this->recharge_log['out_trade_no'] . '; 未支付模板消息发送失败！'."\n" . $e->getMessage());
        }
    }
}
