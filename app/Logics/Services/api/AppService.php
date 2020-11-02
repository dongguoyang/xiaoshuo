<?php

namespace App\Logics\Services\api;

use App\Logics\Repositories\src\DutyReglistRepository;
use App\Logics\Repositories\src\DutyRepository;
use App\Logics\Repositories\src\DutyResultRepository;
use App\Logics\Repositories\src\PersonateAppRepository;
use App\Logics\Repositories\src\SmsRepository;
use App\Logics\Repositories\src\StRewardRepository;
use App\Logics\Repositories\src\UserMoneyLogRepository;
use App\Logics\Repositories\src\UserRepository;
use App\Logics\Services\Service;
use App\Logics\Traits\DutyTrait;
use App\Logics\Traits\SmsTrait;
use Illuminate\Support\Facades\DB;

class AppService extends Service {
    use SmsTrait, DutyTrait;

    protected $sms;
    protected $users;
    protected $duties;
    protected $dutyResults;
    protected $dutyReglists;
    protected $stRewards;
    protected $userMoneyLogs;
    protected $personateApps;

	public function Repositories() {
		return [
			'sms'           => SmsRepository::class,
            'duties'        => DutyRepository::class,
            'dutyResults'   => DutyResultRepository::class,
            'dutyReglists'  => DutyReglistRepository::class,
            'stRewards'     => StRewardRepository::class,
            'users'         => UserRepository::class,
            'userMoneyLogs' => UserMoneyLogRepository::class,
            'personateApps'  => PersonateAppRepository::class,
		];
	}

    /**
     * 发送短信
     */
    public function SendSms()
    {
        $input = request()->input();
        if (!isset($input['sign']) || !isset($input['tel']) || !isset($input['nonce_str'])) {
            throw new \Exception('参数错误.', 2000);
        }
        if ($input['sign'] != strtolower(md5($input['tel'] . 'jfds789432jdfseru' . $input['nonce_str']))) {
            throw new \Exception('签名错误.', 2000);
        }

        return $this->result($this->alidayuSendSms($input), 0, '发送短信验证码成功！');
    }
    /**
     * 检测验证任务是否过期
     */
    public function CheckDutyOverTime($passwd)
    {
        if (!$passwd) {
            $passwd = request()->input('passwd', '');
        }

        if ($passwd != 'abcd666520') {
            throw new \Exception('参数错误.', 2000);
        }

        $this->duties->model->where('end_at', '<', time())->update(['status'=>9]); // 结束过期的任务

        $num = $id = 0;
        while (true)
        {
            // 查询报名列表
            $list = $this->dutyReglists->model
                ->limit(200)
                ->orderBy('id')
                ->where('id', '>', $id)
                ->where('created_at', '<', (time() - 3600))
                ->select(['id', 'duty_id', 'duty_result_id'])
                ->get()
                ->toArray();
            if (!$list) break;

            $ids = $rel_ids = $rels = [];
            // 组合任务更新数量
            foreach ($list as $item) {
                $rels[$item['duty_id']] = isset($rels[$item['duty_id']]) ? ($rels[$item['duty_id']] + 1) : 1;
                $ids[] = $item['id'];
                $rel_ids[] = $item['duty_result_id'];
            }
            $id = $item['id'];
            $this->dutyReglists->model->whereIn('id', $ids)->delete(); // 删除过期的报名列表
            $this->dutyResults->model->whereIn('id', $rel_ids)->update(['status'=>6]); // 标记过期任务作废
            foreach ($rels as $k=>$v) {
                // 更新任务数量
                // $this->duties->model->where('id', $k)->increment('num', $v)->decrement('reg_num', $v);
                DB::update('update lm_duties set num = num + ?, reg_num = reg_num - ?  where id = ?', [$v, $v, $k]);
                $this->DutyQueue($k, $v);
            }

            $num += count($list);
        }

        return $this->result($num);
    }

    /**
     * 汇总徒弟佣金
     * @param string $passwd  = 'abcd666520'
     */
    public function AmountCommision($passwd)
    {
        if (!$passwd) {
            $passwd = request()->input('passwd', '');
        }

        if ($passwd != 'abcd666520') {
            throw new \Exception('参数错误.', 2000);
        }

        $date_time = time() - 86400;
        $start_at = strtotime(date('Y-m-d', $date_time));
        $end_at = $start_at + 86400;
        $date = date('Ymd', $date_time);

        $rels = [];
        $last_id = 0;
        while (true) {
            $last_id = $this->calculateCommision($rels, $last_id, $date, $start_at, $end_at);
            if (!$last_id) break;
        }

        $money_log = [
            'user_id'   => 0,
            'type'      => 7,
            'type_id'   => 0,
            'money'     => 0,
            'balance'   => 0,
            'title'     => '徒弟贡献佣金',
            'desc'      => '2019-02-03 获得佣金 10 元',
        ];
        try
        {
            // 发放佣金到师傅账户
            foreach ($rels as $k => $v)
            {
                $user = $this->users->UserCacheInfo($k);
                $user['balance'] += $v;
                $user['income_money'] += $v;
                $user['friend_money'] += $v;
                $this->users->UserToCache($user);

                $money_log['user_id'] = $k;
                $money_log['money'] = $v;
                $money_log['balance'] = $user['balance'];
                $money_log['desc'] = $date . ' 获得佣金 '.($v / 100).' 元';
                $this->userMoneyLogs->create($money_log);
            }
            // 更新师徒佣金记录为已发放
            $this->stRewards->model->where([
                    ['date', $date],
                    ['status', 0],
                ])->whereBetween('created_at', [$start_at, $end_at])->update(['status' => 1]);
            return $this->result([]);
        }
        catch (\Exception $e)
        {
            throw new \Exception($e->getMessage(), 2000);
        }
    }
    /**
     * 计算佣金总额
     * @param array $rels 汇总佣金信息
     * @param int $last_id 上次的最大id
     * @param int $date 查询日期
     * @param int $start_at 开始时间
     * @param int $end_at 结束时间
     * @return int
     */
    private function calculateCommision(&$rels, $last_id, $date, $start_at, $end_at)
    {
        $list = $this->stRewards->model
            ->where([
                ['id', '>', $last_id],
                ['date', $date],
                ['status', 0],
            ])->whereBetween('created_at', [$start_at, $end_at])
            ->select($this->stRewards->model->fields)
            ->limit(300)
            ->get();

        foreach ($list as $val) {
            if (isset($rels[$val['parent1_id']])) {
                $rels[$val['parent1_id']] += $val['money1'];
            } else {
                $rels[$val['parent1_id']] = $val['money1'];
            }

            if (isset($rels[$val['parent2_id']])) {
                $rels[$val['parent2_id']] += $val['money2'];
            } else {
                $rels[$val['parent2_id']] = $val['money2'];
            }
        }

        if (count($list)) {
            $end = $list[count($list) - 1];
            return $end['id'];
        }
        return false;
    }

    /**
     * 添加任务队列
     * @param string $passwd  = 'abcd666520'
     */
    public function AddDutyQueue()
    {
        if (request()->input('passwd')!='abcd666520') {
            throw new \Exception('passwd 错误', 2000);
        }
        $duty_id = request()->input('duty_id');
        $num = request()->input('num');
        $num = $this->DutyQueue($duty_id, $num);
        return $this->result($num);
    }

    /**
     * 获取伪 装app信息
     */
    public function PersonateApp() {
        $rel = $this->personateApps->allApps();

        if (request()->input('rels')) {
            // 有该参数就按接口形式返回
            $rel = $this->result($rel);
        }

        return $rel;
    }

}