<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\Author;
use App\Logics\Repositories\Repository;

class AuthorRepository extends Repository {
	public function model() {
		return Author::class;
	}

	public function getOrAddAuthor($name) {
	    $had = $this->findBy('name', $name, ['id']);
	    if (!$had) {
	        $data = ['name'=>$name, 'status'=>1];
	        $had = $this->create($data);
        }

	    return $had['id'];
    }
}