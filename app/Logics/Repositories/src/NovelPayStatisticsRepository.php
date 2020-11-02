<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\NovelPayStatistics;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class NovelPayStatisticsRepository extends Repository {
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
            'pay_num'     =>  0,
            'novel_id'=>  0,
        ];
        return $this->create($statistic_init);
    }

    /**
     * 获取完备的统计数据
     * @param int $group_id 组ID（实际上是组长ID，对应customer.id 和 customer.pic）
     * @param int $customer_id 商家ID，为 0 则代表组汇总数据
     * @param array|null $extend_condition 附加查询条件
     * @param int $page 页码
     * @param bool $force_update 是否强制刷新
     * @param int $page_size 单页数据量大小
     * @return array
     */
    public function getExtendedList($group_id = 0, $customer_id = 0, $extend_condition = [], $page = 1, $force_update = false, $page_size = 20) {
        $current_date = date('Y-m-d');
        $key = config('app.name').'-statistics-g'.$group_id.'-c'.$customer_id.'-ec'.md5(json_encode($extend_condition, JSON_UNESCAPED_UNICODE)).'-p'.$page.'-s'.$page_size.'-d'.$current_date;
        if($force_update) {
            Cache::forget($key);
        }
        return Cache::remember($key, 60, function () use ($group_id, $customer_id, $extend_condition, $page, $page_size) {
            $condition = [
                ['group_id', '=', $group_id],
                ['customer_id', '=', $customer_id]
            ];
            $condition = array_merge($condition, $extend_condition);
            $record_count = $this->countByMap($condition);
            $temp = $this->limitPage($condition, $page, $page_size, array('*'), ['id' => 'desc']);
            if($record_count && $temp) {
                $temp = $temp->toArray();
            } else {
                return ['record_count' => $record_count, 'list' => [], 'max_page' => 1];
            }
            $list = [];
            foreach ($temp as $record) {
                $record['bill_recharge_count'] = $record['paid_num'] + $record['unpay_num'];// 总充值笔数
                $record['pay_success_ratio'] = $record['bill_recharge_count'] ? round($record['paid_num'] / $record['bill_recharge_count'] * 100, 2) : 0;// 去除 % 后的比例值
                // 时间范围
                $start_timestamp = strtotime($record['date_belongto']);
                $end_timestamp = $start_timestamp + 86400;
                // 统计充值人数
                if($customer_id < 1) {
                    // 此时为 组汇总数据
                    $customer_ids = $this->getGroupMemberIds($group_id);
                    $recharged_user_count = DB::table('recharge_logs')
                        ->select([DB::raw('DISTINCT(user_id)')])
                        ->whereIn('customer_id', $customer_ids)
                        ->where([
                            ['status', '=', 1],
                            ['pay_time', '>=', $start_timestamp],
                            ['pay_time', '<', $end_timestamp]
                        ])
                        ->count();
                } else {
                    $recharged_user_count = DB::table('recharge_logs')
                        ->select([DB::raw('DISTINCT(user_id)')])
                        ->where([
                            ['customer_id', '=', $customer_id],
                            ['status', '=', 1],
                            ['pay_time', '>=', $start_timestamp],
                            ['pay_time', '<', $end_timestamp]
                        ])
                        ->count();
                }
                $record['recharged_user_count'] = $recharged_user_count;// 充值人数（去重）
                $record['average_consumption'] = $recharged_user_count ? $record['recharge_money'] / $recharged_user_count : 0;// 平均每人消费（仅计算已充值人群）

                $list[$record['date_belongto']] = $record;
            }
            return ['record_count' => $record_count, 'list' => $list, 'max_page' => max(1, ceil($record_count / $page_size))];
        });
    }

    /**
     * 获取组成员ID
     * @param int $leader_id
     * @return array
     */
    public function getGroupMemberIds($leader_id) {
        if(isset($this->group_member_ids[$leader_id])) {
            return $this->group_member_ids[$leader_id];
        } else {
            $temp = DB::table('customers')->where([
                ['pid', '=', $leader_id]
            ])->pluck('id');
            $temp = $temp ? $temp->toArray() : [];
            $temp = array_values($temp);// 其实可以去掉这个...
            $temp[] = $leader_id;// 加入组长
            return $this->group_member_ids[$leader_id] = $temp;
        }
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

        //$key = config('app.name') . 'novel_pay_statistic_' . $map['date_belongto'] . '_' . $map['group_id'] . '_' . $map['customer_id'];
        $key = config('app.name') . 'novel_pay_statistic_' . $map['date_belongto'] . '_' . $map['group_id'] . '_' . $map['customer_id'] . '_' . $map['novel_id'];
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