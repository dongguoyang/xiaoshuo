<?php
namespace App\Logics\Traits;

use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatRepository;
use Illuminate\Support\Facades\Cache;

trait OfficialAccountTrait
{
    protected $wechat;

    protected $appId;
    protected $appSecret;
    protected $redirectUri;

    /**
     * 初始化类属性
     * @param int $customer_id
     */
    private function initOfficialAccountTrait($customer_id, $wechat_id = 0, $wechat = []) {
        if ($this->wechat && $this->wechat['token_out'] > time()) {
            return false; // 公众号已经实例化并且token未过期；就直接使用
        }
        if ($wechat_id) {
            $wechat = $this->getWechatsInOfficialAccountTrait()->getWechatForId($wechat_id); //获取公众平台信息
        } else {
            $wechat = $this->getWechatsInOfficialAccountTrait()->getWechatForCustomer($customer_id); //获取公众平台信息
        }
        if(!$wechat || !$wechat['status']){
            throw new \Exception('该状态已经关闭', 20002);
        }

        $this->appId = $wechat['appid'];
        $this->appSecret = $wechat['appsecret'];

        if (!$wechat['token'] || $wechat['token_out'] < time()) {
            $wechat = $this->updateWechatAccessToken($wechat);
        }
        $auth_domain = $wechat['redirect_uri'];
        $this->redirectUri = urlencode($auth_domain . route('h5wechat.redirect', ['wechat_id'=>$wechat['id']], false));
        $this->wechat = $wechat;
    }

    /**
     * 跳转获取授权code
     * @param int $customer_id
     * @param array $state
     * @param string $scope  snsapi_base为scope的网页授权，就静默授权的，用户无感知； snsapi_userinfo 获取用户详细信息
     */
    public function ToAuthCode($customer_id, $state = ['id'=>0], $scope = ''){
        $this->initOfficialAccountTrait($customer_id);

        $scope = $scope === 'tip' ? 'snsapi_userinfo' : 'snsapi_base';

        if(!$this->appId || !$this->appSecret || !$this->redirectUri){
            throw new \Exception('缺少配置信息', 20001);
        }
        $state = $this->stateInfo($state);
        // 公众号模式获取授权码
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appId}&redirect_uri={$this->redirectUri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
        return redirect($url);
    }
    /**
     * 跳转获取授权code
     * @param int $customer_id
     * @param array $state
     * @param string $scope  snsapi_base为scope的网页授权，就静默授权的，用户无感知； snsapi_userinfo 获取用户详细信息
     */
    public function ToPayWechatAuthCode($wechat, $state = ['id'=>0]){
        if(!$wechat['appid'] || !$wechat['appsecret'] || !$wechat['redirect_uri']){
            throw new \Exception('缺少配置信息', 20001);
        }
        $state = $this->stateInfo($state);
        $auth_domain = $wechat['redirect_uri'];
        $redirectUri = urlencode($auth_domain . route('h5wechat.login', [], false));
        $cache_key = md5($state);
        Cache::put($cache_key, $state, 3600);
        // 公众号模式获取授权码
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$wechat['appid']}&redirect_uri={$redirectUri}&response_type=code&scope=snsapi_base&state={$cache_key}#wechat_redirect";
        return redirect($url);
    }

    /**
     * 获取授权token和用户openid
     * 用于换区用户详细信息
     * @param int $type    novel-wechat 小说登录；pay-wechat 支付登录
     * @param array $wechat 公众号信息
     */
    public function GetAuthToken2Openid($type = 'novel-wechat', $wechat = []){
        $code  = request()->input('code');
        if (!$code) {
            throw new \Exception('未获取到 code 或 state 信息', 2000);
        }

        $state = $this->stateInfo(); // 获取state信息

        if ($type === 'novel-wechat') {
            if (request()->wechat_id > 0) {
                if (!$this->wechat ||
                    !isset($this->wechat['id']) ||
                    $this->wechat['id'] != request()->wechat_id) {
                    $this->wechat = null;
                }
                $this->initOfficialAccountTrait(0, request()->wechat_id);
            } else {
                $this->initOfficialAccountTrait($state['c']);
            }
        } else {
            $this->wechat = $wechat;
        }

        // 公众号模式获取 网页授权access_token 和 openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wechat['appid']}&secret={$this->wechat['appsecret']}&code={$code}&grant_type=authorization_code";

        // 这他妈的不知道怎么地就偶尔提示 access_token 异常
        $i = 0;
        $err_str = '';
        while ($i < 3) {
            $i++;
            $rel = @file_get_contents($url);
            $rel = json_decode($rel, 1);
            if (isset($rel['access_token'])) {
                break;
            }

            if (isset($rel['errmsg'])) {
                $err_str .= $i . ' = ' . $this->wechat['appid'] . '___' . $rel['errmsg'] . '___' . $code . '______';
            }
        }
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            $err_str .= '__we_id='.request()->wechat_id.'__we_na='.$this->wechat['name'].'__'.$this->wechat['appid'];
            if ($wechat) {
                // 表示是支付号授权
                $err_str .= '__pppy';
            }
            throw new \Exception($err_str, $rel['errcode']);
        }
        return [$rel['openid'], $rel['access_token']];
    }
    /**
     * 获取微信那边的用户详细信息
     * @param string $access_token
     * @param string $openid
     */
    public function GetWechatUserDetail($customer_id, $access_token, $openid) {
        if ($customer_id) {
            $this->initOfficialAccountTrait($customer_id);
        }

        // 采用网页授权 token 获取用户的信息；获取不到 是否关注
        $wechat_user =  $this->getWechatUserInfo($access_token, $openid);

        // 采用 token 获取用户的详细信息 可以获取是否关注
        try {
            $temp =  $this->getWechatUserInfo($this->wechat['token'], $openid, true);
            return array_merge($wechat_user, $temp);
        } catch (\Exception $e) {
            $wechat_user['subscribe'] = 0;
            return $wechat_user;
        }
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
            throw new \Exception('信息获取失败！' . $rel['errmsg'], 2003);
        }
        if(!$rel['openid']){
            //当获取失败时
            throw new \Exception('invalid openid', 40003);
        }
        return $rel;
    }

    /**
     * 更新 公众号 access_token
     * @param array $wechat 公众号数据
     * @param array $platform 开放平台数据
     * @return array $wechat
     */
    private function updateWechatAccessToken($wechat) {
        $passwd = md5($wechat['appid'] . 'zmr-37892jkfds' . $wechat['appsecret']);
        $url = "http://47.111.250.48/wechat_token.php?func=getAccessToken&appid={$wechat['appid']}&app_secret={$wechat['appsecret']}&passwd={$passwd}";
        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);

        /*
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$wechat['appid']}&secret={$wechat['appsecret']}";
        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        */

        if (!isset($rel['access_token'])) {
            throw new \Exception('公众号 access_token 获取失败！' . $rel['errmsg'], $rel['errcode']);
        }
        $data = [
            'token'         => $rel['access_token'],
            'token_out'     => time() + $rel['expires_in'] - 1800,
        ];
        $wechat['token_out'] = $data['token_out'];
        $wechat['token']     = $data['token'];
        // 更新信息到数据库
        $this->getWechatsInOfficialAccountTrait()->update($data, $wechat['id']);
        $this->getWechatsInOfficialAccountTrait()->ClearCache($wechat['customer_id'], $wechat['id']);
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
                if(isset($state) &&cache($state)){
                    $state  = cache($state);
                }
            }
            $state = Array2String($state);
        }

        return $state;
    }
    /**
     * 生成带参数二维码
     * @param int $customer_id 客户ID
     * @param array $scene_str_arr 场景参数内容
     */
    public function ProductParamsQRCode($customer_id, array $scene_str_arr, $action_name = 'QR_STR_SCENE')
    {
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='. $this->wechat['token'];
        $params = [
            // 'expire_seconds' => 3600,
            'action_name'    => $action_name,
            'action_info'    => [
                'scene'      => [
                    'scene_str' => Array2String($scene_str_arr),
                ]
            ]
        ];
        if (in_array($action_name, ['QR_SCENE', 'QR_STR_SCENE'])) {
            $params['expire_seconds'] = 3600;
        }
        $i = 0;
        while ($i < 3) {
            $rel = CallCurl($url, $params, true, 'json');
            $rel = json_decode($rel, 1);
            if (isset($rel['ticket']) && isset($rel['url'])) break;
            $i++;
            //usleep(500000); // 延迟500毫秒再请求章节内容
        }
        if (!isset($rel['ticket']) || !isset($rel['url'])) {
            throw new \Exception('二维码创建失败！' . $rel['errmsg'], 2000);
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
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='. $this->wechat['token'];

        $rel = CallCurl($url, $menus, true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('菜单配置失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    
    /**
     * 取消配置公众号菜单Del
     * @param int $customer_id 客户ID
     * @param array $menus 菜单列表
     */
    public function ProductWechatDelMenu($customer_id)
    {
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='. $this->wechat['token'];

        $rel = CallCurl($url, '', true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('菜单配置失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    
    /**
     * 配置公众号个性化菜单
     * @param int $customer_id 客户ID
     * @param array $menus 菜单列表
     */
    public function ProductWechatConditionMenu($customer_id, array $menus)
    {
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/addconditional?access_token='. $this->wechat['token'];

        $rel = CallCurl($url, $menus, true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('菜单配置失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 配置公众号标签，获取标签信息
     * @param int $customer_id 客户ID
     * @param string $type 操作类型
     * @param array $tags 操作数据
     */
    public function TagsManage($customer_id, $type, array $tags = [])
    {
        if (!in_array($type, ['create', 'get', 'update', 'delete'])) {
            throw new \Exception('操作标签类型异常！', 2000);
        }
        $this->initOfficialAccountTrait($customer_id);

        $url = 'https://api.weixin.qq.com/cgi-bin/tags/'. $type .'?access_token='. $this->wechat['token'];

        if ($tags) {
            // 创建，编辑，删除标签
            $rel = CallCurl($url, $tags, true, 'json', JSON_UNESCAPED_UNICODE);
        } else {
            // 获取标签信息
            $rel = CallCurl($url);
        }
        $rel = json_decode($rel, 1);

        $menu_names = [
            'create' => '创建标签',
            'get'    => '获取公众号已创建的标签',
            'update' => '编辑标签',
            'delete' => '删除标签',
        ];
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception($menu_names[$type] . '失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 管理用户标签
     * @param int $customer_id 客户ID
     * @param string $type 操作类型
     * @param array $data 操作数据
     */
    public function UserTagManage($customer_id, $type, array $data = [])
    {
        $urls = [
            'batch-tag'     => 'https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token=',
            'batch-un-tag'  => 'https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token=',
            'batch-tag-list'=> 'https://api.weixin.qq.com/cgi-bin/tags/getidlist?access_token=',
        ];
        if (!isset($urls[$type])) {
            throw new \Exception('操作标签类型异常！', 2000);
        }
        $this->initOfficialAccountTrait($customer_id);

        $url = $urls[$type] . $this->wechat['token'];

        $rel = CallCurl($url, $data, true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);

        $menu_names = [
            'batch-tag'     => '为用户打标签',
            'batch-un-tag'  => '为用户取消标签',
            'batch-tag-list'=> '获取用户身上的标签列表',
        ];
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception($menu_names[$type] . '失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 获取模板消息的模板列表
     * @param int $customer_id 客户ID
     */
    public function GetAllPrivateTemplate($customer_id)
    {
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token='. $this->wechat['token'];

        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('获取模板消息的模板列表失败！' . $rel['errmsg'], 2000);
        }

        return $rel;
    }
    /**
     * 获取用户列表
     * @param int $wechat_id 微信ID
     * @param string $next_openid 微信下一页第一个openID
     */
    public function GetUserList($customer_id, $next_openid = '') {
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='. $this->wechat['token'];// .'&next_openid='. $next_openid;
        if ($next_openid) {
            $url .= '&next_openid='. $next_openid;
        }

        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('获取用户列表失败！' . $rel['errmsg'], 2000);
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
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='. $this->wechat['token'];
        if ($sock) {
            // socket 发送模板消息
            //return SockPush($url, $content);
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
        $this->initOfficialAccountTrait($customer_id);
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



    /**
     * 返还 emoji 表情图片
     * @param int $num
     */
    public function emojiArr($num = 1) {
        $emojies = [
            '❤', '💝', '💔', '💓', '💘', '💖', '💗', '💞', '💕', '❣',
            '💤', '💢', '👣', '💋', '💎', '👙', '👓', '💌', '💥', '🍡',
            '🍹', '🍾', '🍗', '🥤', '🍫', '🎈', '🎉', '🎀', '🎄', '🎁',
            '🎊', '🎶', '🥇', '💯', '🌹', '🌸', '🌺', '🌼', '🌻', '🌷',
            '💐', '☘', '🍀', '🌴', '🌾', '🍁', '🍂', '🍃', '🌱', '🌿',
            '🌳', '🔥', '✨', '🌟', '💫', '☄', '🌙', '⚡', '⭐', '☀',
            '🌞', '🌛', '🌜', '🐾', '💏', '👄', '🤙', '💃', '🤝', '🤯',

            '😘', '😇',
        ];

        if ($num == 1) {
            return $emojies[mt_rand(0, 70)];
        } else {
            $i = 0;
            $rel = [];
            while ($i < $num) {
                $rel[] = $emojies[mt_rand(0, 70)];
                $i++;
            }
            return $rel;
        }
    }
    /**
     * 返回跳转JS代码
     * @param string $hostUrl 跳转地址
     */
    public function clickATag($hostUrl) {
        $now = time();
        $html = "<!DOCTYPE html><html><head><title>.....</title><meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <style>#aa{$now}: {color: #E1E1E1;text-decoration: none;}
                .loader{$now}{position:fixed!important;top: 50%;left: 50%;margin:auto;margin-top: -3em;margin-left: -3em;font-size:10px;text-indent:-9999em;width:6em;height:6em;border-radius:50%;background:#959595;background:-moz-linear-gradient(left,#959595 10%,rgba(255,255,255,0) 42%);background:-webkit-linear-gradient(left,#959595 10%,rgba(255,255,255,0) 42%);background:-o-linear-gradient(left,#959595 10%,rgba(255,255,255,0) 42%);background:-ms-linear-gradient(left,#959595 10%,rgba(255,255,255,0) 42%);background:linear-gradient(to right,#959595 10%,rgba(255,255,255,0) 42%);position:relative;-webkit-animation:load3 1.4s infinite linear;animation:load3 1.4s infinite linear;-webkit-transform:translateZ(0);-ms-transform:translateZ(0);transform:translateZ(0)}
                .loader{$now}:before{width:50%;height:50%;background:#959595;border-radius:100% 0 0 0;position:absolute;top:0;left:0;content:''}
                .loader{$now}:after{background:#fff;width:75%;height:75%;border-radius:50%;content:'';margin:auto;position:absolute;top:0;left:0;bottom:0;right:0}@-webkit-keyframes load3{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes load3{0%{-webkit-transform:rotate(0deg);transform:rotate(0deg)}100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}</style>
                </head><body>";
        $html .= "<div class='loader{$now}'>  <a id='aa{$now}' href='{$hostUrl}'>登录中......</a>  </div>";
        $html .= "<script> document.getElementById('aa{$now}').click(); </script></body></html>";
        //$html .= "<script> window.location.href = '{$hostUrl}'; </script>";
        // return redirect($hostUrl);
        return $html;
    }
    public function getWechatsInOfficialAccountTrait() {
        if (!isset($this->wechats) || !$this->wechats) {
            $this->wechats = new WechatRepository();
        }
        return $this->wechats;
    }
    public function getUsersInOfficialAccountTrait() {
        if (!isset($this->users) || !$this->users) {
            $this->users = new UserRepository();
        }
        return $this->users;
    }
    public function getCommonSetsInOfficialAccountTrait() {
        if (!isset($this->commonSets) || !$this->commonSets) {
            $this->commonSets = new CommonSetRepository();
        }
        return $this->commonSets;
    }
    
    /**
     * 生成短连接
     */
    public function getShorturl($customer_id,$longUrl){
        $this->initOfficialAccountTrait($customer_id);
        $url = 'https://api.weixin.qq.com/cgi-bin/shorturl?access_token='. $this->wechat['token'];
        $data = [
            'access_token'=>$this->wechat['token'],
            'action'=>'long2short',
            'long_url'=>$longUrl,
        ];
        $rel = CallCurl($url, $data, true, 'json', JSON_UNESCAPED_UNICODE);
        $rel = json_decode($rel, 1);
        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception('短连接生成失败！' . $rel['errmsg'], 2000);
        }
        return $rel;
    }
}
