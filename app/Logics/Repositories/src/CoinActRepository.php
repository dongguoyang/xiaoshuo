<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\CoinAct;
use App\Logics\Repositories\Repository;

class CoinActRepository extends Repository {
	public function model() {
		return CoinAct::class;
	}

}