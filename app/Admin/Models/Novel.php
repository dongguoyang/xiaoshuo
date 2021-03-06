<?php
namespace App\Admin\Models;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Novel extends Base {
    protected $table = 'novels';
    protected $fillable = [
        'title', 'img', 'author_id', 'author_name', 'serial_status', 'word_count', 'suitable_sex', 'type_ids', 'tags', 'desc', 'sections', 'status', 'read_num',
        'week_read_num', 'allbuy_coin', 'reward_coin', 'need_buy_section', 'subscribe_section', 'recommend_section', 'hot_num', 'free_start_at', 'free_end_at', 'spider_url', 'updated_at', 'created_at'
    ];
    public $fields = [
        'id', 'title', 'img', 'author_id', 'author_name', 'serial_status', 'word_count', 'suitable_sex', 'type_ids', 'tags', 'desc', 'sections', 'status', 'read_num',
        'week_read_num', 'allbuy_coin', 'reward_coin', 'need_buy_section', 'subscribe_section', 'recommend_section', 'hot_num', 'free_start_at', 'free_end_at', 'spider_url', 'updated_at', 'created_at'
    ];

    public function author() {
        return $this->belongsTo(Author::class, 'author_id', 'id')->withDefault();
    }

    public function types() {
        return $this->belongsToMany(Type::class, 'novel_type_rels', 'novel_id', 'type_id');
    }

    /*public function getTypeIdsAttribute() {
        $temp = [];
        $types = $this->types;
        foreach($types as $type) {
            $temp[] = $type['pivot']['type_id'];
        }
        return $temp;
    }

    public function setTypeIdsAttribute() {
        $temp = [];
        $types = $this->types;
        foreach($types as $type) {
            $temp[] = $type['pivot']['type_id'];
        }
        $this->attributes['type_ids'] = implode('|', $temp);
    }*/


    public function setDescAttribute($value) {
        $this->attributes['desc'] = strip_tags($value);
    }
    public function setFreeStartAtAttribute($value) {
        $this->attributes['free_start_at'] = strtotime($value);
    }
    public function setFreeEndAtAttribute($value) {
        $this->attributes['free_end_at'] = strtotime($value);
    }
    public function getFreeStartAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }
    public function getFreeEndAtAttribute($value) {
        if (strpos($value, '-') || strpos($value, ':') || strpos($value, '/')) {} else {
            $value = date('Y-m-d H:i:s', $value);
        }
        return $value;
    }

   public function setImgAttribute($picture) {
        if (strpos($picture, 'http') === false) {
            $picture = CloudPreDomain() . $picture;
        }
        $this->attributes['img'] = $picture;
    }
    
    public function setImgUrlAttribute($picture) {
        if($picture){
            $this->attributes['img'] = $picture;
        }
    }
}