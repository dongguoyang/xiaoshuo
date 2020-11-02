<?php
namespace App\Logics\Traits;

use App\Logics\Repositories\src\PlatformRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\UserRepository;

trait WechatTrait
{
    protected $wechat;
    protected $platform;

    protected $appId;
    protected $appSecret;
    protected $redirectUri;
    protected $componentAppid;

    /**
     * 初始化类属性
     * @param int $customer_id
     */
    private function initWechatTrait($customer_id, $platform_wechat_id = 0) {
        if ($this->wechat && $this->wechat['token_out'] > time()) {
            return false; // 公众号已经实例化并且token未过期；就直接使用
        }
        if ($platform_wechat_id) {
            $wechat = $this->getPlatformWechatsInWechatTrait()->getWechatForId($platform_wechat_id); //获取公众平台信息
        } else {
            $wechat = $this->getPlatformWechatsInWechatTrait()->getWechatForCustomer($customer_id); //获取公众平台信息
        }
        if(!$wechat || !$wechat['status']){
            throw new \Exception('该状态已经关闭', 20002);
        }

        $this->appId = $wechat['appid'];
        $this->appSecret = $wechat['app_secret'];

        if (isset($wechat['component_appid'])) {
            $platform = $this->getPlatformsInWechatTrait()->findBy('appid', $wechat['component_appid'], $this->getPlatformsInWechatTrait()->model->fields);
            $this->componentAppid = $platform['appid'];
            $this->redirectUri = urlencode($platform['webauth_url']);
            if (!$platform['component_access_token'] || $platform['token_out_time'] < time()) {
                $platform = $this->updateComponentAccessToken($platform);
            }
            $this->platform = $platform;

            if (!$wechat['token'] || $wechat['token_out'] < time()) {
                $wechat = $this->updateWechatForPlatformAccessToken($wechat, $platform);
            }
        } else {
            $this->redirectUri = urlencode($this->wechat['webauth_url']);
        }

        $this->wechat = $wechat;
    }

    /**
     * 跳转获取授权code
     */
    public function ToAuthCode($customer_id, $state = ['id'=>0]){
        $this->initWechatTrait($customer_id);

        if(!$this->appId || !$this->redirectUri){
            throw new \Exception('缺少配置信息', 20001);
        }

        $state = $this->stateInfo($state);

        if (isset($this->wechat['component_appid']) && $this->wechat['component_appid']) {
            // 开放平台模式获取授权码
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$this->redirectUri}&response_type=code&scope=snsapi_userinfo&state={$state}&component_appid={$this->componentAppid}#wechat_redirect";
        } else {
            // 公众号模式获取授权码
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$this->appId."&redirect_uri=".$this->redirectUri."&response_type=code&scope=snsapi_userinfo&state=$state#wechat_redirect";
        }

        return redirect($url);
    }

    /**
     * 获取授权token和用户openid
     * 用于换区用户详细信息
     * @param int $type    1 app开放平台；2 公众号；
     */
    public function AuthToken2Openid(){
        $code  = request()->input('code');
        if (!$code) {
            throw new \Exception('未获取到 code 或 state 信息', 2000);
        }
        $state = $this->stateInfo(); // 获取state信息

        $this->initWechatTrait($state['c']);

        if (isset($this->wechat['component_appid']) && $this->wechat['component_appid']) {
            // 开放平台模式获取 网页授权access_token 和 openid
            $url = "https://api.weixin.qq.com/sns/oauth2/component/access_token?appid={$this->appId}&code={$code}&grant_type=authorization_code&component_appid={$this->componentAppid}&component_access_token={$this->platform['component_access_token']}";
        } else {
            // 公众号模式获取 网页授权access_token 和 openid
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appId}&secret={$this->appSecret}&code={$code}&grant_type=authorization_code";
        }
        // 这他妈的不知道怎么地就偶尔提示access_token 异常
        $i = 0;
        while ($i < 8) {
            $i++;
            $rel = @file_get_contents($url);
            $rel = json_decode($rel, 1);
            if (isset($rel['access_token'])) {
                break;
            }
        }
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception($rel['errmsg'], $rel['errcode']);
        }
        $openid = $rel['openid'];
        $access_token = $rel['access_token'];

        // 采用网页授权 token 获取用户的信息；获取不到 是否关注
        $wechat_user =  $this->getWechatUserInfo($access_token, $openid);

        // 采用 token 获取用户的详细信息 可以获取是否关注
        $temp =  $this->getWechatUserInfo($this->wechat['token'], $openid, true);
        return array_merge($wechat_user, $temp);
    }

    /**
     * 获取用户微信详细信息
     * @param string $access_token
     * @param string $openid
     * @param bool $detail 是否获取用户详细信息
     */
    private function getWechatUserInfo($access_token, $openid, $detail = false){
        //获取微信信息
        $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        if ($detail) {
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        }
        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        if(isset($rel['errcode']) && $rel['errcode'] > 100){
            throw new \Exception('信息获取失败', 2003);
        }
        if(!$rel['openid']){
            //当获取失败时
            throw new \Exception('invalid openid', 40003);
        }
        return $rel;
    }

    /**
     * 更新 开放平台 access_token
     * @param array $platform 开放平台数据
     * @return array $platform
     */
    private function updateComponentAccessToken($platform) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
        $param = [
            'component_appid'           => $platform['appid'],
            'component_appsecret'       => $platform['appsecret'],
            'component_verify_ticket'   => $platform['component_verify_ticket'],
        ];

        $rel = CallCurl($url, $param, true, 'json');
        $rel = json_decode($rel, 1);

        if (!isset($rel['component_access_token'])) {
            throw new \Exception('开放平台 component_access_token 获取失败！' . $rel['errmsg'], $rel['errcode']);
        }
        $data = [
            'component_access_token' => $rel['component_access_token'],
            'token_out_time'         => time() + $rel['expires_in'] - 600,
        ];
        $platform['token_out_time'] = $data['token_out_time'];
        $platform['component_access_token'] = $data['component_access_token'];
        // 更新信息到数据库
        $this->getPlatformsInWechatTrait()->update($data, $platform['id']);
        return $platform;
    }

    /**
     * 更新 公众号 access_token
     * @param array $wechat 公众号数据
     * @param array $platform 开放平台数据
     * @return array $wechat
     */
    private function updateWechatForPlatformAccessToken($wechat, $platform) {
        $url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $platform['component_access_token'];
        $param = [
            'authorizer_appid'          => $wechat['appid'],
            'authorizer_refresh_token'  => $wechat['refresh_token'],
            'component_appid'           => $platform['appid'],
            // 'component_access_token'    => $platform['component_access_token'],
        ];

        $rel = CallCurl($url, $param, true, 'json');
        $rel = json_decode($rel, 1);

        if (!isset($rel['authorizer_access_token'])) {
            throw new \Exception('授权公众号 authorizer_access_token 获取失败！' . $rel['errmsg'], $rel['errcode']);
        }
        $data = [
            'refresh_token' => $rel['authorizer_refresh_token'],
            'token'         => $rel['authorizer_access_token'],
            'token_out'     => time() + $rel['expires_in'] - 600,
        ];
        $wechat['refresh_token'] = $data['refresh_token'];
        $wechat['token_out'] = $data['token_out'];
        $wechat['token'] = $data['token'];
        // 更新信息到数据库
        $this->getPlatformWechatsInWechatTrait()->update($data, $wechat['id']);
        $this->getPlatformWechatsInWechatTrait()->ClearCache($wechat['customer_id'], $wechat['id']);
        return $wechat;
    }


    /**
     * 获取state信息
     * @param array $state
     */
    protected function stateInfo($state=[]) {
        if ($state && is_array($state)) {
            $state = Array2String($state, true);
        } else {
            if (!$state) {
                $state = request()->input('state');
            }
            $state = Array2String($state);
        }
        /*if ($state) {
            // 转换state信息
            if (is_array($state)) {
                $str = '';
                foreach ($state as $k=>$v) {
                    $str .= $k . '=' . $v . ',';
                }
                $state = substr($str, 0, -1);
            }
            $state = urlencode($state);
        } else {
            // 解码state信息
            $state = request()->input('state');
            $state = urldecode($state);
            $arr = explode(',', $state);
            $data = [];
            foreach ($arr as $va) {
                $tem = explode('=', $va);
                $data[$tem[0]] = $tem[1];
            }
            $state = $data;
        }*/

        return $state;
    }
    /**
     * 生成带参数二维码
     * @param int $customer_id 客户ID
     * @param array $scene_str_arr 场景参数内容
     */
    public function ProductParamsQRCode($customer_id, array $scene_str_arr)
    {
        $this->initWechatTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='. $this->wechat['token'];
        $params = [
            'expire_seconds' => 3600,
            'action_name'    => 'QR_STR_SCENE',
            'action_info'    => [
                'scene'      => [
                    'scene_str' => Array2String($scene_str_arr),
                ]
            ]
        ];
        $rel = CallCurl($url, $params, true, 'json');
        $rel = json_decode($rel, 1);
        if (!isset($rel['ticket']) || !isset($rel['url'])) {
            throw new \Exception('二维码创建失败！', 2000);
        }
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $rel['ticket'];
        $rel = @file_get_contents($url);

        /*$rel = base64_encode($rel);
        $qrcode = 'data:image/png;base64,' . $rel;
        die("<img src='{$qrcode}'>");*/

        return $rel;
    }
    /**
     * 配置公众号菜单
     * @param int $customer_id 客户ID
     * @param array $menus 菜单列表
     */
    public function ProductWechatMenu($customer_id, array $menus)
    {
        $this->initWechatTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='. $this->wechat['token'];

        $rel = CallCurl($url, $menus, true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('菜单配置失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 获取模板消息的模板列表
     * @param int $customer_id 客户ID
     */
    public function GetAllPrivateTemplate($customer_id)
    {
        $this->initWechatTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='. $this->wechat['token'];

        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('获取模板消息的模板列表失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 发送模板消息
     * @param int $customer_id 客户ID
     * @param array $content 模板消息内容
     * @param bool $sock 采用 fsockopen 形式发送
     */
    public function SendTemplate($customer_id, $content, $sock = false)
    {
        $this->initWechatTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='. $this->wechat['token'];

        if ($sock) {
            // socket 发送模板消息
            // return $this->sockPushTemplate($this->wechat['token'], $content);
            return SockPushTemplate($url, $content);
        }
        $rel = CallCurl($url, $content, 'true', 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('发送模板消息失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 推送公众号模板消息
     * @param string $token
     * @param array $content 模板消息内容
     */
    private function sockPushTemplates($token, $content) {
        $params = json_encode($content,JSON_UNESCAPED_UNICODE);
        $fp = fsockopen('api.weixin.qq.com', 80, $error, $errstr, 1);
        $http = "POST /cgi-bin/message/template/send?access_token={$token} HTTP/1.1\r\nHost: api.weixin.qq.com\r\nContent-type: application/x-www-form-urlencoded\r\nContent-Length: " . strlen($params) . "\r\nConnection:close\r\n\r\n$params\r\n\r\n";
        fwrite($fp, $http);
        fclose($fp);
    }
    /**
     * 发送客服消息
     * @param int $customer_id 客户ID
     * @param array $content 消息内容
     * @param bool $sock 采用 fsockopen 形式发送
     */
    public function SendCustomMsg($customer_id, $content, $sock = false)
    {
        $this->initWechatTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='. $this->wechat['token'];

        if ($sock) {
            // socket 发送模板消息
            return SockPushTemplate($url, $content);
        }
        $rel = CallCurl($url, $content, 'true', 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('发送客服消息失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }





    public function getPlatformWechatsInWechatTrait() {
        if (!isset($this->platformWechats) || !$this->platformWechats) {
            $this->platformWechats = new PlatformWechatRepository();
        }
        return $this->platformWechats;
    }
    public function getPlatformsInWechatTrait() {
        if (!isset($this->platforms) || !$this->platforms) {
            $this->platforms = new PlatformRepository();
        }
        return $this->platforms;
    }
    public function getUsersInWechatTrait() {
        if (!isset($this->users) || !$this->users) {
            $this->users = new UserRepository();
        }
        return $this->users;
    }
}
