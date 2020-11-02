<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\WechatQrcode;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class WechatQrcodeRepository extends Repository {
    public function model() {
        return WechatQrcode::class;
    }

    public $validTime = 2592000; //30 * 86400 // 30天有效期

    /**
     * 更新字段数据信息
     * @param int $id 二维码ID
     * @param array $cols ['scan_num'=>1]
     */
    public function IncColumns($id, array $cols) {
        if (empty($cols)) return false;

        $sel_item = ['id', 'scan_num', 'sub_num', 'recharge_num', 'recharge_money', 'order_num', 'order_money', 'user_num'];
        if (count($cols) > 1) {
            $data = $this->find($id, $sel_item);
            if (!$data) return false;
            foreach ($cols as $col=>$num) {
                if (in_array($col, $sel_item)) $data->$col = $data->$col + $num;
            }
            $data->save();
        } else {
            foreach ($cols as $col=>$num) {
                if (in_array($col, $sel_item)) $this->model->where('id', $id)->increment($col, $num);
            }
        }
    }


}