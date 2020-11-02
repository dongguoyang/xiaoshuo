<?php
/**
 * Created by LWL.
 * User: LUWENLONG
 * Date: 2019/3/17
 * Time: 15:31
 */

namespace App\Libraries\Share;


use App\Helpers\IpLocation;
use Illuminate\Support\Facades\Request;
use Jenssegers\Agent\Facades\Agent;

class Client
{
    /**
     * 返回客户端信息
     * @return array
     */
    static function info()
    {
        $ua = Request::server('HTTP_USER_AGENT') ?: '';
        $ua = strlen($ua) <= 255 ? $ua : substr($ua, 0, 255);
        $ipinfo = self::ip();
        $data = [
            'platform' => Agent::platform(),
            'browser' => Agent::browser(),
            'mobile_brand' => Agent::device(),
            'ua' => $ua,
            'ip' => $ipinfo['ip'],
            'ip_addr' => $ipinfo['addr'],
            'os' => self::os($ua),
        ];
        return $data;
    }

    /**
     * 返回客户端OS
     * @param $ua
     * @return string
     */
    public static function os($ua = '')
    {
        $ua = $ua ?: (Request::server('HTTP_USER_AGENT') ?: '');

        if (!$ua) return 'other';

        if (stripos($ua, 'android')) {
            return 'android';
        } else if (stripos($ua, 'ipad')) {
            return 'ipad';
        } else if (stripos($ua, 'iphone')) {
            return 'iphone';
        } else if (stripos($ua, 'windows')) {
            return 'win';
        } else {
            return 'other';
        }
    }

    /**
     * 返回IP信息
     * @return array
     */
    public static function ip()
    {
        $ip = request()->getClientIp();
        if ($ip == '127.0.0.1') {
            $ip = '183.69.203.252';
        }
        // 纯真ip获取ip所在地址
        $IpLocation = new IpLocation();
        $ip_info = $IpLocation->getlocation($ip);
        $addr = '';
        if (isset($ip_info['country'])) {
            $addr = $ip_info['country'];
        }
        return ['ip' => $ip, 'addr' => $addr];
    }

    /**
     * 移动端判断 （未使用过）
     */
    public static function isMobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        $server = Request::server();
        if (isset($server['HTTP_X_WAP_PROFILE'])) {
            return true;
        }
        // 如果via信息含有wap则一定是移动设备
        if (isset($server['HTTP_VIA'])) {
            // 找不到为flase,否则为true
            return stristr($server['HTTP_VIA'], "wap") ? true : false;
        }
        // 脑残法，判断手机发送的客户端标志,兼容性有待提高
        if (isset($server['HTTP_USER_AGENT'])) {
            $clientkeywords = ['nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp', 'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu', 'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi', 'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile',];
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(".implode('|', $clientkeywords).")/i", strtolower($server['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        // 协议法，因为有可能不准确，放到最后判断
        if (isset($server['HTTP_ACCEPT'])) {
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($server['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($server['HTTP_ACCEPT'], 'text/html') === false || (strpos($server['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($server['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }


    /**
     * 获取返回已知浏览器 UA
     * @param string $key 浏览器类型
     * @return string ua
     */
    public static function userAgent($key = 'iphoneWechat')
    {
        $ua['mate10p'] = 'Mozilla/5.0 (Linux; U; Android 8.1.0; zh-cn; BLA-AL00 Build/HUAWEIBLA-AL00) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/8.9 Mobile Safari/537.36';
        $ua['nova3'] = 'Mozilla/5.0 (Linux; Android 8.1; PAR-AL00 Build/HUAWEIPAR-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044304 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x26070333) NetType/WIFI Language/zh_CN Process/tools';
        $ua['OPPOA57'] = 'Mozilla/5.0 (Linux; Android 6.0.1; OPPO A57 Build/MMB29M; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.10 (Baidu; P1 6.0.1)';
        $ua['huaweiP20'] = 'Mozilla/5.0 (Linux; Android 8.1; EML-AL00 Build/HUAWEIEML-AL00; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/53.0.2785.143 Crosswalk/24.53.595.0 XWEB/358 MMWEBSDK/23 Mobile Safari/537.36 MicroMessenger/6.7.2.1340(0x2607023A) NetType/4G Language/zh_CN';
        $ua['honorV9'] = 'Mozilla/5.0 (Linux; Android 8.0; DUK-AL20 Build/HUAWEIDUK-AL20; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044353 Mobile Safari/537.36 MicroMessenger/6.7.3.1360(0x26070333) NetType/WIFI Language/zh_CN Process/tools';
        $ua['vivoX6SA'] = 'Mozilla/5.0 (Linux; Android 5.1.1; vivo X6S A Build/LMY47V; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/57.0.2987.132 MQQBrowser/6.2 TBS/044207 Mobile Safari/537.36 MicroMessenger/6.7.3.1340(0x26070332) NetType/4G Language/zh_CN Process/tools';

        $ua['windows'] = 'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36';

        $ua['iphoneWechat'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/16A366 MicroMessenger/6.7.3(0x16070321) NetType/WIFI Language/zh_CN';
        $ua['iphone6sQQ'] = 'Mozilla/5.0 (iPhone 6s; CPU iPhone OS 11_4_1 like Mac OS X) AppleWebKit/604.3.5 (KHTML, like Gecko) Version/11.0 MQQBrowser/8.3.0 Mobile/15B87 Safari/604.1 MttCustomUA/2 QBWebViewType/1 WKType/1';

        $ua['galaxyS9'] = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-G9650 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)';
        $ua['galaxyNote8'] = 'Mozilla/5.0 (Linux; Android 8.0.0; SM-N9500 Build/R16NW; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/63.0.3239.83 Mobile Safari/537.36 T7/10.13 baiduboxapp/10.13.0.11 (Baidu; P1 8.0.0)';
        return $ua[$key];
    }

    /**
     * 取得根域名
     * @param string $domain 域名
     * @return string 返回根域名
     */
    public static function getBaseDomain($domain)
    {
        $domain_postfix_cn_array = ["com", "net", "org", "gov", "edu", "com.cn", "cn"];
        $array_domain = explode(".", $domain);
        $array_num = count($array_domain) - 1;
        if ($array_domain[$array_num] == 'cn') {
            if (in_array($array_domain[$array_num - 1], $domain_postfix_cn_array)) {
                $re_domain = $array_domain[$array_num - 2].".".$array_domain[$array_num - 1].".".$array_domain[$array_num];
            } else {
                $re_domain = $array_domain[$array_num - 1].".".$array_domain[$array_num];
            }
        } else {
            $re_domain = $array_domain[$array_num - 1].".".$array_domain[$array_num];
        }
        return $re_domain;
    }
}