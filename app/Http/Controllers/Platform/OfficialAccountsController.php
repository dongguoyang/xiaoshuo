<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\BaseController;
use App\Logics\Services\src\OfficialAccountService;
use Symfony\Component\HttpFoundation\Request;

class OfficialAccountsController extends BaseController {

	public function __construct(OfficialAccountService $service) {
		$this->service = $service;
	}
	/**
	 * 公众号消息与事件接收URL
	 * http://wx.test.com/platform/wechatevent?appid=/$APPID$
	 */
	public function wechatEvent(Request $request) {
		try
		{
			return $this->service->wechatEvent($request);
		}
		catch (\Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
			], $e->getCode(), $e->getMessage());
		}
	}
}