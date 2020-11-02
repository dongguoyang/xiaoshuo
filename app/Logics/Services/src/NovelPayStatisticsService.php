<?php

namespace App\Logics\Services\src;

use App\Logics\Repositories\src\RechargeLogRepository;
use App\Logics\Repositories\src\NovelPayStatisticsRepository;
use App\Logics\Repositories\src\ReadLogRepository;
use App\Logics\Repositories\src\CoinLogRepository;
use App\Logics\Repositories\src\UserReadDayRepository;
use App\Logics\Services\Service;


class NovelPayStatisticsService extends Service {

    protected $NovelPayStatistics;
    protected $rechargeLogs;
    protected $readLogs;
    protected $coinLogs;
    protected $userReadDay;
    

    public function Repositories() {
        return [
            'NovelPayStatistics'=>NovelPayStatisticsRepository::class,
            'rechargeLogs'  => RechargeLogRepository::class,
            'readLogs'=>ReadLogRepository::class,
            'coinLogs'=>CoinLogRepository::class,
            'userReadDay'=>UserReadDayRepository::class,
        ];
    }
    
        //获取首页展示数据
    public function index_data($customer_id = 0){
        $map = [];
        if($customer_id){
            $temp = $this->NovelPayStatistics->where('customer_id',$customer_id)->selectRaw('sum(pay_num) as sum, id,novel_id,customer_id,read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc')->get();
            $map = [
                'customer_id'=>$customer_id
            ];
        }else{
            $temp = $this->NovelPayStatistics->selectRaw('sum(pay_num) as sum, id,novel_id,customer_id,read_num,updated_at')->groupBy('novel_id')->orderby('sum','desc')->get();
        }
        if($temp){
            foreach ($temp as $key=>$value){
                $map['novel_id'] = $value['novel_id'];
                $res = $this->userReadDay->where($map)->groupBy('date_belongto')->sum('read_num');
                if($res){
                    $temp[$key]['read_num'] = $res;
                }
            }
        }
        return $temp;
    }

    //判断改付费章节的消费书币 是否为用户今日为该小说的充值
    public function UserNovelPay($user_info,$novel_id){
        $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
        $endToday   = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $map1 = [
            ['user_id','=',$user_info['id']],
            ['status','=',1]
        ];
        $info = $this->rechargeLogs->model->where($map1)->whereBetween('updated_at',[$beginToday,$endToday])->select($this->rechargeLogs->model->fields)->first();
        if($info){
            if ($user_info['vip_end_at'] && $user_info['vip_end_at'] > time()) {
                //查询用户阅读记录 找出今天第一次观看的收费章节的小说
                $read_info = $this->readLogs->model->where('user_id',$user_info['id'])->whereBetween('updated_at',[$beginToday,$endToday])->select($this->readLogs->model->fields)->orderBy('updated_at')->get();
                if($read_info){
                    $novel = 0;
                    foreach ($read_info as $key=>$value){
                        if($value['novel']['need_pay_section'] < $value['max_section_num']){
                            $novel = $value['novel_id'];
                            break;
                        }
                    }
                }
                if($novel){
                   return false;  
                }else{
                   return true;   
                }

            }else{//2用户不是vip
                //查询今天第一次付费章节的小说
                $map = [
                    ['user_id','=',$user_info['id']],
                    ['type','=',3],
                    ['status','=',1],
                    ['updated_at','>',$info['updated_at']]
                ];
                $coin_log_info = $this->coinLogs->model->where($map)->whereBetween('updated_at',[$beginToday,$endToday])->select($this->coinLogs->model->fields)->orderBy('updated_at')->first();
                if($coin_log_info){
                    return false;
                }else{
                    return true;
                }
            }
        }else{
            return false;
        }

    }

}
