<?php
namespace App\Logics\Services;

use App\Logics\Repositories\Repository;
use App\Logics\Traits\ApiResponseTrait;
use App\Logics\Traits\LoginTrait;
use App\Logics\Traits\RedisCacheTrait;
use App\Logics\Traits\ValidateTrait;

abstract class Service implements ServiceInterface {
	use ApiResponseTrait, RedisCacheTrait, ValidateTrait, LoginTrait;

	function __construct() {
		$this->makeRepositories();
	}

	abstract function Repositories();

	public function makeRepositories() {
		$repositories = $this->Repositories();

		if (!is_array($repositories)) {
			throw new \Exception("method repository() must be an array");
		}

		foreach ($repositories as $key => $repository) {
			$rs = app()->make($repository);
			if (!$rs instanceof Repository) {
				throw new \Exception("Class {$repository} must be an instance of App\\Libian\\Repositories\\Repository");
			}

			$this->$key = $rs;
		}

		return $this;
	}
}