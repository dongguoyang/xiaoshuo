<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Test;
use App\Logics\Repositories\Repository;

class TestRepository extends Repository {
	public function model() {
		return Test::class;
	}


    public function create(array $data) {
	    return true;
         //return $this->model->create($data);
    }

}
