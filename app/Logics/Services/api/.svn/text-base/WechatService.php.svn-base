<?php

namespace App\Logics\Services\api;

use App\Libraries\Facades\Tools;
use App\Logics\Repositories\src\CommonSetRepository;
use App\Logics\Repositories\src\PlatformRepository;
use App\Logics\Repositories\src\PlatWechatRepository;
use App\Logics\Repositories\src\WechatRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\UseTimeTrait;
use App\Logics\Traits\WechatSignTrait;

class WechatService extends Service {
	use WechatSignTrait, UseTimeTrait;

	protected $openPlatform;

	protected $Encryptor;
	public function Repositories() {
		return [
			'platforms' => PlatformRepository::class,
			'platWechats' => PlatWechatRepository::class,
			'commonSets' => CommonSetRepository::class,

            'wechats'    => WechatRepository::class,
		];
	}

	public function platformSign() {
		// 允许跨域
		$this->CrossHttp();
		// 检测 refer 是否存在
		$refer = $this->CheckReffer();
		// 获取待签名URL
		$url = $this->checkUrl($refer);

		$api = request()->input('api'); // 接口序号 api
		$plat_id = request()->input('plat_id', 0); // 指定开放平台id
		$wechat = $this->platformNowWechat($api, $plat_id);

		$this->InitWechatSignTrait($wechat['appid'], $wechat['app_secret'], $wechat['component_appid']);
		$signPackage = $this->getSignPackage($url);

		return $this->setSignRel($signPackage);
	}

	public function wechatSign() {
		// 允许跨域
		$this->CrossHttp();
		// 检测 refer 是否存在
		$refer = $this->CheckReffer();
		// 获取待签名URL
		$url = $this->checkUrl($refer);

		$api = request()->input('api'); // 接口序号 api
		$wechat = $this->wechatNowWechat($api, $refer);
		if (!$wechat) {
			throw new \Exception('公众号信息异常！', 2000);
		}

		$this->InitWechatSignTrait($wechat['appid'], $wechat['app_secret']);
		$signPackage = $this->getSignPackage($url);

		return $this->setSignRel($signPackage);
	}
	/**
	 * 返回签名结果
	 * @param array $signPackage
	 */
	private function setSignRel($signPackage = -1) {
		$callback = request()->input('callback');
		$debug = request()->input('debug') ? true : false; // 是否debug

		unset($signPackage['rawString']);
		unset($signPackage['jsapiTicket']);

		$signPackage['jsApiList'] = ["onMenuShareTimeline", "onMenuShareAppMessage", "hideMenuItems", "showMenuItems"];
		$signPackage['callback'] = $callback;
		$signPackage['debug'] = $debug;

        // 分享标题描述以及分享到哪里的配置信息
		$signPackage['shareInfo'] = $this->shareInfo(request()->input('share_num', -1));

		if ($callback) {
			$data = json_encode($signPackage, JSON_UNESCAPED_SLASHES);
			$rel = $callback . '(' . $data . ')';
		} else {
			$rel = $signPackage;
		}
		return $rel;
	}

	/**
	 * 获取开放平台当前使用的公众号
	 */
	private function platformNowWechat($api, $plat_id = 0) {
		// 获取开放平台信息，是否启用随机公众号
		$plat = $this->nowPlatform($api, $plat_id);
		if (!$plat) {
			throw new \Exception('开放平台信息异常！', 2000);
		}
		$random_wechat = $plat ? $plat['wechat_random'] : 0;
		// 获取随机分享间隔时间差
		$use_time = $this->commonSets->values('wechat', 'random_time');
		$use_time = $use_time ? intval($use_time) : 0;

		$sel_cols = ['id', 'appid', 'app_secret', 'component_appid', 'use_time'];
		if ($use_time == 0 || $random_wechat == 0) {
			// 没有启用随机公众号
			$info = $this->platWechats->findByMap([
				['status', '=', 1],
				['api_type', '=', $api],
				['component_appid', '=', $plat['appid']],
			], $sel_cols);
			return $info;
		}

		list($use_time_start, $use_time_end) = $this->getUseTimeBT($use_time);

		// 查询有没有当前使用公众号
		// 查询有没有当前使用公众号
		$info = $this->platWechats->findByMap([
			['status', '=', 1],
			['api_type', '=', $api],
			['component_appid', '=', $plat['appid']],
			['use_time', '>', $use_time_start],
			['use_time', '<=', $use_time_end],
		], $sel_cols);

		if (!$info) {
			$list = $this->platWechats->allByMap([
				['status', '=', 1],
				['api_type', '=', $api],
				['component_appid', '=', $plat['appid']],
			], $sel_cols); // 查询可用公众号列表
			if ($list) {
				// 有可用公众号就转为数组
				$list = $list->toArray();
			}
			$tem = 0;
			foreach ($list as $v) {
				if (!$v['use_time'] || $tem === 1) {
					$info = $v;
					break;
				}
				if ($this->getCheckOkUseTime($use_time, $v['use_time'], $use_time_end)) {
					// 标记下一个公众号符合要求
					$tem = 1;
				}
			}
			if (!$info) {
				if ($list) {
					$info = $list[0];
				} else {
					$info = $this->platWechats->findByMap([
						['api_type', '=', $api],
						['component_appid', '=', $plat['appid']],
					], $sel_cols);
				}
			}
			$this->platWechats->update(['use_time' => $use_time_end], $info['id']);
		}

		return $info;
	}
	/**
	 * 获取当前使用的开放平台
	 */
	public function nowPlatform($api, $plat_id = 0) {
		$use_time = $this->commonSets->values('platform_random_time', 'api' . $api);
		$use_time = $use_time ? intval($use_time) : 0;
		if ($use_time == 0 || $plat_id > 0) {
			// 没有启用按时间段启用开放平台 或者 指定了开放平台id的
			if ($plat_id) {
				$plat = $this->platforms->findByMap([['id', '=', $plat_id], ['status', '=', 1]], ['wechat_random', 'appid']);
			} else {
				$plat = $this->platforms->findByMap([['api_type', '=', $api], ['status', '=', 1]], ['wechat_random', 'appid']);
			}
			if (!$plat) {
				exit('开放平台异常！');
			}
			return $plat;
		}

		list($use_time_start, $use_time_end) = $this->getUseTimeBT($use_time);

		// 查询有没有当前使用公众号
		$plat = $this->platforms->findByMap([
			['api_type', '=', $api],
			['status', '=', 1],
			['use_time', '>', $use_time_start],
			['use_time', '<=', $use_time_start],
		], ['id', 'wechat_random', 'appid', 'use_time']);

		if (!$plat) {
			$list = $this->platforms->allByMap([
				['api_type', '=', $api],
				['status', '=', 1],
			], ['id', 'wechat_random', 'appid', 'use_time']); // 查询可用公众号列表
			if ($list) {
				// 有可用公众号就转为数组
				$list = $this->platforms->toArr($list);
			}
			$tem = 0;
			foreach ($list as $v) {
				if (!$v['use_time'] || $tem === 1) {
					$plat = $v;
					break;
				}
				if ($this->getCheckOkUseTime($use_time, $v['use_time'], $use_time_end)) {
					// 标记下一个公众号符合要求
					$tem = 1;
				}
			}
			if (!$plat) {
				if ($list) {
					$plat = $list[0];
				} else {
					$plat = $this->platforms->findByMap([
						['api_type', '=', $api],
						['status', '=', 1],
					], ['id', 'wechat_random', 'appid', 'use_time']);
				}
			}

			if (!$plat) {
				exit('开放平台异常！');
			}
			$this->platforms->update(['use_time' => $use_time_end], $plat['id']);
		}

		return $plat;
	}
	/**
	 * @param int $api 当前页面使用域名的公众号
	 * @return wechat
	 */
	private function wechatNowWechat($api, $refer = '') {
		if ($api) {
			return $this->wechats->find($api, $this->wechats->model->fields);
		}

		$hosts = parse_url($refer);
		if (!isset($hosts['host'])) {
			throw new \Exception('没有对应域名信息，不能获取当前使用公众号', 2000);
		}
		$luodi = Tools::GetBaseDomain($hosts['host']);
		$list = $this->wechats->allByMap([
			['status', '=', 1],
			['domain_status', '=', 1],
			['domain', 'like', '%' . $luodi],
		], $this->wechats->model->fields);
		if (!$list) {
			throw new \Exception('没有域名对应公众号信息', 2000);
		}
		$wechat = $list[array_rand($list)];
		return $wechat;
	}

	/**
	 * 返回的分享提示信息 并
     * 返回的分享签名信息
     */
	private function shareInfo($num = -1)
    {
        $rel = [];
        $shin1 = [
            'to'    =>'friend',     // 分享好友还是朋友圈
            'tip'   => [    // 分享提示信息
                'title' => '分享成功',
                'desc'  => '请重新分享到不同的50人以上的群，即可领取红包奖励',
                'btn'   => '确 定',
            ],
            'share' => [    // 分享的内容
                'title' => '分享测试标题',
                'desc'  => '分享测试描述',
                'img'   => 'http://ad-admin.zmr029.com/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg',
                'link'  => 'http://lb-open.zmr029.com/tousu/tijiao2.html',
            ],
        ];
        $shin3 = [
            'to'    =>'timeline',     // 分享好友还是朋友圈
            'tip'   => [    // 分享提示信息
                'title' => '分享失败',
                'desc'  => '请重新公开分享朋友圈，即可领取红包奖励',
                'btn'   => '确 定',
            ],
            'share' => [    // 分享的内容
                'title' => '分享朋友圈测试标题',
                'desc'  => '分享朋友圈测试描述',
                'img'   => 'http://ad-admin.zmr029.com/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg',
                'link'  => 'http://lb-open.zmr029.com/tousu',
            ],
        ];
        $rel[0] = $shin1;
        $rel[1] = $shin3;
        $rel[2] = $shin1;
        $rel[3] = $shin1;
        $rel[4] = $shin3;
        $rel[5] = $shin3;
        $rel[6] = $shin1;

        $rel[3]['share']['title'] = '分享群的其他信息标题';
        $rel[4]['share']['title'] = '分享朋友圈的其他信息标题';

        if ($num >= 0 && isset($rel[$num])){
            return $rel[$num];
        } else {
            return $rel;
        }
    }
}