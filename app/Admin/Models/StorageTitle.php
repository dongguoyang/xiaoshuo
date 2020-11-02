<?php

namespace App\Admin\Models;

use Illuminate\Support\Facades\Cache;

class StorageTitle extends Base {
	protected $fillable = ['title', 'desc', 'status', 'type'];

}
