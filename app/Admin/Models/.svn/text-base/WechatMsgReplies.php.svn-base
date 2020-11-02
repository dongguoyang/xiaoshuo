<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Admin\Models\Base;
class WechatMsgReplies extends Base
{
    //
    protected $table='wechat_msg_replies';

    protected $appends = [ 'title','desc','img','url'];
    public function custom(){
        return $this->hasOne(Customer::class,'id','customer_id');
    }
    public  function platform_wechat(){
        return $this->hasOne(PlatformWechat::class,'id','platform_wechat_id');
    }
 /*   public function getReplyContentAttribute($contetn)
    {
        if($this->is_json($contetn)){ //是json
             $contetn=\GuzzleHttp\json_decode($contetn,1);
             $contetn=$contetn['img'];
        }
        return $contetn;
    }*/
    public function getTitleAttribute()
    {
        $title="";
        if($this->is_json($this->reply_content)){
             $data=\GuzzleHttp\json_decode($this->reply_content,1);
             $title=$data['title'];
        }
        return $this->attributes['title'] = $title;
    }
    public function getDescAttribute()
    {
        $desc="";
        if($this->is_json($this->reply_content)){
            $data=\GuzzleHttp\json_decode($this->reply_content,1);
            $desc=$data['desc'];
        }
        return $this->attributes['desc'] = $desc;
    }
    public function getImgAttribute()
    {
        $img="";
        if($this->is_json($this->reply_content)){
            $data=\GuzzleHttp\json_decode($this->reply_content,1);
            $img=$data['img'];
        }
        return $this->attributes['img'] = $img;
    }
    public function getUrlAttribute(){
        $url="";
        if($this->is_json($this->reply_content)){
            $data=\GuzzleHttp\json_decode($this->reply_content,1);
            $url=$data['url'];
            return $this->attributes['url'] = $url;
        }
    }



    protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', '=', $customer->id);
            }
        });
    }


    protected  function is_json($json_str) {
        $json_str = str_replace('＼＼', '', $json_str);
        $out_arr = array();
        preg_match('/{.*}/', $json_str, $out_arr);
        if (!empty($out_arr)) {
            $result = json_decode($out_arr[0], TRUE);
        } else {
            return FALSE;
        }
        return $result;
    }



}
