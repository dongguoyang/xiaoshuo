<?php


namespace App\Admin\Models;


use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Builder;

class ExtendLink extends Base {
    protected $table = 'extend_links';
    protected $fillable = ['customer_id', 'novel_id', 'chapter_id', 'novel_section_num', 'title', 'cost', 'link', 'link_preview', 'preview_expired_at', 'type', 'must_subscribe', 'subscribe_section', 'status', 'updated_at', 'created_at', 'page_conf', 'data_info'];
    public $fields = ['id', 'customer_id', 'novel_id', 'chapter_id', 'novel_section_num', 'title', 'cost', 'link', 'link_preview', 'preview_expired_at', 'type', 'must_subscribe', 'subscribe_section', 'status', 'updated_at', 'created_at', 'page_conf', 'data_info'];
    public $appends=['extend_data', 'qd_info', 'recharge_info'];
    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', $customer->id);
            }
        });
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withDefault();
    }

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id')->withDefault();
    }
    public function getDataInfoAttribute($va)
    {
        if($va) {
            $va = \GuzzleHttp\json_decode($va, 1);
            return $va;
        }
    }
    public function getExtendDataAttribute($rs=1){
        $dataInfo=$this->attributes['data_info']; //推广数据统计
        if(!empty($dataInfo)){
            $dataInfo=\GuzzleHttp\json_decode($dataInfo,1);
            $users=0; //员访问人数
            $recharge=0; //充值笔数
            $recharge_succ=0;
            $money=0; //充值金额
            $subscribe=0; //关注人数
            foreach ($dataInfo as $value){
                $users+=$value['user'];
                $recharge+=$value['recharge'];
                $recharge_succ+=$value['recharge_succ'];
                $money+=$value['money'];
                $subscribe+=$value['subscribe'];
            }
            //推广数据  人数 关注数 关注率
            if(!$rs) {
                $dx = [
                    '人数' => $users,
                    '关注数' => $subscribe,
                    '关注率' => round(($subscribe / ($users ? $users : 1)) * 100) . '%',   //(float)($subscribe/$users), //关注率
                    'sub2rech' => round(($recharge / ($subscribe ? $subscribe : 1)) * 100) . '%',//充值/关注比
                    'sub2rechsucc' => round(($recharge_succ / ($subscribe ? $subscribe : 1)) * 100) . '%',//充值成功/关注比例
                ];

            }else{ //充值记录
                 $cost=$this->attributes['cost'];
                 if(empty($cost)){
                    $hbl=0;
                 }else{
                     $hbl=round(($money / $cost) * 100);
                 }
                 if(empty($recharge)){
                    $czl=0;
                 }else{
                     $czl=round(($recharge_succ / $recharge) * 100);
                 }
                 $dx=[
                     '充值笔数'=>$recharge,
                     '成交数'=>$recharge_succ,
                     '渠道成本'=>$this->attributes['cost'],
                     '充值成功率'=>$czl. '%',
                     '充值金额'=>$money,
                     '回本率'=>$hbl. '%'
                 ];
            }
            return \GuzzleHttp\json_encode($dx);

        }
    }
    public function getQdInfoAttribute(){
        $data=[
            'title'=>$this->attributes['title'],  //渠道名称
            'novel_title'=>$this->getNovelTitle($this->attributes['novel_id']),
            'ID'=>$this->attributes['id'],
            'link'=>$this->attributes['link'],
            'type'=>$this->typelabel($this->attributes['type']),
            'created_at'=>$this->attributes['created_at']
            ];
        $data=\GuzzleHttp\json_encode($data);
        return $data;
    }
    public function typelabel($num){
        if($num==1){
            return '<span class="label label-success">外推</span>';
        }else{
            return '<span class="label label-danger">内推</span>';
        }
    }

    public function getNovelTitle($id){
        $rs=Novel::find($id);
        if($rs){
            return $rs->title;
        }

    }
    public function getRechargeInfoAttribute(){
        $r=$this->getExtendDataAttribute();
        return $r;

    }






}