<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Wechat;
use App\Logics\Repositories\Repository;

class WechatRepository extends Repository {
	public function model() {
		return Wechat::class;
	}
    /**
     * 通过customer_id 获取公众号信息
     * @param int $customer_id
     * @return array
     */
    public function getWechatForCustomer($customer_id) {
        $info = $this->findByMap([['customer_id', $customer_id], ['status', 1]], $this->model->fields);
        return $this->toArr($info);
    }
    /**
     * 通过customer_id 获取公众号信息
     * @param int $customer_id
     * @return array
     */
    public function getWechatForId($id) {
        $info = $this->find($id, $this->model->fields);
        return $this->toArr($info);
    }
    /**
     * 通过 $appid 获取公众号信息
     * @param string $appid
     * @return array
     */
    public function getWechatForAppid($appid) {
        $info = $this->findBy('appid', $appid, $this->model->fields);
        return $this->toArr($info);
    }

    public function ClearCache($customer_id, $id) {
        return true;
    }






    private $wechat;
	/**
	 * 实例化公众号信息
     * @param int $type
     * @param bool $return
     */
	public function initWechat($type, $return = false) {
        $info = $this->model->where('status', 1)->where('type', $type)->select($this->model->fields)->first();

        if (!$info) {
            throw new \Exception('公众号异常，请确认！', 2000);
        }

        $info = $this->toArr($info);
        if ($return) {
            return $info;
        }
        $this->wechat =  $info;
    }
    /**
     * 跳转获取授权code
     */
    public function ToAuthCode($state = ['id'=>0]){
        $this->initWechat(1);

        if(!$this->wechat['appid'] || !$this->wechat['appsecret'] || !$this->wechat['redirect_uri']){
            throw new \Exception('缺少配置信息', 2001);
        }

        $state = Array2String($state);

        // 公众号模式获取授权码
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=". $this->wechat['appid'] ."&redirect_uri=". $this->wechat['redirect_uri'] ."&response_type=code&scope=snsapi_base&state=$state#wechat_redirect"; // scope snsapi_base 静默授权；snsapi_userinfo 获取用户的基本信息的

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

        $state = request()->input('state');// 获取state信息
        $state = Array2String($state);

        $this->initWechat(1);
        // 公众号模式获取 网页授权access_token 和 openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->wechat['appid']}&secret={$this->wechat['appsecret']}&code={$code}&grant_type=authorization_code";

        $rel = @file_get_contents($url);
        $rel = json_decode($rel, 1);

        if (isset($rel['errcode']) && $rel['errcode'] > 100) {
            throw new \Exception($rel['errmsg'], $rel['errcode']);
        }
        $openid = $rel['openid'];
        $access_token = $rel['access_token'];

        return [$openid, $access_token, $this->wechat, $state];
    }

}