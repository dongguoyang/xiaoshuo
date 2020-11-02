<?php


namespace App\Admin\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Encore\Admin\Facades\Admin;

class WechatsUser extends Base {
    protected $table = 'wechats_users';
    protected $fillable = [
        'name', 'created_at', 'updated_at', 'img', 'sex', 'openid', 'customer_id', 'platform_wechat_id','subscribe_time','subscribe_scene'
    ];
    public $fields = [
        'id', 'name', 'created_at', 'updated_at', 'img', 'sex', 'openid', 'customer_id', 'platform_wechat_id','subscribe_time','subscribe_scene'
    ];
    protected $dates = ['created_at', 'updated_at','subscribe_time'];
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s','subscribe_time'=>'datetime:Y-m-d H:i:s'];
    
    public function wechats_user_list($platform_wechat_id){
        $res = $this->where('platform_wechat_id','=',$platform_wechat_id)->first();
        if($res){
            return $res;
        }else{
            return $this;
        }
        
    }
    
    public function addAll(){
        
    }
    
}