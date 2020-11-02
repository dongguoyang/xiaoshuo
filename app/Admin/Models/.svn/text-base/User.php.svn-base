<?php


namespace App\Admin\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Encore\Admin\Facades\Admin;
use App\Logics\Traits\RedisCacheTrait;

class User extends Base {
    use RedisCacheTrait;
    protected $table = 'users';
    protected $fillable = [
        'name', 'email', 'password', 'remember_token', 'invite_code', 'created_at', 'updated_at', 'parent1', 'parent2', 'child_num', 'child2_num', 'img', 'sex', 'subscribe', 'openid', 'unionid',
        'customer_id', 'platform_wechat_id', 'sign_day', 'recharge_money', 'balance', 'vip_end_at', 'extend_link_id'
    ];
    public $fields = [
        'id', 'name', 'email', 'password', 'remember_token', 'invite_code', 'created_at', 'updated_at', 'parent1', 'parent2', 'child_num', 'child2_num', 'img', 'sex', 'subscribe', 'openid', 'unionid',
        'customer_id', 'platform_wechat_id', 'sign_day', 'recharge_money', 'balance', 'vip_end_at', 'extend_link_id'
    ];
    protected $dates = ['created_at', 'updated_at', 'vip_end_at'];
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s', 'vip_end_at' => 'datetime:Y-m-d H:i:s'];

    protected $hidden = ['password'];

    /*protected static function boot() {
        parent::boot();

        static::addGlobalScope('customer_id', function(Builder $builder) {
            $customer = \Illuminate\Support\Facades\Auth::guard('admin')->user();
            if($customer['pid']) {
                $builder->where('customer_id', '=', $customer->id);
            }
        });
    }*/

    public function rechargeLogs() {
        return $this->belongsTo(RechargeLog::class, 'user_id', 'id')->withDefault();
    }

    public function customer() {
        return $this->belongsTo(Customer::class, 'customer_id', 'id')->withDefault();
    }

    public function platformWechat() {
        return $this->belongsTo(PlatformWechat::class, 'platform_wechat_id', 'id')->withDefault();
    }

    public function extendLink() {
        return $this->belongsTo(ExtendLink::class, 'extend_link_id', 'id')->withDefault();
    }

    public function getPasswordAttribute($password) {
        return '';
    }

    public function setPasswordAttribute($password) {
        if(!empty($password) && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*?[#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,])[A-Za-z\d#?!@$%^&*\.\|\~\(\)\-\_\+\=\{\}\[\]:;\'\"\/\\\,]{6,18}$/', $password)) {
            $this->attributes['password'] = Hash::make($password);
        } else {
            unset($this->attributes['password']);
        }
    }

    public function getInviteCodeAttribute($value) {
        return str_pad($value, 10, "0", STR_PAD_LEFT);
    }

    public function setInviteCodeAttribute($value) {
        if((int)$value < 1) {
            $this->attributes['invite_code'] = str_pad($this->IDGenerator('invite_code'), 10, "0", STR_PAD_LEFT);
        } else {
            unset($this->attributes['invite_code']);
        }
    }

    public function setImgAttribute($value) {
        $disk = config('admin.upload.disk');
        if($disk != 'local' && !Str::startsWith($value, ['http://', 'https://', 'HTTP://', 'HTTPS://'])) {
            $temp = Storage::disk($disk)->url($value);
        } elseif($disk == 'local' && !Str::startsWith($value, ['/storage/'])) {
            $temp = Storage::disk($disk)->url($value);
        } else {
            $temp = $value;
        }
        $this->attributes['img'] = $temp ?: $value;
    }
}