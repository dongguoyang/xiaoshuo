<?php
namespace App\Admin\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Goods extends Base {
    protected $table = 'goodses';
    protected $fillable = ['name', 'coin', 'img', 'count', 'stock', 'sale_num', 'sort', 'status', 'updated_at', 'created_at'];
    public $fields = ['id', 'name', 'coin', 'img', 'count', 'stock', 'sale_num', 'sort', 'status', 'updated_at', 'created_at'];

    public function setStockAttribute() {
        $this->attributes['stock'] = $this->count - $this->sale_num;
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