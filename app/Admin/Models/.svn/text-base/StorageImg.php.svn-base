<?php

namespace App\Admin\Models;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class StorageImg extends Base {
	protected $fillable = ['img', 'status', 'type'];

    public function setImgAttribute($img)
    {
        if (strpos($img, 'http') === false) {
            $img = Storage::disk(config('admin.upload.disk'))->url($img);
        }
        $this->attributes['img'] = $img;
    }


    /*public function getImgAttribute($img)
    {
        if (IsJson($img))
            $img = json_decode($img, true);

        return $img;
    }*/
}
