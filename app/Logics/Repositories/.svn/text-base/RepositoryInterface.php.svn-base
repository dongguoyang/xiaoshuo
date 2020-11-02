<?php
/**
 * Created by PhpStorm.
 * User: LM
 * Time: 2019/7/15 13:10
 */

namespace App\Logics\Repositories;

interface RepositoryInterface {
	// 查询所有
	public function all($columns = array('*'));
	public function allByMap(array $condition, $columns = array('*'));

	// 分页查询
	public function paginate($perPage = 15, $columns = array('*'));
	public function paginateByMap(array $condition, $perPage = 15, $columns = array('*'));

	// 查询第几页数据
	public function limitPage(array $condition, $page, $perPage = 15, $columns = array('*'));

	// 创建
	public function create(array $data);

	// 更新
	public function update(array $data, $id, $attribute);
	public function updateByMap(array $data, array $condition);

	// 删除
	public function delete($id);
	public function deleteByMap(array $cond);

	// 查询单条数据
	public function find($id, $columns = array('*'));
	public function findBy($attribute, $value, $columns = array('*'));

	// 根据条件查询单条数据
	public function findByMap(array $condition, $columns = array('*'));

}