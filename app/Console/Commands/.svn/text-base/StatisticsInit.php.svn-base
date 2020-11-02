<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Logics\Repositories\src\StatisticRepository;
use App\Admin\Models\Customer;

class StatisticsInit extends Command
{
    public $total = 0;
    public $valid_count = 0;
    public $invalid_count = 0;
    public $success_count = 0;
    public $failure_count = 0;
    public $skip_count = 0;
    public $page_size = 1000;
    public $last_max_id = 0;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'statistics:init {customer_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化第二天统计数据，命令后接 商户ID 则 只初始化指定商户';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start_time = microtime(true);
        $msg = 'Mission started at '.date('Y-m-d H:i:s', (int)$start_time);
        $this->info($msg);

        // 初始化查询条件
        $customer_id = $this->argument('customer_id');
        if(isset($customer_id) && is_number((string)$customer_id) && $customer_id > 0) {
            $cond = [
                ['id', '=', $customer_id]
            ];
        } else {
            $cond = [
                ['id', '>', 0]
            ];
        }
        $statistics = new StatisticRepository;
        $date_init = date('Y-m-d', strtotime('tomorrow'));

        do {
            $customers = Customer::where($cond)
                ->orderBy('id', 'asc')
                ->limit($this->page_size)
                ->get();
            $customers = $customers ? $customers->toArray() : [];
            foreach ($customers as $customer) {
                ++$this->total;
                $this->last_max_id = $customer['id'];

                $group_id = $customer['pid'] ?: $customer['id'];
                // 如果当前商户为组长，则额外多加一条数据
                if($group_id == $customer['id']) {
                    $statistic_group_init = $statistics->findByCondition([
                        ['date_belongto', '=', $date_init],
                        ['group_id', '=', $group_id],
                        ['customer_id', '=', 0]
                    ]);
                    if(empty($statistic_group_init)) {
                        $statistics->initStatistic($date_init, $group_id, 0);
                    }
                }

                // 检查统计是否已初始化，若已初始化则跳过
                $statistic_init = $statistics->findByCondition([
                    ['date_belongto', '=', $date_init],
                    ['group_id', '=', $group_id],
                    ['customer_id', '=', $customer['id']]
                ]);
                if(!empty($statistic_init)) {
                    ++$this->skip_count;
                    $this->info('[NOTICE] customer ['.$customer['id'].'] has initialized the statistical data already');
                    continue;
                }
                $statistic_init = $statistics->initStatistic($date_init, $group_id, $customer['id']);
                if($statistic_init) {
                    ++$this->success_count;
                    $this->info('[INFO] customer ['.$customer['id'].'] has initialized the statistical data successfully');
                } else {
                    ++$this->failure_count;
                    $this->error('[WARNING] customer ['.$customer['id'].'] failed to initialize the statistical data');
                }
            }
            $cond = [
                ['id', '>', $this->last_max_id]
            ];
        } while(count($customers) >= $this->page_size);
        $end_time = microtime(true);
        $time_spent = $end_time - $start_time;
        $msg = 'Mission Finished at '.date('Y-m-d H:i:s', (int)$start_time).', with in '.gmdate("H:i:s", (int)$time_spent).' [seconds: '.$time_spent.']';
        $this->info($msg);
        $this->info("Summary: ");
        $this->info("Total: ".$this->total);
        $this->info("Success: ".$this->success_count);
        $this->info("Failure: ".$this->failure_count);
        $this->info("Skip: ".$this->skip_count);
        return true;
    }
}
