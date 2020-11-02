<?php
namespace App\Logics\Traits;

use App\Libraries\Facades\Curl;
use App\Libraries\Facades\Tools;

trait WechatSignTrait {
	protected $appId; // 公众号appid
	protected $appSecret; // 公众号app_secret
	protected $component_appid; // 开放平台appid
	protected $signWechat; // 签名使用的公众号

	/**
	 * 配置公众号信息
	 */
	public function confWechat($appId, $appSecret, $component_appid = '') {
		$this->appId = $appId;
		$this->appSecret = $appSecret;
		$this->component_appid = $component_appid;

		$map = [['appid', '=', $appId]];
		if ($component_appid) {
		    // 开放平台模式签名
			$map[] = ['component_appid', '=', $component_appid];
            $this->signWechat = $this->platWechat->findByMap($map, $this->platWechat->model->fields);
		} else {
            // 公众号模式签名
            $this->signWechat = $this->wechat->findByMap($map, $this->wechat->model->fields);
        }
	}
	// 签名
	public function getSignPackage($url = '') {
		$jsapiTicket = $this->getJsApiTicket();

		// 注意 URL 一定要动态获取，不能 hardcode.
		$url = $url ? $url : $this->get_url();
		$timestamp = time();
		$nonceStr = Tools::RandCode(16);
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);

		$signPackage = array(
			"jsapiTicket" => $jsapiTicket,
			"appId" => $this->appId,
			"nonceStr" => $nonceStr,
			"timestamp" => $timestamp,
			"url" => $url,
			"rawString" => $string,
			"signature" => $signature,
		);

		return $signPackage;
	}
	private function get_url() {
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
		return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
	}

	/**
	 * 根据 access_token 获取 icket
	 * @return type
	 */
	public function getJsApiTicket() {
		if ($this->signWechat['js_api_ticket_out_time'] > time()) {
			return $this->signWechat['js_api_ticket'];
		}

		$access_token = $this->getAccessToken();

		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
		$return = Curl::callCurl($url);
		$return = json_decode($return, 1);
		if (!isset($return['ticket'])) {
			throw new \Exception('获取ticket失败！' . $return['errmsg'], $return['errcode']);
		}
		// dump($return);
        if ($this->component_appid)
        {
            $this->platWechat->update([
                'js_api_ticket' => $return['ticket'],
                'js_api_ticket_out_time' => $return['expires_in'] + time() - 200,
            ], $this->signWechat['id']);
        }
        else
        {
            $this->wechat->update([
                'js_api_ticket' => $return['ticket'],
                'js_api_ticket_out_time' => $return['expires_in'] + time() - 200,
            ], $this->signWechat['id']);
        }

		return $return['ticket'];
	}
	/**
	 * 获取 网页授权登录access token
	 * @return type
	 */
	public function getAccessToken() {
		if ($this->component_appid) {
			// 开放平台模式才有 component_appid 所以请求的第三方的 token
			return $this->getThirdAccessToken();
		}

		// 以下代码是  普通的公众号模式获取token
		if ($this->signWechat['token_out'] > time()) {
			return $this->signWechat['token'];
		}

		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->appId}&secret={$this->appSecret}";
		$return = Curl::callCurl($url);
		$return = json_decode($return, 1);
		if (!isset($return['access_token'])) {
			throw new \Exception('获取token失败！' . $return['errmsg'], $return['errcode']);
		}

		$this->wechat->update([
			'token' => $return['access_token'],
			'token_out' => $return['expires_in'] + time() - 200,
		], $this->signWechat['id']);

		return $return['access_token'];
	}

	// 获取开放平台模式的一般的 access_token
	private function getThirdAccessToken($time_need = 0) {
		if ($this->signWechat['token_out'] < time()) {
			// token 过期，重新获取
			$data = $this->refreshToken();

			if ($time_need) {
				return ['token' => $data['token'], 'out_time' => $data['token_out']];
			} else {
				return $data['token'];
			}
		}

		if ($time_need) {
			return ['token' => $this->signWechat['token'], 'out_time' => $this->signWechat['token_out']];
		} else {
			return $this->signWechat['token'];
		}
	}

	/**
	 * 刷新公众号 token
	 * @param $wechat 微信公众号对象
	 * @param $re 重复请求次数
	 */
	private function refreshToken($re = 0) {
		$component_access_token = $this->getComponentToken();
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_authorizer_token?component_access_token=' . $component_access_token;

		$data = [
			'component_appid' => $this->signWechat['component_appid'],
			'authorizer_appid' => $this->signWechat['appid'],
			'authorizer_refresh_token' => $this->signWechat['refresh_token'],
		];
		$data = Curl::callCurl($url, $data, 1, 'json');
		$data = json_decode($data, 1);
		if (!isset($data['authorizer_access_token'])) {
			if ($re > 2) {
				throw new \Exception('获取authorizer_access_token失败！' . $data['errmsg'], $data['errcode']);
			}
			sleep(2);
			return $this->refreshToken(++$re);
		}

		$out_time = time() + $data['expires_in'] - 200;
		$this->platWechat->update([
			'token' => $data['authorizer_access_token'],
			'token_out' => $out_time,
			'refresh_token' => $data['authorizer_refresh_token'],
		], $this->signWechat['id']);

		return ['token' => $data['authorizer_access_token'], 'out_time' => $out_time];
	}
	/**
	 * 获取 component_access_token
	 * @param $appid 开放平台appid
	 * @param $time_need 是否需要返回过期时间
	 * @param $re 重复请求次数
	 */
	private function getComponentToken($time_need = 0, $re = 0) {
		$plat = $this->platform->findBy('appid', $this->signWechat['component_appid'], ['appid', 'app_secret', 'component_verify_ticket', 'component_access_token', 'token_out_time']);

		if ($plat['token_out_time'] > time()) {
			// token未过期，直接返回
			if ($time_need) {
				return ['token' => $plat['component_access_token'], 'out_time' => $plat['token_out_time']];
			} else {
				return $plat['component_access_token'];
			}
		}
		$url = 'https://api.weixin.qq.com/cgi-bin/component/api_component_token';
		// 请求参数
		$data = ['component_appid' => $plat['appid'], 'component_appsecret' => $plat['app_secret'], 'component_verify_ticket' => $plat['component_verify_ticket']];

		// curl请求
		$data = Curl::callCurl($url, $data, 1, 'json');
		// 解析结果

		$data = json_decode($data, 1);
		// 如果错误，就重复请求
		if (!isset($data['component_access_token'])) {
			if ($re > 2) {
				// 最大请求3次
				throw new \Exception('获取component_access_token失败！' . $data['errmsg'], $data['errcode']);
			}
			sleep(2);
			return $this->getComponentToken($time_need, ++$re);
		}
		$token_out_time = time() + $data['expires_in'] - 200;
		$this->platform->update(['component_access_token' => $data['component_access_token'], 'token_out_time' => $token_out_time], $plat['id']);
		if ($time_need) {
			return ['token' => $plat['component_access_token'], 'out_time' => $token_out_time];
		} else {
			return $data['component_access_token'];
		}
	}
	/*

		    // 向用户推送消息
		    public function push_msg($openid,$content){
		        $access_token = get_access_token();
		        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		        $post_arr = array(
		            'touser'=>$openid,
		            'msgtype'=>'text',
		            'text'=>array(
		                'content'=>$content,
		            )
		        );
		        $post_str = json_encode($post_arr,JSON_UNESCAPED_UNICODE);
		        $return = httpRequest($url,'POST',$post_str);
		        $return = json_decode($return,true);
		        return $return;
		    }

		     //向用户推送卡券
		    public function push_card($openid,$cardid,$cardext,$count){
		        $access_token = get_access_token();
		        $url ="https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token={$access_token}";
		        $post_arr = array(
		            'touser'=>$openid,
		            'msgtype'=>'wxcard',
		            'wxcard'=>array(
		                "card_id"=>$cardid,
		                "card_ext"=>json_encode($cardext)
		            )
		        );
		        //dump($post_arr);exit;
		        $post_str = json_encode($post_arr,JSON_UNESCAPED_UNICODE);
		        $return = httpRequest($url,'POST',$post_str);
		        $return = json_decode($return,true);
		        if($return['errcode']=='40001' && $count<5){
		            M('wx_user')->where('id=1')->save(['web_expires'=>0]);
		            $count++;
		            return $this->push_card($openid,$cardid,$cardext,$count);
		        }
		        return $return;
		    }

		    // 卡券签名
		    public function getSignCard($data) {
		        $tmpArr = $data;
		        $tmpArr['api_ticket'] = $this->getApiTicket();
		        //$tmpArr['timestamp'] = time();
		        //$tmpArr['nonce_str'] = $nonce_str;
		        //$tmpArr['openid'] = $openid;
		        //$tmpArr['card_id'] = $card_id;
		        //dump($timestamp);exit;
		        //$tmpArr['']
		        //dump($tmpArr);
		        sort($tmpArr, SORT_STRING);
		        $tmpStr = implode($tmpArr);
		        $signature = sha1($tmpStr);
		        //dump($tmpArr);dump($signature);
		        return $signature;
		    }
	*/

	// 允许跨域
	public function CrossHttp() {
		// header('Access-Control-Allow-Origin:*');
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
		header('Access-Control-Allow-Origin:' . $origin);
		header('Access-Control-Allow-Headers:' . 'Origin, Content-Type, Cookie, Accept, X-Requested-With, Content-Type');
		header('Access-Control-Allow-Methods:' . 'GET, POST, PATCH, PUT, OPTIONS');
		// $response->header('Access-Control-Allow-Credentials', 'false');
		header('Access-Control-Allow-Credentials:' . 'true'); // 跨域时允许 cookie
	}
	// 检测 来路地址是否异常
	public function CheckReffer() {
		$refer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
		if (request()->input('zmrtest'))
        {
            $refer = $this->get_url();
        }

		if (!$refer) {
			die('HTTP_REFERER is null');
		}
		return $refer;
	}
	// 签名地址检测
	public function checkUrl($refer) {
		//获取待签名URL
		$url = request()->input('url');

		if ($url && stripos($url, '%2F'))
        {
            $url = urldecode($url);
        }
		$url = str_replace('&amp;', '&', $url);

		$refer_host = parse_url($refer, PHP_URL_HOST);
		$sgin_host = parse_url($url, PHP_URL_HOST);

		// 检测待签名URL是否均不为空且一致
		if (empty($sgin_host) OR empty($refer_host) OR $refer_host != $sgin_host) {
			exit('sign err ' . request()->input('api'));
		}
		return $url;
	}

}