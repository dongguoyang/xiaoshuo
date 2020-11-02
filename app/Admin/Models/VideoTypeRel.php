<?php

namespace App\Admin\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class VideoTypeRel extends Pivot {

    public $timestamps = false;	// 不自动维护 updated_at、created_at

	protected $fillable = ['video_id', 'type_id', ];

}
