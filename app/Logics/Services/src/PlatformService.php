<?php

namespace App\Logics\Services\src;

use App\Logics\Repositories\src\DomainRepository;
use App\Logics\Repositories\src\NovelRepository;
use App\Logics\Repositories\src\PlatformRepository;
use App\Logics\Repositories\src\PlatformWechatRepository;
use App\Logics\Repositories\src\TestRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Repositories\src\WechatConfigRepository;
use App\Logics\Repositories\src\WechatMsgReplyRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\PushmsgTrait;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Encryptor;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;

class PlatformService extends Service {
    use PushmsgTrait;
	protected $openPlatform; // easy wechat 的 openPlarfotm 对象
	protected $Encryptor;

    protected $platforms;
    protected $platformWechats;
    protected $tests;
    protected $wechatMsgReplies;
    protected $wechatConfigs;
    protected $novels;
    protected $users;
    protected $domains;

	public function Repositories() {
		return [
			'platforms'         => PlatformRepository::class,
			'platformWechats'   => PlatformWechatRepository::class,
			'tests'             => TestRepository::class,
            'wechatMsgReplies'  => WechatMsgReplyRepository::class,
            'wechatConfigs'     => WechatConfigRepository::class,
            'novels'            => NovelRepository::class,
            'users'             => UserRepository::class,
            'domains'           => DomainRepository::class,
		];
	}
	/*public function __construct()
		    {
		        parent::__construct();
		        $this->setOpenPlatform();
	*/
	/**
	 * 获取开放平台信息
	 */
	private function setOpenPlatform($appid = '') {
		$appid = $appid ?: request()->input('appid');
		$plat = $this->platforms->findBy('appid', $appid, $this->platforms->model->fields);
		$config = [
			'app_id' => $plat['appid'],
			'secret' => $plat['appsecret'],
			'token'  => $plat['token'],
			'aes_key'=> $plat['token_key'],

			'component_verify_ticket'  => $plat['component_verify_ticket'],
			'authorizer_refresh_token' => $plat['authorizer_refresh_token'],
			'component_access_token'   => $plat['component_access_token'],
		];

		$this->openPlatform = Factory::openPlatform($config);
	}
	/**
	 * 跳转授权页
	 */
	public function wechatToAuth() {
		$appid = request()->input('appid');
		if (!$appid) {
			throw new \Exception("Appid 异常！", 2000);
		}
		$this->setOpenPlatform($appid);

		$redirect = route('platform.authed', ['appid' => $appid, 'customer_id' => $this->getCustomer()]);
		$url = $this->openPlatform->getPreAuthorizationUrl($redirect); // 传入回调URI即可
		return redirect($url);
	}
	private function getCustomer() {
	    $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
	    if ($customer && isset($customer->id)) {
	        return $customer->id;
        } else {
	        return 0;
        }
    }
	/**
	 * 显示授权的开放平台信息页面
	 */
	public function wechatAuth() {
		$appid = request()->input('appid');
		if (!$appid) {
			// throw new \Exception("AppId 不能为空", 2000);

            $plat = $this->platforms->findBy('status', 1, $this->platforms->model->fields);
		} else {
            $plat = $this->platforms->findBy('appid', $appid, $this->platforms->model->fields);
        }

		return $plat;
	}
	/**
	 * 授权完成跳转的页面
	 */
	public function wechatAuthed() {
		$appid = request()->input('appid');

		if (!$appid) {
			throw new \Exception("AppId 不能为空", 2000);
		}
		$this->setOpenPlatform($appid);

		$wechat = $this->setAuthWechatInfo($appid); // 将公众号加入数据库
		/*$authInfo = $this->openPlatform->handleAuthorize();
	        dump($authInfo);
	        $authInfo = $this->openPlatform->getAuthorizer($appid);
	        dump($authInfo);
	        $conf_val = $conf_name = '';
	        $authInfo = $this->openPlatform->getAuthorizerOption($appid, $conf_name);
	        dump($authInfo);
	        $authInfo = $this->openPlatform->setAuthorizerOption($appid, $conf_name, $conf_val);
	        dump($authInfo);
	        $authInfo = $this->openPlatform->getAuthorizers(0, 500);
*/

		$plat = $this->platforms->findBy('appid', $appid, $this->platforms->model->fields);

		return [$plat, $wechat];
	}
	private function setAuthWechatInfo($appid) {
		$plat = $this->platforms->findBy('appid', $appid, $this->platforms->model->fields);
		if (!$plat) {
			throw new \Exception("开放平台不存在！", 2000);
		}
		$authorization_info = $this->openPlatform->handleAuthorize()['authorization_info'];
		$authorizer_appid = $authorization_info['authorizer_appid'];
		$authorizer_info = $this->openPlatform->getAuthorizer($authorizer_appid)['authorizer_info'];
		$now = time();
		$wechat = [
            'customer_id'       => request()->input('customer_id'),
            'platform_id'       => $plat['id'],
            'component_appid'   => $plat['appid'],
			'appid'             => $authorization_info['authorizer_appid'],
			'app_secret'        => '',
			//'js_api_ticket_out_time',
			//'js_api_ticket',
			// 'api_type'          => $plat['api_type'],
			//'use_time',
			'origin_id'         => $authorizer_info['user_name'],
			'app_name'          => $authorizer_info['nick_name'],
			'service_type'      => $authorizer_info['service_type_info']['id'],
			'img'               => $authorizer_info['qrcode_url'],
			'auth_time'         => $now,
			//'auth_out_time',
			'token'             => $authorization_info['authorizer_access_token'],
			'token_out'         => $authorization_info['expires_in'] + $now - 200,
			'refresh_token'     => $authorization_info['authorizer_refresh_token'],
			//'refresh_out',
			//'verify_ticket',
			'type' => 1,
			'status' => 1,
			//'domain',
			/*'msg',
	            'menu',
	            'web',
	            'notice',
	            'user',
	            'source',
	            'shop',
	            'card',
	            'store',
	            'scan',
	            'wifi',
	            'shake',
	            'city',
	            'ad',
	            'receipt',
	            'reg_miniapp',
	            'man_miniapp',
	            'calorie',
	            'account',
	            'kefu',
	            'open_account',
	            'plugin',
	            'addr',
*/
		];

		$func_info = [];
		foreach ($authorization_info['func_info'] as $k1 => $v1) {
			$func_info[] = $v1['funcscope_category']['id'];
		}
		$funcs = $this->funcInfo('all');
		$fillable = $this->platformWechats->model->fillable;
		foreach ($funcs as $k => $v) {
			if (in_array($v, $fillable)) {
				if (in_array($k, $func_info)) {
					$wechat[$v] = 1;
				} else {
					$wechat[$v] = 0;
				}
			}
		}

		if ($had = $this->platformWechats->findBy('appid', $wechat['appid'], ['id'])) {
			$this->platformWechats->update($wechat, $had['id']);
		} else {
			$wechat = $this->platformWechats->create($wechat);
			// 如果没有公众号配置信息就新增一条数据
			if ($had = $this->wechatConfigs->findByMap([['customer_id', $wechat['customer_id']], ['platform_wechat_id', $wechat['id']]], ['id'])) {} else {
			    $this->wechatConfigs->create(['customer_id'=>$wechat['customer_id'], 'platform_wechat_id'=>$wechat['id']]);
            }
		}
		return $wechat;
	}
	/**
	 * 授权后的 func_info 列表
	 * @param int $id 返回id对应的名称
	 * @param bool $all 返回功能数组列表
	 * @return fixed
	 */
	public function funcInfo($id) {
		$arr = [
			1 => 'msg',
			2 => 'user',
			3 => 'account',
			4 => 'web',
			5 => 'shop',
			6 => 'kefu',
			7 => 'notice',
			8 => 'card',
			9 => 'scan',
			10 => 'wifi',
			11 => 'source',
			12 => 'shake',
			13 => 'store',
			14 => 'pay',
			15 => 'menu',
			16 => 'identification',
			17 => 'account',
			18 => 'develop',
			19 => 'kefu',
			20 => 'login',
			21 => 'da',
			22 => 'city',
			23 => 'ad',
			24 => 'open_account',
			25 => 'open_account',
			26 => 'receipt',
		];

		if ($id === 'all') {
			return $arr;
		} else {
			return isset($arr[$id]) ? $arr[$id] : '';
		}
	}

	/**
	 * 授权事件接收URL
	 * 获取ComponentVerifyTicket
	 */
	public function platformServer() {
		$xml = file_get_contents('php://input');
		$input = request()->all();
		$this->tests->create(['type' => 'xml', 'content' => $xml]);
		$this->tests->create(['type' => 'input', 'content' => http_build_query($input)]);

		$arr = XmlToArray($xml);

		$component_appid = isset($arr['AppId']) ? $arr['AppId'] : $input['cappid'];
		$plat = $this->platforms->findBy('appid', $component_appid, $this->platforms->model->fields);
		if (!$plat) {
			throw new \Exception('appid 异常，不存在对应开放平台。', 2000);
		}

		$encryptor = new Encryptor($plat['appid'], $plat['token'], $plat['token_key']);
		$info = $encryptor->decrypt($arr['Encrypt'], $input['msg_signature'], $input['nonce'], $input['timestamp']);

		$info = XmlToArray($info);
		$event_type = isset($info['InfoType']) ? $info['InfoType'] : $info['MsgType'];
		$this->tests->create(['type' => $event_type, 'content' => json_encode($info, JSON_UNESCAPED_UNICODE)]);

		if ($event_type == 'component_verify_ticket') {
			$this->platforms->update([
				'component_verify_ticket' => $info['ComponentVerifyTicket'],
			], $plat['id']);
		}

		$this->setOpenPlatform($plat['appid']);
		$server = $this->openPlatform->server;
		return $server->serve();
	}
	/**
	 * 公众号消息与事件接收
	 * @param Request $request
	 * @return \Illuminate\Http\JsonResponse|mixed|\Symfony\Component\HttpFoundation\Response
	 */
	public function wechatEvent($request) {
		$xml = file_get_contents('php://input');
		$input = request()->all();
		$this->tests->create(['type' => 'wechatEvent.xml', 'content' => $xml]);
		$this->tests->create(['type' => 'wechatEvent.input', 'content' => http_build_query($input)]);

		$arr = XmlToArray($xml);
		$component_appid = $input['cappid'];
		$this->setOpenPlatform($component_appid);
		/** @var \EasyWeChat\OpenPlatform\Application $open_platform */
		$authorizer_appid = substr($request->get('appid'), 1, -1);

		/**
		 * 全网发布
		 */
		if ($authorizer_appid == 'wx570bc396a51b8ff8') {
			return $this->releaseToNetWork($authorizer_appid);
		}

		/**
		 * 正常业务,根据实际需求自己实现
		 */
		// $official_account = WeChatAuthorizer::where('authorizer_appid', '=', $authorizer_appid)->first();
		$official_account = $this->platformWechats->findByMap([
			'appid' => $authorizer_appid,
			'component_appid' => $component_appid,
		], $this->platformWechats->model->fields);
		if (empty($official_account)) {
			return $this->result([], 2000, 'official account not authorization');
		}

        // $official_account_client = $this->openPlatform->officialAccount($official_account->authorizer_appid, $official_account->authorizer_refresh_token);
        $official_account_client = $this->openPlatform->officialAccount($official_account->appid, $official_account->refresh_token);

		$server = $official_account_client->server;
		/**
		 * 简单的处理 文本消息和事件
		 */
        $server->push(function ($message) {
            switch ($message['MsgType']) {
                case 'event':
                    // return '收到事件消息';// 处理事件消息
                    return $this->operateEvent($message);
                    break;
                case 'text':
                    // return '收到文字消息';// 处理文本消息
                    return $this->operateText($message);
                    break;
                case 'image':
                    // return '收到图片消息';// 处理图片消息
                    return $this->operateImage($message);
                    break;
                case 'voice':
                    // return '收到语音消息';处理语音消息
                    return $this->operateDefault($message);
                    break;
                case 'video':
                    // return '收到视频消息';
                    return $this->operateDefault($message);
                    break;
                case 'location':
                    // return '收到坐标消息';
                    return $this->operateDefault($message);
                    break;
                case 'link':
                    // return '收到链接消息';
                    return $this->operateDefault($message);
                    break;
                case 'file':
                    // return '收到文件消息';
                    return $this->operateDefault($message);
                // ... 其它消息
                default:
                    // return '收到其它消息';// 默认消息处理
                    return $this->operateDefault($message);
                    break;
            }
        });
		// $server->push(TextMessageHandler::class, Message::TEXT);
		// $server->push(EventMessageHandler::class, Message::EVENT);

		$response = $server->serve();
		return $response;
	}
    /**
     * 事件消息处理器
     * @param array $message
     */
    private function operateEvent($message) {
        // 事件消息处理器
        // 修改用户的关注与否状态
        $component_appid = request()->input('cappid');
        $appid = substr(request()->input('appid'), 1, -1);

        // 记录最近有互动的用户
        $plat_wechat = $this->platformWechats->getWechatForCAPPID2APPID($component_appid, $appid); // 获取公众号信息

        // 只处理关注事件
        switch ($message['Event']) {
            case 'unsubscribe':
                // 取消关注事件
                $this->users->SubscribeEvent($plat_wechat['id'], $message['FromUserName'], $message['Event']);// 修改用户的关注与否状态
                return '取消关注成功！';
                break;
            case 'subscribe':
                // 关注事件
                $this->users->SubscribeEvent($plat_wechat['id'], $message['FromUserName'], $message['Event']);// 修改用户的关注与否状态
                if (isset($message['EventKey'])) {
                    // 扫描的带参数二维码
                    $scene = substr($message['EventKey'], 8);
                    if ($scene) {
                        // 带参数二维码关注
                        $scene = Array2String($scene);
                        $novel_id   = $scene['n'];
                        $section = $scene['s'];
                        $rel = $this->qrcodeSend($plat_wechat, $message, $novel_id, $section);
                    } else {
                        // 一般二维码关注
                        $rel = $this->qrcodeSend($plat_wechat, $message);
                    }
                } else {
                    // 一般的二维码关注
                    $rel = $this->qrcodeSend($plat_wechat, $message);
                }
                break;
            /*case 'CLICK':// 点击菜单拉取消息时的事件推送
            case 'VIEW':// 点击菜单跳转链接时的事件推送
                // 记录最近有互动的用户
                $this->users->NoteInteractInfo($plat_wechat, $message['FromUserName']);// 执行记录
                break;*/
            default:
                $rel = null;
                break;
        }
        $this->users->NoteInteractInfo($plat_wechat, $message['FromUserName']);// 执行互动记录
        return $rel;
    }
    /**
     * 二维码关注事件回复
     * @param array $plat_wechat
     * @param array $message
     * @param int $novel_id
     * @param int $section_id
     */
    private function qrcodeSend($plat_wechat, $message, $novel_id=0, $section=0) {
        // if (!$novel_id) { return null; }

        $wechat_config = $this->wechatConfigs->FindByCustomer2PlatWechat($plat_wechat['customer_id'], $plat_wechat['id']); // 获取公众号配置
        $host = $this->domains->randOne(3, $wechat_config['customer_id']); // 获取外推入口域名
        $url = $host . route("jumpto", ['route'=>'section', 'section_num'=>$section, 'novel_id'=>$novel_id, 'section'=>$section, 'customer_id'=>$plat_wechat['customer_id'], 'otherdo'=>'secondpush'], false);
        if (!$novel_id) {
            $url = $host . route('novel.toindex', ['cid'=>$plat_wechat['customer_id']], false);
        }
        $user = $this->users->findBy('openid', $message['FromUserName'], ['name']);
        if (!$user) {
            return $this->noUserPush($host, $plat_wechat);
        }

        switch ($wechat_config['subscribe_msg']) {
            case 1:
                $novel = $this->novels->NovelInfo($novel_id);
                $str = "你好，欢迎关注 <a href='{$url}'>{$plat_wechat['app_name']}</a>！ \r\n <a href='{$url}'>请点击我继续阅读《{$novel['title']}》</a>";
                return $str;
            case 2:
                $user = $this->users->findBy('openid', $message['FromUserName'], ['name']);
                $str = "亲爱的{$user['name']}，欢迎关注 <a href='{$url}'>{$plat_wechat['app_name']}</a>！ \r\n <a href='{$url}'> 请点击我继续阅读刚才的小说吧！</a>";
                return $str;
            case 3:
                $user = $this->users->findBy('openid', $message['FromUserName'], ['name']);
                $str = "亲爱的{$user['name']}，欢迎您~ <a href='{$url}'>点击我继续阅读刚才的小说吧！</a>";
                return $str;
            case 4:
                // 图文消息回复
                $subscribe_content = json_decode($wechat_config['subscribe_content'], 1);
                if ($subscribe_content) {
                    $novel['title'] = $subscribe_content['title'];
                    $novel['desc']  = $subscribe_content['desc'];
                    $novel['img']   = $subscribe_content['img'];
                } elseif ($novel_id) {
                    $novel = $this->novels->NovelInfo($novel_id);
                } else {
                    $novel['title'] = $plat_wechat['app_name'];
                    $novel['desc']  = '欢迎您的到来，关注我可以防止迷路哦，后续观看更方便！';
                    $novel['img']   = 'http://dev.zmr029.com/img/zmr.logo.png';
                }
                $item = [
                    new NewsItem([
                        'title'         => $novel['title'],
                        'description'   => $novel['desc'],
                        'url'           => $url,
                        'image'         => $novel['img'],
                    ]),
                ];
                return new News($item);
            // case 5:
            default:
                $str = "<a href='{$url}'>~点击继续阅读！</a>";
                return $str;
        }
    }
    private function noUserPush($host, $plat_wechat) {
        $novels = $this->novels->model->orderBy('week_read_num', 'desc')->limit(5)->select(['id', 'title', 'img'])->get();
        $str = "你好，欢迎关注 {$plat_wechat['app_name']}\r\n\r\n";
        foreach ($novels as $novel) {
            $url = $host . route("jumpto", ['route'=>'novel', 'novel_id'=>$novel['id'], 'customer_id'=>$plat_wechat['customer_id']], false);

            $str .= "👉<a href='{$url}'>{$novel['title']}</a> \r\n\r\n";
        }
        return $str;
    }
    /**
     * 文本消息处理器
     * @param array $message
     */
    private function operateText($message) {
        // 文本消息处理器
        $component_appid = request()->input('cappid');
        $appid = substr(request()->input('appid'), 1, -1);
        if ($message['Content'] == '签到') {
            $msg = $this->signReply($message); // 签到返还
            if ($msg) {
                return $msg;
            }
        }
        $keyword_info = $this->wechatMsgReplies->KeyWordMsg($component_appid, $appid, $message['Content']);
        if (!$keyword_info) {
            throw new \Exception('没有查询到对应的关键字！', 2000);
        }

        $content = $keyword_info['reply_content'];
        if (IsJson($content)) {
            // 是回复图文消息
            $content = json_decode($content, 1);
            $item = [
                new NewsItem([
                    'title'         => $content['title'],
                    'description'   => $content['desc'],
                    'url'           => $content['url'],
                    'image'         => $content['img'],
                ]),
            ];
            $content = new News($item);
        }

        return $content;
    }
    /**
     * 图片消息处理器
     * @param array $message
     */
    private function operateImage($message) {
        // 文本消息处理器
    }
    /**
     * 默认消息处理
     * @param array $message
     */
    private function operateDefault($message) {
        // 事件默认处理器
    }
	/**
	 * 处理全网发布相关逻辑
	 * @param \EasyWeChat\OpenPlatform\Application $open_platform
	 * @param $authorizer_appid
	 * @return mixed
	 */
	private function releaseToNetWork($authorizer_appid) {
		$message = $this->openPlatform->server->getMessage();

		//返回API文本消息
		if ($message['MsgType'] == 'text' && strpos($message['Content'], "QUERY_AUTH_CODE:") !== false) {
			$auth_code = str_replace("QUERY_AUTH_CODE:", "", $message['Content']);
			$authorization = $this->openPlatform->handleAuthorize($auth_code);
			$official_account_client = $this->openPlatform->officialAccount($authorizer_appid, $authorization['authorization_info']['authorizer_refresh_token']);
			$content = $auth_code . '_from_api';
			$official_account_client['customer_service']->send([
				'touser' => $message['FromUserName'],
				'msgtype' => 'text',
				'text' => [
					'content' => $content,
				],
			]);

			//返回普通文本消息
		} elseif ($message['MsgType'] == 'text' && $message['Content'] == 'TESTCOMPONENT_MSG_TYPE_TEXT') {
			$official_account_client = $this->openPlatform->officialAccount($authorizer_appid);
			$official_account_client->server->push(function ($message) {
				return $message['Content'] . "_callback";
			});
			//发送事件消息
		} elseif ($message['MsgType'] == 'event') {
			$official_account_client = $this->openPlatform->officialAccount($authorizer_appid);
			$official_account_client->server->push(function ($message) {
				return $message['Event'] . 'from_callback';
			});
		}
		$response = $official_account_client->server->serve();
		return $response;
	}

}
