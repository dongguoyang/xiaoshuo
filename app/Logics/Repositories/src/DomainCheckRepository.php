<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\DomainCheck;
use App\Logics\Repositories\Repository;
use Illuminate\Support\Facades\Cache;

class DomainCheckRepository extends Repository {
	public function model() {
		return DomainCheck::class;
	}

	public function hosts() {
	    $key = config('app.name') . 'domain_checks';
	    return Cache::remember($key, 1440,  function (){
	        $list = $this->allByMap([['status', 1]], ['id', 'host as url']);
	        $list = $this->toArr($list);
	        foreach ($list as $k=>$v) {
	            $list[$k]['id'] = $v['id'] . '';
            }

	        return $list;
        });
    }

    public function updateHost($id) {
	    $this->update(['status'=>0], $id);
	    $info = $this->find($id, ['api']);
	    CallCurl($info['api'] . '?id='. $id);

        $key = config('app.name') . 'domain_checks';
	    Cache::forget($key);
    }

}