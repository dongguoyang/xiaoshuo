<?php
/**
 * Created by PhpStorm.
 * User: LONG
 * Time: 2019/3/26 16:23
 */

namespace App\Libraries\String;

class StringHelper {
	private $explode = [",", ";", "|", "\n"];

	public function explode($string) {
		$string = trim($string);
		$array = [$string];
		foreach ($this->explode as $value) {
			if (strpos($string, $value)) {
				$array = explode($value, $string);
				break;
			}
		}
		foreach ($array as $key => $value) {
			$array[$key] = trim($value);
		}
		return $array;
	}

	/**
	 * 生成短连接
	 * @param string $url 完整的url地址
	 * @return string
	 */
	public function shortUrl($url, $type = 'tcn') {
		switch ($type) {
		case 'luoma':
			$shortUrl = $this->luomaUrlCn($url);
			break;
		case 'sougou':
			$shortUrl = $this->sougouUrlCn($url);
			break;
		case 'baidu':
			$shortUrl = $this->baiduSURL($url);
			break;
		default:
			$shortUrl = $this->shortUrlTCn($url);
			break;
		}

		return $shortUrl;
	}
	/**
	 * 生成短连接
	 * @param string $url 完整的url地址
	 * @return string
	 */
	public function shortUrlTCn($url) {
		// 不是完整的 http url 就直接返回
		if (strpos($url, 'http') !== 0) {
			return $url;
		}
		// 检测是否需要 url 编码
		if (strpos($url, '?') || strpos($url, '&')) {
			// urlencode 需要url编码
			$url = urlencode($url);
		}

		// t.cn 短连接 --- start ---
		$data = @file_get_contents("http://api.t.sina.com.cn/short_url/shorten.json?source=3271760578&url_long=" . $url);
		if (!$data) {
			// 信息不存在直接返回 原url
			return urldecode($url);
		}
		$durl = json_decode($data);
		$shortUrl = $durl[0]->url_short;
		// t.cn 短连接 --- end ---

		return $shortUrl;
	}
	/**
	 * 搜狗的url.cn短连接
	 */
	public function sougouUrlCn($url) {
		// 不是完整的 http url 就直接返回
		if (strpos($url, 'http') !== 0) {
			return $url;
		}
		// 检测是否需要 url 编码
		if (strpos($url, '?') || strpos($url, '&')) {
			// urlencode 需要url编码
			$url = urlencode($url);
		}
		// 腾讯短连接 --- start ---
		$data = @file_get_contents("http://sa.sogou.com/gettiny?url=" . $url);
		if (!$data || strpos($data, 'http') !== 0) {
			// 信息不存在直接返回 原url
			return urldecode($url);
		}
		return $data;
	}
	/**
	 * 罗马数据短连接
	 */
	public function luomaUrlCn($url) {
		// 不是完整的 http url 就直接返回
		if (strpos($url, 'http') !== 0) {
			return $url;
		}
		// 检测是否需要 url 编码
		if (strpos($url, '?') || strpos($url, '&')) {
			// urlencode 需要url编码
			$url = urlencode($url);
		}
		// 腾讯短连接 --- start ---
		$data = @file_get_contents("http://api.monkeyapi.com/?appkey=5CD5EDFDC1951A480947A6E27769A3EB&url=" . $url);
		if (!$data) {
			// 信息不存在直接返回 原url
			return urldecode($url);
		}
		$durl = json_decode($data);
		if (!isset($durl->code) || $durl->code != 200) {
			$shortUrl = urldecode($url);
		} else {
			$shortUrl = $durl->data;
		}
		// 腾讯短连接 --- end ---

		return $shortUrl;
	}
	/**
	 * surl.baidu.com 数据短连接
	 * 不行，5 秒内只能请求一次
	 */
	public function baiduSURL($url) {
		// 不是完整的 http url 就直接返回
		if (strpos($url, 'http') !== 0) {
			return $url;
		}
		// 检测是否需要 url 编码
		if (strpos($url, '?') || strpos($url, '&')) {
			// urlencode 需要url编码
			$url = urlencode($url);
		}
		// 百度短连接 --- start ---
		$data = callCurl("https://url.jx.cn/dwz.php?" . $url . time());
		if (!$data) {
			// 信息不存在直接返回 原url
			return urldecode($url);
		}
		$durl = json_decode($data, 1);
		if (!isset($durl['status']) || $durl['status'] != 1) {
			$shortUrl = urldecode($url);
		} else {
			$shortUrl = $durl['url_short'];
		}
		// 百度短连接 --- end ---

		return $shortUrl;
	}
}