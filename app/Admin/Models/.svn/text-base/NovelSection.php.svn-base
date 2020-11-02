<?php
namespace App\Admin\Models;


class NovelSection extends Base {
    protected $table = 'novel_sections';
    protected $fillable = [
        'novel_id', 'num', 'title', 'content', 'updated_num', 'updated_at', 'created_at'
    ];
    public $fields = [
        'id', 'novel_id', 'num', 'title', 'content', 'updated_num', 'updated_at', 'created_at'
    ];

    public function novel() {
        return $this->belongsTo(Novel::class, 'novel_id', 'id')->withDefault();
    }

    public static function num2titlePluck($novel_id) {
        return self::where('novel_id', $novel_id)->select(['num', 'title'])->orderBy('num', 'asc')->limit(20)->pluck('title', 'num');
    }
}