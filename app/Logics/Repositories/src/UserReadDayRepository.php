<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\NovelPayStatistics;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class UserReadDayRepository extends Repository {
    public $group_member_ids = [];

    public function model() {
        return NovelPayStatistics::class;
    }

    public function initStatistic(string $date_belongto, int $group_id, int $customer_id) {
        $statistic_init = [
            'date_belongto' =>  $date_belongto,
            'group_id'      =>  $group_id,
            'customer_id'   =>  $customer_id,
            'read_num'      =>  0,
            'novel_id'=>  0,
        ];
        return $this->create($statistic_init);
    }


    // 查询数据库里面是否有该条件的数据
    private function todayHadData($key, $where) {
        $had = Cache::get($key);
        if (!$had) {
            $had = $this->findByMap($where, ['id']);
            if ($had && $had['id']) {
                $seconds = strtotime(date('Y-m-d', time())) + 86400 - time();
                if (Cache::has($key)) {
                    Cache::put($key, $had['id'], $seconds);
                } else {
                    Cache::add($key, $had['id'], $seconds);
                }
            }
        }
        return $had;
    }
    /**
     * 添加或者更新统计数据
     * @param $map = ['date_belongto'=>'date', 'customer_id'=>'', 'group_id'=>'']
     * @param $cols = ['paid_num'=>1, 'recharge_money'=>3000, 'unpay_num'=>-1]
     */
    public function UpdateColumnNum($map, $cols) {
        if (!$cols || !$map) return false;

        $key = config('app.name') . 'user_read_day' . $map['date_belongto'] . '_' . $map['group_id'] . '_' . $map['customer_id'].'_'.$map['novel_id'].'_'.$map['user_id'];
        $where = [];
        foreach ($map as $k => $v) {
            $where[] = [$k, '=', $v];
        }
        // 查询是否已经有一条符合条件的数据了
        $had = $this->todayHadData($key, $where);
        if ($had) {
            // 有数据了；执行更新
            $up_data = [];
            foreach ($cols as $k => $v) {
                $fuhao = ' + ';
                if ($v < 0) {
                    $fuhao = ' - ';
                    $v = abs($v);
                }
                $up_data[$k] = DB::raw($k . $fuhao . $v);
            }
            $this->updateByMap($up_data, $where);
        } else {
            // 没有数据；执行更新
            $in_data = array_merge($map, $cols);
            $this->create($in_data);
            if (Cache::has($key)) Cache::forget($key);
        }
    }


}