<?php
namespace App\Admin\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Coupon extends Base {
    protected $table = 'coupons';
    protected $fillable = ['name', 'img', 'type', 'novel_info', 'count', 'send_num', 'use_num', 'status', 'start_at', 'end_at', 'updated_at', 'created_at'];
    public $fields = ['id', 'name', 'img', 'type', 'novel_info', 'count', 'send_num', 'use_num', 'status', 'start_at', 'end_at', 'updated_at', 'created_at'];
    protected $dates = ['created_at', 'updated_at', 'start_at', 'end_at'];
    protected $casts = ['created_at' => 'datetime:Y-m-d H:i:s', 'updated_at' => 'datetime:Y-m-d H:i:s', 'start_at' => 'datetime:Y-m-d H:i:s', 'end_at' => 'datetime:Y-m-d H:i:s'];

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