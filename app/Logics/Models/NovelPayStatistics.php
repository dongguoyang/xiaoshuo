<?php

namespace App\Logics\Models;

class NovelPayStatistics extends Base {
    protected $table = 'novel_pay_statistics';
    protected $fillable = ['date_belongto', 'group_id', 'customer_id', 'read_num', 'pay_num', 'novel_id', 'updated_at', 'created_at'];
    public $fields = ['id', 'date_belongto', 'group_id', 'customer_id', 'read_num', 'pay_num', 'novel_id', 'updated_at', 'created_at'];
    
    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id');
    }
    
    public function read_num($novel_id,$customer,$data = 0){
        $map = [
                 ['novel_id','=',$novel_id]
            ];
        if($customer['pid']){
             $map[1] = ['customer_id','=',$customer['id']];
        }
        if($data == date('Y-m-d')){ //获取当天实时数据
            $beginToday = mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday   = mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $res = (new ReadLog())
                ->where($map)
                ->whereBetween('updated_at',[$beginToday,$endToday])
                ->count('id');
        }else{
           if($data){
               $map[2] = ['date_belongto','=',$data];
           }
           $res = (new UserReadDay())->where($map)->sum('read_num'); 
        }
        return $res?$res:0;
    }
}