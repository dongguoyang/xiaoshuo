<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\StorageTitle;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class StorageTitleRepository extends Repository {
	public function model() {
		return StorageTitle::class;
	}

	public function TitleList($type = 1, $page = 1) {
	    $key = config('app.name') . 'storage_titles_' . $type . '_' . $page;
	    return Cache::remember($key, 1440, function() use ($type, $page) {
	        $list = $this->model
                ->where('type', $type)
                ->where('status', 1)
                ->offset(50 * ($page - 1))
                ->limit(50)
                ->select(['id', 'title', 'desc'])
                ->get();

	        return $this->toArr($list);
        });
    }
}