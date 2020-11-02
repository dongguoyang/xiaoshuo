<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\PageLog;
use App\Logics\Repositories\Repository;

class PageLogRepository extends Repository {
	public function model() {
		return PageLog::class;
	}
    /**
     * 统计页面次数
     * @param string $col 栏目名称
     * @param int $num 增加次数
     */
    public function IncColNum($col, $num = 1) {
	    $bl_date = date('Ymd', time());
	    $info = $this->findBy('bl_date', $bl_date, $this->model->fields);
	    if ($info) {
	        $info->$col += $num;
	        $info->save();
        } else {
	        $this->create(['bl_date' => $bl_date, $col => $num]);
        }
    }

}