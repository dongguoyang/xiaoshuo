<?php
namespace App\Logics\Repositories\src;

use App\Logics\Models\UserPrereg;
use App\Logics\Repositories\Repository;

class UserPreregRepository extends Repository {
	public function model() {
		return UserPrereg::class;
	}

	public $id_pre = 'pre';
    /**
     * 添加或者更新用户信息
     */
    public function AddOrUpdate($user_id, $customer_id, $openid) {
        $info = $this->preregInfo($user_id, $customer_id);

        if (!$info) {
            $this->create(['user_id'=>$user_id, 'customer_id'=>$customer_id, 'openid'=>$openid]);
        } else {
            if ($info['openid'] != $openid) {
                $this->update(['openid' => $openid], $info['id']);
            }
        }
    }
    /**
     * 获取用户信息
     */
    public function PreregInfo($user_id, $customer_id) {
        $info = $this->findByMap([
            ['user_id', $user_id],
            ['customer_id', $customer_id],
        ], $this->model->fields);

        return $this->toArr($info);
    }













    /**
     * 外推链接注册信息
     * @param $user 微信获取的用户信息
     * @param $state 登录前的state参数
     * @param $wechat_id 商户的微信ID
     */
    public function OutLinkReg($data) {
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->model->fields)) {
                unset($data[$k]);
            }
        }

        if (!$had = $this->findBy('openid', $data['openid'], $this->model->fields)) {
            $had = $this->create($data);
        }

        $had = $this->toArr($had);
        $had['id'] = $this->id_pre . $had['id'];
        return $had;
    }
    /**
     * 外推链接注册信息
     * @param $user 微信获取的用户信息
     * @param $state 登录前的state参数
     * @param $wechat_id 商户的微信ID
     */
    public function FindByID($id) {
        $id = strpos($id, $this->id_pre) === 0 ? str_replace($this->id_pre, '', $id) : $id;
        $id = intval($id);

        $had = $this->find($id, $this->model->fields);
        $had = $this->toArr($had);
        $had['id'] = 'pre' . $had['id'];
        return $had;
    }
}