<?php

namespace App\Admin\Models;


class Video extends Base {
	protected $fillable = [
        'title', 'alias', 'score', 'clarity', 'starring', 'type_ids', 'tag_ids', 'area', 'lang', 'release_date',
        'duration', 'update_date', 'play_num', 'score_num', 'desc', 'status',
    ];

    public function tag() {
        return $this->belongsToMany(Tag::class);
    }
    public function type() {
        return $this->belongsToMany(Type::class);
    }
}
