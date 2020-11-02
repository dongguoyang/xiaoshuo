<?php

namespace App\Jobs;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Traits\OfficialAccountTrait;
use App\Logics\Traits\WechatTrait;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCustomerMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, OfficialAccountTrait;

    protected $msgcontent;
    protected $customer_id;
    protected $openid;
    protected $recharged;
    protected $msgtype;
    protected $domains;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    //public function __construct($customer_id, $msgcontent)
    public function __construct($msgtype, $customer_id, $openid, $recharged)
    {
        //
        //$this->msgcontent   = $msgcontent;
        $this->msgtype  = $msgtype;
        $this->openid   = $openid;
        $this->recharged    = $recharged;
        $this->customer_id  = $customer_id;
        $this->domains      = new DomainRepository();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
        if ($this->attempts() > 3) {
            Log::info('SendCustomerMsg 消息 attempts > 3 后发送失败！');
        } else {
            if ($this->msgtype == 'recharge-fail') {
                $this->msgcontent = $this->rechargeFailMsg();
            } else {
                if (!is_array($this->msgcontent)) {
                    $this->msgcontent = json_decode($this->msgcontent, 1);
                }
            }

            if ($this->msgcontent && is_array($this->msgcontent)) {
                $this->SendCustomMsg($this->customer_id, $this->msgcontent, true);
            }
        }
    }

    private function rechargeFailMsg() {
        $host = $this->domains->randOne(1, $this->customer_id);
        if ($this->recharged) {
            // 充值失败提醒
            $content = [
                'touser'    => $this->openid,
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => '>>> 充值失败了💔💔💔',
                            'description'   => '亲，您刚刚提交的充值订单充值失败了，点我重新充值吧！',
                            'url'           => $host . route('jumpto', ['route'=>'recharge', 'dtype'=>2, 'customer_id'=>$this->customer_id], false),
                            'picurl'        => 'https://novelsys.oss-cn-shenzhen.aliyuncs.com/coupon/images/49c27ed5be905cb2a2ac050ead023ec.png',
                        ],
                    ],
                ],
            ];
            //$this->SendCustomMsg($user['customer_id'], $content, true);
        } else {
            // 首充优惠提醒
            $content = [
                'touser'    => $this->openid,
                'msgtype'   => 'news',
                'news'      => [
                    'articles'  =>  [
                        [
                            'title'         => '>>> 首充优惠活动🎉🎉🎉',
                            'description'   => '亲，首次充值仅需9.9元，还送您900个书币，点击前往！',
                            'url'           => $host . route('jumpto', ['route'=>'first_recharge', 'dtype'=>2, 'customer_id'=>$this->customer_id], false),
                            'picurl'        => 'https://novelsys.oss-cn-shenzhen.aliyuncs.com/coupon/images/f4abd987efd929090b69a0414c8f077.png',
                        ],
                    ],
                ],
            ];
            //$this->SendCustomMsg($customer_id, $content, true);
        }

        return $content;
    }
}
