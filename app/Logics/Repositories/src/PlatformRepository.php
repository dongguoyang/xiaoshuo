<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Platform;
use App\Logics\Repositories\Repository;

class PlatformRepository extends Repository {
	public function model() {
		return Platform::class;
	}
}