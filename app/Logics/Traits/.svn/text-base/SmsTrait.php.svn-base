<?php
namespace App\Logics\Traits;

use Illuminate\Support\Facades\Cache;
use Overtrue\EasySms\EasySms;

trait SmsTrait
{
    protected $SmsObj; // 短信实例化对象
    /**
     * 发送短信验证码
     */
    public function SendSms($tel, $data) {
        if (!$this->SmsObj) $this->initSms();

        return $this->SmsObj->send($tel, $data);
    }
    // 实例化短信操作类
    private function initSms() {
        $config = [
            // HTTP 请求的超时时间（秒）
            'timeout' => 5.0,

            // 默认发送配置
            'default' => [
                // 网关调用策略，默认：顺序调用
                'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

                // 默认可用的发送网关
                'gateways' => [
                    'aliyun',
                ],
            ],
            // 可用的网关配置
            'gateways' => [
                'errorlog' => [
                    'file' => '/tmp/easy-sms.log',
                ],
                'aliyun' => [
                    'access_key_id' => 'LTAIQumQVJQlNm3s',
                    'access_key_secret' => 'KoxDtCsPZZCWaM9r2dEHMh9HXwG3hH',
                    'sign_name' => '晚八点',
                ],
                //...
            ],
        ];
        $this->SmsObj = new EasySms($config);
        return $this->SmsObj;
    }
    /**
     * 域名死亡发送提醒
     * @param string $domain 提醒的域名
     */
    public function DomainNotice($domain) {
        if ($start = strpos($domain, '//')) {
            $domain = substr($domain, $start + 2);
        }
        // 表示刚通知过了；防止重复通知
        $key = config('app.name') . 'domain_notice_' . $domain;
        if (Cache::has($key)) return false;
        Cache::add($key, $domain, 3600);

        $tels = [
            //'17772422850', // 是哦
            //'18623063128', // 卡莫
            //'13677645708', // 老板
            '13883857311', // 李星
        ];
        $datetime = date('Y-m-d H:i:s');
        try {
            foreach ($tels as $tel) {
                $data = [
                    'content'  => '尊敬的${name}，您的域名${host}已在${datetime}被封禁，请及时更换处理！',
                    'template' => 'SMS_186945138',
                    'data' => [
                        'name'  => $tel,
                        'host'  => $domain,
                        'datetime'  => $datetime,
                    ],
                ];
                $this->SendSms($tel, $data);
            }
            return 'ok';
        } catch (\Overtrue\EasySms\Exceptions\Exception $e) {
            // dd($e->getExceptions());
            return false;
        } catch (\Exception $e) {
            //dd($e->getMessage());
            return false;
        }
    }

}