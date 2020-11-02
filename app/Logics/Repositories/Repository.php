<?php
/**
 * Created by PhpStorm.
 * User: LONG
 * Time: 2019/3/25 20:01
 */

namespace App\Logics\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository implements RepositoryInterface {

	protected $model;

	public $pagenum = 15;

	public function __construct() {
		$this->makeModel();
	}

    /**
     * 根据条件获取记录数
     * @param array $condition 条件
     * @return int 记录数
     */
	public function countByMap(array $condition) {
	    return $this->model->where($condition)->count();
    }

    /**
     * 根据条件获取合计
     * @param array $condition 条件
     * @return int 记录数
     */
    public function sumByMap(array $condition, string $field) {
        if(empty($field) || is_numeric($field)) {
            return null;
        }
        return $this->model->where($condition)->sum($field);
    }

	public function all($columns = array('*')) {
		return $this->model->select($columns)->get();
	}

	public function allByMap(array $condition, $columns = array('*')) {
		return $this->model->select($columns)->where($condition)->get();
	}

	public function paginate($perPage = 15, $columns = array('*')) {
		return $this->model->select($columns)->paginate($perPage);
	}

	public function paginateByMap(array $condition, $perPage = 15, $columns = array('*')) {
		return $this->model->select($columns)->where($condition)->paginate($perPage);
	}

	public function limitPage(array $condition, $page, $perPage = 15, $columns = array('*'), $orderBy = ['id'=>'desc']) {
		$offset = ($page - 1) * $perPage;
		$query = $this->model->select($columns)->where($condition)->limit($perPage)->offset($offset);
		foreach ($orderBy as $col=>$order) {
		    $query = $query->orderBy($col, $order);
        }
		return $query->get();
	}

    /**
     * 根据条件获取所有
     * 注：分组最好取出结果后通过集合分组
     * @param array $condition 条件
     * @param array $columns 字段
     * @param array $orderBy 排序字段集
     * @return Collection
     */
	public function getAllByMap(array $condition, $columns = array('*'), array $orderBy = ['id'=>'desc']) {
	    $query = $this->model->select($columns)->where($condition);
        foreach ($orderBy as $col => $order) {
            $query = $query->orderBy($col, $order);
        }
	    return $query->get();
    }

	public function create(array $data) {
		return $this->model->create($data);
	}

	public function update(array $data, $value, $attribute = 'id') {
		return $this->model->where($attribute, '=', $value)->update($data);
	}

    public function updateByMap(array $data, array $condition)
    {
        if(empty($condition)) {
            return false;
        }
        return $this->model->where($condition)->update($data);
    }

	public function delete($id, $attribute = 'id') {
		return $this->model->where($attribute, $id)->delete();
	}

	/**
	 * 根据条件组合删除指定数据
	 * @param array: $cond 条件组合
	 * @return mixed
	 */
	public function deleteByMap(array $cond) {
		if (empty($cond)) {
			return false;
		}
		return $this->model->where($cond)->delete();
	}

	public function find($id, $columns = array('*')) {
		return $this->model->find($id, $columns);
	}

	public function findBy($attribute, $value, $columns = array('*')) {
		return $this->model->where($attribute, '=', $value)->first($columns);
	}

	public function findByMap(array $condition, $columns = array('*')) {
		return $this->model->where($condition)->first($columns);
	}

    public function findAValue($id, string $column) {
        $info = $this->model->find($id, [$column]);
        return $info ? $info[$column] : null;
    }

    /**
     * 根据条件查找某条数据
     * @param array $condition 条件
     * @param array $columns 字段数组
     * @param array $order 排序数组, 形如：[
     *                                          ['column1', 'asc'],
     *                                          ['column2', 'desc'],
     *                                          ...
     *                                      ] 或 ['column', 'asc']
     * @return array 数据
     */
    public function findByCondition(array $condition, $columns = array('*'), $order = []) {
	    if(empty($order) || !is_array($order)) {
            return Optional($this->model->where($condition)->first($columns))->toArray();
        } else {
	        $res = $this->model->where($condition);
	        if(isset($order[0]) && is_array($order[0])) {
	            foreach($order as $orderItem) {
	                if(!empty($orderItem)) {
                        $res = $res->orderBy($orderItem[0], strtolower($orderItem[1]) == 'desc' ? 'desc' : 'asc');
                    }
                }
            } else {
                $res = $res->orderBy($order[0], strtolower($order[1]) == 'desc' ? 'desc' : 'asc');
            }
            return Optional($res->first($columns))->toArray();
        }
    }

	public function findPluck($where, $key, $val) {
		return $this->model->where($where)->pluck($key, $val);
	}

	public function column($columb, $value, $item = 'id') {
		$info = $this->findBy($item, $value, [$columb]);
		return $info ? $info->$columb : null;
	}

	// 对象转数组
	public function toArr($obj) {
        if (is_array($obj)) {
            return $obj;
        }elseif ($obj && ($obj instanceof Collection) || $obj instanceof Model) {
			return $obj->toArray();
		} else {
			return [];
		}
	}
	// 获取所需信息转为键值对
	public function toPluck($list, $val, $key='') {
		if ($list && $list instanceof Collection) {
			if ($key === '') {
                $list = $list->pluck($val);
            } else {
                $list = $list->pluck($val, $key);
            }
            $list = $list->toArray();
		}
		return $list;
	}

	/**
	 * 根据主键ID对指定字段增减数量
	 * @param int: $id 主键ID
	 * @param string: $column 指定的字段
	 * @param string: $action 是增还是减
	 * @param int: $step 步进值，默认1
	 * @return mixed
	 */
	public function setColumnIncOrDescById($id, $column, $action = 'increment', $step = 1) {
		if (!in_array($action, ['increment', 'decrement'])) {
			return false;
		}
		return $this->model->where('id', $id)->$action($column, $step);
	}

	/**
	 * 根据条件组合对指定字段增减数量
	 * @param array: $cond 条件组合
	 * @param string: $column 指定的字段
	 * @param string: $action 是增还是减
	 * @param int: $step 步进值，默认1
	 * @return mixed
	 */
	public function setColumnIncOrDescByCondition($cond, $column, $action = 'increment', $step = 1) {
		if (!in_array($action, ['increment', 'decrement']) || empty($cond)) {
			return false;
		}
		return $this->model->where($cond)->$action($column, $step);
	}

	abstract function model();

	public function makeModel() {
		$model = app()->make($this->model());
		if (!$model instanceof Model) {
			throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
		}

		return $this->model = $model;
	}

	public function __get($name) {
		// TODO: Implement __get() method.
		return $this->$name;
	}

	/**
	 * 打印最近的sql语句
	 * @param string $type start 启用该功能； end 执行完成打印sql
	 */
	public function dumpSql($type = 'start') {
		if ($type == 'start') {
			\DB::enableQueryLog();
		} else {
			dd(\DB::getQueryLog());
		}
	}
}