<?php
namespace App\Logics\Traits;

trait UseTimeTrait {

	/**
	 * 获取当前使用中的时间
	 * @param int $use_time 使用时间间隔
	 * @param int $v_time 当前应用的使用开始时间
	 * @param int $use_time_en 当前时间
	 * @return bool
	 */
	public function getCheckOkUseTime($use_time, $v_time, $use_time_end) {
		$H = intval(substr($v_time, 0, 2));
		$i = intval(substr($v_time, 2));

		$i = $i + $use_time;
		if ($i >= 60) {
			$H++;
			$i -= 60;
		}
		if ($i < 10) {
			$i = '0' . $i;
		}

		$time2 = $H . '' . $i;
		$time2 = intval($time2);
		if ($time2 >= $use_time_end) {
			// 找到下一个应该启用的公众号了
			return true;
		}
		return false;
	}
	/**
	 * 获取当前使用中的时间
	 */
	public function getUseTimeBT($use_time) {
		$now = time();
		$H = intval(date('H', $now)) + 24; // 获取当前小时
		$i = intval(date('i', $now)); // 获取当前分钟

		$use_time_end = $H . '' . (($i < 10) ? ('0' . $i) : $i);
		$use_time_end = intval($use_time_end);
		// $use_time_end_H = $H;
		// $use_time_end_i = $i;

		$i = $i - $use_time;
		if ($i < 0) {
			$i = $i + 60;
			$H--;
			if ($H < 24) {
				$H = 47;
			}
		}

		$use_time_start = $H . '' . (($i < 10) ? ('0' . $i) : $i);
		$use_time_start = intval($use_time_start);
		// $use_time_start_H = $H;
		// $use_time_start_i = $i;
		// return [$use_time_start_H, $use_time_start_i, $use_time_end_H, $use_time_end_i];
		return [$use_time_start, $use_time_end];
	}
}