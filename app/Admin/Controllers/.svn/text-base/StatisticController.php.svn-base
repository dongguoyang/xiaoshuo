<?php
namespace App\Admin\Controllers;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Logics\Repositories\src\StatisticRepository;
use App\Logics\Traits\ApiResponseTrait;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StatisticsExport;

class StatisticController extends AdminController {
    public $customer;

    public function customer() {
        if(!empty($this->customer)) {
            return $this->customer;
        } else {
            return $this->customer = Admin::user();
        }
    }

    public function index(Content $content) {
        $params = request()->input();
        $params['p'] = $page = max(1, $params['p'] ?? 1);
        $query = '';

        $extend_condition = [];
        if(isset($params['start_date']) && strtotime($params['start_date'])) {
            $query .= 'start_date='.$params['start_date'].'&';
            $extend_condition[] = ['date_belongto', '>=', $params['start_date']];
        }
        if(isset($params['end_date']) && strtotime($params['end_date'])) {
            $query .= 'end_date='.$params['end_date'].'&';
            $extend_condition[] = ['date_belongto', '<=', $params['end_date']];
        }
        if(isset($params['with_deduct']) && $params['with_deduct'] == 1) {
            $query .= 'with_deduct=1&';
            $extend_condition[] = ['deduct_money', '>', 0];
        }
        $query = trim($query, '&');
        $customer = $this->customer();
        $statistics = new StatisticRepository;
        $group_id = $customer['pid'] ?: $customer['id'];
        $data = $statistics->getExtendedList($group_id, $group_id == $customer['id'] ? 0 : $customer['id'], $extend_condition, $page);
        $today = $statistics->getStatisticsToday($group_id, $group_id == $customer['id'] ? 0 : $customer['id']);
        $yesterday = $statistics->getStatisticsYesterday($group_id, $group_id == $customer['id'] ? 0 : $customer['id']);
        $month = $statistics->getStatisticsThisMonth($group_id, $group_id == $customer['id'] ? 0 : $customer['id']);
        $all = $statistics->getStatisticsAll($group_id, $group_id == $customer['id'] ? 0 : $customer['id']);
        $page_list_config = getPageList($page, $data['record_count'], $query, 20);
        $params['p'] = $page = $page_list_config['current_page'];
        $page_arr = $page_list_config['page_list'];

        return $content->view('admin.statistics.index', [
            'data'      =>  $data,
            'today'     =>  $today,
            'yesterday' =>  $yesterday,
            'month'     =>  $month,
            'all'       =>  $all,
            'params'    =>  $params,
            'query'     =>  $query,
            'page_arr'  =>  $page_arr
        ]);
    }

    public function export() {
        $params = request()->input();
        $params['p'] = $page = max(1, $params['p'] ?? 1);
        $file_name = '每日消费统计';

        $extend_condition = [];
        if(isset($params['start_date']) && strtotime($params['start_date'])) {
            $extend_condition[] = ['date_belongto', '>=', $params['start_date']];
            $file_name .= $params['start_date'].'后';
        }
        if(isset($params['end_date']) && strtotime($params['end_date'])) {
            $extend_condition[] = ['date_belongto', '<=', $params['end_date']];
            if(isset($params['start_date']) && strtotime($params['start_date'])) {
                $file_name .= '至'.$params['end_date'].'前';
            } else {
                $file_name .= $params['end_date'].'前';
            }
        }
        if(isset($params['with_deduct']) && $params['with_deduct'] == 1) {
            $extend_condition[] = ['deduct_money', '>', 0];
            $file_name .= '[分成]';
        }

        $customer = $this->customer();
        $statistics = new StatisticRepository;
        $group_id = $customer['pid'] ?: $customer['id'];
        $data = $statistics->getExtendedList($group_id, $customer['id'], $extend_condition, $page);
        $params['p'] = min($params['p'], $data['max_page']);
        $file_name .= 'p'.$params['p'].'.xlsx';

        // 导出EXCEL
        $rows = [];
        $header = ['日期', '充值金额', '分成金额', '充值数据分析'];
        foreach($data['list'] as $record) {
            $rows[] = [
                $record['date_belongto'],
                '￥'.round($record['recharge_money'] / 100, 2),
                '￥'.round($record['deduct_money'] / 100, 2),
                '充值笔数：'.$record['bill_recharge_count']."\r\n".
                '充值人数：'.$record['recharged_user_count']."\r\n".
                '未支付：'.$record['unpay_num']."\r\n".
                '充值成功率：'.$record['pay_success_ratio']."%\r\n".
                '人均消费：'.round($record['average_consumption'] / 100, 2)
            ];
        }
        return Excel::download(new StatisticsExport($rows, $header), $file_name);
    }
}