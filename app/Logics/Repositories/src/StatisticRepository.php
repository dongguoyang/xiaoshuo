<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Statistic;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatisticRepository extends Repository {
    public $group_member_ids = [];

	public function model() {
		return Statistic::class;
	}

	public function initStatistic(string $date_belongto, int $group_id, int $customer_id) {
        $statistic_init = [
            'date_belongto' =>  $date_belongto,
            'group_id'      =>  $group_id,
            'customer_id'   =>  $customer_id,
            'paid_num'      =>  0,
            'unpay_num'     =>  0,
            'recharge_money'=>  0,
            'deduct_money'  =>  0,
            'user_num'      =>  0,
            'subscribe_num' =>  0
        ];
        return $this->create($statistic_init);
    }

    /**
     * 获取今天的统计数据（附加额外统计）
     * @param int $group_id 组ID（实际上是组长ID，对应customer.id 和 customer.pic）
     * @param int $customer_id 商家ID，为 0 则代表组汇总数据
     * @return array
     */
	public function getStatisticsToday($group_id = 0, $customer_id = 0) {
        $current_date = date('Y-m-d');
        $data = $this->getExtendedList($group_id, $customer_id, [], 1);
        $data_today = $data['list'][$current_date] ?? [
                'date_belongto'     =>  $current_date,
                'group_id'          =>  $group_id,
                'customer_id'       =>  $customer_id,
                'paid_num'          =>  0,
                'unpay_num'         =>  0,
                'recharge_money'    =>  0,
                'deduct_money'      =>  0,
                'user_num'          =>  0,
                'subscribe_num'     =>  0,
                'bill_recharge_count'   =>  0,
                'pay_success_ratio'     =>  0,
                'recharged_user_count'  =>  0,
                'average_consumption'   =>  0
            ];
        // 填充额外统计数据：昨日此时充值额度
        // 实时数据，不用缓存
        $yestoday_timestamp = strtotime($current_date) - 86400;
        $yestoday_when_timestamp = time() - 86400;
        if($customer_id < 1) {
            $customer_ids = $this->getGroupMemberIds($group_id);
            $yestoday_recharged_sum = DB::table('recharge_logs')
                ->whereIn('customer_id', $customer_ids)
                ->where([
                    ['status', '=', 1],
                    ['pay_time', '>=', $yestoday_timestamp],
                    ['pay_time', '<=', $yestoday_when_timestamp]
                ])
                ->sum('money');
        } else {
            $yestoday_recharged_sum = DB::table('recharge_logs')
                ->where([
                    ['customer_id', '=', $customer_id],
                    ['status', '=', 1],
                    ['pay_time', '>=', $yestoday_timestamp],
                    ['pay_time', '<=', $yestoday_when_timestamp]
                ])
                ->sum('money');
        }
        $data_today['yestoday_recharged_sum'] = $yestoday_recharged_sum;
        return $data_today;
    }

    /**
     * 获取昨日统计数据
     * @param int $group_id 组ID（实际上是组长ID，对应customer.id 和 customer.pic）
     * @param int $customer_id 商家ID，为 0 则代表组汇总数据
     * @return array
     */
    public function getStatisticsYesterday($group_id = 0, $customer_id = 0) {
	    $yesterday_date = date('Y-m-d', time() - 86400);
        $data = $this->getExtendedList($group_id, $customer_id, [], 1);
        $data_yesterday = $data['list'][$yesterday_date] ?? [
                'date_belongto'     =>  $yesterday_date,
                'group_id'          =>  $group_id,
                'customer_id'       =>  $customer_id,
                'paid_num'          =>  0,
                'unpay_num'         =>  0,
                'recharge_money'    =>  0,
                'deduct_money'      =>  0,
                'user_num'          =>  0,
                'subscribe_num'     =>  0,
                'bill_recharge_count'   =>  0,
                'pay_success_ratio'     =>  0,
                'recharged_user_count'  =>  0,
                'average_consumption'   =>  0
            ];
        return $data_yesterday;
    }

    /**
     * 获取本月统计汇总
     * @param int $group_id 组ID（实际上是组长ID，对应customer.id 和 customer.pic）
     * @param int $customer_id 商家ID，为 0 则代表组汇总数据
     * @param bool $force_update 是否强制刷新
     * @return array
     */
    public function getStatisticsThisMonth($group_id = 0, $customer_id = 0, $force_update = false) {
        $month = date('Ym');
        $key = config('app.name').'-statistics-month'.$month.'-g'.$group_id.'-c'.$customer_id;
        if($force_update) {
            Cache::forget($key);
        }
        return Cache::remember($key, 360, function () use ($group_id, $customer_id, $month) {
            $condition = [
                ['group_id', '=', $group_id],
                ['customer_id', '=', $customer_id],
                ['date_belongto', '>=', date('Y-m-d', strtotime('first day of this month'))],
                ['date_belongto', '<=', date('Y-m-d')]
            ];
            $recharged_sum = DB::table('statistics')->where($condition)->sum('recharge_money');
            $deduct_sum = DB::table('statistics')->where($condition)->sum('deduct_money');
            $paid_sum = DB::table('statistics')->where($condition)->sum('paid_num');
            $unpaid_sum = DB::table('statistics')->where($condition)->sum('unpay_num');
            $bill_sum = $paid_sum + $unpaid_sum;
            $pay_success_ratio = $bill_sum ? round($paid_sum / $bill_sum * 100, 2) : 0;
            $user_sum = DB::table('statistics')->where($condition)->sum('user_num');
            $subscribe_sum = DB::table('statistics')->where($condition)->sum('subscribe_num');
            return [
                'month'         =>  $month,
                'recharged_sum' =>  $recharged_sum,
                'deduct_sum'    =>  $deduct_sum,
                'paid_sum'      =>  $paid_sum,
                'unpaid_sum'    =>  $unpaid_sum,
                'user_sum'      =>  $user_sum,
                'subscribe_sum' =>  $subscribe_sum,
                'pay_success_ratio' =>  $pay_success_ratio,
            ];
        });
    }

    /**
     * 获取统计汇总
     * @param int $group_id 组ID（实际上是组长ID，对应customer.id 和 customer.pic）
     * @param int $customer_id 商家ID，为 0 则代表组汇总数据
     * @param bool $force_update 是否强制刷新
     * @return array
     */
    public function getStatisticsAll($group_id = 0, $customer_id = 0, $force_update = false) {
        $key = config('app.name').'-statistics-all-g'.$group_id.'-c'.$customer_id;
        if($force_update) {
            Cache::forget($key);
        }
        return Cache::remember($key, 360, function () use ($group_id, $customer_id) {
            $condition = [
                ['group_id', '=', $group_id],
                ['customer_id', '=', $customer_id]
            ];
            $recharged_sum = DB::table('statistics')->where($condition)->sum('recharge_money');
            $deduct_sum = DB::table('statistics')->where($condition)->sum('deduct_money');
            $paid_sum = DB::table('statistics')->where($condition)->sum('paid_num');
            $unpaid_sum = DB::table('statistics')->where($condition)->sum('unpay_num');
            $bill_sum = $paid_sum + $unpaid_sum;
            $pay_success_ratio = $bill_sum ? round($paid_sum / $bill_sum * 100, 2) : 0;
            $user_sum = DB::table('statistics')->where($condition)->sum('user_num');
            $subscribe_sum = DB::table('statistics')->where($condition)->sum('subscribe_num');
            return [
                'recharged_sum' =>  $recharged_sum,
                'deduct_sum'    =>  $deduct_sum,
                'paid_sum'      =>  $paid_sum,
                'unpaid_sum'    =>  $unpaid_sum,
                'user_sum'      =>  $user_sum,
                'subscribe_sum' =>  $subscribe_sum,
                'pay_success_ratio' =>  $pay_success_ratio,
            ];
        });
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
                $map = [
                    ['users.created_at','>=',$start_timestamp],
                    ['users.created_at','<',$end_timestamp],
                    ['recharge_logs.status','=',1]
                ];
                $map_users = [
                    ['created_at','>=',$start_timestamp],
                    ['created_at','<',$end_timestamp],
                ];
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
                    $map[3] = ['users.customer_id','=',$customer_id];
                    $map_users[2] = ['customer_id','=',$customer_id];
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
                //今日注册的会员充值金额
                $today_user_pay = Db::table('users')
                                ->join('recharge_logs', 'users.id', '=', 'recharge_logs.user_id')
                                ->where($map)
                                ->sum('recharge_logs.money');
                //今日注册的会员
                $today_user_number = Db::table('users')
                                ->where($map_users)
                                ->count('id');
                //今日注册的会员的当日充值金额
                $map[] = ['recharge_logs.created_at','>=',$start_timestamp];
                $map[] = ['recharge_logs.created_at','<',$end_timestamp];
                $today_user_todat_pay = Db::table('users')
                ->join('recharge_logs', 'users.id', '=', 'recharge_logs.user_id')
                ->where($map)
                ->sum('recharge_logs.money');
                $record['today_user_pay'] = $today_user_pay;
                $record['today_user_number'] = $today_user_number;
                $record['today_user_todat_pay'] = $today_user_todat_pay;
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

    /**
     * 增加关注人数统计
     * @param int $customer_id
     */
    public function AddSubscribeNum($customer_id) {
        $customer = Cache::remember(config('app.name') . 'customer_id_pid'.$customer_id, 1440, function () use ($customer_id){
            $customerRep = new CustomerRepository();
            $customer = $customerRep->find($customer_id, ['id', 'pid']);
            return $customerRep->toArr($customer);
        });

        $current_date = date('Y-m-d');
        $group_id     = $customer['pid'] ? $customer['pid'] : $customer['id'];
        $map = [
            ['date_belongto', $current_date],
            ['group_id', $group_id],
            ['customer_id', $customer['id']],
        ];

        $map_g = [
            ['date_belongto', $current_date],
            ['group_id', $group_id],
            ['customer_id', 0],
        ];

        $had = $this->findByMap($map, ['subscribe_num', 'id']);
        if ($had) {
            // 更新 客户/客户组 的新关注用户数量
            $had->subscribe_num++;
            $had->save();
            $this->model->where($map_g)->increment('subscribe_num', 1);
        } else {
            $data = [
                'date_belongto' => $current_date,
                'group_id'      => $group_id,
                'customer_id'   => $customer['id'],
                'subscribe_num' => 1,
            ];
            // 新增 客户 的新关注用户数量
            $this->create($data);
            $had = $this->findByMap($map_g, ['subscribe_num', 'id']);
            if ($had) {
                // 更新 客户组 的新关注用户数量
                $had->subscribe_num++;
                $had->save();
            } else {
                // 新增 客户组 的新关注用户数量
                $data['customer_id'] = 0;
                $this->create($data);
            }
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

        $key = config('app.name') . 'statistic_' . $map['date_belongto'] . '_' . $map['group_id'] . '_' . $map['customer_id'];
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

        /*$this->statistics->updateByMap([
            'paid_num'  =>  DB::raw('paid_num + 1'),
            'unpay_num' =>  DB::raw('unpay_num - 1'),
            'recharge_money'    =>  DB::raw('recharge_money + '.$log['money'])
        ], [
            ['date_belongto', '=', $current_date],
            ['group_id', '=', $group_id],
            ['customer_id', '=', $customer['id']]
        ]);*/
    }


}