<?php
namespace App\Http\Controllers\Platform;

use App\Http\Controllers\BaseController;
use App\Logics\Services\src\PlatformService;
use EasyWeChat\Kernel\Exceptions\Exception;
use Symfony\Component\HttpFoundation\Request;

class PlatformsController extends BaseController {

	public function __construct(PlatformService $service) {
		$this->service = $service;
	}
	/**
	 * 公众号授权
	 */
	public function wechatAuth() {
		$plat = $this->service->wechatAuth();
		$url = route('platform.toauth', ['appid' => $plat['appid']]);
		return view('front.wechat.auth', ['plat' => $plat, 'url' => $url]);
	}
	/**
	 * 跳转公众号授权
	 */
	public function wechatToAuth() {
		try
		{
			return $this->service->wechatToAuth();
		}
		catch (Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
		catch (\Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}

	}
	/**
	 * 公众号授权成功的页面
	 */
	public function wechatAuthed() {
		try
		{
			list($plat, $wechat) = $this->service->wechatAuthed();
			return view('front.wechat.authed', ['plat' => $plat, 'wechat' => $wechat]);
		}
		catch (Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
		catch (\Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
	}

	/**
	 * 接收component_verify_ticket
	 */
	public function server() {
		try
		{
			return $this->service->platformServer();
		}
		catch (Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
		catch (\Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
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
		catch (Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
		catch (\Exception $e)
        {
			return $this->result([
				'file' => $e->getFile(),
				'line' => $e->getLine(),
				//'trace'=>$e->getTrace()
			], $e->getCode(), $e->getMessage());
		}
	}
}
