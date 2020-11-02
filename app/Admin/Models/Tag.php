<?php

namespace App\Admin\Models;

class Tag extends Base {
	protected $fillable = ['name', 'img', 'status'];

	public function setImgAttribute($value) {
		if (strpos($value, 'http') === false) {
			$value = CloudPreDomain() . $value;
		}
		$this->attributes['img'] = $value;
	}

    /**
     * 标签对应视频
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function video() {
	    return $this->belongsToMany(Video::class, 'video_tag_rels', 'tag_id', 'video_id');
    }
}
