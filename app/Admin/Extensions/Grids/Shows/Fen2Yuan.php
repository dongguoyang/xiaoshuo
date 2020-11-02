<?php

namespace App\Admin\Extensions\Grids\Shows;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Displayers\AbstractDisplayer;

class Fen2Yuan extends AbstractDisplayer {
	public function display($str = '￥ ') {
		// $val = $this->value / 100;
		// $val = sprintf($val . '', ".2f");
		// return $str . $val;
		return $str . round($this->value / 100, 2);
	}
}