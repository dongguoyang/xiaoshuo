<?php
namespace App\Admin\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Prize extends Base {
    protected $table = 'prizes';
    protected $fillable = ['name', 'img', 'count', 'send_num', 'type', 'coin', 'coupon_id', 'chance', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'name', 'img', 'count', 'send_num', 'type', 'coin', 'coupon_id', 'chance', 'status', 'updated_at', 'created_at'];

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

    public function setCoinAttribute($value) {
        if($this->type != 1) {
            $this->attributes['coin'] = 0;
        } else {
            $this->attributes['coin'] = $value;
        }
    }

    public function setCouponIdAttribute($value) {
        if($this->type != 2) {
            $this->attributes['coupon_id'] = 0;
        } else {
            $this->attributes['coupon_id'] = $value;
        }
    }
}